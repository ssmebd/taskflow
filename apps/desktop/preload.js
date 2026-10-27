const { contextBridge, ipcRenderer } = require('electron');
contextBridge.exposeInMainWorld('tf', {
  menu: (name, x, y) => ipcRenderer.send('menu', name, x, y),
  activate: (id) => ipcRenderer.send('tab-activate', id),
  close: (id) => ipcRenderer.send('tab-close', id),
  newTab: (x, y) => ipcRenderer.send('tab-new', x, y),
  onTabs: (cb) => ipcRenderer.on('tabs', (e, s) => cb(s)),
  ready: () => ipcRenderer.send('chrome-ready')
});
