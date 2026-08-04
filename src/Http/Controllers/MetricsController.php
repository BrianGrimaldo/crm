<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AgentSession;
use App\Models\Ticket;
use App\Core\Permission;
use PDO;

class MetricsController
{
    private AgentSession $agentSession;
    private PDO $db;

    public function __construct()
    {
        $this->agentSession = new AgentSession();
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Muestra el dashboard de métricas en tiempo real.
     */
    public function index(): void
    {
        Permission::require('tickets', 'view'); // Manager/Admin or Vendedor with tickets access
        
        // Registrar mi propio ping
        $this->agentSession->ping($_SESSION['user_id']);

        $tenantName = $_SESSION['tenant_name'] ?? 'Empresa';
        $userEmail  = $_SESSION['user_email'] ?? 'Usuario';
        
        // Agentes online
        $activeAgents = $this->agentSession->getActiveAgents();
        
        // Tickets del día
        $tenantId = \App\Core\TenantContext::getTenantId();
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_today,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_today,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_today
            FROM tickets 
            WHERE tenant_id = :tenant_id AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        $ticketStats = $stmt->fetch(PDO::FETCH_OBJ);

        // Desempeño por tipificación hoy
        $stmtTip = $this->db->prepare("
            SELECT tp.name, tp.color, COUNT(t.id) as count
            FROM tickets t
            JOIN tipifications tp ON t.tipification_id = tp.id
            WHERE t.tenant_id = :tenant_id AND DATE(t.resolved_at) = CURDATE()
            GROUP BY tp.id, tp.name, tp.color
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmtTip->execute([':tenant_id' => $tenantId]);
        $tipStats = $stmtTip->fetchAll(PDO::FETCH_OBJ);

        require __DIR__ . '/../../Views/reports/metrics_realtime.php';
    }

    /**
     * Endpoint AJAX para actualizar gráficas.
     */
    public function dataJson(): void
    {
        header('Content-Type: application/json');
        Permission::require('tickets', 'view');

        $this->agentSession->ping($_SESSION['user_id']);
        
        echo json_encode([
            'agents' => $this->agentSession->getActiveAgents(),
            'timestamp' => date('H:i:s')
        ]);
    }
}
