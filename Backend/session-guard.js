/* EduVault admin: auto sign-out after inactivity (works across tabs). */
(function () {
  'use strict';
  var cfg = window.eduVaultSession || {};
  var IDLE = (cfg.idle || 900) * 1000;
  var WARN = Math.min(60000, IDLE / 3);
  var KEY = 'ev_admin_last_activity';
  var last = Date.now(), lastPing = Date.now(), lastWrite = 0, banner = null;

  function store() { try { localStorage.setItem(KEY, String(last)); } catch (e) {} }
  function stored() { try { return parseInt(localStorage.getItem(KEY) || '0', 10) || 0; } catch (e) { return 0; } }

  function activity() {
    last = Date.now();
    if (last - lastWrite > 5000) { lastWrite = last; store(); }
    if (banner) { banner.remove(); banner = null; }
  }
  ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function (ev) {
    window.addEventListener(ev, activity, { passive: true });
  });
  store();

  function signOut() { window.location.replace('logout.php?reason=idle'); }

  function ping() {
    lastPing = Date.now();
    fetch('keepalive.php', { credentials: 'same-origin', cache: 'no-store', headers: { 'X-EV-Ajax': '1' } })
      .then(function (r) { if (r.status === 401) signOut(); })
      .catch(function () {});
  }

  function showBanner(secs) {
    if (!banner) {
      banner = document.createElement('div');
      banner.setAttribute('role', 'alert');
      banner.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:99999;background:#1b1f2a;color:#fff;' +
        'padding:12px 16px;border-radius:10px;box-shadow:0 6px 24px rgba(0,0,0,.35);font:14px system-ui,sans-serif;max-width:300px';
      document.body.appendChild(banner);
    }
    banner.textContent = 'You will be signed out in ' + secs + 's due to inactivity. Move the mouse or press a key to stay signed in.';
  }

  setInterval(function () {
    var lastAny = Math.max(last, stored());       // activity in any tab counts
    var idleFor = Date.now() - lastAny;
    if (idleFor >= IDLE) { signOut(); return; }
    if (idleFor >= IDLE - WARN) showBanner(Math.ceil((IDLE - idleFor) / 1000));
    else if (banner) { banner.remove(); banner = null; }
    // tell the server about real activity (at most once a minute)
    if (lastAny > lastPing && Date.now() - lastPing > 60000) ping();
  }, 1000);

  // Back/forward cache: re-validate the session instead of showing a stale page
  window.addEventListener('pageshow', function (e) { if (e.persisted) window.location.reload(); });
})();
