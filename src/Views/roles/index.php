<?php
$pageTitle = 'Roles del Sistema - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Gestión de Roles</h1>
        <p>Administra los roles de acceso y sus permisos en el sistema.</p>
    </div>
    <a href="/crm_einsurglobal/public/roles/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nuevo Rol
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Usuarios</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($roles)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem;">
                            <p style="color: var(--text-muted);">No se encontraron roles.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($role->name) ?></strong>
                            </td>
                            <td>
                                <code style="background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($role->slug) ?></code>
                            </td>
                            <td style="color: var(--text-muted);"><?= htmlspecialchars($role->description ?? '-') ?></td>
                            <td>
                                <span style="background: var(--primary); color: var(--primary-text); padding: 0.15rem 0.6rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem;">
                                    <?= (int)$role->user_count ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($role->is_system): ?>
                                    <span style="background: #DBEAFE; color: #1E40AF; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">SISTEMA</span>
                                <?php else: ?>
                                    <span style="background: #F0FDF4; color: #166534; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">PERSONALIZADO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <a href="/crm_einsurglobal/public/roles/edit?id=<?= $role->id ?>" style="color: var(--primary-hover); text-decoration: none; font-weight: 600;">Permisos</a>
                                    <?php if (!$role->is_system): ?>
                                        <form action="/crm_einsurglobal/public/roles/delete" method="POST" onsubmit="return confirm('¿Eliminar este rol? Los usuarios asignados perderán sus permisos.');" style="display:inline; margin:0;">
                                            <input type="hidden" name="id" value="<?= $role->id ?>">
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
