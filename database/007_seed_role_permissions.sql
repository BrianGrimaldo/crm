-- ============================================================
-- CRM Multi-Tenant :: SEED PERMISOS POR ROL
-- Asigna permisos específicos a cada rol del sistema
-- Ejecutar DESPUÉS de 006_seed_data.sql
-- ============================================================

SET @tenant_id = (SELECT id FROM tenants WHERE slug = 'einsur-demo' LIMIT 1);

-- ─── Gerente de Ventas: todo en ventas, contactos, organizaciones + ver reportes ───
SET @sales_mgr_role = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND slug = 'sales-mgr' LIMIT 1);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @sales_mgr_role, p.id FROM permissions p
WHERE (p.module = 'contacts' AND p.action IN ('view','create','update','delete'))
   OR (p.module = 'accounts' AND p.action IN ('view','create','update','delete'))
   OR (p.module = 'deals'    AND p.action IN ('view','create','update','delete','export'))
   OR (p.module = 'leads'    AND p.action IN ('view','create','update','delete'))
   OR (p.module = 'reports'  AND p.action IN ('view','export'))
   OR (p.module = 'users'    AND p.action = 'view')
   OR (p.module = 'notes'    AND p.action IN ('view','create','update','delete'))
   OR (p.module = 'tasks'    AND p.action IN ('view','create','update','delete'))
   OR (p.module = 'events'   AND p.action IN ('view','create','update','delete'));

-- ─── Vendedor: CRUD en contactos/deals, solo ver organizaciones ───
SET @sales_rep_role = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND slug = 'sales-rep' LIMIT 1);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @sales_rep_role, p.id FROM permissions p
WHERE (p.module = 'contacts' AND p.action IN ('view','create','update'))
   OR (p.module = 'accounts' AND p.action = 'view')
   OR (p.module = 'deals'    AND p.action IN ('view','create','update'))
   OR (p.module = 'leads'    AND p.action IN ('view','create','update'))
   OR (p.module = 'notes'    AND p.action IN ('view','create'))
   OR (p.module = 'tasks'    AND p.action IN ('view','create','update'))
   OR (p.module = 'events'   AND p.action IN ('view','create'));

-- ─── Soporte: tickets y ver contactos ───
SET @support_role = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND slug = 'support' LIMIT 1);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @support_role, p.id FROM permissions p
WHERE (p.module = 'tickets'  AND p.action IN ('view','create','update','delete'))
   OR (p.module = 'contacts' AND p.action = 'view')
   OR (p.module = 'accounts' AND p.action = 'view')
   OR (p.module = 'notes'    AND p.action IN ('view','create'))
   OR (p.module = 'tasks'    AND p.action IN ('view','create','update'));

-- ─── Solo Lectura: solo view en todos los módulos de negocio ───
SET @readonly_role = (SELECT id FROM roles WHERE tenant_id = @tenant_id AND slug = 'read-only' LIMIT 1);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT @readonly_role, p.id FROM permissions p
WHERE p.action = 'view'
  AND p.module IN ('contacts','accounts','deals','leads','reports','notes','tasks','events');
