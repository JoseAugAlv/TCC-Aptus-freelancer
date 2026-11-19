<?php
// app/Views/interesses/detalhes.php

$tituloPagina = $tituloPagina ?? 'Detalhes do Interesse - Aptus';
$cssPagina = $cssPagina ?? 'detalhes.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesse = $interesse ?? [];
$usuario = $_SESSION['usuario'] ?? null;

require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

$jaAvaliou         = $jaAvaliou ?? false;
$avaliacaoData     = $avaliacaoData ?? null;      // avaliação que eu fiz
$avaliacaoRecebida = $avaliacaoRecebida ?? null;  // avaliação que recebi
$souCliente        = $usuario && (int) $usuario['id'] === (int) ($interesse['id_contratante'] ?? 0);
$papelOutro        = $souCliente ? 'prestador' : 'cliente';
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
    <?php
        require_once __DIR__ . '/../../Helpers/UploadHelper.php';
        $fotosServico = $fotosServico ?? [];
        $__capaUrl = \UploadHelper::capaUrl($interesse['anuncio_foto_capa'] ?? '');
    ?>
    <div class="interesse-midia">
        <img id="fotoServicoPrincipal" src="<?= htmlspecialchars($__capaUrl, ENT_QUOTES, 'UTF-8') ?>"
             alt="Foto do serviço <?= htmlspecialchars($interesse['anuncio_titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if (count($fotosServico) > 1): ?>
            <div class="interesse-miniaturas">
                <?php foreach ($fotosServico as $__f): $__u = \UploadHelper::capaUrl($__f['arquivo']); ?>
                    <button type="button" data-foto="<?= htmlspecialchars($__u, ENT_QUOTES, 'UTF-8') ?>"
                            onclick="document.getElementById('fotoServicoPrincipal').src=this.dataset.foto"
                            aria-label="Ver foto">
                        <img src="<?= htmlspecialchars($__u, ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <h2><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'N/A') ?></h2>
    
    <p><strong>Preço:</strong> R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?></p>
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
    <p style="background: var(--tema-fundo-alt, #f8f9fa); padding: 15px; border-radius: 8px;">
        <?= nl2br(htmlspecialchars($interesse['mensagem_inicial'] ?? 'Sem mensagem')) ?>
    </p>
    
    <hr>
    
    <h3>Informações do Contato</h3>
    
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
    
    <!-- Conclusão do serviço: cada parte confirma; com as duas, vira concluído -->
    <?php if (($interesse['situacao'] ?? '') === 'ativo'): ?>
        <?php
            $euConclui    = !empty($interesse[$souCliente ? 'concluido_contratante' : 'concluido_freelancer']);
            $outroConcluiu = !empty($interesse[$souCliente ? 'concluido_freelancer' : 'concluido_contratante']);
        ?>
        <h3>Conclusão do serviço</h3>
        <ul class="conclusao-lista">
            <li class="<?= !empty($interesse['concluido_contratante']) ? 'ok' : '' ?>">
                <i class="fas <?= !empty($interesse['concluido_contratante']) ? 'fa-check-circle' : 'fa-circle' ?>" aria-hidden="true"></i>
                Cliente <?= !empty($interesse['concluido_contratante']) ? 'confirmou a conclusão' : 'ainda não confirmou' ?>
            </li>
            <li class="<?= !empty($interesse['concluido_freelancer']) ? 'ok' : '' ?>">
                <i class="fas <?= !empty($interesse['concluido_freelancer']) ? 'fa-check-circle' : 'fa-circle' ?>" aria-hidden="true"></i>
                Prestador <?= !empty($interesse['concluido_freelancer']) ? 'confirmou a conclusão' : 'ainda não confirmou' ?>
            </li>
        </ul>
        <?php if (!$euConclui): ?>
            <form method="POST" action="<?= aptus_base() ?>/interesses/confirmar-execucao">
                <input type="hidden" name="id" value="<?= (int) $interesse['id_interesse'] ?>">
                <input type="hidden" name="origem" value="detalhes">
                <?= CsrfMiddleware::field() ?>
                <button type="submit" class="btn-confirmar"
                        onclick="return confirm('Confirmar que o serviço foi feito? Quando o <?= $papelOutro ?> confirmar também, o serviço é concluído.')">
                    <i class="fas fa-check-double" aria-hidden="true"></i> Concluir serviço
                </button>
            </form>
        <?php else: ?>
            <p>Você já confirmou. <?= $outroConcluiu ? '' : 'Aguardando o ' . $papelOutro . ' confirmar para concluir o serviço.' ?></p>
        <?php endif; ?>
        <p class="conclusao-dica">Depois que os dois confirmarem, cada um poderá avaliar o outro.</p>
        <hr>
    <?php endif; ?>

    <!-- Avaliações: a que eu fiz e a que recebi -->
    <?php if (($interesse['situacao'] ?? '') === 'concluido' && $usuario): ?>
        <h3>Avaliações</h3>

        <h4>Sua avaliação sobre o <?= $papelOutro ?></h4>
        <?php if ($avaliacaoData): ?>
            <p><strong>Nota:</strong> <?= (int) $avaliacaoData['nota'] ?> de 5 estrelas</p>
            <?php if (trim((string) $avaliacaoData['comentario']) !== ''): ?>
                <p><strong>Comentário:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['comentario'])) ?></p>
            <?php endif; ?>
            <?php if (!empty($avaliacaoData['resposta_avaliado'])): ?>
                <p><strong>Resposta:</strong> <?= nl2br(htmlspecialchars($avaliacaoData['resposta_avaliado'])) ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p><a href="<?= aptus_base() ?>/avaliacoes/criar/<?= url_id($interesse['id_interesse']) ?>" class="btn-confirmar">
                <i class="fas fa-star" aria-hidden="true"></i> Avaliar o <?= $papelOutro ?>
            </a></p>
        <?php endif; ?>

        <h4>Avaliação que você recebeu</h4>
        <?php if ($avaliacaoRecebida): ?>
            <p><strong>Nota:</strong> <?= (int) $avaliacaoRecebida['nota'] ?> de 5 estrelas</p>
            <?php if (trim((string) $avaliacaoRecebida['comentario']) !== ''): ?>
                <p><strong>Comentário:</strong> <?= nl2br(htmlspecialchars($avaliacaoRecebida['comentario'])) ?></p>
            <?php endif; ?>
            <?php if (empty($avaliacaoRecebida['resposta_avaliado'])): ?>
                <p><a href="<?= aptus_base() ?>/avaliacoes/responder/<?= url_id((int) $avaliacaoRecebida['id_avaliacao']) ?>">Responder avaliação</a></p>
            <?php else: ?>
                <p><strong>Sua resposta:</strong> <?= nl2br(htmlspecialchars($avaliacaoRecebida['resposta_avaliado'])) ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p>O <?= $papelOutro ?> ainda não avaliou você.</p>
        <?php endif; ?>

        <hr>
    <?php endif; ?>

    <div style="margin-top: 20px;">
        <a href="<?= aptus_base() ?>/interesses/meus">Voltar</a>
    </div>
</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>