<?php
$pageTitle = 'Inventario - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Inventario / Productos</h1>
        <p>Gestiona los equipos y productos disponibles en tu almacén.</p>
    </div>
    <a href="<?= url('/productos/create') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nuevo Producto
    </a>
</div>

<div class="card table-responsive">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nombre del Producto</th>
                <th>Stock Disponible</th>
                <th>Precio Unit.</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><strong style="color: var(--text-muted);"><?= htmlspecialchars($product->sku) ?></strong></td>
                <td>
                    <strong><?= htmlspecialchars($product->name) ?></strong>
                    <?php if ($product->category_name): ?>
                        <br><small style="color: var(--text-muted);"><?= htmlspecialchars($product->category_name) ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                        $qty = (float)$product->quantity;
                        $color = $qty > 0 ? '#16a34a' : '#ef4444';
                    ?>
                    <strong style="color: <?= $color ?>; font-size: 1.1rem;"><?= number_format($qty, 2) ?></strong>
                </td>
                <td>$<?= number_format((float)$product->unit_price, 2) ?></td>
                <td>
                    <a href="<?= url('/productos/edit?id=' . $product->id) ?>" class="btn" style="padding: 0.4rem 0.8rem; background: var(--border); color: var(--text-main);">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?>
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted);">No hay productos registrados en el inventario.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
