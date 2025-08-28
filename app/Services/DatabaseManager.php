<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class DatabaseManager
{
    /**
     * Test database connection
     */
    public function testConnection(array $config): bool
    {
        try {
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']}",
                $config['username'],
                $config['password'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );

            // Test a simple query
            $pdo->query('SELECT 1');
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create database connection configuration
     */
    public function createConnection(string $name, array $config): void
    {
        Config::set("database.connections.{$name}", [
            'driver' => 'mysql',
            'host' => $config['host'],
            'port' => $config['port'] ?? 3306,
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);

        // Clear any cached connections
        DB::purge($name);
    }

    /**
     * Update main database configuration file
     */
    public function updateDatabaseConfig(string $connectionName, array $config): void
    {
        $configFile = config_path('database.php');
        $configContent = file_get_contents($configFile);

        $connectionConfig = [
            'driver' => 'mysql',
            'host' => $config['host'],
            'port' => $config['port'] ?? 3306,
            'database' => $config['database'],
            'username' => $config['username'],
            'password' => $config['password'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        $connectionString = var_export($connectionConfig, true);
        $newConnection = "'{$connectionName}' => {$connectionString},";

        // Check if connection already exists
        if (strpos($configContent, "'{$connectionName}' =>") === false) {
            // Add new connection after the mysql connection
            $pattern = "/('mysql' => \[[\s\S]*?\],)/";
            $replacement = "$1\n\n        {$newConnection}";
            $configContent = preg_replace($pattern, $replacement, $configContent);
            
            file_put_contents($configFile, $configContent);
        }
    }

    /**
     * Check if database exists
     */
    public function databaseExists(array $config): bool
    {
        try {
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']}",
                $config['username'],
                $config['password']
            );

            $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
            $stmt->execute([$config['database']]);

            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create database if it doesn't exist
     */
    public function createDatabase(array $config): bool
    {
        try {
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']}",
                $config['username'],
                $config['password']
            );

            $databaseName = $config['database'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}