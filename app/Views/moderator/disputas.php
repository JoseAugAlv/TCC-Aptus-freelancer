<?php
// app/Views/moderator/disputas.php

$tituloPagina = $tituloPagina ?? 'Disputas - Aptus';
$cssPagina = $cssPagina ?? 'disputa.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$disputas = $disputas ?? [];
$totalPendentes = $totalPendentes ?? 0;
?>

<div class="disputas-wrapper">
    <div class="disputas-header">
        <h1>Disputas</h1>
        <p>Disputas pendentes de análise</p>
    </div>

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
        <div class="empty-state">
            <p>Nenhuma disputa pendente no momento.</p>
            <a href="/Aptus/moderator" class="btn-voltar">Voltar</a>
        </div>
    <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Aberto por</th>
                        <th>Serviço</th>
                        <th>Motivo</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Ações</th>
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
                            <td><span class="status-badge <?= strtolower($disputa['situacao_nome']) ?>"><?= htmlspecialchars($disputa['situacao_nome']) ?></span></td>
                            <td>
                                <a href="/Aptus/disputas/detalhes/<?= $disputa['id_disputa'] ?>" class="btn-analisar">
                                    <i class="fas fa-eye"></i> Analisar
                                </a>
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