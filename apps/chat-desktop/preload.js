const { contextBridge, ipcRenderer } = require('electron');
contextBridge.exposeInMainWorld('tfNative', { badge: (n) => ipcRenderer.send('badge', Number(n) || 0) });
