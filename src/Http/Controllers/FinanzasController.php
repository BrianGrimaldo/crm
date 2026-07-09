<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Deal;
use App\Models\Account;
use App\Models\Contact;
use App\Models\AuditLog;
use App\Core\Permission;

class FinanzasController
{
    private Invoice $invoiceModel;
    private Deal $dealModel;
    private Account $accountModel;
    private Contact $contactModel;
    private AuditLog $auditLog;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
        $this->dealModel = new Deal();
        $this->accountModel = new Account();
        $this->contactModel = new Contact();
        $this->auditLog = new AuditLog();
    }

    // ─── DASHBOARD DE FINANZAS ────────────────────────────────

    /**
     * Vista principal: Dashboard de Finanzas y Cobranza.
     * Diseñada para Dirección, Gerencia y C-Level.
     */
    public function index(): void
    {
        Permission::require('finance', 'view');

        $role = strtolower(str_replace('-', '', $_SESSION['user_role'] ?? ''));
        if (in_array($role, ['cobranza', 'collections'])) {
            header('Location: ' . url('/finanzas/cobranza'));
            exit;
        }

        // Auto-mark overdue invoices
        $this->invoiceModel->markOverdueInvoices();

        // KPIs
        $stats = $this->invoiceModel->getFinanceStats();

        // Alertas / Banderas Rojas
        $dealsWithoutInvoice = $this->invoiceModel->getDealsWithoutInvoice();
        $overdueInvoices = $this->invoiceModel->getOverdueInvoices();
        $upcomingDue = $this->invoiceModel->getUpcomingDueInvoices(7);

        // Charts
        $invoicesByMonth = $this->invoiceModel->getInvoicesByMonth();
        $statusDistribution = $this->invoiceModel->getStatusDistribution();

        // Top Deudores
        $topDebtors = $this->invoiceModel->getTopDebtors(5);

        // Alert counts for badges
        $alertCount = count($dealsWithoutInvoice) + count($overdueInvoices);

        // JSON para Chart.js
        $chartMonths = json_encode(array_map(fn($r) => $r->month_label, $invoicesByMonth));
        $chartEmitido = json_encode(array_map(fn($r) => (float) $r->total_emitido, $invoicesByMonth));
        $chartCobrado = json_encode(array_map(fn($r) => (float) $r->total_cobrado, $invoicesByMonth));

        $statusLabels = json_encode(array_map(fn($r) => ucfirst($r->status), $statusDistribution));
        $statusCounts = json_encode(array_map(fn($r) => (int) $r->count, $statusDistribution));
        $statusColors = json_encode(array_map(function ($r) {
            $map = [
                'emitida' => '#3b82f6',
                'parcial' => '#f59e0b',
                'vencida' => '#ef4444',
                'pagada' => '#10b981',
                'cancelada' => '#94a3b8',
            ];
            return $map[$r->status] ?? '#6366f1';
        }, $statusDistribution));

        require __DIR__ . '/../../Views/finanzas/index.php';
    }

    // ─── LISTADO DE FACTURAS ──────────────────────────────────

    /**
     * Vista de lista de todas las facturas.
     */
    public function list(): void
    {
        Permission::require('finance', 'view');

        $role = strtolower(str_replace('-', '', $_SESSION['user_role'] ?? ''));
        if (in_array($role, ['cobranza', 'collections'])) {
            header('Location: ' . url('/finanzas/cobranza'));
            exit;
        }

        $keyword = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';

        $ownerId = null;
        if (!Permission::canViewAllInvoices()) {
            $ownerId = (int) ($_SESSION['user_id'] ?? 0);
        }

        $invoices = $this->invoiceModel->allWithRelations($keyword, $status, $ownerId);

        require __DIR__ . '/../../Views/finanzas/list.php';
    }

    // ─── CREAR FACTURA ────────────────────────────────────────

    /**
     * Formulario de creación de factura.
     */
    public function create(): void
    {
        Permission::require('finance', 'create');

        $deals = $this->dealModel->allWithRelations('', 'Ganado');
        $accounts = $this->accountModel->all();
        $contacts = $this->contactModel->all();

        // Pre-fill from deal if passed via query param
        $prefillDealId = (int) ($_GET['deal_id'] ?? 0);
        $prefillDeal = null;
        if ($prefillDealId) {
            $prefillDeal = $this->dealModel->findWithRelations($prefillDealId);

            if (!$prefillDeal || ($prefillDeal->status ?? '') !== 'Ganado') {
                $_SESSION['flash_error'] = "Solo puedes generar una factura para oportunidades en estado Ganado.";
                header('Location: ' . url('/oportunidades/pipeline'));
                exit;
            }
        }

        require __DIR__ . '/../../Views/finanzas/create.php';
    }

    /**
     * Guardar nueva factura.
     */
    public function store(): void
    {
        Permission::require('finance', 'create');

        $dealId = !empty($_POST['deal_id']) ? (int) $_POST['deal_id'] : null;

        if ($dealId) {
            $deal = $this->dealModel->findWithRelations($dealId);
            if (!$deal || ($deal->status ?? '') !== 'Ganado') {
                $_SESSION['flash_error'] = "Solo puedes generar una factura para oportunidades en estado Ganado.";
                header('Location: ' . url('/finanzas/crear'));
                exit;
            }
        }

        $data = [
            'invoice_number' => trim($_POST['invoice_number'] ?? ''),
            'reference' => $_POST['reference'] ?? null,
            'deal_id' => $dealId,
            'account_id' => !empty($_POST['account_id']) ? (int) $_POST['account_id'] : null,
            'contact_id' => !empty($_POST['contact_id']) ? (int) $_POST['contact_id'] : null,
            'owner_id' => $_SESSION['user_id'] ?? null,
            'subtotal' => (float) ($_POST['subtotal'] ?? 0),
            'tax_amount' => (float) ($_POST['tax_amount'] ?? 0),
            'total' => (float) ($_POST['total'] ?? 0),
            'currency_code' => $_POST['currency_code'] ?? 'MXN',
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?? '',
            'status' => $_POST['status'] ?? 'borrador',
            'notes' => $_POST['notes'] ?? null,
            'source' => 'manual',
        ];

        // Manejar subida de archivo PDF
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/invoices/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) === 'pdf') {
                $fileName = 'inv_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
                    $data['pdf_path'] = 'uploads/invoices/' . $fileName;
                }
            }
        }

        if (empty($data['invoice_number']) || empty($data['due_date'])) {
            $_SESSION['flash_error'] = "El folio de factura y la fecha de vencimiento son obligatorios.";
            header('Location: ' . url('/finanzas/crear'));
            exit;
        }

        try {
            $id = $this->invoiceModel->create($data);
            $this->auditLog->log('create', 'invoice', $id, null, $data);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['flash_error'] = "El folio de factura '{$data['invoice_number']}' ya existe. Por favor, usa un folio distinto.";
                header('Location: ' . url('/finanzas/crear' . ($dealId ? "?deal_id={$dealId}" : '')));
                exit;
            }
            throw $e;
        }

        // If came from deal, update deal's invoice_status
        // If came from deal, update deal's invoice_status
        if ($dealId && !empty($data['status']) && in_array($data['status'], ['emitida', 'pagada', 'parcial'])) {
            $this->dealModel->update($dealId, [
                'invoice_status' => $data['status']
            ]);
        }

        $_SESSION['flash_success'] = "Factura #{$data['invoice_number']} creada exitosamente.";
        
        if (!Permission::has('finance', 'view')) {
            header('Location: ' . url('/oportunidades/pipeline'));
        } else {
            header('Location: ' . url('/finanzas'));
        }
        exit;
    }
    // ─── EDITAR FACTURA ───────────────────────────────────────

    /**
     * Formulario de edición.
     */
    public function edit(): void
    {
        Permission::require('finance', 'update');

        $id = (int) ($_GET['id'] ?? 0);
        $invoice = $this->invoiceModel->findWithRelations($id);

        if (!$invoice) {
            $_SESSION['flash_error'] = "Factura no encontrada.";
            header('Location: ' . url('/finanzas/facturas'));
            exit;
        }

        $deals = $this->dealModel->allWithRelations();
        $accounts = $this->accountModel->all();
        $contacts = $this->contactModel->all();
        $payments = $this->invoiceModel->getPayments($id);

        require __DIR__ . '/../../Views/finanzas/edit.php';
    }

    /**
     * Actualizar factura.
     */
    public function update(): void
    {
        Permission::require('finance', 'update');

        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'invoice_number' => trim($_POST['invoice_number'] ?? ''),
            'reference' => $_POST['reference'] ?? null,
            'deal_id' => !empty($_POST['deal_id']) ? (int) $_POST['deal_id'] : null,
            'account_id' => !empty($_POST['account_id']) ? (int) $_POST['account_id'] : null,
            'contact_id' => !empty($_POST['contact_id']) ? (int) $_POST['contact_id'] : null,
            'subtotal' => (float) ($_POST['subtotal'] ?? 0),
            'tax_amount' => (float) ($_POST['tax_amount'] ?? 0),
            'total' => (float) ($_POST['total'] ?? 0),
            'currency_code' => $_POST['currency_code'] ?? 'MXN',
            'issue_date' => $_POST['issue_date'] ?? date('Y-m-d'),
            'due_date' => $_POST['due_date'] ?? '',
            'status' => $_POST['status'] ?? 'borrador',
            'notes' => $_POST['notes'] ?? null,
        ];

        // Manejar actualización de archivo PDF
        if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../public/uploads/invoices/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['pdf']['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) === 'pdf') {
                $fileName = 'inv_' . time() . '_' . bin2hex(random_bytes(4)) . '.pdf';
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['pdf']['tmp_name'], $targetPath)) {
                    $data['pdf_path'] = 'uploads/invoices/' . $fileName;
                }
            }
        }

        if (empty($data['invoice_number']) || empty($data['due_date'])) {
            $_SESSION['flash_error'] = "El folio y la fecha de vencimiento son obligatorios.";
            header('Location: ' . url('/finanzas/editar?id=' . $id));
            exit;
        }

        $old = $this->invoiceModel->findWithRelations($id);

        try {
            $this->invoiceModel->update($id, $data);
            $this->auditLog->log('update', 'invoice', $id, (array) $old, $data);
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $_SESSION['flash_error'] = "El folio de factura '{$data['invoice_number']}' ya está en uso por otra factura. Usa uno distinto.";
                header('Location: ' . url('/finanzas/editar?id=' . $id));
                exit;
            }
            throw $e;
        }

        $_SESSION['flash_success'] = "Factura actualizada exitosamente.";
        header('Location: ' . url('/finanzas/facturas'));
        exit;
    }

    // ─── ELIMINAR FACTURA ─────────────────────────────────────

    public function delete(): void
    {
        Permission::require('finance', 'delete');

        $id = (int) ($_POST['id'] ?? 0);
        $old = $this->invoiceModel->findWithRelations($id);
        $success = $this->invoiceModel->delete($id);

        if ($success) {
            $this->auditLog->log('delete', 'invoice', $id, (array) $old, null);
            $_SESSION['flash_success'] = "Factura eliminada.";
        } else {
            $_SESSION['flash_error'] = "No se pudo eliminar la factura.";
        }

        header('Location: ' . url('/finanzas/facturas'));
        exit;
    }

    // ─── REGISTRAR PAGO ───────────────────────────────────────

    public function registerPayment(): void
    {
        Permission::require('finance', 'update');

        $invoiceId = (int) ($_POST['invoice_id'] ?? 0);
        $paymentData = [
            'amount' => (float) ($_POST['amount'] ?? 0),
            'payment_method' => $_POST['payment_method'] ?? 'transferencia',
            'payment_date' => $_POST['payment_date'] ?? date('Y-m-d'),
            'reference' => $_POST['reference'] ?? null,
            'notes' => $_POST['notes'] ?? null,
        ];

        if ($paymentData['amount'] <= 0) {
            $_SESSION['flash_error'] = "El monto del pago debe ser mayor a 0.";
            header('Location: ' . url('/finanzas/editar?id=' . $invoiceId));

            exit;
        }

        $this->invoiceModel->registerPayment($invoiceId, $paymentData);
        $this->auditLog->log('payment', 'invoice', $invoiceId, null, $paymentData);

        $_SESSION['flash_success'] = "Pago registrado exitosamente.";

        $redirectUrl = $_POST['redirect_to'] ?? '/finanzas/editar?id=' . $invoiceId;
        header('Location: ' . url($redirectUrl));
        exit;
    }

    // ─── AUDITORÍA CEO ────────────────────────────────────────

    /**
     * Vista de lista de vendedores y sus métricas financieras globales.
     */
    public function auditSellers(): void
    {
        Permission::require('finance', 'view');

        $role = strtolower($_SESSION['user_role'] ?? '');
        if (!in_array($role, ['superadmin', 'admin', 'ceo', 'dirección'])) {
            $_SESSION['flash_error'] = "Acceso denegado: Requiere nivel de Dirección.";
            header('Location: ' . url('/dashboard'));
            exit;
        }

        $sellers = $this->invoiceModel->getSellersFinanceAudit();

        require __DIR__ . '/../../Views/finanzas/ceo/sellers_list.php';
    }

    /**
     * Vista de detalle de facturación de un vendedor específico.
     */
    public function auditSellerDetail(): void
    {
        Permission::require('finance', 'view');

        $role = strtolower($_SESSION['user_role'] ?? '');
        if (!in_array($role, ['superadmin', 'admin', 'ceo', 'dirección'])) {
            $_SESSION['flash_error'] = "Acceso denegado: Requiere nivel de Dirección.";
            header('Location: ' . url('/dashboard'));
            exit;
        }

        $sellerId = (int) ($_GET['seller_id'] ?? 0);
        if (!$sellerId) {
            $_SESSION['flash_error'] = "Vendedor no especificado.";
            header('Location: ' . url('/finanzas/ceo/auditoria'));
            exit;
        }

        $sellerInfo = $this->invoiceModel->getSellerInfo($sellerId);
        $invoices = $this->invoiceModel->getSellerInvoices($sellerId);
        $dealsWithoutInvoice = $this->invoiceModel->getSellerDealsWithoutInvoice($sellerId);

        // KPIs del vendedor
        $totalFacturado = array_sum(array_map(fn($i) => (float) $i->total, $invoices));
        $totalCobrado = array_sum(array_map(fn($i) => (float) $i->amount_paid, $invoices));
        $totalPorCobrar = array_sum(array_map(function ($i) {
            return in_array($i->status, ['emitida', 'parcial', 'vencida']) ? ($i->total - $i->amount_paid) : 0;
        }, $invoices));
        $totalVencido = array_sum(array_map(function ($i) {
            return $i->status === 'vencida' ? ($i->total - $i->amount_paid) : 0;
        }, $invoices));
        $montoSinFacturar = array_sum(array_map(fn($d) => (float) $d->amount, $dealsWithoutInvoice));

        require __DIR__ . '/../../Views/finanzas/ceo/seller_detail.php';
    }

    // ─── PORTAL DE COBRANZA ───────────────────────────────────

    /**
     * Vista única para el usuario de cobranza con buscador estricto.
     */
    public function collectionsPortal(): void
    {
        Permission::require('finance', 'view');

        $roleStr = strtolower(str_replace('-', '', $_SESSION['user_role'] ?? ''));
        $isCobranza = strpos($roleStr, 'cobranza') !== false 
                   || strpos($roleStr, 'collection') !== false 
                   || strpos($roleStr, 'cobrador') !== false;

        // Permitir exclusivamente a Cobranza o Superadmin
        if (!$isCobranza && !in_array($roleStr, ['superadmin', 'admin'])) {
            $_SESSION['flash_error'] = "Acceso denegado: Solo el departamento de Cobranza puede acceder a este portal.";
            header('Location: ' . url('/dashboard'));
            exit;
        }

        $ownerId = null;
        $crossTenant = false;

        // Solo el superadmin corporativo o un usuario global de cobranza ven todo
        if ($isCobranza || $roleStr === 'superadmin') {
            $crossTenant = true;
        } elseif (!Permission::canViewAllInvoices()) {
            $ownerId = (int) ($_SESSION['user_id'] ?? 0);
        }

        $invoiceNumber = $_GET['invoice_number'] ?? '';

        // Obtenemos todas cruzando tenants si es posible
        $invoicesRaw = $this->invoiceModel->allWithRelations($invoiceNumber, '', $ownerId, $crossTenant);

        // Agrupamos por empresa (tenant)
        $invoicesGrouped = [];
        $totalSales = 0;
        foreach ($invoicesRaw as $inv) {
            $tenantName = $inv->tenant_name ?? 'Corporativo';
            $invoicesGrouped[$tenantName][] = $inv;
            $totalSales += (float) $inv->total;
        }

        require __DIR__ . '/../../Views/finanzas/collections/portal.php';
    }

    // ─── WEBHOOK ENDPOINT ─────────────────────────────────────

    /**
     * Endpoint para recibir datos de facturación desde sistemas externos.
     * POST /api/finanzas/webhook
     */
    public function webhook(): void
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input) || empty($input['invoice_number'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos. Se requiere invoice_number.']);
            return;
        }

        try {
            $id = $this->invoiceModel->upsertFromWebhook($input);
            echo json_encode(['status' => 'success', 'invoice_id' => $id]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
