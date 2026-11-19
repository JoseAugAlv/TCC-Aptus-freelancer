<?php
// app/Views/anuncios/_fotos.php — gerenciador de fotos do anúncio (criar/editar)
// Espera: $fotos (fotos já salvas, pode ser []), $capaAtual (arquivo da capa ou '').
require_once __DIR__ . '/../../Helpers/UploadHelper.php';
require_once __DIR__ . '/../../Controllers/AnuncioController.php';

$fotos     = $fotos ?? [];
$capaAtual = $capaAtual ?? '';
$maxFotos  = AnuncioController::MAX_FOTOS;
?>
<div class="form-group fotos-group" data-fotos-anuncio data-max="<?= (int) $maxFotos ?>" data-existentes="<?= count($fotos) ?>">
    <label>Fotos do serviço</label>
    <p class="foto-helper">
        Até <?= (int) $maxFotos ?> fotos (JPG, PNG, WEBP ou GIF, até 5MB cada).
        Marque <strong>Capa</strong> na foto que aparece primeiro nas listagens.
    </p>

    <div class="fotos-grid" data-fotos-grid>
        <?php foreach ($fotos as $foto): ?>
            <?php $token = url_id($foto['id_anuncio_foto']); ?>
            <div class="foto-item">
                <img src="<?= htmlspecialchars(\UploadHelper::capaUrl($foto['arquivo']), ENT_QUOTES, 'UTF-8') ?>" alt="Foto do serviço">
                <label class="foto-capa-opcao">
                    <input type="radio" name="capa" value="<?= $token ?>" <?= $foto['arquivo'] === $capaAtual ? 'checked' : '' ?>>
                    <span>Capa</span>
                </label>
                <label class="foto-remover-opcao" title="Remover ao salvar">
                    <input type="checkbox" name="remover_fotos[]" value="<?= $token ?>" data-remover>
                    <i class="fas fa-trash-alt" aria-hidden="true"></i><span class="sr-only">Remover</span>
                </label>
            </div>
        <?php endforeach; ?>
    </div>

    <label class="btn-upload" for="inputFotosAnuncio">
        <i class="fas fa-images" aria-hidden="true"></i> Adicionar fotos
    </label>
    <input type="file" id="inputFotosAnuncio" name="fotos[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden>
    <p class="foto-helper" data-fotos-aviso role="status" aria-live="polite"></p>
</div>
