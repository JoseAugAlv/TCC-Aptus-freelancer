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
                    
                    // Verificar se pagamento divergente
                    $pagamentoDivergente = false;
                    if (isset($interesse['situacao_final']) && $interesse['situacao_final'] == 'divergente') {
                        $pagamentoDivergente = true;
                    }
                    
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
                            <?php if ($pagamentoDivergente): ?>
                                <span class="interesse-status divergente">Divergente</span>
                            <?php endif; ?>
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
                            <?php if ($pagamentoDivergente): ?>
                                <p class="interesse-pagamento-divergente" style="color: #dc2626;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Pagamento divergente. Abra uma disputa para resolver.
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- FORMULARIO DE AVALIACAO -->
                        <?php if (!$usuarioJaAvaliou && !$pagamentoDivergente): ?>
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
                                            Enviar Avaliacao
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php elseif ($usuarioJaAvaliou && !$pagamentoDivergente): ?>
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
                            
                            <?php if ($usuarioJaAvaliou && !$jaConfirmou && !$pagamentoDivergente): ?>
                                <form method="POST" action="/Aptus/interesses/confirmar-execucao" style="display: inline;">
                                    <input type="hidden" name="id" value="<?= $interesse['id_interesse'] ?>">
                                    <button type="submit" class="btn-confirmar" onclick="return confirm('Confirmar que o servico foi executado?')">
                                        Confirmar Execucao
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($pagamentoDivergente): ?>
                                <a href="/Aptus/disputas/criar?interesse_id=<?= $interesse['id_interesse'] ?>" class="btn-disputa">
                                    Abrir Disputa
                                </a>
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
                            <?php if (!$usuarioJaAvaliou): ?>
                                <p class="interesse-avaliacao-pendente" style="color: #f59e0b;">
                                    Avalie o servico para concluir o processo.
                                </p>
                            <?php endif; ?>
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
                                            Enviar Avaliacao
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

<style>
.interesses-container {
    max-width: 900px;
    margin: 120px auto 40px;
    padding: 0 20px;
}

.interesses-header h1 {
    color: #006577;
    font-size: 1.8rem;
    margin-bottom: 4px;
}

.interesses-header p {
    color: #555;
}

.interesses-empty {
    text-align: center;
    padding: 60px 20px;
    background: #f8fafc;
    border-radius: 12px;
    border: 2px dashed #d1d5db;
}

.interesses-empty p {
    color: #6b7280;
    margin-bottom: 8px;
}

.btn-primary {
    display: inline-block;
    padding: 10px 24px;
    background: #006577;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
}

.btn-primary:hover {
    background: #004d5c;
}

.interesses-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.interesse-card {
    background: #fff;
    padding: 16px 20px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s;
}

.interesse-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.interesse-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    flex-wrap: wrap;
    gap: 8px;
}

.interesse-cliente {
    display: flex;
    align-items: center;
    gap: 10px;
}

.interesse-cliente i {
    font-size: 1.8rem;
    color: #94a3b8;
}

.interesse-cliente strong {
    display: block;
    color: #1a2f3e;
}

.interesse-data {
    font-size: 0.75rem;
    color: #94a3b8;
}

.interesse-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.interesse-status.ativo {
    background: #dbeafe;
    color: #1e40af;
}

.interesse-status.concluido {
    background: #d1fae5;
    color: #065f46;
}

.interesse-status.divergente {
    background: #fef2f2;
    color: #dc2626;
}

.interesse-body h3 {
    color: #006577;
    margin: 0 0 4px 0;
    font-size: 1.1rem;
}

.interesse-preco {
    font-weight: 700;
    color: #C9A227;
    margin: 4px 0;
}

.interesse-status-confirmacao {
    font-size: 0.85rem;
    margin: 4px 0;
}

.interesse-confirmado {
    color: #10b981;
    font-size: 0.85rem;
    margin: 4px 0;
}

.interesse-pagamento-divergente {
    font-size: 0.85rem;
    margin: 4px 0;
}

.avaliacao-form-container {
    background: #f8fafc;
    padding: 16px 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin: 12px 0;
}

.avaliacao-form-container h4 {
    color: #006577;
    margin: 0 0 4px 0;
}

.avaliacao-form-container p {
    color: #6b7280;
    font-size: 0.85rem;
    margin: 0 0 12px 0;
}

.avaliacao-form .avaliacao-estrelas {
    margin-bottom: 12px;
}

.avaliacao-form .avaliacao-estrelas label {
    display: block;
    font-weight: 600;
    color: #1a2f3e;
    margin-bottom: 4px;
}

.avaliacao-form .estrelas {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 4px;
}

.avaliacao-form .estrelas input {
    display: none;
}

.avaliacao-form .estrelas label {
    font-size: 1.5rem;
    color: #d1d5db;
    cursor: pointer;
    transition: all 0.2s;
    padding: 0 2px;
}

.avaliacao-form .estrelas label:hover,
.avaliacao-form .estrelas label:hover ~ label,
.avaliacao-form .estrelas input:checked ~ label {
    color: #f59e0b;
}

.avaliacao-form .avaliacao-comentario {
    margin-bottom: 12px;
}

.avaliacao-form .avaliacao-comentario label {
    display: block;
    font-weight: 600;
    color: #1a2f3e;
    margin-bottom: 4px;
}

.avaliacao-form .avaliacao-comentario textarea {
    width: 100%;
    padding: 8px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: inherit;
    resize: vertical;
    transition: all 0.3s;
}

.avaliacao-form .avaliacao-comentario textarea:focus {
    border-color: #006577;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 101, 119, 0.1);
}

.btn-enviar-avaliacao {
    padding: 8px 20px;
    background: #006577;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-enviar-avaliacao:hover {
    background: #004d5c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 101, 119, 0.3);
}

.avaliacao-ja-feita {
    background: #f0fdf4;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid #10b981;
    margin: 12px 0;
}

.avaliacao-ja-feita p {
    color: #065f46;
    margin: 0;
    font-weight: 500;
}

.interesse-acoes {
    display: flex;
    gap: 10px;
    margin-top: 12px;
    flex-wrap: wrap;
}

.btn-chat {
    padding: 8px 16px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-chat:hover {
    background: #2563eb;
}

.btn-detalhes {
    padding: 8px 16px;
    background: #6b7280;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-detalhes:hover {
    background: #4b5563;
}

.btn-confirmar {
    padding: 8px 16px;
    background: #10b981;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-confirmar:hover {
    background: #059669;
}

.btn-disputa {
    padding: 8px 16px;
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-disputa:hover {
    background: #b91c1c;
}

.interesses-links {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.interesses-links a {
    color: #006577;
    text-decoration: none;
    font-weight: 600;
}

.interesses-links a:hover {
    color: #C9A227;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>