<?php
$pageTitle = 'Nueva Oportunidad - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Crear Oportunidad de Venta</h1>
        <p>Añade un nuevo prospecto o trato a tu embudo.</p>
    </div>
    <a href="/oportunidades/pipeline" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver al Pipeline
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/oportunidades" method="POST">
        <div class="form-group">
            <label for="name">Nombre de la Oportunidad *</label>
            <input type="text" id="name" name="name" class="form-control" placeholder="Ej. Venta de licencias anuales" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="account_id">Organización</label>
                <select id="account_id" name="account_id" class="form-control">
                    <option value="">-- Seleccionar Organización --</option>
                    <?php if(!empty($accounts)): foreach($accounts as $account): ?>
                        <option value="<?= $account->id ?>"><?= htmlspecialchars($account->name) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="contact_id">Contacto asociado</label>
                <select id="contact_id" name="contact_id" class="form-control">
                    <option value="">-- Seleccionar Contacto --</option>
                    <?php foreach ($contacts as $contact): ?>
                        <option value="<?= $contact->id ?>"><?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="stage_id">Etapa Inicial *</label>
                <select id="stage_id" name="stage_id" class="form-control" required>
                    <?php foreach ($stages as $stage): ?>
                        <option value="<?= $stage->id ?>"><?= htmlspecialchars($stage->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="amount">Valor (Monto Estimado)</label>
                <input type="number" step="0.01" id="amount" name="amount" class="form-control" placeholder="0.00">
            </div>
            <div class="form-group">
                <label for="probability">Probabilidad (%)</label>
                <input type="number" min="0" max="100" id="probability" name="probability" class="form-control" placeholder="Ej. 50">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="expected_close_date">Fecha</label>
                <input type="date" id="expected_close_date" name="expected_close_date" class="form-control">
            </div>
            <div class="form-group">
                <label for="source">Fuente</label>
                <select id="source" name="source" class="form-control">
                    <option value="">-- Seleccionar --</option>
                    <option value="Redes sociales">Redes sociales</option>
                    <option value="Campaña de correo">Campaña de correo</option>
                    <option value="Centro de llamadas">Centro de llamadas</option>
                    <option value="Recomendación">Recomendación</option>
                    <option value="Web">Web</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Notas / Descripción</label>
            <textarea id="description" name="description" class="form-control" rows="4" placeholder="Detalles de la oportunidad..."></textarea>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Guardar Oportunidad
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
