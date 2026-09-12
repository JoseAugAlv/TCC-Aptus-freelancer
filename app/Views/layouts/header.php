<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'Aptus - Conectando Talentos', ENT_QUOTES, 'UTF-8') ?></title>

    <?php
    // [FIX-CRIT-2.3] CSRF token exposto via meta para os scripts AJAX lerem.
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

    <!-- CSS Específico da Página -->
    <?php if (isset($cssPagina)): ?>
        <link rel="stylesheet" href="/Aptus/public/css/<?= htmlspecialchars($cssPagina, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="/Aptus/public/css/cookies.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php require '../app/Views/layouts/lgpd_modal.php'; ?>

<div class="cookie-banner" id="cookieBanner" role="dialog" aria-label="Consentimento de cookies" aria-modal="true">
  <p>Utilizamos cookies e tecnologias semelhantes para melhorar sua experiência, garantir segurança e cumprir a LGPD. Você pode aceitar, recusar ou consultar <a href="/Aptus/cookies">nossa política de cookies</a>.</p>
  <button class="btn-cookie" onclick="aceitarCookies()" aria-label="Aceitar cookies">Aceitar</button>
</div>
<script>
function aceitarCookies(){
  document.getElementById('cookieBanner').classList.remove('show');
  document.cookie='aptus_cookie_consent=1;path=/;max-age=2592000;SameSite=Lax';
  localStorage.setItem('aptus_cookie_consent','1');
}
if(localStorage.getItem('aptus_cookie_consent')==='1' || document.cookie.includes('aptus_cookie_consent=1')){
  document.getElementById('cookieBanner').classList.remove('show');
} else {
  document.getElementById('cookieBanner').classList.add('show');
}
</script>