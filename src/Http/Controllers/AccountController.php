<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Activity;
use App\Core\Permission;

class AccountController
{
    private Account $accountModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->accountModel = new Account();
        $this->auditLog = new AuditLog();
    }

    public function index(): void
    {
        \App\Core\Permission::require('accounts', 'view');
        $keyword = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $accounts = $this->accountModel->search($keyword, $type);

        require __DIR__ . '/../../Views/accounts/index.php';
    }

    public function create(): void
    {
        \App\Core\Permission::require('accounts', 'create');
        require __DIR__ . '/../../Views/accounts/create.php';
    }

    public function store(): void
    {
        \App\Core\Permission::require('accounts', 'create');
        $data = [
            'name'       => $_POST['name'] ?? '',
            'type'       => $_POST['type'] ?? 'customer',
            'priority'   => $_POST['priority'] ?? 'B',
            'website'    => $_POST['website'] ?? '',
            'linkedin'   => $_POST['linkedin'] ?? '',
            'phone'      => $_POST['phone'] ?? '',
            'country'    => $_POST['country'] ?? '',
            'city'       => $_POST['city'] ?? '',
            'postal_code'=> $_POST['postal_code'] ?? '',
            'billing_address' => $_POST['billing_address'] ?? '',
            'notes'      => $_POST['notes'] ?? '',
            'owner_id'   => $_SESSION['user_id'] ?? null,
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre de la organización es obligatorio.";
            header('Location: /crm_einsurglobal/public/accounts/create');
            exit;
        }

        $accountId = $this->accountModel->create($data);
        $this->auditLog->log('create', 'account', $accountId, null, $data);

        $_SESSION['flash_success'] = "Organización creada exitosamente.";
        header('Location: /crm_einsurglobal/public/accounts');
        exit;
    }

    public function edit(): void
    {
        \App\Core\Permission::require('accounts', 'update');
        $id = (int)($_GET['id'] ?? 0);
        $account = $this->accountModel->find($id);

        if (!$account) {
            $_SESSION['flash_error'] = "Organización no encontrada.";
            header('Location: /crm_einsurglobal/public/accounts');
            exit;
        }

        // Fetch activities
        $activityModel = new Activity();
        $activities = $activityModel->getForEntity('account', $id);

        require __DIR__ . '/../../Views/accounts/edit.php';
    }

    public function update(): void
    {
        \App\Core\Permission::require('accounts', 'update');
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name'       => $_POST['name'] ?? '',
            'type'       => $_POST['type'] ?? 'customer',
            'priority'   => $_POST['priority'] ?? 'B',
            'website'    => $_POST['website'] ?? '',
            'linkedin'   => $_POST['linkedin'] ?? '',
            'phone'      => $_POST['phone'] ?? '',
            'country'    => $_POST['country'] ?? '',
            'city'       => $_POST['city'] ?? '',
            'postal_code'=> $_POST['postal_code'] ?? '',
            'billing_address' => $_POST['billing_address'] ?? '',
            'notes'      => $_POST['notes'] ?? '',
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header("Location: /crm_einsurglobal/public/accounts/edit?id={$id}");
            exit;
        }

        $oldAccount = $this->accountModel->find($id);
        $success = $this->accountModel->update($id, $data);

        if ($success) {
            $this->auditLog->log('update', 'account', $id, (array)$oldAccount, $data);
            $_SESSION['flash_success'] = "Organización actualizada exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar la organización.";
        }

        header('Location: /crm_einsurglobal/public/accounts');
        exit;
    }

    public function delete(): void
    {
        \App\Core\Permission::require('accounts', 'delete');
        $id = (int)($_POST['id'] ?? 0);
        $oldAccount = $this->accountModel->find($id);
        $success = $this->accountModel->delete($id);

        if ($success) {
            $this->auditLog->log('delete', 'account', $id, (array)$oldAccount, null);
            $_SESSION['flash_success'] = "Organización eliminada.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar la organización.";
        }

        header('Location: /crm_einsurglobal/public/accounts');
        exit;
    }
}
