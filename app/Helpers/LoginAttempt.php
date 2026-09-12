<?php
// app/Helpers/LoginAttempt.php

require_once __DIR__ . '/../Config/database.php';
require_once __DIR__ . '/../Models/Configuracao.php';

class LoginAttempt
{
    private static function getLimite(): int
    {
        try {
            $config = new Configuracao();
            $valor  = $config->get('tentativas_login');
            return $valor ? (int) $valor : 5;
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
            // Fail-open: se a tabela não existe, não trava o usuário legítimo
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