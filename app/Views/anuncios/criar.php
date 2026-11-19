<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
require_once __DIR__ . "/../../Controllers/AnuncioController.php";
// app/Views/anuncios/criar.php

$tituloPagina = $tituloPagina ?? 'Criar Anúncio - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$categorias = $categorias ?? [];
?>

<div class="anuncio-container">
    <div class="anuncio-header">
        <h1>Criar Anúncio</h1>
        <p>Preencha os dados para criar seu serviço</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="anuncio-card">
        <form method="POST" action="<?= aptus_base() ?>/anuncios/salvar" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título do Serviço *</label>
                <input type="text" id="titulo" name="titulo" placeholder="Ex: Reparos Elétricos Residenciais" required>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria *</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id_categoria'] ?>">
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição *</label>
                <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva detalhadamente o serviço que você oferece..." required></textarea>
            </div>

            <div class="form-group">
                <label for="preco">Preço (R$) *</label>
                <input type="number" id="preco" name="preco" placeholder="0.00" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="max_simultaneos">Clientes atendidos ao mesmo tempo *</label>
                <input type="number" id="max_simultaneos" name="max_simultaneos" min="1" max="<?= (int) AnuncioController::MAX_SIMULTANEOS_LIMITE ?>" step="1"
                       value="1" required inputmode="numeric" aria-describedby="ajudaSimultaneos">
                <small class="campo-ajuda" id="ajudaSimultaneos">
                    Quantas propostas você consegue atender em paralelo neste serviço. Ex.: manicure pode atender várias clientes; pedreiro, uma ou duas obras.
                    Quando o limite for atingido, o anúncio mostra "Agenda cheia" até algum atendimento terminar.
                </small>
            </div>

            <?php $fotos = []; $capaAtual = ''; require __DIR__ . '/_fotos.php'; ?>

            <p class="campo-ajuda">Ao publicar, você declara que o serviço segue a <a href="<?= aptus_base() ?>/politica-anuncios" target="_blank" rel="noopener">Política de Anúncios</a>. Todo anúncio passa pela moderação antes de aparecer.</p>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-paper-plane"></i> Publicar Serviço
                </button>
                <a href="<?= aptus_base() ?>/anuncios/meus" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        <?= CsrfMiddleware::field() ?>
</form>
    </div>
</div>



<?php $jsPagina = 'fotos-anuncio.js'; require_once __DIR__ . '/../layouts/footer.php'; ?>