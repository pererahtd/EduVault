/* EduVault: login-only welcome + persistent admin photo. No other features altered. */
(function () {
  'use strict';
  // Runs synchronously in <head> so a revisited page does not flash the welcome overlay.
  try {
    var script = document.currentScript;
    var token = script && script.getAttribute('data-ev-session');
    var key = 'ev_admin_welcome_session_v1';
    if (token) {
      if (sessionStorage.getItem(key) === token) {
        document.documentElement.classList.add('ev-admin-welcome-seen');
      } else {
        sessionStorage.setItem(key, token);
      }
    }
  } catch (error) {}

  function initAdminProfile() {
    // This is the pre-existing profile picture handler from the uploaded Dashboard.php.
(function () {
    'use strict';
    const menu = document.getElementById('admin-menu');
    const trigger = document.getElementById('admin-menu-trigger');
    const dropdown = document.getElementById('admin-dropdown');
    const image = document.getElementById('admin-avatar-image');
    const avatar = document.getElementById('admin-avatar');
    const picker = document.getElementById('admin-photo-input');
    const remove = document.getElementById('admin-remove-picture');
    const name = document.getElementById('admin-display-name');
    const photoKey = 'eduvault_admin_profile_photo_v1';
    let signedInName = '';
    try {
        signedInName = String(window.eduVaultCurrentAdmin?.name || sessionStorage.getItem('eduvault_admin_name') || '').trim();
    } catch (error) { signedInName = String(window.eduVaultCurrentAdmin?.name || '').trim(); }
    name.textContent = signedInName || 'Admin';
    name.title = name.textContent;
    const photoStorageKey = photoKey + '_' + (signedInName || 'default').toLowerCase();
    function setImage(data) {
        image.hidden = !data;
        image.src = data || '';
        avatar.querySelector('i').hidden = Boolean(data);
        remove.hidden = !data;
    }
    try { setImage(localStorage.getItem(photoStorageKey)); } catch (error) { setImage(null); }
    function setOpen(open) {
        trigger.setAttribute('aria-expanded', String(open));
        dropdown.hidden = !open;
    }
    trigger.addEventListener('click', () => setOpen(dropdown.hidden));
    document.addEventListener('click', event => { if (!menu.contains(event.target)) setOpen(false); });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !dropdown.hidden) { setOpen(false); trigger.focus(); }
    });
    picker.addEventListener('change', () => {
        const file = picker.files?.[0];
        if (!file) return;
        if (!['image/png','image/jpeg','image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
            alert('Please choose a JPG, PNG, or WebP photo under 2 MB.'); picker.value = ''; return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            const data = String(reader.result || '');
            try {
                localStorage.setItem(photoStorageKey, data);
                setImage(data);
                window.eduVaultLog?.('success', 'Administrator profile picture updated');
            } catch (error) { alert('Could not save the picture in this browser. Try a smaller image.'); }
            picker.value = '';
            setOpen(false);
        };
        reader.readAsDataURL(file);
    });
    remove.addEventListener('click', () => {
        try { localStorage.removeItem(photoStorageKey); } catch (error) {}
        setImage(null); setOpen(false);
        window.eduVaultLog?.('info', 'Administrator profile picture removed');
    });
})();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminProfile, {once: true});
  } else {
    initAdminProfile();
  }
})();
