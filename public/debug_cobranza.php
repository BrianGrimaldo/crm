<?php
/**
 * Script temporal de debug para portal de cobranza.
 * ELIMINAR EN PRODUCCIÓN.
 */
session_start();

echo "<h2>Debug Portal de Cobranza</h2>";
echo "<pre>";

echo "=== SESSION DATA ===\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'N/A') . "\n";
echo "tenant_id: " . ($_SESSION['tenant_id'] ?? 'N/A') . "\n";
echo "user_role (raw): '" . ($_SESSION['user_role'] ?? 'N/A') . "'\n";
echo "is_superadmin: " . ($_SESSION['is_superadmin'] ?? 'N/A') . "\n";
echo "permissions: " . json_encode($_SESSION['permissions'] ?? []) . "\n";

$roleStr = strtolower(str_replace('-', '', $_SESSION['user_role'] ?? ''));
echo "\n=== ROLE CHECKS ===\n";
echo "roleStr (normalized): '{$roleStr}'\n";
echo "strpos 'cobranza': " . var_export(strpos($roleStr, 'cobranza'), true) . "\n";
echo "in_array exact match: " . var_export(in_array($roleStr, ['cobranza', 'collections', 'cobrador']), true) . "\n";

$isCobranza = strpos($roleStr, 'cobranza') !== false 
           || strpos($roleStr, 'collection') !== false 
           || strpos($roleStr, 'cobrador') !== false;
echo "isCobranza: " . var_export($isCobranza, true) . "\n";

$crossTenant = false;
if ($isCobranza || $roleStr === 'superadmin') {
    $crossTenant = true;
}
echo "crossTenant: " . var_export($crossTenant, true) . "\n";

// Test DB query
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = new PDO(
    "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'] . ";charset=" . $_ENV['DB_CHARSET'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

$sql = "SELECT i.id, i.tenant_id, t.name AS tenant_name, i.invoice_number, i.status 
        FROM invoices i 
        LEFT JOIN tenants t ON t.id = i.tenant_id 
        WHERE 1=1";

if (!$crossTenant) {
    $sql .= " AND i.tenant_id = " . intval($_SESSION['tenant_id'] ?? 0);
}

$sql .= " ORDER BY i.updated_at DESC";

echo "\n=== SQL QUERY ===\n";
echo $sql . "\n";

$stmt = $db->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_OBJ);

echo "\n=== RESULTS (" . count($results) . " rows) ===\n";
foreach ($results as $r) {
    echo "ID:{$r->id} | Tenant:{$r->tenant_id} ({$r->tenant_name}) | #{$r->invoice_number} | {$r->status}\n";
}

echo "</pre>";
