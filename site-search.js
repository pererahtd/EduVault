/* site-search.js - search dropdown for every top bar search box */
(function () {
    'use strict';

    // Pages students can search for. t = title, u = link, k = extra keywords
    const PAGES = [
        { t: 'Dashboard',         u: 'index.php',                k: 'home overview' },
        { t: 'My Profile',        u: 'profile.php',              k: 'profile account password picture email' },
        { t: 'Your Uploads',      u: 'youruploads.php',          k: 'upload pdf pending approved rejected batch' },
        { t: 'Study Material',    u: 'study-material.php',       k: 'notes material study download pdf' },
        { t: 'Groups',            u: 'groups.php',               k: 'group create join chat study sessions calendar' },
        { t: 'Notice board',      u: 'notice-board_forst.php',   k: 'notice announcement exam event urgent' },
        { t: 'Discussion / Q&A',  u: 'discussion-qa.php',        k: 'discussion forum question answer post reply' },
        { t: 'Feedback',          u: 'feedback.php',             k: 'feedback bug rating suggestion' },
        { t: 'Login',             u: 'login.php',                k: 'login sign in' },
        { t: 'Register',          u: 'register.php',             k: 'register sign up account otp' },
        { t: 'Forgot password',   u: 'forgot.php',               k: 'forgot password reset' }
    ];

    // Styles are added from here, so no other CSS file needs to change
    const CSS = `
    .topbar { position: relative; z-index: 500; }
    .topbar .search-bar { position: relative; }
    .ss-dd { position: absolute; top: calc(100% + 8px); left: 50%; transform: translateX(-50%);
        width: max(100%, 360px); max-width: 92vw; max-height: 70vh; overflow-y: auto;
        background: var(--card-bg); color: var(--text-main); border: 1px solid var(--border-color);
        border-radius: 14px; box-shadow: 0 18px 40px rgba(0,0,0,.22); padding: 6px; z-index: 3000; text-align: left; }
    .ss-dd[hidden] { display: none; }
    .ss-h { font-size: 10.5px; font-weight: 800; letter-spacing: .5px; text-transform: uppercase; color: var(--text-muted); padding: 8px 10px 4px; }
    .ss-item { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; color: var(--text-main); text-decoration: none; font-size: 13px; }
    .ss-item:hover, .ss-item.on { background: var(--primary-bg); }
    .ss-ico { width: 30px; height: 30px; flex-shrink: 0; border-radius: 9px; display: flex; align-items: center; justify-content: center; background: rgba(0,168,89,.14); color: var(--nsbm-green); font-size: 13px; }
    .ss-txt { min-width: 0; display: flex; flex-direction: column; }
    .ss-txt strong { font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ss-txt small { font-size: 11px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ss-empty { padding: 14px 10px; font-size: 12.5px; color: var(--text-muted); text-align: center; }`;
    const style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const item = (u, icon, t, s) =>
        `<a class="ss-item" href="${esc(u)}"><span class="ss-ico"><i class="fas ${esc(icon)}"></i></span>` +
        `<span class="ss-txt"><strong>${esc(t)}</strong>${s ? `<small>${esc(s)}</small>` : ''}</span></a>`;

    function pageMatches(q) {
        const words = q.toLowerCase().split(/\s+/).filter(Boolean);
        return PAGES.filter(p => {
            const hay = (p.t + ' ' + p.k).toLowerCase();
            return words.every(w => hay.includes(w));
        }).slice(0, 6);
    }

    function render(box, q, pages, content, loading) {
        let html = '';
        if (pages.length) html += '<div class="ss-h">Pages</div>' + pages.map(p => item(p.u, 'fa-compass', p.t, '')).join('');
        if (content.length) html += '<div class="ss-h">Content</div>' + content.map(c => item(c.u, c.i, c.t, c.s)).join('');
        if (loading) html += '<div class="ss-empty">Searching…</div>';
        else if (!pages.length && !content.length) html += `<div class="ss-empty">Nothing found for “${esc(q)}”</div>`;
        box.innerHTML = html;
    }

    document.querySelectorAll('.topbar .search-bar input').forEach(input => {
        const bar = input.closest('.search-bar');
        const box = document.createElement('div');
        box.className = 'ss-dd';
        box.hidden = true;
        bar.appendChild(box);

        let timer = null;
        let seq = 0;
        let pages = [];
        let active = -1;
        const links = () => [...box.querySelectorAll('.ss-item')];
        const mark = () => links().forEach((a, i) => a.classList.toggle('on', i === active));

        input.addEventListener('input', () => {
            const q = input.value.trim();
            clearTimeout(timer);
            active = -1;
            if (!q) { box.hidden = true; return; }

            pages = pageMatches(q);
            render(box, q, pages, [], true);
            box.hidden = false;

            const mySeq = ++seq;
            timer = setTimeout(async () => {
                let content = [];
                try {
                    const res = await fetch('site_search.php?q=' + encodeURIComponent(q), { credentials: 'same-origin', cache: 'no-store' });
                    const data = await res.json();
                    if (data.ok) content = data.items || [];
                } catch (e) { /* offline or not logged in: show pages only */ }
                if (mySeq !== seq) return; // a newer search has started
                render(box, q, pages, content, false);
            }, 300);
        });

        input.addEventListener('focus', () => { if (input.value.trim()) box.hidden = false; });

        input.addEventListener('keydown', e => {
            const list = links();
            if (e.key === 'ArrowDown' && list.length) {
                e.preventDefault(); active = (active + 1) % list.length; mark();
            } else if (e.key === 'ArrowUp' && list.length) {
                e.preventDefault(); active = (active - 1 + list.length) % list.length; mark();
            } else if (e.key === 'Enter') {
                const target = list[active] || list[0];
                if (target) { e.preventDefault(); window.location.href = target.getAttribute('href'); }
            } else if (e.key === 'Escape') {
                box.hidden = true; input.blur();
            }
        });

        document.addEventListener('click', e => { if (!bar.contains(e.target)) box.hidden = true; });
    });
})();