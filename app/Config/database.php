<?php
// app/Config/database.php
require_once __DIR__ . '/config.php';

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $host   = Config::get('DB_HOST') ?: '127.0.0.1';
            $port   = Config::get('DB_PORT') ?: '3306';
            $dbname = Config::get('DB_NAME') ?: 'Aptus';
            $user   = Config::get('DB_USER') ?: 'root';
            $pass   = Config::get('DB_PASS') ?: '';

            if (empty($dbname)) {
                throw new RuntimeException('Configuração de banco incompleta.');
            }

            try {
                self::$connection = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        // [FIX-BAIXA-05] PDO::ATTR_PERSISTENT removido.
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                        PDO::ATTR_TIMEOUT            => 5,
                    ]
                );

                if (Config::get('APP_ENV') !== 'production') {
                    error_log("DB conectado em {$host}:{$port} - {$dbname}");
                }

            } catch (PDOException $e) {
                // [FIX-MED-08] Detalhe técnico só vai para o log
                error_log('Falha de conexão MySQL: ' . $e->getMessage());

                // [FIX-MED-08] Usuário recebe mensagem genérica, sem host/db
                throw new RuntimeException(
                    'Serviço temporariamente indisponível. Tente novamente em instantes.',
                    0,
                    $e
                );
            }
        }

        return self::$connection;
    }

    public static function isConnected()
    {
        try {
            if (self::$connection === null) return false;
            self::$connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function disconnect()
    {
        self::$connection = null;
    }

    public static function lastInsertId()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->lastInsertId();
    }

    public static function beginTransaction()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->beginTransaction();
    }

    public static function commit()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->commit();
    }

    public static function rollBack()
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        return self::$connection->rollBack();
    }

    public static function inTransaction()
    {
        if (self::$connection === null) return false;
        return self::$connection->inTransaction();
    }

    public static function query($sql, $params = [])
    {
        if (self::$connection === null) {
            throw new RuntimeException('Conexão não estabelecida');
        }
        $stmt = self::$connection->prepare($sql);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value))       $type = PDO::PARAM_INT;
                elseif (is_bool($value))  $type = PDO::PARAM_BOOL;
                elseif (is_null($value))  $type = PDO::PARAM_NULL;
                $stmt->bindValue($key, $value, $type);
            }
        }
        $stmt->execute();
        return $stmt;
    }

    public static function fetchAll($sql, $params = [])
    {
        return self::query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fetchOne($sql, $params = [])
    {
        return self::query($sql, $params)->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function fetchColumn($sql, $params = [])
    {
        return self::query($sql, $params)->fetchColumn();
    }

    public static function getStats()
    {
        if (self::$connection === null) {
            return ['connected' => false];
        }
        try {
            return [
                'connected'         => true,
                'server_info'       => self::$connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_info'       => self::$connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => self::$connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            ];
        } catch (PDOException $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }
}