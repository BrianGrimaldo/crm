<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
require 'src/Core/Database.php';
$db = App\Core\Database::getInstance();
try {
    $stmt = $db->query('SELECT id, tenant_id, name FROM roles ORDER BY name ASC');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
