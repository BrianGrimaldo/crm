<?php

/**
 * Funciones helper globales para el CRM.
 */

/**
 * Genera una URL relativa al base path de la aplicación.
 * En producción (Document Root = public/): devuelve "/login"
 * En desarrollo (localhost/crm_einsurglobal): devuelve "/crm_einsurglobal/public/login"
 *
 * @param string $path  Ruta relativa (ej: "/login", "/dashboard")
 * @return string       URL completa con base path
 */
function url(string $path = '/'): string
{
    static $basePath = null;

    if ($basePath === null) {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $basePath = rtrim(dirname($scriptName), '/\\');
        if ($basePath === '.' || $basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
    }

    $path = '/' . ltrim($path, '/');
    return $basePath . $path;
}

/**
 * Genera una URL para assets estáticos (imágenes, CSS, JS).
 *
 * @param string $path  Ruta del asset (ej: "/img/logo.png")
 * @return string       URL completa del asset
 */
function asset(string $path): string
{
    return url($path);
}
