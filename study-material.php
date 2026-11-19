<?php
date_default_timezone_set('Asia/Colombo');
require_once 'bootstrap_2.php';
require_once 'materials_store.php';

$user         = current_user();
$userId       = $user ? mat_owner_key($user) : '';
$rawName      = $user ? trim((string) ($user['full_name'] ?? '')) : '';
$firstName    = $user ? h(first_name($rawName)) : 'Guest';
$firstLetter  = $rawName !== '' ? strtoupper(substr($rawName, 0, 1)) : 'G';

/* ======================================================================
   AJAX: like, comment, view  (answers with JSON)
   ====================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    if (!$user || $userId === '') {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Please log in first.']);
        exit;
    }

    $matId  = (string) ($_POST['material_id'] ?? '');
    $action = (string) $_POST['ajax_action'];

    if (!mat_valid_id($matId) || !in_array($action, ['like', 'comment', 'view'], true)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
        exit;
    }

    $result = ['ok' => false, 'error' => 'Material not found.'];

    mat_mutate(function (array &$items) use ($matId, $action, $userId, $rawName, &$result) {
        foreach ($items as &$m) {
            if (($m['id'] ?? '') !== $matId) {
                continue;
            }
            if (strtolower((string) ($m['status'] ?? '')) !== 'approved') {
                $result = ['ok' => false, 'error' => 'This material is not available.'];
                return;
            }

            $m['likes']    = array_values(array_map('strval', $m['likes'] ?? []));
            $m['comments'] = $m['comments'] ?? [];
            $m['views']    = (int) ($m['views'] ?? 0);

            if ($action === 'like') {
                if (in_array($userId, $m['likes'], true)) {
                    $m['likes'] = array_values(array_diff($m['likes'], [$userId]));
                } else {
                    $m['likes'][] = $userId;
                }
            } elseif ($action === 'comment') {
                $text = mat_clean((string) ($_POST['comment_text'] ?? ''), 500);
                if ($text === '') {
                    $result = ['ok' => false, 'error' => 'Please write a comment first.'];
                    return;
                }
                $m['comments'][] = [
                    'user' => first_name($rawName) ?: 'Student',
                    'text' => $text,
                    'date' => date('d M Y H:i'),
                ];
            } else { // view
                $m['views']++;
            }

            $result = [
                'ok'       => true,
                'likes'    => count($m['likes']),
                'liked'    => in_array($userId, $m['likes'], true),
                'views'    => $m['views'],
                'comments' => $m['comments'],
            ];
            return;
        }
    });

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ======================================================================
   Page data: approved materials, batches and module pairs for filters
   ====================================================================== */
$approved = array_values(array_filter(
    mat_load(),
    fn($m) => strtolower((string) ($m['status'] ?? '')) === 'approved'
));
usort($approved, fn($a, $b) => ($b['reviewedAt'] ?? $b['created'] ?? 0) <=> ($a['reviewedAt'] ?? $a['created'] ?? 0));

