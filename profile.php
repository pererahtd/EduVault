<?php
require_once 'bootstrap_2.php';
require_once 'db.php';
require_once 'materials_store.php'; // study material list, used by Recent Activity

$user         = current_user();
$loggedInUser = $user ? first_name($user['full_name']) : 'Guest';
$ownerKey     = ($user && function_exists('mat_owner_key')) ? mat_owner_key($user) : '';

$message = '';
$msgType = ''; // 'success' or 'error'

/* ======================================================================
   HELPERS
   ====================================================================== */

/** Runs a SELECT whose placeholders are all integers. Returns [] if anything fails. */
function profile_rows(mysqli $conn, string $sql, array $ints): array
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    $bind = [str_repeat('i', count($ints))];
    foreach ($ints as $k => $v) {
        $bind[] = &$ints[$k];
    }
    call_user_func_array([$stmt, 'bind_param'], $bind);
    if (!$stmt->execute()) {
        return [];
    }
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

/** "5 min ago", "2 days ago" ... or the date for older items. */
function profile_time_ago(int $ts): string
{
    if ($ts <= 0) {
        return 'Earlier';
    }
    $diff = time() - $ts;
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . ' min ago';
    if ($diff < 86400)  return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) {
        $days = (int) floor($diff / 86400);
        return $days . ($days === 1 ? ' day ago' : ' days ago');
    }
    return date('d M Y', $ts);
}

/** Collects this student's real activity from the database and the material store. */
function profile_recent_activity(mysqli $conn, ?array $user, string $ownerKey): array
{
    if (!$user) {
        return [];
    }
    $uid   = (int) $user['id'];
    $items = [];

    // 1) Study material uploads and admin decisions (kept in material_data)
    if ($ownerKey !== '' && function_exists('mat_load')) {
        foreach (mat_load() as $m) {
            if (($m['owner'] ?? '') !== $ownerKey) {
                continue;
            }
            $title  = (string) ($m['title'] ?? 'Untitled');
            $status = strtolower((string) ($m['status'] ?? 'pending'));

            if ($status === 'approved') {
                $items[] = ['icon' => 'fa-circle-check', 'color' => 'green', 'title' => 'Material approved',
                            'sub' => $title, 'ts' => (int) ($m['reviewedAt'] ?? $m['created'] ?? 0)];
            } elseif ($status === 'rejected') {
                $items[] = ['icon' => 'fa-circle-xmark', 'color' => 'red', 'title' => 'Material not approved',
                            'sub' => $title, 'ts' => (int) ($m['reviewedAt'] ?? $m['created'] ?? 0)];
            } else {
                $items[] = ['icon' => 'fa-file-arrow-up', 'color' => 'amber', 'title' => 'Uploaded study material',
                            'sub' => $title . ' · waiting for approval', 'ts' => (int) ($m['created'] ?? 0)];
            }
        }
    }

    // 2) Groups you created
    foreach (profile_rows($conn,
        'SELECT name, created_at FROM groups WHERE ownerId = ? ORDER BY created_at DESC LIMIT 5',
        [$uid]) as $r) {
        $items[] = ['icon' => 'fa-users', 'color' => 'purple', 'title' => 'Created a group',
                    'sub' => (string) $r['name'], 'ts' => (int) strtotime((string) $r['created_at'])];
    }

    // 3) Groups you joined (your own groups are skipped, they are already in step 2)
    foreach (profile_rows($conn,
        'SELECT g.name, gm.joined_at
         FROM group_members gm
         INNER JOIN groups g ON g.id = gm.groupId
         WHERE gm.userId = ? AND g.ownerId <> ?
         ORDER BY gm.joined_at DESC LIMIT 5',
        [$uid, $uid]) as $r) {
        $items[] = ['icon' => 'fa-user-plus', 'color' => 'blue', 'title' => 'Joined a group',
                    'sub' => (string) $r['name'], 'ts' => (int) strtotime((string) $r['joined_at'])];
    }

    // 4) Study sessions you scheduled
    foreach (profile_rows($conn,
        'SELECT title, created_at FROM study_sessions WHERE createdBy = ? ORDER BY created_at DESC LIMIT 5',
        [$uid]) as $r) {
        $items[] = ['icon' => 'fa-calendar-plus', 'color' => 'amber', 'title' => 'Scheduled a study session',
                    'sub' => (string) $r['title'], 'ts' => (int) strtotime((string) $r['created_at'])];
    }

    // 5) Your replies to study sessions (Going / Maybe / Can't go)
    $rsvpLabel = ['going' => 'Going to', 'maybe' => 'Maybe going to', 'no' => "Can't go to"];
    foreach (profile_rows($conn,
        'SELECT s.title, r.status, r.created_at
         FROM session_rsvps r
         INNER JOIN study_sessions s ON s.id = r.sessionId
         WHERE r.userId = ?
         ORDER BY r.created_at DESC LIMIT 5',
        [$uid]) as $r) {
        $label = $rsvpLabel[$r['status']] ?? 'Responded to';
        $items[] = ['icon' => 'fa-calendar-check', 'color' => 'blue', 'title' => 'Study session response',
                    'sub' => $label . ': ' . $r['title'], 'ts' => (int) strtotime((string) $r['created_at'])];
    }

    // Newest first, show the latest 6
    usort($items, fn($a, $b) => $b['ts'] <=> $a['ts']);
    return array_slice($items, 0, 6);
}

