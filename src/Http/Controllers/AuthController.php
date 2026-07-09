<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use PDO;

class AuthController
{
    /**
     * Muestra el formulario de login.
     */
    public function showLogin(): void
    {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['user_id']) && isset($_SESSION['tenant_id'])) {
            header('Location: ' . url('/dashboard'));
            exit();
        }

        require __DIR__ . '/../../Views/auth/login.php';
    }

    /**
     * Procesa las credenciales enviadas por POST.
     */
    public function authenticate(): void
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->redirectBackWithError('Por favor, ingresa tu correo y contraseña.');
        }

        $db = Database::getInstance();

        // 1. Buscar al usuario globalmente
        $stmt = $db->prepare("SELECT id, email, password_hash, is_active, is_superadmin, first_name, last_name FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$user) {
            $this->redirectBackWithError('Credenciales incorrectas.');
        }

        if (!$user->is_active) {
            $this->redirectBackWithError('Tu cuenta está inactiva. Contacta al administrador.');
        }

        // 2. Verificar la contraseña
        if (!password_verify($password, $user->password_hash)) {
            // Nota: En la semilla el password suele ser 'password' o '123456'
            $this->redirectBackWithError('Credenciales incorrectas.');
        }

        // 3. Obtener el tenant (empresa) y el rol al que pertenece el usuario
        $stmtTenant = $db->prepare("
            SELECT t.id as tenant_id, t.name as tenant_name, t.logo_url as tenant_logo,
                   tu.role_id, tu.is_owner, r.slug as role_slug 
            FROM tenant_users tu
            JOIN tenants t ON t.id = tu.tenant_id
            LEFT JOIN roles r ON r.id = tu.role_id
            WHERE tu.user_id = :user_id AND tu.is_active = 1 AND t.is_active = 1
            LIMIT 1
        ");
        $stmtTenant->execute([':user_id' => $user->id]);
        $tenant = $stmtTenant->fetch(PDO::FETCH_OBJ);

        if (!$tenant) {
            $this->redirectBackWithError('No tienes acceso a ninguna empresa activa.');
        }

        // Cargar permisos del rol
        $permissions = [];
        if ($tenant->is_owner) {
            // El propietario tiene acceso total (super admin del tenant)
            $permissions = ['*'];
        } else if ($tenant->role_id) {
            $stmtPerms = $db->prepare("
                SELECT p.module, p.action 
                FROM role_permissions rp
                JOIN permissions p ON p.id = rp.permission_id
                WHERE rp.role_id = :role_id
            ");
            $stmtPerms->execute([':role_id' => $tenant->role_id]);
            $perms = $stmtPerms->fetchAll(PDO::FETCH_OBJ);
            foreach ($perms as $p) {
                $permissions[] = "{$p->module}.{$p->action}";
            }
        }

        // 4. Obtener todas las empresas disponibles para el selector
        $isRoleSuperAdmin = strpos(strtolower($tenant->role_slug ?? ''), 'superadmin') !== false;
        
        if ($user->is_superadmin || $isRoleSuperAdmin) {
            $stmtAllTenants = $db->prepare("SELECT id, name FROM tenants WHERE is_active = 1");
            $stmtAllTenants->execute();
        } else {
            $stmtAllTenants = $db->prepare("
                SELECT t.id, t.name 
                FROM tenant_users tu
                JOIN tenants t ON t.id = tu.tenant_id
                WHERE tu.user_id = :user_id AND tu.is_active = 1 AND t.is_active = 1
            ");
            $stmtAllTenants->execute([':user_id' => $user->id]);
        }
        $_SESSION['available_tenants'] = $stmtAllTenants->fetchAll(PDO::FETCH_ASSOC);

        // 5. Iniciar sesión correctamente
        // Evitamos Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user->id;
        $_SESSION['tenant_id'] = $tenant->tenant_id;
        $_SESSION['tenant_name'] = $tenant->tenant_name;
        $_SESSION['tenant_logo'] = $tenant->tenant_logo;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: $user->email;
        $_SESSION['user_role'] = $tenant->role_slug ?? 'user';
        $_SESSION['is_owner'] = (bool) $tenant->is_owner;
        $_SESSION['is_superadmin'] = (bool) $user->is_superadmin || $isRoleSuperAdmin;
        $_SESSION['permissions'] = $permissions;

        if (\App\Core\Permission::isSuperadmin()) {
            header('Location: ' . url('/grupo-einsur'));
        } else {
            header('Location: ' . url('/dashboard'));
        }
        exit();
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout(): void
    {
        session_unset();
        session_destroy();
        header('Location: ' . url('/login'));
        exit();
    }

    private function redirectBackWithError(string $message): void
    {
        $_SESSION['login_error'] = $message;
        header('Location: ' . url('/login'));
        exit();
    }

    /**
     * Cambia la sesión a otra empresa seleccionada por el usuario.
     */
    public function switchTenant(): void
    {
        try {
            $tenantId = (int) ($_GET['id'] ?? 0);
            if (!$tenantId || !isset($_SESSION['user_id'])) {
                header('Location: ' . url('/dashboard'));
                exit();
            }

            $db = Database::getInstance();
            $user_id = $_SESSION['user_id'];
            $is_superadmin = $_SESSION['is_superadmin'] ?? false;
            $tenant = null;
            $permissions = [];

            if ($is_superadmin) {
                $stmt = $db->prepare("SELECT id as tenant_id, name as tenant_name, logo_url as tenant_logo FROM tenants WHERE id = :id AND is_active = 1");
                $stmt->execute([':id' => $tenantId]);
                $tenant = $stmt->fetch(PDO::FETCH_OBJ);
                if ($tenant) {
                    $tenant->role_slug = 'superadmin';
                    $tenant->is_owner = 1;
                    $permissions = ['*'];
                }
            } else {
                $stmt = $db->prepare("
                    SELECT t.id as tenant_id, t.name as tenant_name, t.logo_url as tenant_logo,
                           tu.role_id, tu.is_owner, r.slug as role_slug 
                    FROM tenant_users tu
                    JOIN tenants t ON t.id = tu.tenant_id
                    LEFT JOIN roles r ON r.id = tu.role_id
                    WHERE tu.user_id = :user_id AND tu.tenant_id = :tenant_id AND tu.is_active = 1 AND t.is_active = 1
                ");
                $stmt->execute([':user_id' => $user_id, ':tenant_id' => $tenantId]);
                $tenant = $stmt->fetch(PDO::FETCH_OBJ);

                if ($tenant) {
                    if ($tenant->is_owner) {
                        $permissions = ['*'];
                    } else if ($tenant->role_id) {
                        $stmtPerms = $db->prepare("SELECT p.module, p.action FROM role_permissions rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = :role_id");
                        $stmtPerms->execute([':role_id' => $tenant->role_id]);
                        $perms = $stmtPerms->fetchAll(PDO::FETCH_OBJ);
                        foreach ($perms as $p) {
                            $permissions[] = "{$p->module}.{$p->action}";
                        }
                    }
                }
            }

            if ($tenant) {
                $_SESSION['tenant_id'] = $tenant->tenant_id;
                $_SESSION['tenant_name'] = $tenant->tenant_name;
                $_SESSION['tenant_logo'] = $tenant->tenant_logo;
                $_SESSION['user_role'] = $tenant->role_slug ?? 'user';
                $_SESSION['is_owner'] = (bool) $tenant->is_owner;
                $_SESSION['permissions'] = $permissions;
            } else {
                $_SESSION['flash_error'] = "No tienes permiso para acceder a esta empresa.";
            }

            // Mantener al usuario en la vista actual tras cambiar de empresa
            $redirectUrl = url('/dashboard');
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $redirectUrl = $_SERVER['HTTP_REFERER'];
            }
            header('Location: ' . $redirectUrl);
            exit();
        } catch (\Exception $e) {
            die("Error Crítico al cambiar de empresa: " . $e->getMessage());
        }
    }
}
