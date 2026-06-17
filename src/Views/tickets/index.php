<?php
$pageTitle = 'Soporte y Tickets - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Módulo de Soporte y Tickets</h1>
        <p>Monitorea y resuelve las solicitudes de soporte técnico de tus clientes.</p>
    </div>
    <?php if (\App\Core\Permission::has('tickets', 'create')): ?>
    <a href="/tickets/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Ticket
    </a>
    <?php endif; ?>
</div>

<!-- Tarjetas de Resumen KPI de Soporte -->
<div class="kpi-strip" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 2rem;">
    <?php
    $openCount = 0; $inProgCount = 0; $highCount = 0; $resolvedCount = 0;
    foreach ($tickets as $t) {
        if ($t->status === 'open') $openCount++;
        if ($t->status === 'in_progress') $inProgCount++;
        if ($t->status === 'resolved' || $t->status === 'closed') $resolvedCount++;
        if ($t->priority === 'high' || $t->priority === 'urgent') $highCount++;
    }
    ?>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background: rgba(99,102,241,.1); color: #6366f1;"><i class="fas fa-envelope-open"></i></div>
            <span class="kpi-tag" style="background: rgba(99,102,241,.1); color: #6366f1;">Nuevos</span>
        </div>
        <div class="kpi-val"><?= $openCount ?></div>
        <div class="kpi-lbl">Tickets Abiertos</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background: rgba(245,158,11,.1); color: #f59e0b;"><i class="fas fa-spinner"></i></div>
            <span class="kpi-tag" style="background: rgba(245,158,11,.1); color: #f59e0b;">En Proceso</span>
        </div>
        <div class="kpi-val"><?= $inProgCount ?></div>
        <div class="kpi-lbl">En curso</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background: rgba(239,68,68,.1); color: #ef4444;"><i class="fas fa-exclamation-triangle"></i></div>
            <span class="kpi-tag" style="background: rgba(239,68,68,.1); color: #ef4444;">Urgentes</span>
        </div>
        <div class="kpi-val"><?= $highCount ?></div>
        <div class="kpi-lbl">Alta Prioridad / Críticos</div>
    </div>
    <div class="kpi">
        <div class="kpi-top">
            <div class="kpi-dot" style="background: rgba(16,185,129,.1); color: #10b981;"><i class="fas fa-check-circle"></i></div>
            <span class="kpi-tag" style="background: rgba(16,185,129,.1); color: #10b981;">Completados</span>
        </div>
        <div class="kpi-val"><?= $resolvedCount ?></div>
        <div class="kpi-lbl">Resueltos / Cerrados</div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Asunto</th>
                    <th>Prioridad</th>
                    <th>Estado</th>
                    <th>Contacto</th>
                    <th>Organización</th>
                    <th>Asignado A</th>
                    <th>Canal</th>
                    <th>Creado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tickets)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 3rem;">
                            <p style="color: var(--text-muted);">No se encontraron solicitudes de soporte abiertas.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td>
                                <a href="/tickets/show?id=<?= $t->id ?>" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                    <?= htmlspecialchars($t->subject) ?>
                                </a>
                                <br><small style="color: var(--text-muted);"><?= htmlspecialchars($t->category ?? 'General') ?></small>
                            </td>
                            <td>
                                <?php
                                $pColor = match($t->priority) {
                                    'urgent' => '#ef4444',
                                    'high'   => '#f97316',
                                    'medium' => '#3b82f6',
                                    'low'    => '#64748b',
                                    default  => '#64748b'
                                };
                                ?>
                                <span style="font-weight:700; color: <?= $pColor ?>;">
                                    <i class="fas fa-circle" style="font-size:0.6rem; margin-right:0.3rem;"></i><?= strtoupper($t->priority) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $sClass = match($t->status) {
                                    'open'        => 'background: rgba(99,102,241,.1); color: #6366f1;',
                                    'in_progress' => 'background: rgba(245,158,11,.1); color: #f59e0b;',
                                    'waiting'     => 'background: rgba(99,102,241,.1); color: #6366f1;',
                                    'resolved'    => 'background: rgba(16,185,129,.1); color: #10b981;',
                                    'closed'      => 'background: rgba(100,116,139,.1); color: #64748b;',
                                    default       => 'background: rgba(100,116,139,.1); color: #64748b;'
                                };
                                ?>
                                <span style="padding: 0.25rem 0.6rem; border-radius: 8px; font-weight:700; font-size:0.75rem; text-transform: uppercase; <?= $sClass ?>">
                                    <?= str_replace('_', ' ', $t->status) ?>
                                </span>
                            </td>
                            <td><?= $t->contact_first ? htmlspecialchars($t->contact_first . ' ' . $t->contact_last) : '-' ?></td>
                            <td><?= htmlspecialchars($t->account_name ?? '-') ?></td>
                            <td>
                                <span style="font-weight: 600;"><i class="fas fa-user" style="font-size:0.8rem; color: var(--text-muted); margin-right:0.3rem;"></i><?= htmlspecialchars($t->assigned_name ?? 'Sin Asignar') ?></span>
                            </td>
                            <td>
                                <span style="font-size: 1.1rem;" title="Canal: <?= $t->channel ?>">
                                    <?= match($t->channel) {
                                        'email'  => '<i class="fas fa-envelope" style="color:#3b82f6;"></i>',
                                        'phone'  => '<i class="fas fa-phone" style="color:#10b981;"></i>',
                                        'chat'   => '<i class="fab fa-whatsapp" style="color:#25d366;"></i>',
                                        'social' => '<i class="fab fa-facebook" style="color:#1877f2;"></i>',
                                        default  => '<i class="fas fa-globe" style="color:#64748b;"></i>'
                                    } ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($t->created_at)) ?></td>
                            <td>
                                <a href="/tickets/show?id=<?= $t->id ?>" style="color: var(--primary-hover); text-decoration: none; font-weight: 600;">Atender</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
