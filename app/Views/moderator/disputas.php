<?php
// app/Views/moderator/disputas.php

$tituloPagina = $tituloPagina ?? 'Disputas - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$disputas = $disputas ?? [];
$totalPendentes = $totalPendentes ?? 0;
?>

<h1>Disputas</h1>
<p>Disputas pendentes de analise</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="moderator-stats">
    <div class="stat-item">
        <span class="stat-number"><?= $totalPendentes ?></span>
        <span class="stat-label">Pendentes</span>
    </div>
</div>

<?php if (empty($disputas)): ?>
    <p>Nenhuma disputa pendente no momento.</p>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Aberto por</th>
                <th>Servico</th>
                <th>Motivo</th>
                <th>Data</th>
                <th>Status</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($disputas as $disputa): ?>
                <tr>
                    <td>#<?= $disputa['id_disputa'] ?></td>
                    <td><?= htmlspecialchars($disputa['aberto_por_nome']) ?></td>
                    <td><?= htmlspecialchars($disputa['anuncio_titulo'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($disputa['motivo']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($disputa['data_abertura'])) ?></td>
                    <td><?= htmlspecialchars($disputa['situacao_nome']) ?></td>
                    <td>
                        <a href="/Aptus/disputas/detalhes/<?= $disputa['id_disputa'] ?>">
                            <i class="fas fa-eye"></i> Analisar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><a href="/Aptus/moderator">Voltar</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>