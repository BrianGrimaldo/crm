<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;
use App\Core\TenantContext;
use PDO;

class Role extends BaseModel
{
    protected string $table = 'roles';

    /**
     * Obtiene todos los roles del tenant actual.
     */
    public function getTenantRoles(): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT r.*, 
                       (SELECT COUNT(*) FROM tenant_users tu WHERE tu.role_id = r.id) as user_count
                FROM {$this->table} r
                WHERE r.tenant_id = :tenant_id
                ORDER BY r.is_system DESC, r.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Encuentra un rol por ID asegurando que pertenece al tenant.
     */
    public function findTenantRole(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        return $result ?: null;
    }

    /**
     * Crea un nuevo rol para el tenant actual.
     */
    public function createRole(array $data): int
    {
        $tenantId = TenantContext::getTenantId();
        $data['tenant_id'] = $tenantId;

        $fields = implode(', ', array_map(fn($k) => "`$k`", array_keys($data)));
        $placeholders = implode(', ', array_map(fn($k) => ":$k", array_keys($data)));

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un rol existente (solo nombre y descripción).
     */
    public function updateRole(int $id, array $data): bool
    {
        $tenantId = TenantContext::getTenantId();

        $fields = [];
        $params = [':id' => $id, ':tenant_id' => $tenantId];

        foreach ($data as $key => $value) {
            $fields[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id AND tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Elimina un rol (solo si no es de sistema).
     */
    public function deleteRole(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "DELETE FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id AND is_system = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Obtiene todos los permisos globales disponibles, agrupados por módulo.
     */
    public function getAllPermissions(): array
    {
        $sql = "SELECT * FROM permissions ORDER BY module ASC, action ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $all = $stmt->fetchAll(PDO::FETCH_OBJ);

        $grouped = [];
        foreach ($all as $perm) {
            $grouped[$perm->module][] = $perm;
        }

        return $grouped;
    }

    /**
     * Obtiene los IDs de permisos asignados a un rol.
     */
    public function getRolePermissionIds(int $roleId): array
    {
        $sql = "SELECT permission_id FROM role_permissions WHERE role_id = :role_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':role_id' => $roleId]);

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
    }

    /**
     * Sincroniza los permisos de un rol (elimina todos y reinserta).
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        // Eliminar permisos actuales
        $stmt = $this->db->prepare("DELETE FROM role_permissions WHERE role_id = :role_id");
        $stmt->execute([':role_id' => $roleId]);

        // Insertar los nuevos
        if (!empty($permissionIds)) {
            $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES ";
            $values = [];
            $params = [];
            foreach ($permissionIds as $i => $permId) {
                $values[] = "(:role_id_{$i}, :perm_id_{$i})";
                $params[":role_id_{$i}"] = $roleId;
                $params[":perm_id_{$i}"] = (int)$permId;
            }
            $sql .= implode(', ', $values);
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        }
    }
}
