<?php
// app/Views/anuncios/criar.php

$tituloPagina = $tituloPagina ?? 'Criar Anúncio - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$categorias = $categorias ?? [];
?>

<h1>Criar Anúncio</h1>
<p>Preencha os dados para criar seu serviço</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<form method="POST" action="/Aptus/anuncios/salvar" enctype="multipart/form-data">
    <div>
        <label for="titulo">Título do Serviço *</label>
        <input type="text" id="titulo" name="titulo" placeholder="Ex: Reparos Elétricos Residenciais" required>
    </div>

    <div>
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

    <div>
        <label for="descricao">Descrição *</label>
        <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva detalhadamente o serviço que você oferece..." required></textarea>
    </div>

    <div>
        <label for="preco">Preço (R$) *</label>
        <input type="number" id="preco" name="preco" placeholder="0.00" step="0.01" min="0" required>
    </div>

    <div>
        <label for="foto_capa">Foto de Capa</label>
        <input type="file" id="foto_capa" name="foto_capa" accept="image/*">
    </div>

    <div>
        <button type="submit">Publicar Serviço</button>
        <a href="/Aptus/anuncios/meus">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>