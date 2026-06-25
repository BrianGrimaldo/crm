<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Contact;
use App\Models\Account;
use App\Models\User;
use App\Models\AuditLog;
use App\Core\Permission;

class TicketController
{
    private Ticket $ticketModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->ticketModel = new Ticket();
        $this->auditLog = new AuditLog();
    }

    /**
     * Muestra la lista de tickets.
     */
    public function index(): void
    {
        Permission::require('tickets', 'view');

        $tickets = $this->ticketModel->all();
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        require __DIR__ . '/../../Views/tickets/index.php';
    }

    /**
     * Muestra el formulario para crear un ticket.
     */
    public function create(): void
    {
        Permission::require('tickets', 'create');

        $contactModel = new Contact();
        $contacts = $contactModel->search('', ''); // Get all contacts for select

        $accountModel = new Account();
        $accounts = $accountModel->all();

        $userModel = new User();
        $users = $userModel->all(); // Get all users for assignment

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        require __DIR__ . '/../../Views/tickets/create.php';
    }

    /**
     * Guarda un nuevo ticket.
     */
    public function store(): void
    {
        Permission::require('tickets', 'create');

        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = $_POST['priority'] ?? 'medium';
        $status = $_POST['status'] ?? 'open';
        $channel = $_POST['channel'] ?? 'web';
        $category = trim($_POST['category'] ?? '');
        $contactId = !empty($_POST['contact_id']) ? (int)$_POST['contact_id'] : null;
        $accountId = !empty($_POST['account_id']) ? (int)$_POST['account_id'] : null;
        $assignedTo = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

        if (empty($subject)) {
            $_SESSION['flash_error'] = "El asunto del ticket es obligatorio.";
            header('Location: ' . url('/tickets/create'));
            exit;
        }

        $ticketId = $this->ticketModel->create([
            'contact_id'  => $contactId,
            'account_id'  => $accountId,
            'assigned_to' => $assignedTo,
            'subject'     => $subject,
            'description' => $description,
            'priority'    => $priority,
            'status'      => $status,
            'channel'     => $channel,
            'category'    => $category,
            'due_date'    => $dueDate,
        ]);

        $this->auditLog->log('create', 'ticket', $ticketId, null, $_POST);

        $_SESSION['flash_success'] = "Ticket creado exitosamente.";
        header('Location: ' . url('/tickets'));
        exit;
    }

    /**
     * Muestra el detalle de un ticket.
     */
    public function show(): void
    {
        Permission::require('tickets', 'view');

        $id = (int)($_GET['id'] ?? 0);
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            $_SESSION['flash_error'] = "Ticket no encontrado.";
            header('Location: ' . url('/tickets'));
            exit;
        }

        $comments = $this->ticketModel->getComments($id);

        $userModel = new User();
        $users = $userModel->all(); // for reassigning

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail = $_SESSION['user_email'] ?? 'Usuario';

        require __DIR__ . '/../../Views/tickets/show.php';
    }

    /**
     * Añade un comentario al ticket.
     */
    public function addComment(): void
    {
        Permission::require('tickets', 'update');

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        $isInternal = (int)($_POST['is_internal'] ?? 0);

        if (empty($body)) {
            $_SESSION['flash_error'] = "El comentario no puede estar vacío.";
            header('Location: ' . url('/tickets/show?id={$ticketId}'));
            exit;
        }

        $this->ticketModel->addComment([
            'ticket_id'   => $ticketId,
            'user_id'     => $_SESSION['user_id'],
            'body'        => $body,
            'is_internal' => $isInternal,
        ]);

        $this->auditLog->log('add_comment', 'ticket', $ticketId, null, ['body' => $body, 'is_internal' => $isInternal]);

        $_SESSION['flash_success'] = "Comentario añadido exitosamente.";
        header('Location: ' . url('/tickets/show?id={$ticketId}'));
        exit;
    }

    /**
     * Actualiza el estado o prioridad de un ticket.
     */
    public function updateStatus(): void
    {
        Permission::require('tickets', 'update');

        $id = (int)($_POST['id'] ?? 0);
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            $_SESSION['flash_error'] = "Ticket no encontrado.";
            header('Location: ' . url('/tickets'));
            exit;
        }

        $data = [];
        if (isset($_POST['status'])) {
            $data['status'] = $_POST['status'];
            if ($_POST['status'] === 'resolved') {
                $data['resolved_at'] = date('Y-m-d H:i:s');
            } elseif ($_POST['status'] === 'closed') {
                $data['closed_at'] = date('Y-m-d H:i:s');
            }
        }
        if (isset($_POST['priority'])) {
            $data['priority'] = $_POST['priority'];
        }
        if (isset($_POST['assigned_to'])) {
            $data['assigned_to'] = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
        }
        if (isset($_POST['resolution'])) {
            $data['resolution'] = trim($_POST['resolution']);
        }

        $this->ticketModel->update($id, $data);
        $this->auditLog->log('update_status', 'ticket', $id, (array)$ticket, $data);

        $_SESSION['flash_success'] = "Ticket actualizado exitosamente.";
        header('Location: ' . url('/tickets/show?id={$id}'));
        exit;
    }
}
