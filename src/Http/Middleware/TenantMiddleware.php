<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\TenantContext;

/**
 * Middleware que intercepta la petición para establecer el contexto del Tenant (Cliente).
 * Garantiza que cada petición opere de forma aislada sobre los datos correspondientes.
 */
class TenantMiddleware
{
    public function handle(): void
    {
        // 1. Intentar obtener el tenant_id. 
        // Normalmente esto viene de la sesión (tras hacer login) o de un JWT/subdominio.
        $tenantId = $_SESSION['tenant_id'] ?? null;
        $userId   = $_SESSION['user_id'] ?? null;

        // Para propósito de desarrollo/test, si no hay sesión, 
        // podemos aceptar un parámetro por header o forzar uno temporalmente
        // pero en producción se debe bloquear si no existe.
        if (!$tenantId) {
            $tenantId = $_SERVER['HTTP_X_TENANT_ID'] ?? null;
        }

        if ($tenantId) {
            // 2. Establecer el TenantContext globalmente para esta petición
            TenantContext::set((int) $tenantId, $userId ? (int) $userId : null);
        } else {
            // Si la ruta requiere TenantMiddleware y no hay tenant, abortamos.
            // Para rutas públicas (como el login), este middleware no debería ejecutarse.
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                echo json_encode(['error' => 'Acceso denegado: Tenant no identificado.']);
            } else {
                echo "<h1>403 - Acceso Denegado</h1><p>Tenant no identificado. Por favor, inicie sesión.</p>";
            }
            exit();
        }
    }
}
