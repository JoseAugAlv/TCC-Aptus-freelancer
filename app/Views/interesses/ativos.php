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
            <a href="/Aptus/anuncios" class="btn-primary">Ver Anúncios</a>
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
                    $isContratante    = ($usuario && $interesse['id_contratante'] == $usuario['id']);
                    $usuarioJaAvaliou = !empty($interesse['usuario_ja_avaliou']);
                    $outroJaAvaliou   = !empty($interesse['outro_ja_avaliou']);
                    $ambosJaAvaliaram = $usuarioJaAvaliou && $outroJaAvaliou;
                    $outroNome        = $isContratante
                        ? ($interesse['freelancer_nome']  ?? 'Freelancer')
                        : ($interesse['contratante_nome'] ?? 'Cliente');

                    $contratanteConfirmou = $interesse['confirmado_contratante'] ?? false;
                    $freelancerConfirmou  = $interesse['confirmado_freelancer']  ?? false;
                    $jaConfirmou          = $isContratante ? $contratanteConfirmou : $freelancerConfirmou;
                    $outroConfirmou       = $isContratante ? $freelancerConfirmou  : $contratanteConfirmou;
                    $pagamentoDivergente  = (isset($interesse['situacao_final']) && $interesse['situacao_final'] === 'divergente');

                    if ($jaConfirmou && $outroConfirmou) {
                        $statusConfirmacao = 'Ambos confirmaram - Concluído!';
                        $statusCor         = '#10b981';
                    } elseif ($jaConfirmou) {
                        $statusConfirmacao = 'Aguardando confirmação do ' . ($isContratante ? 'freelancer' : 'cliente');
                        $statusCor         = '#f59e0b';
                    } elseif ($outroConfirmou) {
                        $statusConfirmacao = ($isContratante ? 'Freelancer' : 'Cliente') . ' já confirmou. Confirme também!';
                        $statusCor         = '#3b82f6';
                    } else {
                        $statusConfirmacao = 'Aguardando confirmação de ambos';
                        $statusCor         = '#94a3b8';
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
                            <div class="interesse-status-confirmacao" style="color: <?= $statusCor ?>;">
                                <strong>Status:</strong> <?= $statusConfirmacao ?>
                            </div>
                            <?php if ($jaConfirmou): ?>
                                <p class="interesse-confirmado">Você já confirmou a execução do serviço.</p>
                            <?php endif; ?>
                            <?php if ($pagamentoDivergente): ?>
                                <p style="color:#dc2626;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Pagamento divergente. Abra uma disputa para resolver.
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if (!$usuarioJaAvaliou && !$pagamentoDivergente): ?>
                            <div class="avaliacao-form-container">
                                <h4>Avaliar Serviço</h4>
                                <p>Avalie o serviço antes de confirmar a execução</p>

                                <form method="POST" action="/Aptus/avaliacoes/salvar" class="avaliacao-form">
                                    <input type="hidden" name="interesse_id" value="<?= (int) $interesse['id_interesse'] ?>">

                                    <div class="avaliacao-estrelas">
                                        <label>Nota</label>
                                        <div class="estrelas">
                                            <?php for ($n = 1; $n <= 5; $n++): ?>
                                                <input type="radio" name="nota" value="<?= $n ?>"
                                                       id="star<?= $n ?>_<?= $interesse['id_interesse'] ?>"
                                                       <?= $n === 1 ? 'required' : '' ?>>
                                                <label for="star<?= $n ?>_<?= $interesse['id_interesse'] ?>">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                    </div>

                                    <div class="avaliacao-comentario">
                                        <label for="comentario_<?= $interesse['id_interesse'] ?>">Comentário</label>
                                        <textarea id="comentario_<?= $interesse['id_interesse'] ?>"
                                                  name="comentario" rows="3"
                                                  placeholder="Descreva sua experiência..."></textarea>
                                    </div>

                                    <div class="avaliacao-acoes">
                                        <button type="submit" class="btn-enviar-avaliacao">Enviar Avaliação</button>
                                    </div>
                                    <?= CsrfMiddleware::field() ?>
                                </form>
                            </div>
                        <?php elseif ($usuarioJaAvaliou && !$pagamentoDivergente): ?>
                            <div class="avaliacao-ja-feita">
                                <p><i class="fas fa-check-circle" style="color:#10b981;"></i> Você já avaliou este serviço.</p>
                            </div>
                        <?php endif; ?>

                        <div class="interesse-acoes">
                            <a href="/Aptus/chat/<?= $interesse['id_interesse'] ?>" class="btn-chat">Chat</a>
                            <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>" class="btn-detalhes">Detalhes</a>

                            <?php if (!$pagamentoDivergente): ?>

                                <?php if ($ambosJaAvaliaram && !$jaConfirmou): ?>
                                    <form method="POST" action="/Aptus/interesses/confirmar-execucao" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= (int) $interesse['id_interesse'] ?>">
                                        <?= CsrfMiddleware::field() ?>
                                        <button type="submit" class="btn-confirmar"
                                                onclick="return confirm('Confirmar que o serviço foi executado?')">
                                            Confirmar Execução
                                        </button>
                                    </form>

                                <?php elseif ($usuarioJaAvaliou && !$outroJaAvaliou): ?>
                                    <span class="aviso-aguardando"
                                          style="padding:10px 16px; background:#fef3c7; color:#92400e;
                                                 border-radius:8px; font-size:.85rem; display:inline-block;">
                                        <i class="fas fa-hourglass-half"></i>
                                        Aguardando a outra parte avaliar…
                                    </span>
                                <?php endif; ?>

                            <?php else: ?>
                                <a href="/Aptus/disputas/criar?interesse_id=<?= $interesse['id_interesse'] ?>"
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
                                <h4>Avaliar Serviço</h4>
                                <p>Deixe sua avaliação sobre o serviço</p>

                                <form method="POST" action="/Aptus/avaliacoes/salvar" class="avaliacao-form">
                                    <input type="hidden" name="interesse_id" value="<?= (int) $interesse['id_interesse'] ?>">

                                    <div class="avaliacao-estrelas">
                                        <label>Nota</label>
                                        <div class="estrelas">
                                            <?php for ($n = 1; $n <= 5; $n++): ?>
                                                <input type="radio" name="nota" value="<?= $n ?>"
                                                       id="starC<?= $n ?>_<?= $interesse['id_interesse'] ?>"
                                                       <?= $n === 1 ? 'required' : '' ?>>
                                                <label for="starC<?= $n ?>_<?= $interesse['id_interesse'] ?>">
                                                    <i class="fas fa-star"></i>
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
                        <?php endif; ?>

                        <div class="interesse-acoes">
                            <a href="/Aptus/chat/<?= $interesse['id_interesse'] ?>" class="btn-chat">Chat</a>
                            <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>" class="btn-detalhes">Detalhes</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

    <div class="interesses-links">
        <a href="/Aptus/interesses/pendentes">Propostas Pendentes</a>
        <a href="/Aptus/interesses/meus">Meus Interesses</a>
        <a href="/Aptus/">Voltar</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>