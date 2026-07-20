<?php
// app/Views/auth/esqueci_senha.php

$tituloPagina = 'Esqueci a Senha - Aptus';
$cssPagina = 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <h2>Esqueci a Senha</h2>
                <p>Digite seu e-mail para receber as instrucoes</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
                    <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="POST" action="/Aptus/auth/enviar-token" class="auth-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Enviar Instrucoes</button>
            </form>

            <div class="auth-footer">
                <p><a href="/Aptus/login">Voltar para o login</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>