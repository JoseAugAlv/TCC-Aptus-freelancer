<?php
// app/Views/auth/index.php

$tituloPagina = $tituloPagina ?? 'Login - Aptus';
$cssPagina = $cssPagina ?? 'login.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

// Buscar todos os usuários para login rápido
$pdo = Database::getConnection();
$sql = "SELECT id_usuario, nome, email, id_perfil FROM usuario WHERE ativo = 1 ORDER BY id_perfil, nome";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Bem-vindo de volta</h2>
                <p>Faça login para acessar sua conta</p>
            </div>

            <?php if (isset($_SESSION['flash'])): ?>
                <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
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
            
            <!-- Área DEV - Login Rápido com todos os usuários -->
            <div class="auth-dev">
                <hr>
                <h3>🔧 Login Rápido (DEV)</h3>
                <p class="dev-subtitle">Clique em um usuário para preencher as credenciais</p>
                <div class="dev-buttons">
                    <?php foreach ($usuarios as $user): 
                        $perfilNome = '';
                        switch ($user['id_perfil']) {
                            case 1: $perfilNome = 'Admin'; break;
                            case 2: $perfilNome = 'Moderador'; break;
                            case 4: $perfilNome = 'Master'; break;
                            default: $perfilNome = 'Usuário';
                        }
                        $cor = '';
                        switch ($user['id_perfil']) {
                            case 1: $cor = '#ef4444'; break;
                            case 2: $cor = '#f59e0b'; break;
                            case 4: $cor = '#8b5cf6'; break;
                            default: $cor = '#10b981';
                        }
                    ?>
                        <button type="button" class="btn-dev" 
                                onclick="preencherLogin('<?= $user['email'] ?>', '123')"
                                style="background: <?= $cor ?>;"
                                title="<?= htmlspecialchars($user['nome']) ?>">
                            <?= htmlspecialchars(explode(' ', $user['nome'])[0]) ?>
                            <span class="dev-badge"><?= $perfilNome ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function preencherLogin(email, senha) {
    document.getElementById("email").value = email;
    document.getElementById("senha").value = senha;
}
</script>

<style>
/* ===== ÁREA DEV ===== */
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
    margin-bottom: 6px;
    font-weight: 600;
}

.dev-subtitle {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 10px;
}

.dev-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-dev {
    padding: 6px 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 11px;
    transition: all 0.3s;
    color: #fff;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-dev:hover {
    transform: translateY(-2px);
    opacity: 0.85;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-dev:active {
    transform: scale(0.95);
}

.dev-badge {
    font-size: 0.55rem;
    opacity: 0.8;
    background: rgba(255,255,255,0.2);
    padding: 1px 6px;
    border-radius: 10px;
    font-weight: 400;
}

/* Responsivo */
@media (max-width: 480px) {
    .dev-buttons {
        gap: 4px;
    }
    
    .btn-dev {
        font-size: 10px;
        padding: 4px 8px;
    }
    
    .dev-badge {
        font-size: 0.5rem;
        padding: 1px 4px;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>