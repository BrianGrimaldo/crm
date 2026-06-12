<?php
$pageTitle = 'Nueva Organización - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Registrar Organización</h1>
        <p>Añade una nueva empresa o cliente a tu portafolio.</p>
    </div>
    <a href="/crm_einsurglobal/public/organizaciones" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a la Lista
    </a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/crm_einsurglobal/public/organizaciones" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="name">Nombre de Organización *</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="type">Tipo</label>
                <select id="type" name="type" class="form-control">
                    <option value="Prospecto">Prospecto</option>
                    <option value="Cliente">Cliente</option>
                    <option value="Otro">Otro</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="priority">Prioridad</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="B" selected>B</option>
                    <option value="C">C</option>
                </select>
            </div>
            <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="website">Sitio Web</label>
                <input type="url" id="website" name="website" class="form-control" placeholder="https://www.ejemplo.com">
            </div>
            <div class="form-group">
                <label for="linkedin">URL de LinkedIn</label>
                <input type="url" id="linkedin" name="linkedin" class="form-control" placeholder="https://linkedin.com/company/ejemplo">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="country">País</label>
                <input type="text" id="country" name="country" class="form-control">
            </div>
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="postal_code">Código Postal</label>
                <input type="text" id="postal_code" name="postal_code" class="form-control">
            </div>
            <div class="form-group">
                <label for="billing_address">Dirección Física</label>
                <input type="text" id="billing_address" name="billing_address" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notas / Comentarios</label>
            <textarea id="notes" name="notes" class="form-control" rows="4"></textarea>
        </div>

        <div style="margin-top: 1.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">
                Guardar Organización
            </button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
