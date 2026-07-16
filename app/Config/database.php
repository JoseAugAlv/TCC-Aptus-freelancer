<?php
// app/Config/database.php

class AptusDatabase
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            try {
                // Carregar variáveis do .env se não estiverem carregadas
                if (empty($_ENV['DB_HOST'])) {
                    $envFile = __DIR__ . '/../../.env';
                    if (file_exists($envFile)) {
                        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($lines as $line) {
                            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                                list($key, $value) = explode('=', $line, 2);
                                $_ENV[trim($key)] = trim($value);
                            }
                        }
                    }
                }

                $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
                $port = '3306';
                
                // Extrair porta se estiver no host
                if (strpos($host, ':') !== false) {
                    $parts = explode(':', $host);
                    $host = $parts[0];
                    $port = $parts[1];
                }
                
                $dbname = $_ENV['DB_NAME'] ?? 'Aptus';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';

                self::$connection = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
                
            } catch (PDOException $e) {
                error_log("Erro de conexão com o banco: " . $e->getMessage());
                throw new Exception("Erro de conexão com o banco de dados");
            }
        }

        return self::$connection;
    }
}