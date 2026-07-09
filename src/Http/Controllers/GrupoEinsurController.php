<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Core\Database;
use App\Core\TenantContext;

class GrupoEinsurController
{
    public function index(): void
    {
        $role = isset($_SESSION['user_role']) ? strtolower(str_replace('-', '', $_SESSION['user_role'])) : '';
        if (!\App\Core\Permission::isSuperadmin() && !\App\Core\Permission::isAdmin()) {
            header("HTTP/1.0 403 Forbidden");
            echo "Acceso denegado. Se requiere nivel de Superadmin o Admin.";
            exit;
        }

        $db = Database::getInstance();

        // 1. Estadísticas Globales
        $sqlGlobal = "
            SELECT 
                COUNT(id) as total_deals,
                SUM(CASE WHEN status = 'Ganado' THEN 1 ELSE 0 END) as won_deals_count,
                SUM(CASE WHEN status = 'Perdido' THEN 1 ELSE 0 END) as lost_deals_count,
                SUM(CASE WHEN status = 'Abierto' THEN 1 ELSE 0 END) as open_deals_count,
                SUM(CASE WHEN status = 'Ganado' THEN amount ELSE 0 END) as total_won_amount,
                SUM(CASE WHEN status = 'Abierto' THEN amount ELSE 0 END) as total_open_amount
            FROM deals
        ";
        $stmtGlobal = $db->query($sqlGlobal);
        $globalStats = $stmtGlobal->fetch(\PDO::FETCH_ASSOC);

        // 2. Estadísticas por Empresa
        $sqlEmpresas = "
            SELECT 
                t.id,
                t.name as empresa,
                COUNT(d.id) as total_deals,
                SUM(CASE WHEN d.status = 'Ganado' THEN 1 ELSE 0 END) as won_deals,
                SUM(CASE WHEN d.status = 'Ganado' THEN d.amount ELSE 0 END) as won_amount,
                SUM(CASE WHEN d.status = 'Perdido' THEN 1 ELSE 0 END) as lost_deals,
                SUM(CASE WHEN d.status = 'Abierto' THEN 1 ELSE 0 END) as open_deals,
                SUM(CASE WHEN d.status = 'Abierto' THEN d.amount ELSE 0 END) as open_amount
            FROM tenants t
            LEFT JOIN deals d ON d.tenant_id = t.id
            GROUP BY t.id, t.name
            ORDER BY won_amount DESC
        ";
        $stmtEmpresas = $db->query($sqlEmpresas);
        $empresas = $stmtEmpresas->fetchAll(\PDO::FETCH_OBJ);

        // 3. Estadísticas por Vendedor (Todos los vendedores en todo el grupo)
        $sqlVendedores = "
            SELECT 
                u.id, 
                CONCAT(u.first_name, ' ', IFNULL(u.last_name, '')) as name, 
                u.email,
                t.name as tenant_name,
                COUNT(d.id) as total_deals,
                SUM(CASE WHEN d.status = 'Ganado' THEN 1 ELSE 0 END) as won_deals,
                SUM(CASE WHEN d.status = 'Ganado' THEN d.amount ELSE 0 END) as won_amount,
                SUM(CASE WHEN d.status = 'Perdido' THEN 1 ELSE 0 END) as lost_deals,
                SUM(CASE WHEN d.status = 'Abierto' THEN 1 ELSE 0 END) as open_deals
            FROM users u
            INNER JOIN tenant_users tu ON tu.user_id = u.id
            INNER JOIN tenants t ON t.id = tu.tenant_id
            LEFT JOIN deals d ON d.owner_id = u.id AND d.tenant_id = t.id
            GROUP BY u.id, u.first_name, u.last_name, u.email, t.name
            HAVING total_deals > 0 OR won_deals > 0 OR open_deals > 0
            ORDER BY won_amount DESC
        ";
        $stmtVendedores = $db->query($sqlVendedores);
        $vendedores = $stmtVendedores->fetchAll(\PDO::FETCH_OBJ);

        // 4. Tendencia mensual global (Últimos 6 meses)
        $sqlTrend = "
            SELECT 
                DATE_FORMAT(updated_at, '%Y-%m') AS month_key,
                DATE_FORMAT(updated_at, '%b %Y') AS month_label,
                SUM(CASE WHEN status = 'Ganado' THEN amount ELSE 0 END) AS won_amount,
                SUM(CASE WHEN status = 'Abierto' THEN amount ELSE 0 END) AS open_amount
            FROM deals
            WHERE updated_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY month_key, month_label
            ORDER BY month_key ASC
        ";
        $stmtTrend = $db->query($sqlTrend);
        $monthlyTrend = $stmtTrend->fetchAll(\PDO::FETCH_OBJ);

        // 5. Top 10 Negocios Abiertos Globales (Hot Pipeline)
        $sqlTopDeals = "
            SELECT d.id, d.name, d.amount, d.probability,
                   t.name AS tenant_name,
                   CONCAT(u.first_name, ' ', IFNULL(u.last_name,'')) AS owner_name
            FROM deals d
            INNER JOIN tenants t ON t.id = d.tenant_id
            LEFT JOIN users u ON u.id = d.owner_id
            WHERE d.status = 'Abierto'
            ORDER BY d.amount DESC
            LIMIT 10
        ";
        $stmtTopDeals = $db->query($sqlTopDeals);
        $topOpenDeals = $stmtTopDeals->fetchAll(\PDO::FETCH_OBJ);

        require __DIR__ . '/../../Views/grupo_einsur/index.php';
    }
}
