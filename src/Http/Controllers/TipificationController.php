<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tipification;
use App\Models\AuditLog;
use App\Core\Permission;

class TipificationController
{
    private Tipification $model;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->model = new Tipification();
        $this->auditLog = new AuditLog();
    }

    /**
     * Lista de tipificaciones (vista de configuración).
     */
    public function index(): void
    {
        Permission::require('settings', 'view');

        $tipifications = $this->model->all(false); // incluir inactivas
        $stats = $this->model->getStats();
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail  = $_SESSION['user_email'] ?? 'Usuario';

        require __DIR__ . '/../../Views/settings/tipifications.php';
    }

    /**
     * Guarda una nueva tipificación (AJAX / form).
     */
    public function store(): void
    {
        Permission::require('settings', 'view');

        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            $_SESSION['flash_error'] = "El nombre de la tipificación es obligatorio.";
            header('Location: ' . url('/configuracion/tipificaciones'));
            exit;
        }

        $id = $this->model->create([
            'name'        => $name,
            'color'       => $_POST['color'] ?? '#6366f1',
            'icon'        => $_POST['icon'] ?? 'fa-tag',
            'description' => trim($_POST['description'] ?? ''),
            'auto_action' => $_POST['auto_action'] ?? 'none',
            'position'    => (int) ($_POST['position'] ?? 0),
        ]);

        $this->auditLog->log('create', 'tipification', $id, null, $_POST);

        $_SESSION['flash_success'] = "Tipificación «{$name}» creada exitosamente.";
        header('Location: ' . url('/configuracion/tipificaciones'));
        exit;
    }

    /**
     * Actualiza una tipificación existente.
     */
    public function update(): void
    {
        Permission::require('settings', 'view');

        $id = (int) ($_POST['id'] ?? 0);
        $old = $this->model->find($id);

        if (!$old) {
            $_SESSION['flash_error'] = "Tipificación no encontrada.";
            header('Location: ' . url('/configuracion/tipificaciones'));
            exit;
        }

        $data = [
            'name'        => trim($_POST['name'] ?? $old->name),
            'color'       => $_POST['color'] ?? $old->color,
            'icon'        => $_POST['icon'] ?? $old->icon,
            'description' => trim($_POST['description'] ?? ''),
            'auto_action' => $_POST['auto_action'] ?? $old->auto_action,
            'is_active'   => isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1,
        ];

        $this->model->update($id, $data);
        $this->auditLog->log('update', 'tipification', $id, (array) $old, $data);

        $_SESSION['flash_success'] = "Tipificación actualizada exitosamente.";
        header('Location: ' . url('/configuracion/tipificaciones'));
        exit;
    }

    /**
     * Desactiva (soft-delete) una tipificación.
     */
    public function delete(): void
    {
        Permission::require('settings', 'view');

        $id = (int) ($_POST['id'] ?? 0);
        $this->model->delete($id);
        $this->auditLog->log('delete', 'tipification', $id, null, null);

        $_SESSION['flash_success'] = "Tipificación desactivada.";
        header('Location: ' . url('/configuracion/tipificaciones'));
        exit;
    }

    /**
     * Devuelve tipificaciones en JSON (para AJAX selects en tickets).
     */
    public function listJson(): void
    {
        header('Content-Type: application/json');
        Permission::require('tickets', 'view');

        echo json_encode($this->model->all());
    }
}
