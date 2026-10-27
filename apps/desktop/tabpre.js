const { contextBridge, ipcRenderer } = require('electron');
// Only offered to the TaskFlow sign-in page: lets it fill / store the saved login (kept encrypted by the OS).
contextBridge.exposeInMainWorld('tfCred', {
  get: () => ipcRenderer.invoke('cred-get'),
  save: (u, p) => ipcRenderer.send('cred-save', String(u || ''), String(p || '')),
  clear: () => ipcRenderer.send('cred-clear')
});
