<?php
require 'vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
require 'src/Core/Database.php';
$db = App\Core\Database::getInstance();
$stmt = $db->query("SELECT p.module, p.action FROM permissions p JOIN role_permissions rp ON p.id = rp.permission_id JOIN roles r ON rp.role_id = r.id WHERE r.slug = 'super-admin'");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
