<?php
// app/Views/anuncios/_cards.php — cards de anúncio (listagem e "Ver mais")
require_once __DIR__ . '/../../Helpers/UploadHelper.php';
?>
<?php foreach ($anuncios as $anuncio): ?>
    <div class="profissional-card">
        <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>" class="card-capa" tabindex="-1" aria-hidden="true">
            <img src="<?= htmlspecialchars(\UploadHelper::capaUrl($anuncio['foto_capa'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                 alt="" loading="lazy">
        </a>
        <div class="categoria-tag">
            <i class="<?= htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
            <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
        </div>

        <h3><?= htmlspecialchars($anuncio['titulo']) ?></h3>

    <div class="card-nota">
        <?php $estrelasNota = $anuncio['nota_anuncio'] ?? 0; $estrelasTotal = $anuncio['total_avaliacoes_anuncio'] ?? 0; $estrelasCompacto = true; require __DIR__ . '/../partials/estrelas.php'; $estrelasCompacto = false; ?>
    </div>

        <p class="descricao">
            <?= htmlspecialchars(mb_strimwidth($anuncio['descricao'], 0, 150, '...')) ?>
        </p>

        <div class="info-footer">
            <span class="autor">
                <i class="fas fa-user-circle"></i>
                <?= htmlspecialchars($anuncio['freelancer_nome']) ?>
            </span>
            <span class="preco">
                <i class="fas fa-tag"></i>
                R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
            </span>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <a href="<?= aptus_base() ?>/anuncios/<?= htmlspecialchars($anuncio['slug']) ?>"
               class="btn-perfil" style="flex: 1; margin-top: 0;">
                <i class="fas fa-eye"></i> Ver Detalhes
            </a>
        </div>
    </div>
<?php endforeach; ?>
