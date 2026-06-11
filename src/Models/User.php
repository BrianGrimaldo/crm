<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use PDO;

class User extends BaseModel
{
    protected string $table = 'users';

    /**
     * Obtiene los usuarios que pertenecen al tenant actual
     */
    public function getTenantUsers(): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT u.id, u.uuid, u.first_name, u.last_name, u.email, u.phone, 
                       u.avatar_url, u.is_active, tu.role_id, tu.is_owner, r.name as role_name
                FROM {$this->table} u
                INNER JOIN tenant_users tu ON tu.user_id = u.id
                LEFT JOIN roles r ON r.id = tu.role_id
                WHERE tu.tenant_id = :tenant_id
                ORDER BY u.first_name ASC";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
    
    /**
     * Encuentra un usuario por su ID asegurando que pertenece al tenant
     */
    public function findTenantUser(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();
        
        $sql = "SELECT u.*, tu.role_id, tu.is_owner
                FROM {$this->table} u
                INNER JOIN tenant_users tu ON tu.user_id = u.id
                WHERE tu.tenant_id = :tenant_id AND u.id = :id
                LIMIT 1";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ?: null;
    }

    /**
     * Actualiza el perfil del usuario (solo datos globales)
     */
    public function updateProfile(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Crea un usuario y lo asocia al tenant (por defecto al actual, o al proporcionado)
     */
    public function createUserForTenant(array $userData, int $roleId, ?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? TenantContext::getTenantId();
        
        try {
            $this->db->beginTransaction();

            // Insertar en la tabla global users
            $stmt = $this->db->prepare("
                INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, is_active) 
                VALUES (UUID(), :first_name, :last_name, :email, :phone, :password_hash, 1)
            ");
            $stmt->execute([
                ':first_name' => $userData['first_name'],
                ':last_name' => $userData['last_name'],
                ':email' => $userData['email'],
                ':phone' => $userData['phone'] ?? null,
                ':password_hash' => password_hash($userData['password'], PASSWORD_DEFAULT)
            ]);
            
            $userId = $this->db->lastInsertId();

            // Asociar al tenant con su rol
            $stmtTenantUser = $this->db->prepare("
                INSERT INTO tenant_users (tenant_id, user_id, role_id, is_owner, is_active)
                VALUES (:tenant_id, :user_id, :role_id, 0, 1)
            ");
            $stmtTenantUser->execute([
                ':tenant_id' => $tenantId,
                ':user_id' => $userId,
                ':role_id' => $roleId
            ]);

            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
