<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
$tituloPagina = $tituloPagina ?? 'Categorias - Admin';
$cssPagina    = $cssPagina    ?? 'admin.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
$categorias = $categorias ?? [];
?>
<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-tags"></i> Categorias</h1>
        <a href="/Aptus/admin/categorias/criar" class="btn-criar"><i class="fas fa-plus"></i> Nova Categoria</a>
    </div>
    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($categorias)): ?>
        <p>Nenhuma categoria cadastrada.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Ícone</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td>#<?= (int) $cat['id_categoria'] ?></td>
                        <td><?= htmlspecialchars($cat['nome']) ?></td>
                        <td><?= htmlspecialchars($cat['descricao'] ?? '') ?></td>
                        <td><i class="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>"></i></td>
                        <td>
                            <div class="acoes-tabela">
                                <a href="/Aptus/admin/categorias/editar/<?= $cat['id_categoria'] ?>" class="btn-editar">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form method="POST" action="/Aptus/admin/categorias/excluir"
                                      class="form-inline"
                                      onsubmit="return confirm('Excluir esta categoria?');">
                                    <input type="hidden" name="id" value="<?= (int) $cat['id_categoria'] ?>">
                                    <?= CsrfMiddleware::field() ?>
                                    <button type="submit" class="btn-excluir">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
.acoes-tabela {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}
.acoes-tabela .form-inline {
    display: inline;
    margin: 0;
}
.btn-editar,
.btn-excluir {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
    line-height: 1.3;
    transition: transform .15s, box-shadow .15s;
}
.btn-editar {
    background: #006577;
    color: #fff;
}
.btn-editar:hover {
    background: #004d5c;
    transform: translateY(-1px);
}
.btn-excluir {
    background: #ef4444;
    color: #fff;
}
.btn-excluir:hover {
    background: #dc2626;
    transform: translateY(-1px);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>