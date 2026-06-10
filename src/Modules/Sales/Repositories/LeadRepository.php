<?php

declare(strict_types=1);

namespace App\Modules\Sales\Repositories;

use App\Core\BaseRepository;
use App\Core\TenantContext;

/**
 * Repositorio de Leads.
 *
 * Hereda TODAS las operaciones CRUD con aislamiento
 * automático por tenant_id de BaseRepository.
 *
 * Ejemplo de uso:
 *   $repo = new LeadRepository();
 *   $lead = $repo->findById(42);          // filtra por tenant
 *   $all  = $repo->findAll(['status' => 'new']); // filtra por tenant
 *   $id   = $repo->create(['first_name' => 'Juan', ...]);
 */
class LeadRepository extends BaseRepository
{
    protected string $table = 'leads';

    /**
     * Buscar leads por email (siempre dentro del tenant).
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->query(
            "SELECT * FROM `{$this->table}`
             WHERE `tenant_id` = :tid AND `email` = :email
             LIMIT 1",
            [
                ':tid'   => TenantContext::getTenantId(),
                ':email' => $email,
            ]
        );

        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Leads agrupados por fuente (para gráficas).
     */
    public function countBySource(): array
    {
        $stmt = $this->query(
            "SELECT `source`, COUNT(*) AS `total`
             FROM `{$this->table}`
             WHERE `tenant_id` = :tid
             GROUP BY `source`
             ORDER BY `total` DESC",
            [':tid' => TenantContext::getTenantId()]
        );

        return $stmt->fetchAll();
    }

    /**
     * Convertir un lead a cuenta + contacto (dentro de transacción).
     */
    public function markAsConverted(int $leadId): bool
    {
        return $this->update($leadId, [
            'status'       => 'converted',
            'converted_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
