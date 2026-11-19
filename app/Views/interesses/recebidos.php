<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/interesses/recebidos.php

$tituloPagina = $tituloPagina ?? 'Interesses Recebidos - Aptus';
$cssPagina = $cssPagina ?? 'recebidos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
?>

<div class="recebidos-container">
    <div class="recebidos-header">
        <h1><i class="fas fa-inbox"></i> Interesses Recebidos</h1>
        <p>Clientes que demonstraram interesse nos seus serviços</p>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($interesses)): ?>
        <div class="recebidos-empty">
            <i class="fas fa-inbox"></i>
            <h3>Nenhum interesse recebido ainda</h3>
            <p>Quando um cliente demonstrar interesse em um dos seus serviços, ele aparecerá aqui.</p>
            <a href="<?= aptus_base() ?>/anuncios/meus" class="btn-primary">Ver meus anúncios</a>
        </div>
    <?php else: ?>
        <div class="recebidos-table-wrapper">
            <table class="recebidos-table">
                <thead>
                    <tr>
                        <th>Serviço</th>
                        <th>Cliente</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th class="col-acoes">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interesses as $interesse): ?>
                        <?php $status = (string) $interesse['situacao']; ?>
                        <tr>
                            <td><?= htmlspecialchars($interesse['anuncio_titulo']) ?></td>
                            <td><?= htmlspecialchars($interesse['contratante_nome']) ?></td>
                            <td>R$ <?= number_format($interesse['anuncio_preco'], 2, ',', '.') ?></td>
                            <td>
                                <span class="status-badge <?= htmlspecialchars($status) ?>">
                                    <?= htmlspecialchars(ucfirst($status)) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($interesse['data_interesse'])) ?></td>
                            <td class="col-acoes">
                                <div class="acoes-group">
                                    <a href="<?= aptus_base() ?>/interesses/detalhes/<?= url_id((int) $interesse['id_interesse']) ?>" class="btn-ver">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <?php if ($status === 'ativo' && empty($interesse['concluido_freelancer'])): ?>
                                        <form method="POST" action="<?= aptus_base() ?>/interesses/confirmar-execucao">
                                            <input type="hidden" name="id" value="<?= (int) $interesse['id_interesse'] ?>">
                                            <input type="hidden" name="origem" value="recebidos">
                                            <?= CsrfMiddleware::field() ?>
                                            <button type="submit" class="btn-concluir"
                                                    onclick="return confirm('Confirmar que o serviço foi feito? Quando o cliente confirmar também, o serviço é concluído.')">
                                                <i class="fas fa-check"></i> Concluir
                                            </button>
                                        </form>
                                    <?php elseif ($status === 'ativo'): ?>
                                        <span class="status-badge pendente" title="Você já concluiu; falta o cliente confirmar">Aguardando cliente</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="recebidos-footer">
        <a href="<?= aptus_base() ?>/freelancer" class="btn-voltar"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
