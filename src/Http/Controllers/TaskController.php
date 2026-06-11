<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Deal;
use App\Models\Contact;
use App\Core\Permission;
use App\Core\TenantContext;

class TaskController
{
    private Task $taskModel;

    public function __construct()
    {
        $this->taskModel = new Task();
    }

    public function index(): void
    {
        Permission::require('activities', 'view');
        
        $assignedTo = 0;
        if (\App\Core\Permission::isRestrictedToOwnRecords()) {
            $assignedTo = (int)$_SESSION['user_id'];
        }

        $tasks = $this->taskModel->getAllForTenant($assignedTo);
        require __DIR__ . '/../../Views/tasks/index.php';
    }

    public function create(): void
    {
        Permission::require('activities', 'create');
        
        $userModel = new User();
        $users = $userModel->getTenantUsers(TenantContext::getTenantId());
        
        $dealModel = new Deal();
        $deals = $dealModel->getTenantDeals();
        
        $contactModel = new Contact();
        $contacts = $contactModel->getTenantContacts();

        // Si viene preseleccionado desde un Trato o Contacto
        $relatedType = $_GET['related_type'] ?? '';
        $relatedId = (int)($_GET['related_id'] ?? 0);

        require __DIR__ . '/../../Views/tasks/create.php';
    }

    public function store(): void
    {
        Permission::require('activities', 'create');
        
        $data = [
            'title'       => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'priority'    => $_POST['priority'] ?? 'medium',
            'status'      => 'pending',
            'due_date'    => $_POST['due_date'] ?? null,
            'assigned_to' => (int)($_POST['assigned_to'] ?? $_SESSION['user_id']),
            'related_type'=> $_POST['related_type'] ?? null,
            'related_id'  => (int)($_POST['related_id'] ?? 0),
        ];

        if (empty($data['related_type']) || empty($data['related_id'])) {
            $data['related_type'] = null;
            $data['related_id'] = null;
        }

        if (empty($data['title'])) {
            $_SESSION['flash_error'] = "El título de la tarea es requerido.";
            header('Location: /crm_einsurglobal/public/tareas/create');
            exit;
        }

        if ($this->taskModel->create($data)) {
            $_SESSION['flash_success'] = "Tarea creada exitosamente.";
            header('Location: /crm_einsurglobal/public/tareas');
        } else {
            $_SESSION['flash_error'] = "Error al crear la tarea.";
            header('Location: /crm_einsurglobal/public/tareas/create');
        }
        exit;
    }

    public function edit(): void
    {
        Permission::require('activities', 'update');
        
        $id = (int)($_GET['id'] ?? 0);
        $task = $this->taskModel->findById($id);

        if (!$task) {
            $_SESSION['flash_error'] = "Tarea no encontrada.";
            header('Location: /crm_einsurglobal/public/tareas');
            exit;
        }

        $userModel = new User();
        $users = $userModel->getTenantUsers(TenantContext::getTenantId());
        
        $dealModel = new Deal();
        $deals = $dealModel->getTenantDeals();
        
        $contactModel = new Contact();
        $contacts = $contactModel->getTenantContacts();

        require __DIR__ . '/../../Views/tasks/edit.php';
    }

    public function update(): void
    {
        Permission::require('activities', 'update');
        
        $id = (int)($_POST['id'] ?? 0);
        
        $data = [
            'title'       => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'priority'    => $_POST['priority'] ?? 'medium',
            'status'      => $_POST['status'] ?? 'pending',
            'due_date'    => $_POST['due_date'] ?? null,
            'assigned_to' => (int)($_POST['assigned_to'] ?? $_SESSION['user_id']),
            'related_type'=> $_POST['related_type'] ?? null,
            'related_id'  => (int)($_POST['related_id'] ?? 0),
        ];

        if (empty($data['related_type']) || empty($data['related_id'])) {
            $data['related_type'] = null;
            $data['related_id'] = null;
        }

        if (empty($data['title'])) {
            $_SESSION['flash_error'] = "El título de la tarea es requerido.";
            header("Location: /crm_einsurglobal/public/tareas/edit?id=$id");
            exit;
        }

        if ($this->taskModel->update($id, $data)) {
            $_SESSION['flash_success'] = "Tarea actualizada exitosamente.";
            header('Location: /crm_einsurglobal/public/tareas');
        } else {
            $_SESSION['flash_error'] = "Error al actualizar la tarea.";
            header("Location: /crm_einsurglobal/public/tareas/edit?id=$id");
        }
        exit;
    }

    public function delete(): void
    {
        Permission::require('activities', 'delete');
        
        $id = (int)($_POST['id'] ?? 0);
        if ($this->taskModel->delete($id)) {
            $_SESSION['flash_success'] = "Tarea eliminada.";
        } else {
            $_SESSION['flash_error'] = "Error al eliminar la tarea.";
        }
        header('Location: /crm_einsurglobal/public/tareas');
        exit;
    }
    
    public function complete(): void
    {
        Permission::require('activities', 'update');
        
        $id = (int)($_POST['id'] ?? 0);
        $redirect = $_POST['redirect'] ?? '/crm_einsurglobal/public/tareas';
        
        if ($this->taskModel->updateStatus($id, 'completed')) {
            $_SESSION['flash_success'] = "¡Tarea marcada como completada!";
        }
        header("Location: $redirect");
        exit;
    }
}
