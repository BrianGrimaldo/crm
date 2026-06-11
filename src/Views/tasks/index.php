<?php
$pageTitle = 'Bitácora de Tareas - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Bitácora de Tareas</h1>
        <p>Da seguimiento a tus llamadas, reuniones y compromisos.</p>
    </div>
    <a href="/crm_einsurglobal/public/tareas/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Tarea
    </a>
</div>

<div class="card table-responsive">
    <table>
        <thead>
            <tr>
                <th>Estado</th>
                <th>Fecha Límite</th>
                <th>Tarea / Actividad</th>
                <th>Relacionado a</th>
                <th>Prioridad</th>
                <th>Vendedor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task): ?>
            <tr style="<?= $task->status === 'completed' ? 'opacity: 0.6;' : '' ?>">
                <td>
                    <?php if ($task->status === 'completed'): ?>
                        <span style="background: #dcfce7; color: #166534; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-check"></i> Completada</span>
                    <?php elseif ($task->status === 'cancelled'): ?>
                        <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-times"></i> Cancelada</span>
                    <?php elseif ($task->due_date && strtotime($task->due_date) < time()): ?>
                        <span style="background: #fef08a; color: #854d0e; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-exclamation-triangle"></i> Vencida</span>
                    <?php else: ?>
                        <span style="background: #e0f2fe; color: #075985; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;"><i class="fas fa-clock"></i> Pendiente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?= $task->due_date ? date('d/M Y h:i A', strtotime($task->due_date)) : '<span style="color:var(--text-muted)">Sin fecha</span>' ?>
                </td>
                <td>
                    <strong><?= htmlspecialchars($task->title) ?></strong>
                </td>
                <td>
                    <?php if ($task->related_type === 'deal'): ?>
                        <i class="fas fa-handshake" style="color: #6366f1;"></i> <?= htmlspecialchars($task->deal_name) ?>
                    <?php elseif ($task->related_type === 'contact'): ?>
                        <i class="fas fa-user" style="color: #10b981;"></i> <?= htmlspecialchars($task->contact_first_name . ' ' . $task->contact_last_name) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                    $colors = ['low' => '#94a3b8', 'medium' => '#3b82f6', 'high' => '#f59e0b', 'urgent' => '#ef4444'];
                    $color = $colors[$task->priority] ?? '#94a3b8';
                    ?>
                    <strong style="color: <?= $color ?>; text-transform: uppercase; font-size: 0.8rem;"><?= htmlspecialchars($task->priority) ?></strong>
                </td>
                <td><?= htmlspecialchars($task->first_name . ' ' . $task->last_name) ?></td>
                <td>
                    <?php if ($task->status !== 'completed'): ?>
                    <form action="/crm_einsurglobal/public/tareas/complete" method="POST" style="display:inline-block;">
                        <input type="hidden" name="id" value="<?= $task->id ?>">
                        <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; background: #dcfce7; color: #166534;" title="Marcar como Completada">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    <a href="/crm_einsurglobal/public/tareas/edit?id=<?= $task->id ?>" class="btn" style="padding: 0.4rem 0.8rem; background: #f1f5f9; color: #334155;">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($tasks)): ?>
            <tr>
                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No tienes tareas registradas. ¡Disfruta tu día libre!</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
