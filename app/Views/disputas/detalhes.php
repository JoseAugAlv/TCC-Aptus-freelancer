<?php
// app/Views/disputas/detalhes.php

$tituloPagina = $tituloPagina ?? 'Detalhes da Disputa - Aptus';
$cssPagina = $cssPagina ?? 'disputas.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$disputa = $disputa ?? [];
$usuario = $_SESSION['usuario'] ?? null;
$role = $_SESSION['usuario']['role'] ?? 0;
?>

<div class="disputa-detalhes-container">
    <div class="disputa-detalhes-header">
        <h1>Detalhes da Disputa</h1>
        <p>Status: <span class="status-<?= strtolower($disputa['situacao_nome'] ?? 'pendente') ?>">
            <?= htmlspecialchars($disputa['situacao_nome'] ?? 'Pendente') ?>
        </span></p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="disputa-detalhes-card">
        <div class="disputa-detalhes-info">
            <h3>Informacoes</h3>
            <p><strong>ID:</strong> #<?= $disputa['id_disputa'] ?? 0 ?></p>
            <p><strong>Data de Abertura:</strong> <?= date('d/m/Y H:i', strtotime($disputa['data_abertura'] ?? 'now')) ?></p>
            <p><strong>Motivo:</strong> <?= htmlspecialchars($disputa['motivo'] ?? 'N/A') ?></p>
        </div>

        <div class="disputa-detalhes-servico">
            <h3>Servico</h3>
            <p><strong>Titulo:</strong> <?= htmlspecialchars($disputa['anuncio_titulo'] ?? 'N/A') ?></p>
            <p><strong>Contratante:</strong> <?= htmlspecialchars($disputa['contratante_nome'] ?? 'N/A') ?></p>
            <p><strong>Freelancer:</strong> <?= htmlspecialchars($disputa['freelancer_nome'] ?? 'N/A') ?></p>
        </div>

        <?php if (!empty($disputa['valor_informado_contratante']) || !empty($disputa['valor_informado_freelancer'])): ?>
            <div class="disputa-detalhes-pagamento">
                <h3>Pagamento</h3>
                <p><strong>Cliente informou:</strong> R$ <?= number_format($disputa['valor_informado_contratante'] ?? 0, 2, ',', '.') ?></p>
                <p><strong>Freelancer informou:</strong> R$ <?= number_format($disputa['valor_informado_freelancer'] ?? 0, 2, ',', '.') ?></p>
                <p><strong>Status:</strong> <?= htmlspecialchars($disputa['pagamento_situacao'] ?? 'Pendente') ?></p>
            </div>
        <?php endif; ?>

        <div class="disputa-detalhes-descricao">
            <h3>Descricao do Problema</h3>
            <p><?= nl2br(htmlspecialchars($disputa['descricao'] ?? 'Nao informado')) ?></p>
        </div>

        <?php if (!empty($disputa['resposta'])): ?>
            <div class="disputa-detalhes-resposta">
                <h3>Resposta do Moderador</h3>
                <p><?= nl2br(htmlspecialchars($disputa['resposta'])) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($disputa['id_responsavel'] ?? false): ?>
            <div class="disputa-detalhes-moderador">
                <p><strong>Analisado por:</strong> <?= htmlspecialchars($disputa['id_responsavel'] ?? 'N/A') ?></p>
                <p><strong>Data de Resolucao:</strong> <?= date('d/m/Y H:i', strtotime($disputa['data_resolucao'] ?? 'now')) ?></p>
            </div>
        <?php endif; ?>

        <div class="disputa-detalhes-acoes">
            <?php if (in_array($role, [1, 2, 4]) && $disputa['situacao_nome'] == 'Pendente'): ?>
                <form method="POST" action="/Aptus/moderator/disputas/aprovar" style="display: inline;">
                    <input type="hidden" name="id" value="<?= $disputa['id_disputa'] ?>">
                    <div class="form-group">
                        <label for="resposta_aprovar">Resposta (opcional)</label>
                        <textarea id="resposta_aprovar" name="resposta" rows="2" placeholder="Justificativa..."></textarea>
                    </div>
                    <button type="submit" class="btn-aprovar" onclick="return confirm('Aprovar esta disputa?')">
                        <i class="fas fa-check"></i> Aprovar
                    </button>
                </form>
                <form method="POST" action="/Aptus/moderator/disputas/rejeitar" style="display: inline;">
                    <input type="hidden" name="id" value="<?= $disputa['id_disputa'] ?>">
                    <div class="form-group">
                        <label for="resposta_rejeitar">Resposta (opcional)</label>
                        <textarea id="resposta_rejeitar" name="resposta" rows="2" placeholder="Justificativa..."></textarea>
                    </div>
                    <button type="submit" class="btn-rejeitar" onclick="return confirm('Rejeitar esta disputa?')">
                        <i class="fas fa-times"></i> Rejeitar
                    </button>
                </form>
            <?php endif; ?>
            
            <a href="javascript:history.back()" class="btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>