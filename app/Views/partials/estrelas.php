<?php
// app/Views/partials/estrelas.php — nota em estrelas a partir de dados reais.
// Espera: $estrelasNota (float|null), $estrelasTotal (int). Opcional: $estrelasCompacto (bool).
// Sem avaliações não mostra nota nenhuma (nada de 0.0 ou estrelas vazias que parecem nota ruim).
$__nota  = (float) ($estrelasNota ?? 0);
$__total = (int) ($estrelasTotal ?? 0);
$__comp  = !empty($estrelasCompacto);
?>
<?php if ($__total === 0): ?>
    <span class="nota-vazia"><i class="far fa-star" aria-hidden="true"></i> Sem avaliações ainda</span>
<?php else: ?>
    <span class="nota-estrelas" aria-label="Nota <?= number_format($__nota, 1, ',', '') ?> de 5, <?= $__total ?> avaliação(ões)">
        <?php if (!$__comp): ?>
            <?php for ($__i = 1; $__i <= 5; $__i++): ?>
                <?php if ($__nota >= $__i - 0.25): ?>
                    <i class="fas fa-star" aria-hidden="true"></i>
                <?php elseif ($__nota >= $__i - 0.75): ?>
                    <i class="fas fa-star-half-alt" aria-hidden="true"></i>
                <?php else: ?>
                    <i class="far fa-star" aria-hidden="true"></i>
                <?php endif; ?>
            <?php endfor; ?>
        <?php else: ?>
            <i class="fas fa-star" aria-hidden="true"></i>
        <?php endif; ?>
        <strong class="nota"><?= number_format($__nota, 1, ',', '') ?></strong>
        <span class="total">(<?= $__total ?> <?= $__total === 1 ? 'avaliação' : 'avaliações' ?>)</span>
    </span>
<?php endif; ?>
