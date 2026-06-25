<?php
$pageTitle = 'Organizaciones - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Directorio de Organizaciones</h1>
        <p>Gestiona las empresas con las que interactúas.</p>
    </div>
    <?php if (\App\Core\Permission::has('accounts', 'create')): ?>
    <a href="<?= url('/organizaciones/create') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nueva Organización
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; gap: 1rem;">
        <form action="<?= url('/organizaciones') ?>" method="GET" style="display: flex; gap: 1rem; flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre, sitio web..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="type" class="form-control" style="max-width: 200px;">
                <option value="">Todos los Tipos</option>
                <option value="Prospecto" <?= ($_GET['type'] ?? '') == 'Prospecto' ? 'selected' : '' ?>>Prospecto</option>
                <option value="Cliente" <?= ($_GET['type'] ?? '') == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="Otro" <?= ($_GET['type'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
            </select>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
                <a href="<?= url('/organizaciones') ?>" class="btn" style="background: var(--border); color: var(--text-main);">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table style="min-width: 1400px;">
            <thead>
                <tr>
                    <th>Prioridad</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Gerente de Cuenta</th>
                    <th>Sitio web</th>
                    <th>LinkedIn</th>
                    <th>Teléfono</th>
                    <th>País</th>
                    <th>Dirección</th>
                    <th>Ciudad</th>
                    <th>Código Postal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($accounts)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 3rem;">
                            <p style="color: var(--text-muted);">No se encontraron organizaciones.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td>
                                <span style="font-weight: 700; color: <?= ($account->priority ?? 'B') === 'A+' ? '#EF4444' : (($account->priority ?? 'B') === 'A' ? '#F59E0B' : '#10B981') ?>;">
                                    <?= htmlspecialchars($account->priority ?? 'B') ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($account->name) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($account->type ?? 'customer') ?></td>
                            <td><?= htmlspecialchars($account->owner_name ?? 'Sin asignar') ?></td>
                            <td>
                                <?php if (!empty($account->website)): ?>
                                    <a href="<?= htmlspecialchars($account->website) ?>" target="_blank" style="color: var(--primary); text-decoration: none;"><?= htmlspecialchars($account->website) ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($account->linkedin)): ?>
                                    <a href="<?= htmlspecialchars($account->linkedin) ?>" target="_blank" style="color: var(--primary); text-decoration: none;">Ver Perfil</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($account->phone ?? '-') ?></td>
                            <td><?= htmlspecialchars($account->country ?? '-') ?></td>
                            <td><?= htmlspecialchars($account->billing_address ?? '-') ?></td>
                            <td><?= htmlspecialchars($account->city ?? '-') ?></td>
                            <td><?= htmlspecialchars($account->postal_code ?? '-') ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?php if (\App\Core\Permission::has('accounts', 'update')): ?>
                                        <a href="<?= url('/organizaciones/edit?id=' . $account->id) ?>" style="color: var(--primary-hover); text-decoration: none; font-weight: 600;">Editar</a>
                                    <?php endif; ?>
                                    <?php if (\App\Core\Permission::has('accounts', 'delete')): ?>
                                        <form action="<?= url('/organizaciones/delete') ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta organización?');" style="display:inline; margin:0;">
                                            <input type="hidden" name="id" value="<?= $account->id ?>">
                                            <button type="submit" style="background: none; border: none; color: var(--error); cursor: pointer; font-weight: 600; font-family: inherit; font-size: inherit;">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
