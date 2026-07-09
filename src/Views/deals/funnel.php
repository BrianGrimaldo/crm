<?php
$pageTitle = 'Embudo de Ventas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

// Separar etapas activas de resultados finales
$activeStages = array_filter($funnel, fn($r) => !$r->is_won && !$r->is_lost);
$wonStage = array_filter($funnel, fn($r) => $r->is_won);
$lostStage = array_filter($funnel, fn($r) => $r->is_lost);
$wonRow = !empty($wonStage) ? array_values($wonStage)[0] : null;
$lostRow = !empty($lostStage) ? array_values($lostStage)[0] : null;

// El máximo es el total de deals en la primera etapa activa
$activeStages = array_values($activeStages);
$topCount = !empty($activeStages) ? max(1, (int) $activeStages[0]->total_deals) : 1;

// KPIs
$totalActive = array_sum(array_map(fn($r) => (int) $r->total_deals, $activeStages));
$wonDeals = $wonRow ? (int) $wonRow->total_deals : 0;
$lostDeals = $lostRow ? (int) $lostRow->total_deals : 0;
$totalDeals = $totalActive + $wonDeals + $lostDeals;
$convRate = $totalDeals > 0 ? round($wonDeals / $totalDeals * 100, 1) : 0;
?>

<div class="page-header">
    <div>
        <h1>Embudo de Ventas</h1>
        <p>Visualiza en qué etapa se pierden más oportunidades.</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="<?= url('/oportunidades/pipeline') ?>" class="btn btn-outline"
            style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" style="margin-right:.5rem">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Vista Kanban
        </a>
        <a href="<?= url('/oportunidades') ?>" class="btn btn-outline"
            style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" style="margin-right:.5rem">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 10h16M4 14h16M4 18h16" />
            </svg>
            Vista Lista
        </a>
    </div>
</div>

