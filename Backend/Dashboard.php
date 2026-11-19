<?php require __DIR__ . '/_auth.php'; ?>
<!DOCTYPE html>

<html data-eduvault-page="dashboard" data-eduvault-theme="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>EduVault | Dashboard</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet"/>
<link href="eduvault-design-system.css" rel="stylesheet"/><script id="eduvault-navigation-state">(function(){try{var s=sessionStorage;var via=s.getItem('ev_internal_navigation')==='1';if(via){document.documentElement.classList.add('ev-internal-navigation');s.removeItem('ev_internal_navigation');}var collapsed=s.getItem('ev_sidebar_collapsed');if(collapsed==='0')document.documentElement.classList.add('ev-sidebar-was-open');}catch(e){}})();</script>
<?php ev_head(); ?>

<!-- Show the EduVault welcome screen only once per authenticated browser session. -->
<style>html.ev-admin-welcome-seen .ev-welcome{display:none!important;animation:none!important}</style>
<script src="eduvault-admin-profile-animation.js" data-ev-session="<?php echo htmlspecialchars((string)($_SESSION['ev_csrf'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></script>
</head>
<body>
<div aria-hidden="true" class="ev-welcome"><div class="ev-welcome-inner"><div class="ev-welcome-symbol"><i class="fa-solid fa-graduation-cap"></i></div><div class="ev-welcome-name">Edu<span>Vault</span></div><div class="ev-welcome-sub">Admin Command Center</div><div class="ev-welcome-rule"></div></div></div>
<div class="admin-app"><aside aria-label="Admin navigation" class="side-navigation" id="side-navigation"><div class="sidebar-brand"><i class="fa-solid fa-graduation-cap"></i><span>Edu<span class="brand-accent">Vault</span><small>ADMIN PANEL</small></span></div><button aria-controls="side-navigation" aria-expanded="true" aria-label="Collapse sidebar" class="sidebar-collapse-toggle" id="sidebar-collapse-toggle" title="Collapse sidebar" type="button"><i aria-hidden="true" class="fa-solid fa-bars"></i></button><nav class="sidebar-links"><a aria-current="page" class="sidebar-link active" data-target="dashboard" href="Dashboard.php"><i aria-hidden="true" class="fa-solid fa-gauge-high"></i> Dashboard</a><a aria-current="false" class="sidebar-link" data-target="materials" href="Study_Materials_Manage_Batch.php"><i aria-hidden="true" class="fa-solid fa-file-circle-check"></i> Study Materials &amp; Manage Batch</a><a aria-current="false" class="sidebar-link" data-target="forums" href="Discussion_Forums_QA.php"><i aria-hidden="true" class="fa-solid fa-comments"></i> Discussion Forums &amp; Q&amp;A</a><a aria-current="false" class="sidebar-link" data-target="subjects" href="Subjects_Categories.php"><i aria-hidden="true" class="fa-solid fa-book"></i> Subjects &amp; Categories</a><a aria-current="false" class="sidebar-link" data-target="events" href="Events_Announcements.php"><i aria-hidden="true" class="fa-solid fa-calendar-days"></i> Events &amp; Announcements</a><a aria-current="false" class="sidebar-link" data-target="notice" href="Notice_Board.php"><i aria-hidden="true" class="fa-solid fa-clipboard-list"></i> Notice Board</a><a aria-current="false" class="sidebar-link" data-target="logs" href="Activity_Logs.php"><i aria-hidden="true" class="fa-solid fa-terminal"></i> Activity Logs</a></nav><div class="sidebar-bottom"><a class="sidebar-link" href="../index.php" id="sidebar-home"><i class="fa-solid fa-house"></i> Student Dashboard</a><a class="sidebar-link sidebar-signout" href="logout.php" id="sidebar-signout"><i class="fa-solid fa-right-from-bracket"></i> Sign out</a></div></aside>
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
</header><div class="workspace-view" data-view="dashboard" id="view-dashboard"><section class="page-heading">
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
                0
            </h2>
