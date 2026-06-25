<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Permission;
use App\Core\TenantContext;

/**
 * ============================================================
 *  RoleMiddleware — Middleware de autorización por rol
 * ============================================================
 *
 *  Se encarga de:
 *  1. Verificar que hay sesión activa (usuario autenticado).
 *  2. Enrutar al usuario a la vista correcta según su rol:
 *       - Superadmin → /grupo-einsur (dashboard global)
 *       - Admin      → /dashboard   (dashboard de su empresa)
 *       - Vendedor   → /dashboard   (su pipeline personal)
 *  3. Bloquear acceso a rutas no permitidas para cada rol.
 * ============================================================
 */
class RoleMiddleware
{
    /**
     * Verifica que el usuario esté autenticado.
     * Si no, lo manda al login.
     */
    public static function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    /**
     * Bloquea al Superadmin de acceder a vistas operativas de empresa.
     * El Superadmin es SOLO analítico/lectura a nivel de Grupo.
     */
    public static function blockIfSuperadmin(): void
    {
        if (Permission::isSuperadmin()) {
            header('Location: ' . url('/grupo-einsur'));
            exit;
        }
    }

    /**
     * Permite solo al Superadmin.
     */
    public static function onlySuperadmin(): void
    {
        self::requireAuth();
        if (!Permission::isSuperadmin()) {
            http_response_code(403);
            http_response_code(403);
            $_SESSION['flash_error'] = 'Esta sección es exclusiva para el Superadmin del Grupo EINSUR.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }

    /**
     * Permite al Admin y Superadmin (para gestión de usuarios/empresas).
     * El Superadmin sigue sin poder hacer operativa.
     */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!Permission::isAdmin() && !Permission::isSuperadmin()) {
            $_SESSION['flash_error'] = 'Acceso restringido a Administradores.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
