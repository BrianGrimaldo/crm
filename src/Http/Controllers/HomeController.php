<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\AuditLog;
use App\Models\Account;
use App\Models\Deal;

class HomeController
{
    public function index(): void
    {
        require __DIR__ . '/../../Views/home/index.php';
    }



    public function dashboard(): void
    {
        $tenantId = \App\Core\TenantContext::getTenantId();
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        
        $dealModel = new Deal();
        $dealStats = $dealModel->getDashboardStats();
        $dealsSummary = $dealModel->summaryByStage();
        $dealsByMonth = $dealModel->getDealsByMonth();
        $topOpenDeals = $dealModel->getTopOpenDeals(5);
        $statsByOwner = $dealModel->getStatsByOwner();
        
        $totalDeals = $dealStats['total_deals'] ?? 0;
        $wonDeals = $dealStats['won_deals_count'] ?? 0;
        $lostDeals = $dealStats['lost_deals_count'] ?? 0;
        $closedDeals = $wonDeals + $lostDeals;
        $conversionRate = $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 1) : 0;
        $totalPipelineAmount = $dealStats['open_deals_amount'] ?? 0;

        $contactModel = new Contact();
        $totalContacts = $contactModel->getTotalContacts();

        $accountModel = new Account();
        $totalAccounts = $accountModel->getTotalAccounts();

        $auditLogModel = new AuditLog();
        $recentActivities = $auditLogModel->getRecentActivity(8);

        // Datos para Chart.js (JSON)
        $chartMonthLabels = json_encode(array_map(fn($r) => $r->month_label, $dealsByMonth));
        $chartWonAmounts  = json_encode(array_map(fn($r) => (float)$r->won_amount, $dealsByMonth));
        $chartLostAmounts = json_encode(array_map(fn($r) => (float)$r->lost_amount, $dealsByMonth));

        $stageLabels  = json_encode(array_map(fn($r) => $r->name, $dealsSummary));
        $stageCounts  = json_encode(array_map(fn($r) => (int)$r->deal_count, $dealsSummary));
        $stageColors  = json_encode(array_map(fn($r) => $r->color ?? '#94a3b8', $dealsSummary));

        // Datos para Gráficos de Inventario/Equipos
        $db = \App\Core\Database::getInstance();
        try {
            $sql_estados_chart = "SELECT estado, COUNT(*) as cantidad FROM equipos GROUP BY estado";
            $chart_estados = $db->query($sql_estados_chart)->fetchAll(\PDO::FETCH_ASSOC);
            
            $sql_tipos_chart = "SELECT tipo_equipo, COUNT(*) as cantidad FROM equipos GROUP BY tipo_equipo ORDER BY cantidad DESC";
            $chart_tipos = $db->query($sql_tipos_chart)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $chart_estados = [];
            $chart_tipos = [];
        }

        require __DIR__ . '/../../Views/dashboard/index.php';
    }
}
