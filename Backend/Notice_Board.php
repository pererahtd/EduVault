<?php
error_reporting(E_ALL);

// Admin login check (same file the Study Materials admin page uses)
require __DIR__ . '/_auth.php';
// db.php is in htdocs/ (one level above the Backend folder)
require __DIR__ . '/../db.php';

// Works with both PDO ($pdo) and MySQLi ($conn)
$db = (isset($pdo) && $pdo instanceof PDO) ? $pdo : ($conn ?? null);
if (!$db) {
    die('Database connection not found. Check db.php');
}

function ev_insert($db, $title, $category, $description, $expires, $file) {
    $sql = "INSERT INTO notices (title, category, body, expires_at, attachment) VALUES (?, ?, ?, ?, ?)";
    if ($db instanceof PDO) {
        $db->prepare($sql)->execute([$title, $category, $description, $expires, $file]);
    } else {
        $st = $db->prepare($sql);
        $st->bind_param('sssss', $title, $category, $description, $expires, $file);
        $st->execute();
    }
}

function ev_find($db, $id) {
    $sql = "SELECT * FROM notices WHERE id = ?";
    if ($db instanceof PDO) {
        $st = $db->prepare($sql);
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }
    $st = $db->prepare($sql);
    $st->bind_param('i', $id);
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_assoc() : null;
}

function ev_delete($db, $id) {
    $sql = "DELETE FROM notices WHERE id = ?";
    if ($db instanceof PDO) {
        $db->prepare($sql)->execute([$id]);
    } else {
        $st = $db->prepare($sql);
        $st->bind_param('i', $id);
        $st->execute();
    }
}

function ev_all($db) {
    $sql = "SELECT * FROM notices ORDER BY created_at DESC, id DESC";
    if ($db instanceof PDO) {
        return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
    $r = $db->query($sql);
    return $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}

$uploadDir = __DIR__ . '/../uploads/';

// ---------- PUBLISH ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publish_notice'])) {
    $title       = trim($_POST['title'] ?? '');
    $category    = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $expires_at  = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

    $attachmentName = null;
    if (isset($_FILES['notice_file']) && $_FILES['notice_file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['notice_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp'], true)
            && $_FILES['notice_file']['size'] <= 10 * 1024 * 1024) {
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['notice_file']['name']));
            $attachmentName = time() . '_' . $safe;
            move_uploaded_file($_FILES['notice_file']['tmp_name'], $uploadDir . $attachmentName);
        }
    }

    if ($title !== '' && $category !== '' && $description !== '') {
        ev_insert($db, $title, $category, $description, $expires_at, $attachmentName);
        header("Location: Notice_Board.php?success=1");
    } else {
        header("Location: Notice_Board.php?error=1");
    }
    exit();
}

// ---------- DELETE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice'])) {
    $id  = (int)($_POST['id'] ?? 0);
    $row = ev_find($db, $id);
    if ($row && !empty($row['attachment'])) {
        $path = $uploadDir . basename($row['attachment']);
        if (is_file($path)) { @unlink($path); }
    }
    ev_delete($db, $id);
    header("Location: Notice_Board.php?deleted=1");
    exit();
}

$allNotices = ev_all($db);
$today = date('Y-m-d');
?>
<!DOCTYPE html>

<html data-eduvault-page="notice" data-eduvault-theme="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>EduVault | Notice Board</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
<link href="eduvault-design-system.css" rel="stylesheet"/><script id="eduvault-navigation-state">(function(){try{var s=sessionStorage;var via=s.getItem('ev_internal_navigation')==='1';if(via){document.documentElement.classList.add('ev-internal-navigation');s.removeItem('ev_internal_navigation');}var collapsed=s.getItem('ev_sidebar_collapsed');if(collapsed==='0')document.documentElement.classList.add('ev-sidebar-was-open');}catch(e){}})();</script>

