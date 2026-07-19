<?php
// app/Views/anuncios/editar.php

$tituloPagina = $tituloPagina ?? 'Editar Anúncio - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncio = $anuncio ?? [];
$categorias = $categorias ?? [];
?>

<h1>Editar Anúncio</h1>
<p>Atualize as informações do seu serviço</p>

<hr>

<?php if (isset($_SESSION['flash'])): ?>
    <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
        <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<form method="POST" action="/Aptus/anuncios/atualizar" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $anuncio['id_anuncio'] ?? 0 ?>">

    <div>
        <label for="titulo">Título do Serviço *</label>
        <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($anuncio['titulo'] ?? '') ?>" required>
    </div>

    <div>
        <label for="categoria_id">Categoria *</label>
        <select id="categoria_id" name="categoria_id" required>
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id_categoria'] ?>" <?= ($anuncio['id_categoria'] ?? 0) == $cat['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nome']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="descricao">Descrição *</label>
        <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($anuncio['descricao'] ?? '') ?></textarea>
    </div>

    <div>
        <label for="preco">Preço (R$) *</label>
        <input type="number" id="preco" name="preco" value="<?= $anuncio['preco'] ?? 0 ?>" step="0.01" min="0" required>
    </div>

    <div>
        <label for="situacao">Status</label>
        <select id="situacao" name="situacao">
            <option value="ativo" <?= ($anuncio['situacao'] ?? '') == 'ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="pausado" <?= ($anuncio['situacao'] ?? '') == 'pausado' ? 'selected' : '' ?>>Pausado</option>
        </select>
    </div>

    <div>
        <label for="foto_capa">Nova Foto de Capa (opcional)</label>
        <input type="file" id="foto_capa" name="foto_capa" accept="image/*">
        <?php if (!empty($anuncio['foto_capa'])): ?>
            <p>Foto atual: <?= htmlspecialchars($anuncio['foto_capa']) ?></p>
        <?php endif; ?>
    </div>

    <div>
        <button type="submit">Atualizar Serviço</button>
        <a href="/Aptus/anuncios/meus">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>