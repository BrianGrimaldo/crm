<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Database;
use App\Core\TenantContext;

/**
 * Middleware de autenticación.
 *
 * Resuelve el usuario y el tenant a partir del token JWT
 * (o sesión) y establece el TenantContext para toda la request.
 *
 * ⚠ Debe ejecutarse ANTES de cualquier acceso a repositorios.
 */
class AuthMiddleware
{
    /**
     * Procesar la petición entrante.
     *
     * @param callable $next Siguiente middleware / controlador.
     */
    public function handle(callable $next): mixed
    {
        $token = $this->extractBearerToken();

        if ($token === null) {
            http_response_code(401);
            return json_encode(['error' => 'Token de autenticación requerido.']);
        }

        try {
            $jwtConfig = require dirname(__DIR__, 3) . '/config/jwt.php';

            // Decodificar JWT (usando firebase/php-jwt)
            $decoded = \Firebase\JWT\JWT::decode(
                $token,
                new \Firebase\JWT\Key($jwtConfig['secret'], $jwtConfig['algorithm'])
            );

            $tenantId = (int) ($decoded->tenant_id ?? 0);
            $userId   = (int) ($decoded->sub ?? 0);

            if ($tenantId === 0) {
                throw new \RuntimeException('Token sin tenant_id.');
            }

            // Verificar que el usuario pertenece al tenant
            $db   = Database::getInstance();
            $stmt = $db->prepare(
                "SELECT tu.id FROM tenant_users tu
                 WHERE tu.tenant_id = :tid AND tu.user_id = :uid AND tu.is_active = 1
                 LIMIT 1"
            );
            $stmt->execute([':tid' => $tenantId, ':uid' => $userId]);

            if (!$stmt->fetch()) {
                http_response_code(403);
                return json_encode(['error' => 'Sin acceso a esta empresa.']);
            }

            // ─── ESTABLECER CONTEXTO GLOBAL ───
            TenantContext::set($tenantId, $userId);

            return $next();

        } catch (\Firebase\JWT\ExpiredException $e) {
            http_response_code(401);
            return json_encode(['error' => 'Token expirado.']);
        } catch (\Throwable $e) {
            http_response_code(401);
            return json_encode(['error' => 'Token inválido.']);
        }
    }

    /**
     * Extraer el token Bearer del header Authorization.
     */
    private function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
                ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
                ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
