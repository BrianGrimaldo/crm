<?php

require __DIR__ . '/../vendor/autoload.php';

$envPath = __DIR__ . '/../.env';
$envVariables = @parse_ini_file($envPath);
$host = $envVariables['DB_HOST'] ?? '127.0.0.1';
$port = $envVariables['DB_PORT'] ?? '3306';
$user = $envVariables['DB_USERNAME'] ?? 'root';
$pass = $envVariables['DB_PASSWORD'] ?? '';
$dbName = $envVariables['DB_DATABASE'] ?? 'crm_einsurglobal_dev';
$charset = $envVariables['DB_CHARSET'] ?? 'utf8mb4';

$pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName;charset=$charset", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
]);

$email = 'admin@einsurglobal.com';
$password = 'Admin@2026!';

echo "Testing login for $email...\n";

$stmt = $pdo->prepare("SELECT id, email, password_hash, is_active FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    die("Error: User not found.\n");
}

echo "User found. ID: {$user->id}, Active: {$user->is_active}\n";

if (!password_verify($password, $user->password_hash)) {
    echo "Error: Password verification failed.\n";
    echo "Hash in DB: {$user->password_hash}\n";
    // Let's generate a new hash and update it
    $newHash = password_hash($password, PASSWORD_BCRYPT);
    echo "Updating with new hash: $newHash\n";
    $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
    $updateStmt->execute([':hash' => $newHash, ':id' => $user->id]);
    echo "Password updated! Try logging in again.\n";
} else {
    echo "Password verification passed!\n";
    
    // Check tenant
    $stmtTenant = $pdo->prepare("
        SELECT t.id as tenant_id, t.name as tenant_name 
        FROM tenant_users tu
        JOIN tenants t ON t.id = tu.tenant_id
        WHERE tu.user_id = :user_id AND tu.is_active = 1 AND t.is_active = 1
        LIMIT 1
    ");
    $stmtTenant->execute([':user_id' => $user->id]);
    $tenant = $stmtTenant->fetch();
    
    if (!$tenant) {
        echo "Error: Tenant not found or active.\n";
    } else {
        echo "Tenant found: {$tenant->tenant_name} (ID: {$tenant->tenant_id})\n";
    }
}