<style>
    .funnel-wrap {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 1.5rem;
        align-items: start;
    }

    @media(max-width: 1100px) {
        .funnel-wrap {
            grid-template-columns: 1fr;
        }
    }

    .funnel-stage {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: .6rem;
    }

    .funnel-label {
        width: 130px;
        flex-shrink: 0;
        font-size: .85rem;
        font-weight: 700;
        color: var(--text-main);
        text-align: right;
    }

    .funnel-bar-wrap {
        flex: 1;
        background: rgba(128, 128, 128, .08);
        border-radius: 8px;
        height: 42px;
        overflow: hidden;
    }

    .funnel-bar {
        height: 100%;
        border-radius: 8px;
        display: flex;
        align-items: center;
        padding: 0 1rem;
        gap: .75rem;
        transition: width .7s cubic-bezier(.4, 0, .2, 1);
        min-width: 52px;
    }

    .funnel-bar-count {
        font-size: .95rem;
        font-weight: 800;
        color: #fff;
        white-space: nowrap;
    }

    .funnel-bar-amount {
        font-size: .78rem;
        font-weight: 600;
        color: rgba(255, 255, 255, .8);
        white-space: nowrap;
    }

    .funnel-pct {
        width: 48px;
        flex-shrink: 0;
        font-size: .82rem;
        font-weight: 700;
        color: var(--text-muted);
    }

    .funnel-conv {
        display: flex;
        align-items: center;
        gap: .4rem;
        padding-left: 146px;
        font-size: .76rem;
        color: var(--text-muted);
        margin-bottom: .3rem;
        margin-top: -.2rem;
    }

    /* Resultados finales */
    .funnel-results {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px dashed var(--border);
    }

    .result-box {
        border-radius: 14px;
        padding: 1.2rem 1.4rem;
        display: flex;
        flex-direction: column;
        gap: .3rem;
    }

    .result-box-won {
        background: rgba(16, 185, 129, .08);
        border: 1px solid rgba(16, 185, 129, .25);
    }

    .result-box-lost {
        background: rgba(239, 68, 68, .08);
        border: 1px solid rgba(239, 68, 68, .25);
    }

    .result-val {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -.03em;
    }

    .result-lbl {
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .result-amt {
        font-size: .85rem;
        font-weight: 700;
        margin-top: .2rem;
    }

    /* Tabla lateral */
    .funnel-table {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }

    .funnel-table-head {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        font-weight: 800;
        font-size: 1rem;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .ft-row {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: .5rem;
        align-items: center;
        padding: .85rem 1.5rem;
        border-bottom: 1px solid var(--border);
        transition: background .15s;
    }

    .ft-row:last-child {
        border-bottom: none;
    }

    .ft-row:hover {
        background: rgba(128, 128, 128, .04);
    }

    .ft-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-block;
        margin-right: .5rem;
    }

    .ft-name {
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-main);
        display: flex;
        align-items: center;
    }

    .ft-num {
        font-size: .85rem;
        font-weight: 700;
        color: var(--text-muted);
        text-align: right;
    }

    .ft-amt {
        font-size: .82rem;
        font-weight: 700;
        color: #10b981;
        background: rgba(16, 185, 129, .08);
        padding: .2rem .55rem;
        border-radius: 6px;
    }

    /* KPIs */
    .funnel-kpis {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    @media(max-width:900px) {
        .funnel-kpis {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .fkpi {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.2rem 1.4rem;
    }

    .fkpi-val {
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -.03em;
    }

    .fkpi-lbl {
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-top: .2rem;
    }
</style>

<!-- KPIs -->
<div class="funnel-kpis">
    <div class="fkpi">
        <div class="fkpi-val"><?= $totalDeals ?></div>
        <div class="fkpi-lbl">Total Oportunidades</div>
    </div>
    <div class="fkpi">
        <div class="fkpi-val"><?= $totalActive ?></div>
        <div class="fkpi-lbl">En Pipeline</div>
    </div>
    <div class="fkpi">
        <div class="fkpi-val" style="color:#10b981;"><?= $wonDeals ?></div>
        <div class="fkpi-lbl">Ganadas</div>
    </div>
    <div class="fkpi">
        <div class="fkpi-val" style="color:#6366f1;"><?= $convRate ?>%</div>
        <div class="fkpi-lbl">Tasa de Conversión</div>
    </div>
</div>

<div class="funnel-wrap">
    <!-- Embudo visual -->
    <div class="panel"
        style="background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:1.75rem;">
        <div
            style="font-weight:800;font-size:1.05rem;color:var(--text-main);margin-bottom:1.5rem;display:flex;align-items:center;gap:.5rem;">
            <span
                style="width:28px;height:28px;border-radius:8px;background:rgba(99,102,241,.1);color:#6366f1;display:flex;align-items:center;justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4h18M3 8l4 4v8l4-2 4 2V12l4-4" />
                </svg>
            </span>
            Conversión por Etapa
        </div>

        <?php
        // Lógica de acumulado para un embudo real:
        // Los deals en etapas avanzadas o ganadas tuvieron que pasar por las anteriores.
        $cumulativeActive = [];
        $runningTotal = $wonDeals; // Empezamos sumando los ganados (llegaron al final)
        
        for ($i = count($activeStages) - 1; $i >= 0; $i--) {
            $runningTotal += (int) $activeStages[$i]->total_deals;
            $cumulativeActive[$i] = $runningTotal;
        }

        // El 100% del embudo es el total de todas las oportunidades (incluyendo perdidas)
        $topCount = max(1, $totalDeals);
        $prevCount = $topCount; // Para calcular la conversión desde la etapa anterior

        foreach ($activeStages as $index => $row):
            // En vez de usar solo los deals que están parados en esta etapa, 
            // usamos el acumulado (los que llegaron aquí o más lejos).
            $count = $cumulativeActive[$index];
            $pct = round($count / $topCount * 100);
            $barW = max($pct, 3);
            $color = $row->stage_color ?: '#6366f1';
            
            // Conversión desde la etapa anterior
            $convPct = ($prevCount > 0) ? round($count / $prevCount * 100, 1) : 0;
            
            // Solo mostrar la flecha de conversión a partir de la segunda etapa
            if ($index > 0): 
            ?>
                <div class="funnel-conv">
                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                    <?= $convPct ?>% conversión desde etapa anterior
                </div>
            <?php endif; ?>

            <div class="funnel-stage">
                <div class="funnel-label"><?= htmlspecialchars($row->stage_name) ?></div>
                <div class="funnel-bar-wrap">
                    <div class="funnel-bar" style="width:<?= $barW ?>%;background:<?= htmlspecialchars($color) ?>cc;">
                        <span class="funnel-bar-count"><?= $count ?></span>
                        <span class="funnel-bar-amount" style="font-weight:400;opacity:0.8;">(Deals que llegaron aquí)</span>
                    </div>
                </div>
                <div class="funnel-pct"><?= $pct ?>%</div>
            </div>
            <?php
            $prevCount = $count;
        endforeach; ?>

        <!-- Resultados finales -->
        <div class="funnel-results">
            <div class="result-box result-box-won">
                <div class="result-val" style="color:#10b981;"><?= $wonDeals ?></div>
                <div class="result-lbl">Ganadas (Conversión final: <?= $prevCount > 0 ? round($wonDeals / $prevCount * 100, 1) : 0 ?>%)</div>
                <?php if ($wonRow && (float) $wonRow->total_amount > 0): ?>
                    <div class="result-amt" style="color:#10b981;">
                        $<?= number_format((float) $wonRow->total_amount, 0, '.', ',') ?></div>
                <?php endif; ?>
            </div>
            <div class="result-box result-box-lost">
                <div class="result-val" style="color:#ef4444;"><?= $lostDeals ?></div>
                <div class="result-lbl">Perdidas</div>
                <?php if ($lostRow && (float) $lostRow->total_amount > 0): ?>
                    <div class="result-amt" style="color:#ef4444;">
                        $<?= number_format((float) $lostRow->total_amount, 0, '.', ',') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabla lateral -->
    <div class="funnel-table">
        <div class="funnel-table-head">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" style="color:#6366f1">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Resumen por Etapa
        </div>
        <?php foreach ($activeStages as $row): ?>
            <div class="ft-row">
                <div class="ft-name">
                    <span class="ft-dot" style="background:<?= htmlspecialchars($row->stage_color ?: '#94a3b8') ?>;"></span>
                    <?= htmlspecialchars($row->stage_name) ?>
                </div>
                <div class="ft-num"><?= (int) $row->total_deals ?> deals</div>
                <div class="ft-amt">$<?= number_format((float) $row->total_amount, 0, '.', ',') ?></div>
            </div>
        <?php endforeach; ?>
        <?php if ($wonRow): ?>
            <div class="ft-row" style="background:rgba(16,185,129,.04);">
                <div class="ft-name"><span class="ft-dot" style="background:#10b981;"></span>Ganadas</div>
                <div class="ft-num"><?= $wonDeals ?> deals</div>
                <div class="ft-amt" style="color:#10b981;">$<?= number_format((float) $wonRow->total_amount, 0, '.', ',') ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($lostRow): ?>
            <div class="ft-row" style="background:rgba(239,68,68,.04);">
                <div class="ft-name"><span class="ft-dot" style="background:#ef4444;"></span>Perdidas</div>
                <div class="ft-num"><?= $lostDeals ?> deals</div>
                <div class="ft-amt" style="color:#ef4444;">$<?= number_format((float) $lostRow->total_amount, 0, '.', ',') ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
