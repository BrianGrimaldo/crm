<?php
$pageTitle = 'Tipificaciones - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Tipificaciones de Cierre</h1>
        <p>Configura las etiquetas para clasificar tickets y conversaciones finalizadas.</p>
    </div>
    <button class="btn btn-primary" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> Nueva Tipificación
    </button>
</div>

<div class="dash-grid" style="grid-template-columns: 1fr;">
    <div class="panel">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tipificación</th>
                        <th>Descripción</th>
                        <th>Acción Automática</th>
                        <th>Uso (Tickets)</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tipifications)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay tipificaciones configuradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tipifications as $tip): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.8rem;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: <?= htmlspecialchars($tip->color) ?>22; color: <?= htmlspecialchars($tip->color) ?>; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas <?= htmlspecialchars($tip->icon) ?>"></i>
                                        </div>
                                        <strong style="color: var(--text-main);"><?= htmlspecialchars($tip->name) ?></strong>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-size: 0.85rem;">
                                    <?= htmlspecialchars($tip->description ?? '—') ?>
                                </td>
                                <td>
                                    <span style="background: var(--border); padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.75rem; font-family: monospace;">
                                        <?= htmlspecialchars($tip->auto_action) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $count = 0;
                                    foreach($stats as $s) { if($s->id == $tip->id) $count = $s->ticket_count; }
                                    echo $count;
                                    ?>
                                </td>
                                <td>
                                    <?php if ($tip->is_active): ?>
                                        <span style="color: #10b981; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-check-circle"></i> Activa</span>
                                    <?php else: ?>
                                        <span style="color: #ef4444; font-weight: 600; font-size: 0.85rem;"><i class="fas fa-times-circle"></i> Inactiva</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <button class="btn" style="padding: 0.4rem 0.8rem; background: transparent; border: 1px solid var(--border); color: var(--text-main);" onclick='openEditModal(<?= json_encode($tip) ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Create / Edit -->
<div id="tipModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: var(--surface); padding: 2rem; border-radius: var(--radius-lg); width: 100%; max-width: 500px; box-shadow: var(--shadow-lg);">
        <h3 id="modalTitle" style="margin-bottom: 1.5rem; color: var(--primary);">Nueva Tipificación</h3>
        
        <form id="tipForm" action="<?= url('/configuracion/tipificaciones') ?>" method="POST">
            <input type="hidden" name="id" id="tipId" value="">
            
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" id="tipName" class="form-control" required placeholder="Ej: Venta Positiva">
            </div>
            
            <div class="dash-grid g-1-1">
                <div class="form-group">
                    <label>Color (Hex)</label>
                    <input type="color" name="color" id="tipColor" class="form-control" value="#6366f1" style="height: 42px; padding: 0.2rem;">
                </div>
                <div class="form-group">
                    <label>Icono (FontAwesome)</label>
                    <input type="text" name="icon" id="tipIcon" class="form-control" value="fa-tag" placeholder="Ej: fa-check">
                </div>
            </div>
            
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="description" id="tipDesc" class="form-control" placeholder="Breve descripción del uso">
            </div>
            
            <div class="form-group">
                <label>Acción Automática (Opcional)</label>
                <select name="auto_action" id="tipAction" class="form-control">
                    <option value="none">Ninguna</option>
                    <option value="create_task">Crear Tarea (Seguimiento)</option>
                    <option value="close_ticket">Cerrar Ticket Automáticamente</option>
                </select>
            </div>
            
            <div class="form-group" id="statusGroup" style="display: none;">
                <label>Estado</label>
                <select name="is_active" id="tipActive" class="form-control">
                    <option value="1">Activa</option>
                    <option value="0">Inactiva</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                <button type="button" class="btn" style="background: var(--border);" onclick="closeModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('modalTitle').innerText = 'Nueva Tipificación';
    document.getElementById('tipForm').action = '<?= url('/configuracion/tipificaciones') ?>';
    document.getElementById('tipId').value = '';
    document.getElementById('tipName').value = '';
    document.getElementById('tipColor').value = '#6366f1';
    document.getElementById('tipIcon').value = 'fa-tag';
    document.getElementById('tipDesc').value = '';
    document.getElementById('tipAction').value = 'none';
    document.getElementById('statusGroup').style.display = 'none';
    
    document.getElementById('tipModal').style.display = 'flex';
}

function openEditModal(tip) {
    document.getElementById('modalTitle').innerText = 'Editar Tipificación';
    document.getElementById('tipForm').action = '<?= url('/configuracion/tipificaciones/update') ?>';
    
    document.getElementById('tipId').value = tip.id;
    document.getElementById('tipName').value = tip.name;
    document.getElementById('tipColor').value = tip.color;
    document.getElementById('tipIcon').value = tip.icon;
    document.getElementById('tipDesc').value = tip.description;
    document.getElementById('tipAction').value = tip.auto_action;
    
    document.getElementById('tipActive').value = tip.is_active;
    document.getElementById('statusGroup').style.display = 'block';
    
    document.getElementById('tipModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('tipModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
