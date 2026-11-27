<?php
require_once 'bootstrap_2.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user = current_user();
$loggedInUser = $user ? first_name($user['full_name'] ?? $user['name'] ?? 'Student') : 'Guest';

const FEEDBACK_TO         = 'eduvaultdveteam@gmail.com';
const FEEDBACK_GAS_URL    = 'https://script.google.com/macros/s/AKfycbyi8JxJHtac0cxtFc9RAOlE5pE3z-NerQdJPhv1oJCWEWggCoZT4qQ4MnzGGRxY4UCeVg/exec';
const FEEDBACK_GAS_SECRET = 'random';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduVault | Feedback</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="fb.css">
    <link rel="stylesheet" href="mediaqueries.css">
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
            <li onclick="window.location.href='index.php'"><i class="fas fa-home"></i> Dashboard</li>
            <li onclick="window.location.href='profile.php'"><i class="far fa-user"></i> My Profile</li>
            <li onclick="window.location.href='youruploads.php'"><i class="fas fa-cloud-upload-alt"></i> Your Uploads</li>
            <li onclick="window.location.href='study-material.php'"><i class="fas fa-book"></i> Study Material</li>
            <li onclick="window.location.href='groups.php'"><i class="fas fa-users"></i> Groups</li>
            <li onclick="window.location.href='notice-board_forst.php'"><i class="fas fa-clipboard-list"></i> Notice board</li>
            <li onclick="window.location.href='discussion-qa.php'"><i class="fas fa-comments"></i> Discussion / Q&amp;A</li>
        </ul>

        <ul class="bottom-menu">
            <li class="active" onclick="window.location.href='feedback.php'"><i class="far fa-comment-dots"></i> Feedback</li>
            <li id="logout-btn" onclick="window.location.href='<?= isset($user) && $user ? 'logout.php' : 'login.php' ?>'">
                <i class="fas fa-sign-out-alt"></i> <?= isset($user) && $user ? 'Logout' : 'Login' ?>
            </li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <h2>
                <i class="fas fa-bars" id="menu-toggle" style="color: var(--text-muted); cursor: pointer; margin-right: 10px;"></i>
                Student Feedback
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
                        $isLoggedIn = isset($user) && !empty($user);
                        $rawName    = $isLoggedIn ? trim((string)($user['full_name'] ?? $user['name'] ?? '')) : '';

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

        <section class="feedback-banner">
            <h1>We Value Your Feedback</h1>
            <p>Help us improve EduVault! Share your suggestions, report system issues, or give feedback on materials shared across NSBM modules.</p>
        </section>

        <div class="feedback-card">
            <div class="alert" id="successAlert">
                <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Thank you! Your feedback has been submitted successfully.
            </div>
            <div class="alert" id="errorAlert" style="display:none; background:#fdecea; color:#b71c1c; border-color:#f5c2c0;">
                <i class="fas fa-circle-exclamation" style="margin-right: 5px;"></i> <span id="errorText"></span>
            </div>

            <form id="feedbackForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" id="name" name="name" maxlength="100" placeholder="e.g. Kasun Perera" value="<?= $user ? htmlspecialchars((string) ($user['full_name'] ?? $user['name'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label>NSBM Email Address</label>
                        <input type="email" id="email" name="email" maxlength="150" placeholder="e.g. kasun@students.nsbm.ac.lk" value="<?= $user ? htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES, 'UTF-8') : '' ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Feedback Category</label>
                    <select id="category" name="category" required>
                        <option value="" disabled selected>Select Category</option>
                        <option value="Study Materials">Study Materials & Notes</option>
                        <option value="Bug Report">Technical Issue / Bug</option>
                        <option value="Feature Request">New Feature Suggestion</option>
                        <option value="General">General Experience</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Overall Experience Rating</label>
                    <div class="rating-stars">
                        <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                        <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                        <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                        <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                        <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Your Message / Feedback Details</label>
                    <textarea id="comments" name="comments" maxlength="3000" placeholder="Write your feedback or bug details here..." required></textarea>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">Submit Feedback</button>
            </form>
        </div>
    </main>

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

        const feedbackForm = document.getElementById("feedbackForm");
        const successAlert = document.getElementById("successAlert");
        const errorAlert = document.getElementById("errorAlert");
        const errorText = document.getElementById("errorText");
        const submitBtn = document.getElementById("submitBtn");

        const isLoggedIn  = <?= $user ? 'true' : 'false' ?>;
        const gasUrl      = <?= json_encode(FEEDBACK_GAS_URL) ?>;
        const gasSecret   = <?= json_encode(FEEDBACK_GAS_SECRET) ?>;
        const feedbackTo  = <?= json_encode(FEEDBACK_TO) ?>;
        const accountName = <?= json_encode((string) ($user['full_name'] ?? $user['name'] ?? $user['username'] ?? '')) ?>;
        let lastSentAt    = 0;

        function showError(message) {
            successAlert.style.display = "none";
            errorText.textContent = message;
            errorAlert.style.display = "block";
        }

        if (feedbackForm) {
            feedbackForm.addEventListener("submit", async (e) => {
                e.preventDefault();
                if (!isLoggedIn) {
                    showError("Please log in to submit feedback.");
                    return;
                }
                if (Date.now() - lastSentAt < 60000) {
                    showError("Please wait a minute before sending another feedback.");
                    return;
                }

                errorAlert.style.display = "none";
                const nameVal     = feedbackForm.name.value.trim();
                const emailVal    = feedbackForm.email.value.trim();
                const categoryVal = feedbackForm.category.value;
                const ratingEl    = feedbackForm.querySelector('input[name="rating"]:checked');
                const commentsVal = feedbackForm.comments.value.trim();

                if (!nameVal) { showError("Please enter your name."); return; }
                if (!/^\S+@\S+\.\S+$/.test(emailVal)) { showError("Please enter a valid email address."); return; }
                if (!categoryVal) { showError("Please select a category."); return; }
                if (!ratingEl) { showError("Please give a star rating."); return; }
                if (!commentsVal) { showError("Please write your feedback message."); return; }

                submitBtn.disabled = true;
                const originalLabel = submitBtn.textContent;
                submitBtn.textContent = "Sending...";

                try {
                    const body = new URLSearchParams({
                        secret: gasSecret,
                        to: feedbackTo,
                        name: nameVal,
                        email: emailVal,
                        category: categoryVal,
                        rating: ratingEl.value,
                        comments: commentsVal,
                        account: accountName || nameVal
                    });

                    await fetch(gasUrl, {
                        method: "POST",
                        mode: "no-cors",
                        headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
                        body: body.toString()
                    });

                    lastSentAt = Date.now();
                    successAlert.style.display = "block";
                    feedbackForm.reset();
                    setTimeout(() => { successAlert.style.display = "none"; }, 5000);
                } catch (error) {
                    console.error("Feedback submit failed:", error);
                    showError("Could not send your feedback. Please check your internet connection and try again.");
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalLabel;
                }
            });
        }
    </script>
    <script src='site-search.js'></script>
</body>
</html>