<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->safeLoad();
require 'src/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    $db->beginTransaction();

    // Get tenant id
    $tenantStmt = $db->query("SELECT id FROM tenants LIMIT 1");
    $tenant = $tenantStmt->fetch(PDO::FETCH_OBJ);
    $tenant_id = $tenant ? $tenant->id : 1;

    // Check if role exists
    $stmt = $db->query("SELECT id FROM roles WHERE slug = 'collections' OR name = 'Cobranza'");
    $exists = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$exists) {
        $insert = $db->prepare("INSERT INTO roles (tenant_id, name, slug) VALUES (:tid, 'Cobranza', 'collections')");
        $insert->execute([':tid' => $tenant_id]);
        $role_id = $db->lastInsertId();
    } else {
        $role_id = $exists->id;
    }

    // Get finance permissions (view, update)
    $stmt = $db->query("SELECT id, action FROM permissions WHERE module = 'finance' AND action IN ('view', 'update')");
    $perms = $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($perms as $p) {
        $stmtLink = $db->prepare("SELECT * FROM role_permissions WHERE role_id = :r AND permission_id = :p");
        $stmtLink->execute([':r' => $role_id, ':p' => $p->id]);
        if (!$stmtLink->fetch()) {
            $insertLink = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (:r, :p)");
            $insertLink->execute([':r' => $role_id, ':p' => $p->id]);
        }
    }

    $db->commit();
    echo "Rol de 'Cobranza' creado exitosamente y permisos asignados.\n";
} catch (\Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error: " . $e->getMessage();
}
