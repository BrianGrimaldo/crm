<?php

declare(strict_types=1);

/**
 * Define las rutas de la aplicación web.
 * Esta variable $router está disponible porque el archivo es requerido desde public/index.php
 */

use App\Http\Controllers\HomeController;

// Rutas públicas
$router->get('/', HomeController::class, 'index');

use App\Http\Controllers\AuthController;
$router->get('/login', AuthController::class, 'showLogin');
$router->post('/login', AuthController::class, 'authenticate');
$router->get('/logout', AuthController::class, 'logout');
$router->get('/switch-tenant', AuthController::class, 'switchTenant');

// Rutas protegidas (Requieren Tenant)
use App\Http\Middleware\TenantMiddleware;
$router->get('/dashboard', HomeController::class, 'dashboard', [TenantMiddleware::class]);

use App\Http\Controllers\ContactController;
$router->get('/contacts', ContactController::class, 'index', [TenantMiddleware::class]);
$router->get('/contacts/create', ContactController::class, 'create', [TenantMiddleware::class]);
$router->post('/contacts', ContactController::class, 'store', [TenantMiddleware::class]);
$router->get('/contacts/edit', ContactController::class, 'edit', [TenantMiddleware::class]);
$router->post('/contacts/update', ContactController::class, 'update', [TenantMiddleware::class]);
$router->post('/contacts/delete', ContactController::class, 'delete', [TenantMiddleware::class]);

use App\Http\Controllers\DealController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

// Profile routes
$router->get('/profile', ProfileController::class, 'edit', [TenantMiddleware::class]);
$router->post('/profile/update', ProfileController::class, 'update', [TenantMiddleware::class]);

// Users routes
$router->get('/users', UserController::class, 'index', [TenantMiddleware::class]);
$router->get('/users/create', UserController::class, 'create', [TenantMiddleware::class]);
$router->post('/users', UserController::class, 'store', [TenantMiddleware::class]);

$router->get('/accounts', AccountController::class, 'index', [TenantMiddleware::class]);
$router->get('/accounts/create', AccountController::class, 'create', [TenantMiddleware::class]);
$router->post('/accounts', AccountController::class, 'store', [TenantMiddleware::class]);
$router->get('/accounts/edit', AccountController::class, 'edit', [TenantMiddleware::class]);
$router->post('/accounts/update', AccountController::class, 'update', [TenantMiddleware::class]);
$router->post('/accounts/delete', AccountController::class, 'delete', [TenantMiddleware::class]);

$router->get('/deals', DealController::class, 'index', [TenantMiddleware::class]);
$router->get('/deals/pipeline', DealController::class, 'pipeline', [TenantMiddleware::class]);
$router->get('/deals/create', DealController::class, 'create', [TenantMiddleware::class]);
$router->post('/deals', DealController::class, 'store', [TenantMiddleware::class]);
$router->get('/deals/edit', DealController::class, 'edit', [TenantMiddleware::class]);
$router->post('/deals/update', DealController::class, 'update', [TenantMiddleware::class]);
$router->post('/deals/delete', DealController::class, 'delete', [TenantMiddleware::class]);
$router->post('/api/deals/move-stage', DealController::class, 'moveStage', [TenantMiddleware::class]);

// Roles routes
use App\Http\Controllers\RoleController;
$router->get('/roles', RoleController::class, 'index', [TenantMiddleware::class]);
$router->get('/roles/create', RoleController::class, 'create', [TenantMiddleware::class]);
$router->post('/roles', RoleController::class, 'store', [TenantMiddleware::class]);
$router->get('/roles/edit', RoleController::class, 'edit', [TenantMiddleware::class]);
$router->post('/roles/update', RoleController::class, 'update', [TenantMiddleware::class]);
$router->post('/roles/delete', RoleController::class, 'delete', [TenantMiddleware::class]);

// Activities route
$router->post('/activities', App\Http\Controllers\ActivityController::class, 'store', [TenantMiddleware::class]);

// Vendedores route (Superadmin only)
use App\Http\Controllers\VendedoresController;
$router->get('/vendedores', VendedoresController::class, 'index', [TenantMiddleware::class]);

// Empresas route (Superadmin only)
use App\Http\Controllers\TenantController;
$router->get('/empresas', TenantController::class, 'index', [TenantMiddleware::class]);
$router->get('/empresas/create', TenantController::class, 'create', [TenantMiddleware::class]);
$router->post('/empresas', TenantController::class, 'store', [TenantMiddleware::class]);
$router->get('/empresas/edit', TenantController::class, 'edit', [TenantMiddleware::class]);
$router->post('/empresas/update', TenantController::class, 'update', [TenantMiddleware::class]);

