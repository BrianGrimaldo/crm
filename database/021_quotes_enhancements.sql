-- ============================================================
-- CRM Multi-Tenant :: Mejoras al módulo de Cotizaciones (Deals)
-- MySQL 8+ | UTF8MB4
-- Ejecutar: 2026-08 — Nuevas columnas para analytics de cotizaciones
-- ============================================================

-- ── 1. Tiempo de vida de la cotización ──────────────────────
ALTER TABLE `deals`
    ADD COLUMN `expires_at`   DATE         DEFAULT NULL  COMMENT 'Fecha de expiración de la cotización (TTL)',
    ADD COLUMN `area`         VARCHAR(100) DEFAULT NULL  COMMENT 'Área / departamento que genera la cotización (ej. Vida, Daños, Salud)',
    ADD COLUMN `quote_date`   DATE         DEFAULT NULL  COMMENT 'Fecha en que se emitió la cotización (si difiere de created_at)';

-- Índice para consultas de cotizaciones vigentes/expiradas
ALTER TABLE `deals`
    ADD INDEX `idx_deals_expires_at`   (`tenant_id`, `expires_at`),
    ADD INDEX `idx_deals_area`         (`tenant_id`, `area`);

-- ── 2. TTL por defecto: 30 días al registrar ────────────────
-- Se puede poblar retroactivamente con:
-- UPDATE deals SET expires_at = DATE_ADD(created_at, INTERVAL 30 DAY) WHERE expires_at IS NULL;

-- ── 3. Vista auxiliar: cotizaciones del mes con estado de expiración ──
CREATE OR REPLACE VIEW `v_quotes_month_summary` AS
SELECT
    d.id,
    d.tenant_id,
    d.name,
    d.amount,
    d.area,
    d.status,
    d.created_at,
    d.expires_at,
    d.quote_date,
    d.owner_id,
    CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS owner_name,
    CASE
        WHEN d.expires_at IS NOT NULL AND d.expires_at < CURDATE() AND d.status = 'Abierto' THEN 'Expirada'
        WHEN d.status = 'Ganado'  THEN 'Concretada'
        WHEN d.status = 'Perdido' THEN 'Perdida'
        ELSE 'Vigente'
    END AS quote_status,
    DATEDIFF(IFNULL(d.expires_at, DATE_ADD(d.created_at, INTERVAL 30 DAY)), CURDATE()) AS days_remaining
FROM deals d
LEFT JOIN users u ON u.id = d.owner_id
WHERE MONTH(d.created_at) = MONTH(CURDATE())
  AND YEAR(d.created_at)  = YEAR(CURDATE());
