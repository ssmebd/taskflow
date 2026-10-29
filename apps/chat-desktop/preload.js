const { contextBridge, ipcRenderer } = require('electron');
contextBridge.exposeInMainWorld('tfNative', {
  badge: (n) => ipcRenderer.send('badge', Number(n) || 0),
  onDownload: (cb) => ipcRenderer.on('dl-done', (e, d) => cb(d)),
  openFile: (p) => ipcRenderer.send('dl-open', String(p || '')),
  showFolder: (p) => ipcRenderer.send('dl-folder', String(p || ''))
});
