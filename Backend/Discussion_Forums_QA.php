<?php
// htdocs/Backend/Discussion_Forums_QA.php
require __DIR__ . '/_auth.php';
require_once __DIR__ . '/../forum_db.php';

$db = ev_db();
if (!$db) { die('Database connection not found. Check db.php'); }
ev_forum_tables($db);

$uploadDir = __DIR__ . '/../uploads/forum/';

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fclean($v, $max) { return mb_substr(trim((string)$v), 0, $max); }

$flash = '';
$flashErr = false;
if (isset($_GET['saved']))    $flash = 'Post created successfully.';
if (isset($_GET['updated']))  $flash = 'Post updated successfully.';
if (isset($_GET['approved'])) $flash = 'Post approved.';
if (isset($_GET['deleted']))  $flash = 'Post deleted.';
if (isset($_GET['rdeleted'])) $flash = 'Reply deleted.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    try {
        /* ---------- CREATE / UPDATE ---------- */
        if ($act === 'save_post') {
            $id     = (int)($_POST['id'] ?? 0);
            $type   = (($_POST['type'] ?? '') === 'qa') ? 'qa' : 'discussion';
            $title  = fclean($_POST['title'] ?? '', 160);
            $author = fclean($_POST['user'] ?? '', 100);
            $cat    = fclean($_POST['category'] ?? '', 100);
            $desc   = fclean($_POST['description'] ?? '', 2000);
            $answer = ($type === 'qa') ? fclean($_POST['answer'] ?? '', 2000) : '';
            if ($title === '' || $author === '' || $cat === '' || $desc === '') {
                throw new Exception('Please complete all required fields.');
            }

            $existing = null;
            if ($id) {
                $r = ev_run($db, "SELECT * FROM forum_posts WHERE id = ?", [$id]);
                $existing = $r[0] ?? null;
                if (!$existing) throw new Exception('Post not found.');
            }

            $photo = $existing['photo'] ?? null;
            if ($type !== 'qa') {
                if ($photo) ev_delete_photo($photo, $uploadDir);
                $photo = null;
            } else {
                if (!empty($_POST['remove_photo']) && $photo) { ev_delete_photo($photo, $uploadDir); $photo = null; }
                $new = ev_save_photo('photo', $uploadDir);
                if ($new) { if ($photo) ev_delete_photo($photo, $uploadDir); $photo = $new; }
            }

            if ($existing) {
                ev_run($db, "UPDATE forum_posts SET type = ?, title = ?, author = ?, category = ?, description = ?, answer = ?, photo = ?, edited_at = NOW() WHERE id = ?",
                       [$type, $title, $author, $cat, $desc, $answer, $photo, $id]);
                header('Location: Discussion_Forums_QA.php?updated=1');
            } else {
                ev_run($db, "INSERT INTO forum_posts (type, title, author, owner_key, category, description, answer, photo, status) VALUES (?, ?, ?, NULL, ?, ?, ?, ?, 'Active')",
                       [$type, $title, $author, $cat, $desc, $answer, $photo]);
                header('Location: Discussion_Forums_QA.php?saved=1');
            }
            exit();
        }

        /* ---------- APPROVE ---------- */
        if ($act === 'approve_post') {
            ev_run($db, "UPDATE forum_posts SET status = 'Active' WHERE id = ?", [(int)($_POST['id'] ?? 0)]);
            header('Location: Discussion_Forums_QA.php?approved=1');
            exit();
        }

        /* ---------- DELETE POST ---------- */
        if ($act === 'delete_post') {
            $id = (int)($_POST['id'] ?? 0);
            $r = ev_run($db, "SELECT photo FROM forum_posts WHERE id = ?", [$id]);
            if ($r) ev_delete_photo($r[0]['photo'], $uploadDir);
            ev_run($db, "DELETE FROM forum_reply_likes WHERE reply_id IN (SELECT id FROM forum_replies WHERE post_id = ?)", [$id]);
            ev_run($db, "DELETE FROM forum_replies WHERE post_id = ?", [$id]);
            ev_run($db, "DELETE FROM forum_posts WHERE id = ?", [$id]);
            header('Location: Discussion_Forums_QA.php?deleted=1');
            exit();
        }

        /* ---------- DELETE A STUDENT REPLY ---------- */
        if ($act === 'delete_reply') {
            $rid = (int)($_POST['reply_id'] ?? 0);
            ev_run($db, "DELETE FROM forum_reply_likes WHERE reply_id = ?", [$rid]);
            ev_run($db, "DELETE FROM forum_replies WHERE id = ?", [$rid]);
            header('Location: Discussion_Forums_QA.php?rdeleted=1');
            exit();
        }
    } catch (Exception $e) {
        $flash = $e->getMessage();
        $flashErr = true;
    }
}

