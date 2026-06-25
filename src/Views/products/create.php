<?php
$pageTitle = 'Nuevo Producto - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Registrar Nuevo Producto / Equipo</h1>
        <p>Añade un nuevo ítem a tu catálogo de inventario.</p>
    </div>
    <a href="<?= url('/productos') ?>" class="btn" style="background: var(--surface); border: 1px solid var(--border);">Volver</a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="<?= url('/productos') ?>" method="POST">
        <h3 style="font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Información Básica</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="sku">SKU / Código</label>
                <input type="text" id="sku" name="sku" class="form-control" placeholder="Ej: EQ-001 (Automático si se deja en blanco)">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="name">Nombre del Producto *</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="Ej: Máquina de Rayos X Portátil">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descripción / Características</label>
            <textarea id="description" name="description" class="form-control" rows="3"></textarea>
        </div>

        <h3 style="font-size: 1.2rem; margin-top: 2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Precios e Inventario Inicial</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="cost_price">Precio de Costo</label>
                <input type="number" step="0.01" id="cost_price" name="cost_price" class="form-control" value="0.00">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="unit_price">Precio de Venta</label>
                <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" value="0.00">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="initial_stock">Inventario Inicial</label>
                <input type="number" step="1" id="initial_stock" name="initial_stock" class="form-control" value="0">
            </div>
        </div>

        <div style="margin-top: 2.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Guardar Producto</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
