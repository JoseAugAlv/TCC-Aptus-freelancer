<?php
// app/Views/interesses/pendentes.php

$tituloPagina = $tituloPagina ?? 'Propostas Pendentes - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
?>

<div class="interesses-container">
    <div class="interesses-header">
        <h1><i class="fas fa-clock"></i> Propostas Pendentes</h1>
        <p>Clientes aguardando sua resposta</p>
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
            <h3>Nenhuma proposta pendente</h3>
            <p>Quando um cliente enviar uma proposta, ela aparecerá aqui.</p>
            <a href="/Aptus/anuncios" class="btn-primary">Ver Anúncios</a>
        </div>
    <?php else: ?>
        <div class="interesses-grid">
            <?php foreach ($interesses as $interesse): ?>
                <div class="interesse-card">
                    <div class="interesse-header">
                        <div class="interesse-cliente">
                            <?php if (!empty($interesse['contratante_foto']) && $interesse['contratante_foto'] != 'default.png'): ?>
                                <img src="/Aptus/public/uploads/<?= htmlspecialchars($interesse['contratante_foto']) ?>" 
                                     alt="<?= htmlspecialchars($interesse['contratante_nome']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user-circle"></i>
                            <?php endif; ?>
                            <div>
                                <strong><?= htmlspecialchars($interesse['contratante_nome']) ?></strong>
                                <span class="interesse-data"><?= date('d/m/Y H:i', strtotime($interesse['data_interesse'])) ?></span>
                            </div>
                        </div>
                        <span class="interesse-status pendente">Pendente</span>
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
                        <form method="POST" action="/Aptus/interesses/aceitar" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                            <button type="submit" class="btn-aceitar" onclick="return confirm('Aceitar esta proposta?')">
                                <i class="fas fa-check"></i> Aceitar
                            </button>
                        </form>
                        <form method="POST" action="/Aptus/interesses/recusar" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                            <button type="submit" class="btn-recusar" onclick="return confirm('Recusar esta proposta?')">
                                <i class="fas fa-times"></i> Recusar
                            </button>
                        </form>
                        <a href="/Aptus/chat/<?= $interesse['id_interesse'] ?>" class="btn-chat">
                            <i class="fas fa-comments"></i> Chat
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="interesses-links">
        <a href="/Aptus/interesses/ativos"><i class="fas fa-check-circle"></i> Serviços Ativos</a>
        <a href="/Aptus/interesses/meus"><i class="fas fa-list"></i> Meus Interesses</a>
        <a href="/Aptus/"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>