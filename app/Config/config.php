<?php
// app/Config/config.php

class Config
{
    private static $config = [];
    private static $loaded = false;

    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            error_log("Config: arquivo .env não encontrado em: " . $envFile);
            self::$loaded = true;
            return;
        }

        // INI_SCANNER_RAW evita que # e " quebrem os valores (ex.: senhas de app)
        $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);

        if ($parsed === false) {
            error_log("Config: erro ao parsear o arquivo .env");
            self::$config = [];
        } else {
            self::$config = $parsed;
        }

        foreach (self::$config as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$config[$key] ?? $default;
    }

    public static function set($key, $value)
    {
        if (!self::$loaded) {
            self::load();
        }
        self::$config[$key] = $value;
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }

    public static function getAll()
    {
        if (!self::$loaded) {
            self::load();
        }
        return self::$config;
    }

    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }
        return isset(self::$config[$key]);
    }
}