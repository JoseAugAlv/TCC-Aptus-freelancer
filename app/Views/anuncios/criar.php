<?php
// app/Views/anuncios/criar.php

$tituloPagina = $tituloPagina ?? 'Criar Anuncio - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$categorias = $categorias ?? [];
?>

<div class="anuncio-container">
    <div class="anuncio-header">
        <h1>Criar Anuncio</h1>
        <p>Preencha os dados para criar seu servico</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= $_SESSION['flash']['tipo'] ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div class="anuncio-card">
        <form method="POST" action="/Aptus/anuncios/salvar" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Titulo do Servico *</label>
                <input type="text" id="titulo" name="titulo" placeholder="Ex: Reparos Eletricos Residenciais" required>
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
                <label for="descricao">Descricao *</label>
                <textarea id="descricao" name="descricao" rows="5" placeholder="Descreva detalhadamente o servico que voce oferece..." required></textarea>
            </div>

            <div class="form-group">
                <label for="preco">Preco (R$) *</label>
                <input type="number" id="preco" name="preco" placeholder="0.00" step="0.01" min="0" required>
            </div>

            <div class="form-group foto-capa-group">
                <label>Foto de Capa</label>
                <div class="foto-preview-container">
                    <div class="foto-preview">
                        <img src="/Aptus/public/images/no-image.png" alt="Preview" id="fotoPreview">
                    </div>
                    <div class="foto-upload">
                        <label for="foto_capa" class="btn-upload">
                            <i class="fas fa-image"></i> Escolher imagem
                        </label>
                        <input type="file" id="foto_capa" name="foto_capa" accept="image/*" style="display: none;">
                        <p class="foto-helper">Formatos: JPG, PNG, WEBP, GIF. Maximo: 5MB</p>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-paper-plane"></i> Publicar Servico
                </button>
                <a href="/Aptus/anuncios/meus" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputFoto = document.getElementById('foto_capa');
    var preview = document.getElementById('fotoPreview');
    
    if (inputFoto && preview) {
        inputFoto.addEventListener('change', function(e) {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>