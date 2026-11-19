<?php require __DIR__ . '/_auth.php'; ?>
<!DOCTYPE html>

<html data-eduvault-page="subjects" data-eduvault-theme="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>EduVault | Subjects &amp; Categories</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
<link href="eduvault-design-system.css" rel="stylesheet"/><script id="eduvault-navigation-state">(function(){try{var s=sessionStorage;var via=s.getItem('ev_internal_navigation')==='1';if(via){document.documentElement.classList.add('ev-internal-navigation');s.removeItem('ev_internal_navigation');}var collapsed=s.getItem('ev_sidebar_collapsed');if(collapsed==='0')document.documentElement.classList.add('ev-sidebar-was-open');}catch(e){}})();</script>
<?php ev_head(); ?>

<!-- Show the EduVault welcome screen only once per authenticated browser session. -->
<style>html.ev-admin-welcome-seen .ev-welcome{display:none!important;animation:none!important}</style>
<script src="eduvault-admin-profile-animation.js" data-ev-session="<?php echo htmlspecialchars((string)($_SESSION['ev_csrf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
</head>
<body>
<div aria-hidden="true" class="ev-welcome"><div class="ev-welcome-inner"><div class="ev-welcome-symbol"><i class="fa-solid fa-graduation-cap"></i></div><div class="ev-welcome-name">Edu<span>Vault</span></div><div class="ev-welcome-sub">Admin Command Center</div><div class="ev-welcome-rule"></div></div></div>
<div class="admin-app"><aside aria-label="Admin navigation" class="side-navigation" id="side-navigation"><div class="sidebar-brand"><i class="fa-solid fa-graduation-cap"></i><span>Edu<span class="brand-accent">Vault</span><small>ADMIN PANEL</small></span></div><button aria-controls="side-navigation" aria-expanded="true" aria-label="Collapse sidebar" class="sidebar-collapse-toggle" id="sidebar-collapse-toggle" title="Collapse sidebar" type="button"><i aria-hidden="true" class="fa-solid fa-bars"></i></button><nav class="sidebar-links"><a aria-current="page" class="sidebar-link" data-target="dashboard" href="Dashboard.php"><i aria-hidden="true" class="fa-solid fa-gauge-high"></i> Dashboard</a><a aria-current="false" class="sidebar-link" data-target="materials" href="Study_Materials_Manage_Batch.php"><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials &amp; Manage Batch</a><a aria-current="false" class="sidebar-link" data-target="forums" href="Discussion_Forums_QA.php"><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</a><a aria-current="false" class="sidebar-link active" data-target="subjects" href="Subjects_Categories.php"><i aria-hidden="true" class="fa-solid fa-book"></i> Subjects &amp; Categories</a><a aria-current="false" class="sidebar-link" data-target="events" href="Events_Announcements.php"><i aria-hidden="true" class="fa-solid fa-calendar-days"></i> Events &amp; Announcements</a><a aria-current="false" class="sidebar-link" data-target="notice" href="Notice_Board.php"><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</a><a aria-current="false" class="sidebar-link" data-target="logs" href="Activity_Logs.php"><i aria-hidden="true" class="fa-solid fa-terminal"></i> Activity Logs</a></nav><div class="sidebar-bottom"><a class="sidebar-link" href="../index.php" id="sidebar-home"><i class="fa-solid fa-house"></i> Student Dashboard</a><a class="sidebar-link sidebar-signout" href="logout.php" id="sidebar-signout"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
<main class="main-content">
<!-- HEADER -->
<header class="topbar">
<div class="header-brand">
<div class="brand-icon">
<i class="fa-solid fa-graduation-cap"></i>
</div>
<div class="brand-text">
<h2>
                Edu<span>Vault</span>
</h2>
<p>
                Admin Command Center
            </p>
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
</header><div class="workspace-view" data-view="dashboard" hidden="" id="view-dashboard"><section class="page-heading">
<h1>
        Admin Dashboard
    </h1>
<p>
        NSBM Green University - Faculty of Computing
    </p>
</section><section class="stats">
<div class="card stat-card" data-stat="users" data-target-view="users" role="button" tabindex="0" title="Go to Manage Users">
<div class="stat-icon blue">
<i class="fa-solid fa-users"></i>
</div>
<div>
<p>
                Registered Users
            </p>
<h2 data-stat-value="users">
                1,248
            </h2>
<span class="up">From saved dashboard records</span>
</div>
</div>
<div class="card stat-card" data-stat="materials" data-target-view="materials" role="button" tabindex="0" title="Go to Study Materials">
<div class="stat-icon purple">
<i class="fa-solid fa-file-lines"></i>
</div>
<div>
<p>
                Uploaded Materials
            </p>
<h2 data-stat-value="materials">
                684
            </h2>
<span class="up">From saved dashboard records</span>
</div>
</div>
<div class="card stat-card" data-stat="pending" data-target-view="materials" role="button" tabindex="0" title="Go to Pending Study Materials">
<div class="stat-icon orange">
<i class="fa-solid fa-clock"></i>
</div>
<div>
<p>
                Pending Approval
            </p>
<h2 data-stat-value="pending">
                26
            </h2>
<span class="warning">Needs review</span>
</div>
</div>
<div class="card stat-card" data-stat="forums" data-target-view="forums" role="button" tabindex="0" title="Go to Discussion Forums">
<div class="stat-icon green">
<i class="fa-solid fa-comments"></i>
</div>
<div>
<p>
                Forum Posts
            </p>
<h2 data-stat-value="forums">
                391
            </h2>
<span class="up">From saved dashboard records</span>
</div>
</div>
</section><section class="grid-2">
<div class="card">
<h3 class="card-title">
<i class="fa-solid fa-chart-line"></i>
            Resource Usage / Engagement Report
        </h3>
<div aria-label="Weekly engagement trend: Monday 80, Tuesday 120, Wednesday 100, Thursday 160, Friday 140, Saturday 180, Sunday 150" class="report-chart-wrap reference-line-chart" role="img">
<svg aria-hidden="true" preserveaspectratio="xMidYMid meet" viewbox="0 0 820 310">
<defs><lineargradient id="engagementLine" x1="0" x2="1" y1="0" y2="0"><stop offset="0%" stop-color="#f05a9d"></stop><stop offset="100%" stop-color="#b56aff"></stop></lineargradient><marker id="chartArrow" markerheight="5" markerwidth="5" orient="auto-start-reverse" refx="8" refy="5" viewbox="0 0 10 10"><path d="M0 0 L10 5 L0 10 Z" fill="#93a8de"></path></marker></defs>
<line class="chart-gridline" x1="86" x2="750" y1="245.0" y2="245.0"></line>
<text class="chart-axis-label" text-anchor="end" x="72" y="249.0">0</text>
<line class="chart-gridline" x1="86" x2="750" y1="204.2" y2="204.2"></line>
<text class="chart-axis-label" text-anchor="end" x="72" y="208.2">40</text>
<line class="chart-gridline" x1="86" x2="750" y1="163.4" y2="163.4"></line>
<text class="chart-axis-label" text-anchor="end" x="72" y="167.4">80</text>
<line class="chart-gridline" x1="86" x2="750" y1="122.6" y2="122.6"></line>
<text class="chart-axis-label" text-anchor="end" x="72" y="126.6">120</text>
<line class="chart-gridline" x1="86" x2="750" y1="81.8" y2="81.8"></line>
<text class="chart-axis-label" text-anchor="end" x="72" y="85.8">160</text>
<line class="chart-gridline" x1="86" x2="750" y1="41.0" y2="41.0"></line>
<text class="chart-axis-label" text-anchor="end" x="72" y="45.0">200</text>
<line class="chart-gridline" x1="100" x2="100" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="100" y="276">Mon</text>
<line class="chart-gridline" x1="205" x2="205" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="205" y="276">Tue</text>
<line class="chart-gridline" x1="310" x2="310" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="310" y="276">Wed</text>
<line class="chart-gridline" x1="415" x2="415" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="415" y="276">Thu</text>
<line class="chart-gridline" x1="520" x2="520" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="520" y="276">Fri</text>
<line class="chart-gridline" x1="625" x2="625" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="625" y="276">Sat</text>
<line class="chart-gridline" x1="730" x2="730" y1="41" y2="245"></line>
<text class="chart-axis-label" text-anchor="middle" x="730" y="276">Sun</text>
<line class="chart-axis" marker-end="url(#chartArrow)" x1="86" x2="773" y1="245" y2="245"></line><line class="chart-axis" marker-end="url(#chartArrow)" x1="86" x2="86" y1="245" y2="23"></line>
<text class="chart-axis-title" text-anchor="middle" x="420" y="304">Day of Week</text><text class="chart-axis-title" text-anchor="middle" transform="rotate(-90,22,145)" x="22" y="145">Engagement</text>
<polyline class="reference-trend-line" points="100,163.4 205,122.6 310,143.0 415,81.8 520,102.2 625,61.4 730,92.0"></polyline>
<g class="chart-point"><circle class="chart-dot-halo" cx="100" cy="163.4" r="11"></circle><circle class="chart-dot" cx="100" cy="163.4" r="6"></circle><title>Mon: 80 engagements</title></g>
<g class="chart-point"><circle class="chart-dot-halo" cx="205" cy="122.6" r="11"></circle><circle class="chart-dot" cx="205" cy="122.6" r="6"></circle><title>Tue: 120 engagements</title></g>
<g class="chart-point"><circle class="chart-dot-halo" cx="310" cy="143.0" r="11"></circle><circle class="chart-dot" cx="310" cy="143.0" r="6"></circle><title>Wed: 100 engagements</title></g>
<g class="chart-point"><circle class="chart-dot-halo" cx="415" cy="81.8" r="11"></circle><circle class="chart-dot" cx="415" cy="81.8" r="6"></circle><title>Thu: 160 engagements</title></g>
<g class="chart-point"><circle class="chart-dot-halo" cx="520" cy="102.2" r="11"></circle><circle class="chart-dot" cx="520" cy="102.2" r="6"></circle><title>Fri: 140 engagements</title></g>
<g class="chart-point"><circle class="chart-dot-halo" cx="625" cy="61.4" r="11"></circle><circle class="chart-dot" cx="625" cy="61.4" r="6"></circle><title>Sat: 180 engagements</title></g>
<g class="chart-point"><circle class="chart-dot-halo" cx="730" cy="92.0" r="11"></circle><circle class="chart-dot" cx="730" cy="92.0" r="6"></circle><title>Sun: 150 engagements</title></g>
</svg>
</div>
</div>
<!-- ACTIVITY -->
<div class="card">
<h3 class="card-title">
<i class="fa-solid fa-chart-pie"></i>
            Activity Overview
        </h3>
<div class="donut">
<div class="donut-inner">
<span>Study Materials</span>
<strong id="overview-total">
                    1248
                </strong>
</div>
</div><div aria-live="polite" class="overview-legend" id="overview-legend"></div>
<div class="report-actions">
<button class="report-btn" id="report-pdf" title="Download PDF report" type="button">
<i class="fa-solid fa-file-pdf"></i>
                PDF Report
            </button>
<button class="report-btn" id="report-excel" type="button">
<i class="fa-solid fa-file-excel"></i>
                Excel Report
            </button>
<button class="report-btn" id="report-print" type="button">
<i class="fa-solid fa-print"></i>
                Print
            </button>
</div><p class="report-help">Reports use records stored in this browser. PDF downloads directly. Print lets you choose a paper size and orientation.</p>
</div>
</section><section aria-label="Manage Users" class="dashboard-users-section" id="dashboard-manage-users"><div class="workspace-heading"><h2><i aria-hidden="true" class="fa-solid fa-users"></i> Manage Users</h2><p>Manage student accounts and access</p></div><div class="card">
<h3 class="card-title">
<i class="fa-solid fa-users"></i>
            Manage Users
        </h3>
<form class="eduvault-form" hidden="" id="user-edit-form"><strong>Edit User</strong><div class="eduvault-form-row"><label>Name<input id="user-edit-name" maxlength="100" required=""/></label><label>Role<select id="user-edit-role"><option>Student</option><option>Administrator</option><option>Moderator</option></select></label></div><div class="eduvault-form-actions"><button type="submit">Save Changes</button><button class="cancel" id="user-edit-cancel" type="button">Cancel</button></div></form><p aria-live="polite" class="management-message" id="users-status" role="status"></p><div class="table-wrapper">
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody id="users-table"></tbody>
</table>
</div>
</div></section></div><div class="workspace-view" data-view="materials" hidden="" id="view-materials"><div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials</h1><p>EduVault administrator workspace</p></div><section class="card section-space">
<h3 class="card-title">
<i class="fa-solid fa-file-circle-check"></i>
        Approve Uploaded Notes / Study Materials
    </h3>