/* ---------- DATA FOR THE PAGE ---------- */
$editing = null;
if (isset($_GET['edit'])) {
    $r = ev_run($db, "SELECT * FROM forum_posts WHERE id = ?", [(int)$_GET['edit']]);
    $editing = $r[0] ?? null;
}
$allPosts = ev_run($db, "SELECT * FROM forum_posts ORDER BY created_at DESC, id DESC");
$allReplies = ev_run($db, "SELECT * FROM forum_replies ORDER BY created_at, id");
$repliesByPost = [];
foreach ($allReplies as $r) { $repliesByPost[$r['post_id']][] = $r; }

$fType  = $editing['type'] ?? 'discussion';
$fTitle = $editing['title'] ?? '';
$fUser  = $editing['author'] ?? 'Admin';
$fCat   = $editing['category'] ?? '';
$fDesc  = $editing['description'] ?? '';
$fAns   = $editing['answer'] ?? '';
$fPhoto = $editing['photo'] ?? '';
?>
<!DOCTYPE html>

<html data-eduvault-page="forums" data-eduvault-theme="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>EduVault | Discussion Forums &amp; Q&amp;A</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
<link href="eduvault-design-system.css" rel="stylesheet"/><script id="eduvault-navigation-state">(function(){try{var s=sessionStorage;var via=s.getItem('ev_internal_navigation')==='1';if(via){document.documentElement.classList.add('ev-internal-navigation');s.removeItem('ev_internal_navigation');}var collapsed=s.getItem('ev_sidebar_collapsed');if(collapsed==='0')document.documentElement.classList.add('ev-sidebar-was-open');}catch(e){}})();</script>
<?php ev_head(); ?>

<!-- Show the EduVault welcome screen only once per authenticated browser session. -->
<style>html.ev-admin-welcome-seen .ev-welcome{display:none!important;animation:none!important}</style>
<script src="eduvault-admin-profile-animation.js" data-ev-session="<?php echo htmlspecialchars((string)($_SESSION['ev_csrf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
</head>
<body>
<div aria-hidden="true" class="ev-welcome"><div class="ev-welcome-inner"><div class="ev-welcome-symbol"><i class="fa-solid fa-graduation-cap"></i></div><div class="ev-welcome-name">Edu<span>Vault</span></div><div class="ev-welcome-sub">Admin Command Center</div><div class="ev-welcome-rule"></div></div></div>
<div class="admin-app"><aside aria-label="Admin navigation" class="side-navigation" id="side-navigation"><div class="sidebar-brand"><i class="fa-solid fa-graduation-cap"></i><span>Edu<span class="brand-accent">Vault</span><small>ADMIN PANEL</small></span></div><button aria-controls="side-navigation" aria-expanded="true" aria-label="Collapse sidebar" class="sidebar-collapse-toggle" id="sidebar-collapse-toggle" title="Collapse sidebar" type="button"><i aria-hidden="true" class="fa-solid fa-bars"></i></button><nav class="sidebar-links"><a aria-current="false" class="sidebar-link" data-target="dashboard" href="Dashboard.php"><i aria-hidden="true" class="fa-solid fa-gauge-high"></i> Dashboard</a><a aria-current="false" class="sidebar-link" data-target="materials" href="Study_Materials_Manage_Batch.php"><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials &amp; Manage Batch</a><a aria-current="page" class="sidebar-link active" data-target="forums" href="Discussion_Forums_QA.php"><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</a><a aria-current="false" class="sidebar-link" data-target="subjects" href="Subjects_Categories.php"><i aria-hidden="true" class="fa-solid fa-book"></i> Subjects &amp; Categories</a><a aria-current="false" class="sidebar-link" data-target="events" href="Events_Announcements.php"><i aria-hidden="true" class="fa-solid fa-calendar-days"></i> Events &amp; Announcements</a><a aria-current="false" class="sidebar-link" data-target="notice" href="Notice_Board.php"><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</a><a aria-current="false" class="sidebar-link" data-target="logs" href="Activity_Logs.php"><i aria-hidden="true" class="fa-solid fa-terminal"></i> Activity Logs</a></nav><div class="sidebar-bottom"><a class="sidebar-link" href="../index.php" id="sidebar-home"><i class="fa-solid fa-house"></i> Student Dashboard</a><a class="sidebar-link sidebar-signout" href="logout.php" id="sidebar-signout"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
<main class="main-content">
<!-- HEADER -->
<header class="topbar">
<div class="header-brand">
<div class="brand-icon"><i class="fa-solid fa-graduation-cap"></i></div>
<div class="brand-text"><h2>Edu<span>Vault</span></h2><p>Admin Command Center</p></div>
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

