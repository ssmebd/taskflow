// TaskFlow desktop shell: a thin menu ribbon + tab strip around the live TaskFlow site.
// Each tab is its own web view (shared sign-in), so several company spaces can be open side by side.
const { app, BrowserWindow, WebContentsView, Menu, shell, Tray, nativeImage, session, ipcMain, clipboard, dialog, safeStorage } = require('electron');
const path = require('path');
const fs = require('fs');

const URL_HOME = process.env.TASKFLOW_URL || 'https://taskflow.gurucadd.com/index.php?home=1';
const ORIGIN = new URL(URL_HOME).origin;
const HOST = new URL(URL_HOME).host;
const TOP = 26 + 34; // ribbon + tab strip height (see chrome.html)

if (!app.requestSingleInstanceLock()) { app.quit(); }
app.setAppUserModelId('com.gurucadd.taskflow');

let win, tray, quitting = false;
let tabs = [], activeId = 0, nextId = 1, companies = [], saveT = null;

const sameHost = (u) => { try { return new URL(u).host === HOST; } catch (e) { return false; } };
const cleanTitle = (t) => {
  t = String(t || '').replace(/\s*[–-]\s*TaskFlow\s*$/, '').trim();
  return !t || t === 'TaskFlow' ? 'Home' : t;
};
const active = () => tabs.find((t) => t.id === activeId);
const ui = () => win && !win.isDestroyed() && win.webContents;

function pushState() {
  const c = ui(); if (!c) return;
  c.send('tabs', { active: activeId, tabs: tabs.map((t) => ({ id: t.id, title: t.title, home: t.home, pinned: !!t.pinned })) });
  const a = tabs.find((t) => t.id === activeId); c.send('status', (a && a.status) || { ok: false, build: '', warn: false, n: 0 });
  scheduleSave();
}
let lastSize = '', layoutQ = false;
function layout(force) {
  if (!win || win.isDestroyed()) return;
  const [w, h] = win.getContentSize();
  const key = w + 'x' + h + ':' + activeId + ':' + tabs.length;
  if (!force && key === lastSize) return;
  lastSize = key;
  const b = { x: 0, y: TOP, width: w, height: Math.max(0, h - TOP) };
  tabs.forEach((t) => {
    const on = t.id === activeId;
    if (on) t.view.setBounds(b);          // only the visible tab follows the live resize
    else if (force) t.view.setBounds(b);  // hidden tabs are fixed up on tab changes
    t.view.setVisible(on);
  });
}
// resize events fire many times per frame: do the work once per frame instead
function layoutSoon() { if (layoutQ) return; layoutQ = true; setImmediate(() => { layoutQ = false; layout(); }); }
function activate(id) {
  const t = tabs.find((x) => x.id === id); if (!t) return;
  activeId = id; layout(true); pushState(); t.view.webContents.focus();
  win.setTitle(t.title === 'Home' ? 'TaskFlow' : t.title + ' – TaskFlow');
}

// ---- tab persistence (reopen the same company tabs next time) ----
const stateFile = () => path.join(app.getPath('userData'), 'tabs.json');
function scheduleSave() {
  clearTimeout(saveT);
  saveT = setTimeout(() => {
    try {
      const list = tabs.filter((t) => t.pinned).map((t) => t.url);
      const a = active();
      fs.writeFileSync(stateFile(), JSON.stringify({ list, active: a && (a.home || a.pinned) ? tabs.findIndex((t) => t.id === activeId) : 0 }));
    } catch (e) {}
  }, 800);
}
function loadSaved() {
  try {
    const j = JSON.parse(fs.readFileSync(stateFile(), 'utf8'));
    return { list: (j.list || []).filter(sameHost).slice(0, 12), active: j.active | 0 };
  } catch (e) { return { list: [], active: 0 }; }
}

