<?php require 'config.php';require 'remember.php'; if(empty($_SESSION['uid'])){header('Location: login.php?next=chat');exit;} ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>GC Chat</title>
<meta name="theme-color" content="#0f2a5c">
<link rel="manifest" href="chat.webmanifest">
<link rel="icon" type="image/png" href="chat-192.png?v=2">
<link rel="apple-touch-icon" href="chat-192.png?v=2">
<style>
:root{--bg:#eef2f7;--card:#fff;--ink:#0f172a;--mut:#64748b;--line:#e2e8f0;--col:#f1f5f9;--pri:#0f2a5c;--acc:#38bdf8;--grn:#22c55e;--me:#e4e8fb;--meink:#1b1f3a;--them:#f1f1f1;--avbg:#dfe3d6;--avink:#3d4a35;--sel:#e8f1ff;--side:#f1f2f5;--shadow:0 2px 10px rgba(15,42,92,.08)}
@media(prefers-color-scheme:dark){:root{--bg:#0c0f16;--card:#171b26;--ink:#e8eaf2;--mut:#98a0b5;--line:#272c3b;--col:#1d2230;--pri:#7cb4ff;--me:#3a3f8f;--meink:#fff;--them:#262a38;--avbg:#3a4a46;--avink:#d6efe6;--sel:#1c2540;--side:#10131b;--shadow:0 2px 10px rgba(0,0,0,.4)}}
*{box-sizing:border-box}html,body{height:100%;margin:0}
body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;background:var(--bg);color:var(--ink);overflow:hidden;-webkit-tap-highlight-color:transparent}
button{font:inherit;color:inherit;cursor:pointer}
#app{height:100dvh;display:grid;grid-template-columns:minmax(300px,360px) minmax(0,1fr)}
/* sidebar */
#side{display:flex;flex-direction:column;background:var(--side);border-right:1px solid var(--line);min-height:0}
.sh{display:flex;align-items:center;gap:10px;padding:14px 14px 10px;background:linear-gradient(135deg,#0a1d44,#0f2a5c);color:#fff;padding-top:calc(14px + env(safe-area-inset-top,0px))}
.sh img{width:34px;height:34px;border-radius:9px}
.sh .t{flex:1;min-width:0}.sh b{display:block;font-size:16px;letter-spacing:.2px}.sh small{display:block;font-size:11px;color:#9fc4ff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ib{border:0;background:rgba(255,255,255,.12);color:#fff;width:34px;height:34px;border-radius:10px;display:grid;place-items:center;font-size:16px;position:relative}
.ib:hover{background:rgba(255,255,255,.22)}
#menu{position:absolute;right:10px;top:56px;z-index:30;background:var(--card);color:var(--ink);border:1px solid var(--line);border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.25);padding:6px;min-width:210px}
#menu[hidden]{display:none}#menu a,#menu button{display:block;width:100%;text-align:left;border:0;background:none;color:var(--ink);padding:9px 11px;border-radius:8px;text-decoration:none;font-size:14px}
#menu a:hover,#menu button:hover{background:var(--col)}
.sr{padding:10px 12px;border-bottom:1px solid var(--line)}
.sr input{width:100%;padding:9px 12px 9px 34px;border:1px solid var(--line);border-radius:12px;background:var(--card) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round'%3E%3Ccircle cx='7' cy='7' r='5'/%3E%3Cpath d='M11 11l3.5 3.5'/%3E%3C/svg%3E") 11px center no-repeat;color:var(--ink);font:inherit;font-size:14px;outline:0}
.sr input:focus{border-color:var(--acc)}
#list{flex:1;overflow:auto;min-height:0}
#bld{border:0;border-top:1px solid var(--line);background:var(--side);color:var(--mut);font-size:12px;padding:9px 12px calc(9px + env(safe-area-inset-bottom,0px));text-align:center;opacity:.85}#bld.new{background:#fff4d6;color:#7a5200;font-weight:700;opacity:1}
.cv{display:grid;grid-template-columns:30px minmax(0,1fr) auto;gap:1px 11px;align-items:center;padding:9px 14px;border:0;border-bottom:1px solid transparent;background:none;width:100%;text-align:left}
.cv:hover{background:rgba(127,127,127,.12)}.cv.on{background:var(--card);box-shadow:inset 3px 0 0 var(--acc)}
.avw{position:relative;grid-row:1/3;flex:none;width:30px;height:30px}.avw.sm{width:32px;height:32px;grid-row:auto}
.av{width:100%;height:100%;border-radius:50%;display:grid;place-items:center;color:#3b4540;font-weight:600;font-size:11px;letter-spacing:.2px}
.avw.sm .av{font-size:11.5px}@media(prefers-color-scheme:dark){.av{color:#e4ebe7}}
.stt{position:absolute;right:-3px;bottom:-3px;width:13px;height:13px;border-radius:50%;box-sizing:border-box;border:2px solid var(--side);background:#3f9a56;display:grid;place-items:center}.avw.sm .stt{border-color:var(--card)}
.stt.on:after{content:"";width:3px;height:5.5px;border:solid #fff;border-width:0 1.6px 1.6px 0;transform:rotate(45deg) translate(-.5px,-.5px)}
.stt.off{background:var(--card);border-color:#9aa3b2}
.cv.on .stt{border-color:var(--card)}
.cv b{font-weight:400;font-size:14.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;gap:6px;align-items:center}
.cv b i{font-style:normal;font-size:10px;font-weight:700;padding:1px 6px;border-radius:6px;background:var(--col);color:var(--mut);text-transform:uppercase;letter-spacing:.04em}
.cv small{color:var(--mut);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;grid-column:2}
.cv .tm{font-size:11.5px;color:var(--mut);justify-self:end;align-self:start}
.cv .ub{grid-column:3;grid-row:2;justify-self:end;background:var(--grn);color:#052e16;font-size:11.5px;font-weight:800;min-width:20px;height:20px;border-radius:10px;display:grid;place-items:center;padding:0 6px}
.cv.un b{font-weight:600}.cv.un b,.cv.un small{color:var(--ink)}.cv.un small{font-weight:600}.cv.un .tm{color:var(--grn);font-weight:700}
.empty{padding:26px;text-align:center;color:var(--mut);font-size:14px}
/* main */
#main{display:flex;flex-direction:column;min-width:0;min-height:0;position:relative;background:var(--bg)}
#ph{flex:1;display:grid;place-items:center;text-align:center;color:var(--mut);padding:30px}
#ph img{width:96px;height:96px;border-radius:24px;opacity:.9;margin-bottom:14px}#ph h2{margin:0 0 6px;color:var(--ink)}
#thr{display:none;flex-direction:column;flex:1;min-height:0}#main.open #thr{display:flex}#main.open #ph{display:none}
.th{display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--card);border-bottom:1px solid var(--line);padding-top:calc(10px + env(safe-area-inset-top,0px))}
.th .t{flex:1;min-width:0}.th b{display:block;font-size:16px}.th small{color:var(--mut);font-size:12px}
.bk{display:none;border:0;background:none;font-size:22px;width:34px;height:34px;color:var(--ink)}
#msgs{background:var(--card);flex:1;overflow:auto;padding:6px max(16px,calc((100% - 1000px)/2)) 8px;display:flex;flex-direction:column;gap:0;min-height:0;overscroll-behavior:contain}
.hd{display:flex;gap:8px;align-items:baseline;font-size:12px;color:var(--mut);margin:14px 0 3px}.hd b{font-weight:400}
.g{display:grid;grid-template-columns:40px minmax(0,1fr);column-gap:8px;align-items:start;max-width:100%}
.g.me{display:block}
.gav img{width:100%;height:100%;border-radius:50%;object-fit:cover;display:block}.av{object-fit:cover}
.gav{width:34px;overflow:hidden;height:34px;border-radius:50%;background:var(--avbg);color:var(--avink);display:grid;place-items:center;font-size:12px;font-weight:600;margin-top:2px}
.gc{display:flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:0}.g.me .gc{align-items:flex-end}
.g.me .hd{justify-content:flex-end}
.bb{max-width:min(82%,840px);background:var(--them);color:var(--ink);border-radius:4px;padding:8px 12px;overflow-wrap:anywhere;font-size:14px;line-height:1.45}
.me .bb{background:var(--me);color:var(--meink)}
.bb .tx{white-space:pre-wrap}.bb a{color:inherit;text-decoration:underline}
.bb .ex{margin-top:4px;font-size:11px;opacity:.65}
.bb.dl{background:transparent!important;border:1px dashed var(--line);color:var(--mut);font-size:13px}.bb .ed{font-size:11px;opacity:.6;margin-top:2px}.eded{display:flex;flex-direction:column;gap:6px;min-width:min(420px,70vw)}.eded textarea{width:100%;border:1px solid var(--acc);border-radius:8px;padding:8px;font:inherit;background:var(--card);color:var(--ink);resize:vertical;min-height:60px}.eded div{display:flex;gap:6px;justify-content:flex-end}.eded button{border:0;border-radius:8px;padding:6px 12px;font-weight:600;cursor:pointer;background:var(--col);color:var(--ink)}.eded button.p{background:var(--acc);color:#fff}
.sn{font-size:11px;color:var(--mut);margin:1px 2px 0 0;align-self:flex-end}
.bb{position:relative}
.bt{display:none;position:absolute;bottom:calc(100% - 5px);right:6px;z-index:8;align-items:center;gap:1px;background:var(--card);border:1px solid var(--line);border-radius:10px;padding:3px 4px;box-shadow:0 4px 16px rgba(0,0,0,.22);color:var(--ink)}
.bb:hover .bt,.bb.hv .bt{display:flex}
.bt button{border:0;background:none;width:32px;height:30px;border-radius:7px;font-size:17px;line-height:1;color:inherit;display:grid;place-items:center}.bt button:hover{background:var(--col)}
.bt .sp{width:1px;height:20px;background:var(--line);margin:0 3px}.bt .tb2{font-size:14px;font-weight:700;width:auto;padding:0 8px}
.rx{display:flex;flex-wrap:wrap;gap:4px;margin:1px 0 2px}
.rp{border:1px solid var(--line);background:var(--card);border-radius:999px;padding:1px 8px;font-size:13px;display:flex;gap:4px;align-items:center;color:var(--ink)}.rp.on{background:var(--sel);border-color:var(--acc)}.rp b{font-size:12px;font-weight:700}
#rpop{position:fixed;z-index:40;background:var(--card);border:1px solid var(--line);border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.3);padding:6px;display:grid;grid-template-columns:repeat(8,1fr);gap:2px;width:300px;max-width:96vw}#rpop[hidden]{display:none}
#rpop button{border:0;background:none;font-size:21px;height:34px;border-radius:8px}#rpop button:hover{background:var(--col)}

.im{display:block;max-width:100%;max-height:300px;border-radius:10px;margin:2px 0 4px;cursor:zoom-in;object-fit:cover}
.fl{display:flex;align-items:center;gap:10px;padding:8px 10px;background:rgba(127,127,127,.14);border-radius:10px;text-decoration:none!important;margin:3px 0}
.fl i{font-style:normal;font-size:22px}.fl span{display:block;font-size:13.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px}.fl small{opacity:.7;font-size:11.5px}
.tk{display:flex;align-items:center;gap:10px;padding:9px 11px;border-radius:12px;margin:4px 0;text-decoration:none!important;background:linear-gradient(135deg,rgba(56,189,248,.18),rgba(34,197,94,.16));border:1px solid rgba(56,189,248,.45)}
.tk i{font-style:normal;font-size:20px}.tk b{display:block;font-size:14px}.tk small{opacity:.75;font-size:11.5px}
/* composer */
#cmp{background:var(--card);border-top:1px solid var(--line);padding:8px max(10px,calc((100% - 1000px)/2)) calc(8px + env(safe-area-inset-bottom,0px))}
#pend{display:flex;gap:8px;flex-wrap:wrap;padding:0 2px 8px}#pend:empty{display:none}
.pc{position:relative;display:flex;align-items:center;gap:8px;padding:6px 26px 6px 8px;background:var(--col);border:1px solid var(--line);border-radius:10px;font-size:12.5px;max-width:220px}
.pc img{width:38px;height:38px;object-fit:cover;border-radius:6px}.pc span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pc button{position:absolute;right:3px;top:3px;border:0;background:none;color:var(--mut);font-size:15px;line-height:1}
.cr{display:flex;gap:6px;align-items:flex-end}
.tb{border:0;background:none;width:38px;height:38px;border-radius:10px;font-size:19px;color:var(--mut);flex:none}.tb:hover{background:var(--col);color:var(--ink)}
#rtx{flex:1;min-width:0;max-height:45vh;overflow:auto;padding:9px 13px;border:1px solid var(--acc);border-radius:16px;background:var(--col);color:var(--ink);font-size:15px;outline:0;line-height:1.4;overflow-wrap:anywhere}#rtx img{max-width:100%;max-height:260px;border-radius:8px;display:block;margin:6px 0}#rtx table,.rich table{border-collapse:collapse;margin:4px 0;max-width:100%}#rtx td,#rtx th,.rich td,.rich th{border:1px solid rgba(127,127,127,.4);padding:3px 6px;vertical-align:top}#rtx p,.rich p{margin:0 0 6px}#rtx ul,#rtx ol,.rich ul,.rich ol{margin:4px 0;padding-left:22px}
.bb .tx.rich{white-space:normal}.rich img.im{max-width:100%;max-height:340px;border-radius:8px;display:block;margin:6px 0;cursor:zoom-in}#dlbar{position:fixed;left:50%;bottom:20px;transform:translateX(-50%);z-index:60;display:flex;gap:8px;align-items:center;background:#0f2a5c;color:#fff;padding:10px 14px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,.35);max-width:94vw;font-size:14px}#dlbar[hidden]{display:none}#dlbar #dlt{max-width:38vw;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}#dlbar button{border:0;border-radius:9px;padding:6px 11px;font-weight:600;cursor:pointer;background:rgba(255,255,255,.16);color:#fff}#dlbar button:hover{background:rgba(255,255,255,.28)}#dlbar #dlo{background:#38bdf8;color:#06223f}
.sn.seen{color:#38bdf8;font-weight:600}
#tx{flex:1;resize:none;max-height:45vh;min-height:40px;padding:9px 13px;border:1px solid var(--line);border-radius:20px;background:var(--col);color:var(--ink);font:inherit;font-size:15px;outline:0;line-height:1.35}
#tx:focus{border-color:var(--acc)}
#snd{border:0;width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#38bdf8,#22c55e);color:#062a4a;font-size:18px;font-weight:900;flex:none;display:grid;place-items:center}
#snd:disabled{opacity:.45}
.pop{position:absolute;bottom:64px;left:10px;right:10px;max-width:420px;background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 12px 34px rgba(0,0,0,.3);z-index:20;overflow:hidden}
.pop[hidden]{display:none}.pop input{width:100%;border:0;border-bottom:1px solid var(--line);padding:11px 14px;background:var(--card);color:var(--ink);font:inherit;outline:0}
.pop .li{max-height:260px;overflow:auto}
.pop .it{display:block;width:100%;text-align:left;border:0;background:none;padding:9px 14px;border-bottom:1px solid var(--line)}.pop .it:hover{background:var(--col)}
.pop .it b{display:block;font-size:14px}.pop .it small{color:var(--mut);font-size:12px}
#emo{display:grid;grid-template-columns:repeat(8,1fr);gap:2px;padding:8px}#emo button{border:0;background:none;font-size:22px;border-radius:8px;height:38px}#emo button:hover{background:var(--col)}
#lb{position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:50;display:grid;place-items:center;cursor:zoom-out}#lb[hidden]{display:none}#lb img{max-width:96vw;max-height:84vh;border-radius:8px}
#lbt{position:absolute;top:calc(10px + env(safe-area-inset-top,0px));right:12px;display:flex;gap:8px;cursor:default}
.lbb{border:0;border-radius:10px;padding:9px 14px;font-weight:700;font-size:14px;background:rgba(255,255,255,.16);color:#fff;backdrop-filter:blur(6px)}.lbb:hover{background:rgba(255,255,255,.28)}.lbb.p{background:linear-gradient(135deg,#38bdf8,#22c55e);color:#062a4a}
#ann{position:fixed;inset:0;z-index:55;background:#0b0e14;display:flex;flex-direction:column}#ann[hidden]{display:none}
#annb{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:8px 10px calc(8px) 10px;padding-top:calc(8px + env(safe-area-inset-top,0px));background:#151a26;border-bottom:1px solid #2a3042}
#annb .g{display:flex;gap:4px;align-items:center;padding-right:8px;margin-right:2px;border-right:1px solid #2a3042}#annb .g:last-child{border:0;margin-left:auto;padding:0}
.at{border:1px solid transparent;background:#1f2536;color:#e8eaf2;border-radius:9px;padding:7px 11px;font-size:14px;font-weight:700}.at.on{background:#1d4694;border-color:#38bdf8}.at:disabled{opacity:.4}
.sw{width:26px;height:26px;border-radius:50%;border:2px solid #fff3;padding:0}.sw.on{border-color:#fff;box-shadow:0 0 0 2px #38bdf8}
#annw{flex:1;min-height:0;display:grid;place-items:center;padding:10px;overflow:hidden;position:relative}
#annc{touch-action:none;cursor:crosshair;background:#fff;border-radius:4px;box-shadow:0 6px 30px rgba(0,0,0,.5)}
#annt{position:absolute;z-index:3;background:rgba(255,255,255,.92);border:2px solid #38bdf8;border-radius:6px;padding:3px 6px;font:700 18px system-ui;min-width:90px;outline:0}#annt[hidden]{display:none}
#toast{position:fixed;left:50%;bottom:90px;transform:translateX(-50%);background:#0f172a;color:#fff;padding:9px 16px;border-radius:10px;font-size:13.5px;z-index:60;box-shadow:0 8px 24px rgba(0,0,0,.35)}#toast[hidden]{display:none}
#drop{position:absolute;inset:0;background:rgba(56,189,248,.18);border:3px dashed var(--acc);z-index:15;display:grid;place-items:center;font-weight:800;font-size:18px;color:var(--pri)}#drop[hidden]{display:none}
@media(max-width:760px){
 #app{grid-template-columns:minmax(0,1fr)}
 #main{position:fixed;inset:0;z-index:10;transform:translateX(100%);transition:transform .22s ease}
 #main.open{transform:none}#ph{display:none}.bk{display:block}
 .bb{max-width:86%}
}
</style>
</head>
<body>
<div id="app">
 <aside id="side">
  <div class="sh"><img src="chat-192.png?v=2" alt=""><div class="t"><b>GC Chat</b><small id="who"></small></div><button class="ib" id="mb" title="Menu" aria-haspopup="true">⋯</button></div>
  <div id="menu" hidden>
   <button id="mnot">🔔 Notifications: …</button>
   <button id="mph">📷 My profile photo</button><input type="file" id="phf" accept="image/*" hidden>
   <a href="index.php?home=1" target="_blank" rel="noopener">↗ Open TaskFlow</a>
   <a href="login.php?logout=1&next=chat">⎋ Log out</a>
  </div>
  <div class="sr"><input id="q" type="search" placeholder="Search people" autocomplete="off"></div>
  <div id="list"><div class="empty">Loading…</div></div>
  <button id="bld" type="button" title="Version of this app. Tap to reload and get the newest version.">Build</button>
 </aside>
 <main id="main">
  <div id="ph"><div><img src="chat-512.png?v=2" alt=""><h2>GC Chat</h2><div>Pick a person or the team chat to start.</div></div></div>
  <section id="thr">
   <div class="th"><button class="bk" id="bk" aria-label="Back">‹</button><div class="avw sm" id="tav"></div><div class="t"><b id="tn"></b><small id="ts"></small></div></div>
   <div id="msgs"></div>
   <div id="cmp">
    <div id="pend"></div>
    <div class="cr">
     <button class="tb" id="bat" title="Attach photo or file (deleted after 48 hours)">📎</button>
     <button class="tb" id="btk" title="Share a task">📋</button>
     <button class="tb" id="bem" title="Emoji">😊</button>
     <textarea id="tx" rows="1" placeholder="Write a message" enterkeyhint="send" spellcheck="true" autocorrect="on" autocapitalize="sentences" lang="en"></textarea><div id="rtx" contenteditable="true" spellcheck="true" lang="en" role="textbox" aria-label="Write a message" data-ph="Write a message" hidden></div>
     <button id="snd" title="Send" aria-label="Send">➤</button>
    </div>
    <input type="file" id="fi" multiple hidden>
   </div>
   <div class="pop" id="tkp" hidden><input id="tkq" placeholder="Search tasks by title or order no." autocomplete="off"><div class="li" id="tkl"></div></div>
   <div class="pop" id="emp" hidden><div id="emo"></div></div>
  </section>
  <div id="drop" hidden>Drop to attach</div>
  <div id="dlbar" hidden><span id="dlt"></span><button type="button" id="dlo">Open file</button><button type="button" id="dlf">Show in folder</button><button type="button" id="dlx" aria-label="Close">✕</button></div>
 </main>
</div>
<div id="lb" hidden><div id="lbt"><button class="lbb" id="lbd" type="button">⬇ Download</button><button class="lbb p" id="lba" type="button">✎ Annotate</button><button class="lbb" id="lbx" type="button" aria-label="Close">✕</button></div><img alt=""></div>
<div id="ann" hidden>
 <div id="annb">
  <div class="g"><button class="at on" data-tool="pen" type="button">✏ Pen</button><button class="at" data-tool="arrow" type="button">➜ Arrow</button><button class="at" data-tool="rect" type="button">▭ Box</button><button class="at" data-tool="text" type="button">T Text</button></div>
  <div class="g" id="annco"></div>
  <div class="g"><button class="at" data-sz="3" type="button">S</button><button class="at on" data-sz="6" type="button">M</button><button class="at" data-sz="11" type="button">L</button></div>
  <div class="g"><button class="at" id="annun" type="button">↶ Undo</button><button class="at" id="anncl" type="button">Clear</button></div>
  <div class="g"><button class="at" id="anndl" type="button">⬇ Save</button><button class="at on" id="annsd" type="button">➤ Add to message</button><button class="at" id="annx" type="button">Cancel</button></div>
 </div>
 <div id="annw"><canvas id="annc"></canvas><input id="annt" hidden placeholder="Type, press Enter"></div>
</div>
<div id="rpop" hidden></div>
<div id="toast" hidden></div>
<script>
"use strict";
const BUILD=281;
const $=s=>document.querySelector(s);
const esc=s=>String(s==null?"":s).replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
let ME={id:0,name:""},conv=[],team={},cur=null,lastId=0,peerSeen=0,peerDlv=0,pending=[],sending=false,msgs=[],unreadTotal=0,firstLoad=true,knownLast={};
const PAL=["#2563eb","#0891b2","#16a34a","#d97706","#9333ea","#dc2626","#0d9488","#db2777"];
const col=n=>{let h=0;for(const c of String(n))h=(h*31+c.charCodeAt(0))>>>0;return matchMedia("(prefers-color-scheme:dark)").matches?"hsl("+(h%360)+" 18% 32%)":"hsl("+(h%360)+" 28% 80%)"};
const ini=n=>String(n||"?").trim().split(/\s+/).slice(0,2).map(x=>x[0]||"").join("").toUpperCase();
const phUrl=(id,v)=>"api.php?a=photo&id="+id+"&v="+v;
const phOf=id=>{if(ME&&ME.id===id)return ME.ph||0;const c=conv.find(x=>x.id===id);return c&&c.ph||0};
const avHtml=(n,team_,sm,on,id,ph)=>team_?`<div class="avw${sm?" sm":""}"><div class="av" style="background:linear-gradient(135deg,#38bdf8,#22c55e);color:#fff"><svg viewBox="0 0 24 24" width="60%" height="60%" fill="#fff" aria-hidden="true"><circle cx="9" cy="8" r="3.3"/><path d="M2.5 19c0-3.4 2.9-5.6 6.5-5.6s6.5 2.2 6.5 5.6z"/><circle cx="17" cy="9" r="2.6" opacity=".85"/><path d="M16.2 13.6c3 0 5.3 1.8 5.3 4.6h-4.1c0-1.9-.5-3.4-1.8-4.4.2-.1.4-.2.6-.2z" opacity=".85"/></svg></div></div>`:`<div class="avw${sm?" sm":""}">${ph?`<img class="av" src="${phUrl(id,ph)}" alt="" onerror="this.outerHTML='<div class=&quot;av&quot; style=&quot;background:#ddd&quot;></div>'">`:`<div class="av" style="background:${col(n)}">${esc(ini(n))}</div>`}<i class="stt ${on?"on":"off"}" title="${on?"Online":"Offline"}"></i></div>`;
async function api(a,data){
  const r=await fetch("api.php?a="+a,{method:"POST",headers:{"Content-Type":"application/json","X-Requested-With":"fetch"},body:JSON.stringify(data||{}),credentials:"same-origin"});
  if(r.status===401){location="login.php?next=chat";throw new Error("auth")}
  const j=await r.json();if(j.error)throw new Error(j.error);return j}
function toast(t){const e=$("#toast");e.textContent=t;e.hidden=false;clearTimeout(toast.t);toast.t=setTimeout(()=>e.hidden=true,3200)}
const pad=n=>String(n).padStart(2,"0");
const D=s=>new Date(String(s).replace(" ","T"));
const hm=d=>d.toLocaleTimeString(undefined,{hour:"numeric",minute:"2-digit"});
const sameDay=(a,b)=>a.toDateString()===b.toDateString();
function dayLabel(d){const t=new Date(),y=new Date(Date.now()-864e5);if(sameDay(d,t))return"Today";if(sameDay(d,y))return"Yesterday";return d.toLocaleDateString(undefined,{weekday:"long",day:"numeric",month:"short",year:d.getFullYear()===t.getFullYear()?undefined:"numeric"})}
function listTime(s){if(!s)return"";const d=D(s),t=new Date();if(sameDay(d,t))return hm(d);if(sameDay(d,new Date(Date.now()-864e5)))return"Yesterday";return d.toLocaleDateString(undefined,{day:"numeric",month:"short"})}
function fmtSize(n){return n>1048576?(n/1048576).toFixed(1)+" MB":Math.max(1,Math.round(n/1024))+" KB"}
/* message body: escape, task chips, links */
function body(b){
  let h="",rest=String(b||""),tasks=[];
  rest=rest.replace(/\[\[task:(\d+):(\d+):([^\]]*)\]\]/g,(m,w,i,t)=>{tasks.push([w,i,t]);return""}).trim();
  h=esc(rest).replace(/(https?:\/\/[^\s<]+[^\s<.,;:!?)\]])/g,'<a href="$1" target="_blank" rel="noopener">$1</a>');
  return {text:h,tasks}}
/* sidebar */
function chkBuild(b){const e=$("#bld");if(b&&+b!==BUILD){e.textContent="↻ Build "+b+" ready · tap to update";e.classList.add("new")}else{e.textContent="GC Chat · Build "+BUILD;e.classList.remove("new")}}
$("#bld").onclick=()=>location.reload();chkBuild(0);
function headStat(force){if(!cur)return;const c=cur.team?null:conv.find(x=>x.id===cur.id),on=!!(c&&c.online),k=cur.team+"|"+on+"|"+cur.name+"|"+((c&&c.ph)||0);if(!force&&headStat.k===k)return;headStat.k=k;
  const t=$("#tav");if(t)t.outerHTML=avHtml(cur.name,cur.team,true,on,cur.id,(c&&c.ph)||0).replace('<div class="avw sm"','<div id="tav" class="avw sm"');
  $("#ts").textContent=cur.team?"Everyone at GURUCADD":(on?"Online":"Offline")+(cur.role==="guest"?" · Guest":"")}
function renderList(){headStat();
  const q=$("#q").value.trim().toLowerCase();
  const rows=[{id:0,name:"Team chat",team:true,...team}].concat(conv);
  rows.sort((a,b)=>{if(a.team)return -1;if(b.team)return 1;const A=a.at||"",B=b.at||"";return A===B?a.name.localeCompare(b.name):(A<B?1:-1)});
  const f=rows.filter(r=>!q||r.name.toLowerCase().includes(q)||(r.team&&"team chat everyone".includes(q)));
  $("#list").innerHTML=f.map(r=>`<button class="cv${cur&&cur.id===r.id?" on":""}${r.n?" un":""}" data-id="${r.id}">${avHtml(r.name,r.team,false,r.online,r.id,0)}<b>${esc(r.name)}${r.role==="guest"?"<i>guest</i>":""}</b><span class="tm">${esc(listTime(r.at))}</span><small>${r.lb?(r.lm?"You: ":"")+esc(r.lb):(r.team?"Everyone at GURUCADD":"No messages yet")}</small>${r.n?`<span class="ub">${r.n>99?"99+":r.n}</span>`:""}</button>`).join("")||'<div class="empty">No one found</div>'}
async function loadList(){
  const r=await api("msg_users");conv=r.users||[];team=r.team||{};chkBuild(r.build);
  const tot=conv.reduce((a,c)=>a+c.n,0)+(team.n||0);
  // alerts for new messages in conversations that are not open
  const all=[{id:0,name:"Team chat",...team}].concat(conv);
  for(const c of all){const k=c.last||0,old=knownLast[c.id];
    if(!firstLoad&&old!==undefined&&k>old&&c.n>0&&(!cur||cur.id!==c.id||document.hidden||!document.hasFocus()))notify(c);
    knownLast[c.id]=k}
  firstLoad=false;badge(tot);renderList()}
function badge(n){unreadTotal=n;document.title=(n?"("+(n>99?"99+":n)+") ":"")+"GC Chat";try{if(navigator.setAppBadge){n?navigator.setAppBadge(n):navigator.clearAppBadge()}}catch(e){}try{window.tfNative&&window.tfNative.badge&&window.tfNative.badge(n)}catch(e){}}
function beep(){try{const a=new (window.AudioContext||window.webkitAudioContext)(),o=a.createOscillator(),g=a.createGain();o.type="sine";o.frequency.value=880;g.gain.value=.05;o.connect(g);g.connect(a.destination);o.start();o.stop(a.currentTime+.12)}catch(e){}}
function notify(c){if(window.tfNative&&tfNative.notify){try{tfNative.notify(c.id?c.name:"Team chat",c.lb||"New message",c.id)}catch(e){}if(document.hidden||!document.hasFocus())beep();return}beep();if(!("Notification" in window)||Notification.permission!=="granted")return;
  try{const n=new Notification(c.id?c.name:"Team chat",{body:(c.lb||"New message"),icon:"chat-192.png?v=2",tag:"c"+c.id});n.onclick=()=>{window.focus();open_(c.id)}}catch(e){}}
/* thread */
const byId=id=>id?conv.find(c=>c.id===id):{id:0,name:"Team chat",team:true};
async function open_(id){
  const c=byId(id);if(!c)return;cur={id:c.id,name:c.name,team:!!c.team||!c.id,role:c.role};lastId=0;peerSeen=0;msgs=[];
  $("#msgs").innerHTML="";$("#main").classList.add("open");
  headStat(true);
  $("#tn").textContent=cur.name;
  pending=[];setRich(false);renderPend();renderList();await pull(true);if(matchMedia("(pointer:fine)").matches)$("#tx").focus();{const m=$("#msgs");if(m)m.scrollTop=m.scrollHeight}}
const QUICK=["👍","❤️","😆","😮","😢"],MORE=["👍","❤️","😆","😮","😢","🙏","👏","🔥","✅","❌","🎉","💡","👀","😅","🤝","💯"];
function hdrTime(d){const t=hm(d),n=new Date();if(sameDay(d,n))return"Today "+t;if(sameDay(d,new Date(Date.now()-864e5)))return"Yesterday "+t;
  if(n-d<6*864e5)return d.toLocaleDateString(undefined,{weekday:"short"})+" "+t;return d.toLocaleDateString(undefined,{day:"numeric",month:"short",year:d.getFullYear()===n.getFullYear()?undefined:"numeric"})+" "+t}
function bubble(m){
  if(m.deleted)return `<div class="bb dl" data-m="${m.id}"><i>🚫 This message was deleted</i></div>`;
  const b=body(m.body);
  const inl=new Set();if(m.html)for(const x of m.html.matchAll(/data-fid="(\d+)"/g))inl.add(+x[1]);
  const rich=m.html?`<div class="tx rich">${m.html.replace(/<img data-fid="(\d+)">/g,(x,id)=>{const f=(m.files||[]).find(q=>q.id==id);if(!f)return "";if(f.gone)return '<span class="gone" style="opacity:.65">🗑 picture removed after 48 hours</span>';return `<img class="im" src="api.php?a=msg_file&id=${f.id}" alt="${esc(f.name)}" data-big="api.php?a=msg_file&id=${f.id}" data-fid="${f.id}" data-name="${esc(f.name)}" loading="lazy">`})}</div>`:"";
  const fl=(m.files||[]).filter(f=>!inl.has(f.id)).map(f=>f.gone?`<div class="fl" style="opacity:.65"><i>🗑</i><div><span>${esc(f.name)}</span><small>Removed after 48 hours</small></div></div>`:/^image\//.test(f.mime)?`<img class="im" src="api.php?a=msg_file&id=${f.id}" alt="${esc(f.name)}" data-big="api.php?a=msg_file&id=${f.id}" data-fid="${f.id}" data-name="${esc(f.name)}" loading="lazy">`:`<a class="fl" href="api.php?a=msg_file&id=${f.id}&dl=1" data-dl="${f.id}" data-name="${esc(f.name)}"><i>📄</i><div><span>${esc(f.name)}</span><small>${esc(fmtSize(f.size))}</small></div></a>`).join("");
  const tk=b.tasks.map(t=>`<a class="tk" href="index.php?w=${t[0]}&t=${t[1]}" target="_blank" rel="noopener"><i>📋</i><div><b>${esc(t[2]||"Task")}</b><small>Open task in TaskFlow ↗</small></div></a>`).join("");
  const live=(m.files||[]).some(f=>!f.gone),hl=Math.ceil(48-(Date.now()-D(m.created_at).getTime())/36e5),exp=live&&hl>0?`<div class="ex" title="Attachments are deleted 48 hours after sending">📎 deleted in ${hl>=2?hl+" h":"<2 h"}</div>`:"";
  const fs=(m.files||[]).filter(f=>!f.gone),hasImg=fs.some(f=>/^image\//.test(f.mime));
  const bt=`<div class="bt">${QUICK.map(e=>`<button type="button" data-r="${e}" title="React">${e}</button>`).join("")}<button type="button" data-more="1" title="More reactions">☺</button>${fs.length?`<span class="sp"></span><button type="button" data-dlm="1" title="Download${fs.length>1?" all":""}">⬇</button>`:""}${hasImg?`<button type="button" data-anm="1" title="Annotate picture">✎</button>`:""}${m.mine?`<span class="sp"></span>${m.html?"":`<button type="button" data-ed="1" title="Edit message">✏️</button>`}<button type="button" data-del="1" title="Delete message">🗑</button>`:""}</div>`;
  const rx=(m.reacts||[]).length?`<div class="rx" data-m="${m.id}">${m.reacts.map(r=>`<button type="button" class="rp${r.mine?" on":""}" data-r="${esc(r.e)}" title="${esc(r.names.join(", "))}">${esc(r.e)} <b>${r.n}</b></button>`).join("")}</div>`:"";
  return `<div class="bb" data-m="${m.id}" title="${esc(hdrTime(D(m.created_at)))}">${bt}${fl}${m.html?rich:(b.text?`<div class="tx">${b.text}</div>`:"")}${m.html?"":tk}${m.edited?`<div class="ed">(edited)</div>`:""}${exp}</div>${rx}`}
function paint(all){
  const el=$("#msgs");let h="",prev=null,open="";
  let openMine=false;const close=()=>{if(open){h+=open+(openMine?"</div></div>":"</div></div></div>");open=""}};
  let lastMine=0;all.forEach(m=>{if(m.mine)lastMine=m.id});
  for(const m of all){
    const d=D(m.created_at),same=prev&&prev.mine===m.mine&&(m.mine||prev.from_id===m.from_id)&&(d-D(prev.created_at))<5*60000&&sameDay(d,D(prev.created_at));
    if(!same){close();
      const hd=`<div class="hd">${m.mine?"":`<b style="font-weight:600;color:var(--ink)">${esc(m.name)}</b>`}<span>${esc(hdrTime(d))}</span></div>`;
      openMine=!!m.mine;
      open=m.mine?`<div class="g me">${hd}<div class="gc">`:`<div class="g"><div class="gav">${phOf(m.from_id)?`<img src="${phUrl(m.from_id,phOf(m.from_id))}" alt="">`:esc(ini(m.name))}</div><div>${hd}<div class="gc">`}
    open+=bubble(m);
    if(m.mine&&!cur.team&&m.id===lastMine)open+=`<div class="sn${m.id<=peerSeen?" seen":""}">${m.id<=peerSeen?"✓✓ Seen":m.id<=peerDlv?"✓✓ Delivered":"✓ Sent"}</div>`;
    prev=m}
  close();
  const near=el.scrollHeight-el.scrollTop-el.clientHeight<140;
  el.innerHTML=h||'<div class="empty">No messages yet. Say hello 👋</div>';
  return near}
async function pull(first){
  if(!cur)return;const id=cur.id;
  const away=document.hidden||!document.hasFocus();const r=await api("msg_list",{peer:id,after:first?0:lastId,noread:away?1:0});if(!cur||cur.id!==id)return;
  const nu=r.messages||[];const seenChanged=!cur.team&&(r.peer_seen!==peerSeen||(r.peer_dlv||0)!==peerDlv);peerSeen=r.peer_seen||0;peerDlv=r.peer_dlv||0;
  const rm=r.reacts||{};let rxChanged=false;
  if(first)msgs=nu;else{const have=new Set(msgs.map(m=>m.id));nu.forEach(m=>{if(!have.has(m.id))msgs.push(m)})}
  const chg=r.changes||{};msgs.forEach(m=>{const k=chg[m.id];if(k){if(k.d&&!m.deleted){m.deleted=1;m.body="";m.files=[];m.reacts=[];rxChanged=true}else if(!k.d&&(k.b!==m.body||!m.edited)){m.body=k.b;m.edited=1;rxChanged=true}}});
  msgs.forEach(m=>{const nr=rm[m.id]||[];if(JSON.stringify(nr)!==JSON.stringify(m.reacts||[])){m.reacts=nr;rxChanged=true}});
  if(!nu.length&&!first&&!seenChanged&&!rxChanged)return;
  lastId=msgs.length?msgs[msgs.length-1].id:0;
  const el=$("#msgs"),wasNear=el.scrollHeight-el.scrollTop-el.clientHeight<140;paint(msgs);
  if(first||wasNear||nu.some(m=>m.mine))el.scrollTop=el.scrollHeight;
  if(nu.some(m=>!m.mine)&&!first){loadList().catch(()=>{})}else if(first)loadList().catch(()=>{})}
/* composer */
function renderPend(){
  $("#pend").innerHTML=(pending.length?'<div style="flex-basis:100%;font-size:11.5px;color:var(--mut)">Attachments are deleted automatically 48 hours after sending.</div>':"")+pending.map((f,i)=>`<div class="pc">${f.thumb?`<img src="${f.thumb}" alt="">`:"<span>📄</span>"}<span>${esc(f.name)}</span><button data-rm="${i}" aria-label="Remove">×</button></div>`).join("");
  $("#snd").disabled=sending}
function toB64(blob){return new Promise((ok,no)=>{const r=new FileReader();r.onload=()=>ok(String(r.result).split(",")[1]);r.onerror=no;r.readAsDataURL(blob)})}
async function shrink(file){
  if(!/^image\/(jpeg|png|webp)$/.test(file.type)||file.size<350*1024)return file;
  try{const bmp=await createImageBitmap(file),sc=Math.min(1,1600/Math.max(bmp.width,bmp.height));
    const c=document.createElement("canvas");c.width=Math.round(bmp.width*sc);c.height=Math.round(bmp.height*sc);c.getContext("2d").drawImage(bmp,0,0,c.width,c.height);
    const b=await new Promise(r=>c.toBlob(r,"image/jpeg",.82));return b&&b.size<file.size?new File([b],file.name.replace(/\.\w+$/,"")+".jpg",{type:"image/jpeg"}):file}catch(e){return file}}
async function addFiles(list){
  for(const f0 of list){if(pending.length>=6){toast("Up to 6 files per message");break}
    if(f0.size>40*1048576){toast(f0.name+" is too large");continue}
    const f=await shrink(f0);if(f.size>6*1048576){toast(f.name+" is over 6 MB");continue}
    pending.push({name:f.name||"file",mime:f.type||"application/octet-stream",blob:f,thumb:/^image\//.test(f.type)?URL.createObjectURL(f):""})}
  renderPend()}
async function send(){
  if(!cur||sending)return;const tx=$("#tx");let b=tx.value.trim(),html="",used=[];
  if(rich){const r=RT();b=r.innerText.replace(/\n{3,}/g,"\n\n").trim();const hasImg=!!r.querySelector("img[data-k]");if(!b&&!hasImg&&!pending.length)return;
    html=r.innerHTML.replace(/<img\b[^>]*data-k="(\d+)"[^>]*>/g,(x,k)=>{const f=richFiles[+k];if(!f)return "";let i=used.indexOf(f);if(i<0){used.push(f);i=used.length-1}return `<img data-i="${i}">`}).replace(/<img\b(?![^>]*data-i)[^>]*>/g,"");
    if(used.length+pending.length>6){toast("Up to 6 pictures and files per message");return}}
  else if(!b&&!pending.length)return;
  sending=true;renderPend();
  try{const files=[];for(const f of used)files.push({name:f.name,mime:f.type||"image/png",data:await toB64(f)});for(const p of pending)files.push({name:p.name,mime:p.mime,data:await toB64(p.blob)});
    await api("msg_send",rich?{peer:cur.id,body:b,html,files}:{peer:cur.id,body:b,files});tx.value="";tx.style.height="";setRich(false);pending.forEach(p=>p.thumb&&URL.revokeObjectURL(p.thumb));pending=[];
    sending=false;renderPend();await pull(false);const el=$("#msgs");el.scrollTop=el.scrollHeight;loadList().catch(()=>{})}
  catch(e){sending=false;renderPend();toast(e.message)}}
/* events */
$("#snd").onclick=send;
$("#tx").addEventListener("keydown",e=>{if(e.key==="Enter"&&!e.shiftKey&&!e.isComposing&&matchMedia("(pointer:fine)").matches){e.preventDefault();send()}});
$("#tx").addEventListener("input",e=>{const t=e.target;t.style.height="auto";t.style.height=Math.min(Math.round(innerHeight*.45),t.scrollHeight)+"px"});
/* rich composer: Outlook / Word paste keeps formatting and puts pictures where they were */
let rich=false,richFiles=[];const RT=()=>$("#rtx");
function setRich(on){rich=on;$("#tx").hidden=on;RT().hidden=!on;if(!on){RT().innerHTML="";richFiles=[]}}
function insTxt(t){if(rich){RT().focus();document.execCommand("insertText",false,t)}else{const x=$("#tx"),s=x.selectionStart==null?x.value.length:x.selectionStart,e=x.selectionEnd==null?s:x.selectionEnd;x.value=x.value.slice(0,s)+t+x.value.slice(e);x.focus();x.selectionStart=x.selectionEnd=s+t.length;x.dispatchEvent(new Event("input"))}}
function srcFile(s,n){try{let b;if(/^data:/i.test(s)){const m=s.match(/^data:([^;,]+)?(;base64)?,(.*)$/is);if(!m)return null;const bin=m[2]?atob(m[3]):decodeURIComponent(m[3]),u=new Uint8Array(bin.length);for(let i=0;i<bin.length;i++)u[i]=bin.charCodeAt(i);b=new Blob([u],{type:m[1]||"image/png"})}else return fetch(s).then(r=>r.blob()).then(b=>b.size>60?new File([b],"pasted-image-"+n+".png",{type:b.type||"image/png"}):null).catch(()=>null);
  return b.size>60?new File([b],"pasted-image-"+n+"."+((b.type.split("/")[1]||"png").replace("jpeg","jpg")),{type:b.type||"image/png"}):null}catch(x){return null}}
const RK={p:1,div:1,b:1,strong:1,i:1,em:1,u:1,s:1,strike:1,del:1,ul:1,ol:1,li:1,blockquote:1,pre:1,code:1,span:1,table:1,thead:1,tbody:1,tfoot:1,tr:1,td:1,th:1,sub:1,sup:1};
async function richBuild(html,fs){
  const d=new DOMParser().parseFromString(html,"text/html"),imgs=[...d.querySelectorAll("img")],map=new Map(),bad=[];let n=0;
  for(const im of imgs){const s=im.getAttribute("src")||"";let f=null;if(/^(data:image|blob:)/i.test(s))f=await srcFile(s,++n);if(f)map.set(im,f);else bad.push(im)}
  let spare=fs.slice();if(map.size)spare=[];if(spare.length&&spare.length===bad.length){bad.forEach((im,i)=>map.set(im,spare[i]));spare=[]}
  const reg=f=>{if(richFiles.length+used0()>=6)return"";richFiles.push(f);return `<img data-k="${richFiles.length-1}" src="${URL.createObjectURL(f)}">`};
  const used0=()=>pending.length;
  const w=(nd,pre)=>{let o="";for(const ch of nd.childNodes){
    if(ch.nodeType===3){let t=ch.nodeValue;if(!pre)t=t.replace(/[\s ]+/g," ");o+=esc(t);continue}
    if(ch.nodeType!==1)continue;const t=ch.nodeName.toLowerCase();
    if(["script","style","head","title","meta","link","object","embed","iframe","svg","xml","form","input","button"].includes(t))continue;
    if(t==="br"){o+="<br>";continue}if(t==="hr"){o+="<hr>";continue}
    if(t==="img"){const f=map.get(ch);if(f)o+=reg(f);continue}
    if(t==="a"){const h=(ch.getAttribute("href")||"").trim(),inn=w(ch,pre);o+=/^(https?:|mailto:)/i.test(h)?`<a href="${esc(h)}" target="_blank" rel="noopener">${inn}</a>`:inn;continue}
    if(/^h[1-6]$/.test(t)){o+="<p><b>"+w(ch,pre)+"</b></p>";continue}
    if(RK[t]){let at="";if(t==="td"||t==="th"){const cs=ch.getAttribute("colspan"),rs=ch.getAttribute("rowspan");if(/^\d+$/.test(cs||""))at+=` colspan="${+cs}"`;if(/^\d+$/.test(rs||""))at+=` rowspan="${+rs}"`}o+=`<${t}${at}>${w(ch,pre||t==="pre")}</${t}>`;continue}
    o+=w(ch,pre)}return o};
  let out=w(d.body,false);for(const f of spare)out+="<p>"+reg(f)+"</p>";return out}
const OFFICE=/urn:schemas-microsoft-com|class="?Mso|<o:p>|mso-/i,FORMAT=/<(b|strong|i|em|u|ul|ol|table|h[1-6]|blockquote)\b/i;
async function onPaste(e){const cd=e.clipboardData;if(!cd)return;
  const fs=[...(cd.files||[])].filter(f=>/^image\//.test(f.type)).map((f,i)=>new File([f],"pasted-image-"+(i+1)+"."+((f.type.split("/")[1]||"png").replace("jpeg","jpg")),{type:f.type}));
  const others=[...(cd.files||[])].filter(f=>!/^image\//.test(f.type));
  const html=cd.getData("text/html")||"",txt=cd.getData("text/plain")||"";
  const hasImg=/<img\b/i.test(html),useRich=hasImg||/<table\b/i.test(html)||(OFFICE.test(html)&&FORMAT.test(html));
  if(others.length&&!html){e.preventDefault();addFiles(others);return}
  if(!useRich&&!fs.length)return;                                  // ordinary text: normal paste
  e.preventDefault();
  if(!useRich){if(txt.trim())insTxt(txt.replace(/\r\n/g,"\n"));addFiles(fs);return}   // image(s) only
  const frag=html.replace(/^[\s\S]*?<!--StartFragment-->/i,"").replace(/<!--EndFragment-->[\s\S]*$/i,"");
  const out=await richBuild(frag,fs);
  if(!out.replace(/<[^>]*>/g,"").trim()&&!/<img/.test(out)){if(txt.trim())insTxt(txt);return}
  if(!rich){const old=$("#tx").value.trim();setRich(true);RT().innerHTML=(old?"<p>"+esc(old).replace(/\n/g,"<br>")+"</p>":"")+out;
    const r=document.createRange();r.selectNodeContents(RT());r.collapse(false);const sel=getSelection();sel.removeAllRanges();sel.addRange(r);RT().focus()}
  else{RT().focus();document.execCommand("insertHTML",false,out)}
  if(others.length)addFiles(others);
  if(/<img/.test(frag)&&!/<img data-k/.test(out))toast("Outlook did not pass the pictures. Copy a picture alone, or use Win+Shift+S and paste.")}
$("#tx").addEventListener("paste",onPaste);RT().addEventListener("paste",onPaste);
RT().addEventListener("keydown",e=>{if(e.key==="Enter"&&!e.shiftKey&&!e.isComposing&&matchMedia("(pointer:fine)").matches){e.preventDefault();send()}});
RT().addEventListener("input",()=>{const r=RT();if(!r.innerText.trim()&&!r.querySelector("img")){setRich(false);$("#tx").focus()}});
$("#bat").onclick=()=>$("#fi").click();$("#fi").onchange=e=>{addFiles([...e.target.files]);e.target.value=""};
$("#pend").onclick=e=>{const b=e.target.closest("[data-rm]");if(!b)return;const p=pending.splice(+b.dataset.rm,1)[0];if(p&&p.thumb)URL.revokeObjectURL(p.thumb);renderPend()};
$("#list").onclick=e=>{const b=e.target.closest(".cv");if(b)open_(+b.dataset.id)};
$("#q").oninput=renderList;
$("#bk").onclick=()=>{$("#main").classList.remove("open");cur=null;renderList()};
let lbCur=null;
$("#msgs").onclick=e=>{const d=e.target.closest("[data-dl]");if(d){e.preventDefault();download(d.dataset.dl,d.dataset.name);return}const i=e.target.closest(".im");if(i){lbCur={id:i.dataset.fid,name:i.dataset.name,src:i.dataset.big};$("#lb img").src=i.dataset.big;$("#lb").hidden=false}};
$("#lb").onclick=e=>{if(!e.target.closest("#lbt"))$("#lb").hidden=true};
$("#lbx").onclick=()=>{$("#lb").hidden=true};
$("#lbd").onclick=()=>lbCur&&download(lbCur.id,lbCur.name);
$("#lba").onclick=()=>{if(lbCur){$("#lb").hidden=true;annOpen(lbCur)}};
/* download: works in the browser, Windows app and Android app */
async function download(id,name){
  try{const r=await fetch("api.php?a=msg_file&id="+id+"&dl=1",{credentials:"same-origin"});if(r.status===410){toast("This file was removed after 48 hours");return}if(!r.ok)throw new Error("Download failed");
    const blob=await r.blob();await saveBlob(blob,name||"file")}catch(e){toast(e.message||"Download failed")}}
async function saveBlob(blob,name){
  const C=window.Capacitor,P=C&&C.isNativePlatform&&C.isNativePlatform()&&C.Plugins;
  if(P&&P.Filesystem&&P.Share){const b64=await toB64(blob);const w=await P.Filesystem.writeFile({path:name,data:b64,directory:"CACHE"});await P.Share.share({title:name,url:w.uri,dialogTitle:"Save or share"});return}
  const u=URL.createObjectURL(blob),a=document.createElement("a");a.href=u;a.download=name;document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(u),60000);if(!(window.tfNative&&tfNative.onDownload))toast("Saved: "+name)}
/* desktop app: after a download offer to open the file or its folder */
if(window.tfNative&&tfNative.onDownload){let dt=0,dp="";tfNative.onDownload(d=>{dp=d.path;$("#dlt").textContent="✓ Saved to Downloads: "+d.name;$("#dlbar").hidden=false;clearTimeout(dt);dt=setTimeout(()=>$("#dlbar").hidden=true,15000)});
  $("#dlo").onclick=()=>{tfNative.openFile(dp);$("#dlbar").hidden=true};$("#dlf").onclick=()=>{tfNative.showFolder(dp);$("#dlbar").hidden=true};$("#dlx").onclick=()=>$("#dlbar").hidden=true}
/* annotate a picture, then add it to the message as feedback */
const A={img:null,name:"",shapes:[],tool:"pen",color:"#ef4444",sz:6,cur:null,W:0,H:0};
["#ef4444","#f59e0b","#22c55e","#38bdf8","#ffffff","#111827"].forEach((c,i)=>{const b=document.createElement("button");b.type="button";b.className="sw"+(i?"":" on");b.style.background=c;b.dataset.c=c;$("#annco").appendChild(b)});
function annOpen(f){
  const im=new Image();im.onload=()=>{A.img=im;A.name=f.name||"image";A.shapes=[];A.cur=null;
    const sc=Math.min(1,2000/Math.max(im.naturalWidth,im.naturalHeight));A.W=Math.round(im.naturalWidth*sc);A.H=Math.round(im.naturalHeight*sc);
    const cv=$("#annc");cv.width=A.W;cv.height=A.H;$("#annt").hidden=true;$("#ann").hidden=false;annFit();annDraw();annTools()};
  im.onerror=()=>toast("Could not open the picture");im.src=f.src}
function annFit(){const w=$("#annw"),cv=$("#annc");if(!A.W)return;const s=Math.min((w.clientWidth-20)/A.W,(w.clientHeight-20)/A.H,3);cv.style.width=Math.round(A.W*s)+"px";cv.style.height=Math.round(A.H*s)+"px"}
window.addEventListener("resize",()=>{if(!$("#ann").hidden)annFit()});
function annTools(){document.querySelectorAll("#annb [data-tool]").forEach(b=>b.classList.toggle("on",b.dataset.tool===A.tool));document.querySelectorAll("#annb [data-sz]").forEach(b=>b.classList.toggle("on",+b.dataset.sz===A.sz));document.querySelectorAll("#annco .sw").forEach(b=>b.classList.toggle("on",b.dataset.c===A.color));$("#annun").disabled=!A.shapes.length}
function annShape(g,s){
  const k=Math.max(.6,Math.max(A.W,A.H)/1000),lw=s.sz*k;g.strokeStyle=s.color;g.fillStyle=s.color;g.lineWidth=lw;g.lineCap="round";g.lineJoin="round";
  if(s.t==="pen"){g.beginPath();s.p.forEach((q,i)=>i?g.lineTo(q[0],q[1]):g.moveTo(q[0],q[1]));if(s.p.length===1)g.lineTo(s.p[0][0]+.1,s.p[0][1]);g.stroke()}
  else if(s.t==="rect"){g.strokeRect(s.a[0],s.a[1],s.b[0]-s.a[0],s.b[1]-s.a[1])}
  else if(s.t==="arrow"){const[dx,dy]=[s.b[0]-s.a[0],s.b[1]-s.a[1]],L=Math.hypot(dx,dy);if(L<2)return;const an=Math.atan2(dy,dx),h=Math.max(14*k,lw*3.2);
    g.beginPath();g.moveTo(s.a[0],s.a[1]);g.lineTo(s.b[0]-Math.cos(an)*h*.6,s.b[1]-Math.sin(an)*h*.6);g.stroke();
    g.beginPath();g.moveTo(s.b[0],s.b[1]);g.lineTo(s.b[0]-h*Math.cos(an-.42),s.b[1]-h*Math.sin(an-.42));g.lineTo(s.b[0]-h*Math.cos(an+.42),s.b[1]-h*Math.sin(an+.42));g.closePath();g.fill()}
  else if(s.t==="text"){const fs=Math.max(18*k,s.sz*k*3.4);g.font="800 "+fs+"px system-ui,Segoe UI,Arial";g.textBaseline="top";g.lineWidth=fs/6;g.strokeStyle=s.color==="#ffffff"?"#111827":"#ffffff";g.strokeText(s.s,s.a[0],s.a[1]);g.fillText(s.s,s.a[0],s.a[1])}}
function annDraw(){const cv=$("#annc"),g=cv.getContext("2d");g.clearRect(0,0,A.W,A.H);g.drawImage(A.img,0,0,A.W,A.H);A.shapes.forEach(s=>annShape(g,s));if(A.cur)annShape(g,A.cur)}
function annPt(e){const r=$("#annc").getBoundingClientRect();return[(e.clientX-r.left)*A.W/r.width,(e.clientY-r.top)*A.H/r.height]}
const ac=$("#annc");
ac.addEventListener("pointerdown",e=>{if(!A.img)return;e.preventDefault();const p=annPt(e);
  if(A.tool==="text"){annText(e,p);return}
  ac.setPointerCapture(e.pointerId);A.cur=A.tool==="pen"?{t:"pen",color:A.color,sz:A.sz,p:[p]}:{t:A.tool,color:A.color,sz:A.sz,a:p,b:p};annDraw()});
ac.addEventListener("pointermove",e=>{if(!A.cur)return;const p=annPt(e);if(A.cur.t==="pen")A.cur.p.push(p);else A.cur.b=p;annDraw()});
const annEnd=()=>{if(!A.cur)return;A.shapes.push(A.cur);A.cur=null;annDraw();annTools()};
ac.addEventListener("pointerup",annEnd);ac.addEventListener("pointercancel",annEnd);
function annText(e,p){const t=$("#annt"),w=$("#annw").getBoundingClientRect();t.hidden=false;t.value="";t.style.left=(e.clientX-w.left)+"px";t.style.top=(e.clientY-w.top-14)+"px";t.style.color=A.color==="#ffffff"?"#111":A.color;t.dataset.x=p[0];t.dataset.y=p[1];setTimeout(()=>t.focus(),30)}
function annCommit(){const t=$("#annt");if(t.hidden)return;const s=t.value.trim();t.hidden=true;if(s){A.shapes.push({t:"text",color:A.color,sz:A.sz,a:[+t.dataset.x,+t.dataset.y],s});annDraw();annTools()}}
$("#annt").addEventListener("keydown",e=>{if(e.key==="Enter"){e.preventDefault();annCommit()}if(e.key==="Escape")$("#annt").hidden=true});
$("#annt").addEventListener("blur",annCommit);
$("#annb").addEventListener("click",e=>{const b=e.target.closest("button");if(!b)return;
  if(b.dataset.tool){A.tool=b.dataset.tool}else if(b.dataset.sz){A.sz=+b.dataset.sz}else if(b.dataset.c){A.color=b.dataset.c}
  else if(b.id==="annun"){A.shapes.pop()}else if(b.id==="anncl"){A.shapes=[]}
  else if(b.id==="annx"){$("#ann").hidden=true;A.img=null;return}
  else if(b.id==="anndl"||b.id==="annsd"){annCommit();annDraw();annExport(b.id==="annsd");return}
  annDraw();annTools()});
function annExport(addIt){
  $("#annc").toBlob(async bl=>{if(!bl){toast("Could not save the picture");return}
    const base=String(A.name).replace(/\.\w+$/,"")||"image",fn="annotated-"+base+".jpg";
    if(!addIt){await saveBlob(bl,fn);return}
    if(!cur){toast("Open a chat first");return}
    await addFiles([new File([bl],fn,{type:"image/jpeg"})]);$("#ann").hidden=true;A.img=null;toast("Annotated picture added. Write your feedback and press send.");$("#tx").focus()},"image/jpeg",.88)}
document.addEventListener("keydown",e=>{if(e.key==="Escape"){if(!$("#ann").hidden&&$("#annt").hidden){$("#ann").hidden=true;A.img=null}$("#lb").hidden=true;$("#tkp").hidden=true;$("#emp").hidden=true;$("#menu").hidden=true}});
$("#mb").onclick=e=>{e.stopPropagation();$("#menu").hidden=!$("#menu").hidden};
document.addEventListener("click",e=>{if(!e.target.closest("#menu,#mb"))$("#menu").hidden=true;if(!e.target.closest("#tkp,#btk"))$("#tkp").hidden=true;if(!e.target.closest("#emp,#bem"))$("#emp").hidden=true});
/* hover toolbar: reactions, download, annotate */
const msgOf=id=>msgs.find(m=>m.id==id);
async function react(id,e){try{const r=await api("msg_react",{id,e});const m=msgOf(id);if(m){m.reacts=r.reacts||[];const el=$("#msgs"),st=el.scrollTop;paint(msgs);el.scrollTop=st}}catch(x){toast(x.message)}}
function rpopClose(){$("#rpop").hidden=true}
$("#msgs").addEventListener("click",async e=>{
  const bt=e.target.closest(".bt button,.rx .rp");
  if(bt){e.stopPropagation();const row=bt.closest("[data-m]"),id=row&&+row.dataset.m;
    if(bt.dataset.r){react(id,bt.dataset.r);return}
    if(bt.dataset.more){const r=bt.getBoundingClientRect(),p=$("#rpop");p.innerHTML=MORE.map(x=>`<button type="button" data-id="${id}" data-e="${x}">${x}</button>`).join("");p.hidden=false;
      const w=p.offsetWidth,h=p.offsetHeight;p.style.left=Math.max(6,Math.min(innerWidth-w-6,r.left-w+40))+"px";p.style.top=(r.top-h-6<6?r.bottom+6:r.top-h-6)+"px";return}
    if(bt.dataset.del){if(!confirm("Delete this message for everyone?"))return;try{await api("msg_del",{id});const m0=msgOf(id);if(m0){m0.deleted=1;m0.body="";m0.files=[];m0.reacts=[];const el=$("#msgs"),st=el.scrollTop;paint(msgs);el.scrollTop=st}loadList().catch(()=>{})}catch(x){toast(x.message)}return}
    if(bt.dataset.ed){const m0=msgOf(id);if(!m0)return;const bb=$("#msgs").querySelector('.bb[data-m="'+id+'"]');if(!bb)return;
      bb.innerHTML=`<div class="eded"><textarea maxlength="2000"></textarea><div><button type="button" data-ec="1">Cancel</button><button type="button" class="p" data-es="1">Save</button></div></div>`;const ta=bb.querySelector("textarea");ta.value=m0.body;ta.focus();
      const done=()=>{const el=$("#msgs"),st=el.scrollTop;paint(msgs);el.scrollTop=st},save=async()=>{const v=ta.value.trim();if(v===m0.body.trim()){done();return}try{const r=await api("msg_edit",{id,body:v});m0.body=r.body;m0.edited=1;done()}catch(x){toast(x.message)}};
      bb.querySelector("[data-ec]").onclick=done;bb.querySelector("[data-es]").onclick=save;ta.addEventListener("keydown",k=>{if(k.key==="Escape")done();else if(k.key==="Enter"&&!k.shiftKey){k.preventDefault();save()}});return}
    const m=msgOf(id),fs=m?(m.files||[]).filter(f=>!f.gone):[];
    if(bt.dataset.dlm){for(const f of fs)await download(f.id,f.name);return}
    if(bt.dataset.anm){const f=fs.find(f=>/^image\//.test(f.mime));if(f)annOpen({id:f.id,name:f.name,src:"api.php?a=msg_file&id="+f.id});return}}
  if(matchMedia("(hover:none)").matches){const bb=e.target.closest(".bb");if(bb&&!e.target.closest("a,img,button")){const on=bb.classList.contains("hv");document.querySelectorAll(".bb.hv").forEach(x=>x.classList.remove("hv"));if(!on)bb.classList.add("hv")}}
},true);
$("#rpop").onclick=e=>{const b=e.target.closest("button");if(!b)return;rpopClose();react(+b.dataset.id,b.dataset.e)};
document.addEventListener("click",e=>{if(!e.target.closest("#rpop,[data-more]"))rpopClose()});
/* emoji */
$("#emo").innerHTML=["😀","😂","🙂","😉","😍","🤝","👍","👎","🙏","👏","🔥","✅","❌","⚠️","❓","🎉","💡","📐","📎","⏰","☕","😅","😢","❤️"].map(e=>`<button type="button">${e}</button>`).join("");
$("#bem").onclick=e=>{e.stopPropagation();$("#tkp").hidden=true;$("#emp").hidden=!$("#emp").hidden};
$("#emo").onclick=e=>{const b=e.target.closest("button");if(!b)return;insTxt(b.textContent)};
/* share a task */
let tkT=0;
async function tkSearch(){const q=$("#tkq").value.trim();try{const r=await api("chat_tasks",{q});$("#tkl").innerHTML=(r.tasks||[]).map(t=>`<button class="it" data-w="${t.ws_id}" data-i="${t.id}" data-t="${esc(t.title)}"><b>${esc(t.order_no?t.order_no+"  ":"")+esc(t.title)}</b><small>${esc(t.company||"")}</small></button>`).join("")||'<div class="empty">No tasks found</div>'}catch(e){$("#tkl").innerHTML='<div class="empty">'+esc(e.message)+"</div>"}}
$("#btk").onclick=e=>{e.stopPropagation();$("#emp").hidden=true;const p=$("#tkp");p.hidden=!p.hidden;if(!p.hidden){$("#tkq").value="";tkSearch();$("#tkq").focus()}};
$("#tkq").oninput=()=>{clearTimeout(tkT);tkT=setTimeout(tkSearch,250)};
$("#tkl").onclick=e=>{const b=e.target.closest(".it");if(!b)return;const tok="[[task:"+b.dataset.w+":"+b.dataset.i+":"+b.dataset.t.replace(/[\]\[:]/g," ").slice(0,90)+"]]";$("#tkp").hidden=true;insTxt((rich||!$("#tx").value?"":" ")+tok)};
/* drag and drop */
let dn=0;const mn=$("#main");
mn.addEventListener("dragenter",e=>{if(!cur||!e.dataTransfer||![...e.dataTransfer.types].includes("Files"))return;e.preventDefault();dn++;$("#drop").hidden=false});
mn.addEventListener("dragover",e=>{if(cur)e.preventDefault()});
mn.addEventListener("dragleave",()=>{dn=Math.max(0,dn-1);if(!dn)$("#drop").hidden=true});
mn.addEventListener("drop",e=>{e.preventDefault();dn=0;$("#drop").hidden=true;if(cur&&e.dataTransfer.files.length)addFiles([...e.dataTransfer.files])});
/* notifications permission */
function notState(){return !("Notification" in window)?"not supported":Notification.permission==="granted"?"On":Notification.permission==="denied"?"Blocked in browser":"Off"}
$("#mph").onclick=()=>{$("#menu").hidden=true;if(ME&&ME.ph&&confirm("Change your photo? Press Cancel to remove it instead."))$("#phf").click();else if(ME&&ME.ph){api("photo_set",{remove:1}).then(()=>{ME.ph=0;loadList().catch(()=>{});toast("Photo removed")}).catch(e=>toast(e.message))}else $("#phf").click()};
$("#phf").onchange=async e=>{const f=e.target.files[0];e.target.value="";if(!f)return;try{const bmp=await createImageBitmap(f),s=Math.min(bmp.width,bmp.height),c=document.createElement("canvas");c.width=c.height=256;c.getContext("2d").drawImage(bmp,(bmp.width-s)/2,(bmp.height-s)/2,s,s,0,0,256,256);
  const b=await new Promise(r=>c.toBlob(r,"image/jpeg",.85)),r=await api("photo_set",{data:await toB64(b)});ME.ph=r.ph||1;await loadList();headStat(true);toast("Photo updated")}catch(x){toast(x.message||"Could not use that picture")}};
function notLabel(){$("#mnot").textContent="🔔 Notifications: "+notState()}
$("#mnot").onclick=async()=>{if("Notification" in window&&Notification.permission==="default"){try{await Notification.requestPermission()}catch(e){}}else if(Notification.permission==="denied")toast("Notifications are blocked. Allow them in the browser or app settings.");notLabel();$("#menu").hidden=true};
/* phone push (Android app) */
(()=>{try{const C=window.Capacitor;if(!C||!C.isNativePlatform||!C.isNativePlatform()||!/TFPush/.test(navigator.userAgent))return;const PN=C.Plugins&&C.Plugins.PushNotifications;if(!PN)return;
 PN.addListener("registration",t=>{try{api("push_reg",{token:t.value,app:"chat"}).catch(()=>{})}catch(e){}});
 PN.addListener("pushNotificationActionPerformed",()=>{try{loadList()}catch(e){}});
 setTimeout(()=>{PN.checkPermissions().then(r=>r.receive==="granted"?r:PN.requestPermissions()).then(r=>{if(r.receive==="granted")PN.register()}).catch(()=>{})},2000)}catch(e){}})();
/* polling */
let busy=false;
async function tick(){if(busy)return;busy=true;try{await loadList();if(cur)await pull(false)}catch(e){}busy=false}
setInterval(tick,window.tfNative?4000:(document.hidden?15000:4000));if(window.tfNative&&tfNative.onOpen)tfNative.onOpen(id=>{try{if(id)open_(id)}catch(e){}});
document.addEventListener("visibilitychange",()=>{if(!document.hidden)tick()});
/* messages that arrived while the window was hidden or unfocused stay unread (taskbar count stays) until the chat is actually opened */
function seenNow(){if(cur&&!document.hidden&&document.hasFocus()){pull(true).catch(()=>{})}}
addEventListener("focus",seenNow);document.addEventListener("visibilitychange",()=>{if(!document.hidden)setTimeout(seenNow,150)});
window.addEventListener("focus",tick);
if("serviceWorker" in navigator)navigator.serviceWorker.register("sw.js").catch(()=>{});
/* start */
(async()=>{
  try{const j=await api("chat_me");ME=j.me;$("#who").textContent=ME.name||""}catch(e){if(/guest/i.test(e.message||"")){document.body.innerHTML='<div style="font:16px system-ui;padding:40px;text-align:center"><h3>Chat is for the GURUCADD team</h3><p>Guest accounts can add messages on tasks in TaskFlow.</p><p><a href="index.php">Back to TaskFlow</a></p></div>';throw e}}
  notLabel();
  if("Notification" in window&&Notification.permission==="default")setTimeout(()=>{try{Notification.requestPermission().then(notLabel)}catch(e){}},3000);
  try{await loadList()}catch(e){$("#list").innerHTML='<div class="empty">'+esc(e.message)+"</div>"}
  const h=new URLSearchParams(location.search).get("u");
  if(h!==null)open_(+h||0);
  else{/* open on the conversation with the newest message, so the last message is the first thing you see */
    const all=[{id:0,...team}].concat(conv).filter(c=>c.at);
    const unread=all.filter(c=>c.n>0).sort((a,b)=>String(b.at).localeCompare(String(a.at)))[0];
    const pick=unread||all.sort((a,b)=>String(b.at).localeCompare(String(a.at)))[0];
    if(pick){try{await open_(pick.id);const m=$("#msgs");if(m)m.scrollTop=m.scrollHeight}catch(e){}}}
})();
</script>
</body>
</html>
