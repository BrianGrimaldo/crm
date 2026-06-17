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
    <a href="/contactos/create" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
        Nuevo Contacto
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div style="padding: 1.5rem; border-bottom: 1px solid var(--border); display: flex; gap: 1rem;">
        <form action="/contactos" method="GET" style="display: flex; gap: 1rem; flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o correo..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="type" class="form-control" style="max-width: 200px;">
                <option value="">Todos los Tipos</option>
                <option value="Prospecto" <?= ($_GET['type'] ?? '') == 'Prospecto' ? 'selected' : '' ?>>Prospecto</option>
                <option value="Cliente" <?= ($_GET['type'] ?? '') == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="Otro" <?= ($_GET['type'] ?? '') == 'Otro' ? 'selected' : '' ?>>Otro</option>
            </select>
            <button type="submit" class="btn btn-primary">Buscar</button>
            <?php if (!empty($_GET['search']) || !empty($_GET['type'])): ?>
                <a href="/contactos" class="btn" style="background: var(--border); color: var(--text-main);">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive" style="border-radius: 0 0 12px 12px; overflow: hidden;">
        <table style="width: 100%; min-width: 1000px; border-collapse: separate; border-spacing: 0;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th style="padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0;">Contacto</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0;">Empresa / Asignación</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0;">Vías de Contacto</th>
                    <th style="padding: 1rem 1.5rem; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0;">Estado</th>
                    <th style="padding: 1rem 1.5rem; text-align: right; font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contacts)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 4rem 2rem;">
                            <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"><i class="fas fa-users-slash"></i></div>
                            <h3 style="color: #475569; font-size: 1.2rem; margin-bottom: 0.5rem;">No se encontraron contactos</h3>
                            <p style="color: #94a3b8; font-size: 0.95rem;">Intenta ajustando los filtros de búsqueda o agrega un nuevo contacto.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($contacts as $contact): ?>
                        <tr style="transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='#f1f5f9'" onmouseout="this.style.backgroundColor='transparent'">
                            <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <div style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);">
                                        <?= strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
                                            <?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?>
                                            <?php if (!empty($contact->linkedin)): ?>
                                                <a href="<?= htmlspecialchars($contact->linkedin) ?>" target="_blank" style="color: #0a66c2; font-size: 0.9rem;" title="Ver LinkedIn"><i class="fab fa-linkedin"></i></a>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.85rem; color: #64748b; margin-top: 0.2rem;"><i class="fas fa-id-badge" style="margin-right: 0.4rem; color: #94a3b8;"></i><?= htmlspecialchars($contact->job_title ?: 'Puesto no especificado') ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; color: #334155; font-size: 0.9rem;"><i class="far fa-building" style="margin-right: 0.4rem; color: #94a3b8;"></i><?= htmlspecialchars($contact->account_name ?: 'Independiente') ?></div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.3rem;"><i class="far fa-user-circle" style="margin-right: 0.4rem; color: #94a3b8;"></i>Resp: <span style="font-weight: 500; color: #475569;"><?= htmlspecialchars($contact->owner_name ?: 'Sin Asignar') ?></span></div>
                            </td>
                            <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <?php if (!empty($contact->email)): ?>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <a href="mailto:<?= htmlspecialchars($contact->email) ?>" style="color: #475569; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; font-weight: 500;"><i class="far fa-envelope" style="color: #94a3b8; font-size: 0.9rem;"></i> <?= htmlspecialchars($contact->email) ?></a>
                                            <button onclick='openEmailModal(<?= (int)$contact->id ?>, <?= htmlspecialchars(json_encode(trim($contact->first_name . " " . $contact->last_name)), ENT_QUOTES, "UTF-8") ?>)' style="padding: 0.2rem 0.5rem; background: #e0f2fe; color: #0284c7; border-radius: 6px; font-size: 0.75rem; border: none; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#bae6fd'" onmouseout="this.style.background='#e0f2fe'" title="Enviar correo vía CRM"><i class="fas fa-paper-plane"></i></button>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1; font-size: 0.85rem;"><i class="far fa-envelope" style="margin-right: 0.4rem;"></i>Sin correo</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($contact->phone)): ?>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <a href="tel:<?= htmlspecialchars($contact->phone) ?>" style="color: #475569; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; font-weight: 500;"><i class="fas fa-phone-alt" style="color: #94a3b8; font-size: 0.9rem;"></i> <?= htmlspecialchars($contact->phone) ?></a>
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact->phone) ?>" target="_blank" style="padding: 0.2rem 0.5rem; background: #dcfce7; color: #16a34a; border-radius: 6px; font-size: 0.85rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#bbf7d0'" onmouseout="this.style.background='#dcfce7'" title="Abrir WhatsApp"><i class="fab fa-whatsapp"></i></a>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #cbd5e1; font-size: 0.85rem;"><i class="fas fa-phone-alt" style="margin-right: 0.4rem;"></i>Sin teléfono</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9;">
                                <?php 
                                $type = $contact->type ?? 'Prospecto';
                                $badgeBg = $type === 'Cliente' ? '#dcfce7' : ($type === 'Prospecto' ? '#fef3c7' : '#f1f5f9');
                                $badgeCol = $type === 'Cliente' ? '#166534' : ($type === 'Prospecto' ? '#92400e' : '#475569');
                                ?>
                                <span style="display: inline-flex; align-items: center; justify-content: center; padding: 0.35rem 0.85rem; background: <?= $badgeBg ?>; color: <?= $badgeCol ?>; border-radius: 999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.03em;">
                                    <?= htmlspecialchars($type) ?>
                                </span>
                            </td>
                            <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid #f1f5f9; text-align: right;">
                                <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                                    <button onclick='openCallLogModal(<?= $contact->id ?>, <?= htmlspecialchars(json_encode($contact->first_name . " " . $contact->last_name), ENT_QUOTES, "UTF-8") ?>)' style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #e2e8f0; color: #f59e0b; border-radius: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#fef3c7'" onmouseout="this.style.background='#fff'" title="Registrar Llamada"><i class="fas fa-phone-volume"></i></button>
                                    
                                    <?php if (\App\Core\Permission::has('contacts', 'update')): ?>
                                        <a href="/contactos/edit?id=<?= $contact->id ?>" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #e2e8f0; color: #3b82f6; border-radius: 8px; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'" title="Editar"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    
                                    <?php if (\App\Core\Permission::has('contacts', 'delete')): ?>
                                        <form action="/contactos/delete" method="POST" onsubmit="return confirm('¿Está seguro de que deseas eliminar a <?= htmlspecialchars($contact->first_name) ?>? Esta acción no se puede deshacer.');" style="display: inline; margin: 0;">
                                            <input type="hidden" name="id" value="<?= $contact->id ?>">
                                            <button type="submit" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #e2e8f0; color: #ef4444; border-radius: 8px; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='#fff'" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
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

