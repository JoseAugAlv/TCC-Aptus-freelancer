<?php
// app/Views/auth/cadastrar.php

$tituloPagina = 'Criar Conta - Aptus';
$cssPagina = 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Criar Conta</h2>
                <p>Preencha os dados para se cadastrar</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
                    <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="POST" action="/Aptus/login/salvar" class="auth-form" onsubmit="return validarSenha()">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Seu nome completo" required>
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Minimo 6 caracteres" required minlength="6" onkeyup="verificarRequisitosSenha()">
                    <div id="requisitos-senha" style="margin-top: 8px; font-size: 0.85rem;"></div>
                </div>

                <div class="form-group">
                    <label for="senha_confirm">Confirmar Senha</label>
                    <input type="password" id="senha_confirm" name="senha_confirm" class="form-control" placeholder="Repita a senha" required minlength="6" onkeyup="verificarConfirmacaoSenha()">
                    <div id="confirmacao-senha" style="margin-top: 8px; font-size: 0.85rem;"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="btnCadastrar">Cadastrar</button>
            </form>

            <div class="auth-footer">
                <p>Já tem conta? <a href="/Aptus/login">Faça login</a></p>
            </div>
        </div>
    </div>
</section>

<script>
function verificarRequisitosSenha() {
    var senha = document.getElementById('senha').value;
    var requisitos = document.getElementById('requisitos-senha');
    
    var criterios = [
        { regex: /.{6,}/, label: 'Minimo 6 caracteres', ok: false },
        { regex: /[A-Z]/, label: 'Pelo menos 1 letra maiuscula', ok: false },
        { regex: /[a-z]/, label: 'Pelo menos 1 letra minuscula', ok: false },
        { regex: /[0-9]/, label: 'Pelo menos 1 numero', ok: false },
        { regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/, label: 'Pelo menos 1 caractere especial', ok: false }
    ];
    
    var html = '<ul style="list-style: none; padding: 0; margin: 0;">';
    var todosOk = true;
    
    criterios.forEach(function(c) {
        c.ok = c.regex.test(senha);
        if (!c.ok) todosOk = false;
        var cor = c.ok ? '#10b981' : '#ef4444';
        var icone = c.ok ? '✓' : '✗';
        html += '<li style="color: ' + cor + '; padding: 2px 0;">' + icone + ' ' + c.label + '</li>';
    });
    
    html += '</ul>';
    
    if (senha.length > 0) {
        requisitos.innerHTML = html;
    } else {
        requisitos.innerHTML = '';
    }
    
    // Habilitar/desabilitar botao
    document.getElementById('btnCadastrar').disabled = !todosOk;
    if (senha.length > 0) {
        verificarConfirmacaoSenha();
    }
}

function verificarConfirmacaoSenha() {
    var senha = document.getElementById('senha').value;
    var confirm = document.getElementById('senha_confirm').value;
    var div = document.getElementById('confirmacao-senha');
    
    if (confirm.length === 0) {
        div.innerHTML = '';
        return;
    }
    
    if (senha === confirm) {
        div.innerHTML = '<span style="color: #10b981;">✓ As senhas coincidem</span>';
    } else {
        div.innerHTML = '<span style="color: #ef4444;">✗ As senhas nao coincidem</span>';
    }
}

function validarSenha() {
    var senha = document.getElementById('senha').value;
    var confirm = document.getElementById('senha_confirm').value;
    
    var criterios = [
        { regex: /.{6,}/, ok: false },
        { regex: /[A-Z]/, ok: false },
        { regex: /[a-z]/, ok: false },
        { regex: /[0-9]/, ok: false },
        { regex: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/, ok: false }
    ];
    
    criterios.forEach(function(c) {
        c.ok = c.regex.test(senha);
    });
    
    var todosOk = criterios.every(function(c) { return c.ok; });
    
    if (!todosOk) {
        alert('A senha deve atender a todos os criterios de seguranca:\n- Minimo 6 caracteres\n- Pelo menos 1 letra maiuscula\n- Pelo menos 1 letra minuscula\n- Pelo menos 1 numero\n- Pelo menos 1 caractere especial');
        return false;
    }
    
    if (senha !== confirm) {
        alert('As senhas nao coincidem!');
        return false;
    }
    
    return true;
}

// Verificar requisitos ao carregar a pagina
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar estados
    verificarRequisitosSenha();
});
</script>

<style>
/* Estilos para os requisitos */
#requisitos-senha ul {
    background: #f8fafc;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

#requisitos-senha li {
    font-size: 0.8rem;
    padding: 2px 0;
}

#btnCadastrar:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

#btnCadastrar:disabled:hover {
    transform: none;
    box-shadow: none;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>