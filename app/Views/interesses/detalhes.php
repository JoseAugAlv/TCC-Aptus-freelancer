<?php
// app/Views/interesses/detalhes.php

$tituloPagina = $tituloPagina ?? 'Detalhes do Interesse - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesse = $interesse ?? [];
$usuario = $_SESSION['usuario'] ?? null;

require_once __DIR__ . '/../../Models/Avaliacao.php';
$avaliacaoModel = new Avaliacao();
$jaAvaliou = false;
$avaliacaoData = null;

if ($interesse && isset($interesse['situacao']) && $interesse['situacao'] == 'concluido') {
    $jaAvaliou = $avaliacaoModel->exists($interesse['id_interesse']);
    if ($jaAvaliou) {
        $avaliacaoData = $avaliacaoModel->findByInteresse($interesse['id_interesse']);
    }
}
?>

<h1>Detalhes do Interesse</h1>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="detalhes-container">
    <h2><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'N/A') ?></h2>
    
    <p><strong>Preco:</strong> R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?></p>
    <p><strong>Status:</strong> 
        <span style="color: <?= match($interesse['situacao'] ?? '') { 
            'pendente' => '#f59e0b',
            'ativo' => '#3b82f6', 
            'concluido' => '#10b981', 
            'cancelado' => '#ef4444',
            'recusado' => '#6b7280',
            default => '#94a3b8' 
        } ?>">
            <?= ucfirst($interesse['situacao'] ?? 'Desconhecido') ?>
        </span>
    </p>
    <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($interesse['data_interesse'] ?? 'now')) ?></p>
    
    <hr>
    
    <h3>Mensagem</h3>
    <p style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
        <?= nl2br(htmlspecialchars($interesse['mensagem_inicial'] ?? 'Sem mensagem')) ?>
    </p>
    
    <hr>
    
    <h3>Informacoes do Contato</h3>
    
    <?php if ($interesse['id_contratante'] == $usuario['id']): ?>
        <p><strong>Freelancer:</strong> <?= htmlspecialchars($interesse['freelancer_nome'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($interesse['freelancer_email'] ?? 'N/A') ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($interesse['freelancer_telefone'] ?? 'Nao informado') ?></p>
    <?php else: ?>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($interesse['contratante_nome'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($interesse['contratante_email'] ?? 'N/A') ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($interesse['contratante_telefone'] ?? 'Nao informado') ?></p>
    <?php endif; ?>
    
    <hr>
    
    <!-- Confirmacao de Execucao -->
    <?php if (isset($interesse['situacao']) && $interesse['situacao'] == 'ativo'): ?>
        <h3>Confirmacao de Execucao</h3>
        <?php if ($interesse['confirmado_contratante'] ?? false): ?>
            <p>Cliente confirmou a execucao</p>
        <?php else: ?>
            <p>Cliente ainda nao confirmou</p>
        <?php endif; ?>
        <?php if ($interesse['confirmado_freelancer'] ?? false): ?>
            <p>Freelancer confirmou a execucao</p>
        <?php else: ?>
            <p>Freelancer ainda nao confirmou</p>
        <?php endif; ?>
        <hr>
    <?php endif; ?>
    
    <!-- Avaliacao -->
    <?php if (isset($interesse['situacao']) && $interesse['situacao'] == 'concluido'): ?>
        <h3>Avaliacao</h3>
        
        <?php if ($usuario && $usuario['id'] == $interesse['id_contratante']): ?>
            <?php if ($jaAvaliou): ?>
                <p>Voce ja avaliou este servico.</p>
                <p><strong>Nota:</strong> <?= $avaliacaoData['nota'] ?? 0 ?> estrelas</p>
                <p><strong>Comentario:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['comentario'] ?? '')) ?></p>
                <?php if (!empty($avaliacaoData['resposta_avaliado'])): ?>
                    <p><strong>Resposta do freelancer:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['resposta_avaliado'])) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p><a href="/Aptus/avaliacoes/criar/<?= $interesse['id_interesse'] ?>">Avaliar Servico</a></p>
            <?php endif; ?>
            
        <?php elseif ($usuario && $usuario['id'] == $interesse['id_freelancer']): ?>
            <?php if ($jaAvaliou): ?>
                <p><strong>Nota:</strong> <?= $avaliacaoData['nota'] ?? 0 ?> estrelas</p>
                <p><strong>Comentario:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['comentario'] ?? '')) ?></p>
                <?php if (empty($avaliacaoData['resposta_avaliado'])): ?>
                    <p><a href="/Aptus/avaliacoes/responder/<?= $avaliacaoData['id_avaliacao'] ?? 0 ?>">Responder Avaliacao</a></p>
                <?php else: ?>
                    <p><strong>Sua resposta:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['resposta_avaliado'])) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p>O cliente ainda nao avaliou este servico.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <hr>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="/Aptus/interesses/ativos">Voltar</a>
    </div>
</div>

<style>
.detalhes-container {
    max-width: 700px;
    margin: 0 auto;
    background: #fff;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>