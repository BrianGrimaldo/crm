-- ============================================================
-- FASE 1: Tipificaciones + Métricas en Tiempo Real + SLA/KPIs
-- CRM Multi-Tenant :: Einsur Global
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 1. TIPIFICACIONES — Catálogo de clasificaciones de cierre
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tipifications` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    BIGINT UNSIGNED NOT NULL,
  `name`         VARCHAR(120)    NOT NULL COMMENT 'Ej: Venta Positiva, Fin Negativo, Seguimiento',
  `slug`         VARCHAR(120)    NOT NULL,
  `color`        VARCHAR(20)     NOT NULL DEFAULT '#6366f1' COMMENT 'Color hex para badge',
  `icon`         VARCHAR(50)     NOT NULL DEFAULT 'fa-tag' COMMENT 'Icono FontAwesome',
  `description`  VARCHAR(255)    DEFAULT NULL,
  `auto_action`  ENUM('none','create_task','move_pipeline','send_template','close_ticket')
                 NOT NULL DEFAULT 'none' COMMENT 'Acción automática al tipificar',
  `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
  `position`     INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Orden de despliegue',
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tipif_tenant_slug` (`tenant_id`, `slug`),
  KEY `idx_tipif_tenant` (`tenant_id`),
  CONSTRAINT `fk_tipif_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar columna de tipificación a tickets
ALTER TABLE `tickets`
  ADD COLUMN IF NOT EXISTS `tipification_id` BIGINT UNSIGNED DEFAULT NULL AFTER `category`,
  ADD COLUMN IF NOT EXISTS `first_response_at` DATETIME DEFAULT NULL COMMENT 'Timestamp de la primera respuesta del agente',
  ADD COLUMN IF NOT EXISTS `resolved_at`       DATETIME DEFAULT NULL COMMENT 'Timestamp de resolución',
  ADD COLUMN IF NOT EXISTS `closed_at`         DATETIME DEFAULT NULL COMMENT 'Timestamp de cierre',
  ADD COLUMN IF NOT EXISTS `sla_first_response_minutes` INT DEFAULT NULL COMMENT 'Minutos hasta primera respuesta (calculado)',
  ADD COLUMN IF NOT EXISTS `sla_resolution_minutes`     INT DEFAULT NULL COMMENT 'Minutos hasta resolución (calculado)',
  ADD COLUMN IF NOT EXISTS `created_by`        BIGINT UNSIGNED DEFAULT NULL COMMENT 'Usuario que creó el ticket';

-- FK tipification_id → tipifications
-- (usamos procedimiento para evitar error si ya existe)
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_tipification` FOREIGN KEY (`tipification_id`)
  REFERENCES `tipifications`(`id`) ON DELETE SET NULL;


-- ────────────────────────────────────────────────────────────
-- 2. SESIONES DE AGENTE — Para métricas en tiempo real
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `agent_sessions` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    BIGINT UNSIGNED NOT NULL,
  `user_id`      BIGINT UNSIGNED NOT NULL,
  `status`       ENUM('online','away','busy','offline') NOT NULL DEFAULT 'online',
  `last_ping_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Último heartbeat del agente',
  `started_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at`     DATETIME        DEFAULT NULL,
  `ip_address`   VARCHAR(45)     DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_agses_tenant_user` (`tenant_id`, `user_id`),
  KEY `idx_agses_status` (`tenant_id`, `status`),
  CONSTRAINT `fk_agses_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_agses_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ────────────────────────────────────────────────────────────
-- 3. POLÍTICAS DE SLA — Configuración de niveles de servicio
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `sla_policies` (
  `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`                 BIGINT UNSIGNED NOT NULL,
  `name`                      VARCHAR(120)    NOT NULL COMMENT 'Ej: SLA Estándar, SLA Premium',
  `priority`                  ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `first_response_minutes`    INT UNSIGNED    NOT NULL DEFAULT 60  COMMENT 'Tiempo máximo para primera respuesta (min)',
  `resolution_minutes`        INT UNSIGNED    NOT NULL DEFAULT 1440 COMMENT 'Tiempo máximo para resolver (min, default 24h)',
  `is_default`                TINYINT(1)      NOT NULL DEFAULT 0,
  `is_active`                 TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`                DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sla_tenant` (`tenant_id`),
  CONSTRAINT `fk_sla_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ────────────────────────────────────────────────────────────
-- 4. SEED — Tipificaciones y SLA por defecto
-- ────────────────────────────────────────────────────────────

-- Tipificaciones predeterminadas (se insertan para todos los tenants existentes)
INSERT IGNORE INTO `tipifications` (`tenant_id`, `name`, `slug`, `color`, `icon`, `description`, `auto_action`, `position`)
SELECT t.id, vals.name, vals.slug, vals.color, vals.icon, vals.description, vals.auto_action, vals.position
FROM tenants t
CROSS JOIN (
  SELECT 'Venta Positiva'      AS name, 'venta-positiva'      AS slug, '#10b981' AS color, 'fa-check-circle'  AS icon, 'El cliente compró o cerró el trato'    AS description, 'none' AS auto_action, 1 AS position
  UNION ALL
  SELECT 'Venta Negativa',            'venta-negativa',            '#ef4444',       'fa-times-circle',  'El cliente rechazó la oferta',                 'none',              2
  UNION ALL
  SELECT 'Seguimiento Pendiente',     'seguimiento-pendiente',     '#f59e0b',       'fa-clock',         'Requiere seguimiento futuro',                  'create_task',       3
  UNION ALL
  SELECT 'Cotización Enviada',        'cotizacion-enviada',        '#3b82f6',       'fa-file-invoice',  'Se envió cotización, esperando respuesta',     'none',              4
  UNION ALL
  SELECT 'Sin Interés',               'sin-interes',               '#94a3b8',       'fa-ban',           'El cliente no mostró interés',                 'close_ticket',      5
  UNION ALL
  SELECT 'Derivado a Otra Área',      'derivado-otra-area',        '#8b5cf6',       'fa-share',         'Transferido a otro departamento',               'none',              6
  UNION ALL
  SELECT 'Problema Resuelto',         'problema-resuelto',         '#059669',       'fa-thumbs-up',     'Soporte técnico resuelto satisfactoriamente',  'close_ticket',      7
  UNION ALL
  SELECT 'Requiere Escalamiento',     'requiere-escalamiento',     '#dc2626',       'fa-exclamation-triangle', 'Necesita atención de un supervisor',    'none',              8
) AS vals;

-- Políticas SLA predeterminadas
INSERT IGNORE INTO `sla_policies` (`tenant_id`, `name`, `priority`, `first_response_minutes`, `resolution_minutes`, `is_default`)
SELECT t.id, vals.name, vals.priority, vals.frm, vals.rm, vals.is_default
FROM tenants t
CROSS JOIN (
  SELECT 'Urgente — Respuesta Inmediata' AS name, 'urgent' AS priority, 15  AS frm, 240   AS rm, 0 AS is_default
  UNION ALL
  SELECT 'Alta — Respuesta Rápida',               'high',                 30,       480,          0
  UNION ALL
  SELECT 'Media — Estándar',                       'medium',               60,       1440,         1
  UNION ALL
  SELECT 'Baja — Sin Urgencia',                    'low',                  120,      2880,         0
) AS vals;


SET FOREIGN_KEY_CHECKS = 1;
