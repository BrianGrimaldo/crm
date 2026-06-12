<?php
$pageTitle = 'Editar Etapa - Einsur Global CRM';
require __DIR__ . '/../../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Etapa de Venta</h1>
    </div>
    <a href="/crm_einsurglobal/public/configuracion/embudo" class="btn" style="background: var(--surface); border: 1px solid var(--border);">Volver</a>
</div>

<div class="card" style="max-width: 600px; padding: 2rem;">
    <form action="/crm_einsurglobal/public/configuracion/embudo/update" method="POST" style="margin-bottom: 2rem;">
        <input type="hidden" name="id" value="<?= $stage->id ?>">
        
        <div class="form-group">
            <label for="name">Nombre de la Etapa *</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($stage->name) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="position">Posición (Orden)</label>
                <input type="number" id="position" name="position" class="form-control" value="<?= $stage->position ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="probability">Probabilidad (%)</label>
                <input type="number" id="probability" name="probability" class="form-control" min="0" max="100" value="<?= $stage->probability ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="color">Color</label>
            <input type="color" id="color" name="color" class="form-control" value="<?= htmlspecialchars($stage->color) ?>" style="height: 40px; padding: 0.2rem;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <input type="checkbox" name="is_won" value="1" <?= $stage->is_won ? 'checked' : '' ?>> Marca el trato como GANADO al entrar a esta etapa
            </label>
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_lost" value="1" <?= $stage->is_lost ? 'checked' : '' ?>> Marca el trato como PERDIDO al entrar a esta etapa
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Etapa</button>
    </form>
    
    <form action="/crm_einsurglobal/public/configuracion/embudo/delete" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta etapa? Si hay tratos en ella, podrías tener problemas.');">
        <input type="hidden" name="id" value="<?= $stage->id ?>">
        <button type="submit" class="btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444; width: 100%;">
            <i class="fas fa-trash"></i> Eliminar Etapa
        </button>
    </form>
</div>

<?php require __DIR__ . '/../../layouts/footer.php'; ?>
