<?php
// app/Views/perfil/portfolio_criar.php

$tituloPagina = $tituloPagina ?? 'Adicionar ao Portfólio - Aptus';
$cssPagina = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="perfil-container">
    <div class="perfil-header">
        <h1><i class="fas fa-plus"></i> Adicionar ao Portfólio</h1>
        <a href="/Aptus/perfil/portfolio" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message flash-<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'sucesso' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="perfil-card">
        <form method="POST" action="/Aptus/perfil/portfolio/salvar" class="perfil-form" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ex: Reforma de Apartamento" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="4" placeholder="Descreva o trabalho realizado..."></textarea>
            </div>

            <div class="form-group">
                <label for="imagem">Imagem</label>
                <input type="file" id="imagem" name="imagem" class="form-control" accept="image/*">
                <small style="color: #888; display: block; margin-top: 5px;">Formatos permitidos: JPG, PNG, GIF. Tamanho máximo: 5MB</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-save"></i> Adicionar
                </button>
                <a href="/Aptus/perfil/portfolio" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>