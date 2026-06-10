<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Repositorio base con aislamiento automático por tenant_id.
 *
 * Todas las operaciones CRUD inyectan `tenant_id` de forma
 * transparente, eliminando el riesgo de fuga de datos entre
 * empresas (tenants).
 *
 * Cada módulo extiende esta clase indicando la tabla:
 *
 *   class LeadRepository extends BaseRepository {
 *       protected string $table = 'leads';
 *   }
 */
abstract class BaseRepository
{
    protected string $table;
    protected PDO    $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /* ────────────────────────────────────────────────
     *  READ
     * ──────────────────────────────────────────────── */

    /**
     * Obtener un registro por ID (con filtro tenant).
     */
    public function findById(int $id): ?array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `id` = :id AND `tenant_id` = :tid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'  => $id,
            ':tid' => TenantContext::getTenantId(),
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtener todos los registros del tenant con paginación.
     *
     * @param array  $where   Filtros adicionales ['column' => value]
     * @param string $orderBy Columna de orden
     * @param string $dir     ASC | DESC
     * @param int    $limit   Registros por página
     * @param int    $offset  Desplazamiento
     */
    public function findAll(
        array  $where   = [],
        string $orderBy = 'id',
        string $dir     = 'DESC',
        int    $limit   = 25,
        int    $offset  = 0
    ): array {
        $dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
        $params = [':tid' => TenantContext::getTenantId()];
        $clauses = ['`tenant_id` = :tid'];

        foreach ($where as $col => $val) {
            $placeholder = ':w_' . $col;
            $clauses[]   = "`{$col}` = {$placeholder}";
            $params[$placeholder] = $val;
        }

        $sql = sprintf(
            "SELECT * FROM `%s` WHERE %s ORDER BY `%s` %s LIMIT %d OFFSET %d",
            $this->table,
            implode(' AND ', $clauses),
            $orderBy,
            $dir,
            $limit,
            $offset
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Contar registros del tenant (con filtros opcionales).
     */
    public function count(array $where = []): int
    {
        $params  = [':tid' => TenantContext::getTenantId()];
        $clauses = ['`tenant_id` = :tid'];

        foreach ($where as $col => $val) {
            $placeholder = ':w_' . $col;
            $clauses[]   = "`{$col}` = {$placeholder}";
            $params[$placeholder] = $val;
        }

        $sql = sprintf(
            "SELECT COUNT(*) FROM `%s` WHERE %s",
            $this->table,
            implode(' AND ', $clauses)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /* ────────────────────────────────────────────────
     *  CREATE
     * ──────────────────────────────────────────────── */

    /**
     * Insertar un registro. tenant_id se inyecta automáticamente.
     *
     * @return int ID del registro insertado.
     */
    public function create(array $data): int
    {
        // Forzar tenant_id
        $data['tenant_id'] = TenantContext::getTenantId();

        $columns      = array_keys($data);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', $placeholders)
        );

        $stmt = $this->db->prepare($sql);

        foreach ($data as $col => $val) {
            $stmt->bindValue(":{$col}", $val);
        }

        $stmt->execute();
        return (int) $this->db->lastInsertId();
    }

    /* ────────────────────────────────────────────────
     *  UPDATE
     * ──────────────────────────────────────────────── */

    /**
     * Actualizar un registro por ID (filtrado por tenant).
     */
    public function update(int $id, array $data): bool
    {
        // Nunca permitir cambiar el tenant_id
        unset($data['tenant_id'], $data['id']);

        $sets   = [];
        $params = [
            ':id'  => $id,
            ':tid' => TenantContext::getTenantId(),
        ];

        foreach ($data as $col => $val) {
            $placeholder    = ":u_{$col}";
            $sets[]         = "`{$col}` = {$placeholder}";
            $params[$placeholder] = $val;
        }

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `id` = :id AND `tenant_id` = :tid",
            $this->table,
            implode(', ', $sets)
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /* ────────────────────────────────────────────────
     *  DELETE
     * ──────────────────────────────────────────────── */

    /**
     * Eliminar un registro por ID (filtrado por tenant).
     */
    public function delete(int $id): bool
    {
        $sql  = "DELETE FROM `{$this->table}` WHERE `id` = :id AND `tenant_id` = :tid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'  => $id,
            ':tid' => TenantContext::getTenantId(),
        ]);

        return $stmt->rowCount() > 0;
    }

    /* ────────────────────────────────────────────────
     *  HELPERS
     * ──────────────────────────────────────────────── */

    /**
     * Ejecutar una consulta arbitraria con filtro tenant
     * ya incluido en el SQL.
     */
    protected function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Iniciar transacción.
     */
    public function beginTransaction(): void
    {
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    public function rollback(): void
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }
}
