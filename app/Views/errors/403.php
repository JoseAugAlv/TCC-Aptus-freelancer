<?php
// app/Views/errors/403.php — reaproveita o layout da página de erro 404

$erroCodigo   = '403';
$erroTitulo   = 'Acesso negado';
$erroMensagem = 'Você não tem permissão para acessar esta página.';
$erroDica     = 'Se acha que isso é um erro, fale com um administrador.';
$erroIcone    = 'fas fa-lock';

require __DIR__ . '/404.php';
