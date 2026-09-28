<div id="lgpd-modal" class="lgpd-overlay" role="dialog" aria-modal="true" aria-label="Concordância com termos"
     style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.65);
            z-index:99999; align-items:center; justify-content:center; padding:20px;">

  <div class="lgpd-box" style="background:#fff; max-width:520px; width:100%;
       border-radius:16px; padding:28px 26px; box-shadow:0 20px 60px rgba(0,0,0,.25);">

    <h2 style="font-size:1.25rem; color:#0f172a; margin:0 0 .6rem;">
      <i class="fas fa-shield-alt" style="color:#006577;"></i> Confirmar aceitação
    </h2>

    <p style="color:#334155; margin:0 0 1rem; line-height:1.6;">
      Ao continuar, você concorda com nossos
      <a href="/Aptus/termos" style="color:#006577; text-decoration:underline;">Termos de Uso</a>,
      <a href="/Aptus/termos#privacidade" style="color:#006577; text-decoration:underline;">Política de Privacidade</a>
      e <a href="/Aptus/cookies" style="color:#006577; text-decoration:underline;">Política de Cookies</a>,
      conforme a LGPD (Lei nº 13.709/2018).
    </p>

    <ul style="padding-left:1.2rem; color:#334155; margin:0 0 1.2rem; font-size:.9rem; line-height:1.7;">
      <li>Seus dados são protegidos e não compartilhados com terceiros.</li>
      <li>Você pode revogar o consentimento a qualquer momento.</li>
      <li>Cookies essenciais garantem o funcionamento e a segurança do site.</li>
    </ul>

    <div style="display:flex; gap:.75rem; flex-wrap:wrap;">
      <button type="button" onclick="aceitarLgpd()"
              style="padding:11px 24px; background:#006577; color:#fff; border:none;
                     border-radius:8px; font-weight:700; cursor:pointer;">
        <i class="fas fa-check"></i> Concordo — Continuar
      </button>
      <a href="/Aptus/termos"
         style="padding:11px 22px; background:#f1f5f9; color:#1a2f3e; text-decoration:none;
                border-radius:8px; font-weight:600; border:1px solid #e2e8f0;">
        Ler Termos
      </a>
    </div>
  </div>
</div>

<script>
(function () {
  var PATH = '/Aptus/';

  function setCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + value + ';expires=' + d.toUTCString() +
                      ';path=' + PATH + ';SameSite=Lax';
  }

  function getCookie(name) {
    var m = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return m ? m[2] : null;
  }

  // Só mostra o modal em páginas de autenticação (login/cadastro)
  var path = window.location.pathname;
  var isAuthPage = /\/(login|login\/cadastrar)/.test(path);

  if (!isAuthPage) {
    // Fora de auth → deixa escondido
    var modal = document.getElementById('lgpd-modal');
    if (modal) modal.style.display = 'none';
    return;
  }

  // Já aceitou? Esconde.
  var aceito = getCookie('aptus_lgpd_aceito');
  if (!aceito) {
    try { aceito = localStorage.getItem('aptus_lgpd_aceito'); } catch (e) {}
  }

  var modal = document.getElementById('lgpd-modal');
  if (modal && !aceito) {
    modal.style.display = 'flex';
  }

  window.aceitarLgpd = function () {
    var m = document.getElementById('lgpd-modal');
    if (m) m.style.display = 'none';
    setCookie('aptus_lgpd_aceito', '1', 365);
    try { localStorage.setItem('aptus_lgpd_aceito', '1'); } catch (e) {}
  };
})();
</script>
<style>
.lgpd-overlay{animation:fadeIn .2s ease}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
</style>
<script>
function aceitarLgpd(){
  document.getElementById('lgpd-modal').style.display='none';
  document.cookie='aptus_lgpd_aceito=1;path=/;max-age=31536000;SameSite=Lax';
  localStorage.setItem('aptus_lgpd_aceito','1');
}
if(localStorage.getItem('aptus_lgpd_aceito')==='1' || document.cookie.includes('aptus_lgpd_aceito=1')){
  document.getElementById('lgpd-modal').style.display='none';
}
</script>