<!-- Show the EduVault welcome screen only once per authenticated browser session. -->
<style>html.ev-admin-welcome-seen .ev-welcome{display:none!important;animation:none!important}</style>
<script src="eduvault-admin-profile-animation.js" data-ev-session="<?php echo htmlspecialchars((string)($_SESSION['ev_csrf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
</head>
<body>
<div aria-hidden="true" class="ev-welcome"><div class="ev-welcome-inner"><div class="ev-welcome-symbol"><i class="fa-solid fa-graduation-cap"></i></div><div class="ev-welcome-name">Edu<span>Vault</span></div><div class="ev-welcome-sub">Admin Command Center</div><div class="ev-welcome-rule"></div></div></div>
<div class="admin-app"><aside aria-label="Admin navigation" class="side-navigation" id="side-navigation"><div class="sidebar-brand"><i class="fa-solid fa-graduation-cap"></i><span>Edu<span class="brand-accent">Vault</span><small>ADMIN PANEL</small></span></div><button aria-controls="side-navigation" aria-expanded="true" aria-label="Collapse sidebar" class="sidebar-collapse-toggle" id="sidebar-collapse-toggle" title="Collapse sidebar" type="button"><i aria-hidden="true" class="fa-solid fa-bars"></i></button><nav class="sidebar-links"><a aria-current="false" class="sidebar-link" data-target="dashboard" href="Dashboard.php"><i aria-hidden="true" class="fa-solid fa-gauge-high"></i> Dashboard</a><a aria-current="false" class="sidebar-link" data-target="materials" href="Study_Materials_Manage_Batch.php"><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials &amp; Manage Batch</a><a aria-current="false" class="sidebar-link" data-target="forums" href="Discussion_Forums_QA.php"><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</a><a aria-current="false" class="sidebar-link" data-target="subjects" href="Subjects_Categories.php"><i aria-hidden="true" class="fa-solid fa-book"></i> Subjects &amp; Categories</a><a aria-current="false" class="sidebar-link" data-target="events" href="Events_Announcements.php"><i aria-hidden="true" class="fa-solid fa-calendar-days"></i> Events &amp; Announcements</a><a aria-current="page" class="sidebar-link active" data-target="notice" href="Notice_Board.php"><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</a><a aria-current="false" class="sidebar-link" data-target="logs" href="Activity_Logs.php"><i aria-hidden="true" class="fa-solid fa-terminal"></i> Activity Logs</a></nav><div class="sidebar-bottom"><a class="sidebar-link" href="../index.php" id="sidebar-home"><i class="fa-solid fa-house"></i> Student Dashboard</a><a class="sidebar-link sidebar-signout" href="logout.php" id="sidebar-signout"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
<main class="main-content">
<!-- HEADER -->
<header class="topbar">
<div class="header-brand">
<div class="brand-icon">
<i class="fa-solid fa-graduation-cap"></i>
</div>
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

<!-- ================= NOTICE BOARD ================= -->
<div class="workspace-view" data-view="notice" id="view-notice">
<div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</h1><p>Latest notices and important announcements for the student community.</p></div>

<section aria-label="Create notice" class="card section-space">
<h3 class="card-title"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i> Create Notice</h3>
<p class="management-help">Notices published here are saved in the database and shown on the student Notice Board.</p>

<form class="management-form" id="notice-form" method="post" action="Notice_Board.php" enctype="multipart/form-data">
<input type="hidden" name="publish_notice" value="1"/>

<div class="input-group"><label for="notice-title">Title</label><input id="notice-title" name="title" maxlength="120" placeholder="e.g. Final exam timetable released" required=""/></div>

