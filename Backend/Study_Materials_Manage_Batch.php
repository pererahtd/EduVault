<?php 
error_reporting(E_ALL);


// Shared settings: display_errors (on for localhost only) and DB constants
require_once __DIR__ . '/../config.php';

// Admin login check
require __DIR__ . '/_auth.php';

require_once __DIR__ . '/../materials_store.php';
require_once __DIR__ . '/../batches_store.php';

$materials = mat_load();

// ---------- Batch actions (create / delete) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batch_action'])) {
    $msg = '';
    if ($_POST['batch_action'] === 'create') {
        $result = batch_add((string)($_POST['batch'] ?? ''));
        $msg = [
            'ok'      => 'Batch created successfully.',
            'exists'  => 'This batch already exists.',
            'empty'   => 'Batch name is required.',
            'toolong' => 'Batch name is too long (max 75).',
            'error'   => 'Unable to save batch.',
        ][$result] ?? 'Error';
    } elseif ($_POST['batch_action'] === 'delete') {
        $msg = batch_delete((string)($_POST['batch'] ?? '')) ? 'Batch deleted.' : 'Unable to delete batch.';
    }
    header("Location: Study_Materials_Manage_Batch.php?msg=" . urlencode($msg) . "#batches-section");
    exit();
}

// ---------- Approve / Reject / Delete material ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['material_id'])) {
    $mat_id = (string)$_POST['material_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        mat_mutate(function (array &$items) use ($mat_id) {
            foreach ($items as &$m) {
                if ($m['id'] === $mat_id) {
                    $m['status'] = 'approved';
                    $m['reviewedAt'] = time();
                    return true;
                }
            }
        });
    } elseif ($action === 'reject') {
        mat_mutate(function (array &$items) use ($mat_id) {
            foreach ($items as &$m) {
                if ($m['id'] === $mat_id) {
                    $m['status'] = 'rejected';
                    $m['reviewedAt'] = time();
                    return true;
                }
            }
        });
    } elseif ($action === 'delete') {
        mat_mutate(function (array &$items) use ($mat_id) {
            foreach ($items as $key => $m) {
                if ($m['id'] === $mat_id) {
                    mat_delete_file($m);
                    unset($items[$key]);
                    return true;
                }
            }
        });
    }
    header("Location: Study_Materials_Manage_Batch.php");
    exit();
}

usort($materials, function($a, $b) {
    return ($b['created'] ?? 0) <=> ($a['created'] ?? 0);
});

$batchList = batch_load();
$flash = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html data-eduvault-page="materials" data-eduvault-theme="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>EduVault | Study Materials &amp; Manage Batch</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
<link href="eduvault-design-system.css" rel="stylesheet"/>
<script id="eduvault-navigation-state">(function(){try{var s=sessionStorage;var via=s.getItem('ev_internal_navigation')==='1';if(via){document.documentElement.classList.add('ev-internal-navigation');s.removeItem('ev_internal_navigation');}var collapsed=s.getItem('ev_sidebar_collapsed');if(collapsed==='0')document.documentElement.classList.add('ev-sidebar-was-open');}catch(e){}})();</script>
<?php if (function_exists('ev_head')) ev_head(); ?>

