<?php
$tituloPagina = 'Criar Conta - RecycleWays';
$cssPagina = 'auth.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

// Verificar se veio com sucesso
$success = isset($_GET['success']) && $_GET['success'] == 1;

if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Criar Conta</h2>
                <p>Preencha os dados para se cadastrar</p>
            </div>

            <form action="/RecycleWays/login/salvar" method="POST" class="auth-form" onsubmit="return validarFormulario()">
                <?= ViewHelper::csrfField() ?>
                <!-- Dados Pessoais -->
                <div class="auth-grid">
                    <div class="form-group form-group-full">
                        <label for="nome">Nome completo</label>
                        <input type="text" id="nome" name="nome" class="form-control" placeholder="Seu nome completo" required>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6" onkeyup="atualizarRequisitosSenha()">
                        <div id="requisitos_senha" style="margin-top:5px;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="senha_confirm">Confirmar Senha</label>
                        <input type="password" id="senha_confirm" name="senha_confirm" class="form-control" placeholder="Repita a senha" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" maxlength="15">
                    </div>
                    
                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" class="form-control">
                    </div>
                </div>

                <!-- Tipo de Usuário -->
                <div class="form-group">
                    <label>Tipo de Usuário</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="tipo_usuario" value="aluno" checked onchange="toggleCamposAluno()">
                            Aluno
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="tipo_usuario" value="professor" onchange="toggleCamposAluno()">
                            Professor
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="tipo_usuario" value="coordenador" onchange="toggleCamposAluno()">
                            Coordenador
                        </label>
                    </div>
                </div>

                <!-- Dados do Aluno -->
                <div id="campos_aluno" class="campos-aluno">
                    <h4>Dados do Aluno</h4>
                    
                    <div class="auth-grid">
                        <div class="form-group">
                            <label for="rm">RM</label>
                            <input type="text" id="rm" name="rm" class="form-control" placeholder="12345" maxlength="5">
                        </div>
                        
                        <div class="form-group">
                            <label for="ano_escolar">Ano Escolar</label>
                            <select id="ano_escolar" name="ano_escolar" class="form-control">
                                <option value="">Selecione</option>
                                <option value="1">1º Ano</option>
                                <option value="2">2º Ano</option>
                                <option value="3">3º Ano</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_curso">Curso</label>
                            <select id="id_curso" name="id_curso" class="form-control">
                                <option value="">Selecione</option>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id_curso'] ?>"><?= htmlspecialchars($curso['nome']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_periodo">Período</label>
                            <select id="id_periodo" name="id_periodo" class="form-control">
                                <option value="">Selecione</option>
                                <?php foreach ($periodos as $periodo): ?>
                                    <option value="<?= $periodo['id_periodo'] ?>"><?= htmlspecialchars($periodo['periodo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label for="ano_ingresso">Ano da Matrícula</label>
                            <input type="number" id="ano_ingresso" name="ano_ingresso" class="form-control" placeholder="2026" min="2000" max="2099">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-full">Cadastrar</button>
            </form>

            <div class="auth-footer">
                <p>Já tem conta? <a href="/RecycleWays/login">Faça login</a></p>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($success): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Cadastro realizado com sucesso!',
        html: 'Um e-mail de verificação foi enviado para <strong><?= htmlspecialchars($_GET['email'] ?? 'seu e-mail') ?></strong>.<br><br>Por favor, verifique sua caixa de entrada e clique no link de confirmação para ativar sua conta.',
        confirmButtonColor: '#10b981',
        confirmButtonText: 'OK, entendi!',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showClass: {
            popup: 'animate__animated animate__fadeInUp'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutDown'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/RecycleWays/login';
        }
    });
});
</script>
<?php endif; ?>

<script>
// ============================================================
// MÁSCARA DE TELEFONE (SALVA SEM MÁSCARA)
// ============================================================
document.getElementById('telefone').addEventListener('input', function(e) {
    var valor = e.target.value.replace(/\D/g, '');
    
    if (valor.length === 0) {
        e.target.value = '';
        return;
    }
    
    var valorFormatado = '';
    
    if (valor.length <= 2) {
        valorFormatado = '(' + valor;
    } else if (valor.length <= 6) {
        valorFormatado = '(' + valor.substring(0, 2) + ') ' + valor.substring(2);
    } else if (valor.length <= 10) {
        valorFormatado = '(' + valor.substring(0, 2) + ') ' + valor.substring(2, 6) + '-' + valor.substring(6);
    } else {
        valorFormatado = '(' + valor.substring(0, 2) + ') ' + valor.substring(2, 7) + '-' + valor.substring(7, 11);
    }
    
    e.target.value = valorFormatado;
});

// ============================================================
// TOGGLE CAMPOS DO ALUNO
// ============================================================
function toggleCamposAluno() {
    var camposAluno = document.getElementById('campos_aluno');
    var radios = document.querySelectorAll('input[name="tipo_usuario"]');
    var isAluno = false;
    var inputs = camposAluno.querySelectorAll('input, select');
    
    radios.forEach(function(radio) {
        if (radio.checked && radio.value === 'aluno') {
            isAluno = true;
        }
    });
    
    // Mostrar/esconder campos
    camposAluno.style.display = isAluno ? 'block' : 'none';
    
    // Habilitar/desabilitar required
    inputs.forEach(function(input) {
        if (isAluno) {
            input.setAttribute('required', 'required');
        } else {
            input.removeAttribute('required');
        }
    });
}

// ============================================================
// VALIDAÇÃO DO FORMULÁRIO
// ============================================================
function validarFormulario() {
    var senha = document.getElementById('senha').value;
    var senhaConfirm = document.getElementById('senha_confirm').value;
    
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
    
    var telefone = document.getElementById('telefone').value;
    if (telefone && telefone.replace(/\D/g, '').length < 10) {
        Swal.fire({
            icon: 'error',
            title: 'Telefone inválido!',
            text: 'O telefone deve ter pelo menos 10 dígitos (DDD + número).',
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
    toggleCamposAluno();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>