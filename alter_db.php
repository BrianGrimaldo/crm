<?php
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

require_once __DIR__ . '/src/Core/Database.php';

try {
    $db = \App\Core\Database::getInstance();
    $sql = "ALTER TABLE invoices ADD COLUMN pdf_path VARCHAR(255) NULL AFTER source;";
    $db->exec($sql);
    echo "Column pdf_path added successfully!\n";
} catch (\PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