<!-- Modal Enviar Correo (Estilo Outlook) -->
<div id="emailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="width: 100%; max-width: 650px; background: #fff; border-radius: 8px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.15); font-family: 'Segoe UI', system-ui, sans-serif;">
        
        <!-- Cabecera estilo ventana Windows/Outlook -->
        <div style="background: #f3f2f1; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edebe9;">
            <div style="font-size: 0.9rem; font-weight: 600; color: #323130; display: flex; align-items: center; gap: 0.5rem;">
                <i class="far fa-envelope" style="color: #0078d4;"></i> Mensaje Nuevo
            </div>
            <button type="button" onclick="closeEmailModal()" style="background: transparent; border: none; font-size: 1.2rem; color: #605e5c; cursor: pointer; line-height: 1; padding: 0 0.5rem;">&times;</button>
        </div>

        <form id="emailForm" onsubmit="sendEmailAjax(event)" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex-grow: 1;">
            <input type="hidden" id="emailContactId" name="contact_id">
            
            <!-- Barra de herramientas superior -->
            <div style="padding: 0.5rem 1rem; background: #fff; border-bottom: 1px solid #edebe9; display: flex; gap: 0.5rem;">
                <button type="submit" id="emailSubmitBtn" style="background: #0078d4; color: white; border: none; padding: 0.4rem 1rem; border-radius: 4px; font-size: 0.85rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="fas fa-paper-plane"></i> Enviar
                </button>
                <label for="emailAttachments" style="background: transparent; color: #323130; border: 1px solid transparent; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; transition: background 0.2s;">
                    <i class="fas fa-paperclip" style="color: #605e5c;"></i> Adjuntar
                </label>
                <input type="file" id="emailAttachments" name="attachments[]" multiple style="display: none;" onchange="updateAttachmentList(this)">
                <button type="button" onclick="closeEmailModal()" style="background: transparent; color: #323130; border: 1px solid transparent; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem; cursor: pointer; margin-left: auto;">
                    Descartar
                </button>
            </div>

            <!-- Información de envío y campos -->
            <div style="padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                
                <?php
                    $userId = (int)($_SESSION['user_id'] ?? 0);
                    $smtpConfig = null;
                    try {
                        $smtpConfig = \App\Core\EmailService::getUserSmtpConfig($userId);
                    } catch (\Throwable $e) {}
                ?>
                <?php if ($smtpConfig): ?>
                    <div style="font-size: 0.85rem; color: #605e5c; display: flex; align-items: center; border-bottom: 1px solid #edebe9; padding-bottom: 0.5rem;">
                        <span style="width: 50px; color: #a19f9d;">De:</span>
                        <strong><?= htmlspecialchars($smtpConfig['smtp_email']) ?></strong>
                    </div>
                <?php else: ?>
                    <div style="font-size: 0.85rem; color: #a80000; display: flex; align-items: center; border-bottom: 1px solid #edebe9; padding-bottom: 0.5rem; background: #fdf3f4;">
                        <span style="width: 50px; color: #a19f9d;">De:</span>
                        <span>[Genérico] <a href="/perfil" style="color:#0078d4; text-decoration:underline;">Configura tu correo aquí</a></span>
                    </div>
                <?php endif; ?>

                <div style="font-size: 0.85rem; color: #323130; display: flex; align-items: center; border-bottom: 1px solid #edebe9; padding-bottom: 0.5rem;">
                    <span style="width: 50px; color: #a19f9d;">Para:</span>
                    <strong id="emailModalContactName"></strong>
                </div>

                <div style="display: flex; align-items: center; border-bottom: 1px solid #edebe9;">
                    <span style="width: 50px; color: #a19f9d; font-size: 0.85rem;">Asunto:</span>
                    <input type="text" id="emailSubject" name="subject" required style="flex-grow: 1; border: none; padding: 0.5rem 0; font-size: 0.95rem; font-family: inherit; color: #323130; outline: none; background: transparent;">
                </div>

                <!-- Lista de archivos adjuntos (se llena dinámicamente con JS) -->
                <div id="attachmentList" style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem;"></div>

            </div>

            <!-- Cuerpo del mensaje -->
            <div style="padding: 0 1.5rem 1.5rem 1.5rem; flex-grow: 1; display: flex; flex-direction: column;">
                <textarea id="emailBody" name="body" required style="width: 100%; min-height: 250px; border: none; resize: none; font-family: 'Segoe UI', system-ui, sans-serif; font-size: 0.95rem; color: #323130; outline: none; line-height: 1.5;" placeholder="Escribe tu mensaje aquí..."></textarea>
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
        <form action="/activities" method="POST">
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
window.onerror = function(msg, url, line, col, error) {
    alert("Error de Javascript: " + msg + "\nEn la línea: " + line);
    return false;
};

function openEmailModal(contactId, contactName) {
    document.getElementById('emailContactId').value = contactId;
    document.getElementById('emailModalContactName').innerText = contactName;
    document.getElementById('emailSubject').value = '';
    document.getElementById('emailBody').value = '';
    document.getElementById('emailAttachments').value = '';
    document.getElementById('attachmentList').innerHTML = '';
    document.getElementById('emailModal').style.display = 'flex';
}

function closeEmailModal() {
    document.getElementById('emailModal').style.display = 'none';
}

function updateAttachmentList(input) {
    const list = document.getElementById('attachmentList');
    list.innerHTML = '';
    if (input.files && input.files.length > 0) {
        Array.from(input.files).forEach(file => {
            const badge = document.createElement('div');
            badge.style.cssText = 'background: #f3f2f1; border: 1px solid #edebe9; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem; color: #323130; display: flex; align-items: center; gap: 0.4rem;';
            badge.innerHTML = `<i class="fas fa-file-alt" style="color: #605e5c;"></i> ${file.name} <span style="color:#a19f9d; font-size:0.7rem;">(${(file.size/1024/1024).toFixed(2)} MB)</span>`;
            list.appendChild(badge);
        });
    }
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

    fetch('/api/contactos/send-email', {
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
