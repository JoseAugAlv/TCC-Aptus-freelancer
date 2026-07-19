<?php
// app/Views/moderator/disputas.php

$tituloPagina = $tituloPagina ?? 'Disputas - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$disputas = $disputas ?? [];
?>

<h1>Disputas</h1>
<p>Disputas pendentes de resolução</p>

<hr>

<?php if (empty($disputas)): ?>
    <p>Nenhuma disputa pendente.</p>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Aberto por</th>
                <th>Anúncio</th>
                <th>Motivo</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($disputas as $disputa): ?>
                <tr>
                    <td><?= htmlspecialchars($disputa['aberto_por_nome']) ?></td>
                    <td><?= htmlspecialchars($disputa['anuncio_titulo']) ?></td>
                    <td><?= htmlspecialchars($disputa['motivo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($disputa['data_abertura'])) ?></td>
                    <td>
                        <a href="/Aptus/moderator/disputas/visualizar/<?= $disputa['id_disputa'] ?>">Resolver</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>