$batches = [];
$pairs   = [];
foreach ($approved as $m) {
    $b   = trim((string) ($m['batch'] ?? ''));
    $mod = trim((string) ($m['module'] ?? ''));
    if ($b !== '')   { $batches[$b] = true; }
    if ($mod !== '') { $pairs[] = ['b' => $b, 'm' => $mod]; }
}
$batches = array_keys($batches);
sort($batches);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Study Material</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="stmaterials.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <style>
        .mat-title { font-size: 15px; margin-bottom: 5px; }
        .mat-meta { font-size: 11px; color: var(--text-muted); margin-bottom: 10px; }
        .mat-module { font-size: 11px; background: var(--primary-bg); padding: 6px; border-radius: 6px; margin-bottom: 10px; }
        .view-pill { font-size: 11px; color: var(--text-muted); }
        .btn-block { display: block; text-align: center; margin-bottom: 10px; }
        .action-icon.liked { color: #e74c3c; }
        .cm-row { margin-bottom: 4px; padding-bottom: 2px; border-bottom: 1px dashed var(--border-color); word-break: break-word; }
        .empty-note { grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 30px; display: none; }
        .filters select { min-width: 150px; }
    </style>
    <script>
        document.documentElement.setAttribute('data-theme', localStorage.getItem('eduvault_theme') || 'light');
    </script>
</head>
<body data-theme="light">

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
            <li class="active" onclick="window.location.href='study-material.php'"><i class="fas fa-book"></i> Study Material</li>
            <li onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
            <li onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
            <li onclick="window.location.href='discussion-qa.php'"><i class="fas fa-comments"></i> Discussion / Q&amp;A</li>
        </ul>

        <ul class="bottom-menu">
            <li onclick="window.location.href='feedback.php'"><i class="far fa-comment-dots"></i> Feedback</li>
            <li id="logout-btn" onclick="window.location.href='<?= $user ? 'logout.php' : 'login.php' ?>'">
                <i class="fas fa-sign-out-alt"></i> <?= $user ? 'Logout' : 'Login' ?>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
                Study Materials
            </h2>

            <div class="search-bar">
                <input type="text" placeholder="Search here...">
                <i class="fas fa-search" style="color: var(--text-muted); font-size: 12px;"></i>
            </div>

            <div class="top-icons">
                <div class="icon-badge" onclick="window.location.href='notice-board_forst.php'" title="Notifications">
                    <i class="far fa-bell"></i>
                </div>
                <div class="icon-badge" onclick="window.location.href='groups.php'" title="Messages">
                    <i class="far fa-envelope"></i>
                </div>
                <button id="theme-toggle" class="icon-badge theme-btn" type="button" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <div class="profile-short" onclick="window.location.href='profile.php'" title="My Profile">
                    <div class="profile-text">
                        <strong>Hi, <?= $firstName ?></strong>
                        <?php if ($user): ?><span>Student</span><?php endif; ?>
                    </div>
                    <img src="<?= h(ev_avatar_url($user)) ?>" alt="User"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($firstLetter) ?>&background=00a859&color=fff&size=128&font-size=0.5&bold=true'">
                </div>
            </div>
        </header>

        <section class="pdf-banner">
            <h1>PDF Study Material Portal</h1>
            <p>Access high-resolution lecture notes, official module past papers, and tutorial PDFs shared securely across NSBM batches.</p>
        </section>

        <div class="filters">
            <select id="batch">
                <option value="">All Batches</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= h($b) ?>"><?= h($b) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="module">
                <option value="">All Modules</option>
            </select>
        </div>

        <section class="material-grid" id="materialGrid">
            <?php if (!$approved): ?>
                <div class="empty-note" style="display: block;">No approved PDF materials available yet.</div>
            <?php else: ?>
                <?php foreach ($approved as $item):
                    $id       = (string) $item['id'];
                    $batch    = trim((string) ($item['batch'] ?? ''));
                    $module   = trim((string) ($item['module'] ?? ''));
                    $likes    = array_map('strval', $item['likes'] ?? []);
                    $liked    = in_array($userId, $likes, true);
                    $comments = $item['comments'] ?? [];
                    $views    = (int) ($item['views'] ?? 0);
                ?>
                    <article class="material-card"
                             data-id="<?= h($id) ?>"
                             data-batch="<?= h($batch) ?>"
                             data-module="<?= h($module) ?>">
                        <div>
                            <div class="card-top">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span class="view-pill"><i class="fa-solid fa-eye"></i> <span data-views><?= $views ?></span></span>
                            </div>
                            <h3 class="mat-title"><?= h($item['title'] ?? '') ?></h3>
                            <p class="mat-meta">By: <?= h($item['ownerName'] ?? 'Student') ?> | Batch: <?= h($batch !== '' ? $batch : '-') ?></p>
                            <div class="mat-module">Module: <?= h($module !== '' ? $module : '-') ?></div>
                        </div>

                        <div>
                            <a class="btn-pdf btn-block" href="material_file.php?id=<?= h($id) ?>"
                               target="_blank" rel="noopener" data-act="view">View / Download PDF</a>

                            <div class="interaction-bar">
                                <button type="button" class="action-icon<?= $liked ? ' liked' : '' ?>" data-act="like">
                                    <i class="fa-solid fa-heart"></i> <span data-likes><?= count($likes) ?></span>
                                </button>
                                <span><i class="fa-solid fa-comment"></i> <span data-ccount><?= count($comments) ?></span> Comments</span>
                            </div>

                            <div class="comment-section">
                                <div class="comment-list" data-comments>
                                    <?php foreach ($comments as $c): ?>
                                        <div class="cm-row"><strong><?= h($c['user'] ?? 'Student') ?>:</strong> <?= h($c['text'] ?? '') ?></div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="comment-box">
                                    <input type="text" maxlength="500" placeholder="Add a comment..." data-comment-input>
                                    <button type="button" data-act="comment">Post</button>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <div class="empty-note" id="noMatch">No study materials match these filters.</div>
            <?php endif; ?>
        </section>
    </main>

    <script>
    (function () {
        'use strict';

        /* ---------- Sidebar, and phone drawer close button ---------- */
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => sidebar.classList.toggle('collapsed'));

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
            });
        }

        /* ---------- Theme ---------- */
        const themeBtn = document.getElementById('theme-toggle');
        const applyTheme = t => {
            document.documentElement.setAttribute('data-theme', t);
            document.body.setAttribute('data-theme', t);
            if (themeBtn) themeBtn.querySelector('i').className = t === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        };
        applyTheme(localStorage.getItem('eduvault_theme') || 'light');
        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const next = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                localStorage.setItem('eduvault_theme', next);
                applyTheme(next);
            });
        }

        /* ---------- Batch and module filters ---------- */
        const PAIRS = <?= json_encode($pairs, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const batchSel  = document.getElementById('batch');
        const moduleSel = document.getElementById('module');
        const noMatch   = document.getElementById('noMatch');
        const cards     = Array.from(document.querySelectorAll('.material-card'));

        // Module list shows only the modules of the chosen batch
        function rebuildModules() {
            const chosen = batchSel.value;
            const names = [...new Set(PAIRS.filter(p => !chosen || p.b === chosen).map(p => p.m))].sort();
            const keep = moduleSel.value;
            moduleSel.innerHTML = '';
            moduleSel.add(new Option('All Modules', ''));
            names.forEach(n => moduleSel.add(new Option(n, n)));
            moduleSel.value = names.includes(keep) ? keep : '';
        }

        function applyFilters() {
            const b = batchSel.value;
            const m = moduleSel.value;
            let shown = 0;
            cards.forEach(card => {
                const ok = (!b || card.dataset.batch === b) && (!m || card.dataset.module === m);
                card.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            if (noMatch) noMatch.style.display = (cards.length && !shown) ? 'block' : 'none';
        }

        batchSel.addEventListener('change', () => { rebuildModules(); applyFilters(); });
        moduleSel.addEventListener('change', applyFilters);
        rebuildModules();
        applyFilters();

        /* ---------- Like, comment, view (saved on the server) ---------- */
        const grid = document.getElementById('materialGrid');

        async function send(params) {
            const res = await fetch('study-material.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'Accept': 'application/json' },
                body: new URLSearchParams(params)
            });
            let data = null;
            try { data = await res.json(); } catch (e) { /* not JSON */ }
            if (!res.ok || !data || !data.ok) {
                throw new Error((data && data.error) || 'Something went wrong. Please try again.');
            }
            return data;
        }

        function setText(card, selector, value) {
            const el = card.querySelector(selector);
            if (el) el.textContent = String(value);
        }

        // Builds the comment rows with textContent, so user text can never run as HTML
        function renderComments(card, comments) {
            const list = card.querySelector('[data-comments]');
            list.textContent = '';
            comments.forEach(c => {
                const row = document.createElement('div');
                row.className = 'cm-row';
                const who = document.createElement('strong');
                who.textContent = (c.user || 'Student') + ': ';
                row.append(who, document.createTextNode(c.text || ''));
                list.appendChild(row);
            });
            setText(card, '[data-ccount]', comments.length);
        }

        async function postComment(card) {
            const input = card.querySelector('[data-comment-input]');
            const text = input.value.trim();
            if (!text) return;
            const data = await send({ ajax_action: 'comment', material_id: card.dataset.id, comment_text: text });
            input.value = '';
            renderComments(card, data.comments);
        }

        grid.addEventListener('click', async e => {
            const btn = e.target.closest('[data-act]');
            if (!btn) return;
            const card = btn.closest('.material-card');
            const id = card.dataset.id;

            try {
                if (btn.dataset.act === 'view') {
                    // The PDF opens in a new tab through the normal link. Here we only count the view.
                    send({ ajax_action: 'view', material_id: id })
                        .then(d => setText(card, '[data-views]', d.views))
                        .catch(() => {});
                } else if (btn.dataset.act === 'like') {
                    const data = await send({ ajax_action: 'like', material_id: id });
                    btn.classList.toggle('liked', data.liked);
                    setText(card, '[data-likes]', data.likes);
                } else if (btn.dataset.act === 'comment') {
                    await postComment(card);
                }
            } catch (err) {
                alert(err.message);
            }
        });

        grid.addEventListener('keydown', e => {
            if (e.key === 'Enter' && e.target.matches('[data-comment-input]')) {
                e.preventDefault();
                postComment(e.target.closest('.material-card')).catch(err => alert(err.message));
            }
        });
    })();
    </script>
    <script src="site-search.js"></script>
</body>
</html>