// ---- tabs ----
function newTab(url, opts = {}) {
  const view = new WebContentsView({ webPreferences: { backgroundThrottling: false, preload: path.join(__dirname, 'tabpre.js'), contextIsolation: true, sandbox: true } });
  view.setBackgroundColor('#0f2a5c');
  const wc = view.webContents;
  const t = { id: nextId++, view, home: !!opts.home, pinned: !!opts.pinned, title: opts.home ? 'Home' : 'Loading…', url };
  tabs.push(t);
  win.contentView.addChildView(view);
  hook(wc);
  wc.setWindowOpenHandler(({ url: u }) => {
    if (sameHost(u)) { newTab(u); return { action: 'deny' }; }
    shell.openExternal(u); return { action: 'deny' };
  });
  wc.on('page-title-updated', (e, title) => {
    t.title = cleanTitle(title); pushState();
    if (t.id === activeId) win.setTitle(t.title === 'Home' ? 'TaskFlow' : t.title + ' – TaskFlow');
  });
  const nav = (e, u) => { if (/^https?:/.test(u)) { t.url = u; scheduleSave(); } };
  wc.on('did-navigate', nav);
  wc.on('did-navigate-in-page', nav);
  wc.on('did-fail-load', (e, code, desc, u, isMain) => {
    if (!isMain || code === -3) return;
    t.title = 'Offline'; pushState();
    wc.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent(
      '<body style="font-family:system-ui;background:#0f2a5c;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center"><div><h1>TaskFlow</h1><p>Cannot reach the server. Check your connection.</p><button onclick="location.href=\'' + (t.url || URL_HOME) + '\'" style="padding:10px 18px;border-radius:8px;border:0;font-size:16px">Retry</button></div>'));
  });
  wc.on('context-menu', (e, p) => {
    const items = [];
    if (p.linkURL && sameHost(p.linkURL)) items.push({ label: 'Open in new tab', click: () => newTab(p.linkURL) });
    if (p.linkURL) items.push({ label: 'Open link in browser', click: () => shell.openExternal(p.linkURL) }, { label: 'Copy link address', click: () => clipboard.writeText(p.linkURL) });
    if (p.isEditable) items.push({ role: 'cut' }, { role: 'copy' }, { role: 'paste' }, { role: 'selectAll' });
    else if (p.selectionText) items.push({ role: 'copy' });
    if (!items.length) items.push({ label: 'Back', enabled: wc.canGoBack(), click: () => wc.goBack() });
    items.push({ type: 'separator' }, { label: 'Reload', click: () => wc.reload() });
    Menu.buildFromTemplate(items).popup({ window: win });
  });
  wc.loadURL(url);
  if (opts.background) { layout(true); pushState(); } else activate(t.id);
  return t;
}
function closeTab(id) {
  const i = tabs.findIndex((t) => t.id === id);
  if (i < 0 || tabs[i].home || tabs[i].pinned) return;
  const [t] = tabs.splice(i, 1);
  try { win.contentView.removeChildView(t.view); t.view.webContents.close(); } catch (e) {}
  if (activeId === id) activate((tabs[i] || tabs[i - 1] || tabs[0]).id); else { layout(true); pushState(); }
}
const wsOf = (u) => { try { return new URL(u).searchParams.get('w') || ''; } catch (e) { return ''; } };
function sortTabs() { const r = (t) => (t.home ? 0 : t.pinned ? 1 : 2); tabs = tabs.map((t, i) => [t, i]).sort((a, b) => r(a[0]) - r(b[0]) || a[1] - b[1]).map((x) => x[0]); }
function setPin(t, on) { if (t.home) return; t.pinned = on; sortTabs(); layout(true); pushState(); }
function pinCompany(c, on) {
  const id = String(c.id);
  const t = tabs.find((x) => !x.home && wsOf(x.url) === id);
  if (on) { if (t) { setPin(t, true); activate(t.id); } else newTab(ORIGIN + '/index.php?w=' + c.id, { pinned: true }), sortTabs(), pushState(); }
  else if (t) setPin(t, false);
}
function pinItems() {
  if (!companies.length) return [{ label: 'Open a company from Home first', enabled: false }];
  return companies.map((c) => {
    const t = tabs.find((x) => !x.home && wsOf(x.url) === String(c.id));
    return { label: c.name || ('Company ' + c.id), type: 'checkbox', checked: !!(t && t.pinned), click: (i) => pinCompany(c, i.checked) };
  });
}
ipcMain.on('tab-menu', (e, id, x, y) => {
  if (e.sender !== ui()) return;
  const t = tabs.find((q) => q.id === (id | 0)); if (!t) return;
  Menu.buildFromTemplate([
    { label: t.pinned ? 'Unpin tab' : 'Pin tab', enabled: !t.home, click: () => setPin(t, !t.pinned) },
    { label: 'Reload', click: () => t.view.webContents.reload() },
    { label: 'Close tab', enabled: !t.home && !t.pinned, click: () => closeTab(t.id) }
  ]).popup({ window: win, x: x | 0, y: y | 0 });
});
function cycle(d) {
  const i = tabs.findIndex((t) => t.id === activeId);
  activate(tabs[(i + d + tabs.length) % tabs.length].id);
}

