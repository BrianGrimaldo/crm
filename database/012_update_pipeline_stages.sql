-- ============================================================
-- CRM Multi-Tenant :: ACTUALIZAR PIPELINE (8 Etapas Einsur)
-- Estructura nueva del embudo de ventas
-- ============================================================
-- Script IDEMPOTENTE: se puede ejecutar múltiples veces sin problemas.
-- ============================================================

-- PASO 1: Actualizar etapas existentes por posición
UPDATE pipeline_stages SET name = 'Prospección', probability = 5, color = '#94A3B8', is_won = 0, is_lost = 0 WHERE position = 1;

UPDATE pipeline_stages SET name = 'Contacto y calificación', probability = 15, color = '#38BDF8', is_won = 0, is_lost = 0 WHERE position = 2;

UPDATE pipeline_stages SET name = 'Levantamiento', probability = 30, color = '#818CF8', is_won = 0, is_lost = 0 WHERE position = 3;

UPDATE pipeline_stages SET name = 'Propuesta / cotización', probability = 45, color = '#a855f7', is_won = 0, is_lost = 0 WHERE position = 4;

UPDATE pipeline_stages SET name = 'Negociación', probability = 65, color = '#FB923C', is_won = 0, is_lost = 0 WHERE position = 5;

UPDATE pipeline_stages SET name = 'Ganada', probability = 100, color = '#22C55E', is_won = 1, is_lost = 0 WHERE position = 6;

-- PASO 2: Insertar etapas nuevas (7, 8, 9) si no existen
INSERT INTO pipeline_stages (tenant_id, name, position, probability, is_won, is_lost, color)
SELECT t.id, 'Onboarding / entrega', 7, 0, 0, 0, '#14b8a6'
FROM tenants t
WHERE NOT EXISTS (
    SELECT 1 FROM pipeline_stages ps WHERE ps.tenant_id = t.id AND ps.position = 7
);

INSERT INTO pipeline_stages (tenant_id, name, position, probability, is_won, is_lost, color)
SELECT t.id, 'Recompra / expansión', 8, 0, 0, 0, '#f59e0b'
FROM tenants t
WHERE NOT EXISTS (
    SELECT 1 FROM pipeline_stages ps WHERE ps.tenant_id = t.id AND ps.position = 8
);

INSERT INTO pipeline_stages (tenant_id, name, position, probability, is_won, is_lost, color)
SELECT t.id, 'Perdida', 9, 0, 0, 1, '#EF4444'
FROM tenants t
WHERE NOT EXISTS (
    SELECT 1 FROM pipeline_stages ps WHERE ps.tenant_id = t.id AND ps.position = 9
);

-- Actualizar también posiciones 7, 8, 9 por si ya existen con datos viejos
UPDATE pipeline_stages SET name = 'Onboarding / entrega', probability = 0, color = '#14b8a6', is_won = 0, is_lost = 0 WHERE position = 7;
UPDATE pipeline_stages SET name = 'Recompra / expansión', probability = 0, color = '#f59e0b', is_won = 0, is_lost = 0 WHERE position = 8;
UPDATE pipeline_stages SET name = 'Perdida', probability = 0, color = '#EF4444', is_won = 0, is_lost = 1 WHERE position = 9;

-- PASO 3: Sincronizar probabilidad de deals existentes con su etapa
UPDATE deals d
JOIN pipeline_stages ps ON ps.id = d.stage_id
SET d.probability = ps.probability
WHERE d.probability IS NULL OR d.probability != ps.probability;

-- PASO 4: Sincronizar status de deals con las flags de su etapa
-- Deals en etapa Ganada (is_won=1) deben tener status='Ganado'
UPDATE deals d
JOIN pipeline_stages ps ON ps.id = d.stage_id
SET d.status = 'Ganado', d.is_won = 1
WHERE ps.is_won = 1 AND (d.status != 'Ganado' OR d.is_won != 1);

-- Deals en etapa Perdida (is_lost=1) deben tener status='Perdido'
UPDATE deals d
JOIN pipeline_stages ps ON ps.id = d.stage_id
SET d.status = 'Perdido', d.is_won = 0
WHERE ps.is_lost = 1 AND d.status != 'Perdido';

-- Deals en etapas normales deben tener status='Abierto'
UPDATE deals d
JOIN pipeline_stages ps ON ps.id = d.stage_id
SET d.status = 'Abierto'
WHERE ps.is_won = 0 AND ps.is_lost = 0 AND d.status NOT IN ('Abierto');


