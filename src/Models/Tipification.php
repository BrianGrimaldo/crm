<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\TenantContext;
use PDO;

class Tipification
{
    private PDO $db;
    private string $table = 'tipifications';

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Devuelve todas las tipificaciones activas del tenant.
     */
    public function all(bool $onlyActive = true): array
    {
        $tenantId = TenantContext::getTenantId();

        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = :tenant_id";
        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }
        $sql .= " ORDER BY position ASC, name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Busca una tipificación por ID.
     */
    public function find(int $id): ?object
    {
        $tenantId = TenantContext::getTenantId();

        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->table} WHERE id = :id AND tenant_id = :tenant_id LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);

        return $result ?: null;
    }

    /**
     * Crea una nueva tipificación.
     */
    public function create(array $data): int
    {
        $tenantId = TenantContext::getTenantId();

        $slug = $this->generateSlug($data['name']);

        $stmt = $this->db->prepare("
            INSERT INTO {$this->table}
                (tenant_id, name, slug, color, icon, description, auto_action, position, is_active)
            VALUES
                (:tenant_id, :name, :slug, :color, :icon, :description, :auto_action, :position, :is_active)
        ");
        $stmt->execute([
            ':tenant_id'   => $tenantId,
            ':name'        => $data['name'],
            ':slug'        => $slug,
            ':color'       => $data['color'] ?? '#6366f1',
            ':icon'        => $data['icon'] ?? 'fa-tag',
            ':description' => $data['description'] ?? null,
            ':auto_action' => $data['auto_action'] ?? 'none',
            ':position'    => $data['position'] ?? 0,
            ':is_active'   => $data['is_active'] ?? 1,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza una tipificación.
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

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields)
             . " WHERE id = :id AND tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Elimina una tipificación (soft: desactiva).
     */
    public function delete(int $id): bool
    {
        $tenantId = TenantContext::getTenantId();

        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET is_active = 0 WHERE id = :id AND tenant_id = :tenant_id"
        );
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId]);
    }

    /**
     * Estadísticas: cuántos tickets tiene cada tipificación (para reportes).
     */
    public function getStats(): array
    {
        $tenantId = TenantContext::getTenantId();

        $stmt = $this->db->prepare("
            SELECT tp.id, tp.name, tp.color, tp.icon,
                   COUNT(t.id) AS ticket_count
            FROM {$this->table} tp
            LEFT JOIN tickets t ON t.tipification_id = tp.id AND t.tenant_id = tp.tenant_id
            WHERE tp.tenant_id = :tenant_id AND tp.is_active = 1
            GROUP BY tp.id, tp.name, tp.color, tp.icon
            ORDER BY tp.position ASC
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Genera un slug único a partir del nombre.
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return $slug ?: 'tipificacion';
    }
}
