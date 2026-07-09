<?php
$pageTitle = 'Grupo EINSUR - Vista Global';
require __DIR__ . '/../layouts/header.php';
?>

<style>
    /* ========== FOUNDATION ========== */
    .content-area {
        background: var(--bg-main) !important;
    }

    /* ========== GREETING ========== */
    .dash-greeting {
        margin-bottom: 2rem;
    }

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

    .dash-greeting p {
        font-size: 0.95rem;
        color: var(--text-muted);
        font-weight: 500;
        margin: 0;
    }

    /* ========== KPI STRIP ========== */
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    @media(max-width:1200px) {
        .kpi-strip {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:500px) {
        .kpi-strip {
            grid-template-columns: 1fr;
        }
    }

    .kpi {
        background: var(--surface);
        border-radius: var(--radius-lg);
        padding: 1.4rem;
        border: 1px solid rgba(0, 0, 0, 0.03);
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .kpi:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px -8px rgba(0, 0, 0, .12);
        border-color: rgba(0, 0, 0, 0.06);
    }

    .kpi::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--accent), var(--primary));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .kpi:hover::before {
        opacity: 1;
    }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .kpi-dot {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: transform 0.3s;
    }

    .kpi:hover .kpi-dot {
        transform: rotate(-5deg) scale(1.1);
    }

    .kpi-tag {
        font-size: .68rem;
        font-weight: 800;
        padding: .25rem .6rem;
        border-radius: 8px;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .kpi-val {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text-main);
        letter-spacing: -.04em;
        margin-bottom: .3rem;
    }

    .kpi-lbl {
        font-size: .85rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    /* ========== GRID SYSTEM ========== */
    .dash-grid {
        display: grid;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .dash-grid.g-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    @media(max-width:1100px) {
        .dash-grid.g-2 {
            grid-template-columns: 1fr;
        }
    }

    /* ========== PANEL CARD ========== */
    .panel {
        background: var(--surface);
        border: 1px solid rgba(0, 0, 0, 0.04);
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        transition: all .3s;
    }

    .panel:hover {
        box-shadow: var(--shadow-lg);
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.4rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.03);
    }

    .panel-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .panel-title-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        background: var(--primary-light);
        color: var(--primary);
    }

    /* ========== ROWS ========== */
    .ranking-row {
        display: flex;
        align-items: center;
        gap: .9rem;
        padding: .75rem;
        border-radius: 14px;
        transition: all .2s;
        border: 1px solid transparent;
    }

    .ranking-row:hover {
        background: #fdfdfd;
        border-color: rgba(0, 0, 0, 0.03);
        transform: scale(1.01);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
    }

    .ranking-av {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: .9rem;
        box-shadow: 0 4px 10px rgba(0, 45, 98, 0.2);
    }

    .ranking-body {
        flex: 1;
        min-width: 0;
    }

    .ranking-nm {
        font-weight: 700;
        font-size: .92rem;
        color: var(--text-main);
    }

    .ranking-sub {
        font-size: .75rem;
        color: var(--text-muted);
        margin-top: .15rem;
    }

    .ranking-num {
        font-weight: 800;
        font-size: .95rem;
        color: #059669;
        background: #d1fae5;
        padding: .25rem .6rem;
        border-radius: 8px;
        text-align: right;
    }
</style>

<div class="dash-greeting">
    <h1>Vista Global - Grupo EINSUR</h1>
    <p>Resumen estadístico de todas las empresas y vendedores del grupo</p>
</div>

