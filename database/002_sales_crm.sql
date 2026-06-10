-- ============================================================
-- CRM Multi-Tenant :: Módulo VENTAS / CRM
-- MySQL 8+ | UTF8MB4
-- ============================================================


SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 9. LEADS (Prospectos)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `leads` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `owner_id`      BIGINT UNSIGNED DEFAULT NULL COMMENT 'Usuario responsable',
  `first_name`    VARCHAR(100)    NOT NULL,
  `last_name`     VARCHAR(100)    DEFAULT NULL,
  `email`         VARCHAR(255)    DEFAULT NULL,
  `phone`         VARCHAR(30)     DEFAULT NULL,
  `company_name`  VARCHAR(200)    DEFAULT NULL,
  `job_title`     VARCHAR(150)    DEFAULT NULL,
  `source`        ENUM('web','referral','cold_call','social','advertisement','trade_show','other') DEFAULT 'other',
  `status`        ENUM('new','contacted','qualified','unqualified','converted') NOT NULL DEFAULT 'new',
  `score`         SMALLINT UNSIGNED DEFAULT 0,
  `estimated_value` DECIMAL(15,2) DEFAULT NULL,
  `notes`         TEXT            DEFAULT NULL,
  `converted_at`  DATETIME        DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_leads_tenant_status` (`tenant_id`, `status`),
  INDEX `idx_leads_tenant_email`  (`tenant_id`, `email`),
  INDEX `idx_leads_tenant_owner`  (`tenant_id`, `owner_id`),
  INDEX `idx_leads_tenant_source` (`tenant_id`, `source`),
  CONSTRAINT `fk_leads_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leads_owner`  FOREIGN KEY (`owner_id`)  REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 10. ACCOUNTS (Cuentas / Clientes B2B)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `accounts` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `owner_id`      BIGINT UNSIGNED DEFAULT NULL,
  `priority`      VARCHAR(10)     DEFAULT 'B',
  `name`          VARCHAR(200)    NOT NULL,
  `industry`      VARCHAR(100)    DEFAULT NULL,
  `website`       VARCHAR(255)    DEFAULT NULL,
  `linkedin`      VARCHAR(255)    DEFAULT NULL,
  `phone`         VARCHAR(30)     DEFAULT NULL,
  `email`         VARCHAR(255)    DEFAULT NULL,
  `tax_id`        VARCHAR(50)     DEFAULT NULL,
  `annual_revenue` DECIMAL(18,2)  DEFAULT NULL,
  `employees`     INT UNSIGNED    DEFAULT NULL,
  `billing_address`  TEXT         DEFAULT NULL,
  `shipping_address` TEXT         DEFAULT NULL,
  `country`       VARCHAR(100)    DEFAULT NULL,
  `city`          VARCHAR(100)    DEFAULT NULL,
  `postal_code`   VARCHAR(20)     DEFAULT NULL,
  `notes`         TEXT            DEFAULT NULL,
  `type`          ENUM('customer','partner','vendor','competitor','other') DEFAULT 'customer',
  `is_active`     TINYINT(1)      NOT NULL DEFAULT 1,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_accounts_tenant_name`  (`tenant_id`, `name`),
  INDEX `idx_accounts_tenant_owner` (`tenant_id`, `owner_id`),
  INDEX `idx_accounts_tenant_type`  (`tenant_id`, `type`),
  CONSTRAINT `fk_accounts_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_accounts_owner`  FOREIGN KEY (`owner_id`)  REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 11. CONTACTS