<div class="input-group"><label for="notice-category">Category</label>
<div aria-label="Common notice categories" class="ev-notice-categories" id="notice-category-pills" role="group">
<button data-category="General" type="button"><i class="fa-solid fa-clipboard"></i> General</button><button data-category="Academic" type="button"><i class="fa-solid fa-book"></i> Academic</button><button data-category="Exams" type="button"><i class="fa-solid fa-pen-to-square"></i> Exams</button><button data-category="Event" type="button"><i class="fa-solid fa-calendar"></i> Events</button><button data-category="Urgent" type="button"><i class="fa-solid fa-triangle-exclamation"></i> Urgent</button></div>
<input id="notice-category" name="category" maxlength="80" placeholder="Or type your own category" required="" type="text"/></div>

<div class="input-group"><label for="notice-description">Message</label><textarea id="notice-description" name="description" maxlength="1500" placeholder="Write the full notice here..." required=""></textarea><span class="ev-notice-counter" id="notice-counter">0/1500</span></div>

<div class="input-group"><label for="notice-file">Attachment (optional · PDF or image, max 10 MB)</label>
<label class="ev-notice-drop" for="notice-file" id="notice-drop"><i class="fa-solid fa-cloud-arrow-up"></i><span>Click to choose a file or drag it here</span></label>
<input accept="application/pdf,image/*" hidden="" id="notice-file" name="notice_file" type="file"/>
<div class="ev-notice-picked" hidden="" id="notice-picked"></div>
<small class="ev-notice-help">PDF or image, up to 10 MB. The file is uploaded to the server.</small></div>

<div class="input-group"><label for="notice-expiry">Expiry date (optional)</label><input id="notice-expiry" name="expires_at" type="date"/><small class="ev-notice-help">Leave empty for no expiry. After this date the notice is hidden from students.</small></div>

<div class="ev-notice-stamp"><i class="fa-regular fa-clock"></i><div><strong>Will be posted on</strong><div id="notice-stamp"></div><small>(added automatically)</small></div></div>

<div class="form-actions"><button class="add-btn" id="notice-submit" type="submit"><i class="fa-solid fa-paper-plane"></i> Publish notice</button></div>
<p aria-live="polite" class="management-message<?= isset($_GET['error']) ? ' error' : '' ?>" id="notice-message" role="status"><?php
    if (isset($_GET['success']))      echo 'Notice published successfully.';
    elseif (isset($_GET['deleted']))  echo 'Notice deleted.';
    elseif (isset($_GET['error']))    echo 'Please fill in title, category and message.';
?></p>
</form>
</section>

<section aria-label="Published notices" class="card section-space">
<div class="notice-board-toolbar"><h3 class="card-title"><i aria-hidden="true" class="fa-solid fa-bullhorn"></i> Published Notices</h3></div>
<div class="notice-board-list" id="notice-board-list">
<?php if (!$allNotices): ?>
  <p class="management-help">No notices yet. Publish your first notice above.</p>
<?php endif; ?>
<?php foreach ($allNotices as $n):
    $expired = !empty($n['expires_at']) && $n['expires_at'] < $today; ?>
  <article class="notice-board-item">
    <h4><?= htmlspecialchars($n['title']) ?></h4>
    <span class="notice-board-meta"><?= htmlspecialchars($n['created_at']) ?></span>
    <span class="ev-notice-status<?= $expired ? ' expired' : '' ?>"><?= $expired ? 'Expired' : 'Active' ?></span>
    <div class="notice-board-meta"><?= htmlspecialchars($n['category']) ?><?= !empty($n['expires_at']) ? ' · Expires ' . htmlspecialchars($n['expires_at']) : '' ?></div>
    <p><?= nl2br(htmlspecialchars($n['body'])) ?></p>
    <?php if (!empty($n['attachment'])): ?>
      <a class="ev-notice-file" href="../uploads/<?= rawurlencode($n['attachment']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($n['attachment']) ?></a>
    <?php endif; ?>
    <div class="notice-item-actions">
      <form method="post" action="Notice_Board.php" onsubmit="return confirm('Delete this notice?')" style="display:inline">
        <input type="hidden" name="delete_notice" value="1"/>
        <input type="hidden" name="id" value="<?= (int)$n['id'] ?>"/>
        <button aria-label="Delete notice" class="action-btn delete-btn" title="Delete notice" type="submit"><i class="fa-solid fa-trash"></i></button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
