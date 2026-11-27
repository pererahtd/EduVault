<?php 
error_reporting(E_ALL);


require_once 'bootstrap_2.php'; 
require_once 'db.php';
require_once 'batches_store.php'; // Admin batches (server-side JSON)
require_once 'materials_store.php'; // CSRF token helper + material storage

$user = current_user(); 
$loggedInUser = $user ? first_name($user['full_name']) : 'Guest';
$adminBatches = batch_load();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Your Uploads</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="youruploads.css">
    <link rel="stylesheet" href="mediaqueries.css">
    <script>
        const savedTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body data-theme="light">
    <!-- SIDEBAR -->
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
            <li class="active" onclick="window.location.href='youruploads.php'"><i class="fas fa-cloud-upload-alt"></i> Your Uploads</li>
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="color: var(--text-muted); cursor: pointer; margin-right: 10px;"></i>
                Your Uploads
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

        <div class="content-area">
            <section class="content-banner">
                <div class="banner-copy">
                    <div class="banner-badge"><i class="fa-solid fa-cloud-arrow-up"></i> STUDENT RESOURCE SHARING</div>
                    <h1>Your Uploads</h1>
                    <p>Upload your study materials, track pending approvals, and manage your shared documents seamlessly.</p>
                </div>
                <button class="btn-upload-file" id="openUploadModalBtn" type="button">
                    <i class="fa-solid fa-plus"></i> Upload New PDF
                </button>
            </section>

            <div class="tabs-container">
                <button class="tab-btn active" onclick="switchTab('pending', this)">Pending Uploads <span id="pendingCount" class="badge">0</span></button>
                <button class="tab-btn" onclick="switchTab('approved', this)">Approved Materials <span id="approvedCount" class="badge">0</span></button>
                <button class="tab-btn" onclick="switchTab('rejected', this)">Rejected <span id="rejectedCount" class="badge">0</span></button>
            </div>
            <div class="cards-grid" id="uploadsGrid"></div>
        </div>
    </main>

    <!-- UPLOAD MODAL -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h2 id="modalTitle">Upload a PDF File</h2>
            <form id="uploadForm">
                <div class="form-group">
                    <label>Choose File (PDF only)</label>
                    <input type="file" id="fileInput" accept=".pdf" required>
                </div>
                <div class="form-group">
                    <label>Save as (Rename File)</label>
                    <input type="text" id="fileNameInput" placeholder="Enter custom file name" required>
                </div>
                <div class="form-group">
                    <label>Contributor</label>
                    <input type="text" id="contributorInput" placeholder="Enter contributor name" required>
                </div>
                <div class="form-group">
                    <label>Batch</label>
                    <select id="batchInput">
                        <option value="" selected>Select Batch / General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Module</label>
                    <input type="text" id="moduleInput" placeholder="Enter module name" required>
                </div>
                <button type="submit" class="submit-file-btn" id="modalSubmitBtn">Upload this file</button>
            </form>
        </div>
    </div>

    <!-- VIEWER MODAL -->
    <div id="viewerModal" class="modal">
        <div class="modal-content viewer-content">
            <span class="close-btn" id="closeViewerBtn">&times;</span>
            <h2 id="viewerFileName">View PDF</h2>
            <iframe id="pdfViewerFrame" src="" width="100%" height="500"></iframe>
        </div>
    </div>

    <script>
        // Sidebar Toggle Code
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }
        if (!sidebar.querySelector('.drawer-close')) { 
            const cb = document.createElement('button');
            cb.type = 'button'; 
            cb.className = 'drawer-close'; 
            cb.setAttribute('aria-label', 'Close menu');
            cb.innerHTML = '<i class="fas fa-xmark"></i>'; 
            cb.addEventListener('click', () => sidebar.classList.remove('collapsed')); 
            sidebar.appendChild(cb); 
        }   document.addEventListener('click', e => 
        { 
            if (window.innerWidth <= 992 && sidebar.classList.contains('collapsed') && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('collapsed'); 
        });

        // Theme Toggle with Local Storage
        const themeToggleBtn = document.getElementById('theme-toggle');

        // get the previous theme, or keep light 
        const currentTheme = localStorage.getItem('eduvault_theme') || 'light';
        document.documentElement.setAttribute('data-theme', currentTheme);
        document.body.setAttribute('data-theme', currentTheme);

        if (themeToggleBtn) {
            // correct the icon
            themeToggleBtn.querySelector('i').className = currentTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            
            // if the button press
            themeToggleBtn.addEventListener('click', () => {
                const nextTheme = document.body.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                
                document.documentElement.setAttribute('data-theme', nextTheme);
                document.body.setAttribute('data-theme', nextTheme);
                
                // save the new theme in browser
                localStorage.setItem('eduvault_theme', nextTheme);
                
                themeToggleBtn.querySelector('i').className = nextTheme === 'dark' ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            });
        }

    let uploads = [];
    const CSRF = '<?php echo function_exists('mat_csrf') ? mat_csrf() : ''; ?>';
    const LOGGED_IN = <?php echo $user ? 'true' : 'false'; ?>;
    // By Admin Create Batches (server-side)
    const ADMIN_BATCHES = <?php echo json_encode($adminBatches, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    function esc(v) { return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

    let currentTab = 'pending';
    let editingId = null;
    const searchInput = document.getElementById('search');
    if (searchInput) searchInput.addEventListener('input', renderUploads);

    const openModalBtn = document.getElementById('openUploadModalBtn');
    const uploadModal = document.getElementById('uploadModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const fileNameInput = document.getElementById('fileNameInput');
    const contributorInput = document.getElementById('contributorInput');
    const batchInput = document.getElementById('batchInput');
    const moduleInput = document.getElementById('moduleInput');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');

    const viewerModal = document.getElementById('viewerModal');
    const closeViewerBtn = document.getElementById('closeViewerBtn');
    const pdfViewerFrame = document.getElementById('pdfViewerFrame');
    const viewerFileName = document.getElementById('viewerFileName');

    // Admin batches load to dropdown
   function loadAdminBatches(selectedBatch = '') {
        const list = ADMIN_BATCHES.slice();
        
        if (list.length === 0) {
            list.push('2025.1', '2025.2', '2026.1', '2026.2');
        }
        
        if (selectedBatch && !list.includes(selectedBatch)) list.push(selectedBatch);

        batchInput.innerHTML = '<option value="">Select Batch / General</option>';
        list.forEach(batch => {
            const opt = document.createElement('option');
            opt.value = batch;
            opt.textContent = batch;
            batchInput.appendChild(opt);
        });
        batchInput.value = selectedBatch || '';
        if (!selectedBatch) batchInput.selectedIndex = 0;
    }

    if (openModalBtn) openModalBtn.addEventListener('click', () => {
        editingId = null;
        modalTitle.innerText = "Upload a PDF File";
        modalSubmitBtn.innerText = "Upload this file";
        fileInput.required = true;
        fileInput.value = "";
        fileNameInput.value = "";
        contributorInput.value = "";
        loadAdminBatches();
        moduleInput.value = "";
        uploadModal.style.display = 'flex';
    });

    if (fileInput) fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            fileNameInput.value = e.target.files[0].name;
        }
    });

    closeModalBtn.addEventListener('click', () => uploadModal.style.display = 'none');
    closeViewerBtn.addEventListener('click', () => viewerModal.style.display = 'none');

    window.addEventListener('click', (e) => {
        if (e.target === uploadModal) uploadModal.style.display = 'none';
        if (e.target === viewerModal) viewerModal.style.display = 'none';
    });

    function switchTab(tab, clickedButton) {
        currentTab = tab;
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        if (clickedButton) clickedButton.classList.add('active');
        renderUploads();
    }

    function renderUploads() {
        const grid = document.getElementById('uploadsGrid');
        if (!grid) return;
        grid.innerHTML = '';

        const searchTerm = (searchInput?.value || '').trim().toLowerCase();
        const filtered = uploads.filter(item =>
            String(item.status || '').trim().toLowerCase() === currentTab &&
            [item.name, item.contributor, item.batch, item.module].some(value =>
                String(value || '').toLowerCase().includes(searchTerm)
            )
        );

        document.getElementById('pendingCount').innerText = uploads.filter(i => String(i.status || '').trim().toLowerCase() === 'pending').length;
        document.getElementById('approvedCount').innerText = uploads.filter(i => String(i.status || '').trim().toLowerCase() === 'approved').length;
        document.getElementById('rejectedCount').innerText = uploads.filter(i => String(i.status || '').trim().toLowerCase() === 'rejected').length;

        if (filtered.length === 0) {
            grid.innerHTML = `<p style="grid-column: 1/-1; text-align:center; color:#777; padding: 40px;">No ${currentTab} materials found.</p>`;
            return;
        }

        filtered.forEach(item => {
            const statusLower = String(item.status || '').trim().toLowerCase();
            let card = document.createElement('div');
            card.className = 'resource-card';
            card.innerHTML = `
                <span class="status-tag status-${esc(statusLower)}">${esc(item.status).toUpperCase()}</span>
                <div class="card-icon"><i class="fa-solid fa-file-pdf"></i></div>
                <a class="file-title" onclick="downloadFile('${esc(item.id)}')" title="Click to Download">${esc(item.name)}</a>
                <div class="file-meta">Contributor: ${esc(item.contributor)} | Batch: ${esc(item.batch || 'General')} | Module: ${esc(item.module)}</div>
                
                <div class="card-actions">
                    <button class="btn-sm btn-view" onclick="viewFile('${esc(item.id)}')"><i class="fa-solid fa-eye"></i> View</button>
                    ${statusLower === 'pending' ? `<button class="btn-sm btn-edit" onclick="editFile('${esc(item.id)}')"><i class="fa-solid fa-pen"></i> Edit</button>` : ''}
                    ${statusLower !== 'approved' ? `<button class="btn-sm btn-delete" onclick="deleteFile('${esc(item.id)}')"><i class="fa-solid fa-trash"></i> Remove</button>` : ''}
                </div>
            `;
            grid.appendChild(card);
        });
    }

    async function api(action, fd) {
        const opt = { credentials: 'same-origin', cache: 'no-store' };
        if (fd) { opt.method = 'POST'; opt.body = fd; opt.headers = { 'X-CSRF-Token': CSRF }; }
        const res = await fetch('materials_api.php?action=' + encodeURIComponent(action), opt);
        let data = null;
        try { data = await res.json(); } catch (_) {}
        if (!res.ok || !data || !data.ok) throw new Error((data && data.error) || 'Request failed (' + res.status + ')');
        return data;
    }

    async function loadUploads() {
        if (!LOGGED_IN) { renderUploads(); return; }
        try {
            const data = await api('list');
            uploads = data.items || [];
        } catch (err) { console.error(err); }
        renderUploads();
    }

    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData();
        fd.append('name', fileNameInput.value.trim());
        fd.append('contributor', contributorInput.value.trim());
        fd.append('batch', batchInput.value);
        fd.append('module', moduleInput.value.trim());

        let action = 'update';
        if (editingId) {
            fd.append('id', editingId);
        } else {
            const file = fileInput.files[0];
            if (!file) return;
            if (!/\.pdf$/i.test(file.name)) { alert('Only PDF files are allowed.'); return; }
            if (file.size > 10 * 1024 * 1024) { alert('PDF must be 10 MB or smaller.'); return; }
            fd.append('file', file);
            action = 'upload';
        }

        modalSubmitBtn.disabled = true;
        const label = modalSubmitBtn.innerText;
        modalSubmitBtn.innerText = action === 'upload' ? 'Uploading...' : 'Saving...';
        try {
            await api(action, fd);
            uploadModal.style.display = 'none';
            if (action === 'upload') {
                currentTab = 'pending';
                document.querySelectorAll('.tab-btn').forEach((b, idx) => b.classList.toggle('active', idx === 0));
            }
            await loadUploads();
        } catch (err) {
            alert(err.message);
        } finally {
            modalSubmitBtn.disabled = false;
            modalSubmitBtn.innerText = label;
        }
    });

    function editFile(id) {
        let item = uploads.find(i => i.id === id);
        if (item) {
            editingId = id;
            modalTitle.innerText = "Edit Pending File Details";
            modalSubmitBtn.innerText = "Save Changes";
            fileInput.required = false;
            fileNameInput.value = item.name;
            contributorInput.value = item.contributor;
            loadAdminBatches(item.batch);
            moduleInput.value = item.module;
            uploadModal.style.display = 'flex';
        }
    }

    async function deleteFile(id) {
        if (!confirm("Are you sure you want to remove this upload?")) return;
        const fd = new FormData();
        fd.append('id', id);
        try { await api('delete', fd); await loadUploads(); }
        catch (err) { alert(err.message); }
    }

    function viewFile(id) {
        let item = uploads.find(i => i.id === id);
        if (item) {
            viewerFileName.innerText = item.name;
            pdfViewerFrame.src = item.url;
            viewerModal.style.display = 'flex';
        }
    }

    function downloadFile(id) {
        let item = uploads.find(i => i.id === id);
        if (item) {
            const a = document.createElement('a');
            a.href = item.dl;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    }

    loadUploads();
    setInterval(() => { if (!document.hidden) loadUploads(); }, 30000);
    </script>
    <script src="badges.js"></script>
<script src='site-search.js'></script></body>
</html>