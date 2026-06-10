-- ============================================================
-- CRM Multi-Tenant :: Módulo CATÁLOGO / INVENTARIO
-- MySQL 8+ | UTF8MB4
-- ============================================================


SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 14. PRODUCT CATEGORIES
-- ────────────────────────────────────────────────────────────
CREATE TABLE `product_categories` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `parent_id`   BIGINT UNSIGNED DEFAULT NULL,
  `name`        VARCHAR(150)    NOT NULL,
  `slug`        VARCHAR(150)    NOT NULL,
  `description` TEXT            DEFAULT NULL,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categ_tenant_slug` (`tenant_id`, `slug`),
  INDEX `idx_categ_tenant_parent` (`tenant_id`, `parent_id`),
  CONSTRAINT `fk_categ_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`)             ON DELETE CASCADE,
  CONSTRAINT `fk_categ_parent` FOREIGN KEY (`parent_id`) REFERENCES `product_categories`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 15. PRODUCTS
-- ────────────────────────────────────────────────────────────
CREATE TABLE `products` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `category_id`   BIGINT UNSIGNED DEFAULT NULL,
  `sku`           VARCHAR(80)     NOT NULL,
  `name`          VARCHAR(200)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `unit_price`    DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
  `cost_price`    DECIMAL(15,2)   DEFAULT NULL,
  `tax_rate`      DECIMAL(5,2)    NOT NULL DEFAULT 0.00 COMMENT '% IVA',
  `unit_of_measure` VARCHAR(30)   DEFAULT 'pieza',
  `image_url`     VARCHAR(500)    DEFAULT NULL,
  `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_products_tenant_sku` (`tenant_id`, `sku`),
  INDEX `idx_products_tenant_categ` (`tenant_id`, `category_id`),
  INDEX `idx_products_tenant_name`  (`tenant_id`, `name`),
  CONSTRAINT `fk_products_tenant` FOREIGN KEY (`tenant_id`)   REFERENCES `tenants`(`id`)             ON DELETE CASCADE,
  CONSTRAINT `fk_products_categ`  FOREIGN KEY (`category_id`) REFERENCES `product_categories`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 16. INVENTORY
-- ────────────────────────────────────────────────────────────
CREATE TABLE `inventory` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`       BIGINT UNSIGNED NOT NULL,
  `product_id`      BIGINT UNSIGNED NOT NULL,
  `warehouse`       VARCHAR(100)    NOT NULL DEFAULT 'principal',
  `quantity`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `reserved`        DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
  `reorder_level`   DECIMAL(12,2)   DEFAULT NULL,
  `last_restocked`  DATETIME        DEFAULT NULL,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inv_tenant_product_wh` (`tenant_id`, `product_id`, `warehouse`),
  INDEX `idx_inv_tenant_product` (`tenant_id`, `product_id`),
  CONSTRAINT `fk_inv_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_inv_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 17. INVENTORY MOVEMENTS
-- ────────────────────────────────────────────────────────────
CREATE TABLE `inventory_movements` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `product_id`  BIGINT UNSIGNED NOT NULL,
  `warehouse`   VARCHAR(100)    NOT NULL DEFAULT 'principal',
  `type`        ENUM('in','out','adjustment','return') NOT NULL,
  `quantity`    DECIMAL(12,2)   NOT NULL,
  `reference`   VARCHAR(200)    DEFAULT NULL COMMENT 'deal_id, ticket_id, etc.',
  `notes`       TEXT            DEFAULT NULL,
  `created_by`  BIGINT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_invmov_tenant_product` (`tenant_id`, `product_id`),
  INDEX `idx_invmov_tenant_date`    (`tenant_id`, `created_at`),
  CONSTRAINT `fk_invmov_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_invmov_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invmov_user`    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