<!-- Show the EduVault welcome screen only once per authenticated browser session. -->
<style>html.ev-admin-welcome-seen .ev-welcome{display:none!important;animation:none!important}</style>
<script src="eduvault-admin-profile-animation.js" data-ev-session="<?php echo htmlspecialchars((string)($_SESSION['ev_csrf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
</head>
<body>
<div aria-hidden="true" class="ev-welcome"><div class="ev-welcome-inner"><div class="ev-welcome-symbol"><i class="fa-solid fa-graduation-cap"></i></div><div class="ev-welcome-name">Edu<span>Vault</span></div><div class="ev-welcome-sub">Admin Command Center</div><div class="ev-welcome-rule"></div></div></div>
<div class="admin-app">
<aside aria-label="Admin navigation" class="side-navigation" id="side-navigation">
    <div class="sidebar-brand"><i class="fa-solid fa-graduation-cap"></i><span>Edu<span class="brand-accent">Vault</span><small>ADMIN PANEL</small></span></div>
    <nav class="sidebar-links">
        <a aria-current="false" class="sidebar-link" data-target="dashboard" href="Dashboard.php"><i aria-hidden="true" class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a aria-current="page" class="sidebar-link active" data-target="materials" href="Study_Materials_Manage_Batch.php"><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials &amp; Manage Batch</a>
        <a aria-current="false" class="sidebar-link" data-target="forums" href="Discussion_Forums_QA.php"><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</a>
        <a aria-current="false" class="sidebar-link" data-target="subjects" href="Subjects_Categories.php"><i aria-hidden="true" class="fa-solid fa-book"></i> Subjects &amp; Categories</a>
        <a aria-current="false" class="sidebar-link" data-target="events" href="Events_Announcements.php"><i aria-hidden="true" class="fa-solid fa-calendar-days"></i> Events &amp; Announcements</a>
        <a aria-current="false" class="sidebar-link" data-target="notice" href="Notice_Board.php"><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</a>
        <a aria-current="false" class="sidebar-link" data-target="logs" href="Activity_Logs.php"><i aria-hidden="true" class="fa-solid fa-terminal"></i> Activity Logs</a>
    </nav>
    <div class="sidebar-bottom">
        <a class="sidebar-link" href="../index.php" id="sidebar-home"><i class="fa-solid fa-house"></i> Student Dashboard</a>
        <a class="sidebar-link sidebar-signout" href="logout.php" id="sidebar-signout"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a>
    </div>
</aside>
<main class="main-content">
<header class="topbar">
    <button aria-controls="side-navigation" aria-expanded="true" aria-label="Collapse sidebar" class="sidebar-collapse-toggle" id="sidebar-collapse-toggle" title="Collapse sidebar" type="button"><i aria-hidden="true" class="fa-solid fa-bars"></i></button>
    <div class="header-brand">
        <div class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></div>
        <div class="brand-text">
            <h2>Edu<span>Vault</span></h2>
            <p>Admin Command Center</p>
        </div>
    </div>
    <div class="top-right">
        <div class="inline-search-wrap" id="inline-search-wrap">
            <div class="search header-search-bar inline-search-bar">
                <i aria-hidden="true" class="fa-solid fa-magnifying-glass"></i>
                <input aria-controls="global-search-results" aria-expanded="false" aria-label="Search dashboard" autocomplete="off" id="global-search-input" placeholder="Search here..." type="search"/>
            </div>
            <div class="inline-search-results" hidden="" id="inline-search-dropdown">
                <div aria-label="Dashboard search results" class="global-search-results" id="global-search-results"></div>
                <p class="inline-search-status" id="global-search-status" role="status"></p>
            </div>
        </div>
        <div class="sidebar-account admin-menu" id="admin-menu">
            <button aria-controls="admin-dropdown" aria-expanded="false" aria-haspopup="true" class="sidebar-account-trigger" id="admin-menu-trigger" type="button">
                <span class="sidebar-account-avatar" id="admin-avatar"><i aria-hidden="true" class="fa-solid fa-user"></i><img alt="Administrator profile photo" hidden="" id="admin-avatar-image"/></span>
                <span class="sidebar-account-details"><strong id="admin-display-name">Admin</strong><small>Administrator</small></span>
                <i aria-hidden="true" class="fa-solid fa-chevron-up sidebar-account-chevron"></i>
            </button>
            <div class="sidebar-account-dropdown admin-dropdown" hidden="" id="admin-dropdown">
                <label class="sidebar-account-item" for="admin-photo-input"><i aria-hidden="true" class="fa-solid fa-camera"></i> Change picture</label>
                <input accept="image/png,image/jpeg,image/webp" hidden="" id="admin-photo-input" type="file"/>
                <button class="sidebar-account-item" id="admin-remove-picture" type="button"><i aria-hidden="true" class="fa-solid fa-user-xmark"></i> Remove picture</button>
            </div>
        </div>
    </div>
</header>

