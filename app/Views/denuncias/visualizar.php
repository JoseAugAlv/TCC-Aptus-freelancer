<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/denuncias/visualizar.php

$tituloPagina = $tituloPagina ?? 'Visualizar Denúncia - Aptus';
$cssPagina = $cssPagina ?? 'denuncias.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$denuncia = $denuncia ?? [];
$motivos = $motivos ?? [];
?>
 
<div class="denuncia-visualizar-container">
    <div class="denuncia-visualizar-header">
        <h1>Visualizar Denúncia</h1>
        <p>Analise a denúncia e tome uma decisão</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="denuncia-visualizar-card">
        <div class="denuncia-visualizar-info">
            <h3>Informações da Denúncia</h3>
            <p><strong>ID:</strong> #<?= $denuncia['id_denuncia'] ?? 0 ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($denuncia['situacao_nome'] ?? 'Pendente') ?></p>
            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($denuncia['data_criacao'] ?? 'now')) ?></p>
        </div>

        <div class="denuncia-visualizar-partes">
            <div class="parte">
                <h4>Denunciante</h4>
                <p><strong>Nome:</strong> <?= htmlspecialchars($denuncia['denunciante_nome'] ?? 'N/A') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($denuncia['denunciante_email'] ?? 'N/A') ?></p>
            </div>
            
            <div class="parte">
                <h4>Denunciado</h4>
                <p><strong>Nome:</strong> <?= htmlspecialchars($denuncia['denunciado_nome'] ?? 'N/A') ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($denuncia['denunciado_email'] ?? 'N/A') ?></p>
            </div>
        </div>

        <?php if (!empty($denuncia['anuncio_titulo'])): ?>
            <div class="denuncia-visualizar-anuncio">
                <h4>Anúncio Denunciado</h4>
                <p><strong>Título:</strong> <?= htmlspecialchars($denuncia['anuncio_titulo']) ?></p>
            </div>
        <?php endif; ?>

        <div class="denuncia-visualizar-motivo">
            <h4>Motivo</h4>
            <p><strong><?= htmlspecialchars($motivos[$denuncia['motivo']] ?? $denuncia['motivo']) ?></strong></p>
            <?php if (!empty($denuncia['descricao'])): ?>
                <p><strong>Descrição:</strong></p>
                <div class="denuncia-descricao">
                    <?= nl2br(htmlspecialchars($denuncia['descricao'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php $anexos = $anexos ?? []; ?>
        <div class="denuncia-visualizar-motivo">
            <h4>Anexos (<?= count($anexos) ?>)</h4>
            <?php if (!$anexos): ?>
                <p>Nenhum arquivo anexado.</p>
            <?php else: ?>
                <div class="denuncia-anexos">
                    <?php foreach ($anexos as $i => $anexo): ?>
                        <?php $__urlAnexo = (aptus_base() . '/denuncias/anexo/') . url_id($anexo['id_anexo']);
                              $__pdf = strtolower(pathinfo($anexo['arquivo'], PATHINFO_EXTENSION)) === 'pdf'; ?>
                        <a href="<?= $__urlAnexo ?>" target="_blank" rel="noopener" class="denuncia-anexo">
                            <?php if ($__pdf): ?>
                                <i class="fas fa-file-pdf" aria-hidden="true"></i>
                            <?php else: ?>
                                <img src="<?= $__urlAnexo ?>" alt="Anexo <?= $i + 1 ?>" loading="lazy">
                            <?php endif; ?>
                            <span>Anexo <?= $i + 1 ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ((int) ($denuncia['id_situacao'] ?? 1) !== 1): ?>
            <div class="denuncia-visualizar-motivo">
                <h4>Análise</h4>
                <p><strong><?= htmlspecialchars($denuncia['situacao_nome'] ?? '') ?></strong>
                   por <?= htmlspecialchars($denuncia['moderador_nome'] ?? '—') ?>
                   em <?= !empty($denuncia['data_analise']) ? date('d/m/Y H:i', strtotime($denuncia['data_analise'])) : '—' ?></p>
            </div>
        <?php endif; ?>

        <div class="denuncia-visualizar-acoes">
            <?php if ((int) ($denuncia['id_situacao'] ?? 1) === 1): ?>
            <form method="POST" action="<?= aptus_base() ?>/moderator/denuncias/aprovar" style="display: inline;">
                <input type="hidden" name="id" value="<?= $denuncia['id_denuncia'] ?? 0 ?>">
                <button type="submit" class="btn-aprovar" onclick="return confirm('Aprovar esta denúncia?')">
                    <i class="fas fa-check"></i> Aprovar Denúncia
                </button>
            <?= CsrfMiddleware::field() ?>
</form>
            
            <form method="POST" action="<?= aptus_base() ?>/moderator/denuncias/rejeitar" style="display: inline;">
                <input type="hidden" name="id" value="<?= $denuncia['id_denuncia'] ?? 0 ?>">
                <button type="submit" class="btn-rejeitar" onclick="return confirm('Rejeitar esta denúncia?')">
                    <i class="fas fa-times"></i> Rejeitar Denúncia
                </button>
            <?= CsrfMiddleware::field() ?>
</form>
            
            <?php endif; ?>

            <a href="<?= aptus_base() ?>/moderator/denuncias" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>