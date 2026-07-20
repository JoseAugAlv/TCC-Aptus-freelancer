<?php
// app/Views/auth/redefinir_senha.php

$tituloPagina = 'Redefinir Senha - Aptus';
$cssPagina = 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$token = $_GET['token'] ?? '';
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <h2>Redefinir Senha</h2>
                <p>Digite sua nova senha</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div id="flashData" data-tipo="<?= $_SESSION['flash']['tipo'] ?>" data-mensagem="<?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>"></div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>

            <form method="POST" action="/Aptus/auth/redefinir-senha" class="auth-form" id="formRedefinir" onsubmit="return validarSenha()">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label for="senha">Nova Senha <span class="obrigatorio">*</span></label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="Minimo 6 caracteres" required minlength="6" onkeyup="verificarRequisitosSenha()">
                    <div id="requisitos-senha" style="margin-top: 8px; font-size: 0.85rem;"></div>
                </div>

                <div class="form-group">
                    <label for="senha_confirm">Confirmar Senha <span class="obrigatorio">*</span></label>
                    <input type="password" id="senha_confirm" name="senha_confirm" class="form-control" placeholder="Repita a senha" required minlength="6" onkeyup="verificarConfirmacaoSenha()">
                    <div id="confirmacao-senha" style="margin-top: 8px; font-size: 0.85rem;"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="btnRedefinir">Redefinir Senha</button>
            </form>

            <div class="auth-footer">
                <p><a href="/Aptus/login">Voltar para o login</a></p>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ============================================================
// VERIFICAR REQUISITOS DE SENHA
// ============================================================
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
    
    var html = '<ul style="list-style: none; padding: 8px 12px; margin: 0; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">';
    var todosOk = true;
    
    criterios.forEach(function(c) {
        c.ok = c.regex.test(senha);
        if (!c.ok) todosOk = false;
        var cor = c.ok ? '#10b981' : '#ef4444';
        var icone = c.ok ? '✓' : '✗';
        html += '<li style="color: ' + cor + '; padding: 3px 0; font-weight: 500; font-size: 0.8rem;">' + icone + ' ' + c.label + '</li>';
    });
    
    html += '</ul>';
    
    if (senha.length > 0) {
        requisitos.innerHTML = html;
    } else {
        requisitos.innerHTML = '';
    }
    
    // Habilitar/desabilitar botao
    var btn = document.getElementById('btnRedefinir');
    btn.disabled = !todosOk;
    
    if (senha.length > 0) {
        verificarConfirmacaoSenha();
    }
}

// ============================================================
// VERIFICAR CONFIRMACAO DE SENHA
// ============================================================
function verificarConfirmacaoSenha() {
    var senha = document.getElementById('senha').value;
    var confirm = document.getElementById('senha_confirm').value;
    var div = document.getElementById('confirmacao-senha');
    
    if (confirm.length === 0) {
        div.innerHTML = '';
        return;
    }
    
    if (senha === confirm) {
        div.innerHTML = '<span style="color: #10b981; font-weight: 500;">✓ As senhas coincidem</span>';
    } else {
        div.innerHTML = '<span style="color: #ef4444; font-weight: 500;">✗ As senhas nao coincidem</span>';
    }
}

// ============================================================
// VALIDAR SENHA ANTES DE ENVIAR
// ============================================================
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
        Swal.fire({
            icon: 'warning',
            title: 'Senha fraca!',
            html: 'A senha deve atender a todos os criterios de seguranca:<br><br>' +
                  '• Minimo 6 caracteres<br>' +
                  '• Pelo menos 1 letra maiuscula<br>' +
                  '• Pelo menos 1 letra minuscula<br>' +
                  '• Pelo menos 1 numero<br>' +
                  '• Pelo menos 1 caractere especial',
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    if (senha !== confirm) {
        Swal.fire({
            icon: 'warning',
            title: 'Senhas nao coincidem!',
            text: 'As senhas digitadas nao sao iguais.',
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    // Desabilitar botao para evitar duplo clique
    var btn = document.getElementById('btnRedefinir');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redefinindo...';
    
    return true;
}

// ============================================================
// FLASH MESSAGES COM SWEETALERT2
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    var flashData = document.getElementById('flashData');
    
    if (flashData) {
        var tipo = flashData.dataset.tipo;
        var mensagem = flashData.dataset.mensagem;
        
        if (tipo === 'sucesso') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: mensagem,
                confirmButtonColor: '#006577',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = '/Aptus/login';
            });
        } else if (tipo === 'erro') {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: mensagem,
                confirmButtonColor: '#006577',
                confirmButtonText: 'OK'
            });
        } else if (tipo === 'aviso') {
            Swal.fire({
                icon: 'warning',
                title: 'Aviso!',
                text: mensagem,
                confirmButtonColor: '#006577',
                confirmButtonText: 'OK'
            });
        }
    }
    
    // Inicializar verificacao de requisitos
    verificarRequisitosSenha();
});
</script>

<style>
/* Estilos adicionais para os requisitos */
#requisitos-senha ul {
    list-style: none;
    padding: 8px 12px;
    margin: 0;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

#requisitos-senha li {
    padding: 3px 0;
    font-weight: 500;
    font-size: 0.8rem;
}

#confirmacao-senha {
    margin-top: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

#btnRedefinir:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

#btnRedefinir:disabled:hover {
    transform: none;
    box-shadow: none;
}

.form-control.error {
    border-color: #ef4444;
    background: #fef2f2;
}

.form-control.success {
    border-color: #10b981;
    background: #ecfdf5;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>