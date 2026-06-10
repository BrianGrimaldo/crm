<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/**
 * Fábrica de conexiones PDO (Singleton por request).
 *
 * Uso:
 *   $pdo = Database::getInstance();
 */
final class Database
{
    private static ?PDO $instance = null;

    /** No instanciable */
    private function __construct() {}
    private function __clone() {}

    /**
     * Obtiene (o crea) la conexión PDO singleton.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/database.php';

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $cfg['host'],
                $cfg['port'],
                $cfg['database'],
                $cfg['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $cfg['username'],
                    $cfg['password'],
                    $cfg['options']
                );
            } catch (\PDOException $e) {
                throw new RuntimeException(
                    'Database connection failed: ' . $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        return self::$instance;
    }

    /**
     * Destruye la conexión (útil en tests).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