<div class="workspace-view" data-view="materials" id="view-materials">
    <div class="workspace-heading">
        <h1><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials</h1>
        <p>EduVault administrator workspace</p>
    </div>
    
    <!-- APPROVE STUDY MATERIALS SECTION -->
    <section class="card section-space">
        <h3 class="card-title">
            <i class="fa-solid fa-file-circle-check"></i>
            Approve Uploaded Notes / Study Materials
        </h3>
        <p aria-live="polite" class="management-message" id="materials-status" role="status"></p>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Module/Subject</th>
                        <th>Uploaded By</th>
                        <th>Batch</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($materials)): ?>
                        <tr><td colspan="7" class="management-empty">No study materials available.</td></tr>
                    <?php else: ?>
                        <?php foreach ($materials as $item): ?>
                            <?php $status = strtolower($item['status'] ?? 'pending'); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['module'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['ownerName'] ?? 'Student'); ?></td>
                                <td><?php echo htmlspecialchars($item['batch'] ?? ''); ?></td>
                                <td><?php echo date('d M Y', (int)($item['created'] ?? time())); ?></td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($status == 'approved') ? 'approved' : 
                                             (($status == 'pending') ? 'pending' : 'reported'); 
                                    ?>">
                                        <?php echo htmlspecialchars(ucfirst($status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="action-btn view-btn" title="View File" onclick="window.open('../material_file.php?id=<?php echo urlencode($item['id']); ?>', '_blank')">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </button>
                                    
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="material_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                        
                                        <?php if ($status !== 'approved'): ?>
                                            <button type="submit" name="action" value="approve" class="action-btn approve-btn" title="Approve" onclick="return confirm('Approve this material?');">
                                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($status !== 'rejected'): ?>
                                            <button type="submit" name="action" value="reject" class="action-btn delete-btn" title="Reject" onclick="return confirm('Reject this material?');">
                                                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                            </button>
                                        <?php endif; ?>

                                        <button type="submit" name="action" value="delete" class="action-btn delete-btn" title="Delete" onclick="return confirm('Permanently delete this material?');">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- BATCH MANAGEMENT SECTION (server-side) -->
    <section class="card section-space" id="batches-section">
        <h3 class="card-title"><i aria-hidden="true" class="fa-solid fa-plus-circle"></i> Create Batch</h3>
        <form class="batch-creation-form" method="POST" action="Study_Materials_Manage_Batch.php">
            <input type="hidden" name="batch_action" value="create">
            <div class="input-group">
                <label for="new-batch-name">Batch Name</label>
                <input autocomplete="off" id="new-batch-name" maxlength="75" name="batch" placeholder="e.g. 27.1 IFSLS" required/>
            </div>
            <button class="add-btn" type="submit"><i aria-hidden="true" class="fa-solid fa-plus"></i> Create Batch</button>
        </form>
        <p aria-live="polite" class="management-message" id="batch-message" role="status"><?php echo htmlspecialchars($flash); ?></p>

        <div class="management-list">
            <h4>Available Batches</h4>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Batch</th><th>Action</th></tr></thead>
                    <tbody id="batch-table">
                        <?php if (empty($batchList)): ?>
                            <tr><td colspan="2" class="management-empty">No batches created.</td></tr>
                        <?php else: ?>
                            <?php foreach ($batchList as $b): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($b); ?></td>
                                    <td>
                                        <form method="POST" action="Study_Materials_Manage_Batch.php" style="display:inline-block;">
                                            <input type="hidden" name="batch_action" value="delete">
                                            <input type="hidden" name="batch" value="<?php echo htmlspecialchars($b); ?>">
                                            <button type="submit" class="action-btn delete-btn" title="Delete batch" onclick="return confirm('Delete batch <?php echo htmlspecialchars(addslashes($b)); ?>?');">
                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<footer class="footer">
    EduVault Admin Command Center | NSBM Green University - Faculty of Computing
</footer>
</main>
</div>

<script>
(function () {
    "use strict";
    const key = "eduvault_activity_logs_v1";
    window.eduVaultLog = function(level, message) {
        try {
            let records = JSON.parse(localStorage.getItem(key) || "[]");
            records.unshift({time: new Date().toISOString(), level, message: String(message).slice(0, 500)});
            localStorage.setItem(key, JSON.stringify(records.slice(0, 300)));
        } catch (_) {}
    };
})();
</script>

<!-- Reuse the original dashboard profile-photo handler. -->

</body>
</html>