-- ────────────────────────────────────────────────────────────
CREATE TABLE `contacts` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `account_id`    BIGINT UNSIGNED DEFAULT NULL,
  `owner_id`      BIGINT UNSIGNED DEFAULT NULL,
  `type`          VARCHAR(50)     DEFAULT 'Prospecto',
  `first_name`    VARCHAR(100)    NOT NULL,
  `last_name`     VARCHAR(100)    DEFAULT NULL,
  `email`         VARCHAR(255)    DEFAULT NULL,
  `phone`         VARCHAR(30)     DEFAULT NULL,
  `mobile`        VARCHAR(30)     DEFAULT NULL,
  `job_title`     VARCHAR(150)    DEFAULT NULL,
  `department`    VARCHAR(100)    DEFAULT NULL,
  `linkedin`      VARCHAR(255)    DEFAULT NULL,
  `is_primary`    TINYINT(1)      NOT NULL DEFAULT 0,
  `date_of_birth` DATE            DEFAULT NULL,
  `address`       TEXT            DEFAULT NULL,
  `country`       VARCHAR(100)    DEFAULT NULL,
  `city`          VARCHAR(100)    DEFAULT NULL,
  `postal_code`   VARCHAR(20)     DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_contacts_tenant_email`   (`tenant_id`, `email`),
  INDEX `idx_contacts_tenant_account` (`tenant_id`, `account_id`),
  INDEX `idx_contacts_tenant_owner`   (`tenant_id`, `owner_id`),
  CONSTRAINT `fk_contacts_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_contacts_account` FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contacts_owner`   FOREIGN KEY (`owner_id`)   REFERENCES `users`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 12. PIPELINE STAGES (Etapas del embudo)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `pipeline_stages` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `name`        VARCHAR(100)    NOT NULL,
  `position`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `probability` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0-100 %',
  `is_won`      TINYINT(1)      NOT NULL DEFAULT 0,
  `is_lost`     TINYINT(1)      NOT NULL DEFAULT 0,
  `color`       CHAR(7)         DEFAULT '#3B82F6',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pipeline_tenant_name` (`tenant_id`, `name`),
  INDEX `idx_pipeline_tenant_pos` (`tenant_id`, `position`),
  CONSTRAINT `fk_pipeline_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 13. DEALS / OPPORTUNITIES (Oportunidades)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `deals` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `account_id`    BIGINT UNSIGNED DEFAULT NULL,
  `contact_id`    BIGINT UNSIGNED DEFAULT NULL,
  `owner_id`      BIGINT UNSIGNED DEFAULT NULL,
  `stage_id`      BIGINT UNSIGNED NOT NULL,
  `status`        VARCHAR(50)     DEFAULT 'Abierto',
  `name`          VARCHAR(200)    NOT NULL,
  `amount`        DECIMAL(18,2)   DEFAULT NULL,
  `currency_code` CHAR(3)         NOT NULL DEFAULT 'MXN',
  `probability`   TINYINT UNSIGNED DEFAULT NULL,
  `expected_close_date` DATE      DEFAULT NULL,
  `actual_close_date`   DATE      DEFAULT NULL,
  `source`        VARCHAR(100)    DEFAULT NULL,
  `description`   TEXT            DEFAULT NULL,
  `is_won`        TINYINT(1)      DEFAULT NULL,
  `lost_reason`   VARCHAR(255)    DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_deals_tenant_stage`   (`tenant_id`, `stage_id`),
  INDEX `idx_deals_tenant_owner`   (`tenant_id`, `owner_id`),
  INDEX `idx_deals_tenant_account` (`tenant_id`, `account_id`),
  INDEX `idx_deals_tenant_close`   (`tenant_id`, `expected_close_date`),
  CONSTRAINT `fk_deals_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`)          ON DELETE CASCADE,
  CONSTRAINT `fk_deals_account` FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`)         ON DELETE SET NULL,
  CONSTRAINT `fk_deals_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`)         ON DELETE SET NULL,
  CONSTRAINT `fk_deals_owner`   FOREIGN KEY (`owner_id`)   REFERENCES `users`(`id`)            ON DELETE SET NULL,
  CONSTRAINT `fk_deals_stage`   FOREIGN KEY (`stage_id`)   REFERENCES `pipeline_stages`(`id`)  ON DELETE RESTRICT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
