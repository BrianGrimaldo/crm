<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use PDO;

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    /**
     * Registra una nueva actividad.
     */
    public function log(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): bool
    {
        $tenantId = TenantContext::getTenantId();
        $userId = $_SESSION['user_id'] ?? null;
        
        $sql = "INSERT INTO {$this->table} (tenant_id, user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                VALUES (:tenant_id, :user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip, :ua)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':old_values' => $oldValues ? json_encode($oldValues) : null,
            ':new_values' => $newValues ? json_encode($newValues) : null,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ]);
    }

    /**
     * Obtiene la actividad reciente del tenant.
     */
    public function getRecentActivity(int $limit = 10): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as user_name 
                FROM {$this->table} a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE a.tenant_id = :tenant_id
                ORDER BY a.created_at DESC
                LIMIT :limit";
                
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
