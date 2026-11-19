<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
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

            <?php $googleTexto = 'Cadastrar com Google'; $googleDivisoriaAntes = false; require __DIR__ . '/../partials/botao_google.php'; ?>

            <form method="POST" action="<?= aptus_base() ?>/login/salvar" class="auth-form"
                onsubmit="return validarSenha() && aceitarLgpdEContinuarCadastrar(event)">
                <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" id="nome" name="nome" class="form-control" placeholder="Seu nome completo" required
                           value="<?= htmlspecialchars((string) ($_SESSION['cadastro_old']['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <?php $__old = $_SESSION['cadastro_old'] ?? []; unset($_SESSION['cadastro_old']);
                      $__o = fn($k) => htmlspecialchars((string) ($__old[$k] ?? ''), ENT_QUOTES, 'UTF-8'); ?>

                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <div class="input-prefixo">
                        <span aria-hidden="true">@</span>
                        <input type="text" id="username" name="username" class="form-control" required
                               minlength="3" maxlength="30" pattern="[a-z0-9._]{3,30}" autocomplete="username"
                               placeholder="seu.usuario" value="<?= $__o('username') ?>" data-username-check
                               aria-describedby="usernameAjuda usernameStatus">
                    </div>
                    <small id="usernameAjuda" class="campo-ajuda">Único, como no Instagram: letras minúsculas, números, ponto ou sublinhado. Diferencia você de pessoas com o mesmo nome.</small>
                    <small id="usernameStatus" class="campo-status" role="status" aria-live="polite"></small>
                </div>

                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required value="<?= $__o('email') ?>">
                </div>

                <div class="form-row-2">
                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" class="form-control" inputmode="numeric" required
                               placeholder="000.000.000-00" maxlength="14" value="<?= $__o('cpf') ?>" data-cpf-check>
                        <small class="campo-status" data-cpf-status role="status" aria-live="polite"></small>
                    </div>
                    <div class="form-group">
                        <label for="data_nascimento">Data de nascimento</label>
                        <input type="text" id="data_nascimento" name="data_nascimento" class="form-control" required
                               inputmode="numeric" placeholder="dd/mm/aaaa" maxlength="10" value="<?= $__o('data_nascimento') ?>" data-idade-check>
                        <small class="campo-status" data-idade-status role="status" aria-live="polite"></small>
                    </div>
                </div>
                <p class="campo-ajuda">É preciso ter 18 anos ou mais para usar a Aptus. CPF e data de nascimento são verificados e nunca aparecem no seu perfil.</p>

                <?php $endereco = $__old; $enderecoObrigatorio = true; require __DIR__ . '/../partials/endereco.php'; ?>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 8 caracteres" required minlength="8" onkeyup="verificarRequisitosSenha()">
                    <div id="requisitos-senha" style="margin-top: 8px; font-size: 0.85rem;"></div>
                </div>

                <div class="form-group">
                    <label for="senha_confirm">Confirmar Senha</label>
                    <input type="password" id="senha_confirm" name="senha_confirm" class="form-control" placeholder="Repita a senha" required minlength="8" onkeyup="verificarConfirmacaoSenha()">
                    <div id="confirmacao-senha" style="margin-top: 8px; font-size: 0.85rem;"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="btnCadastrar">Cadastrar</button>
            <?= CsrfMiddleware::field() ?>
</form>

            <div class="auth-footer">
                <p>Já tem conta? <a href="<?= aptus_base() ?>/login">Faça login</a></p>
            </div>
        </div>
    </div>
</section>

<script>
function aceitarLgpdEContinuarCadastrar(e){
  if(document.getElementById('lgpd-modal').style.display!=='none'){
    alert('Por favor, concorde com os Termos, Privacidade e Cookies (LGPD) para cadastrar.');
    document.getElementById('lgpd-modal').style.display='flex';
    e.preventDefault();
    return false;
  }
  return true;
}
function verificarRequisitosSenha() {
    var senha = document.getElementById('senha').value;
    var requisitos = document.getElementById('requisitos-senha');
    
    var criterios = [
        { regex: /.{8,}/, label: 'Minimo 8 caracteres', ok: false },
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
        div.innerHTML = '<span style="color: var(--cor-sucesso);">✓ As senhas coincidem</span>';
    } else {
        div.innerHTML = '<span style="color: var(--cor-erro);">✗ As senhas não coincidem</span>';
    }
}

function validarSenha() {
    var senha = document.getElementById('senha').value;
    var confirm = document.getElementById('senha_confirm').value;
    
    var criterios = [
        { regex: /.{8,}/, ok: false },
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
        alert('A senha deve atender a todos os critérios de segurança:\n- Mínimo 8 caracteres\n- Pelo menos 1 letra maiúscula\n- Pelo menos 1 letra minúscula\n- Pelo menos 1 número\n- Pelo menos 1 caractere especial');
        return false;
    }
    
    if (senha !== confirm) {
        alert('As senhas não coincidem!');
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
    background: var(--tema-fundo-alt, #f8fafc);
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid var(--tema-borda, #e2e8f0);
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

<?php $jsPagina = ['cep.js', 'cadastro.js']; require_once __DIR__ . '/../layouts/footer.php'; ?>