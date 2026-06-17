<?php
$pageTitle = 'Nuevo Ticket de Soporte - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Registrar Nuevo Ticket</h1>
        <p>Abre un ticket de soporte técnico para resolver el problema de un cliente.</p>
    </div>
    <a href="/tickets" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a Tickets
    </a>
</div>

<div class="card" style="padding: 2rem;">
    <form action="/tickets" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            
            <div class="form-group">
                <label for="subject">Asunto del Ticket *</label>
                <input type="text" id="subject" name="subject" class="form-control" placeholder="Ej. Pantalla rota de laptop / Error al iniciar sesión..." required>
            </div>

            <div class="form-group">
                <label for="category">Categoría</label>
                <select id="category" name="category" class="form-control">
                    <option value="Soporte Técnico">Soporte Técnico</option>
                    <option value="Hardware / Equipo">Hardware / Equipo</option>
                    <option value="Software / Licencias">Software / Licencias</option>
                    <option value="Redes e Internet">Redes e Internet</option>
                    <option value="Facturación / Ventas">Facturación / Ventas</option>
                    <option value="General">General</option>
                </select>
            </div>

            <div class="form-group">
                <label for="contact_id">Contacto / Cliente Asociado</label>
                <select id="contact_id" name="contact_id" class="form-control">
                    <option value="">-- Seleccionar Contacto (Opcional) --</option>
                    <?php foreach ($contacts as $c): ?>
                        <option value="<?= $c->id ?>"><?= htmlspecialchars($c->first_name . ' ' . $c->last_name) ?> (<?= htmlspecialchars($c->email) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="account_id">Organización Asociada</label>
                <select id="account_id" name="account_id" class="form-control">
                    <option value="">-- Seleccionar Organización (Opcional) --</option>
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a->id ?>"><?= htmlspecialchars($a->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="assigned_to">Asignar a Agente / Vendedor</label>
                <select id="assigned_to" name="assigned_to" class="form-control">
                    <option value="">-- Sin Asignar (Opcional) --</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u->id ?>" <?= $u->id == $_SESSION['user_id'] ? 'selected' : '' ?>><?= htmlspecialchars($u->name) ?> (<?= htmlspecialchars($u->email) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="priority">Prioridad</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low">Baja</option>
                    <option value="medium" selected>Media</option>
                    <option value="high">Alta</option>
                    <option value="urgent">Urgente / Crítica</option>
                </select>
            </div>

            <div class="form-group">
                <label for="channel">Canal de Entrada</label>
                <select id="channel" name="channel" class="form-control">
                    <option value="web" selected>Portal Web / Formulario</option>
                    <option value="email">Correo Electrónico</option>
                    <option value="phone">Llamada Telefónica</option>
                    <option value="chat">WhatsApp / Chat</option>
                    <option value="social">Redes Sociales</option>
                </select>
            </div>

            <div class="form-group">
                <label for="due_date">Fecha Límite de Resolución</label>
                <input type="datetime-local" id="due_date" name="due_date" class="form-control">
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 2rem;">
            <label for="description">Descripción Detallada del Problema</label>
            <textarea id="description" name="description" class="form-control" rows="6" placeholder="Escribe aquí los detalles del problema o solicitud..."></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="/tickets" class="btn" style="background: var(--border); color: var(--text-main);">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar Ticket</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
