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
.dash-greeting { margin-bottom: 2rem; }
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

/* ========== KPI STRIP ========== */
.kpi-strip { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
@media(max-width:1200px){ .kpi-strip{ grid-template-columns: repeat(3,1fr); } }
@media(max-width:768px){ .kpi-strip{ grid-template-columns: repeat(2,1fr); } }
@media(max-width:500px){ .kpi-strip{ grid-template-columns: 1fr; } }

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

/* ========== GRID SYSTEM ========== */
.dash-grid { display: grid; gap: 1.5rem; margin-bottom: 1.5rem; }
.dash-grid.g-2-1 { grid-template-columns: 2fr 1fr; }
.dash-grid.g-1-1 { grid-template-columns: 1fr 1fr; }
.dash-grid.g-3   { grid-template-columns: repeat(3, 1fr); }
.dash-grid.g-2   { grid-template-columns: repeat(2, 1fr); }
.dash-grid.g-4   { grid-template-columns: repeat(4, 1fr); }
@media(max-width:1100px){ .dash-grid.g-3, .dash-grid.g-4 { grid-template-columns: repeat(2, 1fr); } }
@media(max-width:768px){ .dash-grid.g-2-1, .dash-grid.g-1-1, .dash-grid.g-3, .dash-grid.g-2, .dash-grid.g-4 { grid-template-columns: 1fr; } }

/* ========== PANEL CARD ========== */
.panel {
    background: var(--surface); border: 1px solid rgba(0,0,0,0.04); border-radius: 20px;
    padding: 1.5rem; box-shadow: var(--shadow-md); transition: all .3s;
}
.panel:hover { box-shadow: var(--shadow-lg); }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.4rem; padding-bottom: 0.8rem; border-bottom: 1px solid rgba(0,0,0,0.03); }
.panel-title { font-size: 1.05rem; font-weight: 800; color: var(--primary); display: flex; align-items: center; gap: .5rem; }
.panel-title-icon { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; background: var(--primary-light); color: var(--primary); }
.panel-badge { font-size: .7rem; font-weight: 800; padding: .25rem .6rem; border-radius: 8px; }

