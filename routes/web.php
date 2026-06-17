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
$router->get('/contactos', ContactController::class, 'index', [TenantMiddleware::class]);
$router->get('/contactos/create', ContactController::class, 'create', [TenantMiddleware::class]);
$router->post('/contactos', ContactController::class, 'store', [TenantMiddleware::class]);
$router->get('/contactos/edit', ContactController::class, 'edit', [TenantMiddleware::class]);
$router->post('/contactos/update', ContactController::class, 'update', [TenantMiddleware::class]);
$router->post('/contactos/delete', ContactController::class, 'delete', [TenantMiddleware::class]);
$router->post('/api/contactos/send-email', ContactController::class, 'sendEmail', [TenantMiddleware::class]);

// Import routes
use App\Http\Controllers\ImportController;
$router->get('/importar',            ImportController::class, 'index',    [TenantMiddleware::class]);
$router->post('/importar/preview',   ImportController::class, 'preview',  [TenantMiddleware::class]);
$router->post('/importar/commit',    ImportController::class, 'commit',   [TenantMiddleware::class]);
$router->get('/importar/plantilla',  ImportController::class, 'template', [TenantMiddleware::class]);

use App\Http\Controllers\DealController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

// Profile routes
$router->get('/perfil', ProfileController::class, 'edit', [TenantMiddleware::class]);
$router->post('/perfil/update', ProfileController::class, 'update', [TenantMiddleware::class]);

// Users routes
$router->get('/usuarios', UserController::class, 'index', [TenantMiddleware::class]);
$router->get('/usuarios/create', UserController::class, 'create', [TenantMiddleware::class]);
$router->post('/usuarios', UserController::class, 'store', [TenantMiddleware::class]);

$router->get('/organizaciones', AccountController::class, 'index', [TenantMiddleware::class]);
$router->get('/organizaciones/create', AccountController::class, 'create', [TenantMiddleware::class]);
$router->post('/organizaciones', AccountController::class, 'store', [TenantMiddleware::class]);
$router->get('/organizaciones/edit', AccountController::class, 'edit', [TenantMiddleware::class]);
$router->post('/organizaciones/update', AccountController::class, 'update', [TenantMiddleware::class]);
$router->post('/organizaciones/delete', AccountController::class, 'delete', [TenantMiddleware::class]);

$router->get('/oportunidades', DealController::class, 'index', [TenantMiddleware::class]);
$router->get('/oportunidades/pipeline', DealController::class, 'pipeline', [TenantMiddleware::class]);
$router->get('/oportunidades/create', DealController::class, 'create', [TenantMiddleware::class]);
$router->post('/oportunidades', DealController::class, 'store', [TenantMiddleware::class]);
$router->get('/oportunidades/edit', DealController::class, 'edit', [TenantMiddleware::class]);
$router->post('/oportunidades/update', DealController::class, 'update', [TenantMiddleware::class]);
$router->post('/oportunidades/delete', DealController::class, 'delete', [TenantMiddleware::class]);
$router->post('/api/oportunidades/move-stage', DealController::class, 'moveStage', [TenantMiddleware::class]);

// Roles routes
use App\Http\Controllers\RoleController;
$router->get('/roles', RoleController::class, 'index', [TenantMiddleware::class]);
$router->get('/roles/create', RoleController::class, 'create', [TenantMiddleware::class]);
$router->post('/roles', RoleController::class, 'store', [TenantMiddleware::class]);
$router->get('/roles/edit', RoleController::class, 'edit', [TenantMiddleware::class]);
$router->post('/roles/update', RoleController::class, 'update', [TenantMiddleware::class]);
$router->post('/roles/delete', RoleController::class, 'delete', [TenantMiddleware::class]);

// Pipeline routes
use App\Http\Controllers\PipelineController;
$router->get('/configuracion/embudo', PipelineController::class, 'index', [TenantMiddleware::class]);
$router->get('/configuracion/embudo/create', PipelineController::class, 'create', [TenantMiddleware::class]);
$router->post('/configuracion/embudo', PipelineController::class, 'store', [TenantMiddleware::class]);
$router->get('/configuracion/embudo/edit', PipelineController::class, 'edit', [TenantMiddleware::class]);
$router->post('/configuracion/embudo/update', PipelineController::class, 'update', [TenantMiddleware::class]);
$router->post('/configuracion/embudo/delete', PipelineController::class, 'delete', [TenantMiddleware::class]);

// Products / Inventory routes
use App\Http\Controllers\ProductController;
$router->get('/productos', ProductController::class, 'index', [TenantMiddleware::class]);
$router->get('/productos/create', ProductController::class, 'create', [TenantMiddleware::class]);
$router->post('/productos', ProductController::class, 'store', [TenantMiddleware::class]);
$router->get('/productos/edit', ProductController::class, 'edit', [TenantMiddleware::class]);
$router->post('/productos/update', ProductController::class, 'update', [TenantMiddleware::class]);
$router->post('/productos/delete', ProductController::class, 'delete', [TenantMiddleware::class]);

// Reports route
use App\Http\Controllers\ReportController;
$router->get('/reportes', ReportController::class, 'index', [TenantMiddleware::class]);
$router->get('/reportes/exportar-ventas', ReportController::class, 'exportDeals', [TenantMiddleware::class]);
$router->get('/reportes/exportar-timeline', ReportController::class, 'exportTimeline', [TenantMiddleware::class]);
$router->get('/analiticas', ReportController::class, 'analytics', [TenantMiddleware::class]);

// Tasks / Activities routes
use App\Http\Controllers\TaskController;
$router->get('/tareas', TaskController::class, 'index', [TenantMiddleware::class]);
$router->get('/tareas/create', TaskController::class, 'create', [TenantMiddleware::class]);
$router->post('/tareas', TaskController::class, 'store', [TenantMiddleware::class]);
$router->get('/tareas/edit', TaskController::class, 'edit', [TenantMiddleware::class]);
$router->post('/tareas/update', TaskController::class, 'update', [TenantMiddleware::class]);
$router->post('/tareas/delete', TaskController::class, 'delete', [TenantMiddleware::class]);
$router->post('/tareas/complete', TaskController::class, 'complete', [TenantMiddleware::class]);

// Activities route
$router->post('/activities', App\Http\Controllers\ActivityController::class, 'store', [TenantMiddleware::class]);

// Support / Tickets routes
use App\Http\Controllers\TicketController;
$router->get('/tickets', TicketController::class, 'index', [TenantMiddleware::class]);
$router->get('/tickets/create', TicketController::class, 'create', [TenantMiddleware::class]);
$router->post('/tickets', TicketController::class, 'store', [TenantMiddleware::class]);
$router->get('/tickets/show', TicketController::class, 'show', [TenantMiddleware::class]);
$router->post('/tickets/comment', TicketController::class, 'addComment', [TenantMiddleware::class]);
$router->post('/tickets/update-status', TicketController::class, 'updateStatus', [TenantMiddleware::class]);

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

// Client Portal Routes (Public / Passwordless Access)
use App\Http\Controllers\PortalController;
$router->get('/portal', PortalController::class, 'showLogin');
$router->post('/portal/login', PortalController::class, 'authenticate');
$router->get('/portal/dashboard', PortalController::class, 'dashboard');
$router->post('/portal/ticket', PortalController::class, 'createTicket');
$router->get('/portal/logout', PortalController::class, 'logout');

