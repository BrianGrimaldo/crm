<?php
$envPath = __DIR__ . '/../.env';
$envVariables = @parse_ini_file($envPath);
$host = $envVariables['DB_HOST'] ?? '127.0.0.1';
$port = $envVariables['DB_PORT'] ?? '3306';
$user = $envVariables['DB_USERNAME'] ?? 'root';
$pass = $envVariables['DB_PASSWORD'] ?? '';
$dbName = $envVariables['DB_DATABASE'] ?? 'crm_einsurglobal';
$charset = $envVariables['DB_CHARSET'] ?? 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("ALTER TABLE `users` ADD COLUMN `email_signature` TEXT DEFAULT NULL COMMENT 'Firma de correo electrónico' AFTER `smtp_from_name`");
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "Column already exists, skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
