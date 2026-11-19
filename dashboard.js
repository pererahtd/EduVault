/* =====================================================================
   dashboard.js - the live parts of the dashboard
   1) greeting by time of day      2) live countdown to tomorrow
   3) academic calendar            4) GPA calculator (saved in the browser)
   5) discussion preview           6) pop-ups and theme switch
   ===================================================================== */
(function () {
    'use strict';

    const $ = id => document.getElementById(id);
    const pad = n => String(n).padStart(2, '0');

    /* ---------- 1. Greeting word + emoji for an hour (0-23) ---------- */
    function greetingFor(hour) {
        if (hour >= 5 && hour < 12)  return ['Good morning', '☀️'];
        if (hour >= 12 && hour < 17) return ['Good afternoon', '🌤️'];
        if (hour >= 17 && hour < 21) return ['Good evening', '🌆'];
        return ['Good night', '🌙'];
    }

    /* ---------- 2. Date text and month calendar (drawn once per day) ---------- */
    function renderDay(now) {
        if ($('wbDate')) {
            $('wbDate').textContent = now.toLocaleDateString(undefined, {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
            });
        }
        if (!$('calGrid')) return;

        const year = now.getFullYear();
        const month = now.getMonth();
        const today = now.getDate();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrev = new Date(year, month, 0).getDate();
        const offset = (new Date(year, month, 1).getDay() + 6) % 7; // Monday = 0

        const cells = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            .map(d => `<div class="cal-wd">${d}</div>`);

        // Days from the previous month that fill the first row
        for (let i = offset; i > 0; i--) {
            cells.push(`<div class="cal-d muted">${daysInPrev - i + 1}</div>`);
        }
        // Days of this month
        for (let d = 1; d <= daysInMonth; d++) {
            const dow = (offset + d - 1) % 7; // 5 = Saturday, 6 = Sunday
            const classes = ['cal-d'];
            if (dow >= 5) classes.push('wkend');
            if (d === today) classes.push('today');
            cells.push(`<div class="${classes.join(' ')}">${d}</div>`);
        }
        // Days from the next month that fill the last row
        const trailing = (7 - ((offset + daysInMonth) % 7)) % 7;
        for (let d = 1; d <= trailing; d++) {
            cells.push(`<div class="cal-d muted">${d}</div>`);
        }

        $('calGrid').innerHTML = cells.join('');
        if ($('calMonth')) {
            $('calMonth').textContent = now.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
        }
    }

    /* ---------- 3. Live clock: greeting + countdown to midnight ---------- */
    let lastDayKey = '';

    function tick() {
        const now = new Date();

        const dayKey = now.toDateString();
        if (dayKey !== lastDayKey) {
            lastDayKey = dayKey;
            renderDay(now);
        }

        const [word, emoji] = greetingFor(now.getHours());
        if ($('greetWord') && $('greetWord').textContent !== word) $('greetWord').textContent = word;
        if ($('greetEmoji') && $('greetEmoji').textContent !== emoji) $('greetEmoji').textContent = emoji;

        const dayStart = new Date(now);
        dayStart.setHours(0, 0, 0, 0);
        const nextDay = new Date(now);
        nextDay.setHours(24, 0, 0, 0);

        const left = Math.max(0, Math.floor((nextDay - now) / 1000));
        if ($('timeLeft')) {
            $('timeLeft').textContent =
                pad(Math.floor(left / 3600)) + ':' + pad(Math.floor((left % 3600) / 60)) + ':' + pad(left % 60);
        }
        if ($('timeBar')) {
            const passed = (now - dayStart) / (nextDay - dayStart);
            $('timeBar').style.width = (passed * 100).toFixed(2) + '%';
        }
    }

    /* ---------- 4. Theme switch (uses the existing top bar toggle) ---------- */
    function syncThemeSwitch() {
        const sw = $('wbThemeSwitch');
        if (!sw) return;
        const dark = document.documentElement.getAttribute('data-theme') === 'dark';
        sw.setAttribute('aria-checked', String(dark));
        sw.classList.toggle('on', dark);
        if ($('wbThemeLabel')) $('wbThemeLabel').textContent = dark ? 'Dark' : 'Light';
    }

    if ($('wbThemeSwitch')) {
        $('wbThemeSwitch').addEventListener('click', () => {
            const topToggle = $('theme-toggle');
            if (topToggle) topToggle.click();
        });
        new MutationObserver(syncThemeSwitch)
            .observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });
        syncThemeSwitch();
    }

    /* ---------- 5. GPA calculator (saved in this browser) ---------- */
    // Standard 4.0 scale. Change these values if NSBM uses a different scale.
    const GRADE_POINTS = {
        'A+': 4.0, 'A': 4.0, 'A-': 3.7,
        'B+': 3.3, 'B': 3.0, 'B-': 2.7,
        'C+': 2.3, 'C': 2.0, 'C-': 1.7,
        'D': 1.0, 'F': 0.0
    };
    const GPA_KEY = 'eduvault_gpa_v1';

    let courses = [];
    try {
        const saved = JSON.parse(localStorage.getItem(GPA_KEY) || '[]');
        courses = Array.isArray(saved) ? saved : [];
    } catch (e) {
        courses = [];
    }

    function saveCourses() {
        try { localStorage.setItem(GPA_KEY, JSON.stringify(courses)); } catch (e) { /* storage full or blocked */ }
    }

    function gpaTotals() {
        let points = 0;
        let credits = 0;
        courses.forEach(c => {
            const cr = Number(c.credits) || 0;
            if (cr > 0 && Object.prototype.hasOwnProperty.call(GRADE_POINTS, c.grade)) {
                points += GRADE_POINTS[c.grade] * cr;
                credits += cr;
            }
        });
        return { gpa: credits ? points / credits : 0, credits };
    }

    function renderGpa() {
        const { gpa, credits } = gpaTotals();
        const text = gpa.toFixed(2);
        if ($('gpaValue')) $('gpaValue').textContent = text;
        if ($('gpaBar')) $('gpaBar').style.width = Math.min(100, (gpa / 4) * 100) + '%';
        if ($('gpaCredits')) $('gpaCredits').textContent = credits;
        if ($('gpaResult')) $('gpaResult').textContent = text;
        if ($('gpaTotalCredits')) $('gpaTotalCredits').textContent = credits;
    }

    function renderGpaRows() {
        const body = $('gpaRows');
        if (!body) return;
        body.textContent = '';

        if (!courses.length) {
            body.innerHTML = '<tr><td colspan="4" class="dash-muted">No courses yet. Click "Add course".</td></tr>';
            return;
        }

        courses.forEach((c, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" maxlength="40" placeholder="Course name"></td>
                <td style="width:90px"><input type="number" min="0" max="10" step="0.5"></td>
                <td style="width:110px"><select>${Object.keys(GRADE_POINTS).map(g => `<option value="${g}">${g}</option>`).join('')}</select></td>
                <td style="width:44px"><button type="button" class="gpa-del" aria-label="Remove course">&times;</button></td>`;

            const nameInput = tr.querySelector('input[type="text"]');
            const creditInput = tr.querySelector('input[type="number"]');
            const gradeSelect = tr.querySelector('select');

            nameInput.value = c.name || '';
            creditInput.value = c.credits ?? '';
            gradeSelect.value = c.grade || 'A';

            nameInput.addEventListener('input', () => {
                courses[i].name = nameInput.value;
                saveCourses();
            });
            creditInput.addEventListener('input', () => {
                courses[i].credits = creditInput.value;
                saveCourses();
                renderGpa();
            });
            gradeSelect.addEventListener('change', () => {
                courses[i].grade = gradeSelect.value;
                saveCourses();
                renderGpa();
            });
            tr.querySelector('.gpa-del').addEventListener('click', () => {
                courses.splice(i, 1);
                saveCourses();
                renderGpaRows();
                renderGpa();
            });

            body.appendChild(tr);
        });
    }

    if ($('gpaAdd')) {
        $('gpaAdd').addEventListener('click', () => {
            courses.push({ name: '', credits: 3, grade: 'A' });
            saveCourses();
            renderGpaRows();
            renderGpa();
        });
    }
    if ($('gpaClear')) {
        $('gpaClear').addEventListener('click', () => {
            if (!courses.length || !confirm('Remove all courses from the calculator?')) return;
            courses = [];
            saveCourses();
            renderGpaRows();
            renderGpa();
        });
    }

    /* ---------- 6. Discussion preview (needs a logged-in session) ---------- */
    async function loadForum() {
        const list = $('fdList');
        if (!list) return;

        try {
            const res = await fetch('forum_api.php?action=list', { credentials: 'same-origin', cache: 'no-store' });
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Could not load');

            const posts = data.posts || [];
            const isQA = p => p.type === 'qa';
            const answered = p => !!p.answer || (p.replies || []).some(r => r.best);

            if ($('fdDisc')) $('fdDisc').textContent = posts.filter(p => !isQA(p)).length;
            if ($('fdQa')) $('fdQa').textContent = posts.filter(isQA).length;
            if ($('fdOpen')) $('fdOpen').textContent = posts.filter(p => isQA(p) && !answered(p)).length;

            const latest = posts.slice()
                .sort((a, b) => (b.createdAt || 0) - (a.createdAt || 0))
                .slice(0, 2);

            list.textContent = '';
            if (!latest.length) {
                list.innerHTML = '<span class="dash-muted">No posts yet. Be the first to start one!</span>';
                return;
            }

            latest.forEach(p => {
                const row = document.createElement('div');
                row.className = 'fd-item';

                const icon = document.createElement('i');
                icon.className = isQA(p) ? 'fas fa-circle-question fd-qa' : 'far fa-comment fd-disc';

                const text = document.createElement('span');
                text.textContent = p.title || 'Untitled';

                row.append(icon, text);
                list.appendChild(row);
            });
        } catch (err) {
            list.innerHTML = '<span class="dash-muted">Log in to see the latest discussions.</span>';
        }
    }

    /* ---------- 7. Pop-ups ---------- */
    function openModal(id) {
        const el = $(id);
        if (!el) return;
        if (id === 'gpaModal') renderGpaRows();
        el.hidden = false;
    }

    function closeModal(el) {
        el.hidden = true;
    }

    document.querySelectorAll('[data-open]').forEach(el => {
        el.addEventListener('click', () => openModal(el.dataset.open));
    });

    document.querySelectorAll('.dash-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => {
            if (e.target === overlay) closeModal(overlay);
        });
        overlay.querySelectorAll('[data-close]').forEach(btn => {
            btn.addEventListener('click', () => closeModal(overlay));
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.dash-overlay:not([hidden])').forEach(closeModal);
        }
    });

    /* ---------- start everything ---------- */
    tick();
    setInterval(tick, 1000);
    renderGpa();
    loadForum();
})();