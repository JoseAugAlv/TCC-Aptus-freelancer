<?php
// app/Views/favoritos/index.php

$tituloPagina = $tituloPagina ?? 'Meus Favoritos - Aptus';
$cssPagina = $cssPagina ?? 'favoritos.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$favoritos = $favoritos ?? [];
?>

<div class="favoritos-container">
    <div class="favoritos-header">
        <h1><i class="fas fa-heart" style="color: #ef4444;"></i> Meus Favoritos</h1>
        <p>Servicos que voce salvou para ver depois</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if (empty($favoritos)): ?>
        <div class="favoritos-empty">
            <i class="fas fa-heart" style="font-size: 4rem; color: #d1d5db;"></i>
            <h3>Voce ainda nao tem favoritos</h3>
            <p>Explore os servicos e salve seus favoritos clicando no coracao.</p>
            <a href="/Aptus/anuncios" class="btn-explorar">
                <i class="fas fa-search"></i> Explorar Servicos
            </a>
        </div>
    <?php else: ?>
        <div class="favoritos-grid">
            <?php foreach ($favoritos as $favorito): ?>
                <div class="favorito-card" data-anuncio-id="<?= $favorito['id_anuncio'] ?>">
                    <div class="favorito-imagem">
                        <?php if (!empty($favorito['foto_capa'])): ?>
                            <img src="/Aptus/public/uploads/anuncios/<?= htmlspecialchars($favorito['foto_capa']) ?>" 
                                 alt="<?= htmlspecialchars($favorito['titulo']) ?>">
                        <?php else: ?>
                            <div class="favorito-sem-imagem">
                                <i class="fas fa-briefcase"></i>
                            </div>
                        <?php endif; ?>
                        <button class="btn-remover-favorito" data-anuncio-id="<?= $favorito['id_anuncio'] ?>">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="favorito-info">
                        <div class="favorito-categoria">
                            <i class="<?= htmlspecialchars($favorito['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
                            <?= htmlspecialchars($favorito['categoria_nome'] ?? 'Geral') ?>
                        </div>
                        <h3>
                            <a href="/Aptus/anuncios/<?= htmlspecialchars($favorito['slug']) ?>">
                                <?= htmlspecialchars($favorito['titulo']) ?>
                            </a>
                        </h3>
                        <p class="favorito-descricao">
                            <?= htmlspecialchars(mb_strimwidth($favorito['descricao'] ?? '', 0, 120, '...')) ?>
                        </p>
                        <div class="favorito-footer">
                            <div class="favorito-freelancer">
                                <?php if (!empty($favorito['freelancer_foto']) && $favorito['freelancer_foto'] != 'default.png'): ?>
                                    <img src="/Aptus/public/uploads/<?= htmlspecialchars($favorito['freelancer_foto']) ?>" 
                                         alt="<?= htmlspecialchars($favorito['freelancer_nome']) ?>"
                                         class="avatar-mini">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($favorito['freelancer_nome']) ?></span>
                            </div>
                            <div class="favorito-preco">
                                R$ <?= number_format($favorito['preco'], 2, ',', '.') ?>
                            </div>
                        </div>
                        <div class="favorito-stats">
                            <span><i class="fas fa-star" style="color: #f59e0b;"></i> <?= number_format($favorito['nota_media'] ?? 0, 1) ?></span>
                            <span><i class="fas fa-eye"></i> <?= $favorito['visualizacoes'] ?? 0 ?></span>
                            <span><i class="fas fa-handshake"></i> <?= $favorito['total_interesses'] ?? 0 ?></span>
                        </div>
                        <div class="favorito-acoes">
                            <a href="/Aptus/anuncios/<?= htmlspecialchars($favorito['slug']) ?>" class="btn-ver">
                                <i class="fas fa-eye"></i> Ver Servico
                            </a>
                            <button class="btn-remover-favorito-texto" data-anuncio-id="<?= $favorito['id_anuncio'] ?>">
                                <i class="fas fa-heart-broken"></i> Remover
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="favoritos-voltar">
        <a href="/Aptus/"><i class="fas fa-arrow-left"></i> Voltar</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Remover favorito (botao X na imagem)
    document.querySelectorAll('.btn-remover-favorito').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var anuncioId = this.dataset.anuncioId;
            var card = this.closest('.favorito-card');
            removerFavorito(anuncioId, card);
        });
    });

    // Remover favorito (botao texto)
    document.querySelectorAll('.btn-remover-favorito-texto').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var anuncioId = this.dataset.anuncioId;
            var card = this.closest('.favorito-card');
            removerFavorito(anuncioId, card);
        });
    });

    function removerFavorito(anuncioId, card) {
        Swal.fire({
            title: 'Remover dos favoritos?',
            text: 'Voce tem certeza que deseja remover este servico dos favoritos?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sim, remover',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                var formData = new FormData();
                formData.append('anuncio_id', anuncioId);

                fetch('/Aptus/favoritos/remover', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success) {
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(function() {
                                card.remove();
                                // Verificar se ainda tem favoritos
                                var cards = document.querySelectorAll('.favorito-card');
                                if (cards.length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                        Swal.fire({
                            icon: 'success',
                            title: 'Removido!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: data.message
                        });
                    }
                })
                .catch(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: 'Erro ao remover dos favoritos. Tente novamente.'
                    });
                });
            }
        });
    }
});
</script>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>