<?php
/**
 * Run the SMTP migration for user credentials.
 */
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

    $pdo->exec("ALTER TABLE `users`
        ADD COLUMN `smtp_host`       VARCHAR(255)  DEFAULT NULL COMMENT 'Servidor SMTP del usuario' AFTER `avatar_url`,
        ADD COLUMN `smtp_port`       SMALLINT      DEFAULT 587  COMMENT 'Puerto SMTP' AFTER `smtp_host`,
        ADD COLUMN `smtp_email`      VARCHAR(255)  DEFAULT NULL COMMENT 'Email SMTP para envio' AFTER `smtp_port`,
        ADD COLUMN `smtp_password`   VARCHAR(500)  DEFAULT NULL COMMENT 'App Password SMTP' AFTER `smtp_email`,
        ADD COLUMN `smtp_encryption` VARCHAR(10)   DEFAULT 'tls' COMMENT 'tls, ssl o none' AFTER `smtp_password`,
        ADD COLUMN `smtp_from_name`  VARCHAR(200)  DEFAULT NULL COMMENT 'Nombre mostrado al enviar' AFTER `smtp_encryption`
    ");
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "Columns already exist, skipping.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
