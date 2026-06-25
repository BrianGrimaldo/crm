<?php
$pageTitle = 'Dashboard de Cobranza - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<style>
/* ========== FOUNDATION ========== */
.content-area { background: var(--bg-main) !important; }

/* ========== GREETING ========== */
.dash-greeting { margin-bottom: 2rem; }
.dash-greeting h1 { 
    font-size: 1.8rem; 
    font-weight: 800; 
    margin: 0 0 .25rem; 
    letter-spacing: -0.03em; 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}
.dash-greeting p { font-size: 0.95rem; color: var(--text-muted); font-weight: 500; margin: 0; }

/* ========== KPI STRIP ========== */
.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
@media(max-width:1200px){ .kpi-strip{ grid-template-columns: repeat(2,1fr); } }
@media(max-width:600px){ .kpi-strip{ grid-template-columns: 1fr; } }

.kpi {
    background: var(--surface); border-radius: var(--radius-lg); padding: 1.4rem;
    border: 1px solid rgba(0,0,0,0.03); position: relative; overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.kpi:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 15px 30px -8px rgba(0,0,0,.12); border-color: rgba(0,0,0,0.06); }

.kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.kpi-dot { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: transform 0.3s; }
.kpi:hover .kpi-dot { transform: rotate(-5deg) scale(1.1); }
.kpi-tag { font-size: .68rem; font-weight: 800; padding: .25rem .6rem; border-radius: 8px; letter-spacing: .04em; text-transform: uppercase; }
.kpi-val { font-size: 1.8rem; font-weight: 800; line-height: 1; color: var(--text-main); letter-spacing: -.04em; margin-bottom: .3rem; }
.kpi-lbl { font-size: .85rem; font-weight: 600; color: var(--text-muted); }

/* ========== GRID SYSTEM ========== */
.dash-grid { display: grid; gap: 1.5rem; margin-bottom: 1.5rem; }
.dash-grid.g-2 { grid-template-columns: 2fr 1fr; }
@media(max-width:1000px){ .dash-grid.g-2 { grid-template-columns: 1fr; } }

/* ========== PANEL CARD ========== */
.panel {
    background: var(--surface); border: 1px solid rgba(0,0,0,0.04); border-radius: 20px;
    padding: 1.5rem; box-shadow: var(--shadow-md); transition: all .3s;
}
.panel:hover { box-shadow: var(--shadow-lg); }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.4rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.03); }
.panel-title { font-size: 1.05rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: .5rem; }
.panel-title-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; background: var(--primary-light); color: var(--primary); }
</style>

<!-- GREETING -->
<div class="dash-greeting">
    <h1><?= isset($isCEO) && $isCEO ? 'Dashboard Directivo Financiero' : 'Portal de Cobranza' ?></h1>
    <p>Resumen Financiero de <?= htmlspecialchars($tenantName) ?> · <?= date('d \d\e F, Y') ?></p>
</div>

<!-- KPI STRIP -->
<div class="kpi-strip">
    <div class="kpi" style="border-top: 4px solid #10b981;">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="fas fa-check-circle"></i></div>
            <span class="kpi-tag" style="background:rgba(16,185,129,.1);color:#10b981;">Liquidez</span>
        </div>
        <div class="kpi-val">$<?= number_format($financeStats['total_cobrado'] ?? 0, 2, '.', ',') ?></div>
        <div class="kpi-lbl">Total Cobrado (Histórico)</div>
    </div>
    
    <div class="kpi" style="border-top: 4px solid #3b82f6;">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(59,130,246,.1);color:#3b82f6;"><i class="fas fa-file-invoice-dollar"></i></div>
            <span class="kpi-tag" style="background:rgba(59,130,246,.1);color:#3b82f6;"><?= $financeStats['pending_count'] ?? 0 ?> vigentes</span>
        </div>
        <div class="kpi-val">$<?= number_format($financeStats['total_por_cobrar'] ?? 0, 2, '.', ',') ?></div>
        <div class="kpi-lbl">Total Pendiente por Cobrar</div>
    </div>

    <div class="kpi" style="border-top: 4px solid #ef4444;">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(239,68,68,.1);color:#ef4444;"><i class="fas fa-exclamation-circle"></i></div>
            <span class="kpi-tag" style="background:rgba(239,68,68,.1);color:#ef4444;"><?= $financeStats['overdue_count'] ?? 0 ?> facturas</span>
        </div>
        <div class="kpi-val" style="color: #ef4444;">$<?= number_format($financeStats['overdue_amount'] ?? 0, 2, '.', ',') ?></div>
        <div class="kpi-lbl">Cartera Vencida</div>
    </div>

    <div class="kpi" style="border-top: 4px solid #a855f7;">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(168,85,247,.1);color:#a855f7;"><i class="fas fa-receipt"></i></div>
            <span class="kpi-tag" style="background:rgba(168,85,247,.1);color:#a855f7;">Total</span>
        </div>
        <div class="kpi-val">$<?= number_format($financeStats['total_facturado'] ?? 0, 2, '.', ',') ?></div>
        <div class="kpi-lbl">Total Facturado</div>
    </div>
</div>

<!-- CHARTS -->
<div class="dash-grid g-2">
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(59,130,246,.1);color:#3b82f6;"><i class="fas fa-chart-area"></i></div> Tendencia de Emisión vs Cobranza (Últimos 6 Meses)</div>
        </div>
        <div style="position:relative;height:300px;"><canvas id="financeTrendChart"></canvas></div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="fas fa-chart-pie"></i></div> Estado de Facturas</div>
        </div>
        <div style="position:relative;height:300px;"><canvas id="financeStatusChart"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Definición global de colores (soporte dark mode)
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Outfit', sans-serif";

    // 1. Gráfica de Tendencia (Líneas / Área)
    const trendCtx = document.getElementById('financeTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= $chartMonthLabels ?>,
                datasets: [
                    {
                        label: 'Total Facturado Emitido ($)',
                        data: <?= $chartEmitido ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#3b82f6',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Total Cobrado ($)',
                        data: <?= $chartCobrado ?>,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                    tooltip: { mode: 'index', intersect: false, padding: 12, cornerRadius: 8 }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        beginAtZero: true, 
                        grid: { color: gridColor },
                        ticks: { callback: function(value) { return '$' + (value/1000) + 'k'; } }
                    }
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false }
            }
        });
    }

    // 2. Gráfica de Distribución (Donut)
    const statusCtx = document.getElementById('financeStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $donutLabels ?>,
                datasets: [{
                    data: <?= $donutAmounts ?>,
                    backgroundColor: <?= $donutColors ?>,
                    borderWidth: isDark ? 2 : 0,
                    borderColor: isDark ? '#1e293b' : '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
                    tooltip: {
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.raw);
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
