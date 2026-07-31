<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\AuditLog;
use App\Models\Account;
use App\Models\Deal;

class HomeController
{
    private Contact $contactModel;
    private AuditLog $auditLogModel;
    private Account $accountModel;
    private Deal $dealModel;
    private \App\Models\Task $taskModel;

    public function __construct()
    {
        $this->contactModel = new Contact();
        $this->auditLogModel = new AuditLog();
        $this->accountModel = new Account();
        $this->dealModel = new Deal();
        $this->taskModel = new \App\Models\Task();
    }

    public function index(): void
    {
        $path = function_exists('url') ? \url('/login') : '/login';
        header('Location: ' . $path);
        exit;
    }

    public function dashboard(): void
    {
        $tenantId = \App\Core\TenantContext::getTenantId();
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';

        $roleStr = strtolower(str_replace(['-', ' '], '', $_SESSION['user_role'] ?? ''));
        $isCobranza = strpos($roleStr, 'cobranza') !== false 
                   || strpos($roleStr, 'collection') !== false 
                   || strpos($roleStr, 'cobrador') !== false;

        if ($isCobranza) {
            $this->cobranzaDashboard($tenantName);
            return;
        }

        $ownerId = \App\Core\Permission::isRestrictedToOwnRecords() ? (int) ($_SESSION['user_id'] ?? 0) : null;

        $dealStats = $this->dealModel->getDashboardStats();
        $dealsSummary = $this->dealModel->summaryByStage();
        $dealsByMonth = $this->dealModel->getDealsByMonth();
        $topOpenDeals = $this->dealModel->getTopOpenDeals(5);
        $statsByOwner = $this->dealModel->getStatsByOwner();

        $totalDeals = $dealStats['total_deals'] ?? 0;
        $wonDeals = $dealStats['won_deals_count'] ?? 0;
        $lostDeals = $dealStats['lost_deals_count'] ?? 0;
        $closedDeals = $wonDeals + $lostDeals;
        $conversionRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 1) : 0;
        $totalPipelineAmount = $dealStats['open_deals_amount'] ?? 0;

        $totalContacts = $this->contactModel->getTotalContacts();

        $totalAccounts = $this->accountModel->getTotalAccounts();

        $recentActivities = $this->auditLogModel->getRecentActivity(8);

        // Métricas de Facturación y Cobranza (separadas de "Ganado")
        $invoiceModel = new \App\Models\Invoice();
        $invoiceStats = $invoiceModel->getFinanceStats($ownerId);
        $totalFacturado = $invoiceStats['total_facturado'] ?? 0;
        $totalCobrado = $invoiceStats['total_cobrado'] ?? 0;

        // Datos para Chart.js (JSON)
        $chartMonthLabels = json_encode(array_map(fn($r) => $r->month_label, $dealsByMonth));
        $chartWonAmounts = json_encode(array_map(fn($r) => (float) $r->won_amount, $dealsByMonth));
        $chartLostAmounts = json_encode(array_map(fn($r) => (float) $r->lost_amount, $dealsByMonth));

        $stageLabels = json_encode(array_map(fn($r) => $r->name, $dealsSummary));
        $stageCounts = json_encode(array_map(fn($r) => (int) $r->deal_count, $dealsSummary));
        $stageColors = json_encode(array_map(fn($r) => $r->color ?? '#94a3b8', $dealsSummary));

        $chart_estados = [];
        $chart_tipos = [];

        // Obtener actividades pendientes del día para el usuario
        $pendingTasks = $this->taskModel->getPendingForToday((int) $_SESSION['user_id']);

        require __DIR__ . '/../../Views/dashboard/index.php';
    }

    private function cobranzaDashboard(string $tenantName, bool $isCEO = false): void
    {
        $invoiceModel = new \App\Models\Invoice();

        $financeStats = $invoiceModel->getFinanceStats();
        $invoicesByMonth = $invoiceModel->getInvoicesByMonth();
        $statusDistribution = $invoiceModel->getStatusDistribution();

        // Datos para Chart.js
        $chartMonthLabels = json_encode(array_map(fn($r) => $r->month_label, $invoicesByMonth));
        $chartEmitido = json_encode(array_map(fn($r) => (float) $r->total_emitido, $invoicesByMonth));
        $chartCobrado = json_encode(array_map(fn($r) => (float) $r->total_cobrado, $invoicesByMonth));

        $donutLabels = json_encode(array_map(fn($r) => ucfirst($r->status), $statusDistribution));
        $donutAmounts = json_encode(array_map(fn($r) => (float) $r->amount, $statusDistribution));
        $donutColors = json_encode(array_map(function ($r) {
            $colors = [
                'emitida' => '#3b82f6',
                'parcial' => '#f59e0b',
                'vencida' => '#ef4444',
                'pagada' => '#10b981',
                'cancelada' => '#9ca3af'
            ];
            return $colors[$r->status] ?? '#64748b';
        }, $statusDistribution));

        require __DIR__ . '/../../Views/dashboard/cobranza.php';
    }
}
