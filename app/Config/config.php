<?php
// app/Config/config.php

class Config
{
    private static $config = [];
    private static $loaded = false;

    /**
     * Carrega as configurações do arquivo .env
     */
    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../../.env';
        
        if (!file_exists($envFile)) {
            error_log("⚠️ Arquivo .env não encontrado em: " . $envFile);
            self::$loaded = true;
            return;
        }

        self::$config = parse_ini_file($envFile);
        
        if (self::$config === false) {
            error_log("⚠️ Erro ao parsear o arquivo .env");
            self::$config = [];
        }

        // Setar variáveis de ambiente
        foreach (self::$config as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    /**
     * Obtém uma configuração pelo nome
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config[$key] ?? $default;
    }

    /**
     * Define uma configuração em tempo de execução
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        if (!self::$loaded) {
            self::load();
        }

        self::$config[$key] = $value;
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }

    /**
     * Obtém todas as configurações
     * 
     * @return array
     */
    public static function getAll()
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config;
    }

    /**
     * Verifica se uma configuração existe
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$config[$key]);
    }
}