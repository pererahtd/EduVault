<?php
/**
 * EduVault study sessions API.
 * Included by groups.php and group_discussion.php:
 *     require_once __DIR__ . '/sessions_api.php';
 *     sessions_handle($conn, $me);
 */
date_default_timezone_set('Asia/Colombo'); // keep the same as your MySQL server time zone

function sessions_json(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function sessions_group_for_member(mysqli $conn, int $groupId, int $userId): ?array
{
    $stmt = $conn->prepare(
        'SELECT g.id, g.name, g.ownerId
         FROM groups g
         LEFT JOIN group_members gm ON gm.groupId = g.id AND gm.userId = ?
         WHERE g.id = ? AND (gm.userId IS NOT NULL OR g.ownerId = ?) LIMIT 1'
    );
    $stmt->bind_param('iii', $userId, $groupId, $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: null;
}

function sessions_post_chat(mysqli $conn, int $groupId, int $userId, string $text): void
{
    $stmt = $conn->prepare('INSERT INTO messages (group_id, user_id, message, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iis', $groupId, $userId, $text);
    $stmt->execute();
}

function sessions_len(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

function sessions_handle(mysqli $conn, int $me): void
{
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    if (!in_array($action, ['get_sessions', 'get_session_members', 'create_session', 'cancel_session', 'rsvp_session'], true)) {
        return; // not a session request, let the page handle it
    }

    try {
        switch ($action) {
            case 'get_session_members':
                $groupId = (int) ($_GET['groupId'] ?? 0);
                if ($groupId < 1 || !sessions_group_for_member($conn, $groupId, $me)) {
                    sessions_json(['ok' => false, 'error' => 'Group not found or you are not a member.'], 404);
                }
                $stmt = $conn->prepare(
                    'SELECT DISTINCT u.id, u.name FROM users u
                     LEFT JOIN group_members gm ON gm.userId = u.id AND gm.groupId = ?
                     INNER JOIN groups g ON g.id = ?
                     WHERE (gm.userId IS NOT NULL OR u.id = g.ownerId) AND u.id <> ?
                     ORDER BY u.name'
                );
                $stmt->bind_param('iii', $groupId, $groupId, $me);
                $stmt->execute();
                sessions_json(['ok' => true, 'members' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);

            case 'get_sessions':
                $groupId = (int) ($_GET['groupId'] ?? 0);
                $cutoff = date('Y-m-d H:i:s', strtotime('-14 days'));
                // private sessions: only the creator and invited members can see them
                $visible = "(s.visibility = 'group' OR s.createdBy = ? OR EXISTS (SELECT 1 FROM session_invites i WHERE i.sessionId = s.id AND i.userId = ?))";
                $sql = 'SELECT s.id, s.groupId, s.seriesId, s.title, s.description, s.location,
                               s.startsAt, s.endsAt, s.status, s.createdBy, s.visibility,
                               g.name AS group_name, g.color AS group_color, g.ownerId AS group_owner,
                               u.name AS creator_name,
                               (SELECT COUNT(*) FROM study_sessions x WHERE x.seriesId = s.seriesId) AS series_count
                        FROM study_sessions s
                        INNER JOIN groups g ON g.id = s.groupId
                        LEFT JOIN users u ON u.id = s.createdBy
                        WHERE s.endsAt >= ? AND ';
                if ($groupId > 0) {
                    if (!sessions_group_for_member($conn, $groupId, $me)) {
                        sessions_json(['ok' => false, 'error' => 'Group not found or you are not a member.'], 404);
                    }
                    $sql .= "s.groupId = ? AND $visible ORDER BY s.startsAt ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('siii', $cutoff, $groupId, $me, $me);
                } else {
                    $sql .= "(s.groupId IN (SELECT groupId FROM group_members WHERE userId = ?) OR g.ownerId = ?)
                             AND $visible ORDER BY s.startsAt ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param('siiii', $cutoff, $me, $me, $me, $me);
                }
                $stmt->execute();
                $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                $rsvps = [];
                if ($rows) {
                    $in = implode(',', array_map(fn($r) => (int) $r['id'], $rows));
                    $result = $conn->query(
                        "SELECT r.sessionId, r.userId, r.status, u.name
                         FROM session_rsvps r INNER JOIN users u ON u.id = r.userId
                         WHERE r.sessionId IN ($in) ORDER BY u.name"
                    );
                    foreach ($result->fetch_all(MYSQLI_ASSOC) as $r) {
                        $rsvps[(int) $r['sessionId']][] = $r;
                    }
                }

                $invites = [];
                if ($rows) {
                    $in = implode(',', array_map(fn($r) => (int) $r['id'], $rows));
                    $result = $conn->query(
                        "SELECT i.sessionId, u.name FROM session_invites i
                         INNER JOIN users u ON u.id = i.userId WHERE i.sessionId IN ($in) ORDER BY u.name"
                    );
                    foreach ($result->fetch_all(MYSQLI_ASSOC) as $r) {
                        $invites[(int) $r['sessionId']][] = $r['name'];
                    }
                }

                $sessions = [];
                foreach ($rows as $row) {
                    $sid = (int) $row['id'];
                    $going = [];
                    $maybe = 0;
                    $mine = null;
                    foreach ($rsvps[$sid] ?? [] as $r) {
                        if ($r['status'] === 'going') $going[] = $r['name'];
                        if ($r['status'] === 'maybe') $maybe++;
                        if ((int) $r['userId'] === $me) $mine = $r['status'];
                    }
                    $sessions[] = [
                        'id' => $sid,
                        'groupId' => (int) $row['groupId'],
                        'seriesId' => $row['seriesId'] !== null ? (int) $row['seriesId'] : null,
                        'series_count' => (int) $row['series_count'],
                        'title' => $row['title'],
                        'description' => $row['description'],
                        'location' => $row['location'],
                        'startsAt' => $row['startsAt'],
                        'endsAt' => $row['endsAt'],
                        'status' => $row['status'],
                        'group_name' => $row['group_name'],
                        'group_color' => $row['group_color'],
                        'creator_name' => $row['creator_name'],
                        'visibility' => $row['visibility'],
                        'invited_names' => $invites[$sid] ?? [],
                        'can_manage' => ((int) $row['createdBy'] === $me), // only the creator can cancel
                        'going_count' => count($going),
                        'maybe_count' => $maybe,
                        'going_names' => array_slice($going, 0, 4),
                        'my_rsvp' => $mine,
                    ];
                }
                sessions_json(['ok' => true, 'sessions' => $sessions]);

            case 'create_session':
                $groupId = (int) ($_POST['groupId'] ?? 0);
                if ($groupId < 1 || !sessions_group_for_member($conn, $groupId, $me)) {
                    sessions_json(['ok' => false, 'error' => 'Group not found or you are not a member.'], 404);
                }
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $location = trim($_POST['location'] ?? '');
                $duration = (int) ($_POST['duration'] ?? 60);
                $weeks = max(1, min(12, (int) ($_POST['weeks'] ?? 1)));
                $start = DateTime::createFromFormat('Y-m-d\TH:i', trim($_POST['start'] ?? ''));
                $visibility = ($_POST['visibility'] ?? 'group') === 'invited' ? 'invited' : 'group';
                $invitees = [];
                if ($visibility === 'invited') {
                    $ids = array_unique(array_filter(array_map('intval', explode(',', (string) ($_POST['invitees'] ?? '')))));
                    foreach ($ids as $uid) {
                        if ($uid !== $me && sessions_group_for_member($conn, $groupId, $uid)) {
                            $invitees[] = $uid;
                        }
                    }
                    if (!$invitees) {
                        sessions_json(['ok' => false, 'error' => 'Select at least one group member to invite.'], 422);
                    }
                }

                if (sessions_len($title) < 1 || sessions_len($title) > 120) {
                    sessions_json(['ok' => false, 'error' => 'Title must be 1 to 120 characters.'], 422);
                }
                if (sessions_len($description) > 1000 || sessions_len($location) > 255) {
                    sessions_json(['ok' => false, 'error' => 'Description or location is too long.'], 422);
                }
                if (!$start) {
                    sessions_json(['ok' => false, 'error' => 'Please pick a valid date and time.'], 422);
                }
                if ($start < new DateTime('now')) {
                    sessions_json(['ok' => false, 'error' => 'Please pick a time in the future.'], 422);
                }
                if ($duration < 15 || $duration > 720) {
                    sessions_json(['ok' => false, 'error' => 'Duration must be between 15 minutes and 12 hours.'], 422);
                }

                $conn->begin_transaction();
                $insert = $conn->prepare(
                    'INSERT INTO study_sessions (groupId, seriesId, createdBy, title, description, location, startsAt, endsAt, visibility)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $rsvp = $conn->prepare("INSERT INTO session_rsvps (sessionId, userId, status) VALUES (?, ?, 'going')");
                $inviteStmt = $conn->prepare('INSERT IGNORE INTO session_invites (sessionId, userId) VALUES (?, ?)');
                $seriesId = null;
                $firstId = 0;
                for ($i = 0; $i < $weeks; $i++) {
                    $a = (clone $start)->modify("+{$i} week");
                    $b = (clone $a)->modify("+{$duration} minutes");
                    $startsAt = $a->format('Y-m-d H:i:s');
                    $endsAt = $b->format('Y-m-d H:i:s');
                    $insert->bind_param('iiissssss', $groupId, $seriesId, $me, $title, $description, $location, $startsAt, $endsAt, $visibility);
                    $insert->execute();
                    $sid = (int) $conn->insert_id;
                    if ($i === 0) {
                        $firstId = $sid;
                        if ($weeks > 1) {
                            $seriesId = $sid;
                            $set = $conn->prepare('UPDATE study_sessions SET seriesId = ? WHERE id = ?');
                            $set->bind_param('ii', $seriesId, $sid);
                            $set->execute();
                        }
                    }
                    $rsvp->bind_param('ii', $sid, $me);
                    $rsvp->execute();
                    foreach ($invitees as $uid) {
                        $inviteStmt->bind_param('ii', $sid, $uid);
                        $inviteStmt->execute();
                    }
                }
                $text = '📅 New study session: ' . $title . ' — ' . $start->format('D j M, g:i A')
                      . ($weeks > 1 ? " (repeats weekly for {$weeks} weeks)" : '');
                if ($visibility === 'group') { // private sessions are not announced in the group chat
                    sessions_post_chat($conn, $groupId, $me, $text);
                }
                $conn->commit();
                sessions_json(['ok' => true, 'sessionId' => $firstId]);

            case 'cancel_session':
                $sid = (int) ($_POST['sessionId'] ?? 0);
                $scope = ($_POST['scope'] ?? 'one') === 'future' ? 'future' : 'one';
                $stmt = $conn->prepare(
                    'SELECT s.id, s.groupId, s.seriesId, s.title, s.startsAt, s.createdBy, s.status, s.visibility, g.ownerId
                     FROM study_sessions s INNER JOIN groups g ON g.id = s.groupId WHERE s.id = ?'
                );
                $stmt->bind_param('i', $sid);
                $stmt->execute();
                $s = $stmt->get_result()->fetch_assoc();
                if (!$s) {
                    sessions_json(['ok' => false, 'error' => 'Session not found.'], 404);
                }
                if ((int) $s['createdBy'] !== $me) {
                    sessions_json(['ok' => false, 'error' => 'Only the person who created this session can cancel it.'], 403);
                }
                if ($scope === 'future' && $s['seriesId'] !== null) {
                    $up = $conn->prepare("UPDATE study_sessions SET status = 'cancelled' WHERE seriesId = ? AND startsAt >= ?");
                    $up->bind_param('is', $s['seriesId'], $s['startsAt']);
                } else {
                    $scope = 'one';
                    $up = $conn->prepare("UPDATE study_sessions SET status = 'cancelled' WHERE id = ?");
                    $up->bind_param('i', $sid);
                }
                $up->execute();
                $when = (new DateTime($s['startsAt']))->format('D j M, g:i A');
                if ($s['visibility'] === 'group') sessions_post_chat(
                    $conn, (int) $s['groupId'], $me,
                    '❌ Study session cancelled: ' . $s['title'] . ' (' . $when . ')' . ($scope === 'future' ? ' and all later weekly sessions' : '')
                );
                sessions_json(['ok' => true]);

            case 'rsvp_session':
                $sid = (int) ($_POST['sessionId'] ?? 0);
                $status = $_POST['status'] ?? '';
                if (!in_array($status, ['going', 'maybe', 'no'], true)) {
                    sessions_json(['ok' => false, 'error' => 'Invalid response.'], 422);
                }
                $stmt = $conn->prepare('SELECT groupId, status, visibility, createdBy FROM study_sessions WHERE id = ?');
                $stmt->bind_param('i', $sid);
                $stmt->execute();
                $s = $stmt->get_result()->fetch_assoc();
                if (!$s || !sessions_group_for_member($conn, (int) $s['groupId'], $me)) {
                    sessions_json(['ok' => false, 'error' => 'Session not found.'], 404);
                }
                if ($s['visibility'] === 'invited' && (int) $s['createdBy'] !== $me) {
                    $chk = $conn->prepare('SELECT 1 FROM session_invites WHERE sessionId = ? AND userId = ?');
                    $chk->bind_param('ii', $sid, $me);
                    $chk->execute();
                    if (!$chk->get_result()->fetch_row()) {
                        sessions_json(['ok' => false, 'error' => 'Session not found.'], 404);
                    }
                }
                if ($s['status'] === 'cancelled') {
                    sessions_json(['ok' => false, 'error' => 'This session was cancelled.'], 409);
                }
                $stmt = $conn->prepare(
                    'INSERT INTO session_rsvps (sessionId, userId, status) VALUES (?, ?, ?)
                     ON DUPLICATE KEY UPDATE status = VALUES(status)'
                );
                $stmt->bind_param('iis', $sid, $me, $status);
                $stmt->execute();
                sessions_json(['ok' => true]);
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Study sessions request failed: ' . $e->getMessage());
        try { $conn->rollback(); } catch (mysqli_sql_exception $ignored) {}
        sessions_json(['ok' => false, 'error' => 'The session request could not be completed.'], 500);
    }
}