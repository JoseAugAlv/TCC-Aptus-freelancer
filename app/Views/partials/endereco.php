<?php
// app/Views/partials/endereco.php — campos de endereço com busca de CEP (public/js/cep.js)
// Espera opcionalmente $endereco = ['cep'=>..., 'logradouro'=>..., ...] e $enderecoObrigatorio (bool).
$endereco            = $endereco ?? [];
$enderecoObrigatorio = $enderecoObrigatorio ?? false;
$__req = $enderecoObrigatorio ? 'required' : '';
$__v   = fn(string $k) => htmlspecialchars((string) ($endereco[$k] ?? ''), ENT_QUOTES, 'UTF-8');
$__ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>
<fieldset class="endereco-fieldset" data-endereco>
    <legend>Endereço</legend>
    <p class="campo-ajuda">
        Digite o CEP para preencher o endereço, ou preencha estado, cidade e rua e clique em
        <strong>Descobrir CEP</strong>. Seu endereço não é exibido publicamente.
    </p>

    <div class="endereco-grid">
        <div class="form-group endereco-cep">
            <label for="cep">CEP</label>
            <div class="cep-linha">
                <input type="text" id="cep" name="cep" class="form-control" inputmode="numeric"
                       placeholder="00000-000" maxlength="9" value="<?= $__v('cep') ?>" <?= $__req ?> data-cep-input>
                <button type="button" class="btn btn-secondary btn-cep" data-cep-descobrir>
                    <i class="fas fa-search-location" aria-hidden="true"></i> Descobrir CEP
                </button>
            </div>
            <small class="cep-status" data-cep-status role="status" aria-live="polite"></small>
            <div class="cep-opcoes" data-cep-opcoes hidden></div>
        </div>

        <div class="form-group endereco-uf">
            <label for="estado">Estado</label>
            <select id="estado" name="estado" class="form-control" <?= $__req ?> data-cep-uf>
                <option value="">UF</option>
                <?php foreach ($__ufs as $uf): ?>
                    <option value="<?= $uf ?>" <?= ($endereco['estado'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group endereco-cidade">
            <label for="cidade">Cidade</label>
            <input type="text" id="cidade" name="cidade" class="form-control" maxlength="100"
                   value="<?= $__v('cidade') ?>" <?= $__req ?> data-cep-cidade>
        </div>

        <div class="form-group endereco-rua">
            <label for="logradouro">Rua / Avenida</label>
            <input type="text" id="logradouro" name="logradouro" class="form-control" maxlength="150"
                   value="<?= $__v('logradouro') ?>" <?= $__req ?> data-cep-logradouro autocomplete="address-line1">
        </div>

        <div class="form-group endereco-numero">
            <label for="numero">Número</label>
            <input type="text" id="numero" name="numero" class="form-control" maxlength="10"
                   value="<?= $__v('numero') ?>" <?= $__req ?> data-cep-numero>
        </div>

        <div class="form-group endereco-complemento">
            <label for="complemento">Complemento <span class="opcional">(opcional)</span></label>
            <input type="text" id="complemento" name="complemento" class="form-control" maxlength="60"
                   value="<?= $__v('complemento') ?>" placeholder="Apto, bloco, casa..." autocomplete="address-line2">
        </div>

        <div class="form-group endereco-bairro">
            <label for="bairro">Bairro</label>
            <input type="text" id="bairro" name="bairro" class="form-control" maxlength="100"
                   value="<?= $__v('bairro') ?>" <?= $__req ?> data-cep-bairro>
        </div>
    </div>
</fieldset>
