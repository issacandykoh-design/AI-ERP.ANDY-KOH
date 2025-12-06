// AI Enterprise Training Chat Widget (Support-style UI)
(function () {
  // Absolute API endpoint to ensure hitting Laravel backend
  const AI_ET_API = 'https://craveva.com/aiapi/ai/enterprise/chat';
  function qs(sel, root) { return (root || document).querySelector(sel); }
  function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }
  function fmtTime(date) {
    const h24 = date.getHours();
    const h = ((h24 + 11) % 12) + 1; // 1-12
    const m = date.getMinutes().toString().padStart(2, '0');
    const ampm = h24 >= 12 ? 'PM' : 'AM';
    return `${h}:${m} ${ampm}`;
  }

  // Base URL helper for navigation (cloud vs localhost handled by origin)
  function getBaseUrl() {
    return window.location.origin;
  }

  // Module quick-access mapping (account-prefixed to avoid 404)
  const MODULE_ROUTES = {
    dashboard: '/account/dashboard',
    hr: '/account/employees',
    work: '/account/tasks',
    finance: '/account/payments',
    projects: '/account/projects',
    milestones: '/account/projects/milestones',
    tasks: '/account/tasks',
    timelogs: '/account/timelogs',
    invoices: '/account/invoices',
    estimates: '/account/estimates',
    payments: '/account/payments',
    expenses: '/account/expenses',
    proposals: '/account/proposals',
    credit_notes: '/account/credit-notes',
    recurring_invoices: '/account/recurring-invoices',
    clients: '/account/clients',
    employees: '/account/employees',
    attendance: '/account/attendances',
    leaves: '/account/leaves',
    employee_shifts: '/account/employee-shifts',
    tickets: '/account/tickets',
    orders: '/account/orders',
    inventory: '/account/purchase-inventory',
    affiliates: '/account/affiliates',
    assets: '/account/assets',
    knowledgebase: '/account/knowledgebase',
    messages: '/account/messages',
    calendar: '/account/my-calendar',
    notices: '/account/notices',
    reports: '/account/reports',
    crm: '/account/deals',
    biometric: '/account/attendances'
  };
  const MODULE_TITLES = {
    dashboard: 'Dashboard',
    hr: 'HR',
    work: 'Work',
    finance: 'Finance',
    projects: 'Projects',
    milestones: 'Milestones',
    tasks: 'Tasks',
    timelogs: 'Time Logs',
    invoices: 'Invoices',
    estimates: 'Estimates',
    payments: 'Payments',
    expenses: 'Expenses',
    proposals: 'Proposals',
    credit_notes: 'Credit Notes',
    recurring_invoices: 'Recurring Invoices',
    clients: 'Clients',
    employees: 'Employees',
    attendance: 'Attendance',
    leaves: 'Leaves',
    employee_shifts: 'Employee Shifts',
    tickets: 'Tickets',
    orders: 'Orders',
    inventory: 'Inventory',
    affiliates: 'Affiliates',
    assets: 'Assets',
    knowledgebase: 'Knowledge Base',
    messages: 'Messages',
    calendar: 'My Calendar',
    notices: 'Notices',
    reports: 'Reports',
    crm: 'CRM Deals',
    biometric: 'Biometric Attendance'
  };
  const MODULE_KEYWORDS = {
    dashboard: ['dashboard', 'overview'],
    hr: ['hr', 'human resources', 'people', 'employees'],
    work: ['work', 'tasks', 'todo', 'to-do'],
    finance: ['finance', 'accounts', 'payments', 'invoices', 'billing'],
    projects: ['project', 'projects', 'milestone', 'milestones'],
    milestones: ['milestone', 'milestones'],
    tasks: ['task', 'tasks', 'todo', 'work'],
    timelogs: ['timelog', 'time log', 'timesheet', 'time sheet', 'time tracking'],
    invoices: ['invoice', 'invoices', 'billing'],
    estimates: ['estimate', 'estimates'],
    payments: ['payment', 'payments'],
    expenses: ['expense', 'expenses'],
    proposals: ['proposal', 'proposals'],
    credit_notes: ['credit note', 'credit notes', 'credit-note', 'credit-notes'],
    recurring_invoices: ['recurring invoice', 'recurring invoices'],
    clients: ['client', 'clients', 'customer', 'customers'],
    employees: ['employee', 'employees', 'staff', 'hr'],
    attendance: ['attendance', 'attendances', 'check-in', 'checkin', 'clock in', 'clock out'],
    leaves: ['leave', 'leaves', 'vacation', 'holiday'],
    employee_shifts: ['shift', 'shifts', 'roster', 'employee shift', 'employee shifts'],
    tickets: ['ticket', 'tickets', 'support'],
    orders: ['order', 'orders', 'sales'],
    inventory: ['inventory', 'purchase inventory'],
    affiliates: ['affiliate', 'affiliates', 'referral', 'commission'],
    assets: ['asset', 'assets', 'equipment'],
    knowledgebase: ['knowledge base', 'knowledgebase', 'documentation', 'help center'],
    messages: ['message', 'messages', 'chat'],
    calendar: ['calendar', 'my calendar', 'schedule'],
    notices: ['notice', 'notices', 'announcement'],
    reports: ['report', 'reports', 'analytics'],
    crm: ['crm', 'lead', 'leads', 'deal', 'deals'],
    biometric: ['biometric', 'fingerprint', 'punch']
  };
  const DEFAULT_TRAINING_QUICK = ['dashboard', 'hr', 'work', 'finance'];

  function guessModulesFromText(text) {
    const lc = String(text || '').toLowerCase();
    const hits = [];
    Object.keys(MODULE_KEYWORDS).forEach((key) => {
      const words = MODULE_KEYWORDS[key];
      if (words.some((w) => lc.includes(w))) {
        hits.push(key);
      }
    });
    // De-duplicate and limit to 3
    return Array.from(new Set(hits)).slice(0, 3);
  }

  function suggestModules(question, answer, contexts) {
    const s = new Set();
    guessModulesFromText(question).forEach((k) => s.add(k));
    guessModulesFromText(answer).forEach((k) => s.add(k));
    if (Array.isArray(contexts)) {
      contexts.forEach((ctx) => {
        const str = [ctx && ctx.title, ctx && ctx.filename, ctx && ctx.source, ctx && ctx.text]
          .filter(Boolean)
          .join(' ');
        guessModulesFromText(str).forEach((k) => s.add(k));
      });
    }
    const arr = Array.from(s);
    return arr.length ? arr.slice(0, 3) : DEFAULT_TRAINING_QUICK.slice(0, 3);
  }

  function navigateToModule(key) {
    const path = MODULE_ROUTES[key];
    if (!path) return;
    const target = getBaseUrl() + path;
    window.location.assign(target);
  }

  function renderQuickAccess(bubbleEl, modules) {
    if (!bubbleEl || !modules || !modules.length) return;
    const wrap = document.createElement('div');
    wrap.className = 'ai-et-quick';
    const lead = document.createElement('span');
    lead.textContent = 'Quick Access:';
    wrap.appendChild(lead);
    modules.forEach((key) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'ai-et-quick-btn';
      btn.textContent = MODULE_TITLES[key] || key;
      btn.addEventListener('click', function (e) {
        e.preventDefault();
        navigateToModule(key);
      });
      wrap.appendChild(btn);
    });
    bubbleEl.appendChild(wrap);
  }

  function buildRow(role, text, timeStr) {
    const row = document.createElement('div');
    row.className = `ai-et-row ${role === 'user' ? 'user' : 'ai'}`;

    if (role !== 'user') {
      const avatar = document.createElement('div');
      avatar.className = 'ai-et-avatar';
      // Use image-based avatar; text hidden via CSS
      avatar.textContent = '';
      row.appendChild(avatar);
    }

    const bubbleWrap = document.createElement('div');
    bubbleWrap.className = 'ai-et-wrap';
    const bubble = document.createElement('div');
    bubble.className = 'ai-et-bubble';
    if (typeof text === 'string') {
      bubble.textContent = text;
    } else if (text && text.nodeType === 1) {
      bubble.appendChild(text);
    } else {
      bubble.textContent = String(text || '');
    }
    bubbleWrap.appendChild(bubble);

    const time = document.createElement('div');
    time.className = 'ai-et-time';
    time.textContent = timeStr || fmtTime(new Date());
    bubbleWrap.appendChild(time);

    row.appendChild(bubbleWrap);
    return row;
  }

  function init() {
    const launcher = qs('#ai-et-launcher');
    const widget = qs('#ai-enterprise-training-widget');
    if (!launcher || !widget) return;

    // Read company id from data attribute for company-mode scoping
    const companyIdAttr = widget.getAttribute('data-company-id');
    const COMPANY_ID = parseInt(companyIdAttr || '0', 10) || 0;

    const providerSelect = qs('.ai-et-select', widget);
    const messagesEl = qs('.ai-et-messages', widget);
    const textarea = qs('.ai-et-textarea', widget);
    const sendBtn = qs('.ai-et-send', widget);
    const closeBtn = qs('#ai-et-close', widget);
    const header = qs('.ai-et-header', widget);
    const title = qs('.ai-et-title', widget);
    const actions = qs('.ai-et-actions', widget);

    // Mode management: three modes with independent chat histories
    const MODE_MAP = {
      training: 'Craveva Training',
      company: 'Company Process',
      general: 'General'
    };
    let currentMode = localStorage.getItem('ai-et-current-mode') || 'training';
    let history = [];
    let rendering = false; // avoid double-saving when rendering history

    function storageKey(mode) { return `ai-et-history-${mode}`; }
    function loadHistory(mode) {
      try {
        const raw = localStorage.getItem(storageKey(mode));
        const arr = raw ? JSON.parse(raw) : [];
        return Array.isArray(arr) ? arr : [];
      } catch (e) {
        return [];
      }
    }
    function saveHistory(mode, arr) {
      try { localStorage.setItem(storageKey(mode), JSON.stringify(arr)); } catch (_) {}
    }

    // Prepare recent conversation history for backend (limit last N messages)
    function getHistoryForPayload(mode, limit) {
      const arr = loadHistory(mode);
      const lim = typeof limit === 'number' ? limit : 12;
      const trimmed = arr.slice(Math.max(0, arr.length - lim));
      return {
        raw: trimmed, // original shape {role: 'user'|'ai', text, time}
        messages: trimmed.map((m) => ({
          role: m.role === 'ai' ? 'assistant' : 'user',
          content: m.text
        }))
      };
    }

    // Mode-specific welcome messages
    const WELCOME_TEXT_MAP = {
      training: 'Welcome to Craveva Training. Ask about training content.',
      company: 'Welcome to Company Process. Ask about internal processes.',
      general: 'Welcome! How can I assist you today?'
    };
    function getWelcomeText(mode) {
      return WELCOME_TEXT_MAP[mode] || WELCOME_TEXT_MAP.general;
    }
    function hasWelcomeInMode(mode) {
      const arr = loadHistory(mode);
      const welcome = getWelcomeText(mode);
      return arr.some((m) => m.role === 'ai' && m.text === welcome);
    }

    // Enhance header: add avatar on the left (keep names intact)
    if (header && !qs('.ai-et-header-left', header)) {
      const left = document.createElement('div');
      left.className = 'ai-et-header-left';
      const headerAvatar = document.createElement('div');
      headerAvatar.className = 'ai-et-header-avatar';
      // Use image-based avatar; text hidden via CSS
      headerAvatar.textContent = '';
      left.appendChild(headerAvatar);
      if (title) left.appendChild(title);
      header.insertBefore(left, header.firstChild);
    }

    function scrollToBottom() { messagesEl.scrollTop = messagesEl.scrollHeight; }

  function appendMessage(role, text, timeStr, opts) {
    const ts = timeStr || fmtTime(new Date());
    const row = buildRow(role, text, ts);
    messagesEl.appendChild(row);
    scrollToBottom();
    // Render persisted quick access on AI messages in Training mode
    try {
      const bubble = qs('.ai-et-bubble', row);
      const qa = opts && Array.isArray(opts.quickAccess) ? opts.quickAccess : null;
      if (role === 'ai' && currentMode === 'training') {
        const mods = (qa && qa.length) ? qa : DEFAULT_TRAINING_QUICK.slice(0, 4);
        renderQuickAccess(bubble, mods);
      }
    } catch (_) {}
    if (!rendering) {
      const entry = { role, text, time: ts };
      // Persist quick access suggestions if provided
      if (opts && Array.isArray(opts.quickAccess) && opts.quickAccess.length) {
        entry.quickAccess = opts.quickAccess.slice(0, 4);
      }
      history.push(entry);
      saveHistory(currentMode, history);
    }
    return row;
  }

    function setThinking() {
      const row = buildRow('ai', 'Thinking...', fmtTime(new Date()));
      messagesEl.appendChild(row);
      scrollToBottom();
      return row;
    }

    async function send() {
      const q = (textarea.value || '').trim();
      if (!q) return;
      sendBtn.disabled = true;
      appendMessage('user', q);
      textarea.value = '';

      const thinkingRow = setThinking();
      const bubble = qs('.ai-et-bubble', thinkingRow);
      const timeEl = qs('.ai-et-time', thinkingRow);

      try {
        const payload = { question: q, mode: currentMode };
        // Include recent conversation for Training mode to enable contextual answers
        if (currentMode === 'training') {
          const h = getHistoryForPayload(currentMode, 12);
          payload.history = h.raw;
          payload.messages = h.messages;
        }
        // Attach company_id when using company mode
        if (currentMode === 'company' && COMPANY_ID > 0) {
          payload.company_id = COMPANY_ID;
        }
        // Respect UI selection; if 'default', rely on backend config (OpenAI)
        const pv = providerSelect && providerSelect.value ? providerSelect.value : 'default';
        if (pv && pv !== 'default') {
          payload.provider = pv;
        }

        const resp = await fetch(AI_ET_API, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          mode: 'cors',
          body: JSON.stringify(payload)
        });

        let data = null;
        const ct = resp.headers.get('content-type') || '';
        if (ct.includes('text/html')) {
          // Avoid dumping HTML into the chat; report a friendly error
          await resp.text();
          data = { error: 'Unexpected HTML response from server. Please check the API route.' };
        } else if (!resp.ok) {
          // Try to parse JSON error, otherwise text
          if (ct.includes('application/json')) {
            try { data = await resp.json(); } catch (_) { data = { error: `HTTP ${resp.status}` }; }
          } else {
            const txt = await resp.text();
            data = { error: txt || `HTTP ${resp.status}` };
          }
        } else if (ct.includes('application/json')) {
          try { data = await resp.json(); } catch (e) { data = { error: 'Invalid JSON response' }; }
        } else {
          // Fallback: treat body as text if not JSON
          const txt = await resp.text();
          data = { answer: txt };
        }
        const answerStr = String(data && (data.answer || data.error || 'No response'));
        const nowStr = fmtTime(new Date());
        // Non-general modes: when irrelevant, replace the thinking bubble with a single tip
        if (currentMode === 'company' && data && data.hint === 'general') {
          bubble.textContent = '';
          const tip = document.createElement('span');
          tip.innerHTML = "No company-specific context found. <strong>Try General mode</strong> for a broader answer.";
          bubble.appendChild(tip);
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'ai-et-link-btn';
          btn.textContent = 'Switch to General';
          btn.addEventListener('click', function (e) {
            e.preventDefault();
            setMode('general');
          });
          bubble.appendChild(document.createTextNode(' '));
          bubble.appendChild(btn);
          if (timeEl) timeEl.textContent = nowStr;
          if (!rendering) {
            history.push({ role: 'ai', text: 'No company-specific context found. Try General mode for a broader answer.', time: nowStr });
            saveHistory(currentMode, history);
          }
        } else if (currentMode === 'training' && data && Array.isArray(data.contexts) && data.contexts.length === 0) {
          bubble.textContent = '';
          const tip = document.createElement('span');
          tip.innerHTML = "No enterprise training context matched your question. <strong>Try General mode</strong> for a broader answer.";
          bubble.appendChild(tip);
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'ai-et-link-btn';
          btn.textContent = 'Switch to General';
          btn.addEventListener('click', function (e) {
            e.preventDefault();
            setMode('general');
          });
          bubble.appendChild(document.createTextNode(' '));
          bubble.appendChild(btn);
          if (timeEl) timeEl.textContent = nowStr;
          if (!rendering) {
            history.push({ role: 'ai', text: 'No enterprise training context matched your question. Try General mode for a broader answer.', time: nowStr });
            saveHistory(currentMode, history);
          }
        } else {
          bubble.textContent = answerStr;
          // Training mode: show intelligent quick access based on question, answer, and contexts
          let modules = [];
          if (currentMode === 'training') {
            try {
              modules = suggestModules(q, answerStr, (data && Array.isArray(data.contexts)) ? data.contexts : []);
              renderQuickAccess(bubble, modules);
            } catch (_) { modules = DEFAULT_TRAINING_QUICK.slice(0, 3); renderQuickAccess(bubble, modules); }
          }
          if (timeEl) timeEl.textContent = nowStr;
          // Persist AI answer (with quickAccess suggestions when present)
          const entry = { role: 'ai', text: answerStr, time: nowStr };
          if (Array.isArray(modules) && modules.length) entry.quickAccess = modules.slice(0, 3);
          history.push(entry);
          saveHistory(currentMode, history);
        }

        // Company mode: handled above by replacing the bubble when hint is present
      } catch (err) {
        bubble.textContent = 'Error: ' + (err && err.message || err);
      } finally {
        sendBtn.disabled = false;
        scrollToBottom();
      }
    }

    launcher.addEventListener('click', function () {
      widget.classList.toggle('ai-et-open');
      // Greet once per mode if not already greeted
      if (widget.classList.contains('ai-et-open') && !hasWelcomeInMode(currentMode)) {
        appendMessage('ai', getWelcomeText(currentMode));
      }
    });
    if (closeBtn) {
      closeBtn.addEventListener('click', function () { widget.classList.remove('ai-et-open'); });
    }
    sendBtn.addEventListener('click', send);
    textarea.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
    });

    // Manual resize via handle for browsers not supporting CSS resize well
    const resizer = qs('.ai-et-resizer', widget);
    let isResizing = false;
    let startX = 0, startY = 0, startW = 0, startH = 0;
    function onMouseMove(e) {
      if (!isResizing) return;
      const newW = Math.max(300, Math.min(window.innerWidth - 40, startW + (e.pageX - startX)));
      const newH = Math.max(360, Math.min(window.innerHeight - 40, startH + (e.pageY - startY)));
      widget.style.width = newW + 'px';
      widget.style.height = newH + 'px';
    }
    function onMouseUp() {
      if (!isResizing) return;
      isResizing = false;
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
    }
    if (resizer) {
      resizer.addEventListener('mousedown', function (e) {
        e.preventDefault();
        isResizing = true;
        startX = e.pageX;
        startY = e.pageY;
        startW = widget.offsetWidth;
        startH = widget.offsetHeight;
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
      });
    }

    // Create custom mode dropdown inside header actions (no native blue selection)
    if (actions && !qs('.ai-et-mode', widget)) {
      const wrap = document.createElement('div');
      wrap.className = 'ai-et-mode';
      const trigger = document.createElement('button');
      trigger.type = 'button';
      trigger.className = 'ai-et-mode-trigger';
      trigger.textContent = MODE_MAP[currentMode];
      const menu = document.createElement('ul');
      menu.className = 'ai-et-mode-menu';
      Object.keys(MODE_MAP).forEach((key) => {
        const item = document.createElement('li');
        item.className = 'ai-et-mode-item';
        item.textContent = MODE_MAP[key];
        item.dataset.mode = key;
        if (key === currentMode) item.classList.add('active');
        item.addEventListener('click', (e) => {
          e.stopPropagation();
          setMode(key);
          closeModeMenu();
        });
        menu.appendChild(item);
      });
      trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        wrap.classList.toggle('open');
      });
      document.addEventListener('click', () => closeModeMenu());
      widget.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModeMenu(); });
      wrap.appendChild(trigger);
      wrap.appendChild(menu);
      actions.insertBefore(wrap, closeBtn || null);
    }

    // Insert a Clear History button next to Close
    if (actions && !qs('.ai-et-clear', widget)) {
      const clearBtn = document.createElement('button');
      clearBtn.type = 'button';
      clearBtn.className = 'ai-et-clear';
      clearBtn.title = 'Clear history';
      clearBtn.setAttribute('aria-label', 'Clear history');
      clearBtn.textContent = '🗑';
      clearBtn.addEventListener('click', function () {
        // Clear current mode chat history and UI, then send welcome
        history = [];
        saveHistory(currentMode, history);
        messagesEl.innerHTML = '';
        appendMessage('ai', getWelcomeText(currentMode));
      });
      actions.insertBefore(clearBtn, closeBtn || null);
    }

    function closeModeMenu() {
      const w = qs('.ai-et-mode', widget);
      if (w) w.classList.remove('open');
    }

    function setMode(mode) {
      if (!MODE_MAP[mode]) return;
      currentMode = mode;
      localStorage.setItem('ai-et-current-mode', mode);
      // Sync custom dropdown UI
      const trig = qs('.ai-et-mode-trigger', widget);
      if (trig) trig.textContent = MODE_MAP[mode];
      qsa('.ai-et-mode-item', widget).forEach((it) => {
        it.classList.toggle('active', it.dataset.mode === mode);
      });
      closeModeMenu();
      // Load and render history for selected mode
      history = loadHistory(mode);
      messagesEl.innerHTML = '';
      rendering = true;
      history.forEach((m) => appendMessage(m.role, m.text, m.time, { quickAccess: m.quickAccess }));
      rendering = false;
      // If the mode has no history yet, greet with its welcome text
      if (!history.length) {
        appendMessage('ai', getWelcomeText(mode));
      }
      scrollToBottom();
    }

    // Initialize history for current mode on load
    history = loadHistory(currentMode);
    if (history.length) {
      rendering = true;
      history.forEach((m) => appendMessage(m.role, m.text, m.time, { quickAccess: m.quickAccess }));
      rendering = false;
    }

    // Default provider selection to 'Default' to use backend configuration
    if (providerSelect) {
      try { providerSelect.value = 'default'; } catch (_) {}
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();