<?php
// app/Views/anuncios/show.php

$tituloPagina = $tituloPagina ?? 'Detalhes do Servico - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncio = $anuncio ?? [];
$fotos = $fotos ?? [];
$usuario = $_SESSION['usuario'] ?? null;
$usuarioInteressado = $usuarioInteressado ?? false;
$favoritado = $favoritado ?? false;
$totalFavoritos = $totalFavoritos ?? 0;
?>

<h1>Detalhes do Servico</h1>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="detalhes-container">
    <div class="detalhes-imagem">
        <?php if (!empty($anuncio['foto_capa'])): ?>
            <img src="/Aptus/public/uploads/anuncios/<?= htmlspecialchars($anuncio['foto_capa']) ?>" 
                 alt="<?= htmlspecialchars($anuncio['titulo']) ?>">
        <?php else: ?>
            <i class="fas fa-briefcase"></i>
        <?php endif; ?>
    </div>

    <div class="detalhes-info">
        <div class="detalhes-topo">
            <div class="categoria-badge">
                <i class="<?= htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
                <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
            </div>
            
            <!-- BOTAO FAVORITO -->
            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <button class="btn-favorito <?= $favoritado ? 'ativo' : '' ?>" 
                        data-anuncio-id="<?= $anuncio['id_anuncio'] ?>">
                    <i class="<?= $favoritado ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="favorito-texto">
                        <?= $favoritado ? 'Favoritado' : 'Favoritar' ?>
                    </span>
                    <span class="favorito-contador">(<?= $totalFavoritos ?? 0 ?>)</span>
                </button>
            <?php endif; ?>
        </div>
        
        <h2><?= htmlspecialchars($anuncio['titulo']) ?></h2>
        
        <p class="preco-destaque">
            <i class="fas fa-tag"></i> R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
        </p>

        <!-- ... resto do conteudo ... -->
    </div>
</div>

<script src="/Aptus/public/js/favoritos.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>