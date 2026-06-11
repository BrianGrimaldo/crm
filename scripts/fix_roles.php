<?php
require __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
require __DIR__ . '/../src/Core/Database.php';

$db = \App\Core\Database::getInstance();
$stmt = $db->query("SELECT id FROM tenants");
$tenants = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($tenants as $tenantId) {
    // Verificar si ya tiene roles
    $stmtCheck = $db->prepare("SELECT COUNT(*) FROM roles WHERE tenant_id = ?");
    $stmtCheck->execute([$tenantId]);
    if ($stmtCheck->fetchColumn() == 0) {
        echo "Generando roles para tenant $tenantId\n";
        $sqlRoles = "INSERT INTO `roles` (`tenant_id`, `name`, `slug`, `description`, `is_system`) VALUES
            (?, 'Super Administrador', 'super-admin', 'Acceso total al sistema', 1),
            (?, 'Gerente de Ventas',   'sales-mgr',   'Gestiona equipo de ventas', 1),
            (?, 'Vendedor',            'sales-rep',   'Operaciones de venta básicas', 1),
            (?, 'Soporte',             'support',     'Gestión de tickets y soporte', 1),
            (?, 'Solo Lectura',        'read-only',   'Acceso de solo lectura', 1)";
        $stmtRoles = $db->prepare($sqlRoles);
        $stmtRoles->execute([$tenantId, $tenantId, $tenantId, $tenantId, $tenantId]);
    }
    
    // Verificar pipeline
    $stmtCheckPipe = $db->prepare("SELECT COUNT(*) FROM pipeline_stages WHERE tenant_id = ?");
    $stmtCheckPipe->execute([$tenantId]);
    if ($stmtCheckPipe->fetchColumn() == 0) {
        echo "Generando pipeline para tenant $tenantId\n";
        $sqlPipeline = "INSERT INTO `pipeline_stages` (`tenant_id`, `name`, `position`, `probability`, `is_won`, `is_lost`, `color`) VALUES
            (?, 'Prospección',    1,  10, 0, 0, '#94A3B8'),
            (?, 'Calificación',   2,  25, 0, 0, '#38BDF8'),
            (?, 'Propuesta',      3,  50, 0, 0, '#818CF8'),
            (?, 'Negociación',    4,  75, 0, 0, '#FB923C'),
            (?, 'Ganada',         5, 100, 1, 0, '#22C55E'),
            (?, 'Perdida',        6,   0, 0, 1, '#EF4444')";
        $stmtPipe = $db->prepare($sqlPipeline);
        $stmtPipe->execute([$tenantId, $tenantId, $tenantId, $tenantId, $tenantId, $tenantId]);
    }
}
echo "Terminado\n";
