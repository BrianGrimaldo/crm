<?php
$pageTitle = 'Analíticas y Gráficas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    .analytics-grid {
        display: grid;
        gap: 2rem;
        margin-bottom: 2.5rem;
    }
    .grid-2-1 {
        grid-template-columns: 2fr 1fr;
    }
    .grid-1-1 {
        grid-template-columns: 1fr 1fr;
    }
    .grid-full {
        grid-template-columns: 1fr;
    }
    
    @media(max-width: 992px) {
        .grid-2-1, .grid-1-1 {
            grid-template-columns: 1fr;
        }
    }

    .chart-panel {
        background: var(--surface);
        border: 1px solid rgba(0,0,0,0.04);
        border-radius: var(--radius-lg);
        padding: 2rem;
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }
    .chart-panel:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }
    
    .chart-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border);
    }
    
    .chart-panel-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    
    .chart-panel-title-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .chart-container {
        position: relative;
        height: 320px;
        width: 100%;
    }
</style>

<div class="page-header">
    <div>
        <h1>Analíticas y Gráficas del Negocio</h1>
        <p>Vista completa del rendimiento comercial, embudo de ventas y productividad de vendedores.</p>
    </div>
</div>

<div class="analytics-grid grid-2-1">
    <!-- 1. Tendencia de Ingresos -->
    <div class="chart-panel">
        <div class="chart-panel-head">
            <div class="chart-panel-title">
                <div class="chart-panel-title-icon" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">
                    <i class="fas fa-chart-line"></i>
                </div>
                Tendencia de Ingresos Cerrados
            </div>
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); background: var(--bg-main); padding: 0.25rem 0.6rem; border-radius: 6px;">Últimos 6 meses</span>
        </div>
        <div class="chart-container">
            <canvas id="revenueTrendChart"></canvas>
        </div>
    </div>

    <!-- 2. Tasa de Éxito -->
    <div class="chart-panel">
        <div class="chart-panel-head">
            <div class="chart-panel-title">
                <div class="chart-panel-title-icon" style="background: rgba(99, 102, 241, 0.08); color: #6366f1;">
                    <i class="fas fa-percentage"></i>
                </div>
                Tasa de Conversión General
            </div>
        </div>
        <div class="chart-container">
            <canvas id="successRateChart"></canvas>
        </div>
    </div>
</div>

<div class="analytics-grid grid-1-1">
    <!-- 3. Cantidad de Deals por Etapa -->
    <div class="chart-panel">
        <div class="chart-panel-head">
            <div class="chart-panel-title">
                <div class="chart-panel-title-icon" style="background: rgba(168, 85, 247, 0.08); color: #a855f7;">
                    <i class="fas fa-funnel-dollar"></i>
                </div>
                Distribución de Oportunidades
            </div>
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); background: var(--bg-main); padding: 0.25rem 0.6rem; border-radius: 6px;">Por Etapa (Cantidad)</span>
        </div>
        <div class="chart-container">
            <canvas id="stageCountChart"></canvas>
        </div>
    </div>

    <!-- 4. Monto Total por Etapa -->
    <div class="chart-panel">
        <div class="chart-panel-head">
            <div class="chart-panel-title">
                <div class="chart-panel-title-icon" style="background: rgba(245, 158, 11, 0.08); color: #f59e0b;">
                    <i class="fas fa-wallet"></i>
                </div>
                Valor Financiero del Pipeline
            </div>
            <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); background: var(--bg-main); padding: 0.25rem 0.6rem; border-radius: 6px;">Por Etapa (Monto)</span>
        </div>
        <div class="chart-container">
            <canvas id="stageAmountChart"></canvas>
        </div>
    </div>
</div>

<div class="analytics-grid grid-full">
    <!-- 5. Rendimiento por Vendedor -->
    <div class="chart-panel">
        <div class="chart-panel-head">
            <div class="chart-panel-title">
                <div class="chart-panel-title-icon" style="background: rgba(59, 130, 246, 0.08); color: #3b82f6;">
                    <i class="fas fa-user-friends"></i>
                </div>
                Rendimiento Comercial por Vendedor
            </div>
        </div>
        <div class="chart-container" style="height: 360px;">
            <canvas id="sellerPerformanceChart"></canvas>
        </div>
    </div>
</div>

<!-- Scripts de Chart.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.font.weight = '500';

const textColor = '#64748b';
const gridColor = 'rgba(0,0,0,0.04)';

// ── 1. TENDENCIA DE INGRESOS ──────────────────────────
const monthLabels = <?= $chartMonthLabels ?>;
const wonAmounts  = <?= $chartWonAmounts ?>;
const lostAmounts = <?= $chartLostAmounts ?>;

