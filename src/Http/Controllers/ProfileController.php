<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;

class ProfileController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function edit(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        
        if (!$userId) {
            header('Location: ' . url('/auth/login'));
            exit;
        }
        
        $user = $this->userModel->find($userId);

        require __DIR__ . '/../../Views/profile/edit.php';
    }

    public function update(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        
        if (!$userId) {
            header('Location: ' . url('/auth/login'));
            exit;
        }

        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name'  => $_POST['last_name'] ?? '',
            'phone'      => $_POST['phone'] ?? '',
        ];

        // Datos SMTP del vendedor (opcionales)
        $data['email_signature'] = $_POST['email_signature'] ?? '';
        
        $smtpHost = trim($_POST['smtp_host'] ?? '');
        if ($smtpHost !== '') {
            $data['smtp_host']       = $smtpHost;
            $data['smtp_port']       = (int)($_POST['smtp_port'] ?? 587);
            $data['smtp_email']      = trim($_POST['smtp_email'] ?? '');
            $data['smtp_encryption'] = $_POST['smtp_encryption'] ?? 'tls';
            $data['smtp_from_name']  = trim($_POST['smtp_from_name'] ?? '');
            
            // Solo actualizar contraseña SMTP si se proporcionó una nueva
            $smtpPass = $_POST['smtp_password'] ?? '';
            if ($smtpPass !== '') {
                $data['smtp_password'] = $smtpPass;
            }
        } else {
            // Si el host está vacío, se limpian las credenciales
            $data['smtp_host'] = null;
            $data['smtp_email'] = null;
            $data['smtp_password'] = null;
        }

        // Procesar subida de logo de firma (aplica para todos)
        if (isset($_FILES['signature_logo']) && $_FILES['signature_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 3) . '/public/img/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $targetPath = $uploadDir . 'company_signature_logo.png';
            
            // Validar que sea una imagen
            $mimeType = mime_content_type($_FILES['signature_logo']['tmp_name']);
            if (strpos($mimeType, 'image/') === 0) {
                move_uploaded_file($_FILES['signature_logo']['tmp_name'], $targetPath);
            }
        }

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['flash_error'] = "Nombre y apellidos son requeridos.";
            header('Location: ' . url('/perfil'));
            exit;
        }

        // Cambio de contraseña (opcional)
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                $_SESSION['flash_error'] = "Las contraseñas no coinciden.";
                header('Location: ' . url('/perfil'));
                exit;
            }
            $data['password_hash'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        $success = $this->userModel->updateProfile($userId, $data);

        if ($success) {
            // Actualizar variables de sesión si cambió el nombre
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['flash_success'] = "Perfil actualizado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "Hubo un problema al actualizar el perfil.";
        }

        header('Location: ' . url('/perfil'));
        exit;
    }
}
