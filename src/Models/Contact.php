<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class Contact extends BaseModel
{
    protected string $table = 'contacts';

    /**
     * Buscar contactos con filtro opcional.
     */
    public function search(string $keyword = '', string $type = ''): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT c.*, 
                       a.name as account_name,
                       CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as owner_name
                FROM {$this->table} c
                LEFT JOIN accounts a ON a.id = c.account_id
                LEFT JOIN users u ON u.id = c.owner_id
                WHERE c.tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if ($keyword !== '') {
            $sql .= " AND (c.first_name LIKE :keyword1 OR c.last_name LIKE :keyword2)";
            $params[':keyword1'] = "%{$keyword}%";
            $params[':keyword2'] = "%{$keyword}%";
        }
        if ($type !== '') {
            $sql .= " AND c.type = :type";
            $params[':type'] = $type;
        }

        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND c.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtiene el total de contactos.
     */
    public function getTotalContacts(): int
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE tenant_id = :tenant_id";
        $params = [':tenant_id' => $tenantId];

        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Lista simple de contactos del tenant para dropdowns.
     */
    public function getTenantContacts(): array
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT id, first_name, last_name FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY first_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
