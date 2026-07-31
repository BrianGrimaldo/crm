<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\TenantContext;
use App\Core\Permission;
use PDO;

class SLAController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Muestra el dashboard de SLA (Service Level Agreement).
     */
    public function index(): void
    {
        Permission::require('tickets', 'view'); // Requiere permiso de tickets para ver SLA

        $tenantId = TenantContext::getTenantId();
        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail  = $_SESSION['user_email'] ?? 'Usuario';

        // Obtener políticas SLA
        $stmt = $this->db->prepare("SELECT * FROM sla_policies WHERE tenant_id = :tenant_id AND is_active = 1 ORDER BY priority DESC");
        $stmt->execute([':tenant_id' => $tenantId]);
        $policies = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Calcular cumplimiento general (Últimos 30 días)
        $statsStmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_tickets,
                AVG(sla_first_response_minutes) as avg_first_response,
                AVG(sla_resolution_minutes) as avg_resolution,
                SUM(CASE WHEN sla_first_response_minutes <= 60 THEN 1 ELSE 0 END) as met_first_response_sla,
                SUM(CASE WHEN sla_resolution_minutes <= 1440 THEN 1 ELSE 0 END) as met_resolution_sla
            FROM tickets 
            WHERE tenant_id = :tenant_id 
              AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              AND status IN ('resolved', 'closed')
        ");
        $statsStmt->execute([':tenant_id' => $tenantId]);
        $globalStats = $statsStmt->fetch(PDO::FETCH_OBJ);

        // Agentes y su desempeño
        $agentsStmt = $this->db->prepare("
            SELECT 
                u.name,
                COUNT(t.id) as resolved_tickets,
                AVG(t.sla_first_response_minutes) as avg_response,
                AVG(t.sla_resolution_minutes) as avg_resolution
            FROM tickets t
            JOIN users u ON t.assigned_to = u.id
            WHERE t.tenant_id = :tenant_id
              AND t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              AND t.status IN ('resolved', 'closed')
            GROUP BY u.id, u.name
            ORDER BY resolved_tickets DESC
        ");
        $agentsStmt->execute([':tenant_id' => $tenantId]);
        $agentStats = $agentsStmt->fetchAll(PDO::FETCH_OBJ);

        require __DIR__ . '/../../Views/reports/sla_dashboard.php';
    }
}
