<?php
session_start();
require_once __DIR__ . '/bootstrap_2.php'; // session, current_user(), first_name()
$user = current_user();
$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (empty($_SESSION['user_id'])) {
    if ($action !== null) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Please sign in to manage groups.']);
        exit;
    }
    header('Location: login.php');
    exit;
}

$me = (int) $_SESSION['user_id'];
$displayName = htmlspecialchars((string) ($_SESSION['user_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$displayRole = htmlspecialchars((string) ($_SESSION['user_role'] ?? ''), ENT_QUOTES, 'UTF-8');
require_once __DIR__ . '/db.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function groups_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function groups_require_owner(mysqli $conn, int $groupId, int $userId): void
{
    $stmt = $conn->prepare('SELECT ownerId FROM groups WHERE id = ?');
    $stmt->bind_param('i', $groupId);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc();
    if (!$group) {
        groups_json_response(['ok' => false, 'error' => 'Group not found.'], 404);
    }
    if ((int) $group['ownerId'] !== $userId) {
        groups_json_response(['ok' => false, 'error' => 'Only the group creator can do that.'], 403);
    }
}

// Study sessions API (get_sessions, create_session, cancel_session, rsvp_session)
require_once __DIR__ . '/sessions_api.php';
sessions_handle($conn, $me);

if ($action !== null) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        switch ($action) {
            case 'get_groups':
                $stmt = $conn->prepare(
                    'SELECT g.id, g.name, g.description, g.color, g.ownerId AS owner_id,
                            u.name AS owner_name, COUNT(DISTINCT gm.userId) AS member_count,
                            MAX(CASE WHEN gm.userId = ? THEN 1 ELSE 0 END) AS is_member
                     FROM groups g
                     INNER JOIN users u ON u.id = g.ownerId
                     LEFT JOIN group_members gm ON gm.groupId = g.id
                     GROUP BY g.id, g.name, g.description, g.color, g.ownerId, u.name
                     ORDER BY g.name'
                );
                $stmt->bind_param('i', $me);
                $stmt->execute();
                $result = $stmt->get_result();
                $myGroups = [];
                $exploreGroups = [];
                $previewStmt = $conn->prepare(
                    'SELECT u.id, u.name FROM group_members gm
                     INNER JOIN users u ON u.id = gm.userId
                     WHERE gm.groupId = ? ORDER BY u.name LIMIT 4'
                );
                while ($group = $result->fetch_assoc()) {
                    $group['id'] = (int) $group['id'];
                    $group['owner_id'] = (int) $group['owner_id'];
                    $group['member_count'] = (int) $group['member_count'];
                    $group['is_owner'] = $group['owner_id'] === $me;
                    $isMember = (bool) $group['is_member'] || $group['is_owner'];
                    unset($group['is_member']);
                    $groupId = $group['id'];
                    $previewStmt->bind_param('i', $groupId);
                    $previewStmt->execute();
                    $group['members_preview'] = $previewStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    foreach ($group['members_preview'] as &$member) {
                        $member['id'] = (int) $member['id'];
                    }
                    unset($member);
                    if ($isMember) {
                        $myGroups[] = $group;
                    } else {
                        $exploreGroups[] = $group;
                    }
                }
                groups_json_response(['ok' => true, 'my' => $myGroups, 'explore' => $exploreGroups]);

            case 'create_group':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $color = trim($_POST['color'] ?? '#00a859');
                $nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
                $descriptionLength = function_exists('mb_strlen') ? mb_strlen($description, 'UTF-8') : strlen($description);
                if ($nameLength < 1 || $nameLength > 60) {
                    groups_json_response(['ok' => false, 'error' => 'Group name must be 1 to 60 characters.'], 422);
                }
                if ($descriptionLength > 255) {
                    groups_json_response(['ok' => false, 'error' => 'Description must be 255 characters or fewer.'], 422);
                }
                if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                    $color = '#00a859';
                }
                $conn->begin_transaction();
                $stmt = $conn->prepare('INSERT INTO groups (name, description, color, ownerId) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('sssi', $name, $description, $color, $me);
                $stmt->execute();
                $groupId = (int) $conn->insert_id;
                $memberStmt = $conn->prepare('INSERT INTO group_members (groupId, userId) VALUES (?, ?)');
                $memberStmt->bind_param('ii', $groupId, $me);
                $memberStmt->execute();
                $conn->commit();
                groups_json_response(['ok' => true, 'groupId' => $groupId]);

            case 'join_group':
                $groupId = filter_input(INPUT_POST, 'groupId', FILTER_VALIDATE_INT);
                if (!$groupId || $groupId < 1) {
                    groups_json_response(['ok' => false, 'error' => 'Invalid group.'], 422);
                }
                $check = $conn->prepare('SELECT id FROM groups WHERE id = ?');
                $check->bind_param('i', $groupId);
                $check->execute();
                if (!$check->get_result()->fetch_assoc()) {
                    groups_json_response(['ok' => false, 'error' => 'Group not found.'], 404);
                }
                $check = $conn->prepare('SELECT 1 FROM group_members WHERE groupId = ? AND userId = ?');
                $check->bind_param('ii', $groupId, $me);
                $check->execute();
                if (!$check->get_result()->fetch_row()) {
                    $insert = $conn->prepare('INSERT INTO group_members (groupId, userId) VALUES (?, ?)');
                    $insert->bind_param('ii', $groupId, $me);
                    $insert->execute();
                }
                groups_json_response(['ok' => true]);

            case 'rename_group':
                $groupId = filter_input(INPUT_POST, 'groupId', FILTER_VALIDATE_INT);
                $name = trim($_POST['name'] ?? '');
                $nameLength = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
                if (!$groupId || $groupId < 1 || $nameLength < 1 || $nameLength > 60) {
                    groups_json_response(['ok' => false, 'error' => 'Provide a group name from 1 to 60 characters.'], 422);
                }
                groups_require_owner($conn, $groupId, $me);
                $stmt = $conn->prepare('UPDATE groups SET name = ? WHERE id = ?');
                $stmt->bind_param('si', $name, $groupId);
                $stmt->execute();
                groups_json_response(['ok' => true]);

            case 'delete_group':
                $groupId = filter_input(INPUT_POST, 'groupId', FILTER_VALIDATE_INT);
                if (!$groupId || $groupId < 1) {
                    groups_json_response(['ok' => false, 'error' => 'Invalid group.'], 422);
                }
                groups_require_owner($conn, $groupId, $me);
                $conn->begin_transaction();
                $stmt = $conn->prepare('DELETE FROM session_invites WHERE sessionId IN (SELECT id FROM study_sessions WHERE groupId = ?)');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $stmt = $conn->prepare('DELETE FROM session_rsvps WHERE sessionId IN (SELECT id FROM study_sessions WHERE groupId = ?)');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $stmt = $conn->prepare('DELETE FROM study_sessions WHERE groupId = ?');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $stmt = $conn->prepare('DELETE FROM messages WHERE group_id = ?');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $stmt = $conn->prepare('DELETE FROM group_members WHERE groupId = ?');
                $stmt->bind_param('i', $groupId);
                $stmt->execute();
                $stmt = $conn->prepare('DELETE FROM groups WHERE id = ? AND ownerId = ?');
                $stmt->bind_param('ii', $groupId, $me);
                $stmt->execute();
                $conn->commit();
                groups_json_response(['ok' => true]);

            case 'leave_group':
                $groupId = filter_input(INPUT_POST, 'groupId', FILTER_VALIDATE_INT);
                if (!$groupId || $groupId < 1) {
                    groups_json_response(['ok' => false, 'error' => 'Invalid group.'], 422);
                }
                $check = $conn->prepare('SELECT ownerId FROM groups WHERE id = ?');
                $check->bind_param('i', $groupId);
                $check->execute();
                $group = $check->get_result()->fetch_assoc();
                if (!$group) {
                    groups_json_response(['ok' => false, 'error' => 'Group not found.'], 404);
                }
                if ((int) $group['ownerId'] === $me) {
                    groups_json_response(['ok' => false, 'error' => 'The group creator cannot leave. Delete the group instead.'], 403);
                }
                $stmt = $conn->prepare('DELETE FROM group_members WHERE groupId = ? AND userId = ?');
                $stmt->bind_param('ii', $groupId, $me);
                $stmt->execute();
                groups_json_response(['ok' => true]);

            default:
                groups_json_response(['ok' => false, 'error' => 'Invalid action provided.'], 400);
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Groups database request failed: ' . $e->getMessage());
        if ($conn->connect_errno === 0) {
            try {
                $conn->rollback();
            } catch (mysqli_sql_exception $rollbackError) {
                error_log('Groups database rollback failed: ' . $rollbackError->getMessage());
            }
        }
        groups_json_response(['ok' => false, 'error' => 'The group request could not be completed.'], 500);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<!--
  EduVault LMS - Groups page.
  Group records and membership actions are served by this file's MySQL API.
  Study sessions are served by sessions_api.php (UI in sessions.js / sessions.css).
-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Groups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="sessions.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <link rel="stylesheet" href="groups.css">
    <script>
        const savedTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

</head>
<body class="student-layout">

    <!-- ================= SIDEBAR (unchanged) ================= -->
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
            <li class="active" onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
            <li onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
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

        <!-- TOP BAR (unchanged) -->
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
                Groups
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

                <!-- Profile Section -->
                <div class="profile-short" onclick="window.location.href='profile.php'" title="My Profile">
            <?php
                $isLoggedIn  = isset($user) && !empty($user);
                $rawName     = $isLoggedIn ? trim((string)($user['full_name'] ?? '')) : '';

                // If Logged out Guest, logged in name or Student
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

        <!-- ================= GROUPS CONTENT ================= -->
        <section class="groups-page">

            <!-- Hero banner -->
            <div class="groups-hero">
                <div class="hero-text">
                    <span class="hero-kicker"><i class="fas fa-people-group"></i> Student Community</span>
                    <h2>Groups</h2>
                    <p>Connect and share ideas with your fellow NSBM students.</p>
                    <div class="hero-stats">
                        <div class="hero-stat"><strong id="statJoined">0</strong><span>Joined</span></div>
                        <div class="hero-stat"><strong id="statOwned">0</strong><span>You manage</span></div>
                        <div class="hero-stat"><strong id="statExplore">0</strong><span>To explore</span></div>
                        <div class="hero-stat"><strong id="statSessions">0</strong><span>Upcoming sessions</span></div>
                    </div>
                </div>
                <button class="btn-hero" id="openCreate"><i class="fas fa-plus"></i> Create Group</button>
            </div>

            <!-- Tabs + search -->
            <div class="groups-toolbar">
                <div class="seg">
                    <button class="tab active" data-tab="my"><i class="fas fa-user-group"></i> My Groups <span class="count" id="cntMy">0</span></button>
                    <button class="tab" data-tab="explore"><i class="fas fa-compass"></i> Explore More Groups <span class="count" id="cntExplore">0</span></button>
                    <button class="tab" data-tab="sessions"><i class="fas fa-calendar-days"></i> Study Sessions <span class="count" id="cntSessions">0</span></button>
                </div>
                <div class="groups-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="groupSearch" placeholder="Search groups...">
                </div>
            </div>

            <div id="tab-my" class="groups-grid"></div>
            <div id="tab-explore" class="groups-grid hidden"></div>
            <div id="tab-sessions" class="sx-view hidden" style="padding:0; background:transparent;"></div>

        </section>
        <footer>
            <p>&copy; 2026 EduVault — Student Community Platform | NSBM Green University</p>
        </footer>
    </main>

    <!-- ================= CREATE GROUP MODAL ================= -->
    <div class="ui-modal-overlay hidden" id="createModal">
        <div class="ui-modal create-modal">
            <div class="modal-head">
                <div class="g-avatar" id="createPreview">G</div>
                <div>
                    <h3>Create a new group</h3>
                    <p class="modal-text">You'll be the group admin and can rename or delete it later.</p>
                </div>
            </div>

            <div class="modal-field">
                <label for="groupName">Group name</label>
                <input type="text" id="groupName" maxlength="60" placeholder="Group name">
            </div>
            <div class="modal-field">
                <label for="groupDesc">Description (optional)</label>
                <textarea id="groupDesc" maxlength="255" rows="3" placeholder="What is this group about?"></textarea>
            </div>
            <div class="modal-field">
                <label>Group color</label>
                <div class="swatches" id="swatches"></div>
            </div>

            <p class="form-error" id="createError"></p>
            <div class="modal-actions">
                <button class="g-btn-secondary" id="cancelCreate">Cancel</button>
                <button class="g-btn-primary" id="submitCreate"><i class="fas fa-check"></i> Create group</button>
            </div>
        </div>
    </div>

    <!-- ================= SCRIPTS ================= -->
    <script src="sessions.js"></script>
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
const UI = (() => {
  const palette = ['#4318ff', '#05cd99', '#e53935', '#f6a609', '#0ea5e9', '#a855f7', '#ec4899', '#14b8a6'];
  const esc = value => {
    const el = document.createElement('span');
    el.textContent = value == null ? '' : String(value);
    return el.innerHTML;
  };
  const letter = name => String(name || '').trim().charAt(0).toUpperCase() || '?';
  const colorFor = name => {
    let hash = 0;
    const value = String(name || '');
    for (let i = 0; i < value.length; i++) hash = (hash * 31 + value.charCodeAt(i)) >>> 0;
    return palette[hash % palette.length];
  };
  const avatarHTML = (name, color, cls = '') => `<div class="g-avatar ${cls}" style="background:${esc(color)}">${esc(letter(name))}</div>`;
  function toast(message, type = 'success') {
    let box = document.querySelector('.ui-toasts');
    if (!box) {
      box = document.createElement('div');
      box.className = 'ui-toasts';
      document.body.appendChild(box);
    }
    const item = document.createElement('div');
    item.className = `ui-toast ${type}`;
    item.textContent = message;
    box.appendChild(item);
    setTimeout(() => item.remove(), 3200);
  }
  return {
    esc, letter, colorFor, avatarHTML, toast,
    promptDialog: async options => window.prompt(options.message, options.input.value),
    confirmDialog: async options => window.confirm(options.message)
  };
})();

const GROUP_COLORS = ['#00a859', '#4318ff', '#0ea5e9', '#f6a609', '#ff6b6b', '#a855f7', '#ec4899', '#14b8a6'];
const myTab = document.getElementById('tab-my');
const exploreTab = document.getElementById('tab-explore');
const sessionsTab = document.getElementById('tab-sessions');
const searchInput = document.getElementById('groupSearch');
const createModal = document.getElementById('createModal');
const nameInput = document.getElementById('groupName');
const descInput = document.getElementById('groupDesc');
const errorEl = document.getElementById('createError');
const preview = document.getElementById('createPreview');
const swatchBox = document.getElementById('swatches');
let selectedColor = GROUP_COLORS[0];
let groups = { my: [], explore: [] };
let loadError = '';

async function request(action, values = {}) {
  const body = new URLSearchParams({ action, ...values });
  const response = await fetch('groups.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
    body
  });
  let result;
  try { result = await response.json(); }
  catch (error) { throw new Error('The server returned an invalid response.'); }
  if (!response.ok || !result.ok) throw new Error(result.error || 'The request could not be completed.');
  return result;
}

function filterGroups(list) {
  const query = searchInput.value.trim().toLowerCase();
  if (!query) return list;
  return list.filter(group => group.name.toLowerCase().includes(query) || (group.description || '').toLowerCase().includes(query));
}
function stackHTML(members) {
  return `<div class="avatar-stack">${members.map(member =>
    `<span class="stack-avatar" style="background:${UI.colorFor(member.name)}" title="${UI.esc(member.name)}">${UI.esc(UI.letter(member.name))}</span>`
  ).join('')}</div>`;
}
function cardHTML(group, mode, index) {
  const isMine = mode === 'my';
  const badge = group.is_owner ? '<span class="role-badge"><i class="fas fa-crown"></i> Owner</span>' : '';

  // The 3-dot menu is placed OUTSIDE the banner so the banner can never clip it
  const menu = isMine ? `
    <button class="gc-menu-btn" data-role="menu-btn" aria-label="Group options"><i class="fas fa-ellipsis-v"></i></button>
    <div class="gc-menu hidden">
      <button data-action="open"><i class="fas fa-comments"></i> Open chat</button>
      ${group.is_owner
        ? '<button data-action="rename"><i class="fas fa-pen"></i> Rename group</button><button data-action="delete" class="danger"><i class="fas fa-trash"></i> Delete group</button>'
        : '<button data-action="leave" class="danger"><i class="fas fa-right-from-bracket"></i> Leave group</button>'}
    </div>` : '';

  const creator = isMine ? '' : `<div class="gc-creator"><i class="fas fa-crown"></i> Created by ${UI.esc(group.owner_name)}</div>`;
  const footer = isMine
    ? '<button class="gc-btn soft" data-action="open">Open chat <i class="fas fa-arrow-right"></i></button>'
    : '<button class="gc-btn solid" data-action="join"><i class="fas fa-user-plus"></i> Join group</button>';

  return `
    <article class="group-card ${isMine ? 'clickable' : ''}" data-id="${group.id}" style="--c:${UI.esc(group.color)}; --i:${index}">
      <div class="gc-banner">${badge}</div>
      ${menu}
      ${UI.avatarHTML(group.name, group.color, 'gc-avatar')}
      <div class="gc-body">
        <h3 class="gc-name">${UI.esc(group.name)}</h3>
        <p class="gc-desc">${UI.esc(group.description) || 'No description yet.'}</p>
        ${creator}
        <div class="gc-meta">${stackHTML(group.members_preview)}
          <span class="gc-count">${group.member_count} member${group.member_count === 1 ? '' : 's'}</span>
        </div>
      </div>
      <div class="gc-footer">${footer}</div>
    </article>`;
}
function emptyHTML(mode, noneAtAll) {
  const query = searchInput.value.trim();
  if (loadError) return `<div class="empty-state"><h4>Groups could not be loaded</h4><p>${UI.esc(loadError)}</p></div>`;
  if (query && !noneAtAll) return `<div class="empty-state"><div class="empty-icon"><i class="fas fa-magnifying-glass"></i></div><h4>No groups found</h4><p>Nothing matches "${UI.esc(query)}". Try a different word.</p></div>`;
  return mode === 'my'
    ? '<div class="empty-state"><div class="empty-icon"><i class="fas fa-people-group"></i></div><h4>You haven\'t joined any groups yet</h4><p>Create your own group or explore ones to join.</p></div>'
    : '<div class="empty-state"><div class="empty-icon"><i class="fas fa-compass"></i></div><h4>You\'ve joined every group</h4><p>New groups will show up here when students create them.</p></div>';
}
function render() {
  const mine = groups.my;
  const explore = groups.explore;
  document.getElementById('cntMy').textContent = mine.length;
  document.getElementById('cntExplore').textContent = explore.length;
  document.getElementById('statJoined').textContent = mine.length;
  document.getElementById('statOwned').textContent = mine.filter(group => group.is_owner).length;
  document.getElementById('statExplore').textContent = explore.length;
  const filteredMine = filterGroups(mine);
  const filteredExplore = filterGroups(explore);
  myTab.innerHTML = filteredMine.length ? filteredMine.map((group, i) => cardHTML(group, 'my', i)).join('') : emptyHTML('my', mine.length === 0);
  exploreTab.innerHTML = filteredExplore.length ? filteredExplore.map((group, i) => cardHTML(group, 'explore', i)).join('') : emptyHTML('explore', explore.length === 0);
}
async function loadGroups() {
  loadError = '';
  try {
    const response = await fetch('groups.php?action=get_groups', { headers: { 'Accept': 'application/json' } });
    const result = await response.json();
    if (!response.ok || !result.ok) throw new Error(result.error || 'Unable to load groups.');
    groups = { my: result.my, explore: result.explore };
  } catch (error) {
    loadError = error.message || 'Unable to load groups.';
    UI.toast(loadError, 'error');
  }
  render();
}
function openChat(id) { window.location.href = `group_discussion.php?id=${encodeURIComponent(id)}`; }
function closeMenus() {
  document.querySelectorAll('.gc-menu').forEach(menu => menu.classList.add('hidden'));
  document.querySelectorAll('.group-card.menu-open').forEach(card => card.classList.remove('menu-open'));
}
async function handleAction(action, id) {
  const group = [...groups.my, ...groups.explore].find(item => item.id === id);
  if (!group) return;
  if (action === 'open') { openChat(id); return; }
  if (action === 'join') {
    await request('join_group', { groupId: String(id) });
    openChat(id);
    return;
  }
  if (action === 'rename') {
    const name = await UI.promptDialog({ message: 'Enter the new group name.', input: { value: group.name } });
    if (name === null) return;
    await request('rename_group', { groupId: String(id), name: name.trim() });
    UI.toast('Group renamed');
    await loadGroups();
    return;
  }
  if (action === 'delete') {
    if (!await UI.confirmDialog({ message: `Delete "${group.name}" and its messages for every member?` })) return;
    await request('delete_group', { groupId: String(id) });
    UI.toast('Group deleted');
    await loadGroups();
    loadSessions();
    return;
  }
  if (action === 'leave') {
    if (!await UI.confirmDialog({ message: `Leave "${group.name}"?` })) return;
    await request('leave_group', { groupId: String(id) });
    UI.toast('You left the group');
    await loadGroups();
    loadSessions();
  }
}
[myTab, exploreTab].forEach(grid => grid.addEventListener('click', async event => {
  const card = event.target.closest('.group-card');
  if (!card) return;
  const id = Number(card.dataset.id);
  if (event.target.closest('[data-role="menu-btn"]')) {
    event.stopPropagation();
    const menu = card.querySelector('.gc-menu');
    const wasOpen = !menu.classList.contains('hidden');
    closeMenus();
    if (!wasOpen) { menu.classList.remove('hidden'); card.classList.add('menu-open'); }
    return;
  }
  const button = event.target.closest('[data-action]');
  closeMenus();
  if (button) {
    event.stopPropagation();
    try { await handleAction(button.dataset.action, id); }
    catch (error) { UI.toast(error.message, 'error'); }
  } else if (card.classList.contains('clickable')) openChat(id);
}));
document.addEventListener('click', closeMenus);
searchInput.addEventListener('input', render);

groupTabEvents();
function groupTabEvents() {
  document.querySelectorAll('.seg .tab').forEach(tab => tab.addEventListener('click', () => {
    document.querySelectorAll('.seg .tab').forEach(item => item.classList.toggle('active', item === tab));
    myTab.classList.toggle('hidden', tab.dataset.tab !== 'my');
    exploreTab.classList.toggle('hidden', tab.dataset.tab !== 'explore');
    sessionsTab.classList.toggle('hidden', tab.dataset.tab !== 'sessions');
  }));
}
swatchBox.innerHTML = GROUP_COLORS.map(color =>
  `<button type="button" class="swatch" data-color="${color}" style="background:${color}" aria-label="Color ${color}"></button>`
).join('');
swatchBox.addEventListener('click', event => {
  const swatch = event.target.closest('.swatch');
  if (!swatch) return;
  selectedColor = swatch.dataset.color;
  swatchBox.querySelectorAll('.swatch').forEach(item => item.classList.toggle('selected', item === swatch));
  preview.style.background = selectedColor;
});
nameInput.addEventListener('input', () => { preview.textContent = nameInput.value.trim() ? UI.letter(nameInput.value) : 'G'; });
function openCreateModal() {
  nameInput.value = '';
  descInput.value = '';
  errorEl.textContent = '';
  preview.textContent = 'G';
  selectedColor = GROUP_COLORS[0];
  swatchBox.querySelectorAll('.swatch').forEach((item, index) => item.classList.toggle('selected', index === 0));
  preview.style.background = selectedColor;
  createModal.classList.remove('hidden');
  nameInput.focus();
}
function closeCreateModal() { createModal.classList.add('hidden'); }
async function submitCreate() {
  const name = nameInput.value.trim();
  if (!name) { errorEl.textContent = 'Please enter a group name.'; return; }
  const submitButton = document.getElementById('submitCreate');
  submitButton.disabled = true;
  errorEl.textContent = '';
  try {
    const result = await request('create_group', { name, description: descInput.value.trim(), color: selectedColor });
    openChat(result.groupId);
  } catch (error) {
    errorEl.textContent = error.message;
  } finally {
    submitButton.disabled = false;
  }
}
document.getElementById('openCreate').addEventListener('click', openCreateModal);
document.getElementById('cancelCreate').addEventListener('click', closeCreateModal);
document.getElementById('submitCreate').addEventListener('click', submitCreate);
nameInput.addEventListener('keydown', event => { if (event.key === 'Enter') submitCreate(); });
createModal.addEventListener('click', event => { if (event.target === createModal) closeCreateModal(); });
document.addEventListener('keydown', event => { if (event.key === 'Escape') closeCreateModal(); });

/* ================= STUDY SESSIONS ================= */
EduSessions.init('groups.php');
let sessionList = [];
function renderSessions() {
  const query = searchInput.value.trim().toLowerCase();
  const shown = query
    ? sessionList.filter(s => s.title.toLowerCase().includes(query) || s.group_name.toLowerCase().includes(query))
    : sessionList;
  EduSessions.render(sessionsTab, shown, {
    showGroup: true,
    canCreate: true,
    groups: groups.my,
    onChange: loadSessions
  });
}
async function loadSessions() {
  try { sessionList = await EduSessions.list(); }
  catch (error) { sessionList = []; UI.toast(error.message, 'error'); }
  const count = EduSessions.upcoming(sessionList).length;
  document.getElementById('cntSessions').textContent = count;
  document.getElementById('statSessions').textContent = count;
  renderSessions();
}
searchInput.addEventListener('input', renderSessions);

loadGroups().then(loadSessions);
const params = new URLSearchParams(window.location.search);
if (params.get('msg') === 'deleted') UI.toast('Group deleted');
if (params.get('msg') === 'left') UI.toast('You left the group');
if (window.location.search) window.history.replaceState(null, '', window.location.pathname);
    </script>
<script src='site-search.js'></script></body>
</html>