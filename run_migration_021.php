<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$db   = $_ENV['DB_DATABASE'] ?? '';
$user = $_ENV['DB_USERNAME'] ?? '';
$pass = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB Connected OK\n";

    $sql = file_get_contents(__DIR__ . '/database/021_quotes_enhancements.sql');
    // Split by semicolons but skip empty
    $stmts = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($stmts as $s) {
        if (strlen($s) < 5) continue;
        try {
            $pdo->exec($s);
            echo "OK: " . substr(preg_replace('/\s+/', ' ', $s), 0, 70) . "...\n";
        } catch (PDOException $e) {
            echo "SKIP/ERR: " . $e->getMessage() . "\n";
        }
    }
    echo "\nMigration 021 complete.\n";
} catch (PDOException $e) {
    echo "CONNECTION ERROR: " . $e->getMessage() . "\n";
}
