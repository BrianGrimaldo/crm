<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Activity;
use App\Core\TenantContext;
use App\Core\Permission;

class ContactController
{
    private Contact $contactModel;
    private Account $accountModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->contactModel = new Contact();
        $this->accountModel = new Account();
        $this->auditLog = new AuditLog();
    }

    /**
     * Muestra la lista de contactos.
     */
    public function index(): void
    {
        Permission::require('contacts', 'view');
        $keyword = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $contacts = $this->contactModel->search($keyword, $type);

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        require __DIR__ . '/../../Views/contacts/index.php';
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create(): void
    {
        Permission::require('contacts', 'create');
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        // Cargar cuentas para el select
        $accounts = $this->accountModel->all();

        require __DIR__ . '/../../Views/contacts/create.php';
    }

    /**
     * Guarda un nuevo contacto.
     */
    public function store(): void
    {
        Permission::require('contacts', 'create');
        $data = [
            'first_name'  => $_POST['first_name'] ?? '',
            'last_name'   => $_POST['last_name'] ?? '',
            'type'        => $_POST['type'] ?? 'Prospecto',
            'email'       => $_POST['email'] ?? '',
            'phone'       => $_POST['phone'] ?? '',
            'linkedin'    => $_POST['linkedin'] ?? '',
            'job_title'   => $_POST['job_title'] ?? '',
            'department'  => $_POST['department'] ?? '',
            'country'     => $_POST['country'] ?? '',
            'city'        => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'owner_id'    => $_SESSION['user_id'] ?? null,
            'account_id'  => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
        ];

        if (empty($data['first_name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header('Location: /crm_einsurglobal/public/contacts/create');
            exit;
        }

        $contactId = $this->contactModel->create($data);
        $this->auditLog->log('create', 'contact', $contactId, null, $data);

        $_SESSION['flash_success'] = "Contacto creado exitosamente.";
        header('Location: /crm_einsurglobal/public/contacts');
        exit;
    }

    /**
     * Muestra el formulario para editar un contacto existente.
     */
    public function edit(): void
    {
        Permission::require('contacts', 'update');
        $id = (int)($_GET['id'] ?? 0);
        $contact = $this->contactModel->find($id);

        if (!$contact) {
            $_SESSION['flash_error'] = "Contacto no encontrado.";
            header('Location: /crm_einsurglobal/public/contacts');
            exit;
        }

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';

        // Cargar cuentas para el select
        $accounts = $this->accountModel->all();

        // Fetch activities
        $activityModel = new Activity();
        $activities = $activityModel->getForEntity('contact', $id);

        require __DIR__ . '/../../Views/contacts/edit.php';
    }

    /**
     * Actualiza un contacto en la base de datos.
     */
    public function update(): void
    {
        Permission::require('contacts', 'update');
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'first_name'  => $_POST['first_name'] ?? '',
            'last_name'   => $_POST['last_name'] ?? '',
            'type'        => $_POST['type'] ?? 'Prospecto',
            'email'       => $_POST['email'] ?? '',
            'phone'       => $_POST['phone'] ?? '',
            'linkedin'    => $_POST['linkedin'] ?? '',
            'job_title'   => $_POST['job_title'] ?? '',
            'department'  => $_POST['department'] ?? '',
            'country'     => $_POST['country'] ?? '',
            'city'        => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'account_id'  => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
        ];

        if (empty($data['first_name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header("Location: /crm_einsurglobal/public/contacts/edit?id={$id}");
            exit;
        }

        $oldContact = $this->contactModel->find($id);
        $success = $this->contactModel->update($id, $data);

        if ($success) {
            $this->auditLog->log('update', 'contact', $id, (array)$oldContact, $data);
            $_SESSION['flash_success'] = "Contacto actualizado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar el contacto.";
        }

        header('Location: /crm_einsurglobal/public/contacts');
        exit;
    }

    /**
     * Elimina un contacto de la base de datos.
     */
    public function delete(): void
    {
        Permission::require('contacts', 'delete');
        $id = (int)($_POST['id'] ?? 0);
        $oldContact = $this->contactModel->find($id);
        $success = $this->contactModel->delete($id);

        if ($success) {
            $this->auditLog->log('delete', 'contact', $id, (array)$oldContact, null);
            $_SESSION['flash_success'] = "Contacto eliminado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar el contacto.";
        }

        header('Location: /crm_einsurglobal/public/contacts');
        exit;
    }
}
