<?php
/*
 * EduVault group chat page and its JSON API.
 *
 * Page:  group_discussion.php?id=N
 * API:   GET  group_discussion.php?action=get_chat_data&groupId=N
 *        POST group_discussion.php  (action=send_message, groupId=N, message=...)
 */

require_once __DIR__ . '/bootstrap_2.php'; // session, current_user(), first_name(), LOGIN_PAGE
require_once __DIR__ . '/db.php';          // mysqli connection in $conn

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ------------------------------------------------------------------
   Helpers
   ------------------------------------------------------------------ */

/** Sends a JSON response and stops the script. */
function chat_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

/** Returns the group when the user is a member or the owner. Otherwise stops with a 404. */
function chat_group_for_member(mysqli $conn, int $groupId, int $userId): array
{
    $stmt = $conn->prepare(
        'SELECT g.id, g.name, g.description, g.color, g.ownerId AS owner_id, u.name AS owner_name
         FROM groups g
         INNER JOIN users u ON u.id = g.ownerId
         LEFT JOIN group_members gm ON gm.groupId = g.id AND gm.userId = ?
         WHERE g.id = ? AND (gm.userId IS NOT NULL OR g.ownerId = ?)'
    );
    $stmt->bind_param('iii', $userId, $groupId, $userId);
    $stmt->execute();
    $group = $stmt->get_result()->fetch_assoc();

    if (!$group) {
        chat_json_response(['ok' => false, 'error' => 'Group not found or you are not a member.'], 404);
    }

    return $group;
}

/* ------------------------------------------------------------------
   Login check
   ------------------------------------------------------------------ */
$user   = current_user();
$me     = $user ? (int) $user['id'] : 0;
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($me === 0) {
    if ($action !== null) {
        chat_json_response(['ok' => false, 'error' => 'Please sign in to use group chat.'], 401);
    }
    header('Location: ' . LOGIN_PAGE);
    exit;
}

/* ------------------------------------------------------------------
   JSON API
   ------------------------------------------------------------------ */
if ($action !== null) {
    try {
        $groupId = filter_var($_GET['groupId'] ?? $_POST['groupId'] ?? null, FILTER_VALIDATE_INT);
        if ($groupId === false || $groupId < 1) {
            chat_json_response(['ok' => false, 'error' => 'Invalid group.'], 422);
        }

        switch ($action) {
            case 'get_chat_data':
                $group = chat_group_for_member($conn, $groupId, $me);

                // Members = group_members rows + the owner
                $memberStmt = $conn->prepare(
                    'SELECT u.id, u.name
                     FROM users u
                     WHERE u.id IN (
                         SELECT gm.userId FROM group_members gm WHERE gm.groupId = ?
                         UNION
                         SELECT g.ownerId FROM groups g WHERE g.id = ?
                     )
                     ORDER BY u.name'
                );
                $memberStmt->bind_param('ii', $groupId, $groupId);
                $memberStmt->execute();
                $members = $memberStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                $messageStmt = $conn->prepare(
                    'SELECT m.id, m.user_id, m.message, m.created_at, u.name
                     FROM messages m
                     LEFT JOIN users u ON u.id = m.user_id
                     WHERE m.group_id = ?
                     ORDER BY m.created_at ASC, m.id ASC'
                );
                $messageStmt->bind_param('i', $groupId);
                $messageStmt->execute();
                $messages = $messageStmt->get_result()->fetch_all(MYSQLI_ASSOC);

                chat_json_response([
                    'ok'       => true,
                    'group'    => $group,
                    'members'  => $members,
                    'messages' => $messages,
                    'me'       => $me,
                ]);

            case 'send_message':
                chat_group_for_member($conn, $groupId, $me);

                $text   = trim((string) ($_POST['message'] ?? ''));
                $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
                if ($length < 1 || $length > 2000) {
                    chat_json_response(['ok' => false, 'error' => 'Messages must be between 1 and 2000 characters.'], 422);
                }

                $stmt = $conn->prepare('INSERT INTO messages (group_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())');
                $stmt->bind_param('iis', $groupId, $me, $text);
                $stmt->execute();

                chat_json_response(['ok' => true, 'messageId' => (int) $conn->insert_id]);

            default:
                chat_json_response(['ok' => false, 'error' => 'Invalid action provided.'], 400);
        }
    } catch (Throwable $e) {
        error_log('EduVault group chat request failed: ' . $e->getMessage());
        chat_json_response(['ok' => false, 'error' => 'The chat request could not be completed.'], 500);
    }
}

