<?php
// app/Views/moderator/categorias.php

$tituloPagina = $tituloPagina ?? 'Categorias - Aptus';
$cssPagina = $cssPagina ?? 'categoria.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$categorias = $categorias ?? [];
?>

<div class="categorias-wrapper">
    <div class="categorias-header">
        <h1>Categorias</h1>
        <p>Gerenciar categorias de serviços</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="form-section">
        <h2>Nova Categoria</h2>
        <form method="POST" action="/Aptus/moderator/categorias/salvar" class="form-categoria">
            <div class="form-row">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <button type="submit" class="btn-adicionar">Adicionar</button>
            </div>
        </form>
    </div>

    <hr>

    <?php if (empty($categorias)): ?>
        <div class="empty-state">
            <p>Nenhuma categoria encontrada.</p>
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Ícone</th>
                        <th>Anúncios</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $categoria): ?>
                        <tr>
                            <td>#<?= $categoria['id_categoria'] ?></td>
                            <td><?= htmlspecialchars($categoria['nome']) ?></td>
                            <td><?= htmlspecialchars($categoria['icone'] ?? 'N/A') ?></td>
                            <td><?= $categoria['total_anuncios'] ?? 0 ?></td>
                            <td>
                                <a href="/Aptus/moderator/categorias/editar/<?= $categoria['id_categoria'] ?>" class="btn-editar">Editar</a>
                                <a href="/Aptus/moderator/categorias/excluir/<?= $categoria['id_categoria'] ?>" class="btn-excluir" onclick="return confirm('Tem certeza?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="voltar-wrapper">
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>