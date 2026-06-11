<?php
require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'src/Core/Database.php';
$db = \App\Core\Database::getInstance();

try {
    $stmt = $db->query("SELECT * FROM role_permissions LIMIT 5");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}

echo "\nPermisos del usuario en sesion actual?\n";
session_start();
print_r($_SESSION['permissions'] ?? 'no hay permisos');
