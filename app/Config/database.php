<?php
// app/Config/database.php

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            try {
                // Tenta diferentes portas e configurações
                $configs = [
                    ['host' => '127.0.0.1', 'port' => '3306'],
                    ['host' => '127.0.0.1', 'port' => '3307'],
                    ['host' => 'localhost', 'port' => '3306'],
                    ['host' => 'localhost', 'port' => '3307'],
                ];
                
                $lastError = null;
                
                foreach ($configs as $config) {
                    try {
                        $host = $config['host'];
                        $port = $config['port'];
                        $dbname = 'Aptus';
                        $user = 'root';
                        $pass = '';

                        self::$connection = new PDO(
                            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                            $user,
                            $pass,
                            [
                                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                PDO::ATTR_EMULATE_PREPARES => false
                            ]
                        );
                        
                        // Se chegou aqui, conectou com sucesso
                        error_log("Conectado ao MySQL em {$host}:{$port}");
                        return self::$connection;
                        
                    } catch (PDOException $e) {
                        $lastError = $e->getMessage();
                        continue;
                    }
                }
                
                // Se nenhuma configuração funcionou
                throw new Exception("Não foi possível conectar ao MySQL. Último erro: " . $lastError);
                
            } catch (Exception $e) {
                die("Erro de conexão: " . $e->getMessage() . 
                    "<br><br>Verifique se o MySQL está rodando:<br>" .
                    "1. No XAMPP, clique em 'Start' no MySQL<br>" .
                    "2. Ou execute: net start MySQL<br>" .
                    "3. Verifique se a porta é 3306 ou 3307");
            }
        }

        return self::$connection;
    }
}