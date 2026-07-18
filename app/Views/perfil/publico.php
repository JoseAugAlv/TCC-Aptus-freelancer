<?php
// app/Views/perfil/publico.php

$tituloPagina = $tituloPagina ?? 'Perfil - Aptus';
$cssPagina = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$perfil = $perfilData ?? [];

// Determinar o nome do perfil com base no id_perfil
$perfilNome = 'Usuário';
if (isset($perfil['id_perfil'])) {
    switch ($perfil['id_perfil']) {
        case 1:
            $perfilNome = 'Administrador';
            break;
        case 2:
            $perfilNome = 'Moderador';
            break;
        case 4:
            $perfilNome = 'Master';
            break;
        default:
            $perfilNome = 'Usuário';
            break;
    }
}
?>

<div class="perfil-publico-container">
    <div class="perfil-publico-card">
        <div class="perfil-publico-avatar">
            <?php if (!empty($perfil['foto_perfil']) && $perfil['foto_perfil'] != 'default.png'): ?>
                <img src="/Aptus/public/uploads/<?= htmlspecialchars($perfil['foto_perfil']) ?>" alt="Foto de perfil">
            <?php else: ?>
                <i class="fas fa-user-circle"></i>
            <?php endif; ?>
        </div>
        <div class="perfil-publico-info">
            <h2><?= htmlspecialchars($perfil['nome'] ?? 'Usuário') ?></h2>
            
            <?php if (!empty($perfil['cidade']) || !empty($perfil['estado'])): ?>
                <p class="localizacao">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?= htmlspecialchars($perfil['cidade'] ?? '') . (!empty($perfil['cidade']) && !empty($perfil['estado']) ? ', ' : '') . htmlspecialchars($perfil['estado'] ?? '') ?>
                </p>
            <?php endif; ?>
            
            <p class="role">
                <span class="badge"><?= $perfilNome ?></span>
            </p>
            
            <div class="avaliacao-publico">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <?php if ($i <= round($perfil['nota_media'] ?? 0)): ?>
                        <i class="fas fa-star" style="color: #f59e0b;"></i>
                    <?php else: ?>
                        <i class="far fa-star" style="color: #d1d5db;"></i>
                    <?php endif; ?>
                <?php endfor; ?>
                <span class="nota"><?= number_format($perfil['nota_media'] ?? 0, 1) ?></span>
                <span class="total-avaliacoes">(<?= $perfil['total_avaliacoes'] ?? 0 ?> avaliações)</span>
            </div>
            
            <?php if (!empty($perfil['bio'])): ?>
                <p class="bio-publico"><?= htmlspecialchars($perfil['bio']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="perfil-publico-stats">
        <div class="stat-item">
            <span class="stat-number"><?= $perfil['quantidade_servicos'] ?? 0 ?></span>
            <span class="stat-label">Serviços Realizados</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= number_format($perfil['nota_media'] ?? 0, 1) ?></span>
            <span class="stat-label">Avaliação Média</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $perfil['total_avaliacoes'] ?? 0 ?></span>
            <span class="stat-label">Avaliações</span>
        </div>
    </div>

    <div class="perfil-publico-actions">
        <a href="/Aptus/chat?usuario=<?= $perfil['id_usuario'] ?>" class="btn-contatar">
            <i class="fas fa-comments"></i> Contatar
        </a>
        <a href="/Aptus/anuncios?freelancer=<?= $perfil['id_usuario'] ?>" class="btn-ver-servicos">
            <i class="fas fa-tools"></i> Ver Serviços
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>