/* ========== DEAL ROWS ========== */
.deal-row { display: flex; align-items: center; gap: 1rem; padding: .8rem; border-radius: 14px; transition: all .2s ease; border: 1px solid transparent; }
.deal-row:hover { background: #fdfdfd; border-color: rgba(0,0,0,0.03); transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
.deal-pos { font-size: .95rem; font-weight: 800; color: var(--border); min-width: 24px; text-align: center; }
.deal-stage-pip { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 8px currentColor; }
.deal-body { flex: 1; min-width: 0; }
.deal-nm { font-weight: 700; font-size: .92rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.deal-sub { font-size: .75rem; color: var(--text-muted); font-weight: 500; margin-top: .15rem; }
.deal-val { text-align: right; }
.deal-amt { font-weight: 800; font-size: 1rem; color: #059669; }
.deal-pct { font-size: .7rem; color: var(--text-muted); font-weight: 700; margin-top: .15rem; }
.prob-track { height: 5px; background: rgba(0,0,0,0.04); border-radius: 5px; margin-top: .4rem; overflow: hidden; }
.prob-fill  { height: 100%; border-radius: 5px; transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: inset 0 2px 4px rgba(255,255,255,0.3); }

/* ========== SELLER ROWS ========== */
.seller-row { display: flex; align-items: center; gap: .9rem; padding: .75rem; border-radius: 14px; transition: all .2s; border: 1px solid transparent; }
.seller-row:hover { background: #fdfdfd; border-color: rgba(0,0,0,0.03); transform: scale(1.01); box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
.seller-av { width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .9rem; box-shadow: 0 4px 10px rgba(0, 45, 98, 0.2); }
.seller-body { flex: 1; min-width: 0; }
.seller-nm { font-weight: 700; font-size: .92rem; color: var(--text-main); }
.seller-sub { font-size: .75rem; color: var(--text-muted); }
.seller-num { font-weight: 800; font-size: .95rem; color: #059669; background: #d1fae5; padding: .25rem .6rem; border-radius: 8px; }

/* ========== ACTIVITY TIMELINE ========== */
.timeline { max-height: 360px; overflow-y: auto; padding-right: .5rem; }
.timeline::-webkit-scrollbar { width: 5px; }
.timeline::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 5px; }
.tl-item { display: flex; gap: .85rem; padding: .75rem 0; position: relative; }
.tl-item + .tl-item { border-top: 1px dashed rgba(0,0,0,.06); }
.tl-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; background: #f1f5f9; color: var(--primary); }
.tl-body { flex: 1; min-width: 0; }
.tl-text { font-size: .88rem; font-weight: 600; color: var(--text-main); line-height: 1.4; }
.tl-text strong { color: var(--primary); }
.tl-time { font-size: .75rem; color: var(--text-muted); margin-top: .2rem; font-weight: 500; display: flex; align-items: center; gap: .3rem; }

/* ========== FUNNEL ========== */
.funnel-stage { display: flex; align-items: center; gap: .85rem; padding: .65rem .75rem; border-radius: 12px; transition: all .2s; }
.funnel-stage:hover { background: rgba(0,0,0,.015); transform: translateX(4px); }
.funnel-dot { width: 12px; height: 12px; border-radius: 4px; flex-shrink: 0; }
.funnel-name { flex: 1; font-size: .88rem; font-weight: 700; color: var(--text-main); }
.funnel-count { font-size: .88rem; font-weight: 800; color: var(--text-main); min-width: 28px; text-align: center; }
.funnel-bar-bg { flex: 2; height: 8px; background: rgba(0,0,0,0.04); border-radius: 8px; overflow: hidden; }
.funnel-bar-fill { height: 100%; border-radius: 8px; transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1); }
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
    <div class="panel" style="cursor:pointer;" onclick="window.location.href='/analiticas'">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(16,185,129,.08);color:#10b981;"><i class="fas fa-chart-line"></i></div> Tendencia de Ingresos</div>
            <a href="/analiticas" style="font-size:0.75rem;color:#6366f1;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">Ver completo <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i></a>
        </div>
        <div style="position:relative;height:260px;"><canvas id="revenueChart"></canvas></div>
    </div>
    <!-- Negocios por Etapa -->
    <div class="panel" style="cursor:pointer;" onclick="window.location.href='/analiticas'">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(168,85,247,.08);color:#a855f7;"><i class="fas fa-chart-pie"></i></div> Negocios por Etapa</div>
            <a href="/analiticas" style="font-size:0.75rem;color:#6366f1;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">Ver completo <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i></a>
        </div>
        <div style="position:relative;height:260px;"><canvas id="stageChart"></canvas></div>
    </div>
    <!-- Embudo de Ventas -->
    <div class="panel" style="cursor:pointer;" onclick="window.location.href='/analiticas'">
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;"><i class="fas fa-hourglass-half"></i></div> Embudo de Ventas</div>
            <a href="/analiticas" style="font-size:0.75rem;color:#6366f1;font-weight:700;text-decoration:none;display:flex;align-items:center;gap:0.3rem;">Ver completo <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i></a>
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
<?php endif; ?>
    
    <!-- ═══════════════ Tareas de Hoy (visible para todos) ═══════════════ -->
<?php if ($isManager): ?>
    <div class="panel">
<?php else: ?>
    <div class="dash-grid" style="grid-template-columns: 1fr;">
    <div class="panel">
<?php endif; ?>
        <div class="panel-head">
            <div class="panel-title"><div class="panel-title-icon" style="background:rgba(59,130,246,.08);color:#3b82f6;"><i class="fas fa-check-square"></i></div> Mis Tareas de Hoy</div>
            <a href="/tareas" style="font-size:0.8rem;color:#6366f1;font-weight:600;text-decoration:none;">Ver bitácora <i class="fas fa-arrow-right"></i></a>
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
                            <form action="/tareas/complete" method="POST">
                                <input type="hidden" name="id" value="<?= $task->id ?>">
                                <input type="hidden" name="redirect" value="/dashboard">
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
<?php if ($isManager): ?>
</div>
<?php else: ?>
</div>
<?php endif; ?>

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

// Inventory charts removed
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
