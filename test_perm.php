<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['user_role'] = 'vendedor';
$_SESSION['tenant_id'] = 1;
$_SESSION['permissions'] = ['deals.view'];

// Simulate AJAX request
ob_start();
$controller = new \App\Http\Controllers\DealController();
$controller->moveStage();
$output = ob_get_clean();

echo "OUTPUT FROM SERVER:\n";
echo ">>>\n" . $output . "\n<<<\n";
