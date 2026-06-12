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
            'first_name'  => $_POST['first_name'] ?? '',
            'last_name'   => $_POST['last_name'] ?? '',
            'type'        => $_POST['type'] ?? 'Prospecto',
            'email'       => $_POST['email'] ?? '',
            'phone'       => $_POST['phone'] ?? '',
            'linkedin'    => $_POST['linkedin'] ?? '',
            'job_title'   => $_POST['job_title'] ?? '',
            'department'  => $_POST['department'] ?? '',
            'country'     => $_POST['country'] ?? '',
            'city'        => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'owner_id'    => $_SESSION['user_id'] ?? null,
            'account_id'  => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
        ];

        if (empty($data['first_name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header('Location: /crm_einsurglobal/public/contactos/create');
            exit;
        }

        $contactId = $this->contactModel->create($data);
        $this->auditLog->log('create', 'contact', $contactId, null, $data);

        $_SESSION['flash_success'] = "Contacto creado exitosamente.";
        header('Location: /crm_einsurglobal/public/contactos');
        exit;
    }

    /**
     * Muestra el formulario para editar un contacto existente.
     */
    public function edit(): void
    {
        Permission::require('contacts', 'update');
        $id = (int)($_GET['id'] ?? 0);
        $contact = $this->contactModel->find($id);

        if (!$contact) {
            $_SESSION['flash_error'] = "Contacto no encontrado.";
            header('Location: /crm_einsurglobal/public/contactos');
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
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'first_name'  => $_POST['first_name'] ?? '',
            'last_name'   => $_POST['last_name'] ?? '',
            'type'        => $_POST['type'] ?? 'Prospecto',
            'email'       => $_POST['email'] ?? '',
            'phone'       => $_POST['phone'] ?? '',
            'linkedin'    => $_POST['linkedin'] ?? '',
            'job_title'   => $_POST['job_title'] ?? '',
            'department'  => $_POST['department'] ?? '',
            'country'     => $_POST['country'] ?? '',
            'city'        => $_POST['city'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'account_id'  => !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null,
        ];

        if (empty($data['first_name'])) {
            $_SESSION['flash_error'] = "El nombre es obligatorio.";
            header("Location: /crm_einsurglobal/public/contactos/edit?id={$id}");
            exit;
        }

        $oldContact = $this->contactModel->find($id);
        $success = $this->contactModel->update($id, $data);

        if ($success) {
            $this->auditLog->log('update', 'contact', $id, (array)$oldContact, $data);
            $_SESSION['flash_success'] = "Contacto actualizado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo actualizar el contacto.";
        }

        header('Location: /crm_einsurglobal/public/contactos');
        exit;
    }

    /**
     * Elimina un contacto de la base de datos.
     */
    public function delete(): void
    {
        Permission::require('contacts', 'delete');
        $id = (int)($_POST['id'] ?? 0);
        $oldContact = $this->contactModel->find($id);
        $success = $this->contactModel->delete($id);

        if ($success) {
            $this->auditLog->log('delete', 'contact', $id, (array)$oldContact, null);
            $_SESSION['flash_success'] = "Contacto eliminado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar el contacto.";
        }

        header('Location: /crm_einsurglobal/public/contactos');
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

            $contactId = (int)($_POST['contact_id'] ?? 0);
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
            $userId = (int)($_SESSION['user_id'] ?? 0);
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
                $rawLines = explode("\n", str_replace("\r", "", strip_tags($smtpConfig['email_signature'])));
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
                
                $signatureHtml = '
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
                
                $bodyHtml .= $signatureHtml;
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
}
