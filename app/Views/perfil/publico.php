<?php
require_once __DIR__ . '/../../Helpers/UploadHelper.php';
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
// app/Views/perfil/publico.php

$tituloPagina = $tituloPagina ?? 'Perfil - Aptus';
$cssPagina    = $cssPagina    ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$perfil        = $perfilData ?? [];
$anuncios      = $anuncios ?? [];
$totalAnuncios = $totalAnuncios ?? 0;
$temMais       = $temMais ?? false;
$localizacao   = $localizacao ?? null;
$logado        = $_SESSION['usuario'] ?? null;
$ehProprio     = $logado && (int) $logado['id'] === (int) ($perfil['id_usuario'] ?? 0);

$perfilNome = match ((int) ($perfil['id_perfil'] ?? 3)) {
    1 => 'Administrador',
    2 => 'Moderador',
    4 => 'Master',
    default => 'Usuário',
};
?>
<link rel="stylesheet" href="<?= aptus_asset('css/anuncios.css') ?>">

<div class="perfil-publico-container">
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="perfil-publico-card">
        <div class="perfil-publico-avatar">
            <?php if (!empty($perfil['foto_perfil']) && $perfil['foto_perfil'] != 'default.png'): ?>
                <img src="<?= htmlspecialchars(\UploadHelper::getUrl($perfil['foto_perfil']), ENT_QUOTES, 'UTF-8') ?>" alt="Foto de perfil">
            <?php else: ?>
                <i class="fas fa-user-circle"></i>
            <?php endif; ?>
        </div>
        <div class="perfil-publico-info">
            <h2><?= htmlspecialchars($perfil['nome'] ?? 'Usuário') ?></h2>
            <?php if (!empty($perfil['username'])): ?>
                <p class="username-tag">@<?= htmlspecialchars($perfil['username']) ?></p>
            <?php endif; ?>

            <?php if ($localizacao): ?>
                <p class="localizacao">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($localizacao) ?>
                </p>
            <?php endif; ?>

            <p class="role">
                <span class="badge"><?= $perfilNome ?></span>
                <?php if (!empty($bloqueioAtivo)): ?>
                    <span class="badge badge-excluido"><i class="fas fa-ban"></i> Bloqueado</span>
                <?php endif; ?>
            </p>

            <div class="avaliacao-publico">
                <span class="reputacao-rotulo">Como prestador:</span>
                <?php $estrelasNota = $perfil['nota_media'] ?? 0; $estrelasTotal = $perfil['total_avaliacoes'] ?? 0; require __DIR__ . '/../partials/estrelas.php'; ?>
            </div>
            <?php if (!empty($resumoCliente['total'])): ?>
                <div class="avaliacao-publico">
                    <span class="reputacao-rotulo">Como cliente:</span>
                    <?php $estrelasNota = $resumoCliente['media']; $estrelasTotal = $resumoCliente['total']; $estrelasCompacto = true; require __DIR__ . '/../partials/estrelas.php'; $estrelasCompacto = false; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($perfil['bio'])): ?>
                <p class="bio-publico"><?= htmlspecialchars($perfil['bio']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="perfil-publico-stats">
        <div class="stat-item">
            <span class="stat-number"><?= (int) $totalAnuncios ?></span>
            <span class="stat-label">Anúncios Ativos</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= (int) ($perfil['total_avaliacoes'] ?? 0) > 0 ? number_format((float) $perfil['nota_media'], 1, ',', '') : '—' ?></span>
            <span class="stat-label">Avaliação Média</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= (int) ($servicosConcluidos ?? 0) ?></span>
            <span class="stat-label">Serviços Concluídos</span>
        </div>
    </div>

    <?php if ($logado && !$ehProprio): ?>
        <div class="perfil-publico-actions">
            <?php if (!empty($euBloqueei)): ?>
                <form method="POST" action="<?= aptus_base() ?>/usuarios/desbloquear">
                    <input type="hidden" name="usuario" value="<?= url_id((int) $perfil['id_usuario']) ?>">
                    <?= CsrfMiddleware::field() ?>
                    <button type="submit" class="btn-ver-servicos btn-secundario">
                        <i class="fas fa-unlock"></i> Desbloquear
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="<?= aptus_base() ?>/usuarios/bloquear"
                      onsubmit="return confirm('Bloquear este usuário? Vocês não poderão contratar serviços um do outro nem trocar mensagens.');">
                    <input type="hidden" name="usuario" value="<?= url_id((int) $perfil['id_usuario']) ?>">
                    <?= CsrfMiddleware::field() ?>
                    <button type="submit" class="btn-ver-servicos btn-secundario">
                        <i class="fas fa-user-slash"></i> Bloquear
                    </button>
                </form>
            <?php endif; ?>
            <a href="<?= aptus_base() ?>/denuncias/criar?tipo=perfil&amp;id=<?= url_id((int) $perfil['id_usuario']) ?>" class="btn-ver-servicos btn-secundario">
                <i class="fas fa-flag"></i> Denunciar
            </a>
        </div>
    <?php endif; ?>

    <section class="perfil-anuncios" aria-labelledby="tituloAnunciosPerfil">
        <h3 id="tituloAnunciosPerfil">
            <i class="fas fa-tools"></i> Serviços de <?= htmlspecialchars(strtok($perfil['nome'] ?? 'Usuário', ' ')) ?>
            <span class="perfil-anuncios-total"><?= (int) $totalAnuncios ?></span>
        </h3>

        <?php if (empty($anuncios)): ?>
            <p class="perfil-anuncios-vazio">Nenhum serviço publicado no momento.</p>
        <?php else: ?>
            <div class="profissionais-grid" id="gridAnunciosPerfil">
                <?php require __DIR__ . '/../anuncios/_cards.php'; ?>
            </div>
            <?php if ($temMais): ?>
                <div class="ver-mais-wrap">
                    <button type="button" class="btn-ver-mais" data-ver-mais data-alvo="#gridAnunciosPerfil"
                            data-url="<?= aptus_base() ?>/anuncios?freelancer=<?= url_id((int) $perfil['id_usuario']) ?>&amp;parcial=1"
                            data-pagina="1">
                        <i class="fas fa-plus" aria-hidden="true"></i> Ver mais
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</div>

<?php $jsPagina = 'ver-mais.js'; require_once __DIR__ . '/../layouts/footer.php'; ?>
