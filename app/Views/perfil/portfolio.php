<?php
// app/Views/perfil/portfolio.php

$tituloPagina = $tituloPagina ?? 'Meu Portfólio - Aptus';
$cssPagina = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$itens = $itens ?? [];
?>

<div class="perfil-container">
    <div class="perfil-header">
        <h1><i class="fas fa-images"></i> Meu Portfólio</h1>
        <div class="perfil-actions">
            <a href="/Aptus/perfil/portfolio/criar" class="btn-editar">
                <i class="fas fa-plus"></i> Adicionar Item
            </a>
            <a href="/Aptus/perfil" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'sucesso' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($itens)): ?>
        <div class="portfolio-empty">
            <i class="fas fa-images"></i>
            <h3>Nenhum item no portfólio</h3>
            <p>Adicione fotos dos seus trabalhos para mostrar sua experiência.</p>
            <a href="/Aptus/perfil/portfolio/criar" class="btn-editar" style="display: inline-block; margin-top: 1rem;">
                <i class="fas fa-plus"></i> Adicionar Primeiro Item
            </a>
        </div>
    <?php else: ?>
        <div class="portfolio-grid">
            <?php foreach ($itens as $item): ?>
                <div class="portfolio-item">
                    <?php if (!empty($item['imagem'])): ?>
                        <img src="/Aptus/public/uploads/portfolio/<?= htmlspecialchars($item['imagem']) ?>" 
                             alt="<?= htmlspecialchars($item['titulo']) ?>">
                    <?php else: ?>
                        <div class="portfolio-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    <?php endif; ?>
                    <div class="portfolio-item-info">
                        <h4><?= htmlspecialchars($item['titulo']) ?></h4>
                        <?php if (!empty($item['descricao'])): ?>
                            <p><?= htmlspecialchars(mb_strimwidth($item['descricao'], 0, 80, '...')) ?></p>
                        <?php endif; ?>
                        <div class="portfolio-item-actions">
                            <a href="/Aptus/perfil/portfolio/editar/<?= $item['id_portfolio'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/Aptus/perfil/portfolio/excluir/<?= $item['id_portfolio'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Tem certeza que deseja excluir este item?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>