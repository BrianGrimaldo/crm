<?php
$pageTitle = 'Métricas en Tiempo Real - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header" style="margin-bottom: 1rem;">
    <div>
        <h1>Métricas en Tiempo Real</h1>
        <p>Monitorea la actividad de tu equipo y el volumen de tickets de hoy.</p>
    </div>
    <div style="font-size: 0.9rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
        <span id="liveIndicator" style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; animation: pulse 2s infinite;"></span>
        Actualizado: <span id="lastUpdated"><?= date('H:i:s') ?></span>
    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.metric-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(0,0,0,0.03);
    text-align: center;
}
.metric-value {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.metric-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}
.agent-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.8rem;
    border-bottom: 1px solid rgba(0,0,0,0.03);
}
.agent-status {
    width: 12px; height: 12px; border-radius: 50%; display: inline-block;
}
</style>

<div class="dash-grid g-3" style="margin-bottom: 2rem;">
    <div class="metric-card">
        <div class="metric-value"><?= $ticketStats->total_today ?? 0 ?></div>
        <div class="metric-label">Tickets Recibidos Hoy</div>
    </div>
    <div class="metric-card">
        <div class="metric-value"><?= $ticketStats->open_today ?? 0 ?></div>
        <div class="metric-label">Tickets Abiertos / Pendientes</div>
    </div>
    <div class="metric-card">
        <div class="metric-value"><?= $ticketStats->resolved_today ?? 0 ?></div>
        <div class="metric-label">Tickets Resueltos Hoy</div>
    </div>
</div>

<div class="dash-grid g-2-1">
    <div class="panel">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--primary);">Tipificaciones del Día</h3>
        <?php if (empty($tipStats)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 2rem;">No hay tickets resueltos hoy.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php 
                $max = max(array_column($tipStats, 'count'));
                foreach ($tipStats as $ts): 
                    $pct = ($ts->count / $max) * 100;
                ?>
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.3rem;">
                            <span><?= htmlspecialchars($ts->name) ?></span>
                            <span><?= $ts->count ?></span>
                        </div>
                        <div style="height: 8px; background: rgba(0,0,0,0.05); border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $pct ?>%; background: <?= htmlspecialchars($ts->color) ?>; border-radius: 4px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--primary);">
            Agentes Conectados (<span id="agentCount"><?= count($activeAgents) ?></span>)
        </h3>
        <div id="agentsList">
            <?php foreach($activeAgents as $agent): 
                $statusColor = match($agent->status) {
                    'online' => '#10b981',
                    'busy' => '#ef4444',
                    'away' => '#f59e0b',
                    default => '#94a3b8'
                };
            ?>
            <div class="agent-row">
                <div style="display: flex; align-items: center; gap: 0.8rem;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800;">
                        <?= strtoupper(substr($agent->user_name, 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);"><?= htmlspecialchars($agent->user_name) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);">Inactivo hace <?= $agent->minutes_idle ?> min</div>
                    </div>
                </div>
                <div class="agent-status" style="background: <?= $statusColor ?>;" title="<?= htmlspecialchars($agent->status) ?>"></div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($activeAgents)): ?>
                <p style="color: var(--text-muted); text-align: center; font-size: 0.9rem; padding: 1rem;">Nadie conectado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-refresh agents list
setInterval(() => {
    fetch('<?= url("/api/metricas/live") ?>')
        .then(res => res.json())
        .then(data => {
            document.getElementById('lastUpdated').innerText = data.timestamp;
            document.getElementById('agentCount').innerText = data.agents.length;
            
            let html = '';
            if(data.agents.length === 0) {
                html = '<p style="color: var(--text-muted); text-align: center; font-size: 0.9rem; padding: 1rem;">Nadie conectado.</p>';
            } else {
                data.agents.forEach(agent => {
                    let color = '#94a3b8';
                    if(agent.status === 'online') color = '#10b981';
                    if(agent.status === 'busy') color = '#ef4444';
                    if(agent.status === 'away') color = '#f59e0b';
                    
                    let initial = agent.user_name.charAt(0).toUpperCase();
                    html += `
                    <div class="agent-row">
                        <div style="display: flex; align-items: center; gap: 0.8rem;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                ${initial}
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">${agent.user_name}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">Inactivo hace ${agent.minutes_idle} min</div>
                            </div>
                        </div>
                        <div class="agent-status" style="background: ${color};" title="${agent.status}"></div>
                    </div>
                    `;
                });
            }
            document.getElementById('agentsList').innerHTML = html;
        });
}, 30000); // 30 seconds
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
