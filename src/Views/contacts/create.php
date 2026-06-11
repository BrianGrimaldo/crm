<?php
$pageTitle = 'Nuevo Contacto - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Registrar Nuevo Contacto</h1>
        <p>Agrega un nuevo cliente o prospecto a tu agenda.</p>
    </div>
    <a href="/crm_einsurglobal/public/contactos" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a la Lista
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/crm_einsurglobal/public/contactos" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="first_name">Nombre(s) *</label>
                <input type="text" id="first_name" name="first_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="last_name">Apellidos</label>
                <input type="text" id="last_name" name="last_name" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="email">Correo ElectrÃ³nico</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            <div class="form-group">
                <label for="phone">TelÃ©fono / Celular</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="type">Tipo</label>
                <select id="type" name="type" class="form-control">
                    <option value="Prospecto">Prospecto</option>
                    <option value="Cliente">Cliente</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
            <div class="form-group">
                <label for="account_id">OrganizaciÃ³n</label>
                <select id="account_id" name="account_id" class="form-control">
                    <option value="">-- Seleccionar OrganizaciÃ³n --</option>
                    <?php if(!empty($accounts)): foreach($accounts as $account): ?>
                        <option value="<?= $account->id ?>"><?= htmlspecialchars($account->name) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="job_title">PosiciÃ³n (Cargo)</label>
                <input type="text" id="job_title" name="job_title" class="form-control">
            </div>
            <div class="form-group">
                <label for="department">Departamento</label>
                <input type="text" id="department" name="department" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="linkedin">URL de LinkedIn</label>
                <input type="url" id="linkedin" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/usuario">
            </div>
            <div class="form-group">
                <label for="country">PaÃ­s</label>
                <input type="text" id="country" name="country" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" class="form-control">
            </div>
            <div class="form-group">
                <label for="postal_code">CÃ³digo Postal</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control">
            </div>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Guardar Contacto
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
