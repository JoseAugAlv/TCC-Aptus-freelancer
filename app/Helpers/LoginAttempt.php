<?php
require_once __DIR__ . '/../Models/Configuracao.php';
class LoginAttempt {
    public static function check(string $email, string $ip = ''): bool {
        if ($ip === '') $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = Database::getConnection();
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_tentativa WHERE (email = ? OR ip = ?) AND criado_em > (NOW() - INTERVAL 15 MINUTE)");
            $stmt->execute([$email, $ip]);
            return (int)$stmt->fetchColumn() < 5;
        } catch (Exception $e) {
            return true;
        }
    }
    public static function increment(string $email, string $ip = ''): void {
        if ($ip === '') $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = Database::getConnection();
        try {
            $pdo->prepare("INSERT INTO login_tentativa (email, ip, criado_em) VALUES (?, ?, NOW())")->execute([$email, $ip]);
        } catch (Exception $e) {}
    }
    public static function reset(string $email, string $ip = ''): void {
        if ($ip === '') $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $pdo = Database::getConnection();
        try {
            $pdo->prepare("DELETE FROM login_tentativa WHERE email = ? OR ip = ?")->execute([$email, $ip]);
        } catch (Exception $e) {}
    }
}
