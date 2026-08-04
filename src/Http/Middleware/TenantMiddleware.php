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
            // Sesión expirada o no iniciada.
            // Para peticiones AJAX/API, devolver JSON 401.
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                http_response_code(401);
                echo json_encode(['error' => 'Sesión expirada. Por favor, inicie sesión nuevamente.']);
                exit();
            }

            // Para peticiones normales (navegador), redirigir al login con mensaje.
            $_SESSION['flash_error'] = 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.';
            $loginUrl = function_exists('url') ? url('/login') : '/login';
            header('Location: ' . $loginUrl);
            exit();
        }
    }
}
