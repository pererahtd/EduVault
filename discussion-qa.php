<?php
// htdocs/discussion-qa.php  (student page)
error_reporting(E_ALL);

// Shared bootstrap: database, session and helper functions
require_once __DIR__ . '/bootstrap_2.php';

// Logged in user (null if not logged in)
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<!--
  EduVault LMS - DISCUSSION FORUM & Q&A (student page)
  Posts, replies, likes and best answers are stored in the database through forum_api.php.
  Posts created by the admin (Backend/Discussion_Forums_QA.php) appear here automatically.
  New student posts are "Pending" until an admin approves them (see NEW_POST_STATUS in forum_api.php).
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Discussion / Q&amp;A</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dqa.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <script>
        const savedTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="student-layout">

    <aside class="sidebar">
        <div class="logo-area">
        <div style="font-size: 42px; color: #2563eb; margin: 15px auto 10px; text-align: center;">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
            <h2><span class="edu-text">Edu</span><span class="vault-text">Vault</span></h2>
            <p style="font-size: 10px; color: var(--text-muted);">NSBM Student Hub</p>
        </div>

        <ul class="nav-menu">
            <li onclick="window.location.href='index.php'"><i class="fas fa-home"></i> Dashboard</li>
            <li onclick="window.location.href='profile.php'"><i class="far fa-user"></i> My Profile</li>
            <li onclick="window.location.href='youruploads.php'"><i class="fas fa-cloud-upload-alt"></i> Your Uploads</li>
            <li onclick="window.location.href='study-material.php'"><i class="fas fa-book"></i> Study Material</li>
            <li onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
            <li onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
            <li class="active" onclick="window.location.href='discussion-qa.php'"><i class="fas fa-comments"></i> Discussion / Q&amp;A</li>
        </ul>

        <ul class="bottom-menu">
            <li onclick="window.location.href='feedback.php'"><i class="far fa-comment-dots"></i> Feedback</li>
            <li id="logout-btn" onclick="window.location.href='<?= isset($user) && $user ? 'logout.php' : 'login.php' ?>'">
                <i class="fas fa-sign-out-alt"></i> <?= isset($user) && $user ? 'Logout' : 'Login' ?>
            </li>
        </ul>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
                Discussion / Q&amp;A
            </h2>

            <div class="search-bar">
                <input type="text" placeholder="Search here...">
                <i class="fas fa-search" style="color: var(--text-muted); font-size: 12px;"></i>
            </div>

            <div class="top-icons">
                <!-- Notification Bell -->
                <div class="icon-badge" onclick="window.location.href='notice-board_forst.php'" title="Notifications">
                    <i class="far fa-bell"></i>
                    <span class="badge">3</span>
                </div>

                <!-- Messages Icon -->
                <div class="icon-badge" onclick="window.location.href='groups.php'" title="Messages">
                    <i class="far fa-envelope"></i>
                    <span class="badge">3</span>
                </div>

                <!-- Theme Toggle -->
                <button id="theme-toggle" class="icon-badge theme-btn" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>

                <!-- Profile Section in Topbar -->
                <div class="profile-short" onclick="window.location.href='profile.php'" title="My Profile">
                    <?php
                        $isLoggedIn  = isset($user) && !empty($user);
                        $rawName     = $isLoggedIn ? trim((string)($user['full_name'] ?? '')) : '';
                        
                        if (!$isLoggedIn) {
                            $firstName = 'Guest';
                        } elseif ($rawName !== '') {
                            $firstName = htmlspecialchars(first_name($rawName), ENT_QUOTES, 'UTF-8');
                        } else {
                            $firstName = 'Student';
                        }

                        $firstLetter = $rawName !== '' ? strtoupper(substr($rawName, 0, 1)) : ($isLoggedIn ? 'S' : 'G');
                        $defaultAvatar = "https://ui-avatars.com/api/?name=" . urlencode($firstLetter) . "&background=00a859&color=fff&size=128&font-size=0.5&bold=true";
                        
                        $profilePic = (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) ? htmlspecialchars($user['profile_picture']) : $defaultAvatar;
                    ?>
                    <div class="profile-text">
                        <strong>Hi, <?= $firstName ?></strong>
                        <?php if ($isLoggedIn): ?>
                        <span>Student</span>
                        <?php endif; ?>
                    </div>
                    
                    <img src="<?php echo $profilePic; ?>" alt="User" onerror="this.src='<?php echo $defaultAvatar; ?>'">
                </div>
            </div>
        </header>

        <!-- ================= DISCUSSION / Q&A CONTENT ================= -->
        <section class="fq-page">

            <div class="fq-hero">
                <div>
                    <span class="fq-kicker"><i class="fas fa-comments"></i> Student community</span>
                    <h2>Discussion / Q&amp;A</h2>
                    <p>Start a discussion, ask a question and help your classmates with answers.</p>
                    <div class="fq-stats">
                        <div class="fq-stat"><strong id="fqStatDisc">0</strong><span>Discussions</span></div>
                        <div class="fq-stat"><strong id="fqStatQa">0</strong><span>Questions</span></div>
                        <div class="fq-stat"><strong id="fqStatOpen">0</strong><span>Need an answer</span></div>
                        <div class="fq-stat"><strong id="fqStatMine">0</strong><span>My posts</span></div>
                    </div>
                </div>
                <button class="fq-btn-hero" id="fqNew"><i class="fas fa-plus"></i> New post</button>
            </div>

            <div class="fq-toolbar">
                <div class="fq-chips" id="fqChips"></div>
                <div class="fq-tools">
                    <div class="fq-search"><i class="fas fa-search"></i><input type="text" id="fqSearch" placeholder="Search posts..."></div>
                    <select class="fq-select" id="fqCat" aria-label="Category"></select>
                    <select class="fq-select" id="fqSort" aria-label="Sort">
                        <option value="new">Newest</option>
                        <option value="replies">Most replies</option>
                        <option value="unanswered">Unanswered first</option>
                    </select>
                    <button class="fq-toggle" id="fqMine"><i class="fas fa-user"></i> My posts</button>
                </div>
            </div>

            <div class="fq-list" id="fqList"></div>
        </section>

        <footer>
            <p>&copy; 2026 EduVault — Student Community Platform | NSBM Green University</p>
        </footer>
    </main>

    <!-- ================= VIEW A POST ================= -->
    <div class="nb-overlay nb-hidden" id="fqView">
        <div class="fq-modal" id="fqViewCard" role="dialog" aria-modal="true">
            <button class="fq-x" id="fqViewClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
            <div id="fqViewBody"></div>
        </div>
    </div>

    <!-- ================= CREATE / EDIT A POST ================= -->
    <div class="nb-overlay nb-hidden" id="fqFormModal">
        <div class="fq-modal narrow fq-form" role="dialog" aria-modal="true">
            <button class="fq-x" id="fqFormClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
            <h2 id="fqFormHead"></h2>

            <div class="fq-field" id="fqTypeBox">
                <label class="l">What would you like to post?</label>
                <div class="fq-typebox">
                    <button type="button" class="fq-type on" data-t="discussion"><strong><i class="fas fa-comments"></i> Discussion Forum</strong><small>Start an open discussion for others to share ideas and replies.</small></button>
                    <button type="button" class="fq-type" data-t="qa"><strong><i class="fas fa-circle-question"></i> Q&amp;A</strong><small>Ask a specific question and get answers.</small></button>
                </div>
            </div>
            <div class="fq-field"><label class="l" for="fqTitle" id="fqTitleLabel">Discussion topic</label><input type="text" id="fqTitle" maxlength="160"></div>
            <div class="fq-field"><label class="l" for="fqCategory">Category</label><input type="text" id="fqCategory" maxlength="100" list="catList" autocomplete="off" placeholder="Pick or type a category (e.g. Programming)"><datalist id="catList"></datalist></div>
            <div class="fq-field"><label class="l" for="fqDesc" id="fqDescLabel">Discussion details</label><textarea id="fqDesc" maxlength="2000"></textarea><div class="fq-count" style="text-align:right" id="fqDescCount">0/2000</div></div>
            <div class="fq-field nb-hidden" id="fqPhotoGroup">
                <label class="l" for="fqPhoto">Photo (optional)</label>
                <input type="file" id="fqPhoto" accept="image/png,image/jpeg,image/webp,image/gif">
                <div class="fq-pphoto nb-hidden" id="fqPhotoPrev"><img id="fqPhotoImg" alt="Photo preview"><button type="button" class="fq-act danger" id="fqPhotoRemove"><i class="fas fa-xmark"></i> Remove photo</button></div>
                <div class="fq-count">JPG, PNG, WebP or GIF, max 800 KB.</div>
            </div>
            <div class="fq-notice nb-hidden" id="fqApprovalNote"><i class="fas fa-circle-info"></i><span>New posts are checked by a moderator first. You will see yours right away; everyone else sees it after approval.</span></div>
            <p class="fq-err" id="fqErr"></p>
            <div class="fq-form-actions">
                <button class="nb-btn secondary" id="fqCancel">Cancel</button>
                <button class="fq-primary" id="fqSubmit"><i class="fas fa-paper-plane"></i> Start discussion</button>
            </div>
        </div>
    </div>

    <script>
    // Sidebar Toggle
