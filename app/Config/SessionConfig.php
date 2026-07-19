<?php
// app/Config/SessionConfig.php

class SessionConfig
{
    public static function configure()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // Definir caminho alternativo para sessões
        $sessionPath = __DIR__ . '/../../tmp';
        
        // Criar diretório se não existir
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0777, true);
        }
        
        // Verificar se o diretório tem permissão de escrita
        if (is_writable($sessionPath)) {
            session_save_path($sessionPath);
        }

        $isProduction = isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production';
        $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        session_set_cookie_params([
            'lifetime' => 3600,
            'path' => '/Aptus',
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'secure' => $isProduction && $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_name('APTUS_SESSION');
        
        // Iniciar sessão com supressão de erros
        @session_start();

        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = time();
        } elseif (time() - $_SESSION['last_regenerate'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        }
    }
}