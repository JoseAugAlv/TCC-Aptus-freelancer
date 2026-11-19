<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/denuncias/criar.php

$tituloPagina = $tituloPagina ?? 'Denunciar - Aptus';
$cssPagina = $cssPagina ?? 'criar_denun.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

// $tipo, $id (já decodificado), $alvoNome e $motivos vêm do DenunciaController::criar()
$tipo     = $tipo ?? '';
$id       = (int) ($id ?? 0);
$alvoNome = $alvoNome ?? '';
$motivos  = $motivos ?? [];
?>
 
<div class="denuncia-container">
    <div class="denuncia-header">
        <h1><i class="fas fa-flag"></i> Denunciar</h1>
        <p>Denuncie conteúdo inadequado para a moderação</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="denuncia-card">
        <form method="POST" action="<?= aptus_base() ?>/denuncias/salvar" enctype="multipart/form-data">
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars(url_id($id), ENT_QUOTES, 'UTF-8') ?>">

            <div class="denuncia-info">
                <p><strong>Tipo:</strong> <?= $tipo === 'anuncio' ? 'Anúncio' : 'Perfil' ?></p>
                <?php if ($alvoNome !== ''): ?>
                <p><strong><?= $tipo === 'anuncio' ? 'Serviço' : 'Usuário' ?>:</strong> <?= htmlspecialchars($alvoNome, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="motivo">Motivo *</label>
                <select id="motivo" name="motivo" required>
                    <option value="">Selecione um motivo</option>
                    <?php foreach ($motivos as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>">
                            <?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição (opcional)</label>
                <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva detalhadamente o problema..."></textarea>
            </div>

            <div class="form-group">
                <label for="anexos">Fotos ou comprovantes (opcional)</label>
                <input type="file" id="anexos" name="anexos[]" multiple
                       accept="image/jpeg,image/png,image/webp,application/pdf" data-max-anexos="3">
                <p class="foto-helper">Até 3 arquivos (JPG, PNG, WEBP ou PDF, até 5MB cada). Só a moderação e você podem ver.</p>
            </div>

            <div class="denuncia-acoes">
                <button type="submit" class="btn-enviar">
                    <i class="fas fa-paper-plane"></i> Enviar Denúncia
                </button>
                <a href="javascript:history.back()" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        <?= CsrfMiddleware::field() ?>
</form>
    </div>

    <div class="denuncia-aviso">
        <i class="fas fa-info-circle"></i>
        <p>Denúncias falsas podem resultar em penalidades. Denuncie apenas conteúdo que viole as regras da plataforma.</p>
    </div>
</div>

<script>
document.getElementById('anexos')?.addEventListener('change', function () {
    if (this.files.length > 3) {
        alert('Envie no máximo 3 arquivos.');
        this.value = '';
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>