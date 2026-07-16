<?php
$tituloPagina = 'Verificação de E-mail - RecycleWays';
$cssPagina = 'auth.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$status = $_GET['status'] ?? '';
$mensagem = $_GET['mensagem'] ?? '';

$isSuccess = $status === 'sucesso';
$isError = $status === 'erro' || empty($status);
?>

<section class="auth-section animate-in">
    <div class="auth-container">
        <div class="auth-card" style="text-align: center; max-width: 500px;">

            <?php if ($isSuccess): ?>
                <div style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 style="color: #065f46; margin-bottom: 0.5rem;">E-mail Verificado!</h1>
                <p style="color: var(--color-text); margin-bottom: 1.5rem;">
                    Seu e-mail foi verificado com sucesso. Agora você já pode fazer login na sua conta.
                </p>
                <a href="/RecycleWays/login" class="btn btn-new">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login
                </a>
            <?php else: ?>
                <div style="font-size: 4rem; color: #ef4444; margin-bottom: 1rem;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h1 style="color: #991b1b; margin-bottom: 0.5rem;">Erro na Verificação</h1>
                <p style="color: var(--color-text); margin-bottom: 1.5rem;">
                    <?= htmlspecialchars($mensagem ?? 'Token inválido ou expirado.') ?>
                </p>
                <p style="color: var(--color-text); font-size: 0.9rem; margin-bottom: 1.5rem;">
                    <i class="fas fa-lightbulb"></i> Dica: O link de verificação é válido por 24 horas. 
                    Se você perdeu o prazo, faça login e solicite um novo link.
                </p>
                <div style="display: flex; gap: 0.8rem; justify-content: center; flex-wrap: wrap;">
                    <a href="/RecycleWays/login" class="btn">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="/RecycleWays/auth/re-enviar?email=" class="btn btn-new">
                        <i class="fas fa-envelope"></i> Reenviar Link
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if ($isSuccess): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'E-mail verificado com sucesso!',
        text: 'Sua conta foi ativada. Agora você já pode fazer login.',
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Fazer Login',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/RecycleWays/login';
        }
    });
});
</script>
<?php elseif ($isError): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Erro na verificação',
        text: '<?= htmlspecialchars($mensagem ?? 'Token inválido ou expirado.') ?>',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Voltar',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/RecycleWays/login';
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>