<p aria-live="polite" class="management-message" id="materials-status" role="status"></p><div class="table-wrapper">
<table>
<thead>
<tr>
<th>Title</th>
<th>Subject</th>
<th>Uploaded By</th>
<th>Date</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody id="materials-table"></tbody>
</table>
</div>
</section>
<section class="card section-space" id="batches-section">
<h3 class="card-title"><i aria-hidden="true" class="fa-solid fa-plus-circle"></i> Create Batch</h3>
<form class="batch-creation-form" id="batch-create-form">
<div class="input-group"><label for="new-batch-name">Batch Name</label><input autocomplete="off" id="new-batch-name" maxlength="75" name="batch" placeholder="e.g. 27.1 IFSLS" required=""/></div>
<button class="add-btn" type="submit"><i aria-hidden="true" class="fa-solid fa-plus"></i> Create Batch</button>
</form>
<p aria-live="polite" class="management-message" id="batch-message" role="status"></p>
<div class="batch-management-toolbar"><label for="admin-batch-select">Current Batch</label><select aria-label="Select current batch" id="admin-batch-select"></select></div>
<div class="management-list"><h4>Available Batches</h4><div class="table-wrapper"><table><thead><tr><th>Batch</th><th>Action</th></tr></thead><tbody id="batch-table"></tbody></table></div></div>
</section>
</div><div class="workspace-view" data-view="forums" hidden="" id="view-forums"><div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</h1><p>EduVault administrator workspace</p></div><div class="card" id="forums-section">
<h3 class="card-title"><i class="fa-solid fa-comments"></i> Moderate Discussion Forums &amp; Q&amp;A</h3>
<p class="management-help">Create discussion posts and questions, review replies, and moderate existing posts.</p>
<form class="management-form" data-post-type="discussion" id="forum-form">
<div class="forum-type-select"><label for="forum-type">Choose Post Type</label><select aria-label="Choose Discussion Forum or Q&amp;A" id="forum-type" name="type"><option value="discussion">Discussion Forum</option><option value="qa">Q&amp;A</option></select><p id="forum-type-help">Start an open discussion for students to share ideas and replies.</p></div>
<div class="input-group"><label for="forum-title" id="forum-title-label">Discussion Topic</label><input id="forum-title" maxlength="160" name="title" placeholder="e.g. How do PHP sessions work?" required=""/></div>
<div class="form-row">
<div class="input-group"><label for="forum-user">Posted By</label><input id="forum-user" maxlength="100" name="user" placeholder="e.g. Admin" required=""/></div>
<div class="input-group"><label for="forum-category">Category</label><input autocomplete="off" id="forum-category" maxlength="100" name="category" placeholder="Type a category (e.g. Programming)" required="" type="text"/></div>
</div>
<div class="input-group"><label for="forum-description" id="forum-description-label">Discussion Details</label><textarea id="forum-description" maxlength="2000" name="description" placeholder="Enter discussion or question details" required=""></textarea></div>
<div class="input-group" hidden="" id="forum-photo-group"><label for="forum-photo">Q&amp;A Intro Photo (optional)</label><input accept="image/png,image/jpeg,image/webp,image/gif" id="forum-photo" type="file"/><div class="qa-photo-preview" hidden="" id="forum-photo-preview"><img alt="Q&amp;A introductory photo preview" id="forum-photo-image"/><button class="cancel-edit" id="forum-photo-remove" type="button">Remove Photo</button></div><small class="forum-photo-help">Upload an image for the question. Max 800 KB. Stored locally in this browser.</small></div>
<input id="forum-answer" name="answer" type="hidden" value=""/>
<div class="form-actions"><button class="add-btn" id="forum-submit" type="submit"><i class="fa-solid fa-plus"></i> Create Discussion</button><button class="cancel-edit" id="forum-cancel" type="button">Cancel Edit</button></div>
<p class="editing-indicator">Editing existing discussion. Save changes or cancel.</p>
<p aria-live="polite" class="management-message" id="forum-message" role="status"></p>
</form>
<div class="management-list"><h4>Discussion Posts &amp; Questions</h4>
<div class="table-wrapper records-scroll" id="forum-scroll"><table class="management-table"><thead><tr><th>Question / Post</th><th>User</th><th>Status</th><th>Action</th></tr></thead><tbody id="forum-table"></tbody></table></div>
<p aria-live="polite" class="management-list-count" id="forum-list-count"></p>
</div>
</div></div><div class="workspace-view" data-view="subjects" id="view-subjects">
<div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-book"></i> Subjects &amp; Categories</h1><p>EduVault administrator workspace</p></div>
<section class="card section-space" id="subjects-section">
<h3 class="card-title"><i class="fa-solid fa-book"></i> Manage Subjects &amp; Categories</h3>
<form class="management-form" id="subject-form">
<div class="form-row">
<div class="input-group"><label for="subject-name">Subject Name</label><input id="subject-name" maxlength="120" name="name" placeholder="Enter subject name" required="" type="text"/></div>
<div class="input-group"><label for="subject-category">Category</label><input autocomplete="off" id="subject-category" maxlength="100" name="category" placeholder="Type a category (e.g. Programming)" required="" type="text"/></div>
</div>
<div class="form-actions"><button class="add-btn" id="subject-submit" type="submit"><i class="fa-solid fa-plus"></i> Add Subject</button><button class="cancel-edit" hidden="" id="subject-cancel" type="button">Cancel Edit</button></div>
<p aria-live="polite" class="management-message" id="subject-message" role="status"></p>
</form>
<div class="table-wrapper subject-table"><table><thead><tr><th>ID</th><th>Subject</th><th>Category</th><th>Materials</th><th>Action</th></tr></thead><tbody id="subjects-table"></tbody></table></div>
</section></div><div class="workspace-view" data-view="events" hidden="" id="view-events"><div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-calendar-days"></i> Events &amp; Announcements</h1><p>Create and manage events or announcements from one page.</p></div>
<div class="card combined-type-panel"><div class="input-group"><label for="content-type-switch">Select Type</label><select aria-label="Choose Events or Announcements" id="content-type-switch"><option value="events">Events</option><option value="announcements">Announcements</option></select></div></div>
<div id="combined-events-panel"><div class="card" id="events-section">
<h3 class="card-title"><i class="fa-solid fa-calendar-plus"></i> Create Event</h3>
<p class="management-help">Schedule upcoming university activities and events for students.</p>
<form class="management-form" id="event-form">
<div class="input-group"><label for="event-title">Event Title</label><input id="event-title" maxlength="120" name="title" placeholder="e.g. Computing Workshop" required="" type="text"/></div>
<div class="form-row">
<div class="input-group"><label for="event-date">Event Date</label><input id="event-date" name="date" required="" type="date"/></div>
<div class="input-group"><label for="event-time">Event Time</label><input id="event-time" name="time" required="" type="time"/></div>
</div>
<div class="input-group"><label for="event-description">Description</label><textarea id="event-description" maxlength="800" name="description" placeholder="Brief details about the event" required=""></textarea></div>
<div class="form-actions"><button class="add-btn" id="event-submit" type="submit"><i class="fa-solid fa-plus"></i> Create Event</button><button class="cancel-edit" id="event-cancel" type="button">Cancel Edit</button></div><p class="editing-indicator">Editing existing event. Save changes or cancel.</p>
<p aria-live="polite" class="management-message" id="event-message" role="status"></p>
</form>
<div class="management-list"><h4>Created Events</h4>
<div class="table-wrapper records-scroll"><table class="management-table"><thead><tr><th>Event</th><th>Date &amp; Time</th><th>Action</th></tr></thead><tbody id="events-table"></tbody></table></div><p aria-live="polite" class="management-list-count" id="event-list-count"></p>
</div>
</div></div><div hidden="" id="combined-announcements-panel"><div class="card" id="announcements-section">
<h3 class="card-title"><i class="fa-solid fa-bullhorn"></i> Create Announcement</h3>
<p class="management-help">Post important notices, updates, and reminders for students.</p>
<form class="management-form" id="announcement-form">
<div class="input-group"><label for="announcement-title">Announcement Title</label><input id="announcement-title" maxlength="120" name="title" placeholder="e.g. Semester Registration Open" required="" type="text"/></div>
<div class="form-row">
<div class="input-group"><label for="announcement-category">Category</label><select id="announcement-category" name="category" required=""><option value="">Select Category</option><option>General</option><option>Academic</option><option>Examinations</option><option>Events</option><option>Important Notice</option></select></div>
<div class="input-group"><label for="announcement-date">Publish Date</label><input id="announcement-date" name="date" required="" type="date"/></div>
</div>
<div class="input-group"><label for="announcement-description">Announcement Message</label><textarea id="announcement-description" maxlength="1500" name="description" placeholder="Write the announcement details" required=""></textarea></div>
<div class="form-actions"><button class="add-btn" id="announcement-submit" type="submit"><i class="fa-solid fa-plus"></i> Create Announcement</button><button class="cancel-edit" id="announcement-cancel" type="button">Cancel Edit</button></div><p class="editing-indicator">Editing existing announcement. Save changes or cancel.</p>
<p aria-live="polite" class="management-message" id="announcement-message" role="status"></p>
</form>
<div class="management-list"><h4>Created Announcements</h4>
<div class="table-wrapper records-scroll"><table class="management-table"><thead><tr><th>Announcement</th><th>Publish Date</th><th>Action</th></tr></thead><tbody id="announcements-table"></tbody></table></div><p aria-live="polite" class="management-list-count" id="announcement-list-count"></p>
</div>
</div></div></div><div class="workspace-view" data-view="notice" hidden="" id="view-notice">
<div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</h1><p>Latest notices and important announcements for the student community.</p></div>
<section aria-label="Create or edit notice" class="card section-space">
<h3 class="card-title"><i aria-hidden="true" class="fa-solid fa-pen-to-square"></i> Create Notice</h3>
<p class="management-help">Create and manage notices directly from this page.</p>
<form class="management-form" id="notice-form">
<div class="input-group"><label for="notice-title">Title</label><input id="notice-title" maxlength="120" placeholder="e.g. Final exam timetable released" required=""/></div>
<div class="input-group"><label for="notice-category">Category</label><div aria-label="Common notice categories" class="ev-notice-categories" id="notice-category-pills" role="group">
<button data-category="General" type="button"><i class="fa-solid fa-clipboard"></i> General</button><button data-category="Academic" type="button"><i class="fa-solid fa-book"></i> Academic</button><button data-category="Exams" type="button"><i class="fa-solid fa-pen-to-square"></i> Exams</button><button data-category="Events" type="button"><i class="fa-solid fa-calendar"></i> Events</button><button data-category="Urgent" type="button"><i class="fa-solid fa-triangle-exclamation"></i> Urgent</button></div><input id="notice-category" maxlength="80" placeholder="Or type your own category" required="" type="text"/></div>
<div class="input-group"><label for="notice-description">Message</label><textarea id="notice-description" maxlength="1500" placeholder="Write the full notice here..." required=""></textarea><span class="ev-notice-counter" id="notice-counter">0/1500</span></div>
<div class="input-group"><label for="notice-file">Attachment (optional · PDF or image, max 10 MB)</label><label class="ev-notice-drop" for="notice-file" id="notice-drop"><i class="fa-solid fa-cloud-arrow-up"></i><span>Click to choose a file or drag it here</span></label><input accept="application/pdf,image/*" hidden="" id="notice-file" type="file"/><div class="ev-notice-picked" hidden="" id="notice-picked"></div><small class="ev-notice-help">For this offline dashboard, attachments up to 400 KB can be saved in the browser. Larger files are not supported without a backend.</small></div>
<div class="input-group"><label for="notice-expiry">Expiry date (optional)</label><input id="notice-expiry" type="date"/><small class="ev-notice-help">Leave empty for no expiry. After this date the notice is marked expired and hidden from the active list.</small></div>
<div class="ev-notice-stamp"><i class="fa-regular fa-clock"></i><div><strong id="notice-stamp-title">Will be posted on</strong><div id="notice-stamp"></div><small>(added automatically)</small></div></div>
<div class="form-actions"><button class="add-btn" id="notice-submit" type="submit"><i class="fa-solid fa-paper-plane"></i> Publish notice</button><button class="cancel-edit" hidden="" id="notice-cancel" type="button">Cancel Edit</button></div>
<p aria-live="polite" class="management-message" id="notice-message" role="status"></p>
</form>
</section>
<section aria-label="Published notices" class="card section-space">
<div class="notice-board-toolbar"><h3 class="card-title"><i aria-hidden="true" class="fa-solid fa-bullhorn"></i> Published Notices</h3></div>
<div aria-live="polite" class="notice-board-list" id="notice-board-list"><p class="management-help">No notices to display yet.</p></div>
</section></div><div class="workspace-view" data-view="logs" hidden="" id="view-logs"><div class="workspace-heading"><h1><i aria-hidden="true" class="fa-solid fa-terminal"></i> Activity Logs</h1><p>EduVault administrator workspace</p></div><section aria-label="Activity log console" class="card section-space log-console">
<h3 class="card-title"><i class="fa-solid fa-terminal"></i> Activity Logs Console</h3>
<p class="log-caption">Search dashboard activity recorded in this browser. Server and PHP logs require a separate secure backend connection.</p>
<div class="log-toolbar">
<input aria-label="Search logs" id="log-search" placeholder="Search logs by message..." type="search"/>
<select aria-label="Filter log level" id="log-level-filter">
<option value="all">All levels</option><option value="info">Info</option><option value="success">Success</option><option value="warn">Warning</option><option value="error">Error</option>
</select>
<button id="log-export" type="button"><i class="fa-solid fa-download"></i> Export</button>
<button id="log-clear" type="button"><i class="fa-solid fa-trash"></i> Clear Logs</button>
</div>
<div aria-live="polite" aria-relevant="additions text" class="log-output" id="log-output" role="log"></div>
<p class="log-count" id="log-count"></p>
</section></div>
<!-- PAGE TITLE -->
<!-- STATISTICS -->
<!-- REPORT SECTION -->
<!-- APPROVE MATERIALS -->
<!-- USERS & FORUM -->
<!-- SUBJECT MANAGEMENT -->
<!-- EVENTS AND ANNOUNCEMENTS -->
<!-- ACTIVITY LOG CONSOLE -->
<!-- Global dashboard search: searches content available on this page only -->
<!-- FOOTER -->
<footer class="footer">
    EduVault Admin Command Center
    |
    NSBM Green University - Faculty of Computing
