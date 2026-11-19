<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/avaliacoes/criar.php

$tituloPagina = $tituloPagina ?? 'Avaliar Serviço - Aptus';
$cssPagina = $cssPagina ?? 'avaliacoes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesse = $interesse ?? [];
$anuncio = $anuncio ?? [];
?>

<div class="avaliacao-container">
    <div class="avaliacao-header">
        <h1><i class="fas fa-star"></i> Avaliar Serviço</h1>
        <p>Compartilhe sua experiência com o freelancer</p>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="avaliacao-card">
        <div class="avaliacao-info">
            <p><strong>Serviço:</strong> <?= htmlspecialchars($anuncio['titulo'] ?? '') ?></p>
            <p><strong>Freelancer:</strong> <?= htmlspecialchars($interesse['freelancer_nome'] ?? '') ?></p>
            <p><strong>Preço:</strong> R$ <?= number_format($anuncio['preco'] ?? 0, 2, ',', '.') ?></p>
        </div>

        <form method="POST" action="<?= aptus_base() ?>/avaliacoes/salvar">
            <input type="hidden" name="interesse_id" value="<?= (int) ($interesse['id_interesse'] ?? 0) ?>">

            <fieldset class="form-group avaliacao-nota">
                <legend>Nota *</legend>
                <div class="avaliacao-estrelas">
                    <?php for ($n = 5; $n >= 1; $n--): ?>
                        <input type="radio" id="nota<?= $n ?>" name="nota" value="<?= $n ?>" <?= $n === 1 ? 'required' : '' ?>>
                        <label for="nota<?= $n ?>" title="<?= $n ?> estrela<?= $n > 1 ? 's' : '' ?>">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            <span class="sr-only"><?= $n ?> estrela<?= $n > 1 ? 's' : '' ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </fieldset>

            <div class="form-group">
                <label for="comentario">Comentário</label>
                <textarea id="comentario" name="comentario" rows="4" placeholder="Descreva sua experiência com o serviço..."></textarea>
            </div>

            <div class="avaliacao-acoes">
                <button type="submit" class="btn-enviar">
                    <i class="fas fa-paper-plane"></i> Enviar Avaliação
                </button>
                <a href="<?= aptus_base() ?>/interesses/detalhes/<?= url_id((int) ($interesse['id_interesse'] ?? 0)) ?>" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
            <?= CsrfMiddleware::field() ?>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
