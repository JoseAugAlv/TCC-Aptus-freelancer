<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
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
        <form method="POST" action="<?= aptus_base() ?>/moderator/categorias/salvar" class="form-categoria">
            <?= CsrfMiddleware::field() ?>
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
            <a href="<?= aptus_base() ?>/moderator" class="btn-voltar">Voltar</a>
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
                                <details class="editar-categoria-inline">
                                    <summary class="btn-editar">Editar</summary>
                                    <form method="POST" action="<?= aptus_base() ?>/moderator/categorias/editar" class="form-editar-categoria">
                                        <input type="hidden" name="id" value="<?= (int) $categoria['id_categoria'] ?>">
                                        <input type="text" name="nome" required maxlength="100" aria-label="Nome"
                                               value="<?= htmlspecialchars($categoria['nome']) ?>">
                                        <input type="text" name="descricao" maxlength="255" aria-label="Descrição" placeholder="Descrição"
                                               value="<?= htmlspecialchars($categoria['descricao'] ?? '') ?>">
                                        <input type="text" name="icone" maxlength="50" aria-label="Ícone" placeholder="fas fa-tag"
                                               value="<?= htmlspecialchars($categoria['icone'] ?? 'fas fa-tag') ?>">
                                        <?= CsrfMiddleware::field() ?>
                                        <button type="submit" class="btn-editar">Salvar</button>
                                    </form>
                                </details>
                                <form method="POST" action="<?= aptus_base() ?>/moderator/categorias/excluir" style="display:inline;"
                                    onsubmit="return confirm('Excluir esta categoria?')">
                                    <input type="hidden" name="id" value="<?= (int) $categoria['id_categoria'] ?>">
                                    <?= CsrfMiddleware::field() ?>
                                    <button type="submit" class="btn-excluir">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="voltar-wrapper">
            <a href="<?= aptus_base() ?>/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>