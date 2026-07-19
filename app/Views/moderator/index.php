<?php
// app/Views/moderator/index.php

$tituloPagina = $tituloPagina ?? 'Moderação - Aptus';
$cssPagina = $cssPagina ?? 'moderador.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div style="max-width: 1200px; margin: 120px auto 40px; padding: 0 20px;">
    <h1 style="color: #006577;">Moderação</h1>
    <p>Gerencie anúncios, denúncias e conteúdo da plataforma</p>
    
    <hr style="margin: 20px 0;">
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
            <h3>Anúncios</h3>
            <p>Moderar anúncios pendentes</p>
            <a href="/Aptus/moderator/anuncios" style="display: inline-block; padding: 8px 16px; background: #006577; color: white; text-decoration: none; border-radius: 4px;">Ir para Anúncios</a>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
            <h3>Denúncias</h3>
            <p>Analisar denúncias de usuários</p>
            <a href="/Aptus/moderator/denuncias" style="display: inline-block; padding: 8px 16px; background: #006577; color: white; text-decoration: none; border-radius: 4px;">Ir para Denúncias</a>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
            <h3>Disputas</h3>
            <p>Resolver disputas entre usuários</p>
            <a href="/Aptus/moderator/disputas" style="display: inline-block; padding: 8px 16px; background: #006577; color: white; text-decoration: none; border-radius: 4px;">Ir para Disputas</a>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
            <h3>Usuários</h3>
            <p>Gerenciar usuários da plataforma</p>
            <a href="/Aptus/moderator/usuarios" style="display: inline-block; padding: 8px 16px; background: #006577; color: white; text-decoration: none; border-radius: 4px;">Ir para Usuários</a>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd; text-align: center;">
            <h3>Categorias</h3>
            <p>Gerenciar categorias de serviços</p>
            <a href="/Aptus/moderator/categorias" style="display: inline-block; padding: 8px 16px; background: #006577; color: white; text-decoration: none; border-radius: 4px;">Ir para Categorias</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>