<!-- ================= DISCUSSION FORUMS & Q&A ================= -->
<div class="workspace-view" data-view="forums" id="view-forums">
<div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</h1><p>EduVault administrator workspace</p></div>

<div class="card" id="forums-section">
<h3 class="card-title"><i class="fa-solid fa-comments"></i> Moderate Discussion Forums &amp; Q&amp;A</h3>
<p class="management-help">Posts created here are saved in the database and shown on the student Discussion / Q&amp;A page.</p>

<form class="management-form<?= $editing ? ' is-editing' : '' ?>" data-post-type="<?= h($fType) ?>" enctype="multipart/form-data" id="forum-form" method="post" action="Discussion_Forums_QA.php">
<input type="hidden" name="action" value="save_post"/>
<input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>"/>

<div class="forum-type-select"><label for="forum-type">Choose Post Type</label>
<select aria-label="Choose Discussion Forum or Q&amp;A" id="forum-type" name="type">
<option value="discussion"<?= $fType === 'discussion' ? ' selected' : '' ?>>Discussion Forum</option>
<option value="qa"<?= $fType === 'qa' ? ' selected' : '' ?>>Q&amp;A</option>
</select><p id="forum-type-help">Start an open discussion for students to share ideas and replies.</p></div>

<div class="input-group"><label for="forum-title" id="forum-title-label">Discussion Topic</label><input id="forum-title" maxlength="160" name="title" placeholder="e.g. How do PHP sessions work?" required="" value="<?= h($fTitle) ?>"/></div>

<div class="form-row">
<div class="input-group"><label for="forum-user">Posted By</label><input id="forum-user" maxlength="100" name="user" placeholder="e.g. Admin" required="" value="<?= h($fUser) ?>"/></div>
<div class="input-group"><label for="forum-category">Category</label><input autocomplete="off" id="forum-category" maxlength="100" name="category" placeholder="Type a category (e.g. Programming)" required="" type="text" value="<?= h($fCat) ?>"/></div>
</div>

<div class="input-group"><label for="forum-description" id="forum-description-label">Discussion Details</label><textarea id="forum-description" maxlength="2000" name="description" placeholder="Enter discussion or question details" required=""><?= h($fDesc) ?></textarea></div>

<div class="input-group" hidden="" id="forum-answer-group"><label for="forum-answer">Official Answer (optional)</label><textarea id="forum-answer" maxlength="2000" name="answer" placeholder="Write the official answer students will see on this question"><?= h($fAns) ?></textarea></div>

<div class="input-group" hidden="" id="forum-photo-group"><label for="forum-photo">Q&amp;A Intro Photo (optional)</label>
<input accept="image/png,image/jpeg,image/webp,image/gif" id="forum-photo" name="photo" type="file"/>
<?php if ($fPhoto): ?>
<div class="qa-photo-preview" id="forum-photo-current"><img alt="Current photo" src="../uploads/forum/<?= rawurlencode($fPhoto) ?>"/><label style="display:block;margin-top:6px"><input type="checkbox" name="remove_photo" value="1"/> Remove current photo</label></div>
<?php endif; ?>
<div class="qa-photo-preview" hidden="" id="forum-photo-preview"><img alt="Q&amp;A introductory photo preview" id="forum-photo-image"/></div>
<small class="forum-photo-help">Upload an image for the question. JPG, PNG, WebP or GIF, max 800 KB.</small></div>

<div class="form-actions">
<button class="add-btn" id="forum-submit" type="submit"><i class="fa-solid <?= $editing ? 'fa-floppy-disk' : 'fa-plus' ?>"></i> <?= $editing ? 'Save Changes' : 'Create Discussion' ?></button>
<?php if ($editing): ?><a class="cancel-edit" href="Discussion_Forums_QA.php" style="display:inline-block;text-decoration:none">Cancel Edit</a><?php endif; ?>
</div>
<?php if ($editing): ?><p class="editing-indicator">Editing existing post. Save changes or cancel.</p><?php endif; ?>
<p aria-live="polite" class="management-message<?= $flashErr ? ' error' : '' ?>" id="forum-message" role="status"><?= h($flash) ?></p>
</form>

