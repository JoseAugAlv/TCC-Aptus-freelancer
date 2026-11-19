<?php
require_once __DIR__ . "/../../Middleware/CsrfMiddleware.php";
require_once __DIR__ . "/../../Helpers/UploadHelper.php";
require_once __DIR__ . "/../../Controllers/AnuncioController.php";
// app/Views/anuncios/editar.php

$tituloPagina = $tituloPagina ?? 'Editar Anúncio - Aptus';
$cssPagina = $cssPagina ?? 'anuncios.css';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/nav.php';

$anuncio = $anuncio ?? [];
$categorias = $categorias ?? [];
?>

<div class="anuncio-container">
    <div class="anuncio-header">
        <h1>Editar Anúncio</h1>
        <p>Atualize as informações do seu serviço</p>
    </div>

    <hr>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-<?= htmlspecialchars($_SESSION['flash']['tipo']) ?>">
            <?= htmlspecialchars($_SESSION['flash']['mensagem']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <?php if ((int) ($anuncio['id_situacao_moderacao'] ?? 0) === 3): ?>
        <div class="aviso-moderacao" role="status">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <div>
                <strong>Este anúncio está fora do ar.</strong>
                <?php if (!empty($anuncio['motivo_remocao'])): ?>
                    Motivo: <?= htmlspecialchars($anuncio['motivo_remocao']) ?>.
                <?php endif; ?>
                Corrija o que for preciso e salve: ele volta para a análise da moderação.
            </div>
        </div>
    <?php endif; ?>

    <div class="anuncio-card">
        <form method="POST" action="<?= aptus_base() ?>/anuncios/atualizar" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= (int) ($anuncio['id_anuncio'] ?? 0) ?>">

            <div class="form-group">
                <label for="titulo">Título do Serviço *</label>
                <input type="text" id="titulo" name="titulo" value="<?= htmlspecialchars($anuncio['titulo'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="categoria_id">Categoria *</label>
                <select id="categoria_id" name="categoria_id" required>
                    <option value="">Selecione uma categoria</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id_categoria'] ?>" <?= (int) ($anuncio['id_categoria'] ?? 0) === (int) $cat['id_categoria'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição *</label>
                <textarea id="descricao" name="descricao" rows="5" required><?= htmlspecialchars($anuncio['descricao'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="preco">Preço (R$) *</label>
                <input type="number" id="preco" name="preco" value="<?= htmlspecialchars((string) ($anuncio['preco'] ?? '')) ?>" step="0.01" min="0" required>
            </div>

            <div class="form-group">
                <label for="situacao">Status</label>
                <select id="situacao" name="situacao">
                    <option value="ativo" <?= ($anuncio['situacao'] ?? '') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                    <option value="pausado" <?= ($anuncio['situacao'] ?? '') === 'pausado' ? 'selected' : '' ?>>Pausado</option>
                </select>
            </div>

            <div class="form-group">
                <label for="max_simultaneos">Clientes atendidos ao mesmo tempo *</label>
                <input type="number" id="max_simultaneos" name="max_simultaneos" min="1" max="<?= (int) AnuncioController::MAX_SIMULTANEOS_LIMITE ?>" step="1"
                       value="<?= max(1, (int) ($anuncio['max_simultaneos'] ?? 1)) ?>" required inputmode="numeric" aria-describedby="ajudaSimultaneos">
                <small class="campo-ajuda" id="ajudaSimultaneos">
                    Quantas propostas você consegue atender em paralelo neste serviço. Ex.: manicure pode atender várias clientes; pedreiro, uma ou duas obras.
                    Quando o limite for atingido, o anúncio mostra "Agenda cheia" até algum atendimento terminar.
                </small>
            </div>

            <?php $fotos = $fotos ?? []; $capaAtual = $anuncio['foto_capa'] ?? ''; require __DIR__ . '/_fotos.php'; ?>

            <div class="form-actions">
                <button type="submit" class="btn-salvar">
                    <i class="fas fa-save"></i> Atualizar Serviço
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
