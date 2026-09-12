<?php
// public/index.php

// 1) Config
require_once __DIR__ . '/../app/Config/config.php';
Config::load();

$appEnv = Config::get('APP_ENV') ?: 'development';

// 2) Bootstrap de erros
if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Handler global
set_exception_handler(function (Throwable $e) use ($appEnv) {
    error_log('Exceção não capturada: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);

    if ($appEnv === 'production') {
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">';
        echo '<title>Erro - Aptus</title></head><body>';
        echo '<h1>Algo deu errado</h1>';
        echo '<p>Ocorreu um erro inesperado. Nossa equipe foi notificada.</p>';
        echo '<p><a href="/Aptus/">Voltar ao início</a></p>';
        echo '</body></html>';
    } else {
        echo '<pre style="padding:20px;background:#fee;color:#900;font-family:monospace;">';
        echo htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8');
        echo '</pre>';
    }
});

// 3) Sessão
require_once __DIR__ . '/../app/Config/SessionConfig.php';
SessionConfig::configure();

// 4) Dependências
require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Core/Router.php';

// 5) Auto-login via remember_token (com path dinâmico)
if (!isset($_SESSION['usuario'])
    && isset($_COOKIE['remember_token'])
    && $_COOKIE['remember_token'] !== '') {

    try {
        $pdo = Database::getConnection();

        $stmt = $pdo->prepare(
            "SELECT id_usuario, nome, email, id_perfil, ativo, banido
             FROM usuario
             WHERE remember_token = ?
             LIMIT 1"
        );
        $stmt->execute([$_COOKIE['remember_token']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && (int) $user['ativo'] === 1 && (int) $user['banido'] === 0) {
            session_regenerate_id(true);

            $_SESSION['usuario'] = [
                'id'    => (int) $user['id_usuario'],
                'nome'  => $user['nome'],
                'email' => $user['email'],
                'role'  => (int) ($user['id_perfil'] ?? 0),
            ];
        } else {
            // Token de usuário banido/inativo: invalida o cookie
            $appUrlPath = parse_url(Config::get('APP_URL', '/Aptus'), PHP_URL_PATH) ?: '/Aptus';
            setcookie('remember_token', '', [
                'expires'  => time() - 3600,
                'path'     => rtrim($appUrlPath, '/') . '/',
                'domain'   => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    } catch (Throwable $e) {
        error_log('Falha no auto-login por remember_token: ' . $e->getMessage());
    }
}

// 6) Despachar
$router = new Router();
require_once __DIR__ . '/../routes/web.php';
$router->dispatch($_SERVER['REQUEST_URI']);