<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class Deal extends BaseModel
{
    protected string $table = 'deals';

    /**
     * Obtiene todos los deals con información de etapa y contacto.
     */
    public function allWithRelations(string $keyword = '', string $status = ''): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT d.*, 
                    ps.name AS stage_name, ps.color AS stage_color,
                    CONCAT(c.first_name, ' ', IFNULL(c.last_name, '')) AS contact_name,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name
                FROM {$this->table} d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN contacts c ON c.id = d.contact_id
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if ($keyword !== '') {
            $sql .= " AND (d.name LIKE :keyword OR CONCAT(c.first_name, ' ', IFNULL(c.last_name, '')) LIKE :keyword2)";
            $params[':keyword'] = "%{$keyword}%";
            $params[':keyword2'] = "%{$keyword}%";
        }

        if ($status !== '') {
            $sql .= " AND d.status = :status";
            $params[':status'] = $status;
        }

        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql .= " ORDER BY d.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene los deals agrupados por etapa (para la vista Kanban).
     */
    public function allGroupedByStage(?int $overrideOwnerId = null): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT d.*, 
                    ps.name AS stage_name, ps.color AS stage_color, ps.position AS stage_position,
                    CONCAT(c.first_name, ' ', IFNULL(c.last_name, '')) AS contact_name,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name
                FROM {$this->table} d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN contacts c ON c.id = d.contact_id
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($overrideOwnerId !== null && !Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $overrideOwnerId;
        } elseif (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql .= " ORDER BY ps.position ASC, d.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene el resumen de totales por etapa.
     */
    public function summaryByStage(?int $overrideOwnerId = null): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT ps.id AS stage_id, ps.name, ps.color, ps.position,
                    COUNT(d.id) AS deal_count,
                    IFNULL(SUM(d.amount), 0) AS total_amount
                FROM pipeline_stages ps
                LEFT JOIN deals d ON d.stage_id = ps.id AND d.tenant_id = ps.tenant_id";

        $params = [':tenant_id' => $tenantId];

        if ($overrideOwnerId !== null && !Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $overrideOwnerId;
        } elseif (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql .= " WHERE ps.tenant_id = :tenant_id
                GROUP BY ps.id, ps.name, ps.color, ps.position
                ORDER BY ps.position ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function funnelData(?int $overrideOwnerId = null): array
    {
        $tenantId = TenantContext::getTenantId();
        $params = [':tenant_id' => $tenantId, ':tenant_id2' => $tenantId];
        $ownerFilter = "";

        if ($overrideOwnerId !== null && !Permission::isRestrictedToOwnRecords()) {
            $ownerFilter = " AND d.owner_id = :owner_id ";
            $params[':owner_id'] = $overrideOwnerId;
        } elseif (Permission::isRestrictedToOwnRecords()) {
            $ownerFilter = " AND d.owner_id = :owner_id ";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql = "
        SELECT
            ps.id          AS stage_id,
            ps.name        AS stage_name,
            ps.color       AS stage_color,
            ps.position    AS position,
            ps.is_won      AS is_won,
            ps.is_lost     AS is_lost,
            COUNT(d.id)    AS total_deals,
            COALESCE(SUM(d.amount), 0) AS total_amount,
            SUM(CASE WHEN d.status = 'Ganado' THEN 1 ELSE 0 END)  AS won_deals,
            SUM(CASE WHEN d.status = 'Perdido' THEN 1 ELSE 0 END) AS lost_deals,
            SUM(CASE WHEN d.status = 'Abierto' THEN 1 ELSE 0 END) AS open_deals
        FROM pipeline_stages ps
        LEFT JOIN deals d ON d.stage_id = ps.id AND d.tenant_id = :tenant_id {$ownerFilter}
        WHERE ps.tenant_id = :tenant_id2
        GROUP BY ps.id, ps.name, ps.color, ps.position, ps.is_won, ps.is_lost
        ORDER BY ps.position ASC
    ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene un deal con toda su información relacional.
     */
    public function findWithRelations(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT d.*, 
                    ps.name AS stage_name, ps.color AS stage_color,
                    CONCAT(c.first_name, ' ', IFNULL(c.last_name, '')) AS contact_name,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name
                FROM {$this->table} d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN contacts c ON c.id = d.contact_id
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.id = :id AND d.tenant_id = :tenant_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    /**
     * Obtiene métricas clave para el dashboard
     */
    public function getDashboardStats(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT 
                    COUNT(*) as total_deals,
                    SUM(CASE WHEN status = 'Ganado' THEN amount ELSE 0 END) AS total_won,
                    SUM(CASE WHEN status = 'Perdido' THEN amount ELSE 0 END) AS total_lost,
                    COUNT(CASE WHEN status = 'Abierto' THEN 1 END) AS open_deals_count,
                    COUNT(CASE WHEN status = 'Ganado' THEN 1 END) AS won_deals_count,
                    COUNT(CASE WHEN status = 'Perdido' THEN 1 END) AS lost_deals_count,
                    SUM(CASE WHEN status = 'Abierto' THEN amount ELSE 0 END) AS open_deals_amount
                FROM {$this->table} 
                WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_deals' => (int) ($result['total_deals'] ?? 0),
            'total_won' => (float) ($result['total_won'] ?? 0),
            'total_lost' => (float) ($result['total_lost'] ?? 0),
            'open_deals_count' => (int) ($result['open_deals_count'] ?? 0),
            'won_deals_count' => (int) ($result['won_deals_count'] ?? 0),
            'lost_deals_count' => (int) ($result['lost_deals_count'] ?? 0),
            'open_deals_amount' => (float) ($result['open_deals_amount'] ?? 0),
        ];
    }

    /**
     * Deals cerrados por mes (últimos 6 meses) para gráfica de tendencia.
     */
    public function getDealsByMonth(): array
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT 
                    DATE_FORMAT(updated_at, '%Y-%m') AS month_key,
                    DATE_FORMAT(updated_at, '%b %Y') AS month_label,
                    SUM(CASE WHEN status = 'Ganado' THEN amount ELSE 0 END) AS won_amount,
                    SUM(CASE WHEN status = 'Perdido' THEN amount ELSE 0 END) AS lost_amount,
                    COUNT(CASE WHEN status = 'Ganado' THEN 1 END) AS won_count
                FROM {$this->table}
                WHERE tenant_id = :tenant_id
                  AND updated_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Top 5 oportunidades abiertas por monto (para tabla de deals calientes).
     */
    public function getTopOpenDeals(int $limit = 5): array
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT d.id, d.name, d.amount, d.probability, d.expected_close_date,
                    ps.name AS stage_name, ps.color AS stage_color,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name
                FROM {$this->table} d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id AND d.status = 'Abierto'
                ORDER BY d.amount DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Resumen de rendimiento por vendedor.
     */
    public function getStatsByOwner(): array
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT 
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name,
                    COUNT(*) AS total_deals,
                    COUNT(CASE WHEN d.status = 'Ganado' THEN 1 END) AS won_deals,
                    IFNULL(SUM(CASE WHEN d.status = 'Ganado' THEN d.amount END), 0) AS won_amount
                FROM {$this->table} d
                JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id
                GROUP BY u.id, owner_name
                ORDER BY won_amount DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Lista simple de deals del tenant para dropdowns.
     */
    public function getTenantDeals(): array
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT id, name FROM {$this->table} WHERE tenant_id = :tenant_id AND status = 'Abierto' ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ─────────────────────────────────────────────────────────
    //  ANALYTICS DE COTIZACIONES
    // ─────────────────────────────────────────────────────────

    /**
     * Resumen de cotizaciones emitidas en el mes actual.
     * Incluye conteos por estado: vigentes, expiradas, concretadas, perdidas.
     */
    public function getQuotesThisMonth(): array
    {
        $tenantId = TenantContext::getTenantId();
        $params   = [':tenant_id' => $tenantId];

        $ownerFilter = '';
        if (Permission::isRestrictedToOwnRecords()) {
            $ownerFilter = ' AND d.owner_id = :owner_id';
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql = "SELECT
                    COUNT(*) AS total_quotes,
                    COUNT(CASE WHEN d.status = 'Ganado'  THEN 1 END) AS concretadas,
                    COUNT(CASE WHEN d.status = 'Perdido' THEN 1 END) AS perdidas,
                    COUNT(CASE WHEN d.status = 'Abierto'
                               AND (d.expires_at IS NULL OR d.expires_at >= CURDATE()) THEN 1 END) AS vigentes,
                    COUNT(CASE WHEN d.status = 'Abierto'
                               AND d.expires_at IS NOT NULL
                               AND d.expires_at < CURDATE() THEN 1 END) AS expiradas,
                    COALESCE(SUM(d.amount), 0) AS total_amount,
                    COALESCE(SUM(CASE WHEN d.status = 'Ganado' THEN d.amount END), 0) AS amount_won
                FROM {$this->table} d
                WHERE d.tenant_id = :tenant_id
                  AND MONTH(d.created_at) = MONTH(CURDATE())
                  AND YEAR(d.created_at)  = YEAR(CURDATE())
                  {$ownerFilter}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int) ($row['total_quotes'] ?? 0);
        $won   = (int) ($row['concretadas']  ?? 0);

        return [
            'total_quotes'  => $total,
            'concretadas'   => $won,
            'perdidas'      => (int) ($row['perdidas']  ?? 0),
            'vigentes'      => (int) ($row['vigentes']  ?? 0),
            'expiradas'     => (int) ($row['expiradas'] ?? 0),
            'total_amount'  => (float) ($row['total_amount'] ?? 0),
            'amount_won'    => (float) ($row['amount_won']   ?? 0),
            'conversion_pct'=> $total > 0 ? round(($won / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Estadísticas de seguimiento al día siguiente de crear una cotización.
     * Retorna cuántas cotizaciones del mes tuvieron actividad registrada
     * dentro de las primeras 24 horas siguientes a su creación.
     */
    public function getQuotesFollowupStats(): array
    {
        $tenantId = TenantContext::getTenantId();
        $params   = [':tenant_id' => $tenantId];

        $ownerFilter = '';
        if (Permission::isRestrictedToOwnRecords()) {
            $ownerFilter = ' AND d.owner_id = :owner_id';
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        // Cotizaciones del mes con al menos 1 actividad dentro del día siguiente
        $sql = "SELECT
                    COUNT(DISTINCT d.id)                AS total_quotes,
                    COUNT(DISTINCT followup.entity_id)  AS with_followup
                FROM {$this->table} d
                LEFT JOIN activities followup
                       ON followup.entity_type = 'deal'
                      AND followup.entity_id   = d.id
                      AND followup.created_at BETWEEN d.created_at
                                                  AND DATE_ADD(d.created_at, INTERVAL 1 DAY)
                WHERE d.tenant_id = :tenant_id
                  AND MONTH(d.created_at) = MONTH(CURDATE())
                  AND YEAR(d.created_at)  = YEAR(CURDATE())
                  {$ownerFilter}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total      = (int) ($row['total_quotes']  ?? 0);
        $followup   = (int) ($row['with_followup'] ?? 0);
        $noFollowup = $total - $followup;

        return [
            'total_quotes'   => $total,
            'with_followup'  => $followup,
            'no_followup'    => $noFollowup,
            'followup_pct'   => $total > 0 ? round(($followup / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Cotizaciones del mes agrupadas por área/departamento.
     * Incluye total, concretadas y monto para gráfica de barras.
     */
    public function getQuotesByArea(): array
    {
        $tenantId = TenantContext::getTenantId();
        $params   = [':tenant_id' => $tenantId];

        $ownerFilter = '';
        if (Permission::isRestrictedToOwnRecords()) {
            $ownerFilter = ' AND d.owner_id = :owner_id';
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql = "SELECT
                    COALESCE(NULLIF(d.area, ''), 'Sin Área') AS area,
                    COUNT(*) AS total_quotes,
                    COUNT(CASE WHEN d.status = 'Ganado' THEN 1 END) AS concretadas,
                    COUNT(CASE WHEN d.status = 'Abierto' THEN 1 END) AS vigentes,
                    COALESCE(SUM(d.amount), 0) AS total_amount
                FROM {$this->table} d
                WHERE d.tenant_id = :tenant_id
                  AND MONTH(d.created_at) = MONTH(CURDATE())
                  AND YEAR(d.created_at)  = YEAR(CURDATE())
                  {$ownerFilter}
                GROUP BY area
                ORDER BY total_quotes DESC
                LIMIT 15";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Matriz de conversión por vendedor: cuántos cotizan vs cuántos cierran.
     * Útil para identificar quién cotiza mucho pero cierra poco (y viceversa).
     */
    public function getQuoteConversionMatrix(): array
    {
        $tenantId = TenantContext::getTenantId();
        $params   = [':tenant_id' => $tenantId];

        $ownerFilter = '';
        if (Permission::isRestrictedToOwnRecords()) {
            $ownerFilter = ' AND d.owner_id = :owner_id';
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql = "SELECT
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name,
                    COUNT(*)  AS total_quotes,
                    COUNT(CASE WHEN d.status = 'Ganado'  THEN 1 END) AS won,
                    COUNT(CASE WHEN d.status = 'Perdido' THEN 1 END) AS lost,
                    COUNT(CASE WHEN d.status = 'Abierto' THEN 1 END) AS open,
                    COALESCE(SUM(d.amount), 0) AS total_amount,
                    COALESCE(SUM(CASE WHEN d.status = 'Ganado' THEN d.amount END), 0) AS won_amount,
                    ROUND(
                        COUNT(CASE WHEN d.status = 'Ganado' THEN 1 END) * 100.0
                        / NULLIF(COUNT(*), 0)
                    , 1) AS conversion_pct
                FROM {$this->table} d
                JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id
                  AND MONTH(d.created_at) = MONTH(CURDATE())
                  AND YEAR(d.created_at)  = YEAR(CURDATE())
                  {$ownerFilter}
                GROUP BY u.id, owner_name
                ORDER BY total_quotes DESC, won DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
