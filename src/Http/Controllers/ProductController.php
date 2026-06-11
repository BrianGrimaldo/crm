<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Core\Permission;

class ProductController
{
    private Product $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function index(): void
    {
        Permission::require('products', 'view');
        $products = $this->productModel->getAllForTenant();
        require __DIR__ . '/../../Views/products/index.php';
    }

    public function create(): void
    {
        Permission::require('products', 'create');
        require __DIR__ . '/../../Views/products/create.php';
    }

    public function store(): void
    {
        Permission::require('products', 'create');
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'sku'  => $_POST['sku'] ?? '',
            'description' => $_POST['description'] ?? '',
            'unit_price' => (float)($_POST['unit_price'] ?? 0),
            'cost_price' => (float)($_POST['cost_price'] ?? 0),
            'initial_stock' => (float)($_POST['initial_stock'] ?? 0),
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre del producto/equipo es requerido.";
            header('Location: /crm_einsurglobal/public/productos/create');
            exit;
        }

        if ($this->productModel->create($data)) {
            $_SESSION['flash_success'] = "Producto creado exitosamente.";
            header('Location: /crm_einsurglobal/public/productos');
        } else {
            $_SESSION['flash_error'] = "Error al crear el producto.";
            header('Location: /crm_einsurglobal/public/productos/create');
        }
        exit;
    }

    public function edit(): void
    {
        Permission::require('products', 'update');
        
        $id = (int)($_GET['id'] ?? 0);
        $product = $this->productModel->findById($id);

        if (!$product) {
            $_SESSION['flash_error'] = "Producto no encontrado.";
            header('Location: /crm_einsurglobal/public/productos');
            exit;
        }

        require __DIR__ . '/../../Views/products/edit.php';
    }

    public function update(): void
    {
        Permission::require('products', 'update');
        
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => $_POST['name'] ?? '',
            'sku'  => $_POST['sku'] ?? '',
            'description' => $_POST['description'] ?? '',
            'unit_price' => (float)($_POST['unit_price'] ?? 0),
            'cost_price' => (float)($_POST['cost_price'] ?? 0),
            'add_stock' => (float)($_POST['add_stock'] ?? 0),
        ];

        if (empty($data['name'])) {
            $_SESSION['flash_error'] = "El nombre del producto es requerido.";
            header("Location: /crm_einsurglobal/public/productos/edit?id=$id");
            exit;
        }

        if ($this->productModel->update($id, $data)) {
            $_SESSION['flash_success'] = "Producto actualizado exitosamente.";
            header('Location: /crm_einsurglobal/public/productos');
        } else {
            $_SESSION['flash_error'] = "Error al actualizar el producto.";
            header("Location: /crm_einsurglobal/public/productos/edit?id=$id");
        }
        exit;
    }

    public function delete(): void
    {
        Permission::require('products', 'delete');
        
        $id = (int)($_POST['id'] ?? 0);
        if ($this->productModel->delete($id)) {
            $_SESSION['flash_success'] = "Producto eliminado (inactivado).";
        } else {
            $_SESSION['flash_error'] = "Error al eliminar el producto.";
        }
        header('Location: /crm_einsurglobal/public/productos');
        exit;
    }
}
