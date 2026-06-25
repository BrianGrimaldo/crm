<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->safeLoad();
require 'src/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    
    // Get tenant id
    $tenantStmt = $db->query("SELECT id FROM tenants LIMIT 1");
    $tenant = $tenantStmt->fetch(PDO::FETCH_OBJ);
    $tenant_id = $tenant ? $tenant->id : 1;

    // Get role id for 'Cobranza'
    $roleStmt = $db->query("SELECT id FROM roles WHERE slug = 'collections' OR name = 'Cobranza' LIMIT 1");
    $role = $roleStmt->fetch(PDO::FETCH_OBJ);
    
    if (!$role) {
        die("Error: El rol Cobranza no existe.\n");
    }

    $email = 'cobranza@einsur.com';
    $password_plain = 'Cobranza2026!';
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    // Check if user already exists
    $userStmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $userStmt->execute([':email' => $email]);
    $userExists = $userStmt->fetch(PDO::FETCH_OBJ);

    if ($userExists) {
        $update = $db->prepare("UPDATE users SET role_id = :role_id, password = :pwd WHERE email = :email");
        $update->execute([':role_id' => $role->id, ':pwd' => $password_hash, ':email' => $email]);
        echo "Usuario actualizado.\n";
    } else {
        $insert = $db->prepare("INSERT INTO users (tenant_id, role_id, first_name, last_name, email, password) VALUES (:tid, :rid, 'Gestor', 'Cobranza', :email, :pwd)");
        $insert->execute([
            ':tid' => $tenant_id,
            ':rid' => $role->id,
            ':email' => $email,
            ':pwd' => $password_hash
        ]);
        echo "Usuario creado exitosamente.\n";
    }
    
    echo "Email: " . $email . "\n";
    echo "Password: " . $password_plain . "\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
