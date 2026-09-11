<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/auth/index.php

$tituloPagina = $tituloPagina ?? 'Login - Aptus';
$cssPagina = $cssPagina ?? 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$status = $_GET['status'] ?? '';
$mensagem = $_GET['mensagem'] ?? '';
$email = $_GET['email'] ?? '';
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Bem-vindo de volta</h2>
                <p>Faça login para acessar sua conta</p>
            </div>

            <!-- FLASH MESSAGES REMOVIDAS - USANDO SWEETALERT2 -->

            <?php if ($status === 'sucesso'): ?>
                <div id="flashData" data-tipo="sucesso" data-mensagem="<?= htmlspecialchars($mensagem) ?>" data-email="<?= htmlspecialchars($email) ?>"></div>
            <?php elseif ($status === 'erro'): ?>
                <div id="flashData" data-tipo="erro" data-mensagem="<?= htmlspecialchars($mensagem) ?>"></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash'])): ?>
                <div id="flashData" data-tipo="<?= $_SESSION['flash']['tipo'] ?>" data-mensagem="<?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>"></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="POST" action="/Aptus/login" class="auth-form" id="loginForm" onsubmit="return aceitarLgpdEContinuar(event)">
                <div class="form-group">
                    <label for="email">E-mail <span class="obrigatorio">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required aria-label="E-mail">
                </div>

                <div class="form-group">
                    <label for="senha">Senha <span class="obrigatorio">*</span></label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required aria-label="Senha">
                </div>

                <div class="form-group remember-group">
                    <label class="remember-check">
                        <input type="checkbox" name="lembrar" value="1">
                        <span>Lembrar-me</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="btnLogin">Entrar</button>
            <?= CsrfMiddleware::field() ?>
</form>

            <div class="auth-footer">
                <div class="auth-links">
                    <a href="/Aptus/auth/esqueci-senha">Esqueci minha senha</a>
                </div>
                <p>Não tem uma conta? <a href="/Aptus/login/cadastrar">Criar Conta</a></p>
            </div>
            <script>
            function aceitarLgpdEContinuar(e){
              if(document.getElementById('lgpd-modal').style.display!=='none'){
                alert('Por favor, concorde com os Termos, Privacidade e Cookies (LGPD) para continuar.');
                document.getElementById('lgpd-modal').style.display='flex';
                e.preventDefault();
                return false;
              }
              return true;
            }
            </script>

