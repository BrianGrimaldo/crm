<?php
$pageTitle = 'Embudo de Ventas - Einsur Global CRM';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Embudo de Ventas (Pipeline)</h1>
        <p>Configura las etapas por las que pasan tus tratos.</p>
    </div>
    <a href="/crm_einsurglobal/public/configuracion/embudo/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> AÃ±adir Etapa
    </a>
</div>

<div class="card table-responsive">
    <table>
        <thead>
            <tr>
                <th>PosiciÃ³n</th>
                <th>Nombre</th>
                <th>Color</th>
                <th>Probabilidad</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stages as $stage): ?>
            <tr>
                <td><strong><?= htmlspecialchars((string)$stage->position) ?></strong></td>
                <td><?= htmlspecialchars($stage->name) ?></td>
                <td>
                    <span style="display: inline-block; width: 16px; height: 16px; border-radius: 50%; background-color: <?= htmlspecialchars($stage->color) ?>; vertical-align: middle; margin-right: 8px;"></span>
                    <?= htmlspecialchars($stage->color) ?>
                </td>
                <td><?= htmlspecialchars((string)$stage->probability) ?>%</td>
                <td>
                    <?php if ($stage->is_won): ?>
                        <span style="background: #dcfce7; color: #166534; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Ganada</span>
                    <?php elseif ($stage->is_lost): ?>
                        <span style="background: #fee2e2; color: #991b1b; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Perdida</span>
                    <?php else: ?>
                        <span style="background: #e0f2fe; color: #075985; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">Abierta</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="/crm_einsurglobal/public/configuracion/embudo/edit?id=<?= $stage->id ?>" class="btn" style="padding: 0.4rem 0.8rem; background: #f1f5f9; color: #334155;">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($stages)): ?>
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted);">No hay etapas configuradas.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
