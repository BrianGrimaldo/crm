<?php
$pageTitle = 'Dashboard - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$totalWon = $dealStats['total_won'] ?? 0;
$openDealsCount = $dealStats['open_deals_count'] ?? 0;
$totalPipeline = $dealStats['open_deals_amount'] ?? 0;
$avgDealSize = $openDealsCount > 0 ? round($totalPipeline / $openDealsCount) : 0;
?>

<style>
    /* ========== FOUNDATION ========== */
    .content-area {
        background: var(--bg-main) !important;
    }

    /* ========== SOFT ICON UTILITIES ========== */
    .icon-soft-primary {
        background: #e0e7ff;
        color: #4338ca;
    }

    .icon-soft-success {
        background: #dcfce7;
        color: #15803d;
    }

    .icon-soft-warning {
        background: #fef9c3;
        color: #a16207;
    }

    .icon-soft-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .icon-soft-info {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .icon-soft-purple {
        background: #f3e8ff;
        color: #7e22ce;
    }

    /* ========== DASHBOARD LOGO HEADER ========== */
    .dash-hero {
        margin-bottom: 32px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .dash-main-header {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .dash-main-header img {
        height: 300px;
        width: auto;
        object-fit: contain;
        margin: -80px -40px -80px -60px; /* Arriba, Derecha, Abajo, Izquierda (para compensar el espacio transparente de la imagen original) */
    }

    .dash-main-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .dash-main-text h1 {
        font-size: 1.6rem;
        font-weight: 700;
        margin: 0 0 4px 0;
        letter-spacing: -0.03em;
        color: var(--text-title);
        line-height: 1.1;
    }

    .dash-main-text span {
        font-size: 1.05rem;
        color: var(--text-muted);
        font-style: italic;
        font-weight: 500;
    }

    .dash-summary-info {
        font-size: 0.95rem;
        color: var(--text-muted);
        font-weight: 400;
        margin-top: 4px;
    }

    /* ========== KPI STRIP ========== */
    .kpi-strip {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    @media(max-width:1400px) {
        .kpi-strip {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media(max-width:900px) {
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
        padding: 24px;
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
    }

    .kpi:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .kpi-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .kpi-dot {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .kpi-tag {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        color: var(--text-muted);
        background: var(--bg-main);
    }

    .kpi-val {
        font-size: 1.8rem;
        font-weight: 600;
        line-height: 1;
        color: var(--text-title);
        letter-spacing: -.02em;
        margin-bottom: 8px;
        font-variant-numeric: tabular-nums;
    }

    .kpi-lbl {
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--text-muted);
    }

    /* ========== GRID SYSTEM ========== */
    .dash-grid {
        display: grid;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .dash-grid.g-2-1 {
        grid-template-columns: 2fr 1fr;
    }

    .dash-grid.g-1-1 {
        grid-template-columns: 1fr 1fr;
    }

    .dash-grid.g-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .dash-grid.g-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .dash-grid.g-4 {
        grid-template-columns: repeat(4, 1fr);
    }

    @media(max-width:1100px) {

        .dash-grid.g-3,
        .dash-grid.g-4 {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:768px) {

        .dash-grid.g-2-1,
        .dash-grid.g-1-1,
        .dash-grid.g-3,
        .dash-grid.g-2,
        .dash-grid.g-4 {
            grid-template-columns: 1fr;
        }
    }

    /* ========== PANEL CARD ========== */
    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 0.2s ease;
    }

    .panel:hover {
        box-shadow: var(--shadow-md);
    }

    .panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .panel-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-title);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .panel-title-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .panel-badge {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 4px 8px;
        border-radius: 4px;
        background: var(--bg-main);
        color: var(--text-muted);
    }

    /* ========== DEAL ROWS ========== */
    .deal-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 0;
        transition: all .2s ease;
        border-bottom: 1px solid var(--border);
    }

    .deal-row:last-child {
        border-bottom: none;
    }

    .deal-pos {
        font-size: 0.95rem;
        font-weight: 500;
        color: var(--text-muted);
        min-width: 24px;
        text-align: center;
    }

    .deal-stage-pip {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .deal-body {
        flex: 1;
        min-width: 0;
    }

    .deal-nm {
        font-weight: 500;
        font-size: 0.95rem;
        color: var(--text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .deal-sub {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .deal-val {
        text-align: right;
    }

    .deal-amt {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-main);
        font-variant-numeric: tabular-nums;
    }

    .deal-pct {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .prob-track {
        height: 4px;
        background: var(--border);
        border-radius: 4px;
        margin-top: 6px;
        overflow: hidden;
    }

    .prob-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* ========== SELLER ROWS ========== */
    .seller-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
    }

    .seller-row:last-child {
        border-bottom: none;
    }

    .seller-av {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        font-size: 0.9rem;
    }

    .seller-body {
        flex: 1;
        min-width: 0;
    }

    .seller-nm {
        font-weight: 500;
        font-size: 0.95rem;
        color: var(--text-main);
    }

    .seller-sub {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .seller-num {
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-main);
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    /* ========== ACTIVITY TIMELINE ========== */
    .timeline {
        max-height: 360px;
        overflow-y: auto;
        padding-right: .5rem;
    }

    .timeline::-webkit-scrollbar {
        width: 5px;
    }

    .timeline::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 5px;
    }

    .tl-item {
        display: flex;
        gap: .85rem;
        padding: .75rem 0;
        position: relative;
    }

    .tl-item+.tl-item {
        border-top: 1px dashed rgba(0, 0, 0, .06);
    }

    .tl-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
        flex-shrink: 0;
    }

    .tl-body {
        flex: 1;
        min-width: 0;
    }

    .tl-text {
        font-size: .88rem;
        font-weight: 600;
        color: var(--text-main);
        line-height: 1.4;
    }

    .tl-text strong {
        color: var(--primary);
    }

    .tl-time {
        font-size: .75rem;
        color: var(--text-muted);
        margin-top: .2rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: .3rem;
    }

    /* ========== FUNNEL ========== */
    .funnel-stage {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .65rem .75rem;
        border-radius: 12px;
        transition: all .2s;
    }

    .funnel-stage:hover {
        background: rgba(0, 0, 0, .015);
        transform: translateX(4px);
    }

    .funnel-dot {
        width: 12px;
        height: 12px;
        border-radius: 4px;
        flex-shrink: 0;
    }

    .funnel-name {
        flex: 1;
        font-size: .88rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .funnel-count {
        font-size: .88rem;
        font-weight: 800;
        color: var(--text-main);
        min-width: 28px;
        text-align: center;
    }

    .funnel-bar-bg {
        flex: 2;
        height: 8px;
        background: rgba(0, 0, 0, 0.04);
        border-radius: 8px;
        overflow: hidden;
    }

    .funnel-bar-fill {
        height: 100%;
        border-radius: 8px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
</style>

<!-- ═══════════════ HEADER ═══════════════ -->
<div class="dash-hero" style="margin-bottom: 24px;">
    <div class="dash-summary-info" style="font-size: 1.05rem; font-weight: 600; color: var(--text-title);">
        Resumen de <?= htmlspecialchars($tenantName) ?> &middot; <?= date('d \d\e F, Y') ?>
    </div>
</div>

<!-- ═══════════════ KPI STRIP ═══════════════ -->
<div class="kpi-strip">
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-success"><i class="fa-solid fa-money-bill-wave"></i></div>
            <span class="kpi-tag badge-success">Cerradas</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalWon, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Total Ganado</div>
    </div>
    <div class="kpi" style="cursor:pointer;" onclick="window.location.href='<?= url('/finanzas/facturas') ?>'">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-info"><i class="fa-solid fa-file-invoice-dollar"></i></div>
            <span class="kpi-tag badge-info">Facturas</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalFacturado, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Total Facturado</div>
    </div>
    <div class="kpi" style="cursor:pointer;" onclick="window.location.href='<?= url('/finanzas/facturas') ?>'">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-success"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <span class="kpi-tag badge-success">Cobranza</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalCobrado, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Total Cobrado</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-primary"><i class="fa-solid fa-chart-bar"></i></div>
            <span class="kpi-tag badge-primary"><?= $openDealsCount ?> abiertas</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalPipeline, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Pipeline activo</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-warning"><i class="fa-solid fa-bullseye"></i></div>
            <span class="kpi-tag badge-warning"><?= $closedDeals ?> cerrados</span>
        </div>
        <div class="kpi-val"><?= $conversionRate ?>%</div>
        <div class="kpi-lbl">Tasa de cierre</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-purple"><i class="fa-solid fa-users"></i></div>
            <span class="kpi-tag badge-info"><?= $totalAccounts ?> empresas</span>
        </div>
        <div class="kpi-val"><?= $totalContacts ?></div>
        <div class="kpi-lbl">Contactos</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot icon-soft-danger"><i class="fa-solid fa-times-circle"></i></div>
            <span class="kpi-tag badge-error"><?= $dealStats['lost_deals_count'] ?? 0 ?> perdidas</span>
        </div>
        <div class="kpi-val">$<?= number_format($dealStats['total_lost'] ?? 0, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Ventas perdidas</div>
    </div>
</div>

<!-- ═══════════════ ROW 1: Revenue + Donut + Funnel ═══════════════ -->
<div class="dash-grid g-3">
    <!-- Tendencia de Ingresos -->
    <div class="panel" style="cursor:pointer;" onclick="window.location.href='/analiticas'">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon icon-soft-success"><i class="fa-solid fa-chart-line"></i></div> Tendencia
                de Ingresos
            </div>
            <a href="<?= url('/analiticas') ?>"
                style="font-size:0.85rem;color:var(--text-muted);font-weight:500;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">Ver
                completo <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i></a>
        </div>
        <div style="position:relative;height:260px;"><canvas id="revenueChart"></canvas></div>
    </div>
    <!-- Negocios por Etapa -->
    <div class="panel" style="cursor:pointer;" onclick="window.location.href='/analiticas'">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon icon-soft-purple"><i class="fa-solid fa-chart-pie"></i></div> Negocios por
                Etapa
            </div>
            <a href="<?= url('/analiticas') ?>"
                style="font-size:0.85rem;color:var(--text-muted);font-weight:500;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">Ver
                completo <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i></a>
        </div>
        <div style="position:relative;height:260px;"><canvas id="stageChart"></canvas></div>
    </div>
    <!-- Embudo de Ventas -->
    <div class="panel" style="cursor:pointer;" onclick="window.location.href='/analiticas'">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon icon-soft-warning"><i class="fa-solid fa-hourglass-half"></i></div> Embudo
                de Ventas
            </div>
            <a href="<?= url('/analiticas') ?>"
                style="font-size:0.85rem;color:var(--text-muted);font-weight:500;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">Ver
                completo <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i></a>
        </div>
        <?php if (!empty($dealsSummary)):
            $maxCount = max(array_map(fn($s) => (int) $s->deal_count, $dealsSummary)) ?: 1;
            ?>
            <?php foreach ($dealsSummary as $stage):
                $pct = round(((int) $stage->deal_count / $maxCount) * 100);
                $color = $stage->color ?? '#94a3b8';
                ?>
                <div class="funnel-stage">
                    <div class="funnel-dot" style="background:<?= htmlspecialchars($color) ?>;"></div>
                    <div class="funnel-name"><?= htmlspecialchars($stage->name) ?></div>
                    <div class="funnel-count"><?= $stage->deal_count ?></div>
                    <div class="funnel-bar-bg">
                        <div class="funnel-bar-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($color) ?>;">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin etapas
                configuradas.</p>
        <?php endif; ?>
    </div>
</div>

<!-- ROW 2 removed: no inventory charts -->

<!-- ═══════════════ ROW 3: Top Deals + Sellers + Activity (solo admin/gerente) + Tareas ═══════════════ -->
<?php
$dashRole = strtolower(str_replace('-', '', $_SESSION['user_role'] ?? ''));
$isManager = in_array($dashRole, ['superadmin', 'admin', 'salesmgr', 'gerente'])
    || !empty($_SESSION['is_superadmin'])
    || !empty($_SESSION['is_owner']);
?>

<?php if ($isManager): ?>
    <div class="dash-grid g-3">
        <!-- Top Deals -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <div class="panel-title-icon icon-soft-danger"><i class="fa-solid fa-fire"></i></div> Oportunidades Top
                </div>
                <span class="panel-badge badge-success">Abiertas</span>
            </div>
            <?php if (empty($topOpenDeals)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin oportunidades
                    abiertas.</p>
            <?php else: ?>
                <?php foreach ($topOpenDeals as $i => $deal): ?>
                    <div class="deal-row">
                        <div class="deal-pos"><?= $i + 1 ?></div>
                        <div class="deal-stage-pip" style="background:<?= htmlspecialchars($deal->stage_color ?? '#94a3b8') ?>;">
                        </div>
                        <div class="deal-body">
                            <div class="deal-nm"><?= htmlspecialchars($deal->name) ?></div>
                            <div class="deal-sub"><?= htmlspecialchars($deal->account_name ?? '—') ?> ·
                                <?= htmlspecialchars($deal->stage_name ?? '—') ?>
                            </div>
                            <div class="prob-track">
                                <div class="prob-fill"
                                    style="width:<?= (int) $deal->probability ?>%;background:<?= htmlspecialchars($deal->stage_color ?? '#6366f1') ?>;">
                                </div>
                            </div>
                        </div>
                        <div class="deal-val">
                            <div class="deal-amt">$<?= number_format((float) $deal->amount, 0, '.', ',') ?></div>
                            <div class="deal-pct"><?= (int) $deal->probability ?>%</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sellers -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <div class="panel-title-icon icon-soft-primary"><i class="fa-solid fa-trophy"></i></div> Ranking
                    Vendedores
                </div>
            </div>
            <?php if (empty($statsByOwner)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin datos.</p>
            <?php else: ?>
                <?php foreach ($statsByOwner as $idx => $seller):
                    $init = strtoupper(substr($seller->owner_name, 0, 1));
                    $colors = ['icon-soft-primary', 'icon-soft-success', 'icon-soft-warning', 'icon-soft-purple', 'icon-soft-info'];
                    $colorClass = $colors[$idx % count($colors)];
                    ?>
                    <div class="seller-row">
                        <div class="seller-av <?= $colorClass ?>"><?= $init ?></div>
                        <div class="seller-body">
                            <div class="seller-nm"><?= htmlspecialchars($seller->owner_name) ?></div>
                            <div class="seller-sub"><?= $seller->total_deals ?> deals · <?= $seller->won_deals ?> ganados</div>
                        </div>
                        <div class="seller-num">$<?= number_format((float) $seller->won_amount, 0, '.', ',') ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Activity -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <div class="panel-title-icon icon-soft-info"><i class="fa-solid fa-bolt"></i></div> Actividad Reciente
                </div>
            </div>
            <div class="timeline">
                <?php if (empty($recentActivities)): ?>
                    <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin actividad.</p>
                <?php else: ?>
                    <?php foreach ($recentActivities as $act):
                        $icons = ['create' => '<i class="fas fa-star"></i>', 'update' => '<i class="fas fa-pencil-alt"></i>', 'delete' => '<i class="fas fa-trash"></i>', 'update_stage' => '<i class="fas fa-layer-group"></i>', 'update_probability' => '<i class="fas fa-percentage"></i>'];
                        $colors = ['create' => 'icon-soft-success', 'update' => 'icon-soft-primary', 'delete' => 'icon-soft-danger', 'update_stage' => 'icon-soft-warning', 'update_probability' => 'icon-soft-purple'];
                        $lbls = ['create' => 'Creó', 'update' => 'Actualizó', 'delete' => 'Eliminó', 'update_stage' => 'Movió etapa', 'update_probability' => 'Cambió prob.'];
                        $icon = $icons[$act->action] ?? '<i class="fas fa-thumbtack"></i>';
                        $bgClass = $colors[$act->action] ?? 'icon-soft-primary';
                        $lbl = $lbls[$act->action] ?? $act->action;
                        $time = (new DateTime($act->created_at))->format('d M, H:i');
                        ?>
                        <div class="tl-item">
                            <div class="tl-icon <?= $bgClass ?>"><?= $icon ?></div>
                            <div class="tl-body">
                                <div class="tl-text">
                                    <?= htmlspecialchars($act->user_name ?? 'Sistema') ?>
                                    <span style="color:var(--text-muted);font-weight:400;"> <?= $lbl ?> </span>
                                    <?= htmlspecialchars($act->entity_type) ?>             <?php if ($act->entity_id): ?>
                                        #<?= $act->entity_id ?><?php endif; ?>
                                </div>
                                <div class="tl-time"><?= $time ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>


    <!-- ═══════════════ CHART.JS ═══════════════ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<?php if ($isManager): ?>
<!-- ═══════════════ ANALYTICS DE COTIZACIONES ═══════════════ -->
<div style="margin-top:2rem;">
    <!-- ── Cabecera de Sección ── -->
    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1.25rem;">
        <div style="width:36px;height:36px;border-radius:10px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
            <i class="fa-solid fa-file-contract"></i>
        </div>
        <div>
            <div style="font-size:1.05rem;font-weight:700;color:var(--text-title);">Análisis de Cotizaciones — <?= date('F Y') ?></div>
            <div style="font-size:.82rem;color:var(--text-muted);">Propuestas entregadas, seguimiento y efectividad por área y vendedor</div>
        </div>
    </div>

    <!-- ── Fila 1: KPIs del Mes ── -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:1.5rem;">
        <?php
        $qm = $quotesThisMonth ?? [];
        $kpis = [
            ['val' => $qm['total_quotes'] ?? 0,  'lbl' => 'Cotizaciones emitidas', 'icon' => 'fa-file-alt',       'bg' => '#e0e7ff', 'col' => '#4338ca'],
            ['val' => $qm['vigentes']    ?? 0,  'lbl' => 'Vigentes',              'icon' => 'fa-hourglass-half',  'bg' => '#dcfce7', 'col' => '#15803d'],
            ['val' => $qm['expiradas']   ?? 0,  'lbl' => 'Expiradas',             'icon' => 'fa-calendar-times',  'bg' => '#fee2e2', 'col' => '#b91c1c'],
            ['val' => $qm['concretadas'] ?? 0,  'lbl' => 'Concretadas',           'icon' => 'fa-check-circle',    'bg' => '#fef9c3', 'col' => '#a16207'],
            ['val' => ($qm['conversion_pct'] ?? 0).'%', 'lbl' => 'Tasa de cierre', 'icon' => 'fa-bullseye',       'bg' => '#f3e8ff', 'col' => '#7e22ce'],
        ];
        foreach ($kpis as $k): ?>
        <div class="kpi" style="padding:1.25rem;">
            <div class="kpi-top">
                <div class="kpi-dot" style="background:<?= $k['bg'] ?>;color:<?= $k['col'] ?>;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid <?= $k['icon'] ?>"></i>
                </div>
            </div>
            <div class="kpi-val" style="font-size:1.6rem;"><?= $k['val'] ?></div>
            <div class="kpi-lbl"><?= $k['lbl'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Fila 2: Gráfica por Área + Seguimiento ── -->
    <div class="dash-grid g-2-1" style="margin-bottom:1.5rem;">
        <!-- Gráfica: Cotizaciones por Área -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <div class="panel-title-icon icon-soft-info"><i class="fa-solid fa-chart-column"></i></div>
                    Cotizaciones por Área
                </div>
                <span class="panel-badge" style="background:#e0f2fe;color:#0284c7;">Mes actual</span>
            </div>
            <div style="position:relative;height:240px;">
                <canvas id="areaQuotesChart"></canvas>
            </div>
        </div>

        <!-- Panel: Seguimiento al día siguiente -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <div class="panel-title-icon icon-soft-warning"><i class="fa-solid fa-phone-volume"></i></div>
                    Follow-up al Día Siguiente
                </div>
                <span class="panel-badge" style="background:#fef9c3;color:#a16207;"><?= ($quotesFollowup['total_quotes'] ?? 0) ?> cotizaciones</span>
            </div>
            <?php $qf = $quotesFollowup ?? []; ?>
            <div style="position:relative;height:180px;">
                <canvas id="followupDonutChart"></canvas>
            </div>
            <div style="display:flex;justify-content:center;gap:2rem;margin-top:.75rem;">
                <div style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:700;color:#10b981;"><?= $qf['with_followup'] ?? 0 ?></div>
                    <div style="font-size:.78rem;color:var(--text-muted);">Con seguimiento</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:700;color:#ef4444;"><?= $qf['no_followup'] ?? 0 ?></div>
                    <div style="font-size:.78rem;color:var(--text-muted);">Sin contacto</div>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:1.5rem;font-weight:700;color:#f59e0b;"><?= $qf['followup_pct'] ?? 0 ?>%</div>
                    <div style="font-size:.78rem;color:var(--text-muted);">Tasa follow-up</div>
                </div>
            </div>
            <p style="font-size:.76rem;color:var(--text-muted);margin-top:.75rem;text-align:center;line-height:1.5;">
                Actividad registrada en las primeras 24 h tras emitir la cotización.
            </p>
        </div>
    </div>

    <!-- ── Fila 3: Scatter + Tabla Vendedores ── -->
    <div class="dash-grid g-1-1" style="margin-bottom:1.5rem;">
    <!-- Scatter chart cotiza vs cierra -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon icon-soft-primary"><i class="fa-solid fa-chart-scatter"></i></div>
                Mapa: Cotizan vs Cierran
            </div>
            <span class="panel-badge" style="background:#e0e7ff;color:#4338ca;">Mes actual</span>
        </div>
        <div style="position:relative;height:280px;">
            <canvas id="vendorScatterChart"></canvas>
        </div>
        <p style="font-size:.75rem;color:var(--text-muted);margin-top:.5rem;text-align:center;">
            Eje X = cotizaciones emitidas &nbsp;·&nbsp; Eje Y = concretadas &nbsp;·&nbsp; Ideal: arriba a la derecha
        </p>
    </div>
    <!-- Tabla Vendedores -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">
                <div class="panel-title-icon icon-soft-primary"><i class="fa-solid fa-ranking-star"></i></div>
                Efectividad por Vendedor
            </div>
            <span class="panel-badge" style="background:#f3e8ff;color:#7e22ce;">Mes actual</span>
        </div>
        <?php if (empty($quoteMatrix)): ?>
            <p style="text-align:center;color:var(--text-muted);padding:2rem;">Sin datos de cotizaciones en este período.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.88rem;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border);">
                        <th style="text-align:left;padding:.75rem 1rem;color:var(--text-muted);font-weight:600;">Vendedor</th>
                        <th style="text-align:center;padding:.75rem .5rem;color:var(--text-muted);font-weight:600;">Cotizaciones</th>
                        <th style="text-align:center;padding:.75rem .5rem;color:var(--text-muted);font-weight:600;">Concretadas</th>
                        <th style="text-align:center;padding:.75rem .5rem;color:var(--text-muted);font-weight:600;">Perdidas</th>
                        <th style="text-align:center;padding:.75rem .5rem;color:var(--text-muted);font-weight:600;">Abiertas</th>
                        <th style="text-align:center;padding:.75rem 1rem;color:var(--text-muted);font-weight:600;">Tasa Cierre</th>
                        <th style="text-align:right;padding:.75rem 1rem;color:var(--text-muted);font-weight:600;">Monto Ganado</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $matrixColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#0ea5e9'];
                foreach ($quoteMatrix as $idx => $seller):
                    $convPct = (float) $seller->conversion_pct;

                    $barColor = $matrixColors[$idx % count($matrixColors)];
                    $init = strtoupper(substr($seller->owner_name, 0, 1));
                ?>
                <tr style="border-bottom:1px solid var(--border);transition:background .15s ease;" onmouseover="this.style.background='var(--bg-main)'" onmouseout="this.style.background='transparent'">
                    <td style="padding:.85rem 1rem;">
                        <div style="display:flex;align-items:center;gap:.75rem;">
                            <div style="width:34px;height:34px;border-radius:8px;background:<?= $barColor ?>22;color:<?= $barColor ?>;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.95rem;flex-shrink:0;">
                                <?= $init ?>
                            </div>
                            <span style="font-weight:600;color:var(--text-main);"><?= htmlspecialchars($seller->owner_name) ?></span>
                        </div>
                    </td>
                    <td style="text-align:center;padding:.85rem .5rem;">
                        <span style="font-size:1.2rem;font-weight:700;color:var(--text-title);"><?= $seller->total_quotes ?></span>
                    </td>
                    <td style="text-align:center;padding:.85rem .5rem;">
                        <span style="font-weight:700;color:#15803d;"><?= $seller->won ?></span>
                    </td>
                    <td style="text-align:center;padding:.85rem .5rem;">
                        <span style="font-weight:700;color:#b91c1c;"><?= $seller->lost ?></span>
                    </td>
                    <td style="text-align:center;padding:.85rem .5rem;">
                        <span style="color:var(--text-muted);"><?= $seller->open ?></span>
                    </td>
                    <td style="text-align:center;padding:.85rem 1rem;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:.3rem;">
                            <span style="font-weight:700;font-size:1rem;color:<?= $convPct >= 50 ? '#15803d' : ($convPct >= 25 ? '#a16207' : '#b91c1c') ?>;"><?= $convPct ?>%</span>
                            <div style="width:80px;height:5px;background:var(--border);border-radius:5px;overflow:hidden;">
                                <div style="width:<?= min($convPct, 100) ?>%;height:100%;background:<?= $convPct >= 50 ? '#10b981' : ($convPct >= 25 ? '#f59e0b' : '#ef4444') ?>;border-radius:5px;"></div>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:right;padding:.85rem 1rem;font-weight:600;color:var(--text-main);">
                        $<?= number_format((float)$seller->won_amount, 0, '.', ',') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    </div><!-- end dash-grid g-1-1 -->
</div>
<?php endif; // end $isManager ?>

    <script>
        Chart.defaults.font.family = "'Outfit', sans-serif";
        Chart.defaults.font.weight = '500';
        Chart.defaults.font.size = 13.5;

        const monthLabels = <?= $chartMonthLabels ?? '[]' ?>;
        const wonAmounts = <?= $chartWonAmounts ?? '[]' ?>;
        const lostAmounts = <?= $chartLostAmounts ?? '[]' ?>;
        const stageLabels = <?= $stageLabels ?? '[]' ?>;
        const stageCounts = <?= $stageCounts ?? '[]' ?>;
        const stageColors = <?= $stageColors ?? '[]' ?>;

        const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-main').trim() || '#f8fafc';
        const gridColor = getComputedStyle(document.documentElement).getPropertyValue('--border').trim() || 'rgba(255,255,255,0.08)';

        // ── Revenue Trend ──────────────────────────
        const rCtx = document.getElementById('revenueChart').getContext('2d');
        const gG = rCtx.createLinearGradient(0, 0, 0, 280);
        gG.addColorStop(0, 'rgba(16,185,129,.3)'); gG.addColorStop(1, 'rgba(16,185,129,.01)');
        const rG = rCtx.createLinearGradient(0, 0, 0, 280);
        rG.addColorStop(0, 'rgba(239,68,68,.2)'); rG.addColorStop(1, 'rgba(239,68,68,.01)');

        new Chart(rCtx, {
            type: 'line',
            data: {
                labels: monthLabels.length ? monthLabels : ['Sin datos'],
                datasets: [
                    { label: 'Ganado', data: wonAmounts, borderColor: '#10b981', backgroundColor: gG, borderWidth: 2.5, pointBackgroundColor: '#10b981', pointRadius: 4, pointHoverRadius: 6, tension: .4, fill: true },
                    { label: 'Perdido', data: lostAmounts, borderColor: '#ef4444', backgroundColor: rG, borderWidth: 2, pointBackgroundColor: '#ef4444', pointRadius: 3, pointHoverRadius: 5, tension: .4, fill: true }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { labels: { color: textColor, font: { weight: '600', size: 13 }, usePointStyle: true, pointStyle: 'circle', padding: 16 } },
                    tooltip: { callbacks: { label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX')}` } }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: textColor, font: { size: 13 } } },
                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 12 }, callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v) }, border: { dash: [4, 4] } }
                }
            }
        });

        // ── Stage Donut ────────────────────────────
        const sCtx = document.getElementById('stageChart').getContext('2d');
        const fL = stageLabels.filter((_, i) => stageCounts[i] > 0);
        const fC = stageCounts.filter(c => c > 0);
        const fCo = stageColors.filter((_, i) => stageCounts[i] > 0);

        new Chart(sCtx, {
            type: 'doughnut',
            data: {
                labels: fL.length ? fL : ['Sin datos'],
                datasets: [{ data: fC.length ? fC : [1], backgroundColor: fCo.length ? fCo : ['#94a3b8'], borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }]
            },
            options: {
                responsive: true, maintainAspectRatio: true, cutout: '70%',
                layout: {
                    padding: 10
                },
                plugins: {
                    legend: { position: 'bottom', labels: { color: textColor, padding: 12, font: { size: 13, weight: '600' }, boxWidth: 10, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                }
            }
        });

        // ── Gráfica de Cotizaciones por Área ───────
        const areaEl = document.getElementById('areaQuotesChart');
        if (areaEl) {
            const areaLabels = <?= $areaLabels ?? '[]' ?>;
            const areaTotal  = <?= $areaTotal  ?? '[]' ?>;
            const areaClosed = <?= $areaClosed ?? '[]' ?>;
            new Chart(areaEl.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: areaLabels.length ? areaLabels : ['Sin datos'],
                    datasets: [
                        { label: 'Emitidas', data: areaTotal, backgroundColor: 'rgba(99,102,241,.75)', borderRadius: 6, borderSkipped: false },
                        { label: 'Concretadas', data: areaClosed, backgroundColor: 'rgba(16,185,129,.8)', borderRadius: 6, borderSkipped: false }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { labels: { color: textColor, font: { weight: '600', size: 12 }, usePointStyle: true, pointStyle: 'circle' } },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y}` } }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: textColor, maxRotation: 30 } },
                        y: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1, precision: 0 }, border: { dash: [4,4] } }
                    }
                }
            });
        }

        // ── Follow-up Doughnut ──────────────────────
        const fuEl = document.getElementById('followupDonutChart');
        if (fuEl) {
            const fuWith    = <?= (int)($quotesFollowup['with_followup'] ?? 0) ?>;
            const fuWithout = <?= (int)($quotesFollowup['no_followup']   ?? 0) ?>;
            new Chart(fuEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Con seguimiento', 'Sin contacto'],
                    datasets: [{
                        data: fuWith + fuWithout > 0 ? [fuWith, fuWithout] : [0, 1],
                        backgroundColor: ['#10b981', '#ef4444'],
                        borderColor: 'transparent',
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                    }
                }
            });
        }

        // ── Scatter: Vendedores Cotizan vs Cierran ──
        const scEl = document.getElementById('vendorScatterChart');
        if (scEl) {
            const matrix = <?= json_encode(array_map(fn($r) => [
                'x'    => (int)$r->total_quotes,
                'y'    => (int)$r->won,
                'name' => $r->owner_name,
                'pct'  => (float)$r->conversion_pct
            ], $quoteMatrix ?? [])) ?>;

            const paleta = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#0ea5e9','#ec4899','#14b8a6'];

            const scDatasets = matrix.map((v, i) => ({
                label: v.name,
                data: [{ x: v.x, y: v.y }],
                backgroundColor: paleta[i % paleta.length] + 'cc',
                borderColor:     paleta[i % paleta.length],
                borderWidth: 2,
                pointRadius: Math.max(10, v.x * 3),
                pointHoverRadius: Math.max(13, v.x * 3 + 3)
            }));

            const maxQ = Math.max(...matrix.map(v => v.x), 1);
            const maxW = Math.max(...matrix.map(v => v.y), 1);

            new Chart(scEl.getContext('2d'), {
                type: 'scatter',
                data: { datasets: scDatasets.length ? scDatasets : [{ label: 'Sin datos', data: [{ x:0, y:0 }], backgroundColor: '#94a3b8' }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: textColor, font: { size: 12, weight: '600' }, usePointStyle: true, pointStyle: 'circle', padding: 10 } },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const d = matrix[ctx.datasetIndex];
                                    return d ? ` ${d.name} — Emitidas: ${d.x}  Concretadas: ${d.y}  (${d.pct}%)` : '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            title: { display: true, text: 'Cotizaciones emitidas', color: textColor, font: { size: 12 } },
                            grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1, precision: 0 },
                            min: 0, suggestedMax: maxQ + 1
                        },
                        y: {
                            title: { display: true, text: 'Concretadas (ganadas)', color: textColor, font: { size: 12 } },
                            grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1, precision: 0 },
                            min: 0, suggestedMax: maxW + 1
                        }
                    }
                }
            });
        }

        // Inventory charts removed
    </script>

    <?php require __DIR__ . '/../layouts/footer.php'; ?>