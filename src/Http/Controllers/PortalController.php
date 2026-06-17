<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\TenantContext;
use App\Models\Ticket;
use App\Models\Product;
use PDO;

class PortalController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Muestra la pantalla de ingreso al portal de clientes.
     */
    public function showLogin(): void
    {
        if (isset($_SESSION['portal_contact_id'])) {
            header('Location: /portal/dashboard');
            exit;
        }

        require __DIR__ . '/../../Views/portal/login.php';
    }

    /**
     * Valida el correo electrónico del cliente para iniciar sesión.
     */
    public function authenticate(): void
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $_SESSION['portal_error'] = "Por favor ingrese su correo electrónico.";
            header('Location: /portal');
            exit;
        }

        // Buscar el contacto en la base de datos global
        $stmt = $this->db->prepare("
            SELECT c.*, t.name as tenant_name 
            FROM contacts c 
            JOIN tenants t ON c.tenant_id = t.id
            WHERE c.email = :email AND t.is_active = 1 
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $contact = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$contact) {
            $_SESSION['portal_error'] = "El correo electrónico no se encuentra registrado en nuestro sistema de clientes.";
            header('Location: /portal');
            exit;
        }

        // Iniciar sesión del portal
        $_SESSION['portal_contact_id'] = (int)$contact->id;
        $_SESSION['portal_contact_name'] = $contact->first_name . ' ' . $contact->last_name;
        $_SESSION['portal_account_id'] = $contact->account_id ? (int)$contact->account_id : null;
        $_SESSION['portal_tenant_id'] = (int)$contact->tenant_id;
        $_SESSION['portal_tenant_name'] = $contact->tenant_name;

        header('Location: /portal/dashboard');
        exit;
    }

    /**
     * Dashboard del portal de clientes.
     */
    public function dashboard(): void
    {
        if (!isset($_SESSION['portal_contact_id'])) {
            header('Location: /portal');
            exit;
        }

        $tenantId = (int)$_SESSION['portal_tenant_id'];
        $contactId = (int)$_SESSION['portal_contact_id'];
        $accountId = $_SESSION['portal_account_id'];

        // Configurar el contexto de Tenant para que los modelos respondan a este tenant
        TenantContext::setTenantId($tenantId);

        // 1. Obtener los tickets del cliente
        $sqlTickets = "SELECT t.*, u.name as assigned_name 
                       FROM tickets t
                       LEFT JOIN users u ON t.assigned_to = u.id
                       WHERE t.tenant_id = :tenant_id AND t.contact_id = :contact_id
                       ORDER BY t.created_at DESC";
        $stmt = $this->db->prepare($sqlTickets);
        $stmt->execute([':tenant_id' => $tenantId, ':contact_id' => $contactId]);
        $tickets = $stmt->fetchAll(PDO::FETCH_OBJ);

        // 2. Obtener catálogo de productos disponibles
        $sqlProducts = "SELECT p.*, pc.name as category_name, i.quantity as stock
                        FROM products p
                        LEFT JOIN product_categories pc ON p.category_id = pc.id
                        LEFT JOIN inventory i ON p.id = i.product_id
                        WHERE p.tenant_id = :tenant_id AND p.is_active = 1
                        ORDER BY p.name ASC";
        $stmt = $this->db->prepare($sqlProducts);
        $stmt->execute([':tenant_id' => $tenantId]);
        $products = $stmt->fetchAll(PDO::FETCH_OBJ);

        require __DIR__ . '/../../Views/portal/dashboard.php';
    }

    /**
     * Permite al cliente crear un ticket desde el portal.
     */
    public function createTicket(): void
    {
        if (!isset($_SESSION['portal_contact_id'])) {
            header('Location: /portal');
            exit;
        }

        $tenantId = (int)$_SESSION['portal_tenant_id'];
        $contactId = (int)$_SESSION['portal_contact_id'];
        $accountId = $_SESSION['portal_account_id'];

        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? 'Soporte Técnico');

        if (empty($subject)) {
            $_SESSION['portal_error'] = "El asunto es obligatorio.";
            header('Location: /portal/dashboard');
            exit;
        }

        // Insertar ticket
        $sql = "INSERT INTO tickets 
                (tenant_id, contact_id, account_id, subject, description, priority, status, channel, category)
                VALUES 
                (:tenant_id, :contact_id, :account_id, :subject, :description, 'medium', 'open', 'web', :category)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tenant_id'  => $tenantId,
            ':contact_id' => $contactId,
            ':account_id' => $accountId,
            ':subject'    => $subject,
            ':description'=> $description,
            ':category'   => $category,
        ]);

        $_SESSION['portal_success'] = "¡Ticket creado exitosamente! Un agente se comunicará pronto.";
        header('Location: /portal/dashboard');
        exit;
    }

    /**
     * Cierra la sesión del portal.
     */
    public function logout(): void
    {
        unset($_SESSION['portal_contact_id']);
        unset($_SESSION['portal_contact_name']);
        unset($_SESSION['portal_account_id']);
        unset($_SESSION['portal_tenant_id']);
        unset($_SESSION['portal_tenant_name']);
        unset($_SESSION['portal_error']);
        unset($_SESSION['portal_success']);

        header('Location: /portal');
        exit;
    }
}
