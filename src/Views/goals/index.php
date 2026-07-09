<?php
$pageTitle = 'Metas y Objetivos - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

// Variables inyectadas desde GoalsController::index()
$goals       = $goals ?? [];
$tenantUsers = $tenantUsers ?? [];
$period      = $period ?? ['start' => date('Y-m-01'), 'end' => date('Y-m-t'), 'type' => 'monthly'];

$periodStart = $period['start'];
$periodType  = $period['type'];
$isSuperadmin = \App\Core\Permission::isSuperadmin();
$isVendedor   = \App\Core\Permission::isVendedor();
?>

<style>
    .goals-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .filters-bar {
        display: flex;
        gap: 1rem;
        background: var(--surface);
        padding: 1rem;
        border-radius: 12px;
        border: 1px solid var(--border);
        margin-bottom: 1.5rem;
    }
    .filters-bar select, .filters-bar input {
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: 6px;
        background: var(--bg-main);
        color: var(--text-main);
    }
    .goal-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .goal-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .goal-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    .goal-owner {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .goal-tenant-badge {
        font-size: 0.7rem;
        background: rgba(110,223,246,.12);
        color: var(--accent);
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        margin-top: 0.25rem;
        display: inline-block;
    }
    .goal-type {
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .goal-progress-wrap {
        margin-top: 1rem;
    }
    .goal-bar-bg {
        height: 10px;
        background: rgba(128,128,128,0.15);
        border-radius: 10px;
        overflow: hidden;
    }
    .goal-bar-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
    }
    .goal-stats {
        display: flex;
        justify-content: space-between;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .btn-action {
        background: none;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 0.2rem 0.5rem;
        transition: color 0.2s;
    }
    .btn-action:hover {
        color: #ef4444;
    }
    .modal-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
    }
    .modal-overlay.active {
        opacity: 1;
        pointer-events: all;
    }
    .modal-content {
        background: var(--surface);
        padding: 2rem;
        border-radius: 12px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-muted);
    }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--bg-main);
        color: var(--text-main);
        font-family: inherit;
        font-size: 0.95rem;
    }
    .btn-submit-goal {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-submit-goal:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    .spinner-sm {
        display: none;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="goals-header">
    <div>
        <h1><i class="fas fa-bullseye" style="color:var(--accent);margin-right:0.5rem;"></i> Metas y Objetivos</h1>
        <p style="color:var(--text-muted);">Configura y monitorea el avance de ventas y cobranza.</p>
    </div>
    <?php if (!$isVendedor): ?>
        <button class="btn btn-primary" onclick="openGoalModal()">
            <i class="fas fa-plus"></i> Nueva Meta
        </button>
    <?php endif; ?>
</div>

<div class="filters-bar">
    <form method="GET" action="<?= url('/metas') ?>" style="display:flex; gap:1rem; align-items:center; width:100%;">
        <div>
            <label style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.2rem; display:block;">Tipo de Periodo</label>
            <select name="period_type" onchange="this.form.submit()">
                <option value="monthly" <?= $periodType === 'monthly' ? 'selected' : '' ?>>Mensual</option>
                <option value="quarterly" <?= $periodType === 'quarterly' ? 'selected' : '' ?>>Trimestral</option>
            </select>
        </div>
        <div>
            <label style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.2rem; display:block;">Mes/Trimestre</label>
            <input type="date" name="period_start" value="<?= htmlspecialchars($periodStart) ?>" onchange="this.form.submit()">
        </div>
        <div>
            <label style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.2rem; display:block;">Buscar Vendedor</label>
            <input type="text" id="searchSeller" placeholder="Nombre..." onkeyup="filterGoals()">
        </div>
        <div style="margin-left: auto;">
            <button type="submit" class="btn btn-secondary">Filtrar</button>
        </div>
    </form>
</div>

<?php 
// Agrupar metas por empresa si es superadmin, o usar un solo grupo
$groupedGoals = [];
foreach ($goals as $goal) {
    $tenantGroup = $isSuperadmin && !empty($goal['tenant_name']) ? $goal['tenant_name'] : 'Metas';
    $groupedGoals[$tenantGroup][] = $goal;
}
?>

<?php if (empty($goals)): ?>
    <div style="text-align:center; padding: 3rem; background:var(--surface); border:1px solid var(--border); border-radius:12px;">
        <i class="fas fa-flag-checkered" style="font-size:3rem; color:var(--border); margin-bottom:1rem;"></i>
        <h3 style="margin-bottom:0.5rem;">No hay metas en este periodo</h3>
        <p style="color:var(--text-muted); margin-bottom:1.5rem;">Crea la primera meta para monitorear tu avance de ventas.</p>
        <?php if (!$isVendedor): ?>
            <button class="btn btn-primary" onclick="openGoalModal()">Crear Meta</button>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div id="goalsContainer">
    <?php foreach ($groupedGoals as $tenantName => $tenantGoals): ?>
        <div class="tenant-group">
            <?php if ($isSuperadmin): ?>
                <h3 style="margin: 2rem 0 1rem 0; color: var(--text-main); border-bottom: 2px solid var(--border); padding-bottom: 0.5rem;">
                    <i class="fas fa-building" style="color:var(--text-muted); margin-right:0.5rem;"></i> <?= htmlspecialchars($tenantName) ?>
                </h3>
            <?php endif; ?>
            <div class="display-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem;">
                <?php foreach ($tenantGoals as $goal):
                    $pct = (float) ($goal['progress_pct'] ?? 0);
                    $color = $pct >= 80 ? '#10b981' : (!empty($goal['is_at_risk']) ? '#ef4444' : '#f59e0b');
                    $owner = !empty($goal['owner_name']) ? $goal['owner_name'] : 'Meta del Equipo';
                    $metricLabel = $goal['metric_type'] === 'sales_won' ? 'Ventas Ganadas' : 'Facturación Cobrada';
                    $canDelete = $isSuperadmin
                        || (!$isVendedor)
                        || ($isVendedor && $goal['owner_id'] !== null && (int)$goal['owner_id'] === (int)($_SESSION['user_id'] ?? 0));
                ?>
                <div class="goal-card">
                    <div class="goal-card-header">
                        <div>
                            <div class="goal-owner"><?= htmlspecialchars($owner) ?></div>
                            <?php if ($isSuperadmin && !empty($goal['tenant_name'])): ?>
                                <span class="goal-tenant-badge"><?= htmlspecialchars($goal['tenant_name']) ?></span>
                            <?php endif; ?>
                            <div class="goal-type"><?= htmlspecialchars($metricLabel) ?></div>
                        </div>
                        <?php if ($canDelete): ?>
                        <div>
                            <button class="btn-action" onclick="deleteGoal(<?= (int) $goal['id'] ?>)" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div style="font-size:1.5rem; font-weight:800; margin-bottom:0.5rem;">
                        $<?= number_format((float)($goal['achieved_amount'] ?? 0), 2) ?>
                        <span style="font-size:1rem; color:var(--text-muted); font-weight:500;">/ $<?= number_format((float)($goal['target_amount'] ?? 0), 2) ?></span>
                    </div>

                    <div class="goal-progress-wrap">
                        <div class="goal-bar-bg">
                            <div class="goal-bar-fill" style="width: <?= min($pct, 100) ?>%; background: <?= htmlspecialchars($color) ?>;"></div>
                        </div>
                        <div class="goal-stats">
                            <span style="color:<?= htmlspecialchars($color) ?>; font-weight:700;"><?= htmlspecialchars((string) $pct) ?>% Logrado</span>
                            <span>Falta: $<?= number_format((float)($goal['remaining_amount'] ?? 0), 2) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Agregar Meta -->
<div class="modal-overlay" id="goalModal">
    <div class="modal-content">
        <h3 style="margin-bottom:1.5rem;">Configurar Meta</h3>
        <form id="goalForm" onsubmit="saveGoal(event)">
            <?php if ($isVendedor): ?>
                <input type="hidden" id="goal_owner_id" value="<?= (int) ($_SESSION['user_id'] ?? 0) ?>">
            <?php else: ?>
                <div class="form-group">
                    <label>Vendedor (Opcional, dejar en 'Meta General' si aplica a todos)</label>
                    <select id="goal_owner_id">
                        <option value="" data-tenant="<?= (int) ($_SESSION['tenant_id'] ?? 0) ?>">-- Meta General de Equipo (Empresa Actual) --</option>
                        <?php foreach ($tenantUsers as $u): ?>
                            <option value="<?= (int) $u['id'] ?>" data-tenant="<?= (int) $u['tenant_id'] ?>">
                                <?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?> (<?= htmlspecialchars($u['role']) ?> - <?= htmlspecialchars($u['tenant_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div style="display:flex; gap:1rem;">
                <div class="form-group" style="flex:1;">
                    <label>Tipo de Periodo</label>
                    <select id="goal_period_type" required>
                        <option value="monthly">Mensual</option>
                        <option value="quarterly">Trimestral</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Fecha de Inicio (Mes)</label>
                    <input type="date" id="goal_period_start" required>
                </div>
            </div>
            <div class="form-group">
                <label>Métrica</label>
                <select id="goal_metric_type" required>
                    <option value="sales_won">Ventas Ganadas</option>
                    <option value="revenue_collected">Facturación Cobrada</option>
                </select>
            </div>
            <div class="form-group">
                <label>Monto Objetivo ($)</label>
                <input type="number" step="0.01" min="0.01" id="goal_target_amount" required>
            </div>
            <div class="form-group">
                <label>Notas (Opcional)</label>
                <textarea id="goal_notes" rows="2" maxlength="255"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:1rem; margin-top:2rem;">
                <button type="button" class="btn btn-secondary" onclick="closeGoalModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-submit-goal" id="btnSaveGoal">
                    <span class="spinner-sm" id="saveSpinner"></span>
                    <span id="saveLabel">Guardar Meta</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openGoalModal() {
    document.getElementById('goal_period_start').value = new Date().toISOString().split('T')[0];
    document.getElementById('goalModal').classList.add('active');
}

function closeGoalModal() {
    document.getElementById('goalModal').classList.remove('active');
    document.getElementById('goalForm').reset();
    setSubmitLoading(false);
}

function setSubmitLoading(loading) {
    const btn = document.getElementById('btnSaveGoal');
    const spinner = document.getElementById('saveSpinner');
    const label = document.getElementById('saveLabel');
    if (!btn) return;
    btn.disabled = loading;
    spinner.style.display = loading ? 'inline-block' : 'none';
    label.textContent = loading ? 'Guardando...' : 'Guardar Meta';
}

async function saveGoal(e) {
    e.preventDefault();
    setSubmitLoading(true);

    const ownerEl = document.getElementById('goal_owner_id');
    let ownerIdVal = ownerEl ? ownerEl.value : null;
    let targetTenantId = null;

    // Extraer tenant_id del data-attribute si es un select
    if (ownerEl && ownerEl.tagName === 'SELECT') {
        const opt = ownerEl.options[ownerEl.selectedIndex];
        targetTenantId = opt ? opt.getAttribute('data-tenant') : null;
    }

    const data = {
        owner_id: ownerIdVal,
        target_tenant_id: targetTenantId,
        period_type: document.getElementById('goal_period_type').value,
        period_start: document.getElementById('goal_period_start').value,
        metric_type: document.getElementById('goal_metric_type').value,
        target_amount: document.getElementById('goal_target_amount').value,
        notes: document.getElementById('goal_notes').value
    };

    try {
        const res = await fetch('<?= url('/metas') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const body = await res.json();

        if (res.ok && body.success) {
            window.location.reload();
        } else {
            alert(body.error || 'Error al guardar la meta.');
            setSubmitLoading(false);
        }
    } catch (err) {
        console.error('saveGoal error:', err);
        alert('Error de conexión. Verifica tu red e intenta de nuevo.');
        setSubmitLoading(false);
    }
}

async function deleteGoal(id) {
    if (!confirm('¿Estás seguro de eliminar esta meta? Esta acción no se puede deshacer.')) return;

    try {
        const res = await fetch('<?= url('/metas/delete') ?>?id=' + encodeURIComponent(id), {
            method: 'POST'
        });

        const body = await res.json();

        if (res.ok && body.success) {
            window.location.reload();
        } else {
            alert(body.error || 'No se pudo eliminar la meta.');
        }
    } catch (err) {
        console.error('deleteGoal error:', err);
        alert('Error de conexión al intentar eliminar.');
    }
}

function filterGoals() {
    const searchVal = document.getElementById('searchSeller').value.toLowerCase();
    const groups = document.querySelectorAll('.tenant-group');
    
    groups.forEach(group => {
        let hasVisibleCards = false;
        const cards = group.querySelectorAll('.goal-card');
        
        cards.forEach(card => {
            const ownerName = card.querySelector('.goal-owner').textContent.toLowerCase();
            if (ownerName.includes(searchVal)) {
                card.style.display = 'block';
                hasVisibleCards = true;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Hide the whole group (tenant section) if no cards match
        if (hasVisibleCards) {
            group.style.display = 'block';
        } else {
            group.style.display = 'none';
        }
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
