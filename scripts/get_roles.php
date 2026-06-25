<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->safeLoad();
require 'src/Core/Database.php';

try {
    $db = App\Core\Database::getInstance();
    
    // Get Roles and Permissions
    $sql = "SELECT r.name as role_name, p.module, p.action 
            FROM roles r 
            LEFT JOIN role_permissions rp ON r.id = rp.role_id 
            LEFT JOIN permissions p ON rp.permission_id = p.id 
            ORDER BY r.name, p.module, p.action";
    $stmt = $db->query($sql);
    $roles_perms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $grouped = [];
    foreach ($roles_perms as $row) {
        $role = $row['role_name'];
        if (!isset($grouped[$role])) $grouped[$role] = [];
        if ($row['module']) {
            $grouped[$role][] = $row['module'] . '.' . $row['action'];
        }
    }
    
    // Get Users and their roles
    $sql2 = "SELECT u.first_name, u.last_name, r.name as role_name 
             FROM users u 
             LEFT JOIN roles r ON u.role_id = r.id 
             ORDER BY r.name, u.first_name";
    $stmt2 = $db->query($sql2);
    $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'roles' => $grouped,
        'users' => $users
    ], JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
