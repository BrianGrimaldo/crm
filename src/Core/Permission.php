<?php

declare(strict_types=1);

namespace App\Core;

/**
 * ============================================================
 *  RBAC — Sistema de Control de Acceso por Roles
 * ============================================================
 *
 *  TRES ROLES ÚNICOS:
 *
 *  1. superadmin  — Vista global de Grupo Einsur (solo lectura analítica).
 *                   No opera en ninguna empresa individual.
 *
 *  2. admin       — Gerente de empresa. Lee y gestiona datos de su
 *                   empresa (tenant_id). Nunca ve datos de otras empresas.
 *
 *  3. vendedor    — Operativo individual. Solo ve y gestiona los registros
 *                   asignados a su propio user_id.
 *
 * ============================================================
 */
class Permission
{
    // ─── Constantes de roles ───────────────────────────────────
    public const ROLE_SUPERADMIN = 'superadmin'; // antes decía 'superadministrador'
    public const ROLE_ADMIN = 'gerente';          // antes decía 'gerentedeventas'
    public const ROLE_VENDEDOR = 'vendedor';      // este ya está bien
    public const ROLE_COBRANZA = 'cobranza';      // este ya está bien

    // ─── Helpers de identidad ─────────────────────────────────

    /** Devuelve el slug del rol actual (normalizado). */
    public static function currentRole(): string
    {
        $raw = $_SESSION['user_role'] ?? '';
        return strtolower(str_replace(['-', ' '], '', $raw));
    }

    public static function isSuperadmin(): bool
    {
        return self::currentRole() === self::ROLE_SUPERADMIN
            || (!empty($_SESSION['is_superadmin']) && (bool) $_SESSION['is_superadmin']);
    }

    public static function isAdmin(): bool
    {
        return self::currentRole() === self::ROLE_ADMIN;
    }

    public static function isVendedor(): bool
    {
        // Cualquier rol que NO sea superadmin ni admin se trata como vendedor
        return !self::isSuperadmin() && !self::isAdmin();
    }

    // ─── Verificación de permiso ──────────────────────────────

    /**
     * ¿Tiene el usuario permiso para un módulo/acción?
     *
     * Superadmin: Solo tiene acceso READ a módulos analíticos/globales.
     *             NO tiene acceso a operativa (create, update, delete).
     * Admin:      Acceso total dentro de su empresa (tenant-scoped).
     * Vendedor:   Acceso operativo solo a sus propios registros.
     */
    public static function has(string $module, string $action): bool
    {
        if (self::isSuperadmin()) {
            // El Superadmin (CEO) tiene acceso total (CRUD) a absolutamente todo el sistema
            return true;
        }

        if (self::isAdmin()) {
            // El Admin tiene acceso total dentro de su empresa
            // (los datos ya están filtrados por tenant_id a nivel de BD)
            if (in_array('*', $_SESSION['permissions'] ?? [], true)) {
                return true;
            }
            if (in_array("{$module}.*", $_SESSION['permissions'] ?? [], true)) {
                return true;
            }
            return in_array("{$module}.{$action}", $_SESSION['permissions'] ?? [], true);
        }

        // Vendedor: permisos operativos
        $permissions = $_SESSION['permissions'] ?? [];

        if (in_array('*', $permissions, true)) {
            return true;
        }
        if (in_array("{$module}.*", $permissions, true)) {
            return true;
        }

        // Por defecto, permitir que el vendedor vea, cree y actualice SUS propios registros operativos
        $operationalModules = ['deals', 'contacts', 'accounts', 'tasks', 'activities', 'tickets'];

        // Roles de cobranza tienen acceso total al módulo de finanzas
        $roleStr = self::currentRole();
        $isCobranza = strpos($roleStr, 'cobranza') !== false 
                   || strpos($roleStr, 'collection') !== false 
                   || strpos($roleStr, 'cobrador') !== false;
        
        if ($isCobranza && $module === 'finance') {
            return true;
        }

        if (in_array($module, $operationalModules, true) && in_array($action, ['view', 'create', 'update'])) {
            return true;
        }

        // Permitir crear facturas (emitir factura) desde Ventas, sin darles acceso a ver el módulo entero
        if ($module === 'finance' && $action === 'create') {
            return true;
        }

        return in_array("{$module}.{$action}", $permissions, true);
    }

    /**
     * Aborta con error si el usuario no tiene el permiso.
     */
    public static function require(string $module, string $action): void
    {
        if (!self::has($module, $action)) {
            $_SESSION['flash_error'] = "No tienes permisos para realizar esta acción ({$module}:{$action}).";
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }

    // ─── Data Scoping ─────────────────────────────────────────

    /**
     * ¿Deben filtrarse los registros solo por el owner_id del usuario actual?
     *
     * true  → Vendedor:    WHERE owner_id = :user_id
     * false → Admin:       WHERE tenant_id = :tenant_id  (sin restricción por usuario)
     * false → Superadmin:  Sin filtros (consulta global, solo analítica)
     */
    public static function isRestrictedToOwnRecords(): bool
    {
        return self::isVendedor();
    }

    /**
     * ¿Deben filtrarse los registros por el tenant_id (empresa)?
     *
     * true  → TODOS los roles, incluyendo Superadmin.
     *         El Superadmin ve datos de la empresa que tenga seleccionada
     *         en sesión, igual que un Gerente. La vista global sin filtros
     *         es exclusiva del GrupoEinsurController (consultas directas).
     * false → Nunca. Ningún rol rompe el aislamiento de empresa aquí.
     */
    public static function isRestrictedToOwnTenant(): bool
    {
        return true;
    }

    /**
     * ¿Tiene el usuario visibilidad global sobre la facturación de la empresa?
     * Superadmin, Admin y roles de cobranza pueden ver todas las facturas.
     */
    public static function canViewAllInvoices(): bool
    {
        $role = self::currentRole();
        
        $isCobranza = strpos($role, 'cobranza') !== false 
                   || strpos($role, 'collection') !== false 
                   || strpos($role, 'cobrador') !== false;

        return self::isAdmin() || self::isSuperadmin() || $isCobranza;
    }

    // ─── Guardia de Ruta ──────────────────────────────────────

    /**
     * Protege rutas que solo puede ver el Superadmin.
     * Redirige a /dashboard si el rol no coincide.
     */
    public static function requireSuperadmin(): void
    {
        if (!self::isSuperadmin()) {
            $_SESSION['flash_error'] = 'Acceso restringido al Superadmin del Grupo.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }

    /**
     * Protege rutas que solo puede ver el Admin de empresa.
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin() && !self::isSuperadmin()) {
            $_SESSION['flash_error'] = 'Acceso restringido a Administradores de empresa.';
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }

    /**
     * Bloquea al Superadmin de entrar a rutas operativas de empresa.
     * El Superadmin no debe operar dentro de ninguna empresa individual.
     */
    public static function blockSuperadmin(): void
    {
        if (self::isSuperadmin()) {
            header('Location: ' . url('/grupo-einsur'));
            exit;
        }
    }
}
