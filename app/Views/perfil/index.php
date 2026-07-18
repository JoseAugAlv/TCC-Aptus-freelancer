<?php
// app/Views/perfil/index.php

$tituloPagina = $tituloPagina ?? 'Meu Perfil - Aptus';
$cssPagina = $cssPagina ?? 'perfil.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$usuario = $usuarioData ?? $_SESSION['usuario'];

// Determinar o nome do perfil com base no id_perfil
$perfilNome = 'Usuário';
if (isset($usuario['id_perfil'])) {
    switch ($usuario['id_perfil']) {
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

<div class="perfil-container">
    <div class="perfil-header">
        <h1><i class="fas fa-user-circle"></i> Meu Perfil</h1>
        <div class="perfil-actions">
            <a href="/Aptus/perfil/editar" class="btn-editar">
                <i class="fas fa-edit"></i> Editar Perfil
            </a>
            <a href="/Aptus/perfil/portfolio" class="btn-portfolio">
                <i class="fas fa-images"></i> Portfólio
            </a>
        </div>
    </div>

    <div class="perfil-card">
        <div class="perfil-avatar">
            <?php if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] != 'default.png'): ?>
                <img src="/Aptus/public/uploads/<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
            <?php else: ?>
                <i class="fas fa-user-circle"></i>
            <?php endif; ?>
        </div>
        <div class="perfil-info">
            <h2><?= htmlspecialchars($usuario['nome'] ?? 'Usuário') ?></h2>
            <p class="email"><i class="fas fa-envelope"></i> <?= htmlspecialchars($usuario['email'] ?? '') ?></p>
            
            <?php if (!empty($usuario['telefone'])): ?>
                <p class="telefone"><i class="fas fa-phone"></i> <?= htmlspecialchars($usuario['telefone']) ?></p>
            <?php endif; ?>
            
            <?php if (!empty($usuario['whatsapp'])): ?>
                <p class="whatsapp"><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($usuario['whatsapp']) ?></p>
            <?php endif; ?>
            
            <p class="role">
                <span class="badge"><?= $perfilNome ?></span>
            </p>
            
            <?php if (!empty($usuario['bio'])): ?>
                <p class="bio"><?= htmlspecialchars($usuario['bio']) ?></p>
            <?php endif; ?>
            
            <?php if (!empty($usuario['cidade']) || !empty($usuario['estado'])): ?>
                <p class="localizacao">
                    <i class="fas fa-map-marker-alt"></i> 
                    <?= htmlspecialchars($usuario['cidade'] ?? '') . (!empty($usuario['cidade']) && !empty($usuario['estado']) ? ', ' : '') . htmlspecialchars($usuario['estado'] ?? '') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="perfil-stats">
        <div class="stat-item">
            <span class="stat-number"><?= $usuario['quantidade_servicos'] ?? 0 ?></span>
            <span class="stat-label">Serviços Realizados</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= number_format($usuario['nota_media'] ?? 0, 1) ?></span>
            <span class="stat-label">Avaliação Média</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $usuario['total_avaliacoes'] ?? 0 ?></span>
            <span class="stat-label">Avaliações</span>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>