<?php
$pageTitle = 'Dashboard Financiero - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$totalFacturado = $stats['total_facturado'] ?? 0;
$totalPorCobrar = $stats['total_por_cobrar'] ?? 0;
$totalCobrado = $stats['total_cobrado'] ?? 0;
$overdueAmount = $stats['overdue_amount'] ?? 0;

$alertCount = count($dealsWithoutInvoice) + count($overdueInvoices);
?>

<style>
/* ========== FOUNDATION ========== */
.content-area { background: var(--bg-main) !important; }

/* ========== GREETING ========== */
.dash-greeting { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; }
.dash-greeting h1 { 
    font-size: 1.8rem; 
    font-weight: 800; 
    margin: 0 0 .25rem; 
    letter-spacing: -0.03em; 
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    display: inline-block;
}
.dash-greeting p { font-size: 0.95rem; color: var(--text-muted); font-weight: 500; margin: 0; }
.header-actions { display: flex; gap: 1rem; }

/* ========== KPI STRIP ========== */
.kpi-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
@media(max-width:1200px){ .kpi-strip{ grid-template-columns: repeat(2,1fr); } }
@media(max-width:768px){ .kpi-strip{ grid-template-columns: 1fr; } }

.kpi {
    background: var(--surface); border-radius: var(--radius-lg); padding: 1.4rem;
    border: 1px solid rgba(0,0,0,0.03); position: relative; overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.kpi:hover { transform: translateY(-5px) scale(1.02); box-shadow: 0 15px 30px -8px rgba(0,0,0,.12); border-color: rgba(0,0,0,0.06); }
.kpi::before {
    content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--primary)); opacity: 0; transition: opacity 0.3s ease;
}
.kpi:hover::before { opacity: 1; }

.kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.kpi-dot { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: transform 0.3s; }
.kpi:hover .kpi-dot { transform: rotate(-5deg) scale(1.1); }
.kpi-tag { font-size: .68rem; font-weight: 800; padding: .25rem .6rem; border-radius: 8px; letter-spacing: .04em; text-transform: uppercase; }
.kpi-val { font-size: 1.8rem; font-weight: 800; line-height: 1; color: var(--text-main); letter-spacing: -.04em; margin-bottom: .3rem; }
.kpi-lbl { font-size: .85rem; font-weight: 600; color: var(--text-muted); }

