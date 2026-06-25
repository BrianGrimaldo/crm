<?php
$pageTitle = 'Editar Empresa - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Empresa: <?= htmlspecialchars($empresa->name) ?></h1>
        <p>Actualiza la información de la compañía y su logotipo.</p>
    </div>
    <a href="<?= url('/empresas') ?>" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="<?= url('/empresas/update') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $empresa->id ?>">
        
        <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Datos de la Empresa</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="name">Nombre de la Empresa *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($empresa->name) ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="logo">Actualizar Logotipo</label>
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <?php if ($empresa->logo_url): ?>
                        <img src="<?= htmlspecialchars($empresa->logo_url) ?>" alt="Logo Actual" style="height: 45px; border-radius: 4px; border: 1px solid var(--border);">
                    <?php endif; ?>
                    <input type="file" id="logo" name="logo" class="form-control" accept="image/*" style="flex: 1;">
                </div>
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Sube uno nuevo solo si deseas reemplazar el actual.</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="email">Correo de Contacto</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($empresa->email ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($empresa->phone ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Dirección Fiscal / Operativa</label>
            <textarea id="address" name="address" class="form-control" rows="2"><?= htmlspecialchars($empresa->address ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="currency_code">Moneda *</label>
                <select id="currency_code" name="currency_code" class="form-control" required>
                    <option value="MXN" <?= $empresa->currency_code === 'MXN' ? 'selected' : '' ?>>Pesos Mexicanos (MXN)</option>
                    <option value="USD" <?= $empresa->currency_code === 'USD' ? 'selected' : '' ?>>Dólares (USD)</option>
                    <option value="EUR" <?= $empresa->currency_code === 'EUR' ? 'selected' : '' ?>>Euros (EUR)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem; margin-top: 1.8rem;">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= $empresa->is_active ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                <label for="is_active" style="margin-bottom: 0; cursor: pointer;">Empresa Activa</label>
            </div>
        </div>

        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
