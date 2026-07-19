<?php
// app/Views/chat/index.php

$tituloPagina = $tituloPagina ?? 'Chat - Aptus';
$cssPagina = $cssPagina ?? 'chat.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$conversas = $conversas ?? [];
$usuario = $_SESSION['usuario'] ?? null;
?>

<div class="chat-container">
    <div class="chat-header">
        <h1><i class="fas fa-comments"></i> Mensagens</h1>
        <p>Converse com clientes e freelancers</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($conversas)): ?>
        <div class="chat-empty">
            <i class="fas fa-inbox" style="font-size: 4rem; color: #d1d5db;"></i>
            <h3>Nenhuma conversa ainda</h3>
            <p>Quando voce enviar ou receber um interesse, o chat sera ativado.</p>
            <a href="/Aptus/anuncios" class="btn-explorar">
                <i class="fas fa-search"></i> Explorar Servicos
            </a>
        </div>
    <?php else: ?>
        <div class="conversas-lista">
            <?php foreach ($conversas as $conversa): ?>
                <a href="/Aptus/chat/<?= $conversa['id_interesse'] ?>" class="conversa-item <?= ($conversa['nao_lidas'] ?? 0) > 0 ? 'nao-lida' : '' ?>">
                    <div class="conversa-avatar">
                        <?php if (!empty($conversa['outro_usuario_foto']) && $conversa['outro_usuario_foto'] != 'default.png'): ?>
                            <img src="/Aptus/public/uploads/<?= htmlspecialchars($conversa['outro_usuario_foto']) ?>" 
                                 alt="<?= htmlspecialchars($conversa['outro_usuario_nome']) ?>">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                        <?php if (($conversa['nao_lidas'] ?? 0) > 0): ?>
                            <span class="badge-nao-lidas"><?= $conversa['nao_lidas'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="conversa-info">
                        <div class="conversa-nome">
                            <?= htmlspecialchars($conversa['outro_usuario_nome']) ?>
                            <span class="conversa-data">
                                <?= date('d/m/Y H:i', strtotime($conversa['ultima_data'] ?? 'now')) ?>
                            </span>
                        </div>
                        <div class="conversa-assunto">
                            <?= htmlspecialchars($conversa['anuncio_titulo']) ?>
                        </div>
                        <div class="conversa-ultima">
                            <?= htmlspecialchars(mb_strimwidth($conversa['ultima_mensagem'] ?? '', 0, 60, '...')) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>