/* ========== ALERTS SECTION ========== */
.alerts-banner {
    background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
    border: 1px solid #fecaca;
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1);
}
.alerts-header { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
.alerts-icon { width: 40px; height: 40px; background: #fee2e2; color: #ef4444; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.alerts-title { font-size: 1.1rem; font-weight: 800; color: #991b1b; margin: 0; }
.alerts-subtitle { font-size: 0.9rem; color: #b91c1c; margin: 0; }

.alert-item { display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; background: #fff; border-radius: 12px; margin-bottom: 0.8rem; border: 1px solid #fecdd3; transition: all 0.2s; }
.alert-item:hover { transform: translateX(5px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
.alert-item:last-child { margin-bottom: 0; }
.alert-body { flex: 1; }
.alert-heading { font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 0.2rem; }
.alert-desc { font-size: 0.85rem; color: var(--text-muted); }
.alert-action { font-size: 0.8rem; font-weight: 600; color: #2563eb; text-decoration: none; padding: 0.4rem 0.8rem; background: #eff6ff; border-radius: 8px; transition: all 0.2s; }
.alert-action:hover { background: #dbeafe; }

/* ========== GRID SYSTEM ========== */
.dash-grid { display: grid; gap: 1.5rem; margin-bottom: 1.5rem; }
.dash-grid.g-3 { grid-template-columns: repeat(3, 1fr); }
.dash-grid.g-2 { grid-template-columns: repeat(2, 1fr); }
@media(max-width:1100px){ .dash-grid.g-3, .dash-grid.g-2 { grid-template-columns: 1fr; } }

/* ========== PANEL CARD ========== */
.panel {
    background: var(--surface); border: 1px solid rgba(0,0,0,0.04); border-radius: 20px;
    padding: 1.5rem; box-shadow: var(--shadow-md); transition: all .3s; display: flex; flex-direction: column;
}
.panel:hover { box-shadow: var(--shadow-lg); }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.4rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.03); }
.panel-title { font-size: 1.05rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: .5rem; }
.panel-title-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; background: var(--primary-light); color: var(--primary); }

/* ========== LIST ROWS ========== */
.list-row { display: flex; align-items: center; gap: 1rem; padding: .8rem; border-radius: 14px; transition: all .2s ease; border: 1px solid transparent; text-decoration: none; color: inherit; }
.list-row:hover { background: #fdfdfd; border-color: rgba(0,0,0,0.03); transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
.list-pos { font-size: .95rem; font-weight: 800; color: var(--border); min-width: 24px; text-align: center; }
.list-body { flex: 1; min-width: 0; }
.list-nm { font-weight: 700; font-size: .92rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-sub { font-size: .75rem; color: var(--text-muted); font-weight: 500; margin-top: .15rem; }
.list-val { text-align: right; }
.list-amt { font-weight: 800; font-size: 1rem; color: #059669; }
.list-amt.danger { color: #ef4444; }

.status-badge { display: inline-block; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
.status-emitida { background: #dbeafe; color: #1d4ed8; }
.status-parcial { background: #fef3c7; color: #b45309; }
.status-vencida { background: #fee2e2; color: #b91c1c; }
.status-pagada { background: #dcfce7; color: #15803d; }
.status-borrador { background: var(--border); color: var(--text-main); }
.status-cancelada { background: var(--border); color: var(--text-main); }
</style>

<!-- ═══════════════ GREETING ═══════════════ -->
<div class="dash-greeting">
    <div>
        <h1>Dashboard C-Level: Finanzas</h1>
        <p>Visibilidad ejecutiva de facturación y cobranza · <?= date('d \d\e F, Y') ?></p>
    </div>
    <div class="header-actions">
        <a href="<?= url('/finanzas/facturas') ?>" class="btn btn-primary" style="background: #fff; color: var(--primary); border: 1px solid var(--border); box-shadow: var(--shadow-sm);"><i class="fas fa-list"></i> Todas las Facturas</a>
        <?php if (\App\Core\Permission::has('finance', 'create')): ?>
        <a href="<?= url('/finanzas/crear') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Factura</a>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════ ALERTS BANNER ═══════════════ -->
<?php if ($alertCount > 0): ?>
<div class="alerts-banner">
    <div class="alerts-header">
        <div class="alerts-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
            <h2 class="alerts-title"><?= $alertCount ?> Alertas Críticas (Banderas Rojas)</h2>
            <p class="alerts-subtitle">Requieren atención ejecutiva inmediata para flujo de efectivo.</p>
        </div>
    </div>
    <div class="alerts-list" style="margin-top: 1.5rem;">
        
        <?php foreach ($dealsWithoutInvoice as $deal): ?>
        <div class="alert-item">
            <div style="color: #f59e0b; font-size: 1.2rem; margin-top: 0.2rem;"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="alert-body">
                <div class="alert-heading">Contrato Ganado SIN Facturar: <?= htmlspecialchars($deal->deal_name) ?></div>
                <div class="alert-desc">
                    <strong>Cliente:</strong> <?= htmlspecialchars($deal->account_name ?? 'N/A') ?> | 
                    <strong>Responsable:</strong> <?= htmlspecialchars($deal->owner_name ?? 'N/A') ?> | 
                    <strong>Monto Contrato:</strong> $<?= number_format((float)$deal->amount, 2) ?> |
                    <strong>Ganado hace:</strong> <?= $deal->days_since_won ?> días.
                </div>
            </div>
            <?php if (\App\Core\Permission::has('finance', 'create')): ?>
            <a href="<?= url('/finanzas/crear?deal_id=' . $deal->id) ?>" class="alert-action"><i class="fas fa-plus"></i> Emitir</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <?php foreach ($overdueInvoices as $inv): ?>
        <div class="alert-item">
            <div style="color: #ef4444; font-size: 1.2rem; margin-top: 0.2rem;"><i class="fas fa-clock"></i></div>
            <div class="alert-body">
                <div class="alert-heading">Factura Vencida: #<?= htmlspecialchars($inv->invoice_number) ?> - <?= htmlspecialchars($inv->account_name ?? 'N/A') ?></div>
                <div class="alert-desc">
                    <strong>Vencimiento:</strong> <?= date('d M Y', strtotime($inv->due_date)) ?> (Hace <?= $inv->days_overdue ?> días) | 
                    <strong>Saldo Pendiente:</strong> $<?= number_format($inv->total - $inv->amount_paid, 2) ?>
                </div>
            </div>
            <a href="<?= url('/finanzas/editar?id=' . $inv->id) ?>" class="alert-action"><i class="fas fa-eye"></i> Detalle</a>
        </div>
        <?php endforeach; ?>

    </div>
</div>
<?php endif; ?>

<!-- ═══════════════ KPI STRIP ═══════════════ -->
<div class="kpi-strip">
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(99,102,241,.1);color:#6366f1;"><i class="fas fa-file-invoice"></i></div>
            <span class="kpi-tag" style="background:rgba(99,102,241,.1);color:#6366f1;">Total</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalFacturado, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Facturado Global</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="fas fa-hand-holding-usd"></i></div>
            <span class="kpi-tag" style="background:rgba(16,185,129,.1);color:#10b981;">Ingreso</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalCobrado, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Cobrado (Efectivo)</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="fas fa-hourglass-half"></i></div>
            <span class="kpi-tag" style="background:rgba(245,158,11,.1);color:#f59e0b;">CxC</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalPorCobrar, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Por Cobrar Vigente</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(239,68,68,.1);color:#ef4444;"><i class="fas fa-exclamation-circle"></i></div>
            <span class="kpi-tag" style="background:rgba(239,68,68,.1);color:#ef4444;">Riesgo</span>
        </div>
        <div class="kpi-val" style="color: #ef4444;">$<?= number_format($overdueAmount, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Vencido (Riesgo)</div>
    </div>
</div>

<!-- ═══════════════ ROW 1: Charts ═══════════════ -->
<div class="dash-grid g-2">
    <!-- Tendencia de Facturación vs Cobranza -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(16,185,129,.08);color:#10b981;"><i class="fas fa-chart-area"></i></div> Emisión vs Cobranza (6 meses)</div>
        </div>
        <div style="position:relative;height:300px;"><canvas id="financeTrendChart"></canvas></div>
    </div>
    
    <!-- Distribución de Estatus -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(168,85,247,.08);color:#a855f7;"><i class="fas fa-chart-pie"></i></div> Estado de Cartera</div>
        </div>
        <div style="position:relative;height:300px;"><canvas id="statusChart"></canvas></div>
    </div>
</div>

<!-- ═══════════════ ROW 2: Upcoming & Debtors ═══════════════ -->
<div class="dash-grid g-2">
    <!-- Próximos Vencimientos -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(59,130,246,.08);color:#3b82f6;"><i class="fas fa-calendar-alt"></i></div> Próximos Vencimientos (7 días)</div>
        </div>
        <div style="flex: 1;">
            <?php if (empty($upcomingDue)): ?>
                <div style="text-align:center; padding: 2rem; color: var(--text-muted);">
                    <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem; opacity: 0.5;"></i><br>
                    No hay facturas por vencer en los próximos 7 días.
                </div>
            <?php else: ?>
                <?php foreach ($upcomingDue as $inv): ?>
                <a href="<?= url('/finanzas/editar?id=' . $inv->id) ?>" class="list-row">
                    <div class="list-body">
                        <div class="list-nm">#<?= htmlspecialchars($inv->invoice_number) ?> - <?= htmlspecialchars($inv->account_name ?? 'N/A') ?></div>
                        <div class="list-sub">
                            Vence: <?= date('d M Y', strtotime($inv->due_date)) ?> 
                            (<?= $inv->days_until_due == 0 ? '¡Hoy!' : "en {$inv->days_until_due} días" ?>)
                        </div>
                    </div>
                    <div class="list-val">
                        <div class="list-amt">$<?= number_format($inv->total - $inv->amount_paid, 2) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Saldo Pend.</div>
                    </div>
                </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Deudores -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;"><i class="fas fa-users"></i></div> Top Cuentas por Cobrar</div>
        </div>
        <div style="flex: 1;">
            <?php if (empty($topDebtors)): ?>
                <div style="text-align:center; padding: 2rem; color: var(--text-muted);">
                    Sin deudores pendientes.
                </div>
            <?php else: ?>
                <?php foreach ($topDebtors as $i => $debtor): ?>
                <div class="list-row" style="cursor: default;">
                    <div class="list-pos"><?= $i + 1 ?></div>
                    <div class="list-body">
                        <div class="list-nm"><?= htmlspecialchars($debtor->account_name) ?></div>
                        <div class="list-sub"><?= $debtor->invoice_count ?> facturas pendientes</div>
                    </div>
                    <div class="list-val">
                        <div class="list-amt danger">$<?= number_format($debtor->outstanding_amount, 2) ?></div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Adeudo Total</div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════ CHART.JS ═══════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.font.weight = '500';
Chart.defaults.font.size = 13.5;
const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-main').trim() || '#f8fafc';
const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || 'rgba(255,255,255,0.08)';

// Trend Chart
const tCtx = document.getElementById('financeTrendChart').getContext('2d');
const months = <?= $chartMonths ?? '[]' ?>;
const emitted = <?= $chartEmitido ?? '[]' ?>;
const collected = <?= $chartCobrado ?? '[]' ?>;

new Chart(tCtx, {
    type: 'bar',
    data: {
        labels: months.length ? months : ['Sin datos'],
        datasets: [
            { label: 'Facturado', data: emitted, backgroundColor: 'rgba(99,102,241,0.8)', borderRadius: 4 },
            { label: 'Cobrado', data: collected, backgroundColor: 'rgba(16,185,129,0.8)', borderRadius: 4 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { color: textColor, font: { weight: '600', size: 13 }, usePointStyle: true } },
            tooltip: { callbacks: { label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX')}` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: textColor, font: { size: 13 } } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 12 }, callback: v => '$'+(v>=1000?(v/1000).toFixed(0)+'K':v) } }
        }
    }
});

// Donut Chart
const sCtx = document.getElementById('statusChart').getContext('2d');
const statLabels = <?= $statusLabels ?? '[]' ?>;
const statCounts = <?= $statusCounts ?? '[]' ?>;
const statColors = <?= $statusColors ?? '[]' ?>;

new Chart(sCtx, {
    type: 'doughnut',
    data: {
        labels: statLabels.length ? statLabels : ['Sin datos'],
        datasets: [{ data: statCounts.length ? statCounts : [1], backgroundColor: statColors.length ? statColors : ['var(--border)'], borderWidth: 2, hoverOffset: 4 }]
    },
    options: {
        responsive: true, maintainAspectRatio: true, cutout: '75%',
        layout: {
            padding: 10
        },
        plugins: {
            legend: { position: 'right', labels: { color: textColor, font: { weight: '600', size: 13 }, usePointStyle: true, padding: 15 } }
        }
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
