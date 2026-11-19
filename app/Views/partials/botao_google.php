<?php
// app/Views/partials/botao_google.php — botão "Continuar com Google" + divisória "ou".
// Espera: $googleAtivo (bool) e opcional $googleTexto e $googleDivisoriaAntes.
// Não aparece se as chaves GOOGLE_CLIENT_ID/SECRET não estiverem no .env.
require_once __DIR__ . '/../../Helpers/GoogleAuth.php';
if (empty($googleAtivo)) return;
$__txt   = $googleTexto ?? 'Continuar com Google';
$__antes = $googleDivisoriaAntes ?? true;
?>
<?php if ($__antes): ?><div class="auth-divisoria" role="separator"><span>ou</span></div><?php endif; ?>
<a href="<?= aptus_base() ?>/auth/google" class="btn-google">
    <?= GoogleAuth::iconeSvg(20) ?>
    <span><?= htmlspecialchars($__txt, ENT_QUOTES, 'UTF-8') ?></span>
</a>
<?php if (!$__antes): ?><div class="auth-divisoria" role="separator"><span>ou preencha seus dados</span></div><?php endif; ?>
