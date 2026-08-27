<?php
// app/Views/moderator/anuncios.php

$tituloPagina = $tituloPagina ?? 'Moderar Anuncios - Aptus';
$cssPagina = $cssPagina ?? 'anuncios_mod.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
?>

<div class="anuncios-wrapper">

    <div class="anuncios-header">
        <h1>Moderar Anúncios</h1>
        <p>Anúncios pendentes de aprovação</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($anuncios)): ?>
        <div class="empty-state">
            <p>Nenhum anúncio pendente no momento.</p>
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
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
                                    <button type="submit" class="btn-aprovar" onclick="return confirm('Aprovar este anúncio?')">Aprovar</button>
                                </form>
                                <form method="POST" action="/Aptus/moderator/anuncios/rejeitar">
                                    <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                                    <button type="submit" class="btn-rejeitar" onclick="return confirm('Rejeitar este anúncio?')">Rejeitar</button>
                                </form>
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