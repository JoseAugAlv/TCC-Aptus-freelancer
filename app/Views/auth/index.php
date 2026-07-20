<?php
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

            <form method="POST" action="/Aptus/login" class="auth-form" id="loginForm">
                <div class="form-group">
                    <label for="email">E-mail <span class="obrigatorio">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha <span class="obrigatorio">*</span></label>
                    <input type="password" id="senha" name="senha" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary btn-full" id="btnLogin">Entrar</button>
            </form>

            <div class="auth-footer">
                <div class="auth-links">
                    <a href="/Aptus/auth/esqueci-senha">Esqueci minha senha</a>
                </div>
                <p>Não tem uma conta? <a href="/Aptus/login/cadastrar">Criar Conta</a></p>
            </div>

            <!-- Area DEV - Login Rapido -->
            <?php
            $pdo = Database::getConnection();
            $sql = "SELECT id_usuario, nome, email, id_perfil FROM usuario WHERE ativo = 1 ORDER BY id_perfil, nome";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if (!empty($usuarios)): ?>
                <div class="auth-dev">
                    <h3><i class="fas fa-code"></i> Login Rápido (DEV)</h3>
                    <div class="dev-buttons">
                        <?php foreach ($usuarios as $user): 
                            $perfilNome = '';
                            $cor = '';
                            switch ($user['id_perfil']) {
                                case 1: $perfilNome = 'Admin'; $cor = '#ef4444'; break;
                                case 2: $perfilNome = 'Mod'; $cor = '#f59e0b'; break;
                                case 4: $perfilNome = 'Master'; $cor = '#8b5cf6'; break;
                                default: $perfilNome = 'User'; $cor = '#10b981';
                            }
                        ?>
                            <button type="button" class="btn-dev" 
                                    onclick="preencherLogin('<?= $user['email'] ?>', '123')"
                                    style="background: <?= $cor ?>;">
                                <?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?>
                                <span class="dev-badge"><?= $perfilNome ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function preencherLogin(email, senha) {
    document.getElementById("email").value = email;
    document.getElementById("senha").value = senha;
}

document.addEventListener('DOMContentLoaded', function() {
    var flashData = document.getElementById('flashData');
    
    if (flashData) {
        var tipo = flashData.dataset.tipo;
        var mensagem = flashData.dataset.mensagem;
        var email = flashData.dataset.email || '';
        
        if (tipo === 'sucesso') {
            Swal.fire({
                icon: 'success',
                title: 'Sucesso!',
                text: mensagem,
                confirmButtonColor: '#006577',
                confirmButtonText: 'OK'
            }).then(function() {
                // Se foi sucesso de verificacao, mostrar opcao de reenviar
                if (email) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Não recebeu o e-mail?',
                        html: 'Clique no botão abaixo para reenviar o e-mail de verificação.',
                        showCancelButton: true,
                        confirmButtonColor: '#006577',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Reenviar E-mail',
                        cancelButtonText: 'Fechar'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            window.location.href = '/Aptus/auth/reenviar-verificacao?email=' + encodeURIComponent(email);
                        }
                    });
                }
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
});

// Validar formulario antes de enviar
document.getElementById('loginForm').addEventListener('submit', function(e) {
    var email = document.getElementById('email').value.trim();
    var senha = document.getElementById('senha').value.trim();
    
    if (!email || !senha) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Campos vazios!',
            text: 'Preencha todos os campos para fazer login.',
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK'
        });
        return false;
    }
    
    // Desabilitar botao para evitar duplo clique
    var btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entrando...';
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>