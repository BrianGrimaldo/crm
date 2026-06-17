<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tenant;

class TenantController
{
    private Tenant $tenantModel;

    public function __construct()
    {
        // Solo superadmins pueden acceder a este módulo
        if (!isset($_SESSION['is_superadmin']) || !$_SESSION['is_superadmin']) {
            header("HTTP/1.0 403 Forbidden");
            echo "Acceso denegado. Se requiere nivel de Superadmin.";
            exit;
        }
        
        $this->tenantModel = new Tenant();
    }

    public function index(): void
    {
        $empresas = $this->tenantModel->getAll();
        require __DIR__ . '/../../Views/empresas/index.php';
    }

    public function create(): void
    {
        require __DIR__ . '/../../Views/empresas/create.php';
    }

    public function store(): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'slug' => strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'] ?? '')),
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'currency_code' => $_POST['currency_code'] ?? 'MXN',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Manejo del logotipo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/img/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['logo']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $data['logo_url'] = '/img/logos/' . $fileName;
            }
        }

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre de la empresa es requerido.";
            header('Location: /empresas/create');
            exit;
        }

        if ($this->tenantModel->create($data) > 0) {
            $_SESSION['flash_success'] = "Empresa creada exitosamente.";
            header('Location: /empresas');
        } else {
            $_SESSION['flash_error'] = "Error al crear la empresa.";
            header('Location: /empresas/create');
        }
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $empresa = $this->tenantModel->findById($id);

        if (!$empresa) {
            header('Location: /empresas');
            exit;
        }

        require __DIR__ . '/../../Views/empresas/edit.php';
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? null,
            'phone' => $_POST['phone'] ?? null,
            'address' => $_POST['address'] ?? null,
            'currency_code' => $_POST['currency_code'] ?? 'MXN',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Manejo del logotipo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/img/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['logo']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetPath)) {
                $data['logo_url'] = '/img/logos/' . $fileName;
            }
        }

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre de la empresa es requerido.";
            header("Location: /empresas/edit?id=$id");
            exit;
        }

        if ($this->tenantModel->update($id, $data)) {
            $_SESSION['flash_success'] = "Empresa actualizada exitosamente.";
            
            // Si la empresa actualizada es la empresa actual en sesión, actualizar nombre y logo
            if ($id === (int)($_SESSION['tenant_id'] ?? 0)) {
                $_SESSION['tenant_name'] = $data['name'];
                if (isset($data['logo_url'])) {
                    $_SESSION['tenant_logo'] = $data['logo_url'];
                }
            }
            
            header('Location: /empresas');
        } else {
            $_SESSION['flash_error'] = "Error al actualizar la empresa.";
            header("Location: /empresas/edit?id=$id");
        }
        exit;
    }
}
