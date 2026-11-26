<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
date_default_timezone_set('Asia/Colombo'); // Sri Lanka time for dates and countdowns
require_once 'bootstrap_2.php';
require_once 'materials_store.php';
require_once __DIR__ . '/db.php';

$user = current_user();
$loggedInUser = $user ? first_name($user['full_name']) : 'Guest';

// Points Calculation Logic (1 Approved PDF = 10 Points)
$allMaterials = function_exists('mat_load') ? mat_load() : [];
$userScores = [];
$userNames = [];

$normName   = fn(string $n): string => strtolower(trim(preg_replace('/\s+/', ' ', $n)));
$ownerKey   = (function_exists('mat_owner_key') && $user) ? mat_owner_key($user) : '';
$myNameKey  = $user ? $normName((string)($user['full_name'] ?? $user['name'] ?? '')) : '';
$myGroupKey = $myNameKey !== '' ? 'name:' . $myNameKey : ($ownerKey !== '' ? 'id:' . $ownerKey : '');

foreach ($allMaterials as $m) {
    if (isset($m['status']) && strtolower((string)$m['status']) === 'approved') {
        $uid   = (string)($m['owner'] ?? 'unknown');
        $uname = trim((string)($m['ownerName'] ?? 'Student')) ?: 'Student';
        $nKey  = $normName($uname);

        // Merge points if the same student had a different user ID before DB backup restore
        $groupKey = ($nKey !== '' && $nKey !== 'student') ? 'name:' . $nKey : 'id:' . $uid;

        if (!isset($userScores[$groupKey])) {
            $userScores[$groupKey] = 0;
            $userNames[$groupKey]  = $uname;
        }
        $userScores[$groupKey] += 10;
    }
}

arsort($userScores);
$topUsers = array_slice($userScores, 0, 5, true);
$myScore  = $myGroupKey !== '' ? ($userScores[$myGroupKey] ?? 0) : 0;

// This student's own uploads (all statuses), newest first
$myMaterials = [];
if ($ownerKey !== '' || $myNameKey !== '') {
    foreach ($allMaterials as $mat) {
        $matOwner = (string)($mat['owner'] ?? '');
        $matName  = $normName((string)($mat['ownerName'] ?? ''));
        if (($ownerKey !== '' && $matOwner === $ownerKey) || ($myNameKey !== '' && $matName === $myNameKey)) {
            $myMaterials[] = $mat;
        }
    }
    usort($myMaterials, fn($a, $b) => ($b['created'] ?? 0) <=> ($a['created'] ?? 0));
}

$myCounts = ['approved' => 0, 'pending' => 0, 'rejected' => 0];
foreach ($myMaterials as $mat) {
    $st = strtolower((string) ($mat['status'] ?? 'pending'));
    if (isset($myCounts[$st])) {
        $myCounts[$st]++;
    }
}

// Top 3 for the leaderboard, and this student's rank
$lbTop = [];
foreach ($topUsers as $gKey => $pts) {
    $lbTop[] = [
        'key'    => (string) $gKey,
        'name'   => (string) ($userNames[$gKey] ?? 'Student'),
        'points' => (int) $pts,
    ];
}
$myRank = 0;
$pos = 0;
foreach ($userScores as $gKey => $pts) {
    $pos++;
    if ((string) $gKey === (string) $myGroupKey) {
        $myRank = $pos;
        break;
    }
}

// Live JSON endpoint for real-time leaderboard updates
if (isset($_GET['ajax']) && $_GET['ajax'] === 'leaderboard') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'         => true,
        'top'        => array_slice($lbTop, 0, 3),
        'myGroupKey' => $myGroupKey,
        'myRank'     => $myRank,
        'myScore'    => (int) $myScore,
        'counts'     => $myCounts,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Fetch Upcoming Events from Database
$upcomingEvents = [];
$eventQuery = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 5";
$result = $conn->query($eventQuery);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $upcomingEvents[] = $row;
    }
}

// Fetch Announcements from Database
$announcementsList = [];
$annQuery = "SELECT * FROM announcements ORDER BY publish_date DESC LIMIT 5";
$annResult = $conn->query($annQuery);
if ($annResult) {
    while ($row = $annResult->fetch_assoc()) {
        $announcementsList[] = $row;
    }
}

