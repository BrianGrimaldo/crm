<?php
$pageTitle = 'Nueva Tarea - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Crear Tarea / Actividad</h1>
        <p>Programa una llamada, reunión o seguimiento.</p>
    </div>
    <a href="/tareas" class="btn" style="background: var(--surface); border: 1px solid var(--border);">Volver</a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem;">
    <form action="/tareas" method="POST">
        <div class="form-group">
            <label for="title">Título de la Actividad *</label>
            <input type="text" id="title" name="title" class="form-control" required placeholder="Ej: Llamar para confirmar presupuesto">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="due_date">Fecha y Hora Programada</label>
                <input type="datetime-local" id="due_date" name="due_date" class="form-control">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="priority">Prioridad</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low">Baja</option>
                    <option value="medium" selected>Media</option>
                    <option value="high">Alta</option>
                    <option value="urgent">Urgente</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descripción / Notas</label>
            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Detalles de la reunión o llamada..."></textarea>
        </div>

        <h3 style="font-size: 1.1rem; margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Vincular Tarea (Opcional)</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="related_type">Relacionar con...</label>
                <select id="related_type" name="related_type" class="form-control" onchange="updateRelatedOptions()">
                    <option value="">Ninguno</option>
                    <option value="deal" <?= $relatedType === 'deal' ? 'selected' : '' ?>>Oportunidad (Trato)</option>
                    <option value="contact" <?= $relatedType === 'contact' ? 'selected' : '' ?>>Contacto</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="related_id">Selecciona el registro</label>
                <select id="related_id" name="related_id" class="form-control">
                    <option value="">-- Selecciona --</option>
                    <!-- Las opciones se cargan por JS -->
                </select>
            </div>
        </div>

        <?php if (isset($_SESSION['is_superadmin']) || in_array($_SESSION['role_slug'] ?? '', ['gerente', 'superadmin'])): ?>
        <div class="form-group" style="margin-top: 1.5rem;">
            <label for="assigned_to">Asignar a Vendedor</label>
            <select id="assigned_to" name="assigned_to" class="form-control">
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u->id ?>" <?= $u->id == $_SESSION['user_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u->first_name . ' ' . $u->last_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <input type="hidden" name="assigned_to" value="<?= $_SESSION['user_id'] ?>">
        <?php endif; ?>

        <div style="margin-top: 2.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Guardar Tarea</button>
        </div>
    </form>
</div>

<script>
const deals = <?= json_encode($deals) ?>;
const contacts = <?= json_encode($contacts) ?>;
const selectedId = <?= $relatedId ?>;

function updateRelatedOptions() {
    const type = document.getElementById('related_type').value;
    const select = document.getElementById('related_id');
    select.innerHTML = '<option value="">-- Selecciona --</option>';
    
    if (type === 'deal') {
        deals.forEach(d => {
            select.innerHTML += `<option value="${d.id}" ${d.id == selectedId ? 'selected' : ''}>${d.name}</option>`;
        });
    } else if (type === 'contact') {
        contacts.forEach(c => {
            select.innerHTML += `<option value="${c.id}" ${c.id == selectedId ? 'selected' : ''}>${c.first_name} ${c.last_name}</option>`;
        });
    }
}
updateRelatedOptions();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
