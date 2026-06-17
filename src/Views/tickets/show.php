<?php
$pageTitle = 'Detalle del Ticket - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Ticket #<?= $ticket->id ?>: <?= htmlspecialchars($ticket->subject) ?></h1>
        <p>Gestiona el avance y resolución de este ticket de soporte.</p>
    </div>
    <a href="/tickets" class="btn" style="background: var(--surface); color: var(--text-main); border: 1px solid var(--border);">
        Volver a Tickets
    </a>
</div>

<div class="dash-grid g-2-1">
    <!-- Panel Izquierdo: Conversación y Detalles -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Descripción General -->
        <div class="panel" style="padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid rgba(0,0,0,0.03); padding-bottom: 0.5rem;"><i class="fas fa-info-circle" style="color: var(--accent); margin-right: 0.5rem;"></i> Descripción de Solicitud</h3>
            <p style="font-size: 0.95rem; line-height: 1.6; color: var(--text-main); background: #f8fafc; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--accent); white-space: pre-line;">
                <?= htmlspecialchars($ticket->description ?: 'No se proporcionó descripción.') ?>
            </p>
        </div>

        <!-- Historial de Comentarios / Mensajes -->
        <div class="panel" style="padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1.5rem;"><i class="fas fa-comments" style="color: var(--accent); margin-right: 0.5rem;"></i> Historial e Intervenciones</h3>
            
            <div style="display: flex; flex-direction: column; gap: 1.25rem; max-height: 400px; overflow-y: auto; padding-right: 0.5rem;">
                <?php if (empty($comments)): ?>
                    <p style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 2rem;">No hay comentarios registrados en este ticket aún.</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div style="padding: 1rem; border-radius: 12px; background: <?= $c->is_internal ? '#fef3c7' : '#f1f5f9' ?>; border-left: 4px solid <?= $c->is_internal ? '#d97706' : 'var(--primary)' ?>;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.8rem;">
                                <span style="font-weight: 700; color: var(--text-main);">
                                    <i class="fas fa-user-circle"></i> <?= htmlspecialchars($c->user_name ?? 'Cliente / Web') ?>
                                    <?php if ($c->is_internal): ?>
                                        <span style="background: #fbbf24; color: #78350f; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 0.5rem;">NOTA INTERNA</span>
                                    <?php endif; ?>
                                </span>
                                <span style="color: var(--text-muted);"><?= date('d/m/Y H:i', strtotime($c->created_at)) ?></span>
                            </div>
                            <p style="font-size: 0.9rem; margin: 0; line-height: 1.5; color: var(--text-main); white-space: pre-line;">
                                <?= htmlspecialchars($c->body) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Formulario para agregar respuesta / comentario -->
            <form action="/tickets/comment" method="POST" style="margin-top: 1.5rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 1.5rem;">
                <input type="hidden" name="ticket_id" value="<?= $ticket->id ?>">
                <div class="form-group">
                    <label for="body">Agregar Comentario / Respuesta</label>
                    <textarea id="body" name="body" class="form-control" rows="4" placeholder="Escribe tu respuesta al cliente o nota de seguimiento..." required></textarea>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; font-weight: 600; cursor: pointer; color: var(--text-muted);">
                        <input type="checkbox" name="is_internal" value="1" style="width: 18px; height: 18px;">
                        ¿Es una nota interna? (Visible solo para agentes)
                    </label>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-reply"></i> Responder</button>
                </div>
            </form>
        </div>

    </div>

    <!-- Panel Derecho: Acciones de Estado y Metadatos -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Acciones rápidas de Estado -->
        <div class="panel" style="padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1.25rem;"><i class="fas fa-cog" style="color: var(--accent); margin-right: 0.5rem;"></i> Acciones y Estado</h3>
            
            <form action="/tickets/update-status" method="POST">
                <input type="hidden" name="id" value="<?= $ticket->id ?>">
                
                <div class="form-group">
                    <label for="status">Estado del Ticket</label>
                    <select id="status" name="status" class="form-control" onchange="this.form.submit()">
                        <option value="open" <?= $ticket->status === 'open' ? 'selected' : '' ?>>Abierto / Nuevo</option>
                        <option value="in_progress" <?= $ticket->status === 'in_progress' ? 'selected' : '' ?>>En Proceso</option>
                        <option value="waiting" <?= $ticket->status === 'waiting' ? 'selected' : '' ?>>En Espera de Cliente</option>
                        <option value="resolved" <?= $ticket->status === 'resolved' ? 'selected' : '' ?>>Resuelto / Solucionado</option>
                        <option value="closed" <?= $ticket->status === 'closed' ? 'selected' : '' ?>>Cerrado / Archivado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="priority">Prioridad</label>
                    <select id="priority" name="priority" class="form-control" onchange="this.form.submit()">
                        <option value="low" <?= $ticket->priority === 'low' ? 'selected' : '' ?>>Baja</option>
                        <option value="medium" <?= $ticket->priority === 'medium' ? 'selected' : '' ?>>Media</option>
                        <option value="high" <?= $ticket->priority === 'high' ? 'selected' : '' ?>>Alta</option>
                        <option value="urgent" <?= $ticket->priority === 'urgent' ? 'selected' : '' ?>>Urgente / Crítica</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="assigned_to">Asignado a</label>
                    <select id="assigned_to" name="assigned_to" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Sin Asignar --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?= $u->id ?>" <?= $ticket->assigned_to == $u->id ? 'selected' : '' ?>><?= htmlspecialchars($u->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="resolution">Notas de Resolución</label>
                    <textarea id="resolution" name="resolution" class="form-control" rows="3" placeholder="Describe la solución aplicada al ticket..."><?= htmlspecialchars($ticket->resolution ?? '') ?></textarea>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.8rem; justify-content: center;"><i class="fas fa-check-circle"></i> Guardar Resolución</button>
                </div>
            </form>
        </div>

        <!-- Ficha técnica del Cliente -->
        <div class="panel" style="padding: 1.5rem;">
            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--primary); margin-bottom: 1.25rem;"><i class="fas fa-address-card" style="color: var(--accent); margin-right: 0.5rem;"></i> Ficha de Asociación</h3>
            
            <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.9rem;">
                <li>
                    <span style="color: var(--text-muted); font-weight: 600; display: block;">Cliente Asociado:</span>
                    <strong>
                        <?php if ($ticket->contact_id): ?>
                            <a href="/contactos/edit?id=<?= $ticket->contact_id ?>" style="color: var(--primary); text-decoration: none;">
                                <?= htmlspecialchars($ticket->contact_first . ' ' . $ticket->contact_last) ?>
                            </a>
                        <?php else: ?>
                            Sin Contacto Registrado
                        <?php endif; ?>
                    </strong>
                </li>
                <li>
                    <span style="color: var(--text-muted); font-weight: 600; display: block;">Correo de Contacto:</span>
                    <span><?= htmlspecialchars($ticket->contact_email ?? '-') ?></span>
                </li>
                <li>
                    <span style="color: var(--text-muted); font-weight: 600; display: block;">Organización / Cuenta:</span>
                    <strong><?= htmlspecialchars($ticket->account_name ?? 'Ninguna') ?></strong>
                </li>
                <li>
                    <span style="color: var(--text-muted); font-weight: 600; display: block;">Canal de Origen:</span>
                    <span>
                        <?= match($ticket->channel) {
                            'email'  => '<i class="fas fa-envelope" style="color:#3b82f6; margin-right:0.3rem;"></i> Correo Electrónico',
                            'phone'  => '<i class="fas fa-phone" style="color:#10b981; margin-right:0.3rem;"></i> Llamada',
                            'chat'   => '<i class="fab fa-whatsapp" style="color:#25d366; margin-right:0.3rem;"></i> WhatsApp',
                            'social' => '<i class="fab fa-facebook" style="color:#1877f2; margin-right:0.3rem;"></i> Redes Sociales',
                            default  => '<i class="fas fa-globe" style="color:#64748b; margin-right:0.3rem;"></i> Portal Web'
                        } ?>
                    </span>
                </li>
                <li>
                    <span style="color: var(--text-muted); font-weight: 600; display: block;">Fecha de Entrada:</span>
                    <span><?= date('d/m/Y H:i:s', strtotime($ticket->created_at)) ?></span>
                </li>
                <li>
                    <span style="color: var(--text-muted); font-weight: 600; display: block;">Fecha Límite:</span>
                    <span style="color: <?= $ticket->due_date && strtotime($ticket->due_date) < time() ? '#ef4444' : 'var(--text-main)' ?>; font-weight: 600;">
                        <?= $ticket->due_date ? date('d/m/Y H:i', strtotime($ticket->due_date)) : 'Sin establecer' ?>
                    </span>
                </li>
            </ul>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
