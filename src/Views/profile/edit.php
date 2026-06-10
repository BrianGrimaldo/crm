<?php
$pageTitle = 'Mi Perfil - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Mi Perfil</h1>
        <p>Actualiza tus datos personales y credenciales de acceso.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
    <!-- Información Visual / Avatar -->
    <div class="card" style="padding: 2rem; text-align: center; height: fit-content;">
        <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: bold; margin: 0 auto 1.5rem auto; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);">
            <?= strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name ?? '', 0, 1)) ?>
        </div>
        <h3 style="margin-bottom: 0.5rem; font-size: 1.5rem; color: var(--text-main);">
            <?= htmlspecialchars($user->first_name . ' ' . ($user->last_name ?? '')) ?>
        </h3>
        <p style="color: var(--text-muted); margin-bottom: 0.5rem;"><?= htmlspecialchars($user->email) ?></p>
        <span style="display: inline-block; background: var(--bg-sidebar); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
            Rol: <?= htmlspecialchars(ucfirst($_SESSION['user_role'] ?? 'Usuario')) ?>
        </span>
    </div>

    <!-- Formulario de Edición -->
    <div class="card" style="padding: 2rem;">
        <form action="/crm_einsurglobal/public/profile/update" method="POST">
            <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Datos Personales</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="first_name">Nombre *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($user->first_name) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="last_name">Apellidos *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($user->last_name ?? '') ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="email">Correo Electrónico (Solo Lectura)</label>
                    <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($user->email) ?>" disabled style="background-color: var(--bg-main); cursor: not-allowed;">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">El correo es tu identificador único y no puede cambiarse.</small>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="phone">Teléfono / Celular</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user->phone ?? '') ?>">
                </div>
            </div>

            <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Cambiar Contraseña</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;">Déjalo en blanco si no deseas cambiar tu contraseña actual.</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••">
                </div>
            </div>

            <div style="text-align: right; margin-top: 2rem;">
                <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
