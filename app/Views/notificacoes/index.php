<?php
// app/Views/notificacoes/index.php

$tituloPagina = $tituloPagina ?? 'Notificações - Aptus';
$cssPagina    = $cssPagina    ?? 'notificacoes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$naoLidas = $naoLidas ?? [];
$todas    = $todas    ?? [];
$usuario  = $_SESSION['usuario'] ?? null;

$mostrarTodas = isset($_GET['todas']) && $_GET['todas'] == '1';
$notificacoes = $mostrarTodas ? $todas : $naoLidas;

/**
 * Resolve a URL de destino a partir da origem + registro_id
 * (a tabela notificacao não tem coluna "link", então montamos aqui).
 */
function urlNotificacao(array $n): string
{
    $tabela = $n['tabela_origem'] ?? '';
    $regId  = (int) ($n['registro_id'] ?? 0);
    $intId  = (int) ($n['id_interesse'] ?? 0);

    switch ($tabela) {
        case 'interesse':
            return $regId ? '/Aptus/interesses/detalhes/' . $regId : '/Aptus/interesses/ativos';
        case 'disputa':
            return $regId ? '/Aptus/disputas/detalhes/' . $regId : '/Aptus/interesses/ativos';
        case 'mensagem':
            return $intId ? '/Aptus/chat/' . $intId : '/Aptus/chat';
        case 'avaliacao':
            return '/Aptus/interesses/ativos';
        case 'confirmacao_pagamento':
            return '/Aptus/pagamentos';
        case 'anuncio_servico':
            return '/Aptus/anuncios/meus';
        default:
            return '';
    }
}
?>

<div class="notificacoes-container">
    <div class="notificacoes-header">
        <h1><i class="fas fa-bell"></i> Notificações</h1>
    </div>

    <div class="notificacoes-tabs">
        <a href="/Aptus/notificacoes" class="tab-btn <?= !$mostrarTodas ? 'active' : '' ?>">
            Não Lidas
            <?php if (count($naoLidas) > 0): ?>
                <span class="tab-badge"><?= count($naoLidas) ?></span>
            <?php endif; ?>
        </a>
        <a href="/Aptus/notificacoes?todas=1" class="tab-btn <?= $mostrarTodas ? 'active' : '' ?>">
            Todas
            <span class="tab-badge"><?= count($todas) ?></span>
        </a>
        <?php if (count($naoLidas) > 0): ?>
            <button class="btn-marcar-todas">Marcar todas como lidas</button>
        <?php endif; ?>
    </div>

    <?php if (empty($notificacoes)): ?>
        <div class="notificacoes-empty">
            <i class="fas fa-inbox"></i>
            <p>Nenhuma notificação encontrada.</p>
        </div>
    <?php else: ?>
        <div class="notificacoes-list">
            <?php foreach ($notificacoes as $notif): ?>
                <?php $linkDestino = urlNotificacao($notif); ?>
                <div class="notificacao-item <?= $notif['lida'] ? 'lida' : 'nao-lida' ?>">
                    <div class="notificacao-content">
                        <div class="notificacao-titulo">
                            <?= htmlspecialchars($notif['titulo']) ?>
                            <?php if (!$notif['lida']): ?>
                                <span class="notificacao-badge">NOVA</span>
                            <?php endif; ?>
                        </div>
                        <p class="notificacao-mensagem">
                            <?= htmlspecialchars($notif['mensagem']) ?>
                        </p>
                        <small class="notificacao-data">
                            <?= date('d/m/Y H:i', strtotime($notif['data_criacao'])) ?>
                        </small>
                    </div>
                    <div class="notificacao-actions">
                        <?php if ($linkDestino !== ''): ?>
                            <a href="<?= htmlspecialchars($linkDestino) ?>" class="btn-ver">
                                <i class="fas fa-arrow-right"></i> Ver
                            </a>
                        <?php endif; ?>
                        <?php if (!$notif['lida']): ?>
                            <button class="btn-marcar-lida" data-id="<?= $notif['id_notificacao'] ?>">
                                <i class="fas fa-check"></i> Marcar como lida
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="notificacoes-voltar">
        <a href="/Aptus/"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<script src="/Aptus/public/js/notificacoes.js"></script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>