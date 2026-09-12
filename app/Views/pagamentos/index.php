<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/pagamentos/index.php

$tituloPagina = $tituloPagina ?? 'Pagamentos - Aptus';
$cssPagina = $cssPagina ?? 'pagamentos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="pagamento-section animate-in">
    <div class="pagamento-container">
        <div class="pagamento-header">
            <h2>Meus Pagamentos</h2>
            <p>Gerencie suas confirmações de pagamento</p>
        </div>

        <div class="pagamento-cards">
            <div class="pagamento-card">
                <h3>Confirmar como Contratante</h3>
                <p>Confirme o pagamento para liberar o serviço.</p>
                <form method="POST" action="/Aptus/pagamentos/confirmar-contratante" class="pagamento-form">
                    <input type="hidden" name="interesse_id" value="<?= htmlspecialchars($_GET['id'] ?? 1) ?>">
                    <?= CsrfMiddleware::field() ?>
                    <button type="submit" class="btn btn-primary">Confirmar Pagamento</button>
                </form>
            </div>

            <div class="pagamento-card">
                <h3>Confirmar como Freelancer</h3>
                <p>Confirme o recebimento do pagamento.</p>
                <form method="POST" action="/Aptus/pagamentos/confirmar-freelancer" class="pagamento-form">
                    <input type="hidden" name="interesse_id" value="<?= htmlspecialchars($_GET['id'] ?? 1) ?>">
                    <?= CsrfMiddleware::field() ?>
                    <button type="submit" class="btn btn-primary">Confirmar Recebimento</button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.pagamento-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const btn = this.querySelector('button');
        btn.innerText = 'Processando...';
        btn.disabled = true;
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
