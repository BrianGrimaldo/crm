<?php
$pageTitle = 'Dashboard - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

$totalWon       = $dealStats['total_won'] ?? 0;
$openDealsCount = $dealStats['open_deals_count'] ?? 0;
$totalPipeline  = $dealStats['open_deals_amount'] ?? 0;
$avgDealSize    = $openDealsCount > 0 ? round($totalPipeline / $openDealsCount) : 0;
?>

<style>
/* ========== FOUNDATION ========== */
.content-area { background: var(--bg-main) !important; }

/* ========== GREETING ========== */
.dash-greeting { margin-bottom: 1.75rem; }
.dash-greeting h1 { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0 0 .15rem; letter-spacing: -.02em; }
.dash-greeting p  { font-size: .88rem; color: var(--text-muted); font-weight: 500; margin: 0; }

/* ========== KPI STRIP ========== */
.kpi-strip { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media(max-width:1200px){ .kpi-strip{ grid-template-columns: repeat(3,1fr); } }
@media(max-width:768px){ .kpi-strip{ grid-template-columns: repeat(2,1fr); } }
@media(max-width:500px){ .kpi-strip{ grid-template-columns: 1fr; } }

.kpi {
    background: var(--surface); border-radius: 16px; padding: 1.25rem 1.25rem 1rem;
    border: 1px solid var(--border); position: relative; overflow: hidden;
    transition: transform .25s, box-shadow .25s;
}
.kpi:hover { transform: translateY(-4px); box-shadow: 0 12px 28px -8px rgba(0,0,0,.08); }
.kpi-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: .85rem; }
.kpi-dot { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; }
.kpi-tag { font-size: .65rem; font-weight: 700; padding: .2rem .55rem; border-radius: 6px; letter-spacing: .03em; }
.kpi-val { font-size: 1.55rem; font-weight: 800; line-height: 1; color: var(--text-main); letter-spacing: -.03em; margin-bottom: .2rem; }
.kpi-lbl { font-size: .78rem; font-weight: 600; color: var(--text-muted); }

/* ========== GRID SYSTEM ========== */
.dash-grid { display: grid; gap: 1.25rem; margin-bottom: 1.25rem; }
.dash-grid.g-2-1 { grid-template-columns: 2fr 1fr; }
.dash-grid.g-1-1 { grid-template-columns: 1fr 1fr; }
.dash-grid.g-3   { grid-template-columns: repeat(3, 1fr); }
.dash-grid.g-2   { grid-template-columns: repeat(2, 1fr); }
.dash-grid.g-4   { grid-template-columns: repeat(4, 1fr); }
@media(max-width:1100px){
    .dash-grid.g-3, .dash-grid.g-4 { grid-template-columns: repeat(2, 1fr); }
}
@media(max-width:768px){
    .dash-grid.g-2-1, .dash-grid.g-1-1, .dash-grid.g-3, .dash-grid.g-2, .dash-grid.g-4 { grid-template-columns: 1fr; }
}

/* ========== PANEL CARD ========== */
.panel {
    background: var(--surface); border: 1px solid var(--border); border-radius: 18px;
    padding: 1.35rem; transition: box-shadow .3s;
}
.panel:hover { box-shadow: 0 8px 24px -6px rgba(0,0,0,.06); }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.panel-title { font-size: 1rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: .45rem; }
.panel-title-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; }
.panel-badge { font-size: .68rem; font-weight: 700; padding: .2rem .55rem; border-radius: 6px; }

