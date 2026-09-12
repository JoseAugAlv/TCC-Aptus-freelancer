<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
// app/Views/pagamentos/index.php
// [FIX-CRIT-01] Reescrita: lista apenas interesses do usuário logado,
//               mostra o botão certo conforme o papel naquele interesse,
//               usa dados reais em vez de "interesse_id default 1".

$tituloPagina = $tituloPagina ?? 'Pagamentos - Aptus';
$cssPagina    = $cssPagina    ?? 'pagamentos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuarioId  = (int) ($_SESSION['usuario']['id'] ?? 0);
$interesses = $interesses ?? [];
?>

<section class="pagamento-section animate-in">
    <div class="pagamento-container">
        <div class="pagamento-header">
            <h2>Meus Pagamentos</h2>
            <p>Gerencie suas confirmações de pagamento</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($_SESSION['flash']['mensagem'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (empty($interesses)): ?>
            <div class="pagamento-card">
                <h3>Sem transações</h3>
                <p>Você ainda não possui interesses ativos ou concluídos.</p>
            </div>
        <?php else: ?>
            <div class="pagamento-cards">
                <?php foreach ($interesses as $int): ?>
                    <?php
                        $souContratante = ((int) $int['id_contratante'] === $usuarioId);
                        $souFreelancer  = ((int) $int['id_freelancer'] === $usuarioId);

                        $confirmadoContratante = !empty($int['confirmado_contratante']);
                        $confirmadoFreelancer  = !empty($int['confirmado_freelancer']);
                        $situacaoFinal         = $int['situacao_final'] ?? 'pendente';

                        $tituloAnuncio = $int['anuncio_titulo'] ?? ('Interesse #' . (int) $int['id_interesse']);
                    ?>
                    <div class="pagamento-card">
                        <h3><?= htmlspecialchars($tituloAnuncio, ENT_QUOTES, 'UTF-8') ?></h3>

                        <div class="detalhe-row">
                            <span class="label">Contratante:</span>
                            <span class="valor"><?= htmlspecialchars($int['contratante_nome'] ?? ('#' . (int) $int['id_contratante']), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="detalhe-row">
                            <span class="label">Freelancer:</span>
                            <span class="valor"><?= htmlspecialchars($int['freelancer_nome'] ?? ('#' . (int) $int['id_freelancer']), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="detalhe-row">
                            <span class="label">Valor combinado:</span>
                            <span class="valor">R$ <?= number_format((float) ($int['anuncio_preco'] ?? 0), 2, ',', '.') ?></span>
                        </div>
                        <div class="detalhe-row">
                            <span class="label">Situação:</span>
                            <span class="valor">
                                <?php if ($situacaoFinal === 'confirmado'): ?>
                                    <span class="badge badge-ativo">Confirmado</span>
                                <?php elseif ($situacaoFinal === 'divergente'): ?>
                                    <span class="badge badge-excluido">Divergente</span>
                                <?php else: ?>
                                    <span class="badge badge-pendente">Pendente</span>
                                <?php endif; ?>
                            </span>
                        </div>

                        <div class="form-actions">
                            <?php if ($souContratante): ?>
                                <?php if ($confirmadoContratante): ?>
                                    <button type="button" class="btn btn-secondary" disabled>Você já confirmou (contratante)</button>
                                <?php else: ?>
                                    <a href="/Aptus/pagamentos/confirmar?id=<?= (int) $int['id_interesse'] ?>&amp;papel=contratante"
                                       class="btn btn-primary">Confirmar como Contratante</a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($souFreelancer): ?>
                                <?php if ($confirmadoFreelancer): ?>
                                    <button type="button" class="btn btn-secondary" disabled>Você já confirmou (freelancer)</button>
                                <?php else: ?>
                                    <a href="/Aptus/pagamentos/confirmar?id=<?= (int) $int['id_interesse'] ?>&amp;papel=freelancer"
                                       class="btn btn-primary">Confirmar como Freelancer</a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>