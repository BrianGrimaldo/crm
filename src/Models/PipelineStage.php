<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use PDO;

class PipelineStage extends BaseModel
{
    protected string $table = 'pipeline_stages';

    /**
     * Obtiene todas las etapas ordenadas por posición.
     */
    public function allOrdered(): array
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY position ASC"
        );
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findById(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id LIMIT 1");
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (tenant_id, name, position, probability, is_won, is_lost, color) 
                VALUES (:tenant_id, :name, :position, :probability, :is_won, :is_lost, :color)";
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':tenant_id' => $data['tenant_id'],
            ':name' => $data['name'],
            ':position' => $data['position'],
            ':probability' => $data['probability'] ?? 0,
            ':is_won' => isset($data['is_won']) ? 1 : 0,
            ':is_lost' => isset($data['is_lost']) ? 1 : 0,
            ':color' => $data['color'] ?? '#94A3B8'
        ]);
        
        return $success ? (int)$this->db->lastInsertId() : 0;
    }

    public function update(int $id, array $data): bool
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "UPDATE {$this->table} 
                SET name = :name, position = :position, probability = :probability, 
                    is_won = :is_won, is_lost = :is_lost, color = :color
                WHERE id = :id AND tenant_id = :tenant_id";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $data['name'],
            ':position' => $data['position'],
            ':probability' => $data['probability'] ?? 0,
            ':is_won' => isset($data['is_won']) ? 1 : 0,
            ':is_lost' => isset($data['is_lost']) ? 1 : 0,
            ':color' => $data['color'] ?? '#94A3B8',
            ':id' => $id,
            ':tenant_id' => $tenantId
        ]);
    }

    public function delete(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id");
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }
}