<div class="management-list"><h4>Discussion Posts &amp; Questions</h4>
<div class="table-wrapper" style="max-height:640px;overflow:auto">
<table class="management-table">
<thead><tr><th>Question / Post</th><th>User</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php if (!$allPosts): ?>
<tr><td class="management-empty" colspan="4">No discussions created yet.</td></tr>
<?php endif; ?>
<?php foreach ($allPosts as $p):
    $isQa = ($p['type'] === 'qa');
    $reps = $repliesByPost[$p['id']] ?? [];
    $badge = $p['status'] === 'Reported' ? 'reported' : ($p['status'] === 'Pending' ? 'pending' : 'approved'); ?>
<tr>
<td class="forum-post-cell">
<strong><?= h($p['title']) ?></strong>
<span class="forum-kind<?= $isQa ? ' qa' : '' ?>"><?= $isQa ? 'Q&amp;A' : 'Discussion Forum' ?></span>
<p class="forum-detail"><?= nl2br(h($p['description'])) ?></p>
<p class="forum-meta"><?= h($p['category']) ?> · <?= h($p['created_at']) ?></p>
<?php if ($isQa && !empty($p['answer'])): ?><p class="forum-detail">Answer: <?= nl2br(h($p['answer'])) ?></p><?php endif; ?>
<?php if ($isQa && !empty($p['photo'])): ?><img alt="Intro photo for <?= h($p['title']) ?>" class="forum-intro-thumbnail" loading="lazy" src="../uploads/forum/<?= rawurlencode($p['photo']) ?>"/><?php endif; ?>
<?php if ($reps): ?>
<details style="margin-top:8px"><summary><?= count($reps) ?> repl<?= count($reps) === 1 ? 'y' : 'ies' ?></summary>
<?php foreach ($reps as $r): ?>
<div style="display:flex;gap:8px;align-items:flex-start;margin:6px 0">
<div style="flex:1"><strong><?= h($r['author']) ?></strong><?= $r['best'] ? ' <em>(best answer)</em>' : '' ?><br/><?= nl2br(h($r['body'])) ?><br/><small><?= h($r['created_at']) ?></small></div>
<form method="post" action="Discussion_Forums_QA.php" onsubmit="return confirm('Delete this reply?')"><input type="hidden" name="action" value="delete_reply"/><input type="hidden" name="reply_id" value="<?= (int)$r['id'] ?>"/><button aria-label="Delete reply" class="action-btn delete-btn" title="Delete reply" type="submit"><i class="fa-solid fa-trash"></i></button></form>
</div>
<?php endforeach; ?>
</details>
<?php endif; ?>
</td>
<td><?= h($p['author']) ?></td>
<td><span class="badge <?= $badge ?>"><?= h($p['status']) ?></span></td>
<td><div class="forum-row-actions">
<?php if ($p['status'] !== 'Active'): ?>
<form method="post" action="Discussion_Forums_QA.php" style="display:inline"><input type="hidden" name="action" value="approve_post"/><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/><button aria-label="Approve post" class="action-btn approve-btn" title="Approve" type="submit"><i class="fa-solid fa-check"></i></button></form>
<?php endif; ?>
<a aria-label="Edit post" class="action-btn edit-btn" href="Discussion_Forums_QA.php?edit=<?= (int)$p['id'] ?>#forum-form" title="Edit" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none"><i class="fa-solid fa-pen"></i></a>
<form method="post" action="Discussion_Forums_QA.php" onsubmit="return confirm('Delete this post and all of its replies?')" style="display:inline"><input type="hidden" name="action" value="delete_post"/><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"/><button aria-label="Delete post" class="action-btn delete-btn" title="Delete" type="submit"><i class="fa-solid fa-trash"></i></button></form>
</div></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<p class="management-list-count"><?= count($allPosts) ?> post(s)</p>
</div>
</div>
</div>

<!-- FOOTER -->
<footer class="footer">
    EduVault Admin Command Center
    |
    NSBM Green University - Faculty of Computing
</footer>
</main>
</div>

