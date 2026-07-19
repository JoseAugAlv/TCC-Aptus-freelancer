<?php
// app/Views/denuncias/visualizar.php

$tituloPagina = $tituloPagina ?? 'Visualizar Denuncia - Aptus';
$cssPagina = $cssPagina ?? 'denuncias.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$denuncia = $denuncia ?? [];
$motivos = $motivos ?? [];
?>

<div class="denuncia-visualizar-container">
    <div class="denuncia-visualizar-header">
        <h1><i class="fas fa-search"></i> Visualizar Denuncia</h1>
        <p>Analise a denuncia e tome uma decisao</p>
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
            <h3>Informacoes da Denuncia</h3>
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
                <h4>Anuncio Denunciado</h4>
                <p><strong>Titulo:</strong> <?= htmlspecialchars($denuncia['anuncio_titulo']) ?></p>
            </div>
        <?php endif; ?>

        <div class="denuncia-visualizar-motivo">
            <h4>Motivo</h4>
            <p><strong><?= htmlspecialchars($motivos[$denuncia['motivo']] ?? $denuncia['motivo']) ?></strong></p>
            <?php if (!empty($denuncia['descricao'])): ?>
                <p><strong>Descricao:</strong></p>
                <div class="denuncia-descricao">
                    <?= nl2br(htmlspecialchars($denuncia['descricao'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="denuncia-visualizar-acoes">
            <form method="POST" action="/Aptus/moderator/denuncias/aprovar" style="display: inline;">
                <input type="hidden" name="id" value="<?= $denuncia['id_denuncia'] ?? 0 ?>">
                <button type="submit" class="btn-aprovar" onclick="return confirm('Aprovar esta denuncia?')">
                    <i class="fas fa-check"></i> Aprovar Denuncia
                </button>
            </form>
            
            <form method="POST" action="/Aptus/moderator/denuncias/rejeitar" style="display: inline;">
                <input type="hidden" name="id" value="<?= $denuncia['id_denuncia'] ?? 0 ?>">
                <button type="submit" class="btn-rejeitar" onclick="return confirm('Rejeitar esta denuncia?')">
                    <i class="fas fa-times"></i> Rejeitar Denuncia
                </button>
            </form>
            
            <a href="/Aptus/moderator/denuncias" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>