<?php
require_once __DIR__ . "/../../../Middleware/CsrfMiddleware.php";
$tituloPagina = 'Nova Categoria - Admin';
$cssPagina = 'admin.css';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/nav.php';
?>
<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-plus"></i> Nova Categoria</h1>
        <a href="/Aptus/admin/categorias" class="btn-voltar">
            <i class="fas fa-arrow-left"></i> Voltar
        </a>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <form method="POST" action="/Aptus/admin/categorias/salvar" class="config-form">
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" class="form-control"
                   required maxlength="80" placeholder="Ex: Eletricista">
        </div>

        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" class="form-control"
                   maxlength="255" placeholder="Breve descrição da categoria">
        </div>

        <div class="form-group">
            <label for="icone">Ícone (classe Font Awesome)</label>
            <input type="text" id="icone" name="icone" class="form-control"
                   value="fas fa-tag" maxlength="60" placeholder="fas fa-tag">
            <small class="help-text">Ex: <code>fas fa-tag</code>, <code>fas fa-bolt</code>, <code>fas fa-tools</code></small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Salvar
            </button>
            <a href="/Aptus/admin/categorias" class="btn-voltar">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
        <?= CsrfMiddleware::field() ?>
    </form>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>