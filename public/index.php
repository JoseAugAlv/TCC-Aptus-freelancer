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

// 5) Auto-login via remember_token
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

// 6) [NOVO] Verificação de Modo Manutenção
try {
    require_once __DIR__ . '/../app/Models/Configuracao.php';
    $configModel     = new Configuracao();
    $manutencaoAtiva = $configModel->get('manutencao') === '1';

    if ($manutencaoAtiva) {
        $role      = (int) ($_SESSION['usuario']['role'] ?? 0);
        $isAdmin   = in_array($role, [1, 4], true);
        $rotaAtual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        // Rotas liberadas mesmo em manutenção
        $rotasLiberadas = [
            '/Aptus/login',
            '/Aptus/logout',
            '/Aptus/auth/verificar',
            '/Aptus/admin/configuracoes',
        ];

        $liberado = false;
        foreach ($rotasLiberadas as $r) {
            if (strpos($rotaAtual, $r) === 0) { $liberado = true; break; }
        }

        if (!$isAdmin && !$liberado) {
            $mensagem = $configModel->get('manutencao_mensagem')
                ?: 'Sistema em manutenção. Volte em breve.';

            http_response_code(503);
            header('Retry-After: 3600');
            header('Content-Type: text/html; charset=utf-8');

            echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">';
            echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
            echo '<title>Em manutenção - Aptus</title>';
            echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
            echo '<style>
                body { font-family: "Segoe UI", Arial, sans-serif; background: #f8fafc; margin:0;
                       display:flex; align-items:center; justify-content:center; min-height:100vh; }
                .box { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:48px 40px;
                       max-width:520px; text-align:center; box-shadow:0 8px 32px rgba(0,101,119,.08); }
                .icon { font-size:4rem; color:#006577; margin-bottom:1rem; }
                h1 { color:#006577; margin:0 0 12px; font-size:1.6rem; }
                p { color:#475569; line-height:1.6; margin:0; }
                a { display:inline-block; margin-top:24px; padding:10px 24px; background:#006577;
                    color:#fff; text-decoration:none; border-radius:8px; font-weight:600; }
            </style></head><body>';
            echo '<div class="box">';
            echo '<div class="icon"><i class="fas fa-tools"></i></div>';
            echo '<h1>Sistema em manutenção</h1>';
            echo '<p>' . htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') . '</p>';
            echo '<a href="/Aptus/login"><i class="fas fa-sign-in-alt"></i> Acesso administrativo</a>';
            echo '</div></body></html>';
            exit;
        }
    }
} catch (Throwable $e) {
    error_log('Erro ao verificar modo manutenção: ' . $e->getMessage());
}

// 7) Despachar
$router = new Router();
require_once __DIR__ . '/../routes/web.php';
$router->dispatch($_SERVER['REQUEST_URI']);