/* ======================================================================
   FORM HANDLING
   ====================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ---------- 1) Password change ----------
    if ($_POST['action'] === 'update_password') {
        if (!$user) {
            $message = 'You must be logged in to change your password.';
            $msgType = 'error';
        } else {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword     = $_POST['new_password'] ?? '';
            $uid             = (int) $user['id'];

            $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
            if (!$stmt) {
                $message = 'Database query failed.';
                $msgType = 'error';
            } else {
                $stmt->bind_param('i', $uid);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();

                if (!$row) {
                    $message = 'User not found.';
                    $msgType = 'error';
                } elseif (!password_verify($currentPassword, $row['password'])) {
                    $message = 'Current password is incorrect.';
                    $msgType = 'error';
                } else {
                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $up   = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                    if ($up) {
                        $up->bind_param('si', $hash, $uid);
                        $up->execute();
                        $message = 'Password updated successfully!';
                        $msgType = 'success';
                    } else {
                        $message = 'Database error updating password. Please try again.';
                        $msgType = 'error';
                    }
                }
            }
        }
    }

    // ---------- 2) Profile picture upload ----------
    elseif ($_POST['action'] === 'upload_picture') {
        $file    = $_FILES['profile_picture'] ?? null;
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        // The real image type is read from the file itself, not from the name the browser sends
        $imgInfo = ($file && ($file['error'] ?? 0) === UPLOAD_ERR_OK) ? @getimagesize($file['tmp_name']) : false;

        if (!$user) {
            $message = 'You must be logged in to change your picture.';
            $msgType = 'error';
        } elseif (!$file || ($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            $message = 'Please select a valid image.';
            $msgType = 'error';
        } elseif (!$imgInfo || !isset($allowed[$imgInfo['mime']])) {
            $message = 'Only JPG, PNG, GIF or WEBP images are allowed.';
            $msgType = 'error';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $message = 'The image must be 2 MB or smaller.';
            $msgType = 'error';
        } else {
            $uid      = (int) $user['id'];
            $relPath  = 'uploads/profiles/profile_' . $uid . '_' . time() . '.' . $allowed[$imgInfo['mime']];
            $fullPath = __DIR__ . '/' . $relPath;
            $oldPic   = (string) ($user['profile_picture'] ?? '');

            if (!is_dir(__DIR__ . '/uploads/profiles')) {
                @mkdir(__DIR__ . '/uploads/profiles', 0755, true);
            }

            // Prepare first, so a missing column is caught before the file is saved
            $stmt = $conn->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
            if (!$stmt) {
                $message = 'Database error: the profile_picture column is missing. Run the ALTER TABLE SQL first.';
                $msgType = 'error';
            } elseif (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                $message = 'Could not save the image. Check the uploads/profiles folder permission.';
                $msgType = 'error';
            } else {
                $stmt->bind_param('si', $relPath, $uid);
                if ($stmt->execute()) {
                    // Remove the old picture file only after the new one is saved in the database
                    if ($oldPic !== '' && is_file(__DIR__ . '/' . $oldPic)) {
                        @unlink(__DIR__ . '/' . $oldPic);
                    }
                    $user['profile_picture'] = $relPath;
                    $message = 'Profile picture updated successfully!';
                    $msgType = 'success';
                } else {
                    @unlink($fullPath);
                    $message = 'Database error updating picture.';
                    $msgType = 'error';
                }
            }
        }
    }
}

/* ======================================================================
   DATA FOR THE PAGE
   ====================================================================== */
$isLoggedIn  = isset($user) && !empty($user);
$rawName     = $isLoggedIn ? trim((string) ($user['full_name'] ?? '')) : '';
$firstName   = $isLoggedIn ? htmlspecialchars(first_name($rawName), ENT_QUOTES, 'UTF-8') : 'Guest';
$firstLetter = $rawName !== '' ? strtoupper(substr($rawName, 0, 1)) : ($isLoggedIn ? 'S' : 'G');

$defaultAvatar = 'https://ui-avatars.com/api/?name=' . urlencode($firstLetter)
               . '&background=00a859&color=fff&size=128&font-size=0.5&bold=true';

// Uploaded picture if the file exists. The file time is added so the browser shows a new picture at once.
$picPath    = (string) ($user['profile_picture'] ?? '');
$profilePic = ($picPath !== '' && is_file(__DIR__ . '/' . $picPath))
    ? htmlspecialchars($picPath, ENT_QUOTES, 'UTF-8') . '?v=' . filemtime(__DIR__ . '/' . $picPath)
    : $defaultAvatar;

