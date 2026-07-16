<?php
// app/Middleware/CsrfMiddleware.php

class CsrfMiddleware
{
    /**
     * Gera token CSRF único por sessão
     */
    public static function generateToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Retorna o campo HTML pronto para colar em formulários
     */
    public static function field()
    {
        $token = self::generateToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Valida token CSRF da requisição
     */
    public static function validate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $tokenSessao = $_SESSION['csrf_token'] ?? null;
        $tokenRequisicao = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

        if (!$tokenSessao || !$tokenRequisicao || $tokenSessao !== $tokenRequisicao) {
            // Log de tentativa de CSRF
            error_log("CSRF inválido - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            // Retorna erro em JSON ou redireciona
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                http_response_code(403);
                echo json_encode(['erro' => 'Token CSRF inválido']);
                exit;
            }
            
            $_SESSION['flash'] = [
                'tipo' => 'erro',
                'mensagem' => 'Token de segurança inválido. Tente novamente.'
            ];
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/Aptus/login'));
            exit;
        }
    }
}