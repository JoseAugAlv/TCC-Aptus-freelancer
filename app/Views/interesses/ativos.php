<?php
// app/Views/interesses/ativos.php
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';

$tituloPagina = $tituloPagina ?? 'Serviços Ativos - Aptus';
$cssPagina    = $cssPagina    ?? 'ativos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
$usuario    = $_SESSION['usuario'] ?? null;
?>

<div class="interesses-container">
    <div class="interesses-header">
        <h1>Serviços Ativos</h1>
        <p>Serviços em andamento e concluídos</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo'], ENT_QUOTES, 'UTF-8') ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($interesses)): ?>
        <div class="interesses-empty">
            <p>Nenhum serviço ativo</p>
            <p>Quando um serviço for aprovado, ele aparecerá aqui.</p>
            <a href="<?= aptus_base() ?>/anuncios" class="btn-primary">Ver Anúncios</a>
        </div>
    <?php else: ?>

        <?php
        $ativos     = array_filter($interesses, fn($i) => $i['situacao'] == 'ativo');
        $concluidos = array_filter($interesses, fn($i) => $i['situacao'] == 'concluido');
        ?>

        <?php if (!empty($ativos)): ?>
            <h2>Em Andamento</h2>
            <div class="interesses-grid">
                <?php foreach ($ativos as $interesse):
                    $isContratante = ($usuario && $interesse['id_contratante'] == $usuario['id']);
                    $outroNome     = $isContratante
                        ? ($interesse['freelancer_nome']  ?? 'Prestador')
                        : ($interesse['contratante_nome'] ?? 'Cliente');
                    $papelOutro    = $isContratante ? 'prestador' : 'cliente';

                    // "Concluir serviço": cada parte confirma; com as duas, vira concluído
                    $jaConfirmou         = !empty($interesse[$isContratante ? 'concluido_contratante' : 'concluido_freelancer']);
                    $outroConfirmou      = !empty($interesse[$isContratante ? 'concluido_freelancer' : 'concluido_contratante']);
                    $pagamentoDivergente = (isset($interesse['situacao_final']) && $interesse['situacao_final'] === 'divergente');

                    if ($jaConfirmou) {
                        $statusConfirmacao = 'Você concluiu. Aguardando o ' . $papelOutro . ' confirmar.';
                        $statusClasse      = 'aguardando';
                    } elseif ($outroConfirmou) {
                        $statusConfirmacao = 'O ' . $papelOutro . ' já concluiu. Falta só você confirmar!';
                        $statusClasse      = 'sua-vez';
                    } else {
                        $statusConfirmacao = 'Em andamento. Quando o serviço terminar, clique em "Concluir serviço".';
                        $statusClasse      = 'andamento';
                    }
                ?>
                    <div class="interesse-card">
                        <div class="interesse-header">
                            <div class="interesse-cliente">
                                <i class="fas fa-user-circle"></i>
                                <div>
                                    <strong><?= htmlspecialchars($outroNome) ?></strong>
                                    <span class="interesse-data"><?= date('d/m/Y H:i', strtotime($interesse['data_interesse'])) ?></span>
                                </div>
                            </div>
                            <span class="interesse-status ativo">Ativo</span>
                            <?php if ($pagamentoDivergente): ?>
                                <span class="interesse-status divergente">Divergente</span>
                            <?php endif; ?>
                        </div>

                        <div class="interesse-body">
                            <h3><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'Serviço') ?></h3>
                            <p class="interesse-preco">
                                R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?>
                            </p>
                            <div class="interesse-status-confirmacao conclusao-<?= $statusClasse ?>">
                                <i class="fas <?= $statusClasse === 'sua-vez' ? 'fa-bell' : ($statusClasse === 'aguardando' ? 'fa-hourglass-half' : 'fa-tools') ?>" aria-hidden="true"></i>
                                <?= htmlspecialchars($statusConfirmacao) ?>
                            </div>
                            <?php if ($pagamentoDivergente): ?>
                                <p style="color:var(--cor-erro-escuro);">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Pagamento divergente. Abra uma disputa para resolver.
                                </p>
                            <?php endif; ?>
                        </div>

                        <p class="conclusao-dica">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            Depois que você e o <?= $papelOutro ?> concluírem, cada um poderá avaliar o outro.
                        </p>

                        <div class="interesse-acoes">
                            <a href="<?= aptus_base() ?>/chat/<?= url_id($interesse['id_interesse']) ?>" class="btn-chat">Chat</a>
                            <a href="<?= aptus_base() ?>/interesses/detalhes/<?= url_id($interesse['id_interesse']) ?>" class="btn-detalhes">Detalhes</a>

                            <?php if (!$pagamentoDivergente): ?>

                                <?php if (!$jaConfirmou): ?>
                                    <form method="POST" action="<?= aptus_base() ?>/interesses/confirmar-execucao" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int) $interesse['id_interesse'] ?>">
                                        <?= CsrfMiddleware::field() ?>
                                        <button type="submit" class="btn-confirmar"
                                                onclick="return confirm('Confirmar que o serviço foi feito? Quando o <?= $papelOutro ?> confirmar também, o serviço é concluído.')">
                                            <i class="fas fa-check-double" aria-hidden="true"></i> Concluir serviço
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="aviso-aguardando">
                                        <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                                        Aguardando o <?= $papelOutro ?>
                                    </span>
                                <?php endif; ?>

                            <?php else: ?>
                                <a href="<?= aptus_base() ?>/disputas/criar?interesse_id=<?= url_id($interesse['id_interesse']) ?>"
                                   class="btn-disputa">Abrir Disputa</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($concluidos)): ?>
            <h2>Concluídos</h2>
            <div class="interesses-grid">
                <?php foreach ($concluidos as $interesse):
                    $isContratante = ($usuario && $interesse['id_contratante'] == $usuario['id']);
                    $outroNome     = $isContratante
                        ? ($interesse['freelancer_nome']  ?? 'Freelancer')
                        : ($interesse['contratante_nome'] ?? 'Cliente');
                    $usuarioJaAvaliou = !empty($interesse['usuario_ja_avaliou']);
                ?>
                    <div class="interesse-card concluido">
                        <div class="interesse-header">
                            <div class="interesse-cliente">
                                <i class="fas fa-user-circle"></i>
                                <div>
                                    <strong><?= htmlspecialchars($outroNome) ?></strong>
                                    <span class="interesse-data"><?= date('d/m/Y H:i', strtotime($interesse['data_conclusao'] ?? $interesse['data_interesse'])) ?></span>
                                </div>
                            </div>
                            <span class="interesse-status concluido">Concluído</span>
                        </div>

                        <div class="interesse-body">
                            <h3><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'Serviço') ?></h3>
                            <p class="interesse-preco">
                                R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?>
                            </p>
                        </div>

                        <?php if (!$usuarioJaAvaliou): ?>
                            <div class="avaliacao-form-container">
                                <h4>Avaliar <?= $isContratante ? 'o prestador' : 'o cliente' ?></h4>
                                <p>Como foi trabalhar com <?= htmlspecialchars($outroNome) ?>?</p>

                                <form method="POST" action="<?= aptus_base() ?>/avaliacoes/salvar" class="avaliacao-form">
                                    <input type="hidden" name="interesse_id" value="<?= (int) $interesse['id_interesse'] ?>">

                                    <div class="avaliacao-estrelas">
                                        <label>Nota</label>
                                        <div class="estrelas">
                                            <?php // De 5 para 1: o CSS usa row-reverse, então na tela fica 1..5 da esquerda para a direita
                                                  for ($n = 5; $n >= 1; $n--): ?>
                                                <input type="radio" name="nota" value="<?= $n ?>"
                                                       id="starC<?= $n ?>_<?= $interesse['id_interesse'] ?>"
                                                       <?= $n === 1 ? 'required' : '' ?>>
                                                <label for="starC<?= $n ?>_<?= $interesse['id_interesse'] ?>" title="<?= $n ?> estrela<?= $n > 1 ? 's' : '' ?>">
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <span class="sr-only"><?= $n ?> estrela<?= $n > 1 ? 's' : '' ?></span>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div class="avaliacao-comentario">
                                        <label for="comentarioC_<?= $interesse['id_interesse'] ?>">Comentário</label>
                                        <textarea id="comentarioC_<?= $interesse['id_interesse'] ?>"
                                                  name="comentario" rows="3"
                                                  placeholder="Descreva sua experiência..."></textarea>
                                    </div>

                                    <div class="avaliacao-acoes">
                                        <button type="submit" class="btn-enviar-avaliacao">Enviar Avaliação</button>
                                    </div>
                                    <?= CsrfMiddleware::field() ?>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="avaliacao-ja-feita">
                                <p><i class="fas fa-check-circle" aria-hidden="true"></i> Você já avaliou este serviço.</p>
                            </div>
                        <?php endif; ?>

                        <div class="interesse-acoes">
                            <a href="<?= aptus_base() ?>/chat/<?= url_id($interesse['id_interesse']) ?>" class="btn-chat">Chat</a>
                            <a href="<?= aptus_base() ?>/interesses/detalhes/<?= url_id($interesse['id_interesse']) ?>" class="btn-detalhes">Detalhes</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="interesses-links">
        <a href="<?= aptus_base() ?>/interesses/pendentes">Propostas Pendentes</a>
        <a href="<?= aptus_base() ?>/interesses/meus">Meus Interesses</a>
        <a href="<?= aptus_base() ?>/">Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>