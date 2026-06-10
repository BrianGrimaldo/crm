<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class Activity extends BaseModel
{
    protected string $table = 'activities';

    /**
     * Registra una nueva actividad.
     */
    public function log(string $entityType, int $entityId, string $type, string $description): bool
    {
        $tenantId = TenantContext::getTenantId();
        $userId = $_SESSION['user_id'] ?? null;
        
        $sql = "INSERT INTO {$this->table} (tenant_id, user_id, entity_type, entity_id, type, description) 
                VALUES (:tenant_id, :user_id, :entity_type, :entity_id, :type, :description)";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $userId,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':type' => $type,
            ':description' => $description
        ]);
    }

    /**
     * Obtiene las actividades para una entidad específica.
     */
    public function getForEntity(string $entityType, int $entityId): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT a.*, CONCAT(u.first_name, ' ', u.last_name) as user_name 
                FROM {$this->table} a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE a.tenant_id = :tenant_id AND a.entity_type = :entity_type AND a.entity_id = :entity_id
                ORDER BY a.created_at DESC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
