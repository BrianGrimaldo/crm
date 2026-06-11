<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\TenantContext;
use PDO;

class Task
{
    private PDO $db;
    private string $table = 'tasks';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllForTenant(int $assignedTo = 0): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT t.*, u.first_name, u.last_name, 
                       c.first_name as contact_first_name, c.last_name as contact_last_name,
                       d.name as deal_name
                FROM {$this->table} t
                LEFT JOIN users u ON u.id = t.assigned_to
                LEFT JOIN contacts c ON c.id = t.related_id AND t.related_type = 'contact'
                LEFT JOIN deals d ON d.id = t.related_id AND t.related_type = 'deal'
                WHERE t.tenant_id = :tenant_id";
        
        $params = [':tenant_id' => $tenantId];

        if ($assignedTo > 0) {
            $sql .= " AND t.assigned_to = :assigned_to";
            $params[':assigned_to'] = $assignedTo;
        }

        $sql .= " ORDER BY t.due_date ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function getPendingForToday(int $assignedTo): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT t.*, d.name as deal_name, c.first_name as contact_first_name
                FROM {$this->table} t
                LEFT JOIN deals d ON d.id = t.related_id AND t.related_type = 'deal'
                LEFT JOIN contacts c ON c.id = t.related_id AND t.related_type = 'contact'
                WHERE t.tenant_id = :tenant_id 
                  AND t.assigned_to = :assigned_to 
                  AND t.status IN ('pending', 'in_progress')
                  AND DATE(t.due_date) <= CURDATE()
                ORDER BY t.due_date ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':assigned_to' => $assignedTo]);
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
        $tenantId = TenantContext::getTenantId();
        
        $sql = "INSERT INTO {$this->table} (tenant_id, assigned_to, created_by, related_type, related_id, title, description, priority, status, due_date) 
                VALUES (:tenant_id, :assigned_to, :created_by, :related_type, :related_id, :title, :description, :priority, :status, :due_date)";
                
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':tenant_id'   => $tenantId,
            ':assigned_to' => $data['assigned_to'] ?: null,
            ':created_by'  => $_SESSION['user_id'] ?? null,
            ':related_type'=> $data['related_type'] ?: null,
            ':related_id'  => $data['related_id'] ?: null,
            ':title'       => $data['title'],
            ':description' => $data['description'] ?? null,
            ':priority'    => $data['priority'] ?? 'medium',
            ':status'      => $data['status'] ?? 'pending',
            ':due_date'    => $data['due_date'] ?: null
        ]);

        return $success ? (int)$this->db->lastInsertId() : 0;
    }

    public function update(int $id, array $data): bool
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "UPDATE {$this->table} 
                SET assigned_to = :assigned_to, related_type = :related_type, related_id = :related_id, 
                    title = :title, description = :description, priority = :priority, status = :status, due_date = :due_date";
        
        if (isset($data['status']) && $data['status'] === 'completed') {
            $sql .= ", completed_at = CURRENT_TIMESTAMP";
        } elseif (isset($data['status']) && $data['status'] !== 'completed') {
            $sql .= ", completed_at = NULL";
        }

        $sql .= " WHERE id = :id AND tenant_id = :tenant_id";
                
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':assigned_to' => $data['assigned_to'] ?: null,
            ':related_type'=> $data['related_type'] ?: null,
            ':related_id'  => $data['related_id'] ?: null,
            ':title'       => $data['title'],
            ':description' => $data['description'] ?? null,
            ':priority'    => $data['priority'] ?? 'medium',
            ':status'      => $data['status'] ?? 'pending',
            ':due_date'    => $data['due_date'] ?: null,
            ':id'          => $id,
            ':tenant_id'   => $tenantId
        ]);
    }

    public function delete(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id");
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }
    
    public function updateStatus(int $id, string $status): bool
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "UPDATE {$this->table} SET status = :status";
        if ($status === 'completed') {
            $sql .= ", completed_at = CURRENT_TIMESTAMP";
        } else {
            $sql .= ", completed_at = NULL";
        }
        $sql .= " WHERE id = :id AND tenant_id = :tenant_id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $id, ':tenant_id' => $tenantId]);
    }
}
