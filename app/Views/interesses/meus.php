<?php
// app/Views/interesses/meus.php

$tituloPagina = $tituloPagina ?? 'Meus Interesses - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
?>

<div class="interesses-container">
    <div class="interesses-header">
        <h1><i class="fas fa-paper-plane"></i> Meus Interesses</h1>
        <p>Interesses que voce enviou</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($interesses)): ?>
        <div class="interesses-empty">
            <i class="fas fa-inbox"></i>
            <h3>Nenhum interesse enviado</h3>
            <p>Voce ainda nao enviou nenhum interesse.</p>
            <a href="/Aptus/anuncios" class="btn-primary">Explorar Servicos</a>
        </div>
    <?php else: ?>
        <div class="interesses-grid">
            <?php foreach ($interesses as $interesse): 
                $statusCor = match($interesse['situacao']) {
                    'pendente' => '#f59e0b',
                    'ativo' => '#3b82f6',
                    'concluido' => '#10b981',
                    'cancelado' => '#ef4444',
                    'recusado' => '#6b7280',
                    default => '#94a3b8'
                };
                $statusLabel = match($interesse['situacao']) {
                    'pendente' => 'Pendente',
                    'ativo' => 'Ativo',
                    'concluido' => 'Concluido',
                    'cancelado' => 'Cancelado',
                    'recusado' => 'Recusado',
                    default => ucfirst($interesse['situacao'])
                };
            ?>
                <div class="interesse-card">
                    <div class="interesse-header">
                        <div class="interesse-cliente">
                            <?php if (!empty($interesse['freelancer_foto']) && $interesse['freelancer_foto'] != 'default.png'): ?>
                                <img src="/Aptus/public/uploads/<?= htmlspecialchars($interesse['freelancer_foto']) ?>" 
                                     alt="<?= htmlspecialchars($interesse['freelancer_nome']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($interesse['freelancer_nome']) ?></strong>
                                <span class="interesse-data"><?= date('d/m/Y H:i', strtotime($interesse['data_interesse'])) ?></span>
                            </div>
                        </div>
                        <span class="interesse-status" style="background: <?= $statusCor ?>20; color: <?= $statusCor ?>;">
                            <?= $statusLabel ?>
                        </span>
                    </div>

                    <div class="interesse-body">
                        <h3><?= htmlspecialchars($interesse['anuncio_titulo']) ?></h3>
                        <p class="interesse-mensagem">
                            <strong>Mensagem:</strong>
                            <?= nl2br(htmlspecialchars($interesse['mensagem_inicial'] ?? 'Sem mensagem')) ?>
                        </p>
                        <p class="interesse-preco">
                            <i class="fas fa-tag"></i> R$ <?= number_format($interesse['anuncio_preco'], 2, ',', '.') ?>
                        </p>
                    </div>

                    <div class="interesse-acoes">
                        <a href="/Aptus/chat/<?= $interesse['id_interesse'] ?>" class="btn-chat">
                            <i class="fas fa-comments"></i> Chat
                        </a>
                        <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>" class="btn-detalhes">
                            <i class="fas fa-eye"></i> Detalhes
                        </a>
                        <?php if ($interesse['situacao'] == 'pendente'): ?>
                            <button class="btn-aguardando" disabled>
                                <i class="fas fa-clock"></i> Aguardando Resposta
                            </button>
                        <?php endif; ?>
                        <?php if ($interesse['situacao'] == 'ativo'): ?>
                            <a href="/Aptus/interesses/ativos" class="btn-ativo">
                                <i class="fas fa-check-circle"></i> Servico Ativo
                            </a>
                        <?php endif; ?>
                        <?php if ($interesse['situacao'] == 'pendente' || $interesse['situacao'] == 'ativo'): ?>
                            <form method="POST" action="/Aptus/interesses/cancelar" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                                <button type="submit" class="btn-cancelar" onclick="return confirm('Cancelar este interesse?')">
                                    <i class="fas fa-times"></i> Cancelar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="interesses-links">
        <a href="/Aptus/interesses/pendentes"><i class="fas fa-clock"></i> Propostas Pendentes</a>
        <a href="/Aptus/interesses/ativos"><i class="fas fa-check-circle"></i> Servicos Ativos</a>
        <a href="/Aptus/"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>