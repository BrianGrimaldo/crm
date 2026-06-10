<?php
$pageTitle = 'Nuevo Rol - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';

// Traducciones de módulos
$moduleLabels = [
    'leads' => 'Prospectos', 'accounts' => 'Organizaciones', 'contacts' => 'Contactos',
    'deals' => 'Ventas / Oportunidades', 'products' => 'Productos', 'inventory' => 'Inventario',
    'tickets' => 'Soporte / Tickets', 'tasks' => 'Tareas', 'events' => 'Eventos',
    'notes' => 'Notas', 'reports' => 'Reportes', 'settings' => 'Configuración', 'users' => 'Usuarios',
];
$actionLabels = [
    'view' => 'Ver', 'create' => 'Crear', 'update' => 'Editar', 'delete' => 'Eliminar',
    'export' => 'Exportar', 'manage' => 'Gestionar',
];
?>

<div class="page-header">
    <div>
        <h1>Crear Nuevo Rol</h1>
        <p>Define un rol y asigna permisos por módulo.</p>
    </div>
    <a href="/crm_einsurglobal/public/roles" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a Roles
    </a>
</div>

<div class="card" style="padding: 2rem;">
    <form action="/crm_einsurglobal/public/roles" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group">
                <label for="name">Nombre del Rol *</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Ej: Gerente de Marketing">
            </div>
            <div class="form-group">
                <label for="description">Descripción</label>
                <input type="text" id="description" name="description" class="form-control" placeholder="Breve descripción de las responsabilidades">
            </div>
        </div>

        <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.5rem;">Matriz de Permisos</h3>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Selecciona las acciones permitidas para cada módulo del sistema.</p>

        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
            <button type="button" onclick="toggleAll(true)" class="btn" style="background: var(--bg-sidebar); color: white; padding: 0.4rem 1rem; font-size: 0.85rem;">Seleccionar Todo</button>
            <button type="button" onclick="toggleAll(false)" class="btn" style="background: var(--border); color: var(--text-main); padding: 0.4rem 1rem; font-size: 0.85rem;">Deseleccionar Todo</button>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="min-width: 200px;">Módulo</th>
                        <th style="text-align: center;">Ver</th>
                        <th style="text-align: center;">Crear</th>
                        <th style="text-align: center;">Editar</th>
                        <th style="text-align: center;">Eliminar</th>
                        <th style="text-align: center;">Exportar</th>
                        <th style="text-align: center;">Gestionar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allPermissions as $module => $permissions): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($moduleLabels[$module] ?? ucfirst($module)) ?></strong>
                                <br><small style="color: var(--text-muted);"><?= htmlspecialchars($module) ?></small>
                            </td>
                            <?php
                            $actions = ['view', 'create', 'update', 'delete', 'export', 'manage'];
                            foreach ($actions as $action):
                                $found = null;
                                foreach ($permissions as $p) {
                                    if ($p->action === $action) { $found = $p; break; }
                                }
                            ?>
                                <td style="text-align: center;">
                                    <?php if ($found): ?>
                                        <label style="cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                            <input type="checkbox" name="permissions[]" value="<?= $found->id ?>"
                                                   style="width: 18px; height: 18px; cursor: pointer; accent-color: var(--bg-sidebar);">
                                        </label>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1;">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Crear Rol
            </button>
        </div>
    </form>
</div>

<script>
function toggleAll(state) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = state);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
