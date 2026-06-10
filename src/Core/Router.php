<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Enrutador principal de la aplicación.
 * Permite registrar rutas y mapearlas a Controladores y Métodos.
 */
class Router
{
    private array $routes = [];
    private array $globalMiddlewares = [];

    /**
     * Agrega un middleware global que se ejecutará en todas las rutas.
     */
    public function addGlobalMiddleware(string $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }

    /**
     * Registra una ruta GET
     */
    public function get(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        $this->addRoute('GET', $uri, $controller, $action, $middlewares);
    }

    /**
     * Registra una ruta POST
     */
    public function post(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        $this->addRoute('POST', $uri, $controller, $action, $middlewares);
    }

    /**
     * Registra una ruta PUT
     */
    public function put(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        $this->addRoute('PUT', $uri, $controller, $action, $middlewares);
    }

    /**
     * Registra una ruta DELETE
     */
    public function delete(string $uri, string $controller, string $action, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $uri, $controller, $action, $middlewares);
    }

    private function addRoute(string $method, string $uri, string $controller, string $action, array $middlewares): void
    {
        // Convertimos la URI de ej: /user/{id} a un patrón Regex: /user/(?<id>[a-zA-Z0-9_-]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?<$1>[a-zA-Z0-9_-]+)', $uri);
        $pattern = "#^" . $pattern . "$#";

        $this->routes[] = [
            'method'      => $method,
            'uri'         => $uri,
            'pattern'     => $pattern,
            'controller'  => $controller,
            'action'      => $action,
            'middlewares' => $middlewares
        ];
    }

    /**
     * Resuelve la ruta actual basada en el request
     */
    public function dispatch(string $requestUri, string $requestMethod): void
    {
        // Limpiamos la URI de query strings (?foo=bar)
        $uri = parse_url($requestUri, PHP_URL_PATH) ?? '/';

        // Procesar Method Spoofing (Ej. un input oculto _method="PUT" en un formulario POST)
        if ($requestMethod === 'POST' && isset($_POST['_method'])) {
            $requestMethod = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $uri, $matches)) {
                
                // Extraer solo los parámetros nombrados de las coincidencias de Regex
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Ejecutar Middlewares Globales
                foreach ($this->globalMiddlewares as $middleware) {
                    $this->executeMiddleware($middleware);
                }

                // Ejecutar Middlewares Específicos de la ruta
                foreach ($route['middlewares'] as $middleware) {
                    $this->executeMiddleware($middleware);
                }

                // Instanciar el controlador y ejecutar la acción
                $controllerClass = $route['controller'];
                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    $action = $route['action'];

                    if (method_exists($controllerInstance, $action)) {
                        // Pasar los parámetros dinámicos (ej: $id) al método del controlador
                        call_user_func_array([$controllerInstance, $action], $params);
                        return;
                    } else {
                        throw new \Exception("Método {$action} no encontrado en el controlador {$controllerClass}");
                    }
                } else {
                    throw new \Exception("Controlador {$controllerClass} no encontrado.");
                }
            }
        }

        // Si no encuentra ruta, mostramos 404
        $this->abort(404);
    }

    /**
     * Instancia y ejecuta un middleware
     */
    private function executeMiddleware(string $middlewareClass): void
    {
        if (class_exists($middlewareClass)) {
            $middleware = new $middlewareClass();
            // Asumimos que los middlewares tienen un método handle()
            // En un flujo real, podrían recibir un objeto Request y pasar un 'next' closure
            if (method_exists($middleware, 'handle')) {
                $middleware->handle();
            }
        }
    }

    /**
     * Muestra una página de error o devuelve un JSON según corresponda
     */
    public function abort(int $code = 404): void
    {
        http_response_code($code);
        
        // Si es una petición API/JSON
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            echo json_encode(['error' => "Error {$code}. Route not found."]);
        } else {
            echo "<h1>{$code} - Página no encontrada</h1>";
        }
        
        exit();
    }
}
