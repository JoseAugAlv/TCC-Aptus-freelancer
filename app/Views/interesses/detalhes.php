<?php
// app/Views/interesses/detalhes.php

$tituloPagina = $tituloPagina ?? 'Detalhes do Interesse - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

// CORREÇÃO: Caminho absoluto para o Model
require_once __DIR__ . '/../../Models/Avaliacao.php';

$interesse = $interesse ?? [];
$usuario = $_SESSION['usuario'] ?? null;

// Verificar avaliação
$avaliacaoModel = new Avaliacao();
$jaAvaliou = false;
$avaliacaoData = null;
$podeAvaliar = false;

if ($interesse && isset($interesse['situacao']) && $interesse['situacao'] == 'concluido') {
    $jaAvaliou = $avaliacaoModel->exists($interesse['id_interesse']);
    if ($jaAvaliou) {
        $avaliacaoData = $avaliacaoModel->findByInteresse($interesse['id_interesse']);
    }
    if ($usuario && $usuario['id'] == $interesse['id_contratante']) {
        $podeAvaliar = true;
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

<div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
    <h2><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'N/A') ?></h2>
    
    <p><strong>Preço:</strong> R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?></p>
    <p><strong>Status:</strong> 
        <span style="color: <?= match($interesse['situacao'] ?? '') { 
            'ativo' => 'green', 
            'concluido' => 'blue', 
            'cancelado' => 'red', 
            default => 'gray' 
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
    
    <h3>Informações do Contato</h3>
    
    <?php if ($interesse['id_contratante'] == $usuario['id']): ?>
        <p><strong>Freelancer:</strong> <?= htmlspecialchars($interesse['freelancer_nome'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($interesse['freelancer_email'] ?? 'N/A') ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($interesse['freelancer_telefone'] ?? 'Não informado') ?></p>
    <?php else: ?>
        <p><strong>Cliente:</strong> <?= htmlspecialchars($interesse['contratante_nome'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($interesse['contratante_email'] ?? 'N/A') ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($interesse['contratante_telefone'] ?? 'Não informado') ?></p>
    <?php endif; ?>
    
    <hr>
    
    <!-- ============================================================ -->
    <!-- ÁREA DE AVALIAÇÃO -->
    <!-- ============================================================ -->
    <?php if (isset($interesse['situacao']) && $interesse['situacao'] == 'concluido'): ?>
        <h3>Avaliação</h3>
        
        <?php if ($usuario && $usuario['id'] == $interesse['id_contratante']): ?>
            <?php if ($jaAvaliou): ?>
                <p>Você já avaliou este serviço.</p>
                <p><strong>Nota:</strong> <?= $avaliacaoData['nota'] ?? 0 ?> estrelas</p>
                <p><strong>Comentário:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['comentario'] ?? '')) ?></p>
                <?php if (!empty($avaliacaoData['resposta_avaliado'])): ?>
                    <p><strong>Resposta do freelancer:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['resposta_avaliado'])) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p><a href="/Aptus/avaliacoes/criar/<?= $interesse['id_interesse'] ?>">Avaliar Serviço</a></p>
            <?php endif; ?>
            
        <?php elseif ($usuario && $usuario['id'] == $interesse['id_freelancer']): ?>
            <?php if ($jaAvaliou): ?>
                <p><strong>Nota:</strong> <?= $avaliacaoData['nota'] ?? 0 ?> estrelas</p>
                <p><strong>Comentário:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['comentario'] ?? '')) ?></p>
                <?php if (empty($avaliacaoData['resposta_avaliado'])): ?>
                    <p><a href="/Aptus/avaliacoes/responder/<?= $avaliacaoData['id_avaliacao'] ?? 0 ?>">Responder Avaliação</a></p>
                <?php else: ?>
                    <p><strong>Sua resposta:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['resposta_avaliado'])) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p>O cliente ainda não avaliou este serviço.</p>
            <?php endif; ?>
        <?php endif; ?>
        
        <hr>
    <?php endif; ?>
    
    <div style="margin-top: 20px;">
        <a href="/Aptus/interesses/meus">Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>