<!-- Shared activity logger (same localStorage key the Activity Logs page reads) -->
<script>
window.eduVaultLog = function(level, message) {
    try {
        var k = 'eduvault_activity_logs_v1';
        var r = JSON.parse(localStorage.getItem(k) || '[]');
        if (!Array.isArray(r)) r = [];
        r.unshift({time: new Date().toISOString(), level: level, message: String(message).slice(0, 500)});
        localStorage.setItem(k, JSON.stringify(r.slice(0, 300)));
    } catch (e) {}
};
<?php if (isset($_GET['saved'])): ?>window.eduVaultLog('success', 'Discussion post created');<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>window.eduVaultLog('success', 'Discussion post updated');<?php endif; ?>
<?php if (isset($_GET['approved'])): ?>window.eduVaultLog('success', 'Discussion post approved');<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>window.eduVaultLog('warn', 'Discussion post deleted');<?php endif; ?>
<?php if (isset($_GET['rdeleted'])): ?>window.eduVaultLog('warn', 'Discussion reply deleted');<?php endif; ?>
</script>

<!-- Admin profile picture menu -->


<!-- Header search + page navigation -->
<script>
(function(){
  'use strict';
  const pages={dashboard:'Dashboard.php',materials:'Study_Materials_Manage_Batch.php',forums:'Discussion_Forums_QA.php',subjects:'Subjects_Categories.php',events:'Events_Announcements.php',notice:'Notice_Board.php',logs:'Activity_Logs.php'};
  const current='forums';

  function navigate(target){
    let name=target;
    const detail=name==='announcements'?'announcements':name==='users'?'users':name==='batches'?'batches':null;
    if(name==='announcements')name='events';
    if(name==='users')name='dashboard';
    if(name==='batches')name='materials';
    if(!pages[name])name='dashboard';
    if(name!==current){
      try{
        sessionStorage.setItem('ev_internal_navigation','1');
        sessionStorage.setItem('ev_sidebar_collapsed',document.querySelector('.admin-app').classList.contains('sidebar-collapsed')?'1':'0');
        if(detail)sessionStorage.setItem('ev_pending_section',detail);
      }catch(e){}
      window.location.assign(pages[name]);
    }
  }
  window.eduVaultNavigate=navigate;

  document.querySelectorAll('.sidebar-link[data-target]').forEach(link=>{
    const name=link.dataset.target;
    link.addEventListener('click',e=>{e.preventDefault();if(name!==current)navigate(name);});
  });

  const input=document.getElementById('global-search-input');
  const dropdown=document.getElementById('inline-search-dropdown');
  const results=document.getElementById('global-search-results');
  const status=document.getElementById('global-search-status');
  if(!input||!dropdown||!results)return;
  const items=[
    {label:'Dashboard',view:'dashboard',terms:'home overview'},
    {label:'Study Materials & Manage Batch',view:'materials',terms:'study materials uploaded notes batch'},
    {label:'Manage Users',view:'users',terms:'users students registered users'},
    {label:'Discussion Forums & Q&A',view:'forums',terms:'forum q and a questions discussions'},
    {label:'Subjects & Categories',view:'subjects',terms:'subject category categories'},
    {label:'Events & Announcements',view:'events',terms:'event announcement create'},
    {label:'Notice Board',view:'notice',terms:'notice notices board'},
    {label:'Manage Batches',view:'batches',terms:'batch manage batch'},
    {label:'Activity Logs',view:'logs',terms:'log console activity'}
  ];
  const normalize=v=>String(v||'').toLowerCase().replace(/[^a-z0-9]+/g,' ').trim();
  let matches=[];
  function close(){dropdown.hidden=true;input.setAttribute('aria-expanded','false');}
  function go(item){close();input.value='';input.blur();navigate(item.view);}
  function render(){
    const q=normalize(input.value);
    results.replaceChildren();
    if(!q){matches=[];close();return;}
    matches=items.filter(i=>normalize(i.label).includes(q)||normalize(i.terms).split(' ').some(w=>w.startsWith(q)));
    dropdown.hidden=false;input.setAttribute('aria-expanded','true');
    matches.forEach(item=>{
      const b=document.createElement('button');
      b.type='button';b.className='global-search-result';
      const s=document.createElement('strong');s.textContent=item.label;b.appendChild(s);
      b.addEventListener('click',()=>go(item));
      results.appendChild(b);
    });
    if(status)status.textContent=matches.length?'':'No matching page found';
  }
  input.addEventListener('input',render);
  input.addEventListener('focus',render);
  input.addEventListener('keydown',e=>{
    if(e.key==='Escape'){close();input.blur();}
    if(e.key==='Enter'){e.preventDefault();if(matches.length)go(matches[0]);}
  });
  document.addEventListener('pointerdown',e=>{if(!e.target.closest('#inline-search-wrap'))close();});
  document.addEventListener('keydown',e=>{
    if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();input.focus();input.select();}
  });
  close();
})();
</script>

