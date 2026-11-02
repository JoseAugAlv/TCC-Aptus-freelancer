<?php
// app/Helpers/LoginAttempt.php

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Configuracao.php';

class LoginAttempt
{
    private static $limiteCache = null;
    private static $limiteCacheTime = 0;

    private static function getLimite(): int
    {
        // Cache interno por 60s (evita ler config toda hora)
        if (self::$limiteCache !== null && (time() - self::$limiteCacheTime) < 60) {
            return self::$limiteCache;
        }

        try {
            $config = new Configuracao();
            $valor  = $config->get('tentativas_login');
            $limite = $valor ? (int) $valor : 5;
            if ($limite < 1) $limite = 5;

            self::$limiteCache     = $limite;
            self::$limiteCacheTime = time();
            return $limite;
        } catch (Throwable $e) {
            return 5;
        }
    }

    private static function getIp(string $ip): string
    {
        return $ip !== '' ? $ip : ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public static function check(string $email, string $ip = ''): bool
    {
        $ip  = self::getIp($ip);
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM login_tentativa
                 WHERE email = ? AND ip = ?
                   AND criado_em > (NOW() - INTERVAL 15 MINUTE)"
            );
            $stmt->execute([$email, $ip]);
            $tentativas = (int) $stmt->fetchColumn();
            return $tentativas < self::getLimite();
        } catch (Throwable $e) {
            error_log('LoginAttempt::check falhou: ' . $e->getMessage());
            return true;
        }
    }

    public static function increment(string $email, string $ip = ''): void
    {
        $ip  = self::getIp($ip);
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO login_tentativa (email, ip, criado_em) VALUES (?, ?, NOW())"
            );
            $stmt->execute([$email, $ip]);
        } catch (Throwable $e) {
            error_log('LoginAttempt::increment falhou: ' . $e->getMessage());
        }
    }

    public static function reset(string $email, string $ip = ''): void
    {
        $ip  = self::getIp($ip);
        $pdo = Database::getConnection();

        try {
            $stmt = $pdo->prepare(
                "DELETE FROM login_tentativa WHERE email = ? AND ip = ?"
            );
            $stmt->execute([$email, $ip]);
        } catch (Throwable $e) {
            error_log('LoginAttempt::reset falhou: ' . $e->getMessage());
        }
    }
}