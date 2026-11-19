<?php
// app/Views/moderator/index.php

$tituloPagina = $tituloPagina ?? 'Moderação - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div style="max-width: 1200px; margin: 120px auto 40px; padding: 0 20px;">
    <h1 style="color: var(--tema-primaria-texto, var(--cor-primaria));">Moderação</h1>
    <p>Gerencie anúncios, denúncias e conteúdo da plataforma</p>
    
    <hr style="margin: 20px 0;">
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
        <div style="background: var(--tema-superficie, white); padding: 20px; border-radius: 8px; border: 1px solid var(--tema-borda, #ddd); text-align: center;">
            <h3>Anúncios</h3>
            <p>Moderar anúncios pendentes</p>
            <a href="<?= aptus_base() ?>/moderator/anuncios" style="display: inline-block; padding: 8px 16px; background: var(--cor-primaria); color: white; text-decoration: none; border-radius: 4px;">Ir para Anúncios</a>
        </div>
        
        <div style="background: var(--tema-superficie, white); padding: 20px; border-radius: 8px; border: 1px solid var(--tema-borda, #ddd); text-align: center;">
            <h3>Denúncias</h3>
            <p>Analisar denúncias de usuários</p>
            <a href="<?= aptus_base() ?>/moderator/denuncias" style="display: inline-block; padding: 8px 16px; background: var(--cor-primaria); color: white; text-decoration: none; border-radius: 4px;">Ir para Denúncias</a>
        </div>
        
        <div style="background: var(--tema-superficie, white); padding: 20px; border-radius: 8px; border: 1px solid var(--tema-borda, #ddd); text-align: center;">
            <h3>Disputas</h3>
            <p>Resolver disputas entre usuários</p>
            <a href="<?= aptus_base() ?>/moderator/disputas" style="display: inline-block; padding: 8px 16px; background: var(--cor-primaria); color: white; text-decoration: none; border-radius: 4px;">Ir para Disputas</a>
        </div>
        
        <div style="background: var(--tema-superficie, white); padding: 20px; border-radius: 8px; border: 1px solid var(--tema-borda, #ddd); text-align: center;">
            <h3>Usuários</h3>
            <p>Gerenciar usuários da plataforma</p>
            <a href="<?= aptus_base() ?>/moderator/usuarios" style="display: inline-block; padding: 8px 16px; background: var(--cor-primaria); color: white; text-decoration: none; border-radius: 4px;">Ir para Usuários</a>
        </div>
        
        <div style="background: var(--tema-superficie, white); padding: 20px; border-radius: 8px; border: 1px solid var(--tema-borda, #ddd); text-align: center;">
            <h3>Categorias</h3>
            <p>Gerenciar categorias de serviços</p>
            <a href="<?= aptus_base() ?>/moderator/categorias" style="display: inline-block; padding: 8px 16px; background: var(--cor-primaria); color: white; text-decoration: none; border-radius: 4px;">Ir para Categorias</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>