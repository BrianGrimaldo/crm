-- ============================================================
-- CRM Multi-Tenant :: Módulo FINANZAS Y COBRANZA
-- MySQL 8+ | UTF8MB4
-- ============================================================
-- FASE 1: Facturas vinculadas a Deals (contratos/proyectos)
-- Los datos pueden alimentarse manualmente o vía webhook.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ────────────────────────────────────────────────────────────
-- INVOICES (Facturas)
-- ────────────────────────────────────────────────────────────
-- Una factura puede estar vinculada a un deal (contrato/proyecto)
-- y a un account (cliente/empresa facturada).
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`         BIGINT UNSIGNED NOT NULL,
  `deal_id`           BIGINT UNSIGNED DEFAULT NULL  COMMENT 'Contrato/Proyecto asociado',
  `account_id`        BIGINT UNSIGNED DEFAULT NULL  COMMENT 'Empresa facturada',
  `contact_id`        BIGINT UNSIGNED DEFAULT NULL  COMMENT 'Contacto de facturación',
  `owner_id`          BIGINT UNSIGNED DEFAULT NULL  COMMENT 'Vendedor/responsable',

  -- Identificación de la factura
  `invoice_number`    VARCHAR(100)    NOT NULL       COMMENT 'Folio de factura (puede ser externo)',
  `reference`         VARCHAR(200)    DEFAULT NULL   COMMENT 'Referencia ERP/SAT/otro sistema',

  -- Montos
  `subtotal`          DECIMAL(18,2)   NOT NULL DEFAULT 0.00,
  `tax_amount`        DECIMAL(18,2)   NOT NULL DEFAULT 0.00 COMMENT 'IVA u otros impuestos',
  `total`             DECIMAL(18,2)   NOT NULL DEFAULT 0.00,
  `amount_paid`       DECIMAL(18,2)   NOT NULL DEFAULT 0.00 COMMENT 'Monto cobrado hasta ahora',
  `currency_code`     CHAR(3)         NOT NULL DEFAULT 'MXN',

  -- Fechas
  `issue_date`        DATE            NOT NULL          COMMENT 'Fecha de emisión',
  `due_date`          DATE            NOT NULL          COMMENT 'Fecha de vencimiento',
  `paid_date`         DATE            DEFAULT NULL      COMMENT 'Fecha de pago completo',

  -- Estatus
  `status`            ENUM('borrador','emitida','parcial','pagada','vencida','cancelada')
                      NOT NULL DEFAULT 'borrador'
                      COMMENT 'borrador=sin emitir, emitida=pendiente pago, parcial=pago parcial, pagada=completa, vencida=pasó fecha, cancelada',

  -- Metadatos
  `notes`             TEXT            DEFAULT NULL,
  `source`            ENUM('manual','webhook','api','erp_sync') NOT NULL DEFAULT 'manual'
                      COMMENT 'Origen del registro',
  `external_id`       VARCHAR(200)    DEFAULT NULL   COMMENT 'ID del sistema externo (ERP/SAT)',

  -- Timestamps
  `created_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_tenant_number` (`tenant_id`, `invoice_number`),
  INDEX `idx_invoices_tenant_status`  (`tenant_id`, `status`),
  INDEX `idx_invoices_tenant_deal`    (`tenant_id`, `deal_id`),
  INDEX `idx_invoices_tenant_account` (`tenant_id`, `account_id`),
  INDEX `idx_invoices_tenant_due`     (`tenant_id`, `due_date`),
  INDEX `idx_invoices_tenant_owner`   (`tenant_id`, `owner_id`),

  CONSTRAINT `fk_invoices_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_invoices_deal`    FOREIGN KEY (`deal_id`)    REFERENCES `deals`(`id`)     ON DELETE SET NULL,
  CONSTRAINT `fk_invoices_account` FOREIGN KEY (`account_id`) REFERENCES `accounts`(`id`)  ON DELETE SET NULL,
  CONSTRAINT `fk_invoices_contact` FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`)  ON DELETE SET NULL,
  CONSTRAINT `fk_invoices_owner`   FOREIGN KEY (`owner_id`)   REFERENCES `users`(`id`)     ON DELETE SET NULL
) ENGINE=InnoDB;


-- ────────────────────────────────────────────────────────────
-- INVOICE PAYMENTS (Pagos registrados)
-- ────────────────────────────────────────────────────────────
-- Cada pago parcial o total se registra como un payment.
-- Esto permite trazabilidad completa del flujo de cobranza.
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `invoice_payments` (
  `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id`       BIGINT UNSIGNED NOT NULL,
  `invoice_id`      BIGINT UNSIGNED NOT NULL,
  `amount`          DECIMAL(18,2)   NOT NULL,
  `payment_method`  ENUM('transferencia','efectivo','cheque','tarjeta','otro') DEFAULT 'transferencia',
  `payment_date`    DATE            NOT NULL,
  `reference`       VARCHAR(200)    DEFAULT NULL COMMENT 'No. de referencia bancaria',
  `notes`           TEXT            DEFAULT NULL,
  `created_by`      BIGINT UNSIGNED DEFAULT NULL,
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  INDEX `idx_payments_invoice` (`invoice_id`),
  INDEX `idx_payments_tenant`  (`tenant_id`),

  CONSTRAINT `fk_payments_tenant`  FOREIGN KEY (`tenant_id`)  REFERENCES `tenants`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`)   ON DELETE CASCADE,
  CONSTRAINT `fk_payments_user`    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)      ON DELETE SET NULL
) ENGINE=InnoDB;


-- ────────────────────────────────────────────────────────────
-- SEED: Permissions for the finance module
-- ────────────────────────────────────────────────────────────
INSERT IGNORE INTO `permissions` (`module`, `action`, `description`) VALUES
  ('finance', 'view',   'Ver módulo de finanzas y cobranza'),
  ('finance', 'create', 'Crear facturas manualmente'),
  ('finance', 'update', 'Editar facturas y registrar pagos'),
  ('finance', 'delete', 'Eliminar/cancelar facturas'),
  ('finance', 'export', 'Exportar datos de facturación');

SET FOREIGN_KEY_CHECKS = 1;
