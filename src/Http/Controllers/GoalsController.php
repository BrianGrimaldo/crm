<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

/**
 * GoalsController
 *
 * Maneja el CRUD de metas (mensuales/trimestrales, individuales o de
 * equipo) y calcula el avance real comparando contra:
 *   - sales_won:        suma de deals.amount donde is_won = 1, dentro del periodo
 *                        (filtrando por actual_close_date, no created_at)
 *   - revenue_collected: suma de invoice_payments dentro del periodo
 *
 * Todas las consultas están aisladas por tenant_id (multi-tenant).
 */
class GoalsController
{
    private \PDO $db;

    /** Roles que no deben acceder a este módulo. */
    private const BLOCKED_ROLES = ['cobranza', 'collections', 'cobrador'];

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    // ─── Acciones públicas ──────────────────────────────────────

    /**
     * Vista principal del módulo de metas.
     */
    public function index(): void
    {
        Permission::require('deals', 'view');
        $this->blockCobranza();

        $tenantId = TenantContext::getTenantId();
        $isSuperadmin = Permission::isSuperadmin();

        $period = $this->resolveRequestedPeriod();

        // Superadmin ve metas de TODOS los tenants; los demás solo de su empresa
        if ($isSuperadmin) {
            $goals = $this->getGoalsWithProgressAllTenants($period['start'], $period['end']);
        } else {
            $goals = $this->getGoalsWithProgress($tenantId, $period['start'], $period['end']);
        }

        // Vendedor: solo ve sus metas individuales + metas de equipo de su tenant
        if (Permission::isVendedor()) {
            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $goals = array_values(array_filter($goals, function ($g) use ($userId, $tenantId) {
                return (int) $g['tenant_id'] === $tenantId
                    && ($g['owner_id'] === null || (int) $g['owner_id'] === $userId);
            }));
        }

        // Lista de usuarios para el formulario de creación
        $tenantUsers = $this->fetchUserList($isSuperadmin, $tenantId);

        require __DIR__ . '/../../Views/goals/index.php';
    }