// ---- keyboard shortcuts (work from the page and from the ribbon) ----
function hook(wc) {
  wc.on('before-input-event', (e, i) => {
    if (i.type !== 'keyDown') return;
    const c = i.control || i.meta, k = String(i.key).toLowerCase(), a = active();
    let h = true;
    if (c && k === 't') newTab(URL_HOME);
    else if (c && k === 'w') { if (a) closeTab(a.id); }
    else if (c && k === 'tab') cycle(i.shift ? -1 : 1);
    else if (c && /^[1-9]$/.test(k)) { const t = tabs[+k - 1]; if (t) activate(t.id); }
    else if ((c && k === 'r') || k === 'f5') { if (a) i.shift ? a.view.webContents.reloadIgnoringCache() : a.view.webContents.reload(); }
    else if (c && (k === '=' || k === '+')) zoom(0.5);
    else if (c && k === '-') zoom(-0.5);
    else if (c && k === '0') zoom(0);
    else if (k === 'f11') win.setFullScreen(!win.isFullScreen());
    else h = false;
    if (h) e.preventDefault();
  });
}
function zoom(d) {
  const a = active(); if (!a) return;
  const wc = a.view.webContents;
  wc.setZoomLevel(d === 0 ? 0 : Math.max(-3, Math.min(5, wc.getZoomLevel() + d)));
}

