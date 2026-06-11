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
            header('Location: /crm_einsurglobal/public/auth/login');
            exit;
        }
        
        $user = $this->userModel->findTenantUser($userId);

        require __DIR__ . '/../../Views/profile/edit.php';
    }

    public function update(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        
        if (!$userId) {
            header('Location: /crm_einsurglobal/public/auth/login');
            exit;
        }

        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name'  => $_POST['last_name'] ?? '',
            'phone'      => $_POST['phone'] ?? '',
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $_SESSION['flash_error'] = "El nombre y apellido son obligatorios.";
            header('Location: /crm_einsurglobal/public/perfil');
            exit;
        }

        // Cambio de contraseÃ±a (opcional)
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                $_SESSION['flash_error'] = "Las contraseÃ±as no coinciden.";
                header('Location: /crm_einsurglobal/public/perfil');
                exit;
            }
            $data['password_hash'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        }

        $success = $this->userModel->updateProfile($userId, $data);

        if ($success) {
            // Actualizar variables de sesiÃ³n si cambiÃ³ el nombre
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['flash_success'] = "Perfil actualizado exitosamente.";
        } else {
            $_SESSION['flash_error'] = "Hubo un problema al actualizar el perfil.";
        }

        header('Location: /crm_einsurglobal/public/perfil');
        exit;
    }
}
