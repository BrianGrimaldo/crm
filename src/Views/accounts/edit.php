<?php
$pageTitle = 'Editar Organización - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Organización</h1>
        <p>Actualiza la información de la empresa.</p>
    </div>
    <a href="/crm_einsurglobal/public/accounts" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a la Lista
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/crm_einsurglobal/public/accounts/update" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$account->id) ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="name">Nombre de Organización *</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($account->name) ?>" required>
            </div>
            <div class="form-group">
                <label for="type">Tipo</label>
                <select id="type" name="type" class="form-control">
                    <option value="Prospecto" <?= ($account->type ?? '') == 'Prospecto' ? 'selected' : '' ?>>Prospecto</option>
                    <option value="Cliente" <?= ($account->type ?? '') == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="Otro" <?= ($account->type ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="priority">Prioridad</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="A+" <?= ($account->priority ?? '') == 'A+' ? 'selected' : '' ?>>A+</option>
                    <option value="A" <?= ($account->priority ?? '') == 'A' ? 'selected' : '' ?>>A</option>
                    <option value="B" <?= ($account->priority ?? '') == 'B' ? 'selected' : '' ?>>B</option>
                    <option value="C" <?= ($account->priority ?? '') == 'C' ? 'selected' : '' ?>>C</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($account->phone ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="website">Sitio Web</label>
                <input type="url" id="website" name="website" class="form-control" value="<?= htmlspecialchars($account->website ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="linkedin">URL de LinkedIn</label>
                <input type="url" id="linkedin" name="linkedin" class="form-control" value="<?= htmlspecialchars($account->linkedin ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="country">País</label>
                <input type="text" id="country" name="country" class="form-control" value="<?= htmlspecialchars($account->country ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($account->city ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="postal_code">Código Postal</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?= htmlspecialchars($account->postal_code ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="billing_address">Dirección Física</label>
                <input type="text" id="billing_address" name="billing_address" class="form-control" value="<?= htmlspecialchars($account->billing_address ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notas / Comentarios</label>
            <textarea id="notes" name="notes" class="form-control" rows="4"><?= htmlspecialchars($account->notes ?? '') ?></textarea>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Actualizar Organización
            </button>
        </div>
    </form>
</div>

<?php 
$entityType = 'account';
$entityId = $account->id;
require __DIR__ . '/../partials/activities_log.php'; 
?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
