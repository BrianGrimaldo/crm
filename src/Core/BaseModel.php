<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los registros del tenant actual.
     */
    public function all(): array
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id ORDER BY id DESC");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Encuentra un registro por ID, asegurando que pertenezca al tenant actual.
     */
    public function find(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id LIMIT 1");
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    /**
     * Crea un nuevo registro asignado automáticamente al tenant actual.
     */
    public function create(array $data): int
    {
        $data['tenant_id'] = TenantContext::getTenantId();
        
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Actualiza un registro, asegurando que pertenezca al tenant actual.
     */
    public function update(int $id, array $data): bool
    {
        $tenantId = TenantContext::getTenantId();
        
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClauseStr = implode(', ', $setClause);

        $sql = "UPDATE {$this->table} SET {$setClauseStr} WHERE id = :id AND tenant_id = :tenant_id";
        
        $data['id'] = $id;
        $data['tenant_id'] = $tenantId;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Elimina un registro, asegurando que pertenezca al tenant actual.
     */
    public function delete(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id");
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }
}
