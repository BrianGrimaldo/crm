<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\TenantContext;

class VendedoresController
{
    public function index(): void
    {
        $role = isset($_SESSION['user_role']) ? strtolower(str_replace('-', '', $_SESSION['user_role'])) : '';
        if ($role !== 'superadmin' && $role !== 'admin') {
            header("HTTP/1.0 403 Forbidden");
            echo "Acceso denegado. Se requiere nivel de Superadmin.";
            exit;
        }

        $tenantId = TenantContext::getTenantId();
        $db = Database::getInstance();

        $sql = "
            SELECT 
                u.id, 
                CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as name, 
                u.email,
                COUNT(d.id) as total_deals,
                SUM(CASE WHEN d.status = 'Ganado' THEN 1 ELSE 0 END) as won_deals,
                SUM(CASE WHEN d.status = 'Ganado' THEN d.amount ELSE 0 END) as won_amount,
                SUM(CASE WHEN d.status = 'Perdido' THEN 1 ELSE 0 END) as lost_deals,
                SUM(CASE WHEN d.status = 'Abierto' THEN 1 ELSE 0 END) as open_deals
            FROM users u
            INNER JOIN tenant_users tu ON tu.user_id = u.id
            LEFT JOIN deals d ON d.owner_id = u.id AND d.tenant_id = :t1
            WHERE tu.tenant_id = :t2
            GROUP BY u.id, u.first_name, u.last_name, u.email
            ORDER BY won_amount DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':t1' => $tenantId,
            ':t2' => $tenantId
        ]);
        $vendedores = $stmt->fetchAll(\PDO::FETCH_OBJ);

        require __DIR__ . '/../../Views/vendedores/index.php';
    }
}
