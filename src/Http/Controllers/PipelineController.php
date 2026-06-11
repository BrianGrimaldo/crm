<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PipelineStage;
use App\Core\Permission;
use App\Core\TenantContext;

class PipelineController
{
    private PipelineStage $pipelineModel;

    public function __construct()
    {
        $this->pipelineModel = new PipelineStage();
    }

    public function index(): void
    {
        Permission::require('settings', 'view');
        $stages = $this->pipelineModel->allOrdered();
        require __DIR__ . '/../../Views/settings/pipeline/index.php';
    }

    public function create(): void
    {
        Permission::require('settings', 'update');
        require __DIR__ . '/../../Views/settings/pipeline/create.php';
    }

    public function store(): void
    {
        Permission::require('settings', 'update');
        
        $data = [
            'tenant_id'   => TenantContext::getTenantId(),
            'name'        => $_POST['name'] ?? '',
            'position'    => (int)($_POST['position'] ?? 0),
            'probability' => (int)($_POST['probability'] ?? 0),
            'is_won'      => isset($_POST['is_won']) ? 1 : 0,
            'is_lost'     => isset($_POST['is_lost']) ? 1 : 0,
            'color'       => $_POST['color'] ?? '#94A3B8'
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre de la etapa es requerido.";
            header('Location: /crm_einsurglobal/public/configuracion/embudo/create');
            exit;
        }

        // Si la posiciÃ³n es 0, calcular la mÃ¡xima + 1
        if ($data['position'] === 0) {
            $stages = $this->pipelineModel->allOrdered();
            $maxPos = 0;
            foreach ($stages as $s) {
                if ($s->position > $maxPos) {
                    $maxPos = $s->position;
                }
            }
            $data['position'] = $maxPos + 1;
        }

        if ($this->pipelineModel->create($data)) {
            $_SESSION['flash_success'] = "Etapa creada exitosamente.";
            header('Location: /crm_einsurglobal/public/configuracion/embudo');
        } else {
            $_SESSION['flash_error'] = "Error al crear la etapa.";
            header('Location: /crm_einsurglobal/public/configuracion/embudo/create');
        }
        exit;
    }

    public function edit(): void
    {
        Permission::require('settings', 'update');
        
        $id = (int)($_GET['id'] ?? 0);
        $stage = $this->pipelineModel->findById($id);

        if (!$stage) {
            $_SESSION['flash_error'] = "Etapa no encontrada.";
            header('Location: /crm_einsurglobal/public/configuracion/embudo');
            exit;
        }

        require __DIR__ . '/../../Views/settings/pipeline/edit.php';
    }

    public function update(): void
    {
        Permission::require('settings', 'update');
        
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name'        => $_POST['name'] ?? '',
            'position'    => (int)($_POST['position'] ?? 0),
            'probability' => (int)($_POST['probability'] ?? 0),
            'is_won'      => isset($_POST['is_won']) ? 1 : 0,
            'is_lost'     => isset($_POST['is_lost']) ? 1 : 0,
            'color'       => $_POST['color'] ?? '#94A3B8'
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre de la etapa es requerido.";
            header("Location: /crm_einsurglobal/public/configuracion/embudo/edit?id=$id");
            exit;
        }

        if ($this->pipelineModel->update($id, $data)) {
            $_SESSION['flash_success'] = "Etapa actualizada exitosamente.";
            header('Location: /crm_einsurglobal/public/configuracion/embudo');
        } else {
            $_SESSION['flash_error'] = "Error al actualizar la etapa.";
            header("Location: /crm_einsurglobal/public/configuracion/embudo/edit?id=$id");
        }
        exit;
    }

    public function delete(): void
    {
        Permission::require('settings', 'update');
        
        $id = (int)($_POST['id'] ?? 0);
        if ($this->pipelineModel->delete($id)) {
            $_SESSION['flash_success'] = "Etapa eliminada.";
        } else {
            $_SESSION['flash_error'] = "Error al eliminar la etapa. Es posible que tenga tratos asociados.";
        }
        header('Location: /crm_einsurglobal/public/configuracion/embudo');
        exit;
    }
}
