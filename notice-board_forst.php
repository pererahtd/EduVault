<?php
error_reporting(E_ALL);

if (file_exists(__DIR__ . '/bootstrap_2.php')) { require __DIR__ . '/bootstrap_2.php'; }
if (file_exists(__DIR__ . '/db.php'))    { require __DIR__ . '/db.php'; }
$user = function_exists('current_user') ? current_user() : null;

$notices = [];
try {
    // Use only the columns that exist in the notices table.
// body is renamed to description because the JavaScript below reads description.
$sql = "SELECT id, title, body AS description, category, expires_at, attachment, created_at
        FROM notices
        WHERE expires_at IS NULL OR expires_at >= CURDATE()
        ORDER BY created_at DESC";
    $db = (isset($pdo) && $pdo instanceof PDO) ? $pdo : ($conn ?? null);
    if ($db instanceof PDO) {
        $notices = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($db instanceof mysqli) {
        $r = $db->query($sql);
        if ($r) { $notices = $r->fetch_all(MYSQLI_ASSOC); }
    }
} catch (Throwable $e) {
    // Write the real error to the log so it can be checked later
    error_log('EduVault notice query failed: ' . $e->getMessage());
    $notices = [];
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Notice Board</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="noticeboard.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <script>
        const savedTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body class="student-layout">

    <!-- ================= SIDEBAR ================= -->
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
            <li class="active" onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
            <li onclick="window.location.href='discussion-qa.php'"><i class="fas fa-comments"></i> Discussion / Q&amp;A</li>
        </ul>

        <ul class="bottom-menu">
            <li onclick="window.location.href='feedback.php'"><i class="far fa-comment-dots"></i> Feedback</li>
            <li id="logout-btn" onclick="window.location.href='<?= isset($user) && $user ? 'logout.php' : 'login.php' ?>'">
                <i class="fas fa-sign-out-alt"></i> <?= isset($user) && $user ? 'Logout' : 'Login' ?>
            </li>
        </ul>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="main-content">

        <!-- TOP BAR -->
        <header class="topbar">
    <h2>
        <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
        Notice Board
    </h2>

    <div class="search-bar">
        <input type="text" placeholder="Search here...">
        <i class="fas fa-search" style="color: var(--text-muted); font-size: 12px;"></i>
    </div>

    <div class="top-icons">
        <!-- Notification Bell -->
        <div class="icon-badge" onclick="window.location.href='notice-board_forst.php'" title="Notifications">
            <i class="far fa-bell"></i>
            <span class="badge" id="notifBadge">3</span>
        </div>

        <!-- Messages Icon -->
        <div class="icon-badge" onclick="window.location.href='groups.php'" title="Messages">
            <i class="far fa-envelope"></i>
            <span class="badge" id="msgBadge">3</span>
        </div>

        <!-- Theme Toggle -->
        <button id="theme-toggle" class="icon-badge theme-btn" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-moon"></i>
        </button>

        <!-- Profile Section -->
        <div class="profile-short" onclick="window.location.href='profile.php'" title="My Profile">
            <?php
                $isLoggedIn = isset($user) && !empty($user);
                $rawName    = $isLoggedIn ? trim((string)($user['full_name'] ?? '')) : '';

                if (!$isLoggedIn) {
                    $firstName = 'Guest';
                } elseif ($rawName !== '') {
                    $firstName = htmlspecialchars(first_name($rawName), ENT_QUOTES, 'UTF-8');
                } else {
                    $firstName = 'Student';
                }

                $firstLetter = $rawName !== '' ? strtoupper(substr($rawName, 0, 1)) : ($isLoggedIn ? 'S' : 'G');
            ?>

            <div class="profile-text">
                <strong>Hi, <?= $firstName ?></strong>
                <?php if ($isLoggedIn): ?>
                    <span>Student</span>
                <?php endif; ?>
            </div>

            <img src="<?= h(ev_avatar_url($user)) ?>" alt="User" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($firstLetter) ?>&background=00a859&color=fff&size=128&font-size=0.5&bold=true'">
        </div>
    </div>
</header>

        <!-- ================= NOTICE BOARD CONTENT ================= -->
        <section class="nb-page">
            <div class="nb-hero">
                <div class="nb-hero-text">
                    <span class="nb-kicker"><i class="fas fa-bullhorn"></i> Official announcements</span>
                    <h2>Notice Board</h2>
                    <p>Stay up to date with exams, events and important news from the NSBM administration.</p>
                    <div class="nb-stats">
                        <div class="nb-stat"><strong id="statActive">0</strong><span>Active notices</span></div>
                        <div class="nb-stat"><strong id="statUnread">0</strong><span>Unread</span></div>
                        <div class="nb-stat"><strong id="statUrgent">0</strong><span>Urgent</span></div>
                    </div>
                </div>
                <button class="nb-btn light" id="nbMarkAll"><i class="fas fa-check-double"></i> Mark all as read</button>
            </div>

            <div class="nb-toolbar">
                <div class="nb-chips" id="nbChips"></div>
                <div class="nb-tools">
                    <div class="nb-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="nbSearch" placeholder="Search notices...">
                    </div>
                    <button class="nb-toggle" id="nbUnreadOnly"><i class="fas fa-envelope-open-text"></i> Unread only</button>
                </div>
            </div>

            <div class="nb-grid" id="nbGrid"></div>
        </section>
        
        <footer>
            <p>&copy; 2026 EduVault — Student Community Platform | NSBM Green University</p>
        </footer>
    </main>

    <!-- ================= MODAL ================= -->
    <div class="nb-overlay nb-hidden" id="noticeModal">
        <div class="nb-modal" id="nbModalCard" role="dialog" aria-modal="true">
            <button class="nb-close" id="mClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
            <span class="nb-cat" id="mCat"></span>
            <h2 id="mTitle"></h2>
            <div class="nb-modal-meta" id="mMeta"></div>
            <div class="nb-modal-body" id="mBody"></div>
            <div id="mAttach"></div>
        </div>
    </div>

    <!-- ================= SCRIPTS ================= -->
    <script>
        const serverNotices = <?php echo json_encode($notices); ?>;
    </script>

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
    const NoticeDB = (() => {
      const KEYS = {
        read: 'eduvault_notices_read_v1',
        toasted: 'eduvault_notices_toasted_v1',
        banner: 'eduvault_notices_banner_v1'
      };

      const CATEGORIES = {
        exam:      { label: 'Exam',     icon: 'fa-file-pen',           color: '#6366f1' },
        event:     { label: 'Event',    icon: 'fa-calendar-day',       color: '#0ea5e9' },
        general:   { label: 'General',  icon: 'fa-circle-info',        color: '#00a859' },
        urgent:    { label: 'Urgent',   icon: 'fa-triangle-exclamation', color: '#e53935' },
        academic:  { label: 'Academic', icon: 'fa-book',               color: '#7b52ff' },
        exams:     { label: 'Exams',    icon: 'fa-pen-to-square',      color: '#6366f1' }
      };

      function getArr(k) {
        try { return JSON.parse(localStorage.getItem(k)) || []; } catch (e) { return []; }
      }
      function setArr(k, a) {
        try { localStorage.setItem(k, JSON.stringify(a)); } catch (e) {}
      }

      const getAll = () => {
        return (serverNotices || []).map(n => ({
          id: Number(n.id),
          title: n.title,
          message: n.description || n.message || '',
          category: (n.category || 'general').toLowerCase(),
          createdAt: n.created_at || new Date().toISOString(),
          expiresAt: n.expires_at || null,
          author: 'Admin',
          attachment: n.attachment ? { name: String(n.attachment).replace(/^\d+_/, ''), size: 0, type: '', dataUrl: 'uploads/' + n.attachment } : null
        })).sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
      };

      const getActive = () => getAll();
      const get = id => getAll().find(n => n.id === Number(id)) || null;

      const isRead = id => getArr(KEYS.read).includes(Number(id));
      function markRead(id) {
        const a = getArr(KEYS.read);
        if (a.includes(Number(id))) return;
        a.push(Number(id));
        setArr(KEYS.read, a);
      }
      function markAllRead() {
        const merged = Array.from(new Set(getArr(KEYS.read).concat(getActive().map(n => n.id))));
        setArr(KEYS.read, merged);
      }

      const fmt = {
        date(iso) { return new Date(iso).toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' }); },
        time(iso) { return new Date(iso).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }); },
        dateTime(iso) { return fmt.date(iso) + ' · ' + fmt.time(iso); },
        expiryInfo(n) {
          if (!n.expiresAt) return { text: 'No expiry', soon: false, expired: false };
          const today = new Date(); today.setHours(0, 0, 0, 0);
          const diff = Math.round((new Date(n.expiresAt + 'T00:00:00') - today) / 86400000);
          const label = fmt.date(n.expiresAt + 'T00:00:00');
          if (diff < 0) return { text: 'Expired ' + label, soon: false, expired: true };
          if (diff === 0) return { text: 'Expires today', soon: true, expired: false };
          if (diff === 1) return { text: 'Expires tomorrow', soon: true, expired: false };
          return { text: 'Valid till ' + label, soon: false, expired: false };
        }
      };

      return { CATEGORIES, fmt, getAll, getActive, get, isRead, markRead, markAllRead };
    })();

    const NoticeUI = (() => {
      function esc(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
      }
      function toast(message) { alert(message); }
      return { esc, toast };
    })();
    </script>

    <script>
    const CAT = NoticeDB.CATEGORIES;
    const fmt = NoticeDB.fmt;
    const esc = NoticeUI.esc;

    const grid = document.getElementById('nbGrid');
    const chipsBox = document.getElementById('nbChips');
    const searchInput = document.getElementById('nbSearch');
    const unreadBtn = document.getElementById('nbUnreadOnly');
    const markAllBtn = document.getElementById('nbMarkAll');
    const modal = document.getElementById('noticeModal');
    const modalCard = document.getElementById('nbModalCard');

    let activeCat = 'all';
    let unreadOnly = false;

    function cardHTML(n, i) {
      const c = CAT[n.category] || CAT.general;
      const unread = !NoticeDB.isRead(n.id);
      const exp = fmt.expiryInfo(n);
      const a = n.attachment;
      const attach = a ? `<div class="nb-attach"><i class="fas fa-file-lines"></i> ${esc(a.name)}</div>` : '';
      return `
        <article class="nb-card ${unread ? 'unread' : ''}" data-id="${n.id}" tabindex="0" style="--cat:${c.color}; --i:${i}">
          <div class="nb-card-top">
            <span class="nb-cat"><i class="fas ${c.icon}"></i> ${c.label}</span>
            ${unread ? '<span class="nb-new">NEW</span>' : ''}
          </div>
          <h3 class="nb-title">${esc(n.title)}</h3>
          <p class="nb-preview">${esc(n.message)}</p>
          ${attach}
          <div class="nb-card-foot">
            <span><i class="far fa-clock"></i>${esc(fmt.dateTime(n.createdAt))}</span>
            <span class="nb-exp ${exp.soon ? 'soon' : ''}"><i class="fas fa-hourglass-half"></i>${esc(exp.text)}</span>
          </div>
        </article>`;
    }

    function renderChips(active) {
      const counts = { all: active.length };
      Object.keys(CAT).forEach(k => { counts[k] = active.filter(n => n.category === k).length; });

      chipsBox.innerHTML =
        `<button class="nb-chip ${activeCat === 'all' ? 'active' : ''}" data-cat="all"><i class="fas fa-layer-group"></i> All <span class="n">${counts.all}</span></button>` +
        Object.entries(CAT).map(([k, c]) =>
          counts[k] > 0 ? `<button class="nb-chip ${activeCat === k ? 'active' : ''}" data-cat="${k}" style="--cat:${c.color}"><i class="fas ${c.icon}"></i> ${c.label} <span class="n">${counts[k]}</span></button>` : ''
        ).join('');
    }

    function visibleList(active) {
      const q = searchInput.value.trim().toLowerCase();
      return active.filter(n =>
        (activeCat === 'all' || n.category === activeCat) &&
        (!unreadOnly || !NoticeDB.isRead(n.id)) &&
        (!q || n.title.toLowerCase().includes(q) || n.message.toLowerCase().includes(q)));
    }

    function emptyHTML(totalActive) {
      if (totalActive === 0) {
        return `<div class="nb-empty"><div class="nb-empty-icon"><i class="fas fa-clipboard-check"></i></div>
          <h4>No notices right now</h4><p>New announcements from the administration will appear here.</p></div>`;
      }
      return `<div class="nb-empty"><div class="nb-empty-icon"><i class="fas fa-magnifying-glass"></i></div>
        <h4>No notices match your filters</h4><p>Try another category or clear the search.</p></div>`;
    }

    function render() {
      const active = NoticeDB.getActive();
      const unread = active.filter(n => !NoticeDB.isRead(n.id)).length;

      if(document.getElementById('statActive')) document.getElementById('statActive').textContent = active.length;
      if(document.getElementById('statUnread')) document.getElementById('statUnread').textContent = unread;
      if(document.getElementById('statUrgent')) document.getElementById('statUrgent').textContent = active.filter(n => n.category === 'urgent').length;
      if(markAllBtn) markAllBtn.disabled = unread === 0;

      renderChips(active);
      const list = visibleList(active);
      if(grid) grid.innerHTML = list.length ? list.map(cardHTML).join('') : emptyHTML(active.length);
    }

    if(chipsBox) {
      chipsBox.addEventListener('click', e => {
        const b = e.target.closest('.nb-chip');
        if (!b) return;
        activeCat = b.dataset.cat;
        render();
      });
    }
    if(searchInput) searchInput.addEventListener('input', render);
    if(unreadBtn) {
      unreadBtn.addEventListener('click', () => {
        unreadOnly = !unreadOnly;
        unreadBtn.classList.toggle('on', unreadOnly);
        render();
      });
    }
    if(markAllBtn) {
      markAllBtn.addEventListener('click', () => {
        NoticeDB.markAllRead();
        NoticeUI.toast('All notices marked as read');
        render();
      });
    }

    function openDetail(id) {
      const n = NoticeDB.get(id);
      if (!n) return;
      const c = CAT[n.category] || CAT.general;
      const exp = fmt.expiryInfo(n);

      if(modalCard) modalCard.style.setProperty('--cat', c.color);
      if(document.getElementById('mCat')) {
        document.getElementById('mCat').style.setProperty('--cat', c.color);
        document.getElementById('mCat').innerHTML = `<i class="fas ${c.icon}"></i> ${c.label}`;
      }
      if(document.getElementById('mTitle')) document.getElementById('mTitle').textContent = n.title;
      if(document.getElementById('mMeta')) {
        document.getElementById('mMeta').innerHTML =
          `<span><i class="far fa-clock"></i>Posted ${esc(fmt.dateTime(n.createdAt))}</span>` +
          `<span><i class="fas fa-user-shield"></i>${esc(n.author)}</span>` +
          `<span class="${exp.soon ? 'soon' : ''}"><i class="fas fa-hourglass-half"></i>${esc(exp.text)}</span>`;
      }
      if(document.getElementById('mBody')) document.getElementById('mBody').textContent = n.message;

      const attach = document.getElementById('mAttach');
      if(attach) {
        attach.innerHTML = '';
        if (n.attachment) {
          attach.innerHTML = `<div class="nb-file" style="margin-top:15px;"><div class="info"><strong>${esc(n.attachment.name)}</strong></div><a href="${n.attachment.dataUrl}" class="nb-btn primary" download target="_blank"><i class="fas fa-download"></i> Download</a></div>`;
        }
      }

      if(modal) modal.classList.remove('nb-hidden');
      NoticeDB.markRead(n.id);
      render();
    }

    function closeDetail() { if(modal) modal.classList.add('nb-hidden'); }

    if(grid) {
      grid.addEventListener('click', e => {
        const card = e.target.closest('.nb-card');
        if (card) openDetail(Number(card.dataset.id));
      });
    }
    if(document.getElementById('mClose')) document.getElementById('mClose').addEventListener('click', closeDetail);
    if(modal) modal.addEventListener('click', e => { if (e.target === modal) closeDetail(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDetail(); });

    render();
    </script>
<script src='site-search.js'></script></body>
</html>