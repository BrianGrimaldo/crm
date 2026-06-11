<?php
$pageTitle = 'Editar Tarea - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Editar Tarea</h1>
    </div>
    <a href="/crm_einsurglobal/public/tareas" class="btn" style="background: var(--surface); border: 1px solid var(--border);">Volver</a>
</div>

<div class="card" style="max-width: 800px; padding: 2rem; margin-bottom: 2rem;">
    <form action="/crm_einsurglobal/public/tareas/update" method="POST">
        <input type="hidden" name="id" value="<?= $task->id ?>">
        
        <div class="form-group">
            <label for="title">Título de la Actividad *</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?= htmlspecialchars($task->title) ?>">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="due_date">Fecha y Hora Programada</label>
                <input type="datetime-local" id="due_date" name="due_date" class="form-control" value="<?= $task->due_date ? date('Y-m-d\TH:i', strtotime($task->due_date)) : '' ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="priority">Prioridad</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low" <?= $task->priority === 'low' ? 'selected' : '' ?>>Baja</option>
                    <option value="medium" <?= $task->priority === 'medium' ? 'selected' : '' ?>>Media</option>
                    <option value="high" <?= $task->priority === 'high' ? 'selected' : '' ?>>Alta</option>
                    <option value="urgent" <?= $task->priority === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="status">Estado</label>
                <select id="status" name="status" class="form-control">
                    <option value="pending" <?= $task->status === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="in_progress" <?= $task->status === 'in_progress' ? 'selected' : '' ?>>En Progreso</option>
                    <option value="completed" <?= $task->status === 'completed' ? 'selected' : '' ?>>Completada</option>
                    <option value="cancelled" <?= $task->status === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descripción / Notas</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($task->description ?? '') ?></textarea>
        </div>

        <h3 style="font-size: 1.1rem; margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Vincular Tarea (Opcional)</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="related_type">Relacionar con...</label>
                <select id="related_type" name="related_type" class="form-control" onchange="updateRelatedOptions()">
                    <option value="">Ninguno</option>
                    <option value="deal" <?= $task->related_type === 'deal' ? 'selected' : '' ?>>Oportunidad (Trato)</option>
                    <option value="contact" <?= $task->related_type === 'contact' ? 'selected' : '' ?>>Contacto</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="related_id">Selecciona el registro</label>
                <select id="related_id" name="related_id" class="form-control">
                    <option value="">-- Selecciona --</option>
                </select>
            </div>
        </div>

        <?php if (isset($_SESSION['is_superadmin']) || in_array($_SESSION['role_slug'] ?? '', ['gerente', 'superadmin'])): ?>
        <div class="form-group" style="margin-top: 1.5rem;">
            <label for="assigned_to">Asignar a Vendedor</label>
            <select id="assigned_to" name="assigned_to" class="form-control">
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u->id ?>" <?= $u->id == $task->assigned_to ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u->first_name . ' ' . $u->last_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
            <input type="hidden" name="assigned_to" value="<?= $task->assigned_to ?>">
        <?php endif; ?>

        <div style="margin-top: 2.5rem; text-align: right;">
            <button type="submit" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Actualizar Tarea</button>
        </div>
    </form>
</div>

<div class="card" style="max-width: 800px; padding: 2rem; border-top: 4px solid #ef4444;">
    <h3 style="color: #ef4444; margin-top: 0;">Zona de Peligro</h3>
    <form action="/crm_einsurglobal/public/tareas/delete" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta tarea?');">
        <input type="hidden" name="id" value="<?= $task->id ?>">
        <button type="submit" class="btn" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid #ef4444;">
            <i class="fas fa-trash"></i> Eliminar Tarea
        </button>
    </form>
</div>

<script>
const deals = <?= json_encode($deals) ?>;
const contacts = <?= json_encode($contacts) ?>;
const selectedId = <?= $task->related_id ?? 0 ?>;
const initialType = '<?= $task->related_type ?? '' ?>';

function updateRelatedOptions() {
    const type = document.getElementById('related_type').value;
    const select = document.getElementById('related_id');
    select.innerHTML = '<option value="">-- Selecciona --</option>';
    
    if (type === 'deal') {
        deals.forEach(d => {
            const isSelected = (type === initialType && d.id == selectedId) ? 'selected' : '';
            select.innerHTML += `<option value="${d.id}" ${isSelected}>${d.name}</option>`;
        });
    } else if (type === 'contact') {
        contacts.forEach(c => {
            const isSelected = (type === initialType && c.id == selectedId) ? 'selected' : '';
            select.innerHTML += `<option value="${c.id}" ${isSelected}>${c.first_name} ${c.last_name}</option>`;
        });
    }
}
updateRelatedOptions();
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