</footer>
<div aria-labelledby="print-paper-heading" aria-modal="true" hidden="" id="print-paper-dialog" role="dialog">
<form class="print-paper-card" id="print-paper-form">
<h2 id="print-paper-heading"><i aria-hidden="true" class="fa-solid fa-print"></i> Print Settings</h2>
<p>Choose paper size and orientation before opening the print preview.</p>
<label for="print-page-size">Paper size</label>
<select id="print-page-size" name="paper-size">
<option selected="" value="A4">A4 (210 × 297 mm)</option>
<option value="A5">A5 (148 × 210 mm)</option>
<option value="Letter">US Letter (8.5 × 11 in)</option>
<option value="Legal">US Legal (8.5 × 14 in)</option>
</select>
<label for="print-orientation">Orientation</label>
<select id="print-orientation" name="orientation"><option value="portrait">Portrait</option><option value="landscape">Landscape</option></select>
<div class="print-paper-actions"><button data-close-print="" type="button">Cancel</button><button type="submit"><i aria-hidden="true" class="fa-solid fa-print"></i> Open Print Preview</button></div>
</form>
</div>
</main>
</div>
<script>
(function () {
    "use strict";
    const key = "eduvault_activity_logs_v1";
    const output = document.getElementById("log-output");
    const count = document.getElementById("log-count");
    const search = document.getElementById("log-search");
    const filter = document.getElementById("log-level-filter");
    let records = [];
    try {
        const stored = JSON.parse(localStorage.getItem(key) || "[]");
        if (Array.isArray(stored)) records = stored.filter(item => item && typeof item.message === "string" && typeof item.time === "string" && ["info","success","warn","error"].includes(item.level)).slice(0,300);
    } catch (_) { /* Browser storage may be unavailable */ }
    function persist() {
        try { localStorage.setItem(key,JSON.stringify(records)); } catch (_) { /* Display still works without storage */ }
    }
    function render() {
        const term = search.value.trim().toLowerCase();
        const level = filter.value;
        const results = records.filter(item => (level === "all" || item.level === level) && (item.message.toLowerCase().includes(term) || item.level.includes(term) || item.time.toLowerCase().includes(term)));
        const fragment = document.createDocumentFragment();
        results.forEach(item => {
            const row=document.createElement("div"); row.className="log-line";
            const time=document.createElement("span"); time.className="log-time"; time.textContent=new Date(item.time).toLocaleString();
            const badge=document.createElement("span"); badge.className="log-level "+item.level; badge.textContent=item.level.toUpperCase();
            const message=document.createElement("span"); message.textContent=item.message;
            row.append(time,badge,message);fragment.append(row);
        });
        if (!results.length) {const empty=document.createElement("p");empty.className="log-empty";empty.textContent="No matching logs found.";fragment.append(empty);}
        output.replaceChildren(fragment);
        count.textContent=`Showing ${results.length} of ${records.length} logged events (latest 300 stored).`;
    }
    window.eduVaultLog = function(level,message) {
        if (!["info","success","warn","error"].includes(level)) level="info";
        records.unshift({time:new Date().toISOString(),level,message:String(message).slice(0,500)});
        records=records.slice(0,300);persist();render();
    };
    search.addEventListener("input",render);filter.addEventListener("change",render);
    document.getElementById("log-clear").addEventListener("click",()=>{
        if (!confirm("Clear browser activity logs?")) return;
        records=[];persist();render();
    });
    document.getElementById("log-export").addEventListener("click",()=>{
        const blob=new Blob([JSON.stringify(records,null,2)],{type:"application/json"});
        const url=URL.createObjectURL(blob);const a=document.createElement("a");a.href=url;a.download="eduvault-activity-logs.json";document.body.append(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),1000);
    });
    window.addEventListener("error",event=>window.eduVaultLog("error", "JavaScript error: "+event.message));
    window.addEventListener("unhandledrejection",event=>window.eduVaultLog("error", "Unhandled promise rejection: "+String(event.reason)));
    render();
    window.eduVaultLog("info","Admin dashboard opened");
})();
</script>
<script>
(function () {
    "use strict";
    // Existing localStorage keys remain unchanged so previously created records survive.
    const configs = {
        event: {form:"event-form",table:"events-table",message:"event-message",key:"eduvault_admin_events_v1",noun:"Event"},
        announcement: {form:"announcement-form",table:"announcements-table",message:"announcement-message",key:"eduvault_admin_announcements_v1",noun:"Announcement"}
    };
    const entries = {};
    const editing = {event:null,announcement:null};
    const make = (tag,text,cls) => {const el=document.createElement(tag);if(cls)el.className=cls;if(text!==undefined)el.textContent=text;return el;};
    const formattedDate = date => /^\d{4}-\d{2}-\d{2}$/.test(date||"") ? date.split("-").reverse().join("/") : (date||"");
    function showMessage(type,msg,isError=false){const el=document.getElementById(configs[type].message);el.textContent=msg;el.classList.toggle("error",isError);}
    function load(type){try{const result=JSON.parse(localStorage.getItem(configs[type].key)||"[]");return Array.isArray(result)?result:[];}catch(_){return [];}}
    function save(type){try{localStorage.setItem(configs[type].key,JSON.stringify(entries[type]));return true;}catch(_){showMessage(type,"Could not save. Check browser storage permissions.",true);window.eduVaultLog?.("error","Failed to save "+type);return false;}}
    function resetEdit(type){
        editing[type]=null;
        const form=document.getElementById(configs[type].form);form.classList.remove("is-editing");form.reset();
        document.getElementById(type+"-submit").innerHTML='<i class="fa-solid fa-plus"></i> Create '+configs[type].noun;
        if(type==="announcement")document.getElementById("announcement-date").valueAsDate=new Date();
    }
    function beginEdit(type,item){
        editing[type]=item.id;
        const form=document.getElementById(configs[type].form);
        for(const field of ["title","date","description",...(type==="event"?["time"]:["category"])]){
            const control=form.elements.namedItem(field);
            if(control)control.value=item[field]||"";
        }
        form.classList.add("is-editing");
        document.getElementById(type+"-submit").innerHTML='<i class="fa-solid fa-floppy-disk"></i> Save Changes';
        showMessage(type,"Editing: "+item.title);
        form.scrollIntoView({behavior:"smooth",block:"center"});
        form.elements.namedItem("title").focus({preventScroll:true});
    }
    function adjustHeight(type){
        const tbody=document.getElementById(configs[type].table);
        const scroller=tbody.closest(".records-scroll");
        const rows=[...tbody.rows];
        const header=scroller.querySelector("thead");
        // Header + exactly two visible rows; all remaining rows are available by scrolling.
        const visible=rows.slice(0,2).reduce((total,row)=>total+row.getBoundingClientRect().height,0);
        scroller.style.maxHeight=Math.ceil(header.getBoundingClientRect().height+visible+2)+"px";
        scroller.scrollTop=0;
        const count=document.getElementById(type+"-list-count");
        count.textContent=entries[type].length>2 ? `Showing the latest 2 of ${entries[type].length} ${type==="event"?"events":"announcements"}. Scroll inside the list to view older entries.` : `${entries[type].length} ${type==="event"?"event(s)":"announcement(s)"}`;
    }
    function render(type){
        const tbody=document.getElementById(configs[type].table);
        tbody.replaceChildren();
        if(!entries[type].length){
            const tr=make("tr"),td=make("td",type==="event"?"No events created yet.":"No announcements created yet.","management-empty");td.colSpan=3;tr.append(td);tbody.append(tr);adjustHeight(type);return;
        }
        for(const item of entries[type]){
            const tr=make("tr");
            const details=make("td");
            details.append(make("strong",item.title),make("p",item.description,"row-description"));
            if(type!=="event") details.append(make("p",item.category,"row-description"));
            const dateCell=make("td",formattedDate(item.date)+(type==="event"?" "+(item.time||""):""));
            const actions=make("td");
            const edit=make("button",undefined,"action-btn edit-btn");edit.type="button";edit.title="Edit "+type;edit.setAttribute("aria-label","Edit "+type+": "+item.title);edit.innerHTML='<i class="fa-solid fa-pen" aria-hidden="true"></i>';
            edit.addEventListener("click",()=>beginEdit(type,item));
            const remove=make("button",undefined,"action-btn delete-btn");remove.type="button";remove.title="Delete "+type;remove.setAttribute("aria-label","Delete "+type+": "+item.title);remove.innerHTML='<i class="fa-solid fa-trash" aria-hidden="true"></i>';
            remove.addEventListener("click",()=>{
                if(!window.confirm("Delete "+item.title+"?"))return;
                const previous=entries[type].slice();
                entries[type]=entries[type].filter(row=>row.id!==item.id);
                if(!save(type)){entries[type]=previous;return;}
                if(editing[type]===item.id)resetEdit(type);
                render(type);showMessage(type,"Deleted successfully.");
                window.eduVaultLog?.("warn",configs[type].noun+" deleted: "+item.title);
            });
            actions.append(edit,remove);tr.append(details,dateCell,actions);tbody.append(tr);
        }
        adjustHeight(type);
    }
    for(const type of Object.keys(configs)){
        entries[type]=load(type);
        const form=document.getElementById(configs[type].form);
        document.getElementById(type+"-cancel").addEventListener("click",()=>{resetEdit(type);showMessage(type,"Editing cancelled.");});
        form.addEventListener("submit",event=>{
            event.preventDefault();
            if(!form.reportValidity())return;
            const data=new FormData(form);
            const update={title:String(data.get("title")||"").trim(),date:String(data.get("date")||""),description:String(data.get("description")||"").trim()};
            if(type==="event")Object.assign(update,{time:String(data.get("time")||"")});
            else update.category=String(data.get("category")||"");
            if(!update.title||!update.description){showMessage(type,"Please complete every field.",true);return;}
            const previous=entries[type].map(item=>({...item}));
            const isEdit=editing[type]!==null;
            if(isEdit){
                const original=entries[type].find(item=>item.id===editing[type]);
                if(!original){resetEdit(type);showMessage(type,"This record no longer exists.",true);return;}
                Object.assign(original,update);
            }else{
                entries[type].unshift({id:crypto?.randomUUID?.()||String(Date.now())+Math.random(),...update});
            }
            if(!save(type)){entries[type]=previous;return;}
            resetEdit(type);render(type);
            showMessage(type,configs[type].noun+(isEdit?" updated":" created")+" successfully.");
            window.eduVaultLog?.("success",configs[type].noun+(isEdit?" updated: ":" created: ")+update.title);
        });
        render(type);
    }
})();
</script>

