<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
require 'src/Core/Database.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT id, name, slug FROM roles");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
