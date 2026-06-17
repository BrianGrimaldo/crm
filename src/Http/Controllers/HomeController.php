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

        // Datos para Chart.js (JSON)
        $chartMonthLabels = json_encode(array_map(fn($r) => $r->month_label, $dealsByMonth));
        $chartWonAmounts  = json_encode(array_map(fn($r) => (float)$r->won_amount, $dealsByMonth));
        $chartLostAmounts = json_encode(array_map(fn($r) => (float)$r->lost_amount, $dealsByMonth));

        $stageLabels  = json_encode(array_map(fn($r) => $r->name, $dealsSummary));
        $stageCounts  = json_encode(array_map(fn($r) => (int)$r->deal_count, $dealsSummary));
        $stageColors  = json_encode(array_map(fn($r) => $r->color ?? '#94a3b8', $dealsSummary));

        $chart_estados = [];
        $chart_tipos = [];

        // Obtener actividades pendientes del día para el usuario
        $pendingTasks = $this->taskModel->getPendingForToday((int)$_SESSION['user_id']);

        require __DIR__ . '/../../Views/dashboard/index.php';
    }
}
