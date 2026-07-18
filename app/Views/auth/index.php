<?php
// app/Views/auth/index.php

// A sessão já é configurada no Controller, mas garantimos aqui também
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tituloPagina = $tituloPagina ?? 'Login - Aptus';
$cssPagina = $cssPagina ?? 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Bem-vindo de volta</h2>
                <p>Faça login para acessar sua conta</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-message flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
                    <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            
            <form method="POST" action="/Aptus/login" class="auth-form">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Entrar</button>
            </form>
            
            <div class="auth-footer">
                <p>Não tem uma conta? <a href="/Aptus/login/cadastrar">Criar Conta</a></p>
            </div>
            
            <!-- Área DEV - Login Rápido -->
            <?php if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1'): ?>
            <div class="auth-dev">
                <hr>
                <h3>🔧 Login Rápido (DEV)</h3>
                <div class="dev-buttons">
                    <button type="button" class="btn btn-sm" onclick="loginRapido('usuario')">Usuário</button>
                    <button type="button" class="btn btn-sm" onclick="loginRapido('moderador')">Moderador</button>
                    <button type="button" class="btn btn-sm" onclick="loginRapido('admin')">Admin</button>
                    <button type="button" class="btn btn-sm" onclick="loginRapido('master')">Master</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
const usuarios = {
    usuario: { email: "usuario@aptus.com", senha: "123" },
    moderador: { email: "moderador@aptus.com", senha: "123" },
    admin: { email: "admin@aptus.com", senha: "123" },
    master: { email: "master@aptus.com", senha: "123" }
};

function loginRapido(tipo) {
    document.getElementById("email").value = usuarios[tipo].email;
    document.getElementById("senha").value = usuarios[tipo].senha;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>