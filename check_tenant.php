<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
require 'src/Core/Database.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT name FROM tenants WHERE id = 1");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