function dash_fetch_all(mysqli $conn, string $sql, string $types, array $params): array
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    if ($types !== '') {
        $bind = [$types];
        foreach ($params as $k => $v) {
            $bind[] = &$params[$k];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind);
    }
    if (!$stmt->execute()) {
        return [];
    }
    $res = $stmt->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

$hourNow = (int) date('G');
if ($hourNow >= 5 && $hourNow < 12)      { $greetWord = 'Good morning'; }
elseif ($hourNow >= 12 && $hourNow < 17) { $greetWord = 'Good afternoon'; }
elseif ($hourNow >= 17 && $hourNow < 21) { $greetWord = 'Good evening'; }
else                                     { $greetWord = 'Good night'; }
$todayText = date('l, j F Y');

$dashUser     = $user ?: [];
$profileName  = (string) ($dashUser['full_name'] ?? 'Guest');
$profileEmail = (string) ($dashUser['email'] ?? '');
$joinedTs     = !empty($dashUser['created_at']) ? (int) strtotime($dashUser['created_at']) : 0;

// Next study sessions from the groups this student belongs to
$nextSessions = [];
if ($user) {
    $myId   = (int) $user['id'];
    $nowStr = date('Y-m-d H:i:s');
    $nextSessions = dash_fetch_all(
        $conn,
        "SELECT s.id, s.title, s.startsAt, s.endsAt, s.location,
                g.name AS group_name, g.color AS group_color
         FROM study_sessions s
         INNER JOIN groups g ON g.id = s.groupId
         LEFT JOIN group_members gm ON gm.groupId = s.groupId AND gm.userId = ?
         WHERE s.status <> 'cancelled'
           AND s.endsAt >= ?
           AND (gm.userId IS NOT NULL OR g.ownerId = ?)
           AND (s.visibility = 'group' OR s.createdBy = ?
                OR EXISTS (SELECT 1 FROM session_invites i WHERE i.sessionId = s.id AND i.userId = ?))
         ORDER BY s.startsAt ASC
         LIMIT 3",
        'isiii',
        [$myId, $nowStr, $myId, $myId, $myId]
    );
}

