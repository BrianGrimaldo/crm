<?php
$pageTitle = 'Editar Producto - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Producto: <?= htmlspecialchars($product->name) ?></h1>
    </div>
    <a href="<?= url('/productos') ?>" class="btn" style="background: var(--surface); border: 1px solid var(--border);">Volver</a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem; margin-bottom: 2rem;">
    <form action="<?= url('/productos/update') ?>" method="POST">
        <input type="hidden" name="id" value="<?= $product->id ?>">
        
        <h3 style="font-size: 1.2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Información Básica</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="sku">SKU / Código</label>
                <input type="text" id="sku" name="sku" class="form-control" value="<?= htmlspecialchars($product->sku) ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="name">Nombre del Producto *</label>
                <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($product->name) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descripción / Características</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($product->description ?? '') ?></textarea>
        </div>

        <h3 style="font-size: 1.2rem; margin-top: 2rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Precios e Inventario Actual</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="cost_price">Precio de Costo</label>
                <input type="number" step="0.01" id="cost_price" name="cost_price" class="form-control" value="<?= $product->cost_price ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="unit_price">Precio de Venta</label>
                <input type="number" step="0.01" id="unit_price" name="unit_price" class="form-control" value="<?= $product->unit_price ?>">
            </div>
        </div>
        
        <div style="background: var(--bg-main); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h4 style="margin: 0; color: var(--text-main);">Stock Actual Disponible</h4>
                    <span style="font-size: 2rem; font-weight: bold; color: #0f172a;"><?= number_format((float)$product->quantity, 2) ?></span>
                </div>
                <div class="form-group" style="margin-bottom: 0; width: 200px;">
                    <label for="add_stock" style="color: #2563eb; font-weight: 600;"><i class="fas fa-plus-circle"></i> Ingresar Nuevo Stock</label>
                    <input type="number" step="1" id="add_stock" name="add_stock" class="form-control" value="0" placeholder="Cantidad a sumar">
                </div>
            </div>
        </div>

        <div style="margin-top: 2.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Actualizar Producto</button>
        </div>
    </form>
</div>

<div class="card" style="max-width: 800px; padding: 2rem; border-top: 4px solid #ef4444;">
    <h3 style="color: #ef4444; margin-top: 0;">Zona de Peligro</h3>
    <p style="color: var(--text-muted);">Al eliminar este producto, dejará de estar disponible para nuevas ventas o asignaciones de inventario.</p>
    <form action="<?= url('/productos/delete') ?>" method="POST" onsubmit="return confirm('¿Estás seguro de inactivar este producto?');">
        <input type="hidden" name="id" value="<?= $product->id ?>">
        <button type="submit" class="btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444;">
            <i class="fas fa-trash"></i> Eliminar Producto
        </button>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
