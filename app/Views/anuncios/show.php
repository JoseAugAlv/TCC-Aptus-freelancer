<?php
// app/Views/anuncios/show.php

$tituloPagina = $tituloPagina ?? 'Detalhes do Servico - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncio = $anuncio ?? [];
$fotos = $fotos ?? [];
$usuario = $_SESSION['usuario'] ?? null;
$usuarioInteressado = $usuarioInteressado ?? false;
$favoritado = $favoritado ?? false;
$totalFavoritos = $totalFavoritos ?? 0;
?>

<h1>Detalhes do Servico</h1>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="detalhes-container">
    <div class="detalhes-imagem">
        <?php if (!empty($anuncio['foto_capa'])): ?>
            <img src="/Aptus/public/uploads/anuncios/<?= htmlspecialchars($anuncio['foto_capa']) ?>" 
                 alt="<?= htmlspecialchars($anuncio['titulo']) ?>">
        <?php else: ?>
            <i class="fas fa-briefcase"></i>
        <?php endif; ?>
    </div>

    <div class="detalhes-info">
        <div class="detalhes-topo">
            <div class="categoria-badge">
                <i class="<?= htmlspecialchars($anuncio['categoria_icone'] ?? 'fas fa-tag') ?>"></i>
                <?= htmlspecialchars($anuncio['categoria_nome'] ?? 'Geral') ?>
            </div>
            
            <!-- BOTAO FAVORITO -->
            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <button class="btn-favorito <?= $favoritado ? 'ativo' : '' ?>" 
                        data-anuncio-id="<?= $anuncio['id_anuncio'] ?>">
                    <i class="<?= $favoritado ? 'fas' : 'far' ?> fa-heart"></i>
                    <span class="favorito-texto">
                        <?= $favoritado ? 'Favoritado' : 'Favoritar' ?>
                    </span>
                    <span class="favorito-contador">(<?= $totalFavoritos ?? 0 ?>)</span>
                </button>
            <?php endif; ?>
        </div>
        
        <h2><?= htmlspecialchars($anuncio['titulo']) ?></h2>
        
        <p class="preco-destaque">
            <i class="fas fa-tag"></i> R$ <?= number_format($anuncio['preco'], 2, ',', '.') ?>
        </p>

        <div class="avaliacao-detalhes">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <?php if ($i <= round($anuncio['nota_media'] ?? 0)): ?>
                    <i class="fas fa-star"></i>
                <?php else: ?>
                    <i class="far fa-star"></i>
                <?php endif; ?>
            <?php endfor; ?>
            <span class="nota"><?= number_format($anuncio['nota_media'] ?? 0, 1) ?></span>
            <span class="total-avaliacoes">(<?= $anuncio['total_avaliacoes'] ?? 0 ?> avaliacoes)</span>
        </div>

        <p class="descricao-completa">
            <?= nl2br(htmlspecialchars($anuncio['descricao'])) ?>
        </p>

        <div class="info-extra">
            <span><i class="fas fa-eye"></i> <?= $anuncio['visualizacoes'] ?? 0 ?> visualizacoes</span>
            <span><i class="fas fa-heart"></i> <?= $totalFavoritos ?? 0 ?> favoritos</span>
            <span><i class="fas fa-handshake"></i> <?= $anuncio['total_interesses'] ?? 0 ?> interesses</span>
        </div>

        <div class="freelancer-card">
            <div class="freelancer-header">
                <div class="freelancer-avatar">
                    <?php if (!empty($anuncio['foto_perfil']) && $anuncio['foto_perfil'] != 'default.png'): ?>
                        <img src="/Aptus/public/uploads/<?= htmlspecialchars($anuncio['foto_perfil']) ?>" 
                             alt="<?= htmlspecialchars($anuncio['freelancer_nome']) ?>">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <div class="freelancer-info">
                    <h3><?= htmlspecialchars($anuncio['freelancer_nome']) ?></h3>
                    <p>
                        <i class="fas fa-star"></i>
                        <?= number_format($anuncio['nota_media'] ?? 0, 1) ?> / 5.0
                        (<?= $anuncio['total_avaliacoes'] ?? 0 ?> avaliacoes)
                    </p>
                    <?php if (!empty($anuncio['cidade']) || !empty($anuncio['estado'])): ?>
                        <p>
                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($anuncio['cidade'] ?? '') . (!empty($anuncio['cidade']) && !empty($anuncio['estado']) ? ', ' : '') . htmlspecialchars($anuncio['estado'] ?? '') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php if (!empty($anuncio['freelancer_bio'])): ?>
                <p class="freelancer-bio"><?= htmlspecialchars($anuncio['freelancer_bio']) ?></p>
            <?php endif; ?>
        </div>

        <!-- BOTOES DE ACAO -->
        <div class="detalhes-acoes">
            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <?php if ($usuarioInteressado): ?>
                    <button class="btn-interesse btn-enviado" disabled>
                        <i class="fas fa-check-circle"></i> Proposta Enviada
                    </button>
                <?php else: ?>
                    <form method="POST" action="/Aptus/interesses/criar?anuncio=<?= $anuncio['id_anuncio'] ?>" 
                          id="formInteresse">
                        <input type="hidden" name="mensagem" value="Ola! Tenho interesse no seu servico.">
                        <button type="submit" class="btn-interesse" id="btnInteresse">
                            <i class="fas fa-handshake"></i> Tenho Interesse
                        </button>
                    </form>
                <?php endif; ?>
            <?php elseif (!$usuario): ?>
                <a href="/Aptus/login" class="btn-interesse">
                    <i class="fas fa-sign-in-alt"></i> Faca login para ter interesse
                </a>
            <?php endif; ?>
            
            <?php if ($usuario && $usuario['id'] == $anuncio['id_usuario']): ?>
                <a href="/Aptus/anuncios/editar/<?= $anuncio['id_anuncio'] ?>" class="btn-editar">
                    <i class="fas fa-edit"></i> Editar
                </a>
            <?php endif; ?>
            
            <a href="/Aptus/perfil/publico/<?= $anuncio['id_usuario'] ?>" class="btn-perfil-publico">
                <i class="fas fa-user"></i> Ver Perfil
            </a>

            <!-- BOTAO DENUNCIAR -->
            <?php if ($usuario && $usuario['id'] != $anuncio['id_usuario']): ?>
                <a href="/Aptus/denuncias/criar?tipo=anuncio&id=<?= $anuncio['id_anuncio'] ?>" 
                   class="btn-denunciar">
                    <i class="fas fa-flag"></i> Denunciar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fotos adicionais -->
