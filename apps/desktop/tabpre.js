const { contextBridge, ipcRenderer } = require('electron');
// Only offered to the TaskFlow sign-in page: lets it fill / store the saved login (kept encrypted by the OS).
contextBridge.exposeInMainWorld('tfCred', {
  get: () => ipcRenderer.invoke('cred-get'),
  save: (u, p) => ipcRenderer.send('cred-save', String(u || ''), String(p || '')),
  clear: () => ipcRenderer.send('cred-clear')
});
// Lets the page show its Live / Build / bell status in the app's tab strip (top right).
contextBridge.exposeInMainWorld('tfDesk', {
  status: (o) => ipcRenderer.send('desk-status', { ok: !!(o && o.ok), build: String((o && o.build) || '').slice(0, 60), warn: !!(o && o.warn), n: (o && o.n) | 0 }),
  onBell: (cb) => ipcRenderer.on('desk-bell', () => cb())
});
