<?php
$pageTitle = 'Dashboard SLA - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$total = $globalStats->total_tickets ?? 0;
$frPct = $total > 0 ? round(($globalStats->met_first_response_sla / $total) * 100) : 0;
$resPct = $total > 0 ? round(($globalStats->met_resolution_sla / $total) * 100) : 0;
?>

<div class="page-header" style="margin-bottom: 2rem;">
    <div>
        <h1>Dashboard SLA</h1>
        <p>Cumplimiento de acuerdos de nivel de servicio (últimos 30 días).</p>
    </div>
</div>

<style>
.sla-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.03);
}
.sla-pct {
    font-size: 2.5rem;
    font-weight: 800;
}
.pct-good { color: #10b981; }
.pct-warn { color: #f59e0b; }
.pct-bad { color: #ef4444; }
.sla-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
}
.prog-bg {
    height: 8px; background: rgba(0,0,0,0.05); border-radius: 4px; overflow: hidden; margin-top: 1rem;
}
</style>

<div class="dash-grid g-2" style="margin-bottom: 2rem;">
    <div class="sla-card">
        <div class="sla-title">Cumplimiento 1ra Respuesta (< 1h)</div>
        <div class="sla-pct <?= $frPct >= 90 ? 'pct-good' : ($frPct >= 75 ? 'pct-warn' : 'pct-bad') ?>">
            <?= $frPct ?>%
        </div>
        <div class="prog-bg">
            <div style="height: 100%; width: <?= $frPct ?>%; background: <?= $frPct >= 90 ? '#10b981' : '#f59e0b' ?>;"></div>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
            Promedio: <?= round(($globalStats->avg_first_response ?? 0)) ?> min
        </p>
    </div>

    <div class="sla-card">
        <div class="sla-title">Cumplimiento Resolución (< 24h)</div>
        <div class="sla-pct <?= $resPct >= 90 ? 'pct-good' : ($resPct >= 75 ? 'pct-warn' : 'pct-bad') ?>">
            <?= $resPct ?>%
        </div>
        <div class="prog-bg">
            <div style="height: 100%; width: <?= $resPct ?>%; background: <?= $resPct >= 90 ? '#10b981' : '#f59e0b' ?>;"></div>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
            Promedio: <?= round(($globalStats->avg_resolution ?? 0) / 60) ?> horas
        </p>
    </div>
</div>

<div class="panel">
    <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--primary);">Desempeño por Agente</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Agente</th>
                    <th style="text-align: right;">Tickets Resueltos</th>
                    <th style="text-align: right;">Promedio 1ra Respuesta</th>
                    <th style="text-align: right;">Promedio Resolución</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($agentStats)): ?>
                    <tr><td colspan="4" style="text-align:center; color: var(--text-muted); padding: 1.5rem;">Sin datos</td></tr>
                <?php else: ?>
                    <?php foreach($agentStats as $ag): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($ag->name) ?></strong></td>
                        <td style="text-align: right; font-weight: 700;"><?= $ag->resolved_tickets ?></td>
                        <td style="text-align: right;"><?= round($ag->avg_response ?? 0) ?> min</td>
                        <td style="text-align: right;"><?= round(($ag->avg_resolution ?? 0) / 60, 1) ?> horas</td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
