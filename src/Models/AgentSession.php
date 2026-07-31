<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\TenantContext;
use PDO;

class AgentSession
{
    private PDO $db;
    private string $table = 'agent_sessions';

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * Registra o actualiza la sesión del agente actual.
     */
    public function ping(int $userId, string $status = 'online'): void
    {
        $tenantId = TenantContext::getTenantId();
        
        // Buscar si ya tiene una sesión abierta hoy
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table} 
            WHERE user_id = :user_id AND tenant_id = :tenant_id 
              AND DATE(started_at) = CURDATE()
              AND ended_at IS NULL
            ORDER BY started_at DESC LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId, ':tenant_id' => $tenantId]);
        $session = $stmt->fetch(PDO::FETCH_OBJ);

        if ($session) {
            // Actualizar last_ping_at y status
            $upd = $this->db->prepare("
                UPDATE {$this->table} 
                SET last_ping_at = NOW(), status = :status
                WHERE id = :id
            ");
            $upd->execute([':status' => $status, ':id' => $session->id]);
        } else {
            // Crear nueva sesión
            $ins = $this->db->prepare("
                INSERT INTO {$this->table} (tenant_id, user_id, status)
                VALUES (:tenant_id, :user_id, :status)
            ");
            $ins->execute([
                ':tenant_id' => $tenantId,
                ':user_id'   => $userId,
                ':status'    => $status
            ]);
        }
    }

    /**
     * Marca la sesión como finalizada (logout o timeout).
     */
    public function endSession(int $userId): void
    {
        $tenantId = TenantContext::getTenantId();
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET status = 'offline', ended_at = NOW()
            WHERE user_id = :user_id AND tenant_id = :tenant_id 
              AND ended_at IS NULL
        ");
        $stmt->execute([':user_id' => $userId, ':tenant_id' => $tenantId]);
    }

    /**
     * Obtiene los agentes activos en los últimos 5 minutos.
     */
    public function getActiveAgents(): array
    {
        $tenantId = TenantContext::getTenantId();
        
        $stmt = $this->db->prepare("
            SELECT s.*, u.name as user_name, u.email, u.avatar_url,
                   TIMESTAMPDIFF(MINUTE, s.last_ping_at, NOW()) as minutes_idle
            FROM {$this->table} s
            JOIN users u ON s.user_id = u.id
            WHERE s.tenant_id = :tenant_id 
              AND s.ended_at IS NULL
              AND s.last_ping_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
            ORDER BY s.status ASC, s.last_ping_at DESC
        ");
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