<script>
(function(){
  'use strict';
  const defaultBatches=['26.1 IFSLS','26.1 FOC','25.1 IFSLS'];
  const key='eduvault_admin_batches_v1', selectionKey='eduvault_admin_selected_batch_v1';
  const selector=document.getElementById('admin-batch-select');
  const table=document.getElementById('batch-table');
  const form=document.getElementById('batch-create-form');
  const input=document.getElementById('new-batch-name');
  const message=document.getElementById('batch-message');
  let batches=defaultBatches.slice();
  try {
    const stored=JSON.parse(localStorage.getItem(key)||'null');
    if(Array.isArray(stored)) batches=[...new Set(stored.filter(x=>typeof x==='string' && x.trim() && x.length<=75))];
  } catch (_) {}
  let selected='all';
  try {selected=localStorage.getItem(selectionKey)||'all';}catch(_){}
  function note(value,error=false){message.textContent=value;message.classList.toggle('error',error);}
  function persist(){try{localStorage.setItem(key,JSON.stringify(batches));return true;}catch(_){note('Unable to save batches in browser storage.',true);return false;}}
  function render(){
    selector.replaceChildren(new Option('All Batches','all'));
    batches.forEach(batch=>selector.add(new Option(batch,batch)));
    if(selected!=='all'&&!batches.includes(selected))selected='all';
    selector.value=selected;
    table.replaceChildren();
    if(!batches.length){const row=table.insertRow(),cell=row.insertCell();cell.colSpan=2;cell.textContent='No batches created.';return;}
    batches.forEach(batch=>{
      const row=table.insertRow(),name=row.insertCell(),actions=row.insertCell();
      name.textContent=batch;
      const choose=document.createElement('button');choose.type='button';choose.className='action-btn view-btn';choose.textContent='Select';choose.setAttribute('aria-label','Select '+batch);
      choose.addEventListener('click',()=>{selected=batch;updateSelection();note('Selected batch: '+batch);});
      const remove=document.createElement('button');remove.type='button';remove.className='action-btn delete-btn';remove.innerHTML='<i class="fa-solid fa-trash" aria-hidden="true"></i>';remove.setAttribute('aria-label','Delete '+batch);remove.title='Delete batch';
      remove.addEventListener('click',()=>{
        if(!confirm('Delete batch '+batch+'?'))return;
        const old=batches.slice();batches=batches.filter(x=>x!==batch);
        if(!persist()){batches=old;return;}
        if(selected===batch)selected='all';updateSelection();note('Batch deleted: '+batch);
        window.eduVaultLog?.('warn','Batch deleted: '+batch);
      });
      actions.append(choose,remove);
    });
  }
  function updateSelection(){try{localStorage.setItem(selectionKey,selected);}catch(_){}render();window.eduVaultLog?.('info','Selected batch: '+(selected==='all'?'All Batches':selected));}
  selector.addEventListener('change',()=>{selected=selector.value;updateSelection();note('Selected batch: '+(selected==='all'?'All Batches':selected));});
  form.addEventListener('submit',event=>{
    event.preventDefault();const value=input.value.trim().replace(/\s+/g,' ');
    if(!value){note('Please enter a batch name.',true);return;}
    if(batches.some(x=>x.toLowerCase()===value.toLowerCase())){note('This batch already exists.',true);return;}
    batches.push(value);
    if(!persist()){batches.pop();return;}
    form.reset();render();note('Batch created: '+value);
    window.eduVaultLog?.('success','Batch created: '+value);
  });
  render();
  // Batch selection is stored locally and does not filter backend records until connected to database data.
})();
</script>
<script>
/* Compact live search suggestions, shown only while typing. */
(function(){
  "use strict";
  const input=document.getElementById('global-search-input');
  const dropdown=document.getElementById('inline-search-dropdown');
  const results=document.getElementById('global-search-results');
  const status=document.getElementById('global-search-status');
  if(!input||!dropdown||!results)return;
  const pages=[
    {label:'Dashboard',view:'dashboard',terms:'home overview'},
    {label:'Study Materials & Manage Batch',view:'materials',terms:'study materials uploaded notes batch'},
    {label:'Manage Users',view:'users',terms:'users students registered users'},
    {label:'Discussion Forums & Q&A',view:'forums',terms:'forum q and a questions discussions'},
    {label:'Subjects & Categories',view:'subjects',terms:'subject category categories'},
    {label:'Events & Announcements',view:'events',terms:'event announcement create'},
    {label:'Manage Batches',view:'materials',section:'batches-section',terms:'batch manage batch'},
    {label:'Activity Logs',view:'logs',terms:'log console activity'}
  ];
  const normalize=value=>String(value||'').toLowerCase().replace(/[^a-z0-9]+/g,' ').trim();
  let matches=[];
  function close(){dropdown.hidden=true;input.setAttribute('aria-expanded','false');}
  function navigate(item){
    close();input.value='';input.blur();
    if(typeof window.eduVaultNavigate==='function')window.eduVaultNavigate(item.view);
    else document.querySelector('.sidebar-link[data-target="'+item.view+'"]')?.click();
    if(item.section)setTimeout(()=>{
      const section=document.getElementById(item.section)||document.querySelector('[id*="batch"][class*="section"]');
      section?.scrollIntoView({behavior:'smooth',block:'start'});
    },70);
  }
  function render(){
    const q=normalize(input.value);
    results.replaceChildren();
    if(!q){matches=[];close();return;}
    matches=pages.filter(item=>normalize(item.label).includes(q)||normalize(item.terms).split(' ').some(word=>word.startsWith(q)));
    dropdown.hidden=false;input.setAttribute('aria-expanded','true');
    matches.forEach(item=>{
      const button=document.createElement('button');
      button.type='button';button.className='global-search-result';
      const heading=document.createElement('strong');heading.textContent=item.label;
      button.appendChild(heading);
      button.addEventListener('click',()=>navigate(item));
      results.appendChild(button);
    });
    if(status)status.textContent=matches.length?'': 'No matching page found';
  }
  input.setAttribute('aria-controls','inline-search-dropdown');
  input.setAttribute('aria-expanded','false');
  input.addEventListener('input',render);
  input.addEventListener('focus',render);
  input.addEventListener('keydown',event=>{
    if(event.key==='Escape'){close();input.blur();}
    if(event.key==='Enter'){
      event.preventDefault();if(matches.length)navigate(matches[0]);
    }
  });
  document.addEventListener('pointerdown',event=>{
    if(!event.target.closest('#inline-search-wrap'))close();
  });
  document.addEventListener('keydown',event=>{
    if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){
      event.preventDefault();input.focus();input.select();
    }
  });
  close();
})();
</script>
<script>
(function(){
    "use strict";
    const key="eduvault_admin_forums_v1";
    const form=document.getElementById("forum-form"),tbody=document.getElementById("forum-table"),scroller=document.getElementById("forum-scroll");
    const message=document.getElementById("forum-message"),submit=document.getElementById("forum-submit"),count=document.getElementById("forum-list-count");
    const examples=[
        {id:"forum-example-1",title:"How do PHP sessions work?",user:"Kasun Perera",category:"Programming",description:"Discussion about PHP sessions.",status:"Reported",createdAt:1},
        {id:"forum-example-2",title:"Best way to learn MySQL?",user:"Nimal Silva",category:"Databases",description:"Share useful resources and tips for learning MySQL.",status:"Active",createdAt:2},
        {id:"forum-example-3",title:"JavaScript assignment help",user:"Amal Fernando",category:"Programming",description:"Question about a JavaScript assignment.",status:"Reported",createdAt:3}
    ];
    let entries,editing=null;
    try{const stored=localStorage.getItem(key);entries=stored===null?examples:JSON.parse(stored);if(!Array.isArray(entries))entries=examples;}catch(_){entries=examples;}
    function log(level,text){window.eduVaultLog?.(level,text);}
    function inform(text,error=false){message.textContent=text;message.classList.toggle("error",error);}
    function save(){try{localStorage.setItem(key,JSON.stringify(entries));return true;}catch(_){inform("Unable to save the discussion. Check browser storage.",true);return false;}}
    function el(tag,text,cls){const node=document.createElement(tag);if(cls)node.className=cls;if(text!==undefined)node.textContent=text;return node;}
    const typeSelect=document.getElementById("forum-type");
    function updateType(){
        const qa=typeSelect.value==="qa";
        form.dataset.postType=qa?"qa":"discussion";
        document.getElementById("forum-title-label").textContent=qa?"Question Title":"Discussion Topic";
        document.getElementById("forum-description-label").textContent=qa?"Question Details":"Discussion Details";
        document.getElementById("forum-title").placeholder=qa?"e.g. How do PHP sessions work?":"e.g. Best practices for web development";
        document.getElementById("forum-description").placeholder=qa?"Explain your question clearly":"Describe the topic and invite students to discuss";
        
        document.getElementById("forum-photo-group").hidden=!qa;
        document.getElementById("forum-type-help").textContent=qa?"Post a specific question and optionally provide an answer.":"Start an open discussion for students to share ideas and replies.";
        if(!editing)submit.innerHTML='<i class="fa-solid fa-plus"></i> '+(qa?"Create Q&A":"Create Discussion");
    }
    const photoInput=document.getElementById("forum-photo"),photoPreview=document.getElementById("forum-photo-preview"),photoImage=document.getElementById("forum-photo-image");
    let photoValue="";
    function updatePhotoPreview(){photoPreview.hidden=!photoValue;photoImage.src=photoValue||"";}
    photoInput.addEventListener("change",async()=>{
        const file=photoInput.files&&photoInput.files[0];if(!file)return;
        if(!["image/jpeg","image/png","image/webp","image/gif"].includes(file.type)||file.size>800*1024){inform("Choose a JPG, PNG, WebP or GIF smaller than 800 KB.",true);photoInput.value="";return;}
        try{photoValue=await new Promise((resolve,reject)=>{const reader=new FileReader();reader.onload=()=>resolve(reader.result);reader.onerror=reject;reader.readAsDataURL(file);});updatePhotoPreview();inform("Q&A intro photo ready to save.");}
        catch(_){inform("Unable to read the photo.",true);}
    });
    document.getElementById("forum-photo-remove").addEventListener("click",()=>{photoValue="";photoInput.value="";updatePhotoPreview();});
    typeSelect.addEventListener("change",updateType);
    function reset(){editing=null;form.reset();photoValue="";photoInput.value="";updatePhotoPreview();form.classList.remove("is-editing");updateType();}
    function edit(item){editing=item.id;photoValue=item.photo||"";photoInput.value="";updatePhotoPreview();typeSelect.value=item.type||(item.category==="Q&A"?"qa":"discussion");updateType();for(const name of ["title","user","category","description","answer"]){form.elements.namedItem(name).value=item[name]||"";}form.classList.add("is-editing");submit.innerHTML='<i class="fa-solid fa-floppy-disk"></i> Save Changes';inform("Editing: "+item.title);form.scrollIntoView({behavior:"smooth",block:"center"});form.elements.namedItem("title").focus({preventScroll:true});}
    function modifyStatus(item,status){const previous=entries.map(x=>({...x}));item.status=status;if(!save()){entries=previous;return;}render();log("success","Discussion status changed to "+status+": "+item.title);}
    function addButton(parent,icon,label,cls,handler){const btn=el("button",undefined,"action-btn "+cls);btn.type="button";btn.title=label;btn.setAttribute("aria-label",label);btn.innerHTML='<i class="fa-solid fa-'+icon+'" aria-hidden="true"></i>';btn.addEventListener("click",handler);parent.append(btn);}
    function render(){
        tbody.replaceChildren();
        if(!entries.length){const tr=el("tr");const td=el("td","No discussions created yet.","management-empty");td.colSpan=4;tr.append(td);tbody.append(tr);}
        for(const item of entries){
            const tr=el("tr"),detail=el("td",undefined,"forum-post-cell");detail.append(el("strong",item.title),el("span",(item.type==="qa"||(!item.type&&item.category==="Q&A"))?"Q&A":"Discussion Forum","forum-kind "+((item.type==="qa"||(!item.type&&item.category==="Q&A"))?"qa":"")),el("p",item.description,"forum-detail"),el("p",item.category||"General","forum-meta"));if(item.answer)detail.append(el("p","Answer: "+item.answer,"forum-detail"));if(item.type==="qa"&&item.photo){const img=el("img",undefined,"forum-intro-thumbnail");img.src=item.photo;img.alt="Intro photo for "+item.title;img.loading="lazy";detail.append(img);}
            const user=el("td",item.user),state=el("td"),badge=el("span",item.status||"Active","badge "+(item.status==="Reported"?"reported":item.status==="Pending"?"pending":"approved"));state.append(badge);
            const actions=el("td"),buttons=el("div",undefined,"forum-row-actions");
            addButton(buttons,"eye","View discussion: "+item.title,"view-btn",()=>{
                const info="Discussion: "+item.title+"\nPosted by: "+item.user+"\nCategory: "+item.category+"\nStatus: "+item.status+"\n\n"+item.description+(item.answer?"\n\nAnswer: "+item.answer:"");
                if(item.type==="qa"&&item.photo){const dialog=document.createElement("dialog");dialog.style.cssText="background:#0d1726;color:white;border:1px solid #355169;border-radius:15px;padding:24px;max-width:min(600px,92vw);max-height:88vh;overflow:auto";const infoNode=el("p",info);infoNode.style.whiteSpace="pre-wrap";const pic=el("img");pic.src=item.photo;pic.alt="Q&A introductory photo";pic.style.cssText="display:block;max-width:100%;max-height:45vh;object-fit:contain;margin:16px auto";const close=el("button","Close","add-btn");close.type="button";close.addEventListener("click",()=>dialog.close());dialog.append(infoNode,pic,close);document.body.append(dialog);dialog.addEventListener("close",()=>dialog.remove());dialog.showModal();}
                else window.alert(info);
            });
            if(item.status!=="Active")addButton(buttons,"check","Approve discussion: "+item.title,"approve-btn",()=>modifyStatus(item,"Active"));
            addButton(buttons,"pen","Edit discussion: "+item.title,"edit-btn",()=>edit(item));
            addButton(buttons,"trash","Delete discussion: "+item.title,"delete-btn",()=>{if(!confirm("Delete discussion: "+item.title+"?"))return;const previous=entries.slice();entries=entries.filter(x=>x.id!==item.id);if(!save()){entries=previous;return;}if(editing===item.id)reset();render();inform("Discussion deleted successfully.");log("warn","Discussion deleted: "+item.title);});
            actions.append(buttons);tr.append(detail,user,state,actions);tbody.append(tr);
        }
        // Fit the first two rows. Older records remain within the internal scroll area.
        const header=scroller.querySelector("thead").getBoundingClientRect().height;
        const height=[...tbody.rows].slice(0,2).reduce((v,row)=>v+row.getBoundingClientRect().height,0);
        scroller.style.maxHeight=Math.ceil(header+height+2)+"px";scroller.scrollTop=0;
        count.textContent=entries.length>2?`Showing 2 of ${entries.length} discussions. Scroll inside to view the rest.`:`${entries.length} discussion(s)`;
    }
    document.getElementById("forum-cancel").addEventListener("click",()=>{reset();inform("Editing cancelled.");});
    form.addEventListener("submit",e=>{
        e.preventDefault();if(!form.reportValidity())return;
        const data=new FormData(form),record={};for(const name of ["title","user","category","description","type","answer"])record[name]=String(data.get(name)||"").trim();
        record.status=editing!==null?(entries.find(x=>x.id===editing)?.status||"Active"):"Active";
        if(record.type!=="qa"){record.answer="";record.photo="";}else record.photo=photoValue;
        if(!record.title||!record.user||!record.description||!record.category){inform("Please complete all required fields.",true);return;}
        const previous=entries.map(x=>({...x})),wasEdit=editing!==null;
        if(wasEdit){const target=entries.find(x=>x.id===editing);if(!target){reset();inform("Discussion not found.",true);return;}Object.assign(target,record);}
        else entries.unshift({...record,id:(typeof crypto!=="undefined"&&crypto.randomUUID)?crypto.randomUUID():String(Date.now())+Math.random(),createdAt:Date.now()});
        if(!save()){entries=previous;return;}
        reset();render();inform("Discussion "+(wasEdit?"updated":"created")+" successfully.");log("success","Discussion "+(wasEdit?"updated: ":"created: ")+record.title);
    });
    updateType();render();
})();
</script>
<script>(function(){
  'use strict';
  const pages={dashboard:'Dashboard.php',materials:'Study_Materials_Manage_Batch.php',forums:'Discussion_Forums_QA.php',subjects:'Subjects_Categories.php',events:'Events_Announcements.php',notice:'Notice_Board.php',logs:'Activity_Logs.php'};
  const current=document.documentElement.dataset.eduvaultPage||'dashboard';
  const views=[...document.querySelectorAll('.workspace-view')];
  const links=[...document.querySelectorAll('.sidebar-link[data-target]')];
  function navigate(target){
    let name=target;
    const detail=name==='announcements'?'announcements':name==='users'?'users':name==='batches'?'batches':null;
    if(name==='announcements')name='events';
    if(name==='users')name='dashboard';
    if(name==='batches')name='materials';
    if(!pages[name])name='dashboard';
    if(name!==current){
      try{sessionStorage.setItem('ev_internal_navigation','1');sessionStorage.setItem('ev_sidebar_collapsed',String(document.querySelector('.admin-app').classList.contains('sidebar-collapsed')?'1':'0'));if(detail)sessionStorage.setItem('ev_pending_section',detail);}catch(e){}
      window.location.assign(pages[name]);
      return;
    }
    views.forEach(v=>v.hidden=v.dataset.view!==current);
    links.forEach(link=>{const active=link.dataset.target===current;link.classList.toggle('active',active);link.setAttribute('aria-current',active?'page':'false');});
    if(detail==='announcements')window.eduVaultSetContentType?.('announcements');
    if(detail==='users')requestAnimationFrame(()=>document.getElementById('dashboard-manage-users')?.scrollIntoView({block:'start'}));
    if(detail==='batches')requestAnimationFrame(()=>document.getElementById('batches-section')?.scrollIntoView({block:'start'}));
  }
  window.eduVaultNavigate=navigate;
  links.forEach(link=>{
    const name=link.dataset.target;
    link.setAttribute('data-linked-page',pages[name]);
    link.addEventListener('click',e=>{e.preventDefault();navigate(name);});
  });
  // Student Dashboard is an external PHP page: use its native anchor navigation.
  navigate(current);
  const pending=sessionStorage.getItem('ev_pending_section');
  if(pending){sessionStorage.removeItem('ev_pending_section');setTimeout(()=>navigate(pending),60);}
})();</script><script>
(function(){
  'use strict';
  const select=document.getElementById('content-type-switch');
  const events=document.getElementById('combined-events-panel');
  const announcements=document.getElementById('combined-announcements-panel');
  function changeType(type){
    const selected=type==='announcements'?'announcements':'events';
    select.value=selected;
    events.hidden=selected!=='events';
    announcements.hidden=selected!=='announcements';
  }
  window.eduVaultSetContentType=changeType;
  select.addEventListener('change',()=>changeType(select.value));
  changeType('events');
})();
</script>
<script>
(function(){
 'use strict';
 const STORAGE_KEY='eduvault_admin_subjects_v1';
 const defaults=[
   {id:'SUB001',name:'Web Development',category:'Programming',materials:120},
   {id:'SUB002',name:'Database Systems',category:'Databases',materials:95},
   {id:'SUB003',name:'Computer Networks',category:'Networks',materials:70}
 ];
 const form=document.getElementById('subject-form');
 const table=document.getElementById('subjects-table');
 const submit=document.getElementById('subject-submit');
 const cancel=document.getElementById('subject-cancel');
 const message=document.getElementById('subject-message');
 const nameField=document.getElementById('subject-name');
 const categoryField=document.getElementById('subject-category');
 let editing=null;
 let subjects;
 try {
   const stored=localStorage.getItem(STORAGE_KEY);
   const data=stored===null?defaults:JSON.parse(stored);
   subjects=Array.isArray(data)?data:defaults.map(x=>({...x}));
 } catch(err){subjects=defaults.map(x=>({...x}));}
 function notify(text,error=false){message.textContent=text;message.classList.toggle('error',error);}
 function persist(){try{localStorage.setItem(STORAGE_KEY,JSON.stringify(subjects));return true;}catch(err){notify('Unable to save. Check browser storage permissions.',true);return false;}}
 function log(level,text){window.eduVaultLog?.(level,text);}
 function reset(){editing=null;form.reset();submit.innerHTML='<i class="fa-solid fa-plus"></i> Add Subject';cancel.hidden=true;}
 function button(label,icon,cls,handler){const el=document.createElement('button');el.type='button';el.className='action-btn '+cls;el.title=label;el.setAttribute('aria-label',label);const i=document.createElement('i');i.className='fa-solid '+icon;i.setAttribute('aria-hidden','true');el.append(i);el.addEventListener('click',handler);return el;}
 function render(){
  table.replaceChildren();
  if(!subjects.length){const row=document.createElement('tr');const cell=document.createElement('td');cell.colSpan=5;cell.textContent='No subjects available. Add a subject above.';cell.className='management-empty';row.append(cell);table.append(row);return;}
  subjects.forEach(subject=>{
   const row=document.createElement('tr');
   [subject.id,subject.name,subject.category,String(subject.materials??0)].forEach(text=>{const cell=document.createElement('td');cell.textContent=text;row.append(cell);});
   const actions=document.createElement('td');
   actions.append(button('Edit '+subject.name,'fa-pen','edit-btn',()=>{
    editing=subject.id;nameField.value=subject.name;categoryField.value=subject.category;
    submit.innerHTML='<i class="fa-solid fa-check"></i> Save Changes';cancel.hidden=false;
    notify('Editing '+subject.name+'. Save changes or cancel.');
    form.scrollIntoView({behavior:'smooth',block:'center'});nameField.focus({preventScroll:true});
   }));
   actions.append(button('Delete '+subject.name,'fa-trash','delete-btn',()=>{
    if(!window.confirm('Delete subject "'+subject.name+'"?'))return;
    const old=subjects.slice();subjects=subjects.filter(item=>item.id!==subject.id);
    if(!persist()){subjects=old;return;}
    if(editing===subject.id)reset();render();notify('Subject deleted successfully.');log('warn','Subject deleted: '+subject.name);
   }));
   row.append(actions);table.append(row);
  });
 }
 function nextID(){const max=subjects.reduce((max,s)=>{const m=/^SUB(\d+)$/.exec(s.id||'');return m?Math.max(max,Number(m[1])):max;},0);return 'SUB'+String(max+1).padStart(3,'0');}
 form.addEventListener('submit',event=>{
  event.preventDefault();if(!form.reportValidity())return;
  const name=nameField.value.trim(),category=categoryField.value.trim();
  if(!name||!category){notify('Please provide a subject name and category.',true);return;}
  const before=subjects.map(s=>({...s}));const wasEdit=editing!==null;
  if(wasEdit){const subject=subjects.find(s=>s.id===editing);if(!subject){notify('Subject no longer exists.',true);reset();return;}subject.name=name;subject.category=category;}
  else subjects.push({id:nextID(),name,category,materials:0});
  if(!persist()){subjects=before;return;}
  reset();render();notify('Subject '+(wasEdit?'updated':'added')+' successfully.');log('success','Subject '+(wasEdit?'updated: ':'created: ')+name);
 });
 cancel.addEventListener('click',()=>{reset();notify('Editing cancelled.');});
 render();
})();
</script>
<div aria-labelledby="material-details-title" aria-modal="true" class="eduvault-dialog" hidden="" id="material-details" role="dialog"><div class="eduvault-dialog-card"><h3 id="material-details-title">Study Material Details</h3><dl id="material-details-data"></dl><p style="color:#8fa4be;font-size:12px;margin-bottom:16px">This HTML demo contains material metadata only. Opening the actual uploaded file requires connecting the upload backend.</p><button id="material-details-close" type="button">Close</button></div></div><script>
(function(){
 'use strict';
 const materialsDefaults=[
  {id:'MAT001',title:'Web Development Notes',subject:'Web Development',uploadedBy:'Umesh Yasiru',date:'05 Oct 2026',status:'Pending'},
  {id:'MAT002',title:'Database Revision Guide',subject:'Database Systems',uploadedBy:'Kasun Perera',date:'04 Oct 2026',status:'Approved'},
  {id:'MAT003',title:'Computer Networks Notes',subject:'Computer Networks',uploadedBy:'Nimal Silva',date:'03 Oct 2026',status:'Pending'}
 ];
 const usersDefaults=[
  {id:'ST001',name:'Umesh Yasiru',role:'Student',status:'Active'},
  {id:'ST002',name:'Kasun Perera',role:'Student',status:'Active'},
  {id:'ST003',name:'Nimal Silva',role:'Student',status:'Inactive'}
 ];
 const materialKey='eduvault_admin_materials_v1', userKey='eduvault_admin_users_v1';
 function load(key,defaults){try{const raw=localStorage.getItem(key);const value=raw===null?defaults:JSON.parse(raw);return Array.isArray(value)?value:defaults.map(x=>({...x}));}catch(e){return defaults.map(x=>({...x}));}}
 let materials=load(materialKey,materialsDefaults), users=load(userKey,usersDefaults),editing=null;
 function save(key,arr){try{localStorage.setItem(key,JSON.stringify(arr));return true;}catch(e){alert('Unable to save changes in this browser.');return false;}}
 function log(level,msg){window.eduVaultLog?.(level,msg);}
 function node(tag,content,cls){const n=document.createElement(tag);if(cls)n.className=cls;if(content!==undefined)n.textContent=content;return n;}
 function action(text,icon,cls,fn){const b=node('button',undefined,'action-btn '+cls);b.type='button';b.title=text;b.setAttribute('aria-label',text);const i=node('i',undefined,'fa-solid '+icon);i.setAttribute('aria-hidden','true');b.append(i);b.addEventListener('click',fn);return b;}
 function statusBadge(value){return node('span',value,'badge '+(value==='Approved'||value==='Active'?'approved':value==='Pending'?'pending':'reported'));}
 function alertStatus(id,text){document.getElementById(id).textContent=text;}
 const modal=document.getElementById('material-details');
 function showMaterial(item){const dl=document.getElementById('material-details-data');dl.replaceChildren();for(const [k,v] of [['Title',item.title],['Subject',item.subject],['Uploaded By',item.uploadedBy],['Date',item.date],['Status',item.status]]){dl.append(node('dt',k),node('dd',v));}modal.hidden=false;document.getElementById('material-details-close').focus();log('info','Material viewed: '+item.title);}
 function closeMaterial(){modal.hidden=true;}
 document.getElementById('material-details-close').addEventListener('click',closeMaterial);
 modal.addEventListener('click',event=>{if(event.target===modal)closeMaterial();});
 document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!modal.hidden)closeMaterial();});
 function renderMaterials(){
  const body=document.getElementById('materials-table');
  body.replaceChildren();
  if(!materials.length){
   const tr=node('tr'),td=node('td','No study materials available.');
   td.colSpan=6;tr.append(td);body.append(tr);return;
  }
  materials.forEach(item=>{
   const row=node('tr');
   for(const text of [item.title,item.subject,item.uploadedBy,item.date])row.append(node('td',text));
   const st=node('td');st.append(statusBadge(item.status));row.append(st);
   const actions=node('td');
   actions.append(action('View '+item.title,'fa-eye','view-btn',()=>showMaterial(item)));
   const approve=action('Approve '+item.title,'fa-check','approve-btn',()=>{
    if(item.status==='Approved')return;
    if(!confirm('Approve "'+item.title+'"?'))return;
    const previous=materials.map(m=>({...m}));
    item.status='Approved';
    if(!save(materialKey,materials)){materials=previous;renderMaterials();return;}
    renderMaterials();alertStatus('materials-status','Material approved successfully.');
    log('success','Material approved: '+item.title);
   });
   approve.disabled=item.status==='Approved';
   actions.append(approve);
   const reject=action('Reject '+item.title,'fa-xmark','delete-btn',()=>{
    if(item.status==='Rejected')return;
    if(!confirm('Reject "'+item.title+'"? The material will be kept with Rejected status.'))return;
    const previous=materials.map(m=>({...m}));
    item.status='Rejected';
    if(!save(materialKey,materials)){materials=previous;renderMaterials();return;}
    renderMaterials();alertStatus('materials-status','Material rejected successfully.');
    log('warn','Material rejected: '+item.title);
   });
   reject.disabled=item.status==='Rejected';
   actions.append(reject);
   actions.append(action('Delete '+item.title,'fa-trash','delete-btn',()=>{
    if(!confirm('Permanently delete "'+item.title+'" from the dashboard? This cannot be undone.'))return;
    const previous=materials;
    materials=materials.filter(m=>m.id!==item.id);
    if(!save(materialKey,materials)){materials=previous;return;}
    renderMaterials();
    alertStatus('materials-status','Study material deleted successfully.');
    log('warn','Study material deleted: '+item.title);
   }));
   row.append(actions);body.append(row);
  });
 }
 const form=document.getElementById('user-edit-form'),name=document.getElementById('user-edit-name'),role=document.getElementById('user-edit-role');
 function cancelEdit(){editing=null;form.hidden=true;form.reset();}
 document.getElementById('user-edit-cancel').addEventListener('click',cancelEdit);
 function renderUsers(){const body=document.getElementById('users-table');body.replaceChildren();if(!users.length){const tr=node('tr'),td=node('td','No users available.');td.colSpan=5;tr.append(td);body.append(tr);return;}
  users.forEach(item=>{const row=node('tr');for(const text of [item.id,item.name,item.role])row.append(node('td',text));const st=node('td');st.append(statusBadge(item.status));row.append(st);const actions=node('td');
   actions.append(action('Edit '+item.name,'fa-pen','edit-btn',()=>{editing=item.id;name.value=item.name;role.value=item.role;form.hidden=false;form.scrollIntoView({behavior:'smooth',block:'center'});name.focus({preventScroll:true});alertStatus('users-status','Editing '+item.name+'.');}));
   const active=item.status==='Active';
   actions.append(action((active?'Block ':'Activate ')+item.name,active?'fa-ban':'fa-user-check',active?'block-btn':'approve-btn',()=>{
    if(!confirm((active?'Block ':'Activate ')+item.name+'?'))return;
    const previous=users.map(u=>({...u}));item.status=active?'Inactive':'Active';if(!save(userKey,users)){users=previous;return;}
    renderUsers();alertStatus('users-status',item.name+' is now '+item.status+'.');log(active?'warn':'success','User '+(active?'blocked: ':'activated: ')+item.name);
   }));row.append(actions);body.append(row);
  });
 }
 form.addEventListener('submit',event=>{event.preventDefault();if(!form.reportValidity())return;const user=users.find(u=>u.id===editing);if(!user){alertStatus('users-status','Selected user not found.');return;}
  const value=name.value.trim();if(!value){name.focus();return;}const before=users.map(u=>({...u}));user.name=value;user.role=role.value;
  if(!save(userKey,users)){users=before;return;}cancelEdit();renderUsers();alertStatus('users-status','User details updated successfully.');log('success','User updated: '+user.id+' ('+value+')');
 });
 renderMaterials();renderUsers();
})();
</script>
<script>
(function(){
'use strict';
const configs={
 users:{key:'eduvault_admin_users_v1',fallback:[{id:'ST001',name:'Umesh Yasiru',role:'Student',status:'Active'},{id:'ST002',name:'Kasun Perera',role:'Student',status:'Active'},{id:'ST003',name:'Nimal Silva',role:'Student',status:'Inactive'}]},
 materials:{key:'eduvault_admin_materials_v1',fallback:[{id:'MAT001',title:'Web Development Notes',subject:'Web Development',uploadedBy:'Umesh Yasiru',date:'05 Oct 2026',status:'Pending'},{id:'MAT002',title:'Database Revision Guide',subject:'Database Systems',uploadedBy:'Kasun Perera',date:'04 Oct 2026',status:'Approved'},{id:'MAT003',title:'Computer Networks Notes',subject:'Computer Networks',uploadedBy:'Nimal Silva',date:'03 Oct 2026',status:'Pending'}]},
 forums:{key:'eduvault_admin_forums_v1',fallback:[]},
 events:{key:'eduvault_admin_events_v1',fallback:[]},
 announcements:{key:'eduvault_admin_announcements_v1',fallback:[]}
};
function read(config){try{const raw=localStorage.getItem(config.key);const a=raw===null?config.fallback:JSON.parse(raw);return Array.isArray(a)?a:config.fallback;}catch(_){return config.fallback;}}
function snapshot(){const d={};Object.entries(configs).forEach(([k,v])=>d[k]=read(v));return d;}
function refresh(){const d=snapshot(),pending=d.materials.filter(r=>r.status==='Pending').length;
 const values={users:d.users.length,materials:d.materials.length,pending,forums:d.forums.length};
 for(const [name,n] of Object.entries(values)){const el=document.querySelector('[data-stat-value="'+name+'"]');if(el)el.textContent=n.toLocaleString();}
 const total=document.getElementById('overview-total');if(total)total.textContent=d.materials.length.toLocaleString();
 const counts=[d.materials.filter(x=>x.status==='Approved').length,pending,d.materials.filter(x=>x.status==='Rejected').length];
 const colors=['#20d39e','#ffcb48','#f46a83'];let pos=0;
 const stops=counts.map((value,i)=>{const start=pos;pos+=d.materials.length?100*value/d.materials.length:0;return colors[i]+' '+start+'% '+pos+'%';});
 const pie=document.querySelector('#view-dashboard .donut');if(pie)pie.style.background=d.materials.length?'conic-gradient('+stops.join(',')+')':'conic-gradient(#223344 0 100%)';
 const legend=document.getElementById('overview-legend');if(legend){legend.replaceChildren();['Approved','Pending','Rejected'].forEach((label,i)=>{const item=document.createElement('span'),dot=document.createElement('i');dot.style.background=colors[i];item.append(dot,document.createTextNode(label+': '+counts[i]));legend.append(item);});}
}
// Rerender after other dashboard scripts update records.
for(const id of ['materials-table','users-table','forum-table']){const el=document.getElementById(id);if(el)new MutationObserver(()=>refresh()).observe(el,{childList:true});}
window.addEventListener('storage',refresh);
document.querySelectorAll('#view-dashboard .stat-card[data-target-view]').forEach(card=>{
 const nav=()=>{const destination=card.dataset.targetView;const sidebar=document.querySelector('.sidebar-link[data-target="'+destination+'"]');if(sidebar)sidebar.click();else if(destination==='users')window.eduVaultNavigate?.('users'); if(card.dataset.stat==='pending'){const row=[...document.querySelectorAll('#materials-table tr')].find(tr=>tr.textContent.includes('Pending'));if(row){row.scrollIntoView({behavior:'smooth',block:'center'});row.style.outline='2px solid #e7bb39';setTimeout(()=>row.style.outline='',1800);}}};
 card.addEventListener('click',nav);card.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();nav();}});
});
function sanitize(value){return String(value??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));}
function datasets(){const d=snapshot();return [
 ['Users',[['ID','Name','Role','Status'],...d.users.map(r=>[r.id,r.name,r.role,r.status])]],
 ['Study Materials',[['ID','Title','Subject','Uploaded By','Date','Status'],...d.materials.map(r=>[r.id,r.title,r.subject,r.uploadedBy,r.date,r.status])]],
 ['Discussion Forums and Q&A',[['Title','Posted By','Category','Status','Type'],...d.forums.map(r=>[r.title||r.question||'',r.author||r.user||r.postedBy||'',r.category||'',r.status||'',r.type||r.postType||''])]],
 ['Events',[['Title','Date','Time','Description'],...d.events.map(r=>[r.title,r.date,r.time,r.description])]],
 ['Announcements',[['Title','Category','Date','Description'],...d.announcements.map(r=>[r.title,r.category,r.date,r.description])]]
 ];}
