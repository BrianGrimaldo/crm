<?php

/**
 * Script para instalar la base de datos y ejecutar las migraciones iniciales.
 * Ejecutar desde la línea de comandos: php scripts/install_db.php
 */

require __DIR__ . '/../vendor/autoload.php';

// Parsear el archivo .env manualmente para no depender de librerías en este script simple
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("Error: No se encontro el archivo .env\n");
}

$envVariables = @parse_ini_file($envPath);
$host = $envVariables['DB_HOST'] ?? '127.0.0.1';
$port = $envVariables['DB_PORT'] ?? '3306';
$user = $envVariables['DB_USERNAME'] ?? 'root';
$pass = $envVariables['DB_PASSWORD'] ?? '';
$dbName = $envVariables['DB_DATABASE'] ?? 'crm_einsurglobal';
$charset = $envVariables['DB_CHARSET'] ?? 'utf8mb4';

echo "Conectando a MySQL en $host:$port...\n";

try {
    // Conectar sin seleccionar base de datos para poder crearla
    $pdo = new PDO("mysql:host=$host;port=$port;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Crear la base de datos si no existe
    echo "Asegurando que la base de datos '$dbName' exista...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET $charset COLLATE {$charset}_unicode_ci");
    
    // Seleccionar la base de datos
    $pdo->exec("USE `$dbName`");

    // Eliminar todas las tablas existentes para una instalación limpia
    echo "Limpiando tablas existentes...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $pdo->exec("DROP TABLE IF EXISTS `" . $row[0] . "`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Buscar archivos SQL en la carpeta database
    $sqlFiles = glob(__DIR__ . '/../database/*.sql');
    sort($sqlFiles); // Asegurar el orden (001, 002, etc)

    if (empty($sqlFiles)) {
        die("No se encontraron archivos SQL en la carpeta database/\n");
    }

    foreach ($sqlFiles as $file) {
        $filename = basename($file);
        echo "Ejecutando script: $filename...\n";
        
        $sql = file_get_contents($file);
        
        // Ejecutar el script SQL
        // Como los scripts pueden tener múltiples sentencias, PDO::exec() es ideal.
        $pdo->exec($sql);
        
        echo "  -> OK\n";
    }

    echo "\n¡Base de datos instalada y poblada con éxito!\n";

} catch (PDOException $e) {
    die("\nError de base de datos: " . $e->getMessage() . "\n");
}
