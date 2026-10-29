// TaskFlow Chat for Windows: its own window around the live chat page. Stays in the tray so alerts keep coming.
const { app, BrowserWindow, Menu, shell, Tray, nativeImage, session, ipcMain } = require('electron');
const path = require('path');

const URL_CHAT = process.env.TASKFLOW_CHAT_URL || 'https://taskflow.gurucadd.com/chat.php';
const ORIGIN = new URL(URL_CHAT).origin;
const HOST = new URL(URL_CHAT).host;

if (!app.requestSingleInstanceLock()) { app.quit(); }
app.setAppUserModelId('com.gurucadd.taskflow.chat');

let win, tray, quitting = false, lastBadge = 0;
const startHidden = process.argv.includes('--hidden');

function show() { if (!win) return; win.show(); if (win.isMinimized()) win.restore(); win.focus(); win.flashFrame(false); }
app.on('second-instance', show);

function create() {
  win = new BrowserWindow({
    width: 1100, height: 760, minWidth: 380, minHeight: 520, show: !startHidden,
    title: 'TaskFlow Chat', backgroundColor: '#0f2a5c', autoHideMenuBar: true,
    icon: path.join(__dirname, 'icon.png'),
    webPreferences: { preload: path.join(__dirname, 'preload.js'), contextIsolation: true, nodeIntegration: false, backgroundThrottling: false }
  });
  win.setMenuBarVisibility(false);
  win.loadURL(URL_CHAT);
  win.webContents.setWindowOpenHandler(({ url }) => { if (/^https?:/i.test(url)) shell.openExternal(url); return { action: 'deny' }; });
  win.webContents.on('will-navigate', (e, url) => {
    let u; try { u = new URL(url); } catch (x) { return; }
    const own = u.host === HOST && (/\/(chat|login|api)\.php$/.test(u.pathname));
    if (!own) { e.preventDefault(); if (/^https?:/i.test(url)) shell.openExternal(url); }
  });
  win.webContents.on('did-fail-load', (e, code, desc, url, isMain) => {
    if (!isMain || code === -3) return;
    win.webContents.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent('<body style="font-family:system-ui;background:#0f2a5c;color:#fff;display:grid;place-items:center;height:100vh;margin:0;text-align:center"><div><h2>TaskFlow Chat</h2><p>Cannot reach the server. Check your internet connection.</p><button onclick="location.href=\'' + URL_CHAT + '\'" style="padding:10px 18px;border:0;border-radius:8px;font-size:15px">Try again</button></div></body>'));
    setTimeout(() => { if (!quitting && win && !win.isDestroyed()) win.loadURL(URL_CHAT); }, 15000);
  });
  win.on('close', (e) => { if (!quitting) { e.preventDefault(); win.hide(); } });
  win.on('focus', () => win.flashFrame(false));
}

ipcMain.on('badge', (e, n) => {
  if (!win || win.isDestroyed()) return;
  const name = n <= 0 ? null : n > 9 ? 'plus' : String(n);
  win.setOverlayIcon(name ? nativeImage.createFromPath(path.join(__dirname, 'badge-' + name + '.png')) : null, n > 0 ? n + ' unread' : '');
  if (tray) tray.setToolTip(n > 0 ? 'TaskFlow Chat (' + n + ' unread)' : 'TaskFlow Chat');
  if (n > lastBadge && !win.isFocused()) win.flashFrame(true);
  lastBadge = n;
});

let flushing = false;
app.on('before-quit', (e) => {
  quitting = true;
  if (flushing) return;
  flushing = true; e.preventDefault();
  Promise.race([session.defaultSession.cookies.flushStore(), new Promise((r) => setTimeout(r, 1500))]).catch(() => {}).then(() => app.quit());
});
// Keep the sign-in: write cookies to disk right away and make session-only cookies last 60 days.
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
  tray.setToolTip('TaskFlow Chat');
  const menu = () => Menu.buildFromTemplate([
    { label: 'Open TaskFlow Chat', click: show },
    { label: 'Start with Windows', type: 'checkbox', checked: app.getLoginItemSettings().openAtLogin, click: (i) => app.setLoginItemSettings({ openAtLogin: i.checked, args: ['--hidden'] }) },
    { type: 'separator' },
    { label: 'Quit', click: () => { quitting = true; app.quit(); } }
  ]);
  tray.on('click', show);
  tray.on('right-click', () => tray.popUpContextMenu(menu()));
});
app.on('window-all-closed', () => {});
