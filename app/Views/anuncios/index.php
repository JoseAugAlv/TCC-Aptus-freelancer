<?php
// app/Views/anuncios/index.php

$tituloPagina = $tituloPagina ?? 'Serviços - Aptus';
$cssPagina    = $cssPagina    ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios      = $anuncios      ?? [];
$totalAnuncios = $totalAnuncios ?? 0;
$categorias    = $categorias    ?? [];
$catAtual      = $catAtual      ?? 0;
?>

<div class="servicos-header">
    <h1><i class="fas fa-tools"></i> Nossos Serviços</h1>
    <p>Encontre profissionais qualificados para qualquer tipo de projeto</p>
</div>

<main class="servicos-container">
    <div class="stats-info">
        <?php if (!empty($freelancerInfo)): ?>
            <p>Serviços de <strong><?= htmlspecialchars($freelancerInfo['nome']) ?></strong>
               <span class="username-tag">@<?= htmlspecialchars($freelancerInfo['username']) ?></span>
               · <strong><?= (int) $totalAnuncios ?></strong> anúncio(s)</p>
        <?php else: ?>
            <p><strong><?= (int) $totalAnuncios ?></strong> serviço(s) disponível(eis)</p>
        <?php endif; ?>
    </div>

    <div class="filtro-bar" style="display:flex; flex-wrap:wrap; gap:8px;">
        <a href="<?= aptus_base() ?>/anuncios"
           class="filtro-btn <?= $catAtual === 0 ? 'ativo' : '' ?>">
            Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
            <a href="<?= aptus_base() ?>/anuncios?categoria=<?= url_id($cat['id_categoria']) ?>"
               class="filtro-btn <?= $catAtual === (int) $cat['id_categoria'] ? 'ativo' : '' ?>">
                <i class="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>"></i>
                <?= htmlspecialchars($cat['nome']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="profissionais-grid">
        <?php if (!empty($anuncios)): ?>
            <?php require __DIR__ . '/_cards.php'; ?>
        <?php else: ?>
            <div class="msg-vazia">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h3>Nenhum serviço <?= $catAtual > 0 ? 'nesta categoria' : 'disponível' ?></h3>
                <p>Volte em breve para ver novos serviços!</p>
                <a href="<?= aptus_base() ?>/" class="btn-perfil" style="display: inline-block; width: auto; margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Início
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($temMais)): ?>
        <div class="ver-mais-wrap">
            <button type="button" class="btn-ver-mais" data-ver-mais
                    data-alvo=".profissionais-grid" data-url="<?= htmlspecialchars($proximaUrl, ENT_QUOTES, 'UTF-8') ?>"
                    data-pagina="1">
                <i class="fas fa-plus" aria-hidden="true"></i> Ver mais
            </button>
        </div>
    <?php endif; ?>
</main>

<?php $jsPagina = 'ver-mais.js'; require_once __DIR__ . '/../layouts/footer.php'; ?>