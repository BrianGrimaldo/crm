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
        <form action="/crm_einsurglobal/public/perfil/update" method="POST" enctype="multipart/form-data">
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••">
                </div>
            </div>

            <!-- ═══════════════ CONFIGURACIÓN SMTP ═══════════════ -->
            <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 0.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <i class="fas fa-envelope" style="color: var(--accent); margin-right: 0.4rem;"></i> Configuración de Correo (SMTP)
            </h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                Configura tus credenciales SMTP para enviar correos desde <strong>tu propia cuenta de correo</strong> a los contactos del CRM.
                Si usas Gmail, necesitas una <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--accent); font-weight: 600;">Contraseña de Aplicación</a>.
                Si usas Outlook/Hotmail, activa el acceso SMTP en tu cuenta.
            </p>

            <?php
                $smtpConfigured = !empty($user->smtp_host) && !empty($user->smtp_email);
            ?>
            <?php if ($smtpConfigured): ?>
                <div style="background: #dcfce7; color: #166534; padding: 0.8rem 1.2rem; border-radius: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; border: 1px solid #bbf7d0;">
                    <i class="fas fa-check-circle"></i> <strong>Correo SMTP configurado:</strong> <?= htmlspecialchars($user->smtp_email) ?>
                </div>
            <?php else: ?>
                <div style="background: #fef3c7; color: #92400e; padding: 0.8rem 1.2rem; border-radius: 10px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; border: 1px solid #fde68a;">
                    <i class="fas fa-exclamation-triangle"></i> No has configurado tus credenciales SMTP. Los correos se enviarán desde la cuenta genérica del sistema.
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="smtp_host">Servidor SMTP</label>
                    <input type="text" id="smtp_host" name="smtp_host" class="form-control" 
                           value="<?= htmlspecialchars($user->smtp_host ?? '') ?>" 
                           placeholder="Ej: smtp.gmail.com, smtp.office365.com">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="smtp_port">Puerto</label>
                    <input type="number" id="smtp_port" name="smtp_port" class="form-control" 
                           value="<?= htmlspecialchars((string)($user->smtp_port ?? 587)) ?>" 
                           placeholder="587">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="smtp_email">Correo para Envío</label>
                    <input type="email" id="smtp_email" name="smtp_email" class="form-control" 
                           value="<?= htmlspecialchars($user->smtp_email ?? '') ?>" 
                           placeholder="tu.correo@empresa.com">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="smtp_password">Contraseña / App Password</label>
                    <input type="password" id="smtp_password" name="smtp_password" class="form-control" 
                           placeholder="<?= !empty($user->smtp_password) ? '••••••••  (ya configurada)' : 'Tu contraseña SMTP' ?>">
                    <?php if (!empty($user->smtp_password)): ?>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Déjalo vacío para mantener la contraseña actual.</small>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="smtp_encryption">Tipo de Encriptación</label>
                    <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                        <option value="tls" <?= ($user->smtp_encryption ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (Recomendado - Puerto 587)</option>
                        <option value="ssl" <?= ($user->smtp_encryption ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Puerto 465)</option>
                        <option value="none" <?= ($user->smtp_encryption ?? '') === 'none' ? 'selected' : '' ?>>Ninguna</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="smtp_from_name">Nombre del Remitente</label>
                    <input type="text" id="smtp_from_name" name="smtp_from_name" class="form-control" 
                           value="<?= htmlspecialchars($user->smtp_from_name ?? '') ?>" 
                           placeholder="Ej: Juan Pérez - Einsur Global">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px dashed #cbd5e1;">
                <label for="signature_logo" style="color: #334155; font-weight: 600;">Logo para la Firma (Aplica para todos los vendedores)</label>
                <input type="file" id="signature_logo" name="signature_logo" class="form-control" accept="image/png, image/jpeg" style="background: white;">
                <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Sube aquí la imagen del logo de la empresa (PNG o JPG). Si la subes, reemplazará el logo actual y todos los vendedores comenzarán a usarla automáticamente en sus correos.</small>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label for="email_signature">Texto de la Firma (Escribe tus datos, el sistema le agregará el logo automáticamente)</label>
                <textarea id="email_signature" name="email_signature" class="form-control" rows="6" placeholder="Atentamente

ING. MARTHA VAZQUEZ
SISTEMAS
(921) 224 17 65
www.einsursupply.com"><?= htmlspecialchars(strip_tags($user->email_signature ?? '')) ?></textarea>
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Escribe tu nombre, puesto y contacto. El sistema colocará automáticamente el logo oficial de EINSUR a la izquierda de este texto.</small>
            </div>

            <!-- Guía rápida -->
            <div style="background: #f1f5f9; padding: 1rem 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                <p style="font-weight: 700; font-size: 0.85rem; color: var(--text-main); margin-bottom: 0.5rem;"><i class="fas fa-info-circle" style="color: var(--accent);"></i> Guía Rápida por Proveedor</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; font-size: 0.8rem; color: var(--text-muted);">
                    <div>
                        <strong style="color: var(--text-main);">Gmail</strong><br>
                        Host: smtp.gmail.com<br>
                        Puerto: 587 | TLS<br>
                        <a href="https://myaccount.google.com/apppasswords" target="_blank" style="color: var(--accent);">Generar App Password</a>
                    </div>
                    <div>
                        <strong style="color: var(--text-main);">Outlook / Hotmail</strong><br>
                        Host: smtp.office365.com<br>
                        Puerto: 587 | TLS
                    </div>
                    <div>
                        <strong style="color: var(--text-main);">cPanel / Hosting</strong><br>
                        Host: mail.tudominio.com<br>
                        Puerto: 587 | TLS o 465 | SSL
                    </div>
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
