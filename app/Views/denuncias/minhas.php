<?php
// app/Views/denuncias/minhas.php — histórico de denúncias feitas pelo usuário

$tituloPagina = $tituloPagina ?? 'Minhas denúncias - Aptus';
$cssPagina    = $cssPagina ?? 'denuncias.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$denuncias = $denuncias ?? [];
$motivos   = $motivos ?? [];
?>

<div class="denuncias-container">
    <div class="denuncias-header">
        <h1>Minhas denúncias</h1>
        <p>Acompanhe as denúncias que você enviou e o resultado da análise</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($denuncias)): ?>
        <div class="empty-state">
            <i class="fas fa-shield-alt"></i>
            <h3>Você ainda não fez denúncias</h3>
            <p>Viu algo que viola a <a href="<?= aptus_base() ?>/politica-anuncios">Política de Anúncios</a>?
               Use o botão "Denunciar" na página do anúncio ou do perfil.</p>
        </div>
    <?php else: ?>
        <div class="tabela-wrapper">
            <table class="tabela-denuncias">
                <thead>
                    <tr>
                        <th>Denunciado</th>
                        <th>Motivo</th>
                        <th>Anúncio</th>
                        <th>Enviada em</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($denuncias as $d): ?>
                        <?php $st = (int) $d['id_situacao']; ?>
                        <tr>
                            <td data-label="Denunciado"><?= htmlspecialchars($d['denunciado_nome']) ?></td>
                            <td data-label="Motivo">
                                <?= htmlspecialchars($motivos[$d['motivo']] ?? $d['motivo']) ?>
                                <?php if ((int) ($d['total_anexos'] ?? 0) > 0): ?>
                                    <small class="denuncia-analise"><i class="fas fa-paperclip"></i> <?= (int) $d['total_anexos'] ?> anexo(s)</small>
                                <?php endif; ?>
                            </td>
                            <td data-label="Anúncio">
                                <?php if (!empty($d['anuncio_titulo']) && !empty($d['anuncio_slug'])): ?>
                                    <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($d['anuncio_slug']) ?>"><?= htmlspecialchars($d['anuncio_titulo']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($d['anuncio_titulo'] ?? 'Perfil') ?>
                                <?php endif; ?>
                            </td>
                            <td data-label="Enviada em"><?= date('d/m/Y H:i', strtotime($d['data_criacao'])) ?></td>
                            <td data-label="Situação">
                                <span class="status-badge <?= $st === 1 ? 'pendente' : ($st === 2 ? 'aprovado' : 'rejeitado') ?>">
                                    <?= $st === 1 ? 'Em análise' : ($st === 2 ? 'Procedente' : 'Não procedente') ?>
                                </span>
                                <?php if ($st !== 1 && !empty($d['data_analise'])): ?>
                                    <small class="denuncia-analise">em <?= date('d/m/Y', strtotime($d['data_analise'])) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
