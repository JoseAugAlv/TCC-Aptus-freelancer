<?php

class Config
{
    private static $config = [];

    public static function load()
    {
        self::$config = parse_ini_file(__DIR__ . '/../../.env');
    }

    public static function get($key)
    {
        // Se o config ainda não foi carregado, carrega
        if (empty(self::$config)) {
            self::load();
        }
        
        return self::$config[$key] ?? null;
    }
}