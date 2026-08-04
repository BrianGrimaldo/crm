<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class Ticket
{
    private PDO $db;
    private string $table = 'tickets';

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los tickets del tenant.
     */
    public function all(): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT t.*, 
                       c.first_name as contact_first, c.last_name as contact_last, c.email as contact_email,
                       a.name as account_name,
                       u.name as assigned_name,
                       tp.name as tipification_name, tp.color as tipification_color
                FROM {$this->table} t
                LEFT JOIN contacts c ON t.contact_id = c.id
                LEFT JOIN accounts a ON t.account_id = a.id
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN tipifications tp ON t.tipification_id = tp.id
                WHERE t.tenant_id = :tenant_id";

        // Si el usuario está restringido a sus propios registros
        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND (t.assigned_to = :user_id OR t.created_by = :user_id)";
            $params = [
                ':tenant_id' => $tenantId,
                ':user_id' => $_SESSION['user_id'] ?? 0
            ];
        } else {
            $params = [':tenant_id' => $tenantId];
        }

        $sql .= " ORDER BY t.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Busca un ticket por ID asegurando pertenencia al tenant.
     */
    public function find(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT t.*, 
                       c.first_name as contact_first, c.last_name as contact_last, c.email as contact_email,
                       a.name as account_name,
                       u.name as assigned_name,
                       tp.name as tipification_name, tp.color as tipification_color
                FROM {$this->table} t
                LEFT JOIN contacts c ON t.contact_id = c.id
                LEFT JOIN accounts a ON t.account_id = a.id
                LEFT JOIN users u ON t.assigned_to = u.id
                LEFT JOIN tipifications tp ON t.tipification_id = tp.id
                WHERE t.id = :id AND t.tenant_id = :tenant_id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        return $result ?: null;
    }

    /**
     * Crea un nuevo ticket.
     */
    public function create(array $data): int
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "INSERT INTO {$this->table} 
                (tenant_id, contact_id, account_id, assigned_to, subject, description, priority, status, channel, category, due_date, created_by)
                VALUES 
                (:tenant_id, :contact_id, :account_id, :assigned_to, :subject, :description, :priority, :status, :channel, :category, :due_date, :created_by)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id'   => $tenantId,
            ':contact_id'  => $data['contact_id'] ?? null,
            ':account_id'  => $data['account_id'] ?? null,
            ':assigned_to' => $data['assigned_to'] ?? null,
            ':subject'     => $data['subject'],
            ':description' => $data['description'] ?? null,
            ':priority'    => $data['priority'] ?? 'medium',
            ':status'      => $data['status'] ?? 'open',
            ':channel'     => $data['channel'] ?? 'web',
            ':category'    => $data['category'] ?? null,
            ':due_date'    => $data['due_date'] ?? null,
            ':created_by'  => $_SESSION['user_id'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un ticket.
     */
    public function update(int $id, array $data): bool
    {
        $tenantId = TenantContext::getTenantId();
        
        $fields = [];
        $params = [':id' => $id, ':tenant_id' => $tenantId];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}` = :{$key}";
            $params[":{$key}"] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id AND tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Elimina un ticket.
     */
    public function delete(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }

    /**
     * Obtiene los comentarios de un ticket.
     */
    public function getComments(int $ticketId): array
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "SELECT tc.*, u.name as user_name
                FROM ticket_comments tc
                LEFT JOIN users u ON tc.user_id = u.id
                WHERE tc.ticket_id = :ticket_id AND tc.tenant_id = :tenant_id
                ORDER BY tc.created_at ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ticket_id' => $ticketId, ':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Añade un comentario a un ticket.
     */
    public function addComment(array $data): int
    {
        $tenantId = TenantContext::getTenantId();
        $sql = "INSERT INTO ticket_comments 
                (tenant_id, ticket_id, user_id, body, is_internal)
                VALUES 
                (:tenant_id, :ticket_id, :user_id, :body, :is_internal)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id'   => $tenantId,
            ':ticket_id'   => $data['ticket_id'],
            ':user_id'     => $data['user_id'] ?? null,
            ':body'        => $data['body'],
            ':is_internal' => $data['is_internal'] ?? 0,
        ]);

        return (int)$this->db->lastInsertId();
    }
}
