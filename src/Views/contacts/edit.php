<?php
$pageTitle = 'Editar Contacto - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Contacto</h1>
        <p>Actualiza la información de este contacto.</p>
    </div>
    <a href="<?= url('/contactos') ?>" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a la Lista
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="<?= url('/contactos/update') ?>" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$contact->id) ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="first_name">Nombre(s) *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" value="<?= htmlspecialchars($contact->first_name) ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Apellidos</label>
                <input type="text" id="last_name" name="last_name" class="form-control" value="<?= htmlspecialchars($contact->last_name ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($contact->email ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="phone">Teléfono / Celular</label>
                <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($contact->phone ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="type">Tipo</label>
                <select id="type" name="type" class="form-control">
                    <option value="Prospecto" <?= ($contact->type ?? '') == 'Prospecto' ? 'selected' : '' ?>>Prospecto</option>
                    <option value="Cliente" <?= ($contact->type ?? '') == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="Otro" <?= ($contact->type ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="account_id">Organización</label>
                <select id="account_id" name="account_id" class="form-control">
                    <option value="">-- Seleccionar Organización --</option>
                    <?php if(!empty($accounts)): foreach($accounts as $account): ?>
                        <option value="<?= $account->id ?>" <?= ($contact->account_id ?? '') == $account->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($account->name) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="job_title">Posición (Cargo)</label>
                <input type="text" id="job_title" name="job_title" class="form-control" value="<?= htmlspecialchars($contact->job_title ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="department">Departamento</label>
                <input type="text" id="department" name="department" class="form-control" value="<?= htmlspecialchars($contact->department ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="linkedin">URL de LinkedIn</label>
                <input type="url" id="linkedin" name="linkedin" class="form-control" value="<?= htmlspecialchars($contact->linkedin ?? '') ?>" placeholder="https://linkedin.com/in/usuario">
            </div>
            <div class="form-group">
                <label for="country">País</label>
                <input type="text" id="country" name="country" class="form-control" value="<?= htmlspecialchars($contact->country ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($contact->city ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="postal_code">Código Postal</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control" value="<?= htmlspecialchars($contact->postal_code ?? '') ?>">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="date_of_birth"><i class="fas fa-birthday-cake" style="color:#f472b6;margin-right:.35rem;"></i>Fecha de Nacimiento</label>
                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                       value="<?= htmlspecialchars($contact->date_of_birth ?? '') ?>">
                <?php if (!empty($contact->date_of_birth)): ?>
                    <small style="color:var(--text-muted);display:block;margin-top:.4rem;">
                        <i class="fas fa-info-circle"></i>
                        <?php
                            $age = (new DateTime($contact->date_of_birth))->diff(new DateTime())->y;
                            echo "Edad: {$age} años";
                        ?>
                    </small>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <!-- espacio reservado para futuros campos -->
            </div>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Actualizar Contacto
            </button>
        </div>
    </form>
</div>

<?php 
$entityType = 'contact';
$entityId = $contact->id;
require __DIR__ . '/../partials/activities_log.php'; 
?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
