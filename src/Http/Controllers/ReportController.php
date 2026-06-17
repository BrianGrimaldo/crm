<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class ReportController
{
    public function index(): void
    {
        Permission::require('reports', 'view');
        
        $db = Database::getInstance();
        $tenantId = TenantContext::getTenantId();
        
        // Cargar Vendedores para el filtro
        $stmtUsers = $db->prepare("
            SELECT u.id, u.first_name, u.last_name 
            FROM tenant_users tu
            JOIN users u ON u.id = tu.user_id
            WHERE tu.tenant_id = :tenant_id AND tu.is_active = 1
        ");
        $stmtUsers->execute([':tenant_id' => $tenantId]);
        $vendedores = $stmtUsers->fetchAll(PDO::FETCH_OBJ);
        
        // Cargar datos de la Línea de Tiempo si hay filtros aplicados o si se solicita ver en pantalla
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $sellerId = isset($_GET['seller_id']) ? (int)$_GET['seller_id'] : null;
        $activeTab = $_GET['tab'] ?? 'general';

        $timeline = [];
        if ($activeTab === 'timeline') {
            $timeline = $this->getTimelineData($db, $tenantId, $sellerId, $startDate, $endDate);
        }
        
        require __DIR__ . '/../../Views/reports/index.php';
    }

    public function exportDeals(): void
    {
        Permission::require('reports', 'export');
        
        $db = Database::getInstance();
        $tenantId = TenantContext::getTenantId();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $sellerId = (int)($_GET['seller_id'] ?? 0);
        $status = $_GET['status'] ?? 'all';
        
        $sql = "SELECT d.name as Trato, d.amount as Valor, ps.name as Etapa, 
                       c.first_name as Contacto, a.name as Organizacion, 
                       u.first_name as Vendedor, d.created_at as Fecha
                FROM deals d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN contacts c ON c.id = d.contact_id
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id";
                 
        $params = [':tenant_id' => $tenantId];
        
        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        } else {
            if ($sellerId) {
                $sql .= " AND d.owner_id = :seller";
                $params[':seller'] = $sellerId;
            }
        }

        if ($startDate) {
            $sql .= " AND DATE(d.created_at) >= :start";
            $params[':start'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(d.created_at) <= :end";
            $params[':end'] = $endDate;
        }
        if ($status === 'won') {
            $sql .= " AND ps.is_won = 1";
        } elseif ($status === 'lost') {
            $sql .= " AND ps.is_lost = 1";
        } elseif ($status === 'open') {
            $sql .= " AND ps.is_won = 0 AND ps.is_lost = 0";
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Export to Excel (Styled HTML-based XLS)
        $filename = "Reporte_Ventas_" . date('Y-m-d') . ".xls";
        
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<style>';
        echo 'body { font-family: "Segoe UI", Arial, sans-serif; margin: 20px; }';
        echo 'table { border-collapse: collapse; width: 100%; margin-top: 10px; }';
        echo 'th { background-color: #002D62; color: #FFFFFF; font-weight: bold; border: 1px solid #94A3B8; padding: 10px; text-align: left; font-size: 11pt; }';
        echo 'td { border: 1px solid #CBD5E1; padding: 8px; font-size: 10pt; vertical-align: middle; }';
        echo 'tr:nth-child(even) { background-color: #F8FAFC; }';
        echo '.amount { text-align: right; mso-number-format: "\$\#\,\#\#0\.00"; font-weight: bold; }';
        echo '.title { font-size: 16pt; font-weight: bold; color: #0F172A; margin-bottom: 5px; }';
        echo '.subtitle { font-size: 10pt; color: #64748B; margin-bottom: 20px; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="title">Reporte de Oportunidades de Venta</div>';
        echo '<div class="subtitle">Generado el: ' . date('d/m/Y H:i:s') . '</div>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Nombre del Trato</th>';
        echo '<th>Valor</th>';
        echo '<th>Etapa</th>';
        echo '<th>Contacto</th>';
        echo '<th>Organización</th>';
        echo '<th>Vendedor Asignado</th>';
        echo '<th>Fecha de Creación</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($deals as $deal) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($deal['Trato'] ?? '') . '</td>';
            echo '<td class="amount">' . ($deal['Valor'] !== null ? (float)$deal['Valor'] : 0.0) . '</td>';
            echo '<td>' . htmlspecialchars($deal['Etapa'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($deal['Contacto'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($deal['Organizacion'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($deal['Vendedor'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($deal['Fecha'] ?? '') . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit;
    }

    public function exportTimeline(): void
    {
        Permission::require('reports', 'export');
        
        $db = Database::getInstance();
        $tenantId = TenantContext::getTenantId();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $sellerId = isset($_GET['seller_id']) && $_GET['seller_id'] !== '' ? (int)$_GET['seller_id'] : null;
        
        $timeline = $this->getTimelineData($db, $tenantId, $sellerId, $startDate, $endDate);
        
        $filename = "Reporte_LineaTiempo_Vendedores_" . date('Y-m-d') . ".xls";
        
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        echo '<style>';
        echo 'body { font-family: "Segoe UI", Arial, sans-serif; margin: 20px; }';
        echo 'table { border-collapse: collapse; width: 100%; margin-top: 10px; }';
        echo 'th { background-color: #002D62; color: #FFFFFF; font-weight: bold; border: 1px solid #94A3B8; padding: 10px; text-align: left; font-size: 11pt; }';
        echo 'td { border: 1px solid #CBD5E1; padding: 8px; font-size: 10pt; vertical-align: middle; }';
        echo 'tr:nth-child(even) { background-color: #F8FAFC; }';
        echo '.amount { text-align: right; mso-number-format: "\$\#\,\#\#0\.00"; font-weight: bold; }';
        echo '.duration { font-weight: 600; color: #002D62; }';
        echo '.title { font-size: 16pt; font-weight: bold; color: #0F172A; margin-bottom: 5px; }';
        echo '.subtitle { font-size: 10pt; color: #64748B; margin-bottom: 20px; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<div class="title">Línea de Tiempo de Oportunidades por Etapa</div>';
        echo '<div class="subtitle">Generado el: ' . date('d/m/Y H:i:s') . '</div>';
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Oportunidad (Trato)</th>';
        echo '<th>Vendedor Asignado</th>';
        echo '<th>Etapa</th>';
        echo '<th>Fecha de Entrada</th>';
        echo '<th>Fecha de Salida</th>';
        echo '<th>Tiempo en Etapa</th>';
        echo '<th>Monto</th>';
        echo '<th>Estado Actual</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($timeline as $row) {
            $durationFormatted = $this->formatDuration((int)$row['duration']);
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['deal_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['seller_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['stage_name']) . '</td>';
            echo '<td>' . date('d/m/Y H:i', strtotime($row['entered_at'])) . '</td>';
            echo '<td>' . ($row['exited_at'] ? date('d/m/Y H:i', strtotime($row['exited_at'])) : 'Activo') . '</td>';
            echo '<td class="duration">' . htmlspecialchars($durationFormatted) . '</td>';
            echo '<td class="amount">' . (float)$row['amount'] . '</td>';
            echo '<td>' . htmlspecialchars($row['status']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit;
    }

    public function analytics(): void
    {
        Permission::require('reports', 'view');
        
        $db = Database::getInstance();
        $tenantId = TenantContext::getTenantId();
        
        // 1. Tendencia de ingresos (6 meses)
        $stmtRevenue = $db->prepare("
            SELECT 
                DATE_FORMAT(updated_at, '%Y-%m') AS month_key,
                DATE_FORMAT(updated_at, '%b %Y') AS month_label,
                SUM(CASE WHEN status = 'Ganado' THEN amount ELSE 0 END) AS won_amount,
                SUM(CASE WHEN status = 'Perdido' THEN amount ELSE 0 END) AS lost_amount
            FROM deals
            WHERE tenant_id = :tenant_id
              AND updated_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month_key, month_label
            ORDER BY month_key ASC
        ");
        $stmtRevenue->execute([':tenant_id' => $tenantId]);
        $revenueData = $stmtRevenue->fetchAll(PDO::FETCH_OBJ);

        $chartMonthLabels = json_encode(array_column($revenueData, 'month_label'));
        $chartWonAmounts  = json_encode(array_map('floatval', array_column($revenueData, 'won_amount')));
        $chartLostAmounts = json_encode(array_map('floatval', array_column($revenueData, 'lost_amount')));

        // 2. Negocios y Montos por Etapa
        $stmtStages = $db->prepare("
            SELECT ps.name, ps.color,
                   COUNT(d.id) AS deal_count,
                   IFNULL(SUM(d.amount), 0) AS total_amount
            FROM pipeline_stages ps
            LEFT JOIN deals d ON d.stage_id = ps.id AND d.tenant_id = ps.tenant_id
            WHERE ps.tenant_id = :tenant_id
            GROUP BY ps.id, ps.name, ps.color, ps.position
            ORDER BY ps.position ASC
        ");
        $stmtStages->execute([':tenant_id' => $tenantId]);
        $stageStats = $stmtStages->fetchAll(PDO::FETCH_OBJ);

        $stageLabels = json_encode(array_column($stageStats, 'name'));
        $stageCounts = json_encode(array_column($stageStats, 'deal_count'));
        $stageAmounts = json_encode(array_map('floatval', array_column($stageStats, 'total_amount')));
        $stageColors = json_encode(array_column($stageStats, 'color'));

        // 3. Rendimiento por Vendedor
        $stmtSellers = $db->prepare("
            SELECT 
                CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name,
                SUM(CASE WHEN d.status = 'Ganado' THEN d.amount ELSE 0 END) AS won_amount,
                SUM(CASE WHEN d.status = 'Abierto' THEN d.amount ELSE 0 END) AS open_amount
            FROM deals d
            JOIN users u ON u.id = d.owner_id
            WHERE d.tenant_id = :tenant_id
            GROUP BY u.id, owner_name
            ORDER BY won_amount DESC
        ");
        $stmtSellers->execute([':tenant_id' => $tenantId]);
        $sellerStats = $stmtSellers->fetchAll(PDO::FETCH_OBJ);

        $sellerLabels = json_encode(array_column($sellerStats, 'owner_name'));
        $sellerWonAmounts = json_encode(array_map('floatval', array_column($sellerStats, 'won_amount')));
        $sellerOpenAmounts = json_encode(array_map('floatval', array_column($sellerStats, 'open_amount')));

        // 4. Tasa de éxito general (Ganado vs Perdido count)
        $stmtWinLoss = $db->prepare("
            SELECT 
                COUNT(CASE WHEN status = 'Ganado' THEN 1 END) AS won_count,
                COUNT(CASE WHEN status = 'Perdido' THEN 1 END) AS lost_count,
                COUNT(CASE WHEN status = 'Abierto' THEN 1 END) AS open_count
            FROM deals
            WHERE tenant_id = :tenant_id
        ");
        $stmtWinLoss->execute([':tenant_id' => $tenantId]);
        $winLossStats = $stmtWinLoss->fetch(PDO::FETCH_OBJ);

        $winLossCounts = json_encode([
            (int)($winLossStats->won_count ?? 0),
            (int)($winLossStats->lost_count ?? 0),
            (int)($winLossStats->open_count ?? 0)
        ]);

        require __DIR__ . '/../../Views/reports/analytics.php';
    }

    public function getTimelineData(object $db, int $tenantId, ?int $sellerId, ?string $startDate, ?string $endDate): array
    {
        $sql = "SELECT d.id, d.name, d.amount, d.currency_code, d.created_at, d.status, d.stage_id,
                       ps.name AS current_stage_name,
                       CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) AS seller_name,
                       d.owner_id
                FROM deals d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN users u ON u.id = d.owner_id
                WHERE d.tenant_id = :tenant_id";
                 
        $params = [':tenant_id' => $tenantId];
        
        if (Permission::isRestrictedToOwnRecords()) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $_SESSION['user_id'];
        } else {
            if ($sellerId) {
                $sql .= " AND d.owner_id = :seller_id";
                $params[':seller_id'] = $sellerId;
            }
        }

        if ($startDate) {
            $sql .= " AND DATE(d.created_at) >= :start_date";
            $params[':start_date'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(d.created_at) <= :end_date";
            $params[':end_date'] = $endDate;
        }

        $sql .= " ORDER BY d.created_at DESC";

        $stmtDeals = $db->prepare($sql);
        $stmtDeals->execute($params);
        $deals = $stmtDeals->fetchAll(PDO::FETCH_OBJ);

        // Fetch all pipeline stages to map stage IDs to names
        $stmtStages = $db->prepare("SELECT id, name FROM pipeline_stages WHERE tenant_id = :tenant_id");
        $stmtStages->execute([':tenant_id' => $tenantId]);
        $stagesMap = $stmtStages->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch all audit logs for deals in this tenant to avoid N+1 queries
        $stmtLogs = $db->prepare("
            SELECT id, entity_id, action, new_values, created_at
            FROM audit_logs
            WHERE tenant_id = :tenant_id
              AND entity_type = 'deal'
              AND action IN ('create', 'update', 'update_stage')
            ORDER BY entity_id, created_at ASC
        ");
        $stmtLogs->execute([':tenant_id' => $tenantId]);
        $allLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);

        $logsByDeal = [];
        foreach ($allLogs as $log) {
            $logsByDeal[$log['entity_id']][] = $log;
        }

        $timeline = [];
        foreach ($deals as $deal) {
            $dealId = $deal->id;
            $dealLogs = $logsByDeal[$dealId] ?? [];
            
            $transitions = [];
            $currentStageId = null;
            $lastChangeTime = strtotime($deal->created_at);
            
            foreach ($dealLogs as $log) {
                $newVals = json_decode($log['new_values'], true);
                if (isset($newVals['stage_id'])) {
                    $newStageId = (int)$newVals['stage_id'];
                    if ($currentStageId === null) {
                        $currentStageId = $newStageId;
                        $lastChangeTime = strtotime($log['created_at']);
                    } elseif ($newStageId !== $currentStageId) {
                        $exitTime = strtotime($log['created_at']);
                        $duration = $exitTime - $lastChangeTime;
                        
                        $transitions[] = [
                            'stage_name' => $stagesMap[$currentStageId] ?? 'Etapa desconocida',
                            'entered_at' => date('Y-m-d H:i:s', $lastChangeTime),
                            'exited_at' => date('Y-m-d H:i:s', $exitTime),
                            'duration' => $duration
                        ];
                        
                        $currentStageId = $newStageId;
                        $lastChangeTime = $exitTime;
                    }
                }
            }
            
            // Current / last stage
            if ($currentStageId === null) {
                $currentStageId = $deal->stage_id;
                $lastChangeTime = strtotime($deal->created_at);
            }
            $now = time();
            $duration = $now - $lastChangeTime;
            $transitions[] = [
                'stage_name' => $stagesMap[$currentStageId] ?? $deal->current_stage_name ?? 'Etapa desconocida',
                'entered_at' => date('Y-m-d H:i:s', $lastChangeTime),
                'exited_at' => null,
                'duration' => $duration
            ];
            
            foreach ($transitions as $trans) {
                $timeline[] = [
                    'deal_id' => $dealId,
                    'deal_name' => $deal->name,
                    'amount' => $deal->amount,
                    'currency_code' => $deal->currency_code,
                    'seller_name' => $deal->seller_name,
                    'status' => $deal->status,
                    'stage_name' => $trans['stage_name'],
                    'entered_at' => $trans['entered_at'],
                    'exited_at' => $trans['exited_at'],
                    'duration' => $trans['duration']
                ];
            }
        }
        return $timeline;
    }

    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} seg";
        }
        $minutes = (int)floor($seconds / 60);
        if ($minutes < 60) {
            return "{$minutes} min";
        }
        $hours = (int)floor($minutes / 60);
        $remMin = $minutes % 60;
        if ($hours < 24) {
            return "{$hours} hrs" . ($remMin > 0 ? " {$remMin} min" : "");
        }
        $days = (int)floor($hours / 24);
        $remHrs = $hours % 24;
        return "{$days} d" . ($remHrs > 0 ? " {$remHrs} hrs" : "");
    }
}
