<?php
// public/index.php

// Configurar sessão com segurança
require_once __DIR__ . '/../app/Config/SessionConfig.php';
SessionConfig::configure();

// Carregar dependências
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Core/Router.php';

// Inicializar router e carregar rotas
$router = new Router();
require_once __DIR__ . '/../routes/web.php';

// Enviar requisição ao router
$router->dispatch($_SERVER['REQUEST_URI']);