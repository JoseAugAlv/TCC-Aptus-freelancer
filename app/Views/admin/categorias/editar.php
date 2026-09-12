<?php
require_once __DIR__ . "/../../../Middleware/CsrfMiddleware.php";
$tituloPagina = 'Editar Categoria - Admin';
$cssPagina = 'admin.css';
require_once __DIR__ . '/../../layouts/header.php';
require_once __DIR__ . '/../../layouts/nav.php';
$cat = $cat ?? [];
?>
<div class="admin-container">
    <h1>Editar Categoria</h1>
    <hr>
    <form method="POST" action="/Aptus/admin/categorias/atualizar" class="config-form">
        <input type="hidden" name="id" value="<?= (int)($cat['id_categoria'] ?? 0) ?>">
        <div class="form-group">
            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" class="form-control" required maxlength="80"
                   value="<?= htmlspecialchars($cat['nome'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <input type="text" id="descricao" name="descricao" class="form-control" maxlength="255"
                   value="<?= htmlspecialchars($cat['descricao'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="icone">Ícone (classe Font Awesome)</label>
            <input type="text" id="icone" name="icone" class="form-control" maxlength="60"
                   value="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-salvar">Atualizar</button>
            <a href="/Aptus/admin/categorias" class="btn-voltar">Cancelar</a>
        </div>
        <?= CsrfMiddleware::field() ?>
    </form>
</div>
<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>