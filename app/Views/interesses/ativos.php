<?php
// app/Views/interesses/ativos.php

$tituloPagina = $tituloPagina ?? 'Servicos Ativos - Aptus';
$cssPagina = $cssPagina ?? 'interesses.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesses = $interesses ?? [];
$usuario = $_SESSION['usuario'] ?? null;

require_once __DIR__ . '/../../Models/Interesse.php';
require_once __DIR__ . '/../../Models/Avaliacao.php';
$interesseModel = new Interesse();
$avaliacaoModel = new Avaliacao();
?>

<div class="interesses-container">
    <div class="interesses-header">
        <h1>Servicos Ativos</h1>
        <p>Servicos em andamento e concluidos</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($interesses)): ?>
        <div class="interesses-empty">
            <p>Nenhum servico ativo</p>
            <p>Quando um servico for aprovado, ele aparecera aqui.</p>
            <a href="/Aptus/anuncios" class="btn-primary">Ver Anuncios</a>
        </div>
    <?php else: ?>
        
        <?php 
        $ativos = array_filter($interesses, function($i) {
            return $i['situacao'] == 'ativo';
        });
        $concluidos = array_filter($interesses, function($i) {
            return $i['situacao'] == 'concluido';
        });
        ?>

        <?php if (!empty($ativos)): ?>
            <h2>Em Andamento</h2>
            <div class="interesses-grid">
                <?php foreach ($ativos as $interesse): 
                    $isContratante = ($usuario && $interesse['id_contratante'] == $usuario['id']);
                    $isFreelancer = ($usuario && $interesse['id_freelancer'] == $usuario['id']);
                    $outroNome = $isContratante ? ($interesse['freelancer_nome'] ?? 'Freelancer') : ($interesse['contratante_nome'] ?? 'Cliente');
                    
                    $contratanteConfirmou = $interesse['confirmado_contratante'] ?? false;
                    $freelancerConfirmou = $interesse['confirmado_freelancer'] ?? false;
                    
                    $jaConfirmou = $isContratante ? $contratanteConfirmou : $freelancerConfirmou;
                    $outroConfirmou = $isContratante ? $freelancerConfirmou : $contratanteConfirmou;
                    
                    $usuarioJaAvaliou = $interesseModel->usuarioJaAvaliou($interesse['id_interesse'], $usuario['id']);
                    
                    if ($jaConfirmou && $outroConfirmou) {
                        $statusConfirmacao = 'Ambos confirmaram - Concluido!';
                        $statusCor = '#10b981';
                    } elseif ($jaConfirmou && !$outroConfirmou) {
                        $statusConfirmacao = 'Aguardando confirmacao do ' . ($isContratante ? 'freelancer' : 'cliente');
                        $statusCor = '#f59e0b';
                    } elseif (!$jaConfirmou && $outroConfirmou) {
                        $statusConfirmacao = ($isContratante ? 'Freelancer' : 'Cliente') . ' ja confirmou. Confirme tambem!';
                        $statusCor = '#3b82f6';
                    } else {
                        $statusConfirmacao = 'Aguardando confirmacao de ambos';
                        $statusCor = '#94a3b8';
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
                        </div>

                        <div class="interesse-body">
                            <h3><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'Servico') ?></h3>
                            <p class="interesse-preco">
                                R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?>
                            </p>
                            <div class="interesse-status-confirmacao" style="color: <?= $statusCor ?>;">
                                <strong>Status:</strong> <?= $statusConfirmacao ?>
                            </div>
                            <?php if ($jaConfirmou): ?>
                                <p class="interesse-confirmado">
                                    Voce ja confirmou a execucao do servico.
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- FORMULARIO DE AVALIACAO -->
                        <?php if (!$usuarioJaAvaliou): ?>
                            <div class="avaliacao-form-container">
                                <h4>Avaliar Servico</h4>
                                <p>Avalie o servico antes de confirmar a execucao</p>
                                
                                <form method="POST" action="/Aptus/avaliacoes/salvar" class="avaliacao-form">
                                    <input type="hidden" name="interesse_id" value="<?= $interesse['id_interesse'] ?>">
                                    
                                    <div class="avaliacao-estrelas">
                                        <label>Nota</label>
                                        <div class="estrelas">
                                            <input type="radio" name="nota" value="1" id="star1_<?= $interesse['id_interesse'] ?>" required>
                                            <label for="star1_<?= $interesse['id_interesse'] ?>" title="1 estrela"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="2" id="star2_<?= $interesse['id_interesse'] ?>">
                                            <label for="star2_<?= $interesse['id_interesse'] ?>" title="2 estrelas"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="3" id="star3_<?= $interesse['id_interesse'] ?>">
                                            <label for="star3_<?= $interesse['id_interesse'] ?>" title="3 estrelas"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="4" id="star4_<?= $interesse['id_interesse'] ?>">
                                            <label for="star4_<?= $interesse['id_interesse'] ?>" title="4 estrelas"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="5" id="star5_<?= $interesse['id_interesse'] ?>">
                                            <label for="star5_<?= $interesse['id_interesse'] ?>" title="5 estrelas"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                    
                                    <div class="avaliacao-comentario">
                                        <label for="comentario_<?= $interesse['id_interesse'] ?>">Comentario</label>
                                        <textarea id="comentario_<?= $interesse['id_interesse'] ?>" name="comentario" rows="3" placeholder="Descreva sua experiencia com o servico..."></textarea>
                                    </div>
                                    
                                    <div class="avaliacao-acoes">
                                        <button type="submit" class="btn-enviar-avaliacao">
                                            <i class="fas fa-paper-plane"></i> Enviar Avaliacao
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="avaliacao-ja-feita">
                                <p><i class="fas fa-check-circle" style="color: #10b981;"></i> Voce ja avaliou este servico.</p>
                            </div>
                        <?php endif; ?>

                        <div class="interesse-acoes">
                            <a href="/Aptus/chat/<?= $interesse['id_interesse'] ?>" class="btn-chat">
                                Chat
                            </a>
                            <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>" class="btn-detalhes">
                                Detalhes
                            </a>
                            
                            <?php if ($usuarioJaAvaliou && !$jaConfirmou): ?>
                                <form method="POST" action="/Aptus/interesses/confirmar-execucao" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                                    <button type="submit" class="btn-confirmar" onclick="return confirm('Confirmar que o servico foi executado?')">
                                        Confirmar Execucao
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($concluidos)): ?>
            <h2>Concluidos</h2>
            <div class="interesses-grid">
                <?php foreach ($concluidos as $interesse): 
                    $isContratante = ($usuario && $interesse['id_contratante'] == $usuario['id']);
                    $isFreelancer = ($usuario && $interesse['id_freelancer'] == $usuario['id']);
                    $outroNome = $isContratante ? ($interesse['freelancer_nome'] ?? 'Freelancer') : ($interesse['contratante_nome'] ?? 'Cliente');
                    
                    $usuarioJaAvaliou = $interesseModel->usuarioJaAvaliou($interesse['id_interesse'], $usuario['id']);
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
                            <span class="interesse-status concluido">Concluido</span>
                        </div>

                        <div class="interesse-body">
                            <h3><?= htmlspecialchars($interesse['anuncio_titulo'] ?? 'Servico') ?></h3>
                            <p class="interesse-preco">
                                R$ <?= number_format($interesse['anuncio_preco'] ?? 0, 2, ',', '.') ?>
                            </p>
                        </div>

                        <?php if (!$usuarioJaAvaliou): ?>
                            <div class="avaliacao-form-container">
                                <h4>Avaliar Servico</h4>
                                <p>Deixe sua avaliacao sobre o servico</p>
                                
                                <form method="POST" action="/Aptus/avaliacoes/salvar" class="avaliacao-form">
                                    <input type="hidden" name="interesse_id" value="<?= $interesse['id_interesse'] ?>">
                                    
                                    <div class="avaliacao-estrelas">
                                        <label>Nota</label>
                                        <div class="estrelas">
                                            <input type="radio" name="nota" value="1" id="star1_c_<?= $interesse['id_interesse'] ?>" required>
                                            <label for="star1_c_<?= $interesse['id_interesse'] ?>" title="1 estrela"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="2" id="star2_c_<?= $interesse['id_interesse'] ?>">
                                            <label for="star2_c_<?= $interesse['id_interesse'] ?>" title="2 estrelas"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="3" id="star3_c_<?= $interesse['id_interesse'] ?>">
                                            <label for="star3_c_<?= $interesse['id_interesse'] ?>" title="3 estrelas"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="4" id="star4_c_<?= $interesse['id_interesse'] ?>">
                                            <label for="star4_c_<?= $interesse['id_interesse'] ?>" title="4 estrelas"><i class="fas fa-star"></i></label>
                                            
                                            <input type="radio" name="nota" value="5" id="star5_c_<?= $interesse['id_interesse'] ?>">
                                            <label for="star5_c_<?= $interesse['id_interesse'] ?>" title="5 estrelas"><i class="fas fa-star"></i></label>
                                        </div>
                                    </div>
                                    
                                    <div class="avaliacao-comentario">
                                        <label for="comentario_c_<?= $interesse['id_interesse'] ?>">Comentario</label>
                                        <textarea id="comentario_c_<?= $interesse['id_interesse'] ?>" name="comentario" rows="3" placeholder="Descreva sua experiencia com o servico..."></textarea>
                                    </div>
                                    
                                    <div class="avaliacao-acoes">
                                        <button type="submit" class="btn-enviar-avaliacao">
                                            <i class="fas fa-paper-plane"></i> Enviar Avaliacao
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="avaliacao-ja-feita">
                                <p><i class="fas fa-check-circle" style="color: #10b981;"></i> Voce ja avaliou este servico.</p>
                            </div>
                        <?php endif; ?>

                        <div class="interesse-acoes">
                            <a href="/Aptus/chat/<?= $interesse['id_interesse'] ?>" class="btn-chat">
                                Chat
                            </a>
                            <a href="/Aptus/interesses/detalhes/<?= $interesse['id_interesse'] ?>" class="btn-detalhes">
                                Detalhes
                            </a>
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