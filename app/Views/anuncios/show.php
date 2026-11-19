<?php
// app/Views/anuncios/show.php
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../Helpers/UploadHelper.php';

$tituloPagina = $tituloPagina ?? 'Detalhes do Serviço - Aptus';
$cssPagina    = 'show.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncio            = $anuncio            ?? [];
$fotos              = $fotos              ?? [];
$usuario            = $_SESSION['usuario'] ?? null;
$usuarioInteressado = $usuarioInteressado ?? false;
$favoritado         = $favoritado         ?? false;
$totalFavoritos     = $totalFavoritos     ?? 0;
$idInteresseAtual   = $idInteresseAtual   ?? 0;
$limiteSimultaneos  = $limiteSimultaneos  ?? 1;
$ativosAgora        = $ativosAgora        ?? 0;
$agendaCheia        = $agendaCheia        ?? false;
?>

<h1>Detalhes do Serviço</h1>
<hr>

<?php if (!empty($previewNaoPublicado)): ?>
    <div class="flash-aviso">
        Pré-visualização: este anúncio ainda não está publicado (pendente, rejeitado ou pausado) e só você e a equipe de moderação podem vê-lo.
    </div>
<?php endif; ?>
<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="detalhes-container">
    <div class="detalhes-imagem">
        <?php if (!empty($anuncio['foto_capa'])): ?>
            <button type="button" class="foto-ampliar" data-abrir-galeria aria-label="Ampliar foto do serviço">
                <img id="fotoPrincipal" src="<?= htmlspecialchars(\UploadHelper::capaUrl($anuncio['foto_capa']), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($anuncio['titulo'] ?? 'Imagem do serviço', ENT_QUOTES, 'UTF-8') ?>">
                <span class="foto-ampliar-dica" aria-hidden="true"><i class="fas fa-expand"></i> Ampliar</span>
            </button>
        <?php else: ?>
            <i class="fas fa-briefcase"></i>
        <?php endif; ?>
    </div>

    <div class="detalhes-info">
        <div class="detalhes-topo">
            <div class="categoria-badge">
                <i class="<?= htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
                <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
            </div>

            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <button class="btn-favorito <?= $favoritado ? 'ativo' : '' ?>"
                        data-anuncio-id="<?= $anuncio['id_anuncio'] ?>">
                    <i class="<?= $favoritado ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="favorito-texto">
                        <?= $favoritado ? 'Favoritado' : 'Favoritar' ?>
                    </span>
                    <span class="favorito-contador">(<?= (int) $totalFavoritos ?>)</span>
                </button>
            <?php endif; ?>
        </div>

        <h2><?= htmlspecialchars($anuncio['titulo']) ?></h2>

        <p class="preco-destaque">
            <i class="fas fa-tag"></i> R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
        </p>

        <div class="avaliacao-detalhes">
            <?php $estrelasNota = $anuncio['nota_anuncio'] ?? 0; $estrelasTotal = $anuncio['total_avaliacoes_anuncio'] ?? 0; require __DIR__ . '/../partials/estrelas.php'; ?>
        </div>

        <p class="descricao-completa">
            <?= nl2br(htmlspecialchars($anuncio['descricao'])) ?>
        </p>

        <div class="info-extra">
            <span><i class="fas fa-eye"></i> <?= (int) ($anuncio['visualizacoes'] ?? 0) ?> visualizações</span>
            <span><i class="fas fa-heart"></i> <?= (int) $totalFavoritos ?> favoritos</span>
            <span><i class="fas fa-handshake"></i> <?= (int) ($anuncio['total_interesses'] ?? 0) ?> interesses</span>
            <span><i class="fas fa-users"></i>
                <?= $limiteSimultaneos === 1 ? 'Atende 1 cliente por vez' : 'Atende até ' . (int) $limiteSimultaneos . ' clientes ao mesmo tempo' ?>
            </span>
        </div>

        <div class="freelancer-card">
            <div class="freelancer-header">
                <div class="freelancer-avatar">
                    <?php if (!empty($anuncio['foto_perfil']) && $anuncio['foto_perfil'] != 'default.png'): ?>
                        <img src="<?= htmlspecialchars(\UploadHelper::getUrl('uploads/perfil/' . basename($anuncio['foto_perfil'])), ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($anuncio['freelancer_nome']) ?>">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="freelancer-info">
                    <h3><?= htmlspecialchars($anuncio['freelancer_nome']) ?></h3>
                    <p class="reputacao-prestador">
                        <span class="reputacao-rotulo">Como prestador:</span>
                        <?php $estrelasNota = $anuncio['nota_media'] ?? 0; $estrelasTotal = $anuncio['total_avaliacoes'] ?? 0; $estrelasCompacto = true; require __DIR__ . '/../partials/estrelas.php'; $estrelasCompacto = false; ?>
                    </p>
                    <?php if (!empty($anuncio['cidade']) || !empty($anuncio['estado'])): ?>
                        <p>
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($anuncio['cidade'] ?? '') . (!empty($anuncio['cidade']) && !empty($anuncio['estado']) ? ', ' : '') . htmlspecialchars($anuncio['estado'] ?? '') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($anuncio['freelancer_bio'])): ?>
                <p class="freelancer-bio"><?= htmlspecialchars($anuncio['freelancer_bio']) ?></p>
            <?php endif; ?>
        </div>

        <div class="detalhes-acoes">
            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <?php if ($usuarioInteressado): ?>
                    <button class="btn-interesse btn-enviado" disabled>
                        <i class="fas fa-check-circle"></i> Proposta Enviada
                    </button>
                    <?php if ($idInteresseAtual > 0): ?>
                        <a href="<?= aptus_base() ?>/chat/<?= url_id((int) $idInteresseAtual) ?>" class="btn-interesse"
                           style="background:var(--cor-sucesso);">
                            <i class="fas fa-comments"></i> Ir para o Chat
                        </a>
                    <?php endif; ?>
                <?php elseif ($agendaCheia): ?>
                    <button class="btn-interesse btn-agenda-cheia" disabled aria-describedby="motivoAgendaCheia">
                        <i class="fas fa-calendar-times"></i> Agenda cheia no momento
                    </button>
                    <p class="impedimento-msg" id="motivoAgendaCheia">
                        <?= $limiteSimultaneos === 1
                            ? 'Este prestador já está atendendo outro cliente neste serviço.'
                            : 'Este prestador já está atendendo ' . (int) $ativosAgora . ' clientes neste serviço, o máximo ao mesmo tempo.' ?>
                        Volte mais tarde: quando um atendimento terminar, a vaga é liberada.
                    </p>
                <?php elseif (!empty($impedimento)): ?>
                    <button class="btn-interesse btn-enviado" disabled aria-describedby="motivoImpedimento">
                        <i class="fas fa-lock"></i> Contratação indisponível
                    </button>
                    <p class="impedimento-msg" id="motivoImpedimento"><?= htmlspecialchars($impedimento) ?></p>
                <?php else: ?>
                    <form method="POST" action="<?= aptus_base() ?>/interesses/criar?anuncio=<?= url_id($anuncio['id_anuncio']) ?>" id="formInteresse">
                        <input type="hidden" name="mensagem" value="Olá! Tenho interesse no seu serviço.">
                        <button type="submit" class="btn-interesse" id="btnInteresse">
                            <i class="fas fa-handshake"></i> Tenho Interesse
                        </button>
                        <?= CsrfMiddleware::field() ?>
                    </form>
                <?php endif; ?>
            <?php elseif (!$usuario): ?>
                <a href="<?= aptus_base() ?>/login" class="btn-interesse">
                    <i class="fas fa-sign-in-alt"></i> Faça login para ter interesse
                </a>
            <?php endif; ?>

            <?php if ($usuario && $usuario['id'] == $anuncio['id_usuario']): ?>
                <p class="vagas-dono">
                    <i class="fas fa-users"></i>
                    Atendimentos em andamento: <?= (int) $ativosAgora ?> de <?= (int) $limiteSimultaneos ?><?= $agendaCheia ? ' (agenda cheia: novas propostas pausadas)' : '' ?>
                </p>
                <a href="<?= aptus_base() ?>/anuncios/editar/<?= url_id($anuncio['id_anuncio']) ?>" class="btn-editar">
                    <i class="fas fa-edit"></i> Editar
                </a>
            <?php endif; ?>

            <a href="<?= aptus_base() ?>/perfil/publico/<?= url_id($anuncio['id_usuario']) ?>" class="btn-perfil-publico">
                <i class="fas fa-user"></i> Ver Perfil
            </a>

            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <a href="<?= aptus_base() ?>/denuncias/criar?tipo=anuncio&amp;id=<?= url_id($anuncio['id_anuncio']) ?>" class="btn-denunciar">
                    <i class="fas fa-flag"></i> Denunciar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $avaliacoesAnuncio = $avaliacoesAnuncio ?? []; $distribuicao = $distribuicao ?? []; ?>