$recentActivity = profile_recent_activity($conn, $user, $ownerKey);
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | My Profile</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="profile.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <script>
        const savedTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body data-theme="light">

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
            <li class="active" onclick="window.location.href='profile.php'"><i class="far fa-user"></i> My Profile</li>
            <li onclick="window.location.href='youruploads.php'"><i class="fas fa-cloud-upload-alt"></i> Your Uploads</li>
            <li onclick="window.location.href='study-material.php'"><i class="fas fa-book"></i> Study Material</li>
            <li onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
            <li onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
            <li onclick="window.location.href='discussion-qa.php'"><i class="fas fa-comments"></i> Discussion / Q&amp;A</li>
        </ul>

        <ul class="bottom-menu">
            <li onclick="window.location.href='feedback.php'"><i class="far fa-comment-dots"></i> Feedback</li>
            <li id="logout-btn" onclick="window.location.href='<?= $isLoggedIn ? 'logout.php' : 'login.php' ?>'">
                <i class="fas fa-sign-out-alt"></i> <?= $isLoggedIn ? 'Logout' : 'Login' ?>
            </li>
        </ul>
    </aside>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="main-content">
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
                My Profile
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
                    <div class="profile-text">
                        <strong>Hi, <?= $firstName ?></strong>
                        <?php if ($isLoggedIn): ?>
                            <span>Student</span>
                        <?php endif; ?>
                    </div>
                    <img src="<?= $profilePic ?>" alt="User" onerror="this.onerror=null;this.src='<?= $defaultAvatar ?>'">
                </div>
            </div>
        </header>

        <!-- ================= PROFILE GRID ================= -->
        <div class="dashboard-grid">

            <?php if ($message): ?>
                <div class="alert alert-<?= h($msgType) ?>" style="grid-column: 1 / -1; margin-bottom: 15px;">
                    <?= h($message) ?>
                </div>
            <?php endif; ?>

            <!-- Profile card -->
            <div class="card profile-main">

                <!-- Clicking the picture opens the file chooser -->
                <div class="profile-pic-wrapper" style="position: relative; display: inline-block; cursor: pointer; margin-bottom: 15px;"
                     title="Click to change profile picture" onclick="document.getElementById('profile_picture_input').click();">
                    <img src="<?= $profilePic ?>" alt="Profile Picture"
                         onerror="this.onerror=null;this.src='<?= $defaultAvatar ?>'"
                         style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--nsbm-green);">
                    <div class="camera-icon" style="position: absolute; bottom: 5px; right: 5px; background: var(--nsbm-green); color: white; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>

                <!-- Hidden upload form, submits as soon as a file is chosen -->
                <form id="profile-pic-form" action="profile.php" method="POST" enctype="multipart/form-data" style="display: none;">
                    <input type="hidden" name="action" value="upload_picture">
                    <input type="file" id="profile_picture_input" name="profile_picture" accept="image/*"
                           onchange="document.getElementById('profile-pic-form').submit();">
                </form>

                <h3><?= h($user['full_name'] ?? 'Guest') ?></h3>
                <span class="status-badge">NSBM Student</span>

                <div class="profile-info-list">
                    <div class="info-item">
                        <span><i class="fas fa-envelope"></i> Email</span>
                        <strong><?= h($user['email'] ?? '-') ?></strong>
                    </div>
                    <div class="info-item">
                        <span><i class="fas fa-calendar-alt"></i> Joined</span>
                        <strong><?= !empty($user['created_at']) ? date('M Y', strtotime($user['created_at'])) : '-' ?></strong>
                    </div>
                </div>

                <button class="btn-edit" type="button" onclick="document.getElementById('profile_picture_input').click();">
                    <i class="fas fa-camera"></i> Change Picture
                </button>
            </div>

            <!-- Account security -->
            <div class="card profile-security">
                <h3 class="section-title"><i class="fas fa-shield-alt" style="color: var(--nsbm-green);"></i> Account Security</h3>

                <?php if (!$user): ?>
                    <p style="font-size: 13px; color: var(--text-muted);">Log in to change your password.</p>
                <?php else: ?>
                    <form action="profile.php" method="POST">
                        <input type="hidden" name="action" value="update_password">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">Update Password</button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Recent activity (live data) -->
            <div class="card profile-activity">
                <h3 class="section-title"><i class="fas fa-history" style="color: var(--nsbm-green);"></i> Recent Activity</h3>

                <?php if (!$recentActivity): ?>
                    <div class="pa-empty">
                        <i class="far fa-clock"></i>
                        No activity yet. Upload a PDF, join a group or reply to a study session and it will show up here.
                    </div>
                <?php else: ?>
                    <?php foreach ($recentActivity as $act): ?>
                        <div class="activity-item">
                            <div class="activity-icon-wrapper">
                                <div class="activity-icon icon-<?= h($act['color']) ?>">
                                    <i class="fas <?= h($act['icon']) ?>"></i>
                                </div>
                                <div class="activity-text">
                                    <strong><?= h($act['title']) ?></strong>
                                    <span><?= h($act['sub']) ?></span>
                                </div>
                            </div>
                            <span class="activity-time"><?= h(profile_time_ago((int) $act['ts'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </main>

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
        (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");

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
<script src='site-search.js'></script></body>
</html>