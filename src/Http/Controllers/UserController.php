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
        
        $tenants = [];
        $allRolesJson = '{}';
        
        if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin']) {
            $db = \App\Core\Database::getInstance();
            $stmtTenants = $db->query("SELECT id, name FROM tenants WHERE is_active = 1 ORDER BY name ASC");
            $tenants = $stmtTenants->fetchAll(\PDO::FETCH_OBJ);
            
            $stmtRoles = $db->query("SELECT id, tenant_id, name FROM roles ORDER BY name ASC");
            $allRolesRaw = $stmtRoles->fetchAll(\PDO::FETCH_OBJ);
            
            $groupedRoles = [];
            foreach ($allRolesRaw as $r) {
                $groupedRoles[$r->tenant_id][] = ['id' => $r->id, 'name' => $r->name];
            }
            $allRolesJson = json_encode($groupedRoles);
        }
        
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
            header('Location: /usuarios/create');
            exit;
        }

        $targetTenantId = null;
        if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] && !empty($_POST['tenant_id'])) {
            $targetTenantId = (int)$_POST['tenant_id'];
        }

        // En producción habría que validar si el email ya existe
        $success = $this->userModel->createUserForTenant($data, $roleId, $targetTenantId);

        if ($success) {
            try {
                $tenantName = isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] && $targetTenantId
                    ? "nuestra empresa" // O buscar el nombre del tenant
                    : ($_SESSION['tenant_name'] ?? 'nuestra empresa');
                
                $emailService = new \App\Core\EmailService();
                $emailService->sendWelcomeEmail($data['email'], $data['first_name'], $data['password'], $tenantName);
            } catch (\Exception $e) {
                // Ignore email errors to not break the flow
            }

            $_SESSION['flash_success'] = "Usuario {$data['first_name']} creado exitosamente.";
            header('Location: /usuarios');
        } else {
            $_SESSION['flash_error'] = "Hubo un problema al crear el usuario. Es posible que el correo ya esté en uso.";
            header('Location: /usuarios/create');
        }
        exit;
    }
}
