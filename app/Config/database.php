<?php
// app/Config/database.php

require_once __DIR__ . '/config.php';

class Database
{
    private static $connection = null;
    private static $connectionAttempts = 0;
    private static $maxAttempts = 1; // Apenas 1 tentativa com a porta correta

    /**
     * Obtém a conexão com o banco de dados
     * 
     * @return PDO
     * @throws Exception
     */
    public static function getConnection()
    {
        if (self::$connection === null) {
            try {
                // Carregar configurações do .env
                $host = Config::get('DB_HOST') ?: '127.0.0.1';
                $port = Config::get('DB_PORT') ?: '3306';
                $dbname = Config::get('DB_NAME') ?: 'Aptus';
                $user = Config::get('DB_USER') ?: 'root';
                $pass = Config::get('DB_PASS') ?: '';

                // Validar configurações
                if (empty($dbname)) {
                    throw new Exception('Nome do banco de dados não configurado no .env');
                }

                // Log da tentativa de conexão (apenas em desenvolvimento)
                if (Config::get('APP_ENV') !== 'production') {
                    error_log("Tentando conectar ao MySQL em {$host}:{$port}");
                }

                // Criar conexão PDO com configurações otimizadas
                self::$connection = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::ATTR_PERSISTENT => true, // Conexão persistente para performance
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                        PDO::ATTR_TIMEOUT => 5, // Timeout de 5 segundos
                    ]
                );

                // Log de sucesso
                if (Config::get('APP_ENV') !== 'production') {
                    error_log("✅ Conectado ao MySQL em {$host}:{$port} - Banco: {$dbname}");
                }

                return self::$connection;

            } catch (PDOException $e) {
                // Log do erro
                error_log("❌ Erro de conexão MySQL: " . $e->getMessage());
                
                // Tentar reconectar com fallback (apenas 1 vez)
                if (self::$connectionAttempts < self::$maxAttempts) {
                    self::$connectionAttempts++;
                    error_log("🔄 Tentando reconectar... (Tentativa " . self::$connectionAttempts . ")");
                    
                    // Aguardar 1 segundo antes de tentar novamente
                    sleep(1);
                    
                    // Limpar conexão anterior
                    self::$connection = null;
                    
                    // Recursão para tentar novamente
                    return self::getConnection();
                }

                // Se falhou todas as tentativas, exibir erro amigável
                throw new Exception(
                    "Erro de conexão com o banco de dados: " . $e->getMessage() . 
                    "\n\n🔧 Verifique se:\n" .
                    "1. O MySQL está rodando (XAMPP/WAMP/MAMP)\n" .
                    "2. As configurações no arquivo .env estão corretas:\n" .
                    "   - DB_HOST={$host}\n" .
                    "   - DB_PORT={$port}\n" .
                    "   - DB_NAME={$dbname}\n" .
                    "   - DB_USER={$user}\n" .
                    "3. O banco de dados '{$dbname}' existe"
                );
            }
        }

        return self::$connection;
    }

    /**
     * Verifica se a conexão está ativa
     * 
     * @return bool
     */
    public static function isConnected()
    {
        try {
            if (self::$connection === null) {
                return false;
            }
            
            // Testa a conexão com um ping
            self::$connection->query('SELECT 1');
            return true;
            
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fecha a conexão com o banco de dados
     */
    public static function disconnect()
    {
        self::$connection = null;
    }

    /**
     * Obtém o último ID inserido
     * 
     * @return string
     */
    public static function lastInsertId()
    {
        if (self::$connection === null) {
            throw new Exception('Conexão não estabelecida');
        }
        
        return self::$connection->lastInsertId();
    }

    /**
     * Inicia uma transação
     * 
     * @return bool
     */
    public static function beginTransaction()
    {
        if (self::$connection === null) {
            throw new Exception('Conexão não estabelecida');
        }
        
        return self::$connection->beginTransaction();
    }

    /**
     * Commit da transação
     * 
     * @return bool
     */
    public static function commit()
    {
        if (self::$connection === null) {
            throw new Exception('Conexão não estabelecida');
        }
        
        return self::$connection->commit();
    }

    /**
     * Rollback da transação
     * 
     * @return bool
     */
    public static function rollBack()
    {
        if (self::$connection === null) {
            throw new Exception('Conexão não estabelecida');
        }
        
        return self::$connection->rollBack();
    }

    /**
     * Verifica se está em uma transação
     * 
     * @return bool
     */
    public static function inTransaction()
    {
        if (self::$connection === null) {
            return false;
        }
        
        return self::$connection->inTransaction();
    }

    /**
     * Prepara e executa uma query com bind de parâmetros
     * 
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public static function query($sql, $params = [])
    {
        if (self::$connection === null) {
            throw new Exception('Conexão não estabelecida');
        }
        
        $stmt = self::$connection->prepare($sql);
        
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $type = PDO::PARAM_STR;
                
                if (is_int($value)) {
                    $type = PDO::PARAM_INT;
                } elseif (is_bool($value)) {
                    $type = PDO::PARAM_BOOL;
                } elseif (is_null($value)) {
                    $type = PDO::PARAM_NULL;
                }
                
                $stmt->bindValue($key, $value, $type);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * Executa uma query e retorna todos os resultados
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function fetchAll($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Executa uma query e retorna um único resultado
     * 
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public static function fetchOne($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Executa uma query e retorna uma única coluna
     * 
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public static function fetchColumn($sql, $params = [])
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn();
    }

    /**
     * Obtém estatísticas da conexão
     * 
     * @return array
     */
    public static function getStats()
    {
        if (self::$connection === null) {
            return ['connected' => false];
        }

        try {
            $stats = [
                'connected' => true,
                'server_info' => self::$connection->getAttribute(PDO::ATTR_SERVER_VERSION),
                'client_info' => self::$connection->getAttribute(PDO::ATTR_CLIENT_VERSION),
                'connection_status' => self::$connection->getAttribute(PDO::ATTR_CONNECTION_STATUS),
            ];
            
            return $stats;
        } catch (PDOException $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }
}