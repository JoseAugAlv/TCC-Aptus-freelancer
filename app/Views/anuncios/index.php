<?php
// app/Views/anuncios/index.php

$tituloPagina = $tituloPagina ?? 'Serviços - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

// Dados vindos do Controller
$anuncios = $anuncios ?? [];
$totalAnuncios = $totalAnuncios ?? 0;
?>

<div class="servicos-header">
    <h1><i class="fas fa-tools"></i> Nossos Serviços</h1>
    <p>Encontre profissionais qualificados para qualquer tipo de projeto</p>
</div>

<main class="servicos-container">
    <div class="stats-info">
        <p><strong><?= $totalAnuncios ?></strong> serviço(s) disponível(eis)</p>
    </div>

    <div class="filtro-bar">
        <button class="filtro-btn ativo">Todos</button>
        <button class="filtro-btn">Eletricista</button>
        <button class="filtro-btn">Encanador</button>
        <button class="filtro-btn">Limpeza</button>
        <button class="filtro-btn">Construção</button>
        <button class="filtro-btn">Cuidador</button>
        <button class="filtro-btn">Jardineiro</button>
        <button class="filtro-btn">Outros</button>
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
                        <a href="/Aptus/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="btn-perfil" style="flex: 1; margin-top: 0;">
                            <i class="fas fa-eye"></i> Ver Detalhes
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="msg-vazia">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <h3>Nenhum serviço disponível</h3>
                <p>Volte em breve para ver novos serviços!</p>
                <a href="/Aptus/" class="btn-perfil" style="display: inline-block; width: auto; margin-top: 1rem;">
                    <i class="fas fa-arrow-left"></i> Voltar ao Início
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>