<!-- Sidebar collapse + mobile drawer -->
<script>
(function(){
  const app=document.querySelector('.admin-app');
  const toggle=document.getElementById('sidebar-collapse-toggle');
  if(!app||!toggle)return;
  document.querySelectorAll('.sidebar-link').forEach(item=>{
    const label=item.textContent.trim().replace(/\s+/g,' ');
    if(label){item.setAttribute('title',label);item.setAttribute('aria-label',label);}
  });
  function setCollapsed(collapsed){
    app.classList.toggle('sidebar-collapsed',collapsed);
    toggle.setAttribute('aria-expanded',String(!collapsed));
    toggle.setAttribute('aria-label',collapsed?'Open menu':'Close menu');
    toggle.title=collapsed?'Open menu':'Close menu';
    try{sessionStorage.setItem('ev_sidebar_collapsed',collapsed?'1':'0');}catch(e){}
    document.body.classList.toggle('ev-drawer-open',!collapsed);
  }
  const header=document.querySelector('.topbar');
  if(header)header.insertBefore(toggle,header.firstChild);
  let previous='1';try{previous=sessionStorage.getItem('ev_sidebar_collapsed')??'1';}catch(e){}
  setCollapsed(previous!=='0');
  toggle.addEventListener('click',()=>setCollapsed(!app.classList.contains('sidebar-collapsed')));

  const isMobile=()=>window.matchMedia('(max-width:700px)').matches;
  document.addEventListener('pointerdown',e=>{
    if(isMobile()&&!app.classList.contains('sidebar-collapsed')&&!e.target.closest('.side-navigation')&&!e.target.closest('#sidebar-collapse-toggle'))setCollapsed(true);
  });
  document.addEventListener('keydown',e=>{
    if(e.key==='Escape'&&isMobile()&&!app.classList.contains('sidebar-collapsed')){setCollapsed(true);toggle.focus();}
  });
})();
</script>

<!-- Forum form helpers (post type labels, photo preview). The form itself posts to PHP. -->
<script>
(function(){
  const typeSel=document.getElementById('forum-type');
  const isEditing=<?= $editing ? 'true' : 'false' ?>;
  if(!typeSel)return;
  const $=id=>document.getElementById(id);
  function updateType(){
    const qa=typeSel.value==='qa';
    $('forum-form').dataset.postType=qa?'qa':'discussion';
    $('forum-title-label').textContent=qa?'Question Title':'Discussion Topic';
    $('forum-description-label').textContent=qa?'Question Details':'Discussion Details';
    $('forum-title').placeholder=qa?'e.g. How do PHP sessions work?':'e.g. Best practices for web development';
    $('forum-description').placeholder=qa?'Explain your question clearly':'Describe the topic and invite students to discuss';
    $('forum-photo-group').hidden=!qa;
    $('forum-answer-group').hidden=!qa;
    $('forum-type-help').textContent=qa?'Post a specific question and optionally provide an official answer.':'Start an open discussion for students to share ideas and replies.';
    if(!isEditing)$('forum-submit').innerHTML='<i class="fa-solid fa-plus"></i> '+(qa?'Create Q&A':'Create Discussion');
  }
  typeSel.addEventListener('change',updateType);
  updateType();

  const photo=$('forum-photo'),prev=$('forum-photo-preview'),img=$('forum-photo-image');
  photo.addEventListener('change',()=>{
    const f=photo.files&&photo.files[0];
    if(!f){prev.hidden=true;return;}
    if(!['image/jpeg','image/png','image/webp','image/gif'].includes(f.type)||f.size>800*1024){
      $('forum-message').textContent='Choose a JPG, PNG, WebP or GIF smaller than 800 KB.';
      $('forum-message').classList.add('error');photo.value='';prev.hidden=true;return;
    }
    $('forum-message').textContent='';$('forum-message').classList.remove('error');
    img.src=URL.createObjectURL(f);prev.hidden=false;
  });
})();
</script>
</body>
</html>