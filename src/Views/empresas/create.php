<?php
$pageTitle = 'Nueva Empresa - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Registrar Nueva Empresa</h1>
        <p>Añade una nueva compañía al sistema (Tenant).</p>
    </div>
    <a href="/empresas" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/empresas" method="POST" enctype="multipart/form-data">
        <h3 style="font-size: 1.2rem; color: var(--text-main); margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Datos de la Empresa</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="name">Nombre de la Empresa *</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Ej: Einsur Global">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="logo">Logotipo</label>
                <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">Recomendado: PNG o JPG con fondo transparente.</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="email">Correo de Contacto</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="contacto@empresa.com">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="+52 55 1234 5678">
            </div>
        </div>

        <div class="form-group">
            <label for="address">Dirección Fiscal / Operativa</label>
            <textarea id="address" name="address" class="form-control" rows="2" placeholder="Calle, Colonia, Ciudad..."></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="currency_code">Moneda *</label>
                <select id="currency_code" name="currency_code" class="form-control" required>
                    <option value="MXN">Pesos Mexicanos (MXN)</option>
                    <option value="USD">Dólares (USD)</option>
                    <option value="EUR">Euros (EUR)</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem; margin-top: 1.8rem;">
                <input type="checkbox" id="is_active" name="is_active" value="1" checked style="width: 18px; height: 18px;">
                <label for="is_active" style="margin-bottom: 0; cursor: pointer;">Empresa Activa</label>
            </div>
        </div>

        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Crear Empresa
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
