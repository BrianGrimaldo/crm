-- ============================================================
-- CRM Multi-Tenant :: Módulo CORE / GLOBAL
-- Base de datos compartida con aislamiento por tenant_id
-- MySQL 8+ | UTF8MB4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;





-- ────────────────────────────────────────────────────────────
-- 1. TENANTS (Empresas)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `tenants` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`            CHAR(36)        NOT NULL,
  `name`            VARCHAR(200)    NOT NULL,
  `slug`            VARCHAR(100)    NOT NULL,
  `tax_id`          VARCHAR(50)     DEFAULT NULL COMMENT 'RFC / NIT / Tax ID',
  `email`           VARCHAR(255)    DEFAULT NULL,
  `phone`           VARCHAR(30)     DEFAULT NULL,
  `logo_url`        VARCHAR(500)    DEFAULT NULL,
  `address`         TEXT            DEFAULT NULL,
  `timezone`        VARCHAR(60)     NOT NULL DEFAULT 'America/Mexico_City',
  `currency_code`   CHAR(3)         NOT NULL DEFAULT 'MXN',
  `plan`            ENUM('free','starter','professional','enterprise') NOT NULL DEFAULT 'free',
  `is_active`       TINYINT(1)      NOT NULL DEFAULT 1,
  `trial_ends_at`   DATETIME        DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenants_uuid` (`uuid`),
  UNIQUE KEY `uq_tenants_slug` (`slug`),
  INDEX `idx_tenants_active` (`is_active`)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 2. ROLES (globales por tenant)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `roles` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `name`        VARCHAR(80)     NOT NULL,
  `slug`        VARCHAR(80)     NOT NULL,
  `description` VARCHAR(255)    DEFAULT NULL,
  `is_system`   TINYINT(1)      NOT NULL DEFAULT 0 COMMENT 'No editable por usuario',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roles_tenant_slug` (`tenant_id`, `slug`),
  INDEX `idx_roles_tenant` (`tenant_id`),
  CONSTRAINT `fk_roles_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 3. PERMISSIONS
-- ────────────────────────────────────────────────────────────
CREATE TABLE `permissions` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `module`      VARCHAR(60)     NOT NULL COMMENT 'leads, deals, products…',
  `action`      VARCHAR(60)     NOT NULL COMMENT 'view, create, update, delete, export',
  `description` VARCHAR(255)    DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_permissions_module_action` (`module`, `action`)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 4. ROLE ↔ PERMISSION (pivote)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `role_permissions` (
  `role_id`       BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 5. USERS (globales, se asignan a tenants)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `users` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid`              CHAR(36)        NOT NULL,
  `first_name`        VARCHAR(100)    NOT NULL,
  `last_name`         VARCHAR(100)    NOT NULL,
  `email`             VARCHAR(255)    NOT NULL,
  `password_hash`     VARCHAR(255)    NOT NULL,
  `phone`             VARCHAR(30)     DEFAULT NULL,
  `avatar_url`        VARCHAR(500)    DEFAULT NULL,
  `email_verified_at` DATETIME        DEFAULT NULL,
  `is_superadmin`     TINYINT(1)      NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1)      NOT NULL DEFAULT 1,
  `last_login_at`     DATETIME        DEFAULT NULL,
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_uuid` (`uuid`),
  UNIQUE KEY `uq_users_email` (`email`),
  INDEX `idx_users_active` (`is_active`)
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 6. USER ↔ TENANT (membresía multi-empresa)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `tenant_users` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `role_id`     BIGINT UNSIGNED NOT NULL,
  `is_owner`    TINYINT(1)      NOT NULL DEFAULT 0,
  `joined_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tenant_user` (`tenant_id`, `user_id`),
  INDEX `idx_tu_user` (`user_id`),
  INDEX `idx_tu_role` (`role_id`),
  CONSTRAINT `fk_tu_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tu_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_tu_role`   FOREIGN KEY (`role_id`)   REFERENCES `roles`(`id`)   ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 7. JWT REFRESH TOKENS
-- ────────────────────────────────────────────────────────────
CREATE TABLE `refresh_tokens` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NOT NULL,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `token_hash`  VARCHAR(255)    NOT NULL,
  `expires_at`  DATETIME        NOT NULL,
  `revoked_at`  DATETIME        DEFAULT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_rt_user_tenant` (`user_id`, `tenant_id`),
  INDEX `idx_rt_token` (`token_hash`),
  CONSTRAINT `fk_rt_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_rt_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 8. AUDIT LOG
-- ────────────────────────────────────────────────────────────
CREATE TABLE `audit_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `user_id`     BIGINT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(50)     NOT NULL COMMENT 'create, update, delete, login…',
  `entity_type` VARCHAR(80)     NOT NULL,
  `entity_id`   BIGINT UNSIGNED DEFAULT NULL,
  `old_values`  JSON            DEFAULT NULL,
  `new_values`  JSON            DEFAULT NULL,
  `ip_address`  VARCHAR(45)     DEFAULT NULL,
  `user_agent`  VARCHAR(500)    DEFAULT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_tenant_entity` (`tenant_id`, `entity_type`, `entity_id`),
  INDEX `idx_audit_tenant_date` (`tenant_id`, `created_at`),
  CONSTRAINT `fk_audit_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
