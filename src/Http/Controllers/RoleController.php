<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Role;
use App\Core\Permission;

class RoleController
{
    private Role $roleModel;

    public function __construct()
    {
        $this->roleModel = new Role();
    }

    /**
     * Lista todos los roles del tenant.
     */
    public function index(): void
    {
        Permission::require('settings', 'view');
        $roles = $this->roleModel->getTenantRoles();

        require __DIR__ . '/../../Views/roles/index.php';
    }

    /**
     * Formulario para crear un nuevo rol.
     */
    public function create(): void
    {
        Permission::require('settings', 'update');
        $allPermissions = $this->roleModel->getAllPermissions();

        require __DIR__ . '/../../Views/roles/create.php';
    }

    /**
     * Guarda un nuevo rol con permisos.
     */
    public function store(): void
    {
        Permission::require('settings', 'update');

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));

        if (empty($name)) {
            $_SESSION['flash_error'] = "El nombre del rol es obligatorio.";
            header('Location: /roles/create');
            exit;
        }

        $roleId = $this->roleModel->createRole([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'is_system' => 0,
        ]);

        // Sincronizar permisos seleccionados
        $permissionIds = $_POST['permissions'] ?? [];
        if (!empty($permissionIds)) {
            $this->roleModel->syncPermissions($roleId, array_map('intval', $permissionIds));
        }

        $_SESSION['flash_success'] = "Rol '{$name}' creado exitosamente.";
        header('Location: /roles');
        exit;
    }

    /**
     * Formulario para editar rol y su matriz de permisos.
     */
    public function edit(): void
    {
        Permission::require('settings', 'update');

        $id = (int)($_GET['id'] ?? 0);
        $roleData = $this->roleModel->findTenantRole($id);

        if (!$roleData) {
            $_SESSION['flash_error'] = "Rol no encontrado.";
            header('Location: /roles');
            exit;
        }

        $allPermissions = $this->roleModel->getAllPermissions();
        $rolePermissionIds = $this->roleModel->getRolePermissionIds($id);

        require __DIR__ . '/../../Views/roles/edit.php';
    }

    /**
     * Actualiza el rol y sincroniza sus permisos.
     */
    public function update(): void
    {
        Permission::require('settings', 'update');

        $id = (int)($_POST['id'] ?? 0);
        $roleData = $this->roleModel->findTenantRole($id);

        if (!$roleData) {
            $_SESSION['flash_error'] = "Rol no encontrado.";
            header('Location: /roles');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $_SESSION['flash_error'] = "El nombre del rol es obligatorio.";
            header("Location: /roles/edit?id={$id}");
            exit;
        }

        // Actualizar datos del rol (no slug si es de sistema)
        $updateData = ['name' => $name, 'description' => $description];
        if (!$roleData->is_system) {
            $updateData['slug'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        }
        $this->roleModel->updateRole($id, $updateData);

        // Sincronizar permisos
        $permissionIds = $_POST['permissions'] ?? [];
        $this->roleModel->syncPermissions($id, array_map('intval', $permissionIds));

        $_SESSION['flash_success'] = "Rol '{$name}' actualizado exitosamente.";
        header('Location: /roles');
        exit;
    }

    /**
     * Elimina un rol (solo no-sistema).
     */
    public function delete(): void
    {
        Permission::require('settings', 'update');

        $id = (int)($_POST['id'] ?? 0);
        $success = $this->roleModel->deleteRole($id);

        if ($success) {
            $_SESSION['flash_success'] = "Rol eliminado.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar el rol. Los roles del sistema no pueden eliminarse.";
        }

        header('Location: /roles');
        exit;
    }
}
