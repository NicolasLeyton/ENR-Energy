/* Bandeau d'information « cookies » — ENR Energy
   ------------------------------------------------------------------
   Ce site ne dépose AUCUN cookie non essentiel : polices auto-hébergées,
   aucune mesure d'audience, aucun traceur publicitaire. La carte Google
   Maps de la page Contact ne se charge qu'après un clic explicite.

   Un bandeau de consentement n'est donc pas obligatoire (lignes
   directrices CNIL). Ce bandeau est purement informatif : il s'affiche
   une seule fois, mémorise le choix dans localStorage (même origine),
   et ne réapparaît donc ni sur les autres pages ni lors des visites
   suivantes.
   ------------------------------------------------------------------ */
(function () {
  'use strict';

  var STORAGE_KEY = 'enr-cookie-notice';
  var VERSION = '1'; // incrémenter pour ré-afficher le bandeau après une évolution

  // Déjà accepté ? on ne fait rien.
  try {
    if (window.localStorage.getItem(STORAGE_KEY) === VERSION) return;
  } catch (e) {
    // localStorage indisponible (navigation privée stricte) : on affiche
    // le bandeau sans pouvoir mémoriser — comportement dégradé acceptable.
  }

  function init() {
    if (document.getElementById('enr-cookie-notice')) return;

    // ---- Styles (injectés une seule fois, autonomes) ----
    var css =
      '#enr-cookie-notice{position:fixed;left:16px;right:16px;bottom:16px;z-index:2147483000;' +
      'max-width:560px;margin:0 auto;background:#1A1A1A;color:#fff;border-radius:12px;' +
      'box-shadow:0 10px 40px rgba(0,0,0,.28);padding:16px 18px;' +
      "font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;" +
      'display:flex;flex-wrap:wrap;align-items:center;gap:12px 16px;' +
      'transform:translateY(140%);transition:transform .35s cubic-bezier(.22,1,.36,1);}' +
      '#enr-cookie-notice.enr-cc-show{transform:translateY(0);}' +
      '#enr-cookie-notice .enr-cc-txt{flex:1 1 260px;font-size:.82rem;line-height:1.55;color:#E6E6E6;margin:0;}' +
      '#enr-cookie-notice .enr-cc-txt a{color:#5FD46C;text-decoration:underline;}' +
      '#enr-cookie-notice .enr-cc-btn{flex:0 0 auto;background:#3DB54A;color:#fff;border:0;cursor:pointer;' +
      'font:inherit;font-size:.82rem;font-weight:700;padding:10px 20px;border-radius:8px;' +
      'transition:background .18s;white-space:nowrap;}' +
      '#enr-cookie-notice .enr-cc-btn:hover{background:#2A8235;}' +
      '#enr-cookie-notice .enr-cc-btn:focus-visible{outline:2px solid #fff;outline-offset:2px;}' +
      '@media (max-width:520px){#enr-cookie-notice .enr-cc-btn{flex:1 1 100%;}}' +
      '@media (prefers-reduced-motion:reduce){#enr-cookie-notice{transition:none;}}';

    var style = document.createElement('style');
    style.appendChild(document.createTextNode(css));
    document.head.appendChild(style);

    // ---- Bandeau ----
    var bar = document.createElement('div');
    bar.id = 'enr-cookie-notice';
    bar.setAttribute('role', 'region');
    bar.setAttribute('aria-label', 'Information sur les cookies');

    var p = document.createElement('p');
    p.className = 'enr-cc-txt';
    p.innerHTML =
      'Ce site n’utilise <strong>aucun cookie de suivi</strong> et ne collecte pas vos ' +
      'données de navigation. Seuls des éléments strictement nécessaires au ' +
      'fonctionnement du site sont utilisés. ' +
      '<a href="politique-confidentialite.html">En savoir plus</a>.';

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'enr-cc-btn';
    btn.textContent = 'J’ai compris';

    btn.addEventListener('click', function () {
      try { window.localStorage.setItem(STORAGE_KEY, VERSION); } catch (e) {}
      bar.classList.remove('enr-cc-show');
      var remove = function () { if (bar.parentNode) bar.parentNode.removeChild(bar); };
      bar.addEventListener('transitionend', remove, { once: true });
      setTimeout(remove, 500); // filet de sécurité si transitionend ne se déclenche pas
    });

    bar.appendChild(p);
    bar.appendChild(btn);
    document.body.appendChild(bar);

    // Animation d'entrée
    requestAnimationFrame(function () {
      requestAnimationFrame(function () { bar.classList.add('enr-cc-show'); });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
