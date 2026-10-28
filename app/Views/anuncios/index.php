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
        <p><strong><?= (int) $totalAnuncios ?></strong> serviço(s) disponível(eis)</p>
    </div>

    <div class="filtro-bar" style="display:flex; flex-wrap:wrap; gap:8px;">
        <a href="/Aptus/anuncios"
           class="filtro-btn <?= $catAtual === 0 ? 'ativo' : '' ?>">
            Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
            <a href="/Aptus/anuncios?categoria=<?= (int) $cat['id_categoria'] ?>"
               class="filtro-btn <?= $catAtual === (int) $cat['id_categoria'] ? 'ativo' : '' ?>">
                <i class="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>"></i>
                <?= htmlspecialchars($cat['nome']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="profissionais-grid">
        <?php if (!empty($anuncios)): ?>
            <?php foreach ($anuncios as $anuncio): ?>
                <div class="profissional-card">
                    <div class="categoria-tag">
                        <i class="<?= htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
                        <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
                    </div>

                    <h3><?= htmlspecialchars($anuncio['titulo']) ?></h3>

                    <p class="descricao">
                        <?= htmlspecialchars(mb_strimwidth($anuncio['descricao'], 0, 150, '...')) ?>
                    </p>

                    <div class="info-footer">
                        <span class="autor">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($anuncio['freelancer_nome']) ?>
                        </span>
                        <span class="preco">
                            <i class="fas fa-tag"></i>
                            R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
                        </span>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>"
                           class="btn-perfil" style="flex: 1; margin-top: 0;">
                            <i class="fas fa-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="msg-vazia">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h3>Nenhum serviço <?= $catAtual > 0 ? 'nesta categoria' : 'disponível' ?></h3>
                <p>Volte em breve para ver novos serviços!</p>
                <a href="/Aptus/" class="btn-perfil" style="display: inline-block; width: auto; margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Início
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>