/* ------------------------------------------------------------------
   Page data
   ------------------------------------------------------------------ */
$pageGroupId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$pageGroupId = ($pageGroupId !== false && $pageGroupId > 0) ? $pageGroupId : 0;

$rawName     = trim((string) ($user['full_name'] ?? ''));
$firstName   = $rawName !== '' ? htmlspecialchars(first_name($rawName), ENT_QUOTES, 'UTF-8') : 'Student';
$firstLetter = $rawName !== '' ? strtoupper(substr($rawName, 0, 1)) : 'S';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Group Chat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="gdisc.css">
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
            <li class="active" onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
            <li onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
            <li onclick="window.location.href='discussion-qa.php'"><i class="fas fa-comments"></i> Discussion / Q&amp;A</li>
        </ul>

        <ul class="bottom-menu">
            <li onclick="window.location.href='feedback.php'"><i class="far fa-comment-dots"></i> Feedback</li>
            <li id="logout-btn" onclick="window.location.href='logout.php'">
                <i class="fas fa-sign-out-alt"></i> Logout
            </li>
        </ul>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="main-content">

        <!-- TOP BAR -->
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
                Group Chat
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
                <button id="theme-toggle" class="icon-badge theme-btn" type="button" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>

                <!-- Profile Section -->
                <div class="profile-short" onclick="window.location.href='profile.php'" title="My Profile">
                    <div class="profile-text">
                        <strong>Hi, <?= $firstName ?></strong>
                        <span>Student</span>
                    </div>
                    <img src="<?= h(ev_avatar_url($user)) ?>" alt="User" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($firstLetter) ?>&background=00a859&color=fff&size=128&font-size=0.5&bold=true'">
                </div>
            </div>
        </header>

        <!-- ================= CHAT CONTENT ================= -->
        <section class="chat-page" id="chatPage">

            <!-- Chat column -->
            <div class="chat-main">
                <div class="chat-header">
                    <a href="groups.php" class="icon-btn" title="Back to groups"><i class="fas fa-arrow-left"></i></a>
                    <div class="g-avatar" id="chatAvatar">?</div>
                    <div class="chat-title">
                        <strong id="chatName">Loading...</strong>
                        <small id="chatSub"></small>
                    </div>
                    <button class="icon-btn" id="toggleSide" type="button" title="Group info"><i class="fas fa-circle-info"></i></button>
                </div>

                <div class="chat-messages" id="messages"></div>

                <div class="chat-input">
                    <input type="text" id="msgInput" maxlength="2000" placeholder="Type a message..." autocomplete="off">
                    <button class="send-btn" id="sendBtn" type="button" title="Send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>

            <!-- Group info panel -->
            <aside class="chat-side" id="chatSide">
                <div class="side-banner"></div>
                <div class="side-hero">
                    <div class="g-avatar side-avatar" id="sideAvatar">?</div>
                    <h3 id="sideName"></h3>
                    <p class="side-desc" id="sideDesc"></p>
                    <div class="side-owner" id="sideOwner"></div>
                </div>

                <div class="side-members">
                    <h4><i class="fas fa-users"></i> Members <span class="count" id="memberCount">0</span></h4>
                    <ul id="memberList"></ul>
                </div>
            </aside>

        </section>

        <footer>
            <p>&copy; 2026 EduVault — Student Community Platform | NSBM Green University</p>
        </footer>
    </main>

    <!-- ================= THEME + SIDEBAR (same as the other pages) ================= -->
    <script>
        // Sidebar toggle
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

        // Theme toggle with local storage and system preference
        const themeToggleBtn = document.getElementById('theme-toggle');
        const currentTheme = localStorage.getItem('eduvault_theme') ||
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

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

    <!-- ================= CHAT LOGIC ================= -->
    <script>
    (function () {
        'use strict';

        const GROUP_ID    = <?= (int) $pageGroupId ?>;
        const API         = 'group_discussion.php';
        const REFRESH_MS  = 5000;

        const chatPage    = document.getElementById('chatPage');
        const messagesBox = document.getElementById('messages');
        const input       = document.getElementById('msgInput');
        const sendButton  = document.getElementById('sendBtn');
        const toggleSide  = document.getElementById('toggleSide');

        let currentUserId = 0;
        let lastSignature = null;   // null means "not rendered yet"
        let isSending     = false;

        const palette = ['#4318ff', '#05cd99', '#e53935', '#f6a609', '#0ea5e9', '#a855f7', '#ec4899', '#14b8a6'];

        /* ---------- small helpers ---------- */
        function avatarColor(name) {
            let hash = 0;
            for (const ch of String(name || '')) {
                hash = (hash * 31 + ch.charCodeAt(0)) >>> 0;
            }
            return palette[hash % palette.length];
        }

        function initials(name) {
            return String(name || '').trim().charAt(0).toUpperCase() || '?';
        }

        function makeTag(text, className) {
            const tag = document.createElement('span');
            tag.className = 'tag ' + className;
            tag.textContent = text;
            return tag;
        }

        function makeSystemNote(text) {
            const note = document.createElement('div');
            note.className = 'sys-msg';
            note.textContent = text;
            return note;
        }

        /* ---------- rendering ---------- */
        function renderGroup(data) {
            const group   = data.group;
            const members = data.members;
            const name    = group.name || 'Group';
            const color   = group.color || '#00a859';

            document.title = 'EduVault | ' + name;
            document.getElementById('chatName').textContent = name;
            document.getElementById('chatSub').textContent =
                members.length + ' member' + (members.length === 1 ? '' : 's');

            ['chatAvatar', 'sideAvatar'].forEach(id => {
                const avatar = document.getElementById(id);
                avatar.textContent = initials(name);
                avatar.style.background = color;
            });
            document.getElementById('chatSide').style.setProperty('--c', color);

            document.getElementById('sideName').textContent = name;
            document.getElementById('sideDesc').textContent = group.description || 'No description yet.';

            const isOwner = Number(group.owner_id) === currentUserId;
            const owner = document.getElementById('sideOwner');
            owner.replaceChildren();
            const crown = document.createElement('i');
            crown.className = 'fas fa-crown';
            owner.append(crown, isOwner ? ' You created this group' : ' Created by ' + group.owner_name);

            document.getElementById('memberCount').textContent = members.length;
            const list = document.getElementById('memberList');
            list.replaceChildren();

            members.forEach(member => {
                const item = document.createElement('li');
                item.className = 'member-row';

                const avatar = document.createElement('div');
                avatar.className = 'm-avatar';
                avatar.style.background = avatarColor(member.name);
                avatar.textContent = initials(member.name);

                const info = document.createElement('div');
                info.className = 'm-info';

                const nameEl = document.createElement('span');
                nameEl.className = 'm-name';
                nameEl.textContent = member.name;
                info.appendChild(nameEl);

                const tags = document.createElement('div');
                tags.className = 'm-tags';
                if (Number(member.id) === Number(group.owner_id)) tags.appendChild(makeTag('Owner', 'tag-owner'));
                if (Number(member.id) === currentUserId) tags.appendChild(makeTag('You', 'tag-you'));
                info.appendChild(tags);

                item.append(avatar, info);
                list.appendChild(item);
            });
        }

        function renderMessages(messages, forceScroll) {
            const signature = messages.map(m => m.id).join(',');
            if (!forceScroll && signature === lastSignature) return;

            const isFirstRender = lastSignature === null;
            const nearBottom = messagesBox.scrollHeight - messagesBox.scrollTop - messagesBox.clientHeight < 80;
            lastSignature = signature;

            messagesBox.replaceChildren();

            if (messages.length === 0) {
                messagesBox.appendChild(makeSystemNote('No messages yet. Say hello!'));
                return;
            }

            let currentDay = '';
            let previousSender = null;

            messages.forEach(message => {
                const date = new Date(String(message.created_at).replace(' ', 'T'));
                if (Number.isNaN(date.getTime())) return;

                // Day divider
                const day = date.toDateString();
                if (day !== currentDay) {
                    currentDay = day;
                    previousSender = null;
                    const divider = document.createElement('div');
                    divider.className = 'day-divider';
                    divider.textContent = date.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
                    messagesBox.appendChild(divider);
                }

                const senderId   = Number(message.user_id);
                const mine       = senderId === currentUserId;
                const sameSender = previousSender === senderId;
                previousSender   = senderId;

                const row = document.createElement('div');
                row.className = 'msg-row ' + (mine ? 'mine' : 'theirs') + (sameSender ? ' cont' : '');

                if (!mine) {
                    const avatar = document.createElement('div');
                    avatar.className = 'msg-avatar' + (sameSender ? ' spacer' : '');
                    if (!sameSender) {
                        avatar.style.background = avatarColor(message.name);
                        avatar.textContent = initials(message.name);
                    }
                    row.appendChild(avatar);
                }

                const bubble = document.createElement('div');
                bubble.className = 'msg';

                if (!mine && !sameSender) {
                    const sender = document.createElement('div');
                    sender.className = 'sender';
                    sender.style.color = avatarColor(message.name);
                    sender.textContent = message.name || 'Member';
                    bubble.appendChild(sender);
                }

                const text = document.createElement('div');
                text.className = 'text';
                text.textContent = message.message;
                bubble.appendChild(text);

                const time = document.createElement('div');
                time.className = 'time';
                time.textContent = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                bubble.appendChild(time);

                row.appendChild(bubble);
                messagesBox.appendChild(row);
            });

            if (isFirstRender || nearBottom || forceScroll) {
                messagesBox.scrollTop = messagesBox.scrollHeight;
            }
        }

        /* ---------- server calls ---------- */
        async function loadChat(forceScroll) {
            const response = await fetch(API + '?action=get_chat_data&groupId=' + GROUP_ID, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json' }
            });

            let data;
            try {
                data = await response.json();
            } catch (error) {
                throw new Error('The server returned an invalid response. Check the Apache error log.');
            }

            if (!response.ok || !data.ok) {
                throw new Error(data.error || 'Unable to load this group chat.');
            }

            currentUserId = Number(data.me);
            renderGroup(data);
            renderMessages(data.messages, forceScroll);
        }

        async function sendMessage() {
            const text = input.value.trim();
            if (!text || isSending) return;

            isSending = true;
            sendButton.disabled = true;

            try {
                const response = await fetch(API, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                    body: new URLSearchParams({
                        action: 'send_message',
                        groupId: String(GROUP_ID),
                        message: text
                    })
                });

                let result;
                try {
                    result = await response.json();
                } catch (error) {
                    throw new Error('The server returned an invalid response.');
                }

                if (!response.ok || !result.ok) {
                    throw new Error(result.error || 'Message could not be sent.');
                }

                input.value = '';
                await loadChat(true);
            } catch (error) {
                alert(error.message);
            } finally {
                isSending = false;
                sendButton.disabled = false;
                input.focus();
            }
        }

        /* ---------- events ---------- */
        sendButton.addEventListener('click', sendMessage);

        input.addEventListener('keydown', event => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        });

        // Show or hide the group info panel
        toggleSide.addEventListener('click', () => {
            if (window.innerWidth <= 992) {
                chatPage.classList.toggle('show-side');
            } else {
                chatPage.classList.toggle('side-hidden');
            }
        });

        /* ---------- start ---------- */
        if (!Number.isInteger(GROUP_ID) || GROUP_ID < 1) {
            window.location.href = 'groups.php';
            return;
        }

        loadChat(true).catch(error => {
            messagesBox.replaceChildren(makeSystemNote(error.message));
            if (/not a member|not found/i.test(error.message)) {
                setTimeout(() => { window.location.href = 'groups.php'; }, 1500);
            }
        });

        // Refresh messages every few seconds, but not while the tab is hidden
        setInterval(() => {
            if (document.hidden) return;
            loadChat(false).catch(error => console.error('Chat refresh failed:', error));
        }, REFRESH_MS);
    })();
    </script>
<script src='site-search.js'></script></body>
</html>