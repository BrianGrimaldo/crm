<?php
$pageTitle = 'Invitar Usuario - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Invitar Nuevo Usuario</h1>
        <p>Añade un nuevo miembro a tu equipo y asígnale un rol.</p>
    </div>
    <a href="/crm_einsurglobal/public/users" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver al Directorio
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/crm_einsurglobal/public/users" method="POST">
        <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Información del Usuario</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="first_name">Nombre *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required placeholder="Ej: Juan">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="last_name">Apellidos</label>
                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Ej: Pérez">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="email">Correo Electrónico *</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="correo@empresa.com">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="+52 55 1234 5678">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="password">Contraseña Temporal *</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="Asigna una contraseña inicial">
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">El usuario podrá cambiarla luego en su perfil.</small>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="role_id">Rol en el Sistema *</label>
                <select id="role_id" name="role_id" class="form-control" required>
                    <option value="">-- Selecciona un rol --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role->id ?>"><?= htmlspecialchars($role->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] && !empty($tenants)): ?>
        <div style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="tenant_id">Empresa a la que pertenecerá (Solo Superadmin) *</label>
                <select id="tenant_id" name="tenant_id" class="form-control" required onchange="updateRoles()">
                    <?php foreach ($tenants as $tenant): ?>
                        <option value="<?= $tenant->id ?>" <?= $tenant->id == $_SESSION['tenant_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tenant->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <script>
            const allRoles = <?= $allRolesJson ?? '{}' ?>;
            function updateRoles() {
                const tenantId = document.getElementById('tenant_id').value;
                const roleSelect = document.getElementById('role_id');
                const currentRoles = allRoles[tenantId] || [];
                
                roleSelect.innerHTML = '<option value="">-- Selecciona un rol --</option>';
                currentRoles.forEach(role => {
                    const option = document.createElement('option');
                    option.value = role.id;
                    option.textContent = role.name;
                    roleSelect.appendChild(option);
                });
            }
            
            // Inicializar roles en caso de que cambie la selección por defecto
            document.addEventListener('DOMContentLoaded', function() {
                updateRoles();
            });
        </script>
        <?php endif; ?>

        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Crear e Invitar Usuario
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
