-- ============================================================
-- CRM Multi-Tenant :: SEED DATA (Datos iniciales)
-- MySQL 8+ | UTF8MB4
-- ============================================================



-- ─── Permisos base ──────────────────────────────────────────
INSERT INTO `permissions` (`module`, `action`, `description`) VALUES
-- Leads
('leads', 'view',   'Ver prospectos'),
('leads', 'create', 'Crear prospectos'),
('leads', 'update', 'Editar prospectos'),
('leads', 'delete', 'Eliminar prospectos'),
('leads', 'export', 'Exportar prospectos'),
-- Accounts
('accounts', 'view',   'Ver cuentas'),
('accounts', 'create', 'Crear cuentas'),
('accounts', 'update', 'Editar cuentas'),
('accounts', 'delete', 'Eliminar cuentas'),
('accounts', 'export', 'Exportar cuentas'),
-- Contacts
('contacts', 'view',   'Ver contactos'),
('contacts', 'create', 'Crear contactos'),
('contacts', 'update', 'Editar contactos'),
('contacts', 'delete', 'Eliminar contactos'),
-- Deals
('deals', 'view',   'Ver oportunidades'),
('deals', 'create', 'Crear oportunidades'),
('deals', 'update', 'Editar oportunidades'),
('deals', 'delete', 'Eliminar oportunidades'),
('deals', 'export', 'Exportar oportunidades'),
-- Products
('products', 'view',   'Ver productos'),
('products', 'create', 'Crear productos'),
('products', 'update', 'Editar productos'),
('products', 'delete', 'Eliminar productos'),
-- Inventory
('inventory', 'view',   'Ver inventario'),
('inventory', 'manage', 'Gestionar movimientos de inventario'),
-- Tickets
('tickets', 'view',   'Ver solicitudes'),
('tickets', 'create', 'Crear solicitudes'),
('tickets', 'update', 'Editar solicitudes'),
('tickets', 'delete', 'Eliminar solicitudes'),
-- Tasks
('tasks', 'view',   'Ver tareas'),
('tasks', 'create', 'Crear tareas'),
('tasks', 'update', 'Editar tareas'),
('tasks', 'delete', 'Eliminar tareas'),
-- Events
('events', 'view',   'Ver eventos'),
('events', 'create', 'Crear eventos'),
('events', 'update', 'Editar eventos'),
('events', 'delete', 'Eliminar eventos'),
-- Notes
('notes', 'view',   'Ver notas'),
('notes', 'create', 'Crear notas'),
('notes', 'update', 'Editar notas'),
('notes', 'delete', 'Eliminar notas'),
-- Reports
('reports', 'view',   'Ver reportes'),
('reports', 'export', 'Exportar reportes'),
-- Settings
('settings', 'view',   'Ver configuración'),
('settings', 'update', 'Modificar configuración'),
-- Users
('users', 'view',   'Ver usuarios'),
('users', 'create', 'Crear usuarios'),
('users', 'update', 'Editar usuarios'),
('users', 'delete', 'Eliminar usuarios');

-- ─── Tenant de demostración ─────────────────────────────────
INSERT INTO `tenants` (`uuid`, `name`, `slug`, `tax_id`, `email`, `plan`, `is_active`) VALUES
(UUID(), 'Einsur Global Demo', 'einsur-demo', 'XAXX010101000', 'admin@einsurglobal.com', 'professional', 1);

SET @tenant_id = LAST_INSERT_ID();

-- ─── Roles del sistema ──────────────────────────────────────
INSERT INTO `roles` (`tenant_id`, `name`, `slug`, `description`, `is_system`) VALUES
(@tenant_id, 'Super Administrador', 'super-admin', 'Acceso total al sistema',         1),
(@tenant_id, 'Gerente de Ventas',   'sales-mgr',   'Gestiona equipo de ventas',       1),
(@tenant_id, 'Vendedor',            'sales-rep',   'Operaciones de venta básicas',    1),
(@tenant_id, 'Soporte',             'support',     'Gestión de tickets y soporte',    1),
(@tenant_id, 'Solo Lectura',        'read-only',   'Acceso de solo lectura',          1);

-- Asignar TODOS los permisos al Super Admin
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.tenant_id = @tenant_id AND r.slug = 'super-admin';

-- ─── Usuario administrador (password: Admin@2026!) ──────────
INSERT INTO `users` (`uuid`, `first_name`, `last_name`, `email`, `password_hash`, `is_superadmin`, `is_active`, `email_verified_at`) VALUES
(UUID(), 'Admin', 'Sistema', 'admin@einsurglobal.com',
 '$2y$12$LJ3m5RqLfIei7N8F/dO8XeWMbW1Hk8yP1M8GCNB5JxZ1S0FsRK0G6',
 1, 1, NOW());

SET @admin_id = LAST_INSERT_ID();
SET @admin_role = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND slug = 'super-admin' LIMIT 1);

INSERT INTO `tenant_users` (`tenant_id`, `user_id`, `role_id`, `is_owner`) VALUES
(@tenant_id, @admin_id, @admin_role, 1);

-- ─── Pipeline por defecto ───────────────────────────────────
INSERT INTO `pipeline_stages` (`tenant_id`, `name`, `position`, `probability`, `is_won`, `is_lost`, `color`) VALUES
(@tenant_id, 'Prospección',              1,   5, 0, 0, '#94A3B8'),
(@tenant_id, 'Contacto y calificación',  2,  15, 0, 0, '#38BDF8'),
(@tenant_id, 'Levantamiento',            3,  30, 0, 0, '#818CF8'),
(@tenant_id, 'Propuesta / cotización',   4,  45, 0, 0, '#a855f7'),
(@tenant_id, 'Negociación',              5,  65, 0, 0, '#FB923C'),
(@tenant_id, 'Ganada',                   6, 100, 1, 0, '#22C55E'),
(@tenant_id, 'Onboarding / entrega',     7,   0, 0, 0, '#14b8a6'),
(@tenant_id, 'Recompra / expansión',     8,   0, 0, 0, '#f59e0b'),
(@tenant_id, 'Perdida',                  9,   0, 0, 1, '#EF4444');
