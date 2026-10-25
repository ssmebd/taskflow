// TaskFlow desktop shell: opens the live TaskFlow site in its own window.
const { app, BrowserWindow, Menu, shell, Notification } = require('electron');
const path = require('path');
const URL_HOME = process.env.TASKFLOW_URL || 'https://gurucadd.com/taskflow/index.php?home=1';
const HOST = new URL(URL_HOME).host;

if (!app.requestSingleInstanceLock()) { app.quit(); }
let win;
function create() {
  win = new BrowserWindow({
    width: 1400, height: 900, minWidth: 420, minHeight: 600,
    title: 'TaskFlow', icon: path.join(__dirname, 'icon.png'), backgroundColor: '#0f2a5c',
    autoHideMenuBar: true
  });
  Menu.setApplicationMenu(null);
  win.loadURL(URL_HOME);
  win.webContents.setWindowOpenHandler(({ url }) => {
    try { if (new URL(url).host === HOST) return { action: 'allow' }; } catch (e) {}
    shell.openExternal(url); return { action: 'deny' };
  });
  win.webContents.on('did-fail-load', (e, code, desc, u, isMain) => {
    if (!isMain) return;
    win.loadURL('data:text/html;charset=utf-8,' + encodeURIComponent(
      '<body style="font-family:system-ui;background:#0f2a5c;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center"><div><h1>TaskFlow</h1><p>Cannot reach the server. Check your connection.</p><button onclick="location.href=\'' + URL_HOME + '\'" style="padding:10px 18px;border-radius:8px;border:0;font-size:16px">Retry</button></div>'));
  });
}
app.on('second-instance', () => { if (win) { if (win.isMinimized()) win.restore(); win.focus(); } });
app.whenReady().then(create);
app.on('window-all-closed', () => app.quit());
