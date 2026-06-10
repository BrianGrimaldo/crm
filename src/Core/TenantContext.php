<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Contexto del tenant activo por request.
 *
 * Se establece en el middleware de autenticación y queda
 * disponible globalmente durante toda la petición.
 *
 * ⚠ CRÍTICO: Ningún modelo/repositorio debe operar sin
 *   un tenant_id resuelto.
 */
final class TenantContext
{
    private static ?int $tenantId = null;
    private static ?int $userId   = null;

    private function __construct() {}

    /* ─── Setters (llamados desde el middleware) ─── */

    public static function set(int $tenantId, ?int $userId = null): void
    {
        self::$tenantId = $tenantId;
        self::$userId   = $userId;
    }

    /* ─── Getters ────────────────────────────────── */

    /**
     * @throws RuntimeException si no se ha establecido el tenant.
     */
    public static function getTenantId(): int
    {
        if (self::$tenantId === null) {
            throw new RuntimeException(
                'TenantContext: tenant_id no establecido. '
                . '¿Falta el middleware de autenticación?'
            );
        }
        return self::$tenantId;
    }

    public static function getUserId(): ?int
    {
        return self::$userId;
    }

    public static function isResolved(): bool
    {
        return self::$tenantId !== null;
    }

    /* ─── Reset (tests) ──────────────────────────── */

    public static function reset(): void
    {
        self::$tenantId = null;
        self::$userId   = null;
    }
}