<!-- ═══════════════ KPI STRIP ═══════════════ -->
<div class="kpi-strip">
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(16,185,129,.1);color:#10b981;"><i
                    class="fas fa-money-bill-wave"></i></div>
            <span class="kpi-tag" style="background:rgba(16,185,129,.1);color:#10b981;">Total Ganado</span>
        </div>
        <div class="kpi-val">$<?= number_format((float) ($globalStats['total_won_amount'] ?? 0), 0, '.', ',') ?></div>
        <div class="kpi-lbl"><?= $globalStats['won_deals_count'] ?? 0 ?> tratos ganados</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(99,102,241,.1);color:#6366f1;"><i class="fas fa-chart-line"></i>
            </div>
            <span class="kpi-tag" style="background:rgba(99,102,241,.1);color:#6366f1;">Total Abierto</span>
        </div>
        <div class="kpi-val">$<?= number_format((float) ($globalStats['total_open_amount'] ?? 0), 0, '.', ',') ?></div>
        <div class="kpi-lbl"><?= $globalStats['open_deals_count'] ?? 0 ?> tratos en pipeline</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="fas fa-handshake"></i>
            </div>
            <span class="kpi-tag" style="background:rgba(245,158,11,.1);color:#f59e0b;">Volumen</span>
        </div>
        <div class="kpi-val"><?= $globalStats['total_deals'] ?? 0 ?></div>
        <div class="kpi-lbl">Tratos registrados</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(59,130,246,.1);color:#3b82f6;"><i class="fas fa-building"></i>
            </div>
            <span class="kpi-tag" style="background:rgba(59,130,246,.1);color:#3b82f6;">Empresas</span>
        </div>
        <div class="kpi-val"><?= count($empresas) ?></div>
        <div class="kpi-lbl">Operando en el grupo</div>
    </div>
</div>

