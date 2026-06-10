-- ============================================================
-- CRM Multi-Tenant :: Módulo ACTIVIDADES (Polimórfico)
-- MySQL 8+ | UTF8MB4
-- ============================================================


SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- 20. TASKS (Tareas vinculadas a cualquier entidad)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `tasks` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `assigned_to`   BIGINT UNSIGNED DEFAULT NULL,
  `created_by`    BIGINT UNSIGNED DEFAULT NULL,
  `related_type`  VARCHAR(60)     DEFAULT NULL COMMENT 'lead, deal, account, contact, ticket',
  `related_id`    BIGINT UNSIGNED DEFAULT NULL,
  `title`         VARCHAR(255)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `priority`      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `status`        ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `due_date`      DATETIME        DEFAULT NULL,
  `completed_at`  DATETIME        DEFAULT NULL,
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_tasks_tenant_assigned` (`tenant_id`, `assigned_to`),
  INDEX `idx_tasks_tenant_status`   (`tenant_id`, `status`),
  INDEX `idx_tasks_tenant_related`  (`tenant_id`, `related_type`, `related_id`),
  INDEX `idx_tasks_tenant_due`      (`tenant_id`, `due_date`),
  CONSTRAINT `fk_tasks_tenant`   FOREIGN KEY (`tenant_id`)   REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tasks_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`)   ON DELETE SET NULL,
  CONSTRAINT `fk_tasks_creator`  FOREIGN KEY (`created_by`)  REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 21. EVENTS / CALENDAR
-- ────────────────────────────────────────────────────────────
CREATE TABLE `events` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`     BIGINT UNSIGNED NOT NULL,
  `organizer_id`  BIGINT UNSIGNED DEFAULT NULL,
  `related_type`  VARCHAR(60)     DEFAULT NULL,
  `related_id`    BIGINT UNSIGNED DEFAULT NULL,
  `title`         VARCHAR(255)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `location`      VARCHAR(255)    DEFAULT NULL,
  `start_at`      DATETIME        NOT NULL,
  `end_at`        DATETIME        NOT NULL,
  `is_all_day`    TINYINT(1)      NOT NULL DEFAULT 0,
  `reminder_minutes` SMALLINT UNSIGNED DEFAULT NULL,
  `color`         CHAR(7)         DEFAULT '#6366F1',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_events_tenant_dates`    (`tenant_id`, `start_at`, `end_at`),
  INDEX `idx_events_tenant_org`      (`tenant_id`, `organizer_id`),
  INDEX `idx_events_tenant_related`  (`tenant_id`, `related_type`, `related_id`),
  CONSTRAINT `fk_events_tenant` FOREIGN KEY (`tenant_id`)    REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_events_org`    FOREIGN KEY (`organizer_id`) REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 22. EVENT ATTENDEES
-- ────────────────────────────────────────────────────────────
CREATE TABLE `event_attendees` (
  `event_id`  BIGINT UNSIGNED NOT NULL,
  `user_id`   BIGINT UNSIGNED NOT NULL,
  `status`    ENUM('pending','accepted','declined','tentative') NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`event_id`, `user_id`),
  CONSTRAINT `fk_ea_event` FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ea_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 23. NOTES (Polimórficas)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `notes` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    BIGINT UNSIGNED NOT NULL,
  `user_id`      BIGINT UNSIGNED DEFAULT NULL,
  `related_type` VARCHAR(60)     NOT NULL COMMENT 'lead, deal, account, contact, ticket',
  `related_id`   BIGINT UNSIGNED NOT NULL,
  `title`        VARCHAR(200)    DEFAULT NULL,
  `body`         TEXT            NOT NULL,
  `is_pinned`    TINYINT(1)      NOT NULL DEFAULT 0,
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_notes_tenant_related` (`tenant_id`, `related_type`, `related_id`),
  INDEX `idx_notes_tenant_user`    (`tenant_id`, `user_id`),
  CONSTRAINT `fk_notes_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notes_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB;

-- ────────────────────────────────────────────────────────────
-- 24. FILE ATTACHMENTS (Polimórficas)
-- ────────────────────────────────────────────────────────────
CREATE TABLE `attachments` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`    BIGINT UNSIGNED NOT NULL,
  `uploaded_by`  BIGINT UNSIGNED DEFAULT NULL,
  `related_type` VARCHAR(60)     NOT NULL,
  `related_id`   BIGINT UNSIGNED NOT NULL,
  `file_name`    VARCHAR(255)    NOT NULL,
  `file_path`    VARCHAR(500)    NOT NULL,
  `mime_type`    VARCHAR(100)    DEFAULT NULL,
  `file_size`    INT UNSIGNED    DEFAULT NULL COMMENT 'bytes',
  `created_at`   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_attach_tenant_related` (`tenant_id`, `related_type`, `related_id`),
  CONSTRAINT `fk_attach_tenant` FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attach_user`   FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
