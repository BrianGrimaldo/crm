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
    public function allGroupedByStage(): array
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

        if (Permission::isRestrictedToOwnRecords()) {
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
    public function summaryByStage(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT ps.id AS stage_id, ps.name, ps.color, ps.position,
                    COUNT(d.id) AS deal_count,
                    IFNULL(SUM(d.amount), 0) AS total_amount
                FROM pipeline_stages ps
                LEFT JOIN deals d ON d.stage_id = ps.id AND d.tenant_id = ps.tenant_id";
                
        $params = [':tenant_id' => $tenantId];
        
        if (Permission::isRestrictedToOwnRecords()) {
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
            'total_deals' => (int)($result['total_deals'] ?? 0),
            'total_won' => (float)($result['total_won'] ?? 0),
            'total_lost' => (float)($result['total_lost'] ?? 0),
            'open_deals_count' => (int)($result['open_deals_count'] ?? 0),
            'won_deals_count' => (int)($result['won_deals_count'] ?? 0),
            'lost_deals_count' => (int)($result['lost_deals_count'] ?? 0),
            'open_deals_amount' => (float)($result['open_deals_amount'] ?? 0),
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
}
