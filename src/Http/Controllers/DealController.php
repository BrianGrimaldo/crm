<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\Contact;
use App\Models\AuditLog;
use App\Models\Activity;

class DealController
{
    private Deal $dealModel;
    private PipelineStage $stageModel;
    private Contact $contactModel;
    private \App\Models\Account $accountModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->dealModel = new Deal();
        $this->stageModel = new PipelineStage();
        $this->contactModel = new Contact();
        $this->accountModel = new \App\Models\Account();
        $this->auditLog = new AuditLog();
    }

    /**
     * Vista Kanban (tablero visual del pipeline).
     */
    public function pipeline(): void
    {
        \App\Core\Permission::require('deals', 'view');
        $stages = $this->stageModel->allOrdered();
        $deals = $this->dealModel->allGroupedByStage();
        $summary = $this->dealModel->summaryByStage();

        // Agrupar deals por stage_id
        $dealsByStage = [];
        foreach ($deals as $deal) {
            $dealsByStage[$deal->stage_id][] = $deal;
        }

        require __DIR__ . '/../../Views/deals/pipeline.php';
    }

    /**
     * Vista de lista de oportunidades.
     */
    public function index(): void
    {
        \App\Core\Permission::require('deals', 'view');
        $keyword = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $deals = $this->dealModel->allWithRelations($keyword, $status);
        $summary = $this->dealModel->summaryByStage();

        require __DIR__ . '/../../Views/deals/index.php';
    }

    /**
     * Formulario de creación de oportunidad.
     */
    public function create(): void
    {
        \App\Core\Permission::require('deals', 'create');
        $stages = $this->stageModel->allOrdered();
        $contacts = $this->contactModel->all();
        $accounts = $this->accountModel->all();

        require __DIR__ . '/../../Views/deals/create.php';
    }

    /**
     * Guardar nueva oportunidad.
     */
    public function store(): void
    {
        \App\Core\Permission::require('deals', 'create');
        $data = [
            'name'                => $_POST['name'] ?? '',
            'contact_id'          => !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null,
            'account_id'          => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
            'stage_id'            => (int)($_POST['stage_id'] ?? 0),
            'status'              => $_POST['status'] ?? 'Abierto',
            'amount'              => !empty($_POST['amount']) ? (float)$_POST['amount'] : null,
            'currency_code'       => $_POST['currency_code'] ?? 'MXN',
            'probability'         => !empty($_POST['probability']) ? (int)$_POST['probability'] : null,
            'expected_close_date' => !empty($_POST['expected_close_date']) ? $_POST['expected_close_date'] : null,
            'source'              => $_POST['source'] ?? null,
            'description'         => $_POST['description'] ?? null,
            'owner_id'            => $_SESSION['user_id'] ?? null,
        ];

        if (empty($data['name']) || empty($data['stage_id'])) {
            $_SESSION['flash_error'] = "El nombre y la etapa son obligatorios.";
            header('Location: /crm_einsurglobal/public/deals/create');
            exit;
        }

        $dealId = $this->dealModel->create($data);
        $this->auditLog->log('create', 'deal', $dealId, null, $data);

        $_SESSION['flash_success'] = "Oportunidad creada exitosamente.";
        header('Location: /crm_einsurglobal/public/deals/pipeline');
        exit;
    }

    /**
     * Formulario de edición.
     */
    public function edit(): void
    {
        \App\Core\Permission::require('deals', 'update');
        $id = (int)($_GET['id'] ?? 0);
        $deal = $this->dealModel->findWithRelations($id);

        if (!$deal) {
            $_SESSION['flash_error'] = "Oportunidad no encontrada.";
            header('Location: /crm_einsurglobal/public/deals');
            exit;
        }

        $stages = $this->stageModel->allOrdered();
        $contacts = $this->contactModel->all();
        $accounts = $this->accountModel->all();

        // Fetch activities
        $activityModel = new Activity();
        $activities = $activityModel->getForEntity('deal', $id);

        require __DIR__ . '/../../Views/deals/edit.php';
    }

    /**
     * Actualizar oportunidad.
     */
    public function update(): void
    {
        \App\Core\Permission::require('deals', 'update');
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name'                => $_POST['name'] ?? '',
            'contact_id'          => !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null,
            'account_id'          => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
            'stage_id'            => (int)($_POST['stage_id'] ?? 0),
            'status'              => $_POST['status'] ?? 'Abierto',
            'amount'              => !empty($_POST['amount']) ? (float)$_POST['amount'] : null,
            'currency_code'       => $_POST['currency_code'] ?? 'MXN',
            'probability'         => !empty($_POST['probability']) ? (int)$_POST['probability'] : null,
            'expected_close_date' => !empty($_POST['expected_close_date']) ? $_POST['expected_close_date'] : null,
            'source'              => $_POST['source'] ?? null,
            'description'         => $_POST['description'] ?? null,
        ];

        if (empty($data['name']) || empty($data['stage_id'])) {
            $_SESSION['flash_error'] = "El nombre y la etapa son obligatorios.";
            header("Location: /crm_einsurglobal/public/deals/edit?id={$id}");
            exit;
        }

        $oldDeal = $this->dealModel->findWithRelations($id);
        $this->dealModel->update($id, $data);
        
        $this->auditLog->log('update', 'deal', $id, (array)$oldDeal, $data);

        $_SESSION['flash_success'] = "Oportunidad actualizada exitosamente.";
        header('Location: /crm_einsurglobal/public/deals/pipeline');
        exit;
    }

    /**
     * Eliminar oportunidad.
     */
    public function delete(): void
    {
        \App\Core\Permission::require('deals', 'delete');
        $id = (int)($_POST['id'] ?? 0);
        $oldDeal = $this->dealModel->findWithRelations($id);
        $success = $this->dealModel->delete($id);

        if ($success) {
            $this->auditLog->log('delete', 'deal', $id, (array)$oldDeal, null);
            $_SESSION['flash_success'] = "Oportunidad eliminada.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar la oportunidad.";
        }

        header('Location: /crm_einsurglobal/public/deals');
        exit;
    }

    /**
     * Mover deal a otra etapa (API para Kanban drag & drop).
     */
    public function moveStage(): void
    {
        \App\Core\Permission::require('deals', 'update');
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int)($input['deal_id'] ?? 0);
        $newStageId = (int)($input['stage_id'] ?? 0);

        if (!$id || !$newStageId) {
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
            return;
        }

        $success = $this->dealModel->update($id, ['stage_id' => $newStageId]);

        if ($success) {
            $this->auditLog->log('update_stage', 'deal', $id, null, ['stage_id' => $newStageId]);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se pudo mover.']);
        }
    }
}
