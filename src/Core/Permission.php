<?php

declare(strict_types=1);

namespace App\Core;

class Permission
{
    /**
     * Verifica si el usuario actual tiene el permiso requerido.
     * 
     * @param string $module El módulo (ej: 'contacts', 'deals', 'accounts')
     * @param string $action La acción (ej: 'view', 'create', 'update', 'delete')
     * @return bool
     */
    public static function has(string $module, string $action): bool
    {
        $permissions = $_SESSION['permissions'] ?? [];
        $isOwner = $_SESSION['is_owner'] ?? false;
        $isSuperadmin = $_SESSION['is_superadmin'] ?? false;

        // El owner o superadmin siempre tiene acceso total
        if ($isOwner || $isSuperadmin) {
            return true;
        }

        // Acceso root a todos los permisos
        if (in_array('*', $permissions, true)) {
            return true;
        }

        // Acceso total a un módulo (ej: 'contacts.*')
        if (in_array("{$module}.*", $permissions, true)) {
            return true;
        }

        // Permiso específico
        return in_array("{$module}.{$action}", $permissions, true);
    }

    /**
     * Lanza una excepción o redirige si el usuario no tiene permiso.
     */
    public static function require(string $module, string $action): void
    {
        if (!self::has($module, $action)) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción ({$module}:{$action}).";
            header('Location: /crm_einsurglobal/public/dashboard');
            exit;
        }
    }

    /**
     * Determina si el usuario actual debe ver solo sus propios registros.
     * Restringe a cualquier usuario que no sea dueño o superadmin.
     */
    public static function isRestrictedToOwnRecords(): bool
    {
        $isOwner = $_SESSION['is_owner'] ?? false;
        $isSuperadmin = $_SESSION['is_superadmin'] ?? false;
        
        if ($isOwner || $isSuperadmin) {
            return false;
        }

        // Cualquier otro usuario (vendedores, gerentes, soporte) solo ve lo suyo
        // o lo que el sistema permita por defecto.
        return true;
    }
}
