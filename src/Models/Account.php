<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class Account extends BaseModel
{
    protected string $table = 'accounts';

    /**
     * Buscar organizaciones con filtro opcional.
     */
    public function search(string $keyword = '', string $type = ''): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT a.*,
                       CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as owner_name
                FROM {$this->table} a
                LEFT JOIN users u ON u.id = a.owner_id
                WHERE a.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($keyword !== '') {
            $sql .= " AND (a.name LIKE :keyword OR a.email LIKE :keyword OR a.website LIKE :keyword)";
            $params[':keyword'] = "%{$keyword}%";
        }

        if ($type !== '') {
            $sql .= " AND a.type = :type";
            $params[':type'] = $type;
        }

        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND a.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql .= " ORDER BY a.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene el número total de organizaciones para el tenant actual.
     */
    public function getTotalAccounts(): int
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
