<?php
// app/Views/sobre/index.php

$tituloPagina = $tituloPagina ?? 'Sobre Nós - Aptus';
$cssPagina = $cssPagina ?? 'sobre.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<main class="sobre-container">
    <!-- Hero Section -->
    <section class="sobre-hero">
        <h1>Sobre a Aptus</h1>
        <div class="tagline">Conectando profissionais e clientes com segurança</div>
        <p>A Aptus é uma plataforma inovadora que conecta profissionais qualificados a clientes que buscam serviços de qualidade. Com foco em segurança, transparência e eficiência, nossa missão é revolucionar o mercado de serviços no Brasil.</p>
    </section>

    <!-- Missão, Visão, Valores -->
    <section class="mvp-section">
        <div class="mvp-card">
            <i class="fas fa-bullseye"></i>
            <h3>Missão</h3>
            <p>Conectar profissionais qualificados a clientes, garantindo segurança, transparência e qualidade em cada contratação.</p>
        </div>
        <div class="mvp-card">
            <i class="fas fa-eye"></i>
            <h3>Visão</h3>
            <p>Ser a plataforma líder em contratação de serviços no Brasil.</p>
        </div>
        <div class="mvp-card">
            <i class="fas fa-heart"></i>
            <h3>Valores</h3>
            <p>Transparência, segurança, inovação, respeito e compromisso com a excelência.</p>
        </div>
    </section>

    <!-- Como Funciona -->
    <section class="como-funciona-section">
        <h2>Como Funciona</h2>
        <div class="steps-grid">
            <div class="step-item">
                <div class="step-number">1</div>
                <h3>Cadastre-se</h3>
                <p>Crie sua conta gratuitamente</p>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <h3>Encontre ou Ofereça</h3>
                <p>Clientes encontram profissionais, profissionais oferecem serviços</p>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <h3>Contrate com Segurança</h3>
                <p>Negocie e realize o serviço com total segurança</p>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <h2>Pronto para começar?</h2>
        <p>Junte-se a milhares de pessoas que já confiam na Aptus</p>
        <div class="cta-buttons">
            <a href="/Aptus/login" class="btn-primario">Cadastre-se Grátis</a>
            <a href="/Aptus/anuncios" class="btn-secundario">Explorar Serviços</a>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>