function reportSummary(){
 const d=snapshot();
 return {users:d.users.length,active:d.users.filter(x=>x.status==='Active').length,
  materials:d.materials.length,approved:d.materials.filter(x=>x.status==='Approved').length,
  pending:d.materials.filter(x=>x.status==='Pending').length,rejected:d.materials.filter(x=>x.status==='Rejected').length,
  forums:d.forums.length,events:d.events.length,announcements:d.announcements.length};
}
function reportHTML(pageSize="A4", orientation="portrait"){
 const k=reportSummary(),date=new Date().toLocaleString();
 const pageName=["A4","A5","Letter","Legal"].includes(pageSize)?pageSize:"A4";
 const pageDirection=orientation==="landscape"?"landscape":"portrait";
 const cards=[['Registered Users',k.users],['Study Materials',k.materials],['Pending Approval',k.pending],['Forum Posts',k.forums]];
 const statuses=[['Active users',k.active],['Approved materials',k.approved],['Rejected materials',k.rejected],['Events',k.events],['Announcements',k.announcements]];
 const sections=datasets();
 return `<!doctype html><html><head><meta charset="utf-8"><title>EduVault | Administrator Report</title><style>
 @page{size:${pageName} ${pageDirection};margin:15mm 13mm 17mm}
 *{box-sizing:border-box}body{font:11px/1.48 Arial,sans-serif;color:#213047;margin:0;background:#fff}
 .report-top{padding:17px 20px;background:#092032;color:white;border-radius:10px;display:flex;justify-content:space-between;align-items:center;print-color-adjust:exact;-webkit-print-color-adjust:exact}
 .brand{color:#4cdae6;font-size:19px;font-weight:800;letter-spacing:-.7px}.brand span{color:white}.eyebrow{color:#95bbd1;text-transform:uppercase;font-size:9px;letter-spacing:1.5px;margin-top:3px}
 .meta{text-align:right;color:#d3e6f2;font-size:10px}.intro{margin:22px 0 14px}.intro h1{font-size:24px;color:#09283d;margin:0 0 6px}.intro p{margin:0;color:#65778c}
 .notice{font-size:10px;color:#526478;background:#eaf4f7;border-left:3px solid #15b5c5;padding:9px 11px;margin:15px 0 20px}
 .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}.card{padding:13px;border:1px solid #d9e5ed;border-radius:9px;background:#f4f9fb;print-color-adjust:exact;-webkit-print-color-adjust:exact}.card small{display:block;color:#59738a;font-size:9px;font-weight:700}.card strong{display:block;color:#07344e;font-size:23px;margin-top:3px}
 .mini{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:24px}.mini span{padding:6px 11px;border:1px solid #dbe7ee;border-radius:20px;color:#405f72}
 h2{font-size:15px;color:#0a7085;margin:22px 0 8px;break-after:avoid;page-break-after:avoid}h2 small{font-size:10px;color:#6b8396;font-weight:normal;margin-left:7px}
 table{width:100%;border-collapse:collapse;table-layout:auto;font-size:9px;margin:0 0 15px}thead{display:table-header-group}tr{break-inside:avoid;page-break-inside:avoid}th{color:#fff;background:#0e4b61;letter-spacing:.2px;text-align:left;padding:8px 7px;print-color-adjust:exact;-webkit-print-color-adjust:exact}td{padding:8px 7px;border-bottom:1px solid #e0e8ef;vertical-align:top;overflow-wrap:anywhere}tbody tr:nth-child(even){background:#f1f7fa;print-color-adjust:exact;-webkit-print-color-adjust:exact}
 .empty{padding:12px 8px;color:#657a8f;background:#f1f7fa}.foot{border-top:1px solid #dbe6ed;padding-top:9px;margin-top:26px;color:#718397;font-size:9px;display:flex;justify-content:space-between}
 @media print{.report-top{border-radius:0}.card{box-shadow:none}}
 </style></head><body><header class="report-top"><div><div class="brand"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" aria-hidden="true" style="display:inline-block;vertical-align:middle;width:24px;height:24px;margin-right:7px"><path fill="#46d6e9" d="M2 13 20 5 38 13 20 21Z"/><path fill="#46d6e9" d="M10 18v9c5 5 15 5 20 0v-9l-10 5Z"/><path stroke="#46d6e9" stroke-width="2" fill="none" d="M35 14v13"/><circle cx="35" cy="29" r="2.5" fill="#46d6e9"/></svg>Edu<span>Vault</span></div><div class="eyebrow">Administrator Report</div></div><div class="meta">NSBM Green University<br>Faculty of Computing</div></header>
 <section class="intro"><h1>Administrative Activity Report</h1><p>Prepared ${sanitize(date)} · Operational overview and record register</p></section>
 <div class="notice">Data source: locally saved dashboard records in this browser. Figures are not live database or institution-wide totals.</div>
 <div class="cards">${cards.map(([name,num])=>`<div class="card"><small>${sanitize(name)}</small><strong>${num}</strong></div>`).join('')}</div>
 <h2>Operational Breakdown</h2><div class="mini">${statuses.map(([name,num])=>`<span>${sanitize(name)}: <b>${num}</b></span>`).join('')}</div>
 ${sections.map(([title,rows])=>`<section><h2>${sanitize(title)} <small>${Math.max(0,rows.length-1)} records</small></h2>${rows.length>1?`<table><thead><tr>${rows[0].map(x=>`<th>${sanitize(x)}</th>`).join('')}</tr></thead><tbody>${rows.slice(1).map(row=>`<tr>${row.map(v=>`<td>${sanitize(v)}</td>`).join('')}</tr>`).join('')}</tbody></table>`:'<div class="empty">No records available.</div>'}</section>`).join('')}
 <footer class="foot"><span>EduVault • Administrator Workspace</span><span>Confidential · Browser-local records</span></footer>



</body></html>`;
}
function printReport(pageSize='A4', orientation='portrait'){
 const html=reportHTML(pageSize,orientation);
 let frame=document.getElementById('eduvault-print-frame');
 if(frame){frame.remove();}
 frame=document.createElement('iframe');
 frame.id='eduvault-print-frame';
 frame.setAttribute('title','EduVault print document');
 frame.style.cssText='position:fixed;left:-10000px;top:0;width:1px;height:1px;border:0;opacity:0;pointer-events:none';
 document.body.appendChild(frame);
 const doc=frame.contentDocument;
 doc.open();doc.write(html);doc.close();
 let started=false;
 const printNow=()=>{
   if(started)return;
   started=true;
   try{frame.contentWindow.focus();frame.contentWindow.print();}
   catch(err){console.error('Print failed:',err);alert('Unable to open print preview. Check your browser print permissions.');}
 };
 frame.onload=printNow;
 // about:blank document has no network resources and can be printed once styles are parsed.
 setTimeout(printNow,250);
 // Keep the frame until the print dialog finishes so the document survives browser preview.
 const cleanup=()=>setTimeout(()=>{if(frame.isConnected)frame.remove();},500);
 frame.contentWindow.addEventListener('afterprint',cleanup,{once:true});
}