// ---- menus ----
async function refreshCompanies() {
  const a = active(); if (!a) return;
  try {
    const r = await Promise.race([
      a.view.webContents.executeJavaScript("typeof wss!=='undefined'?wss.map(w=>({id:w.id,name:w.name})):[]"),
      new Promise((res) => setTimeout(() => res(null), 1200))
    ]);
    if (Array.isArray(r) && r.length) companies = r;
  } catch (e) {}
}
function companyItems() {
  if (!companies.length) return [{ label: 'Open a company from Home first', enabled: false }];
  return companies.map((c) => ({ label: c.name || ('Company ' + c.id), click: () => newTab(ORIGIN + '/index.php?w=' + c.id) }));
}
function template(name) {
  const a = active(), wc = a && a.view.webContents;
  if (name === 'file') return [
    { label: 'New tab', accelerator: 'Ctrl+T', click: () => newTab(URL_HOME) },
    { label: 'Open company in new tab', submenu: companyItems() },
    { label: 'Pin company', submenu: pinItems() },
    { label: 'Close tab', accelerator: 'Ctrl+W', enabled: !!(a && !a.home && !a.pinned), click: () => a && closeTab(a.id) },
    { type: 'separator' },
    { label: 'Start with Windows', type: 'checkbox', checked: app.getLoginItemSettings().openAtLogin, click: (i) => app.setLoginItemSettings({ openAtLogin: i.checked }) },
    { label: 'Hide to tray', click: () => win.hide() },
    { type: 'separator' },
    { label: 'Quit TaskFlow', click: () => { quitting = true; app.quit(); } }
  ];
  if (name === 'view') return [
    { label: 'Reload', accelerator: 'Ctrl+R', click: () => wc && wc.reload() },
    { label: 'Hard reload', accelerator: 'Ctrl+Shift+R', click: () => wc && wc.reloadIgnoringCache() },
    { type: 'separator' },
    { label: 'Zoom in', accelerator: 'Ctrl+=', click: () => zoom(0.5) },
    { label: 'Zoom out', accelerator: 'Ctrl+-', click: () => zoom(-0.5) },
    { label: 'Actual size', accelerator: 'Ctrl+0', click: () => zoom(0) },
    { type: 'separator' },
    { label: 'Full screen', accelerator: 'F11', click: () => win.setFullScreen(!win.isFullScreen()) },
    { label: 'Next tab', accelerator: 'Ctrl+Tab', click: () => cycle(1) },
    { label: 'Previous tab', accelerator: 'Ctrl+Shift+Tab', click: () => cycle(-1) }
  ];
  if (name === 'tools') return [
    { label: 'Home (all companies)', click: () => { const h = tabs.find((t) => t.home); if (h) activate(h.id); } },
    { label: 'Open company in new tab', submenu: companyItems() },
    { label: 'Pin company', submenu: pinItems() },
    { type: 'separator' },
    { label: 'Back', enabled: !!(wc && wc.canGoBack()), click: () => wc.goBack() },
    { label: 'Forward', enabled: !!(wc && wc.canGoForward()), click: () => wc.goForward() },
    { type: 'separator' },
    { label: 'Copy page link', click: () => wc && clipboard.writeText(wc.getURL()) },
    { label: 'Open this page in browser', click: () => wc && shell.openExternal(wc.getURL()) },
    { type: 'separator' },
    { label: 'Developer tools', accelerator: 'Ctrl+Shift+I', click: () => wc && wc.openDevTools({ mode: 'detach' }) }
  ];
  return [
    { label: 'Help & guide', accelerator: 'F1', click: () => help() },
    { label: 'What we call things (names of every part)', click: () => help('names') },
    { label: 'Help topics', submenu: [
      ['Start here: the map', 'start'], ['Getting around', 'nav'], ['Working with tasks', 'tasks'], ['List, Board and Gantt', 'views'],
      ['Hours, overtime and reminders', 'hours'], ['Chat, notifications and phone alerts', 'chat'], ['Wiki and Tools', 'wiki'],
      ['For admins', 'admin'], ['Questions and fixes', 'faq']
    ].map(([label, id]) => ({ label, click: () => help(id) })) },
    { type: 'separator' },
    { label: 'Open TaskFlow in browser', click: () => shell.openExternal(ORIGIN) },
    { type: 'separator' },
    { label: 'About TaskFlow', click: () => dialog.showMessageBox(win, { type: 'info', title: 'About TaskFlow', message: 'TaskFlow Desktop', detail: 'Version ' + app.getVersion() + '\nPowered by GURUCADD', buttons: ['OK'] }) }
  ];
}
function help(id) { const a = active(); if (a) a.view.webContents.executeJavaScript('window.tfHelp&&window.tfHelp(' + JSON.stringify(id || null) + ')').catch(() => {}); }
ipcMain.on('menu', async (e, name, x, y) => {
  if (e.sender !== ui()) return;
  if (name === 'file' || name === 'tools') await refreshCompanies();
  Menu.buildFromTemplate(template(name)).popup({ window: win, x: x | 0, y: y | 0 });
});
ipcMain.on('tab-new', async (e, x, y) => {
  if (e.sender !== ui()) return;
  await refreshCompanies();
  Menu.buildFromTemplate([...companyItems(), { type: 'separator' }, { label: 'Blank tab (Home)', click: () => newTab(URL_HOME) }]).popup({ window: win, x: x | 0, y: y | 0 });
});
ipcMain.on('desk-status', (e, st) => { const t = tabs.find((q) => q.view.webContents === e.sender); if (!t) return; t.status = st; if (t.id === activeId) { const c = ui(); if (c) c.send('status', st); } });
ipcMain.on('bell-click', (e) => { if (e.sender !== ui()) return; const a = active(); if (a) a.view.webContents.send('desk-bell'); });
ipcMain.on('build-click', (e) => { if (e.sender !== ui()) return; const a = active(); if (a) a.view.webContents.reload(); });
ipcMain.on('tab-activate', (e, id) => { if (e.sender === ui()) activate(id | 0); });
ipcMain.on('tab-close', (e, id) => { if (e.sender === ui()) closeTab(id | 0); });
ipcMain.on('chrome-ready', (e) => { if (e.sender === ui()) pushState(); });

