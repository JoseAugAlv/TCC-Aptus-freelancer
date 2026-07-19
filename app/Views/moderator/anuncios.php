<?php
// app/Views/moderator/anuncios.php

$tituloPagina = $tituloPagina ?? 'Moderar Anúncios - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
?>

<h1>Moderar Anúncios</h1>
<p>Anúncios pendentes de aprovação</p>

<hr>

<?php if (empty($anuncios)): ?>
    <p>Nenhum anúncio pendente no momento.</p>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Título</th>
                <th>Categoria</th>
                <th>Freelancer</th>
                <th>Preço</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anuncios as $anuncio): ?>
                <tr>
                    <td><?= htmlspecialchars($anuncio['titulo']) ?></td>
                    <td><?= htmlspecialchars($anuncio['categoria_nome']) ?></td>
                    <td><?= htmlspecialchars($anuncio['freelancer_nome']) ?></td>
                    <td>R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($anuncio['data_criacao'])) ?></td>
                    <td>
                        <form method="POST" action="/Aptus/moderator/anuncios/aprovar">
                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                            <button type="submit">Aprovar</button>
                        </form>
                        <form method="POST" action="/Aptus/moderator/anuncios/rejeitar">
                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                            <button type="submit">Rejeitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>