<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class Invoice extends BaseModel
{
    protected string $table = 'invoices';

    // ─── RELATIONAL QUERIES ───────────────────────────────────

    public function allWithRelations(string $keyword = '', string $status = '', ?int $ownerId = null, bool $crossTenant = false): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT i.*,
                    d.name AS deal_name, d.status AS deal_status, d.amount AS deal_amount,
                    a.name AS account_name,
                    t.name AS tenant_name,
                    CONCAT(c.first_name, ' ', IFNULL(c.last_name, '')) AS contact_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name,
                    DATEDIFF(i.due_date, CURDATE()) AS days_until_due
                FROM {$this->table} i
                LEFT JOIN deals d ON d.id = i.deal_id
                LEFT JOIN accounts a ON a.id = i.account_id
                LEFT JOIN contacts c ON c.id = i.contact_id
                LEFT JOIN users u ON u.id = i.owner_id
                LEFT JOIN tenants t ON t.id = i.tenant_id
                WHERE 1=1";

        $params = [];

        if (!$crossTenant) {
            $sql .= " AND i.tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        if ($keyword !== '') {
            $sql .= " AND (i.invoice_number LIKE :kw1 OR d.name LIKE :kw2 OR a.name LIKE :kw3)";
            $params[':kw1'] = "%{$keyword}%";
            $params[':kw2'] = "%{$keyword}%";
            $params[':kw3'] = "%{$keyword}%";
        }

        if ($status !== '') {
            $sql .= " AND i.status = :status";
            $params[':status'] = $status;
        }

        if ($ownerId !== null) {
            $sql .= " AND i.owner_id = :owner_id";
            $params[':owner_id'] = $ownerId;
        }

        $sql .= " ORDER BY i.updated_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene una factura con relaciones completas.
     */
    public function findWithRelations(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT i.*,
                    d.name AS deal_name, d.status AS deal_status, d.amount AS deal_amount,
                    a.name AS account_name,
                    CONCAT(c.first_name, ' ', IFNULL(c.last_name, '')) AS contact_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name
                FROM {$this->table} i
                LEFT JOIN deals d ON d.id = i.deal_id
                LEFT JOIN accounts a ON a.id = i.account_id
                LEFT JOIN contacts c ON c.id = i.contact_id
                LEFT JOIN users u ON u.id = i.owner_id
                WHERE i.id = :id";

        $params = [':id' => $id];

        if (!Permission::canViewAllInvoices()) {
            $sql .= " AND i.tenant_id = :tenant_id";
            $params[':tenant_id'] = TenantContext::getTenantId();
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    // ─── DASHBOARD / KPI QUERIES ──────────────────────────────

    /**
     * Obtiene las métricas principales de facturación para el dashboard.
     */
    public function getFinanceStats(?int $ownerId = null): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT
                    COUNT(*) AS total_invoices,
                    IFNULL(SUM(total), 0) AS total_facturado,
                    
                    COUNT(CASE WHEN status = 'emitida' THEN 1 END) AS pending_count,
                    IFNULL(SUM(CASE WHEN status = 'emitida' THEN total ELSE 0 END), 0) AS pending_amount,
                    
                    COUNT(CASE WHEN status = 'vencida' THEN 1 END) AS overdue_count,
                    IFNULL(SUM(CASE WHEN status = 'vencida' THEN total ELSE 0 END), 0) AS overdue_amount,
                    
                    COUNT(CASE WHEN status = 'pagada' THEN 1 END) AS paid_count,
                    IFNULL(SUM(CASE WHEN status = 'pagada' THEN total ELSE 0 END), 0) AS paid_amount,

                    COUNT(CASE WHEN status = 'parcial' THEN 1 END) AS partial_count,
                    IFNULL(SUM(CASE WHEN status = 'parcial' THEN (total - amount_paid) ELSE 0 END), 0) AS partial_pending_amount,

                    COUNT(CASE WHEN status = 'cancelada' THEN 1 END) AS cancelled_count,

                    IFNULL(SUM(amount_paid), 0) AS total_cobrado,
                    
                    IFNULL(SUM(
                        CASE WHEN status IN ('emitida','parcial','vencida') 
                        THEN (total - amount_paid) ELSE 0 END
                    ), 0) AS total_por_cobrar
                FROM {$this->table}
                WHERE tenant_id = :tenant_id AND status != 'borrador'";

        $params = [':tenant_id' => $tenantId];

        if ($ownerId !== null) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $ownerId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_invoices' => (int) ($r['total_invoices'] ?? 0),
            'total_facturado' => (float) ($r['total_facturado'] ?? 0),
            'pending_count' => (int) ($r['pending_count'] ?? 0),
            'pending_amount' => (float) ($r['pending_amount'] ?? 0),
            'overdue_count' => (int) ($r['overdue_count'] ?? 0),
            'overdue_amount' => (float) ($r['overdue_amount'] ?? 0),
            'paid_count' => (int) ($r['paid_count'] ?? 0),
            'paid_amount' => (float) ($r['paid_amount'] ?? 0),
            'partial_count' => (int) ($r['partial_count'] ?? 0),
            'partial_pending_amount' => (float) ($r['partial_pending_amount'] ?? 0),
            'cancelled_count' => (int) ($r['cancelled_count'] ?? 0),
            'total_cobrado' => (float) ($r['total_cobrado'] ?? 0),
            'total_por_cobrar' => (float) ($r['total_por_cobrar'] ?? 0),
        ];
    }

    /**
     * Facturación mensual (últimos 6 meses) para gráfica de tendencia.
     */
    public function getInvoicesByMonth(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT
                    DATE_FORMAT(issue_date, '%Y-%m') AS month_key,
                    DATE_FORMAT(issue_date, '%b %Y') AS month_label,
                    IFNULL(SUM(total), 0) AS total_emitido,
                    IFNULL(SUM(amount_paid), 0) AS total_cobrado,
                    COUNT(*) AS invoice_count
                FROM {$this->table}
                WHERE tenant_id = :tenant_id
                  AND status != 'borrador'
                  AND issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                GROUP BY month_key, month_label
                ORDER BY month_key ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Distribución de facturas por estatus (para gráfica donut).
     */
    public function getStatusDistribution(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT status, COUNT(*) AS count, IFNULL(SUM(total), 0) AS amount
                FROM {$this->table}
                WHERE tenant_id = :tenant_id AND status != 'borrador'
                GROUP BY status
                ORDER BY FIELD(status, 'emitida','parcial','vencida','pagada','cancelada')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ─── ALERTAS / BANDERAS ROJAS ─────────────────────────────

    /**
     * ALERTA PRINCIPAL: Deals ganados que NO tienen factura emitida.
     * Cruza ventas (deals status=Ganado) vs facturación.
     */
    public function getDealsWithoutInvoice(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT d.id, d.name AS deal_name, d.amount, d.actual_close_date,
                    d.expected_close_date,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name,
                    DATEDIFF(CURDATE(), IFNULL(d.actual_close_date, d.updated_at)) AS days_since_won
                FROM deals d
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id
                  AND d.status = 'Ganado'
                  AND d.id NOT IN (
                      SELECT DISTINCT deal_id FROM invoices 
                      WHERE tenant_id = :tenant_id2 
                        AND deal_id IS NOT NULL 
                        AND status NOT IN ('cancelada','borrador')
                  )
                ORDER BY days_since_won DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':tenant_id2' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Facturas vencidas (banderas rojas de cobranza).
     */
    public function getOverdueInvoices(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT i.*, 
                    d.name AS deal_name,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name,
                    DATEDIFF(CURDATE(), i.due_date) AS days_overdue
                FROM {$this->table} i
                LEFT JOIN deals d ON d.id = i.deal_id
                LEFT JOIN accounts a ON a.id = i.account_id
                LEFT JOIN users u ON u.id = i.owner_id
                WHERE i.tenant_id = :tenant_id
                  AND i.status IN ('emitida','parcial')
                  AND i.due_date < CURDATE()
                ORDER BY days_overdue DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Facturas próximas a vencer (próximos 7 días).
     */
    public function getUpcomingDueInvoices(int $days = 7): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT i.*,
                    d.name AS deal_name,
                    a.name AS account_name,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name,
                    DATEDIFF(i.due_date, CURDATE()) AS days_until_due
                FROM {$this->table} i
                LEFT JOIN deals d ON d.id = i.deal_id
                LEFT JOIN accounts a ON a.id = i.account_id
                LEFT JOIN users u ON u.id = i.owner_id
                WHERE i.tenant_id = :tenant_id
                  AND i.status IN ('emitida','parcial')
                  AND i.due_date >= CURDATE()
                  AND i.due_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY)
                ORDER BY i.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ─── TOP DEUDORES ─────────────────────────────────────────

    /**
     * Top cuentas con mayor saldo pendiente.
     */
    public function getTopDebtors(int $limit = 5): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT a.id, a.name AS account_name,
                    COUNT(i.id) AS invoice_count,
                    IFNULL(SUM(i.total - i.amount_paid), 0) AS outstanding_amount
                FROM {$this->table} i
                JOIN accounts a ON a.id = i.account_id
                WHERE i.tenant_id = :tenant_id
                  AND i.status IN ('emitida','parcial','vencida')
                GROUP BY a.id, a.name
                HAVING outstanding_amount > 0
                ORDER BY outstanding_amount DESC
                LIMIT :lim";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ─── AUTO-UPDATE OVERDUE STATUS ───────────────────────────

    /**
     * Marca como 'vencida' las facturas emitidas/parciales cuya
     * due_date ya pasó. Se ejecuta antes de cargar el dashboard.
     */
    public function markOverdueInvoices(): int
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "UPDATE {$this->table}
                SET status = 'vencida'
                WHERE tenant_id = :tenant_id
                  AND status IN ('emitida','parcial')
                  AND due_date < CURDATE()";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->rowCount();
    }

    // ─── PAYMENTS ─────────────────────────────────────────────

    /**
     * Registra un pago y actualiza el estatus de la factura (Transaccional / ACID).
     */
    public function registerPayment(int $invoiceId, array $paymentData): bool
    {
        // Obtener el tenant_id REAL de la factura en lugar de asumir el actual
        $sqlCheck = "SELECT tenant_id FROM {$this->table} WHERE id = :id";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([':id' => $invoiceId]);
        $invoiceTenantId = $stmtCheck->fetchColumn();

        if (!$invoiceTenantId) {
            return false;
        }

        // Verificar permiso global de cobranza (Superadmin o Rol de Cobranza)
        $roleStr = strtolower(str_replace('-', '', $_SESSION['user_role'] ?? ''));
        $isGlobalCollector = $roleStr === 'superadmin' 
                          || strpos($roleStr, 'cobranza') !== false 
                          || strpos($roleStr, 'collection') !== false 
                          || strpos($roleStr, 'cobrador') !== false;

        // Si no tiene permiso global, validar que la factura sea de su empresa actual
        if (!$isGlobalCollector && $invoiceTenantId != TenantContext::getTenantId()) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Insert payment
            $sql = "INSERT INTO invoice_payments (tenant_id, invoice_id, amount, payment_method, payment_date, reference, notes, created_by)
                    VALUES (:tenant_id, :invoice_id, :amount, :method, :date, :ref, :notes, :user)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $invoiceTenantId,
                ':invoice_id' => $invoiceId,
                ':amount' => $paymentData['amount'],
                ':method' => $paymentData['payment_method'] ?? 'transferencia',
                ':date' => $paymentData['payment_date'],
                ':ref' => $paymentData['reference'] ?? null,
                ':notes' => $paymentData['notes'] ?? null,
                ':user' => $_SESSION['user_id'] ?? null,
            ]);

            // Update invoice amount_paid
            $sql2 = "UPDATE {$this->table} 
                     SET amount_paid = amount_paid + :amount
                     WHERE id = :id AND tenant_id = :tenant_id";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([
                ':amount' => $paymentData['amount'],
                ':id' => $invoiceId,
                ':tenant_id' => $invoiceTenantId,
            ]);

            // Fetch the updated invoice within the same transaction context
            $sql3 = "SELECT * FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id FOR UPDATE";
            $stmt3 = $this->db->prepare($sql3);
            $stmt3->execute([':id' => $invoiceId, ':tenant_id' => $invoiceTenantId]);
            $invoice = $stmt3->fetch(\PDO::FETCH_OBJ);

            if ($invoice) {
                if ($invoice->amount_paid >= $invoice->total) {
                    // Update to paid
                    $sql4 = "UPDATE {$this->table} SET status = 'pagada', paid_date = :paid_date WHERE id = :id";
                    $stmt4 = $this->db->prepare($sql4);
                    $stmt4->execute([':paid_date' => date('Y-m-d'), ':id' => $invoiceId]);
                } elseif ($invoice->status !== 'parcial') {
                    // Update to partial if it wasn't already
                    $sql4 = "UPDATE {$this->table} SET status = 'parcial' WHERE id = :id";
                    $stmt4 = $this->db->prepare($sql4);
                    $stmt4->execute([':id' => $invoiceId]);
                }
            }

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("Error en transacción de pago: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene pagos de una factura.
     */
    public function getPayments(int $invoiceId): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT p.*, 
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS created_by_name
                FROM invoice_payments p
                LEFT JOIN users u ON u.id = p.created_by
                WHERE p.invoice_id = :invoice_id";

        $params = [':invoice_id' => $invoiceId];

        if (!Permission::canViewAllInvoices()) {
            $sql .= " AND p.tenant_id = :tenant_id";
            $params[':tenant_id'] = TenantContext::getTenantId();
        }

        $sql .= " ORDER BY p.payment_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // ─── AUDITORÍA CEO ────────────────────────────────────────

    /**
     * Obtiene el listado de vendedores con métricas completas: ventas + facturación + cobranza.
     * Incluye vendedores con deals ganados aunque no tengan facturas.
     */
    public function getSellersFinanceAudit(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT 
                    u.id AS seller_id,
                    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS seller_name,
                    u.email AS seller_email,

                    -- Métricas de Ventas (Deals)
                    IFNULL(deals_data.total_deals, 0) AS total_deals,
                    IFNULL(deals_data.deals_ganados, 0) AS deals_ganados,
                    IFNULL(deals_data.monto_ventas, 0) AS monto_ventas,
                    IFNULL(deals_data.deals_sin_factura, 0) AS deals_sin_factura,

                    -- Métricas de Facturación
                    IFNULL(inv_data.total_invoices, 0) AS total_invoices,
                    IFNULL(inv_data.total_facturado, 0) AS total_facturado,
                    IFNULL(inv_data.total_cobrado, 0) AS total_cobrado,
                    IFNULL(inv_data.total_vencido, 0) AS total_vencido,
                    IFNULL(inv_data.total_por_cobrar, 0) AS total_por_cobrar,
                    IFNULL(inv_data.facturas_pagadas, 0) AS facturas_pagadas,
                    IFNULL(inv_data.facturas_vencidas, 0) AS facturas_vencidas

                FROM users u

                -- Sub-consulta: Métricas de Deals
                LEFT JOIN (
                    SELECT 
                        d.owner_id,
                        COUNT(*) AS total_deals,
                        SUM(CASE WHEN d.status = 'Ganado' THEN 1 ELSE 0 END) AS deals_ganados,
                        IFNULL(SUM(CASE WHEN d.status = 'Ganado' THEN d.amount ELSE 0 END), 0) AS monto_ventas,
                        SUM(CASE WHEN d.status = 'Ganado' AND d.id NOT IN (
                            SELECT DISTINCT deal_id FROM invoices 
                            WHERE tenant_id = :t1 AND deal_id IS NOT NULL AND status NOT IN ('cancelada','borrador')
                        ) THEN 1 ELSE 0 END) AS deals_sin_factura
                    FROM deals d
                    WHERE d.tenant_id = :t2
                    GROUP BY d.owner_id
                ) deals_data ON deals_data.owner_id = u.id

                -- Sub-consulta: Métricas de Facturación
                LEFT JOIN (
                    SELECT 
                        i.owner_id,
                        COUNT(*) AS total_invoices,
                        IFNULL(SUM(i.total), 0) AS total_facturado,
                        IFNULL(SUM(i.amount_paid), 0) AS total_cobrado,
                        IFNULL(SUM(CASE WHEN i.status = 'vencida' THEN (i.total - i.amount_paid) ELSE 0 END), 0) AS total_vencido,
                        IFNULL(SUM(CASE WHEN i.status IN ('emitida','parcial','vencida') THEN (i.total - i.amount_paid) ELSE 0 END), 0) AS total_por_cobrar,
                        SUM(CASE WHEN i.status = 'pagada' THEN 1 ELSE 0 END) AS facturas_pagadas,
                        SUM(CASE WHEN i.status = 'vencida' THEN 1 ELSE 0 END) AS facturas_vencidas
                    FROM invoices i
                    WHERE i.tenant_id = :t3 AND i.status != 'borrador'
                    GROUP BY i.owner_id
                ) inv_data ON inv_data.owner_id = u.id

                -- Solo usuarios que pertenecen a este tenant y tienen actividad
                JOIN tenant_users tu ON tu.user_id = u.id AND tu.tenant_id = :t4 AND tu.is_active = 1

                WHERE (deals_data.total_deals > 0 OR inv_data.total_invoices > 0)
                ORDER BY IFNULL(deals_data.monto_ventas, 0) DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':t1' => $tenantId,
            ':t2' => $tenantId,
            ':t3' => $tenantId,
            ':t4' => $tenantId,
        ]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Obtiene las facturas de un vendedor específico para desglose.
     */
    public function getSellerInvoices(int $sellerId): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT i.*,
                    a.name AS account_name,
                    d.name AS deal_name,
                    CONCAT(c.first_name, ' ', IFNULL(c.last_name,'')) AS contact_name,
                    DATEDIFF(CURDATE(), i.due_date) AS days_overdue
                FROM {$this->table} i
                LEFT JOIN accounts a ON a.id = i.account_id
                LEFT JOIN deals d ON d.id = i.deal_id
                LEFT JOIN contacts c ON c.id = i.contact_id
                WHERE i.tenant_id = :tenant_id AND i.owner_id = :seller_id AND i.status != 'borrador'
                ORDER BY i.issue_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':seller_id' => $sellerId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Obtiene los deals ganados del vendedor que no tienen factura.
     */
    public function getSellerDealsWithoutInvoice(int $sellerId): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT d.id, d.name AS deal_name, d.amount, d.actual_close_date,
                    a.name AS account_name,
                    DATEDIFF(CURDATE(), IFNULL(d.actual_close_date, d.updated_at)) AS days_since_won
                FROM deals d
                LEFT JOIN accounts a ON a.id = d.account_id
                WHERE d.tenant_id = :tenant_id
                  AND d.owner_id = :seller_id
                  AND d.status = 'Ganado'
                  AND d.id NOT IN (
                      SELECT DISTINCT deal_id FROM invoices 
                      WHERE tenant_id = :t2 AND deal_id IS NOT NULL AND status NOT IN ('cancelada','borrador')
                  )
                ORDER BY days_since_won DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':seller_id' => $sellerId, ':t2' => $tenantId]);
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    /**
     * Obtiene info básica del vendedor.
     */
    public function getSellerInfo(int $sellerId): ?object
    {
        $sql = "SELECT u.id, u.email, CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS name
                FROM users u WHERE u.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $sellerId]);
        $r = $stmt->fetch(\PDO::FETCH_OBJ);
        return $r ?: null;
    }

    // ─── WEBHOOK / API INGESTION ──────────────────────────────

    /**
     * Crea o actualiza una factura recibida por webhook/API externa.
     * Usa external_id como clave de deduplicación.
     */
    public function upsertFromWebhook(array $data): int
    {
        $tenantId = TenantContext::getTenantId();

        // Check if exists by external_id
        if (!empty($data['external_id'])) {
            $sql = "SELECT id FROM {$this->table} WHERE tenant_id = :tid AND external_id = :eid LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':tid' => $tenantId, ':eid' => $data['external_id']]);
            $existing = $stmt->fetch(PDO::FETCH_OBJ);

            if ($existing) {
                $data['source'] = 'webhook';
                $this->update((int) $existing->id, $data);
                return (int) $existing->id;
            }
        }

        $data['source'] = 'webhook';
        return $this->create($data);
    }
}