function excelReport(){
 // An actual Office Open XML (.xlsx) workbook, produced without third-party libraries.
 const dateStamp=new Date().toISOString().slice(0,10);
 const clean=v=>String(v??'').replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F]/g,'');
 const xml=v=>clean(v).replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&apos;'}[ch]));
 const summary=reportSummary(), sections=datasets();
 const sheets=[
  {name:'Executive Summary',rows:[
   ['EDUVAULT | ADMINISTRATOR REPORT'],
   ['Generated',new Date().toLocaleString()],
   ['Source','Browser-local dashboard records, not live server totals'],
   [],['METRIC','COUNT'],
   ['Registered Users',summary.users],['Active Users',summary.active],
   ['Study Materials',summary.materials],['Approved Materials',summary.approved],
   ['Pending Approval',summary.pending],['Rejected Materials',summary.rejected],
   ['Forum Posts',summary.forums],['Events',summary.events],['Announcements',summary.announcements],
   [],['Report Sections','Users, Study Materials, Forums, Events, Announcements']
  ],headerIndex:4},
  ...sections.map(([name,rows])=>({name:name==='Discussion Forums and Q&A'?'Forums and Q&A':name,rows,headerIndex:0}))
 ];
 function colName(n){let name='';for(n++;n;n=Math.floor((n-1)/26)){name=String.fromCharCode(65+(n-1)%26)+name;}return name;}
 function sheetXml(sheet){
  const rows=sheet.rows;
  const maxCols=Math.max(1,...rows.map(r=>r.length));
  const colWidths=Array.from({length:maxCols},(_,i)=>{
   const maximum=Math.max(11,...rows.map(r=>String(r[i]??'').length));
   return `<col min="${i+1}" max="${i+1}" width="${Math.min(48,maximum+4)}" customWidth="1"/>`;
  }).join('');
  const body=rows.map((row,ri)=>{
   if(!row.length)return `<row r="${ri+1}"/>`;
   const isTitle=sheet.name==='Executive Summary'&&ri===0;
   const isHeader=ri===sheet.headerIndex;
   const style=isTitle?3:isHeader?1:ri%2===0?2:0;
   const cells=row.map((v,ci)=>{
    const ref=colName(ci)+(ri+1),numeric=typeof v==='number'&&Number.isFinite(v);
    return numeric?`<c r="${ref}" s="${style}"><v>${v}</v></c>`:`<c r="${ref}" t="inlineStr" s="${style}"><is><t xml:space="preserve">${xml(v)}</t></is></c>`;
   }).join('');
   return `<row r="${ri+1}">${cells}</row>`;
  }).join('');
  const autofilter=sheet.headerIndex===0&&rows.length>1?`<autoFilter ref="A1:${colName(maxCols-1)}${rows.length}"/>`:'';
  const freeze=sheet.headerIndex===0?'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>':'<sheetViews><sheetView workbookViewId="0"/></sheetViews>';
  return `<\x3fxml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">${freeze}<sheetFormatPr defaultRowHeight="20"/><cols>${colWidths}</cols><sheetData>${body}</sheetData>${autofilter}<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/></worksheet>`;
 }
 const files=[
  ['[Content_Types].xml',`<\x3fxml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>${sheets.map((_,i)=>`<Override PartName="/xl/worksheets/sheet${i+1}.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>`).join('')}</Types>`],
  ['_rels/.rels','<\x3fxml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>'],
  ['xl/workbook.xml',`<\x3fxml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>${sheets.map((sh,i)=>`<sheet name="${xml(sh.name)}" sheetId="${i+1}" r:id="rId${i+1}"/>`).join('')}</sheets></workbook>`],
  ['xl/_rels/workbook.xml.rels',`<\x3fxml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">${sheets.map((_,i)=>`<Relationship Id="rId${i+1}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet${i+1}.xml"/>`).join('')}<Relationship Id="rId${sheets.length+1}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>`],
  ['xl/styles.xml','<\x3fxml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="3"><font><sz val="11"/><name val="Aptos"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Aptos"/></font><font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Aptos Display"/></font></fonts><fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0F5264"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFEAF4F7"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="4"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1"/><xf numFmtId="0" fontId="0" fillId="3" borderId="0" xfId="0" applyFill="1"/><xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFill="1"/></cellXfs></styleSheet>'],
  ...sheets.map((sh,i)=>[`xl/worksheets/sheet${i+1}.xml`,sheetXml(sh)])
 ];
 const encoder=new TextEncoder();
 const crcTable=new Uint32Array(256);
 for(let i=0;i<256;i++){let c=i;for(let n=0;n<8;n++)c=(c&1)?0xEDB88320^(c>>>1):c>>>1;crcTable[i]=c>>>0;}
 const crc32=b=>{let c=0xFFFFFFFF;for(const v of b)c=crcTable[(c^v)&255]^(c>>>8);return(c^0xFFFFFFFF)>>>0;};
 const parts=[],central=[];let offset=0;
 function u16(v){return [v&255,(v>>>8)&255];}
 function u32(v){return [v&255,(v>>>8)&255,(v>>>16)&255,(v>>>24)&255];}
 const now=new Date(),dosTime=(now.getHours()<<11)|(now.getMinutes()<<5)|(now.getSeconds()>>1),dosDate=((now.getFullYear()-1980)<<9)|((now.getMonth()+1)<<5)|now.getDate();
 for(const [filename,data] of files){
  const name=encoder.encode(filename),bytes=encoder.encode(data),crc=crc32(bytes);
  const local=new Uint8Array([80,75,3,4,...u16(20),...u16(0),...u16(0),...u16(dosTime),...u16(dosDate),...u32(crc),...u32(bytes.length),...u32(bytes.length),...u16(name.length),...u16(0)]);
  parts.push(local,name,bytes);
  const cd=new Uint8Array([80,75,1,2,...u16(20),...u16(20),...u16(0),...u16(0),...u16(dosTime),...u16(dosDate),...u32(crc),...u32(bytes.length),...u32(bytes.length),...u16(name.length),...u16(0),...u16(0),...u16(0),...u16(0),...u32(0),...u32(offset)]);
  central.push(cd,name);
  offset+=local.length+name.length+bytes.length;
 }
 const centralLength=central.reduce((n,b)=>n+b.length,0);
 const end=new Uint8Array([80,75,5,6,...u16(0),...u16(0),...u16(files.length),...u16(files.length),...u32(centralLength),...u32(offset),...u16(0)]);
 const blob=new Blob([...parts,...central,end],{type:'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'});
 const url=URL.createObjectURL(blob),a=document.createElement('a');
 a.href=url;a.download='EduVault_Advanced_Admin_Report_'+dateStamp+'.xlsx';document.body.append(a);a.click();a.remove();
 setTimeout(()=>URL.revokeObjectURL(url),60000);
}