    /**
     * Crea o actualiza una meta. Espera JSON:
     * { owner_id, target_tenant_id, period_type, period_start,
     *   metric_type, target_amount, notes }
     */
    public function store(): void
    {
        header('Content-Type: application/json');
        Permission::require('deals', 'update');
        $this->blockCobranza();

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $tenantId = TenantContext::getTenantId();
        $currentUserId = (int) ($_SESSION['user_id'] ?? 0);

        // Validar que hay un usuario autenticado válido
        if ($currentUserId <= 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Sesión expirada. Inicia sesión de nuevo.']);
            return;
        }

        // Superadmin puede crear metas para cualquier empresa
        if (Permission::isSuperadmin() && !empty($input['target_tenant_id'])) {
            $targetTenantId = (int) $input['target_tenant_id'];
            if (!$this->tenantExists($targetTenantId)) {
                http_response_code(422);
                echo json_encode(['error' => 'La empresa destino no existe.']);
                return;
            }
            $tenantId = $targetTenantId;
        }

        // Vendedor: forzar su propio ID; no puede asignar metas a otros
        if (Permission::isVendedor()) {
            $ownerId = $currentUserId;
        } else {
            $ownerId = isset($input['owner_id']) && $input['owner_id'] !== ''
                ? (int) $input['owner_id']
                : null;
        }

        // Validar que el owner pertenezca al tenant destino
        if ($ownerId !== null && !$this->userBelongsToTenant($ownerId, $tenantId)) {
            http_response_code(422);
            echo json_encode(['error' => 'El vendedor seleccionado no pertenece a esa empresa.']);
            return;
        }

        // Sanitizar y validar campos
        $periodType = in_array($input['period_type'] ?? '', ['monthly', 'quarterly'], true)
            ? $input['period_type'] : 'monthly';
        $metricType = in_array($input['metric_type'] ?? '', ['sales_won', 'revenue_collected'], true)
            ? $input['metric_type'] : 'sales_won';
        $targetAmount = (float) ($input['target_amount'] ?? 0);
        $notes = isset($input['notes']) ? mb_substr(trim((string) $input['notes']), 0, 255) : null;
        $notes = $notes === '' ? null : $notes;

        if ($targetAmount <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'El monto objetivo debe ser mayor a cero.']);
            return;
        }

        $rawStart = $input['period_start'] ?? null;
        if (!$rawStart || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawStart)) {
            http_response_code(422);
            echo json_encode(['error' => 'Fecha de inicio de periodo inválida.']);
            return;
        }

        [$periodStart, $periodEnd] = $this->computePeriodBounds($rawStart, $periodType);

        try {
            $stmt = $this->db->prepare("
                INSERT INTO goals
                    (tenant_id, owner_id, period_type, period_start, period_end,
                     metric_type, target_amount, currency_code, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'MXN', ?, ?)
                ON DUPLICATE KEY UPDATE
                    target_amount = VALUES(target_amount),
                    notes = VALUES(notes),
                    updated_at = NOW()
            ");
            $stmt->execute([
                $tenantId, $ownerId, $periodType, $periodStart, $periodEnd,
                $metricType, $targetAmount, $notes, $currentUserId,
            ]);

            echo json_encode(['success' => true]);
        } catch (\PDOException $e) {
            http_response_code(500);
            error_log('[GoalsController::store] ' . $e->getMessage());
            echo json_encode(['error' => 'No se pudo guardar la meta. Intenta de nuevo.']);
        }
    }

    /**
     * Elimina una meta por ID.
     * - Vendedor:   solo puede eliminar sus propias metas.
     * - Admin:      puede eliminar cualquier meta de su tenant.
     * - Superadmin: puede eliminar cualquier meta de cualquier tenant.
     */
    public function destroy(): void
    {
        header('Content-Type: application/json');
        Permission::require('deals', 'update');
        $this->blockCobranza();

        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(422);
            echo json_encode(['error' => 'ID de meta inválido.']);
            return;
        }

        if (Permission::isSuperadmin()) {
            // Superadmin: sin restricción de tenant
            $sql = "DELETE FROM goals WHERE id = ?";
            $params = [$id];
        } elseif (Permission::isVendedor()) {
            // Vendedor: solo sus propias metas dentro de su tenant
            $tenantId = TenantContext::getTenantId();
            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $sql = "DELETE FROM goals WHERE id = ? AND tenant_id = ? AND owner_id = ?";
            $params = [$id, $tenantId, $userId];
        } else {
            // Admin: cualquier meta de su tenant
            $tenantId = TenantContext::getTenantId();
            $sql = "DELETE FROM goals WHERE id = ? AND tenant_id = ?";
            $params = [$id, $tenantId];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Meta no encontrada o sin permisos para eliminarla.']);
        }
    }

    // ─── Lógica de progreso (pública para IAController) ─────────

    /**
     * Devuelve metas de UN tenant con su avance calculado.
     * Optimizado: 1 query por tipo de métrica en vez de 1 por meta.
     *
     * @return array<int, array<string,mixed>>
     */
    public function getGoalsWithProgress(int $tenantId, string $periodStart, string $periodEnd): array
    {
        $goals = $this->fetchGoalsForTenant($tenantId, $periodStart, $periodEnd);
        return $this->enrichGoalsWithProgress($goals, $tenantId, $periodStart, $periodEnd);
    }

    /**
     * Devuelve metas de TODOS los tenants con su avance (para superadmin).
     */
    private function getGoalsWithProgressAllTenants(string $periodStart, string $periodEnd): array
    {
        $stmt = $this->db->prepare("
            SELECT g.id, g.tenant_id, g.owner_id, g.period_type, g.period_start, g.period_end,
                   g.metric_type, g.target_amount, g.currency_code, g.notes,
                   CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS owner_name,
                   t.name AS tenant_name
            FROM goals g
            LEFT JOIN users u ON u.id = g.owner_id
            JOIN tenants t ON t.id = g.tenant_id
            WHERE g.period_start = ? AND g.period_end = ?
            ORDER BY t.name, (g.owner_id IS NULL) DESC, owner_name ASC
        ");
        $stmt->execute([$periodStart, $periodEnd]);
        $goals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agrupar por tenant y enriquecer en batch
        $grouped = [];
        foreach ($goals as $goal) {
            $grouped[(int) $goal['tenant_id']][] = $goal;
        }

        $enriched = [];
        foreach ($grouped as $tid => $tenantGoals) {
            $enriched = array_merge(
                $enriched,
                $this->enrichGoalsWithProgress($tenantGoals, $tid, $periodStart, $periodEnd)
            );
        }

        return $enriched;
    }

    // ─── Métodos privados de datos ──────────────────────────────

    /**
     * Recupera las metas crudas de un tenant para un periodo.
     */
    private function fetchGoalsForTenant(int $tenantId, string $periodStart, string $periodEnd): array
    {
        $stmt = $this->db->prepare("
            SELECT g.id, g.tenant_id, g.owner_id, g.period_type, g.period_start, g.period_end,
                   g.metric_type, g.target_amount, g.currency_code, g.notes,
                   CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) AS owner_name
            FROM goals g
            LEFT JOIN users u ON u.id = g.owner_id
            WHERE g.tenant_id = ?
              AND g.period_start = ?
              AND g.period_end = ?
            ORDER BY (g.owner_id IS NULL) DESC, owner_name ASC
        ");
        $stmt->execute([$tenantId, $periodStart, $periodEnd]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enriquece un array de metas con achieved_amount, progress_pct, etc.
     * Optimización: agrupa por metric_type y ejecuta UNA sola query
     * para obtener los montos logrados de todos los owners de golpe.
     */
    private function enrichGoalsWithProgress(array $goals, int $tenantId, string $start, string $end): array
    {
        if (empty($goals)) {
            return [];
        }

        // Obtener los montos logrados en batch por tipo de métrica
        $salesMap = $this->getSalesWonBatch($tenantId, $start, $end);
        $revenueMap = $this->getRevenueCollectedBatch($tenantId, $start, $end);

        foreach ($goals as &$goal) {
            $ownerId = $goal['owner_id'] !== null ? (int) $goal['owner_id'] : null;
            $mapKey = $ownerId ?? '__team__';

            $achieved = $goal['metric_type'] === 'revenue_collected'
                ? (float) ($revenueMap[$mapKey] ?? 0)
                : (float) ($salesMap[$mapKey] ?? 0);

            $target = (float) $goal['target_amount'];
            $goal['achieved_amount'] = $achieved;
            $goal['progress_pct'] = $target > 0 ? round(min($achieved / $target * 100, 999), 1) : 0;
            $goal['remaining_amount'] = max($target - $achieved, 0);
            $goal['is_at_risk'] = $this->isGoalAtRisk($goal, $achieved);
        }
        unset($goal);

        return $goals;
    }

    /**
     * Devuelve un mapa [ owner_id => total, '__team__' => total_equipo ]
     * con las ventas ganadas del periodo, en UNA sola query.
     */
    private function getSalesWonBatch(int $tenantId, string $start, string $end): array
    {
        $stmt = $this->db->prepare("
            SELECT owner_id, COALESCE(SUM(amount), 0) AS total
            FROM deals
            WHERE tenant_id = ?
              AND is_won = 1
              AND actual_close_date BETWEEN ? AND ?
            GROUP BY owner_id
        ");
        $stmt->execute([$tenantId, $start, $end]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        $teamTotal = 0.0;
        foreach ($rows as $row) {
            $key = $row['owner_id'] !== null ? (int) $row['owner_id'] : 0;
            $val = (float) $row['total'];
            $map[$key] = $val;
            $teamTotal += $val;
        }
        $map['__team__'] = $teamTotal;

        return $map;
    }

    /**
     * Devuelve un mapa [ owner_id => total, '__team__' => total_equipo ]
     * con la facturación cobrada del periodo, en UNA sola query.
     *
     * Usa invoices.tenant_id directamente (no depende de deals),
     * para soportar facturas manuales sin deal asociado.
     */
    private function getRevenueCollectedBatch(int $tenantId, string $start, string $end): array
    {
        $stmt = $this->db->prepare("
            SELECT i.owner_id, COALESCE(SUM(ip.amount), 0) AS total
            FROM invoice_payments ip
            JOIN invoices i ON i.id = ip.invoice_id
            WHERE i.tenant_id = ?
              AND ip.payment_date BETWEEN ? AND ?
            GROUP BY i.owner_id
        ");
        $stmt->execute([$tenantId, $start, $end]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        $teamTotal = 0.0;
        foreach ($rows as $row) {
            $key = $row['owner_id'] !== null ? (int) $row['owner_id'] : 0;
            $val = (float) $row['total'];
            $map[$key] = $val;
            $teamTotal += $val;
        }
        $map['__team__'] = $teamTotal;

        return $map;
    }

    /**
     * Una meta está "en riesgo" si, al ritmo actual de avance diario,
     * no se alcanzará para el final del periodo.
     */
    private function isGoalAtRisk(array $goal, float $achieved): bool
    {
        $today = new \DateTimeImmutable('today');
        $start = new \DateTimeImmutable($goal['period_start']);
        $end = new \DateTimeImmutable($goal['period_end']);
        $target = (float) $goal['target_amount'];

        // Si ya se alcanzó la meta, no hay riesgo
        if ($target <= 0 || $achieved >= $target) {
            return false;
        }

        // Periodo ya terminó y no se alcanzó
        if ($today >= $end) {
            return true;
        }

        // Periodo aún no comienza
        if ($today < $start) {
            return false;
        }

        $totalDays = max($start->diff($end)->days, 1);
        $elapsedDays = max($start->diff($today)->days, 1);
        $expectedPct = $elapsedDays / $totalDays;
        $actualPct = $achieved / $target;

        // Si vamos 15pp o más por debajo del ritmo esperado → en riesgo
        return ($expectedPct - $actualPct) >= 0.15;
    }

    // ─── Helpers de periodo ─────────────────────────────────────

    /**
     * Convierte una fecha de inicio + tipo de periodo en [start, end].
     */
    private function computePeriodBounds(string $rawStart, string $periodType): array
    {
        $start = new \DateTimeImmutable($rawStart);

        if ($periodType === 'quarterly') {
            $quarterStartMonth = (intdiv(((int) $start->format('n') - 1), 3)) * 3 + 1;
            $start = $start->setDate((int) $start->format('Y'), $quarterStartMonth, 1);
            $end = $start->modify('+3 months -1 day');
        } else {
            $start = $start->setDate((int) $start->format('Y'), (int) $start->format('n'), 1);
            $end = $start->modify('+1 month -1 day');
        }

        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    /**
     * Resuelve el periodo solicitado por query string, por defecto el mes actual.
     */
    private function resolveRequestedPeriod(): array
    {
        $periodType = in_array($_GET['period_type'] ?? '', ['monthly', 'quarterly'], true)
            ? $_GET['period_type'] : 'monthly';
        $rawStart = $_GET['period_start'] ?? (new \DateTimeImmutable('today'))->format('Y-m-d');

        [$start, $end] = $this->computePeriodBounds($rawStart, $periodType);
        return ['start' => $start, 'end' => $end, 'type' => $periodType];
    }

    // ─── Helpers de validación ───────────────────────────────────

    /**
     * Bloquea el acceso a usuarios con rol de cobranza.
     */
    private function blockCobranza(): void
    {
        $roleStr = strtolower(str_replace(['-', ' '], '', $_SESSION['user_role'] ?? ''));
        if (in_array($roleStr, self::BLOCKED_ROLES, true)) {
            if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
                header('Content-Type: application/json');
                http_response_code(403);
                echo json_encode(['error' => 'No tienes acceso a este módulo.']);
            } else {
                header('Location: ' . url('/dashboard'));
            }
            exit;
        }
    }

    /**
     * Verifica que un tenant exista y esté activo.
     */
    private function tenantExists(int $tenantId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM tenants WHERE id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Verifica que un usuario pertenezca a un tenant dado.
     */
    private function userBelongsToTenant(int $userId, int $tenantId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM tenant_users WHERE user_id = ? AND tenant_id = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$userId, $tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Devuelve la lista de usuarios para el dropdown del formulario.
     * Superadmin: todos los usuarios de todos los tenants.
     * Admin/Vendedor: solo usuarios de su tenant.
     */
    private function fetchUserList(bool $isSuperadmin, int $tenantId): array
    {
        if ($isSuperadmin) {
            $stmt = $this->db->prepare("
                SELECT u.id, tu.tenant_id, u.first_name, u.last_name,
                       r.name AS role, t.name AS tenant_name
                FROM users u
                JOIN tenant_users tu ON tu.user_id = u.id AND tu.is_active = 1
                JOIN roles r ON tu.role_id = r.id
                JOIN tenants t ON tu.tenant_id = t.id AND t.is_active = 1
                ORDER BY t.name, u.first_name
            ");
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("
                SELECT u.id, tu.tenant_id, u.first_name, u.last_name,
                       r.name AS role, t.name AS tenant_name
                FROM users u
                JOIN tenant_users tu ON tu.user_id = u.id AND tu.is_active = 1
                JOIN roles r ON tu.role_id = r.id
                JOIN tenants t ON tu.tenant_id = t.id
                WHERE tu.tenant_id = ?
                ORDER BY u.first_name
            ");
            $stmt->execute([$tenantId]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}