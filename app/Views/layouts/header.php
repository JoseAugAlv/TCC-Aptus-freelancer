<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'Aptus - Conectando Talentos', ENT_QUOTES, 'UTF-8') ?></title>

    <?php
    // CSRF token exposto via meta para os scripts AJAX lerem.
    require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
    ?>
    <meta name="csrf-token" content="<?= htmlspecialchars(CsrfMiddleware::generateToken(), ENT_QUOTES, 'UTF-8') ?>">

    <link rel="shortcut icon" href="/Aptus/public/images/logo.ico" type="image/x-icon">

    <!-- CSS Base (variáveis primeiro) -->
    <link rel="stylesheet" href="/Aptus/public/css/variables.css">
    <link rel="stylesheet" href="/Aptus/public/css/style.css">
    <link rel="stylesheet" href="/Aptus/public/css/header.css">
    <link rel="stylesheet" href="/Aptus/public/css/nav.css">
    <link rel="stylesheet" href="/Aptus/public/css/footer.css">
    <link rel="stylesheet" href="/Aptus/public/css/responsive.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- CSS Específico da Página -->
    <?php if (isset($cssPagina)): ?>
        <link rel="stylesheet" href="/Aptus/public/css/<?= htmlspecialchars($cssPagina, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/Aptus/public/css/cookies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php
// Modal LGPD (só aparece em páginas de autenticação)
require_once __DIR__ . '/lgpd_modal.php';

// Banner de cookies fixo na parte inferior
require_once __DIR__ . '/cookie_banner.php';
?>