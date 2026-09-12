<?php
// app/Config/SessionConfig.php

require_once __DIR__ . '/config.php';

class SessionConfig
{
    public static function configure()
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        // 1) Diretório de sessões
        $sessionPath = __DIR__ . '/../../tmp';
        if (!is_dir($sessionPath)) {
            @mkdir($sessionPath, 0750, true);
        }
        if (is_dir($sessionPath) && is_writable($sessionPath)) {
            session_save_path($sessionPath);
        }

        // 2) Ambiente / HTTPS
        $appEnv = Config::get('APP_ENV', 'development');
        $isProduction = ($appEnv === 'production');
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

        // 3) Cookie path derivado do APP_URL (não fixo em /Aptus)
        $appUrl = Config::get('APP_URL', '/Aptus');
        $cookiePath = parse_url($appUrl, PHP_URL_PATH);
        if (!$cookiePath) {
            $cookiePath = '/';
        }
        $cookiePath = rtrim($cookiePath, '/') . '/';
        if ($cookiePath === '//') {
            $cookiePath = '/';
        }

        // 4) Domain dinâmico (remove porta se houver)
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $cookieDomain = preg_replace('/:\d+$/', '', $host);

        session_set_cookie_params([
            'lifetime' => 3600,
            'path'     => $cookiePath,
            'domain'   => $cookieDomain,
            'secure'   => $isProduction && $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_name('APTUS_SESSION');
        session_start();

        // 5) Regeneração periódica
        if (!isset($_SESSION['last_regenerate'])) {
            $_SESSION['last_regenerate'] = time();
        } elseif (time() - $_SESSION['last_regenerate'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regenerate'] = time();
        }
    }
}