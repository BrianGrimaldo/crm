-- ─────────────────────────────────────────────────────────
-- Historial de revisiones de factura
-- Cada vez que se reemplaza el folio/PDF de una factura
-- (típicamente al registrar un pago parcial), la versión
-- anterior se archiva aquí.
-- ─────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS invoice_revisions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    invoice_id      BIGINT UNSIGNED NOT NULL,
    invoice_number  VARCHAR(100)    NOT NULL COMMENT 'Folio que tenía la factura antes del reemplazo',
    pdf_path        VARCHAR(500)    DEFAULT NULL COMMENT 'Ruta del PDF archivado',
    replaced_at     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    replaced_by     BIGINT UNSIGNED DEFAULT NULL COMMENT 'ID del usuario que hizo el reemplazo',
    notes           TEXT            DEFAULT NULL,

    INDEX idx_rev_invoice (invoice_id),
    INDEX idx_rev_tenant  (tenant_id),
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
