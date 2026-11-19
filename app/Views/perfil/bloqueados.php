<?php
// app/Views/perfil/bloqueados.php — usuários que eu bloqueei
require_once __DIR__ . '/../../Helpers/UploadHelper.php';
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

$tituloPagina = $tituloPagina ?? 'Usuários bloqueados - Aptus';
$cssPagina    = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$bloqueados = $bloqueados ?? [];
?>

<div class="perfil-container bloqueados-container">
    <div class="perfil-header">
        <h1><i class="fas fa-user-slash"></i> Usuários bloqueados</h1>
    </div>
    <p class="bloqueados-explicacao">
        Com quem está bloqueado não é possível contratar nem prestar serviços, e as mensagens no chat ficam desativadas.
        O bloqueio vale nos dois sentidos.
    </p>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($bloqueados)): ?>
        <div class="perfil-card bloqueados-vazio">
            <i class="fas fa-check-circle"></i>
            <p>Você não bloqueou ninguém. Para bloquear, abra o perfil do usuário e use o botão "Bloquear".</p>
        </div>
    <?php else: ?>
        <ul class="bloqueados-lista">
            <?php foreach ($bloqueados as $b): ?>
                <li class="perfil-card bloqueado-item">
                    <img src="<?= htmlspecialchars(!empty($b['foto_perfil']) ? \UploadHelper::getUrl($b['foto_perfil']) : (aptus_base() . '/public/images/default-avatar.svg'), ENT_QUOTES, 'UTF-8') ?>"
                         alt="" class="bloqueado-foto">
                    <div class="bloqueado-info">
                        <a href="<?= aptus_base() ?>/perfil/publico/<?= url_id((int) $b['id_usuario']) ?>"><strong><?= htmlspecialchars($b['nome']) ?></strong></a>
                        <span class="username-tag">@<?= htmlspecialchars($b['username']) ?></span>
                        <small>Bloqueado em <?= date('d/m/Y', strtotime($b['data_criacao'])) ?></small>
                    </div>
                    <form method="POST" action="<?= aptus_base() ?>/usuarios/desbloquear">
                        <input type="hidden" name="usuario" value="<?= url_id((int) $b['id_usuario']) ?>">
                        <input type="hidden" name="voltar" value="lista">
                        <?= CsrfMiddleware::field() ?>
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-unlock"></i> Desbloquear</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
