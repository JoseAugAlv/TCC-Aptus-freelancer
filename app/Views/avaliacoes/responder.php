<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/avaliacoes/responder.php

$tituloPagina = $tituloPagina ?? 'Responder Avaliação - Aptus';
$cssPagina = $cssPagina ?? 'avaliacoes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$avaliacao = $avaliacao ?? [];
?>

<div class="avaliacao-container">
    <div class="avaliacao-header">
        <h1><i class="fas fa-reply"></i> Responder Avaliação</h1>
        <p>Responda ao comentário do cliente</p>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="avaliacao-card">
        <div class="avaliacao-info">
            <p><strong>Avaliação de:</strong> <?= htmlspecialchars($avaliacao['avaliador_nome'] ?? '') ?></p>
            <p><strong>Nota:</strong>
                <?php $nota = (int) ($avaliacao['nota'] ?? 0); ?>
                <span class="avaliacao-estrelas-leitura" aria-label="<?= $nota ?> de 5 estrelas">
                    <?php for ($n = 1; $n <= 5; $n++): ?>
                        <i class="<?= $n <= $nota ? 'fas' : 'far' ?> fa-star" aria-hidden="true"></i>
                    <?php endfor; ?>
                </span>
            </p>
            <p><strong>Comentário:</strong> <?= nl2br(htmlspecialchars($avaliacao['comentario'] ?? '')) ?></p>
        </div>

        <form method="POST" action="<?= aptus_base() ?>/avaliacoes/salvar-resposta">
            <input type="hidden" name="avaliacao_id" value="<?= (int) ($avaliacao['id_avaliacao'] ?? 0) ?>">

            <div class="form-group">
                <label for="resposta">Sua resposta *</label>
                <textarea id="resposta" name="resposta" rows="4" placeholder="Escreva sua resposta ao cliente..." required></textarea>
            </div>

            <div class="avaliacao-acoes">
                <button type="submit" class="btn-enviar">
                    <i class="fas fa-paper-plane"></i> Enviar Resposta
                </button>
                <a href="<?= aptus_base() ?>/interesses/detalhes/<?= url_id((int) ($avaliacao['id_interesse'] ?? 0)) ?>" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
            <?= CsrfMiddleware::field() ?>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
