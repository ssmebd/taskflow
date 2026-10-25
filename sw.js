// TaskFlow service worker: makes the app installable. It does not cache task data, so what you see is always live.
const OFFLINE='<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow</title><body style="font-family:system-ui;background:#0f2a5c;color:#fff;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center"><div><h1>TaskFlow</h1><p>You are offline. Check your connection and try again.</p><button onclick="location.reload()" style="padding:10px 18px;border-radius:8px;border:0;font-size:16px">Retry</button></div>';
self.addEventListener('install',e=>self.skipWaiting());
self.addEventListener('activate',e=>e.waitUntil(self.clients.claim()));
self.addEventListener('fetch',e=>{
  if(e.request.mode==='navigate'){
    e.respondWith(fetch(e.request).catch(()=>new Response(OFFLINE,{headers:{'Content-Type':'text/html; charset=utf-8'}})));
  }
});