<section class="avaliacoes-anuncio" aria-labelledby="tituloAvaliacoes">
    <h3 id="tituloAvaliacoes">Avaliações deste serviço</h3>
    <?php if (empty($avaliacoesAnuncio)): ?>
        <p class="avaliacoes-vazio">
            Este serviço ainda não recebeu avaliações. Só quem contrata e conclui o serviço pela Aptus pode avaliar.
        </p>
    <?php else: ?>
        <div class="avaliacoes-resumo">
            <div class="avaliacoes-media">
                <strong><?= number_format((float) $anuncio['nota_anuncio'], 1, ',', '') ?></strong>
                <?php $estrelasNota = $anuncio['nota_anuncio']; $estrelasTotal = $anuncio['total_avaliacoes_anuncio']; require __DIR__ . '/../partials/estrelas.php'; ?>
            </div>
            <ul class="avaliacoes-barras">
                <?php $__tot = max(1, (int) $anuncio['total_avaliacoes_anuncio']); ?>
                <?php foreach ($distribuicao as $__n => $__q): ?>
                    <li>
                        <span><?= $__n ?> <i class="fas fa-star" aria-hidden="true"></i></span>
                        <span class="barra"><span style="width: <?= round($__q / $__tot * 100) ?>%"></span></span>
                        <span><?= (int) $__q ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <ul class="avaliacoes-lista">
            <?php foreach ($avaliacoesAnuncio as $av): ?>
                <li class="avaliacao-item">
                    <div class="avaliacao-topo">
                        <strong><?= htmlspecialchars($av['avaliador_nome']) ?></strong>
                        <span class="nota-estrelas">
                            <?php for ($__i = 1; $__i <= 5; $__i++): ?>
                                <i class="<?= $__i <= (int) $av['nota'] ? 'fas' : 'far' ?> fa-star" aria-hidden="true"></i>
                            <?php endfor; ?>
                            <span class="sr-only">Nota <?= (int) $av['nota'] ?> de 5</span>
                        </span>
                        <time datetime="<?= htmlspecialchars($av['data_avaliacao']) ?>"><?= date('d/m/Y', strtotime($av['data_avaliacao'])) ?></time>
                    </div>
                    <?php if (trim((string) $av['comentario']) !== ''): ?>
                        <p><?= nl2br(htmlspecialchars($av['comentario'])) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($av['resposta_avaliado'])): ?>
                        <div class="avaliacao-resposta">
                            <strong>Resposta de <?= htmlspecialchars(strtok($anuncio['freelancer_nome'], ' ')) ?>:</strong>
                            <?= nl2br(htmlspecialchars($av['resposta_avaliado'])) ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php if (!empty($fotos)): ?>
    <h3>Fotos do Serviço</h3>
    <div class="fotos-grid galeria-anuncio">
        <?php foreach ($fotos as $foto): ?>
            <?php $__url = \UploadHelper::capaUrl($foto['arquivo']); $__capa = $foto['arquivo'] === ($anuncio['foto_capa'] ?? ''); ?>
            <button type="button" class="foto-item <?= $__capa ? 'ativa' : '' ?>"
                    data-foto="<?= htmlspecialchars($__url, ENT_QUOTES, 'UTF-8') ?>" aria-label="Ampliar esta foto">
                <img src="<?= htmlspecialchars($__url, ENT_QUOTES, 'UTF-8') ?>" alt="Foto do serviço" loading="lazy">
                <?php if ($__capa): ?><span class="foto-selo-capa">Capa</span><?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<script src="<?= aptus_asset('js/galeria.js') ?>"></script>

<p><a href="<?= aptus_base() ?>/anuncios"><i class="fas fa-arrow-left"></i> Voltar para Serviços</a></p>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= aptus_base() ?>/public/js/favoritos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('formInteresse');
    var btn  = document.getElementById('btnInteresse');

    if (form && btn) {
        form.addEventListener('submit', function(e) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>