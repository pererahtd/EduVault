/* EduVault study sessions – shared by groups.php and group_discussion.php */
const EduSessions = (() => {
  let endpoint = 'groups.php';

  const esc = value => String(value == null ? '' : value)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  const parse = value => new Date(String(value).replace(' ', 'T'));
  const pad = n => String(n).padStart(2, '0');
  const fmtDay = d => d.toLocaleDateString(undefined, { weekday: 'short', day: 'numeric', month: 'short' });
  const fmtTime = d => d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  const notify = (message, isError) => {
    if (typeof UI !== 'undefined' && UI.toast) UI.toast(message, isError ? 'error' : 'success');
    else if (isError) alert(message);
  };

  function init(url) { endpoint = url; }

  async function api(action, values = {}, method = 'POST') {
    let response;
    if (method === 'GET') {
      response = await fetch(`${endpoint}?${new URLSearchParams({ action, ...values })}`, { headers: { Accept: 'application/json' } });
    } else {
      response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', Accept: 'application/json' },
        body: new URLSearchParams({ action, ...values })
      });
    }
    let data;
    try { data = await response.json(); }
    catch (error) { throw new Error('The server returned an invalid response.'); }
    if (!response.ok || !data.ok) throw new Error(data.error || 'The request could not be completed.');
    return data;
  }
  async function list(groupId) {
    const data = await api('get_sessions', groupId ? { groupId: String(groupId) } : {}, 'GET');
    return data.sessions;
  }

  function state(s) {
    if (s.status === 'cancelled') return 'cancelled';
    const now = new Date();
    const start = parse(s.startsAt);
    const end = parse(s.endsAt);
    if (end < now) return 'done';
    if (start <= now) return 'live';
    if (start - now < 3600000) return 'soon';
    return 'upcoming';
  }
  const isActive = s => ['upcoming', 'soon', 'live'].includes(state(s));
  const upcoming = sessions => sessions.filter(isActive);
  const LABELS = { upcoming: 'Upcoming', soon: 'Starting soon', live: 'Live now', done: 'Completed', cancelled: 'Cancelled' };

  function locationHTML(location) {
    if (!location) return '';
    if (/^https?:\/\//i.test(location)) {
      return `<span><i class="fas fa-video"></i><a href="${esc(location)}" target="_blank" rel="noopener noreferrer">Join meeting link</a></span>`;
    }
    return `<span><i class="fas fa-location-dot"></i>${esc(location)}</span>`;
  }

  function cardHTML(s, showGroup) {
    const st = state(s);
    const start = parse(s.startsAt);
    const end = parse(s.endsAt);
    const active = isActive(s);
    const names = (s.going_names || []).map(esc).join(', ');
    const rsvpBtn = (value, label, icon) =>
      `<button class="sx-rsvp ${value}${s.my_rsvp === value ? ' on' : ''}" data-rsvp="${value}"><i class="${icon}"></i> ${label}</button>`;
    return `
      <article class="sx-card sx-${st}" data-id="${s.id}" style="--c:${esc(s.group_color || '#00a859')}">
        <div class="sx-date"><strong>${start.getDate()}</strong><span>${esc(start.toLocaleDateString(undefined, { month: 'short' }))}</span></div>
        <div class="sx-body">
          <div class="sx-top">
            <h4 class="sx-title">${esc(s.title)}</h4>
            <span class="sx-chip sx-chip-${st}">${LABELS[st]}</span>
            ${s.series_count > 1 ? '<span class="sx-chip sx-chip-rep"><i class="fas fa-repeat"></i> Weekly</span>' : ''}
            ${s.visibility === 'invited' ? '<span class="sx-chip sx-chip-priv"><i class="fas fa-lock"></i> Private</span>' : ''}
          </div>
          <div class="sx-meta">
            <span><i class="far fa-clock"></i>${esc(fmtDay(start))}, ${esc(fmtTime(start))} – ${esc(fmtTime(end))}</span>
            ${locationHTML(s.location)}
            ${showGroup ? `<span><i class="fas fa-users"></i>${esc(s.group_name)}</span>` : ''}
          </div>
          ${s.description ? `<p class="sx-desc">${esc(s.description)}</p>` : ''}
          ${s.visibility === 'invited' && (s.invited_names || []).length ? `<div class="sx-going"><i class="fas fa-envelope"></i> Invited: ${s.invited_names.map(esc).join(', ')}</div>` : ''}
          <div class="sx-going">${s.going_count} going${s.maybe_count ? ` · ${s.maybe_count} maybe` : ''}${names ? ` — ${names}` : ''}</div>
          ${active ? `<div class="sx-actions">
            ${rsvpBtn('going', 'Going', 'fas fa-check')}${rsvpBtn('maybe', 'Maybe', 'fas fa-question')}${rsvpBtn('no', "Can't go", 'fas fa-xmark')}
            ${s.can_manage ? '<button class="sx-btn sx-danger" data-cancel><i class="fas fa-ban"></i> Cancel</button>' : ''}
          </div>` : ''}
        </div>
      </article>`;
  }

  function render(container, sessions, opts = {}) {
    container._sx = { sessions, opts };
    const up = upcoming(sessions);
    const rest = sessions.filter(s => !isActive(s)).reverse();
    const canCreate = opts.canCreate && (opts.groupId || (opts.groups && opts.groups.length));
    container.innerHTML = `
      <div class="sx-head">
        <div><h3>Study sessions</h3><p>${up.length} upcoming</p></div>
        ${canCreate ? '<button class="sx-btn sx-primary" data-new><i class="fas fa-plus"></i> New session</button>' : ''}
      </div>
      ${up.length ? up.map(s => cardHTML(s, opts.showGroup)).join('') : `
        <div class="sx-empty"><i class="fas fa-calendar-days"></i><strong>No upcoming sessions</strong><br>Schedule one so everyone can plan ahead.</div>`}
      ${rest.length ? `<details class="sx-past"><summary>Past &amp; cancelled (${rest.length})</summary>${rest.map(s => cardHTML(s, opts.showGroup)).join('')}</details>` : ''}`;
    if (container._sxBound) return;
    container._sxBound = true;
    container.addEventListener('click', async event => {
      const { sessions: current, opts: o } = container._sx;
      const card = event.target.closest('.sx-card');
      const id = card ? Number(card.dataset.id) : 0;
      const session = current.find(item => item.id === id);
      try {
        if (event.target.closest('[data-new]')) {
          openCreate(o.groupId, o.groups, o.onChange);
          return;
        }
        const rsvp = event.target.closest('[data-rsvp]');
        if (rsvp && session) {
          await api('rsvp_session', { sessionId: String(id), status: rsvp.dataset.rsvp });
          if (o.onChange) o.onChange();
          return;
        }
        if (event.target.closest('[data-cancel]') && session) {
          if (!confirm(`Cancel "${session.title}"?`)) return;
          let scope = 'one';
          if (session.series_count > 1 && confirm('This is a weekly session. Cancel ALL upcoming sessions in the series too?\n\nOK = cancel all upcoming\nCancel = only this one')) scope = 'future';
          await api('cancel_session', { sessionId: String(id), scope });
          notify('Session cancelled');
          if (o.onChange) o.onChange();
        }
      } catch (error) { notify(error.message, true); }
    });
  }

  function nextHTML(sessions) {
    const next = upcoming(sessions)[0];
    if (!next) {
      return `<div class="sx-side"><div class="sx-side-h"><i class="fas fa-calendar-days"></i> Next study session</div>
        <p class="sx-side-e">Nothing scheduled yet.</p>
        <button class="sx-btn sx-soft sx-block" data-goto><i class="fas fa-plus"></i> Schedule one</button></div>`;
    }
    const start = parse(next.startsAt);
    return `<div class="sx-side"><div class="sx-side-h"><i class="fas fa-calendar-days"></i> Next study session</div>
      <div class="sx-side-t">${esc(next.title)}</div>
      <div class="sx-side-m">${esc(fmtDay(start))}, ${esc(fmtTime(start))}</div>
      <button class="sx-btn sx-soft sx-block" data-goto>View sessions</button></div>`;
  }

  function openCreate(groupId, groups, onDone) {
    const d = new Date(Date.now() + 3600000);
    d.setMinutes(0, 0, 0);
    const defaultStart = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:00`;
    const overlay = document.createElement('div');
    overlay.className = 'sx-overlay';
    overlay.innerHTML = `
      <div class="sx-modal">
        <h3>Schedule a study session</h3>
        <p class="sx-sub">Group members will see it and can RSVP.</p>
        ${groupId ? '' : `<div class="sx-field"><label class="sx-label">Group</label>
          <select class="sx-input" id="sxGroup">${groups.map(g => `<option value="${g.id}">${esc(g.name)}</option>`).join('')}</select></div>`}
        <div class="sx-field"><label class="sx-label">Title</label><input class="sx-input" id="sxTitle" maxlength="120" placeholder="e.g. Database revision"></div>
        <div class="sx-row">
          <div class="sx-field"><label class="sx-label">Date &amp; time</label><input class="sx-input" type="datetime-local" id="sxStart" value="${defaultStart}"></div>
          <div class="sx-field"><label class="sx-label">Duration</label>
            <select class="sx-input" id="sxDuration"><option value="30">30 min</option><option value="60" selected>1 hour</option><option value="90">1.5 hours</option><option value="120">2 hours</option><option value="180">3 hours</option></select></div>
        </div>
        <div class="sx-field"><label class="sx-label">Location or meeting link (optional)</label><input class="sx-input" id="sxLocation" maxlength="255" placeholder="Library room 2 / https://meet.google.com/..."></div>
        <div class="sx-field"><label class="sx-label">Description (optional)</label><textarea class="sx-input" id="sxDesc" maxlength="1000" placeholder="What will you study?"></textarea></div>
        <div class="sx-row">
          <label class="sx-check sx-field"><input type="checkbox" id="sxRepeat"> Repeat weekly</label>
          <div class="sx-field" id="sxWeeksBox" style="display:none"><label class="sx-label">For how many weeks (2-12)</label><input class="sx-input" type="number" id="sxWeeks" min="2" max="12" value="4"></div>
        </div>
        <div class="sx-field">
          <label class="sx-label">Who can see this session?</label>
          <label class="sx-check"><input type="radio" name="sxVis" value="invited" checked> Only selected members</label>
          <label class="sx-check"><input type="radio" name="sxVis" value="group"> Everyone in the group</label>
        </div>
        <div class="sx-field" id="sxInviteBox">
          <label class="sx-label">Select members</label>
          <div class="sx-members" id="sxMembers">Loading...</div>
        </div>
        <p class="sx-error" id="sxError"></p>
        <div class="sx-foot"><button class="sx-btn sx-ghost" id="sxCancel">Cancel</button><button class="sx-btn sx-primary" id="sxSave"><i class="fas fa-calendar-check"></i> Schedule</button></div>
      </div>`;
    document.body.appendChild(overlay);
    const $ = id => overlay.querySelector('#' + id);
    const close = () => overlay.remove();
    $('sxRepeat').addEventListener('change', () => { $('sxWeeksBox').style.display = $('sxRepeat').checked ? '' : 'none'; });
    $('sxCancel').addEventListener('click', close);
    overlay.addEventListener('click', event => { if (event.target === overlay) close(); });
    $('sxTitle').focus();
    const currentGroup = () => groupId || $('sxGroup').value;
    const visibility = () => overlay.querySelector('input[name="sxVis"]:checked').value;
    async function loadMembers() {
      const box = $('sxMembers');
      box.textContent = 'Loading...';
      try {
        const data = await api('get_session_members', { groupId: String(currentGroup()) }, 'GET');
        box.innerHTML = data.members.length
          ? data.members.map(m => `<label class="sx-check"><input type="checkbox" value="${m.id}"> ${esc(m.name)}</label>`).join('')
          : 'No other members in this group yet.';
      } catch (error) { box.textContent = error.message; }
    }
    overlay.querySelectorAll('input[name="sxVis"]').forEach(r => r.addEventListener('change', () => {
      $('sxInviteBox').style.display = visibility() === 'invited' ? '' : 'none';
    }));
    if ($('sxGroup')) $('sxGroup').addEventListener('change', loadMembers);
    loadMembers();
    $('sxSave').addEventListener('click', async () => {
      const title = $('sxTitle').value.trim();
      const start = $('sxStart').value;
      if (!title) { $('sxError').textContent = 'Please enter a title.'; return; }
      if (!start || parse(start) < new Date()) { $('sxError').textContent = 'Please pick a time in the future.'; return; }
      const invitees = [...overlay.querySelectorAll('#sxMembers input:checked')].map(i => i.value);
      if (visibility() === 'invited' && !invitees.length) { $('sxError').textContent = 'Select at least one member, or choose "Everyone in the group".'; return; }
      $('sxSave').disabled = true;
      $('sxError').textContent = '';
      try {
        await api('create_session', {
          groupId: String(groupId || $('sxGroup').value),
          title, start,
          duration: $('sxDuration').value,
          location: $('sxLocation').value.trim(),
          description: $('sxDesc').value.trim(),
          weeks: $('sxRepeat').checked ? $('sxWeeks').value : '1',
          visibility: visibility(),
          invitees: invitees.join(',')
        });
        close();
        notify('Study session scheduled');
        if (onDone) onDone();
      } catch (error) {
        $('sxError').textContent = error.message;
        $('sxSave').disabled = false;
      }
    });
  }

  return { init, api, list, render, upcoming, nextHTML, openCreate };
})();