</div>
</section>
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
<?php if (isset($_GET['success'])): ?>window.eduVaultLog('success', 'Notice published');<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>window.eduVaultLog('warn', 'Notice deleted');<?php endif; ?>
</script>

<script>window.eduVaultCurrentAdmin={name:<?php echo json_encode(ev_admin_name(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>};</script>
<!-- Admin profile picture menu -->


<!-- Header search + page navigation -->
<script>
(function(){
  'use strict';
  const pages={dashboard:'Dashboard.php',materials:'Study_Materials_Manage_Batch.php',forums:'Discussion_Forums_QA.php',subjects:'Subjects_Categories.php',events:'Events_Announcements.php',notice:'Notice_Board.php',logs:'Activity_Logs.php'};
  const current='notice';

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
    link.addEventListener('click',e=>{
      if(name===current){e.preventDefault();return;}
      e.preventDefault();navigate(name);
    });
  });

  // ---- search ----
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

<!-- Notice form helpers (category pills, counter, file picker). The form itself posts to PHP. -->
<script id="eduvault-notice-board-script">
(function(){
  const $=id=>document.getElementById(id);
  const category=$('notice-category'),description=$('notice-description'),counter=$('notice-counter'),
        fileInput=$('notice-file'),drop=$('notice-drop'),picked=$('notice-picked'),pills=$('notice-category-pills'),
        message=$('notice-message'),expiry=$('notice-expiry');
  if(!category||!description||!fileInput)return;

  const pad=n=>String(n).padStart(2,'0'), d=new Date();
  expiry.min=`${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
  $('notice-stamp').textContent=d.toLocaleString();

  const updatePills=()=>pills.querySelectorAll('button').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.category.toLowerCase()===category.value.trim().toLowerCase())));
  pills.addEventListener('click',e=>{const b=e.target.closest('button[data-category]');if(b){category.value=b.dataset.category;updatePills();}});
  category.addEventListener('input',updatePills);
  description.addEventListener('input',()=>counter.textContent=description.value.length+'/1500');

  function showPicked(){
    picked.replaceChildren();
    const f=fileInput.files[0];
    picked.hidden=!f;drop.hidden=!!f;
    if(!f)return;
    const icon=document.createElement('i');icon.className='fa-solid fa-paperclip';
    const t=document.createElement('span');t.textContent=' '+f.name+' ('+Math.ceil(f.size/1024)+' KB) ';
    const x=document.createElement('button');x.type='button';x.setAttribute('aria-label','Remove attachment');x.innerHTML='<i class="fa-solid fa-xmark"></i>';
    x.onclick=()=>{fileInput.value='';showPicked();};
    picked.append(icon,t,x);
  }
  function setFile(f){
    if(!f)return;
    if(!(f.type==='application/pdf'||f.type.startsWith('image/'))){message.textContent='Only PDF or image files are supported.';fileInput.value='';showPicked();return;}
    if(f.size>10*1024*1024){message.textContent='Attachment must be smaller than 10 MB.';fileInput.value='';showPicked();return;}
    if(fileInput.files[0]!==f){const dt=new DataTransfer();dt.items.add(f);fileInput.files=dt.files;}
    message.textContent='';showPicked();
  }
  fileInput.addEventListener('change',()=>setFile(fileInput.files[0]));
  ['dragover','dragenter'].forEach(t=>drop.addEventListener(t,e=>{e.preventDefault();drop.classList.add('dragging');}));
  ['dragleave','drop'].forEach(t=>drop.addEventListener(t,e=>{e.preventDefault();drop.classList.remove('dragging');}));
  drop.addEventListener('drop',e=>setFile(e.dataTransfer.files[0]));

  updatePills();
})();
</script>
</body>
</html>