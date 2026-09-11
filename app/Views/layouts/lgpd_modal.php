<div id="lgpd-modal" class="lgpd-overlay" role="dialog" aria-modal="true" aria-label="Concordância com termos">
  <div class="lgpd-box">
    <h2 style="font-size:1.3rem;color:#0f172a;margin-bottom:.5rem;">Confirmar aceitação</h2>
    <p style="color:#334155;margin-bottom:1rem;">Ao continuar, você concorda com nossos <a href="/Aptus/termos">Termos de Uso</a>, <a href="/Aptus/termos#privacidade">Política de Privacidade</a> e <a href="/Aptus/cookies">Política de Cookies</a>, conforme a LGPD (Lei nº 13.709/2018).</p>
    <ul style="padding-left:1.2rem;color:#334155;margin-bottom:1.2rem;font-size:.9rem;">
      <li>Seus dados são protegidos.</li>
      <li>Você pode revogar o consentimento a qualquer momento.</li>
    </ul>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
      <button onclick="aceitarLgpd()" class="btn btn-primary">Concordo — Continuar</button>
      <a href="/Aptus/termos" class="btn btn-secondary">Ler Termos</a>
    </div>
  </div>
</div>
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
