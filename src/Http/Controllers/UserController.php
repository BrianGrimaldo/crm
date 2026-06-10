<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Core\Permission;
use App\Core\TenantContext;

class UserController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        Permission::require('users', 'view');
        $users = $this->userModel->getTenantUsers();

        require __DIR__ . '/../../Views/users/index.php';
    }

    public function create(): void
    {
        Permission::require('users', 'create');
        $roleModel = new Role();
        $roles = $roleModel->getTenantRoles();
        require __DIR__ . '/../../Views/users/create.php';
    }

    public function store(): void
    {
        Permission::require('users', 'create');

        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name'  => $_POST['last_name'] ?? '',
            'email'      => $_POST['email'] ?? '',
            'phone'      => $_POST['phone'] ?? '',
            'password'   => $_POST['password'] ?? ''
        ];
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (empty($data['first_name']) || empty($data['email']) || empty($data['password']) || !$roleId) {
            $_SESSION['flash_error'] = "Todos los campos obligatorios deben estar llenos.";
            header('Location: /crm_einsurglobal/public/users/create');
            exit;
        }

        // En producción habría que validar si el email ya existe
        $success = $this->userModel->createUserForTenant($data, $roleId);

        if ($success) {
            $_SESSION['flash_success'] = "Usuario {$data['first_name']} creado exitosamente.";
            header('Location: /crm_einsurglobal/public/users');
        } else {
            $_SESSION['flash_error'] = "Hubo un problema al crear el usuario. Es posible que el correo ya esté en uso.";
            header('Location: /crm_einsurglobal/public/users/create');
        }
        exit;
    }
}
