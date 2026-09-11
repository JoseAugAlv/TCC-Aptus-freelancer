<?php
// public/index.php

// Configurar sessão com segurança
require_once __DIR__ . '/../app/Config/SessionConfig.php';
SessionConfig::configure();

// Carregar dependências
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Config/database.php';
ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../app/Core/Router.php';

// Inicializar router e carregar rotas
$router = new Router();
require_once __DIR__ . '/../routes/web.php';

// Enviar requisição ao router
// Remember-me: antes de dispatch, se nao logado, tentar cookie
if (!isset($_SESSION['usuario']) && isset($_COOKIE['remember_token'])) {
    require_once __DIR__ . '/../app/Config/database.php';
ini_set("display_errors", 1);
error_reporting(E_ALL);
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id_usuario, nome, email, id_perfil FROM usuario WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$_COOKIE['remember_token']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['usuario'] = ['id' => $user['id_usuario'], 'nome' => $user['nome'], 'email' => $user['email'], 'role' => $user['id_perfil'] ?? 0];
    }
}
$router->dispatch($_SERVER['REQUEST_URI']);
