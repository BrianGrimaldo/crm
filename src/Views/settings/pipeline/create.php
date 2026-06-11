<?php
$pageTitle = 'Nueva Etapa - Einsur Global CRM';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>AÃ±adir Etapa de Venta</h1>
        <p>Crea una nueva etapa para el embudo.</p>
    </div>
    <a href="/crm_einsurglobal/public/configuracion/embudo" class="btn" style="background: var(--surface); border: 1px solid var(--border);">Volver</a>
</div>

<div class="card" style="max-width: 600px; padding: 2rem;">
    <form action="/crm_einsurglobal/public/configuracion/embudo" method="POST">
        <div class="form-group">
            <label for="name">Nombre de la Etapa *</label>
            <input type="text" id="name" name="name" class="form-control" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="position">PosiciÃ³n (Orden)</label>
                <input type="number" id="position" name="position" class="form-control" value="0" placeholder="0 = Al final">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="probability">Probabilidad (%)</label>
                <input type="number" id="probability" name="probability" class="form-control" min="0" max="100" value="50">
            </div>
        </div>

        <div class="form-group">
            <label for="color">Color</label>
            <input type="color" id="color" name="color" class="form-control" value="#94A3B8" style="height: 40px; padding: 0.2rem;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <input type="checkbox" name="is_won" value="1"> Marca el trato como GANADO al entrar a esta etapa
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_lost" value="1"> Marca el trato como PERDIDO al entrar a esta etapa
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Guardar Etapa</button>
    </form>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
