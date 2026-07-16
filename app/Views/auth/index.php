<?php
// app/Views/auth/index.php

$tituloPagina = $tituloPagina ?? 'Login - Aptus';
$cssPagina = $cssPagina ?? 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Bem-vindo de volta</h2>
                <p>Faça login para acessar sua conta</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-erro">
                    <?= htmlspecialchars($_SESSION['flash']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            
            <form method="POST" action="/Aptus/login">
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Entrar</button>
            </form>
            
            <div class="auth-footer">
                <p>Não tem uma conta? <a href="/Aptus/login/cadastrar">Criar Conta</a></p>
            </div>
            
            <!-- Área DEV - Login Rápido -->
            <div class="auth-dev">
                <hr>
                <h3>🔧 Login Rápido (DEV)</h3>
                <div class="dev-buttons">
                    <button type="button" onclick="loginRapido('usuario')">Usuário</button>
                    <button type="button" onclick="loginRapido('moderador')">Moderador</button>
                    <button type="button" onclick="loginRapido('admin')">Admin</button>
                    <button type="button" onclick="loginRapido('master')">Master</button>
                </div>
            </div>
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
    document.getElementById('email').value = usuarios[tipo].email;
    document.getElementById('senha').value = usuarios[tipo].senha;
    document.querySelector('form').submit();
}
</script>

<style>
.auth-section {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 100px 20px;
    background: #f5f7fa;
}

.auth-container {
    width: 100%;
    max-width: 420px;
}

.auth-card {
    background: #fff;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.1);
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header h2 {
    font-size: 24px;
    color: #006577;
    margin-bottom: 8px;
}

.auth-header p {
    color: #666;
    font-size: 14px;
}

.flash-erro {
    background: #f8d7da;
    color: #721c24;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 14px;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #006577;
}

.btn {
    width: 100%;
    padding: 14px;
    background: #006577;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn:hover {
    background: #004d5c;
}

.auth-footer {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.auth-footer a {
    color: #006577;
    text-decoration: none;
    font-weight: 600;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.auth-dev {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.auth-dev hr {
    display: none;
}

.auth-dev h3 {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.dev-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.dev-buttons button {
    flex: 1;
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 12px;
    transition: opacity 0.3s;
}

.dev-buttons button:hover {
    opacity: 0.8;
}

.dev-buttons button:nth-child(1) { background: #10b981; color: #fff; }
.dev-buttons button:nth-child(2) { background: #f59e0b; color: #fff; }
.dev-buttons button:nth-child(3) { background: #3b82f6; color: #fff; }
.dev-buttons button:nth-child(4) { background: #8b5cf6; color: #fff; }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>