<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
$tituloPagina = $tituloPagina ?? 'Categorias - Admin';
$cssPagina = $cssPagina ?? 'admin.css';
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
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>"><?= htmlspecialchars($_SESSION['flash']['mensagem']) ?></div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    <?php if (empty($categorias)): ?>
        <p>Nenhuma categoria cadastrada.</p>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>ID</th><th>Nome</th><th>Descrição</th><th>Ícone</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td>#<?= (int)$cat['id_categoria'] ?></td>
                        <td><?= htmlspecialchars($cat['nome']) ?></td>
                        <td><?= htmlspecialchars($cat['descricao'] ?? '') ?></td>
                        <td><i class="<?= htmlspecialchars($cat['icone'] ?? 'fas fa-tag') ?>"></i></td>
                        <td>
                            <a href="/Aptus/admin/categorias/editar/<?= $cat['id_categoria'] ?>" class="btn-editar">Editar</a>
                            <a href="/Aptus/admin/categorias/excluir/<?= $cat['id_categoria'] ?>" class="btn-excluir" onclick="return confirm('Excluir esta categoria?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>