// ---- window ----
function create() {
  win = new BrowserWindow({
    width: 1400, height: 900, minWidth: 520, minHeight: 600,
    title: 'TaskFlow', icon: path.join(__dirname, 'icon.png'), backgroundColor: '#0f2a5c',
    autoHideMenuBar: true,
    webPreferences: { preload: path.join(__dirname, 'preload.js'), contextIsolation: true, sandbox: true }
  });
  Menu.setApplicationMenu(null);
  win.removeMenu();
  win.on('close', (e) => { if (!quitting) { e.preventDefault(); win.hide(); } });
  win.on('resize', layoutSoon);
  for (const ev of ['maximize', 'unmaximize', 'enter-full-screen', 'leave-full-screen', 'restore']) win.on(ev, () => layout(true));
  win.on('resized', () => layout(true));
  hook(win.webContents);
  win.webContents.setWindowOpenHandler(() => ({ action: 'deny' }));
  win.loadFile(path.join(__dirname, 'chrome.html'));
  const saved = loadSaved();
  const home = newTab(URL_HOME, { home: true });
  saved.list.forEach((u) => newTab(u, { background: true, pinned: true }));
  const pick = saved.list.length && saved.active > 0 ? tabs[saved.active] : home;
  activate((pick || home).id);
}
function show() { if (!win) return; win.show(); if (win.isMinimized()) win.restore(); win.focus(); }
app.on('second-instance', show);
let flushing = false;
app.on('before-quit', (e) => {
  quitting = true;
  if (flushing) return;
  flushing = true; e.preventDefault();
  Promise.race([session.defaultSession.cookies.flushStore(), new Promise((r) => setTimeout(r, 1500))]).catch(() => {}).then(() => app.quit());
});
// Keep the sign-in: write cookies to disk right away, and make session-only cookies persistent.
// ---- saved login (encrypted with the OS keychain; only the TaskFlow sign-in page may use it) ----
const credFile = () => path.join(app.getPath('userData'), 'cred.bin');
const fromTF = (e) => { try { return new URL(e.senderFrame.url).origin === ORIGIN; } catch (x) { return false; } };
ipcMain.handle('cred-get', (e) => {
  if (!fromTF(e) || !safeStorage.isEncryptionAvailable()) return null;
  try { return JSON.parse(safeStorage.decryptString(fs.readFileSync(credFile()))); } catch (x) { return null; }
});
ipcMain.on('cred-save', (e, u, p) => {
  if (!fromTF(e) || !u || !p || !safeStorage.isEncryptionAvailable()) return;
  try { fs.writeFileSync(credFile(), safeStorage.encryptString(JSON.stringify({ u, p }))); } catch (x) {}
});
ipcMain.on('cred-clear', (e) => { if (fromTF(e)) { try { fs.unlinkSync(credFile()); } catch (x) {} } });
function keepCookies() {
  const ck = session.defaultSession.cookies; let t = null;
  ck.on('changed', (e, c, cause, removed) => {
    clearTimeout(t); t = setTimeout(() => ck.flushStore().catch(() => {}), 1500);
    if (removed || c.session !== true || !String(c.domain || '').replace(/^\./, '').endsWith(HOST.split(':')[0])) return;
    ck.set({ url: ORIGIN + (c.path || '/'), name: c.name, value: c.value, path: c.path || '/', secure: c.secure, httpOnly: c.httpOnly, sameSite: c.sameSite, expirationDate: Math.floor(Date.now() / 1000) + 60 * 86400 }).catch(() => {});
  });
}
app.whenReady().then(() => {
  session.defaultSession.setPermissionRequestHandler((wc, permission, cb) => cb(permission === 'notifications' || permission === 'clipboard-sanitized-write' || permission === 'fullscreen'));
  keepCookies();
  create();
  tray = new Tray(nativeImage.createFromPath(path.join(__dirname, 'icon.png')).resize({ width: 16, height: 16 }));
  tray.setToolTip('TaskFlow');
  const menu = () => Menu.buildFromTemplate([
    { label: 'Open TaskFlow', click: show },
    { label: 'Start with Windows', type: 'checkbox', checked: app.getLoginItemSettings().openAtLogin, click: (i) => app.setLoginItemSettings({ openAtLogin: i.checked }) },
    { type: 'separator' },
    { label: 'Quit', click: () => { quitting = true; app.quit(); } }
  ]);
  tray.on('click', show);
  tray.on('right-click', () => tray.popUpContextMenu(menu()));
});
app.on('window-all-closed', () => {});