// Points per month for the last 6 months (1 approved PDF = 10 points)
$chartMonths = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = mktime(0, 0, 0, (int) date('n') - $i, 1, (int) date('Y'));
    $chartMonths[date('Y-m', $ts)] = ['label' => date('M', $ts), 'points' => 0];
}
foreach ($myMaterials as $mat) {
    if (strtolower((string) ($mat['status'] ?? '')) !== 'approved') {
        continue;
    }
    $when = (int) ($mat['reviewedAt'] ?? $mat['created'] ?? 0);
    $key  = date('Y-m', $when);
    if (isset($chartMonths[$key])) {
        $chartMonths[$key]['points'] += 10;
    }
}
$chartMax = max(10, max(array_column($chartMonths, 'points')));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="indexstyle.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <link rel="stylesheet" href="dashboard.css">
    <script>
        const savedTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
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
            <li class="active" onclick="window.location.href='index.php'"><i class="fas fa-home"></i> Dashboard</li>
            <li onclick="window.location.href='profile.php'"><i class="far fa-user"></i> My Profile</li>
            <li onclick="window.location.href='youruploads.php'"><i class="fas fa-cloud-upload-alt"></i> Your Uploads</li>
            <li onclick="window.location.href='study-material.php'"><i class="fas fa-book"></i> Study Material</li>
            <li onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
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

    <main class="main-content">
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="margin-right: 10px; color: var(--text-muted); cursor: pointer;"></i>
                Dashboard
            </h2>

            <div class="search-bar">
                <input type="text" placeholder="Search here...">
                <i class="fas fa-search" style="color: var(--text-muted); font-size: 12px;"></i>
            </div>

            <div class="top-icons">
                <div class="icon-badge" onclick="window.location.href='notice-board_forst.php'" title="Notifications">
                    <i class="far fa-bell"></i>
                    <span class="badge">3</span>
                </div>

                <div class="icon-badge" onclick="window.location.href='groups.php'" title="Messages">
                    <i class="far fa-envelope"></i>
                    <span class="badge">3</span>
                </div>

                <button id="theme-toggle" class="icon-badge theme-btn" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon"></i>
                </button>

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

        <div class="dashboard-grid">

            <!-- Row 1: greeting banner (theme, live countdown, photo) + GPA -->
            <div class="card welcome-banner">
                <div class="wb-greet">
                    <span class="wb-kicker"><i class="far fa-calendar"></i> <span id="wbDate"><?= h($todayText) ?></span></span>
                    <h3 id="dynamic-greeting">
                        <span id="greetWord"><?= h($greetWord) ?></span> <span id="greetEmoji">👋</span><br>
                        <span class="user-name"><?= h($loggedInUser) ?>!</span>
                    </h3>
                    <p class="wb-sub">Stay updated with your academic journey.</p>
                </div>

                <div class="wb-tiles">
                    <div class="wb-tile wb-theme">
                        <small>Theme</small>
                        <button type="button" class="wb-switch" id="wbThemeSwitch" role="switch" aria-checked="false" aria-label="Switch dark mode"></button>
                        <span id="wbThemeLabel">Light</span>
                    </div>

                    <div class="wb-tile wb-timer">
                        <small>Time left</small>
                        <strong id="timeLeft">--:--:--</strong>
                        <span class="wb-sub-s">until tomorrow</span>
                        <div class="wb-bar"><span id="timeBar"></span></div>
                    </div>

                    <div class="wb-tile wb-photo">
                        <img src="<?= h(ev_avatar_url($user)) ?>" alt="Profile" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($firstLetter) ?>&background=ffffff&color=00a859&size=128&bold=true'">
                        <span><?= h($loggedInUser) ?></span>
                    </div>
                </div>
            </div>

            <div class="card grades-card" data-open="gpaModal" title="Open GPA calculator">
                <div class="dash-card-head"><h3><i class="fas fa-graduation-cap"></i> Your Process</h3></div>
                <div>
                    <div class="gpa-big" id="gpaValue">0.00</div>
                    <div class="dash-muted">Current GPA · <span id="gpaCredits">0</span> credits</div>
                </div>
                <div class="gpa-bar"><span id="gpaBar"></span></div>
                <span class="dash-btn"><i class="fas fa-calculator"></i> Calculator</span>
            </div>

            <!-- Row 2 -->
            <div class="card schedule-study" onclick="window.location.href='groups.php'" title="Open groups and study sessions">
                <div class="dash-card-head">
                    <h3><i class="far fa-calendar-alt"></i> Schedule Study Session</h3>
                </div>

                <?php if ($nextSessions):
                    $ns    = $nextSessions[0];
                    $nsTs  = (int) strtotime($ns['startsAt']); ?>
                    <div class="ss-next" style="--ss-c: <?= h($ns['group_color'] ?: '#00a859') ?>">
                        <div class="ss-date"><strong><?= date('d', $nsTs) ?></strong><span><?= date('M', $nsTs) ?></span></div>
                        <div class="ss-info">
                            <strong><?= h($ns['title']) ?></strong>
                            <small><i class="far fa-clock"></i> <?= date('D, g:i A', $nsTs) ?> · <?= h($ns['group_name']) ?></small>
                        </div>
                    </div>
                    <?php if (count($nextSessions) > 1): ?>
                        <div class="ss-more"><?= count($nextSessions) - 1 ?> more session(s) coming up</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="dash-empty"><i class="far fa-calendar-plus"></i>No upcoming sessions.<br>Schedule one from your groups.</div>
                <?php endif; ?>

                <span class="dash-btn"><i class="fas fa-plus"></i> Schedule session</span>
            </div>

            <div class="card schedule-forum" onclick="window.location.href='discussion-qa.php'" title="Open Discussion / Q&amp;A">
                <div class="dash-card-head">
                    <h3><i class="far fa-comments"></i> Join Discussion Forum</h3>
                    <span class="dash-link">Q&amp;A →</span>
                </div>

                <div class="fd-stats">
                    <div class="fd-stat"><strong id="fdDisc">–</strong><span>Discussions</span></div>
                    <div class="fd-stat"><strong id="fdQa">–</strong><span>Questions</span></div>
                    <div class="fd-stat"><strong id="fdOpen">–</strong><span>Open</span></div>
                </div>

                <div class="fd-list" id="fdList"><span class="dash-muted">Loading...</span></div>

                <span class="dash-btn purple"><i class="fas fa-arrow-right"></i> Join the conversation</span>
            </div>

            <div class="card shared-resources" data-open="resModal" title="See everything you shared">
                <div class="dash-card-head">
                    <h3><i class="fas fa-file-alt"></i> Your Shared Resource</h3>
                </div>
                <div class="sr-row sr-approved"><span>Approved</span><strong id="srApproved"><?= (int) $myCounts['approved'] ?></strong></div>
                <div class="sr-row sr-pending"><span>Pending</span><strong id="srPending"><?= (int) $myCounts['pending'] ?></strong></div>
                <div class="sr-row sr-rejected"><span>Rejected</span><strong id="srRejected"><?= (int) $myCounts['rejected'] ?></strong></div>
                <span class="dash-muted">Tap to see all</span>
            </div>

            <div class="card student-profile">
                <div class="dash-card-head">
                    <h3><i class="far fa-user"></i> Student Profile</h3>
                    <a class="dash-link" href="profile.php">Edit</a>
                </div>
                <div class="sp-wrap">
                    <div class="sp-avatar">
                        <img src="<?= h(ev_avatar_url($user)) ?>" alt="Profile photo" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=<?= urlencode($firstLetter) ?>&background=00a859&color=fff&size=128&bold=true'">
                    </div>
                    <div class="sp-info">
                        <strong><?= h($profileName) ?></strong>
                        <small><?= $profileEmail !== '' ? h($profileEmail) : 'No email on file' ?></small>
                        <div class="sp-chips">
                            <span class="sp-chip"><i class="fas fa-user-graduate"></i> Student</span>
                            <?php if ($joinedTs): ?>
                                <span class="sp-chip blue">Joined <?= date('M Y', $joinedTs) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="card leaderboard">
                <div class="dash-card-head">
                    <h3><i class="fas fa-trophy" style="color:#f6a609"></i> Leader board</h3>
                    <span class="dash-muted">Top students by points</span>
                </div>

                <div class="lb-podium" id="lbPodium">
                <?php
                // Visual order: 2nd, 1st, 3rd
                foreach ([1, 0, 2] as $idx):
                    $p     = $lbTop[$idx] ?? null;
                    $place = $idx + 1;
                    $medal = ['🥇', '🥈', '🥉'][$idx];
                ?>
                    <div class="lb-place lb-<?= $place ?>">
                        <?php if ($p): ?>
                            <span class="lb-medal"><?= $medal ?></span>
                            <span class="lb-name" title="<?= h($p['name']) ?>"><?= h($p['name']) ?><?= $p['key'] === (string) $myGroupKey ? ' (You)' : '' ?></span>
                            <span class="lb-pts"><?= (int) $p['points'] ?> pts</span>
                        <?php else: ?>
                            <span class="lb-medal">–</span>
                            <span class="lb-name lb-empty">No one yet</span>
                            <span class="lb-pts">&nbsp;</span>
                        <?php endif; ?>
                        <div class="lb-block"><?= $place ?></div>
                    </div>
                <?php endforeach; ?>
                </div>

                <div class="lb-you" id="lbYou">
                    <?php if ($myRank > 0): ?>
                        You are <strong>#<?= $myRank ?></strong> with <?= (int) $myScore ?> points
                    <?php else: ?>
                        Get a PDF approved to earn points and appear here
                    <?php endif; ?>
                </div>
            </div>

            <div class="card your-score">
                <div class="dash-card-head">
                    <h3><i class="fas fa-chart-column"></i> Your score</h3>
                    <span class="dash-muted">Last 6 months</span>
                </div>
                <div class="ys-total" id="ysTotal"><?= (int) $myScore ?> <small>points</small></div>
                <div class="ys-chart">
                    <?php foreach ($chartMonths as $cm):
                        $barPx = $cm['points'] > 0 ? max(6, (int) round($cm['points'] / $chartMax * 96)) : 4; ?>
                        <div class="ys-col">
                            <em><?= (int) $cm['points'] ?></em>
                            <div class="ys-bar" style="height: <?= $barPx ?>px"></div>
                            <small><?= h($cm['label']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card upcoming-event">
                <div class="dash-card-head">
                    <h3><i class="far fa-calendar-check"></i> Upcoming event</h3>
                    <a class="dash-link" href="events.php">View all</a>
                </div>
                <div class="ue-list">
                <?php if (!empty($upcomingEvents)): ?>
                    <?php foreach (array_slice($upcomingEvents, 0, 4) as $evt):
                        $evTs = (int) strtotime($evt['event_date']); ?>
                        <div class="ue-item">
                            <div class="ue-date"><strong><?= date('d', $evTs) ?></strong><span><?= date('M', $evTs) ?></span></div>
                            <div class="ue-body">
                                <div class="ue-title"><?= h($evt['title']) ?></div>
                                <div class="ue-sub"><?= date('l', $evTs) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dash-empty"><i class="far fa-calendar"></i>No upcoming events</div>
                <?php endif; ?>
                </div>
            </div>

            <!-- Row 4 -->
            <div class="card academic-calendar">
                <div class="dash-card-head">
                    <h3><i class="far fa-calendar-alt"></i> Academic Calendar</h3>
                    <span class="dash-link" id="calMonth"></span>
                </div>
                <div class="cal-grid" id="calGrid"></div>
            </div>

            <div class="card quick-access">
                <a class="qa-tile" href="groups.php">
                    <span class="qa-ico"><i class="fas fa-users"></i></span>
                    <strong>Groups</strong>
                    <small>Join or create</small>
                </a>
                <a class="qa-tile" href="study-material.php">
                    <span class="qa-ico"><i class="fas fa-download"></i></span>
                    <strong>Download</strong>
                    <small>Study material</small>
                </a>
                <a class="qa-tile" href="notice-board_forst.php">
                    <span class="qa-ico"><i class="fas fa-clipboard-list"></i></span>
                    <strong>Noticeboard</strong>
                    <small>Official notices</small>
                </a>
            </div>

            <div class="card announcements">
                <div class="dash-card-head">
                    <h3><i class="fas fa-bullhorn" style="color:var(--nsbm-green)"></i> Announcements</h3>
                    <a class="dash-link" href="notice-board_forst.php">View all</a>
                </div>
                <div class="an-list">
                <?php if (!empty($announcementsList)): ?>
                    <?php foreach ($announcementsList as $ann):
                        $anTs = !empty($ann['publish_date']) ? (int) strtotime($ann['publish_date']) : 0; ?>
                        <a class="an-item" href="notice-board_forst.php">
                            <span class="an-ico"><i class="fas fa-bullhorn"></i></span>
                            <span class="an-body">
                                <span class="an-title"><?= h($ann['title']) ?></span>
                                <span class="an-meta">
                                    <span class="an-cat"><?= h($ann['category']) ?></span>
                                    <?= $anTs ? date('d M Y', $anTs) : '' ?>
                                </span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="dash-empty"><i class="fas fa-bullhorn"></i>No announcements</div>
                <?php endif; ?>
                </div>
            </div>

        </div>

        <footer>
            <p>&copy; 2026 EduVault — Student Community Platform | NSBM Green University</p>
        </footer>
    </main>

    <!-- ================= POP-UPS ================= -->
    <div class="dash-overlay" id="gpaModal" hidden>
        <div class="dash-modal" role="dialog" aria-modal="true" aria-labelledby="gpaTitle">
            <button type="button" class="dash-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button>
            <h2 id="gpaTitle">GPA Calculator</h2>
            <p class="dm-sub">Add each course with its credits and grade. Saved in this browser only.</p>

            <table class="gpa-table">
                <thead><tr><th>Course</th><th>Credits</th><th>Grade</th><th></th></tr></thead>
                <tbody id="gpaRows"></tbody>
            </table>

            <div class="dash-modal-foot">
                <button type="button" class="dash-btn ghost" id="gpaAdd"><i class="fas fa-plus"></i> Add course</button>
                <button type="button" class="dash-btn ghost" id="gpaClear">Clear all</button>
            </div>

            <div class="gpa-result">
                <div><span class="dash-muted">Your GPA</span><strong id="gpaResult">0.00</strong></div>
                <div><span class="dash-muted">Total credits</span><strong class="gpa-small" id="gpaTotalCredits">0</strong></div>
            </div>
        </div>
    </div>

    <div class="dash-overlay" id="resModal" hidden>
        <div class="dash-modal" role="dialog" aria-modal="true" aria-labelledby="resTitle">
            <button type="button" class="dash-x" data-close aria-label="Close"><i class="fas fa-xmark"></i></button>
            <h2 id="resTitle">Your shared resources</h2>
            <p class="dm-sub">Everything you uploaded. Approved files are visible to all students.</p>

            <div class="sr-list">
            <?php if (!$myMaterials): ?>
                <div class="dash-empty"><i class="fas fa-folder-open"></i>You have not uploaded anything yet.</div>
            <?php else: ?>
                <?php foreach ($myMaterials as $mm):
                    $mst = strtolower((string) ($mm['status'] ?? 'pending'));
                    if (!in_array($mst, ['approved', 'pending', 'rejected'], true)) {
                        $mst = 'pending';
                    }
                    $mts = (int) ($mm['created'] ?? 0); ?>
                    <div class="sr-item">
                        <div class="sr-t">
                            <strong><?= h($mm['title'] ?? '') ?></strong>
                            <small><?= h($mm['module'] ?? '') ?> · <?= h($mm['batch'] ?? 'General') ?><?= $mts ? ' · ' . date('d M Y', $mts) : '' ?></small>
                        </div>
                        <span class="sr-tag <?= $mst ?>"><?= ucfirst($mst) ?></span>
                        <a class="dash-link" href="material_file.php?id=<?= urlencode((string) $mm['id']) ?>" target="_blank" rel="noopener">View</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>

            <div class="dash-modal-foot">
                <a class="dash-btn" href="youruploads.php"><i class="fas fa-cloud-arrow-up"></i> Upload new PDF</a>
            </div>
        </div>
    </div>

    <script>
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

// Auto-refresh Leaderboard & Points every 5 seconds
(function () {
    function esc(str) {
        return String(str).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    async function refreshLeaderboard() {
        try {
            const res = await fetch('index.php?ajax=leaderboard&_=' + Date.now(), { cache: 'no-store' });
            const data = await res.json();
            if (!data || !data.ok) return;

            const podium = document.getElementById('lbPodium');
            const lbYou  = document.getElementById('lbYou');
            const ysTot  = document.getElementById('ysTotal');

            if (podium && Array.isArray(data.top)) {
                const medals = ['🥇', '🥈', '🥉'];
                podium.innerHTML = [1, 0, 2].map(idx => {
                    const p = data.top[idx] || null;
                    const place = idx + 1;
                    if (p) {
                        const youTag = (p.key === data.myGroupKey) ? ' (You)' : '';
                        return `<div class="lb-place lb-${place}">
                            <span class="lb-medal">${medals[idx]}</span>
                            <span class="lb-name" title="${esc(p.name)}">${esc(p.name)}${youTag}</span>
                            <span class="lb-pts">${Number(p.points)} pts</span>
                            <div class="lb-block">${place}</div>
                        </div>`;
                    }
                    return `<div class="lb-place lb-${place}">
                        <span class="lb-medal">–</span>
                        <span class="lb-name lb-empty">No one yet</span>
                        <span class="lb-pts">&nbsp;</span>
                        <div class="lb-block">${place}</div>
                    </div>`;
                }).join('');
            }

            if (lbYou) {
                lbYou.innerHTML = data.myRank > 0
                    ? `You are <strong>#${Number(data.myRank)}</strong> with ${Number(data.myScore)} points`
                    : `Get a PDF approved to earn points and appear here`;
            }

            if (ysTot) {
                ysTot.innerHTML = `${Number(data.myScore)} <small>points</small>`;
            }

            if (data.counts) {
                const appEl = document.getElementById('srApproved');
                const penEl = document.getElementById('srPending');
                const rejEl = document.getElementById('srRejected');
                if (appEl) appEl.textContent = Number(data.counts.approved || 0);
                if (penEl) penEl.textContent = Number(data.counts.pending || 0);
                if (rejEl) rejEl.textContent = Number(data.counts.rejected || 0);
            }
        } catch (e) {}
    }

    setInterval(refreshLeaderboard, 5000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refreshLeaderboard();
    });
})();
    </script>
    <script src="dashboard.js"></script>
<?php if (isset($user) && $user): ?>
<style>
#ev-chat-widget {
    position: fixed;
    right: 30px;
    bottom: 30px;
    width: 60px;
    height: 60px;
    z-index: 9999;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
#ev-chat-bubble {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00B756 0%, #007940 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
    touch-action: none;
    box-shadow: 0 6px 18px rgba(0, 183, 86, 0.45);
    transition: transform 0.2s;
}
#ev-chat-bubble:hover { transform: scale(1.08); }
#ev-chat-bubble.dragging { cursor: grabbing; transform: scale(1.05); transition: none; }

