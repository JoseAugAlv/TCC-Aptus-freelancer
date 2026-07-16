<?php
$tituloPagina = 'Redefinir Senha - RecycleWays';
$cssPagina = 'auth.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
    <section class="auth-section animate-in">
        <div class="auth-container">
            <div class="auth-card" style="text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                <h2>Senha redefinida!</h2>
                <p>Sua senha foi alterada com sucesso.</p>
                <a href="/RecycleWays/login" class="btn btn-primary" style="margin-top: 1.5rem;">Fazer login</a>
            </div>
        </div>
    </section>
<?php else: ?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Redefinir Senha</h2>
                <p>Digite sua nova senha</p>
            </div>

            <form action="/RecycleWays/auth/redefinir-senha" method="POST" class="auth-form" onsubmit="return validarSenha()">
                <?= ViewHelper::csrfField() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                
                <div class="form-group">
                    <label for="senha">Nova Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6" onkeyup="atualizarRequisitosSenha()">
                    <div id="requisitos_senha" style="margin-top:5px;"></div>
                </div>
                
                <div class="form-group">
                    <label for="senha_confirm">Confirmar Nova Senha</label>
                    <input type="password" id="senha_confirm" name="senha_confirm" class="form-control" placeholder="Repita a senha" required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary btn-full">Redefinir Senha</button>
            </form>

            <div class="auth-footer">
                <p><a href="/RecycleWays/login">Voltar para o login</a></p>
            </div>
        </div>
    </div>
</section>

<script>
function validarSenha() {
    var senha = document.getElementById('senha').value;
    var senhaConfirm = document.getElementById('senha_confirm').value;
    
    if (senha === '' && senhaConfirm === '') {
        return true;
    }
    
    if (senha !== senhaConfirm) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: 'As senhas não coincidem!',
            confirmButtonColor: '#dc3545'
        });
        return false;
    }
    
    if (senha.length < 6) {
        Swal.fire({
            icon: 'error',
            title: 'Senha fraca!',
            text: 'A senha deve ter no mínimo 6 caracteres.',
            confirmButtonColor: '#dc3545'
        });
        return false;
    }
    
    return true;
}

function atualizarRequisitosSenha() {
    var senha = document.getElementById('senha').value;
    var requisitos = document.getElementById('requisitos_senha');
    
    if (senha === '') {
        requisitos.innerHTML = '';
        return;
    }
    
    var html = '<ul style="list-style:none; padding:0; font-size:12px; margin:5px 0;">';
    
    var ok = senha.length >= 6;
    html += '<li style="color:' + (ok ? 'green' : 'red') + ';">' + (ok ? '✅' : '❌') + ' Mínimo 6 caracteres</li>';
    
    ok = /[A-Z]/.test(senha);
    html += '<li style="color:' + (ok ? 'green' : 'red') + ';">' + (ok ? '✅' : '❌') + ' Letra maiúscula</li>';
    
    ok = /[a-z]/.test(senha);
    html += '<li style="color:' + (ok ? 'green' : 'red') + ';">' + (ok ? '✅' : '❌') + ' Letra minúscula</li>';
    
    ok = /[0-9]/.test(senha);
    html += '<li style="color:' + (ok ? 'green' : 'red') + ';">' + (ok ? '✅' : '❌') + ' Número</li>';
    
    ok = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(senha);
    html += '<li style="color:' + (ok ? 'green' : 'red') + ';">' + (ok ? '✅' : '❌') + ' Caractere especial</li>';
    
    html += '</ul>';
    requisitos.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    atualizarRequisitosSenha();
});
</script>

<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>