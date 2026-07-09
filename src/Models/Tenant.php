<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Tenant
{
    protected string $table = 'tenants';
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function findById(int $id): ?object
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO {$this->table} (uuid, name, slug, email, phone, logo_url, address, timezone, currency_code, is_active) 
                VALUES (UUID(), :name, :slug, :email, :phone, :logo_url, :address, :timezone, :currency_code, :is_active)";
        
        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':email' => $data['email'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':logo_url' => $data['logo_url'] ?? null,
            ':address' => $data['address'] ?? null,
            ':timezone' => $data['timezone'] ?? 'America/Mexico_City',
            ':currency_code' => $data['currency_code'] ?? 'MXN',
            ':is_active' => $data['is_active'] ?? 1
        ]);

        if ($success) {
            $tenantId = (int)$this->db->lastInsertId();
            $this->seedTenantDefaults($tenantId);
            return $tenantId;
        }
        return 0;
    }

    private function seedTenantDefaults(int $tenantId): void
    {
        try {
            // Seed Roles
            $sqlRoles = "INSERT INTO `roles` (`tenant_id`, `name`, `slug`, `description`, `is_system`) VALUES
                (:t1, 'Super Administrador', 'super-admin', 'Acceso total al sistema', 1),
                (:t2, 'Gerente de Ventas',   'sales-mgr',   'Gestiona equipo de ventas', 1),
                (:t3, 'Vendedor',            'sales-rep',   'Operaciones de venta básicas', 1),
                (:t4, 'Soporte',             'support',     'Gestión de tickets y soporte', 1),
                (:t5, 'Solo Lectura',        'read-only',   'Acceso de solo lectura', 1)";
            $stmtRoles = $this->db->prepare($sqlRoles);
            $stmtRoles->execute([':t1'=>$tenantId, ':t2'=>$tenantId, ':t3'=>$tenantId, ':t4'=>$tenantId, ':t5'=>$tenantId]);

            // Seed Pipeline Stages
            $sqlPipeline = "INSERT INTO `pipeline_stages` (`tenant_id`, `name`, `position`, `probability`, `is_won`, `is_lost`, `color`) VALUES
                (:p1, 'Prospección',              1,   5, 0, 0, '#94A3B8'),
                (:p2, 'Contacto y calificación',  2,  15, 0, 0, '#38BDF8'),
                (:p3, 'Levantamiento',            3,  30, 0, 0, '#818CF8'),
                (:p4, 'Propuesta / cotización',   4,  45, 0, 0, '#a855f7'),
                (:p5, 'Negociación',              5,  65, 0, 0, '#FB923C'),
                (:p6, 'Ganada',                   6, 100, 1, 0, '#22C55E'),
                (:p7, 'Onboarding / entrega',     7,   0, 0, 0, '#14b8a6'),
                (:p8, 'Recompra / expansión',     8,   0, 0, 0, '#f59e0b'),
                (:p9, 'Perdida',                  9,   0, 0, 1, '#EF4444')";
            $stmtPipe = $this->db->prepare($sqlPipeline);
            $stmtPipe->execute([':p1'=>$tenantId, ':p2'=>$tenantId, ':p3'=>$tenantId, ':p4'=>$tenantId, ':p5'=>$tenantId, ':p6'=>$tenantId, ':p7'=>$tenantId, ':p8'=>$tenantId, ':p9'=>$tenantId]);
            
            // Asignar al creador como owner de esta nueva empresa
            if (isset($_SESSION['user_id'])) {
                $stmtAdminRole = $this->db->prepare("SELECT id FROM roles WHERE tenant_id = :tid AND slug = 'super-admin' LIMIT 1");
                $stmtAdminRole->execute([':tid' => $tenantId]);
                $adminRole = $stmtAdminRole->fetchColumn();
                if ($adminRole) {
                    $stmtUser = $this->db->prepare("INSERT INTO tenant_users (tenant_id, user_id, role_id, is_owner, is_active) VALUES (:tid, :uid, :rid, 1, 1)");
                    $stmtUser->execute([':tid' => $tenantId, ':uid' => $_SESSION['user_id'], ':rid' => $adminRole]);
                }
            }
        } catch (\Exception $e) {
            // Ignorar errores de seed
        }
    }

    public function update(int $id, array $data): bool
    {
        $sets = [];
        $params = [':id' => $id];
        
        foreach ($data as $key => $value) {
            $sets[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        if (empty($sets)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