<div class="dash-grid g-2">
    <!-- Ranking de Empresas -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon" style="background:rgba(59,130,246,.08);color:#3b82f6;"><i
                        class="fas fa-building"></i></div> Ranking por Empresa
            </div>
        </div>
        <?php if (empty($empresas)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">No hay empresas
                registradas.</p>
        <?php else: ?>
            <?php foreach ($empresas as $i => $emp):
                $init = strtoupper(substr($emp->empresa, 0, 1));
                ?>
                <div class="ranking-row">
                    <div class="ranking-av" style="background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">#<?= $i + 1 ?>
                    </div>
                    <div class="ranking-body">
                        <div class="ranking-nm"><?= htmlspecialchars($emp->empresa) ?></div>
                        <div class="ranking-sub"><?= $emp->won_deals ?> Ganados | <?= $emp->open_deals ?> Abiertos</div>
                    </div>
                    <div class="ranking-num">$<?= number_format((float) $emp->won_amount, 0, '.', ',') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Ranking de Vendedores Global -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon" style="background:rgba(16,185,129,.08);color:#10b981;"><i
                        class="fas fa-trophy"></i></div> Top Vendedores Globales
            </div>
        </div>
        <?php if (empty($vendedores)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">No hay vendedores con
                tratos.</p>
        <?php else: ?>
            <?php foreach ($vendedores as $i => $seller):
                $init = strtoupper(substr($seller->name, 0, 1));
                ?>
                <div class="ranking-row">
                    <div class="ranking-av"><?= $init ?></div>
                    <div class="ranking-body">
                        <div class="ranking-nm"><?= htmlspecialchars($seller->name) ?></div>
                        <div class="ranking-sub">Empresa: <strong><?= htmlspecialchars($seller->tenant_name) ?></strong> &bull;
                            <?= $seller->won_deals ?> ganados</div>
                    </div>
                    <div class="ranking-num">$<?= number_format((float) $seller->won_amount, 0, '.', ',') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Panel Comparativa de Empresas -->
<div class="panel" style="margin-bottom: 2rem;">
    <div class="panel-head">
        <div class="panel-title">
            <div class="panel-title-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;"><i
                    class="fas fa-chart-bar"></i></div> Comparativa de Ventas por Empresa
        </div>
    </div>
    <div style="position:relative;height:300px;"><canvas id="empresasChart"></canvas></div>
</div>

<!-- Tendencia Mensual Global -->
<div class="panel" style="margin-bottom: 2rem;">
    <div class="panel-head">
        <div class="panel-title">
            <div class="panel-title-icon" style="background:rgba(99,102,241,.08);color:#6366f1;"><i
                    class="fas fa-chart-area"></i></div> Tendencia del Grupo (6 Meses)
        </div>
    </div>
    <div style="position:relative;height:300px;"><canvas id="trendChart"></canvas></div>
</div>

<!-- Top 10 Negocios Globales -->
<div class="panel" style="margin-bottom: 2rem;">
    <div class="panel-head">
        <div class="panel-title">
            <div class="panel-title-icon" style="background:rgba(239,68,68,.08);color:#ef4444;"><i
                    class="fas fa-fire"></i></div> Pipeline Caliente: Top 10 Negocios Globales
        </div>
    </div>
    <div class="table-responsive">
        <table class="table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); color: var(--text-muted);">
                    <th style="padding: 1rem 0.5rem;">Empresa</th>
                    <th style="padding: 1rem 0.5rem;">Vendedor</th>
                    <th style="padding: 1rem 0.5rem;">Negocio</th>
                    <th style="padding: 1rem 0.5rem;">Probabilidad</th>
                    <th style="padding: 1rem 0.5rem; text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topOpenDeals)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">No hay negocios
                            abiertos actualmente.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topOpenDeals as $deal): ?>
                        <tr style="border-bottom: 1px solid var(--bg-main);">
                            <td style="padding: 0.8rem 0.5rem; font-weight: 600; color: #3b82f6;">
                                <?= htmlspecialchars($deal->tenant_name) ?></td>
                            <td style="padding: 0.8rem 0.5rem;"><?= htmlspecialchars($deal->owner_name) ?></td>
                            <td style="padding: 0.8rem 0.5rem; font-weight: 600;"><?= htmlspecialchars($deal->name) ?></td>
                            <td style="padding: 0.8rem 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 100%; background: var(--border); height: 6px; border-radius: 4px;">
                                        <div
                                            style="width: <?= $deal->probability ?? 0 ?>%; background: <?= ($deal->probability >= 70) ? '#10b981' : (($deal->probability >= 40) ? '#f59e0b' : '#ef4444') ?>; height: 100%; border-radius: 4px;">
                                        </div>
                                    </div>
                                    <span
                                        style="font-size: 0.8rem; font-weight: 700; width: 30px;"><?= $deal->probability ?? 0 ?>%</span>
                                </div>
                            </td>
                            <td style="padding: 0.8rem 0.5rem; text-align: right; font-weight: 800; color: var(--text-main);">
                                $<?= number_format((float) $deal->amount, 2, '.', ',') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.font.family = "'Outfit', sans-serif";
    Chart.defaults.font.weight = '500';
    Chart.defaults.font.size = 13.5;

    const isDark = document.documentElement.classList.contains('dark')
        || document.body.classList.contains('dark')
        || document.documentElement.getAttribute('data-theme') === 'dark';

    const textColor = isDark ? '#cbd5e1' : '#374151';
    const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';

    const empLabels = <?= json_encode(array_map(fn($e) => $e->empresa, $empresas)) ?>;
    const empWon = <?= json_encode(array_map(fn($e) => (float) $e->won_amount, $empresas)) ?>;
    const empOpen = <?= json_encode(array_map(fn($e) => (float) $e->open_amount, $empresas)) ?>;

    const trendLabels = <?= json_encode(array_map(fn($t) => $t->month_label, $monthlyTrend)) ?>;
    const trendWon = <?= json_encode(array_map(fn($t) => (float) $t->won_amount, $monthlyTrend)) ?>;
    const trendOpen = <?= json_encode(array_map(fn($t) => (float) $t->open_amount, $monthlyTrend)) ?>;

    const ctx = document.getElementById('empresasChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: empLabels,
            datasets: [
                {
                    label: 'Ganado',
                    data: empWon,
                    backgroundColor: '#10b981',
                    borderRadius: 4
                },
                {
                    label: 'Abierto',
                    data: empOpen,
                    backgroundColor: '#6366f1',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: textColor, font: { weight: '600' } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX')}`
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    border: { dash: [4, 4] },
                    ticks: {
                        color: textColor,
                        callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v)
                    }
                }
            }
        }
    });

    const ctxTrend = document.getElementById('trendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Ingresos Ganados',
                    data: trendWon,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Pipeline Abierto',
                    data: trendOpen,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.05)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: textColor, font: { weight: '600' } } },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX')}`
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    border: { dash: [4, 4] },
                    ticks: {
                        color: textColor,
                        callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v)
                    }
                }
            }
        }
    });
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>