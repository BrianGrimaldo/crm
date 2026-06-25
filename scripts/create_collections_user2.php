<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->safeLoad();
require 'src/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    $db->beginTransaction();

    $email = 'cobranza@einsur.com';
    $password_plain = 'Cobranza2026!';
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    // 1. Get first tenant
    $tenantStmt = $db->query("SELECT id FROM tenants LIMIT 1");
    $tenant = $tenantStmt->fetch(PDO::FETCH_OBJ);
    $tenant_id = $tenant ? $tenant->id : 1;

    // 2. Get the role id for Cobranza for this tenant
    $roleStmt = $db->prepare("SELECT id FROM roles WHERE slug = 'collections' AND tenant_id = :tid LIMIT 1");
    $roleStmt->execute([':tid' => $tenant_id]);
    $role = $roleStmt->fetch(PDO::FETCH_OBJ);

    if (!$role) {
        throw new \Exception("Role 'collections' not found for tenant {$tenant_id}.");
    }

    // 3. Create or get user
    $userStmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $userStmt->execute([':email' => $email]);
    $user = $userStmt->fetch(PDO::FETCH_OBJ);

    if ($user) {
        $user_id = $user->id;
        $db->prepare("UPDATE users SET password_hash = :pwd WHERE id = :id")->execute([':pwd' => $password_hash, ':id' => $user_id]);
        echo "Usuario actualizado.\n";
    } else {
        $uuid = bin2hex(random_bytes(16));
        $insertUser = $db->prepare("INSERT INTO users (uuid, first_name, last_name, email, password_hash, is_superadmin) VALUES (:uuid, 'Gestor', 'Cobranza', :email, :pwd, 0)");
        $insertUser->execute([
            ':uuid' => $uuid,
            ':email' => $email,
            ':pwd' => $password_hash
        ]);
        $user_id = $db->lastInsertId();
        echo "Usuario insertado.\n";
    }

    // 4. Attach user to tenant_users
    $tuStmt = $db->prepare("SELECT id FROM tenant_users WHERE user_id = :uid AND tenant_id = :tid");
    $tuStmt->execute([':uid' => $user_id, ':tid' => $tenant_id]);
    $tu = $tuStmt->fetch(PDO::FETCH_OBJ);

    if ($tu) {
        $db->prepare("UPDATE tenant_users SET role_id = :rid WHERE id = :id")->execute([':rid' => $role->id, ':id' => $tu->id]);
    } else {
        $db->prepare("INSERT INTO tenant_users (tenant_id, user_id, role_id, is_active) VALUES (:tid, :uid, :rid, 1)")->execute([
            ':tid' => $tenant_id,
            ':uid' => $user_id,
            ':rid' => $role->id
        ]);
    }

    $db->commit();
    echo "¡Listo! El usuario de cobranza ha sido creado con exito.\n";
    echo "--------------------------\n";
    echo "Email: " . $email . "\n";
    echo "Password: " . $password_plain . "\n";

} catch (\Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
