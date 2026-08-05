<?php
$pageTitle = 'Editar Oportunidad - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Oportunidad</h1>
        <p>Actualiza la información y el estado de la venta.</p>
    </div>
    <a href="<?= url('/oportunidades/pipeline') ?>" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver al Pipeline
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="<?= url('/oportunidades/update') ?>" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$deal->id) ?>">
        
        <div class="form-group">
            <label for="name">Nombre de la Oportunidad *</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($deal->name) ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="account_id">Organización</label>
                <select id="account_id" name="account_id" class="form-control">
                    <option value="">-- Seleccionar Organización --</option>
                    <?php if(!empty($accounts)): foreach($accounts as $account): ?>
                        <option value="<?= $account->id ?>" <?= $deal->account_id == $account->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($account->name) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="contact_id">Contacto asociado</label>
                <select id="contact_id" name="contact_id" class="form-control">
                    <option value="">-- Seleccionar Contacto --</option>
                    <?php foreach ($contacts as $contact): ?>
                        <option value="<?= $contact->id ?>" <?= $deal->contact_id == $contact->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="stage_id">Etapa Actual *</label>
                <select id="stage_id" name="stage_id" class="form-control" required onchange="updateProbabilityAndAmount(this)">
                    <?php foreach ($stages as $stage): ?>
                        <option value="<?= $stage->id ?>" data-probability="<?= (int) $stage->probability ?>" data-position="<?= (int) $stage->position ?>" <?= $deal->stage_id == $stage->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($stage->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="amount">Valor (Monto Estimado)</label>
                <input type="number" step="0.01" id="amount" name="amount" class="form-control" value="<?= htmlspecialchars((string)$deal->amount) ?>">
            </div>
            <div class="form-group">
                <label for="probability">Probabilidad (%) <small style="color: var(--text-muted); font-weight: 400;">— Asignada por etapa</small></label>
                <input type="number" min="0" max="100" id="probability" name="probability" class="form-control" readonly
                    value="<?= htmlspecialchars((string)$deal->probability) ?>"
                    style="background: var(--bg-main); cursor: not-allowed; opacity: 0.8;">
            </div>
        </div>
        <script>
        function updateProbabilityAndAmount(select) {
            var opt = select.options[select.selectedIndex];
            var prob = opt.getAttribute('data-probability');
            document.getElementById('probability').value = prob !== null ? prob : '';
            
            var pos = parseInt(opt.getAttribute('data-position')) || 0;
            var amountInput = document.getElementById('amount');
            if (pos > 0 && pos < 4) {
                amountInput.readOnly = true;
                amountInput.value = '';
                amountInput.placeholder = 'No disponible hasta Propuesta/Cotización';
            } else {
                amountInput.readOnly = false;
                amountInput.placeholder = '0.00';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateProbabilityAndAmount(document.getElementById('stage_id'));
        });
        </script>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
            <div class="form-group">
                <label for="purchase_order">Orden de Compra</label>
                <input type="text" id="purchase_order" name="purchase_order" class="form-control" value="<?= htmlspecialchars($deal->purchase_order ?? '') ?>">
                <small style="color: var(--text-muted);">Para el área de cobranza</small>
            </div>
            <div class="form-group">
                <label for="invoice_folio">Folio de Factura</label>
                <input type="text" id="invoice_folio" name="invoice_folio" class="form-control" value="<?= htmlspecialchars($deal->invoice_folio ?? '') ?>">
                <small style="color: var(--text-muted);">Asignado tras emitir factura</small>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
            <div class="form-group">
                <label for="expected_close_date">Fecha</label>
                <input type="date" id="expected_close_date" name="expected_close_date" class="form-control" value="<?= htmlspecialchars((string)$deal->expected_close_date) ?>">
            </div>
            <div class="form-group">
                <label for="source">Fuente</label>
                <select id="source" name="source" class="form-control">
                    <option value="">-- Seleccionar --</option>
                    <option value="Redes sociales" <?= ($deal->source ?? '') == 'Redes sociales' ? 'selected' : '' ?>>Redes sociales</option>
                    <option value="Campaña de correo" <?= ($deal->source ?? '') == 'Campaña de correo' ? 'selected' : '' ?>>Campaña de correo</option>
                    <option value="Centro de llamadas" <?= ($deal->source ?? '') == 'Centro de llamadas' ? 'selected' : '' ?>>Centro de llamadas</option>
                    <option value="Recomendación" <?= ($deal->source ?? '') == 'Recomendación' ? 'selected' : '' ?>>Recomendación</option>
                    <option value="Web" <?= ($deal->source ?? '') == 'Web' ? 'selected' : '' ?>>Web</option>
                    <option value="Otro" <?= ($deal->source ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="lost_reason">Razón de pérdida (Si aplica)</label>
            <input type="text" id="lost_reason" name="lost_reason" class="form-control" value="<?= htmlspecialchars($deal->lost_reason ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Notas / Descripción</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars((string)$deal->description) ?></textarea>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Actualizar Oportunidad
            </button>
        </div>
    </form>
</div>

<?php 
$entityType = 'deal';
$entityId = $deal->id;
require __DIR__ . '/../partials/activities_log.php'; 
?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
