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
        
        $sql = "SELECT d.name as Trato, d.value as Valor, ps.name as Etapa, 
                       c.first_name as Contacto, a.name as Organizacion, 
                       u.first_name as Vendedor, d.created_at as Fecha
                FROM deals d
                LEFT JOIN pipeline_stages ps ON ps.id = d.stage_id
                LEFT JOIN contacts c ON c.id = d.contact_id
                LEFT JOIN accounts a ON a.id = d.account_id
                LEFT JOIN users u ON u.id = d.assigned_to
                WHERE d.tenant_id = :tenant_id";
                
        $params = [':tenant_id' => $tenantId];
        
        if ($startDate) {
            $sql .= " AND DATE(d.created_at) >= :start";
            $params[':start'] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND DATE(d.created_at) <= :end";
            $params[':end'] = $endDate;
        }
        if ($sellerId) {
            $sql .= " AND d.assigned_to = :seller";
            $params[':seller'] = $sellerId;
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
        
        // Export to Excel (CSV compatible)
        $filename = "Reporte_Ventas_" . date('Y-m-d') . ".csv";
        
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        // Output BOM for Excel UTF-8 support
        echo "\xEF\xBB\xBF";
        
        $output = fopen("php://output", "w");
        
        // Headers
        fputcsv($output, ['Nombre del Trato', 'Valor', 'Etapa', 'Contacto', 'Organizacion', 'Vendedor Asignado', 'Fecha de Creacion']);
        
        foreach ($deals as $deal) {
            fputcsv($output, [
                $deal['Trato'],
                $deal['Valor'],
                $deal['Etapa'],
                $deal['Contacto'],
                $deal['Organizacion'],
                $deal['Vendedor'],
                $deal['Fecha']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
