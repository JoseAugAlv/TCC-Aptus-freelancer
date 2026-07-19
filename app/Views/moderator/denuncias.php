<?php
// app/Views/moderator/denuncias.php

$tituloPagina = $tituloPagina ?? 'Denúncias - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$denuncias = $denuncias ?? [];
?>

<h1>Denúncias</h1>
<p>Denúncias pendentes de análise</p>

<hr>

<?php if (empty($denuncias)): ?>
    <p>Nenhuma denúncia pendente.</p>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Denunciante</th>
                <th>Denunciado</th>
                <th>Motivo</th>
                <th>Anúncio</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($denuncias as $denuncia): ?>
                <tr>
                    <td><?= htmlspecialchars($denuncia['denunciante_nome']) ?></td>
                    <td><?= htmlspecialchars($denuncia['denunciado_nome']) ?></td>
                    <td><?= htmlspecialchars($denuncia['motivo']) ?></td>
                    <td><?= htmlspecialchars($denuncia['anuncio_titulo'] ?? 'N/A') ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($denuncia['data_criacao'])) ?></td>
                    <td>
                        <a href="/Aptus/moderator/denuncias/visualizar/<?= $denuncia['id_denuncia'] ?>">Analisar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>