#ev-chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: min(350px, calc(100vw - 16px));
    height: min(450px, 65vh);
    background: #fff;
    border: 1px solid #e0e5f2;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: column;
    overflow: hidden;
}
#ev-chat-window.show { display: flex; animation: evSlideUp 0.3s ease; }
@keyframes evSlideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: none; }
}
#ev-chat-widget .ev-chat-header {
    background: #0b1d3a;
    color: #fff;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    font-size: 14px;
}
#ev-chat-widget .ev-chat-close {
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 16px;
    padding: 4px 6px;
}
#ev-chat-widget .ev-chat-messages {
    flex: 1;
    min-height: 0;
    padding: 14px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f4f7fe;
}
#ev-chat-widget .ev-msg {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 15px;
    font-size: 13px;
    line-height: 1.45;
    overflow-wrap: anywhere;
}
#ev-chat-widget .ev-msg.ai {
    background: #fff;
    color: #2b3674;
    border: 1px solid #e0e5f2;
    align-self: flex-start;
    border-bottom-left-radius: 2px;
}
#ev-chat-widget .ev-msg.user {
    background: #00B756;
    color: #fff;
    align-self: flex-end;
    border-bottom-right-radius: 2px;
}
#ev-chat-widget .typing-indicator {
    display: none;
    align-self: flex-start;
    font-style: italic;
    font-size: 12px;
    color: #a3aed1;
}
#ev-chat-widget .typing-indicator.show { display: block; }
#ev-chat-widget .ev-chat-input-area {
    display: flex;
    gap: 8px;
    padding: 10px;
    background: #fff;
    border-top: 1px solid #e0e5f2;
}
#ev-chat-widget .ev-chat-input {
    flex: 1;
    min-width: 0;
    padding: 10px 14px;
    border: 1px solid #e0e5f2;
    border-radius: 20px;
    outline: none;
    background: #f4f7fe;
    color: #2b3674;
    font-size: 13px;
}
#ev-chat-widget .ev-send-btn {
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border: none;
    border-radius: 50%;
    background: #00B756;
    color: #fff;
    cursor: pointer;
}
#ev-chat-widget .ev-send-btn:disabled { opacity: 0.6; cursor: default; }
</style>

