<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Deal;

class ActivityController
{
    private Activity $activityModel;

    public function __construct()
    {
        $this->activityModel = new Activity();
    }

    /**
     * Guarda una nueva actividad / intervención.
     */
    public function store(): void
    {
        // En este punto, el usuario ya pasó por el middleware. 
        // Idealmente podríamos verificar permisos si es necesario.
        $entityType = $_POST['entity_type'] ?? '';
        $entityId = (int)($_POST['entity_id'] ?? 0);
        $type = $_POST['type'] ?? '';
        $description = trim($_POST['description'] ?? '');

        if (!$entityType || !$entityId || !$type || empty($description)) {
            $_SESSION['flash_error'] = "Todos los campos de la actividad son obligatorios.";
            $this->redirectBack($entityType, $entityId);
            exit;
        }

        if ($entityType === 'deal') {
            $dealModel = new Deal();
            $deal = $dealModel->findWithRelations($entityId);
            if ($deal && !empty($deal->stage_name)) {
                $description = "[Etapa: " . $deal->stage_name . "] " . $description;
            }
        }

        $this->activityModel->log($entityType, $entityId, $type, $description);

        // Nota: La probabilidad ahora se asigna automáticamente desde la etapa del pipeline.
        // Ya no se incrementa por actividades individuales.

        $_SESSION['flash_success'] = "Intervención guardada exitosamente.";
        if (!empty($_POST['redirect_to'])) {
            header('Location: ' . $_POST['redirect_to']);
        } else {
            $this->redirectBack($entityType, $entityId);
        }
        exit;
    }

    /**
     * Incrementa la probabilidad del deal en base a la intervención.
     */
    private function increaseDealProbability(int $dealId, string $activityType): void
    {
        $dealModel = new Deal();
        $deal = $dealModel->findWithRelations($dealId);
        if (!$deal) return;

        $currentProb = (int)$deal->probability;
        $increase = match ($activityType) {
            'Llamada' => 5,
            'Correo' => 5,
            'WhatsApp' => 5,
            'Visita' => 15,
            'Nota' => 2,
            default => 0
        };

        $newProb = min(100, $currentProb + $increase);

        if ($newProb > $currentProb) {
            $dealModel->update($dealId, ['probability' => $newProb]);
            
            // También registramos esto en el AuditLog
            $auditLog = new \App\Models\AuditLog();
            $auditLog->log('update_probability', 'deal', $dealId, ['probability' => $currentProb], ['probability' => $newProb]);
        }
    }

    /**
     * Redirige al usuario de vuelta a la página de edición de la entidad.
     */
    private function redirectBack(string $entityType, int $entityId): void
    {
        $url = match ($entityType) {
            'deal' => url("/oportunidades/edit?id={$entityId}"),
            'contact' => url("/contactos/edit?id={$entityId}"),
            'account' => url("/organizaciones/edit?id={$entityId}"),
            default => url('/dashboard')
        };

        header("Location: {$url}");
    }
}
