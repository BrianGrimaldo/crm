<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    /**
     * @param array|null $smtpConfig Credenciales SMTP personalizadas del usuario.
     *   Claves: smtp_host, smtp_port, smtp_email, smtp_password, smtp_encryption, smtp_from_name
     *   Si es null, se usan las variables de entorno globales (.env).
     */
    public function __construct(?array $smtpConfig = null)
    {
        $this->mailer = new PHPMailer(true);
        $this->setup($smtpConfig);
    }

    private function setup(?array $smtpConfig = null): void
    {
        $this->mailer->isSMTP();
        $this->mailer->SMTPAuth = true;
        $this->mailer->CharSet  = 'UTF-8';
        $this->mailer->isHTML(true);
        
        // Evitar problemas comunes con certificados en servidores cPanel/propios
        $this->mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        if ($smtpConfig && !empty($smtpConfig['smtp_host']) && !empty($smtpConfig['smtp_email']) && !empty($smtpConfig['smtp_password'])) {
            // ── Usar credenciales del vendedor ──
            $this->mailer->Host     = $smtpConfig['smtp_host'];
            $this->mailer->Port     = (int)($smtpConfig['smtp_port'] ?? 587);
            $this->mailer->Username = $smtpConfig['smtp_email'];
            $this->mailer->Password = $smtpConfig['smtp_password'];

            $encryption = $smtpConfig['smtp_encryption'] ?? 'tls';
            if ($encryption === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $this->mailer->SMTPSecure = '';
                $this->mailer->SMTPAutoTLS = false;
            }

            $fromName = $smtpConfig['smtp_from_name'] ?? $smtpConfig['smtp_email'];
            $this->mailer->setFrom($smtpConfig['smtp_email'], $fromName);
        } else {
            // ── Usar credenciales globales del .env ──
            $this->mailer->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
            $this->mailer->Username   = $_ENV['MAIL_USERNAME'] ?? '';
            $this->mailer->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);

            $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@einsurglobal.com';
            $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'Einsur Global CRM';
            $this->mailer->setFrom($fromEmail, $fromName);
        }
    }

    public function send(string $toEmail, string $toName, string $subject, string $body, array $attachments = [], array $embeddedImages = []): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments(); // Limpiar adjuntos anteriores
            $this->mailer->addAddress($toEmail, $toName);

            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));

            // Agregar archivos adjuntos
            foreach ($attachments as $attachment) {
                if (!empty($attachment['path']) && file_exists($attachment['path'])) {
                    $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                }
            }

            // Agregar imágenes incrustadas (CID)
            foreach ($embeddedImages as $img) {
                if (!empty($img['path']) && !empty($img['cid']) && file_exists($img['path'])) {
                    $this->mailer->addEmbeddedImage($img['path'], $img['cid']);
                }
            }

            return $this->mailer->send();
        } catch (Exception $e) {
            // Logear error si es necesario
            error_log("Error enviando correo a $toEmail: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function getErrorInfo(): string
    {
        return $this->mailer->ErrorInfo;
    }

    /**
     * Envía un correo cuando se le asigna un trato a un vendedor
     */
    public function sendDealAssignmentNotification(string $sellerEmail, string $sellerName, string $dealName, string $contactName): bool
    {
        $subject = "Nueva Oportunidad Asignada: $dealName";
        $body = "
            <h2>¡Hola $sellerName!</h2>
            <p>Se te ha asignado una nueva oportunidad de venta en el sistema.</p>
            <ul>
                <li><strong>Trato:</strong> $dealName</li>
                <li><strong>Contacto/Cliente:</strong> $contactName</li>
            </ul>
            <p>Por favor, ingresa al CRM para ver más detalles y dar seguimiento.</p>
            <br>
            <p><small>Este es un mensaje automatizado, no respondas a esta dirección.</small></p>
        ";

        return $this->send($sellerEmail, $sellerName, $subject, $body);
    }

    /**
     * Envía invitación a un usuario nuevo
     */
    public function sendWelcomeEmail(string $userEmail, string $userName, string $tempPassword, string $tenantName): bool
    {
        $subject = "Invitación a CRM Einsur Global - $tenantName";
        $body = "
            <h2>Bienvenido(a) $userName,</h2>
            <p>Has sido invitado a formar parte del CRM de <strong>$tenantName</strong>.</p>
            <p>Tus credenciales de acceso son las siguientes:</p>
            <ul>
                <li><strong>Correo:</strong> $userEmail</li>
                <li><strong>Contraseña Temporal:</strong> $tempPassword</li>
            </ul>
            <p>Te recomendamos cambiar tu contraseña temporal inmediatamente después de iniciar sesión en tu perfil.</p>
            <br>
            <p><a href='{$_ENV['APP_URL']}/login'>Haz clic aquí para iniciar sesión</a></p>
        ";

        return $this->send($userEmail, $userName, $subject, $body);
    }

    /**
     * Carga las credenciales SMTP del usuario actual desde la base de datos.
     * Retorna un array con las claves smtp_* o null si no tiene configuración.
     */
    public static function getUserSmtpConfig(int $userId): ?array
    {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT smtp_host, smtp_port, smtp_email, smtp_password, smtp_encryption, smtp_from_name, email_signature FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row && !empty($row['smtp_host']) && !empty($row['smtp_email']) && !empty($row['smtp_password'])) {
            return $row;
        }

        return null;
    }
}
