<?php

declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->setup();
    }

    private function setup(): void
    {
        // Configuración del servidor SMTP usando variables de entorno
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.example.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);

        // Remitente
        $fromEmail = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@einsurglobal.com';
        $fromName  = $_ENV['MAIL_FROM_NAME'] ?? 'Einsur Global CRM';
        $this->mailer->setFrom($fromEmail, $fromName);
        
        $this->mailer->isHTML(true);
        $this->mailer->CharSet = 'UTF-8';
    }

    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($toEmail, $toName);

            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $body));

            return $this->mailer->send();
        } catch (Exception $e) {
            // Logear error si es necesario
            error_log("Error enviando correo a $toEmail: {$this->mailer->ErrorInfo}");
            return false;
        }
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
}