<div id="ev-chat-widget">
    <div id="ev-chat-window">
        <div class="ev-chat-header">
            <span><i class="fa-solid fa-robot"></i> EduVault AI</span>
            <button type="button" id="ev-chat-close" class="ev-chat-close" aria-label="Close chat"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="ev-chat-messages" class="ev-chat-messages">
            <div class="ev-msg ai">Hi <?= h(first_name($user['full_name'])) ?>! 👋 I'm EduVault AI. How can I help you today?</div>
            <div id="ev-typing" class="typing-indicator">EduVault AI is typing...</div>
        </div>
        <div class="ev-chat-input-area">
            <input type="text" id="ev-chat-input" class="ev-chat-input" placeholder="Ask me anything..." autocomplete="off">
            <button type="button" id="ev-send-btn" class="ev-send-btn" aria-label="Send"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
    <div id="ev-chat-bubble" role="button" tabindex="0" aria-label="Open EduVault AI chat" title="Drag to move, click to chat">
        <i class="fa-solid fa-message"></i>
    </div>
</div>

<script>
(function () {
    'use strict';

    var widget   = document.getElementById('ev-chat-widget');
    var bubble   = document.getElementById('ev-chat-bubble');
    var win      = document.getElementById('ev-chat-window');
    var closeBtn = document.getElementById('ev-chat-close');
    var msgs     = document.getElementById('ev-chat-messages');
    var typing   = document.getElementById('ev-typing');
    var input    = document.getElementById('ev-chat-input');
    var sendBtn  = document.getElementById('ev-send-btn');

    var POS_KEY = 'ev_chat_pos';
    var busy = false;
    var dragState = null;
    var dragMoved = false;

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function formatText(s) {
        return escapeHtml(s)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
    }

    function addMessage(text, who) {
        var div = document.createElement('div');
        div.className = 'ev-msg ' + who;
        div.innerHTML = formatText(text);
        msgs.insertBefore(div, typing);
        msgs.scrollTop = msgs.scrollHeight;
    }

    function isOpen() {
        return win.classList.contains('show');
    }

    function placeWindow() {
        var r = widget.getBoundingClientRect();
        var gap = 10, margin = 8;
        var winW = win.offsetWidth;
        var winH = win.offsetHeight;

        var left = Math.min(Math.max(margin, r.right - winW), window.innerWidth - winW - margin);
        var top = r.top - winH - gap;
        if (top < margin) top = r.bottom + gap;
        top = Math.min(Math.max(margin, top), window.innerHeight - winH - margin);

        win.style.left = (left - r.left) + 'px';
        win.style.top = (top - r.top) + 'px';
        win.style.right = 'auto';
        win.style.bottom = 'auto';
    }

    function openChat(open) {
        win.classList.toggle('show', open);
        if (open) {
            placeWindow();
            msgs.scrollTop = msgs.scrollHeight;
            input.focus();
        }
    }

    function moveWidget(x, y) {
        var maxX = Math.max(0, window.innerWidth - widget.offsetWidth);
        var maxY = Math.max(0, window.innerHeight - widget.offsetHeight);
        widget.style.left = Math.min(Math.max(0, x), maxX) + 'px';
        widget.style.top = Math.min(Math.max(0, y), maxY) + 'px';
        widget.style.right = 'auto';
        widget.style.bottom = 'auto';
    }

    function savePosition() {
        try {
            var r = widget.getBoundingClientRect();
            localStorage.setItem(POS_KEY, JSON.stringify({ x: r.left, y: r.top }));
        } catch (e) {}
    }

    function restorePosition() {
        try {
            var saved = JSON.parse(localStorage.getItem(POS_KEY) || 'null');
            if (saved && typeof saved.x === 'number' && typeof saved.y === 'number') {
                moveWidget(saved.x, saved.y);
            }
        } catch (e) {}
    }

    bubble.addEventListener('pointerdown', function (e) {
        if (e.button > 0) return;
        var r = widget.getBoundingClientRect();
        dragState = { startX: e.clientX, startY: e.clientY, origX: r.left, origY: r.top };
        dragMoved = false;
        bubble.setPointerCapture(e.pointerId);
    });

    bubble.addEventListener('pointermove', function (e) {
        if (!dragState) return;
        var dx = e.clientX - dragState.startX;
        var dy = e.clientY - dragState.startY;
        if (!dragMoved && Math.abs(dx) + Math.abs(dy) < 5) return;
        dragMoved = true;
        bubble.classList.add('dragging');
        moveWidget(dragState.origX + dx, dragState.origY + dy);
    });

    bubble.addEventListener('pointerup', function () {
        if (!dragState) return;
        var moved = dragMoved;
        dragState = null;
        dragMoved = false;
        bubble.classList.remove('dragging');
        if (moved) {
            savePosition();
            if (isOpen()) placeWindow();
        } else {
            openChat(!isOpen());
        }
    });

    bubble.addEventListener('pointercancel', function () {
        dragState = null;
        dragMoved = false;
        bubble.classList.remove('dragging');
    });

    bubble.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            openChat(!isOpen());
        }
    });

    closeBtn.addEventListener('click', function () {
        openChat(false);
    });

    async function sendMessage() {
        var text = input.value.trim();
        if (!text || busy) return;

        busy = true;
        sendBtn.disabled = true;
        input.value = '';
        addMessage(text, 'user');
        typing.classList.add('show');
        msgs.scrollTop = msgs.scrollHeight;

        var reply;
        try {
            var res = await fetch('eduvault_ai.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            var raw = await res.text();
            var data = null;
            try { data = JSON.parse(raw); } catch (parseErr) { data = null; }

            if (data && (data.reply || data.error)) {
                reply = data.reply || data.error;
            } else if (!res.ok) {
                reply = 'Server error (HTTP ' + res.status + '). Please try again.';
            } else {
                reply = 'Invalid response from server. Please try again.';
            }

            if (!data || !res.ok) {
                console.error('chatbot_api.php HTTP ' + res.status + ' response:', raw.slice(0, 500));
            }
        } catch (err) {
            console.error('chatbot request failed:', err);
            reply = 'Could not reach the server (' + err.message + ').';
        }

        typing.classList.remove('show');
        addMessage(reply, 'ai');
        busy = false;
        sendBtn.disabled = false;
        input.focus();
    }

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    sendBtn.addEventListener('click', sendMessage);

    window.addEventListener('resize', function () {
        var r = widget.getBoundingClientRect();
        moveWidget(r.left, r.top);
        if (isOpen()) placeWindow();
    });

    restorePosition();
})();
</script>
<?php endif; ?>
<script src='site-search.js'></script></body>
</html>
