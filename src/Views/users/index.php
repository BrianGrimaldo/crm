<?php
$pageTitle = 'Usuarios del Equipo - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Directorio de Usuarios</h1>
        <p>Administra las cuentas de los miembros de tu equipo en este espacio de trabajo.</p>
    </div>
    <?php if (\App\Core\Permission::has('users', 'create')): ?>
    <a href="<?= url('/usuarios/create') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
        Invitar Usuario
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo Electrónico</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem;">
                            <p style="color: var(--text-muted);">No se encontraron usuarios.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                        <?= strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)) ?>
                                    </div>
                                    <strong><?= htmlspecialchars($u->first_name . ' ' . $u->last_name) ?></strong>
                                    <?php if ($u->is_owner): ?>
                                        <span style="background: #FEF3C7; color: #D97706; padding: 0.1rem 0.4rem; border-radius: 10px; font-size: 0.6rem; font-weight: bold;">PROPIETARIO</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u->email) ?></td>
                            <td><?= htmlspecialchars($u->phone ?? '-') ?></td>
                            <td>
                                <span style="background: var(--surface); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; border: 1px solid var(--border);">
                                    <?= htmlspecialchars($u->role_name ?? 'Usuario Estándar') ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u->is_active): ?>
                                    <span style="color: #10B981; font-weight: 600;">Activo</span>
                                <?php else: ?>
                                    <span style="color: var(--error); font-weight: 600;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- Acciones por ahora deshabilitadas en UI hasta completarlas -->
                                <button class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.8rem; opacity: 0.5;" disabled>Editar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
