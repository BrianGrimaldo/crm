<?php

declare(strict_types=1);

namespace App\Modules\Sales\Services;

use App\Modules\Sales\Repositories\LeadRepository;
use App\Core\TenantContext;
use InvalidArgumentException;

/**
 * Servicio de negocio para Leads.
 *
 * Encapsula reglas de negocio, validaciones y orquesta
 * múltiples repositorios cuando es necesario.
 */
class LeadService
{
    public function __construct(
        private readonly LeadRepository $leadRepo = new LeadRepository(),
    ) {}

    /**
     * Crear un nuevo lead con validaciones de negocio.
     *
     * @throws InvalidArgumentException
     */
    public function createLead(array $data): int
    {
        // Validación básica
        if (empty($data['first_name'])) {
            throw new InvalidArgumentException('El nombre del lead es obligatorio.');
        }

        // Verificar duplicado por email dentro del tenant
        if (!empty($data['email'])) {
            $existing = $this->leadRepo->findByEmail($data['email']);
            if ($existing) {
                throw new InvalidArgumentException(
                    "Ya existe un lead con el email {$data['email']} en esta empresa."
                );
            }
        }

        // Valores por defecto
        $data['status'] = $data['status'] ?? 'new';
        $data['score']  = $data['score']  ?? 0;

        return $this->leadRepo->create($data);
    }

    /**
     * Listar leads con paginación.
     */
    public function listLeads(
        array  $filters = [],
        int    $page    = 1,
        int    $perPage = 25,
        string $sortBy  = 'created_at',
        string $sortDir = 'DESC'
    ): array {
        $offset = ($page - 1) * $perPage;
        $items  = $this->leadRepo->findAll($filters, $sortBy, $sortDir, $perPage, $offset);
        $total  = $this->leadRepo->count($filters);

        return [
            'data'         => $items,
            'total'        => $total,
            'page'         => $page,
            'per_page'     => $perPage,
            'total_pages'  => (int) ceil($total / $perPage),
        ];
    }

    /**
     * Convertir lead → Cuenta + Contacto.
     */
    public function convertLead(int $leadId): array
    {
        $lead = $this->leadRepo->findById($leadId);

        if (!$lead) {
            throw new InvalidArgumentException("Lead #{$leadId} no encontrado.");
        }

        if ($lead['status'] === 'converted') {
            throw new InvalidArgumentException("El lead ya fue convertido.");
        }

        $this->leadRepo->beginTransaction();

        try {
            // Aquí se orquestarían AccountRepository y ContactRepository
            // para crear la cuenta y contacto a partir del lead.

            $this->leadRepo->markAsConverted($leadId);

            $this->leadRepo->commit();

            return [
                'lead_id' => $leadId,
                'status'  => 'converted',
            ];
        } catch (\Throwable $e) {
            $this->leadRepo->rollback();
            throw $e;
        }
    }
}
