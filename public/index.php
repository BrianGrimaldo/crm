<?php

declare(strict_types=1);

/**
 * Punto de entrada principal del CRM.
 * Carga el autoloader de Composer y las variables de entorno.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/helpers.php';

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// ── Headers de seguridad ────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// ── Zona horaria por defecto ────────────────────────────────
date_default_timezone_set('America/Mexico_City');

// ── Manejo de errores en desarrollo ─────────────────────────
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// ── Iniciar sesión ──────────────────────────────────────────
session_start();

// ── Router ──────────────────────────────────────────────────
use App\Core\Router;

$router = new Router();

// Cargar definición de rutas
require_once __DIR__ . '/../routes/web.php';

// Procesar la URI actual
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
if ($requestUri === '' || $requestUri === false) {
    $requestUri = '/';
}
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Despachar la ruta
try {
    $router->dispatch($requestUri, $requestMethod);
} catch (Exception $e) {
    http_response_code(500);
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        echo "<h1>Error 500</h1><p>" . $e->getMessage() . "</p>";
    } else {
        echo "<h1>Error 500</h1><p>Ha ocurrido un error interno en el servidor.</p>";
    }
}
