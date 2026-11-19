<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($tituloPagina ?? 'Aptus - Conectando Talentos', ENT_QUOTES, 'UTF-8') ?></title>

    <script>
        // Acessibilidade: aplica tema, modo de daltonismo e tamanho da fonte
        // salvos ANTES de renderizar, para a página não "piscar" no tema errado.
        // O painel e as mudanças ficam em public/js/acessibilidade.js.
        (function () {
            var p = {};
            try { p = JSON.parse(localStorage.getItem('aptus_acessibilidade') || '{}') || {}; } catch (e) {}
            var r = document.documentElement;
            var escuro = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            r.setAttribute('data-tema', p.tema === 'escuro' || p.tema === 'claro' ? p.tema : (escuro ? 'escuro' : 'claro'));
            r.setAttribute('data-daltonismo', ['protanopia', 'deuteranopia', 'tritanopia'].indexOf(p.daltonismo) !== -1 ? p.daltonismo : 'nenhum');
            if ([0.875, 1.125, 1.25, 1.5].indexOf(p.escala) !== -1) r.style.setProperty('--escala-fonte', String(p.escala));
        })();

        // Imagem quebrada (foto apagada/inexistente): troca por um placeholder neutro.
        // Imagens com onerror próprio (ex.: logo da navbar) são ignoradas.
        window.addEventListener('error', function (e) {
            var img = e.target;
            if (!img || img.tagName !== 'IMG' || img.hasAttribute('onerror') || img.dataset.fallback) return;
            img.dataset.fallback = '1';
            img.src = '<?= aptus_base() ?>/public/images/no-image.svg';
        }, true);
    </script>

    <?php
    // CSRF token exposto via meta para os scripts AJAX lerem.
    require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

    // URL de arquivo de public/ com versão (data de modificação). Quando o
    // arquivo muda, o navegador baixa o novo em vez de misturar CSS antigo
    // com novo do cache. Usado também em nav.php e footer.php.
    if (!function_exists('aptus_asset')) {
        function aptus_asset(string $caminho): string
        {
            $arquivo = __DIR__ . '/../../../public/' . ltrim($caminho, '/');
            $versao = is_file($arquivo) ? filemtime($arquivo) : 1;
            return (aptus_base() . '/public/') . ltrim($caminho, '/') . '?v=' . $versao;
        }
    }
    ?>
    <meta name="csrf-token" content="<?= htmlspecialchars(CsrfMiddleware::generateToken(), ENT_QUOTES, 'UTF-8') ?>">
    <!-- Caminho base do sistema (vem do APP_URL) para os arquivos JS -->
    <script>window.APTUS_BASE = <?= json_encode(aptus_base()) ?>;</script>

    <link rel="shortcut icon" href="<?= aptus_base() ?>/public/images/logo.ico" type="image/x-icon">

    <!-- CSS Base (variáveis primeiro) -->
    <link rel="stylesheet" href="<?= aptus_asset('css/variables.css') ?>">
    <link rel="stylesheet" href="<?= aptus_asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= aptus_asset('css/componentes.css') ?>">
    <link rel="stylesheet" href="<?= aptus_asset('css/header.css') ?>">
    <link rel="stylesheet" href="<?= aptus_asset('css/nav.css') ?>">
    <link rel="stylesheet" href="<?= aptus_asset('css/footer.css') ?>">
    <link rel="stylesheet" href="<?= aptus_asset('css/responsive.css') ?>">

    <!-- VLibras: carregado só no footer.php (aqui duplicava o widget na tela) -->

    <!-- CSS Específico da Página -->
    <?php if (isset($cssPagina)): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars(aptus_asset('css/' . $cssPagina), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <link rel="stylesheet" href="<?= aptus_asset('css/cookies.css') ?>">

    <!-- Acessibilidade por último: sobrescreve os :root dos CSS de página -->
    <link rel="stylesheet" href="<?= aptus_asset('css/acessibilidade.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php
// Modal LGPD (só aparece em páginas de autenticação)
require_once __DIR__ . '/lgpd_modal.php';

// Banner de cookies fixo na parte inferior
require_once __DIR__ . '/cookie_banner.php';
?>