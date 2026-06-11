<?php
$pageTitle = 'Reportes - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Reportes y Exportación</h1>
        <p>Genera reportes de ventas y descárgalos en Excel (CSV).</p>
    </div>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Exportar Oportunidades de Venta</h3>
    
    <form action="/crm_einsurglobal/public/reportes/exportar-ventas" method="GET">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="start_date">Fecha de Inicio</label>
                <input type="date" id="start_date" name="start_date" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="end_date">Fecha de Fin</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="seller_id">Filtrar por Vendedor</label>
                <select id="seller_id" name="seller_id" class="form-control">
                    <option value="">Todos los vendedores</option>
                    <?php foreach ($vendedores as $vendedor): ?>
                        <option value="<?= $vendedor->id ?>">
                            <?= htmlspecialchars($vendedor->first_name . ' ' . $vendedor->last_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="status">Estado del Trato</label>
                <select id="status" name="status" class="form-control">
                    <option value="all">Cualquier estado</option>
                    <option value="open">Solo Abiertos</option>
                    <option value="won">Solo Ganados</option>
                    <option value="lost">Solo Perdidos</option>
                </select>
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                <i class="fas fa-file-excel"></i> Descargar Reporte (CSV)
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
