-- ============================================================
-- CRM Multi-Tenant :: Módulo SOPORTE / TICKETS
-- MySQL 8+ | UTF8MB4
-- ============================================================


SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 18. TICKETS (Solicitudes de soporte)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `tickets` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `contact_id`    BIGINT UNSIGNED DEFAULT NULL,
  `account_id`    BIGINT UNSIGNED DEFAULT NULL,
  `assigned_to`   BIGINT UNSIGNED DEFAULT NULL,
  `subject`       VARCHAR(255)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `priority`      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status`        ENUM('open','in_progress','waiting','resolved','closed') NOT NULL DEFAULT 'open',
  `channel`       ENUM('email','phone','web','chat','social') DEFAULT 'web',
  `category`      VARCHAR(100)    DEFAULT NULL,
  `resolution`    TEXT            DEFAULT NULL,
  `due_date`      DATETIME        DEFAULT NULL,
  `first_response_at` DATETIME    DEFAULT NULL,
  `resolved_at`   DATETIME        DEFAULT NULL,
  `closed_at`     DATETIME        DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tickets_tenant_status`   (`tenant_id`, `status`),
  INDEX `idx_tickets_tenant_assigned` (`tenant_id`, `assigned_to`),
  INDEX `idx_tickets_tenant_priority` (`tenant_id`, `priority`),
  INDEX `idx_tickets_tenant_contact`  (`tenant_id`, `contact_id`),
  INDEX `idx_tickets_tenant_date`     (`tenant_id`, `created_at`),
  CONSTRAINT `fk_tickets_tenant`   FOREIGN KEY (`tenant_id`)   REFERENCES `tenants`(`id`)  ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_contact`  FOREIGN KEY (`contact_id`)  REFERENCES `contacts`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_account`  FOREIGN KEY (`account_id`)  REFERENCES `accounts`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tickets_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 19. TICKET COMMENTS
-- ────────────────────────────────────────────────────────────
CREATE TABLE `ticket_comments` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`   BIGINT UNSIGNED NOT NULL,
  `ticket_id`   BIGINT UNSIGNED NOT NULL,
  `user_id`     BIGINT UNSIGNED DEFAULT NULL,
  `body`        TEXT            NOT NULL,
  `is_internal` TINYINT(1)      NOT NULL DEFAULT 0 COMMENT 'Nota interna vs respuesta al cliente',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tcomm_tenant_ticket` (`tenant_id`, `ticket_id`),
  CONSTRAINT `fk_tcomm_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tcomm_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tcomm_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