// Offline-safe direct PDF generation. No CDN dependencies or popup needed.
function downloadReportPDF(){
 const button=document.getElementById('report-pdf');
 const oldHTML=button.innerHTML;
 button.disabled=true;
 button.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Creating PDF';
 try {
  const pageW=595.28,pageH=841.89,left=38,right=557,top=40,bottom=802;
  const pages=[];let ops=[],y=0;
  const ascii=value=>String(value??'').normalize('NFKD').replace(/[\u0300-\u036f]/g,'').replace(/[^\x20-\x7E]/g,'?');
  const esc=value=>ascii(value).replace(/\\/g,'\\\\').replace(/\(/g,'\\(').replace(/\)/g,'\\)');
  const color=(r,g,b)=>`${(r/255).toFixed(3)} ${(g/255).toFixed(3)} ${(b/255).toFixed(3)}`;
  const rect=(x,ty,w,h,rgb)=>ops.push(`${color(...rgb)} rg ${x.toFixed(2)} ${(pageH-ty-h).toFixed(2)} ${w.toFixed(2)} ${h.toFixed(2)} re f`);
  const line=(x1,ty1,x2,ty2,rgb)=>ops.push(`${color(...rgb)} RG 0.5 w ${x1.toFixed(2)} ${(pageH-ty1).toFixed(2)} m ${x2.toFixed(2)} ${(pageH-ty2).toFixed(2)} l S`);
  const text=(value,x,ty,size=10,bold=false,rgb=[28,45,64])=>ops.push(`BT /${bold?'F2':'F1'} ${size} Tf ${color(...rgb)} rg 1 0 0 1 ${x.toFixed(2)} ${(pageH-ty).toFixed(2)} Tm (${esc(value)}) Tj ET`);
  const wrap=(value,width,size=9)=>{const max=Math.max(7,Math.floor(width/(size*.51)));const result=[];for(const para of String(value??'').split(/\r?\n/)){let current='';for(const word of ascii(para).split(/\s+/)){if(!word)continue;if(word.length>max){if(current){result.push(current);current='';}for(let i=0;i<word.length;i+=max)result.push(word.slice(i,i+max));continue;}const next=current?current+' '+word:word;if(next.length>max){result.push(current);current=word;}else current=next;}result.push(current);}return result.length?result:[''];};
  const newPage=()=>{if(ops.length)pages.push(ops.join('\n'));ops=[];rect(0,0,pageW,29,[8,30,46]);
  // Small vector cap logo at the left, keeping the existing report layout unchanged.
  const capX=left-29, capTop=6;
  const Y=p=>pageH-(capTop+p), X=p=>capX+p;
  ops.push(`${color(75,216,231)} rg ${X(1)} ${Y(9)} m ${X(13)} ${Y(3)} l ${X(25)} ${Y(9)} l ${X(13)} ${Y(15)} l h f`);
  ops.push(`${color(75,216,231)} rg ${X(6)} ${Y(14)} m ${X(13)} ${Y(18)} l ${X(20)} ${Y(14)} l ${X(20)} ${Y(21)} l ${X(13)} ${Y(24)} l ${X(6)} ${Y(21)} l h f`);
  ops.push(`${color(75,216,231)} RG 1.5 w ${X(23)} ${Y(11)} m ${X(23)} ${Y(21)} l S`);
  text('EduVault  |  ADMINISTRATOR REPORT',left,19,11,true,[75,216,231]);line(left,803,right,803,[218,229,238]);y=50;};
  const space=(h)=>{if(y+h>bottom){newPage();return true;}return false;};
  const summary=reportSummary();
  newPage();
  text('Administrative Activity Report',left,y,20,true,[12,49,73]);y+=25;
  text('Generated: '+new Date().toLocaleString(),left,y,9,false,[90,111,131]);y+=15;
  rect(left,y,pageW-left-(pageW-right),32,[232,246,248]);
  text('Source: Browser-local records, not live university or database totals',left+10,y+20,9,false,[60,94,111]);y+=47;
  const stats=[['REGISTERED USERS',summary.users],['STUDY MATERIALS',summary.materials],['PENDING REVIEW',summary.pending],['FORUM POSTS',summary.forums]];
  stats.forEach(([label,value],i)=>{const w=124,x=left+i*133;rect(x,y,w,65,[237,246,250]);text(label,x+8,y+17,8,true,[56,104,122]);text(String(value),x+8,y+44,21,true,[8,57,84]);});
  y+=91;
  text('Operational Overview',left,y,14,true,[9,113,133]);y+=20;
  const facts=[`Active users: ${summary.active}`,`Approved materials: ${summary.approved}`,`Rejected materials: ${summary.rejected}`,`Events: ${summary.events}`,`Announcements: ${summary.announcements}`];
  facts.forEach((fact,i)=>text(fact,left+(i%3)*177,y+Math.floor(i/3)*17,9,false,[49,76,97]));y+=58;
  // Compact, paginated tables with section headings and repeated column labels.
  for(const [heading,rows] of datasets()){
   space(55);text(heading,left,y,13,true,[10,115,134]);text(`${Math.max(rows.length-1,0)} records`,right-75,y,8,false,[98,122,141]);y+=10;
   if(rows.length<=1){y+=20;text('No records available.',left+6,y,9,false,[107,124,140]);y+=22;continue;}
   const heads=rows[0],data=rows.slice(1),cols=heads.length;
   const widths=cols===6?[55,115,92,98,80,79]:cols===5?[80,112,120,112,95]:[65,176,145,133];
   const total=widths.reduce((a,b)=>a+b,0),scale=(right-left)/total,actual=widths.map(w=>w*scale);
   const drawHead=()=>{rect(left,y,right-left,24,[15,76,98]);let x=left;heads.forEach((h,i)=>{const lines=wrap(h,actual[i]-9,7.5).slice(0,2);lines.forEach((v,j)=>text(v,x+5,y+11+j*8,7.5,true,[255,255,255]));x+=actual[i];});y+=24;};
   space(34);drawHead();
   data.forEach((row,ri)=>{
    const all=row.map((v,i)=>wrap(v,actual[i]-11,8).slice(0,8));
    const longest=Math.max(1,...all.map(a=>a.length)),h=Math.max(23,longest*10+11);
    if(y+h>bottom){newPage();text(heading+' (continued)',left,y,12,true,[10,115,134]);y+=18;drawHead();}
    if(ri%2===0)rect(left,y,right-left,h,[242,248,250]);
    let x=left;all.forEach((lines,i)=>{lines.forEach((v,j)=>text(v,x+5,y+13+j*10,8,false,[35,58,75]));x+=actual[i];});
    line(left,y+h,right,y+h,[218,230,237]);y+=h;
   });
   y+=23;
  }
  pages.push(ops.join('\n'));
  const N=pages.length;
  // Append running page numbers before PDF object serialization.
  pages.forEach((_,i)=>{pages[i]+=`\nBT /F1 8 Tf ${color(105,124,142)} rg 1 0 0 1 ${left} 26 Tm (EduVault - Administrator Workspace) Tj ET`;
   pages[i]+=`\nBT /F1 8 Tf ${color(105,124,142)} rg 1 0 0 1 495 26 Tm (Page ${i+1} of ${N}) Tj ET`;});
  const objects=[],add=(str)=>{objects.push(str);return objects.length;};
  const catalog=add('');const pagesRef=add('');
  const regular=add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>');
  const bold=add('<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>');
  const refs=[];const enc=new TextEncoder();
  for(const stream of pages){const bytes=enc.encode(stream);const content=add(`<< /Length ${bytes.length} >>\nstream\n${stream}\nendstream`);
   const page=add(`<< /Type /Page /Parent ${pagesRef} 0 R /MediaBox [0 0 ${pageW} ${pageH}] /Resources << /Font << /F1 ${regular} 0 R /F2 ${bold} 0 R >> >> /Contents ${content} 0 R >>`);refs.push(page);}
  objects[catalog-1]=`<< /Type /Catalog /Pages ${pagesRef} 0 R >>`;
  objects[pagesRef-1]=`<< /Type /Pages /Kids [${refs.map(n=>n+' 0 R').join(' ')}] /Count ${N} >>`;
  let pdf='%PDF-1.4\n%Offline generated report\n',offsets=[0];
  for(let i=0;i<objects.length;i++){offsets.push(enc.encode(pdf).length);pdf+=`${i+1} 0 obj\n${objects[i]}\nendobj\n`;}
  const xref=enc.encode(pdf).length;pdf+=`xref\n0 ${objects.length+1}\n0000000000 65535 f \n`;
  offsets.slice(1).forEach(n=>pdf+=`${String(n).padStart(10,'0')} 00000 n \n`);
  pdf+=`trailer\n<< /Size ${objects.length+1} /Root ${catalog} 0 R >>\nstartxref\n${xref}\n%%EOF`;
  const blob=new Blob([enc.encode(pdf)],{type:'application/pdf'});
  const url=URL.createObjectURL(blob),a=document.createElement('a');
  a.href=url;a.download='EduVault_Professional_Admin_Report_'+new Date().toISOString().slice(0,10)+'.pdf';
  document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(url),120000);
  window.eduVaultLog?.('success','PDF report downloaded');
 }catch(error){console.error('PDF download error',error);alert('PDF download failed: '+error.message);window.eduVaultLog?.('error','PDF export failed: '+error.message);}
 finally{button.disabled=false;button.innerHTML=oldHTML;}
}
document.getElementById('report-pdf').addEventListener('click',downloadReportPDF);
const printDialog=document.getElementById('print-paper-dialog');
const openPrint=document.getElementById('report-print');
const closePrint=()=>{printDialog.hidden=true;openPrint.focus();};
openPrint.addEventListener('click',()=>{printDialog.hidden=false;printDialog.querySelector('#print-page-size').focus();});
printDialog.querySelectorAll('[data-close-print]').forEach(el=>el.addEventListener('click',closePrint));
printDialog.addEventListener('click',event=>{if(event.target===printDialog)closePrint();});
printDialog.addEventListener('keydown',event=>{if(event.key==='Escape')closePrint();});
printDialog.querySelector('#print-paper-form').addEventListener('submit',event=>{
 event.preventDefault();
 const size=printDialog.querySelector('#print-page-size').value;
 const orientation=printDialog.querySelector('#print-orientation').value;
 // Call print from this direct user action so browsers permit the print window.
 printReport(size,orientation);
 printDialog.hidden=true;
 window.eduVaultLog?.('info','Admin report print requested: '+size+' '+orientation);
});
document.getElementById('report-excel').addEventListener('click',()=>{excelReport();window.eduVaultLog?.('success','Excel-compatible report exported');});
refresh();
})();
</script>
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
})();
</script>
<script id="eduvault-adaptive-sidebar-v72">
(function(){
  const app=document.querySelector('.admin-app');
  const toggle=document.getElementById('sidebar-collapse-toggle');
  if(!app||!toggle)return;
  function sync(){
    document.body.classList.toggle('ev-drawer-open',!app.classList.contains('sidebar-collapsed'));
  }
  // Page links only switch sections. The hamburger is the sole sidebar toggle.
  toggle.addEventListener('click',()=>requestAnimationFrame(sync));
  sync();
})();
</script>
<script id="eduvault-notice-board-script">
(function(){
 const key='eduvault_admin_notices_v1';
 const $=id=>document.getElementById(id), form=$('notice-form'),list=$('notice-board-list');
 if(!form||!list)return;
 const title=$('notice-title'),category=$('notice-category'),description=$('notice-description'),expiry=$('notice-expiry'),
 submit=$('notice-submit'),cancel=$('notice-cancel'),message=$('notice-message'),counter=$('notice-counter'),
 fileInput=$('notice-file'),drop=$('notice-drop'),picked=$('notice-picked'),pills=$('notice-category-pills');
 let notices=[],editId=null,attachment=null;
 try{const stored=JSON.parse(localStorage.getItem(key)||'[]');if(Array.isArray(stored))notices=stored;}catch(e){console.warn('Could not load notices',e);}
 const pad=n=>String(n).padStart(2,'0');
 const today=()=>{const d=new Date();return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;};
 const format=iso=>iso?new Date(iso).toLocaleString(undefined,{year:'numeric',month:'short',day:'numeric',hour:'numeric',minute:'2-digit'}):'';
 const isExpired=item=>Boolean(item.expiresAt&&item.expiresAt<today());
 const stamp=()=>{const n=notices.find(n=>n.id===editId);$('notice-stamp-title').textContent=n?'Originally posted on':'Will be posted on';$('notice-stamp').textContent=format(n?.postedAt||new Date().toISOString());};
 const updatePills=()=>pills.querySelectorAll('button').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.category.toLowerCase()===category.value.trim().toLowerCase())));
 pills.addEventListener('click',e=>{const b=e.target.closest('button[data-category]');if(!b)return;category.value=b.dataset.category;updatePills();});
 category.addEventListener('input',updatePills);
 description.addEventListener('input',()=>counter.textContent=`${description.value.length}/1500`);
 function pickedRender(){picked.replaceChildren();picked.hidden=!attachment;drop.hidden=!!attachment;
  if(!attachment)return;
  const icon=document.createElement('i');icon.className='fa-solid fa-paperclip';const text=document.createElement('span');text.textContent=attachment.name+' ('+Math.ceil(attachment.size/1024)+' KB)';
  const remove=document.createElement('button');remove.type='button';remove.setAttribute('aria-label','Remove attachment');remove.innerHTML='<i class="fa-solid fa-xmark"></i>';remove.addEventListener('click',()=>{attachment=null;pickedRender();});picked.append(icon,text,remove);
 }
 function handleFile(file){if(!file)return;
  if(!(file.type==='application/pdf'||file.type.startsWith('image/'))){message.textContent='Only PDF or image files are supported.';return;}
  if(file.size>10*1024*1024){message.textContent='Attachment must be smaller than 10 MB.';return;}
  if(file.size>400*1024){message.textContent='This offline version can only save files up to 400 KB. Please choose a smaller file.';return;}
  const reader=new FileReader();reader.onload=()=>{attachment={name:file.name,type:file.type,size:file.size,dataUrl:String(reader.result)};pickedRender();message.textContent='';};reader.onerror=()=>message.textContent='Could not read attachment.';reader.readAsDataURL(file);
 }
 fileInput.addEventListener('change',()=>{handleFile(fileInput.files[0]);fileInput.value='';});
 ['dragover','dragenter'].forEach(t=>drop.addEventListener(t,e=>{e.preventDefault();drop.classList.add('dragging');}));
 ['dragleave','drop'].forEach(t=>drop.addEventListener(t,e=>{e.preventDefault();drop.classList.remove('dragging');}));
 drop.addEventListener('drop',e=>handleFile(e.dataTransfer?.files?.[0]));
 const save=()=>{try{localStorage.setItem(key,JSON.stringify(notices));return true;}catch(e){message.textContent='Not enough browser storage to save the notice or attachment.';return false;}};
 const make=(tag,cls,value)=>{const el=document.createElement(tag);if(cls)el.className=cls;if(value!==undefined)el.textContent=value;return el;};
 function reset(){editId=null;attachment=null;form.reset();submit.innerHTML='<i class="fa-solid fa-paper-plane"></i> Publish notice';cancel.hidden=true;cancel.style.display='none';counter.textContent='0/1500';pickedRender();updatePills();stamp();}
 function render(){list.replaceChildren();if(!notices.length){list.append(make('p','management-help','No notices yet. Publish your first notice above.'));return;}
  [...notices].sort((a,b)=>(new Date(b.postedAt||b.createdAt||0))-(new Date(a.postedAt||a.createdAt||0))).forEach(item=>{
   const box=make('article','notice-board-item');box.append(make('h4','',item.title),make('span','notice-board-meta',format(item.postedAt)||item.date||''));
   const status=make('span','ev-notice-status'+(isExpired(item)?' expired':''),isExpired(item)?'Expired':'Active');box.append(status);
   box.append(make('div','notice-board-meta',item.category||'General'),make('p','',item.description));
   if(item.attachment?.name){const link=make('a','ev-notice-file',item.attachment.name);if(item.attachment.dataUrl){link.href=item.attachment.dataUrl;link.download=item.attachment.name;}else{link.title='Attachment file was not saved';}box.append(link);}
   const actions=make('div','notice-item-actions'),edit=make('button','action-btn edit-btn'),del=make('button','action-btn delete-btn');
   edit.type=del.type='button';edit.title='Edit notice';del.title='Delete notice';edit.setAttribute('aria-label','Edit '+item.title);del.setAttribute('aria-label','Delete '+item.title);
   edit.innerHTML='<i class="fa-solid fa-pen"></i>';del.innerHTML='<i class="fa-solid fa-trash"></i>';
   edit.addEventListener('click',()=>{editId=item.id;title.value=item.title;category.value=item.category;description.value=item.description;expiry.value=item.expiresAt||'';attachment=item.attachment||null;counter.textContent=description.value.length+'/1500';submit.textContent='Save Changes';cancel.hidden=false;cancel.style.display='inline-block';updatePills();pickedRender();stamp();form.scrollIntoView({behavior:'smooth',block:'start'});title.focus();});
   del.addEventListener('click',()=>{if(!confirm('Delete this notice?'))return;const previous=notices;notices=notices.filter(n=>n.id!==item.id);if(!save()){notices=previous;return;}if(editId===item.id)reset();message.textContent='Notice deleted.';render();window.eduVaultLog?.('info','Deleted notice: '+item.title);});
   actions.append(edit,del);box.append(actions);list.append(box);
  });
 }
 form.addEventListener('submit',e=>{e.preventDefault();if(!form.reportValidity())return;
  if(expiry.value&&expiry.value<today()){message.textContent='Expiry date cannot be in the past.';return;}
  const prior=notices.find(n=>n.id===editId);const item={id:editId||('notice-'+Date.now()+'-'+Math.random().toString(36).slice(2,7)),title:title.value.trim(),category:category.value.trim(),description:description.value.trim(),expiresAt:expiry.value||null,attachment,postedAt:prior?.postedAt||new Date().toISOString(),createdAt:prior?.createdAt||Date.now()};
  if(!item.title||!item.category||!item.description)return;const previous=notices;notices=editId?notices.map(n=>n.id===editId?item:n):[...notices,item];if(!save()){notices=previous;return;}
  const editing=!!editId;reset();render();message.textContent=editing?'Notice updated successfully.':'Notice published successfully.';window.eduVaultLog?.('success',(editing?'Updated':'Published')+' notice: '+item.title);
 });
 cancel.addEventListener('click',()=>{reset();message.textContent='Editing cancelled.';});
 expiry.min=today();reset();render();
})();
</script><script id="eduvault-mobile-outside-close">
(function(){
 const app=document.querySelector('.admin-app'),toggle=document.getElementById('sidebar-collapse-toggle');
 if(!app||!toggle)return;
 const isMobile=()=>window.matchMedia('(max-width:700px)').matches;
 function close(){app.classList.add('sidebar-collapsed');toggle.setAttribute('aria-expanded','false');toggle.setAttribute('aria-label','Open menu');toggle.title='Open menu';document.body.classList.remove('ev-drawer-open');}
 document.addEventListener('pointerdown',e=>{if(isMobile()&&!app.classList.contains('sidebar-collapsed')&&!e.target.closest('.side-navigation')&&!e.target.closest('#sidebar-collapse-toggle'))close();});
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&isMobile()&&!app.classList.contains('sidebar-collapsed')){close();toggle.focus();}});
})();
</script></body>
</html>