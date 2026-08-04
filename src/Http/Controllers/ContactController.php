<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Activity;
use App\Core\TenantContext;
use App\Core\Permission;

class ContactController
{
    private Contact $contactModel;
    private Account $accountModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->contactModel = new Contact();
        $this->accountModel = new Account();
        $this->auditLog = new AuditLog();
    }

    /**
     * Muestra la lista de contactos.
     */
    public function index(): void
    {
        Permission::require('contacts', 'view');
        $keyword = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $contacts = $this->contactModel->search($keyword, $type);

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        require __DIR__ . '/../../Views/contacts/index.php';
    }

    /**
     * Muestra el formulario de creación.
     */
    public function create(): void
    {
        Permission::require('contacts', 'create');
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        // Cargar cuentas para el select
        $accounts = $this->accountModel->all();

        require __DIR__ . '/../../Views/contacts/create.php';
    }

    /**
     * Guarda un nuevo contacto.
     */
    public function store(): void
    {
        Permission::require('contacts', 'create');
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'type' => $_POST['type'] ?? 'Prospecto',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'linkedin' => $_POST['linkedin'] ?? '',
            'job_title' => $_POST['job_title'] ?? '',
            'department' => $_POST['department'] ?? '',
            'country' => $_POST['country'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            'owner_id' => $_SESSION['user_id'] ?? null,
            'account_id' => !empty($_POST['account_id']) ? (int) $_POST['account_id'] : null,
        ];

        if (empty($data['first_name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header('Location: ' . url('/contactos/create'));
            exit;
        }

        $contactId = $this->contactModel->create($data);
        $this->auditLog->log('create', 'contact', $contactId, null, $data);

        $_SESSION['flash_success'] = "Contacto creado exitosamente.";
        header('Location: ' . url('/contactos'));
        exit;
    }

    /**
     * Muestra el formulario para editar un contacto existente.
     */
    public function edit(): void
    {
        Permission::require('contacts', 'update');
        $id = (int) ($_GET['id'] ?? 0);
        $contact = $this->contactModel->find($id);

        if (!$contact) {
            $_SESSION['flash_error'] = "Contacto no encontrado.";
            header('Location: ' . url('/contactos'));
            exit;
        }

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';

        // Cargar cuentas para el select
        $accounts = $this->accountModel->all();

        // Fetch activities
        $activityModel = new Activity();
        $activities = $activityModel->getForEntity('contact', $id);

        require __DIR__ . '/../../Views/contacts/edit.php';
    }

    /**
     * Actualiza un contacto en la base de datos.
     */
    public function update(): void
    {
        Permission::require('contacts', 'update');
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'type' => $_POST['type'] ?? 'Prospecto',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'linkedin' => $_POST['linkedin'] ?? '',
            'job_title' => $_POST['job_title'] ?? '',
            'department' => $_POST['department'] ?? '',
            'country' => $_POST['country'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'date_of_birth' => !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null,
            'account_id' => !empty($_POST['account_id']) ? (int) $_POST['account_id'] : null,
        ];

        if (empty($data['first_name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header('Location: ' . url('/contactos/edit?id=' . $id));
            exit;
        }

        $oldContact = $this->contactModel->find($id);
        if (!$oldContact) {
            $_SESSION['flash_error'] = "Contacto no encontrado.";
            header('Location: ' . url('/contactos'));
            exit;
        }
        $success = $this->contactModel->update($id, $data);

        if ($success) {
            $this->auditLog->log('update', 'contact', $id, (array) $oldContact, $data);
            $_SESSION['flash_success'] = "Contacto actualizado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar el contacto.";
        }

        header('Location: ' . url('/contactos'));
        exit;
    }

    /**
     * Elimina un contacto de la base de datos.
     */
    public function delete(): void
    {
        Permission::require('contacts', 'delete');
        $id = (int) ($_POST['id'] ?? 0);
        $oldContact = $this->contactModel->find($id);
        $success = $this->contactModel->delete($id);

        if ($success) {
            $this->auditLog->log('delete', 'contact', $id, (array) $oldContact, null);
            $_SESSION['flash_success'] = "Contacto eliminado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar el contacto.";
        }

        header('Location: ' . url('/contactos'));
        exit;
    }

    /**
     * Envía un correo electrónico a un contacto vía SMTP (PHPMailer).
     */
    public function sendEmail(): void
    {
        header('Content-Type: application/json');

        try {
            Permission::require('contacts', 'update');

            $contactId = (int) ($_POST['contact_id'] ?? 0);
            $subject = trim($_POST['subject'] ?? '');
            $body = trim($_POST['body'] ?? '');

            if (!$contactId || !$subject || !$body) {
                echo json_encode(['success' => false, 'message' => 'El asunto y el cuerpo del mensaje son obligatorios.']);
                exit;
            }

            $contact = $this->contactModel->find($contactId);
            if (!$contact || empty($contact->email)) {
                echo json_encode(['success' => false, 'message' => 'Contacto no encontrado o sin correo electrónico registrado.']);
                exit;
            }

            // Intentar usar las credenciales SMTP del vendedor actual
            $userId = (int) ($_SESSION['user_id'] ?? 0);
            $smtpConfig = \App\Core\EmailService::getUserSmtpConfig($userId);
            $emailService = new \App\Core\EmailService($smtpConfig);
            // Procesar archivos adjuntos si existen
            $attachments = [];
            if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
                $totalFiles = count($_FILES['attachments']['name']);
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $attachments[] = [
                            'path' => $_FILES['attachments']['tmp_name'][$i],
                            'name' => $_FILES['attachments']['name'][$i]
                        ];
                    }
                }
            }

            // Convertir texto plano a HTML respetando saltos de línea para el cuerpo
            $bodyHtml = nl2br(htmlspecialchars($body));
            $embeddedImages = [];

            // Adjuntar plantilla de firma automática si el usuario escribió sus datos
            if (!empty($smtpConfig['email_signature'])) {
                $bodyHtml .= $this->formatEmailSignature($smtpConfig['email_signature'], $embeddedImages);
            }

            // Envolver el correo en una estructura XHTML estándar para evitar que Outlook lo degrade a texto plano
            $finalHtml = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Correo</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; font-size: 14px; color: #323130; line-height: 1.5;">
    ' . $bodyHtml . '
</body>
</html>';

            $fullName = trim($contact->first_name . ' ' . $contact->last_name);
            $success = $emailService->send($contact->email, $fullName, $subject, $finalHtml, $attachments, $embeddedImages);

            if ($success) {
                // Registrar en la bitácora de actividades
                $activityModel = new \App\Models\Activity();
                $activityModel->log('contact', $contactId, 'Correo', "Asunto: {$subject}. Mensaje: " . substr(strip_tags($body), 0, 150) . "...");

                echo json_encode(['success' => true, 'message' => '¡Correo electrónico enviado con éxito!']);
            } else {
                $errorInfo = $emailService->getErrorInfo() ?? 'Revisa tu configuración SMTP.';
                echo json_encode(['success' => false, 'message' => 'Error al enviar: ' . $errorInfo]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Da formato HTML y añade estilos a la firma de correo del usuario.
     */
    private function formatEmailSignature(string $rawSignature, array &$embeddedImages): string
    {
        $rawLines = explode("\n", str_replace("\r", "", strip_tags($rawSignature)));
        $formattedLines = [];
        $contentLinesCount = 0;

        foreach ($rawLines as $line) {
            $trimLine = trim($line);
            if ($trimLine === '') {
                $formattedLines[] = '<br>';
                continue;
            }

            $contentLinesCount++;
            $lineHtml = htmlspecialchars($line);

            // Atentamente en negrita
            if (stripos($trimLine, 'atentamente') !== false) {
                $formattedLines[] = '<span style="font-weight: bold; color: #323130;">' . $lineHtml . '</span><br>';
                continue;
            }

            // Nombre (la primera línea de contenido después de Atentamente)
            if ($contentLinesCount === 2 || preg_match('/^(ING\.|LIC\.|C\.P\.|ARQ\.|MTRO\.|DR\.)/i', $trimLine)) {
                $formattedLines[] = '<span style="font-weight: bold; color: #323130;">' . $lineHtml . '</span><br>';
                continue;
            }

            // EINSUR SUPPLY o GLOBAL en azul
            if (stripos($trimLine, 'EINSUR') !== false && stripos($trimLine, 'www') === false) {
                $lineHtml = preg_replace('/(EINSUR\s*(SUPPLY|GLOBAL)?)/i', '<span style="color: #4472c4;">$1</span>', $lineHtml);
                $formattedLines[] = $lineHtml . '<br>';
                continue;
            }

            // Enlace web en azul
            if (stripos($trimLine, 'www.') !== false) {
                $lineHtml = preg_replace('/(www\.[a-z0-9.-]+\.[a-z]{2,})/i', '<a href="http://$1" style="color: #4472c4; text-decoration: underline;">$1</a>', $lineHtml);
                $formattedLines[] = $lineHtml . '<br>';
                continue;
            }

            // Texto normal
            $formattedLines[] = '<span style="color: #323130;">' . $lineHtml . '</span><br>';
        }

        $userTextHtml = implode("", $formattedLines);

        // Buscar la imagen del logo en el servidor local
        $customLogo = dirname(__DIR__, 3) . '/public/img/company_signature_logo.png';
        $defaultLogo = dirname(__DIR__, 3) . '/public/img/logoeglobal.png';
        $logoPath = file_exists($customLogo) ? $customLogo : $defaultLogo;
        $logoSrc = '';

        if (file_exists($logoPath)) {
            $embeddedImages[] = [
                'path' => $logoPath,
                'cid' => 'logo_einsur'
            ];
            $logoSrc = 'cid:logo_einsur';
        }

        return '
        <br><br>
        <table cellpadding="0" cellspacing="0" border="0" style="font-family: Arial, sans-serif; font-size: 13.5px; margin-top: 20px;">
            <tr>
                <td style="padding-right: 20px; vertical-align: middle;">
                    <img src="' . $logoSrc . '" width="150" style="display: block; max-width: 150px; height: auto;" alt="EINSUR Logo">
                </td>
                <td style="padding-left: 0px; vertical-align: middle; line-height: 1.4;">
                    ' . $userTextHtml . '
                </td>
            </tr>
        </table>';
    }

    public function searchAjax(): void
    {
        header('Content-Type: application/json');

        if (!Permission::has('contacts', 'view')) {
            echo json_encode(['html' => '']);
            exit;
        }

        $keyword = $_GET['search'] ?? '';
        $type = $_GET['type'] ?? '';
        $contacts = $this->contactModel->search($keyword, $type);

        ob_start();
        if (empty($contacts)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 3rem; color: var(--text-muted);"><i class="fas fa-users-slash"></i></div>
                    <h3 style="color: var(--text-main);">No se encontraron contactos</h3>
                </td>
            </tr>
        <?php else:
            foreach ($contacts as $contact):
                $type = $contact->type ?? 'Prospecto';
                $badgeBg = $type === 'Cliente' ? '#dcfce7' : ($type === 'Prospecto' ? '#fef3c7' : 'var(--border)');
                $badgeCol = $type === 'Cliente' ? '#166534' : ($type === 'Prospecto' ? '#92400e' : 'var(--text-main)');
                ?>
                <tr onmouseover="this.style.backgroundColor='var(--border)'" onmouseout="this.style.backgroundColor='transparent'">
                    <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div
                                style="width: 42px; height: 42px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; flex-shrink: 0;">
                                <?= strtoupper(substr($contact->first_name, 0, 1) . substr($contact->last_name, 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-main);">
                                    <?= htmlspecialchars($contact->first_name . ' ' . $contact->last_name) ?>
                                    <?php if (!empty($contact->linkedin)): ?>
                                        <a href="<?= htmlspecialchars($contact->linkedin) ?>" target="_blank" style="color: #0a66c2;"><i
                                                class="fab fa-linkedin"></i></a>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    <i class="fas fa-id-badge"
                                        style="margin-right: 0.4rem;"></i><?= htmlspecialchars($contact->job_title ?: 'Puesto no especificado') ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">
                        <div style="font-weight: 600;"><?= htmlspecialchars($contact->account_name ?: 'Independiente') ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Resp:
                            <?= htmlspecialchars($contact->owner_name ?: 'Sin Asignar') ?>
                        </div>
                    </td>
                    <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">
                        <?php if (!empty($contact->email)): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <a href="mailto:<?= htmlspecialchars($contact->email) ?>"
                                    style="color: var(--text-main); font-size: 0.85rem;"><i class="far fa-envelope"
                                        style="color: #94a3b8;"></i> <?= htmlspecialchars($contact->email) ?></a>
                                <button
                                    onclick='openEmailModal(<?= (int) $contact->id ?>, <?= htmlspecialchars(json_encode(trim($contact->first_name . " " . $contact->last_name)), ENT_QUOTES, "UTF-8") ?>)'
                                    style="padding: 0.2rem 0.5rem; background: #e0f2fe; color: #0284c7; border-radius: 6px; font-size: 0.75rem; border: none; cursor: pointer;"><i
                                        class="fas fa-paper-plane"></i></button>
                            </div>
                        <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Sin correo</span>
                        <?php endif; ?>
                        <?php if (!empty($contact->phone)): ?>
                            <div style="margin-top: 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                                <a href="tel:<?= htmlspecialchars($contact->phone) ?>"
                                    style="color: var(--text-main); font-size: 0.85rem;"><i class="fas fa-phone-alt"
                                        style="color: #94a3b8;"></i> <?= htmlspecialchars($contact->phone) ?></a>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $contact->phone) ?>" target="_blank"
                                    style="padding: 0.2rem 0.5rem; background: #dcfce7; color: #16a34a; border-radius: 6px; font-size: 0.85rem; text-decoration: none;"><i
                                        class="fab fa-whatsapp"></i></a>
                            </div>
                        <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.85rem;">Sin teléfono</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border);">
                        <span
                            style="padding: 0.35rem 0.85rem; background: <?= $badgeBg ?>; color: <?= $badgeCol ?>; border-radius: 999px; font-size: 0.75rem; font-weight: 700;">
                            <?= htmlspecialchars($type) ?>
                        </span>
                    </td>
                    <td style="padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--border); text-align: right;">
                        <div style="display: flex; justify-content: flex-end; gap: 0.4rem;">
                            <button
                                onclick='openCallLogModal(<?= $contact->id ?>, <?= htmlspecialchars(json_encode($contact->first_name . " " . $contact->last_name), ENT_QUOTES, "UTF-8") ?>)'
                                style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--border); color: #f59e0b; border-radius: 8px; cursor: pointer;"><i
                                    class="fas fa-phone-volume"></i></button>
                            <?php if (Permission::has('contacts', 'update')): ?>
                                <a href="<?= url('/contactos/edit?id=' . $contact->id) ?>"
                                    style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--border); color: #3b82f6; border-radius: 8px; text-decoration: none;"><i
                                        class="fas fa-edit"></i></a>
                            <?php endif; ?>
                            <?php if (Permission::has('contacts', 'delete')): ?>
                                <form action="<?= url('/contactos/delete') ?>" method="POST"
                                    onsubmit="return confirm('¿Eliminar contacto?');" style="display: inline; margin: 0;">
                                    <input type="hidden" name="id" value="<?= $contact->id ?>">
                                    <button type="submit"
                                        style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: #fff; border: 1px solid var(--border); color: #ef4444; border-radius: 8px; cursor: pointer;"><i
                                            class="fas fa-trash-alt"></i></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif;
        $html = ob_get_clean();

        echo json_encode(['html' => $html]);
        exit;
    }
}
