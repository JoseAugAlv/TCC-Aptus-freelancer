<?php
// app/Views/errors/404.php

$tituloPagina = 'Pagina nao encontrada - Aptus';
$cssPagina = 'erro.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';
?>

<div class="erro-container">
    <div class="erro-content">
        <div class="erro-codigo">404</div>
        <h1>Pagina nao encontrada</h1>
        <p>Ops! A pagina que voce esta procurando nao existe ou foi removida.</p>
        <p class="erro-dica">Verifique se o endereco esta correto ou tente voltar para o inicio.</p>
        <div class="erro-acoes">
            <a href="/Aptus/" class="btn-primary">
                <i class="fas fa-home"></i> Voltar para o Inicio
            </a>
            <a href="javascript:history.back()" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar para a pagina anterior
            </a>
        </div>
        <div class="erro-ilustracao">
            <i class="fas fa-search"></i>
        </div>
    </div>
</div>

<style>
.erro-container {
    max-width: 800px;
    margin: 140px auto 60px;
    padding: 0 20px;
    text-align: center;
}

.erro-content {
    background: #fff;
    padding: 50px 40px;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.erro-codigo {
    font-size: 8rem;
    font-weight: 800;
    color: #006577;
    line-height: 1;
    margin-bottom: 10px;
    text-shadow: 0 4px 20px rgba(0,101,119,0.15);
}

.erro-content h1 {
    font-size: 2rem;
    color: #1a2f3e;
    margin-bottom: 12px;
}

.erro-content p {
    color: #6b7280;
    font-size: 1.05rem;
    margin-bottom: 8px;
}

.erro-dica {
    color: #94a3b8;
    font-size: 0.95rem;
    margin-bottom: 30px;
}

.erro-acoes {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: #006577;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #004d5c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,101,119,0.3);
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    background: #f1f5f9;
    color: #1a2f3e;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-secondary:hover {
    background: #e2e8f0;
}

.erro-ilustracao {
    font-size: 4rem;
    color: #e2e8f0;
}

.erro-ilustracao i {
    opacity: 0.5;
}

@media (max-width: 768px) {
    .erro-codigo {
        font-size: 5rem;
    }
    
    .erro-content {
        padding: 30px 20px;
    }
    
    .erro-content h1 {
        font-size: 1.5rem;
    }
    
    .erro-acoes {
        flex-direction: column;
    }
    
    .btn-primary,
    .btn-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>