const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.querySelector('.sidebar');
if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => sidebar.classList.toggle('collapsed')); if (!sidebar.querySelector('.drawer-close')) { const cb = document.createElement('button'); cb.type = 'button'; cb.className = 'drawer-close'; cb.setAttribute('aria-label', 'Close menu'); cb.innerHTML = '<i class="fas fa-xmark"></i>'; cb.addEventListener('click', () => sidebar.classList.remove('collapsed')); sidebar.appendChild(cb); } document.addEventListener('click', e => { if (window.innerWidth <= 992 && sidebar.classList.contains('collapsed') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('collapsed'); });

// Phone drawer: visible close button, and close when tapping outside
if (!sidebar.querySelector('.drawer-close')) {
    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'drawer-close';
    closeBtn.setAttribute('aria-label', 'Close menu');
    closeBtn.innerHTML = '<i class="fas fa-xmark"></i>';
    closeBtn.addEventListener('click', () => sidebar.classList.remove('collapsed'));
    sidebar.appendChild(closeBtn);
}
document.addEventListener('click', e => {
    if (window.innerWidth <= 992 && sidebar.classList.contains('collapsed')
        && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
        sidebar.classList.remove('collapsed');
    }
}); if (!sidebar.querySelector('.drawer-close')) { const cb = document.createElement('button'); cb.type = 'button'; cb.className = 'drawer-close'; cb.setAttribute('aria-label', 'Close menu'); cb.innerHTML = '<i class="fas fa-xmark"></i>'; cb.addEventListener('click', () => sidebar.classList.remove('collapsed')); sidebar.appendChild(cb); } document.addEventListener('click', e => { if (window.innerWidth <= 992 && sidebar.classList.contains('collapsed') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('collapsed'); }); if (!sidebar.querySelector('.drawer-close')) { const cb = document.createElement('button'); cb.type = 'button'; cb.className = 'drawer-close'; cb.setAttribute('aria-label', 'Close menu'); cb.innerHTML = '<i class="fas fa-xmark"></i>'; cb.addEventListener('click', () => sidebar.classList.remove('collapsed')); sidebar.appendChild(cb); } document.addEventListener('click', e => { if (window.innerWidth <= 992 && sidebar.classList.contains('collapsed') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('collapsed'); });
}

// Theme Toggle with Local Storage & System Preference
const themeToggleBtn = document.getElementById('theme-toggle');

// Check in the Local storage , or check the System is in  Dark mode or not
const currentTheme = localStorage.getItem('eduvault_theme') || 
    (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");

// Apply to the HTMl and Body
document.documentElement.setAttribute('data-theme', currentTheme);
document.body.setAttribute('data-theme', currentTheme);

if (themeToggleBtn) {
    themeToggleBtn.querySelector('i').className = currentTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    
    themeToggleBtn.addEventListener('click', () => {
        const nextTheme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', nextTheme);
        document.body.setAttribute('data-theme', nextTheme);
        
        localStorage.setItem('eduvault_theme', nextTheme);
        themeToggleBtn.querySelector('i').className = nextTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
    });
}
    </script>

    <script>
    /* ---------- Small UI helpers: escaping, toast cards, confirm dialog ---------- */
    const NoticeUI = (() => {
      function esc(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
      }

      function box() {
        let b = document.querySelector('.nt-toasts');
        if (!b) {
          b = document.createElement('div');
          b.className = 'nt-toasts';
          document.body.appendChild(b);
        }
        return b;
      }

      function card(o) {
        const dur = o.duration || 8000;
        const el = document.createElement('div');
        el.className = 'nt-toast' + (o.urgent ? ' urgent' : '');
        el.style.setProperty('--cat', o.color || '#00a859');
        el.innerHTML = `
          <span class="nt-ico"><i class="fas ${o.icon || 'fa-bell'}"></i></span>
          <span class="nt-body"><span class="nt-label"></span><span class="nt-title"></span><span class="nt-snippet"></span></span>
          <button class="nt-x" aria-label="Close">&times;</button>
          <i class="nt-bar" style="animation-duration:${dur}ms"></i>`;
        const fill = (sel, val) => {
          const n = el.querySelector(sel);
          if (val) n.textContent = val; else n.remove();
        };
        fill('.nt-label', o.label);
        fill('.nt-title', o.title);
        fill('.nt-snippet', o.text);

        let timer = setTimeout(close, dur);
        function close() {
          clearTimeout(timer);
          el.classList.add('out');
          setTimeout(() => el.remove(), 300);
        }
        el.addEventListener('mouseenter', () => clearTimeout(timer));
        el.addEventListener('mouseleave', () => { timer = setTimeout(close, 2500); });
        el.addEventListener('click', e => {
          if (e.target.closest('.nt-x')) { close(); return; }
          if (o.onClick) o.onClick();
          if (o.href) window.location.href = o.href;
        });
        box().appendChild(el);
        return el;
      }

      function toast(message, type = 'success') {
        const map = {
          success: ['fa-circle-check', '#00a859'],
          error: ['fa-circle-exclamation', '#e53935'],
          info: ['fa-circle-info', '#0ea5e9']
        };
        const [icon, color] = map[type] || map.success;
        return card({ title: message, icon, color, duration: 3800 });
      }

      function confirm(o) {
        return new Promise(resolve => {
          const overlay = document.createElement('div');
          overlay.className = 'nb-overlay';
          overlay.innerHTML = `
            <div class="nb-dialog" role="dialog" aria-modal="true">
              <div class="nb-dialog-ico"><i class="fas ${o.icon || 'fa-trash'}"></i></div>
              <h3></h3><p></p>
              <div class="nb-dialog-actions">
                <button class="nb-btn secondary" data-a="no"></button>
                <button class="nb-btn danger" data-a="yes"></button>
              </div>
            </div>`;
          overlay.querySelector('h3').textContent = o.title || 'Are you sure?';
          overlay.querySelector('p').textContent = o.message || '';
          overlay.querySelector('[data-a="no"]').textContent = o.cancelText || 'Cancel';
          overlay.querySelector('[data-a="yes"]').textContent = o.confirmText || 'Confirm';

          function done(v) {
            document.removeEventListener('keydown', onKey);
            overlay.remove();
            resolve(v);
          }
          function onKey(e) { if (e.key === 'Escape') done(false); }
          overlay.addEventListener('click', e => {
            if (e.target === overlay) done(false);
            const b = e.target.closest('[data-a]');
            if (b) done(b.dataset.a === 'yes');
          });
          document.addEventListener('keydown', onKey);
          document.body.appendChild(overlay);
          overlay.querySelector('[data-a="yes"]').focus();
        });
      }

      return { esc, card, toast, confirm };
    })();
    </script>

    <script>
    // ==================================================================
    // DISCUSSION / Q&A PAGE LOGIC  (data comes from forum_api.php / MySQL)
    // ==================================================================
    (function () {
      'use strict';

      const API = 'forum_api.php';
      const NEW_POST_NEEDS_APPROVAL = false;      // keep in sync with NEW_POST_STATUS in forum_api.php
      const MAX_PHOTO = 800 * 1024;

      const esc = NoticeUI.esc;
      const $ = id => document.getElementById(id);

      let posts = [];
      let ME = { name: 'Student' };
      let loaded = false;
      let loadError = '';

      /* ---------------------------------------------------------------
         SERVER CALLS
         --------------------------------------------------------------- */
      async function api(action, data) {
        const fd = new FormData();
        fd.append('action', action);
        Object.entries(data || {}).forEach(([k, v]) => { if (v !== undefined && v !== null) fd.append(k, v); });
        const res = await fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' });
        let j;
        try { j = await res.json(); } catch (e) { throw new Error('Server error. Please try again.'); }
        if (!j.ok) throw new Error(j.error || 'Something went wrong.');
        return j;
      }

      async function reload() {
        try {
          const res = await fetch(API + '?action=list', { credentials: 'same-origin', cache: 'no-store' });
          const j = await res.json();
          if (!j.ok) throw new Error(j.error || 'Could not load posts.');
          posts = j.posts || [];
          ME = j.me || ME;
          loaded = true;
          loadError = '';
        } catch (e) {
          loadError = e.message || 'Could not load posts.';
        }
      }

      async function run(action, data, okMsg) {
        try {
          await api(action, data);
          await reload();
          renderList();
          if (viewId && !viewModal.classList.contains('nb-hidden')) renderView();
          if (okMsg) NoticeUI.toast(okMsg);
          return true;
        } catch (e) {
          NoticeUI.toast(e.message, 'error');
          return false;
        }
      }

      /* ---------------------------------------------------------------
         HELPERS
         --------------------------------------------------------------- */
      const isQA = p => p.type === 'qa';
      const isMine = p => !!p.mine;
      const repliesOf = p => p.replies || [];
      const hasBest = p => repliesOf(p).some(x => x.best);
      const isAnswered = p => isQA(p) && (!!p.answer || hasBest(p));
      const byId = id => posts.find(p => String(p.id) === String(id));
      const visible = () => posts;
      const COLORS = ['#7b52ff', '#05cd99', '#e53935', '#f6a609', '#0ea5e9', '#ec4899', '#14b8a6'];
      function colorFor(name) { let h = 0; String(name || '').split('').forEach(c => { h = (h * 31 + c.charCodeAt(0)) >>> 0; }); return COLORS[h % COLORS.length]; }
      const letter = n => String(n || '?').trim().charAt(0).toUpperCase() || '?';
      const avatar = n => `<span class="fq-av" style="background:${colorFor(n)}">${esc(letter(n))}</span>`;
      function ago(ms) {
        if (!ms) return 'Earlier';
        const s = Math.max(0, Math.floor((Date.now() - ms) / 1000));
        if (s < 60) return 'just now';
        const m = Math.floor(s / 60);
        if (m < 60) return m + ' min ago';
        const h = Math.floor(m / 60);
        if (h < 24) return h + (h === 1 ? ' hour ago' : ' hours ago');
        const d = Math.floor(h / 24);
        if (d < 7) return d + (d === 1 ? ' day ago' : ' days ago');
        return new Date(ms).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
      }

      /* ---------------------------------------------------------------
         LIST
         --------------------------------------------------------------- */
      const state = { type: 'all', cat: 'all', q: '', sort: 'new', mine: false };
      let firstRender = true;

      function badgesFor(p) {
        const qa = isQA(p), out = [];
        out.push(`<span class="fq-badge" style="--b:${qa ? '#7b52ff' : '#00a859'}"><i class="fas ${qa ? 'fa-circle-question' : 'fa-comments'}"></i> ${qa ? 'Q&A' : 'Discussion'}</span>`);
        if (qa) out.push(isAnswered(p)
          ? '<span class="fq-badge" style="--b:#00a859"><i class="fas fa-circle-check"></i> Answered</span>'
          : '<span class="fq-badge" style="--b:#f6a609"><i class="fas fa-hourglass-half"></i> Needs an answer</span>');
        if (p.status === 'Reported') out.push('<span class="fq-badge" style="--b:#e53935"><i class="fas fa-flag"></i> Under review</span>');
        if (p.status === 'Pending') out.push('<span class="fq-badge" style="--b:#f6a609"><i class="fas fa-clock"></i> Awaiting approval</span>');
        return out.join('');
      }

      function cardHTML(p, i) {
        const qa = isQA(p), n = repliesOf(p).length;
        return `
          <article class="fq-card" data-id="${esc(p.id)}" tabindex="0" style="--c:${qa ? '#7b52ff' : '#00a859'}; --i:${i}">
            <div class="fq-badges">${badgesFor(p)}</div>
            <h3 class="fq-title">${esc(p.title)}</h3>
            <p class="fq-snippet">${esc(p.description)}</p>
            ${qa && p.photo ? `<img class="fq-thumb" src="${esc(p.photo)}" alt="" loading="lazy">` : ''}
            <div class="fq-foot">
              <span class="fq-by">${avatar(p.user)}<span>${esc(p.user)}${isMine(p) ? ' (You)' : ''} · ${esc(ago(p.createdAt))}</span></span>
              <span><span style="margin-right:10px"><i class="fas fa-tag"></i>${esc(p.category || 'General')}</span><span><i class="far fa-comment"></i>${n} repl${n === 1 ? 'y' : 'ies'}</span></span>
            </div>
          </article>`;
      }

      function filtered() {
        const q = state.q.trim().toLowerCase();
        let list = visible().filter(p =>
          (state.type === 'all' || (state.type === 'qa') === isQA(p)) &&
          (state.cat === 'all' || p.category === state.cat) &&
          (!state.mine || isMine(p)) &&
          (!q || (p.title + ' ' + p.description + ' ' + p.user + ' ' + p.category).toLowerCase().includes(q)));
        const byNew = (a, b) => (b.createdAt || 0) - (a.createdAt || 0);
        if (state.sort === 'replies') list.sort((a, b) => repliesOf(b).length - repliesOf(a).length || byNew(a, b));
        else if (state.sort === 'unanswered') list.sort((a, b) => ((isQA(b) && !isAnswered(b)) - (isQA(a) && !isAnswered(a))) || byNew(a, b));
        else list.sort(byNew);
        return list;
      }

      function renderList() {
        const vis = visible();
        $('fqStatDisc').textContent = vis.filter(p => !isQA(p)).length;
        $('fqStatQa').textContent = vis.filter(isQA).length;
        $('fqStatOpen').textContent = vis.filter(p => isQA(p) && !isAnswered(p)).length;
        $('fqStatMine').textContent = posts.filter(isMine).length;

        const count = t => vis.filter(p => t === 'all' || (t === 'qa') === isQA(p)).length;
        $('fqChips').innerHTML = [['all', 'fa-layer-group', 'All'], ['discussion', 'fa-comments', 'Discussion Forum'], ['qa', 'fa-circle-question', 'Q&A']]
          .map(([k, ic, lab]) => `<button class="fq-chip ${state.type === k ? 'active' : ''}" data-type="${k}"><i class="fas ${ic}"></i> ${lab} <span class="n">${count(k)}</span></button>`).join('');

        const cats = Array.from(new Set(vis.map(p => p.category).filter(Boolean))).sort();
        if (state.cat !== 'all' && !cats.includes(state.cat)) state.cat = 'all';
        $('fqCat').innerHTML = '<option value="all">All categories</option>' + cats.map(c => `<option value="${esc(c)}" ${c === state.cat ? 'selected' : ''}>${esc(c)}</option>`).join('');
        $('catList').innerHTML = cats.map(c => `<option value="${esc(c)}">`).join('');

        const grid = $('fqList');
        grid.classList.toggle('no-anim', !firstRender);

        if (!loaded) {
          grid.innerHTML = `<div class="fq-empty"><div class="ic"><i class="fas ${loadError ? 'fa-triangle-exclamation' : 'fa-spinner fa-spin'}"></i></div>
            <h4>${loadError ? 'Could not load posts' : 'Loading posts...'}</h4><p>${esc(loadError)}</p></div>`;
          return;
        }
        firstRender = false;

        const list = filtered();
        if (!list.length) {
          const none = vis.length === 0;
          grid.innerHTML = `<div class="fq-empty"><div class="ic"><i class="fas ${none ? 'fa-comments' : 'fa-magnifying-glass'}"></i></div>
            <h4>${none ? 'No posts yet' : 'Nothing matches your filters'}</h4>
            <p>${none ? 'Be the first to start a discussion or ask a question.' : 'Try another category or clear the search.'}</p></div>`;
        } else grid.innerHTML = list.map(cardHTML).join('');
      }

      $('fqChips').addEventListener('click', e => { const b = e.target.closest('[data-type]'); if (b) { state.type = b.dataset.type; renderList(); } });
      $('fqCat').addEventListener('change', e => { state.cat = e.target.value; renderList(); });
      $('fqSort').addEventListener('change', e => { state.sort = e.target.value; renderList(); });
      $('fqSearch').addEventListener('input', e => { state.q = e.target.value; renderList(); });
      $('fqMine').addEventListener('click', e => { state.mine = !state.mine; e.currentTarget.classList.toggle('on', state.mine); renderList(); });
      $('fqList').addEventListener('click', e => { const c = e.target.closest('.fq-card'); if (c) openView(c.dataset.id); });
      $('fqList').addEventListener('keydown', e => {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const c = e.target.closest('.fq-card'); if (c) { e.preventDefault(); openView(c.dataset.id); }
      });

      /* ---------------------------------------------------------------
         VIEW A POST  (details + replies)
         --------------------------------------------------------------- */
      const viewModal = $('fqView'), viewBody = $('fqViewBody'), viewCard = $('fqViewCard');
      let viewId = null;
      let draft = '';

      function replyHTML(p, r) {
        const canBest = isQA(p) && isMine(p);
        return `
          <div class="fq-reply ${r.best ? 'best' : ''}" data-rid="${esc(r.id)}">
            ${avatar(r.author)}
            <div class="fq-r-main">
              <div class="fq-r-head"><strong>${esc(r.author)}${r.mine ? ' (You)' : ''}</strong><small>${esc(ago(r.ts))}</small>
                ${r.best ? '<span class="fq-badge" style="--b:#00a859"><i class="fas fa-circle-check"></i> Best answer</span>' : ''}</div>
              <div class="fq-r-text">${esc(r.text)}</div>
              <div class="fq-r-acts">
                <button class="fq-mini ${r.liked ? 'on' : ''}" data-act="like"><i class="${r.liked ? 'fas' : 'far'} fa-thumbs-up"></i> ${r.likeCount || ''} Helpful</button>
                ${canBest ? `<button class="fq-mini good" data-act="best"><i class="fas fa-circle-check"></i> ${r.best ? 'Unmark best' : 'Mark as best answer'}</button>` : ''}
                ${r.mine ? '<button class="fq-mini bad" data-act="delreply"><i class="fas fa-trash"></i> Delete</button>' : ''}
              </div>
            </div>
          </div>`;
      }

      function renderView() {
        const p = byId(viewId);
        if (!p) { closeView(); return; }
        const qa = isQA(p), mine = isMine(p);
        const list = repliesOf(p).slice().sort((a, b) => (b.best - a.best) || (b.likeCount - a.likeCount) || a.ts - b.ts);
        const canReply = p.status !== 'Pending';
        viewCard.style.setProperty('--c', qa ? '#7b52ff' : '#00a859');

        viewBody.innerHTML = `
          <div class="fq-badges">${badgesFor(p)}</div>
          <h2>${esc(p.title)}</h2>
          <div class="fq-meta">
            <span><i class="fas fa-user"></i>${esc(p.user)}${mine ? ' (You)' : ''}</span>
            <span><i class="far fa-clock"></i>${esc(ago(p.createdAt))}${p.editedAt ? ' · edited' : ''}</span>
            <span><i class="fas fa-tag"></i>${esc(p.category || 'General')}</span>
          </div>
          <div class="fq-body">${esc(p.description)}</div>
          ${qa && p.photo ? `<img class="fq-photo" src="${esc(p.photo)}" alt="Photo for this question">` : ''}
          ${p.status === 'Pending' ? '<div class="fq-notice"><i class="fas fa-clock"></i><span>Your post is waiting for a moderator to approve it. Other students will see it after approval.</span></div>' : ''}
          ${p.status === 'Reported' ? '<div class="fq-notice"><i class="fas fa-flag"></i><span>This post was reported and is being reviewed by a moderator.</span></div>' : ''}
          ${qa && p.answer ? `<div class="fq-official"><strong><i class="fas fa-circle-check"></i> Official answer</strong><p>${esc(p.answer)}</p></div>` : ''}
          <div class="fq-actions">
            ${mine ? '<button class="fq-act" data-pact="edit"><i class="fas fa-pen"></i> Edit</button><button class="fq-act danger" data-pact="delete"><i class="fas fa-trash"></i> Delete</button>'
                   : `<button class="fq-act danger" data-pact="report" ${p.status !== 'Active' ? 'disabled' : ''}><i class="fas fa-flag"></i> ${p.status === 'Reported' ? 'Reported' : 'Report'}</button>`}
          </div>
          <div class="fq-replies">
            <h3><i class="far fa-comments"></i> ${list.length} ${qa ? (list.length === 1 ? 'Answer' : 'Answers') : (list.length === 1 ? 'Reply' : 'Replies')}</h3>
            ${list.length ? list.map(r => replyHTML(p, r)).join('') : `<div class="fq-noreply">${qa ? 'No answers yet. Know the answer? Help your classmate out!' : 'No replies yet. Start the conversation!'}</div>`}
            ${canReply ? `<div class="fq-compose">
              <textarea id="fqReplyText" maxlength="1000" placeholder="${qa ? 'Write your answer...' : 'Write a reply...'}">${esc(draft)}</textarea>
              <div class="fq-compose-row"><small id="fqReplyCount">${draft.length}/1000</small>
                <button class="fq-primary" id="fqReplySend" ${draft.trim() ? '' : 'disabled'}><i class="fas fa-paper-plane"></i> ${qa ? 'Post answer' : 'Post reply'}</button></div>
            </div>` : ''}
          </div>`;
      }

      function openView(id) {
        viewId = id; draft = '';
        renderView();
        viewModal.classList.remove('nb-hidden');
        viewCard.scrollTop = 0;
      }
      function closeView() { viewModal.classList.add('nb-hidden'); viewId = null; }

      async function addReply() {
        const p = byId(viewId), text = draft.trim();
        if (!p || !text) return;
        const btn = $('fqReplySend'); if (btn) btn.disabled = true;
        const ok = await run('add_reply', { post_id: p.id, text }, 'Posted');
        if (ok) { draft = ''; renderView(); viewCard.scrollTop = viewCard.scrollHeight; }
        else if (btn) btn.disabled = false;
      }

      viewBody.addEventListener('input', e => {
        if (e.target.id !== 'fqReplyText') return;
        draft = e.target.value;
        $('fqReplyCount').textContent = draft.length + '/1000';
        $('fqReplySend').disabled = !draft.trim();
      });
      viewBody.addEventListener('keydown', e => {
        if (e.target.id === 'fqReplyText' && e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); addReply(); }
      });

      viewBody.addEventListener('click', async e => {
        const p = byId(viewId); if (!p) return;
        if (e.target.closest('#fqReplySend')) { addReply(); return; }

        const pa = e.target.closest('[data-pact]');
        if (pa) {
          const a = pa.dataset.pact;
          if (a === 'edit') { closeView(); openForm(p); }
          if (a === 'delete') {
            const yes = await NoticeUI.confirm({ icon: 'fa-trash', title: 'Delete this post?', message: `“${p.title}” and all of its replies will be removed.`, confirmText: 'Delete' });
            if (!yes) return;
            const ok = await run('delete_post', { id: p.id }, 'Post deleted');
            if (ok) closeView();
          }
          if (a === 'report') {
            const yes = await NoticeUI.confirm({ icon: 'fa-flag', title: 'Report this post?', message: 'A moderator will review it. Only report posts that break the community rules.', confirmText: 'Report' });
            if (!yes) return;
            await run('report_post', { id: p.id }, 'Thanks, a moderator will review this post.');
          }
          return;
        }

        const btn = e.target.closest('[data-act]'); if (!btn) return;
        const row = btn.closest('[data-rid]'), r = repliesOf(p).find(x => String(x.id) === row.dataset.rid);
        if (!r) return;

        if (btn.dataset.act === 'like') await run('toggle_like', { reply_id: r.id });
        if (btn.dataset.act === 'best' && isQA(p) && isMine(p)) await run('toggle_best', { reply_id: r.id }, r.best ? 'Best answer removed' : 'Marked as the best answer');
        if (btn.dataset.act === 'delreply' && r.mine) {
          const yes = await NoticeUI.confirm({ icon: 'fa-trash', title: 'Delete your reply?', message: 'This cannot be undone.', confirmText: 'Delete' });
          if (!yes) return;
          await run('delete_reply', { reply_id: r.id });
        }
      });

      $('fqViewClose').addEventListener('click', closeView);
      viewModal.addEventListener('click', e => { if (e.target === viewModal) closeView(); });

      /* ---------------------------------------------------------------
         CREATE / EDIT A POST
         --------------------------------------------------------------- */
      const formModal = $('fqFormModal');
      const fTitle = $('fqTitle'), fCat = $('fqCategory'), fDesc = $('fqDesc'), fErr = $('fqErr'), fPhoto = $('fqPhoto');
      let formType = 'discussion', editingId = null;
      let photoFile = null, photoSrc = '', photoRemove = false;

      function paintType() {
        document.querySelectorAll('.fq-type').forEach(b => b.classList.toggle('on', b.dataset.t === formType));
        const qa = formType === 'qa';
        $('fqPhotoGroup').classList.toggle('nb-hidden', !qa);
        $('fqTitleLabel').textContent = qa ? 'Question title' : 'Discussion topic';
        $('fqDescLabel').textContent = qa ? 'Question details' : 'Discussion details';
        fTitle.placeholder = qa ? 'e.g. How do PHP sessions work?' : 'e.g. Best practices for web development';
        fDesc.placeholder = qa ? 'Explain your question clearly' : 'Describe the topic and invite others to discuss';
        if (!editingId) $('fqSubmit').innerHTML = `<i class="fas fa-paper-plane"></i> ${qa ? 'Post question' : 'Start discussion'}`;
      }
      function paintPhoto() {
        $('fqPhotoPrev').classList.toggle('nb-hidden', !photoSrc);
        $('fqPhotoImg').src = photoSrc || '';
      }
      function updateCount() { $('fqDescCount').textContent = fDesc.value.length + '/2000'; }

      function openForm(p) {
        editingId = p ? p.id : null;
        formType = p ? (isQA(p) ? 'qa' : 'discussion') : 'discussion';
        fTitle.value = p ? p.title : ''; fCat.value = p ? (p.category || '') : ''; fDesc.value = p ? p.description : '';
        photoFile = null; photoRemove = false; photoSrc = p ? (p.photo || '') : ''; fPhoto.value = ''; fErr.textContent = '';
        $('fqFormHead').innerHTML = p ? '<i class="fas fa-pen"></i> Edit your post' : '<i class="fas fa-pen-to-square"></i> Start a discussion or ask a question';
        $('fqTypeBox').classList.toggle('nb-hidden', !!p);
        $('fqApprovalNote').classList.toggle('nb-hidden', !!p || !NEW_POST_NEEDS_APPROVAL);
        if (p) $('fqSubmit').innerHTML = '<i class="fas fa-floppy-disk"></i> Save changes';
        paintType(); paintPhoto(); updateCount();
        formModal.classList.remove('nb-hidden');
        fTitle.focus();
      }
      function closeForm() { formModal.classList.add('nb-hidden'); }

      document.querySelectorAll('.fq-type').forEach(b => b.addEventListener('click', () => { formType = b.dataset.t; paintType(); }));
      fDesc.addEventListener('input', updateCount);
      fPhoto.addEventListener('change', () => {
        const f = fPhoto.files && fPhoto.files[0]; if (!f) return;
        if (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(f.type) || f.size > MAX_PHOTO) {
          fErr.textContent = 'Choose a JPG, PNG, WebP or GIF smaller than 800 KB.'; fPhoto.value = ''; return;
        }
        fErr.textContent = '';
        photoFile = f; photoRemove = false;
        const rd = new FileReader();
        rd.onload = () => { photoSrc = rd.result; paintPhoto(); };
        rd.onerror = () => { fErr.textContent = 'Could not read that photo.'; };
        rd.readAsDataURL(f);
      });
      $('fqPhotoRemove').addEventListener('click', () => { photoFile = null; photoSrc = ''; photoRemove = true; fPhoto.value = ''; paintPhoto(); });

      async function submitForm() {
        const title = fTitle.value.trim(), category = fCat.value.trim(), description = fDesc.value.trim();
        if (!title) { fErr.textContent = 'Please enter a title.'; return; }
        if (!category) { fErr.textContent = 'Please enter a category (e.g. Programming).'; return; }
        if (!description) { fErr.textContent = 'Please add some details.'; return; }
        const qa = formType === 'qa';
        const btn = $('fqSubmit');
        btn.disabled = true; fErr.textContent = '';
        try {
          const data = { type: formType, title, category, description };
          if (qa && photoFile) data.photo = photoFile;
          if (editingId) {
            data.id = editingId;
            if (photoRemove) data.remove_photo = '1';
            await api('update_post', data);
            await reload(); closeForm(); renderList();
            NoticeUI.toast('Post updated');
            openView(editingId);
          } else {
            await api('create_post', data);
            await reload(); closeForm(); renderList();
            NoticeUI.toast(NEW_POST_NEEDS_APPROVAL ? 'Submitted! It will appear after a moderator approves it.' : 'Posted!');
            if (NEW_POST_NEEDS_APPROVAL) { state.mine = true; $('fqMine').classList.add('on'); state.type = 'all'; renderList(); }
          }
        } catch (e) {
          fErr.textContent = e.message;
        } finally {
          btn.disabled = false;
        }
      }

      $('fqSubmit').addEventListener('click', submitForm);
      $('fqCancel').addEventListener('click', closeForm);
      $('fqFormClose').addEventListener('click', closeForm);
      formModal.addEventListener('click', e => { if (e.target === formModal) closeForm(); });
      $('fqNew').addEventListener('click', () => openForm(null));

      document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (!formModal.classList.contains('nb-hidden')) closeForm();
        else if (!viewModal.classList.contains('nb-hidden')) closeView();
      });

      /* ---------------------------------------------------------------
         START
         --------------------------------------------------------------- */
      renderList();
      reload().then(() => {
        renderList();
        const params = new URLSearchParams(window.location.search);
        if (params.get('new')) openForm(null);
        else if (params.get('post') && byId(params.get('post'))) openView(params.get('post'));
        if (window.location.search) window.history.replaceState(null, '', window.location.pathname);
      });

      // Refresh from the database every minute (keeps new admin posts and replies fresh).
      setInterval(async () => {
        if (!formModal.classList.contains('nb-hidden')) return;
        await reload();
        renderList();
        if (viewId && !viewModal.classList.contains('nb-hidden') && !draft.trim()) renderView();
      }, 60000);
    })();
    </script>
<script src='site-search.js'></script></body>
</html>