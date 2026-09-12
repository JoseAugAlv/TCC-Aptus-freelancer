<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/pagamentos/confirmar.php

$tituloPagina = $tituloPagina ?? 'Confirmar Pagamento - Aptus';
$cssPagina = $cssPagina ?? 'pagamentos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="pagamento-section animate-in">
    <div class="pagamento-container">
        <div class="pagamento-header">
            <h2>Confirmar Pagamento</h2>
            <p>Verifique os detalhes antes de confirmar</p>
        </div>

        <div class="pagamento-detalhes">
            <div class="detalhe-row">
                <span class="label">Interesse:</span>
                <span class="valor">#<?= htmlspecialchars($_GET['id'] ?? 'N/A') ?></span>
            </div>
            <div class="detalhe-row">
                <span class="label">Contratante:</span>
                <span class="valor"><?= htmlspecialchars($_SESSION['usuario']['nome'] ?? 'Você') ?></span>
            </div>
            <div class="detalhe-row">
                <span class="label">Freelancer:</span>
                <span class="valor">Verificado</span>
            </div>
        </div>

        <form method="POST" action="/Aptus/pagamentos/confirmar-contratante" class="pagamento-form-confirmar">
            <input type="hidden" name="interesse_id" value="<?= htmlspecialchars($_GET['id'] ?? 1) ?>">
            <?= CsrfMiddleware::field() ?>
            <div class="form-actions">
                <a href="/Aptus/pagamentos" class="btn btn-secondary">Voltar</a>
                <button type="submit" class="btn btn-primary btn-full" onclick="this.innerText='Confirmado'; this.disabled=true;">Confirmar Pagamento</button>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