<span class="up">Real-time DB records</span>
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
<div aria-label="Weekly engagement trend" class="report-chart-wrap reference-line-chart" role="img">
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
                    684
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
</div><p class="report-help">Reports include live database records and available saved browser records. PDF and Excel download; Print offers paper settings.</p>
</div>
</section><section aria-label="Manage Users" class="dashboard-users-section" id="dashboard-manage-users"><div class="workspace-heading"><h2><i aria-hidden="true" class="fa-solid fa-users"></i> Manage Users</h2><p>Manage student accounts and access</p></div><div class="card">
<h3 class="card-title">
<i class="fa-solid fa-users"></i>
            Manage Users
        </h3>
<form class="eduvault-form" hidden="" id="user-edit-form">
    <strong>Edit User</strong><div class="eduvault-form-row">
    <label>Name<input id="user-edit-name" maxlength="100" required=""/>
    </label><label>Role<select id="user-edit-role">
    <option value="Student">Student</option>
    <option value="Admin">Admin</option></select>
    </label></div><div class="eduvault-form-actions">
    <button type="submit">Save Changes</button><button class="cancel" id="user-edit-cancel" type="button">
    Cancel</button></div></form><p aria-live="polite" class="management-message" id="users-status" role="status">
    </p><div class="table-wrapper">
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
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
</div></div><div class="workspace-view" data-view="subjects" hidden="" id="view-subjects">
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
    } catch (_) {}
    function persist() {
        try { localStorage.setItem(key,JSON.stringify(records)); } catch (_) {}
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
})();
</script>
<script>
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
<div aria-labelledby="material-details-title" aria-modal="true" class="eduvault-dialog" hidden="" id="material-details" role="dialog"><div class="eduvault-dialog-card"><h3 id="material-details-title">Study Material Details</h3><dl id="material-details-data"></dl><p style="color:#8fa4be;font-size:12px;margin-bottom:16px">Open the PDF to review it before approving or rejecting.</p><button id="material-details-close" type="button">Close</button></div></div><script>
(function(){
 'use strict';
 let materials=[], users=[], editing=null;
 function log(level,msg){window.eduVaultLog?.(level,msg);}
 function node(tag,content,cls){const n=document.createElement(tag);if(cls)n.className=cls;if(content!==undefined)n.textContent=content;return n;}
 function action(text,icon,cls,fn){const b=node('button',undefined,'action-btn '+cls);b.type='button';b.title=text;b.setAttribute('aria-label',text);const i=node('i',undefined,'fa-solid '+icon);i.setAttribute('aria-hidden','true');b.append(i);b.addEventListener('click',fn);return b;}
 function statusBadge(value){return node('span',value,'badge '+(value==='Approved'||value==='Active'||value==='admin'?'approved':value==='Pending'?'pending':'reported'));}
 function alertStatus(id,text){
  const el = document.getElementById(id);
  if(el) el.textContent=text;
 }

 // ==========================================
 // API Routing with Provided admin_dashboard.php
 // ==========================================
 const ADMIN_API='../admin_dashboard.php';

 async function fetchDashboardData(){
  try{
   const res=await fetch(ADMIN_API);
   if(!res.ok) throw new Error('Failed to load backend data. Status: ' + res.status);
   const data=await res.json();
   
   if(data && Array.isArray(data.users)){
    users=data.users;
    window.__evUsers = users;
    renderUsers();
    
    alertStatus('users-status', '');
    
    window.evRefreshLiveDashboard?.();
   }

   if(data && Array.isArray(data.pending_notes)){
    materials = data.pending_notes.map(m => ({
     id: m.id,
     title: m.title,
     subject: 'Study Material',
     uploadedBy: m.uploader,
     date: 'Recent',
     status: 'Pending',
     url: m.file_path
    }));
    window.__evMaterials = materials;
   }
  }catch(e){
   console.error(e);
   alertStatus('users-status','Failed to fetch data from Backend API.');
  }
 }

 async function deleteUser(item){
  if(!confirm(`Are you sure you want to delete ${item.name} (ID: ${item.id}) from the database?`)) return;
  
  const fd=new FormData();
  fd.append('action','delete_user');
  fd.append('user_id',item.id);

  try{
   const res=await fetch(ADMIN_API, {method:'POST', body:fd});
   const data=await res.json();
   if(res.ok && data && data.success){
    alertStatus('users-status','User deleted successfully.');
    log('warn','User deleted: '+item.name);
    fetchDashboardData();
   }else{
    alertStatus('users-status', (data && data.error) || (data && data.message) || 'Failed to delete user.');
   }
  }catch(e){
   alertStatus('users-status','Network error. Please try again.');
  }
 }

 const form=document.getElementById('user-edit-form'),nameInput=document.getElementById('user-edit-name'),roleInput=document.getElementById('user-edit-role');
 function cancelEdit(){editing=null;if(form){form.hidden=true;form.reset();}}
 const cancelBtn = document.getElementById('user-edit-cancel');
 if(cancelBtn) cancelBtn.addEventListener('click',cancelEdit);

 function renderUsers() {
  const body = document.getElementById('users-table');
  if(!body) return;
  body.replaceChildren();

  if (!users.length) {
    const tr = node('tr'), td = node('td', 'No users found in the database.');
    td.colSpan = 6;
    tr.append(td);
    body.append(tr);
    return;
  }

  users.forEach(item => {
    const row = node('tr');
    const roleDisplay = item.role ? item.role.charAt(0).toUpperCase() + item.role.slice(1) : 'Student';

    row.append(node('td', String(item.id)));
    row.append(node('td', item.name));
    row.append(node('td', item.email || '-')); 
    row.append(node('td', roleDisplay));

    const st = node('td');
    st.append(statusBadge('Active'));
    row.append(st);

    const actions = node('td');
    actions.append(action('Edit ' + item.name, 'fa-pen', 'edit-btn', () => {
      editing = item.id;
      nameInput.value = item.name;
      roleInput.value = roleDisplay;
      form.hidden = false;
      form.scrollIntoView({ behavior: 'smooth', block: 'center' });
      nameInput.focus({ preventScroll: true });
      alertStatus('users-status', 'Editing ' + item.name + '.');
    }));

    actions.append(action('Delete ' + item.name, 'fa-trash', 'delete-btn', () => deleteUser(item)));
    row.append(actions);
    body.append(row);
  });
 }

 if(form){
  form.addEventListener('submit', async (event) => {
   event.preventDefault();
   if(!form.reportValidity()) return;
   
   const user = users.find(u => u.id === editing);
   if(!user){ 
     alertStatus('users-status', 'Selected user not found.'); 
     return; 
   }

   const newName = nameInput.value.trim();
   const newRole = roleInput.value;

   const fd = new FormData();
   fd.append('action', 'update_user');
   fd.append('user_id', user.id);
   fd.append('name', newName);
   fd.append('role', newRole);

   try {
     const res = await fetch(ADMIN_API, { method: 'POST', body: fd });
     const data = await res.json();
     
     if (res.ok && data && data.success) {
       user.name = newName;
       user.role = newRole;
       cancelEdit();
       renderUsers();
       alertStatus('users-status', 'User details updated successfully.');
       log('success', 'User updated: ' + newName);
       fetchDashboardData(); 
     } else {
       alertStatus('users-status', (data && data.error) || 'Failed to update user in database.');
     }
   } catch (e) {
     alertStatus('users-status', 'Network error. Could not save changes.');
   }
  });
 }

 fetchDashboardData();
 setInterval(()=>{if(!document.hidden){fetchDashboardData();}},30000);
})();
</script>
<script>
(function(){
'use strict';
document.querySelectorAll('#view-dashboard .stat-card[data-target-view]').forEach(card=>{
 const nav=()=>{const destination=card.dataset.targetView;const sidebar=document.querySelector('.sidebar-link[data-target="'+destination+'"]');if(sidebar)sidebar.click();else if(destination==='users')window.eduVaultNavigate?.('users'); if(card.dataset.stat==='pending'){const row=[...document.querySelectorAll('#materials-table tr')].find(tr=>tr.textContent.includes('Pending'));if(row){row.scrollIntoView({behavior:'smooth',block:'center'});row.style.outline='2px solid #e7bb39';setTimeout(()=>row.style.outline='',1800);}}};
 card.addEventListener('click',nav);card.addEventListener('keydown',e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();nav();}});
});
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
  let previous='1';try{previous=sessionStorage.setItem('ev_sidebar_collapsed')??'1';}catch(e){}
  setCollapsed(previous!=='0');
  toggle.addEventListener('click',()=>setCollapsed(!app.classList.contains('sidebar-collapsed')));
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
 try{const stored=JSON.parse(localStorage.getItem(key)||'[]');if(Array.isArray(stored))notices=stored;}catch(e){}
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
</script><script defer src="dashboard_live_features.js"></script>
</body>
</html>