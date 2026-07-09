-- =============================================================
--  013_create_goals_table.sql
--  Tabla de metas mensuales/trimestrales por vendedor o equipo.
--  Sincronizado con el schema en producción (2026-07-09).
-- =============================================================

CREATE TABLE IF NOT EXISTS `goals` (
  `id`             bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id`      bigint(20) unsigned NOT NULL,
  `owner_id`       bigint(20) unsigned DEFAULT NULL COMMENT 'NULL = meta de equipo',
  `period_type`    enum('monthly','quarterly') NOT NULL DEFAULT 'monthly',
  `period_start`   date NOT NULL,
  `period_end`     date NOT NULL,
  `metric_type`    enum('sales_won','revenue_collected') NOT NULL DEFAULT 'sales_won',
  `target_amount`  decimal(18,2) NOT NULL,
  `currency_code`  char(3) NOT NULL DEFAULT 'MXN',
  `notes`          varchar(255) DEFAULT NULL,
  `created_by`     bigint(20) unsigned DEFAULT NULL,
  `created_at`     datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  -- Columna generada: convierte NULL → 0 para que el UNIQUE KEY funcione
  `owner_key`      bigint(20) unsigned GENERATED ALWAYS AS (COALESCE(`owner_id`, 0)) STORED,

  PRIMARY KEY (`id`),

  -- Unicidad: un vendedor solo puede tener UNA meta por periodo + métrica + tenant
  UNIQUE KEY `uq_goals_unique_target` (`tenant_id`, `owner_key`, `period_start`, `period_end`, `metric_type`),

  -- Índices de consulta frecuente
  KEY `idx_goals_tenant_period` (`tenant_id`, `period_start`, `period_end`),
  KEY `idx_goals_tenant_owner`  (`tenant_id`, `owner_id`),

  -- Foreign keys
  CONSTRAINT `fk_goals_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_goals_owner`   FOREIGN KEY (`owner_id`)   REFERENCES `users` (`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_goals_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)   ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice sugerido para acelerar getSalesWonBatch()
-- (tenant_id + is_won + actual_close_date ya cubierto por idx_deals_tenant_close parcialmente)
-- CREATE INDEX idx_deals_won_close ON deals (tenant_id, is_won, actual_close_date);
