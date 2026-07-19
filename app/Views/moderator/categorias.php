<?php
// app/Views/moderator/categorias.php

$tituloPagina = $tituloPagina ?? 'Categorias - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$categorias = $categorias ?? [];
?>

<h1>Categorias</h1>
<p>Gerenciar categorias de serviços</p>

<hr>

<h2>Nova Categoria</h2>
<form method="POST" action="/Aptus/moderator/categorias/salvar">
    <label>Nome:</label>
    <input type="text" name="nome" required>
    <button type="submit">Adicionar</button>
</form>

<hr>

<table border="1" cellpadding="8" cellspacing="0">
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
        <?php if (empty($categorias)): ?>
            <tr>
                <td colspan="5">Nenhuma categoria encontrada.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($categorias as $categoria): ?>
                <tr>
                    <td>#<?= $categoria['id_categoria'] ?></td>
                    <td><?= htmlspecialchars($categoria['nome']) ?></td>
                    <td><?= htmlspecialchars($categoria['icone'] ?? 'N/A') ?></td>
                    <td><?= $categoria['total_anuncios'] ?? 0 ?></td>
                    <td>
                        <a href="/Aptus/moderator/categorias/editar/<?= $categoria['id_categoria'] ?>">Editar</a>
                        <a href="/Aptus/moderator/categorias/excluir/<?= $categoria['id_categoria'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="/Aptus/moderator">Voltar</a></p>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>