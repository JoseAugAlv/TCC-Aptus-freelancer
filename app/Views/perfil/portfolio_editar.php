<?php
// app/Views/perfil/portfolio_editar.php

$tituloPagina = $tituloPagina ?? 'Editar Portfólio - Aptus';
$cssPagina = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$item = $item ?? [];
?>

<div class="perfil-container">
    <div class="perfil-header">
        <h1><i class="fas fa-edit"></i> Editar Item</h1>
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
        <form method="POST" action="/Aptus/perfil/portfolio/atualizar" class="perfil-form" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $item['id_portfolio'] ?? 0 ?>">

            <div class="form-group">
                <label for="titulo">Título *</label>
                <input type="text" id="titulo" name="titulo" class="form-control" value="<?= htmlspecialchars($item['titulo'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="4"><?= htmlspecialchars($item['descricao'] ?? '') ?></textarea>
            </div>

            <?php if (!empty($item['imagem'])): ?>
                <div class="form-group">
                    <label>Imagem Atual</label>
                    <div style="margin-top: 8px;">
                        <img src="/Aptus/public/uploads/portfolio/<?= htmlspecialchars($item['imagem']) ?>" 
                             alt="<?= htmlspecialchars($item['titulo'] ?? '') ?>" 
                             style="max-width: 200px; border-radius: 8px; border: 1px solid #eee;">
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="imagem">Nova Imagem (opcional)</label>
                <input type="file" id="imagem" name="imagem" class="form-control" accept="image/*">
                <small style="color: #888; display: block; margin-top: 5px;">Deixe em branco para manter a imagem atual.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-save"></i> Atualizar
                </button>
                <a href="/Aptus/perfil/portfolio" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>