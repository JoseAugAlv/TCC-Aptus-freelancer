<?php
// app/Views/pagamentos/confirmar.php
require_once __DIR__ . '/../../Middleware/CsrfMiddleware.php';
require_once __DIR__ . '/../../Models/Configuracao.php';

$tituloPagina = $tituloPagina ?? 'Confirmar Pagamento - Aptus';
$cssPagina    = $cssPagina    ?? 'pagamentos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$interesse   = $interesse   ?? null;
$confirmacao = $confirmacao ?? null;
$papel       = $papel       ?? '';
$usuarioId   = (int) ($_SESSION['usuario']['id'] ?? 0);

if (!$interesse) {
    echo '<div class="pagamento-section"><p>Interesse não encontrado.</p></div>';
    require_once __DIR__ . '/../layouts/footer.php';
    return;
}

$souContratante = ((int) $interesse['id_contratante'] === $usuarioId);

$nomeOutro = $souContratante
    ? ($interesse['freelancer_nome']  ?? ('Freelancer #' . (int) $interesse['id_freelancer']))
    : ($interesse['contratante_nome'] ?? ('Contratante #' . (int) $interesse['id_contratante']));

$tituloServico = $interesse['anuncio_titulo'] ?? ('Interesse #' . (int) $interesse['id_interesse']);
$valorSugerido = (float) ($interesse['anuncio_preco'] ?? 0);

$acaoUrl = ($papel === 'freelancer')
    ? '/Aptus/pagamentos/confirmar-freelancer'
    : '/Aptus/pagamentos/confirmar-contratante';

$jaConfirmou = false;
if ($confirmacao) {
    $jaConfirmou = ($papel === 'freelancer')
        ? !empty($confirmacao['confirmado_freelancer'])
        : !empty($confirmacao['confirmado_contratante']);
}

// Formas de pagamento habilitadas nas configurações
$cfgPag = new Configuracao();
$formasHabilitadas = [
    'pix'           => $cfgPag->get('pagamento_pix')           === '1',
    'transferencia' => $cfgPag->get('pagamento_transferencia') === '1',
    'dinheiro'      => $cfgPag->get('pagamento_dinheiro')      === '1',
    'cartao'        => $cfgPag->get('pagamento_cartao')        === '1',
];

if (!array_filter($formasHabilitadas)) {
    $formasHabilitadas['outro'] = true; // fallback
}
?>

<section class="pagamento-section animate-in">
    <div class="pagamento-container">
        <div class="pagamento-header">
            <h2>Confirmar Pagamento</h2>
            <p>Verifique os detalhes antes de confirmar</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo'], ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($_SESSION['flash']['mensagem'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="pagamento-detalhes">
            <div class="detalhe-row">
                <span class="label">Interesse:</span>
                <span class="valor">#<?= (int) $interesse['id_interesse'] ?></span>
            </div>
            <div class="detalhe-row">
                <span class="label">Serviço:</span>
                <span class="valor"><?= htmlspecialchars($tituloServico, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="detalhe-row">
                <span class="label">Você é:</span>
                <span class="valor"><?= $souContratante ? 'Contratante' : 'Freelancer' ?></span>
            </div>
            <div class="detalhe-row">
                <span class="label">Outra parte:</span>
                <span class="valor"><?= htmlspecialchars($nomeOutro, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="detalhe-row">
                <span class="label">Valor combinado no anúncio:</span>
                <span class="valor">R$ <?= number_format($valorSugerido, 2, ',', '.') ?></span>
            </div>
        </div>

        <?php if ($jaConfirmou): ?>
            <div class="pagamento-card">
                <p><strong>Você já registrou sua confirmação para este pagamento.</strong></p>
                <p>Aguarde a confirmação da outra parte para o status final.</p>
            </div>
            <div class="form-actions">
                <a href="/Aptus/pagamentos" class="btn btn-secondary">Voltar</a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= htmlspecialchars($acaoUrl, ENT_QUOTES, 'UTF-8') ?>" class="pagamento-form-confirmar">
                <input type="hidden" name="interesse_id" value="<?= (int) $interesse['id_interesse'] ?>">
                <?= CsrfMiddleware::field() ?>

                <div class="form-group">
                    <label for="valor">Valor <?= $papel === 'freelancer' ? 'recebido' : 'pago' ?> (R$)</label>
                    <input type="text" name="valor" id="valor" required
                           data-mask="money"
                           value="<?= htmlspecialchars(number_format($valorSugerido, 2, ',', '.'), ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <?php if ($papel !== 'freelancer'): ?>
                    <div class="form-group">
                        <label for="forma_pagamento">Forma de pagamento</label>
                        <select name="forma_pagamento" id="forma_pagamento" required>
                            <option value="">Selecione...</option>
                            <?php if ($formasHabilitadas['pix'] ?? false): ?>
                                <option value="pix">PIX</option>
                            <?php endif; ?>
                            <?php if ($formasHabilitadas['transferencia'] ?? false): ?>
                                <option value="transferencia">Transferência bancária</option>
                            <?php endif; ?>
                            <?php if ($formasHabilitadas['dinheiro'] ?? false): ?>
                                <option value="dinheiro">Dinheiro</option>
                            <?php endif; ?>
                            <?php if ($formasHabilitadas['cartao'] ?? false): ?>
                                <option value="cartao">Cartão</option>
                            <?php endif; ?>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="data_pagamento">Data <?= $papel === 'freelancer' ? 'do recebimento' : 'do pagamento' ?></label>
                    <input type="date" name="data_pagamento" id="data_pagamento" required
                           value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label for="observacao">Observação (opcional)</label>
                    <textarea name="observacao" id="observacao" rows="3"
                              placeholder="Detalhes adicionais, comprovante, etc."></textarea>
                </div>

                <div class="form-actions">
                    <a href="/Aptus/pagamentos" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary btn-full">Confirmar</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>