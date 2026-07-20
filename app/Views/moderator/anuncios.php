<?php
// app/Views/moderator/anuncios.php

$tituloPagina = $tituloPagina ?? 'Moderar Anuncios - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncios = $anuncios ?? [];
?>

<h1>Moderar Anuncios</h1>
<p>Anuncios pendentes de aprovacao</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (empty($anuncios)): ?>
    <p>Nenhum anuncio pendente no momento.</p>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Titulo</th>
                <th>Categoria</th>
                <th>Freelancer</th>
                <th>Preco</th>
                <th>Data</th>
                <th>Acoes</th>
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
                        <form method="POST" action="/Aptus/moderator/anuncios/aprovar" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                            <button type="submit" class="btn-aprovar" onclick="return confirm('Aprovar este anuncio?')">Aprovar</button>
                        </form>
                        <form method="POST" action="/Aptus/moderator/anuncios/rejeitar" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?>">
                            <button type="submit" class="btn-rejeitar" onclick="return confirm('Rejeitar este anuncio?')">Rejeitar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>