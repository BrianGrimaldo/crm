<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

session_start();
$_SESSION['user_id'] = 2; // Simular vendedor
$_SESSION['user_role'] = 'vendedor';
$_SESSION['tenant_id'] = 1;
$_SESSION['permissions'] = [];

// Forzar el request method y body
$_SERVER['REQUEST_METHOD'] = 'POST';
$payload = json_encode(['deal_id' => 1, 'stage_id' => 2]);
session_write_close(); // Prevent session deadlock

$ch = curl_init('http://localhost/crm_einsurglobal/public/api/oportunidades/move-stage');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Cookie: ' . session_name() . '=' . session_id()
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP CODE: $httpCode\n";
echo "CURL ERROR: $error\n";
echo "RESPONSE:\n---\n$response\n---\n";
