<?php
// Espera las variables: $entityType, $entityId, $activities
?>
<div class="card" style="margin-top: 2rem; padding: 1.5rem;">
    <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">Bitácora de Intervenciones</h3>
    
    <!-- Formulario para agregar nueva actividad -->
    <form action="/crm_einsurglobal/public/activities" method="POST" style="margin-bottom: 2rem; background: var(--bg-main); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
        <input type="hidden" name="entity_type" value="<?= htmlspecialchars($entityType) ?>">
        <input type="hidden" name="entity_id" value="<?= (int)$entityId ?>">
        
        <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
            <div style="flex: 1;">
                <label for="type" style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Tipo de Intervención</label>
                <select name="type" id="type" class="form-control" required style="width: 100%;">
                    <option value="Llamada"><i class="fas fa-phone"></i> Llamada Telefónica</option>
                    <option value="Correo"><i class="fas fa-envelope"></i> Correo Electrónico</option>
                    <option value="Visita"><i class="fas fa-handshake"></i> Visita Presencial</option>
                    <option value="Nota"><i class="fas fa-sticky-note"></i> Nota Interna</option>
                </select>
            </div>
            <?php if ($entityType === 'deal'): ?>
            <div style="display: flex; align-items: flex-end;">
                <span style="font-size: 0.8rem; color: var(--text-muted); background: var(--surface); padding: 0.5rem; border-radius: 4px; border: 1px solid var(--border);">
                    <i class="fas fa-lightbulb"></i> Suma probabilidad automáticamente
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 600; font-size: 0.9rem;">Detalles / Notas de la Intervención</label>
            <textarea name="description" id="description" rows="3" class="form-control" required placeholder="Describe lo que se habló, acuerdos o información relevante..." style="width: 100%; resize: vertical;"></textarea>
        </div>
        
        <div style="text-align: right;">
            <button type="submit" class="btn btn-primary">Guardar Intervención</button>
        </div>
    </form>

    <!-- Timeline de Actividades -->
    <div>
        <?php if (empty($activities)): ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                Aún no hay intervenciones registradas.
            </div>
        <?php else: ?>
            <div style="position: relative;">
                <div style="position: absolute; left: 24px; top: 10px; bottom: 10px; width: 2px; background: var(--border); z-index: 0;"></div>
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <?php foreach ($activities as $act): 
                        $icon = match ($act->type) {
                            'Llamada' => '<i class="fas fa-phone"></i>',
                            'Correo' => '<i class="fas fa-envelope"></i>',
                            'Visita' => '<i class="fas fa-handshake"></i>',
                            'Nota' => '<i class="fas fa-sticky-note"></i>',
                            default => '<i class="fas fa-thumbtack"></i>'
                        };
                        $date = new DateTime($act->created_at);
                    ?>
                        <div style="display: flex; gap: 1rem; position: relative; z-index: 1;">
                            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--surface); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <?= $icon ?>
                            </div>
                            <div style="flex: 1; background: var(--surface); border: 1px solid var(--border); border-radius: 8px; padding: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; align-items: flex-start;">
                                    <div>
                                        <strong style="color: var(--text-main); font-size: 1rem;"><?= htmlspecialchars($act->type) ?></strong>
                                        <span style="color: var(--text-muted); font-size: 0.9rem; margin-left: 0.5rem;">por <?= htmlspecialchars($act->user_name ?? 'Usuario') ?></span>
                                    </div>
                                    <span style="color: var(--text-muted); font-size: 0.8rem; background: var(--bg-main); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                        <?= $date->format('d M, Y h:i A') ?>
                                    </span>
                                </div>
                                <p style="margin: 0; color: var(--text-main); line-height: 1.5; white-space: pre-wrap; font-size: 0.95rem;"><?= htmlspecialchars($act->description) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
