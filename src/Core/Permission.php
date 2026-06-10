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

        // El owner siempre tiene acceso total
        if ($isOwner) {
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
     * En este caso, el rol 'sales-rep' (Vendedor) está restringido.
     */
    public static function isRestrictedToOwnRecords(): bool
    {
        $role = $_SESSION['user_role'] ?? '';
        $isOwner = $_SESSION['is_owner'] ?? false;
        
        if ($isOwner) {
            return false;
        }

        // Si es vendedor, restringir
        return $role === 'sales-rep';
    }
}
