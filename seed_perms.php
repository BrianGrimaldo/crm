<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
require 'src/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    $db->beginTransaction();

    $perms = [
        ['name' => 'Ver Finanzas', 'module' => 'finance', 'action' => 'view'],
        ['name' => 'Crear Facturas', 'module' => 'finance', 'action' => 'create'],
        ['name' => 'Editar Facturas', 'module' => 'finance', 'action' => 'update'],
        ['name' => 'Eliminar Facturas', 'module' => 'finance', 'action' => 'delete']
    ];

    $role_id = 2; // Gerente de Ventas

    foreach ($perms as $p) {
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM permissions WHERE module = :m AND action = :a");
        $stmt->execute([':m' => $p['module'], ':a' => $p['action']]);
        $exists = $stmt->fetch(PDO::FETCH_OBJ);

        if (!$exists) {
            $insert = $db->prepare("INSERT INTO permissions (module, action) VALUES (:m, :a)");
            $insert->execute([':m' => $p['module'], ':a' => $p['action']]);
            $perm_id = $db->lastInsertId();
        } else {
            $perm_id = $exists->id;
        }

        // Assign to role 2 if not exists
        $stmtLink = $db->prepare("SELECT * FROM role_permissions WHERE role_id = :r AND permission_id = :p");
        $stmtLink->execute([':r' => $role_id, ':p' => $perm_id]);
        if (!$stmtLink->fetch()) {
            $insertLink = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:r, :p)");
            $insertLink->execute([':r' => $role_id, ':p' => $perm_id]);
        }
    }

    $db->commit();
    echo "Permisos de finanzas creados y asignados al Gerente de Ventas exitosamente!\n";
} catch (\Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
