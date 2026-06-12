<?php
$pageTitle = 'Contactos - Einsur Global CRM';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Directorio de Contactos</h1>
        <p>Gestiona los clientes y prospectos de tu empresa.</p>
    </div>
    <?php if (\App\Core\Permission::has('contacts', 'create')): ?>
    <a href="/crm_einsurglobal/public/contactos/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nuevo Contacto
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; gap: 1rem;">
        <form action="/crm_einsurglobal/public/contactos" method="GET" style="display: flex; gap: 1rem; flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="type" class="form-control" style="max-width: 200px;">
                <option value="">Todos los Tipos</option>
                <option value="Prospecto" <?= ($_GET['type'] ?? '') == 'Prospecto' ? 'selected' : '' ?>>Prospecto</option>
                <option value="Cliente" <?= ($_GET['type'] ?? '') == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="Otro" <?= ($_GET['type'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
            </select>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
                <a href="/crm_einsurglobal/public/contactos" class="btn" style="background: var(--border); color: var(--text-main);">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table style="min-width: 1500px;">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Tipo</th>
                    <th>Gerente de Cuenta</th>
                    <th>OrganizaciÃ³n</th>
                    <th>PosiciÃ³n</th>
                    <th>Email</th>
                    <th>LinkedIn</th>
                    <th>TelÃ©fono</th>
                    <th>PaÃ­s</th>
                    <th>Ciudad</th>
                    <th>CÃ³digo Postal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 3rem;">
                            <p style="color: var(--text-muted);">No se encontraron contactos.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($contact->type ?? 'Prospecto') ?></td>
                            <td><?= htmlspecialchars($contact->owner_name ?? 'Sin Asignar') ?></td>
                            <td><?= htmlspecialchars($contact->account_name ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->job_title ?? '-') ?></td>
                            <td>
                                <?php if (!empty($contact->email)): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span><?= htmlspecialchars($contact->email) ?></span>
                                        <button onclick="openEmailModal(<?= $contact->id ?>, '<?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?>')" class="btn" style="padding: 0.3rem 0.5rem; background: rgba(110, 223, 246, 0.1); color: var(--accent); border-radius: 6px; font-size: 0.8rem;" title="Enviar correo vía SMTP">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if (!empty($contact->linkedin)): ?>
                                    <a href="<?= htmlspecialchars($contact->linkedin) ?>" target="_blank" style="color: #0077b5; font-size: 1.25rem;" title="Perfil de LinkedIn">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($contact->phone)): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <a href="tel:<?= htmlspecialchars($contact->phone) ?>" style="color: var(--text-main); text-decoration: none;" title="Llamar directamente">
                                            <i class="fas fa-phone-alt" style="color: var(--primary); margin-right: 0.3rem;"></i><?= htmlspecialchars($contact->phone) ?>
                                        </a>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact->phone) ?>" target="_blank" style="padding: 0.3rem 0.5rem; background: rgba(37, 211, 102, 0.1); color: #25d366; border-radius: 6px; font-size: 0.8rem; text-decoration: none;" title="Abrir chat de WhatsApp">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <button onclick="openCallLogModal(<?= $contact->id ?>, '<?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?>')" class="btn" style="padding: 0.3rem 0.5rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 6px; font-size: 0.8rem;" title="Registrar llamada en bitácora">
                                            <i class="fas fa-phone-slash"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($contact->country ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->city ?? '-') ?></td>
                            <td><?= htmlspecialchars($contact->postal_code ?? '-') ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <?php if (\App\Core\Permission::has('contacts', 'update')): ?>
                                        <a href="/crm_einsurglobal/public/contactos/edit?id=<?= $contact->id ?>" style="color: var(--primary-hover); text-decoration: none; font-weight: 600;">Editar</a>
                                    <?php endif; ?>
                                    <?php if (\App\Core\Permission::has('contacts', 'delete')): ?>
                                        <form action="/crm_einsurglobal/public/contactos/delete" method="POST" onsubmit="return confirm('¿Está seguro de que deseas eliminar este contacto?');" style="display:inline; margin:0;">
                                            <input type="hidden" name="id" value="<?= $contact->id ?>">
                                            <button type="submit" style="background: none; border: none; color: var(--error); cursor: pointer; font-weight: 600; font-family: inherit; font-size: inherit;">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════════════ MODALES OMNICANAL ═══════════════ -->