<?php if (!empty($fotos)): ?>
    <h3>Fotos do Servico</h3>
    <div class="fotos-grid">
        <?php foreach ($fotos as $foto): ?>
            <div class="foto-item">
                <img src="/Aptus/public/uploads/anuncios/<?= htmlspecialchars($foto['arquivo']) ?>" 
                     alt="Foto do servico">
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p><a href="/Aptus/anuncios"><i class="fas fa-arrow-left"></i> Voltar para Servicos</a></p>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/Aptus/public/js/favoritos.js"></script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('formInteresse');
    var btn = document.getElementById('btnInteresse');
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
        });
    }
});

<?php if (isset($_SESSION['flash'])): ?>
    <?php if ($_SESSION['flash']['tipo'] === 'sucesso'): ?>
        Swal.fire({
            icon: 'success',
            title: 'Sucesso!',
            text: '<?= addslashes(htmlspecialchars($_SESSION['flash']['mensagem'])) ?>',
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK',
            timer: 4000,
            timerProgressBar: true
        });
    <?php elseif ($_SESSION['flash']['tipo'] === 'erro'): ?>
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: '<?= addslashes(htmlspecialchars($_SESSION['flash']['mensagem'])) ?>',
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK',
            timer: 4000,
            timerProgressBar: true
        });
    <?php elseif ($_SESSION['flash']['tipo'] === 'aviso'): ?>
        Swal.fire({
            icon: 'warning',
            title: 'Aviso!',
            text: '<?= addslashes(htmlspecialchars($_SESSION['flash']['mensagem'])) ?>',
            confirmButtonColor: '#006577',
            confirmButtonText: 'OK',
            timer: 4000,
            timerProgressBar: true
        });
    <?php endif; ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>