/* ========== DEAL ROWS ========== */
.deal-row { display: flex; align-items: center; gap: .9rem; padding: .65rem .75rem; border-radius: 12px; transition: background .2s; }
.deal-row:hover { background: rgba(0,0,0,.015); }
.deal-pos { font-size: .95rem; font-weight: 800; color: var(--border); min-width: 22px; text-align: center; }
.deal-stage-pip { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.deal-body { flex: 1; min-width: 0; }
.deal-nm { font-weight: 700; font-size: .88rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.deal-sub { font-size: .72rem; color: var(--text-muted); font-weight: 500; margin-top: .1rem; }
.deal-val { text-align: right; }
.deal-amt { font-weight: 800; font-size: .95rem; color: #10b981; }
.deal-pct { font-size: .68rem; color: var(--text-muted); font-weight: 600; margin-top: .1rem; }
.prob-track { height: 4px; background: var(--border); border-radius: 4px; margin-top: .3rem; overflow: hidden; }
.prob-fill  { height: 100%; border-radius: 4px; transition: width 1.2s ease-out; }

/* ========== SELLER ROWS ========== */
.seller-row { display: flex; align-items: center; gap: .85rem; padding: .6rem .65rem; border-radius: 12px; transition: background .2s; }
.seller-row:hover { background: rgba(0,0,0,.015); }
.seller-av { width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg,#6366f1,#a855f7); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .82rem; }
.seller-body { flex: 1; min-width: 0; }
.seller-nm { font-weight: 700; font-size: .88rem; color: var(--text-main); }
.seller-sub { font-size: .72rem; color: var(--text-muted); }
.seller-num { font-weight: 800; font-size: .88rem; color: #10b981; background: rgba(16,185,129,.08); padding: .2rem .5rem; border-radius: 6px; }

/* ========== ACTIVITY TIMELINE ========== */
.timeline { max-height: 340px; overflow-y: auto; padding-right: .3rem; }
.timeline::-webkit-scrollbar { width: 4px; }
.timeline::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
.tl-item { display: flex; gap: .75rem; padding: .55rem 0; position: relative; }
.tl-item + .tl-item { border-top: 1px solid rgba(0,0,0,.03); }
.tl-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
.tl-body { flex: 1; min-width: 0; }
.tl-text { font-size: .82rem; font-weight: 600; color: var(--text-main); line-height: 1.35; }
.tl-time { font-size: .72rem; color: var(--text-muted); margin-top: .1rem; font-weight: 500; }

/* ========== FUNNEL ========== */
.funnel-stage { display: flex; align-items: center; gap: .75rem; padding: .55rem .65rem; border-radius: 10px; transition: background .2s; }
.funnel-stage:hover { background: rgba(0,0,0,.015); }
.funnel-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
.funnel-name { flex: 1; font-size: .85rem; font-weight: 600; color: var(--text-main); }
.funnel-count { font-size: .82rem; font-weight: 800; color: var(--text-main); min-width: 26px; text-align: center; }
.funnel-bar-bg { flex: 2; height: 6px; background: var(--border); border-radius: 6px; overflow: hidden; }
.funnel-bar-fill { height: 100%; border-radius: 6px; transition: width 1s ease; }
</style>

<!-- ═══════════════ GREETING ═══════════════ -->
<div class="dash-greeting">
    <h1>Hola, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?></h1>
    <p>Resumen de <?= htmlspecialchars($tenantName) ?> · <?= date('d \d\e F, Y') ?></p>
</div>

<!-- ═══════════════ KPI STRIP ═══════════════ -->
<div class="kpi-strip">
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(16,185,129,.1);color:#10b981;"><i class="fas fa-money-bill-wave"></i></div>
            <span class="kpi-tag" style="background:rgba(16,185,129,.1);color:#10b981;">Cerradas</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalWon, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Ventas ganadas</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(99,102,241,.1);color:#6366f1;"><i class="fas fa-chart-bar"></i></div>
            <span class="kpi-tag" style="background:rgba(99,102,241,.1);color:#6366f1;"><?= $openDealsCount ?> abiertas</span>
        </div>
        <div class="kpi-val">$<?= number_format($totalPipeline, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Pipeline activo</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(245,158,11,.1);color:#f59e0b;"><i class="fas fa-bullseye"></i></div>
            <span class="kpi-tag" style="background:rgba(245,158,11,.1);color:#f59e0b;"><?= $closedDeals ?> cerrados</span>
        </div>
        <div class="kpi-val"><?= $conversionRate ?>%</div>
        <div class="kpi-lbl">Tasa de cierre</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(59,130,246,.1);color:#3b82f6;"><i class="fas fa-users"></i></div>
            <span class="kpi-tag" style="background:rgba(59,130,246,.1);color:#3b82f6;"><?= $totalAccounts ?> empresas</span>
        </div>
        <div class="kpi-val"><?= $totalContacts ?></div>
        <div class="kpi-lbl">Contactos</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background:rgba(168,85,247,.1);color:#a855f7;"><i class="fas fa-ruler-combined"></i></div>
            <span class="kpi-tag" style="background:rgba(168,85,247,.1);color:#a855f7;">Promedio</span>
        </div>
        <div class="kpi-val">$<?= number_format($avgDealSize, 0, '.', ',') ?></div>
        <div class="kpi-lbl">Ticket promedio</div>
    </div>
</div>

<!-- ═══════════════ ROW 1: Revenue + Donut + Funnel ═══════════════ -->
<div class="dash-grid g-3">
    <!-- Tendencia de Ingresos -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(16,185,129,.08);color:#10b981;"><i class="fas fa-chart-line"></i></div> Tendencia de Ingresos</div>
            <span class="panel-badge" style="background:rgba(99,102,241,.08);color:#6366f1;">6 meses</span>
        </div>
        <div style="position:relative;height:260px;"><canvas id="revenueChart"></canvas></div>
    </div>
    <!-- Negocios por Etapa -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(168,85,247,.08);color:#a855f7;"><i class="fas fa-chart-pie"></i></div> Negocios por Etapa</div>
        </div>
        <div style="position:relative;height:260px;"><canvas id="stageChart"></canvas></div>
    </div>
    <!-- Embudo de Ventas -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;"><i class="fas fa-hourglass-half"></i></div> Embudo de Ventas</div>
        </div>
        <?php if (!empty($dealsSummary)):
            $maxCount = max(array_map(fn($s) => (int)$s->deal_count, $dealsSummary)) ?: 1;
        ?>
            <?php foreach ($dealsSummary as $stage): 
                $pct = round(((int)$stage->deal_count / $maxCount) * 100);
                $color = $stage->color ?? '#94a3b8';
            ?>
            <div class="funnel-stage">
                <div class="funnel-dot" style="background:<?= htmlspecialchars($color) ?>;"></div>
                <div class="funnel-name"><?= htmlspecialchars($stage->name) ?></div>
                <div class="funnel-count"><?= $stage->deal_count ?></div>
                <div class="funnel-bar-bg">
                    <div class="funnel-bar-fill" style="width:<?= $pct ?>%;background:<?= htmlspecialchars($color) ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin etapas configuradas.</p>
        <?php endif; ?>
    </div>
</div>

<!-- ═══════════════ ROW 2: Equipos (solo si hay datos) ═══════════════ -->
<?php if (!empty($chart_tipos) || !empty($chart_estados)): ?>
<div class="dash-grid <?= (!empty($chart_tipos) && !empty($chart_estados)) ? 'g-2' : '' ?>">
    <?php if (!empty($chart_tipos)): ?>
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(0,45,98,.08);color:#002D62;"><i class="fas fa-laptop"></i></div> Tipos de Equipo</div>
        </div>
        <div style="position:relative;height:240px;"><canvas id="chartTipos"></canvas></div>
    </div>
    <?php endif; ?>
    <?php if (!empty($chart_estados)): ?>
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(25,135,84,.08);color:#198754;"><i class="fas fa-box"></i></div> Estado Inventario</div>
        </div>
        <div style="position:relative;height:240px;"><canvas id="chartEstados"></canvas></div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ═══════════════ ROW 3: Top Deals + Sellers + Activity ═══════════════ -->
<div class="dash-grid g-3">
    <!-- Top Deals -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(239,68,68,.08);color:#ef4444;"><i class="fas fa-fire"></i></div> Oportunidades Top</div>
            <span class="panel-badge" style="background:rgba(16,185,129,.08);color:#10b981;">Abiertas</span>
        </div>
        <?php if (empty($topOpenDeals)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin oportunidades abiertas.</p>
        <?php else: ?>
            <?php foreach ($topOpenDeals as $i => $deal): ?>
            <div class="deal-row">
                <div class="deal-pos"><?= $i+1 ?></div>
                <div class="deal-stage-pip" style="background:<?= htmlspecialchars($deal->stage_color ?? '#94a3b8') ?>;"></div>
                <div class="deal-body">
                    <div class="deal-nm"><?= htmlspecialchars($deal->name) ?></div>
                    <div class="deal-sub"><?= htmlspecialchars($deal->account_name ?? '—') ?> · <?= htmlspecialchars($deal->stage_name ?? '—') ?></div>
                    <div class="prob-track"><div class="prob-fill" style="width:<?= (int)$deal->probability ?>%;background:<?= htmlspecialchars($deal->stage_color ?? '#6366f1') ?>;"></div></div>
                </div>
                <div class="deal-val">
                    <div class="deal-amt">$<?= number_format((float)$deal->amount, 0, '.', ',') ?></div>
                    <div class="deal-pct"><?= (int)$deal->probability ?>%</div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sellers -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(99,102,241,.08);color:#6366f1;"><i class="fas fa-trophy"></i></div> Ranking Vendedores</div>
        </div>
        <?php if (empty($statsByOwner)): ?>
            <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin datos.</p>
        <?php else: ?>
            <?php foreach ($statsByOwner as $seller): 
                $init = strtoupper(substr($seller->owner_name, 0, 1));
            ?>
            <div class="seller-row">
                <div class="seller-av"><?= $init ?></div>
                <div class="seller-body">
                    <div class="seller-nm"><?= htmlspecialchars($seller->owner_name) ?></div>
                    <div class="seller-sub"><?= $seller->total_deals ?> deals · <?= $seller->won_deals ?> ganados</div>
                </div>
                <div class="seller-num">$<?= number_format((float)$seller->won_amount, 0, '.', ',') ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Activity -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;"><i class="fas fa-bolt"></i></div> Actividad Reciente</div>
        </div>
        <div class="timeline">
            <?php if (empty($recentActivities)): ?>
                <p style="color:var(--text-muted);text-align:center;padding:2rem .5rem;font-size:.88rem;">Sin actividad.</p>
            <?php else: ?>
                <?php foreach ($recentActivities as $act):
                    $icons  = ['create'=>'<i class="fas fa-star"></i>','update'=>'<i class="fas fa-pencil-alt"></i>','delete'=>'<i class="fas fa-trash"></i>','update_stage'=>'<i class="fas fa-layer-group"></i>','update_probability'=>'<i class="fas fa-percentage"></i>'];
                    $colors = ['create'=>'rgba(16,185,129,.12)','update'=>'rgba(99,102,241,.12)','delete'=>'rgba(239,68,68,.12)','update_stage'=>'rgba(245,158,11,.12)','update_probability'=>'rgba(168,85,247,.12)'];
                    $lbls   = ['create'=>'Creó','update'=>'Actualizó','delete'=>'Eliminó','update_stage'=>'Movió etapa','update_probability'=>'Cambió prob.'];
                    $icon   = $icons[$act->action] ?? '<i class="fas fa-thumbtack"></i>';
                    $bg     = $colors[$act->action] ?? 'rgba(99,102,241,.12)';
                    $lbl    = $lbls[$act->action] ?? $act->action;
                    $time   = (new DateTime($act->created_at))->format('d M, H:i');
                ?>
                <div class="tl-item">
                    <div class="tl-icon" style="background:<?= $bg ?>;"><?= $icon ?></div>
                    <div class="tl-body">
                        <div class="tl-text">
                            <?= htmlspecialchars($act->user_name ?? 'Sistema') ?>
                            <span style="color:var(--text-muted);font-weight:400;"> <?= $lbl ?> </span>
                            <?= htmlspecialchars($act->entity_type) ?><?php if ($act->entity_id): ?> #<?= $act->entity_id ?><?php endif; ?>
                        </div>
                        <div class="tl-time"><?= $time ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- ═══════════════ Tareas de Hoy ═══════════════ -->
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(59,130,246,.08);color:#3b82f6;"><i class="fas fa-check-square"></i></div> Mis Tareas de Hoy</div>
            <a href="/crm_einsurglobal/public/tareas" style="font-size:0.8rem;color:#6366f1;font-weight:600;text-decoration:none;">Ver bitácora <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="activity-list">
            <?php if (!empty($pendingTasks)): ?>
                <?php foreach ($pendingTasks as $task): ?>
                    <div class="activity-item">
                        <div class="activity-icon" style="background: #e0f2fe; color: #0284c7;">
                            <?php if ($task->related_type === 'deal'): ?>
                                <i class="fas fa-handshake"></i>
                            <?php else: ?>
                                <i class="fas fa-phone"></i>
                            <?php endif; ?>
                        </div>
                        <div class="activity-details" style="display:flex; justify-content:space-between; align-items:center; width:100%;">
                            <div>
                                <div class="activity-desc">
                                    <strong style="color: #1e293b;"><?= htmlspecialchars($task->title) ?></strong>
                                </div>
                                <div class="activity-time" style="margin-top: 0.2rem;">
                                    <?php if ($task->related_type === 'deal'): ?>
                                        Trato: <?= htmlspecialchars($task->deal_name) ?>
                                    <?php elseif ($task->related_type === 'contact'): ?>
                                        Contacto: <?= htmlspecialchars($task->contact_first_name) ?>
                                    <?php endif; ?>
                                    &bull; <strong style="color:#ef4444;"><?= date('h:i A', strtotime($task->due_date)) ?></strong>
                                </div>
                            </div>
                            <form action="/crm_einsurglobal/public/tareas/complete" method="POST">
                                <input type="hidden" name="id" value="<?= $task->id ?>">
                                <input type="hidden" name="redirect" value="/crm_einsurglobal/public/dashboard">
                                <button type="submit" class="btn" style="background: #f1f5f9; padding: 0.3rem 0.6rem; color: #166534;" title="Completar">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align:center;color:#94a3b8;padding:2rem;">
                    <i class="fas fa-glass-cheers" style="font-size: 2rem; margin-bottom: 0.5rem; color: #cbd5e1;"></i><br>
                    ¡Todo al día! No tienes tareas pendientes.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ═══════════════ CHART.JS ═══════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Outfit', sans-serif";
Chart.defaults.font.weight = '500';

const monthLabels = <?= $chartMonthLabels ?? '[]' ?>;
const wonAmounts  = <?= $chartWonAmounts ?? '[]' ?>;
const lostAmounts = <?= $chartLostAmounts ?? '[]' ?>;
const stageLabels = <?= $stageLabels ?? '[]' ?>;
const stageCounts = <?= $stageCounts ?? '[]' ?>;
const stageColors = <?= $stageColors ?? '[]' ?>;

const textColor = '#64748b';
const gridColor = 'rgba(0,0,0,0.04)';

// ── Revenue Trend ──────────────────────────
const rCtx = document.getElementById('revenueChart').getContext('2d');
const gG = rCtx.createLinearGradient(0,0,0,280);
gG.addColorStop(0,'rgba(16,185,129,.3)'); gG.addColorStop(1,'rgba(16,185,129,.01)');
const rG = rCtx.createLinearGradient(0,0,0,280);
rG.addColorStop(0,'rgba(239,68,68,.2)'); rG.addColorStop(1,'rgba(239,68,68,.01)');

new Chart(rCtx,{
    type:'line',
    data:{
        labels: monthLabels.length ? monthLabels : ['Sin datos'],
        datasets:[
            { label:'Ganado', data:wonAmounts, borderColor:'#10b981', backgroundColor:gG, borderWidth:2.5, pointBackgroundColor:'#10b981', pointRadius:4, pointHoverRadius:6, tension:.4, fill:true },
            { label:'Perdido', data:lostAmounts, borderColor:'#ef4444', backgroundColor:rG, borderWidth:2, pointBackgroundColor:'#ef4444', pointRadius:3, pointHoverRadius:5, tension:.4, fill:true }
        ]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        interaction:{ mode:'index', intersect:false },
        plugins:{
            legend:{ labels:{ color:textColor, font:{weight:'600'}, usePointStyle:true, pointStyle:'circle', padding:16 } },
            tooltip:{ callbacks:{ label: ctx => ` $${ctx.parsed.y.toLocaleString('es-MX')}` } }
        },
        scales:{
            x:{ grid:{display:false}, ticks:{color:textColor,font:{size:11}} },
            y:{ grid:{color:gridColor}, ticks:{ color:textColor, callback: v => '$'+(v>=1000?(v/1000).toFixed(0)+'K':v) }, border:{dash:[4,4]} }
        }
    }
});

// ── Stage Donut ────────────────────────────
const sCtx = document.getElementById('stageChart').getContext('2d');
const fL = stageLabels.filter((_,i)=>stageCounts[i]>0);
const fC = stageCounts.filter(c=>c>0);
const fCo = stageColors.filter((_,i)=>stageCounts[i]>0);

new Chart(sCtx,{
    type:'doughnut',
    data:{
        labels: fL.length?fL:['Sin datos'],
        datasets:[{ data:fC.length?fC:[1], backgroundColor:fCo.length?fCo:['#94a3b8'], borderColor:'#fff', borderWidth:3, hoverOffset:6 }]
    },
    options:{
        responsive:true, maintainAspectRatio:false, cutout:'70%',
        plugins:{
            legend:{ position:'bottom', labels:{color:textColor,padding:12,font:{size:11,weight:'600'},boxWidth:10,usePointStyle:true,pointStyle:'circle'} },
            tooltip:{ callbacks:{ label: ctx=>` ${ctx.label}: ${ctx.parsed}` } }
        }
    }
});

// ── Equipment: Types (Donut) ───────────────
<?php if (!empty($chart_tipos)): ?>
const tCtx = document.getElementById('chartTipos').getContext('2d');
new Chart(tCtx,{
    type:'doughnut',
    data:{
        labels:[<?php foreach($chart_tipos as $c) echo "'".htmlspecialchars($c['tipo_equipo'])."',"; ?>],
        datasets:[{
            data:[<?php foreach($chart_tipos as $c) echo $c['cantidad'].","; ?>],
            backgroundColor:['#002D62','#0aa2c0','#198754','#ffc107','#dc3545','#6f42c1','#fd7e14','#20c997','#6c757d','#0dcaf0'],
            borderWidth:2, borderColor:'#fff', hoverOffset:5
        }]
    },
    options:{
        responsive:true, maintainAspectRatio:false, cutout:'62%',
        plugins:{
            legend:{position:'right',labels:{color:textColor,padding:10,font:{size:10,weight:'600'},boxWidth:10,usePointStyle:true,pointStyle:'circle'}},
            tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.parsed}`}}
        }
    }
});
<?php endif; ?>

// ── Equipment: States (Pie) ────────────────
<?php if (!empty($chart_estados)): ?>
const eCtx = document.getElementById('chartEstados').getContext('2d');
new Chart(eCtx,{
    type:'pie',
    data:{
        labels:[<?php foreach($chart_estados as $c) echo "'".htmlspecialchars($c['estado'])."',"; ?>],
        datasets:[{
            data:[<?php foreach($chart_estados as $c) echo $c['cantidad'].","; ?>],
            backgroundColor:['#002D62','#dc3545','#198754','#343a40','#ffc107'],
            borderWidth:2, borderColor:'#fff', hoverOffset:5
        }]
    },
    options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{
            legend:{position:'right',labels:{color:textColor,padding:10,font:{size:10,weight:'600'},boxWidth:10,usePointStyle:true,pointStyle:'circle'}},
            tooltip:{callbacks:{label:ctx=>` ${ctx.label}: ${ctx.parsed}`}}
        }
    }
});
<?php endif; ?>
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