const rtCtx = document.getElementById('revenueTrendChart').getContext('2d');
const wonGrad = rtCtx.createLinearGradient(0,0,0,280);
wonGrad.addColorStop(0,'rgba(16,185,129,.25)'); wonGrad.addColorStop(1,'rgba(16,185,129,.01)');
const lostGrad = rtCtx.createLinearGradient(0,0,0,280);
lostGrad.addColorStop(0,'rgba(239,68,68,.18)'); lostGrad.addColorStop(1,'rgba(239,68,68,.01)');

new Chart(rtCtx, {
    type: 'line',
    data: {
        labels: monthLabels.length ? monthLabels : ['Sin datos'],
        datasets: [
            { label: 'Ventas Ganadas ($)', data: wonAmounts, borderColor: '#10b981', backgroundColor: wonGrad, borderWidth: 2.5, pointBackgroundColor: '#10b981', pointRadius: 4, pointHoverRadius: 6, tension: .4, fill: true },
            { label: 'Ventas Perdidas ($)', data: lostAmounts, borderColor: '#ef4444', backgroundColor: lostGrad, borderWidth: 2, pointBackgroundColor: '#ef4444', pointRadius: 3, pointHoverRadius: 5, tension: .4, fill: true }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: textColor, font: { weight: '600' }, usePointStyle: true, pointStyle: 'circle' } },
            tooltip: { callbacks: { label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2})}` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v) }, border: { dash: [4, 4] } }
        }
    }
});

// ── 2. TASA DE ÉXITO ────────────────────────────────
const winLossCounts = <?= $winLossCounts ?>;
const successCtx = document.getElementById('successRateChart').getContext('2d');
new Chart(successCtx, {
    type: 'doughnut',
    data: {
        labels: ['Ganados', 'Perdidos', 'Abiertos'],
        datasets: [{
            data: winLossCounts,
            backgroundColor: ['#10b981', '#ef4444', '#6366f1'],
            borderColor: '#ffffff',
            borderWidth: 3,
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { color: textColor, padding: 15, font: { size: 11, weight: '600' }, boxWidth: 10, usePointStyle: true, pointStyle: 'circle' } }
        }
    }
});

// ── 3. CANTIDAD DE DEALS POR ETAPA ─────────────────
const stageLabels = <?= $stageLabels ?>;
const stageCounts = <?= $stageCounts ?>;
const stageColors = <?= $stageColors ?>;

const scCtx = document.getElementById('stageCountChart').getContext('2d');
new Chart(scCtx, {
    type: 'polarArea',
    data: {
        labels: stageLabels.length ? stageLabels : ['Sin datos'],
        datasets: [{
            data: stageCounts.length ? stageCounts : [1],
            backgroundColor: stageColors.length ? stageColors.map(c => c + 'cc') : ['#94a3b8'],
            borderColor: '#ffffff',
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { color: textColor, font: { size: 10, weight: '600' }, boxWidth: 10, usePointStyle: true } }
        },
        scales: {
            r: { ticks: { display: false } }
        }
    }
});

// ── 4. MONTO TOTAL POR ETAPA ────────────────────────
const stageAmounts = <?= $stageAmounts ?>;
const saCtx = document.getElementById('stageAmountChart').getContext('2d');
new Chart(saCtx, {
    type: 'bar',
    data: {
        labels: stageLabels.length ? stageLabels : ['Sin datos'],
        datasets: [{
            label: 'Monto Total ($)',
            data: stageAmounts,
            backgroundColor: stageColors.length ? stageColors : ['#6366f1'],
            borderRadius: 8,
            borderWidth: 0,
            barPercentage: 0.6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2})}` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: textColor } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v) }, border: { dash: [4, 4] } }
        }
    }
});

// ── 5. RENDIMIENTO POR VENDEDOR ─────────────────────
const sellerLabels = <?= $sellerLabels ?>;
const sellerWon = <?= $sellerWonAmounts ?>;
const sellerOpen = <?= $sellerOpenAmounts ?>;

const spCtx = document.getElementById('sellerPerformanceChart').getContext('2d');
new Chart(spCtx, {
    type: 'bar',
    data: {
        labels: sellerLabels.length ? sellerLabels : ['Sin datos'],
        datasets: [
            { label: 'Ingresos Ganados ($)', data: sellerWon, backgroundColor: '#10b981', borderRadius: 8, barPercentage: 0.5 },
            { label: 'Pipeline Abierto ($)', data: sellerOpen, backgroundColor: '#6366f1', borderRadius: 8, barPercentage: 0.5 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'top', labels: { color: textColor, font: { weight: '600' }, usePointStyle: true, pointStyle: 'circle' } },
            tooltip: { callbacks: { label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX')}` } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: textColor } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v) }, border: { dash: [4, 4] } }
        }
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