<!-- Modal Enviar Correo -->
<div id="emailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div class="panel" style="width: 100%; max-width: 600px; background: white; border-radius: 18px; padding: 2rem; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin: 0;"><i class="fas fa-paper-plane" style="color: var(--accent); margin-right: 0.5rem;"></i> Enviar Correo a <span id="emailModalContactName"></span></h3>
            <button onclick="closeEmailModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form id="emailForm" onsubmit="sendEmailAjax(event)">
            <input type="hidden" id="emailContactId" name="contact_id">
            <div class="form-group">
                <label for="emailSubject">Asunto</label>
                <input type="text" id="emailSubject" name="subject" class="form-control" placeholder="Escribe el asunto del correo..." required>
            </div>
            <div class="form-group">
                <label for="emailBody">Cuerpo del Mensaje</label>
                <textarea id="emailBody" name="body" class="form-control" rows="8" placeholder="Escribe el mensaje aquí..." required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeEmailModal()" class="btn" style="background: var(--border); color: var(--text-main);">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="emailSubmitBtn">Enviar Correo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Registrar Llamada -->
<div id="callLogModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center;">
    <div class="panel" style="width: 100%; max-width: 500px; background: white; border-radius: 18px; padding: 2rem; box-shadow: var(--shadow-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--primary); margin: 0;"><i class="fas fa-phone-alt" style="color: #f59e0b; margin-right: 0.5rem;"></i> Registrar Llamada a <span id="callModalContactName"></span></h3>
            <button onclick="closeCallLogModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form action="/crm_einsurglobal/public/activities" method="POST">
            <input type="hidden" id="callContactId" name="entity_id">
            <input type="hidden" name="entity_type" value="contact">
            <input type="hidden" name="type" value="Llamada">
            <div class="form-group">
                <label for="callDescription">Notas de la Llamada / Resumen</label>
                <textarea id="callDescription" name="description" class="form-control" rows="5" placeholder="¿De qué hablaron en la llamada?" required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" onclick="closeCallLogModal()" class="btn" style="background: var(--border); color: var(--text-main);">Cancelar</button>
                <button type="submit" class="btn" style="background: #f59e0b; color: white;">Registrar Llamada</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEmailModal(contactId, contactName) {
    document.getElementById('emailContactId').value = contactId;
    document.getElementById('emailModalContactName').innerText = contactName;
    document.getElementById('emailSubject').value = '';
    document.getElementById('emailBody').value = '';
    document.getElementById('emailModal').style.display = 'flex';
}

function closeEmailModal() {
    document.getElementById('emailModal').style.display = 'none';
}

function openCallLogModal(contactId, contactName) {
    document.getElementById('callContactId').value = contactId;
    document.getElementById('callModalContactName').innerText = contactName;
    document.getElementById('callDescription').value = '';
    document.getElementById('callLogModal').style.display = 'flex';
}

function closeCallLogModal() {
    document.getElementById('callLogModal').style.display = 'none';
}

function sendEmailAjax(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('emailSubmitBtn');
    const originalText = submitBtn.innerText;
    submitBtn.innerText = 'Enviando...';
    submitBtn.disabled = true;

    const formData = new FormData(document.getElementById('emailForm'));

    fetch('/crm_einsurglobal/public/api/contactos/send-email', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeEmailModal();
        }
    })
    .catch(err => {
        console.error(err);
        alert('Ocurrió un error al enviar el correo electrónico.');
    })
    .finally(() => {
        submitBtn.innerText = originalText;
        submitBtn.disabled = false;
    });
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
