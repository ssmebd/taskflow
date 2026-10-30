<?php require 'config.php';require 'remember.php'; if(empty($_SESSION['uid'])){header('Location: login.php');exit;} ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"><link rel="icon" type="image/svg+xml" href="login.php?logo=1"><link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#0f2a5c"><link rel="icon" type="image/png" href="icon-192.png"><link rel="apple-touch-icon" href="icon-192.png"><meta name="mobile-web-app-capable" content="yes">
<title>TaskFlow</title>
<style>
:root{--bg:#f4f5f9;--card:#fff;--ink:#1d2433;--mut:#6b7385;--line:#e2e5ee;--pri:#4f46e5;--priInk:#fff;--col:#eceef5;--hi:#dc2626;--lo:#16a34a;color-scheme:light}
@media (prefers-color-scheme:dark){:root{--bg:#12141c;--card:#1d212d;--ink:#e8eaf2;--mut:#98a0b5;--line:#2c3142;--pri:#818cf8;--priInk:#12141c;--col:#171a24;--hi:#f87171;--lo:#4ade80;color-scheme:dark}}
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--ink);font:14px/1.45 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
header{display:flex;flex-wrap:wrap;gap:10px;align-items:center;padding:12px 16px;background:var(--card);border-bottom:1px solid var(--line)}
h1{font-size:18px;margin:0 8px 0 0;line-height:1.15}.app{display:block;font-size:11px;font-weight:400;color:var(--mut)}
input,select,textarea,button{font:inherit;color:var(--ink);background:var(--card);border:1px solid var(--line);border-radius:8px;padding:7px 10px}
button{cursor:pointer}button.p{background:var(--pri);color:var(--priInk);border-color:var(--pri);font-weight:600}
.tabs button{border-radius:0}.tabs button:first-child{border-radius:8px 0 0 8px}.tabs button:last-child{border-radius:0 8px 8px 0}
.tabs button.on{background:var(--pri);color:var(--priInk);border-color:var(--pri)}
.sp{flex:1}a.out{color:var(--mut);text-decoration:none;font-size:13px}
.stats{display:flex;gap:10px;padding:12px 16px;flex-wrap:wrap}
.stat{display:flex;align-items:center;gap:8px;background:color-mix(in srgb,var(--pc,#888) 20%,var(--card));border:1px solid color-mix(in srgb,var(--pc,#888) 45%,transparent);border-radius:7px;padding:5px 12px;min-width:0}.stat.more{background:transparent;border:1px dashed var(--line)}
th.ch{cursor:grab;user-select:none;white-space:nowrap}th.ch.over{box-shadow:inset 3px 0 0 var(--pri)}
tr.row[style] td{background:color-mix(in srgb,var(--sc) 14%,var(--card))}
tr.row[style] td:first-child{box-shadow:inset 5px 0 0 var(--sc)}
.dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px;vertical-align:middle}
.gtabs{display:flex;gap:6px;padding:8px 16px 0;overflow-x:auto}
.gt{padding:5px 14px;border-radius:8px 8px 0 0;border:1px solid var(--line);border-bottom:0;background:var(--col);cursor:pointer;white-space:nowrap;color:var(--ink)}
.gt.on{background:var(--card);font-weight:600;box-shadow:inset 0 3px 0 var(--pri)}.gt small{color:var(--mut)}.ro #gm{display:none}
.gh th,.dh th{background:var(--col);color:var(--ink);font-size:13px;cursor:pointer}
.gcw{position:relative;z-index:30;margin-left:-4px}.gcm{padding:9px 14px;border-radius:7px;border:1px dashed var(--line)}.gcm{font-size:12.5px;font-weight:600}.gcl{display:none;position:fixed;left:var(--gl,8px);top:var(--gt,0);right:auto;min-width:150px;max-width:calc(100vw - 16px);z-index:60;background:var(--card);border:1px solid var(--line);border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,.18);padding:4px}.gcw:hover .gcl,.gcw:focus-within .gcl{display:block}.gcl button{display:block;width:100%;text-align:left;border:0;background:none;padding:7px 10px;border-radius:5px}.gcl button:hover{background:color-mix(in srgb,var(--pri) 14%,var(--card))}.gcw:before{content:"";position:absolute;left:0;right:0;top:100%;height:6px}.stat{cursor:pointer}.stat.on{background:color-mix(in srgb,var(--pc,#888) 42%,var(--card));border-color:var(--pc,var(--pri));box-shadow:0 0 0 1px var(--pc,var(--pri))}.ro #sm,.ro #tlb{display:none}
.stat b{font-size:17px;color:color-mix(in srgb,var(--pc,#888) 55%,#000)}.stat span{color:color-mix(in srgb,var(--pc,#888) 45%,var(--ink));font-size:12.5px;font-weight:600}.stat.more span{color:var(--mut);font-weight:400}@media(prefers-color-scheme:dark){.stat b{color:color-mix(in srgb,var(--pc,#888) 55%,#fff)}}
.board{display:grid;grid-template-columns:repeat(3,minmax(220px,1fr));gap:12px;padding:0 16px 16px;overflow-x:auto}
.col{background:var(--col);border-radius:12px;padding:10px;min-height:200px}
.col.over{outline:2px dashed var(--pri)}
.col h3{margin:0 0 8px;font-size:13px;display:flex;justify-content:space-between}.col h3 em{font-style:normal;color:var(--mut)}
.done-wrap{padding:0 16px 16px}.col.done{min-height:90px;border:1px solid var(--lo)}
.donegrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:8px}.donegrid .card{margin:0;opacity:.8}
.card{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:10px;margin-bottom:8px;cursor:grab}
.card b{display:block;margin-bottom:4px}
.meta{display:flex;gap:6px;flex-wrap:wrap;align-items:center;color:var(--mut);font-size:12px}
.tag{border:1px solid var(--line);border-radius:999px;padding:1px 8px}
.late{color:var(--hi);font-weight:600}
.listwrap{padding:0 16px 16px;overflow-x:auto}
@media(min-width:701px){body.lst{display:flex;flex-direction:column;height:100dvh;overflow:hidden}body.lst>*{flex:none}body.lst>#main{flex:1 1 0;min-height:0;display:flex;flex-direction:column}body.lst .listwrap{flex:1 1 0;min-height:0;max-height:none}
.listwrap{overflow:auto;overscroll-behavior:contain;max-height:calc(100dvh - 200px)}
.listwrap thead th{position:sticky;top:0;z-index:7;background:var(--card);border-bottom:0;box-shadow:inset 0 -1px 0 var(--line)}
.listwrap #hrow th{border-right:0;box-shadow:inset 0 -1px 0 var(--line),inset -1px 0 0 color-mix(in srgb,var(--line) 70%,var(--mut))}.listwrap #hrow th:last-child{box-shadow:inset 0 -1px 0 var(--line)}}
table{table-layout:fixed;border-collapse:collapse;background:var(--card);border:1px solid var(--line)}
table td{overflow:hidden}table .ed{min-width:0}
th.ch{position:relative;overflow:hidden;text-overflow:ellipsis;padding-right:10px}
.rz{position:absolute;right:0;top:0;bottom:0;width:8px;cursor:col-resize;touch-action:none}.rz:hover,.rz.on{background:var(--pri);opacity:.5}
th,td{text-align:left;padding:3px 6px;border-bottom:1px solid var(--line)}th{color:var(--mut);font-size:12px;padding:9px 10px}
select.ed.pill{appearance:none;-webkit-appearance:none;width:auto;min-width:0;max-width:100%;padding:3px 10px;border-radius:5px;font-weight:600;font-size:12px;text-transform:uppercase;cursor:pointer;background:color-mix(in srgb,var(--pc) 24%,transparent);color:color-mix(in srgb,var(--pc) 55%,#000);border:1px solid color-mix(in srgb,var(--pc) 50%,transparent)}select.ed.pill:disabled{opacity:1;cursor:default}@media(prefers-color-scheme:dark){select.ed.pill{color:color-mix(in srgb,var(--pc) 55%,#fff)}}
.ed{width:100%;min-width:100px;border:1px solid transparent;background:transparent;padding:5px 6px;border-radius:6px}
.ed:hover{border-color:var(--line)}.ed:focus{border-color:var(--pri);background:var(--card);outline:none}
.dates{white-space:nowrap;color:var(--mut)}.dates .ed{width:calc(50% - 12px);min-width:0;display:inline-block}
input[type=checkbox]{width:16px;height:16px;padding:0;min-width:0}
#bar{position:sticky;top:calc(var(--hb,60px) + 6px);z-index:40;box-shadow:0 6px 18px rgba(20,30,70,.18);flex-wrap:wrap;display:flex;gap:8px;align-items:center;margin:0 16px 10px;padding:8px 12px;background:var(--card);border:1px solid var(--pri);border-radius:10px}#bar[hidden]{display:none}
dialog{border:1px solid var(--line);border-radius:14px;background:var(--card);color:var(--ink);width:min(460px,92vw);padding:18px}
dialog::backdrop{background:rgba(0,0,0,.45)}
#lgdlg .rctl select,#lgdlg .rctl input{width:auto;min-width:0}
dialog>.acts:last-child{position:sticky;bottom:0;z-index:5;background:var(--card);margin:10px 0 -18px;padding:10px 0 18px;border-top:1px solid var(--line)}
label{display:block;margin:8px 0 3px;color:var(--mut);font-size:12px}
dialog input,dialog select,dialog textarea{width:100%}dialog input[type=checkbox]{width:16px}
body{padding-left:112px;transition:padding-left .15s}
body.sc{padding-left:44px}
#side{position:fixed;top:0;left:0;bottom:0;width:112px;background:var(--card);border-right:1px solid var(--line);z-index:20;display:flex;flex-direction:column;overflow-y:auto;overflow-x:hidden;transition:width .15s}
body.sc #side{width:44px}
#side .sh{display:flex;align-items:center;justify-content:center;padding:10px;border-bottom:1px solid var(--line)}
#side .sh button{padding:4px 9px}
#wlinks{display:flex;flex-direction:column;gap:10px;align-items:center;padding:12px 0}
.wl{display:flex;align-items:center;justify-content:center;width:76px;height:76px;border-radius:14px;border:2px solid transparent;padding:4px;background:var(--col);overflow:hidden;text-decoration:none}
.wl:hover{border-color:var(--line)}.wl.on{border-color:var(--pri);box-shadow:0 0 0 3px color-mix(in srgb,var(--pri) 25%,transparent)}
.wl img,#vname img,#wlogo img{max-width:100%;max-height:100%;object-fit:contain;display:block}
.ini{width:100%;height:100%;display:flex;align-items:center;justify-content:center;border-radius:10px;color:#fff;font-weight:700;font-size:22px}
#side .sf{padding:8px;margin-top:auto}#side .sf button{width:100%;padding:6px 4px;font-size:12px}
#vname{display:none;width:32px;height:32px;margin:12px auto}#vname .ini{font-size:13px;border-radius:8px}
#wlogo{width:40px;height:40px;margin-right:4px;flex:none}#wlogo .ini{font-size:15px;border-radius:9px}
body.sc #wlinks{display:none}body.sc .sf{padding:4px}body.sc .sf .lbl{display:none}body.sc .sf button{padding:6px 0}body.sc #vname{display:block}
@media(max-width:700px){body{padding-left:44px}body:not(.sc) #side{box-shadow:8px 0 24px rgba(0,0,0,.3)}}
.wtabs{display:flex;gap:6px;padding:8px 16px;overflow-x:auto;background:var(--card);border-bottom:1px solid var(--line)}
.wt{padding:5px 14px;border-radius:999px;text-decoration:none;color:var(--ink);border:1px solid var(--line);white-space:nowrap}
.wt.on{background:var(--pri);color:var(--priInk);border-color:var(--pri);font-weight:600}
.wrow{border-bottom:1px solid var(--line);padding:6px 0}.wrow .acts{margin-top:0}
.wp label{display:flex;gap:8px;align-items:center;margin:4px 0;color:var(--ink);font-size:13px}
#setw{position:relative}
#smenu{position:absolute;right:0;top:calc(100% + 6px);min-width:170px;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);padding:6px;z-index:40;display:flex;flex-direction:column;gap:2px}
#smenu[hidden]{display:none}
#smenu button{text-align:left;border:0;background:transparent;padding:8px 10px;border-radius:6px;width:100%}
#smenu button:hover{background:var(--col)}
#smenu button[hidden]{display:none}#tmw[hidden]{display:none}#tmw .tms{display:none;position:fixed;flex-direction:column;gap:2px;min-width:170px;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);padding:6px;z-index:60}#tmw:hover .tms,#tmw:focus-within .tms{display:flex}
.ro #smenu>button:not(#pm){display:none}
.tc{display:flex;align-items:center;gap:2px}.tc .ed{flex:1;min-width:0}
.ovb{color:#fff;background:#dc2626;border-radius:6px;font-size:11px;line-height:1;padding:3px 5px;cursor:help;flex:none}
.gtools{display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:0 16px 10px;color:var(--mut);font-size:13px}
.gtools label{display:flex;gap:6px;align-items:center;margin:0;color:var(--ink);font-size:13px}
.gwrap{margin:0 16px 16px;overflow:auto;max-height:calc(100vh - 290px);min-height:240px;background:var(--card);border:1px solid var(--line);border-radius:10px;position:relative}
.ginner{position:relative}
.ghead{display:flex;position:sticky;top:0;z-index:4;background:var(--card);border-bottom:1px solid var(--line);height:46px}
.gname{flex:none;width:var(--gl,170px);position:sticky;left:0;z-index:3;background:var(--card);border-right:1px solid var(--line);padding:8px 10px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.gname.tk{padding:4px 10px;font-size:12.5px;line-height:1.25}
.gname small{display:block;font-weight:400;color:var(--mut)}
.ghead .gname{z-index:5;display:flex;align-items:center}
.gtrack{position:relative;flex:none}
.glane{display:flex;border-bottom:1px solid var(--line)}
.gm{position:absolute;top:0;height:23px;border-left:1px solid var(--line);padding:3px 6px;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden}
.gd{position:absolute;top:23px;height:23px;font-size:11px;color:var(--mut);text-align:center;border-left:1px solid var(--line);line-height:23px;overflow:hidden}
.gbar{position:absolute;height:26px;border-radius:6px;color:#000;font-size:12px;font-weight:500;line-height:26px;padding:0 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer;text-shadow:0 1px 0 rgba(255,255,255,.35);box-shadow:0 1px 2px rgba(0,0,0,.25)}
.gbar:hover{filter:brightness(1.1)}.gbar.dn{opacity:.55}
.gtoday{position:absolute;top:0;bottom:0;width:2px;background:#dc2626;z-index:2;pointer-events:none}
.cc{float:right;margin:0 0 4px 8px}.ro .cc{display:none}
.ro #add,.ro #cm,.ro .rc,.ro #all{display:none}.ro .card{cursor:default}
.acts{display:flex;gap:8px;margin-top:10px;align-items:center}.empty{color:var(--mut);text-align:center;padding:24px}
.cmb{background:#e5e7eb;color:#374151;border-radius:6px;font-size:11px;line-height:1;padding:3px 5px;cursor:pointer;flex:none}.cmb.hasmsg{background:transparent;padding:0 2px;display:inline-flex;align-items:center;line-height:0}.cmb.hasmsg:hover{transform:scale(1.1)}.cmb.nomsg{background:transparent;padding:0 2px;display:inline-flex;align-items:center;line-height:0;opacity:.9}.cmb.nomsg:hover{opacity:1;transform:scale(1.12)}.cmb.un{background:#f59e0b;color:#fff}.cmb.me{background:#dc2626;color:#fff}
#thr{margin-top:12px;border-top:1px solid var(--line);padding-top:10px}#thl{max-height:220px;overflow:auto;display:flex;flex-direction:column;gap:6px;margin-bottom:8px}
.msg{border:1px solid var(--line);border-radius:10px;padding:6px 9px;font-size:13px;white-space:pre-wrap;word-break:break-word}.msg.ins{border-color:#f59e0b;background:rgba(245,158,11,.1)}.msg.new{box-shadow:0 0 0 2px #3b82f6}.msg small{display:block;color:var(--mut);font-size:11px}.msg .x{float:right;cursor:pointer;color:var(--mut);border:0;background:none;width:auto;padding:0}
#bell{position:relative}#bell b{background:#dc2626;color:#fff;border-radius:9px;font-size:11px;padding:1px 6px;margin-left:3px}
#idlg{width:min(430px,94vw);padding:0;overflow:hidden;display:none;max-height:86vh;border-radius:20px}#idlg[open]{display:flex;flex-direction:column}
#idlg .fh{display:flex;align-items:center;gap:12px;padding:16px 16px 6px;cursor:move}
#idlg .fo{width:42px;height:42px;border-radius:50%;background:conic-gradient(from 200deg,#6366f1,#06b6d4,#a855f7,#6366f1);display:grid;place-items:center;box-shadow:0 4px 14px rgba(99,102,241,.45);animation:fspin 9s linear infinite;flex:none}
#idlg .fo i{width:34px;height:34px;border-radius:50%;background:var(--card);display:block;position:relative}#idlg .fo i:after{content:"✦";position:absolute;inset:0;display:grid;place-items:center;font-style:normal;font-size:17px;color:#6366f1}
@keyframes fspin{to{transform:rotate(360deg)}}#idlg .fo i{animation:fspin 9s linear infinite reverse}
#idlg .ft{flex:1;display:flex;flex-direction:column;line-height:1.2}#idlg .ft b{font-size:17px}#idlg .ft small{color:var(--mut);font-size:12px}
#idlg .fh button{border:0;background:transparent;font-size:16px;color:var(--mut);cursor:pointer}
#idlg .fg{padding:2px 18px 10px;font-size:14px;color:var(--ink)}
#idlg .ftabs{display:flex;gap:6px;padding:0 14px 8px;border-bottom:1px solid var(--line)}
#idlg .ftabs button{flex:1;border:0;border-radius:999px;padding:7px 8px;background:transparent;color:var(--mut);font-weight:600;cursor:pointer}
#idlg .ftabs button.on{background:rgba(99,102,241,.14);color:#6366f1}#idlg .ftabs b{background:#ef4444;color:#fff;border-radius:99px;font-size:11px;padding:1px 6px;margin-left:4px}
#idlg #ilist{overflow:auto;padding:6px 12px;flex:1;min-height:120px}
#idlg .fd{font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);margin:12px 4px 4px}
#idlg .fc{position:relative;display:flex;gap:11px;padding:11px 12px;margin:7px 0;border:1px solid var(--line);border-radius:14px;background:var(--card);animation:fin .35s both}
#idlg .fc.un{background:linear-gradient(135deg,rgba(99,102,241,.09),rgba(6,182,212,.07));border-color:rgba(99,102,241,.35)}#idlg .fc.un:before{content:"";position:absolute;top:12px;right:12px;width:8px;height:8px;border-radius:50%;background:#6366f1}
@keyframes fin{from{opacity:0;transform:translateY(8px)}}
#idlg .fi{width:38px;height:38px;border-radius:12px;display:grid;place-items:center;font-size:19px;flex:none}
#idlg .fb{flex:1;min-width:0;font-size:13px}#idlg .fb b{font-size:14px;display:block;padding-right:14px}#idlg .fb p{margin:3px 0 0;color:var(--mut);white-space:pre-wrap;overflow-wrap:anywhere}
#idlg .fb small{color:var(--mut);font-size:11px}
#idlg .fa{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap}#idlg .fa button{border:0;border-radius:999px;padding:5px 12px;font-size:12px;font-weight:600;cursor:pointer;background:#6366f1;color:#fff}#idlg .fa button.g{background:rgba(127,127,127,.15);color:var(--ink)}
#idlg .fe{text-align:center;color:var(--mut);padding:34px 10px}#idlg .fe span{font-size:38px;display:block;margin-bottom:6px}
#idlg>.acts:last-child{margin:0;padding:10px 14px;justify-content:space-between}
@media(max-width:600px){#idlg{width:100vw;max-width:100vw;max-height:92vh;margin:auto 0 0;border-radius:20px 20px 0 0}}
.ib{border:1px solid var(--line);border-radius:10px;padding:7px 10px;margin:6px 0;cursor:pointer;font-size:13px}.ib:hover{background:rgba(127,127,127,.1)}
header{--nv:#0f2a5c;--hfg:#ffffff;background:var(--nv);border-bottom:1px solid color-mix(in srgb,var(--nv) 80%,#000);color:var(--hfg)}
header h1,header .app,header #who,header a.out,header #live{color:var(--hfg)!important}header .app{opacity:.75}
header input,header select,header button{background:color-mix(in srgb,var(--hfg) 12%,transparent);color:var(--hfg);border-color:color-mix(in srgb,var(--hfg) 30%,transparent)}
header input::placeholder{color:color-mix(in srgb,var(--hfg) 70%,transparent)}header option{color:#1d2433;background:#fff}
header button:hover:not(:disabled){background:color-mix(in srgb,var(--hfg) 22%,transparent)}header button:disabled{opacity:.7}
header .tabs button.on{background:var(--hfg);color:var(--nv);border-color:var(--hfg);font-weight:600}
header button.p{background:var(--hfg);color:var(--nv);border-color:var(--hfg)}header button.p:hover{opacity:.88}
header #bell b{background:#ef4444;color:#fff}
header #smenu button{background:var(--card);color:var(--ink);border-color:transparent;text-align:left}header #smenu button:hover{background:var(--col)}
body.hdark #wlogo .wh,body.hdark #wlogo img[src^="data:"]{filter:brightness(0)}
.sw{display:inline-block;width:46px;height:30px;border-radius:8px;border:2px solid var(--line);cursor:pointer;padding:0;font-size:11px;font-weight:700}.sw:hover{border-color:var(--pri)}
#pprev{border-radius:10px;padding:10px 14px;margin:10px 0;font-weight:600}
#side{--sb:color-mix(in srgb,var(--nv,#0f2a5c) 86%,#000);--sf:var(--hfg,#fff);background:var(--sb);border-right-color:color-mix(in srgb,var(--sb) 80%,#000)}#side .sh{border-bottom-color:color-mix(in srgb,var(--sf) 18%,transparent)}
#side button{background:color-mix(in srgb,var(--sf) 12%,transparent);color:var(--sf);border-color:color-mix(in srgb,var(--sf) 30%,transparent)}#side button:hover{background:color-mix(in srgb,var(--sf) 24%,transparent)}
#side .wl{background:color-mix(in srgb,var(--sf) 10%,transparent)}#side .wl:hover{border-color:color-mix(in srgb,var(--sf) 50%,transparent)}#side .wl.on{border-color:transparent;box-shadow:none;background:color-mix(in srgb,var(--sf) 24%,transparent);position:relative}
body.hdark #side .wl .wh,body.hdark #side .wl img[src^="data:"]{filter:brightness(0)}
.gbar{touch-action:none;user-select:none}.ro .gbar{touch-action:auto}.glane.gdrop{background:rgba(79,70,229,.12)}
#gtip{position:fixed;z-index:60;background:#0f2a5c;color:#fff;font-size:12px;padding:3px 8px;border-radius:6px;pointer-events:none;white-space:nowrap}
.wh{filter:brightness(0) invert(1)}
.uh,.ur2{display:grid;grid-template-columns:1.1fr .9fr 1.3fr .9fr 230px;gap:6px;align-items:center}.uh{font-size:11px;color:var(--mut);font-weight:600;margin:10px 0 4px;text-transform:uppercase}
.ur2{margin:4px 0}.ur2 input,.ur2 select{min-width:0}.pw{display:flex;gap:4px}.pw input{flex:1}.eye{padding:4px 8px;width:auto;flex:none}
.urow{display:grid;grid-template-columns:1.1fr .9fr 1.3fr .9fr 230px;gap:6px;align-items:center;border-bottom:1px solid var(--line);padding:6px 0}.urow>span{overflow:hidden;text-overflow:ellipsis}.urow button[data-pw]{white-space:nowrap}
@media(min-width:701px){body:not(.hs) header,body:not(.hs) html.glass header{padding:18px 20px;gap:11px}
body:not(.hs) header h1{font-size:27px;margin-right:12px}body:not(.hs) #wlogo{height:51px;max-width:135px;margin-right:9px}body:not(.hs) #wlogo img{max-width:135px}body:not(.hs) #wlogo .ini{font-size:22px}
body:not(.hs) header .brand{padding-right:18px;margin-right:6px}body:not(.hs) header .brand b{font-size:34px}body:not(.hs) header .brand small{font-size:13px;margin-top:4px}
body:not(.hs) header input,body:not(.hs) header select,body:not(.hs) header button{padding:9px 11px;font-size:14px}body:not(.hs) header #bell b{font-size:13px;padding:1px 7px}}
#side .rpill{display:none}#side .rb.dskonly svg{transition:transform .15s}
@media(min-width:701px){#side{transition:transform .45s cubic-bezier(.22,.8,.25,1),background .45s ease,box-shadow .45s ease,border-color .45s ease}
#side .rlogo,#side .sf,#side .mobonly{transition:opacity .4s ease,transform .45s cubic-bezier(.22,.8,.25,1)}
body.rh,body.rh.sc{padding-left:44px}body.rh header{margin-left:-44px;padding-left:64px!important}body.rh.hs header{padding-left:54px!important}body.rh #side{z-index:47}
body.rh:not(.ro) #side{background:transparent!important;border-right-color:transparent;box-shadow:none;pointer-events:none}
body.rh:not(.ro) #side::before{display:none!important}
body.rh:not(.ro) #side .rlogo,body.rh:not(.ro) #side .sf{opacity:0;pointer-events:none;transform:translateX(-70px)}
body.rh:not(.ro) #side .rb#rhome,body.rh:not(.ro) #side .rb#rco{pointer-events:auto}
body.rh:not(.ro) #side .rpill{display:block;position:fixed;left:0;top:0;bottom:0;width:6px;background:var(--sb,#0f2a5c);box-shadow:2px 0 8px rgba(0,0,0,.2);pointer-events:auto;z-index:47}
body.rh #side .rb#rhome,body.rh #side .rb#rco{--pc:var(--nv,#0f2a5c);position:fixed;left:0;top:var(--tt,104px);width:46px;height:42px;border-radius:0 12px 12px 0;border:1px solid color-mix(in srgb,var(--pc) 55%,transparent);border-left:0;background:color-mix(in srgb,var(--pc) 26%,var(--card));color:color-mix(in srgb,var(--pc) 70%,var(--ink));box-shadow:2px 2px 8px rgba(0,0,0,.18);z-index:48}
body.rh #side .rb#rco{top:calc(var(--tt,104px) + 46px)}
body.rh.ro #side .rb#rhome,body.rh.ro #side .rb#rco{background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.3);color:#fff;box-shadow:none}
body.rh:not(.ro) #side .rb#rhome:hover,body.rh:not(.ro) #side .rb#rco:hover{background:color-mix(in srgb,var(--pc) 38%,var(--card))}

body.rh.ro #side{box-shadow:10px 0 28px rgba(0,0,0,.3)}
body.rh #side .rb.dskonly svg{transform:rotate(180deg)}}
@media(max-width:700px){#side .rb.dskonly{display:none}}
@media(max-width:700px){.uh{display:none}.urow,.ur2{grid-template-columns:1fr 1fr}}
.drng{width:100%;text-align:center;background:transparent;border:1px solid transparent;padding:6px 8px;white-space:nowrap}.drng:hover{border-color:var(--line);background:var(--card)}.drng.empty{color:var(--mut);border:1px dashed var(--line)}.drng.late{color:var(--hi)}.ro .drng{pointer-events:none}
#rpop{position:fixed;z-index:70;background:var(--card);color:var(--ink);border:1px solid var(--line);border-radius:12px;box-shadow:0 10px 36px rgba(0,0,0,.28);padding:14px 16px}
#rpop .rm{display:flex;gap:26px}#rpop .rh{display:flex;align-items:center;justify-content:space-between;font-weight:600;margin-bottom:8px;min-height:26px}#rpop .rh button{border:0;background:transparent;padding:2px 10px;font-size:18px;line-height:1}
#rpop .rg{display:grid;grid-template-columns:repeat(7,34px);row-gap:2px}#rpop .rw{font-size:11px;color:var(--mut);text-align:center;padding:4px 0}
#rpop .rd{height:34px;border:0;border-radius:8px;background:transparent;padding:0;font-size:13px;cursor:pointer}#rpop .rd:hover{background:var(--col)}
#rpop .rd.in{background:color-mix(in srgb,var(--pri) 16%,transparent);border-radius:0}#rpop .rd.on{background:var(--pri);color:var(--priInk);border-radius:8px}#rpop .rd.td{box-shadow:inset 0 0 0 1.5px var(--pri)}
#rpop .rf{display:flex;gap:8px;align-items:center;margin-top:10px;font-size:12px;color:var(--mut)}#rpop .rf .sp{flex:1}
.hd{display:inline-block;cursor:grab;color:var(--mut);padding:2px 5px 2px 0;touch-action:none;user-select:none;font-size:13px;letter-spacing:-2px}.hd:hover{color:var(--ink)}
tr.rdrag{opacity:.4}.ghd{display:inline-block;cursor:grab;touch-action:none;user-select:none;letter-spacing:-2px;margin-right:6px;opacity:.55;font-size:13px}.gh.gdr{opacity:.45}.gh.gd-b th{box-shadow:inset 0 3px 0 var(--pri)}.gh.gd-a th{box-shadow:inset 0 -3px 0 var(--pri)}tr.rd-b td{box-shadow:inset 0 3px 0 var(--pri)}tr.rd-a td{box-shadow:inset 0 -3px 0 var(--pri)}.gh.rd-g td,.gh.rd-g{outline:2px solid var(--pri);outline-offset:-2px}body.rdg{cursor:grabbing;user-select:none}
/* ---- Chat look (task messages + team Messages) ---- */
:root{--gr1:#10b981;--gr2:#0b8f6a;--grbg:#e7f7f0}
@media (prefers-color-scheme:dark){:root{--grbg:#12332a}}
.av{flex:none;width:30px;height:30px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;background:hsl(var(--h,160) 55% 42%);letter-spacing:.3px;box-shadow:0 1px 3px rgba(0,0,0,.18)}.av.sm{width:26px;height:26px;font-size:10px}.av.tm{background:linear-gradient(135deg,#6366f1,#8b5cf6)}
#mdlg{width:min(600px,94vw);padding:0;overflow:hidden;border:0;border-radius:18px;box-shadow:0 24px 70px rgba(10,20,40,.38);resize:none}
#mdlg>.mhd{display:flex;align-items:center;gap:12px;margin:0;padding:14px 16px;background:linear-gradient(135deg,var(--gr1),var(--gr2));color:#fff;position:relative;overflow:hidden}
#mdlg>.mhd:after{content:"";position:absolute;right:-30px;top:-40px;width:130px;height:130px;border-radius:50%;background:rgba(255,255,255,.1)}
#mdlg .mic{flex:none;width:40px;height:40px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
#mdlg .mti{flex:1;min-width:0;position:relative;z-index:1}#mdlg #mord{color:rgba(255,255,255,.85);font-size:11px;letter-spacing:.6px;text-transform:uppercase}#mdlg #mtit{font-weight:700;font-size:16px;line-height:1.25;white-space:nowrap}
#mdlg .mhd button{position:relative;z-index:1;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.35);color:#fff;border-radius:999px;padding:6px 12px;font-size:12.5px;font-weight:600;cursor:pointer}#mdlg .mhd button:hover{background:rgba(255,255,255,.3)}#mdlg #mcl{width:34px;height:34px;padding:0;font-size:15px;line-height:1}
#mdlg #mbody{padding:0}#mdlg #thr{margin:0;border:0;padding:0;display:flex;flex-direction:column}#mdlg #thr .thh{display:none}
#thr .thh{margin-bottom:8px}
#thl{max-height:46vh;min-height:160px;padding:14px 14px 8px;gap:3px;background:linear-gradient(180deg,color-mix(in srgb,var(--col) 70%,transparent),transparent);scroll-behavior:smooth}
#dlg #thl{max-height:260px;min-height:90px;border-radius:12px;border:1px solid var(--line)}
.dsep{align-self:center;margin:10px 0 6px;font-size:11px;font-weight:600;color:var(--mut);background:var(--card);border:1px solid var(--line);border-radius:999px;padding:2px 12px}
.mrow{display:flex;gap:8px;align-items:flex-end;margin-top:8px;max-width:100%}.mrow.gp{margin-top:2px}.mrow.me{flex-direction:row-reverse}.mrow .av{margin-bottom:2px}.mrow.gp .av{visibility:hidden}
.mrow .bub{position:relative;max-width:min(78%,420px);padding:8px 12px 6px;border-radius:16px 16px 16px 4px;background:var(--card);border:1px solid var(--line);box-shadow:0 1px 2px rgba(0,0,0,.06);font-size:13.5px;line-height:1.4;white-space:pre-wrap;word-break:break-word}
.mrow.me .bub{background:linear-gradient(135deg,var(--gr1),var(--gr2));color:#fff;border:0;border-radius:16px 16px 4px 16px;box-shadow:0 2px 8px rgba(11,143,106,.28)}
.mrow.gp .bub{border-top-left-radius:6px}.mrow.me.gp .bub{border-top-left-radius:16px;border-top-right-radius:6px}
.bub .who{display:block;font-size:11.5px;font-weight:700;color:var(--gr2);margin-bottom:2px}.mrow.me .who{display:none}.bub .who i{font-style:normal;font-weight:500;color:var(--mut)}
.bub .tm{display:block;text-align:right;font-size:10px;opacity:.65;margin-top:2px}
.bub .x{position:absolute;top:-8px;right:-6px;width:20px;height:20px;border-radius:50%;border:1px solid var(--line);background:var(--card);color:var(--mut);font-size:10px;line-height:1;padding:0;display:none;cursor:pointer;box-shadow:0 1px 4px rgba(0,0,0,.2)}.mrow:hover .bub .x{display:block}.bub .x:hover{color:#dc2626}
.mrow.me .bub .x{right:auto;left:-8px}
.mrow.ins .bub{background:linear-gradient(135deg,#fff7e0,#ffecb8);color:#5b3b00;border:1px solid #f59e0b;box-shadow:0 2px 8px rgba(245,158,11,.25)}.mrow.ins .who{color:#b45309;display:block}.mrow.ins .bub:before{content:"📌 Instruction";display:block;font-size:10.5px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;color:#b45309;margin-bottom:3px}
.mrow.new .bub{box-shadow:0 0 0 2px #3b82f6,0 2px 8px rgba(59,130,246,.3)}.mrow.new .bub:after{content:"NEW";position:absolute;top:-8px;left:10px;background:#3b82f6;color:#fff;font-size:9px;font-weight:800;border-radius:6px;padding:1px 6px;letter-spacing:.5px}
.mempty{align-self:center;text-align:center;color:var(--mut);padding:26px 10px}.mempty svg{display:block;margin:0 auto 8px;opacity:.5}
#thr .cmp{padding:10px 12px 12px;border-top:1px solid var(--line);background:var(--card)}#dlg #thr .cmp{padding:10px 0 0;border:0}
#thr .cmo{display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap}#thr .cmo select{width:auto;flex:1 1 140px;border-radius:999px;padding:5px 12px;font-size:12.5px;background:var(--col);border-color:transparent}
#thr .cmr{display:flex;gap:8px;align-items:flex-end}#thr .cmr textarea{flex:1;resize:none;border-radius:20px;padding:10px 14px;min-height:42px;max-height:130px;line-height:1.35;background:var(--col);border:1px solid transparent;box-sizing:border-box}#thr .cmr textarea:focus{outline:0;border-color:var(--gr1);background:var(--card);box-shadow:0 0 0 3px color-mix(in srgb,var(--gr1) 25%,transparent)}
.snd{flex:none;width:42px;height:42px;border-radius:50%;padding:0!important;border:0!important;background:linear-gradient(135deg,var(--gr1),var(--gr2))!important;color:#fff!important;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 3px 10px rgba(11,143,106,.4);transition:transform .12s}.snd:hover{transform:scale(1.08)}.snd:active{transform:scale(.94)}.snd svg{width:20px;height:20px;margin-left:2px}
#thr .hint{font-size:10.5px;color:var(--mut);margin:5px 4px 0}
/* team Messages panel */
aside#msgw{z-index:50;border:0;border-radius:18px;box-shadow:0 24px 70px rgba(10,20,40,.4)}
#msgw .mgh{background:linear-gradient(135deg,var(--gr1),var(--gr2));color:#fff;border:0;padding:12px 14px}#msgw .mgh b{font-size:15px}#msgw .mgh button{background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.4);color:#fff;border-radius:50%;width:30px;height:30px;padding:0;min-height:0}
#msgw #mgu{background:var(--col);width:190px}#msgw .mgu{border:0;margin:4px 6px;border-radius:12px;padding:8px 10px}#msgw .mgu.on{background:var(--card);box-shadow:0 1px 6px rgba(0,0,0,.12);font-weight:600}#msgw .mgu:hover{background:color-mix(in srgb,var(--card) 70%,var(--col))}#msgw .mgu .av{width:28px;height:28px;font-size:10px}#msgw .mgu b{background:linear-gradient(135deg,#ef4444,#dc2626);box-shadow:0 1px 4px rgba(220,38,38,.5)}
#msgw .mgtt{font-size:14px;background:var(--card)}
#msgw #mgm{padding:14px 14px 8px;gap:2px;background:linear-gradient(180deg,color-mix(in srgb,var(--col) 70%,transparent),transparent)}
#msgw .mgmsg{max-width:78%;padding:8px 12px 6px;border-radius:16px 16px 16px 4px;background:var(--card);border:1px solid var(--line);box-shadow:0 1px 2px rgba(0,0,0,.06);margin-top:6px}#msgw .mgmsg.gp{margin-top:1px}
#msgw .mgmsg.me{border:0;color:#fff;background:linear-gradient(135deg,var(--gr1),var(--gr2));border-radius:16px 16px 4px 16px;box-shadow:0 2px 8px rgba(11,143,106,.28)}#msgw .mgmsg b{color:var(--gr2);font-size:11.5px}#msgw .mgmsg small{text-align:right;opacity:.7;font-size:10px}#msgw .mgmsg.me small{color:#fff}
#msgw .mgin{align-items:flex-end;padding:10px 12px;gap:8px;background:var(--card)}#msgw .mgin textarea{border-radius:20px;padding:10px 14px;background:var(--col);border:1px solid transparent;min-height:42px;max-height:120px;line-height:1.35}#msgw .mgin textarea:focus{outline:0;border-color:var(--gr1);background:var(--card);box-shadow:0 0 0 3px color-mix(in srgb,var(--gr1) 25%,transparent)}
#msgw #mgem{border:0;background:var(--col);border-radius:50%;width:42px;height:42px;padding:0;font-size:19px;flex:none}#msgw #mgs.snd{font-size:0}
@media(max-width:700px){#mdlg{border-radius:0}#mdlg>.mhd{padding-top:max(14px,env(safe-area-inset-top))}#mdlg #thl{max-height:none;flex:1}#mdlg #thr{height:calc(100dvh - 70px)}#mdlg #mbody{display:flex;flex-direction:column}#msgw #mgu{width:74px}#msgw .mgu span,#msgw .mgu b{font-size:11px}#msgw .mgu .av{display:none}#msgw .mgu{padding:8px 6px}.mrow .bub{max-width:84%}}
.brand{display:flex;flex-direction:column;line-height:1.05;margin-right:6px;padding-right:14px;border-right:1px solid color-mix(in srgb,var(--hfg) 28%,transparent)}.brand b{font-size:23px;letter-spacing:.2px}.brand small{font-size:9px;opacity:.75;letter-spacing:.6px;margin-top:3px;text-transform:uppercase}
header .brand{color:var(--hfg)}
#hrow th{border-right:1px solid color-mix(in srgb,var(--line) 70%,var(--mut))}#hrow th:last-child{border-right:0}
#smenu{position:fixed;right:auto;top:auto;max-height:calc(100vh - 16px);overflow:auto}
#side .sf{display:flex;flex-direction:column;gap:6px}#side .sf #setw{display:block}
#side #smenu button{background:transparent;color:var(--ink);border:0;text-align:left;padding:8px 10px}#side #smenu button:hover{background:var(--col)}
#uw{position:relative}#umenu{position:fixed;min-width:150px;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);padding:6px;z-index:60}#umenu[hidden]{display:none}
#umenu a{display:block;padding:8px 10px;border-radius:6px;color:var(--ink);text-decoration:none;font-size:14px}#umenu a:hover{background:var(--col)}#rpw[hidden]{display:none}#rpl{display:none;position:fixed;flex-direction:column;gap:2px;min-width:180px;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);padding:6px;z-index:61}#rpw:hover #rpl,#rpw.open #rpl{display:flex}
tr.gh th{background:color-mix(in srgb,var(--gc,#888) 22%,var(--card));border-left:8px solid var(--gc,#888);border-top:2px solid var(--gc,#888)}tr.dh.sub th{padding-left:30px;border-left:5px solid var(--gc,#888);background:color-mix(in srgb,var(--gc,#888) 8%,var(--card))}tr.gh.sub th{border-left-width:5px;background:color-mix(in srgb,var(--gc,#888) 12%,var(--card))}tr.gsp td{height:14px;padding:0;border:0;background:transparent}tr.gsp:first-child{display:none}tr.gend td{height:0;padding:0;border:0;border-bottom:3px solid var(--gc,#888)}.gcs{display:flex;gap:6px;flex-wrap:wrap;margin:6px 0}.gcs button{width:26px;height:26px;border-radius:50%;padding:0;border:2px solid transparent}.gcs button.on{border-color:var(--ink);box-shadow:0 0 0 2px var(--card) inset}.gcs button.auto{width:auto;border-radius:14px;padding:0 10px;font-size:12px}.gh.sub th{padding-left:28px}.addrow td{padding:7px 10px;border-bottom:1px solid var(--line);background:var(--card)}.addrow a{color:var(--mut);cursor:pointer;margin-right:18px;font-size:13px;text-decoration:none}.addrow a:hover{color:var(--ink)}
.hbtn{width:100%;background:transparent;border:1px solid transparent;padding:5px 6px;white-space:nowrap;color:var(--ink)}.hbtn:hover{border-color:var(--line);background:var(--card)}
#hrdg{width:min(1100px,98vw);max-height:94vh}#hrdg .rctl,#hrdg .hrf{display:flex;flex-wrap:wrap;gap:8px;align-items:center}#hrdg input:not([type=checkbox]),#hrdg select,#hrdg button{width:auto;margin:0}#hrdg input[type=checkbox]{width:auto}#hrdg #hrq{flex:1;min-width:200px}.hrf{display:flex;flex-wrap:wrap;gap:8px;align-items:flex-start;margin:6px 0}.hrf details{position:relative}.hrf summary{cursor:pointer;border:1px solid var(--line);border-radius:8px;padding:6px 10px;font-size:13px;background:var(--card,#fff);list-style:none;white-space:nowrap}.hrf summary::-webkit-details-marker{display:none}.hrf details[open] summary{border-color:var(--pri)}.hrf .hrp{position:absolute;z-index:30;top:100%;left:0;margin-top:4px;min-width:230px;max-height:300px;overflow:auto;background:var(--card,#fff);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);padding:6px}.hrp label{display:flex;gap:7px;align-items:center;padding:3px 6px;font-size:13px;cursor:pointer;border-radius:6px}.hrp label:hover{background:rgba(0,0,0,.05)}.hrp .hrg{font-size:11px;color:var(--mut);text-transform:uppercase;padding:6px 6px 2px}.hrk{display:flex;flex-wrap:wrap;gap:10px;margin:8px 0}.hrk div{border:1px solid var(--line);border-radius:10px;padding:6px 14px;font-size:12px;color:var(--mut)}.hrk b{display:block;font-size:20px;color:var(--fg,#111)}#hrout{overflow:auto;max-height:56vh}#hrout table{width:100%;border-collapse:collapse;font-size:13px}#hrout th{position:sticky;top:0;background:var(--card,#fff);font-size:11px;color:var(--mut);text-transform:uppercase;text-align:left;padding:6px;border-bottom:2px solid var(--line)}#hrout td{padding:5px 6px;border-bottom:1px solid var(--line)}#hrout td.n,#hrout th.n{text-align:right;font-variant-numeric:tabular-nums}#hrout tr.l1 td{background:rgba(37,99,235,.07);font-weight:700}#hrout tr.l2 td:first-child{padding-left:26px}#hrout tr.ld td{color:var(--mut);font-size:12px}#hrout tr.ld td:first-child{padding-left:46px}#hrout tr.tt td{font-weight:800;border-top:2px solid var(--line)}
/* Guest home + test window */
.gho{display:grid;gap:16px;width:100%;max-width:1180px;margin:0 auto;padding:14px;box-sizing:border-box}
.gh-hero{background:linear-gradient(135deg,#0f2a5c,#123577);color:#fff;border-radius:18px;padding:20px 22px;display:grid;gap:6px}
.gh-hero h2{margin:0;font-size:22px;letter-spacing:-.02em}.gh-hero p{margin:0;color:#b6c5e6;font-size:13px}
.gh-cos{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.gh-cos a{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);color:#fff;border-radius:999px;padding:5px 12px;text-decoration:none;font-weight:700;font-size:12.5px}.gh-cos a:hover{background:rgba(255,255,255,.22)}
.gh-k{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.gh-k div{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px 16px;border-top:4px solid var(--c)}.gh-k b{display:block;font-size:28px;font-weight:900;line-height:1;color:var(--c)}.gh-k span{font-size:12px;color:var(--mut);font-weight:700}
.gho h3{margin:0 0 8px;font-size:14px;display:flex;align-items:center;gap:8px}.gho h3 em{font-style:normal;font-size:11px;background:var(--col);border-radius:999px;padding:1px 8px;color:var(--mut)}
.gh-pp{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:10px}
.gh-p{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:12px 14px;display:grid;gap:8px}
.gh-ph{display:flex;align-items:center;gap:10px}.gh-ph b{font-size:14px}.gh-ph small{color:var(--mut);display:block;font-size:11.5px}
.gh-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#0f2a5c,#1d5fd1);color:#fff;display:grid;place-items:center;font-weight:800;font-size:12px;flex:none}
.gh-t{display:grid;gap:1px;padding:7px 9px;border-radius:9px;background:var(--col);font-size:12.5px}.gh-t b{font-weight:700}.gh-t small{color:var(--mut);font-size:11px}
.gh-l{background:var(--card);border:1px solid var(--line);border-radius:14px;overflow:hidden}
.gh-r{display:grid;grid-template-columns:1fr auto;gap:2px 12px;padding:9px 14px;border-top:1px solid var(--line);font-size:13px}.gh-r:first-child{border-top:0}
.gh-r b{font-weight:700}.gh-r small{color:var(--mut);font-size:11.5px;grid-column:1}.gh-r .d{grid-row:1/3;grid-column:2;text-align:right;font-size:12px;color:var(--mut);align-self:center;white-space:nowrap}
.gh-e{padding:14px;color:var(--mut);font-size:13px}
.gh-tw{background:var(--card);border:1px solid var(--line);border-radius:14px;overflow-x:auto}
.gh-tb{width:100%;border-collapse:collapse;font-size:13px}.gh-tb th{text-align:left;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--mut);padding:10px 12px;background:var(--col);border-bottom:1px solid var(--line);white-space:nowrap}
.gh-tb td{padding:9px 12px;border-top:1px solid var(--line);vertical-align:middle}.gh-tb tbody tr:first-child td{border-top:0}
.gh-pn{background:color-mix(in srgb,var(--col) 60%,var(--card));border-right:1px solid var(--line);min-width:170px;vertical-align:top!important}.gh-pn .gh-av{display:inline-grid;margin-right:8px;vertical-align:middle}.gh-pn b{vertical-align:middle}.gh-pn small{display:block;color:var(--mut);font-size:11px;margin:2px 0 0 42px}
.gh-due{display:inline-flex;flex-direction:column;align-items:flex-start;font-size:14px;font-weight:900;padding:3px 10px;border-radius:9px;border:1px solid;white-space:nowrap}.gh-due small{font-size:9px;letter-spacing:.08em;text-transform:uppercase;font-weight:800}
.gh-due.du{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}.gh-due.od{color:#b91c1c;background:#fef2f2;border-color:#fecaca}
.gh-r .d,.hrow .d{display:flex;flex-direction:column;align-items:flex-end;font-size:14px!important;font-weight:900;padding:3px 10px;border-radius:9px;border:1px solid;color:var(--ink)}.gh-r .d small,.hrow .d small{font-size:9px;letter-spacing:.08em;font-weight:800;color:inherit;grid-column:auto}
.gh-r .d.du,.hrow .d.du{color:#1d4ed8;background:#eff6ff;border-color:#bfdbfe}.gh-r .d.od,.hrow .d.od{color:#b91c1c;background:#fef2f2;border-color:#fecaca}.gh-r .d.dn,.hrow .d.dn{color:#15803d;background:#f0fdf4;border-color:#bbf7d0}.gh-r .d.sn,.hrow .d.sn{color:#b45309;background:#fffbeb;border-color:#fde68a}
@media (prefers-color-scheme:dark){.gh-due.du,.gh-r .d.du,.hrow .d.du{color:#93c5fd;background:#172554;border-color:#1e3a8a}.gh-due.od,.gh-r .d.od,.hrow .d.od{color:#fca5a5;background:#450a0a;border-color:#7f1d1d}.gh-r .d.dn,.hrow .d.dn{color:#86efac;background:#052e16;border-color:#14532d}.gh-r .d.sn,.hrow .d.sn{color:#fcd34d;background:#422006;border-color:#78350f}}
.gh-gp{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:10px 14px;margin:10px 0}.gh-gp summary{cursor:pointer;font-weight:800;font-size:13px}.gh-gp summary small{color:var(--mut);font-weight:600;margin-left:8px}
.gh-gg{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:10px;margin-top:10px}.gh-gb{display:grid;gap:4px;align-content:start;background:var(--col);border-radius:10px;padding:8px 10px}.gh-gb b{font-size:12px;color:var(--mut);text-transform:uppercase;letter-spacing:.05em}.gh-gb label{display:flex;gap:6px;align-items:center;font-size:13px}.gh-gb input{width:auto;margin:0}
.gh-tchips{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 8px}.gh-tchips span{font-size:11.5px;font-weight:700;background:var(--col);border:1px solid var(--line);border-radius:999px;padding:2px 10px;color:var(--ink)}
.hsec .gh-th{border:0;border-radius:8px;margin:10px 0 6px;padding:6px 12px}#ghdg .hw{padding:8px 0 0;max-width:none}#ghdg .hl{position:relative;top:0}
body.guest #rpw,body.guest #tbtn,body.guest #rtools,body.guest #mhm,body.guest #tfwl,body.guest #gm,body.guest #sm,body.guest #cm,body.guest #dm,body.guest #xm,body.guest #rm,body.guest #ivm,body.guest #tmw,body.guest #om,body.guest #apm,body.guest #mgb,body.guest #rchat,body.guest #msgw,body.guest #sidetabs .stab#mgb{display:none!important}
.gh-em{margin:14px 0 4px;text-align:center}.gh-em button{width:100%;padding:12px 14px;border:0;border-radius:12px;background:linear-gradient(135deg,#38bdf8,#22c55e);color:#062a4a;font-weight:800;font-size:14px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.25)}.gh-em button:disabled{opacity:.6;cursor:default}.gh-em small{display:block;margin-top:6px;font-size:12px;color:#cbd5e1;word-break:break-all}.gh-em small.ok{color:#86efac}.gh-em small.er{color:#fca5a5}
.hrow .d{justify-self:end}.gh-tb{min-width:640px}@media(max-width:600px){.hrow .d{grid-column:2;grid-row:1;align-self:start}.hrow>div:nth-child(2){grid-column:1;grid-row:2;justify-content:flex-start!important}}
.gh-tg{margin-bottom:10px}.gh-th{padding:8px 14px;background:var(--col);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ink);border-bottom:1px solid var(--line)}.gh-th small{font-weight:600;letter-spacing:0;color:var(--mut);margin-left:6px;text-transform:none}
.gh-tg .gh-r:first-of-type{border-top:0}
@media (max-width:640px){.gh-k{grid-template-columns:1fr}.gho{padding:8px}}
#ghdg{width:min(1180px,98vw);max-height:94vh;overflow:auto}
#srdg{width:min(980px,98vw);max-height:94vh}#srdg input:not([type=checkbox]),#srdg select,#srdg textarea,#srdg button{width:auto;margin:0}#srdg textarea{width:100%;min-height:56px}.srl{display:flex;flex-wrap:wrap;gap:8px;margin:8px 0}.src{border:1px solid var(--line);border-radius:10px;padding:8px 12px;font-size:13px;cursor:pointer;min-width:200px}.src.on{border-color:var(--pri);box-shadow:0 0 0 2px rgba(37,99,235,.15)}.src small{display:block;color:var(--mut)}.srf{border:1px solid var(--line);border-radius:10px;padding:10px;margin:8px 0;display:grid;gap:8px}.srf .row{display:flex;flex-wrap:wrap;gap:8px;align-items:center}.srf label.ck{display:inline-flex;gap:5px;align-items:center;font-size:13px;border:1px solid var(--line);border-radius:999px;padding:3px 10px;cursor:pointer}#srprev{border:1px solid var(--line);border-radius:10px;padding:10px;max-height:48vh;overflow:auto;background:#f8fafc;color:#0f172a}
#tldg{width:min(620px,94vw)}.tlt{width:100%;border-collapse:collapse;font-size:13px}.tlt td,.tlt th{padding:5px 6px;border-bottom:1px solid var(--line);text-align:left}.tlt td.n{text-align:right}.tlt th{font-size:11px;color:var(--mut);text-transform:uppercase}
.tlf{display:grid;grid-template-columns:150px 90px 1fr auto;gap:6px;margin-top:10px;align-items:center}
#rdlg{width:min(1000px,96vw);max-height:92vh;overflow:auto}#rdlg .rctl{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:8px 0}#rdlg .rctl input,#rdlg .rctl select{width:auto}
.rt{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:6px}.rt th,.rt td{padding:5px 8px;border-bottom:1px solid var(--line);text-align:left}.rt th{font-size:11px;text-transform:uppercase;color:var(--mut)}.rt .n{text-align:right;white-space:nowrap}.rt tr.sub td{font-weight:600;background:var(--col)}.rt tr.tot td{font-weight:700;border-top:2px solid var(--ink)}
.rcust{margin:16px 0 4px;font-size:16px}
#ivdlg{width:min(720px,96vw);max-height:92vh;overflow:auto}.pgrid{display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;margin:6px 0}.pgrid label{font-size:12px;color:var(--mut)}.pgrid input,.pgrid select{width:100%}@media(max-width:600px){.pgrid{grid-template-columns:1fr}}.rt.wl{font-size:11px}
.addcol{border:1px solid var(--line);border-radius:8px;padding:8px 10px;margin:8px 0}.addcol .acr{display:flex;gap:6px;flex-wrap:wrap;align-items:center;margin-top:4px}.addcol .acr input,.addcol .acr select{width:auto;box-sizing:border-box}#cdlg{width:min(560px,94vw)}
#mhdg{width:min(760px,96vw)}#mhdg td,#mhdg th{vertical-align:top}#mhdg td:first-child,#mhdg td:last-child button{white-space:nowrap}#mhdg .rctl input[type=date]{width:auto}#mhad{width:min(420px,94vw)}
dialog{resize:both;overflow:auto;min-width:260px;min-height:110px;max-width:98vw;max-height:98vh}dialog.dmv{margin:0;position:fixed}dialog>.acts:first-child,dialog>b:first-child,dialog>.dh{cursor:move}
#sidetabs{position:fixed;right:0;top:58px;z-index:30;display:flex;flex-direction:column;gap:4px}.stab{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:0;width:46px;height:42px;padding:2px 2px;border-radius:12px 0 0 12px;border:1px solid color-mix(in srgb,var(--pc) 55%,transparent);border-right:0;background:color-mix(in srgb,var(--pc) 26%,var(--card));color:color-mix(in srgb,var(--pc) 60%,#000);cursor:pointer;box-shadow:-2px 2px 8px rgba(0,0,0,.18);transition:transform .2s,opacity .2s;position:relative}#sidetabs.hid .stab{transform:translateX(70%);opacity:.85}.stab::after{content:"";position:absolute;left:-16px;top:0;bottom:0;width:16px}#sidetabs.hid .stab:hover{transform:none;opacity:1}.stab i{font-style:normal;font-size:16px;line-height:1.1}.stab span{font-size:10px;font-weight:700;line-height:1.1}.stab b{position:absolute;top:-3px;left:-3px;background:#dc2626;color:#fff;border-radius:9px;font-size:10px;padding:0 5px}@media(prefers-color-scheme:dark){.stab{color:color-mix(in srgb,var(--pc) 55%,#fff)}}@media print{#sidetabs{display:none}}
#msgw{position:fixed;top:90px;left:calc(50vw - 20px);width:min(640px,96vw);height:min(520px,80vh);min-width:320px;min-height:260px;max-width:98vw;max-height:96vh;resize:both;background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.3);z-index:41;display:flex;flex-direction:column;overflow:hidden}#msgw[hidden]{display:none!important}.mgh{display:flex;align-items:center;padding:8px 10px;border-bottom:1px solid var(--line);cursor:move;user-select:none;touch-action:none}.mgbody{display:flex;flex:1;min-height:0}#mgu{width:170px;border-right:1px solid var(--line);overflow:auto;flex:none}.mgu{display:flex;align-items:center;gap:6px;padding:8px 10px;cursor:pointer;border-bottom:1px solid var(--line)}.mgu:hover{background:var(--col)}.mgu.on{background:var(--col);font-weight:600;box-shadow:inset 3px 0 0 var(--pri)}.mgu>span:not(.av){flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.mgu b{background:#dc2626;color:#fff;border-radius:9px;font-size:11px;padding:1px 6px}.mgr{flex:1;display:flex;flex-direction:column;min-width:0}.mgtt{padding:8px 12px;font-weight:600;border-bottom:1px solid var(--line)}#mgm{flex:1;overflow:auto;padding:10px;display:flex;flex-direction:column;gap:6px}.mgmsg{max-width:80%;padding:6px 10px;border-radius:12px;background:var(--col);align-self:flex-start;white-space:pre-wrap;word-break:break-word}.mgmsg.me{align-self:flex-end;background:color-mix(in srgb,var(--pri) 20%,var(--card))}.mgmsg small{display:block;color:var(--mut);font-size:11px;margin-top:2px}.mgin{display:flex;gap:6px;padding:8px;border-top:1px solid var(--line)}.mgin textarea{flex:1;resize:none;box-sizing:border-box}#mge{position:absolute;bottom:100%;left:8px;width:min(300px,90%);max-height:170px;overflow:auto;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.25);padding:6px;display:flex;flex-wrap:wrap;gap:2px;z-index:2}#mge[hidden]{display:none}#mge span{font-size:20px;padding:3px 5px;cursor:pointer;border-radius:6px}#mge span:hover{background:var(--col)}@media(max-width:700px){#msgw{left:2vw!important;width:96vw!important}#mgu{width:110px}}
#rside{position:fixed;top:90px;left:calc(50vw - 20px);width:50vw;height:50vh;min-width:300px;min-height:260px;max-width:98vw;max-height:96vh;resize:both;background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.3);z-index:40;display:flex;flex-direction:column;overflow:hidden}#rside .rh{cursor:move;user-select:none;touch-action:none}#rside [hidden],#rside[hidden]{display:none!important}#rside .rh{display:flex;align-items:center;padding:8px 10px;border-bottom:1px solid var(--line)}#rside .rtabs{display:flex;border-bottom:1px solid var(--line)}#rside .rtabs button{flex:1;border:0;border-radius:0;background:none;padding:8px 4px}#rside .rtabs button.on{border-bottom:2px solid var(--pri);font-weight:600}#rside .rbody{padding:10px;overflow:auto;flex:1}#rside label{display:block;font-size:12px;color:var(--mut);margin-bottom:8px}#rside input,#rside select,#rside textarea{width:100%;box-sizing:border-box}#rside .two{display:grid;grid-template-columns:1fr 1fr;gap:8px}.rres{background:var(--col);border-radius:8px;padding:10px;margin:8px 0;font-size:14px;line-height:1.7}.rres b{font-size:18px}.wki{padding:8px;border-bottom:1px solid var(--line);cursor:pointer}.wki:hover{background:var(--col)}.wki small{color:var(--mut);display:block}@media(max-width:700px){#rside{width:96vw!important;left:2vw!important}}.rt.wl th,.rt.wl td{padding:3px 5px}.rinv{border:1px solid var(--line);border-radius:8px;padding:14px;margin:8px 0}
#pbox{display:none}
.pg{background:#fff;color:#000;font-family:Arial,Helvetica,sans-serif;margin:10px 0;box-shadow:0 1px 6px rgba(0,0,0,.25);overflow:auto}
.wlt{border-collapse:collapse;width:100%;table-layout:auto;font:10px Calibri,Arial,sans-serif;color:#000}.wlt td,.wlt th{border:1px solid #777;padding:1px 4px;white-space:nowrap;text-align:left;font-weight:400}
.wlt .band td{border:0;height:78px;background:#38761d;color:#fff;font-weight:700;vertical-align:middle;padding:4px 6px}.wlt .band .bt{background:#0f5499;font-size:17px;text-align:center;font-weight:700}.wlt .band .gtt{background:#38761d}
.wlt .wlh th{color:#000;text-transform:none;background:#a9d08e;font-weight:700;font-size:11px;vertical-align:top;height:34px}.wlt .wlh th.c{text-align:center}.wlt .wlh th.pk{background:#f4cccc}.wlt .wlh th.rt1{text-align:center;background:#fce5cd}
.wlt td.c{text-align:center}.wlt td.k{background:#f4cccc}.wlt .ft td{font-weight:700;border:1px solid #777}
#ivlt{table-layout:auto;width:100%}#ivlt td,#ivlt th{overflow:visible;text-align:left;padding:6px 8px;vertical-align:top}#ivl{width:min(960px,96vw)}.pgsm{width:210mm;min-height:297mm;padding:12mm 12mm 12mm 16mm;margin:10px auto;box-sizing:border-box}.smt{width:100%;border-collapse:collapse;table-layout:fixed;font:15px Calibri,Arial,sans-serif}.smt td,.smt th{font-size:15px;white-space:nowrap;border:1px solid #000;padding:1px 4px;color:#000;text-transform:none;text-align:left;line-height:1.25;overflow:hidden}.smt .bar td{background:#134f5c;color:#fff;font:700 17px Arial;border:0;padding:2px 4px}.smt th{background:#d9d9d9;font:700 15px Arial;white-space:nowrap}.smt th.c{text-align:center}.smt td.c{text-align:center;font:16px Arial}.smt td.cb{text-align:center;font:700 16px Arial}.pginv{width:210mm;min-height:297mm;position:relative;padding:0 0 30mm;margin:10px auto}.pginv .lh{display:block;width:100%;margin-top:4mm}.invt{text-align:center;font:700 16px Arial;margin:8mm 0 3mm}
.itb{table-layout:auto;border-collapse:collapse;width:calc(100% - 20mm);margin:0 10mm;font:12.5px Calibri,Arial,sans-serif}.itb td,.itb th{color:#000;text-transform:none;border:1px solid #000;padding:5px 7px;text-align:left;font-weight:400}.itb th.l{font:700 13px Arial;background:#fff}.itb td.b{font-weight:700;white-space:nowrap;overflow:visible}.itb .c{text-align:center}.itb .g{background:#d9ead3}.itb td.spc{background:#d9d9d9;height:9px;padding:0}.itb td.po{background:#c9daf8;width:30mm}
.itb.nb{border:1px solid #000}.itb.nb td,.itb.nb th{border:1px solid #000}.itb.nb tr.g th{background:#d9ead3;font-weight:400;text-align:left;vertical-align:top;padding:4px 5px}.itb.nb tr.g th.c{text-align:center}.itb.nb td.c{text-align:center}.itb.nb td.g{background:#d9ead3}.itb.nb td.spc{background:#d9ead3;height:7px;padding:0}
.paybox{margin:14mm 10mm 0;padding:5px 8px;background:#d9ead3;font:11px Calibri,Arial,sans-serif;line-height:1.6;width:calc(100% - 20mm)}.paybox b{font-weight:700}
.invf{position:absolute;left:0;right:0;bottom:6mm}.invf .ifbar{height:2.2mm;background:#22784b;margin:0 4mm}.invf .ifad{text-align:right;margin:1.5mm 5mm 0;font:9px Arial}
@page wl{size:A3 landscape;margin:8mm}@page inv{size:A4 portrait;margin:0}@page sm{size:A4 portrait;margin:0}
@media print{body>*:not(#pbox){display:none!important}body{padding:0!important;background:#fff!important}#pbox{display:block}.pg{box-shadow:none;margin:0;overflow:visible;break-after:page}.pgwl{page:wl;width:402mm;max-width:402mm;overflow:hidden}.wlt td,.wlt th{white-space:normal!important;word-break:break-word}.wlt td.c{white-space:nowrap!important}.pgsm{page:sm;margin:0;width:210mm;height:295mm;min-height:0;overflow:hidden}.pginv{page:inv;margin:0;width:210mm;height:295mm;min-height:0;overflow:hidden;break-inside:avoid}.noprint{display:none!important}.pgwl:last-child,.pginv:last-child,.pgsm:last-child{break-after:auto}}

/* Glass look (optional, per device). Blur only on a few static surfaces; rows stay plain. */
html.glass body{background:linear-gradient(135deg,#cfe0ff,#eadcff 55%,#cdeee6) fixed;--card:rgba(255,255,255,.74);--col:rgba(240,243,250,.55)}
html.glass header{position:relative;isolation:isolate;background:transparent;box-shadow:0 2px 18px rgba(20,30,70,.15)}
html.glass header::before{content:"";position:absolute;inset:0;z-index:-1;background:color-mix(in srgb,var(--nv) 72%,transparent);-webkit-backdrop-filter:blur(14px) saturate(150%);backdrop-filter:blur(14px) saturate(150%)}
html.glass #side{isolation:isolate;background:transparent}
html.glass #side::before{content:"";position:fixed;left:0;top:0;bottom:0;width:112px;z-index:-1;background:color-mix(in srgb,var(--sb) 74%,transparent);-webkit-backdrop-filter:blur(14px) saturate(140%);backdrop-filter:blur(14px) saturate(140%)}
html.glass body.sc #side::before{width:44px}
html.glass dialog{background:rgba(255,255,255,.78);-webkit-backdrop-filter:blur(16px) saturate(140%);backdrop-filter:blur(16px) saturate(140%);border-color:rgba(255,255,255,.7);box-shadow:0 18px 50px rgba(20,30,70,.28)}
html.glass dialog::backdrop{background:rgba(15,25,60,.22);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px)}
html.glass #rside{background:rgba(255,255,255,.74);-webkit-backdrop-filter:blur(14px);backdrop-filter:blur(14px)}
html.glass .ghead,html.glass .gname{background:#fff}
html.glass #smenu,html.glass #umenu,html.glass #rpop{background:#fff}
html.glass input,html.glass select,html.glass textarea{background:rgba(255,255,255,.6)}
@media(prefers-color-scheme:dark){html.glass body{background:linear-gradient(135deg,#16213f,#2a1d44 55%,#10312f) fixed;--card:rgba(29,33,45,.72);--col:rgba(23,26,36,.55)}html.glass dialog,html.glass #rside{background:rgba(29,33,45,.8);border-color:rgba(255,255,255,.12)}html.glass .ghead,html.glass .gname{background:#1d212d}html.glass #smenu,html.glass #umenu,html.glass #rpop{background:#1d212d}html.glass input,html.glass select,html.glass textarea{background:rgba(23,26,36,.6)}}
@media(prefers-reduced-transparency:reduce){html.glass *{-webkit-backdrop-filter:none!important;backdrop-filter:none!important}}
header,html.glass header{position:sticky;top:0;z-index:45;transition:padding .15s}
body.hs header,body.hs html.glass header{padding:6px 12px;gap:6px}
body.hs header input,body.hs header select,body.hs header button{padding:4px 8px;font-size:12.5px;min-height:0}
body.hs header .brand{padding-right:10px;margin-right:2px}body.hs header .brand b{font-size:19px}body.hs header .brand small{font-size:7px;margin-top:1px}
body.hs header h1{font-size:15px;margin-right:4px}body.hs #wlogo{height:30px;margin-right:2px}body.hs #wlogo .ini{font-size:12px}body.hs header #bell b{font-size:10px;padding:0 5px}
/* smooth header shrink/grow on scroll (every size that changes is animated together) */
header,header h1,header .brand,header .brand b,header .brand small,#wlogo,#wlogo img,header input,header select,header button,header #bell b{transition:padding .24s cubic-bezier(.4,0,.2,1),margin .24s cubic-bezier(.4,0,.2,1),gap .24s cubic-bezier(.4,0,.2,1),font-size .24s cubic-bezier(.4,0,.2,1),height .24s cubic-bezier(.4,0,.2,1),max-width .24s cubic-bezier(.4,0,.2,1),background-color .15s,color .15s,border-color .15s}
header{backface-visibility:hidden}
@media(prefers-reduced-motion:reduce){header,header *{transition:none!important}}
#wlogo{width:auto;min-width:36px;max-width:90px;height:34px;display:flex;align-items:center;justify-content:center;align-self:center;margin-right:6px}#wlogo img{height:100%;width:auto;max-width:90px;object-fit:contain}header h1{display:flex;align-items:center;align-self:center;line-height:1}

body,body.sc{padding-left:56px}
#side,body.sc #side{width:56px;align-items:center;overflow:visible;padding:10px 0 12px;gap:6px}
@media(max-width:700px){body,body.sc{padding-left:56px}}
#side .rlogo{display:flex;align-items:center;justify-content:center;width:44px;height:44px;margin-bottom:8px;border-radius:12px}
#side .rb{width:42px;height:42px;padding:0;border:0;border-radius:11px;display:flex;align-items:center;justify-content:center;background:transparent;color:var(--sf);text-decoration:none;cursor:pointer}
#side .rb:hover,#side a.rb:hover{background:color-mix(in srgb,var(--sf) 16%,transparent)}
#side .rb.on{background:color-mix(in srgb,var(--sf) 26%,transparent)}
#side .sf,body.sc .sf{padding:0;margin-top:auto;display:flex;flex-direction:column;align-items:center;gap:8px;width:100%}
#side .sf .rb{width:42px;height:42px;padding:0;font-size:inherit}
#side #setw{display:flex}
#side #ubtn{width:38px;height:38px;border-radius:50%;padding:0;background:#6366f1;color:#fff;border:2px solid color-mix(in srgb,var(--sf) 40%,transparent);font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:center}
#side #ubtn:hover{background:#7477f5}
#umenu{min-width:210px}.umh{padding:8px 10px 6px;border-bottom:1px solid var(--line);margin-bottom:4px;display:flex;flex-direction:column}.umh small{color:var(--mut);font-size:12px}
#cpanel{position:fixed;left:56px;top:0;bottom:0;width:290px;background:var(--card);color:var(--ink);border-right:1px solid var(--line);box-shadow:10px 0 28px rgba(0,0,0,.2);z-index:46;display:flex;flex-direction:column;gap:8px;padding:14px 12px;transform:translateX(-14px);opacity:0;pointer-events:none;transition:transform .16s,opacity .16s}
#cpanel.open{transform:none;opacity:1;pointer-events:auto}
#cpanel .cph{display:flex;align-items:center}#cpanel .cph b{flex:1;font-size:16px}#cpanel .cph button{border:0;background:none;font-size:15px;color:var(--mut)}
#cpl{display:flex;flex-direction:column;gap:2px;overflow:auto}
#cpanel .cr{display:flex;align-items:center;gap:11px;padding:7px 8px;border-radius:9px;color:var(--ink);text-decoration:none}
#cpanel .cr:hover{background:var(--col)}#cpanel .cr.on{background:color-mix(in srgb,var(--pri) 16%,var(--card));font-weight:600}
#cpanel .cr .wl{width:40px;height:40px;border-radius:10px;border:0;padding:3px;background:#0f2a5c;flex:none;pointer-events:none}
#cpanel .cr .ini{border-radius:8px;font-size:15px}
#cpanel .cr span.nm{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
body.home .tabs,body.home #dv,body.home #q,body.home #fs,body.home #fp,body.home #fa,body.home #fme,body.home #add,body.home #gtabs,body.home #stats,body.home #gcb,body.home #bar{display:none!important}
.hm{max-width:900px;margin:0 auto;padding:18px 16px 40px}.hm h2{margin:0 0 2px;font-size:22px}.hm .sub{color:var(--mut);margin-bottom:16px}
.hmc{margin-top:16px}.hmc h3{display:flex;align-items:center;gap:8px;margin:0 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:.6px;color:var(--mut)}.hmc h3 i{width:9px;height:9px;border-radius:50%;background:#7c3aed}.hmc h3 em{font-style:normal;background:var(--col);padding:0 7px;border-radius:9px;font-size:11px}
.hmr{display:flex;gap:10px;align-items:center;width:100%;text-align:left;padding:10px 12px;background:var(--card);border:1px solid var(--line);border-radius:10px;margin-bottom:6px;color:var(--ink);cursor:pointer;font:inherit}.hmr:hover{border-color:#7c3aed}.hmr .av{flex:none}.hmr .hmb{flex:1;min-width:0}.hmr .hmb b{display:block}.hmr .hmb small{display:block;color:var(--mut);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.hmr .hmt{font-size:11px;color:var(--mut);white-space:nowrap;text-align:right}.hmr .hmu{display:inline-block;min-width:18px;padding:1px 6px;border-radius:9px;background:#7c3aed;color:#fff;font-size:11px;font-weight:700;margin-top:3px}
.otn{font-size:11px;color:var(--mut);align-self:center;white-space:nowrap}
.htabs{display:flex;gap:4px;margin:14px 0 4px;border-bottom:1px solid var(--line)}.htabs button{all:unset;cursor:pointer;padding:8px 14px;font-weight:600;color:var(--mut);border-bottom:2px solid transparent;margin-bottom:-1px}.htabs button:hover{color:var(--ink)}.htabs button.on{color:var(--ink);border-bottom-color:var(--pri,#2563eb)}.htabs em{font-style:normal;font-size:11px;background:var(--col);padding:1px 7px;border-radius:9px;margin-left:4px}
.hcg{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-top:6px}.hcc{display:flex;flex-direction:column;gap:3px;padding:12px 14px;background:var(--card);border:1px solid var(--line);border-radius:14px;color:var(--ink);text-decoration:none;transition:transform .15s,box-shadow .15s,border-color .15s}.hcc:hover{transform:translateY(-2px);border-color:var(--pri,#2563eb);box-shadow:0 8px 22px rgba(20,32,58,.1)}.hcc .hcl{height:40px;display:flex;align-items:center;margin-bottom:4px}.hcc .hcl img{max-height:44px;max-width:150px;background:var(--nv,#0f2a5c);padding:6px 12px;border-radius:10px;box-sizing:content-box}.hcc .hcl .ini{width:44px;height:44px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700}.hcc b{font-size:15px}.hcc small{color:var(--mut)}.hcc small .lt{font-style:normal;color:#dc2626;font-weight:600}
.hrem{display:flex;gap:12px;align-items:flex-start;margin:0 0 14px;padding:12px 14px;border-radius:12px;background:#fff7e6;border:1px solid #f5d08a;color:#7a4b00}.hrem.bad{background:#fdecec;border-color:#f3b4b0;color:#8a1c17}.hrem .ri{font-size:20px;line-height:1}.hrem .rb{flex:1;min-width:0}.hrem b{display:block;margin-bottom:2px}.hrem .rd{display:flex;flex-wrap:wrap;gap:5px;margin-top:6px}.hrem .rd span{font-size:11.5px;padding:2px 8px;border-radius:9px;background:rgba(255,255,255,.7);border:1px solid rgba(0,0,0,.08)}.hrem button{flex:none;align-self:center}
.hw{display:grid;grid-template-columns:minmax(320px,410px) minmax(0,1fr);gap:22px;max-width:1280px;margin:0 auto;padding:16px 18px 40px;align-items:start}
.hl{position:sticky;top:14px;color:#fff;border-radius:26px;padding:26px 22px 18px;overflow:hidden;background:radial-gradient(700px 420px at 10% 0%,#1d4694 0%,transparent 60%),radial-gradient(520px 380px at 100% 100%,#0e6b4f 0%,transparent 58%),linear-gradient(160deg,#0a1d44,#0f2a5c);box-shadow:0 18px 40px rgba(15,42,92,.28);max-height:calc(100vh - 100px);overflow-y:auto;scrollbar-width:none}
.hl::-webkit-scrollbar{display:none}.hl:before{content:"";position:absolute;inset:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.045) 1px,transparent 1px);background-size:40px 40px;-webkit-mask-image:radial-gradient(ellipse at 30% 25%,#000 15%,transparent 75%);mask-image:radial-gradient(ellipse at 30% 25%,#000 15%,transparent 75%)}
.hl>*{position:relative}.hl .hgr{font-size:13px;letter-spacing:.3px;color:rgba(255,255,255,.75)}.hl h2{margin:4px 0 6px;font:800 clamp(30px,3.2vw,42px)/1.05 system-ui,sans-serif;letter-spacing:-.03em;color:#fff}.hl h2 .wv{display:inline-block;animation:hwv 2.4s ease-in-out 1 .6s;transform-origin:70% 70%}@keyframes hwv{0%,60%,100%{transform:rotate(0)}10%,30%,50%{transform:rotate(16deg)}20%,40%{transform:rotate(-10deg)}}
.hl p{margin:0 0 14px;color:rgba(255,255,255,.8);font-size:14.5px}.hl p b{color:#fff}
.hch{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px}.hch span{display:flex;flex-direction:column;min-width:74px;padding:8px 12px;border-radius:14px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);font-size:11px;color:rgba(255,255,255,.72);backdrop-filter:blur(6px)}.hch span b{font-size:20px;line-height:1.1;color:#fff}.hch .rd{background:rgba(248,113,113,.2);border-color:rgba(248,113,113,.4)}.hch .gn b{color:#4ade80}
.hl .hrem{background:rgba(251,191,36,.14);border:1px solid rgba(251,191,36,.35);color:#fde9b0;border-radius:16px;margin:0 0 16px;flex-direction:column;gap:8px}.hl .hrem.bad{background:rgba(248,113,113,.16);border-color:rgba(248,113,113,.4);color:#fecaca}.hl .hrem b{color:#fff}.hl .hrem .rd span{background:rgba(255,255,255,.12);border-color:rgba(255,255,255,.18);color:#fff}.hl .hrem .ri{display:none}.hl .hrem button{align-self:flex-start;background:#fff;color:#0f2a5c;border:0;border-radius:10px;font-weight:700;padding:7px 12px}
.hlh{font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.6);margin:0 0 8px}
.hl .hcg{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:0}.hl .hcc{background:rgba(255,255,255,.09);border:1px solid rgba(255,255,255,.16);color:#fff;border-radius:18px;padding:12px;backdrop-filter:blur(6px)}.hl .hcc:hover{background:rgba(255,255,255,.17);border-color:rgba(255,255,255,.4);box-shadow:0 10px 24px rgba(0,0,0,.25)}.hl .hcc small{color:rgba(255,255,255,.7)}.hl .hcc small .lt{color:#fca5a5}.hl .hcc .hcl{height:36px}.hl .hcc .hcl img{background:transparent;padding:0;max-height:34px;max-width:100%}.hl .hcc .hcl .ini{width:36px;height:36px;border-radius:11px}.hl .hcc b{font-size:14px}
.hlhelp{margin-top:14px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.32);color:#fff;border-radius:10px;padding:7px 12px;font-size:13px;cursor:pointer}.hlhelp:hover{background:rgba(255,255,255,.26)}.hlf{margin-top:12px;font-style:italic;font-size:12px;color:rgba(255,255,255,.55)}.hlf i{color:#22c55e;font-style:normal;font-weight:900}
.hrr .hmc:first-child,.hrr .hsec:first-child{margin-top:0}
.hw>*{min-width:0}.hl .hrem .rb{overflow-wrap:anywhere}
@media(max-width:900px){.hw{grid-template-columns:minmax(0,1fr);gap:14px;padding:10px 10px 30px;width:100%;box-sizing:border-box}.hl{position:relative;top:0;max-height:none;border-radius:22px;padding:20px 16px 14px;box-sizing:border-box;width:100%}.hl h2{font-size:30px}.hch{gap:6px}.hch span{flex:1 1 calc(50% - 6px);min-width:0}.hl .hrem{box-sizing:border-box}.hl .hcg{grid-template-columns:repeat(2,minmax(0,1fr))}.hl .hcc{min-width:0}.hl .hcc b,.hl .hcc small{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.hrr .hrow{grid-template-columns:minmax(0,1fr) auto}.hmr .hmt{max-width:34%}}
#wkdlg{width:min(1240px,97vw);height:min(86vh,900px);padding:0;display:none;flex-direction:column;overflow:hidden;resize:both}#wkdlg[open]{display:flex}
#wkdlg .wkh{display:flex;gap:8px;align-items:center;padding:12px 14px;border-bottom:1px solid var(--line);margin:0;flex:none;position:static;border-top:0}#wkdlg .wkh b{font-size:16px;white-space:nowrap}#wkdlg .wkh input{flex:1;min-width:120px}.wkbr{display:none}
.wkb{display:grid;grid-template-columns:270px minmax(0,1fr);flex:1;min-height:0}#wkside{overflow:auto;padding:12px;border-right:1px solid var(--line);background:var(--col)}#wkmain{overflow:auto;padding:22px 28px 40px;scroll-behavior:smooth}
#wkside .wkc{margin:0 0 4px}#wkside summary{cursor:pointer;font-weight:700;font-size:12px;letter-spacing:.6px;text-transform:uppercase;color:var(--mut);padding:7px 4px;list-style:none;display:flex;justify-content:space-between;gap:6px}#wkside summary::-webkit-details-marker{display:none}#wkside summary em{font-style:normal;background:var(--card);border-radius:9px;padding:0 7px;font-size:11px}
#wkside .wkl{display:block;padding:6px 10px;border-radius:8px;color:var(--ink);font-size:13.5px;cursor:pointer;text-decoration:none;margin:1px 0}#wkside .wkl:hover{background:var(--card)}#wkside .wkl.on{background:var(--card);box-shadow:0 1px 5px rgba(0,0,0,.12);font-weight:600;color:var(--pri,#2563eb)}#wkside .wkh2{font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--mut);margin:14px 4px 6px}
.wktg{display:inline-block;padding:2px 9px;margin:2px 3px 2px 0;border-radius:11px;font-size:12px;background:var(--card);border:1px solid var(--line);color:var(--ink);cursor:pointer}.wktg:hover{border-color:var(--pri,#2563eb)}.wktg.on{background:var(--pri,#2563eb);color:#fff;border-color:transparent}.wktg i{font-style:normal;opacity:.55;margin-left:3px}
#wkmain .wkcrumb{font-size:12.5px;color:var(--mut);margin-bottom:6px}#wkmain .wkcrumb a{color:var(--mut);cursor:pointer}#wkmain h1{margin:0 0 8px;font-size:28px;letter-spacing:-.02em}#wkmain .wkmeta{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-bottom:14px;font-size:12px;color:var(--mut)}
.wkart{max-width:820px;line-height:1.62;font-size:15px}.wkart h2{font-size:20px;margin:26px 0 8px;padding-bottom:5px;border-bottom:1px solid var(--line)}.wkart h3{font-size:16px;margin:18px 0 6px}.wkart p{margin:8px 0}.wkart ul,.wkart ol{padding-left:22px;margin:8px 0}.wkart li{margin:3px 0}.wkart code{background:var(--col);padding:1px 6px;border-radius:5px;font-size:13px}.wkart hr{border:0;border-top:1px solid var(--line);margin:18px 0}
.wkart table{border-collapse:collapse;margin:12px 0;font-size:14px;max-width:100%;display:block;overflow:auto}.wkart th,.wkart td{border:1px solid var(--line);padding:6px 11px;text-align:left}.wkart th{background:var(--col)}.wkart tr:nth-child(even) td{background:color-mix(in srgb,var(--col) 45%,transparent)}
.wkart blockquote{margin:12px 0;padding:10px 14px;border-left:4px solid #94a3b8;background:var(--col);border-radius:0 10px 10px 0}.wkart blockquote.note{border-color:#2563eb;background:color-mix(in srgb,#2563eb 9%,var(--card))}.wkart blockquote.warn{border-color:#dc2626;background:color-mix(in srgb,#dc2626 9%,var(--card))}.wkart blockquote.tip{border-color:#16a34a;background:color-mix(in srgb,#16a34a 9%,var(--card))}.wkart blockquote.review{border-color:#d97706;background:color-mix(in srgb,#f59e0b 14%,var(--card))}.wkart blockquote .wkbt{display:block;font-weight:700;font-size:12px;letter-spacing:.6px;text-transform:uppercase;margin-bottom:2px}
.wkart figure{margin:14px 0}.wkart figure img{max-width:100%;border:1px solid var(--line);border-radius:10px;cursor:zoom-in;background:#fff}.wkart figcaption{font-size:12.5px;color:var(--mut);margin-top:4px}
#wkmain .wkl{display:block;padding:4px 0;cursor:pointer}.wktoc{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 6px}.wktoc a{font-size:12.5px;padding:3px 10px;border-radius:12px;background:var(--col);color:var(--ink);cursor:pointer}
.wkrel{margin-top:30px;padding-top:12px;border-top:1px solid var(--line)}.wkrel h4{margin:0 0 6px;font-size:12px;letter-spacing:1px;text-transform:uppercase;color:var(--mut)}
.wkhome h1{font-size:30px}.wkgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:12px;margin:16px 0}.wkcard{display:flex;flex-direction:column;gap:4px;padding:14px 16px;border:1px solid var(--line);border-radius:14px;background:var(--card);cursor:pointer;transition:transform .15s,box-shadow .15s,border-color .15s}.wkcard:hover{transform:translateY(-2px);border-color:var(--pri,#2563eb);box-shadow:0 8px 20px rgba(20,32,58,.1)}.wkcard b{font-size:15px}.wkcard small{color:var(--mut)}.wkcard .ic{font-size:22px}
.wkres{max-width:820px}.wkres .wkr{padding:12px 14px;border:1px solid var(--line);border-radius:12px;margin-bottom:8px;background:var(--card);cursor:pointer}.wkres .wkr:hover{border-color:var(--pri,#2563eb)}.wkres .wkr b{font-size:15px}.wkres .wkr small{display:block;color:var(--mut);margin:2px 0}.wkres mark{background:#fde68a;color:inherit;border-radius:3px;padding:0 1px}
.wked{max-width:820px;display:flex;flex-direction:column;gap:8px}.wked label{font-size:12px;font-weight:600;color:var(--mut);margin:0}.wked .two{display:grid;grid-template-columns:1fr 1fr;gap:10px}.wked textarea{min-height:48vh;font:13.5px/1.5 ui-monospace,Menlo,Consolas,monospace}.wktb{display:flex;flex-wrap:wrap;gap:4px}.wktb button{padding:3px 9px;font-size:12.5px}
#wkzoom{position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.82);display:flex;align-items:center;justify-content:center;padding:20px;cursor:zoom-out}#wkzoom[hidden]{display:none}#wkzoom img{max-width:100%;max-height:100%;background:#fff;border-radius:6px}
@media(max-width:800px){#wkdlg{width:100vw;height:100dvh;max-width:100vw;max-height:100dvh;border-radius:0;resize:none}.wkb{grid-template-columns:minmax(0,1fr)}#wkside{display:none;border-right:0;border-bottom:1px solid var(--line);max-height:45vh}#wkdlg.nav #wkside{display:block}#wkmain{padding:16px 14px 40px}.wkbr{display:inline-block}#wkdlg .wkh{flex-wrap:wrap}#wkdlg .wkh input{order:5;flex-basis:100%}.wked .two{grid-template-columns:1fr}.wkart{font-size:14.5px}#wkmain h1{font-size:23px}}
.hsec{margin-top:18px}.hsec h3{display:flex;align-items:center;gap:8px;margin:0 0 6px;font-size:13px;text-transform:uppercase;letter-spacing:.6px;color:var(--mut)}.hsec h3 i{width:9px;height:9px;border-radius:50%;background:var(--c,#64748b)}.hsec h3 em{font-style:normal;font-weight:600;color:var(--ink)}
.hrow{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:6px 14px;align-items:center;padding:10px 12px;background:var(--card);border:1px solid var(--line);border-radius:10px;margin-bottom:6px;color:var(--ink);text-decoration:none}
.hrow:hover{border-color:var(--pri)}.hrow b{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.hrow small{color:var(--mut)}
.hco{font-size:12px;font-weight:600;padding:2px 9px;border-radius:999px;background:var(--col);color:var(--mut);white-space:nowrap}
.hdl{font-size:13px;white-space:nowrap;min-width:84px;text-align:right}.hdl.late{color:#dc2626;font-weight:600}
.hst{font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;white-space:nowrap;text-transform:uppercase;background:color-mix(in srgb,var(--pc,#888) 22%,var(--card));color:color-mix(in srgb,var(--pc,#888) 60%,var(--ink));border:1px solid color-mix(in srgb,var(--pc,#888) 45%,transparent)}
@media(max-width:600px){.hrow{grid-template-columns:minmax(0,1fr) auto}.hrow .hco{grid-column:1}}
#side .rlogo{flex-direction:column;width:52px;height:auto;line-height:1;gap:1px;margin:2px 0 10px;text-decoration:none;color:var(--sf);font-weight:800;font-size:14px;letter-spacing:.2px}#side .rlogo .fl{color:#22c55e;text-shadow:0 0 8px rgba(34,197,94,.55)}
.apn{background:#dc2626;color:#fff;border-radius:9px;font-size:11px;padding:0 6px;margin-left:4px}.apr{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:4px 10px;padding:8px 0;border-bottom:1px solid var(--line);align-items:center}.apr small{color:var(--mut)}.apr .acts{margin:0}
#umenu a[hidden],#umenu [hidden]{display:none!important}
#side .rlogo{flex-direction:row;align-items:center;gap:1px;font-size:22px;letter-spacing:0;width:52px;height:40px}#side .rlogo .fl{font-size:26px;line-height:1;margin-top:-3px;text-shadow:0 0 8px rgba(34,197,94,.7)}
#sidetabs .stab.ring,#sidetabs.hid .stab.ring{transform:translateX(0);opacity:1;animation:stbl .5s ease-in-out 10;z-index:2}@keyframes stbl{0%,100%{box-shadow:-2px 2px 8px rgba(0,0,0,.18);background:color-mix(in srgb,var(--pc) 26%,var(--card))}50%{box-shadow:0 0 0 3px color-mix(in srgb,var(--pc) 55%,transparent),-2px 2px 14px color-mix(in srgb,var(--pc) 70%,transparent);background:color-mix(in srgb,var(--pc) 70%,#fff)}}@media(prefers-reduced-motion:reduce){#sidetabs .stab.ring{animation:none;box-shadow:0 0 0 3px var(--pc)}}
.ed.tt{cursor:pointer;text-overflow:ellipsis}.ed.tt:hover{text-decoration:underline;border-color:transparent}.ed.tt:focus{border-color:transparent;background:transparent}
#acbody td.acc select{font-weight:600;border-color:transparent;color:#fff}#acbody td.acc select option{background:var(--card);color:var(--ink);font-weight:400}
#acbody td.acc[data-lv="none"] select{background-color:#dc2626}#acbody td.acc[data-lv="view"] select{background-color:#f97316}#acbody td.acc[data-lv="edit"] select{background-color:#2563eb}#acbody td.acc[data-lv="full"] select{background-color:#16a34a}

.mobonly,#side .rb.mobonly{display:none}
@media(max-width:700px){
 .mobonly{display:inline-flex}#side .rb.mobonly{display:flex}
 body,body.sc{padding-left:0;padding-bottom:calc(64px + env(safe-area-inset-bottom,0px))}
 /* bottom bar */
 #side,body.sc #side{top:auto;bottom:0;left:0;right:0;width:100%;height:calc(56px + env(safe-area-inset-bottom,0px));flex-direction:row;justify-content:space-around;align-items:center;padding:0 6px env(safe-area-inset-bottom,0px);gap:0;border-right:0;border-top:1px solid color-mix(in srgb,var(--sf) 20%,transparent);overflow:visible;z-index:50}
 #side .rlogo{display:none}
 #side .sf,body.sc .sf{flex-direction:row;width:auto;margin:0;gap:2px}
 #side .rb,#side .sf .rb{width:48px;height:46px;position:relative}
 #rchn{position:absolute;top:2px;right:2px}
 #side #ubtn{width:36px;height:36px}
 #sidetabs{display:none}
 #cpanel{left:0;right:0;top:auto;bottom:calc(56px + env(safe-area-inset-bottom,0px));width:100%;max-height:65vh;border-right:0;border-radius:16px 16px 0 0;transform:translateY(16px);z-index:49}
 #cpanel.open{transform:none}
 #rpl{position:static;display:flex;box-shadow:none;border:0;background:none;padding:0 0 0 14px;min-width:0}#tmw .tms{position:static;display:flex;box-shadow:none;border:0;background:none;padding:0 0 0 14px;min-width:0}
 #smenu{max-height:70vh}
 /* header */
 header,html.glass header{padding:6px 10px;gap:6px}
 body.hs header{padding:6px 10px}
 header .brand,#bld,#dv{display:none}
 #wlogo{height:28px;max-width:70px}
 header h1{font-size:15px;flex:1 1 auto;min-width:0}header h1 #wname{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;max-width:34vw}
 header .sp{display:none}
 body:not(.mf) #q,body:not(.mf) #fs,body:not(.mf) #fp,body:not(.mf) #fme{display:none}
 body.mf #q,body.mf #fs,body.mf #fp,body.mf #fme{display:block;flex:1 1 100%;width:100%;min-width:0}
 #add{padding:6px 10px}
 .tabs button{padding:6px 10px}
 .gtabs{overflow-x:auto;flex-wrap:nowrap;white-space:nowrap;padding-left:10px;padding-right:10px;-webkit-overflow-scrolling:touch}
 .stats{padding:8px 10px;gap:6px}.stat{padding:5px 9px}
 /* list as cards */
 .listwrap{padding:0 8px 12px;overflow-x:visible}
 .listwrap table{width:100%!important;display:block;background:none;border:0}
 .listwrap colgroup,.listwrap thead{display:none}
 .listwrap tbody{display:block}
 tr.row{display:grid;grid-template-columns:1fr 1fr;gap:2px 8px;background:var(--card);border:1px solid var(--line);border-left:5px solid var(--sc,var(--line));border-radius:12px;margin:0 0 8px;padding:8px 10px;align-items:center}
 tr.row>td{display:none;border:0;padding:0;overflow:visible;width:auto;background:transparent!important}
 tr.row>td[data-c="title"]{display:block;grid-column:1/-1}
 tr.row>td[data-c="order_no"],tr.row>td[data-c="customer"]{display:block;font-size:12px;color:var(--mut)}
 tr.row>td[data-c="status"],tr.row>td[data-c="assigned_to"],tr.row>td[data-c="dates"]{display:block}
 tr.row>td[data-c="dates"]{grid-column:1/-1}
 tr.row .drng:not(.empty)::before{content:"TimeLine: ";font-weight:400;opacity:.65}
 tr.row .ed{min-width:0;width:100%}
 tr.row .ed.tt{font-weight:600;font-size:15px;white-space:normal}
 tr.row:not(.draft)>td:first-child,tr.row:not(.draft)>td:last-child{display:none}
 tr.row.draft>td:first-child{display:none}tr.row.draft>td:last-child{display:flex;grid-column:1/-1;gap:8px;justify-content:flex-end}
 tr.gh,tr.dh,tr.gsp,tr.gend,.addrow,tr.addrow{display:block}
 tr.gh th,tr.dh th{display:block;width:auto}
 tr.gsp td,tr.gend td{display:block;height:6px;padding:0}
 tr.addrow td,.listwrap tr:not(.row) td{display:block;width:auto}
 .ghd,.hd{display:none}
 /* board + gantt */
 .board{grid-template-columns:none;grid-auto-flow:column;grid-auto-columns:82vw;scroll-snap-type:x mandatory;padding:0 8px 12px}.board>.col{scroll-snap-align:start}
 .gwrap{margin:0 8px 12px;max-height:calc(100dvh - 250px)}
 /* windows */
 dialog{width:100vw!important;max-width:100vw!important;height:100dvh;max-height:100dvh!important;margin:0;border-radius:0;border:0}
 #rside,#msgw{left:0!important;top:0!important;width:100vw!important;height:calc(100dvh - 56px - env(safe-area-inset-bottom,0px))!important;max-width:100vw;max-height:none;border-radius:0;resize:none;z-index:48}
 .hm{padding:12px 10px 30px}
 select,input,button{min-height:36px}
}
.pres-w{display:inline-flex;align-items:center;padding-left:8px;margin-right:6px}#pres{display:inline-flex;align-items:center}#pres .pb{text-shadow:0 1px 2px rgba(0,0,0,.45);position:relative;margin-left:-8px;width:28px;height:28px;border-radius:50%;border:2px solid var(--nv,#0f2a5c);box-sizing:border-box;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;cursor:default;animation:pbIn .3s ease both}#pres .pb:first-child{margin-left:0}#pres .pb:hover{z-index:3;transform:translateY(-2px)}#pres .pb i{position:absolute;right:-2px;bottom:-2px;width:9px;height:9px;border-radius:50%;background:#22c55e;border:2px solid var(--nv,#0f2a5c);box-sizing:content-box}#pres .pb.mo{background:rgba(255,255,255,.22)}@keyframes pbIn{from{opacity:0;transform:scale(.6)}}@media(max-width:700px){#pres{display:none}}
.asw2{display:flex;gap:6px;align-items:center}.asw2 .av{width:26px;height:26px;font-size:10px}.card .meta .av{width:20px;height:20px;font-size:8px;vertical-align:middle}.asw2 select{flex:1;min-width:0}
.coadd{padding:2px 8px;border-radius:999px;border:1px dashed var(--line);background:var(--card);color:var(--mut);font-size:12px;font-weight:600;white-space:nowrap}.coadd.has{border-style:solid;background:var(--accbg,#dcf3ea);color:#0b8f6a}
.coN{font-size:12px;font-weight:600;color:var(--mut)}
#copop{position:fixed;z-index:70;min-width:200px;max-height:50vh;overflow:auto;background:var(--card);color:var(--ink);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.22);padding:8px}#copop[hidden]{display:none}
#copop .coh{font-size:12px;font-weight:600;color:var(--mut);margin:0 4px 6px}#copop label{display:flex;align-items:center;gap:8px;padding:5px 6px;border-radius:6px;cursor:pointer}#copop label:hover{background:var(--col)}#copop input{width:auto}

/* ---- Layout looks (set in Appearance): lk-n Refined Navy, lk-l Light, lk-d Floating dock ---- */
@media(min-width:701px){
html.lk-n header,html.lk-d header{background:linear-gradient(100deg,color-mix(in srgb,var(--nv) 78%,#000),var(--nv) 70%,color-mix(in srgb,var(--nv) 82%,#38bdf8))}
html.lk-n header:after,html.lk-d header:after{content:"";position:absolute;left:0;right:0;bottom:0;height:2px;background:linear-gradient(90deg,transparent,#38bdf8 30%,#818cf8 70%,transparent);opacity:.85;pointer-events:none}
html.lk-n header,html.lk-l header,html.lk-d header{position:sticky}
html.lk-n header input,html.lk-n header select,html.lk-l header input,html.lk-l header select,html.lk-d header input,html.lk-d header select{border-radius:9px}
html.lk-n header .tabs,html.lk-l header .tabs,html.lk-d header .tabs{padding:3px;border-radius:10px;background:color-mix(in srgb,var(--hfg) 10%,transparent)}
html.lk-n header .tabs button,html.lk-l header .tabs button,html.lk-d header .tabs button{border-radius:7px;border-color:transparent;background:transparent}
html.lk-n header .tabs button.on,html.lk-d header .tabs button.on{background:var(--hfg);color:var(--nv)}
html.lk-n .brand b,html.lk-l .brand b,html.lk-d .brand b{font-size:21px;letter-spacing:-.2px}
/* labelled rail (Navy + Light) */
html.lk-n #side .rb[data-l],html.lk-l #side .rb[data-l]{height:50px;flex-direction:column;gap:2px;border-radius:12px}
html.lk-n #side .rb[data-l]:after,html.lk-l #side .rb[data-l]:after{content:attr(data-l);font-size:9.5px;font-weight:500;line-height:1;letter-spacing:.2px}
html.lk-n #side,html.lk-n body.sc #side{width:64px}html.lk-n body,html.lk-n body.sc{padding-left:64px}html.lk-n #cpanel{left:64px}
html.lk-n body.rh,html.lk-n body.rh.sc{padding-left:44px}
html.lk-n #side .rb:hover,html.lk-n #side .rb.on{background:color-mix(in srgb,var(--sf) 20%,transparent)}
html.lk-n body.rh #side .rb[data-l]:after,html.lk-l body.rh:not(.ro) #side .rb[data-l]:after{display:none}
html.lk-n body.rh #side .rb#rhome,html.lk-n body.rh #side .rb#rco{flex-direction:row;height:42px}
/* Light header + side */
html.lk-l header{--nv:#ffffff!important;--hfg:#14203a!important;background:#fff;border-bottom:1px solid #dde2ee;box-shadow:0 1px 0 rgba(20,32,58,.03)}
html.lk-l header .brand{border-right-color:#dde2ee}
html.lk-l header input,html.lk-l header select,html.lk-l header button{background:#f4f6fb;border-color:#e1e6ef;color:#14203a}
html.lk-l header button:hover:not(:disabled){background:#e9eef8}
html.lk-l header .tabs{background:#eef1f7}html.lk-l header .tabs button.on{background:#fff;color:#14203a;box-shadow:0 1px 2px rgba(20,32,58,.18)}
html.lk-l header button.p{background:linear-gradient(135deg,#12306e,#2563eb);color:#fff;border-color:transparent}
html.lk-l header input::placeholder{color:#7b86a0}
html.lk-l #side{z-index:46;--sb:#ffffff;--sf:#43506e;background:#fff;border-right:1px solid #dde2ee;width:56px;overflow:hidden;transition:width .18s ease,box-shadow .18s ease}
html.lk-l #side .rlogo{background:linear-gradient(135deg,#12306e,#2563eb);color:#fff}
html.lk-l #side .rb:hover,html.lk-l #side a.rb:hover,html.lk-l #side .rb.on{background:#e8efff;color:#1d4ed8}
html.lk-l #side #smenu,html.lk-l #side #umenu{color:var(--ink)}
html.lk-l #side:hover{width:196px;overflow:hidden;box-shadow:10px 0 30px rgba(20,32,58,.14);align-items:stretch;padding-left:7px;padding-right:7px}
 html.lk-l #side:hover .rb[data-l]{width:100%;flex-direction:row;justify-content:flex-start;gap:12px;height:42px;padding:0 10px}
 html.lk-l #side:hover .rb[data-l]:after{font-size:13px;font-weight:600;letter-spacing:0}
 html.lk-l #side:hover .rlogo{align-self:flex-start;margin-left:5px}html.lk-l #side:hover .sf{align-items:stretch}
html.lk-l #wlogo .wh,html.lk-l #wlogo img[src^="data:"]{filter:brightness(0)}
/* Floating dock */
html.lk-d body,html.lk-d body.sc{padding-left:78px}
html.lk-d #side,html.lk-d body.sc #side{top:var(--dky,var(--dk,78px));bottom:auto;left:var(--dkx,12px);width:58px;height:auto;max-height:calc(100vh - var(--dky,var(--dk,78px)) - 16px);border:1px solid #dbe1ee;border-radius:18px;background:#fff;--sb:#fff;--sf:#43506e;box-shadow:0 10px 30px rgba(20,32,58,.16);padding:10px 0;overflow:visible;gap:4px}
html.lk-d #side .rlogo{display:none}
html.lk-d #sidetabs{display:none!important}html.lk-d #side .rb.mobonly{display:flex}
html.lk-d #side .sf{margin-top:6px;border-top:1px solid #e1e6ef;padding-top:8px}
html.lk-d #side .rb:hover,html.lk-d #side a.rb:hover,html.lk-d #side .rb.on{background:#e6eeff;color:#1d4ed8}
html.lk-d #side .rb[data-l]:hover:after{content:attr(data-l);position:absolute;left:calc(100% + 12px);top:50%;transform:translateY(-50%);padding:6px 11px;border-radius:8px;background:#14203a;color:#fff;font-size:12px;font-weight:600;white-space:nowrap;box-shadow:0 6px 16px rgba(20,32,58,.25);pointer-events:none;z-index:60}
html.lk-d #side .rb{position:relative}
html.lk-d #cpanel{left:84px}
html.lk-d body.rh #side{box-shadow:none}
html.lk-d body.rh,html.lk-d body.rh.sc{padding-left:44px}
html.lk-d header{margin-left:-78px;padding-left:96px!important}
html.lk-d #rhide{display:none!important}
.dkg{display:none}
html.lk-d .dkg{display:flex;align-items:center;justify-content:center;width:100%;height:18px;margin:-6px 0 2px;cursor:grab;touch-action:none;user-select:none;flex:none}
html.lk-d .dkg:active{cursor:grabbing}
html.lk-d .dkg i{display:block;width:26px;height:4px;border-radius:2px;background:repeating-linear-gradient(90deg,#b4bdd0 0 4px,transparent 4px 7px)}
html.lk-d .dkg:hover i{background:repeating-linear-gradient(90deg,#1d4ed8 0 4px,transparent 4px 7px)}
html.lk-n #side,html.lk-l #side,html.lk-d #side{z-index:48}
html.lk-d body.rh #side{z-index:48}
/* dock look + hidden side bar: behave like the classic hidden bar (edge strip + Home/Companies tabs); hovering the strip slides the dock out */
html.lk-d body.rh:not(.ro) #side{top:0!important;bottom:0!important;left:0!important;width:56px!important;height:auto!important;max-height:none!important;border:0!important;border-radius:0!important;box-shadow:none!important;background:transparent!important;padding:10px 0 12px!important}
html.lk-d body.rh.ro #side{top:var(--dk,78px);bottom:auto;left:12px;width:58px;height:auto;border:1px solid #dbe1ee;border-radius:18px;background:#fff;box-shadow:0 10px 30px rgba(20,32,58,.22);padding:10px 0}
html.lk-d body.rh.ro #side .rb#rhome,html.lk-d body.rh.ro #side .rb#rco{position:relative;left:auto;top:auto;width:42px;height:42px;border:0;border-radius:11px;background:transparent;color:var(--sf);box-shadow:none}
html.lk-d body.rh.ro #side .rb#rhome:hover,html.lk-d body.rh.ro #side .rb#rco:hover{background:#e6eeff;color:#1d4ed8}
html.lk-d body.rh.ro #side .sf{opacity:1;pointer-events:auto;transform:none}
html.lk-d #setw:has(#smenu:not([hidden])) #sbtn:after,html.lk-d #uw:has(#umenu:not([hidden])) #ubtn:after{display:none!important}
html.lk-d #side .rb[data-l]:hover:after{display:block}
}

/* ---- smooth page-to-page flow (Home <-> company) ---- */
@view-transition{navigation:auto}
header{view-transition-name:tfhead}#side{view-transition-name:tfside}
::view-transition-group(*){animation-duration:.5s;animation-timing-function:cubic-bezier(.22,.8,.25,1)}
::view-transition-old(root){animation:tfOut .3s ease both}
::view-transition-new(root){animation:tfIn .55s cubic-bezier(.22,.8,.25,1) both}
@keyframes tfOut{to{opacity:0;transform:translateX(-18px) scale(.992)}}
@keyframes tfIn{from{opacity:0;transform:translateX(26px) scale(1.004)}}
@media(prefers-reduced-motion:reduce){@view-transition{navigation:none}}

.colgrip{position:absolute;right:-3px;top:0;bottom:0;width:8px;cursor:col-resize;z-index:2;touch-action:none}.colgrip:hover,.colgrip:active{background:linear-gradient(90deg,transparent 3px,var(--pri,#4f46e5) 3px,var(--pri,#4f46e5) 5px,transparent 5px)}table.mhcol td,table.mhcol th{overflow-wrap:anywhere}
body.lsc #gtabs,body.lsc #strow{display:none!important}
#side .rlogo{width:auto!important;height:auto!important;margin:4px 0 10px!important}#side .rlogo svg.tfm{width:42px;height:42px;display:block;filter:drop-shadow(0 4px 8px rgba(0,0,0,.3))}
header .tfh{width:40px;height:40px;flex:none;margin-right:2px;transition:width .24s cubic-bezier(.4,0,.2,1),height .24s cubic-bezier(.4,0,.2,1)}body.hs header .tfh{width:28px;height:28px}
@media(max-width:700px){header .tfh{display:none}}

/* ---- Help dock ---- */
#hpdlg{width:min(1180px,97vw);height:min(88vh,860px);padding:0;display:none;flex-direction:column;overflow:hidden;resize:both}#hpdlg[open]{display:flex}
#hpdlg .hph{display:flex;gap:10px;align-items:center;padding:12px 16px;margin:0;flex:none;position:static;border:0;background:linear-gradient(120deg,#0f2a5c,#1d4694 60%,#0e6b4f);color:#fff}
#hpdlg .hph .hpb{display:flex;align-items:center;gap:10px;font:800 17px/1 system-ui;white-space:nowrap}#hpdlg .hph .hpb svg{width:30px;height:30px}#hpdlg .hph .hpb small{display:block;font:500 11px/1.2 system-ui;opacity:.75;margin-top:2px}
#hpdlg .hph input{flex:1;min-width:120px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.3);color:#fff;border-radius:10px;padding:9px 12px}#hpdlg .hph input::placeholder{color:rgba(255,255,255,.75)}
#hpdlg .hph button{background:rgba(255,255,255,.14);border-color:rgba(255,255,255,.3);color:#fff}#hpdlg .hph button:hover{background:rgba(255,255,255,.26)}.hpbr{display:none}
.hpb2{display:grid;grid-template-columns:240px minmax(0,1fr);flex:1;min-height:0}
#hpside{overflow:auto;padding:12px 10px;border-right:1px solid var(--line);background:var(--col)}
#hpside button{all:unset;box-sizing:border-box;display:flex;align-items:center;gap:10px;width:100%;padding:9px 10px;border-radius:10px;cursor:pointer;font-size:13.5px;color:var(--ink);margin-bottom:2px}#hpside button:hover{background:color-mix(in srgb,var(--pri) 10%,transparent)}#hpside button.on{background:var(--pri);color:var(--priInk,#fff);font-weight:600}#hpside button i{font-style:normal;font-size:17px;width:22px;text-align:center}
#hpmain{overflow:auto;padding:26px 34px 60px;scroll-behavior:smooth}.hpa{max-width:880px;margin:0 auto;font-size:14.5px;line-height:1.6}
.hpa h1{font-size:28px;margin:6px 0 14px;letter-spacing:-.01em}.hpa h3{margin:24px 0 6px;font-size:16px}.hpa p{margin:6px 0}.hpa .lead{font-size:16px;color:var(--mut);margin-bottom:16px}
.hpk{width:54px;height:54px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:28px;background:linear-gradient(135deg,#e0e9ff,#d6f5e4);box-shadow:0 6px 16px -8px rgba(20,40,90,.4)}
.hpt{display:flex;gap:10px;margin:14px 0;padding:11px 14px;border-radius:12px;background:color-mix(in srgb,var(--pri) 8%,var(--card));border:1px solid color-mix(in srgb,var(--pri) 25%,var(--line))}.hpt i{font-style:normal}.hpt-warn{background:#fff4d6;border-color:#f3d37a;color:#6b4a00}.hpt-tip{background:#e9f8ef;border-color:#b6e5c8;color:#16452a}
.hpg{display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:10px}.hpn{padding:11px 13px;border:1px solid var(--line);border-radius:12px;background:var(--card)}.hpn>b{display:block;margin-bottom:2px;color:var(--pri)}.hpn span{font-size:13px;color:var(--mut)}
.hpg3{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:12px;margin:12px 0}.hpc{padding:14px;border:1px solid var(--line);border-radius:14px;background:var(--card);box-shadow:0 8px 20px -16px rgba(20,40,90,.6)}.hpc h4{margin:0 0 6px;font-size:15px}.hpc p{margin:0;font-size:13px;color:var(--mut)}
.hpq b{display:block;margin-top:14px}.hpq p{margin:3px 0 0;color:var(--mut)}
.hpres{all:unset;box-sizing:border-box;display:block;width:100%;padding:12px 14px;border:1px solid var(--line);border-radius:12px;margin:8px 0;cursor:pointer;background:var(--card)}.hpres:hover{border-color:var(--pri)}.hpres b{display:block;margin-bottom:3px}.hpres span{font-size:13px;color:var(--mut)}.hpres mark{background:#fde68a;color:inherit;border-radius:3px;padding:0 1px}
.mk{container-type:inline-size;position:relative;aspect-ratio:16/10;margin:12px 0 18px;border:1px solid var(--line);border-radius:16px;background:#f1f3f9;overflow:hidden;font:600 1.15cqi/1 system-ui;color:#344}
.mk>*{position:absolute;box-sizing:border-box}.mk-s{left:0;top:0;bottom:0;width:5.2%;background:#0f2a5c;display:flex;flex-direction:column;align-items:center;gap:3.2cqi;padding-top:4cqi}.mk-s i{width:2.2cqi;height:2.2cqi;border-radius:.6cqi;background:rgba(255,255,255,.7)}.mk-s u{width:2cqi;height:2cqi;border-radius:50%;background:#6366f1;margin-top:auto;margin-bottom:2cqi}
.mk-h{left:5.2%;right:0;top:0;height:11%;background:#0f2a5c;color:#fff;display:flex;align-items:center;gap:1.4cqi;padding:0 1.6cqi}.mk-h .lg{width:3cqi;height:3cqi;border-radius:.8cqi;background:linear-gradient(135deg,#2557b5,#081a40);border:1px solid rgba(255,255,255,.4)}.mk-h .ti{font-size:1.9cqi;font-weight:800}.mk-h .vs{display:flex;background:rgba(255,255,255,.12);border-radius:.8cqi;overflow:hidden;margin-left:1cqi}.mk-h .vs b{padding:.8cqi 1.2cqi}.mk-h .vs b:first-child{background:#fff;color:#0f2a5c}.mk-h .se{background:rgba(255,255,255,.14);padding:.9cqi 2cqi .9cqi 1.2cqi;border-radius:.8cqi;min-width:10cqi;opacity:.8}.mk-h .pb{display:flex;margin-left:auto}.mk-h .pb i{width:2.6cqi;height:2.6cqi;border-radius:50%;border:.3cqi solid #0f2a5c;margin-left:-.7cqi}.mk-h .be{width:2.2cqi;height:2.2cqi;border-radius:50%;background:rgba(255,255,255,.2)}.mk-h .nt{background:#fff;color:#0f2a5c;padding:.9cqi 1.2cqi;border-radius:.8cqi}.mk-h .ac{width:2.6cqi;height:2.6cqi;border-radius:50%;background:#e11d48}
.mk-g{left:6.5%;top:15%;display:flex;gap:.8cqi}.mk-g b{padding:.8cqi 1.4cqi;border-radius:.8cqi .8cqi 0 0;background:#e3e7f2}.mk-g b.on{background:#fff;box-shadow:inset 0 .3cqi 0 #6366f1}
.mk-st{left:6.5%;top:22%;display:flex;gap:.8cqi}.mk-st b{padding:1cqi 1.4cqi;border-radius:.9cqi;background:color-mix(in srgb,var(--c) 18%,#fff);border:1px solid color-mix(in srgb,var(--c) 45%,#fff)}
.mk-sel{left:6.5%;right:1.4%;top:31%;height:6.6%;border:.25cqi solid #4f46e5;border-radius:1cqi;background:#fff;display:flex;align-items:center;gap:1.2cqi;padding:0 1.4cqi}.mk-sel span{border:1px solid #dde;border-radius:.7cqi;padding:.7cqi 1cqi;font-weight:500}
.mk-th{left:6.5%;right:1.4%;top:40%;height:5.5%;background:#fff;border-bottom:1px solid #dde;display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr 1fr .7fr;align-items:center;padding:0 1.4cqi;color:#667;font-weight:700}
.mk-gr{left:6.5%;right:1.4%;top:46.5%;height:6%;background:#cfdcff;border-left:.6cqi solid #3b66d9;display:flex;align-items:center;gap:1.4cqi;padding:0 1.2cqi}.mk-gr span{background:#fff;padding:.5cqi 1cqi;border-radius:.7cqi;font-weight:700}
.mk-r{left:6.5%;right:1.4%;height:6%;background:#fff;border-bottom:1px solid #eef;display:grid;grid-template-columns:1.6fr 1fr 1fr 1fr 1fr .7fr;align-items:center;padding:0 1.4cqi;font-weight:500}.mk-r:nth-of-type(8){top:53%}.mk-r:nth-of-type(9){top:59.5%}.mk-r:nth-of-type(10){top:66%}.mk-r em{font-style:normal;background:color-mix(in srgb,var(--c) 35%,#fff);border-radius:.6cqi;padding:.6cqi .8cqi;font-size:.9cqi;font-weight:700;width:max-content}.mk-r u{width:2.2cqi;height:2.2cqi;border-radius:50%;background:#16a34a}
.mk-dk{right:0;top:44%;display:flex;flex-direction:column;gap:.8cqi}.mk-dk i{font-style:normal;width:4cqi;padding:1cqi 0;text-align:center;border-radius:1cqi 0 0 1cqi;background:#d6f0f7;box-shadow:0 .4cqi 1cqi rgba(0,0,0,.18)}.mk-dk i+i{background:#e5dcff}
.pin{all:unset;box-sizing:border-box;position:absolute;width:3cqi;height:3cqi;min-width:18px;min-height:18px;margin:-1.5cqi 0 0 -1.5cqi;border-radius:50%;background:#e11d48;color:#fff;font:800 max(10px,1.4cqi)/1 system-ui;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 0 0 .4cqi rgba(255,255,255,.9),0 4px 10px rgba(0,0,0,.35);z-index:3;transition:transform .15s}.pin:hover,.pin.hot{transform:scale(1.25);background:#16a34a;animation:hpp 1s ease-out 2}@keyframes hpp{50%{box-shadow:0 0 0 1.4cqi rgba(22,163,74,.25),0 4px 10px rgba(0,0,0,.35)}}
.hpleg{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:8px}.hpleg .lg{display:flex;gap:10px;padding:10px 12px;border:1px solid var(--line);border-radius:12px;background:var(--card);cursor:pointer;transition:border-color .15s,box-shadow .15s}.hpleg .lg:hover,.hpleg .lg.hot{border-color:#16a34a;box-shadow:0 6px 18px -10px rgba(22,163,74,.7)}.hpleg .lg>b{flex:none;width:26px;height:26px;border-radius:50%;background:#e11d48;color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px}.hpleg h4{margin:0 0 2px;font-size:14px}.hpleg p{margin:0;font-size:12.5px;color:var(--mut);line-height:1.45}
@media(max-width:800px){#hpdlg{width:100vw;height:100dvh;max-width:100vw;max-height:100dvh;border-radius:0;resize:none}.hpb2{grid-template-columns:minmax(0,1fr)}#hpside{display:none;border-right:0;border-bottom:1px solid var(--line);max-height:48vh}#hpdlg.nav #hpside{display:block}.hpbr{display:inline-block}#hpdlg .hph{flex-wrap:wrap}#hpdlg .hph input{order:5;flex-basis:100%}#hpmain{padding:18px 14px 50px}.hpa h1{font-size:23px}.hpleg{grid-template-columns:minmax(0,1fr)}.mk{aspect-ratio:4/3}}
@media(prefers-color-scheme:dark){.hpt-warn{background:#3a2e08;border-color:#6b5513;color:#f3d98a}.hpt-tip{background:#10301f;border-color:#1f5b3a;color:#b6ecca}.mk{background:#202636}.mk-th,.mk-r,.mk-sel,.mk-g b.on{background:#161a24;color:#dde}}
header .bk{display:flex;padding-right:18px;margin-right:6px;border-right:1px solid color-mix(in srgb,var(--hfg) 28%,transparent)}header .bk1{display:flex;align-items:center;gap:10px}header .bk .brand{border:0;padding:0;margin:0}html.lk-l header .bk{border-right-color:#dde2ee}
@media(min-width:701px){html body header{padding-right:230px!important}header .bk2{position:absolute;top:5px;right:14px;display:flex;align-items:center;gap:8px;z-index:2}header .bk2 #bell{padding:3px 9px}html body.hs header{padding-right:360px!important}body.hs header .bk2{top:3px}html:not(.indesk) header #pres{position:absolute;top:46px;right:16px;z-index:2}body.hs header #pres{top:4px;right:214px}}
@media(max-width:700px){header .bk,header .bk1,header .bk2{display:contents}}
html.indesk header .bk2{display:none!important}html.indesk body header{padding-right:20px!important}
@media(min-width:701px){header .fl{display:flex;flex:1 1 380px;min-width:0;max-width:760px;gap:10px;align-items:center}header .fl #q{flex:1 1 110px;min-width:80px;width:auto}header .fl select{flex:0 1 auto;min-width:64px;max-width:190px;text-overflow:ellipsis}}
@media(max-width:700px){header .fl{display:contents}header .fl #q{min-width:140px}}
.fme{font-weight:600;white-space:nowrap;cursor:pointer;border-radius:999px;flex:0 0 auto}header .fme{padding-left:16px;padding-right:16px;opacity:1}header .fme:hover{border-color:var(--hfg)}header .fme.on,html.lk-l header .fme.on{background:#2557b5;border-color:#7fb0ff;color:#fff;box-shadow:0 0 0 2px rgba(127,176,255,.35)}#fa{display:none!important}

#mcd{width:min(680px,94vw);max-height:92vh;padding:0;border:0;border-radius:22px;overflow:hidden;resize:none!important;box-shadow:0 30px 90px rgba(10,25,60,.45);background:var(--card)}
#mcd::backdrop{background:radial-gradient(circle at 30% 20%,rgba(37,87,181,.55),rgba(8,20,48,.78));backdrop-filter:blur(5px)}
#mcd .mch{position:relative;padding:26px 28px 22px;color:#fff;background:linear-gradient(135deg,#12337a 0%,#2557b5 55%,#16a34a 140%);overflow:hidden}
#mcd .mch::after{content:"";position:absolute;right:-40px;top:-60px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(255,214,102,.55),rgba(255,214,102,0) 70%)}
#mcd .mch small{display:block;font-size:12.5px;letter-spacing:.08em;text-transform:uppercase;opacity:.8}
#mcd .mch h2{margin:4px 0 2px;font-size:26px;line-height:1.15}
#mcd .mch p{margin:0;opacity:.88;font-size:14px;position:relative;z-index:1}
#mcd .mcs{position:absolute;right:22px;top:18px;font-size:40px;z-index:1;filter:drop-shadow(0 4px 10px rgba(0,0,0,.25))}
#mcd .mcb{padding:16px 22px 6px;max-height:calc(92vh - 230px);overflow:auto;display:grid;gap:14px}
#mcd .mcc{border:1px solid var(--line);border-radius:16px;padding:14px 16px;background:var(--col,var(--card))}
#mcd .mcc h3{margin:0 0 8px;font-size:14px;display:flex;align-items:center;gap:8px}
#mcd .mcc h3 i{width:26px;height:26px;border-radius:8px;display:grid;place-items:center;font-style:normal;font-size:14px;background:#e8efff}
#mcd .mcc.ok{border-color:#bfe6cb;background:linear-gradient(135deg,#f1fbf4,#fff)}#mcd .mcc.ok h3 i{background:#d7f3df}
#mcd .mcc.warn{border-color:#f3d9a0;background:linear-gradient(135deg,#fff9e8,#fff)}#mcd .mcc.warn h3 i{background:#ffe9b3}
#mcd .mcc.bad{border-color:#f0c4c4;background:linear-gradient(135deg,#fff3f3,#fff)}#mcd .mcc.bad h3 i{background:#fdd}
#mcd .mcr{display:flex;gap:10px;align-items:flex-start;justify-content:space-between;padding:9px 0;border-top:1px dashed var(--line)}#mcd .mcr:first-of-type{border-top:0}
#mcd .mcr b{display:block;font-size:14px}#mcd .mcr small{color:var(--mut);font-size:12px}
#mcd .mcd2{white-space:nowrap;font-size:12px;font-weight:600;padding:3px 9px;border-radius:999px;background:#eef2fb;color:#2b3a67}#mcd .mcd2.late{background:#fde8e8;color:#b42318}
#mcd .mcst{display:flex;gap:6px;flex-wrap:wrap;margin-top:7px}
#mcd .mcst button{font:inherit;font-size:12px;font-weight:600;padding:4px 11px;border-radius:999px;border:1px solid var(--pc,#94a3b8);background:transparent;color:var(--pc,#475569);cursor:pointer}
#mcd .mcst button.on{background:var(--pc,#475569);color:#fff}
#mcd .mcf{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}#mcd .mcf select,#mcd .mcf input{font:inherit;padding:7px 10px;border-radius:10px;border:1px solid var(--line);background:var(--card);color:var(--ink)}#mcd .mcf select{flex:1 1 200px;min-width:0}#mcd .mcf input{width:84px}
#mcd .mcft{padding:14px 22px 20px;display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;border-top:1px solid var(--line)}
#mcd .mcft span{color:var(--mut);font-size:12.5px;flex:1 1 200px}
#mcd .mcgo{font:inherit;font-weight:700;font-size:15px;padding:12px 26px;border-radius:999px;border:0;cursor:pointer;color:#fff;background:linear-gradient(135deg,#2557b5,#16a34a);box-shadow:0 10px 24px rgba(37,87,181,.35)}
#mcd .mcgo:hover{filter:brightness(1.06)}
@media(max-width:700px){#mcd{width:100vw!important;max-width:100vw!important;border-radius:0}#mcd .mcb{max-height:calc(100dvh - 250px)}}
</style><script>try{sessionStorage.removeItem("tf_enter")}catch(e){}</script></head><body><svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><linearGradient id="tfbg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2557b5"/><stop offset=".55" stop-color="#12337a"/><stop offset="1" stop-color="#081a40"/></linearGradient><radialGradient id="tfhl" cx=".2" cy=".08" r=".75"><stop offset="0" stop-color="#fff" stop-opacity=".22"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient><linearGradient id="tfgr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6ee7a0"/><stop offset="1" stop-color="#16a34a"/></linearGradient><linearGradient id="tfsk" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#bae6fd"/><stop offset="1" stop-color="#38bdf8"/></linearGradient><filter id="tfsh" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="9" stdDeviation="8" flood-color="#020b22" flood-opacity=".35"/></filter></defs></svg>
<header>
 <span class="bk"><span class="bk1"><svg class="tfm tfh" viewBox="0 0 512 512" aria-hidden="true"><rect width="512" height="512" rx="116" fill="url(#tfbg)"/><rect width="512" height="512" rx="116" fill="url(#tfhl)"/><g transform="translate(0.00,0.00) scale(1.0)"><g filter="url(#tfsh)"><rect x="84" y="118" width="226" height="68" rx="34" fill="#fff"/><rect x="150" y="222" width="226" height="68" rx="34" fill="url(#tfsk)"/><path d="M216 326 H372 L426 360 L372 394 H216 a34 34 0 0 1 0-68z" fill="url(#tfgr)"/></g><path d="M120 152 l22 22 l40 -44" fill="none" stroke="#12337a" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/></g></svg><span class="brand"><b>TaskFlow</b><small>Powered by GURUCADD <span style="color:#22c55e;text-shadow:0 0 6px #22c55e;font-weight:900;letter-spacing:0;font-size:15px;line-height:0;vertical-align:-1px;margin-left:2px">»</span></small></span></span><span class="bk2"><span id="live" style="font-size:12px;color:var(--mut)">○ …</span><button id="bld" type="button" title="Version of this page. Tap to reload when a newer one is available." style="font-size:11px;padding:2px 7px;opacity:.8"></button><button id="bell" title="Flow – your notifications">🔔<b id="bn" hidden></b></button></span></span><span id="wlogo"></span><h1><span id="wname"></span></h1>
 <button id="mfb" class="mobonly" type="button" title="Search and filters">⌕ Filter</button><span class="tabs"><button id="vl" class="on">List</button><button id="vb">Board</button><button id="vg">Gantt</button></span>
 <button id="dv" title="Choose which view opens first"></button>
 <span class="fl"><input id="q" placeholder="Search tasks"><select id="fs"></select><select id="fp"></select><select id="fa" hidden></select><button type="button" id="fme" class="fme" title="Show only the tasks assigned to me, including ones I help with">Tasks For Me</button></span>
 <span class="sp"></span>
 <span id="pres" title="Online now"></span>
 <button class="p" id="add">+ New task</button>
</header>
<div id="sidetabs"><button id="tbtn" class="stab" style="--pc:#0891b2" title="Tools: containers, converter, wiki"><i>📐</i><span>Tools</span></button><button id="mgb" class="stab" style="--pc:#7c3aed" title="Messages between team members"><i>💬</i><span>Chat</span><b id="mgn" hidden></b></button></div>
<nav class="gtabs" id="gtabs" hidden></nav>
<div id="strow" style="display:flex;align-items:center;flex-wrap:wrap"><div class="stats" id="stats"></div><div class="gcw" id="gcb" hidden><button type="button" class="gcm">Collapse ▾</button><div class="gcl"><button data-gc="c">Collapse all</button><button data-gc="e" title="Open groups, keep Completed folders closed">Expand</button><button data-gc="a" title="Open groups and Completed folders">Expand all</button></div></div></div>
<aside id="side"><span class="rpill"></span><span class="dkg" title="Drag up or down to move. Double-click to reset."><i></i></span><a class="rlogo" href="index.php?home=1" title="TaskFlow"><svg class="tfm" viewBox="0 0 512 512" aria-hidden="true"><rect width="512" height="512" rx="116" fill="url(#tfbg)"/><rect width="512" height="512" rx="116" fill="url(#tfhl)"/><g transform="translate(0.00,0.00) scale(1.0)"><g filter="url(#tfsh)"><rect x="84" y="118" width="226" height="68" rx="34" fill="#fff"/><rect x="150" y="222" width="226" height="68" rx="34" fill="url(#tfsk)"/><path d="M216 326 H372 L426 360 L372 394 H216 a34 34 0 0 1 0-68z" fill="url(#tfgr)"/></g><path d="M120 152 l22 22 l40 -44" fill="none" stroke="#12337a" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/></g></svg></a>
<a class="rb" id="rhome" data-l="Home" href="index.php?home=1" title="Home: my tasks"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/></svg></a>
<button class="rb" id="rco" data-l="Companies" title="Companies"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/><path d="M3 13h18"/></svg></button>
<div style="display:none"><div id="vname"></div><nav id="wlinks"></nav></div>
<button class="rb mobonly" id="rtools" data-l="Tools" title="Tools: containers, converter, wiki"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17L17 3l4 4L7 21H3z"/><path d="M13 7l4 4M8 12l2 2"/></svg></button>
<button class="rb mobonly" id="rchat" data-l="Chat" title="Messages"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 01-11.7 7L3 21l2-5.6A8 8 0 1121 12z"/></svg><b id="rchn" class="apn" hidden></b></button>
<div class="sf"><button class="rb dskonly" id="rhide" data-l="Hide" type="button" title="Hide / pin the side bar"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6l-6 6 6 6"/><path d="M19 5v14"/></svg></button><span id="setw"><button id="sbtn" class="rb" data-l="Settings" aria-haspopup="true" title="Settings"><svg viewBox="0 0 24 24" width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 00.3 1.8l.1.1a2 2 0 11-2.8 2.8l-.1-.1a1.7 1.7 0 00-1.8-.3 1.7 1.7 0 00-1 1.5V21a2 2 0 11-4 0v-.1a1.7 1.7 0 00-1.1-1.5 1.7 1.7 0 00-1.8.3l-.1.1a2 2 0 11-2.8-2.8l.1-.1a1.7 1.7 0 00.3-1.8 1.7 1.7 0 00-1.5-1H3a2 2 0 110-4h.1a1.7 1.7 0 001.5-1.1 1.7 1.7 0 00-.3-1.8l-.1-.1a2 2 0 112.8-2.8l.1.1a1.7 1.7 0 001.8.3H9a1.7 1.7 0 001-1.5V3a2 2 0 114 0v.1a1.7 1.7 0 001 1.5 1.7 1.7 0 001.8-.3l.1-.1a2 2 0 112.8 2.8l-.1.1a1.7 1.7 0 00-.3 1.8V9a1.7 1.7 0 001.5 1H21a2 2 0 110 4h-.1a1.7 1.7 0 00-1.5 1z"/></svg></button>
  <div id="smenu" hidden>
   <button id="gm">📁 Groups</button>
   <button id="sm">🏷 Statuses</button>
   <button id="pm">🎨 Appearance</button>
   <button id="cm">➕ Column</button>
   <button id="dm">✏ Drawing types</button>
   <button id="rm" hidden>🧾 Worklog &amp; invoice</button>
   <button id="ivm" hidden>📋 Invoice tracking</button>
   <button id="xm">⬇ Export to Excel</button>
   <div id="tmw" hidden><button id="tm">👥 Users ▸</button><div class="tms"><button id="tm1"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:5px"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg>Users Management</button><button id="tm2">🔐 Access Control</button></div></div>
   <button id="ghm" hidden>🧭 Guest home</button>
   <button id="om" hidden>⚠ Overlap rules</button>
   <button id="apm" hidden>✅ Approvals <b id="apn" class="apn" hidden></b></button>
  </div></span><button id="wm" hidden style="display:none" title="Manage companies and logos">Manage</button><span id="uw"><button id="ubtn" aria-haspopup="true" title="Account"><span id="uini"></span></button><div id="umenu" hidden><div class="umh"><b id="uname"></b><small id="who"></small></div><a href="#" id="mhm">⏱ My hours check</a><div id="rpw" hidden><a href="#" id="rpt" aria-expanded="false">📑 Reports <span id="rpa" style="float:right">▸</span></a><div id="rpl"><a href="#" id="hrm" hidden>📊 Hours report</a><a href="#" id="srm" hidden>📋 Status report</a><a href="#" id="alg" hidden>🧾 Usage log</a></div></div><a href="#" id="ainst" hidden>📲 Install app</a><a href="#" id="amm" hidden>🗂 Manage companies</a><a href="#" id="anf">🔔 Notifications: …</a><a href="#" id="abk" hidden>💾 Backups</a><a href="https://claude.ai/artifact/KwN83RddD74EoffvwoXrgC" target="_blank" rel="noopener" id="tfwl">💡 TFW Ideas</a><a href="#" id="hpm">❓ Help &amp; guide</a><a href="#" id="ubl" title="Version of this page. Tap to reload and get the newest version.">↻ Build …</a><a href="login.php?logout=1">⎋ Log out</a></div></span></div></aside>

<div id="cpanel" aria-label="Companies"><div class="cph"><b>Companies</b><button id="cpx" title="Close">✕</button></div><input id="cps" placeholder="Search companies" autocomplete="off"><div id="cpl"></div></div>
<div id="bar" hidden></div>
<aside id="msgw" hidden><div class="mgh"><b style="flex:1">💬 Messages</b><button id="mgx" title="Close">✕</button></div>
 <div class="mgbody"><div id="mgu"></div><div class="mgr"><div id="mgt" class="mgtt"></div><div id="mgm"></div><div class="mgin" style="position:relative"><div id="mge" hidden></div><button id="mgem" type="button" title="Emoji">😊</button><textarea id="mgi" rows="1" placeholder="Write a message…" title="Enter to send, Shift+Enter for a new line" maxlength="2000"></textarea><button class="snd" id="mgs" title="Send (Enter)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.4 20.4 21 12 3.4 3.6 3 10l12 2-12 2z"/></svg></button></div></div></div></aside>
<aside id="rside" hidden><div class="rh"><b style="flex:1">📐 Tools</b><button id="rsx" title="Close">✕</button></div>
 <div class="rtabs"><button data-rt="ct" class="on">Containers</button><button data-rt="cv">Convert</button><button data-rt="wk">Wiki</button></div>
 <div id="rt_ct" class="rbody">
  <label>Container<select id="ct_t"><option value="6.058">20 ft (6.058 m)</option><option value="12.192">40 ft (12.192 m)</option><option value="2.991">10 ft (2.991 m)</option><option value="13.716">45 ft (13.716 m)</option><option value="c">Custom length…</option></select></label>
  <label id="ct_cw" hidden>Custom length (mm)<input type="number" id="ct_c" step="any" min="0"></label>
  <div class="two"><label>How many (×)<input type="number" id="ct_n" value="2" min="1" step="1"></label><label>Gap between (mm)<input type="number" id="ct_g" value="76" min="0" step="any"></label></div>
  <label>Extra length (mm)<input type="number" id="ct_x" value="0" step="any"></label>
  <div id="ct_o" class="rres"></div>
  <small style="color:var(--mut)">Gap is added once per join: 2 containers = 1 gap, 3 = 2 gaps. Example: 2 × 20 ft + 76 mm = 12.192 m = one 40 ft.</small>
 </div>
 <div id="rt_cv" class="rbody" hidden>
  <div class="two"><label>Value<input type="number" id="cv_v" step="any" value="1"></label><label>From<select id="cv_u"></select></label></div>
  <div id="cv_o" class="rres"></div>
 </div>
 <div id="rt_wk" class="rbody" hidden>
  <div style="text-align:center;padding:18px 8px"><div style="font-size:34px">📚</div><b style="display:block;margin:6px 0 4px">Wiki</b><div style="color:var(--mut);font-size:13px;margin-bottom:12px">Drawing rules, standards and know-how, grouped, tagged and searchable.</div><button class="p" id="wk_open">Open the wiki</button></div>
 </div>
</aside>
<div id="pbox"></div>
<dialog id="wkdlg"><div class="acts wkh"><b>📚 Wiki</b><input id="wkq" type="search" placeholder="Search rules, sizes, tags…" autocomplete="off"><button id="wkbr" class="wkbr" type="button">☰ Browse</button><button class="p" id="wknew" type="button">+ New page</button><button id="wkx" type="button">Close</button></div>
 <div class="wkb"><nav id="wkside"></nav><main id="wkmain"></main></div></dialog>
<div id="wkzoom" hidden></div>
<dialog id="hpdlg"><div class="acts hph"><span class="hpb"><span>❓ Help &amp; guide<small>Every feature, and the names we use</small></span></span><input id="hpq" type="search" placeholder="Search help: hours, group, wiki…" autocomplete="off"><button id="hpbr" class="hpbr" type="button">☰ Topics</button><button id="hpx" type="button">Close</button></div>
 <div class="hpb2"><nav id="hpside"></nav><main id="hpmain"></main></div></dialog>

<div id="main"></div>

<dialog id="dlg">
 <label>Title</label><input id="t">
 <label>Order number</label><input id="on">
 <label>Customer name</label><input id="pj" list="pjl"><datalist id="pjl"></datalist>
 <label>Drawing type</label><select id="dt"></select>
 <label>Start date</label><input id="sd" type="date">
 <label>End date</label><input id="du" type="date">
 <label>Status</label><select id="st"></select>
 <label>Assigned to</label><select id="as"></select>
 <div id="dcoRow" style="margin-top:6px;display:none;gap:8px;align-items:center"><button type="button" id="dcob" class="coadd">＋ More people</button><small id="dcot" style="color:var(--mut)"></small></div>
 <div id="ovn" style="display:none;margin-top:8px;padding:8px 10px;border:1px solid #dc2626;border-radius:8px;color:#dc2626;font-size:12px;white-space:pre-line"></div>
 <label>Group</label><select id="gp"></select>
 <label>Notes</label><textarea id="nt" rows="3"></textarea>
 <div id="xf"></div>
 <div id="thr" hidden><b class="thh">💬 Discussion</b><div id="thl"></div>
  <div class="cmp"><div class="cmo"><select id="cto"></select><select id="ckind"><option value="comment">💬 Comment</option><option value="instruction">📌 Instruction</option></select></div>
  <div class="cmr"><textarea id="cbody" rows="1" placeholder="Write a message…"></textarea><button type="button" id="csend" class="snd" title="Send (Ctrl+Enter)"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.4 20.4 21 12 3.4 3.6 3 10l12 2-12 2z"/></svg></button></div><div class="hint">Ctrl+Enter to send</div></div></div>
 <div class="acts"><button id="del" style="display:none">Delete</button><button id="tlb" type="button" title="Log hours for this task">⏱ Hours</button><span class="sp"></span><button id="cx">Cancel</button><button class="p" id="sv">Save</button></div>
</dialog>

<dialog id="tdlg" style="width:min(900px,96vw)"><div class="acts" style="margin:0"><b style="flex:1">Users</b></div>
 <div class="uh"><span>Full name</span><span>Username</span><span>Password</span><span>User type</span><span></span></div>
 <div id="tlist"></div>
 <small style="color:var(--mut)">Saved passwords are stored scrambled, so they cannot be shown. Type a new one and press Reset password.</small>
 <div class="acts"><button class="p" id="tnew">+ New user</button><span class="sp"></span><button id="tc">Close</button></div>
</dialog>
<dialog id="acdlg" style="width:min(900px,96vw)"><b>Access Control</b> <small style="color:var(--mut)">choose what each person can do in each company. Admins always have full access everywhere; guests can only be view-only.</small><div id="acbody" style="overflow:auto;max-height:60vh;margin-top:8px"></div><div class="acts"><button class="p" id="acsave">Save access</button><span class="sp"></span><button id="acx">Close</button></div>
</dialog>
<div id="copop" hidden><div class="coh">Also working on this task</div><div id="col"></div></div>
<dialog id="apdlg" style="width:min(720px,96vw)"><b>Approvals</b> <small style="color:var(--mut)">Members ask here before deleting a group, status, column or drawing type.</small><div id="apl" style="margin-top:8px"></div><div class="acts"><span class="sp"></span><button id="apx">Close</button></div></dialog>
<dialog id="bkdlg" style="width:min(640px,96vw)"><div class="acts" style="margin:0 0 6px"><b style="flex:1">💾 Backups</b><button id="bkx">Close</button></div>
 <div id="bks" style="font-size:13px;margin-bottom:8px"></div>
 <div class="acts" style="flex-wrap:wrap"><button class="p" id="bkn">Back up now</button><button id="bkm">Back up and email me a copy</button></div>
 <div id="bkl" style="max-height:30vh;overflow:auto;margin-top:8px"></div>
 <div style="border-top:1px solid var(--line);margin-top:10px;padding-top:8px"><b>⏰ Hours reminders (8am &amp; 3pm Dhaka)</b>
  <div style="font-size:12px;color:var(--mut);margin:4px 0">In cPanel → Cron Jobs, set the time to <b>Every 30 minutes</b> (<code>*/30</code>), then paste this as the Command. Nothing to edit:</div>
  <textarea id="remc" readonly rows="2" style="width:100%;font:12px monospace" onclick="this.select()"></textarea>
  <div class="acts" style="flex-wrap:wrap;margin-top:4px"><button id="remcp">Copy command</button><button id="remam">Send test: morning</button><button id="pshtst">Send test phone notification</button><button id="rempm">Send test: afternoon</button></div></div>
 <div style="margin-top:12px;border-top:1px solid var(--line);padding-top:8px"><b>📊 Worklog (last 15 days, all companies)</b><div id="wls" style="font-size:13px;margin:4px 0"></div>
 <div class="acts" style="flex-wrap:wrap"><button class="p" id="wln">Create worklog now</button><button id="wlm">Create and email me</button></div>
 <div id="wll" style="max-height:24vh;overflow:auto;margin-top:6px"></div></div>
 <small style="color:var(--mut)">The worklog is created twice a day and emailed each time; the last 30 are kept. A backup is saved every 3 hours. The first one each day is also emailed. Copies from the last 24 hours are kept, then one per day for 14 days. Admin only.</small>
</dialog>
<dialog id="lgdlg" style="width:min(1000px,96vw)"><div class="acts" style="margin:0 0 6px"><b style="flex:1">🧾 Usage log</b><button id="lgx">Close</button></div>
 <div id="lgs"></div>
 <div class="rctl" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center"><select id="lgk"><option value="">Logins and activity</option><option value="login">Logins only</option><option value="activity">Activity only</option></select><select id="lgu"><option value="">Everyone</option></select><select id="lgw"><option value="">All companies</option></select><label style="margin:0">From</label><input type="date" id="lgf"><label style="margin:0">To</label><input type="date" id="lgt"><button id="lgr">Refresh</button></div>
 <div id="lgl" style="max-height:52vh;overflow:auto;margin-top:8px"></div>
 <small style="color:var(--mut)">Shows the latest 300 entries that match. Entries older than 90 days are removed automatically. Admin only.</small>
</dialog>
<dialog id="uedlg"><b>Edit user</b>
 <label>Full name</label><input id="een" maxlength="100">
 <label>Username</label><input id="eeu" maxlength="40" autocomplete="off">
 <label>Email</label><input id="eee" type="email" maxlength="190" autocomplete="off" placeholder="name@example.com">
 <small style="color:var(--mut)">Change the password with Reset password in the list. User type is set in the list too.</small>
 <div class="acts"><span class="sp"></span><button id="eec">Cancel</button><button class="p" id="ees">Save</button></div>
</dialog>
<dialog id="udlg"><b>New user</b>
 <label>Full name</label><input id="un" name="nf_fullname" autocomplete="off" readonly>
 <label>Username</label><input id="uu" name="nf_user" autocomplete="off" readonly>
 <label>Email (optional)</label><input id="ue" name="nf_mail" type="email" autocomplete="off" readonly>
 <label>Password (8+ characters)</label><span class="pw"><input id="up" name="nf_pass" type="password" autocomplete="new-password" readonly><button type="button" class="eye" title="Show / hide">👁</button></span>
 <label>User type</label><select id="ur"><option value="member">Member</option><option value="guest">Guest (view only)</option><option value="admin">Admin</option></select>
 <div class="acts"><span class="sp"></span><button id="uc">Cancel</button><button class="p" id="ta">Create user</button></div>
</dialog>

<dialog id="pdlg"><b>Appearance</b>
 <div id="pprev">TaskFlow – preview text</div>
 <div style="display:flex;gap:16px;align-items:center;margin-bottom:10px"><label style="display:flex;gap:8px;align-items:center;margin:0">Bar <input type="color" id="pbg" style="width:52px;height:34px;padding:2px"></label><label style="display:flex;gap:8px;align-items:center;margin:0">Text <input type="color" id="pfg" style="width:52px;height:34px;padding:2px"></label></div>
 <div id="psw" style="display:flex;gap:8px;flex-wrap:wrap"></div>
 <label style="display:flex;gap:10px;align-items:center;margin:12px 0 0;color:var(--ink)"><span>🧭 Layout</span><select id="plk" style="flex:1"><option value="">Classic (current)</option><option value="n">Refined Navy: icons with labels</option><option value="l">Light: white bar, menu opens on hover</option><option value="d">Floating dock: slim and modern</option></select></label>
 <label style="display:flex;gap:8px;align-items:center;margin:12px 0 0;color:var(--ink)"><input type="checkbox" id="pgl"> <span>✨ Glass look <small style="color:var(--mut)">(this device only; turn off if the app feels slow)</small></span></label>
 <div class="acts"><button id="prs">Reset to default</button><span class="sp"></span><button id="pcx">Cancel</button><button class="p" id="psv">Save</button></div>
</dialog>
<dialog id="tldg"><div class="acts" style="margin:0 0 8px"><div style="flex:1;min-width:0"><b id="tlord" style="color:var(--pri)"></b><div id="tltit" style="font-weight:600;overflow:hidden;text-overflow:ellipsis"></div></div><button id="tlcl">Close</button></div>
 <div id="tll"></div>
 <div class="tlf"><input type="date" id="tld"><input type="number" id="tlh" step="0.25" min="0" max="24" placeholder="Hours"><span id="tlrw"></span><span class="otn" title="Mon to Fri: the first 8 hours of a day are regular, anything over 8 is overtime. Saturday and Sunday are all overtime.">Overtime is automatic</span><select id="tlk" hidden title="Regular or overtime"><option value="R">Regular</option><option value="OT">OT</option></select><input id="tln" placeholder="What did you do? (optional)" maxlength="255"><button class="p" id="tla">Add</button></div>
 <small style="color:var(--mut)">Hours are logged under the person the task is assigned to (you will be asked to assign someone if it has no assignee). Use quarter-hours, e.g. 1.5 = 1 h 30 min.</small>
</dialog>
<dialog id="mhdg"><div class="acts" style="margin:0 0 6px"><b style="flex:1">⏱ My hours check</b><button id="mhcl">Close</button></div>
 <div class="rctl"><label style="margin:0">From</label><input type="date" id="mhf"><label style="margin:0">To</label><input type="date" id="mht"><button id="mhtd">Today</button><button id="mhw">This week</button><button id="mhlw">Last week</button><button id="mh14">Last 14 days</button><button id="mh30">Last 30 days</button><label style="margin:0 0 0 6px;display:inline-flex;gap:4px;align-items:center"><input type="checkbox" id="mhmo"> Missed days only</label></div>
 <div class="rctl"><label style="margin:0">Target hours / day</label><input type="number" id="mhg" value="8" min="0" step="0.25" style="width:80px"><span id="mhdays"></span><select id="mhu" hidden></select></div>
 <div class="rctl"><label style="margin:0">Download</label><select id="mhdl"><option value="day">Today</option><option value="week">This week (Mon–Sun)</option><option value="lweek">Last week</option><option value="month">This month</option><option value="lmonth">Last month</option><option value="sel">Dates selected above</option></select><button id="mhdb" title="Save your hours as an Excel file laid out like the worklog">⬇ Excel</button></div>
 <div id="mhs"></div><div id="mhout"></div></dialog>
<dialog id="mhad"><b id="mhat">Add hours</b><div class="rctl" style="flex-direction:column;align-items:stretch"><label>Task<select id="mhtk"></select></label><div style="display:flex;gap:8px"><input type="number" id="mhah" step="0.25" min="0" max="24" placeholder="Hours"><span class="otn" title="Mon to Fri: the first 8 hours of a day are regular, anything over 8 is overtime. Saturday and Sunday are all overtime.">Overtime is automatic</span><select id="mhak" hidden style="width:auto"><option value="R">Regular</option><option value="OT">OT</option></select></div><input id="mhan" placeholder="Note (optional)" maxlength="255"></div><div class="acts"><span class="sp"></span><button id="mhax">Cancel</button><button class="p" id="mhaok">Add</button></div></dialog>
<dialog id="hrdg"><div class="acts" style="margin:0 0 6px"><b style="flex:1">📊 Hours report</b><button id="hrx">Close</button></div>
 <div class="rctl"><label style="margin:0">From</label><input type="date" id="hrf"><label style="margin:0">To</label><input type="date" id="hrt"><button data-hrr="today">Today</button><button data-hrr="week">This week</button><button data-hrr="lweek">Last week</button><button data-hrr="month">This month</button><button data-hrr="lmonth">Last month</button><button data-hrr="30">Last 30 days</button></div>
 <div class="hrf"><details id="hru"><summary></summary><div class="hrp"></div></details><details id="hrc"><summary></summary><div class="hrp"></div></details><details id="hrg"><summary></summary><div class="hrp"></div></details>
  <select id="hrk" title="Hour type"><option value="">Regular + OT</option><option value="R">Regular only</option><option value="OT">OT only</option></select><input id="hrq" placeholder="Search task, order, customer, note" style="min-width:200px"></div>
 <div class="rctl"><label style="margin:0">Group by</label><select id="hr1"><option value="person">Person</option><option value="company">Company</option><option value="grp">Group</option><option value="task">Task</option><option value="customer">Customer</option><option value="day">Day</option><option value="week">Week</option><option value="month">Month</option></select><label style="margin:0">then by</label><select id="hr2"><option value="">(nothing)</option><option value="person">Person</option><option value="company">Company</option><option value="grp">Group</option><option value="task">Task</option><option value="customer">Customer</option><option value="day">Day</option><option value="week">Week</option><option value="month">Month</option></select><label style="margin:0;display:inline-flex;gap:4px;align-items:center"><input type="checkbox" id="hrd"> Show every entry</label><span class="sp" style="flex:1"></span><button class="p" id="hrgo">Show report</button><button id="hrcsv">⬇ CSV</button><button id="hrxl">⬇ Excel</button></div>
 <div class="hrk" id="hrs"></div><div id="hrout"></div></dialog>
<dialog id="ghdg"><div class="acts" style="margin:0 0 8px;flex-wrap:wrap"><b style="flex:1">🧭 Guest home</b><label style="font-size:12px;color:var(--mut)">View as <select id="ghas" style="width:auto;margin:0"></select></label><button id="ghrf" style="width:auto;margin:0">↻ Refresh</button><button id="ghx" style="width:auto;margin:0">Close</button></div><small style="color:var(--mut)">This is what a guest sees when they sign in. Pick a guest to see only the companies that guest can access.</small><div id="ghb"></div></dialog>
<dialog id="srdg"><div class="acts" style="margin:0 0 6px"><b style="flex:1">📋 Task status report</b><button id="srx">Close</button></div>
 <small style="color:var(--mut)">Shows tasks completed this week, tasks ongoing now, and tasks starting in the next days (you choose how many). Set up a weekly email to guests below. It is sent by the server at the chosen Dhaka time (needs the reminder cron job from Backups).</small>
 <div id="srms" style="font-size:12px;color:var(--mut);margin-top:4px"></div><div class="srl" id="srl"></div>
 <div class="srf" id="srf" hidden><div class="row"><input id="srn" placeholder="Name, e.g. Weekly update for Bechtel" maxlength="80" style="flex:1;min-width:220px"></div>
  <div><small style="color:var(--mut)">Companies to include (none ticked = all)</small><div class="row" id="srw"></div></div>
  <div class="row"><label>Work starting in the next</label><select id="srah"></select><small id="srahn" style="color:var(--mut)"></small></div>
  <details id="srod"><summary style="cursor:pointer;font-size:13px">Order of groups in the report <span id="srotc" style="color:var(--mut)"></span></summary><div class="row" style="margin:6px 0"><small style="color:var(--mut)">Move a group up or down. The same order is used in Completed, Ongoing and Starting soon. New groups are added at the end.</small><button id="srorst" type="button">Reset to automatic</button></div><div id="srol" style="border:1px solid var(--line);border-radius:8px;padding:4px 8px"></div></details>
  <details id="srtd"><summary style="cursor:pointer;font-size:13px">Choose groups and tasks <span id="srtc" style="color:var(--mut)"></span></summary><div class="row" style="margin:6px 0"><button id="srta" type="button">Include all</button><small style="color:var(--mut)">Untick a group or a task to leave it out of the report. New tasks are included automatically.</small></div><div id="srtree" style="max-height:260px;overflow:auto;border:1px solid var(--line);border-radius:8px;padding:6px 10px;font-size:13px"></div></details>
  <div><small style="color:var(--mut)">Team members (email comes from their user details)</small><div class="row" id="sru"></div></div>
  <div><small style="color:var(--mut)">Other email addresses, e.g. guests (separate with commas or new lines)</small><textarea id="sre" placeholder="client@example.com, manager@example.com"></textarea></div>
  <div class="row"><label>Send every</label><select id="srd"><option value="1">Monday</option><option value="2">Tuesday</option><option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option><option value="7">Sunday</option></select><label>at</label><select id="srt"></select><span style="color:var(--mut);font-size:12px">Dhaka time</span><label class="ck"><input type="checkbox" id="sra" checked> Active</label><span style="flex:1"></span><button id="srdel">Delete</button><button id="srsn">✉ Send now</button><button class="p" id="srsv">Save</button></div></div>
 <div id="srst" style="font-size:13px;margin:4px 0;min-height:1em"></div><div class="acts" style="margin:8px 0 4px"><b style="flex:1">Preview</b><span id="srpn" style="color:var(--mut);font-size:12px"></span><button id="srpr">Refresh</button></div><div id="srprev"></div></dialog>
<dialog id="mcd" aria-label="Good morning"></dialog>
<dialog id="asdg"><b>Who is this task assigned to?</b><p style="color:var(--mut);font-size:13px;margin:8px 0">This task has no assignee. Hours are logged against the assigned person, so choose who it should be assigned to.</p><select id="asw"></select><div class="acts"><span class="sp"></span><button id="asx">Cancel</button><button class="p" id="asok">Assign &amp; log hours</button></div></dialog>
<dialog id="rdlg"><div class="acts noprint" style="margin:0 0 6px"><b style="flex:1">🧾 Worklog &amp; invoice</b><button id="rcl">Close</button></div>
 <div class="rctl noprint"><label style="margin:0">From</label><input type="date" id="rf"><label style="margin:0">To</label><input type="date" id="rt"><select id="rpp" title="Pick a standard period"></select><button id="r14">Last 14 days</button></div>
 <div class="rctl noprint"><select id="rpf"></select><button id="rpedit">⚙ Edit profile</button><select id="rvw"><option value="pack">Full pack (work logs + invoice)</option><option value="log">Work logs only</option><option value="inv">Invoice only</option></select><span class="sp"></span><button id="rcsv">⬇ Excel</button><button id="rapp" title="Save this report and invoice as a draft in Invoice tracking">➕ Create invoice</button><button class="p" id="rpr">🖨 Print / PDF</button></div>
 <div id="redit" class="noprint" hidden style="background:#fff4d6;color:#7a5200;padding:6px 10px;border-radius:6px;margin:6px 0"></div>
 <div id="rout"></div>
</dialog>
<dialog id="ivl"><div class="acts" style="margin:0 0 6px"><b style="flex:1;white-space:nowrap">📋 Invoice tracking</b><select id="ivlf" style="width:auto;flex:none"><option value="">All</option><option value="draft">Drafts</option><option value="amend">Needs amendment</option><option value="pending">Approved · unpaid</option><option value="paid">Paid</option></select><button id="ivlc">Close</button></div>
 <div id="ivls" style="margin:4px 0;font-size:13.5px"></div><div style="overflow:auto;max-height:70vh"><table id="ivlt"></table></div></dialog>
<dialog id="ivdlg"><b>Invoice profile</b>
 <p style="color:var(--mut);font-size:13px;margin:6px 0">One profile per invoice you send (e.g. Allshelter Pty Ltd, UCA). Company details and hourly rates are set in Manage → company → Details &amp; rates; fill the boxes below only to override them for this invoice. Hours are matched to a profile by Customer or Order No.; hours that match no profile go to the profile with both matching boxes empty.</p>
 <div class="pgrid">
  <label>Profile name<input id="pn"></label><label>Layout<select id="pl"><option value="worklog">Daily work logs per group + summary invoice</option><option value="items">Item list (actioned items / hours / sub-total)</option><option value="summary">Client summary report + 2-column invoice (DMC style)</option></select></label>
  <label>Match Customer (comma separated)<input id="pmc" placeholder="e.g. UCA, Allshelter LLC"></label><label>Match Order No. starting with<input id="pmo" placeholder="e.g. UCA"></label>
  <label>Worklog title (blank = client name)<input id="pwt" placeholder="e.g. ALLSHELTER Pty. Ltd."></label><label>PO no. (UCA-style invoice)<input id="ppo"></label>
  <label>Client name (blank = company)<input id="pcn"></label><label>Client address (blank = company)<input id="pca"></label>
  <label>Currency code<input id="pcc" maxlength="8" placeholder="blank = company"></label><label>Currency symbol<input id="pcs" maxlength="4" placeholder="blank = company"></label>
  <label>Rate / hour for everyone (blank = use company rates)<input type="number" id="prt" min="0" step="0.01"></label><label>OT multiplier (1 = company setting)<input type="number" id="pot" min="0" step="0.05"></label>
  <label>Invoice no. prefix<input id="ppx" placeholder="INVOICE-"></label><label>Next number / suffix<span style="display:flex;gap:6px"><input id="psq" inputmode="numeric" style="width:90px" title="Keep leading zeros, e.g. 033"><input id="psf" placeholder="-UCA"></span></label>
 </div>
 <label>Bank / payment details (blank = company)<textarea id="pbk" rows="3"></textarea></label>
 <label>Invoice column names, one per line as Group=Heading <textarea id="psl" rows="3" placeholder="Sales Drawing=Sales&#10;Project and Others=Projects/Others"></textarea></label>
 <b style="font-size:13px">Rate per person for THIS invoice only <span style="font-weight:400;color:var(--mut)">(blank = rate set in the company window)</span></b><div id="prl" class="pgrid"></div>
 <div class="acts"><button id="pdel" class="danger">Delete</button><span class="sp"></span><button id="pcc2">Cancel</button><button class="p" id="ivsv">Save</button></div>
</dialog>
<dialog id="mdlg"><div class="acts mhd"><span class="mic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 4v-4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg></span><div class="mti"><b id="mord"></b><div id="mtit" style="overflow:hidden;text-overflow:ellipsis"></div></div><button id="mopen" title="Open the full task">Open task</button><button id="mcl" title="Close">✕</button></div><div id="mbody"></div></dialog>
<dialog id="idlg"><div class="fh"><span class="fo"><i></i></span><div class="ft"><b>Flow</b><small>Your TaskFlow assistant</small></div><button id="icl" title="Close">✕</button></div>
<div class="fg" id="fgreet"></div>
<div class="ftabs"><button data-ft="n" class="on">✨ For you <b id="ftn" hidden></b></button><button data-ft="c">💬 Comments <b id="ftc" hidden></b></button></div>
<div id="ilist"></div><div class="acts"><button id="iall">Mark all read</button><button id="iclr">Clear</button></div></dialog>
<dialog id="odlg"><b>Overlap rules</b>
 <p style="color:var(--mut);font-size:13px;margin:8px 0">A task is flagged ⚠ when the same person has another open task on overlapping dates, in any company. These rules apply to everyone.</p>
 <label style="display:flex;gap:8px;align-items:flex-start;color:var(--ink)"><input type="checkbox" id="oh"> <span>Allow a hand-over day: a task may start on the same day another one ends</span></label>
 <label style="display:flex;gap:8px;align-items:flex-start;color:var(--ink)"><input type="checkbox" id="oo"> <span>Accept same-day tasks: tasks that start and finish on the same day (small jobs) are never flagged</span></label>
 <div class="acts"><span class="sp"></span><button id="oc">Cancel</button><button class="p" id="os">Save</button></div>
</dialog>

<dialog id="gedlg"><b>Edit group</b><label>Name</label><input id="gen" maxlength="80"><label>Inside group</label><select id="gep"></select><label>Colour</label><div class="gcs" id="gecs"></div><small id="genote" style="color:var(--mut)"></small><div id="gemw" style="margin-top:10px;border-top:1px solid var(--line);padding-top:8px"><label>Move this group to another company</label><div style="display:flex;gap:6px"><select id="gemt"></select><button id="gemb">Move</button></div><small style="color:var(--mut)">The group, its sub-groups and all their tasks move together. People without access to the other company become unassigned.</small></div><div class="acts"><span class="sp"></span><button id="gex">Cancel</button><button class="p" id="gesv">Save</button></div></dialog>
<dialog id="gdlg"><b>Groups (folders)</b><div id="glist"></div>
 <label>New group</label><input id="gn" placeholder="e.g. Production drawing, Sales drawing">
 <div class="acts"><span class="sp"></span><button id="gc">Close</button><button class="p" id="ga">Add group</button></div>
</dialog>

<dialog id="ddlg"><b>Drawing types</b><div id="dlist"></div>
 <label>New drawing type</label><input id="dn" placeholder="e.g. Shop drawing, Fabrication drawing">
 <div class="acts"><span class="sp"></span><button id="dc">Close</button><button class="p" id="da">Add</button></div>
</dialog>
<dialog id="sdlg"><b>Statuses</b><div id="slist"></div>
 <label>New status</label><input id="sn" placeholder="e.g. Waiting for client, Drafting, Checking">
 <div class="acts"><span class="sp"></span><button id="sc">Close</button><button class="p" id="sa">Add status</button></div>
</dialog>

<dialog id="cdlg"><b>Columns</b>
 <div id="clist" style="margin:8px 0"></div>
 <div class="addcol"><b style="font-size:12px;color:var(--mut)">ADD COLUMN</b>
  <div class="acr"><input id="cn" placeholder="Column name, e.g. Revision" style="flex:2;min-width:120px"><select id="ct" style="flex:1;min-width:100px"><option value="text">Text</option><option value="number">Number</option><option value="date">Date</option><option value="dropdown">Dropdown</option></select><input id="co" placeholder="Options, comma separated" style="display:none;flex:2;min-width:140px"><button class="p" id="ca">Add</button></div>
 </div>
 <div class="acts"><button id="cr" disabled title="Put columns back in the original order and width (only for you, on this device)">↺ Reset</button><span class="sp"></span><button id="cc">Close</button></div>
</dialog>
<dialog id="cedlg"><b>Edit column</b><label>Name</label><input id="cen" maxlength="60"><div id="ceow"><label id="ceol">Options (comma separated)</label><input id="ceo"></div><label style="display:flex;gap:8px;align-items:center;color:var(--ink)"><input type="checkbox" id="ceh"> <span>Hide this column on this device</span></label><small id="cet" style="color:var(--mut)"></small><div class="acts"><span class="sp"></span><button id="cex">Cancel</button><button class="p" id="cesv">Save</button></div></dialog>

<input type="file" id="wf" accept="image/png,image/jpeg,image/webp,image/svg+xml" hidden>
<dialog id="codlg"><b id="cot">Company details &amp; hourly rates</b>
 <p style="color:var(--mut);font-size:13px;margin:6px 0">Only admins can see this. Rates are used to calculate invoice amounts.</p>
 <div class="pgrid">
  <label>Company name (shown in the app)<input id="con" maxlength="60"></label><label>Keep columns in sync with<select id="cosy"></select></label><label>Name on invoice<input id="coc"></label>
  <label>Address<input id="coa"></label><label>Bank / payment details<input id="cob"></label>
  <label>Currency code<input id="coy" maxlength="8" placeholder="AUD"></label><label>Currency symbol<input id="cos" maxlength="4" placeholder="$"></label>
  <label>Default rate / hour<input type="number" id="cod" min="0" step="0.01"></label><label>OT multiplier (1 = same rate)<input type="number" id="coo" min="0" step="0.05"></label>
 </div>
 <div class="pgrid">
  <label>Invoice style<select id="colay"><option value="worklog">Category summary (Operations / Sales / Projects columns)</option><option value="items">Item list with PO no. (UCA style)</option><option value="summary">Client summary report + Drawing/Others invoice (DMC style)</option></select></label><label>Worklog title (blue box)<input id="cowt" placeholder="e.g. ALLSHELTER Pty. Ltd."></label>
  <label>Invoice no. prefix<input id="copx" placeholder="INVOICE-"></label><label>Next number · suffix<span style="display:flex;gap:6px"><input id="cosq" inputmode="numeric" style="width:90px" title="Keep leading zeros, e.g. 033"><input id="cosf" placeholder="-UCA"></span></label>
  <label>PO no.<input id="copo"></label><label>Invoice column names (Group=Heading, one per line)<textarea id="cosl" rows="2" placeholder="Sales Drawing=Sales"></textarea></label>
 </div>
 <b style="font-size:13px">Hourly rate per person <span style="font-weight:400;color:var(--mut)">(blank = default rate)</span></b><div id="cor" class="pgrid"></div>
 <div class="acts"><span class="sp"></span><button id="cox">Cancel</button><button class="p" id="cosv">Save</button></div>
</dialog>
<dialog id="wdlg"><b>Companies</b><div id="wlist"></div>
 <label>New company (e.g. a client name)</label><input id="wn" placeholder="e.g. Allshelter">
 <div class="acts"><span class="sp"></span><button id="wc">Close</button><button class="p" id="wa">Add</button></div>
</dialog>

<script>
let ST=[["todo","To Do"],["progress","In Progress"],["review","In Review"],["done","Completed"]];
let dc=(()=>{try{return localStorage.getItem("tf_dc")==="1"}catch(e){return false}})();
let hoursMap={},draft=null,rowDrag=null,dtypes=[],gdrag=null,gSwallow=false,thrVer=-1,cmeta={},inboxN=0,thrId=null,thrKey='',deepT=+(new URLSearchParams(location.search).get('t')||0),deepM=new URLSearchParams(location.search).get('m'),W=+(new URLSearchParams(location.search).get("w")||(()=>{try{return localStorage.getItem("tf_w")}catch(e){return 0}})()||0),viewInit=false,lastVer=-1,dirty=false,ovl={},ovset={hand:1,one:0},ovOnly=false,lateOnly=false,groups=[],G="",SL=[],LV="",wss=[],names={},allUsers=[],access={},openWp=null;
let tasks=[],users=[],cols=[],me={},view="list",editing=null,sel=new Set(),last="",pend=false;
const $=s=>document.querySelector(s);
const HOME=new URLSearchParams(location.search).get("home")==="1";document.body.classList.toggle("home",HOME);
const esc=s=>{const d=document.createElement("div");d.textContent=s==null?"":String(s);return d.innerHTML};
const ea=s=>esc(s).replace(/"/g,"&quot;");
async function api(a,data){
  const r=await fetch("api.php?a="+a+"&w="+W,{method:"POST",headers:{"Content-Type":"application/json","X-Requested-With":"fetch"},body:JSON.stringify(data||{})});
  if(r.status===401){location="login.php";throw new Error("auth")}
  const j=await r.json();if(j.error)throw new Error(j.error);return j;
}
const act=async(a,d)=>{try{await api(a,d);await load(true)}catch(e){alert(e.message)}};
async function load(force){
  try{const j=await api("list"),{v:jv,...rest}=j,s=JSON.stringify(rest);lastVer=jv;setLive(true);chkBuild(rest.build);if(s===last&&!force)return;
    last=s;if(!viewInit){viewInit=true;view=["board","gantt"].includes(j.me&&j.me.pref_view)?j.me.pref_view:"list";syncViewBtns()}
    tasks=j.tasks;tasks.forEach(t=>{t.id=+t.id;t.co=(t.co||[]).map(Number)});ovl=j.overlaps||{};cmeta=j.cmeta||{};hoursMap=j.hours||{};try{const th=(j.me&&j.me.theme)||"";localStorage.setItem("tf_theme",th);applyTheme(th)}catch(e){}dtypes=j.dtypes||[];inboxN=+j.inbox||0;ovset=j.ovset||ovset;window.__drq=+j.drq||0;users=j.users;cols=j.cols||[];me=j.me;wss=j.ws||[];LV=j.level||"";SL=j.statuses||[];groups=orderGroups(j.groups||[]);try{G=localStorage.getItem("tf_g_"+W)||""}catch(e){G=""}if(G&&G!=="none"&&!groups.some(g=>g.id==G))G="";if(SL.length)ST=SL.map(x=>[x.k,x.name]);document.body.classList.toggle("ro",LV==="view");names=j.names||{};allUsers=j.allUsers||[];access=j.access||{};
    if(j.w!==W){W=j.w;if(history.replaceState&&!HOME)history.replaceState(null,"","index.php"+(W?"?w="+W:""))}
    
    try{localStorage.setItem("tf_w",W)}catch(e){}
    render();bellUpd();if(deepT){const t=tasks.find(x=>x.id==deepT);deepT=0;if(t){if(deepM){deepM=null;openMsg(t.id)}else openDlg(t)}}}catch(e){}
}
const cmBadge=t=>{const m=cmeta[t.id];const P='M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 4v-4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z';
  if(!m)return t.id?`<span class="cmb o nomsg" title="Write a message"><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path d="${P}" fill="#86efac" stroke="#22c55e" stroke-width="1.4" stroke-linejoin="round"/></svg></span>`:"";
  const c=m[2]?"#dc2626":m[1]?"#f59e0b":"#22c55e",n=m[0]>99?"99+":String(m[0]);
  return `<span class="cmb o hasmsg" title="${m[2]?"Message for you":m[1]?"New messages":"Messages"}"><svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true"><path d="${P}" fill="none" stroke="${c}" stroke-width="1.7" stroke-linejoin="round"/><text x="12" y="${n.length>2?12.4:12.8}" text-anchor="middle" dominant-baseline="middle" font-size="${n.length>2?8:10.5}" font-weight="700" fill="${c}" font-family="system-ui,sans-serif">${n}</text></svg></span>`};
function bellUpd(){const b=$("#bn");b.hidden=!inboxN;b.textContent=inboxN;const cur=(wss.find(w=>w.id==W)||{}).name;document.title=(inboxN?"("+inboxN+") ":"")+(cur?cur+" – ":"")+"TaskFlow"}
const dtCol=n=>{const i=dtypes.findIndex(d=>d.name===n);if(i<0)return"#94a3b8";return dtypes[i].color||GPAL[i%GPAL.length]};
const dtOpts=cur=>{cur=cur||"";const names=dtypes.map(d=>d.name);if(cur&&!names.includes(cur))names.push(cur);
  return `<option value="">—</option>`+names.map(n=>`<option value="${ea(n)}"${n===cur?" selected":""}>${esc(n)}</option>`).join("")+(LV==="view"?"":`<option value="__new">＋ Add new…</option>`)};
function ask(msg,def){return new Promise(res=>{const d=document.createElement("dialog");d.style.cssText="border:0;border-radius:16px;padding:22px;width:min(420px,92vw);box-shadow:0 24px 70px rgba(8,26,61,.35);font:14px system-ui,sans-serif;color:#0f172a";d.innerHTML='<form method="dialog" style="display:grid;gap:14px"><b style="font-size:15px"></b><input style="padding:11px 12px;border:1px solid #cbd5e1;border-radius:10px;font:inherit"><div style="display:flex;gap:8px;justify-content:flex-end"><button type="button" data-x style="padding:9px 16px;border:1px solid #cbd5e1;background:#fff;border-radius:10px;cursor:pointer;font:inherit">Cancel</button><button style="padding:9px 18px;border:0;background:#0f2a5c;color:#fff;border-radius:10px;cursor:pointer;font:inherit;font-weight:700">OK</button></div></form>';d.querySelector("b").textContent=msg;const i=d.querySelector("input");i.value=def||"";let v=null;d.querySelector("[data-x]").onclick=()=>d.close();d.querySelector("form").onsubmit=()=>{v=i.value};d.addEventListener("close",()=>{d.remove();res(v)});document.body.appendChild(d);d.showModal();i.focus();i.select()})}
async function addDtype(){const n=await ask("New drawing type:");if(!n||!n.trim())return null;const r=await api("dt_add",{name:n});await load(true);return r.name}
const THEMES=[["#0f2a5c","#ffffff","Navy"],["#312e81","#ffffff","Indigo"],["#0f766e","#ffffff","Teal"],["#166534","#ffffff","Green"],["#7f1d1d","#ffffff","Maroon"],["#1f2937","#ffffff","Charcoal"],["#ffffff","#1d2433","White"],["#dbeafe","#1e3a8a","Sky"]];
function applyTheme(v){const m=/^(#[0-9a-f]{6})\|(#[0-9a-f]{6})$/i.exec(v||""),bg=m?m[1]:"#0f2a5c",fg=m?m[2]:"#ffffff",st=document.documentElement.style;
  st.setProperty("--nv",bg);st.setProperty("--hfg",fg);
  const h=document.querySelector("header");if(h){h.style.setProperty("--nv",bg);h.style.setProperty("--hfg",fg)}
  const lum=c=>{const n=parseInt(c.slice(1),16);return .3*(n>>16)+.59*((n>>8)&255)+.11*(n&255)};document.body.classList.toggle("hdark",lum(fg)<128)}
try{applyTheme(localStorage.getItem("tf_theme")||"")}catch(e){}
function setLook(v){const r=document.documentElement;for(const k of ["n","l","d"])r.classList.toggle("lk-"+k,k===v);{let h="";try{h=localStorage.getItem("tf_rh")}catch(e){}document.body.classList.remove("ro");document.body.classList.toggle("rh",v!=="d"&&h==="1")}try{localStorage.setItem("tf_look",v||"")}catch(e){}requestAnimationFrame(()=>{try{stPos()}catch(e){}})}
try{setLook(localStorage.getItem("tf_look")||"")}catch(e){}
function setGlass(on){document.documentElement.classList.toggle("glass",!!on);try{localStorage.setItem("tf_glass",on?"1":"")}catch(e){}}
try{if(localStorage.getItem("tf_glass"))document.documentElement.classList.add("glass")}catch(e){}
const MON=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
const fmtD=v=>{const m=/^(\d{4})-(\d{2})-(\d{2})/.exec(v||"");if(!m)return"?";return MON[+m[2]-1]+" "+(+m[3])+(+m[1]!==new Date().getFullYear()?", "+m[1]:"")};
const fmtRange=t=>t.start_date&&t.end_date&&t.start_date===t.end_date?fmtD(t.start_date):fmtD(t.start_date)+" – "+fmtD(t.end_date);
const ov=t=>ovl[t.id]||[];
const ovText=t=>"Overlaps with:\n"+ov(t).map(o=>"• "+o.t+" ("+(o.s||"?")+" → "+(o.e||"?")+")"+(o.w?" – "+o.w:"")).join("\n");
const ovBadge=t=>ov(t).length?`<span class="ovb" title="${ea(ovText(t))}">⚠</span>`:"";
const GPAL=['#2563eb','#16a34a','#f59e0b','#db2777','#7c3aed','#0891b2','#dc2626','#65a30d','#ea580c','#475569'];
function gcol(g){if(!g)return'#64748b';if(g.color)return g.color;if(g.parent_id){const p=groups.find(x=>String(x.id)===String(g.parent_id));if(p)return gcol(p)}const i=groups.filter(x=>!x.level).findIndex(x=>String(x.id)===String(g.id));return GPAL[(i<0?0:i)%GPAL.length]}
function orderGroups(rows){const top=rows.filter(g=>!g.parent_id||!rows.some(p=>String(p.id)===String(g.parent_id))),out=[];
  for(const g of top){out.push({...g,parent_id:null,level:0,full:g.name});for(const c of rows)if(String(c.parent_id)===String(g.id))out.push({...c,level:1,full:g.name+" › "+c.name})}return out}
const gOpts=cur=>groups.map(g=>`<option value="${g.id}"${String(cur)===String(g.id)?" selected":""}>${g.level?"\u00a0\u00a0↳ ":""}${esc(g.name)}</option>`).join("");
const gParent=id=>{const g=groups.find(x=>String(x.id)===String(id));return g&&g.parent_id?String(g.parent_id):""};
const inGroup=t=>G===""||(G==="none"?!t.group_id:String(t.group_id)===G||gParent(t.group_id)===G);
const gname=id=>(groups.find(g=>g.id==id)||{}).full||"";
const scol=k=>{const c=(SL.find(x=>x.k===k)||{}).color||"";return /^#[0-9a-f]{6}$/i.test(c)?c:""};
const stOpts=s=>ST.map(([k,n])=>`<option value="${k}"${(s||ST[0][0])===k?" selected":""}>${esc(n)}</option>`).join("")+(LV==="view"?"":`<option value="__new">＋ Add new…</option>`);
async function addStatus(){const n=await ask("New status name:");if(!n||!n.trim())return null;const r=await api("st_add",{name:n});await load(true);return r.k}
const asOpts=c=>`<option value="">Unassigned</option>`+users.map(u=>`<option value="${u.id}"${u.id==c?" selected":""}>${esc(u.name)}</option>`).join("");
const uname=id=>names[id]||"";
const uniq=k=>[...new Set(tasks.map(t=>t[k]).filter(Boolean))].sort();
const xv=(t,c)=>{const v=(t.x||{})["x_"+c.id];return v==null?"":v};
function xCtl(c,v,mode){
  const at=mode==="row"?`class="ed" data-f="x_${c.id}"`:`data-x="x_${c.id}"`;
  if(c.ctype==="dropdown"){const o=(c.options||[]).slice();if(v&&!o.includes(v))o.push(v);
    return `<select ${at}><option value=""></option>${o.map(x=>`<option value="${ea(x)}"${x===v?" selected":""}>${esc(x)}</option>`).join("")}</select>`}
  return `<input ${at} type="${c.ctype==="number"?"number":c.ctype==="date"?"date":"text"}"${c.ctype==="number"?' step="any"':""} value="${ea(v)}">`;
}
const today=()=>new Date().toISOString().slice(0,10);
const dcell=(c,t)=>{const v=xv(t,c);return `<button type="button" class="drng ${v&&v<today()&&t.status!=="done"?"late":""}${v?"":" empty"}" data-rngx="x_${c.id}">${v?esc(fmtD(v)):"Set date"}</button>`};
const late=t=>t.end_date&&t.end_date<today()&&t.status!=="done";

const syncFme=()=>{const f=$("#fme"),a=$("#fa");if(f&&a)f.classList.toggle("on",!!a.value&&String(a.value)===String(me.id))};
function syncFilters(){
  const f=$("#fs"),fv=f.value;f.innerHTML=`<option value="">All statuses</option>`+ST.map(([k,n])=>`<option value="${k}">${esc(n)}</option>`).join("");f.value=fv;
  const p=$("#fp"),a=$("#fa"),pv=p.value,av=a.value;
  p.innerHTML=`<option value="">All customers</option>`+uniq("customer").map(v=>`<option>${esc(v)}</option>`).join("");p.value=pv;
  a.innerHTML=`<option value="">Everyone</option>`+users.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join("");a.value=av;syncFme();
  $("#pjl").innerHTML=uniq("customer").map(v=>`<option value="${ea(v)}">`).join("");
  $("#who").textContent=me.name||"";$("#tmw").hidden=me.role!=="admin";$("#rm").hidden=me.role!=="admin";$("#ivm").hidden=me.role!=="admin";$("#om").hidden=me.role!=="admin";$("#apm").hidden=me.role!=="admin";{const n=+(window.__drq||0);$("#apn").hidden=!n;$("#apn").textContent=n}
}
function filtered(){
  const q=$("#q").value.toLowerCase(),p=$("#fp").value,a=$("#fa").value,st=$("#fs").value;
  return tasks.filter(t=>inGroup(t)&&(!ovOnly||ov(t).length)&&(!lateOnly||late(t))&&(!st||t.status===st)&&(!p||t.customer===p)&&(!a||String(t.assigned_to)===a||(t.co||[]).some(x=>String(x)===a))&&(!q||[t.title,t.order_no,t.customer,t.drawing_type,t.notes,...Object.values(t.x||{})].join(" ").toLowerCase().includes(q)));
}
function cardHTML(t){
  return `<div class="card" draggable="true" data-id="${t.id}"${scol(t.status)?` style="border-left:5px solid ${scol(t.status)}"`:""}><input type="checkbox" class="cc" title="Select"${sel.has(t.id)?" checked":""}><b>${esc(t.title)}</b>${cmBadge(t)}${ov(t).length?`<div class="meta"><span class="ovb" title="${ea(ovText(t))}">⚠ Overlap</span></div>`:""}<div class="meta">
  ${t.order_no?`<span class="tag">#${esc(t.order_no)}</span>`:""}
  ${G===""&&t.group_id&&gname(t.group_id)?`<span class="tag">📁 ${esc(gname(t.group_id))}</span>`:""}
  ${t.customer?`<span class="tag">${esc(t.customer)}</span>`:""}
  ${t.drawing_type?`<span class="tag">${esc(t.drawing_type)}</span>`:""}
  ${t.assigned_to?`<span>${avHtml(uname(t.assigned_to),"sm")} ${esc(uname(t.assigned_to))}${(t.co||[]).length?" +"+t.co.length:""}</span>`:""}
  ${cols.map(c=>{const v=xv(t,c);return v?`<span class="tag">${esc(c.name)}: ${esc(v)}</span>`:""}).join("")}
  ${(t.start_date||t.end_date)?`<span class="${late(t)?"late":""}">${esc(fmtRange(t))}</span>`:""}</div></div>`;
}
function completedHTML(list){
  const c=list.filter(t=>t.status==="done");
  return `<div class="done-wrap"><div class="col done" data-s="done"><h3 class="dh" style="cursor:pointer" title="Click to show or hide">${dc?"▸":"▾"} <i class="dot" style="background:${scol("done")||"#22c55e"}"></i>Completed<em>${c.length}</em></h3>${dc?"":`<div class="donegrid">${c.map(cardHTML).join("")||'<div class="empty">Completed tasks appear here</div>'}</div>`}</div></div>`;
}
function sorted(l){const a=l.filter(t=>t.status!=="done"),d=l.filter(t=>t.status==="done");return a.concat(d)}
function getGC(){try{return new Set(JSON.parse(localStorage.getItem("tf_gc_"+W)||"[]"))}catch(e){return new Set()}}
function getCC(){try{return new Set(JSON.parse(localStorage.getItem("tf_cc_"+W)||"[]"))}catch(e){return new Set()}}
function hiddenRow(t){if(!groups.length)return t.status==="done"?dc:false;const gc=getGC(),id=t.group_id?String(t.group_id):"none";
  if(gc.has(id)||(gParent(id)!==""&&gc.has(gParent(id))))return true;return t.status==="done"&&getCC().has(id)}
function toggleCC(id){const c=getCC();c.has(id)?c.delete(id):c.add(id);try{localStorage.setItem("tf_cc_"+W,JSON.stringify([...c]))}catch(e){}
  for(const t of tasks)if(hiddenRow(t))sel.delete(t.id);render()}
function vis(){return filtered().filter(t=>!hiddenRow(t))}
function toggleGroup(id){const g=getGC();g.has(id)?g.delete(id):g.add(id);try{localStorage.setItem("tf_gc_"+W,JSON.stringify([...g]))}catch(e){}
  for(const t of tasks)if(hiddenRow(t))sel.delete(t.id);render()}
function gcSet(m){const ids=groups.map(g=>String(g.id)).concat("none");let gc=[],cc=[];
  if(m==="c"){gc=ids;cc=ids;dc=true}else if(m==="e"){cc=ids;dc=true}else dc=false;
  try{localStorage.setItem("tf_gc_"+W,JSON.stringify(gc));localStorage.setItem("tf_cc_"+W,JSON.stringify(cc));localStorage.setItem("tf_dc",dc?"1":"0")}catch(e){}
  for(const t of tasks)if(hiddenRow(t))sel.delete(t.id);render()}
function gcPos(){const g=$("#gcb"),b=g.querySelector(".gcm");if(!b)return;const r=b.getBoundingClientRect();g.style.setProperty("--gl",Math.max(8,Math.min(r.left,innerWidth-190))+"px");g.style.setProperty("--gt",Math.min(r.bottom+2,innerHeight-130)+"px")}
for(const ev of ["pointerenter","focusin","pointerdown","touchstart"])$("#gcb").addEventListener(ev,gcPos,{passive:true});
addEventListener("scroll",()=>{if($("#gcb").matches(":hover,:focus-within"))gcPos()},{passive:true});
$("#gcb").addEventListener("click",e=>{const b=e.target.closest("[data-gc]");if(b){gcSet(b.dataset.gc);document.activeElement&&document.activeElement.blur()}});
function lockView(){$("#mhm").hidden=LV==="view"&&!(me&&me.role==="admin");if(LV!=="view")return;document.querySelectorAll("#main .ed").forEach(el=>el.disabled=true);document.querySelectorAll("#main .card").forEach(c=>c.draggable=false)}
function toggleDone(){dc=!dc;try{localStorage.setItem("tf_dc",dc?"1":"0")}catch(e){}
  if(dc)for(const t of tasks)if(t.status==="done")sel.delete(t.id);render()}
function updBar(){const b=$("#bar"),n=sel.size;b.hidden=!n||view==="gantt";if(n)b.innerHTML=`<b>${n} selected</b>${LV!=="view"?'<button id="bdup">Duplicate</button>':""}${LV!=="view"&&groups.length?`<select id="bmv"><option value="">Move to group…</option><option value="0">No group</option>${gOpts("")}</select>`:""}${LV==="full"&&wss.some(w=>w.id!=W&&["edit","full"].includes(w.level))?`<select id="bws"><option value="">Move to company…</option>${wss.filter(w=>w.id!=W&&["edit","full"].includes(w.level)).map(w=>`<option value="${w.id}">${esc(w.name)}</option>`).join("")}</select>`:""}${LV==="full"?'<button id="bdel">Delete</button>':""}<button id="bclr">Clear</button>`}
const fmtH=n=>{n=+n||0;return n.toFixed(2).replace(/\.00$/,"").replace(/(\.\d)0$/,"$1")};
function coPop(btn){const t=tasks.find(x=>x.id==btn.dataset.co);if(!t)return;const pop=$("#copop");pop.dataset.t=t.id;
  const cand=users.filter(u=>String(u.id)!==String(t.assigned_to));
  $("#col").innerHTML=cand.map(u=>`<label><input type="checkbox" value="${u.id}"${(t.co||[]).includes(+u.id)?" checked":""}> ${esc(u.name)}</label>`).join("")||'<div class="empty" style="padding:8px">No one else to add</div>';
  pop.hidden=false;const r=btn.getBoundingClientRect(),w=pop.offsetWidth,h=pop.offsetHeight;
  pop.style.left=Math.max(8,Math.min(r.left,innerWidth-w-8))+"px";pop.style.top=(r.bottom+6+h>innerHeight?Math.max(8,r.top-h-6):r.bottom+6)+"px"}
document.addEventListener("click",e=>{const b=e.target.closest(".coadd");if(b){e.preventDefault();e.stopPropagation();if(!$("#copop").hidden&&$("#copop").dataset.t===b.dataset.co){$("#copop").hidden=true}else coPop(b);return}
  if(!e.target.closest("#copop"))$("#copop").hidden=true},true);
$("#col").addEventListener("change",async e=>{const pop=$("#copop"),id=+pop.dataset.t,t=tasks.find(x=>x.id===id);if(!t)return;
  const ids=[...document.querySelectorAll("#col input:checked")].map(x=>+x.value);
  try{const r=await api("co_set",{id,users:ids});t.co=(r.co||ids).map(Number);const c=$("#dcob");if(c&&c.dataset.co==String(id))$("#dcot").textContent=coNames(t);render()}catch(err){alert(err.message);e.target.checked=!e.target.checked}});
function coNames(t){return(t.co||[]).map(id=>uname(id)).join(", ")}
function coBtn(t){const n=(t.co||[]).length;
  if(LV==="view")return n?`<span class="coN" title="${ea(coNames(t))}">+${n}</span>`:"";
  return `<button type="button" class="coadd${n?" has":""}" data-co="${t.id}" title="${n?ea("Also: "+coNames(t)):"Add more people to this task"}">${n?"+"+n:"＋"}</button>`}
const BASE_COLS=[
 {id:"title",label:"Title",cell:t=>`<div class="tc">${ovBadge(t)}${cmBadge(t)}<input class="ed${t.id?" tt":""}" data-f="title" value="${ea(t.title)}"${t.id?' readonly title="Open task"':""}></div>`},
 {id:"order_no",label:"Order No.",cell:t=>`<input class="ed" data-f="order_no" value="${ea(t.order_no)}">`},
 {id:"customer",label:"Customer",cell:t=>`<input class="ed" data-f="customer" list="pjl" value="${ea(t.customer)}">`},
 {id:"drawing_type",label:"Drawing type",cell:t=>`<select class="ed pill" data-f="drawing_type" style="--pc:${t.drawing_type?dtCol(t.drawing_type):"#94a3b8"}">${dtOpts(t.drawing_type)}</select>`},
 {id:"dates",label:"Start / End",cls:"dates",cell:t=>`<button type="button" class="drng ${late(t)?"late":""}${t.start_date||t.end_date?"":" empty"}" data-rng="${t.id}">${(t.start_date||t.end_date)?esc(fmtRange(t)):"Start – End"}</button>`},
 {id:"status",label:"Status",cell:t=>`<select class="ed pill" data-f="status" style="--pc:${scol(t.status||ST[0][0])||"#94a3b8"}">${stOpts(t.status)}</select>`},
 {id:"assigned_to",label:"Assigned to",cell:t=>`<div class="asw2">${t.assigned_to?avHtml(uname(t.assigned_to),"sm"):""}<select class="ed" data-f="assigned_to">${asOpts(t.assigned_to)}</select>${t.id?coBtn(t):""}</div>`},
 {id:"hours",label:"Hours",cell:t=>t.id?`<button type="button" class="hbtn" data-hrs="${t.id}" title="Log or see hours">⏱ ${hoursMap[t.id]?fmtH(hoursMap[t.id]):"Log"}</button>`:""},
 {id:"notes",label:"Notes",cell:t=>`<input class="ed" data-f="notes" value="${ea(t.notes)}">`}];
const coKey=()=>"tf_co_"+W;
const cwKey=()=>"tf_cw_"+W;
const DEFW={title:220,order_no:110,customer:160,drawing_type:140,dates:170,status:140,assigned_to:185,group_id:140,hours:90,notes:220};
function cwSaved(){try{return JSON.parse(localStorage.getItem(cwKey())||"{}")||{}}catch(e){return {}}}
const colW=id=>Math.max(50,cwSaved()[id]||DEFW[id]||150);
function colOrderSaved(){try{return !!localStorage.getItem(coKey())||!!localStorage.getItem(cwKey())}catch(e){return false}}
let rz=null;
document.addEventListener("pointerdown",e=>{
  const h=e.target.closest&&e.target.closest(".rz");if(!h)return;
  e.preventDefault();e.stopPropagation();
  const th=h.closest("th"),col=document.querySelector(`col[data-c="${h.dataset.rz}"]`),tbl=h.closest("table");
  th.draggable=false;h.classList.add("on");
  rz={id:h.dataset.rz,x:e.clientX,w:col.getBoundingClientRect().width,col,tbl,th,h,total:tbl.getBoundingClientRect().width};
  try{h.setPointerCapture(e.pointerId)}catch(x){}
});
document.addEventListener("pointermove",e=>{
  if(!rz)return;const w=Math.max(50,Math.round(rz.w+e.clientX-rz.x));
  rz.col.style.width=w+"px";rz.tbl.style.width=Math.round(rz.total+(w-rz.w))+"px";rz.cur=w;
});
function endRz(){
  if(!rz)return;const r=rz;rz=null;r.h.classList.remove("on");r.th.draggable=true;
  if(r.cur){const m=cwSaved();m[r.id]=r.cur;try{localStorage.setItem(cwKey(),JSON.stringify(m))}catch(x){}$("#cr").disabled=false}
}
document.addEventListener("pointerup",endRz);document.addEventListener("pointercancel",endRz);
function colOrder(){try{return JSON.parse(localStorage.getItem(coKey())||"[]")}catch(e){return []}}
const hcKey=()=>"tf_ch_"+W,lbKey=()=>"tf_cl_"+W;
function hiddenCols(){try{return JSON.parse(localStorage.getItem(hcKey())||"[]")}catch(e){return[]}}
function colLabels(){try{return JSON.parse(localStorage.getItem(lbKey())||"{}")||{}}catch(e){return{}}}
function colDefs(withHidden){
  const lb=colLabels(),hc=hiddenCols();
  const all=BASE_COLS.map(c=>Object.assign({},c,{label:lb[c.id]||c.label})).concat(cols.map(c=>({id:"x_"+c.id,label:c.name,cell:t=>c.ctype==="date"?dcell(c,t):xCtl(c,xv(t,c),"row")})));
  const saved=colOrder(),out=[];
  for(const id of saved){const d=all.find(x=>x.id===id);if(d&&!out.includes(d))out.push(d)}
  for(const d of all)if(!out.includes(d))out.push(d);
  return withHidden?out:out.filter(d=>d.id==="title"||(!hc.includes(d.id)&&!(LV==="view"&&d.id==="hours")));
}
function moveCol(from,to){
  if(from===to)return;
  const ids=colDefs(true).map(d=>d.id),a=ids.indexOf(from),b=ids.indexOf(to);if(a<0||b<0)return;
  ids.splice(a,1);ids.splice(b,0,from);
  try{localStorage.setItem(coKey(),JSON.stringify(ids))}catch(e){}
  render();
}
let dragCol=null;
document.addEventListener("dragstart",e=>{const th=e.target.closest&&e.target.closest("th.ch");if(th){dragCol=th.dataset.col;e.dataTransfer.effectAllowed="move";try{e.dataTransfer.setData("text/plain",dragCol)}catch(x){}}});
document.addEventListener("dragover",e=>{if(!dragCol)return;const th=e.target.closest&&e.target.closest("th.ch");if(th){e.preventDefault();document.querySelectorAll("th.ch.over").forEach(x=>x.classList.remove("over"));th.classList.add("over")}});
document.addEventListener("drop",e=>{if(!dragCol)return;const th=e.target.closest&&e.target.closest("th.ch");if(th){e.preventDefault();const from=dragCol;dragCol=null;moveCol(from,th.dataset.col)}});
document.addEventListener("dragend",()=>{dragCol=null;document.querySelectorAll("th.ch.over").forEach(x=>x.classList.remove("over"))});
$("#cr").onclick=()=>{try{localStorage.removeItem(coKey());localStorage.removeItem(cwKey())}catch(e){}render()};
(()=>{let on=false,tk=0;const f=()=>{tk=0;const lw=document.querySelector(".listwrap"),y=Math.max(window.scrollY,lw?lw.scrollTop:0);if(!on&&y>70){on=true;document.body.classList.add("hs")}else if(on&&y<6){on=false;document.body.classList.remove("hs")}};addEventListener("scroll",()=>{if(!tk)tk=requestAnimationFrame(f)},{passive:true,capture:true})})();
let homeData=null,homeAt=0,homeBusy=false;
async function renderHome(){
  const m=$("#main");
  if(!homeData)m.innerHTML='<div class="hm"><div class="empty">Loading your tasks…</div></div>';
  if(!homeData||Date.now()-homeAt>2500){if(homeBusy)return;homeBusy=true;try{homeData=await api("my_tasks");homeAt=Date.now()}catch(e){m.innerHTML='<div class="hm"><div class="empty">'+esc(e.message)+'</div></div>';homeBusy=false;return}homeBusy=false}
  const ymd=d=>d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");
  const now=new Date(),td=ymd(now),wk=ymd(new Date(now.getTime()+7*864e5));
  const B=[["late","Overdue","#dc2626"],["today","Due today","#f59e0b"],["going","Ongoing now","#16a34a"],["soon","Upcoming: starts in the next 7 days","#2563eb"],["later","Later","#64748b"],["none","Not scheduled","#94a3b8"]];
  const bk={late:[],today:[],going:[],soon:[],later:[],none:[]};
  for(const t of homeData.tasks){const e=t.end_date||"",st0=t.start_date||"";bk[e&&e<td?"late":e===td?"today":st0&&st0>td?(st0<=wk?"soon":"later"):(st0||e)?"going":"none"].push(t)}
  const fd=s=>{const d=new Date(s+"T00:00:00");return d.toLocaleDateString(undefined,{day:"numeric",month:"short"})};
  const row=t=>{const st=(homeData.st[t.ws_id]||{})[t.status],nm=st?st.n:t.status,pc=st&&st.c?st.c:"#888";
    return `<a class="hrow" href="index.php?w=${t.ws_id}&t=${t.id}"><div style="min-width:0"><b>${esc(t.title)}</b><small>${[t.order_no,t.customer,t.drawing_type].filter(Boolean).map(esc).join(" · ")}</small></div><div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:flex-end"><span class="hco">${esc(homeData.ws[t.ws_id]||"")}</span><span class="hst" style="--pc:${esc(pc)}">${esc(nm)}</span></div><span class="hdl${t.end_date&&t.end_date<td?" late":""}">${t.start_date&&t.start_date>td?"Starts "+fd(t.start_date):t.end_date?fd(t.end_date):"No deadline"}</span></a>`};
  if(!window.__hm||Date.now()-window.__hmAt>8000){try{window.__hm=(await api("home_msgs")).convs||[];window.__hmAt=Date.now()}catch(e){window.__hm=window.__hm||[]}}const hc=window.__hm;
  const hday=a=>{const d=new Date(String(a).replace(" ","T")),t0=new Date();t0.setHours(0,0,0,0);const tm=d.toLocaleTimeString(undefined,{hour:"numeric",minute:"2-digit"});return d>=t0?tm:"Yesterday "+tm};
  const msgHtml=hc.length?`<div class="hmc"><h3><i></i>Messages since yesterday <em>${hc.reduce((a,c)=>a+c.n,0)}</em></h3>${hc.map(c=>`<button class="hmr" data-hp="${c.peer}">${avHtml(c.name)}<span class="hmb"><b>${esc(c.name)}</b><small>${c.peer?"":esc(c.from)+": "}${esc(c.body)}</small></span><span class="hmt">${esc(hday(c.at))}${c.unread?`<br><span class="hmu">${c.unread} new</span>`:""}</span></button>`).join("")}</div>`:"";
  if(!window.__hg||Date.now()-window.__hgAt>60000){try{window.__hg=await api("hours_gap",{today:ltoday()});window.__hgAt=Date.now()}catch(e){window.__hg=window.__hg||{days:[],last:null}}}
  const hg=window.__hg,hfd=d=>new Date(d+"T00:00:00").toLocaleDateString(undefined,{weekday:"short",day:"numeric",month:"short"}),need=+hg.need||8,miss=hg.days.reduce((a,x)=>a+Math.max(0,need-x.h),0);
  const lastBad=hg.last&&hg.last.h<need-.001;
  const remHtml=lastBad||hg.days.length?`<div class="hrem${hg.last&&hg.last.h===0?" bad":""}"><span class="ri">⏱</span><div class="rb"><b>Your hours are not fully logged</b>${hg.last?(lastBad?`Last working day, ${esc(hfd(hg.last.date))}: <b style="display:inline">${fmtH(hg.last.h)} h</b> of ${need} h.`:`Last working day, ${esc(hfd(hg.last.date))}: complete.`):""}${hg.days.length?`<div>Last 15 days: ${hg.days.length} working day${hg.days.length===1?"":"s"} short, <b style="display:inline">${fmtH(miss)} h</b> missing.</div><div class="rd">${hg.days.map(x=>`<span>${esc(hfd(x.date))} · ${fmtH(x.h)} h</span>`).join("")}</div>`:""}</div><button id="hremb" type="button">Open my hours</button></div>`:"";
  const ht=(()=>{try{return sessionStorage.getItem("tf_ht")==="cos"?"cos":"tasks"}catch(e){return"tasks"}})();
  const cn=homeData.cnt||{},hcards=wss.map(w=>{const c=cn[w.id]||{open:0,late:0},mine=homeData.tasks.filter(t=>t.ws_id==w.id).length;return `<a class="hcc" href="index.php?w=${w.id}"><span class="hcl">${wlogo(w)}</span><b>${esc(w.name)}</b><small>${c.open} open task${c.open===1?"":"s"}${c.late?` · <i class="lt">${c.late} overdue</i>`:""}</small><small>${mine?mine+" assigned to you":"Nothing assigned to you"}</small></a>`}).join("")||'<div class="empty">You have not been given access to any company yet.</div>';
  const tabsHtml=`<div class="htabs"><button type="button" data-ht="tasks" class="${ht==="tasks"?"on":""}">My tasks <em>${homeData.tasks.length}</em></button><button type="button" data-ht="cos" class="${ht==="cos"?"on":""}">Companies <em>${wss.length}</em></button></div>`;
  const n=homeData.tasks.length,lt=bk.late.length;
  const hr0=new Date().getHours(),greet=hr0<5?"Working late":hr0<12?"Good morning":hr0<17?"Good afternoon":"Good evening",gem=hr0<5?"\u{1F319}":hr0<12?"\u2600\uFE0F":hr0<17?"\u{1F324}\uFE0F":"\u{1F306}",tdn=bk.today.length;
  m.innerHTML=`<div class="hw"><aside class="hl"><div class="hlt"><div class="hgr">${gem} ${greet}</div><h2>${esc((me.name||"").split(" ")[0]||"there")} <span class="wv">\u{1F44B}</span></h2><p>${n?`You have <b>${n}</b> open task${n===1?"":"s"} across <b>${wss.length}</b> compan${wss.length===1?"y":"ies"}.`:"Nothing is assigned to you right now. Enjoy the calm."}</p>
    <div class="hch"><span><b>${n}</b>Open</span><span class="${lt?"rd":""}"><b>${lt}</b>Overdue</span><span><b>${tdn}</b>Due today</span><span class="gn"><b>${bk.going.length}</b>Ongoing</span></div></div>`+remHtml+`<div class="hlc"><div class="hlh">Your companies</div><div class="hcg">${hcards}</div></div><button type="button" class="hlhelp" onclick="window.tfHelp&&tfHelp()">❓ Help &amp; names of every part</button><div class="hlf">A little glamour goes a long way. <i>\u00BB</i></div></aside><section class="hrr">`+(msgHtml+
    B.filter(b=>bk[b[0]].length).map(b=>`<div class="hsec" style="--c:${b[2]}"><h3><i></i>${b[1]} <em>${bk[b[0]].length}</em></h3>${bk[b[0]].map(row).join("")}</div>`).join("")||(msgHtml?"":'<div class="empty">All clear. Nothing on your plate right now \u{1F389}</div>'))+`</section></div>`}
document.addEventListener("click",e=>{const tb=e.target.closest(".htabs [data-ht]");if(tb){try{sessionStorage.setItem("tf_ht",tb.dataset.ht)}catch(x){}renderHome();return}if(e.target.closest("#hremb")){mhOpen();return}const b=e.target.closest(".hmr[data-hp]");if(!b)return;const p=+b.dataset.hp;mgPeer=p;if($("#msgw").hidden)$("#mgb").click();else mgSel(p)});
function render(){
  const ae=document.activeElement;if(ae&&ae.dataset&&ae.dataset.f&&$("#main").contains(ae)){pend=true;return}
  pend=false;for(const id of [...sel])if(!tasks.some(t=>t.id==id))sel.delete(id);
  updBar();syncFilters();document.body.classList.toggle("lst",view==="list"&&!HOME);
  const cur=wss.find(w=>w.id==W);document.title=(cur?cur.name+" – ":"")+"TaskFlow";
  $("#rpw").hidden=me.role!=="admin";document.body.classList.toggle("guest",me.role==="guest");$("#wm").hidden=me.role!=="admin";
  $("#gcb").hidden=view!=="list";const dvb=$("#dv"),isDef=(me.pref_view||"list")===view;dvb.textContent=isDef?"★ Default view":"☆ Make "+view+" my default";dvb.disabled=isDef;
  $("#who").textContent=me.role==="admin"?"Admin":LV==="view"?"View only":LV==="full"?"Full access":"Member";$("#uname").textContent=me.name||"";$("#uini").textContent=String(me.name||"?").trim().split(/\s+/).slice(0,2).map(x=>x[0]).join("").toUpperCase();$("#amm").hidden=me.role!=="admin";$("#alg").hidden=me.role!=="admin";$("#abk").hidden=me.role!=="admin";$("#hrm").hidden=me.role!=="admin";$("#srm").hidden=me.role!=="admin";$("#ghm").hidden=me.role!=="admin";$("#rhome").classList.toggle("on",HOME);
  $("#wname").textContent=cur?cur.name:"";$("#vname").innerHTML=cur?wlogo(cur):"";$("#wlogo").innerHTML=cur?wlogo(cur):"";
  $("#wlinks").innerHTML=wss.map(w=>`<a class="wl${w.id==W?" on":""}" href="index.php?w=${w.id}" title="${ea(w.name)}">${wlogo(w)}</a>`).join("")||'<div class="empty" style="padding:12px">None</div>';
  fillCp();
  if(me.role==="guest"&&!HOME&&!new URLSearchParams(location.search).get("w")){location.replace("index.php?home=1");return}
  if(HOME&&me.role==="guest"){$("#wname").textContent="Overview";$("#wlogo").innerHTML="";renderGuestHome();return}
  if(HOME){$("#wname").textContent="My tasks";$("#wlogo").innerHTML="";renderHome();return}
  if(!W){$("#stats").innerHTML="";$("#main").innerHTML='<div class="empty">You have not been given access to any company yet. Please ask an admin.</div>';return}
  const list=filtered();
  const gt=tasks.filter(inGroup);
  const fsv=$("#fs").value;
  const smore=(()=>{try{return localStorage.getItem("tf_smore")==="1"}catch(e){return false}})(),isDefSt=(k,n)=>/ongoing|progress|review|ready/i.test(n+" "+k),shown=ST.filter(([k,n])=>smore||isDefSt(k,n)||fsv===k),hid=ST.length-shown.length;
  const pill=(c,on,att,num,lab,tt)=>`<div class="stat${on?" on":""}" ${att} style="--pc:${c}"${tt?` title="${tt}"`:""}><b>${num}</b><span>${lab}</span></div>`;
  $("#stats").innerHTML=pill("#4f46e5",!fsv&&!lateOnly,'data-fs=""',gt.length,"All tasks")+
    shown.map(([k,n])=>pill(scol(k)||"#94a3b8",fsv===k,`data-fs="${k}"`,gt.filter(t=>t.status===k).length,esc(n),"Show only this status")).join("")+
    pill("#dc2626",lateOnly,'data-late="1"',gt.filter(late).length,"Overdue","Show only overdue tasks")+
    (gt.some(t=>ov(t).length)||ovOnly?pill("#dc2626",ovOnly,'data-ov="1"',gt.filter(t=>ov(t).length).length,"⚠ Overlaps","Show only tasks that overlap for the same person"):"")+
    (hid>0||smore?`<div class="stat more" data-more="1" title="${smore?"Show fewer":"Show every status"}"><span>${smore?"− Fewer":"＋ "+hid+" more"}</span></div>`:"");
  const gtb=$("#gtabs"),nn=tasks.filter(t=>!t.group_id).length;
  gtb.hidden=!groups.length;
  gtb.innerHTML=`<a class="gt${G===""?" on":""}" data-g="">All <small>${tasks.length}</small></a>`+groups.filter(g=>!g.level).map(g=>`<a class="gt${String(g.id)===G?" on":""}" data-g="${g.id}">${esc(g.name)} <small>${tasks.filter(t=>t.group_id==g.id||gParent(t.group_id)===String(g.id)).length}</small></a>`).join("")+(nn?`<a class="gt${G==="none"?" on":""}" data-g="none">No group <small>${nn}</small></a>`:"");
  if(view==="board"){
    $("#cr").disabled=true;
    $("#main").innerHTML=`<div class="board" style="grid-template-columns:repeat(${ST.length-1},minmax(220px,1fr))">`+ST.filter(s=>s[0]!=="done").map(([k,n])=>{
      const c=list.filter(t=>t.status===k);
      return `<div class="col" data-s="${k}"><h3><span>${scol(k)?`<i class="dot" style="background:${scol(k)}"></i>`:""}${n}</span><em>${c.length}</em></h3>${c.map(cardHTML).join("")||'<div class="empty">No tasks</div>'}</div>`}).join("")+`</div>`+completedHTML(list);
  }else if(view==="gantt"){
    $("#cr").disabled=true;renderGantt(list);
  }else{
    const defs=colDefs(),span=defs.length+2,gc=getGC();
    const act_=list.filter(t=>t.status!=="done"),done_=list.filter(t=>t.status==="done"),nd=done_.length;
    const rowHTML=t=>`<tr class="row" data-id="${t.id}"${scol(t.status)?` style="--sc:${scol(t.status)}"`:""}><td style="white-space:nowrap">${LV==="view"?"":`<span class="hd" title="Drag to reorder">⋮⋮</span>`}<input type="checkbox" class="rc"${sel.has(t.id)?" checked":""}></td>${defs.map(d=>`<td data-c="${d.id}"${d.cls?` class="${d.cls}"`:""}>${d.cell(t)}</td>`).join("")}<td><button class="o" title="Open details">⋯</button></td></tr>`;
    const draftHTML=()=>`<tr class="row draft" data-id="0"><td></td>${defs.map(d=>`<td data-c="${d.id}"${d.cls?` class="${d.cls}"`:""}>${d.cell(draft.t)}</td>`).join("")}<td style="white-space:nowrap"><button class="o" data-dsave title="Add task (Enter)">✔</button><button class="o" data-dcancel title="Cancel (Esc)">✕</button></td></tr>`;
    let rows="";
    if(groups.length){
      const filt=$("#q").value||$("#fs").value||$("#fp").value||$("#fa").value;
      let secs=groups.map(g=>({id:String(g.id),name:g.name,lv:g.level,parent:g.parent_id?String(g.parent_id):"",color:gcol(g)})).concat([{id:"none",name:"No group",lv:0,parent:"",color:"#64748b"}]);
      if(G!=="")secs=secs.filter(x=>x.id===G||x.parent===G);
      const inSec=(t,sc)=>sc.id==="none"?!t.group_id:String(t.group_id)===sc.id;
      const doneBlk=s=>{const ks=secs.filter(k=>k.parent===s.id),dl=done_.filter(t=>inSec(t,s)||ks.some(k=>inSec(t,k)));if(!dl.length)return"";const cc=getCC().has(s.id);return `<tr class="dh sub" style="--gc:${s.color}" data-dh="${s.id}" title="Click to show or hide completed in this group"><th colspan="${span}">${cc?"▸":"▾"} 📁 ${esc(s.id==="none"?"No group":gname(s.id)||s.name)} Completed (${dl.length})</th></tr>`+(cc?"":dl.map(rowHTML).join(""))};
      for(const sc of secs){
        if(sc.parent&&gc.has(sc.parent))continue;
        const kids=secs.filter(k=>k.parent===sc.id),items=act_.filter(t=>inSec(t,sc)),dn=done_.filter(t=>inSec(t,sc)).length;
        const tot=items.length+kids.reduce((n,k)=>n+act_.filter(t=>inSec(t,k)).length,0),totd=dn+kids.reduce((n,k)=>n+done_.filter(t=>inSec(t,k)).length,0);
        const hasD=draft&&draft.grp===(sc.id==="none"?"":sc.id);
        if(sc.id==="none"&&!items.length&&!dn&&!hasD)continue;
        if(filt&&!tot&&!totd&&!hasD)continue;
        const col=gc.has(sc.id);
        rows+=(sc.lv?"":`<tr class="gsp"><td colspan="${span}"></td></tr>`)+`<tr class="gh${sc.lv?" sub":""}" data-gh="${sc.id}" style="--gc:${sc.color}" title="Click to show or hide"><th colspan="${span}">${LV!=="view"&&sc.id!=="none"?'<span class="ghd" title="Drag to reorder this group">⋮⋮</span>':""}${col?"▸":"▾"} ${sc.lv?"↳ ":""}📁 ${esc(sc.name)} <small style="font-weight:400">${tot} open${totd?` · ${totd} completed`:""}</small>${LV!=="view"&&sc.id!=="none"?` <button class="o" data-gadd="${sc.id}" style="margin-left:8px">+ Task</button>`:""}</th></tr>`;
        if(!col){rows+=items.map(rowHTML).join("")+(hasD?draftHTML():"")+addRow(sc.id,span,sc.id!=="none"&&!sc.lv,sc.color);
          if(sc.lv?!secs.some(x=>x.id===sc.parent):!kids.length)rows+=doneBlk(sc)}
        if(sc.lv&&secs.filter(k=>k.parent===sc.parent).pop()===sc){const par=secs.find(x=>x.id===sc.parent);if(par)rows+=doneBlk(par)}
        if(sc.id!=="none"&&(sc.lv?secs.filter(k=>k.parent===sc.parent).pop()===sc:!kids.length))rows+=`<tr class="gend" style="--gc:${sc.color}"><td colspan="${span}"></td></tr>`
      }
    }else rows=act_.map(rowHTML).join("")+(draft&&draft.grp===""?draftHTML():"")+addRow("none",span);
    if(nd&&!groups.length)rows+=`<tr class="dh" style="cursor:pointer" title="Click to show or hide"><th colspan="${span}">${dc?"▸":"▾"} 📁 Completed (${nd})</th></tr>`+(dc?"":done_.map(rowHTML).join(""));
    $("#main").innerHTML=`<div class="listwrap"><table style="width:${54+defs.reduce((a,d)=>a+colW(d.id),0)+48}px"><colgroup><col style="width:54px">${defs.map(d=>`<col data-c="${d.id}" style="width:${colW(d.id)}px">`).join("")}<col style="width:48px"></colgroup><thead><tr id="hrow"><th><input type="checkbox" id="all"${vis().length&&vis().every(t=>sel.has(t.id))?" checked":""}></th>${defs.map(d=>`<th class="ch" draggable="true" data-col="${d.id}" title="Drag to rearrange">${esc(d.label)}<span class="rz" data-rz="${d.id}" draggable="false"></span></th>`).join("")}<th></th></tr></thead><tbody>`+
      (rows||`<tr><td colspan="${span}" class="empty">No tasks yet. Click “New task”.</td></tr>`)+`</tbody></table></div>`;
    $("#cr").disabled=!colOrderSaved();
  }
  lockView();
}
// ---- Hours log ----
let tlId=0;
async function openTL(id){const t=tasks.find(x=>x.id==id);if(!t)return;tlId=id;
  $("#tlord").textContent=t.order_no?"Order #"+t.order_no:"";$("#tltit").textContent=t.title||"";{const c=revCol(),w=$("#tlrw");w.innerHTML=c?revCtl(c?((t.x||{})["x_"+c.id]||""):"","","tlr"):"";if(c)$("#tlr").title="Revision"}$("#tld").value=today();$("#tlh").value="";$("#tln").value="";
  document.querySelectorAll("#tldg .tlf input,#tldg .tlf button").forEach(x=>x.disabled=LV==="view");$("#tll").innerHTML="";$("#tldg").showModal();await tlLoad()}
const revCol=()=>cols.find(c=>/^rev(ision)?\.?$/i.test(String(c.name).trim()));
function revCtl(v,attr,id){const c=revCol();v=v==null?"":String(v);
  if(c&&c.ctype==="dropdown"){const o=(c.options||[]).slice();if(v&&!o.includes(v))o.push(v);return `<select ${attr} id="${id||""}" style="width:auto;min-width:54px"><option value=""></option>${o.map(x=>`<option${x===v?" selected":""}>${esc(x)}</option>`).join("")}</select>`}
  return `<input ${attr} id="${id||""}" value="${ea(v)}" maxlength="20" style="width:64px" placeholder="Rev">`}
async function tlLoad(){try{const r=await api("tl_list",{task_id:tlId});let tot=0;r.logs.forEach(l=>tot+=+l.hours);
  $("#tll").innerHTML=r.logs.length?`<table class="tlt"><tr><th>Date</th><th>Person</th><th class="n">Hours</th>${revCol()?"<th>Rev</th>":""}<th>Note</th><th></th></tr>${r.logs.map(l=>`<tr><td>${esc(l.work_date)}</td><td>${esc(l.name)}${l.logged_by&&l.logged_by!=l.user_id?' <small style="color:var(--mut)">(logged by someone else)</small>':""}</td><td class="n">${fmtH(l.hours)}${l.kind==="OT"?" <b>OT</b>":""}</td>${revCol()?`<td>${(l.user_id==r.me||me.role==="admin")&&LV!=="view"?revCtl(l.rev,`data-tlr="${l.id}"`):esc(l.rev||"")}</td>`:""}<td>${esc(l.note)}</td><td>${l.user_id==r.me||me.role==="admin"?`<button class="x" data-tld="${l.id}" title="Delete" style="border:0;background:none;width:auto;cursor:pointer">✕</button>`:""}</td></tr>`).join("")}<tr><td colspan="2"><b>Total</b></td><td class="n"><b>${fmtH(tot)}</b></td><td colspan="${revCol()?3:2}"></td></tr></table>`:`<div class="empty">No hours logged yet</div>`}catch(e){}}
async function tlSend(assign){const h=parseFloat($("#tlh").value);
  try{await api("tl_add",{task_id:tlId,date:$("#tld").value,hours:h,kind:$("#tlk").value,note:$("#tln").value,rev:$("#tlr")?$("#tlr").value:"",assign_to:assign||0});$("#tlh").value="";$("#tln").value="";await tlLoad();await load(true)}catch(e){alert(e.message)}}
$("#tla").onclick=()=>{const h=parseFloat($("#tlh").value);if(!(h>0&&h<=24)){alert("Enter hours between 0.25 and 24");return}
  const t=tasks.find(x=>x.id==tlId);if(t&&!t.assigned_to&&me.role==="admin"){$("#asw").innerHTML='<option value="">Choose a person…</option>'+users.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join("");$("#asw").value=String(me.id||"");$("#asdg").showModal();return}
  tlSend(0)};
$("#asx").onclick=()=>$("#asdg").close();
$("#asok").onclick=()=>{const v=+$("#asw").value;if(!v){alert("Choose a person");return}$("#asdg").close();tlSend(v)};
$("#tll").addEventListener("change",async e=>{const id=e.target.dataset.tlr;if(!id)return;try{await api("tl_rev",{id,rev:e.target.value})}catch(err){alert(err.message);await tlLoad()}});
$("#tll").addEventListener("click",async e=>{const id=e.target.dataset.tld;if(!id)return;try{await api("tl_del",{id});await tlLoad();await load(true)}catch(err){alert(err.message)}});
$("#tlcl").onclick=()=>$("#tldg").close();
$("#tlb").onclick=()=>{const id=editing;if(!id)return;$("#dlg").close();openTL(id)};
// ---- Company details & hourly rates (admin) ----
let coId=0,coSy0=0;
async function apiW(a,data,wid){const r=await fetch("api.php?a="+a+"&w="+wid,{method:"POST",headers:{"Content-Type":"application/json","X-Requested-With":"fetch"},body:JSON.stringify(data||{})});if(r.status===401){location="login.php";throw new Error("auth")}const j=await r.json();if(j.error)throw new Error(j.error);return j}
async function coOpen(id){coId=id;const w=wss.find(x=>x.id==id);try{const r=await apiW("co_get",{},id),c=r.company||{};
  $("#cot").textContent=(w?w.name:"Company")+" — details & hourly rates";$("#con").value=w?w.name:"";$("#cosy").innerHTML='<option value="0">Not linked</option>'+wss.filter(x=>x.id!=id).map(x=>`<option value="${x.id}">${esc(x.name)}</option>`).join("");$("#cosy").value=r.colsync||0;coSy0=+(r.colsync||0);$("#coc").value=c.client_name||"";$("#coa").value=c.client_addr||"";$("#cob").value=c.bank||"";$("#coy").value=c.currency||"";$("#cos").value=c.symbol||"";$("#cod").value=c.rate||"";$("#coo").value=c.ot==null?1:c.ot;$("#colay").value=["items","summary"].includes(c.layout)?c.layout:"worklog";$("#cowt").value=c.wl_title||"";$("#copx").value=c.prefix||"";$("#cosf").value=c.suffix||"";$("#cosq").value=c.seq||"1";$("#copo").value=c.po||"";$("#cosl").value=c.sec_labels||"";
  $("#cor").innerHTML=allUsers.map(u=>`<label>${esc(u.name)}<input type="number" min="0" step="0.01" data-cr="${u.id}" value="${c.rates&&c.rates[u.id]!==undefined?c.rates[u.id]:""}"></label>`).join("")||'<span style="color:var(--mut)">No users yet</span>';
  $("#codlg").showModal()}catch(e){alert(e.message)}}
$("#cox").onclick=()=>$("#codlg").close();
$("#cosv").onclick=async()=>{const rates={};document.querySelectorAll("#cor [data-cr]").forEach(i=>{if(i.value!=="")rates[i.dataset.cr]=+i.value});
  const profile={name:"",client_name:$("#coc").value,client_addr:$("#coa").value,bank:$("#cob").value,currency:$("#coy").value,symbol:$("#cos").value,rate:+$("#cod").value||0,ot:$("#coo").value===""?1:+$("#coo").value,layout:$("#colay").value,wl_title:$("#cowt").value,prefix:$("#copx").value,suffix:$("#cosf").value,seq:$("#cosq").value.replace(/\D/g,"")||"1",po:$("#copo").value,sec_labels:$("#cosl").value,rates};
  try{const w=wss.find(x=>x.id==coId),n=$("#con").value.trim();if(n&&w&&n!==w.name)await apiW("ws_rename",{id:coId,name:n},coId);
    await apiW("inv_set",{slot:0,profile},coId);if(+$("#cosy").value!==coSy0)await apiW("ws_colsync",{id:coId,with:+$("#cosy").value},coId);$("#codlg").close();await load(true);renderWs()}catch(e){alert(e.message)}};
// ---- Worklog & invoice reports ----
let rrows=[],rprofs=[],rcsvRows=[],rComp={rates:{},rate:0,ot:1};
const rShift=n=>{const d=new Date();d.setDate(d.getDate()+n);return d.toLocaleDateString("en-CA")};
const rPreset=(a,b)=>{$("#rf").value=rShift(a);$("#rt").value=rShift(b);runReport()};
const money=n=>(+n).toLocaleString("en",{minimumFractionDigits:2,maximumFractionDigits:2});
const rDef=()=>({slot:0,name:"Invoice",match_cust:"",match_order:"",client_name:"",client_addr:"",currency:"",symbol:"",layout:"worklog",rate:0,rates:{},ot:1,bank:"",prefix:"",suffix:"",seq:1});
function isoWeek(s){const d=new Date(s+"T00:00:00Z");const n=d.getUTCDay()||7;d.setUTCDate(d.getUTCDate()+4-n);const y=new Date(Date.UTC(d.getUTCFullYear(),0,1));return Math.ceil(((d-y)/864e5+1)/7)}
const rList=()=>rprofs.length?rprofs:[rDef()];
const rCur=()=>{const l=rList(),v=$("#rpf").value;return l.find(p=>String(p.slot)===v)||l[0]};
const splitC=s=>String(s||"").split(",").map(x=>x.trim().toLowerCase()).filter(Boolean);
const rClaims=(p,x)=>{const mc=splitC(p.match_cust),mo=String(p.match_order||"").trim().toLowerCase();return (mc.length&&mc.includes(String(x.customer||"").trim().toLowerCase()))||(mo&&String(x.order_no||"").toLowerCase().indexOf(mo)===0)};
const hasM=p=>splitC(p.match_cust).length||String(p.match_order||"").trim();
function rProfRows(p){const L=rList();return rrows.filter(x=>hasM(p)?rClaims(p,x):!L.some(q=>q!==p&&hasM(q)&&rClaims(q,x)))}
const rRate=(p,uid)=>{const k=String(uid),v=p.rates&&p.rates[k];if(v!==undefined&&v!=="")return +v;if(+p.rate>0)return +p.rate;const c=rComp.rates&&rComp.rates[k];return c!==undefined&&c!==""?+c:(+rComp.rate||0)};
const rEff=p=>{const e=Object.assign({},p);["client_name","client_addr","currency","symbol","bank","wl_title","po","sec_labels","prefix","suffix"].forEach(k=>{if(!String(e[k]||"").trim())e[k]=rComp[k]||""});
  e.layout=[p.layout,rComp.layout].includes("summary")?"summary":[p.layout,rComp.layout].includes("items")?"items":"worklog";if(!p.slot&&+rComp.seq>0)e.seq=String(rComp.seq);if(!e.prefix&&!p.slot)e.prefix="INVOICE-";if(!e.currency)e.currency="AUD";if(!e.symbol)e.symbol="$";if(!(+e.ot>0)||+e.ot===1)e.ot=+rComp.ot>0?+rComp.ot:1;return e};
const rCost=(p,x)=>x.hours*rRate(p,x.user_id)*(x.kind==="OT"?(+p.ot||0):1);
let rPend=null;
function rInvNo(p){if(rEdit)return rEdit.no;return (p.prefix||"")+$("#rt").value.replace(/-/g,"")+"-"+p.seq+(p.suffix||"")}
function rSections(p,rows){const m=new Map();for(const x of rows){const k=x.top_name||"(No group)";if(!m.has(k))m.set(k,[]);m.get(k).push(x)}return m}
function rLines(rows){const m=new Map();for(const x of rows){const k=x.work_date+"|"+x.task_id+"|"+(x.kind==="OT"?"OT":"R");let l=m.get(k);if(!l){l={date:x.work_date,x,kind:x.kind==="OT"?"OT":"R",by:{},notes:[],hrs:0,cost:0};m.set(k,l)}l.by[x.user_id]=(l.by[x.user_id]||0)+ +x.hours;l.hrs+=+x.hours;if(x.note&&!l.notes.includes(x.note))l.notes.push(x.note)}
  return [...m.values()].sort((a,b)=>a.date<b.date?-1:a.date>b.date?1:String(a.x.order_no).localeCompare(String(b.x.order_no)))}
const LH="data:image/png;base64,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";
const ISSUER_ADDR="HOUSE-E/4, ROAD-124, HOUSING STATE KHALISHPUR, KHULNA-9000, BANGLADESH";
const PCOL=["#fce4d6","#b4c6e7","#ffe699","#5b9bd5","#ffd966","#f8cbad","#a9d08e","#d9d2e9","#c9daf8","#fff2cc"];
const num=n=>String(Math.round((+n||0)*100)/100),h1=n=>(+n||0).toFixed(1);
const dmy=s=>s.slice(8,10)+"/"+s.slice(5,7)+"/"+s.slice(2,4);
const rDefRate=p=>+p.rate>0?+p.rate:(+rComp.rate||0);
function secLabel(p,n){for(const l of String(p.sec_labels||"").split("\n")){const k=l.indexOf("=");if(k>0&&l.slice(0,k).trim().toLowerCase()===n.toLowerCase())return l.slice(k+1).trim()||n}return n}
function secOrder(names){const go=groups.filter(g=>!g.level).map(g=>g.name.toLowerCase());return [...names].sort((a,b)=>{const x=go.indexOf(a.toLowerCase()),y=go.indexOf(b.toLowerCase());return (x<0?999:x)-(y<0?999:y)||a.localeCompare(b)})}
function wlPage(p,name,rows,P,csv){
  const L=rLines(rows),dr=rDefRate(p),sp=P.filter(([id])=>rRate(p,id)!==dr),first=s=>{const w=String(s).split(/\s+/).filter(Boolean),k=w.filter(x=>!/^(md\.?|mohammad|mohammed|muhammad|mst\.?)$/i.test(x));return (k[0]||w[0]||"")};
  let th=0,tc=0;const pt={};P.forEach(([id])=>pt[id]=0);const cc=i=>PCOL[i%PCOL.length];
  const rateH=(sp.length?sp.map(([,n])=>`<th class="rt1">${esc(first(n))}'s<br>Rate</th>`).join("")+'<th class="rt1">Others<br>Rate</th>':'<th class="rt1">Rate</th>');
  const wk=isoWeek($("#rf").value)+"-"+isoWeek($("#rt").value);
  const body=L.map(l=>{l.cost=0;for(const id in l.by){l.cost+=l.by[id]*rRate(p,id)*(l.kind==="OT"?(+p.ot||0):1);pt[id]+=l.by[id]}th+=l.hrs;tc+=l.cost;
    const sub=l.x.sub_name||l.x.top_name||"",rem=l.notes.join("; ");
    csv.push([name,l.date,isoWeek(l.date),sub,l.x.order_no,l.x.title].concat(P.map(([id])=>l.by[id]||""),[l.kind==="OT"?"OT":"Regular",+l.hrs.toFixed(2)],sp.map(([id])=>rRate(p,id)),[dr,+l.cost.toFixed(2),rem]));
    return `<tr><td>${esc(l.date)}</td><td class="c">${isoWeek(l.date)}</td><td>${esc(sub)}</td><td>${esc(l.x.order_no||"")}</td><td>${esc(l.x.title)}</td>${P.map(([id],i)=>`<td class="c" style="background:${cc(i)}66">${l.by[id]?h1(l.by[id]):""}</td>`).join("")}<td class="k">${l.kind==="OT"?"OT":"Regular"}</td><td class="c">${h1(l.hrs)}</td>${sp.map(([id])=>`<td class="c">${num(rRate(p,id))}</td>`).join("")}<td class="c">${num(dr)}</td><td class="c">${num(l.cost)}</td><td>${esc(rem)}</td></tr>`}).join("");
  csv.push([name,"","","","","TOTAL"].concat(P.map(([id])=>+pt[id].toFixed(2)),["",+th.toFixed(2)],sp.map(()=>""),["",+tc.toFixed(2),""]));
  const nr=sp.length+1,ncol=5+P.length+2+nr+2;
  return {th,tc,html:`<div class="pg pgwl"><table class="wlt"><tr class="band"><td colspan="3" class="gtt"><b style="font-size:13px">DailyWorkLog:</b> &nbsp; <span>${esc(name)}</span><br><b>Wk: ${wk}</b></td><td colspan="2" class="bt">${esc(p.wl_title||p.client_name||"")}</td><td colspan="${P.length}" class="gtt"></td><td class="gtt c"></td><td colspan="${nr+3}" class="gtt"></td></tr>
   <tr class="wlh"><th class="lg">Date</th><th class="lg c">Week</th><th class="lg">Project Group</th><th class="lg">Project No</th><th class="lg">Project Name</th>${P.map(([,n],i)=>`<th class="c" style="background:${cc(i)};min-width:62px">${esc(n)}</th>`).join("")}<th class="pk">Regular/ OT</th><th class="rt1">Total Hrs</th>${rateH}<th class="rt1">Total Cost<br>(${esc(p.currency)})</th><th class="rt1">Remarks</th></tr>${body}
   <tr class="ft"><td colspan="5"></td>${P.map(([id])=>`<td class="c">${h1(pt[id])}</td>`).join("")}<td class="k"><b>Total Hours:</b></td><td class="c">${h1(th)}</td>${sp.map(()=>"<td></td>").join("")}<td class="c"><b>Total Amount:</b></td><td class="c">${num(tc)}</td><td></td></tr></table></div>`}}
function invTop(p){const to=$("#rt").value;return `<img class="lh" src="${LH}" alt=""><div class="invt">INVOICE</div>`}
const invBottom=p=>`<div class="invf"><div class="ifbar"></div><div class="ifad">${esc(ISSUER_ADDR)}</div></div>`;
const DEF_BANK="Bank Name: Bank Asia Ltd.\nSWIFT: BALBBDDH\nAccount Name: Guru Cadd\nAccount No: 02333001410";
function payBox(p){const bk=String(p.bank||"").trim()||DEF_BANK;return `<div class="paybox">Payments can be made to-<br>${bk.split("\n").map(l=>{const k=l.indexOf(":");return k>0?esc(l.slice(0,k+1))+" <b>"+esc(l.slice(k+1).trim())+"</b>":esc(l)}).join("<br>")}</div>`}
function invCatPage(p,secs){const from=$("#rf").value,to=$("#rt").value,yy=to.slice(2,4),wk=isoWeek(from)+"-"+isoWeek(to)+"_"+yy;let tot=0;
  const cells=secs.map(s=>{tot+=s[2];return `<td class="c">${esc(p.symbol)}${num(s[2])}</td>`}).join("");
  return `<div class="pg pginv">${invTop(p)}<table class="itb"><tr><th class="l">INVOICE NO:</th><td class="b">${esc(rInvNo(p))}</td><th class="l">DATE:</th><td class="b c">${esc(to)}</td></tr><tr><td colspan="4" class="c"><b style="font-size:13px">CLIENT:</b> ${esc([p.client_name,p.client_addr].filter(Boolean).join(", "))}</td></tr><tr><td colspan="4" class="spc"></td></tr></table>
  <table class="itb"><tr><td colspan="3" class="c">INVOICE WEEK</td><td colspan="${secs.length}" class="g"></td><td rowspan="2" class="g c">Total<br>(${esc(p.currency)})</td></tr>
  <tr><td class="c">WK</td><td class="c">START<br>DATE</td><td class="c">END DATE</td>${secs.map(s=>`<td class="c">${esc(secLabel(p,s[0]))}</td>`).join("")}</tr>
  <tr><td class="c">${wk}</td><td class="c">${esc(from)}</td><td class="c">${esc(to)}</td>${cells}<td class="c">${esc(p.symbol)}${num(tot)}</td></tr><tr><td colspan="${3+secs.length}" style="height:14px"></td><td></td></tr>
  <tr><td colspan="${3+secs.length}">Invoiced Total</td><td class="c">${esc(p.symbol)}${num(tot)}</td></tr></table>${payBox(p)}${invBottom(p)}</div>`}
function invItemsPage(p,rows){const from=$("#rf").value,to=$("#rt").value,m=new Map();for(const x of rows){let r=m.get(x.task_id);if(!r){r={x,h:0,c:0};m.set(x.task_id,r)}r.h+=+x.hours;r.c+=rCost(p,x)}
  let th=0,tc=0;const body=[...m.values()].map(r=>{th+=r.h;tc+=r.c;return `<tr><td>${esc(r.x.title)}</td><td>${esc(r.x.sub_name||r.x.top_name||"")}</td><td class="c">${num(r.h)}</td><td class="c">${num(r.c)}</td></tr>`}).join("");
  return {tc,th,html:`<div class="pg pginv">${invTop(p)}<table class="itb"><tr><th class="l">INVOICE NO:</th><td class="b c" style="font-size:10.5px">${esc(rInvNo(p))}</td><th class="l">DATE:</th><td class="b c">${esc(to)}</td><th class="l">PO NO</th><td class="po">${esc(p.po||"")}</td></tr><tr><td colspan="6" class="c"><b style="font-size:13px">CLIENT:</b> ${esc([p.client_name,p.client_addr].filter(Boolean).join(", "))}</td></tr><tr><td colspan="6" class="spc"></td></tr></table>
  <table class="itb nb"><tr><td colspan="3">INVOICE WEEK</td><td class="g c">${isoWeek(from)}-${isoWeek(to)}</td></tr><tr><td colspan="3">Duration:</td><td class="g c">${dmy(from)} - ${dmy(to)}</td></tr>
  <tr class="g"><th class="a">ACTIONED ITEMS</th><th class="a">ITEM GROUP</th><th class="c">HOURS<br>SPENT</th><th class="c">SUB-TOTAL<br>(${esc(p.currency)})</th></tr>${body}<tr><td colspan="4" class="spc"></td></tr><tr><td colspan="3">Invoiced Total (${esc(p.currency)})</td><td class="c">${num(tc)}</td></tr></table>${payBox(p)}${invBottom(p)}</div>`}}
let rTot=null;
function smPage(p,rows,csv){const from=$("#rf").value,to=$("#rt").value,m=new Map();for(const x of rows){const k=x.task_id;let r=m.get(k);if(!r){r={x,h:0,c:0};m.set(k,r)}r.h+=+x.hours;r.c+=rCost(p,x)}
  const cn=r=>String(r.x.customer||r.x.top_name||"").trim();
  const L=[...m.values()].sort((a,b)=>cn(a).localeCompare(cn(b))||String(a.x.title).localeCompare(String(b.x.title)));let th=0,tc=0,oth=0,dr=0;
  const body=L.map(r=>{th+=r.h;tc+=r.c;if(/^others?$/i.test(r.x.top_name||""))oth+=r.c;else dr+=r.c;const rt=r.h?r.c/r.h:0;csv.push([cn(r),r.x.title,+r.h.toFixed(2),+rt.toFixed(2),+r.c.toFixed(2)]);
    return `<tr><td>${esc(cn(r))}</td><td>${esc(r.x.title)}</td><td class="c">${num(r.h)}</td><td class="c">${num(rt)}</td><td class="c">${num(r.c)}</td></tr>`}).join("");
  csv.push(["","Total",+th.toFixed(2),"",+tc.toFixed(2)]);
  const no=rInvNo(p).replace(/^INVOICE-/i,"");
  return {th,tc,dr,oth,html:`<div class="pg pgsm"><table class="smt"><colgroup><col style="width:27%"><col style="width:22%"><col style="width:22%"><col style="width:15%"><col style="width:14%"></colgroup><tr class="bar"><td colspan="5">Invoice Report: ${esc(no)}</td></tr><tr class="bar"><td colspan="5" style="text-align:center;font-size:24px;padding:4px">${esc(p.wl_title||p.client_name||"")}</td></tr><tr class="bar"><td colspan="5">Duration: ${dmy(from)} -${dmy(to)}</td></tr>
  <tr><th>Client Name</th><th>ACTIONED ITEMS</th><th class="c">HOURS SPENT</th><th class="c">Hourly Rate</th><th class="c">Cost(${esc(p.currency)})</th></tr>${body}<tr><td></td><td></td><td colspan="2" class="cb">Total Cost(${esc(p.currency)})</td><td class="c">${num(tc)}</td></tr></table></div>`}}
function drawReport(){
  const p=rEff(rCur()),rows=rProfRows(rCur()),vw=$("#rvw").value,csv=[];let h0="";
  if(!rows.length){rcsvRows=[];$("#rout").innerHTML='<div class="empty">No hours logged for this company in this period.</div>';return}
  const pp=new Map();rrows.forEach(x=>pp.set(x.user_id,x.person));const P=[...pp.entries()].sort((a,b)=>(+a[0])-(+b[0]));
  let pages="",inv="";
  if(p.layout==="summary"){const t=smPage(p,rows,csv);rcsvRows=[["Client Name","Actioned items","Hours spent","Hourly rate","Cost ("+p.currency+")"]].concat(csv);
    const iv=invCatPage(p,[["DRAWING AND ERP SERVICE",0,t.dr],["Others",0,t.oth]]);rTot={amount:t.tc,hours:t.th,html:t.html+iv};if(!t.tc)h0=RWARN;
    if(!p.client_name)h0+='<div class="noprint" style="background:#fff4d6;color:#7a5200;padding:8px 10px;border-radius:6px;margin:8px 0">Client name and address are empty. Fill them in <b>Manage → company → 💲 Details &amp; rates</b>.</div>';
    $("#rout").innerHTML=h0+(vw==="inv"?"":t.html)+(vw==="log"?"":iv);return}
  const secs=rSections(p,rows),names=secOrder([...secs.keys()]),lines=[];
  if(p.layout==="items"){const t=wlPage(p,(wss.find(w=>w.id==W)||{}).name||names.join(" / "),rows,P,csv);pages+=t.html}
  else for(const n of names){const t=wlPage(p,n,secs.get(n),P,csv);lines.push([n,t.th,t.tc]);pages+=t.html}
  rcsvRows=[["Group","Date","Week","Project group","Project No","Project name"].concat(P.map(x=>x[1]),["Type","Total hrs"],"(rates)","Total cost "+p.currency,"Remarks")].concat(csv);
  if(p.layout==="items"){const it=invItemsPage(p,rows);inv=it.html;rTot={amount:it.tc,hours:it.th};if(!it.tc)h0=RWARN}else{inv=invCatPage(p,lines);rTot={amount:lines.reduce((s,l)=>s+l[2],0),hours:lines.reduce((s,l)=>s+l[1],0)};if(!lines.some(l=>l[2]))h0=RWARN}rTot.html=pages+inv;
  if(!p.client_name)h0+='<div class="noprint" style="background:#fff4d6;color:#7a5200;padding:8px 10px;border-radius:6px;margin:8px 0">Client name and address are empty. Fill them in <b>Manage → company → 💲 Details &amp; rates</b>.</div>';
  $("#rout").innerHTML=h0+(vw==="inv"?"":pages)+(vw==="log"?"":inv)}
const RWARN='<div class="noprint" style="background:#fff4d6;color:#7a5200;padding:8px 10px;border-radius:6px;margin:8px 0">No hourly rates are set, so cost shows 0. Set them in <b>Manage → company → 💲 Details &amp; rates</b>.</div>';
async function runReport(){const from=$("#rf").value,to=$("#rt").value;if(!from||!to)return;
  try{const r=await api("tl_report",{from,to});rrows=r.rows;rprofs=r.profiles||[];rComp=r.company||rComp;const cur=rPend!=null?String(rPend):$("#rpf").value;rPend=null;
    $("#rpf").innerHTML=rList().map(p=>`<option value="${p.slot}">${esc(p.name)}</option>`).join("")+`<option value="new">＋ New profile…</option>`;$("#rpf").value=rList().some(p=>String(p.slot)===cur)?cur:rList()[0].slot;drawReport()}catch(e){alert(e.message)}}
$("#rm").onclick=()=>{if(!$("#rf").value){$("#rf").value=rShift(-13);$("#rt").value=rShift(0)}$("#rdlg").showModal();runReport()};
$("#rcl").onclick=()=>$("#rdlg").close();
$("#r14").onclick=()=>rPreset(-13,0);
(()=>{const d=new Date(),o=[["","Standard period…"]];const f=(y,m,a,b)=>new Date(Date.UTC(y,m,a)).toISOString().slice(0,10);
  for(let k=0;k<4;k++){const y=new Date(d.getFullYear(),d.getMonth()-k,1),Y=y.getFullYear(),M=y.getMonth(),nm=y.toLocaleString("en",{month:"short"})+" "+Y,last=new Date(Y,M+1,0).getDate();
    o.push([f(Y,M,1)+"|"+f(Y,M,15),nm+" · 1st – 15th"],[f(Y,M,16)+"|"+f(Y,M,last),nm+" · 16th – "+last+"th"],[f(Y,M,1)+"|"+f(Y,M,last),nm+" · whole month"])}
  $("#rpp").innerHTML=o.map(x=>`<option value="${x[0]}">${x[1]}</option>`).join("");
  $("#rpp").onchange=()=>{const v=$("#rpp").value;if(!v)return;const [a,b]=v.split("|");$("#rf").value=a;$("#rt").value=b;runReport()}})();
$("#rf").addEventListener("change",runReport);$("#rt").addEventListener("change",runReport);$("#rvw").addEventListener("change",drawReport);
$("#rpf").addEventListener("change",()=>{if($("#rpf").value==="new"){openProf(null);return}drawReport()});
$("#rpr").onclick=()=>{$("#pbox").innerHTML=$("#rout").innerHTML;window.print()};window.addEventListener("afterprint",()=>{$("#pbox").innerHTML=""});
// ---- Real .xlsx writer: turns the on-screen report pages (tables) into Excel sheets, keeping colours, borders and merges ----
const XL={crcT:(()=>{const t=new Uint32Array(256);for(let n=0;n<256;n++){let c=n;for(let k=0;k<8;k++)c=c&1?0xEDB88320^(c>>>1):c>>>1;t[n]=c>>>0}return t})()};
XL.crc=u=>{let c=0xFFFFFFFF;for(let i=0;i<u.length;i++)c=XL.crcT[(c^u[i])&255]^(c>>>8);return(c^0xFFFFFFFF)>>>0};
XL.zip=files=>{const enc=new TextEncoder(),parts=[],cd=[];let off=0;const w16=(a,v)=>a.push(v&255,(v>>8)&255),w32=(a,v)=>a.push(v&255,(v>>8)&255,(v>>16)&255,(v>>>24)&255);
  for(const[name,str]of files){const n=enc.encode(name),d=enc.encode(str),crc=XL.crc(d),h=[];w32(h,0x04034b50);w16(h,20);w16(h,0x0800);w16(h,0);w16(h,0);w16(h,0x21);w32(h,crc);w32(h,d.length);w32(h,d.length);w16(h,n.length);w16(h,0);parts.push(new Uint8Array(h),n,d);
    const c=[];w32(c,0x02014b50);w16(c,20);w16(c,20);w16(c,0x0800);w16(c,0);w16(c,0);w16(c,0x21);w32(c,crc);w32(c,d.length);w32(c,d.length);w16(c,n.length);w16(c,0);w16(c,0);w16(c,0);w16(c,0);w32(c,0);w32(c,off);cd.push(new Uint8Array(c),n);off+=h.length+n.length+d.length}
  let cl=0;for(const x of cd)cl+=x.length;const e=[];w32(e,0x06054b50);w16(e,0);w16(e,0);w16(e,files.length);w16(e,files.length);w32(e,cl);w32(e,off);w16(e,0);
  return new Blob([...parts,...cd,new Uint8Array(e)],{type:"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"})};
XL.esc=s=>String(s).replace(/[&<>"]/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;"}[c])).replace(/[\u0000-\u0008\u000b\u000c\u000e-\u001f]/g,"");
XL.col=n=>{let s="";n++;while(n>0){const m=(n-1)%26;s=String.fromCharCode(65+m)+s;n=Math.floor((n-1)/26)}return s};
XL.rgb=c=>{if(!c)return null;let m=c.match(/^rgba?\(([\d.]+)[, ]+([\d.]+)[, ]+([\d.]+)(?:[,/ ]+([\d.]+%?))?\)/),r,g,b,a=1;
  if(m){r=+m[1];g=+m[2];b=+m[3];if(m[4]!==undefined)a=m[4].endsWith("%")?parseFloat(m[4])/100:+m[4]}
  else{m=c.match(/^color\(srgb ([\d.]+) ([\d.]+) ([\d.]+)(?: \/ ([\d.]+%?))?\)/);if(!m)return null;r=m[1]*255;g=m[2]*255;b=m[3]*255;if(m[4]!==undefined)a=m[4].endsWith("%")?parseFloat(m[4])/100:+m[4]}
  if(a<0.05)return null;if(a<1){r=r*a+255*(1-a);g=g*a+255*(1-a);b=b*a+255*(1-a)}
  return[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,"0")).join("").toUpperCase()};
XL.bg=el=>{for(let e=el;e&&e.nodeType===1;e=e.parentElement){if(e.classList&&e.classList.contains("pg"))return null;const c=XL.rgb(getComputedStyle(e).backgroundColor);if(c)return c==="FFFFFF"?null:c}return null};
XL.styler=()=>{const fonts=[],fills=[],borders=[],xfs=[],idx=(arr,o)=>{const k=JSON.stringify(o);let i=arr.findIndex(x=>x.k===k);if(i<0){arr.push({k,o});i=arr.length-1}return i};
  idx(fonts,{sz:11,b:0,i:0,c:"000000"});idx(fills,{p:"none"});idx(fills,{p:"gray125"});idx(borders,{});idx(xfs,{f:0,fl:0,b:0,h:"",v:""});
  return{fonts,fills,borders,xfs,get(el,isTd){const cs=getComputedStyle(el),fc=XL.rgb(cs.color)||"000000",bgc=XL.bg(el),sz=Math.max(7,Math.round(parseFloat(cs.fontSize)*0.75*2)/2),bold=(+cs.fontWeight>=600||cs.fontWeight==="bold")?1:0,it=cs.fontStyle==="italic"?1:0;
    const side=s=>{const w=parseFloat(cs["border"+s+"Width"]);if(!(w>0)||cs["border"+s+"Style"]==="none")return null;return XL.rgb(cs["border"+s+"Color"])||"000000"};
    const bo={};if(isTd)for(const s of["Top","Right","Bottom","Left"]){const c=side(s);if(c)bo[s.toLowerCase()]=c}
    let h=cs.textAlign;h=h==="center"?"center":(h==="right"||h==="end")?"right":"left";const vv=cs.verticalAlign,v=vv==="top"?"top":vv==="bottom"?"bottom":"center";
    return idx(xfs,{f:idx(fonts,{sz,b:bold,i:it,c:fc}),fl:bgc?idx(fills,{p:"solid",c:bgc}):0,b:idx(borders,bo),h,v})},
  xml(){const fx=fonts.map(f=>`<font>${f.o.b?"<b/>":""}${f.o.i?"<i/>":""}<sz val="${f.o.sz}"/><color rgb="FF${f.o.c}"/><name val="Calibri"/></font>`).join(""),
    fl=fills.map(f=>f.o.p==="solid"?`<fill><patternFill patternType="solid"><fgColor rgb="FF${f.o.c}"/><bgColor indexed="64"/></patternFill></fill>`:`<fill><patternFill patternType="${f.o.p}"/></fill>`).join(""),
    bo=borders.map(b=>`<border>${["left","right","top","bottom"].map(s=>b.o[s]?`<${s} style="thin"><color rgb="FF${b.o[s]}"/></${s}>`:`<${s}/>`).join("")}<diagonal/></border>`).join(""),
    xf=xfs.map(x=>`<xf numFmtId="0" fontId="${x.o.f}" fillId="${x.o.fl}" borderId="${x.o.b}" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="${x.o.h||"left"}" vertical="${x.o.v||"center"}" wrapText="1"/></xf>`).join("");
    return `<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="${fonts.length}">${fx}</fonts><fills count="${fills.length}">${fl}</fills><borders count="${borders.length}">${bo}</borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="${xfs.length}">${xf}</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>`}}};
XL.sheet=(pg,st,name)=>{const pr=pg.getBoundingClientRect(),rowsOut=[];const items=[];
  const cellText=el=>(el.innerText||"").replace(/ /g," ").replace(/[ \t]+\n/g,"\n").trim();
  const leaf=el=>{if(el.nodeType!==1)return;const tag=el.tagName;if(tag==="IMG"||tag==="STYLE"||tag==="SCRIPT")return;const r=el.getBoundingClientRect();if(r.width<2||r.height<1)return;
    if(tag==="TABLE"){for(const tr of el.rows){const tr_r=tr.getBoundingClientRect(),cells=[];for(const td of tr.cells){const r=td.getBoundingClientRect();cells.push({x0:r.left-pr.left,x1:r.right-pr.left,rs:td.rowSpan||1,td})}items.push({h:tr_r.height,cells})}items.push({h:6,cells:[]});return}
    const kids=[...el.children].filter(k=>k.tagName!=="BR"&&k.tagName!=="SUP"&&k.tagName!=="SUB"&&k.tagName!=="B"&&k.tagName!=="SPAN");
    if(kids.length){for(const k of kids)leaf(k);return}
    const tx=cellText(el),bgc=XL.bg(el);if(!tx&&!bgc)return;items.push({h:r.height,cells:[{x0:r.left-pr.left,x1:r.right-pr.left,rs:1,td:el,text:tx}],blk:1})};
  for(const k of pg.children)leaf(k);
  // unified column edges
  let edges=[];for(const it of items)for(const c of it.cells){edges.push(c.x0,c.x1)}edges.sort((a,b)=>a-b);const E=[];for(const e of edges)if(!E.length||e-E[E.length-1]>1.5)E.push(e);
  const ci=x=>{let b=0,bd=1e9;E.forEach((e,i)=>{const d=Math.abs(e-x);if(d<bd){bd=d;b=i}});return b};
  const grid=[],merges=[];items.forEach((it,ri)=>{grid[ri]=grid[ri]||{};for(const c of it.cells){const c1=ci(c.x0),c2=Math.max(c1,ci(c.x1)-1),r2=ri+c.rs-1,sid=st.get(c.td,true);
      let tx=c.text!==undefined?c.text:cellText(c.td),val=tx,num=false;if(/^-?(0|[1-9]\d{0,8})(\.\d+)?$/.test(tx)){num=true;val=+tx}
      for(let r=ri;r<=r2;r++){grid[r]=grid[r]||{};for(let cc=c1;cc<=c2;cc++){grid[r][cc]=(r===ri&&cc===c1)?{s:sid,v:tx===""?null:val,n:num}:{s:sid,v:null}}}
      if(r2>ri||c2>c1)merges.push(`${XL.col(c1)}${ri+1}:${XL.col(c2)}${r2+1}`)}});
  const nc=Math.max(1,E.length-1);let xml=`<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetPr><pageSetUpPr fitToPage="1"/></sheetPr><sheetViews><sheetView showGridLines="0" workbookViewId="0"/></sheetViews><sheetFormatPr defaultRowHeight="15"/><cols>`;
  for(let i=0;i<nc;i++)xml+=`<col min="${i+1}" max="${i+1}" width="${Math.max(2,Math.round((E[i+1]-E[i])/6.4*10)/10)}" customWidth="1"/>`;
  xml+="</cols><sheetData>";items.forEach((it,ri)=>{const g=grid[ri]||{},ks=Object.keys(g).map(Number).sort((a,b)=>a-b);xml+=`<row r="${ri+1}" ht="${Math.max(6,Math.round(it.h*0.75*10)/10)}" customHeight="1">`;
    for(const k of ks){const c=g[k],ref=XL.col(k)+(ri+1);xml+=c.v===null?`<c r="${ref}" s="${c.s}"/>`:c.n?`<c r="${ref}" s="${c.s}"><v>${c.v}</v></c>`:`<c r="${ref}" s="${c.s}" t="inlineStr"><is><t xml:space="preserve">${XL.esc(c.v)}</t></is></c>`}xml+="</row>"});
  xml+="</sheetData>"+(merges.length?`<mergeCells count="${merges.length}">${merges.map(m=>`<mergeCell ref="${m}"/>`).join("")}</mergeCells>`:"")+`<pageMargins left="0.3" right="0.3" top="0.4" bottom="0.4" header="0.2" footer="0.2"/><pageSetup paperSize="${pg.classList.contains("pgwl")?8:9}" orientation="${pg.classList.contains("pgwl")?"landscape":"portrait"}" fitToWidth="1" fitToHeight="0"/></worksheet>`;return xml};
XL.book=(pages,names)=>{const st=XL.styler(),used=new Set(),sheets=pages.map((pg,i)=>{let n=String(names[i]||"Sheet "+(i+1)).replace(/[\\\/?*\[\]:]/g," ").trim().slice(0,28)||"Sheet "+(i+1),b=n,k=2;while(used.has(n.toLowerCase()))n=b.slice(0,26)+" "+k++;used.add(n.toLowerCase());return{n,x:XL.sheet(pg,st,n)}});
  const files=[["[Content_Types].xml",`<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>${sheets.map((s,i)=>`<Override PartName="/xl/worksheets/sheet${i+1}.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>`).join("")}</Types>`],
   ["_rels/.rels",`<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>`],
   ["xl/workbook.xml",`<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>${sheets.map((s,i)=>`<sheet name="${XL.esc(s.n)}" sheetId="${i+1}" r:id="rId${i+1}"/>`).join("")}</sheets></workbook>`],
   ["xl/_rels/workbook.xml.rels",`<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">${sheets.map((s,i)=>`<Relationship Id="rId${i+1}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet${i+1}.xml"/>`).join("")}<Relationship Id="rId${sheets.length+1}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>`],
   ["xl/styles.xml",st.xml()]].concat(sheets.map((s,i)=>["xl/worksheets/sheet"+(i+1)+".xml",s.x]));return XL.zip(files)};
XL.save=(blob,name)=>{const a=document.createElement("a");a.href=URL.createObjectURL(blob);a.download=name;document.body.appendChild(a);a.click();setTimeout(()=>{URL.revokeObjectURL(a.href);a.remove()},500)};
$("#rcsv").onclick=()=>{const pgs=[...$("#rout").querySelectorAll(".pg")];if(!pgs.length)return alert("Nothing to download for this period.");
  const names=pgs.map(p=>{const g=p.querySelector(".gtt span");return p.classList.contains("pgwl")?(g?g.textContent:"Worklog"):p.classList.contains("pgsm")?"Report":"Invoice"});
  try{XL.save(XL.book(pgs,names),(rCur().name||"report").replace(/[^\w-]+/g,"_")+"_"+$("#rf").value+"_to_"+$("#rt").value+".xlsx")}catch(e){alert("Could not build the Excel file: "+e.message)}};
// profile editor

// ---- Create invoice (draft) -> Invoice list: check, edit, amend, approve ----
let rEdit=null;
function rEditBar(){const b=$("#rapp"),n=$("#redit");if(rEdit){b.textContent="💾 Save changes";if(n){n.hidden=false;n.innerHTML="Editing draft <b>"+esc(rEdit.no)+"</b>"+(rEdit.note?" · <span style='color:#c5221f'>Amendment asked: "+esc(rEdit.note)+"</span>":"")+' <button id="rednx" type="button">Stop editing</button>'}}else{b.textContent="➕ Create invoice";if(n)n.hidden=true}}
document.addEventListener("click",e=>{if(e.target.id==="rednx"){rEdit=null;rEditBar();drawReport()}});
$("#rdlg").addEventListener("close",()=>{rEdit=null;rEditBar()});
$("#rapp").onclick=async()=>{if(!rTot||!rTot.html)return alert("Nothing to create for this period.");const p=rEff(rCur()),no=rInvNo(p);
  if(!rTot.amount&&!confirm("The invoice total is 0 (rates may be missing). Create anyway?"))return;
  const el=document.createElement("div");el.innerHTML=rTot.html;
  try{await api("inv_create",{id:rEdit?rEdit.id:0,slot:p.slot||0,no,seq:rEdit?rEdit.seq:String(p.seq),from:$("#rf").value,to:$("#rt").value,client:p.client_name||"",currency:p.currency,amount:rTot.amount,hours:rTot.hours,html:el.innerHTML});
    const was=!!rEdit;rEdit=null;rEditBar();alert((was?"Saved changes to ":"Created draft ")+no+". Check and approve it in Invoice tracking.");$("#rdlg").close();$("#ivl").showModal();ivLoad()}catch(e){alert(e.message)}};
let ivRows=[];
const fmtAmt=(c,n)=>(c?c+" ":"")+(Math.round((+n||0)*100)/100).toLocaleString("en",{minimumFractionDigits:2,maximumFractionDigits:2});
const IVST={draft:["📝 Draft","#5f6368"],amend:["✏ Needs amendment","#c5221f"],pending:["⏳ Approved · unpaid","#b06000"],paid:["✔ Paid","#188038"]};
async function ivLoad(){try{ivRows=(await api("inv_list",{})).invoices}catch(e){return alert(e.message)}ivDraw()}
function ivDraw(){const f=$("#ivlf").value,L=ivRows.filter(x=>!f||x.status===f),tot={},n=s=>ivRows.filter(x=>x.status===s).length;ivRows.filter(x=>x.status==="pending").forEach(x=>tot[x.currency]=(tot[x.currency]||0)+ +x.amount);
  $("#ivls").innerHTML="To check: <b>"+(n("draft")+n("amend"))+"</b> · Awaiting payment: "+(Object.keys(tot).length?Object.entries(tot).map(([c,v])=>"<b>"+esc(fmtAmt(c,v))+"</b>").join(" · "):"<b>nothing</b>")+" ("+n("pending")+" invoice(s))";
  $("#ivlt").innerHTML='<tr><th>Invoice no.</th><th>Company</th><th>Period</th><th>Amount</th><th>Status</th><th></th></tr>'+(L.map(x=>{const s=IVST[x.status]||[x.status,"#000"],b=(k,t)=>`<button data-ivk="${k}" data-id="${x.id}">${t}</button> `;
    const act=x.status==="draft"?b("edit","✎ Edit")+b("approve","✔ Approve")+b("amend","↩ Amendment")+b("open","🖨 Open")+b("del","🗑")
      :x.status==="amend"?b("edit","✎ Edit")+b("approve","✔ Approve")+b("open","🖨 Open")+b("del","🗑")
      :b("pay",x.status==="paid"?"Mark unpaid":"Mark paid")+b("open","🖨 Open")+b("del","🗑");
    return `<tr><td><b>${esc(x.inv_no)}</b><br><small style="color:var(--mut)">${esc(x.inv_date)}</small></td><td>${esc(x.company)}<br><small style="color:var(--mut)">${esc(x.client)}</small></td><td>${esc(x.from_date)} → ${esc(x.to_date)}</td><td style="text-align:right">${esc(fmtAmt(x.currency,x.amount))}<br><small style="color:var(--mut)">${+x.hours} h</small></td>
    <td><span style="color:${s[1]};font-weight:600">${s[0]}</span>${x.status==="paid"?`<br><small>${esc(x.paid_date||"")}</small>`:""}${x.status==="amend"&&x.amend_note?`<br><small style="color:#c5221f">${esc(x.amend_note)}</small>`:""}</td><td style="white-space:nowrap">${act}</td></tr>`}).join("")||'<tr><td colspan="6" style="text-align:center;color:var(--mut);padding:16px">Nothing here yet. Use ➕ Create invoice in Worklog &amp; invoice.</td></tr>')}
$("#ivm").onclick=()=>{$("#ivl").showModal();ivLoad()};$("#ivlc").onclick=()=>$("#ivl").close();$("#ivlf").onchange=ivDraw;
$("#ivlt").addEventListener("click",async e=>{const b=e.target.closest("button[data-ivk]");if(!b)return;const k=b.dataset.ivk,id=+b.dataset.id,x=ivRows.find(r=>+r.id===id);
  try{if(k==="pay"){await api("inv_pay",{id,status:x.status==="paid"?"pending":"paid"});await ivLoad()}
  else if(k==="open"){const j=await api("inv_get",{id});$("#pbox").innerHTML=j.inv.html;window.print()}
  else if(k==="approve"){if(confirm("Approve invoice "+x.inv_no+"?\n\nIt becomes final (awaiting payment) and the next invoice number moves on by one."))await api("inv_step",{id,op:"approve"}).then(ivLoad)}
  else if(k==="amend"){const n=await ask("What needs to be amended?");if(n&&n.trim())await api("inv_step",{id,op:"amend",note:n}).then(ivLoad)}
  else if(k==="del"){if(confirm("Delete "+x.inv_no+"?"+(x.status==="draft"||x.status==="amend"?"":" The invoice number is not given back.")))await api("inv_del",{id}).then(ivLoad)}
  else if(k==="edit"){if(String(x.ws_id)!==String(W))return alert("This invoice belongs to "+x.company+". Switch to that company first, then press Edit.");
    const j=await api("inv_get",{id});rEdit={id,no:j.inv.inv_no,seq:j.inv.seq,note:x.amend_note||""};rPend=j.inv.slot;$("#ivl").close();$("#rf").value=j.inv.from_date;$("#rt").value=j.inv.to_date;$("#rdlg").showModal();rEditBar();await runReport()}}catch(er){alert(er.message)}});

let profEdit=null;
function openProf(p){profEdit=p?p.slot:0;const d=p||Object.assign(rDef(),{name:"New profile"});
  const set=(i,v)=>$(i).value=v==null?"":v;set("#pn",d.name);set("#pl",d.layout);set("#pmc",d.match_cust);set("#pmo",d.match_order);set("#pcn",d.client_name);set("#pwt",d.wl_title);set("#ppo",d.po);set("#psl",d.sec_labels);set("#pca",d.client_addr);set("#pcc",d.currency);set("#pcs",d.symbol);set("#prt",d.rate||"");set("#pot",d.ot);set("#ppx",d.prefix);set("#psf",d.suffix);set("#psq",d.seq);set("#pbk",d.bank);
  const ppl=new Map();allUsers.forEach(u=>ppl.set(String(u.id),u.name));rrows.forEach(x=>ppl.set(String(x.user_id),x.person));
  $("#prl").innerHTML=[...ppl.entries()].map(([id,n])=>`<label>${esc(n)}<input type="number" min="0" step="0.01" data-pr="${id}" value="${d.rates&&d.rates[id]!==undefined?d.rates[id]:""}"></label>`).join("")||'<span style="color:var(--mut)">No people yet</span>';
  $("#pdel").style.visibility=p?"visible":"hidden";$("#ivdlg").showModal()}
$("#rpedit").onclick=()=>{const p=rCur();openProf(p.slot?p:Object.assign(p,{}))};
$("#pcc2").onclick=()=>{$("#ivdlg").close();$("#rpf").value=String(rCur().slot)};
$("#ivsv").onclick=async()=>{const rates={};document.querySelectorAll("#prl [data-pr]").forEach(i=>{if(i.value!=="")rates[i.dataset.pr]=+i.value});
  let slot=profEdit;if(!slot){const used=rprofs.map(p=>p.slot);for(let s=1;s<=9;s++)if(!used.includes(s)){slot=s;break}}
  if(!slot){alert("Maximum of 9 profiles");return}
  const profile={name:$("#pn").value||"Invoice",layout:$("#pl").value,match_cust:$("#pmc").value,match_order:$("#pmo").value,client_name:$("#pcn").value,wl_title:$("#pwt").value,po:$("#ppo").value,sec_labels:$("#psl").value,client_addr:$("#pca").value,currency:$("#pcc").value||"",symbol:$("#pcs").value,rate:+$("#prt").value||0,ot:$("#pot").value===""?1:+$("#pot").value,prefix:$("#ppx").value,suffix:$("#psf").value,seq:$("#psq").value.replace(/\D/g,"")||"0",bank:$("#pbk").value,rates};
  try{await api("inv_set",{slot,profile});$("#ivdlg").close();await runReport();$("#rpf").value=String(slot);drawReport()}catch(e){alert(e.message)}};
$("#pdel").onclick=async()=>{if(!profEdit||!confirm("Delete this profile?"))return;try{await api("inv_set",{slot:profEdit,remove:1});$("#ivdlg").close();await runReport()}catch(e){alert(e.message)}};
// ---- Personal hours check ----
const ltoday=()=>new Date().toLocaleDateString("en-CA");
let mhPlan=[],mhRows=[],mhUser=0,mhAddDate="",mhSel=new Set(),mhWk="";
const isoAdd=(s,n)=>{const d=new Date(s+"T00:00:00Z");d.setUTCDate(d.getUTCDate()+n);return d.toISOString().slice(0,10)};
const wkStart=s=>{const d=new Date(s+"T00:00:00Z");return isoAdd(s,-((d.getUTCDay()+6)%7))};
const WDN=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
const mhSorted=()=>[...mhSel].sort();
function mhRange(a,b){mhSel=new Set();for(let d=a,n=0;d<=b&&n<400;d=isoAdd(d,1),n++)mhSel.add(d);mhWk=wkStart(a);mhSync();mhLoad()}
function mhSync(){const s=mhSorted();if(s.length){$("#mhf").value=s[0];$("#mht").value=s[s.length-1]}mhChips()}
function mhChips(){$("#mhdays").innerHTML=`<button data-mhwk="-7" title="Previous week" style="padding:2px 8px">‹</button>`+[0,1,2,3,4,5,6].map(i=>{const d=isoAdd(mhWk,i),dow=(i+1)%7;return `<label style="margin:0 2px;display:inline-flex;gap:2px;align-items:center;white-space:nowrap"><input type="checkbox" data-mhd="${d}" ${mhSel.has(d)?"checked":""}>${WDN[dow]} ${+d.slice(8)}</label>`}).join("")+`<button data-mhwk="7" title="Next week" style="padding:2px 8px">›</button>`}
async function mhLoad(){const s=mhSorted();if(!s.length){mhRows=[];mhDraw();return}
  try{const r=await api("my_hours",{from:s[0],to:s[s.length-1],user:+$("#mhu").value||0});mhRows=r.rows;mhPlan=r.plan||[];mhUser=r.user;mhDraw()}catch(e){alert(e.message)}}
function mhDraw(){const tg=+$("#mhg").value||0,td=ltoday(),by={},only=$("#mhmo")&&$("#mhmo").checked;
  mhRows.forEach(x=>{if(mhSel.has(x.work_date))(by[x.work_date]=by[x.work_date]||[]).push(x)});
  let exp=0,miss=0,rows="",R=0,O=0,short=0;
  for(const d of mhSorted()){const dow=new Date(d+"T00:00:00Z").getUTCDay(),wk=dow===0||dow===6,t0=wk?0:tg,l=by[d]||[],h=l.reduce((s,x)=>s+ +x.hours,0),ot=l.filter(x=>x.kind==="OT").reduce((s,x)=>s+ +x.hours,0),rg=h-ot;R+=rg;O+=ot;
    const past=d<=td;if(past){exp+=t0;if(rg<t0){miss++;short+=t0-rg}}
    let st="",cl="";if(!past){st="Upcoming";cl="color:var(--mut)"}else if(wk){st=h?"Weekend · all overtime":"Weekend";cl="color:var(--mut)"}else if(rg>=t0){st="✓ Complete"+(ot?" · "+fmtH(ot)+" h OT":"");cl="color:#188038"}else if(rg>0){st="⚠ Short by "+fmtH(t0-rg)+" h";cl="color:#b45f06;font-weight:600"}else{st="✗ Nothing logged";cl="color:#c5221f;font-weight:600"}
    if(only&&!(past&&!wk&&rg<t0))continue;
    const lg={};l.forEach(x=>{lg[x.task_id]=(lg[x.task_id]||0)+ +x.hours});
    const pl=mhPlan.filter(t=>{const a=t.start_date||t.end_date,e=t.end_date||t.start_date;return a<=d&&d<=e});
    const plh=pl.length?`<div style="margin-top:6px;font-size:12px"><span style="color:var(--mut)">Assigned &amp; ongoing:</span> ${pl.map(t=>`<span style="display:inline-block;margin:2px 4px 2px 0;padding:2px 8px;border-radius:9px;border:1px solid var(--line);background:${lg[t.id]?"#e6f4ea":past&&!wk?"#fff4d6":"var(--col)"}" title="${ea(t.title)}">${esc(t.order_no||t.title)}${t.ws?" · "+esc(t.ws):""} · ${lg[t.id]?"✓ "+fmtH(lg[t.id])+" h":past&&!wk?"no hours":"planned"}</span>`).join("")}</div>`:(wk?"":`<div style="margin-top:6px;font-size:12px;color:var(--mut)">No task assigned with dates covering this day</div>`);
    const det=l.map(x=>`<div style="font-size:12px;color:var(--mut)">${esc(x.order_no||"")} ${esc(x.title)} · ${fmtH(x.hours)} h${x.kind==="OT"?" OT":""}${x.ws?" · "+esc(x.ws):""}</div>`).join("");
    rows+=`<tr><td>${esc(d)}</td><td>${WDN[dow]}</td><td style="text-align:left"><b>${fmtH(rg)} h regular${ot?" + "+fmtH(ot)+" h OT":""}</b>${det}${plh}</td><td style="${cl}">${st}</td><td>${LV==="view"?"":`<button data-mha="${d}">+ Add hours</button>`}</td></tr>`}
  $("#mhs").innerHTML=`<div style="margin:6px 0;font-size:15px">Logged <b>${fmtH(R)} h</b> + <b>${fmtH(O)} h OT</b> · expected so far <b>${fmtH(exp)} h</b> · days short: <b style="color:${miss?"#c5221f":"#188038"}">${miss}</b>${short?` · missing <b style="color:#c5221f">${fmtH(short)} h</b>`:""}</div>`;
  $("#mhout").innerHTML=`<table class="rt mhcol"><tr><th>Date</th><th>Day</th><th style="text-align:left">Hours logged and tasks</th><th>Status</th><th></th></tr>${rows||`<tr><td colspan="5" class="empty">${only?"No missed days in the selected dates \u{1F389}":"Tick one or more days above"}</td></tr>`}</table>`;mhCols()}
function mhCols(){const t=document.querySelector("#mhout table.mhcol");if(!t)return;const ths=[...t.rows[0].cells],DEF=[100,70,0,170,110];let W=[];try{W=JSON.parse(localStorage.getItem("tf_mhcols")||"[]")}catch(e){}
  const cg=document.createElement("colgroup");ths.forEach((_,i)=>{const c=document.createElement("col");const w=W[i]||DEF[i];if(w)c.style.width=w+"px";cg.appendChild(c)});t.prepend(cg);t.style.tableLayout="fixed";t.style.width="100%";t.style.minWidth=DEF.reduce((a,b)=>a+b,0)+260+"px";
  const wrap=$("#mhout");wrap.style.overflowX="auto";
  ths.forEach((th,i)=>{if(i===ths.length-1)return;th.style.position="relative";const g=document.createElement("span");g.className="colgrip";g.title="Drag to resize (double-click to reset)";th.appendChild(g);
    g.addEventListener("pointerdown",e=>{e.preventDefault();e.stopPropagation();const col=cg.children[i],x0=e.clientX,w0=th.getBoundingClientRect().width;try{g.setPointerCapture(e.pointerId)}catch(x){}
      const mv=ev=>{col.style.width=Math.max(50,Math.round(w0+ev.clientX-x0))+"px"},up=()=>{g.removeEventListener("pointermove",mv);g.removeEventListener("pointerup",up);W=[...cg.children].map(c=>parseInt(c.style.width)||0);try{localStorage.setItem("tf_mhcols",JSON.stringify(W))}catch(x){}};
      g.addEventListener("pointermove",mv);g.addEventListener("pointerup",up)});
    g.addEventListener("dblclick",()=>{cg.children[i].style.width=DEF[i]?DEF[i]+"px":"";W=[...cg.children].map(c=>parseInt(c.style.width)||0);try{localStorage.setItem("tf_mhcols",JSON.stringify(W))}catch(x){}})})}
function mhOpen(){const sel=$("#mhu");if(me.role==="admin"&&allUsers.length){sel.hidden=false;sel.innerHTML=allUsers.filter(x=>x.role!=="guest").map(x=>`<option value="${x.id}">${esc(x.name)}</option>`).join("");sel.value=String(me.id)}else sel.hidden=true;
  $("#mhdg").showModal();mhRange(ltoday(),ltoday())}
const rpPos=()=>{const r=$("#rpw").getBoundingClientRect(),m=$("#umenu").getBoundingClientRect(),l=$("#rpl");let x=m.right-4;if(x+190>innerWidth)x=Math.max(4,m.left-176);l.style.left=x+"px";l.style.top=Math.max(8,Math.min(r.top-6,innerHeight-150))+"px"};
$("#rpw").addEventListener("mouseenter",rpPos);
$("#rpt").addEventListener("click",e=>{e.preventDefault();e.stopPropagation();$("#rpw").classList.toggle("open");rpPos()});
$("#mhm").addEventListener("click",e=>{e.preventDefault();$("#umenu").hidden=true;mhOpen()});
$("#mhcl").onclick=()=>$("#mhdg").close();
$("#mhtd").onclick=()=>mhRange(ltoday(),ltoday());$("#mh30").onclick=()=>mhRange(isoAdd(ltoday(),-29),ltoday());$("#mhmo").addEventListener("change",mhDraw);
$("#mhw").onclick=()=>{const s=wkStart(ltoday());mhRange(s,isoAdd(s,6))};
$("#mhlw").onclick=()=>{const s=isoAdd(wkStart(ltoday()),-7);mhRange(s,isoAdd(s,6))};
$("#mh14").onclick=()=>mhRange(isoAdd(ltoday(),-13),ltoday());
["mhf","mht"].forEach(i=>$("#"+i).addEventListener("change",()=>{const a=$("#mhf").value,b=$("#mht").value;if(a&&b&&a<=b)mhRange(a,b)}));
// ---- Hours download (daily / weekly / monthly backup), laid out like the worklog ----
$("#mhdb").onclick=async()=>{const t=ltoday(),k=$("#mhdl").value,mo=(y,m)=>[new Date(Date.UTC(y,m,1)).toISOString().slice(0,10),new Date(Date.UTC(y,m+1,0)).toISOString().slice(0,10)];let a,b;const y=+t.slice(0,4),m=+t.slice(5,7)-1;
  if(k==="day")a=b=t;else if(k==="week"){a=wkStart(t);b=isoAdd(a,6)}else if(k==="lweek"){a=isoAdd(wkStart(t),-7);b=isoAdd(a,6)}else if(k==="month")[a,b]=mo(y,m);else if(k==="lmonth")[a,b]=mo(y,m-1);else{const s=mhSorted();if(!s.length)return alert("Pick at least one date first");a=s[0];b=s[s.length-1]}
  try{const r=await api("my_hours",{from:a,to:b,user:+$("#mhu").value||0}),rows=r.rows,who=($("#mhu").hidden?(me.name||"me"):($("#mhu").selectedOptions[0]||{}).textContent)||"me",tg=+$("#mhg").value||0;
    const wkn=d=>isoWeek(d),L=new Map();let R=0,O=0;
    for(const x of rows){const key=x.work_date+"|"+x.ws+"|"+x.order_no+"|"+x.title+"|"+(x.kind==="OT"?"OT":"R");let l=L.get(key);if(!l){l={x,date:x.work_date,ot:x.kind==="OT",h:0,notes:[]};L.set(key,l)}l.h+=+x.hours;if(x.note&&!l.notes.includes(x.note))l.notes.push(x.note);x.kind==="OT"?O+=+x.hours:R+=+x.hours}
    const lines=[...L.values()].sort((p,q)=>p.date<q.date?-1:p.date>q.date?1:0),day=new Map(),wk=new Map(),co=[...new Set(rows.map(x=>x.ws).filter(Boolean))];
    for(const l of lines){const d=day.get(l.date)||{r:0,o:0};l.ot?d.o+=l.h:d.r+=l.h;day.set(l.date,d);const w=wkStart(l.date),e=wk.get(w)||{r:0,o:0};l.ot?e.o+=l.h:e.r+=l.h;wk.set(w,e)}
    const bd="border:1px solid #777;",tdc=(v,st="")=>`<td style="${bd}${st}">${esc(v)}</td>`,F=v=>fmtH(v);
    let H=`<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"></head><body><table style="border-collapse:collapse;font-family:Calibri,Arial;font-size:11pt"><colgroup><col width="85"><col width="50"><col width="125"><col width="95"><col width="170"><col width="85"><col width="105"><col width="75"><col width="95"><col width="170"></colgroup>
<tr style="height:44px"><td colspan="3" style="background:#38761d;color:#fff;font-weight:bold;font-size:13pt">DailyWorkLog: ${esc(co.join(" / ")||"All companies")}<br>Wk: ${wkn(a)}${wkn(b)!==wkn(a)?"-"+wkn(b):""}</td><td colspan="2" style="background:#0b5394;color:#fff;font-weight:bold;font-size:14pt;text-align:center">${esc(who)}</td><td colspan="5" style="background:#38761d;color:#fff">${esc(a)} to ${esc(b)}</td></tr>
<tr style="font-weight:bold"><td style="${bd}background:#a9d08e">Date</td><td style="${bd}background:#a9d08e;text-align:center">Week</td><td style="${bd}background:#a9d08e">Project Group</td><td style="${bd}background:#a9d08e">Project No</td><td style="${bd}background:#a9d08e">Project Name</td><td style="${bd}background:#fce5cd;text-align:center">${esc(who)}<br>Hrs</td><td style="${bd}background:#f4cccc">Regular/ OT</td><td style="${bd}background:#fce5cd;text-align:center">Total Hrs</td><td style="${bd}background:#fce5cd;text-align:center">Company</td><td style="${bd}background:#fce5cd">Remarks</td></tr>`;
    for(const l of lines)H+=`<tr>${tdc(l.date)}${tdc(wkn(l.date),"text-align:center")}${tdc(l.x.top_name||"")}${tdc(l.x.order_no||"")}${tdc(l.x.title)}<td style="${bd}background:#fff2cc;text-align:center">${F(l.h)}</td>${tdc(l.ot?"OT":"Regular","background:#f4cccc")}<td style="${bd}text-align:center">${F(l.h)}</td>${tdc(l.x.ws||"","text-align:center")}${tdc(l.notes.join("; "))}</tr>`;
    H+=`<tr style="font-weight:bold"><td colspan="5" style="${bd}text-align:right">Total hours</td><td style="${bd}background:#fff2cc;text-align:center">${F(R+O)}</td><td style="${bd}background:#f4cccc">${F(R)} regular + ${F(O)} OT</td><td style="${bd}text-align:center">${F(R+O)}</td><td style="${bd}"></td><td style="${bd}"></td></tr></table><br>
<table style="border-collapse:collapse;font-family:Calibri,Arial;font-size:11pt"><tr><td colspan="7" style="background:#38761d;color:#fff;font-weight:bold">Totals by day</td></tr><tr style="font-weight:bold;background:#a9d08e"><td style="${bd}">Date</td><td style="${bd}">Day</td><td style="${bd}text-align:center">Regular</td><td style="${bd}text-align:center">OT</td><td style="${bd}text-align:center">Total</td><td style="${bd}text-align:center">Target</td><td style="${bd}text-align:center">Difference</td></tr>`;
    for(let d=a,n=0;d<=b&&n<400;d=isoAdd(d,1),n++){const e=day.get(d)||{r:0,o:0},dw=new Date(d+"T00:00:00Z").getUTCDay(),we=dw===0||dw===6;if(we&&!day.has(d))continue;const df=we?"":e.r-tg;H+=`<tr>${tdc(d)}${tdc(WDN[dw])}${tdc(F(e.r),"text-align:center")}${tdc(F(e.o),"text-align:center")}${tdc(F(e.r+e.o),"text-align:center;font-weight:bold")}${tdc(we?0:tg,"text-align:center")}<td style="${bd}text-align:center;color:${df<0?"#c5221f":"#188038"}">${df===""?"":F(df)}</td></tr>`}
    if(wk.size>1){H+=`<tr><td colspan="7" style="background:#38761d;color:#fff;font-weight:bold">Totals by week (Mon–Sun)</td></tr><tr style="font-weight:bold;background:#a9d08e"><td style="${bd}" colspan="2">Week starting</td><td style="${bd}text-align:center">Regular</td><td style="${bd}text-align:center">OT</td><td style="${bd}text-align:center">Total</td><td style="${bd}"></td><td style="${bd}"></td></tr>`;for(const [w,e] of [...wk.entries()].sort())H+=`<tr>${tdc(w+" (Wk "+wkn(w)+")").replace("<td","<td colspan=\"2\"")}${tdc(F(e.r),"text-align:center")}${tdc(F(e.o),"text-align:center")}${tdc(F(e.r+e.o),"text-align:center;font-weight:bold")}<td style="${bd}"></td><td style="${bd}"></td></tr>`}
    H+=`</table></body></html>`;
    const box=document.createElement("div");box.className="pg pgwl";box.style.cssText="position:absolute;left:-12000px;top:0;width:1250px;background:#fff";box.innerHTML=H.replace(/^[\s\S]*<body>/,"").replace(/<\/body>[\s\S]*$/,"");document.body.appendChild(box);
    try{XL.save(XL.book([box],["Hours"]),"hours_"+String(who).replace(/[^\w-]+/g,"_")+"_"+a+"_to_"+b+".xlsx")}finally{box.remove()}}catch(e){alert(e.message)}};
$("#mhu").addEventListener("change",mhLoad);$("#mhg").addEventListener("input",mhDraw);
$("#mhdays").addEventListener("click",e=>{const w=e.target.closest("[data-mhwk]");if(w){mhWk=isoAdd(mhWk,+w.dataset.mhwk);mhChips()}});
$("#mhdays").addEventListener("change",e=>{const c=e.target.closest("[data-mhd]");if(!c)return;if(c.checked)mhSel.add(c.dataset.mhd);else mhSel.delete(c.dataset.mhd);mhSync();mhLoad()});
$("#mhout").addEventListener("click",e=>{const b=e.target.closest("[data-mha]");if(!b)return;mhAddDate=b.dataset.mha;const uid=mhUser||me.id;
  const mine=tasks.filter(t=>(String(t.assigned_to)===String(uid)||(t.co||[]).some(x=>String(x)===String(uid)))&&t.status!=="done");
  if(!mine.length){alert("No open tasks assigned to this person in the current company. Switch company or assign a task first.");return}
  $("#mhat").textContent="Add hours · "+mhAddDate;$("#mhtk").innerHTML=mine.map(t=>`<option value="${t.id}">${esc((t.order_no?t.order_no+" · ":"")+t.title)}</option>`).join("");
  const have=mhRows.filter(x=>x.work_date===mhAddDate&&x.kind!=="OT").reduce((s,x)=>s+ +x.hours,0);$("#mhah").value=Math.max(0,(+$("#mhg").value||8)-have)||"";$("#mhan").value="";$("#mhad").showModal()});
$("#mhax").onclick=()=>$("#mhad").close();
$("#mhaok").onclick=async()=>{const h=parseFloat($("#mhah").value);if(!(h>0&&h<=24)){alert("Enter hours between 0.25 and 24");return}
  try{await api("tl_add",{task_id:+$("#mhtk").value,date:mhAddDate,hours:h,kind:$("#mhak").value,note:$("#mhan").value,for_user:(me.role==="admin"&&mhUser&&mhUser!=me.id)?mhUser:0});$("#mhad").close();await mhLoad();await load(true)}catch(e){alert(e.message)}};
// ---- Movable / resizable dialogs ----
(()=>{let dr=null;
 document.addEventListener("pointerdown",e=>{const d=e.target.closest&&e.target.closest("dialog");if(!d||e.button>0)return;
  if(e.target.closest("input,select,textarea,button,a,label,table,.acts button"))return;
  const r=d.getBoundingClientRect();if(e.clientY-r.top>56||e.clientX>r.right-18)return;
  if(!d.classList.contains("dmv")){d.style.left=r.left+"px";d.style.top=r.top+"px";d.style.width=r.width+"px";d.classList.add("dmv")}
  dr={d,x:e.clientX-d.offsetLeft,y:e.clientY-d.offsetTop};try{d.setPointerCapture(e.pointerId)}catch(x){}e.preventDefault()});
 document.addEventListener("pointermove",e=>{if(!dr)return;const d=dr.d;d.style.left=Math.max(0,Math.min(innerWidth-60,e.clientX-dr.x))+"px";d.style.top=Math.max(0,Math.min(innerHeight-40,e.clientY-dr.y))+"px"});
 const end=()=>{dr=null};document.addEventListener("pointerup",end);document.addEventListener("pointercancel",end);
 document.addEventListener("close",e=>{const d=e.target;if(d&&d.tagName==="DIALOG"){d.classList.remove("dmv");d.style.left=d.style.top=d.style.width=d.style.height=""}},true)})();
// ---- Right-hand tools panel ----
const toolsOpen=on=>{const s=$("#rside");s.hidden=!on;try{localStorage.setItem("tf_rp",on?"1":"")}catch(e){}if(on){let g=null;try{g=JSON.parse(localStorage.getItem("tf_rpg")||"null")}catch(e){}
  if(g&&g.w>=300){s.style.left=Math.min(Math.max(0,g.l),innerWidth-120)+"px";s.style.top=Math.min(Math.max(0,g.t),innerHeight-60)+"px";s.style.width=g.w+"px";s.style.height=g.h+"px"}ctCalc();cvCalc()}};
(()=>{const s=$("#rside");let d=null;const save=()=>{if(s.hidden)return;const r=s.getBoundingClientRect();try{localStorage.setItem("tf_rpg",JSON.stringify({l:Math.round(r.left),t:Math.round(r.top),w:Math.round(r.width),h:Math.round(r.height)}))}catch(e){}};
  s.querySelector(".rh").addEventListener("pointerdown",e=>{if(e.target.closest("button"))return;const r=s.getBoundingClientRect();d={x:e.clientX-r.left,y:e.clientY-r.top};e.target.setPointerCapture&&e.target.setPointerCapture(e.pointerId)});
  document.addEventListener("pointermove",e=>{if(!d)return;s.style.left=Math.min(Math.max(-s.offsetWidth+120,e.clientX-d.x),innerWidth-120)+"px";s.style.top=Math.min(Math.max(0,e.clientY-d.y),innerHeight-50)+"px"});
  document.addEventListener("pointerup",()=>{if(d){d=null;save()}});new ResizeObserver(()=>{if(!s.hidden)save()}).observe(s)})();
$("#tbtn").onclick=()=>toolsOpen($("#rside").hidden);$("#rsx").onclick=()=>toolsOpen(false);
document.querySelectorAll("#rside .rtabs button").forEach(b=>b.onclick=()=>{document.querySelectorAll("#rside .rtabs button").forEach(x=>x.classList.toggle("on",x===b));["ct","cv","wk"].forEach(k=>$("#rt_"+k).hidden=k!==b.dataset.rt);if(b.dataset.rt==="wk")wkLoad()});
const f3=(n,d=3)=>(+n).toLocaleString("en",{minimumFractionDigits:0,maximumFractionDigits:d});
const ftIn=m=>{const t=m/0.0254,ft=Math.floor(t/12+1e-9),i=t-ft*12;return ft+"′ "+f3(i,2)+"″"};
function ctCalc(){const sel=$("#ct_t").value;$("#ct_cw").hidden=sel!=="c";const L=sel==="c"?(+$("#ct_c").value||0)/1000:+sel,n=Math.max(1,Math.floor(+$("#ct_n").value)||1),g=(+$("#ct_g").value||0)/1000,x=(+$("#ct_x").value||0)/1000;
  const tot=n*L+(n-1)*g+x;$("#ct_o").innerHTML=`<div>${n} × ${f3(L,3)} m${n>1?` + ${n-1} gap${n>2?"s":""} × ${f3(g*1000,1)} mm`:""}${x?` + ${f3(x*1000,1)} mm extra`:""}</div><b>${f3(tot,3)} m</b> &nbsp;(${f3(tot*1000,1)} mm)<div>${ftIn(tot)} · ${f3(tot/0.0254,2)} in</div>`}
["ct_t","ct_c","ct_n","ct_g","ct_x"].forEach(i=>$("#"+i).addEventListener("input",ctCalc));
const CVU=[["mm",0.001,"L"],["cm",0.01,"L"],["m",1,"L"],["inch",0.0254,"L"],["feet",0.3048,"L"],["yard",0.9144,"L"],["kg",1,"M"],["g",0.001,"M"],["lb",0.45359237,"M"],["tonne",1000,"M"]];
$("#cv_u").innerHTML=CVU.map((u,i)=>`<option value="${i}">${u[0]}</option>`).join("");$("#cv_u").value="0";
function fracIn(t,den){let n=Math.round(t*den),neg=n<0;n=Math.abs(n);const w=Math.floor(n/den);let r=n%den,d=den;while(r&&r%2===0&&d%2===0){r/=2;d/=2}const ft=Math.floor(w/12),wi=w%12,fr=r?`<sup>${r}</sup>⁄<sub>${d}</sub>`:"",err=(n/den-Math.abs(t))*25.4;
  const inch=(wi?wi+(r?" ":""):"")+(r?fr:(wi?"":"0"));
  return `<b>${neg?"−":""}${ft?ft+"′ ":""}${inch}″</b> <span style="color:var(--mut);font-size:12px">${err>=0?"+":"−"}${f3(Math.abs(err),2)} mm</span>`}
const fracRows=m=>{const t=m/0.0254;return `<div style="margin-top:6px;border-top:1px solid var(--line);padding-top:6px"><b style="font-size:13px">Nearest inch fraction</b>`+[8,16,32].map(d=>`<div>1/${d}: ${fracIn(t,d)}</div>`).join("")+`</div>`};
function cvCalc(){const v=+$("#cv_v").value||0,u=CVU[+$("#cv_u").value],base=v*u[1];$("#cv_o").innerHTML=CVU.filter(x=>x[2]===u[2]&&x!==u).map(x=>`<div>${f3(base/x[1],4)} <b style="font-size:14px">${x[0]}</b></div>`).join("")+(u[2]==="L"?`<div style="color:var(--mut)">${ftIn(base)}</div>`+fracRows(base):"")}
$("#cv_v").addEventListener("input",cvCalc);$("#cv_u").addEventListener("change",cvCalc);
let wkPages=[],wkCur=0,wkEd=null,wkTag="",wkLoaded=false;
const wkTagsOf=p=>String(p.tags||"").split(",").map(x=>x.trim()).filter(Boolean);
const wkCan=()=>me.role!=="guest";
function mdI(s){s=esc(s);
  s=s.replace(/!\[([^\]]*)\]\((wimg:\d+|https?:[^)\s]+)\)/g,(m,a,u)=>`<figure><img src="${u.startsWith("wimg:")?"api.php?a=wiki_img&id="+u.slice(5):u}" alt="${a}" loading="lazy" data-wz><figcaption>${a}</figcaption></figure>`);
  s=s.replace(/\[([^\]]+)\]\(([^)]+)\)/g,(m,t,u)=>/^https?:/.test(u)?`<a href="${u}" target="_blank" rel="noopener">${t}</a>`:/^wiki:/.test(u)?`<a data-wkp="${ea(u.slice(5))}" style="color:var(--pri,#2563eb);cursor:pointer">${t}</a>`:m);
  return s.replace(/\*\*([^*]+)\*\*/g,"<b>$1</b>").replace(/(^|[^*\w])\*([^*]+)\*/g,"$1<i>$2</i>").replace(/`([^`]+)`/g,"<code>$1</code>")}
function mdR(src){const L=String(src||"").replace(/\r/g,"").split("\n"),o=[],toc=[];let i=0,n=0;
  const cell=r=>r.trim().replace(/^\||\|$/g,"").split("|").map(c=>c.trim());
  while(i<L.length){const l=L[i];
    if(!l.trim()){i++;continue}
    let m;
    if(m=l.match(/^(#{1,3})\s+(.*)$/)){const lv=m[1].length+0;if(lv===2||lv===1){const id="wkh"+(n++);toc.push([id,m[2]]);o.push(`<h2 id="${id}">${mdI(m[2])}</h2>`)}else o.push(`<h3>${mdI(m[2])}</h3>`);i++;continue}
    if(/^---+\s*$/.test(l)){o.push("<hr>");i++;continue}
    if(l.startsWith("|")&&i+1<L.length&&/^\|?\s*:?-{2,}/.test(L[i+1])){const h=cell(l);i+=2;const rows=[];while(i<L.length&&L[i].startsWith("|")){rows.push(cell(L[i]));i++}
      o.push(`<table><tr>${h.map(c=>`<th>${mdI(c)}</th>`).join("")}</tr>${rows.map(r=>`<tr>${r.map(c=>`<td>${mdI(c)}</td>`).join("")}</tr>`).join("")}</table>`);continue}
    if(l.startsWith(">")){const b=[];while(i<L.length&&L[i].startsWith(">")){b.push(L[i].replace(/^>\s?/,""));i++}
      let k="",t=b.join("\n"),mm=t.match(/^\[!(note|warn|tip|review)\]\s*/i);if(mm){k=mm[1].toLowerCase();t=t.slice(mm[0].length)}
      const lab={note:"Note",warn:"Warning",tip:"Tip",review:"Needs confirmation"}[k]||"";
      o.push(`<blockquote class="${k}">${lab?`<span class="wkbt">${lab}</span>`:""}${t.split(/\n{2,}/).map(x=>mdI(x).replace(/\n/g,"<br>")).join("<br><br>")}</blockquote>`);continue}
    if(/^\s*([-*])\s+/.test(l)||/^\s*\d+[.)]\s+/.test(l)){const ol=/^\s*\d+[.)]\s+/.test(l),it=[];while(i<L.length&&(ol?/^\s*\d+[.)]\s+/.test(L[i]):/^\s*([-*])\s+/.test(L[i]))){it.push(L[i].replace(ol?/^\s*\d+[.)]\s+/:/^\s*([-*])\s+/,""));i++}
      o.push(`<${ol?"ol":"ul"}>${it.map(x=>`<li>${mdI(x)}</li>`).join("")}</${ol?"ol":"ul"}>`);continue}
    const p=[];while(i<L.length&&L[i].trim()&&!/^(#{1,3}\s|---+\s*$|>|\||\s*[-*]\s+|\s*\d+[.)]\s+)/.test(L[i])){p.push(L[i]);i++}
    if(!p.length){p.push(L[i]);i++}
    const h=mdI(p.join("\n")).replace(/\n/g,"<br>");o.push(/^<figure>.*<\/figure>$/.test(h)?h:`<p>${h}</p>`)}
  return{html:o.join("\n"),toc}}
async function wkLoad(force){try{const r=await api("wiki_list",{});wkPages=r.pages||[];wkLoaded=true;if(r.seed_err)console.warn("wiki seed:",r.seed_err);if(r.seeded)wkToast=r.seeded+" starter pages were added"}catch(e){alert(e.message)}}
let wkToast="";
async function wkOpen(id){if(!$("#wkdlg").open)$("#wkdlg").showModal();$("#wkdlg").classList.remove("nav");if(!wkLoaded)await wkLoad();if(id)wkCur=id;wkEd=null;wkDraw()}
const wkCats=()=>{const m=new Map();for(const p of wkPages){const c=p.cat||"General";if(!m.has(c))m.set(c,[]);m.get(c).push(p)}return[...m.entries()].sort((a,b)=>(a[0]==="Import Review")-(b[0]==="Import Review")||a[0].localeCompare(b[0]))};
const wkTags=()=>{const m=new Map();for(const p of wkPages)for(const t of wkTagsOf(p)){const k=t.toLowerCase();const e=m.get(k)||{t,n:0};e.n++;m.set(k,e)}return[...m.values()].sort((a,b)=>b.n-a.n||a.t.localeCompare(b.t))};
function wkMatch(p,q,tag){if(tag&&!wkTagsOf(p).some(t=>t.toLowerCase()===tag))return 0;if(!q)return 1;const hay=(p.title+" "+(p.cat||"")+" "+(p.tags||"")+" "+p.body).toLowerCase();return q.split(/\s+/).every(w=>hay.includes(w))?1:0}
function wkSnip(p,q){const b=String(p.body||"").replace(/!\[[^\]]*\]\([^)]*\)/g," ").replace(/[#>*|`\[\]]/g," ").replace(/\s+/g," ").trim();let k=0;const w=q.split(/\s+/)[0];if(w){const ix=b.toLowerCase().indexOf(w);if(ix>60)k=ix-50}
  let t=esc(b.slice(k,k+170));q.split(/\s+/).filter(x=>x.length>1).forEach(w=>{t=t.replace(new RegExp(w.replace(/[.*+?^${}()|[\]\\]/g,"\\$&"),"gi"),m=>`<mark>${m}</mark>`)});return(k?"\u2026":"")+t+"\u2026"}
function wkDraw(){const q=$("#wkq").value.trim().toLowerCase(),cats=wkCats(),tags=wkTags(),cur=wkPages.find(p=>p.id==wkCur);
  const openCats=(()=>{try{return JSON.parse(localStorage.getItem("tf_wkc")||"null")}catch(e){return null}})();
  $("#wkside").innerHTML=`<a class="wkl${!cur&&!q&&!wkTag&&!wkEd?" on":""}" data-wkhome>\u{1F3E0} Wiki home <span style="color:var(--mut)">(${wkPages.length})</span></a>`+cats.map(([c,l])=>{const has=cur&&(cur.cat||"General")===c,op=openCats?openCats.includes(c)||has:true;return `<details class="wkc" data-c="${ea(c)}" ${op?"open":""}><summary><span>${esc(c)}</span><em>${l.length}</em></summary>${l.map(p=>`<a class="wkl${p.id==wkCur&&!q?" on":""}" data-wkp="${ea(p.title)}" data-id="${p.id}">${esc(p.title)}</a>`).join("")}</details>`}).join("")+(tags.length?`<div class="wkh2">Tags</div><div>${tags.slice(0,40).map(t=>`<span class="wktg${wkTag===t.t.toLowerCase()?" on":""}" data-tag="${ea(t.t.toLowerCase())}">${esc(t.t)}<i>${t.n}</i></span>`).join("")}</div>`:"");
  const M=$("#wkmain");
  if(wkEd){wkDrawEdit();return}
  if(q||wkTag){const l=wkPages.filter(p=>wkMatch(p,q,wkTag));
    M.innerHTML=`<div class="wkres"><h1>${q?`Search: \u201c${esc(q)}\u201d`:"Tag: "+esc(wkTag)}</h1><div class="wkmeta">${l.length} page${l.length===1?"":"s"}${wkTag?` \u00B7 tagged <span class="wktg on" data-tag="">${esc(wkTag)} \u2715</span>`:""}</div>`+(l.map(p=>`<div class="wkr" data-id="${p.id}"><b>${esc(p.title)}</b><small>${esc(p.cat||"General")}${wkTagsOf(p).length?" \u00B7 "+wkTagsOf(p).map(esc).join(", "):""}</small><div style="font-size:13px">${wkSnip(p,q)}</div></div>`).join("")||'<div class="empty">Nothing found. Try fewer words or another tag.</div>')+"</div>";return}
  if(cur){const r=mdR(cur.body),tg=wkTagsOf(cur),rel=wkPages.filter(p=>p.id!==cur.id&&(((p.cat||"General")===(cur.cat||"General"))||wkTagsOf(p).some(t=>tg.map(x=>x.toLowerCase()).includes(t.toLowerCase())))).map(p=>({p,s:wkTagsOf(p).filter(t=>tg.map(x=>x.toLowerCase()).includes(t.toLowerCase())).length*2+((p.cat||"General")===(cur.cat||"General")?1:0)})).sort((a,b)=>b.s-a.s).slice(0,6);
    M.innerHTML=`<div class="wkart"><div class="wkcrumb"><a data-wkhome>Wiki</a> \u203A <a data-cat="${ea(cur.cat||"General")}">${esc(cur.cat||"General")}</a></div><h1>${esc(cur.title)}</h1><div class="wkmeta">${tg.map(t=>`<span class="wktg" data-tag="${ea(t.toLowerCase())}">${esc(t)}</span>`).join("")}<span>${cur.by_name?"Edited by "+esc(cur.by_name)+" \u00B7 ":""}${esc(String(cur.updated_at||"").slice(0,10))}</span>${wkCan()?'<button type="button" id="wkedit" style="margin-left:auto">\u270F Edit</button>':""}</div>${r.toc.length>2?`<div class="wktoc">${r.toc.map(t=>`<a data-sc="${t[0]}">${esc(t[1])}</a>`).join("")}</div>`:""}${r.html}${rel.length?`<div class="wkrel"><h4>Related pages</h4>${rel.map(x=>`<a class="wkl" data-id="${x.p.id}" style="padding-left:0;color:var(--pri,#2563eb)">\u2192 ${esc(x.p.title)}</a>`).join("")}</div>`:""}</div>`;M.scrollTop=0;return}
  const rec=[...wkPages].sort((a,b)=>String(b.updated_at).localeCompare(String(a.updated_at))).slice(0,6),ic=["\u{1F9F1}","\u{1F529}","\u{1F6E1}\uFE0F","\u{1F6AA}","\u{1F4D0}","\u{1F9F5}","\u{1F4CB}","\u{1F527}"];
  M.innerHTML=`<div class="wkhome"><h1>Wiki</h1><div style="color:var(--mut)">${wkPages.length} pages in ${cats.length} categories. Search above, browse a category, or pick a tag on the left.</div>${wkToast?`<div class="wkart"><blockquote class="tip">${esc(wkToast)}</blockquote></div>`:""}<div class="wkgrid">${cats.map(([c,l],i)=>`<div class="wkcard" data-cat="${ea(c)}"><span class="ic">${ic[i%ic.length]}</span><b>${esc(c)}</b><small>${l.length} page${l.length===1?"":"s"}: ${l.slice(0,3).map(p=>esc(p.title)).join(", ")}${l.length>3?"\u2026":""}</small></div>`).join("")||'<div class="empty">No pages yet. Add the first one with + New page.</div>'}</div>${rec.length?`<h3 style="margin:22px 0 6px">Recently updated</h3>${rec.map(p=>`<a class="wkl" data-id="${p.id}" style="color:var(--pri,#2563eb)">${esc(p.title)} <span style="color:var(--mut);font-size:12px">\u00B7 ${esc(p.cat||"General")}</span></a>`).join("")}`:""}</div>`}
function wkDrawEdit(){const e=wkEd,cats=[...new Set(wkPages.map(p=>p.cat||"General"))],tg=wkTags();
  $("#wkmain").innerHTML=`<div class="wked"><h1 style="font-size:20px">${e.id?"Edit page":"New page"}</h1><label>Title</label><input id="wke_t" maxlength="150" value="${ea(e.title)}"><div class="two"><div><label>Category (group)</label><input id="wke_c" list="wke_cl" maxlength="80" value="${ea(e.cat)}" placeholder="e.g. Covers & Fabric"><datalist id="wke_cl">${cats.map(c=>`<option value="${ea(c)}">`).join("")}</datalist></div><div><label>Tags (comma separated)</label><input id="wke_g" list="wke_gl" value="${ea(e.tags)}" placeholder="e.g. chemset, bolts, M16"><datalist id="wke_gl">${tg.map(t=>`<option value="${ea(t.t)}">`).join("")}</datalist></div></div>
   <label>Content</label><div class="wktb"><button type="button" data-ins="## ">H2</button><button type="button" data-ins="### ">H3</button><button type="button" data-ins="**|**">Bold</button><button type="button" data-ins="- ">List</button><button type="button" data-ins="1. ">Numbered</button><button type="button" data-ins="\n| Col A | Col B |\n|---|---|\n| | |\n">Table</button><button type="button" data-ins="> [!note] ">Note</button><button type="button" data-ins="> [!warn] ">Warning</button><button type="button" data-ins="> [!tip] ">Tip</button><button type="button" id="wke_img">\u{1F5BC} Image</button><button type="button" id="wke_pv">Preview</button><input type="file" id="wke_f" accept="image/*" hidden></div>
   <textarea id="wke_b">${esc(e.body)}</textarea><div id="wke_p" class="wkart" hidden></div>
   <div class="acts" style="position:static;border:0;margin:0;padding:0"><button id="wke_d" class="danger" style="${e.id?"":"visibility:hidden"}">Delete</button><span class="sp"></span><button id="wke_x">Cancel</button><button class="p" id="wke_s">Save page</button></div><small style="color:var(--mut)">Tip: link to another page with [text](wiki:Page title). Images show with a caption.</small></div>`;$("#wkmain").scrollTop=0}
async function wkImgUp(f){if(!f)return null;let d=await new Promise((res,rej)=>{const r=new FileReader();r.onload=()=>res(r.result);r.onerror=rej;r.readAsDataURL(f)});
  if(f.size>1500000&&/^image\/(png|jpeg|webp)/.test(f.type)){d=await new Promise(res=>{const im=new Image();im.onload=()=>{const k=Math.min(1,1600/im.width),c=document.createElement("canvas");c.width=im.width*k;c.height=im.height*k;const x=c.getContext("2d");x.fillStyle="#fff";x.fillRect(0,0,c.width,c.height);x.drawImage(im,0,0,c.width,c.height);res(c.toDataURL("image/jpeg",.86))};im.onerror=()=>res(d);im.src=d})}
  const r=await api("wiki_up",{data:d,name:f.name});return r.id}
function wkSetCur(id){wkCur=id;wkEd=null;$("#wkq").value="";wkTag="";$("#wkdlg").classList.remove("nav");wkDraw()}
const wkd=$("#wkdlg");
$("#wk_open").onclick=()=>wkOpen();$("#wkx").onclick=()=>wkd.close();$("#wknew").onclick=()=>{if(!wkCan())return;wkEd={id:0,title:"",cat:wkCur&&(wkPages.find(p=>p.id==wkCur)||{}).cat||"",tags:"",body:""};wkd.classList.remove("nav");wkDraw()};
$("#wkbr").onclick=()=>wkd.classList.toggle("nav");
$("#wkq").addEventListener("input",()=>{wkEd=null;wkDraw()});
wkd.addEventListener("click",async e=>{const t=e.target;
  if(t.closest("[data-wkhome]")){wkCur=0;wkTag="";wkEd=null;$("#wkq").value="";wkDraw();return}
  const sc=t.closest("[data-sc]");if(sc){const h=document.getElementById(sc.dataset.sc);if(h)h.scrollIntoView({behavior:"smooth",block:"start"});return}
  const tg=t.closest("[data-tag]");if(tg){wkTag=tg.dataset.tag===wkTag?"":tg.dataset.tag;wkEd=null;wkDraw();return}
  const ct=t.closest("[data-cat]");if(ct&&!ct.closest("details")){const c=ct.dataset.cat,f=wkPages.find(p=>(p.cat||"General")===c);if(f)wkSetCur(f.id);return}
  const wp=t.closest("[data-wkp]");if(wp){const nm=wp.dataset.wkp.toLowerCase(),f=wkPages.find(p=>p.title.toLowerCase()===nm);if(f)wkSetCur(f.id);return}
  const id=t.closest("[data-id]");if(id&&!t.closest("details summary")){wkSetCur(+id.dataset.id);return}
  if(t.closest("#wkedit")){const p=wkPages.find(x=>x.id==wkCur);if(p){wkEd={id:p.id,title:p.title,cat:p.cat||"",tags:p.tags||"",body:p.body||""};wkDraw()}return}
  const ins=t.closest("[data-ins]");if(ins){const ta=$("#wke_b"),v=ins.dataset.ins.replace(/\\n/g,"\n"),a=ta.selectionStart,b=ta.selectionEnd,sel=ta.value.slice(a,b);const ins2=v.includes("|**")||v==="**|**"?"**"+(sel||"text")+"**":v+sel;ta.setRangeText(ins2,a,b,"end");ta.focus();return}
  if(t.closest("#wke_img")){$("#wke_f").click();return}
  if(t.closest("#wke_pv")){const pv=$("#wke_p"),on=pv.hidden;pv.hidden=!on;$("#wke_b").hidden=on;if(on)pv.innerHTML=mdR($("#wke_b").value).html;t.closest("#wke_pv").textContent=on?"Edit":"Preview";return}
  if(t.closest("#wke_x")){wkEd=null;wkDraw();return}
  if(t.closest("#wke_s")){try{const r=await api("wiki_save",{id:wkEd.id,title:$("#wke_t").value,cat:$("#wke_c").value,tags:$("#wke_g").value,body:$("#wke_b").value});await wkLoad();wkSetCur(r.id)}catch(er){alert(er.message)}return}
  if(t.closest("#wke_d")){if(!confirm("Delete this page?"))return;try{await api("wiki_del",{id:wkEd.id});await wkLoad();wkCur=0;wkEd=null;wkDraw()}catch(er){alert(er.message)}return}
  if(t.matches("img[data-wz]")){const z=$("#wkzoom");z.innerHTML=`<img src="${ea(t.src)}">`;z.hidden=false}});
wkd.addEventListener("change",async e=>{if(e.target.id!=="wke_f")return;const f=e.target.files[0];e.target.value="";if(!f)return;try{const id=await wkImgUp(f),ta=$("#wke_b"),cap=await ask("Caption for this image (optional)","")||"";ta.setRangeText("\n!["+cap.trim()+"](wimg:"+id+")\n",ta.selectionStart,ta.selectionEnd,"end")}catch(er){alert(er.message)}});
wkd.addEventListener("toggle",()=>{});wkd.addEventListener("close",()=>{wkEd=null});
document.addEventListener("click",e=>{const z=$("#wkzoom");if(z&&!z.hidden&&z.contains(e.target))z.hidden=true});
document.addEventListener("toggle",e=>{const d=e.target;if(d.matches&&d.matches("#wkside details")){try{const o=[...document.querySelectorAll("#wkside details[open]")].map(x=>x.dataset.c);localStorage.setItem("tf_wkc",JSON.stringify(o))}catch(x){}}},true);
try{if(localStorage.getItem("tf_rp"))toolsOpen(true)}catch(e){}
function startDraft(gid){if(LV==="view")return;const g=/^\d+$/.test(String(gid))?String(gid):"";
  if(g){const c=getGC();let ch=c.delete(g);if(gParent(g))ch=c.delete(gParent(g))||ch;if(ch)try{localStorage.setItem("tf_gc_"+W,JSON.stringify([...c]))}catch(e){}}
  draft={grp:g,t:{id:0,title:"",order_no:"",customer:"",drawing_type:"",start_date:"",end_date:"",status:ST[0][0],assigned_to:"",notes:"",group_id:g,x:{}}};
  render();const i=document.querySelector("tr.draft input[data-f=title]");if(i){i.focus();try{i.scrollIntoView({block:"center"})}catch(e){}}}
const setDraft=(f,v)=>{if(!draft)return;if(f.indexOf("x_")===0)draft.t.x[f]=v;else draft.t[f]=v};
async function draftSave(){const d=draft;if(!d)return;const t=d.t;
  if(!String(t.title).trim()){const i=document.querySelector("tr.draft input[data-f=title]");if(i)i.focus();return}
  try{const r=await api("save",{title:t.title,order_no:t.order_no,customer:t.customer,drawing_type:t.drawing_type,start_date:t.start_date,end_date:t.end_date,status:t.status,assigned_to:t.assigned_to,notes:"",group_id:t.group_id,x:t.x});
    if(r&&r.id)await api("task_order",{id:+r.id,before:0});draft=null;await load(true)}catch(e){alert(e.message)}}
document.addEventListener("keydown",e=>{if(e.key==="Escape"&&draft&&!document.querySelector("dialog[open],#rpop")){draft=null;render()}});
const addRow=(gid,span,top,gc)=>LV==="view"?"":`<tr class="addrow" style="--gc:${gc||"#64748b"}"><td colspan="${span}"><a data-gadd="${gid}">+ New task</a>${top?`<a data-gsub="${gid}">+ Sub-group</a>`:""}<a data-gnew="1">+ Group</a></td></tr>`;
function openDlg(t,grp){
  editing=t?t.id:null;
  $("#t").value=t?t.title:"";$("#on").value=t?t.order_no||"":"";$("#pj").value=t?t.customer||"":"";$("#dt").innerHTML=dtOpts(t?t.drawing_type||"":"");$("#dt").value=t?t.drawing_type||"":"";
  $("#sd").value=t?t.start_date||"":"";$("#du").value=t?t.end_date||"":"";$("#st").innerHTML=stOpts(t?t.status:ST[0][0]);
  $("#as").innerHTML=asOpts(t?t.assigned_to:"");{const r=$("#dcoRow");r.style.display=t&&LV!=="view"?"flex":"none";$("#dcob").dataset.co=t?t.id:"";$("#dcot").textContent=t&&(t.co||[]).length?coNames(t):""}$("#nt").value=t?t.notes||"":"";
  $("#gp").innerHTML=`<option value="">No group</option>`+gOpts("");$("#gp").value=t?(t.group_id||""):(/^\d+$/.test(grp!==undefined?grp:G)?(grp!==undefined?grp:G):"");
  $("#xf").innerHTML=cols.map(c=>`<label>${esc(c.name)}</label>`+xCtl(c,t?xv(t,c):"","dlg")).join("");
  const on=$("#ovn");if(t&&ov(t).length){on.style.display="block";on.textContent="⚠ "+ovText(t)}else on.style.display="none";
  $("#del").style.display=t&&LV==="full"?"":"none";
  const ro=LV==="view";$("#sv").style.display=ro?"none":"";
  document.querySelectorAll("#dlg input,#dlg select,#dlg textarea").forEach(el=>el.disabled=ro);
  thrHome();thrInit(t);document.querySelectorAll("#thr select,#thr textarea").forEach(el=>el.disabled=false);
  $("#dlg").showModal();$("#t").focus();if(t)thrLoad(true);
}
const avPal=["#e11d48","#2563eb","#16a34a","#d97706","#7c3aed","#0891b2","#db2777","#65a30d","#ea580c","#4f46e5","#0d9488","#b91c1c","#9333ea","#0369a1","#ca8a04","#be185d","#15803d","#c2410c","#6d28d9","#047857"];
const avC=n=>{const l=Object.entries(names||{}).map(([id,nm])=>({id:+id,name:nm})).sort((a,b)=>a.id-b.id),i=l.findIndex(u=>u.name===n);if(i>=0)return avPal[i%avPal.length];let h=0;for(const c of String(n||"?"))h=(h*31+c.charCodeAt(0))%avPal.length;return avPal[h]};
const avI=n=>{const w=String(n||"?").trim().split(/\s+/);return(w.length>1?w[0][0]+w[w.length-1][0]:String(w[0]).slice(0,2)).toUpperCase()};
const avHtml=(n,c="")=>`<span class="av ${c}" style="background:${avC(n)}" title="${ea(n)}">${esc(avI(n))}</span>`;
function dayLbl(d){const t=new Date(),k=x=>x.getFullYear()+"-"+String(x.getMonth()+1).padStart(2,"0")+"-"+String(x.getDate()).padStart(2,"0"),y=new Date(t.getTime()-864e5);
  return d===k(t)?"Today":d===k(y)?"Yesterday":new Date(d+"T12:00:00").toLocaleDateString(undefined,{weekday:"short",day:"numeric",month:"short",year:"numeric"})}
function thrHtml(r){let last="",lu=0,out="";
  for(const c of r.comments){const day=String(c.created_at).slice(0,10),mine=c.user_id==r.me,ins=c.kind==="instruction",nw=c.id>r.prev&&!mine;
    if(day!==last){out+=`<div class="dsep">${esc(dayLbl(day))}</div>`;last=day;lu=0}
    const gp=lu===c.user_id&&!ins;lu=c.user_id;
    out+=`<div class="mrow${mine?" me":""}${gp?" gp":""}${ins?" ins":""}${nw?" new":""}">${avHtml(c.name,"sm")}<div class="bub">${mine||me.role==="admin"?`<button class="x" data-cd="${c.id}" title="Delete">✕</button>`:""}${!mine&&!gp||ins?`<span class="who">${esc(c.name)}${c.to_name?` <i>→ ${esc(c.to_name)}</i>`:""}</span>`:(c.to_name&&!mine?`<span class="who"><i>→ ${esc(c.to_name)}</i></span>`:"")}${esc(c.body)}<span class="tm">${mine&&c.to_name?"→ "+esc(c.to_name)+" · ":""}${esc(String(c.created_at).slice(11,16))}</span></div></div>`}
  return out}
const fmtT=s=>(s||"").slice(0,16).replace("T"," ");
function thrHome(){const d=$("#dlg"),ref=$("#del").parentNode;if($("#thr").parentNode!==d)d.insertBefore($("#thr"),ref)}
function openMsg(id){const t=tasks.find(x=>x.id==id);if(!t)return;
  $("#mord").textContent=t.order_no?"Order #"+t.order_no:"";$("#mtit").textContent=t.title||"";
  thrInit(t);$("#mbody").appendChild($("#thr"));document.querySelectorAll("#thr select,#thr textarea").forEach(el=>el.disabled=false);
  $("#mdlg").showModal();thrLoad(true);$("#cbody").focus()}
$("#mcl").onclick=()=>$("#mdlg").close();
$("#mdlg").addEventListener("close",()=>{if($("#thr").parentNode===$("#mbody")){thrHome();$("#thr").hidden=true;thrId=null}if(dirty)poll()});
$("#mopen").onclick=()=>{const id=thrId,t=tasks.find(x=>x.id==id);$("#mdlg").close();if(t)openDlg(t)};
function thrInit(t){
  thrId=t?t.id:null;$("#thr").hidden=!t;$("#thl").innerHTML="";$("#cbody").value="";if(!t)return;
  $("#cto").innerHTML=`<option value="">To: everyone</option>`+users.filter(u=>u.id!=me.id).map(u=>`<option value="${u.id}">To: ${esc(u.name)}</option>`).join("");
  $("#ckind").value="comment";
}
async function thrLoad(first){
  if(!thrId||!($("#dlg").open||$("#mdlg").open))return;
  try{const r=await api("cm_list",{task_id:thrId}),k=r.comments.map(c=>c.id).join(",");if(!first&&k===thrKey)return;thrKey=k;
    $("#thl").innerHTML=r.comments.length?thrHtml(r):`<div class="mempty"><svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"><path d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 4v-4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>No messages yet.<br><small>Write the first one below.</small></div>`;
    $("#thl").scrollTop=1e9;
    if(first){const t=tasks.find(x=>x.id==thrId);if(cmeta[thrId]){cmeta[thrId]=[cmeta[thrId][0],0,0];render()}api("ping").catch(()=>{});inboxRefresh()}
  }catch(e){}
}
async function inboxRefresh(){try{const r=await api("notices");inboxN=r.unread+r.comments;bellUpd()}catch(e){}}
async function thrSend(){
  const b=$("#cbody").value.trim();if(!b||!thrId)return;
  try{await api("cm_add",{task_id:thrId,body:b,kind:$("#ckind").value,to_user_id:$("#cto").value||0});$("#cbody").value="";grow($("#cbody"));thrKey="";await thrLoad(true);dirty=true}catch(e){alert(e.message)}
}
$("#csend").onclick=thrSend;
const grow=t=>{t.style.height="auto";t.style.height=Math.min(t.scrollHeight,130)+"px"};
$("#cbody").addEventListener("input",e=>grow(e.target));
$("#cbody").addEventListener("keydown",e=>{if(e.key==="Enter"&&(e.ctrlKey||e.metaKey)){e.preventDefault();thrSend()}});
$("#ckind").onchange=()=>{if($("#ckind").value==="instruction"&&!$("#cto").value&&$("#cto").options.length>1)$("#cto").selectedIndex=1};
$("#thl").addEventListener("click",async e=>{const id=e.target.closest("[data-cd]")?.dataset.cd;if(!id)return;try{await api("cm_del",{id});thrKey="";thrLoad(true);dirty=true}catch(x){alert(x.message)}});
const FK={hours:["⏱","#f59e0b"],nudge:["▶️","#3b82f6"],assign:["📌","#8b5cf6"],alert:["🔔","#ef4444"],approval:["✅","#10b981"],info:["✨","#6366f1"]};
let fTab="n",fData=null;
const fAgo=s=>{const d=new Date((s||"").replace(" ","T"));if(isNaN(d))return"";const m=Math.round((Date.now()-d)/6e4);return m<1?"just now":m<60?m+" min ago":m<1440?Math.floor(m/60)+" h ago":Math.floor(m/1440)+" d ago"};
async function fOpen(){try{fData=await api("notices");inboxN=fData.unread+fData.comments;bellUpd();fPaint();const d=$("#idlg");if(!d.open)d.showModal()}catch(e){alert(e.message)}}
function fPaint(){
  const r=fData,h=new Date().getHours(),g=h<12?"Good morning":h<17?"Good afternoon":"Good evening",fn=((me&&me.name)||"").split(" ")[0];
  const tot=r.unread+r.comments;$("#fgreet").innerHTML=`<b>${g}${fn?", "+esc(fn):""}.</b> ${tot?`You have <b>${tot}</b> thing${tot>1?"s":""} waiting.`:"You're all caught up. Nice work. 🎉"}`;
  $("#ftn").hidden=!r.unread;$("#ftn").textContent=r.unread;$("#ftc").hidden=!r.comments;$("#ftc").textContent=r.comments;
  document.querySelectorAll("#idlg .ftabs button").forEach(b=>b.classList.toggle("on",b.dataset.ft===fTab));
  $("#iclr").hidden=fTab!=="n";
  if(fTab==="n"){
    if(!r.items.length){$("#ilist").innerHTML=`<div class="fe"><span>🌿</span>Nothing here yet.<br>I'll remind you about hours and tasks as they come up.</div>`;return}
    const td=new Date().toDateString();let last="",out="";
    r.items.forEach((n,i)=>{const d=new Date(n.created_at.replace(" ","T")),lab=d.toDateString()===td?"Today":"Earlier";if(lab!==last){out+=`<div class="fd">${lab}</div>`;last=lab}
      const k=FK[n.kind]||FK.info,un=+n.unread;
      const acts=n.act==="hours"?`<button data-fa="hours" data-id="${n.id}">Log my hours</button>`:n.act==="task"?`<button data-fa="task" data-id="${n.id}" data-w="${n.ws_id}" data-t="${n.task_id}">Open task</button>`:"";
      out+=`<div class="fc${un?" un":""}" style="animation-delay:${Math.min(i,8)*40}ms"><div class="fi" style="background:${k[1]}22">${k[0]}</div><div class="fb"><b>${esc(n.title)}</b>${n.body?`<p>${esc(n.body)}</p>`:""}<small>${fAgo(n.created_at)}</small><div class="fa">${acts}${un?`<button class="g" data-fa="read" data-id="${n.id}">Got it</button>`:""}<button class="g" data-fa="del" data-id="${n.id}">Dismiss</button></div></div></div>`});
    $("#ilist").innerHTML=out;
  }else{
    api("inbox").then(x=>{if(fTab!=="c")return;$("#ilist").innerHTML=x.items.length?x.items.map(i=>`<div class="ib" data-w="${i.ws_id}" data-t="${i.task_id}"><small style="color:var(--mut)">${esc(i.from_name)}${i.kind==="instruction"?" 📌 instruction":""} · ${esc(i.ws_name)} · ${esc(fmtT(i.created_at))}</small><br><b>${esc(i.title)}</b><br>${esc(i.body.length>140?i.body.slice(0,140)+"…":i.body)}</div>`).join(""):`<div class="fe"><span>💬</span>No new comments.</div>`}).catch(()=>{});
  }
}
$("#bell").onclick=fOpen;
$("#icl").onclick=()=>$("#idlg").close();
$("#idlg .ftabs").addEventListener("click",e=>{const b=e.target.closest("[data-ft]");if(!b)return;fTab=b.dataset.ft;if(fData)fPaint()});
$("#iall").onclick=async()=>{try{await api("notice_read",{});await api("inbox_read");await fOpen();load(true)}catch(e){alert(e.message)}};
$("#iclr").onclick=async()=>{if(!confirm("Clear all of Flow's notifications?"))return;try{await api("notice_del",{});await fOpen()}catch(e){alert(e.message)}};
$("#ilist").addEventListener("click",async e=>{
  const f=e.target.closest("[data-fa]");
  if(f){const a=f.dataset.fa,id=+f.dataset.id;try{
    if(a==="del"){await api("notice_del",{id});await fOpen()}
    else if(a==="read"){await api("notice_read",{id});await fOpen()}
    else{await api("notice_read",{id});$("#idlg").close();inboxRefresh();
      if(a==="hours")mhOpen();else{const w=+f.dataset.w,t=+f.dataset.t;if(w===W){const tk=tasks.find(x=>x.id==t);if(tk)openDlg(tk)}else location.href="index.php?w="+w+"&t="+t}}
  }catch(x){alert(x.message)}return}
  const b=e.target.closest(".ib");if(!b)return;const w=+b.dataset.w,t=+b.dataset.t;$("#idlg").close();
  if(w===W)openMsg(t);else location.href="index.php?w="+w+"&t="+t+"&m=1"});
$("#dt").addEventListener("change",async e=>{if(e.target.value!=="__new")return;const prev=editing?((tasks.find(x=>x.id==editing)||{}).drawing_type||""):"";
  try{const n=await addDtype();$("#dt").innerHTML=dtOpts(n||prev);$("#dt").value=n||prev}catch(err){alert(err.message);$("#dt").innerHTML=dtOpts(prev);$("#dt").value=prev}});
$("#st").addEventListener("change",async e=>{if(e.target.value!=="__new")return;const prev=editing?((tasks.find(x=>x.id==editing)||{}).status||ST[0][0]):ST[0][0];
  try{const k=await addStatus();$("#st").innerHTML=stOpts(k||prev);$("#st").value=k||prev}catch(err){alert(err.message);$("#st").innerHTML=stOpts(prev);$("#st").value=prev}});
$("#add").onclick=()=>openDlg(null);
$("#cx").onclick=()=>$("#dlg").close();
$("#sv").onclick=async()=>{
  const title=$("#t").value.trim();if(!title)return;
  const x={};document.querySelectorAll("#xf [data-x]").forEach(el=>x[el.dataset.x]=el.value);
  try{await api("save",{id:editing,title,order_no:$("#on").value,customer:$("#pj").value,drawing_type:$("#dt").value,start_date:$("#sd").value,end_date:$("#du").value,status:$("#st").value,assigned_to:$("#as").value,notes:$("#nt").value,group_id:$("#gp").value,x});$("#dlg").close();load(true)}catch(e){alert(e.message)}
};
$("#del").onclick=()=>{if(editing&&confirm("Delete this task?")){$("#dlg").close();act("delete",{ids:[editing]})}};
$("#dv").onclick=async()=>{try{await api("pref_view",{v:view});me.pref_view=view;render()}catch(e){alert(e.message)}};
function syncViewBtns(){if(view!=="list")draft=null;$("#add").style.display=view==="list"?"none":"";for(const [id,v] of [["vl","list"],["vb","board"],["vg","gantt"]])$("#"+id).classList.toggle("on",view===v)}
$("#vb").onclick=()=>{view="board";syncViewBtns();render()};
$("#vl").onclick=()=>{view="list";syncViewBtns();render()};
$("#vg").onclick=()=>{view="gantt";syncViewBtns();render()};
for(const id of ["q","fs","fp","fa"])$("#"+id).addEventListener("input",render);
$("#fme").addEventListener("click",()=>{const a=$("#fa");const on=String(a.value)===String(me.id);if(!on&&![...a.options].some(o=>o.value==String(me.id)))a.insertAdjacentHTML("beforeend",`<option value="${me.id}">${esc(me.name||"Me")}</option>`);a.value=on?"":String(me.id);syncFme();a.dispatchEvent(new Event("input"))});

$("#main").addEventListener("click",e=>{
  const sdh=e.target.closest(".dh[data-dh]");if(sdh){toggleCC(sdh.dataset.dh);return}
  if(e.target.closest(".dh")){toggleDone();return}
  const gh=e.target.closest(".gh");if(gh){const ad=e.target.closest("[data-gadd]");if(ad)startDraft(ad.dataset.gadd);else toggleGroup(gh.dataset.gh);return}
  if(e.target.classList.contains("cc"))return;
  const ga=e.target.closest(".addrow [data-gadd]");if(ga){startDraft(ga.dataset.gadd);return}
  if(e.target.closest("[data-dsave]")){draftSave();return}
  if(e.target.closest("[data-dcancel]")){draft=null;render();return}
  const gs=e.target.closest(".addrow [data-gsub]");if(gs){(async()=>{const n=await ask("New sub-group name:");if(!n||!n.trim())return;try{await api("gr_add",{name:n,parent_id:gs.dataset.gsub});await load(true)}catch(err){alert(err.message)}})();return}
  if(e.target.closest(".addrow [data-gnew]")){(async()=>{const n=await ask("New group name:");if(!n||!n.trim())return;try{await api("gr_add",{name:n});await load(true)}catch(err){alert(err.message)}})();return}
  const tt=e.target.closest("input.tt");if(tt){const tr=tt.closest("[data-id]"),t0=tr&&tasks.find(x=>x.id==tr.dataset.id);if(t0){openDlg(t0);return}}
  const hb=e.target.closest("[data-hrs]");if(hb){openTL(+hb.dataset.hrs);return}
  const cb=e.target.closest(".cmb");if(cb){const ce=cb.closest("[data-id]");if(ce){openMsg(+ce.dataset.id);return}}
  const el=e.target.closest("[data-id]");if(!el)return;
  if(el.tagName==="TR"&&!e.target.closest(".o"))return;
  const t=tasks.find(x=>x.id==el.dataset.id);if(t)openDlg(t);
});
$("#main").addEventListener("change",e=>{
  const f=e.target.dataset.f,tr=e.target.closest("tr[data-id]");
  if(draft&&f&&tr&&tr.dataset.id==="0"){const v=e.target.value;
    if(v==="__new"){(async()=>{try{let n=null;if(f==="status")n=await addStatus();else if(f==="drawing_type")n=await addDtype();if(n)setDraft(f,n)}catch(err){alert(err.message)}render()})();return}
    setDraft(f,v);return}
  if(f==="status"&&tr&&e.target.value==="__new"){const id=tr.dataset.id;(async()=>{try{const k=await addStatus();if(k)await api("field",{id,f,v:k});await load(true)}catch(err){alert(err.message);await load(true)}})();return}
  if(f&&tr&&e.target.value==="__new"&&f==="drawing_type"){const el=e.target,id=tr.dataset.id;(async()=>{try{const n=await addDtype();if(n)await api("field",{id,f,v:n});await load(true)}catch(err){alert(err.message);await load(true)}})();return}
  if(f&&tr){const v=e.target.value;e.target.blur();act("field",{id:tr.dataset.id,f,v});return}
  if(e.target.id==="all"){vis().forEach(t=>e.target.checked?sel.add(t.id):sel.delete(t.id));render()}
  else if(e.target.classList.contains("cc")){const id=+e.target.closest(".card").dataset.id;e.target.checked?sel.add(id):sel.delete(id);updBar()}
  else if(e.target.classList.contains("rc")){const id=+e.target.closest("tr").dataset.id;e.target.checked?sel.add(id):sel.delete(id);render()}
});
$("#main").addEventListener("keydown",e=>{if(e.key==="Enter"&&e.target.dataset.f){const dr=e.target.closest("tr.draft");e.target.blur();if(dr)draftSave()}});
$("#main").addEventListener("focusout",()=>setTimeout(()=>{if(pend)render()},0));
$("#bar").addEventListener("change",async e=>{
  if(e.target.id!=="bmv"||e.target.value==="")return;const ids=[...sel],g=e.target.value;sel.clear();
  try{for(const id of ids)await api("field",{id,f:"group_id",v:g})}catch(err){alert(err.message)}await load(true);
});
$("#bar").addEventListener("click",async e=>{
  const id=e.target.id,ids=[...sel];if(!id||!ids.length)return;
  if(id==="bclr"){sel.clear();render()}
  if(id==="bdup"){sel.clear();act("dup",{ids})}
  if(id==="bdel"&&confirm("Delete "+ids.length+" selected task(s)? This cannot be undone.")){sel.clear();act("delete",{ids})}
});
// Logos become white silhouettes on the navy bars (transparent logos via CSS; white-background logos are converted)
document.addEventListener("load",e=>{const im=e.target;if(!im||im.tagName!=="IMG"||!im.closest(".wl,#wlogo,#vname")||im.dataset.w)return;
  try{const nw=im.naturalWidth||32,nh=im.naturalHeight||32,k=Math.min(1,300/Math.max(nw,nh)),w=Math.max(1,Math.round(nw*k)),h=Math.max(1,Math.round(nh*k));
    const c=document.createElement("canvas");c.width=w;c.height=h;const x=c.getContext("2d");x.drawImage(im,0,0,w,h);const img=x.getImageData(0,0,w,h),d=img.data;
    const cs=[[0,0],[w-1,0],[0,h-1],[w-1,h-1]].map(([a,b])=>{const o=(b*w+a)*4;return[d[o],d[o+1],d[o+2],d[o+3]]});
    let op=0;for(let o=3;o<d.length;o+=4)if(d[o]>200)op++;
    if(op/(w*h)>0.55){im.dataset.w=1;return}
    if(cs.every(p=>p[3]<40)){im.dataset.w=1;im.classList.add("wh");return}
    if(cs.every(p=>p[3]>200&&(p[0]+p[1]+p[2])/3>225)){
      for(let o=0;o<d.length;o+=4){const lum=0.3*d[o]+0.59*d[o+1]+0.11*d[o+2],a=Math.min(1,Math.max(0,(255-lum)/255*1.8))*(d[o+3]/255);d[o]=d[o+1]=d[o+2]=255;d[o+3]=Math.round(a*255)}
      x.putImageData(img,0,0);im.dataset.w=1;im.src=c.toDataURL("image/png")}
  }catch(err){}
},true);
// ---- Date range picker (click the Start / End cell) ----
let rp=null;
const ISO=(y,m,d)=>y+"-"+String(m+1).padStart(2,"0")+"-"+String(d).padStart(2,"0");
function rpClose(){const p=$("#rpop");if(p)p.remove();rp=null}
function rpDraw(){
  const p=$("#rpop");if(!p||!rp)return;const n=window.innerWidth<640?1:2,td=today(),lo=rp.s&&rp.e?(rp.s<rp.e?rp.s:rp.e):rp.s,hi=rp.s&&rp.e?(rp.s<rp.e?rp.e:rp.s):(rp.s&&rp.hover?rp.hover:rp.s),a=hi&&lo&&hi<lo?hi:lo,b=hi&&lo&&hi<lo?lo:hi;
  let h='<div class="rm">';
  for(let k=0;k<n;k++){const d0=new Date(Date.UTC(rp.y,rp.m+k,1)),y=d0.getUTCFullYear(),m=d0.getUTCMonth(),dim=new Date(Date.UTC(y,m+1,0)).getUTCDate(),off=(d0.getUTCDay()+6)%7;
    h+=`<div><div class="rh">${k===0?'<button data-rn="-1">‹</button>':"<span></span>"}<span>${MON[m]} ${y}</span>${k===n-1?'<button data-rn="1">›</button>':"<span></span>"}</div><div class="rg">`+["Mo","Tu","We","Th","Fr","Sa","Su"].map(x=>`<div class="rw">${x}</div>`).join("")+'<div></div>'.repeat(off);
    for(let d=1;d<=dim;d++){const iso=ISO(y,m,d),on=iso===a||iso===b,inr=a&&b&&iso>a&&iso<b;h+=`<button type="button" class="rd${on?" on":inr?" in":""}${iso===td?" td":""}" data-rd="${iso}">${d}</button>`}
    h+="</div></div>"}
  h+=`</div><div class="rf"><span>${rp.single?"Pick a date":rp.s&&!rp.e?"Now pick the end date (click the same day for a one-day task)":"Pick the start date"}</span><span class="sp"></span><button type="button" data-rclear>Clear dates</button></div>`;
  p.innerHTML=h}
function rpOpen(btn){
  const tid=btn.dataset.rng||(btn.closest("[data-id]")||{dataset:{}}).dataset.id,t=+tid===0?(draft&&draft.t):tasks.find(x=>x.id==tid);if(!t||LV==="view")return;rpClose();
  if(btn.dataset.rngx){const v=xv(t,{id:btn.dataset.rngx.slice(2)})||"",ref=v||today();
    rp={id:t.id,f:btn.dataset.rngx,single:true,s:v||null,e:v||null,y:+ref.slice(0,4),m:+ref.slice(5,7)-1,hover:null}}
  else{const [s0,e0]=[t.start_date||"",t.end_date||""],ref=s0||e0||today();
  rp={id:t.id,s:null,e:null,y:+ref.slice(0,4),m:+ref.slice(5,7)-1,hover:null,old:[s0,e0]}}
  const p=document.createElement("div");p.id="rpop";document.body.appendChild(p);rpDraw();
  const r=btn.getBoundingClientRect(),w=p.offsetWidth,h=p.offsetHeight;
  p.style.left=Math.max(8,Math.min(r.left,window.innerWidth-w-8))+"px";p.style.top=Math.max(8,Math.min(r.bottom+6+h>window.innerHeight&&r.top-h-6>8?r.top-h-6:r.bottom+6,window.innerHeight-h-8))+"px";
}
async function rpApply(s,e){const id=rp.id,f=rp.single?rp.f:null;rpClose();
  if(id===0&&draft){if(f)setDraft(f,s);else{draft.t.start_date=s;draft.t.end_date=e}render();return}try{if(f)await api("field",{id,f,v:s});else await api("gantt_set",{id,s,e})}catch(err){alert(err.message)}await load(true)}
document.addEventListener("click",e=>{
  const p=$("#rpop");
  if(p&&p.contains(e.target)){
    const n=e.target.dataset.rn,d=e.target.dataset.rd;
    if(n){const x=new Date(Date.UTC(rp.y,rp.m+ +n,1));rp.y=x.getUTCFullYear();rp.m=x.getUTCMonth();rpDraw()}
    else if(d&&rp.single)rpApply(d,d);
    else if(d){if(!rp.s||rp.e){rp.s=d;rp.e=null;rpDraw()}else{const a=rp.s<d?rp.s:d,b=rp.s<d?d:rp.s;rpApply(a,b)}}
    else if(e.target.hasAttribute("data-rclear"))rpApply("","");
    return}
  const b=e.target.closest&&e.target.closest("[data-rng],[data-rngx]");
  if(b){e.stopPropagation();rpOpen(b);return}
  if(p)rpClose();
},true);
document.addEventListener("keydown",e=>{if(e.key==="Escape")rpClose()});
document.addEventListener("mouseover",e=>{if(!rp||!rp.s||rp.e)return;const d=e.target.dataset&&e.target.dataset.rd;if(d&&d!==rp.hover){rp.hover=d;rpDraw()}});
// ---- Drag rows up and down (works with mouse and touch) ----
function rdClear(){document.querySelectorAll(".rd-b,.rd-a,.rd-g,.rdrag").forEach(x=>x.classList.remove("rd-b","rd-a","rd-g","rdrag"));document.body.classList.remove("rdg")}
$("#main").addEventListener("pointerdown",e=>{const h=e.target.closest&&e.target.closest(".hd");if(!h||LV==="view"||(e.button||0)>0)return;const tr=h.closest("tr");e.preventDefault();
  rowDrag={id:+tr.dataset.id,tr,moved:false,tgt:null,pos:"before",gh:null}});
document.addEventListener("pointermove",e=>{const g=rowDrag;if(!g)return;
  if(!g.moved){g.moved=true;g.tr.classList.add("rdrag");document.body.classList.add("rdg")}
  if(e.clientY<70)window.scrollBy(0,-16);else if(window.innerHeight-e.clientY<70)window.scrollBy(0,16);
  document.querySelectorAll(".rd-b,.rd-a,.rd-g").forEach(x=>x.classList.remove("rd-b","rd-a","rd-g"));
  const el=document.elementFromPoint(e.clientX,e.clientY)||document.body,row=el.closest("tr.row[data-id]"),gh=el.closest(".gh");g.tgt=null;g.gh=null;
  if(row&&row!==g.tr){const r=row.getBoundingClientRect();g.pos=e.clientY<r.top+r.height/2?"before":"after";row.classList.add(g.pos==="before"?"rd-b":"rd-a");g.tgt=+row.dataset.id}
  else if(gh){gh.classList.add("rd-g");g.gh=gh.dataset.gh}
});
async function rdEnd(cancel){const g=rowDrag;if(!g)return;rowDrag=null;rdClear();if(!g.moved||cancel||(!g.tgt&&g.gh==null))return;
  const d={id:g.id};
  if(g.tgt){const t=tasks.find(x=>x.id===g.tgt);if(!t)return;let before=0;
    if(g.pos==="before")before=g.tgt;else{let k=tasks.findIndex(x=>x.id===g.tgt)+1;if(tasks[k]&&tasks[k].id===g.id)k++;before=tasks[k]?tasks[k].id:0}
    d.before=before;if(groups.length)d.g=t.group_id||0}
  else{d.before=0;d.g=g.gh==="none"?0:+g.gh}
  try{await api("task_order",d)}catch(err){alert(err.message)}
  await load(true)}
document.addEventListener("pointerup",()=>rdEnd(false));document.addEventListener("pointercancel",()=>rdEnd(true));
document.addEventListener("keydown",e=>{if(e.key==="Escape"&&rowDrag){rdEnd(true)}});
// ---- Drag groups up and down ----
let grDrag=null,grSwallow=false;
const grSibs=id=>{const g=groups.find(x=>String(x.id)===String(id));return g?groups.filter(x=>String(x.parent_id||"")===String(g.parent_id||"")).map(x=>String(x.id)):[]};
function grClear(){document.querySelectorAll(".gd-b,.gd-a,.gdr").forEach(x=>x.classList.remove("gd-b","gd-a","gdr"));document.body.classList.remove("rdg")}
$("#main").addEventListener("pointerdown",e=>{const h=e.target.closest&&e.target.closest(".ghd");if(!h||LV==="view"||(e.button||0)>0)return;const tr=h.closest("tr.gh");e.preventDefault();grDrag={id:tr.dataset.gh,tr,moved:false,tgt:null,pos:"before"}});
document.addEventListener("pointermove",e=>{const g=grDrag;if(!g)return;if(!g.moved){g.moved=true;g.tr.classList.add("gdr");document.body.classList.add("rdg")}
  if(e.clientY<70)window.scrollBy(0,-16);else if(window.innerHeight-e.clientY<70)window.scrollBy(0,16);
  document.querySelectorAll(".gd-b,.gd-a").forEach(x=>x.classList.remove("gd-b","gd-a"));g.tgt=null;
  const el=document.elementFromPoint(e.clientX,e.clientY)||document.body,gh=el.closest&&el.closest("tr.gh");
  if(gh&&gh!==g.tr&&grSibs(g.id).includes(gh.dataset.gh)){const r=gh.getBoundingClientRect();g.pos=e.clientY<r.top+r.height/2?"before":"after";gh.classList.add(g.pos==="before"?"gd-b":"gd-a");g.tgt=gh.dataset.gh}});
async function grEnd(cancel){const g=grDrag;if(!g)return;grDrag=null;grClear();if(!g.moved)return;grSwallow=true;setTimeout(()=>grSwallow=false,50);if(cancel||!g.tgt)return;
  const sib=grSibs(g.id).filter(x=>x!==g.id);let before=0;const k=sib.indexOf(g.tgt);before=g.pos==="before"?+g.tgt:(sib[k+1]?+sib[k+1]:0);
  try{await api("gr_order",{id:+g.id,before})}catch(err){alert(err.message)}await load(true)}
document.addEventListener("pointerup",()=>grEnd(false));document.addEventListener("pointercancel",()=>grEnd(true));
document.addEventListener("click",e=>{if(grSwallow||(e.target.closest&&e.target.closest(".ghd"))){e.stopPropagation();e.preventDefault()}},true);
document.addEventListener("keydown",e=>{if(e.key==="Escape"&&grDrag)grEnd(true)});
// ---- Gantt: drag to move / resize / reassign ----
const iso=ms=>new Date(ms).toISOString().slice(0,10);
function gTipShow(e,txt){let g=$("#gtip");if(!g){g=document.createElement("div");g.id="gtip";document.body.appendChild(g)}g.textContent=txt;g.style.left=(e.clientX+14)+"px";g.style.top=(e.clientY+14)+"px"}
function gTipHide(){const g=$("#gtip");if(g)g.remove()}
function gDates(t){let s=pd(t.start_date),e=pd(t.end_date);if(s==null)s=e;if(e==null)e=s;if(e<s)[s,e]=[e,s];return[s,e]}
$("#main").addEventListener("pointermove",e=>{if(gdrag||LV==="view"||!e.target.closest)return;const b=e.target.closest(".gbar");if(b){const r=b.getBoundingClientRect(),x=e.clientX-r.left;b.style.cursor=(x<7||x>r.width-7)?"ew-resize":"grab"}});
$("#main").addEventListener("pointerdown",e=>{
  if(LV==="view"||e.button>0||view!=="gantt")return;const b=e.target.closest(".gbar");if(!b)return;
  const t=tasks.find(x=>x.id==b.dataset.id);if(!t)return;const r=b.getBoundingClientRect(),x=e.clientX-r.left;
  gdrag={b,t,mode:x<7?"l":x>r.width-7?"r":"m",x0:e.clientX,y0:e.clientY,L:parseFloat(b.style.left),Wd:parseFloat(b.style.width),moved:false,dd:0,lane:null};
});
document.addEventListener("pointermove",e=>{
  const g=gdrag;if(!g)return;const dx=e.clientX-g.x0,dy=e.clientY-g.y0;
  if(!g.moved){if(Math.hypot(dx,dy)<5)return;g.moved=true;g.b.style.pointerEvents="none";g.b.style.zIndex=6;g.b.style.opacity=.85}
  let dd=Math.round(dx/gz);const [s,en]=gDates(g.t),span=Math.round((en-s)/DAYMS);
  if(g.mode==="l")dd=Math.min(dd,span);if(g.mode==="r")dd=Math.max(dd,-span);g.dd=dd;
  if(g.mode==="m"){g.b.style.left=(g.L+dd*gz)+"px";
    if(gMode!=="task"){g.b.style.transform=`translateY(${dy}px)`;const ln=(document.elementFromPoint(e.clientX,e.clientY)||document.body).closest(".glane[data-lane]");
      document.querySelectorAll(".glane.gdrop").forEach(x=>x.classList.remove("gdrop"));if(ln){ln.classList.add("gdrop");g.lane=ln.dataset.lane}}}
  else if(g.mode==="l"){g.b.style.left=(g.L+dd*gz)+"px";g.b.style.width=(g.Wd-dd*gz)+"px"}
  else g.b.style.width=(g.Wd+dd*gz)+"px";
  const ns=g.mode==="r"?s:s+dd*DAYMS,ne=g.mode==="l"?en:en+dd*DAYMS;gTipShow(e,iso(ns)+" → "+iso(ne)+(g.lane!=null&&g.lane!==String(g.t.assigned_to||"")?" · "+((users.find(u=>String(u.id)===g.lane)||{}).name||"Unassigned"):""));
});
async function gEnd(cancel){
  const g=gdrag;if(!g)return;gdrag=null;gTipHide();document.querySelectorAll(".glane.gdrop").forEach(x=>x.classList.remove("gdrop"));
  if(!g.moved)return;gSwallow=true;setTimeout(()=>{gSwallow=false},60);
  const cur=String(g.t.assigned_to||""),newLane=g.mode==="m"&&g.lane!=null?g.lane:cur;
  if(cancel||(g.dd===0&&newLane===cur)){render();return}
  const [s,en]=gDates(g.t),ns=g.mode==="r"?s:s+g.dd*DAYMS,ne=g.mode==="l"?en:en+g.dd*DAYMS;
  const d={id:g.t.id,s:iso(ns),e:iso(ne)};if(newLane!==cur)d.a=newLane?+newLane:0;
  try{await api("gantt_set",d)}catch(err){alert(err.message)}
  await load(true);
}
document.addEventListener("pointerup",()=>gEnd(false));document.addEventListener("pointercancel",()=>gEnd(true));
document.addEventListener("click",e=>{if(gSwallow){e.stopPropagation();e.preventDefault();gSwallow=false}},true);

// ---- Move selected tasks to another company ----
$("#bar").addEventListener("change",async e=>{
  if(e.target.id!=="bws"||!e.target.value)return;const to=+e.target.value,ids=[...sel],w=wss.find(x=>x.id==to);
  if(!confirm("Move "+ids.length+" task(s) to \""+(w?w.name:"")+"\"?\n\nThey leave this company. Group is cleared; status, people and matching custom columns are carried over where possible."))return load(true);
  sel.clear();try{await api("task_move",{ids,to})}catch(err){alert(err.message)}await load(true);
});

// ---- Export to Excel (CSV opens directly in Excel) ----
function csvCell(v){v=v==null?"":String(v);if(/^[=+@]/.test(v)||/^-[A-Za-z]/.test(v))v="'"+v;return /[",\r\n]/.test(v)?'"'+v.replace(/"/g,'""')+'"':v}
$("#xm").onclick=()=>{
  const list=filtered(),head=["Title","Order No.","Customer","Drawing type","Start","End","Status","Assigned to","Group","Notes",...cols.map(c=>c.name)];
  const rows=[head].concat(list.map(t=>[t.title,t.order_no,t.customer,t.drawing_type,t.start_date,t.end_date,(ST.find(x=>x[0]===t.status)||[0,t.status])[1],t.assigned_to?(names[t.assigned_to]||""):"",gname(t.group_id),t.notes,...cols.map(c=>xv(t,c))]));
  const csv="﻿"+rows.map(r=>r.map(csvCell).join(",")).join("\r\n"),cur=(wss.find(w=>w.id==W)||{}).name||"tasks";
  const a=document.createElement("a");a.href=URL.createObjectURL(new Blob([csv],{type:"text/csv;charset=utf-8"}));a.download=cur.replace(/[^\w\-]+/g,"_")+"_"+today()+".csv";document.body.appendChild(a);a.click();setTimeout(()=>{URL.revokeObjectURL(a.href);a.remove()},500);
  $("#smenu").hidden=true;
};
let dragId=null;
$("#main").addEventListener("dragstart",e=>{const c=e.target.closest(".card");if(c&&LV!=="view")dragId=c.dataset.id});
$("#main").addEventListener("dragover",e=>{const c=e.target.closest(".col");if(c){e.preventDefault();c.classList.add("over")}});
$("#main").addEventListener("dragleave",e=>{const c=e.target.closest(".col");if(c)c.classList.remove("over")});
$("#main").addEventListener("drop",e=>{
  const c=e.target.closest(".col");if(!c||!dragId)return;e.preventDefault();c.classList.remove("over");
  act("field",{id:dragId,f:"status",v:c.dataset.s});dragId=null;
});

const BUNDLED_LOGOS={"allshelter":"data:image/png;base64,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","allshelter usa":"data:image/png;base64,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"};
function wlogo(w){
  const bl=BUNDLED_LOGOS[String(w.name||"").trim().toLowerCase()];if(bl)return `<img src="${bl}" alt="${ea(w.name)}">`;
  if(+w.hl)return `<img src="logo.php?id=${w.id}&v=${w.lv}" alt="${ea(w.name)}">`;
  let h=0;for(const ch of w.name)h=(h*31+ch.charCodeAt(0))%360;
  const ini=w.name.trim().split(/\s+/).slice(0,2).map(x=>x[0]||"").join("").toUpperCase();
  return `<span class="ini" style="background:hsl(${h} 55% 42%)">${esc(ini)}</span>`;
}
function fileToLogo(file){return new Promise((res,rej)=>{const img=new Image(),u=URL.createObjectURL(file);
  img.onload=()=>{const m=160,r=Math.min(1,m/Math.max(img.width,img.height)),w=Math.max(1,Math.round(img.width*r)),h=Math.max(1,Math.round(img.height*r));
    const c=document.createElement("canvas");c.width=w;c.height=h;c.getContext("2d").drawImage(img,0,0,w,h);URL.revokeObjectURL(u);res(c.toDataURL("image/png"))};
  img.onerror=()=>{URL.revokeObjectURL(u);rej(new Error("That file is not a readable image"))};img.src=u})}
let logoFor=null;
function renderWs(){
  const LVN={view:"View only",edit:"Can edit",full:"Edit + delete"};
  const members=allUsers.filter(u=>u.role!=="admin");
  $("#wlist").innerHTML=wss.map(w=>`<div class="wrow"><div class="acts"><span class="wl" style="width:44px;height:44px;flex:none;padding:2px;pointer-events:none">${wlogo(w)}</span><span style="flex:1"><b>${esc(w.name)}</b>${w.id==W?' <small style="color:var(--mut)">(current)</small>':""}</span><button data-cod="${w.id}" title="Company details and hourly rates">💲 Details &amp; rates</button><button data-wr="${w.id}">Rename</button><button data-wlg="${w.id}">Logo</button>${+w.hl?`<button data-wlx="${w.id}" title="Remove logo">✕</button>`:""}<button data-wacc="${w.id}">Access</button><button data-wd="${w.id}">Delete</button></div>
    <div class="wp" ${openWp==w.id?"":"hidden"}><small style="color:var(--mut)">Admins always see every company. Tick the members who may open this one and choose what they can do:</small>
    ${members.map(u=>`<label><input type="checkbox" data-wu="${u.id}"${(access[w.id]||{})[u.id]?" checked":""}> <span style="flex:1">${esc(u.name)}</span><select data-wl="${u.id}"${u.role==="guest"?" disabled":""}>${Object.entries(LVN).map(([k,n])=>`<option value="${k}"${(u.role==="guest"?"view":((access[w.id]||{})[u.id]||"edit"))===k?" selected":""}>${n}</option>`).join("")}</select></label>`).join("")||'<div class="empty">No members yet</div>'}
    <div class="acts"><span class="sp"></span><button class="p" data-wsave="${w.id}">Save access</button></div></div></div>`).join("");
}
$("#wm").onclick=()=>{renderWs();$("#wdlg").showModal()};
function menuFit(m,r){m.style.top="auto";m.style.maxHeight=(innerHeight-16)+"px";const h=m.offsetHeight,bot=Math.max(8,innerHeight-r.bottom-4);
  if(innerHeight-bot-h<8){m.style.bottom="auto";m.style.top=Math.max(8,Math.min(r.bottom-h,innerHeight-h-8))+"px"}}
$("#sbtn").onclick=e=>{e.stopPropagation();const m=$("#smenu");$("#umenu").hidden=true;if(!m.hidden){m.hidden=true;return}m.hidden=false;const r=$("#sbtn").getBoundingClientRect();if(innerWidth<=700){m.style.left=Math.max(8,Math.min(r.left,innerWidth-m.offsetWidth-8))+"px";m.style.bottom=(innerHeight-r.top+8)+"px"}else{m.style.left=(r.right+8)+"px";m.style.bottom=Math.max(8,window.innerHeight-r.bottom)+"px";menuFit(m,r)}};
$("#ubtn").onclick=e=>{e.stopPropagation();const m=$("#umenu");$("#smenu").hidden=true;if(!m.hidden){m.hidden=true;return}m.hidden=false;$("#cpanel").classList.remove("open");const r=$("#ubtn").getBoundingClientRect();m.style.top="auto";m.style.right="auto";if(innerWidth<=700){m.style.left=Math.max(8,Math.min(r.right-m.offsetWidth,innerWidth-m.offsetWidth-8))+"px";m.style.bottom=(innerHeight-r.top+8)+"px"}else{m.style.left=(r.right+10)+"px";m.style.bottom=Math.max(8,window.innerHeight-r.bottom-4)+"px";menuFit(m,r)}};
$("#smenu").addEventListener("click",()=>{$("#smenu").hidden=true});
document.addEventListener("click",e=>{if(!e.target.closest("#setw"))$("#smenu").hidden=true;if(!e.target.closest("#uw"))$("#umenu").hidden=true;if(!e.target.closest("#cpanel,#rco"))cpClose()});
async function apOpen(){const l=$("#apl");l.innerHTML='<div class="empty">Loading…</div>';$("#apdlg").showModal();
  try{const r=await api("dr_list"),kn={group:"Group",status:"Status",column:"Column",dtype:"Drawing type"};
    l.innerHTML=r.items.length?r.items.map(i=>`<div class="apr"><div><b>${esc(kn[i.kind]||i.kind)}: ${esc(i.target_name)}</b><br><small>${esc(i.ws_name)} · asked by ${esc(i.by_name)} · ${esc(String(i.created_at).slice(0,16))}</small></div><div class="acts"><button class="p" data-ap="${i.id}" data-act="approve">Approve delete</button><button data-ap="${i.id}" data-act="reject">Keep it</button></div></div>`).join(""):'<div class="empty">Nothing waiting for approval.</div>'}catch(e){l.innerHTML='<div class="empty">'+esc(e.message)+'</div>'}}
$("#apm").onclick=apOpen;$("#apx").onclick=()=>$("#apdlg").close();
$("#apl").addEventListener("click",async e=>{const b=e.target.closest("[data-ap]");if(!b)return;b.disabled=true;try{await api("dr_act",{id:b.dataset.ap,act:b.dataset.act});await load(true);apOpen()}catch(err){alert(err.message);b.disabled=false}});
function delNote(r){if(r&&r.pending)alert("Sent to an admin for approval. It will be removed once an admin approves it.")}
let instEv=null;
window.addEventListener("beforeinstallprompt",e=>{e.preventDefault();instEv=e;$("#ainst").hidden=false});
window.addEventListener("appinstalled",()=>{instEv=null;$("#ainst").hidden=true});
$("#ainst").onclick=async e=>{e.preventDefault();$("#umenu").hidden=true;if(instEv){instEv.prompt();try{await instEv.userChoice}catch(x){}instEv=null;$("#ainst").hidden=true}};
if("serviceWorker" in navigator)addEventListener("load",()=>navigator.serviceWorker.register("sw.js").catch(()=>{}));
if(new URLSearchParams(location.search).get("chat")==="1")addEventListener("load",()=>setTimeout(()=>{const b=$("#mgb");if(b&&$("#msgw").hidden)b.click()},1200));
function rhSet(v){document.body.classList.toggle("rh",v);document.body.classList.remove("ro");try{localStorage.setItem("tf_rh",v?"1":"")}catch(e){}cpClose();$("#umenu").hidden=true;$("#smenu").hidden=true}
function rhChk(){const b=document.body;if(!b.classList.contains("ro"))return;if($("#side").matches(":hover")||$("#cpanel").matches(":hover")||$("#cpanel").classList.contains("open")||!$("#umenu").hidden||!$("#smenu").hidden)return;b.classList.remove("ro")}
try{if(localStorage.getItem("tf_rh")==="1"&&!document.documentElement.classList.contains("lk-d"))document.body.classList.add("rh")}catch(e){}
$("#rco").addEventListener("click",()=>{if(document.body.classList.contains("rh"))document.body.classList.add("ro")});
function stPos(){const h=document.querySelector("header"),t=$("#sidetabs");if(!h||!t)return;const y=Math.max(58,Math.round(h.getBoundingClientRect().bottom+14));t.style.top=y+"px";document.documentElement.style.setProperty("--tt",y+"px");document.documentElement.style.setProperty("--hb",Math.round(h.getBoundingClientRect().bottom)+"px");document.documentElement.style.setProperty("--dk",(y-2)+"px")}
(()=>{const g=document.querySelector("#side .dkg");if(!g)return;const R=document.documentElement,sd=$("#side");
 const clamp=(x,y)=>{const w=sd.offsetWidth||58,h=sd.offsetHeight||300;return[Math.max(4,Math.min(innerWidth-w-4,x)),Math.max(4,Math.min(innerHeight-h-4,y))]};
 const put=(x,y)=>{R.style.setProperty("--dkx",x+"px");R.style.setProperty("--dky",y+"px")};
 const load=()=>{try{const v=JSON.parse(localStorage.getItem("tf_dk")||"null");if(v&&R.classList.contains("lk-d")){const c=clamp(12,v.y);put(12,c[1])}}catch(e){}};
 let d=null;
 g.addEventListener("pointerdown",e=>{const r=sd.getBoundingClientRect();d={ox:e.clientX-r.left,oy:e.clientY-r.top};try{g.setPointerCapture(e.pointerId)}catch(x){}e.preventDefault()});
 g.addEventListener("pointermove",e=>{if(!d)return;const c=clamp(12,e.clientY-d.oy);put(12,c[1])});
 const end=()=>{if(!d)return;d=null;const r=sd.getBoundingClientRect();try{localStorage.setItem("tf_dk",JSON.stringify({x:Math.round(r.left),y:Math.round(r.top)}))}catch(e){}};
 g.addEventListener("pointerup",end);g.addEventListener("pointercancel",end);
 g.addEventListener("dblclick",()=>{R.style.removeProperty("--dkx");R.style.removeProperty("--dky");try{localStorage.removeItem("tf_dk")}catch(e){}});
 addEventListener("resize",load);load()})();
window.lsBusy=false;
(()=>{let on=false;const D=".3s",E="cubic-bezier(.4,0,.2,1)",PR=["height","marginTop","marginBottom","paddingTop","paddingBottom"],
 run=(lw,collapse)=>{window.lsBusy=true;const b=document.body,B=[$("#gtabs"),$("#strow")].filter(x=>x&&!x.hidden),S=B.map(()=>({}));
  if(!collapse)b.classList.remove("lsc");
  B.forEach((x,i)=>{const c=getComputedStyle(x);PR.forEach(p=>S[i][p]=c[p])});
  const zero={height:"0px",marginTop:"0px",marginBottom:"0px",paddingTop:"0px",paddingBottom:"0px"};
  B.forEach((x,i)=>{x.style.transition="none";x.style.overflow="hidden";x.style.boxSizing="border-box";Object.assign(x.style,collapse?S[i]:zero);x.style.opacity=collapse?"1":"0"});
  void document.body.offsetHeight;
  B.forEach(x=>x.style.transition="height "+D+" "+E+",margin "+D+" "+E+",padding "+D+" "+E+",opacity .22s ease");
  B.forEach((x,i)=>{Object.assign(x.style,collapse?zero:S[i]);x.style.opacity=collapse?"0":"1"});
  setTimeout(()=>{B.forEach(x=>{["transition","overflow","boxSizing","height","marginTop","marginBottom","paddingTop","paddingBottom","opacity"].forEach(p=>x.style[p]="")});if(collapse)b.classList.add("lsc");window.lsBusy=false},340)};
 addEventListener("scroll",e=>{const lw=e.target;if(window.lsBusy||!lw||!lw.classList||!lw.classList.contains("listwrap")||innerWidth<=700)return;const y=lw.scrollTop,room=lw.scrollHeight-lw.clientHeight;
  if(!on&&y>40&&room>260){on=true;run(lw,true)}else if(on&&y<4){on=false;run(lw,false)}},true)})();
(()=>{try{const C=window.Capacitor;if(!C||!C.isNativePlatform||!C.isNativePlatform())return;const PN=C.Plugins&&C.Plugins.PushNotifications;if(!PN)return;
 PN.addListener("registration",t=>{try{api("push_reg",{token:t.value}).catch(()=>{})}catch(e){}});
 PN.addListener("pushNotificationActionPerformed",()=>{try{const b=$("#mgb");if(b&&$("#msgw").hidden)b.click()}catch(e){}});
 setTimeout(()=>{PN.checkPermissions().then(r=>r.receive==="granted"?r:PN.requestPermissions()).then(r=>{if(r.receive==="granted")PN.register()}).catch(()=>{})},2500)}catch(e){}})();
(()=>{
const hd=document.getElementById("hpdlg");if(!hd)return;
const T=(a,b)=>`<div class="hpt hpt-${a}"><i>${a==="tip"?"💡":a==="warn"?"⚠️":"ℹ️"}</i><div>${b}</div></div>`;
const N=(n,d)=>`<div class="hpn"><b>${n}</b><span>${d}</span></div>`;
const PINS=[
 [1,"Side bar","The slim bar at the far left (the <b>floating dock</b> when you use the Floating layout). Holds Home, Companies, Settings and, in the floating layout, Tools and Chat.",2.6,60],
 [2,"Header","The top bar: logo, company name, view switch, search, filters, who is online, bell and New task. It shrinks to a slim bar when you scroll.",15,11.2],
 [3,"View switch","<b>List</b>, <b>Board</b> or <b>Gantt</b>. The star button makes the current one your <b>Default view</b>.",33,11.2],
 [4,"Search &amp; filters","Search tasks, then narrow by All statuses, All customers and Everyone (a person).",52,11.2],
 [5,"Presence bubbles","Coloured initials of teammates who are online right now, stacked like Monday. A green dot says <b>Live</b>.",73,11.2],
 [6,"Bell","Messages and notifications for you, with a count. Click it to read them.",79.3,11.2],
 [7,"+ New task","Starts a new task row at the top of your list.",88,11.2],
 [8,"Account menu","Your initials (top right). My hours check, Notifications, Install app, Help, Log out.",95.6,11.2],
 [9,"Group tabs","One tab per task group (All, Operations, Sales…) with a count. Click to look at one group only.",57,17.4],
 [10,"Stat boxes","All tasks, Ongoing, In Review, Ready to Start, Overdue… Click one to filter the list. <b>Collapse</b> closes or opens every group.",73,25.6],
 [11,"Selection bar","Appears when you tick tasks: Duplicate, Move to group, Move to company, Delete, Clear. It stays in view while you scroll.",60,34.4],
 [12,"Column headers","Title, Order No., Customer… Drag a header to reorder, drag its edge to resize. They stay pinned while you scroll.",96.4,42.6],
 [13,"Group header","A coloured bar for each group, with <b>+ Task</b>. Groups can hold Sub-groups and a Completed folder.",33,49.2],
 [14,"Task row","One task. Click any cell to edit it; drag the ⋮⋮ handle to move it.",95,56],
 [15,"Status pill","Coloured status (To Do, Ongoing, In Review, Completed…). Click it to change.",67,56],
 [16,"Tools &amp; Chat","The two tabs on the right edge (or in the dock): Tools (containers, converter, Wiki) and Chat (messages).",97.6,49.8],
];
const MAP=`<div class="mk"><div class="mk-s"><i></i><i></i><i></i><u></u></div><div class="mk-h"><span class="lg"></span><span class="ti">Allshelter</span><span class="vs"><b>List</b><b>Board</b><b>Gantt</b></span><span class="se">Search tasks</span><span class="pb"><i style="background:#e11d48"></i><i style="background:#0ea5e9"></i><i style="background:#16a34a"></i><i style="background:#f59e0b"></i></span><span class="be"></span><span class="nt">+ New task</span><span class="ac"></span></div>
<div class="mk-g"><b class="on">All 13</b><b>Operations 10</b><b>Sales 1</b><b>Production 0</b></div>
<div class="mk-st"><b style="--c:#6366f1">13 All tasks</b><b style="--c:#16a34a">5 Ongoing</b><b style="--c:#f59e0b">0 In Review</b><b style="--c:#0891b2">1 Ready</b><b style="--c:#dc2626">0 Overdue</b></div>
<div class="mk-sel"><b>1 selected</b><span>Duplicate</span><span>Move to group…</span><span>Delete</span></div>
<div class="mk-th"><span>Title</span><span>Order No.</span><span>Customer</span><span>Status</span><span>Assigned</span><span>Hours</span></div>
<div class="mk-gr"><b>▾ Operations</b><span>+ Task</span></div>
<div class="mk-r"><span>SCAS1724ADSIM</span><span>S13729</span><span>Bechtel</span><em style="--c:#9ca3af">ON HOLD</em><u></u><span>Log</span></div>
<div class="mk-r"><span>SCAL3024AOM-PE</span><span>S13729</span><span>Bechtel</span><em style="--c:#22c55e">ONGOING</em><u></u><span>Log</span></div>
<div class="mk-r"><span>SS10AIMFD+RD</span><span>S13823</span><span>Talison</span><em style="--c:#0891b2">READY</em><u></u><span>Log</span></div>
<div class="mk-dk"><i>📐</i><i>💬</i></div>
${PINS.map(p=>`<button type="button" class="pin" data-pin="${p[0]}" style="left:${p[3]}%;top:${p[4]}%">${p[0]}</button>`).join("")}</div>`;
const LEG=`<div class="hpleg">${PINS.map(p=>`<div class="lg" data-pin="${p[0]}"><b>${p[0]}</b><div><h4>${p[1]}</h4><p>${p[2]}</p></div></div>`).join("")}</div>`;
const S=[
{id:"start",ic:"🧭",t:"Start here: the map",tags:"names parts screen layout glossary anatomy",h:()=>`<p class="lead">This is the TaskFlow screen with the name we use for each part. Click a number on the picture, or a name below, to find it.</p>${MAP}${LEG}${T("tip","When someone says <b>“open the Selection bar”</b> or <b>“check the Stat boxes”</b> they mean parts 11 and 10 above. Using the same names makes support faster.")}`},
{id:"names",ic:"🏷",t:"What we call things",tags:"glossary names terms words company group status",h:()=>`<p class="lead">A short dictionary of the words used across TaskFlow.</p><div class="hpg">
${N("Company","One workspace, for example Allshelter or Allshelter USA. Each has its own tasks, groups, statuses and columns. You only see the companies you have been given.")}
${N("Home","Your starting page: hours reminder, Companies, messages from yesterday and My tasks.")}
${N("Group","A section of tasks inside a company (Operations, Sales…). Groups can contain <b>Sub-groups</b>.")}
${N("Completed folder","The folder at the end of a group where finished tasks go. Open it with Expand.")}
${N("Task","One piece of work (a drawing job). It has an Order No., Customer, Drawing type, dates, Status, people, Hours and Notes.")}
${N("Status","Where a task is: To Do, In Progress (Ongoing), In Review, Ready to Start, Completed and so on. Admins can edit the list.")}
${N("Drawing type","The kind of drawing, picked from a list that admins keep (FDS, Shop, Presentation…).")}
${N("Column","A field on the task list. Admins can add custom columns (like Rev); linked companies share columns.")}
${N("Assignee &amp; co-assignee","The main person on a task, plus the <b>+1</b> chip for extra people who help.")}
${N("Hours / Log","Time logged against a task. Click <b>Log</b> to add. Regular and Overtime are worked out automatically.")}
${N("Instruction","A message on a task that asks one person to do something. It is highlighted gold for them. A plain note is a Comment.")}
${N("Overlap ⚠","A red warning when the same person has two open tasks on overlapping dates.")}
${N("Approvals","Admin queue. Members ask here before deleting a group, status, column or drawing type.")}
${N("Wiki","Our drawing rules, standards and checklists, searchable and tagged.")}
${N("Floating dock","A layout option: a slim bar that floats at the left with Tools and Chat inside it.")}
${N("Presence bubbles","The coloured initials at top right showing who is online.")}
${N("Selection bar","The bar that appears when you tick tasks.")}</div>`},
{id:"nav",ic:"🏠",t:"Getting around",tags:"home companies side bar tabs pin desktop navigate",h:()=>`<h3>Home</h3><p>Press the house button or the logo. Home has two sides: on the left the greeting card with your stats, an hours reminder and your <b>Companies</b>; on the right the <b>messages</b> from yesterday and <b>My tasks</b> in buckets: Late, Today, Going on, Soon, Later and No dates. Click a task to jump straight to it in its company.</p>
<h3>Companies</h3><p>The briefcase button opens the Companies panel with a search box. Pick one to switch. Home also shows each company as a tile with open and overdue counts.</p>
<h3>Side bar</h3><p>Home, Companies and Settings sit here. In the <b>Layout</b> options (Appearance) you can choose Classic, Refined Navy, Light or Floating dock. The arrow at the bottom hides or pins the bar.</p>
<h3>Desktop app: ribbon and tabs</h3><p>The thin <b>Ribbon</b> at the top has File, View, Tools and Help. Under it each <b>tab</b> is a company or Home. Use <b>+</b> or File → Open company in new tab for another company. Right-click a tab to <b>Pin</b> it; pinned tabs come back next time and cannot be closed by accident.</p>${T("tip","Keyboard on the desktop app: Ctrl+T new tab, Ctrl+W close tab, Ctrl+Tab next tab, Ctrl+R reload, F11 full screen, Ctrl+= / Ctrl+- zoom.")}`},
{id:"tasks",ic:"📋",t:"Working with tasks",tags:"task create edit group subgroup drag duplicate move delete select columns",h:()=>`<h3>Create</h3><p>Press <b>+ New task</b> in the Header, or <b>+ New task</b> at the bottom of a group. Type the title and press <b>Enter</b>. <b>Esc</b> cancels a new row. <b>+ Sub-group</b> and <b>+ Group</b> sit beside it.</p>
<h3>Edit</h3><p>Click any cell to change it: Order No., Customer, Drawing type, Start / End dates, Status, Assigned to, Notes, Deadline and your custom columns. Changes save by themselves.</p>
<h3>Move and organise</h3><p>Drag the <b>⋮⋮</b> handle to reorder a task or drop it in another group. Tick tasks to use the <b>Selection bar</b>: <b>Duplicate</b>, <b>Move to group</b>, <b>Move to company</b>, <b>Delete</b>, <b>Clear</b>. Groups can be dragged too, and <b>Collapse / Expand</b> opens or closes them all.</p>
<h3>Messages on a task</h3><p>The speech-bubble at the start of a row opens that task's messages. Write a <b>Comment</b>, or choose <b>Instruction</b> and pick the person it is for. They are notified at once.</p>
<h3>Settings menu</h3><p>The gear in the side bar holds <b>Groups</b>, <b>Statuses</b>, <b>Appearance</b>, <b>Column</b>, <b>Drawing types</b>, <b>Export to Excel</b> and, for admins, Users, Overlap rules and Approvals.</p>${T("warn","Delete cannot be undone. TaskFlow asks you to confirm. Members who want to delete a group, status, column or drawing type send a request to the admins.")}`},
{id:"views",ic:"🗂",t:"List, Board and Gantt",tags:"views list board gantt kanban timeline default view",h:()=>`<div class="hpg3"><div class="hpc"><h4>📄 List</h4><p>The spreadsheet view. Best for editing many tasks quickly. Column headers stay pinned while you scroll, and the tabs and Stat boxes slide away to give you room.</p></div>
<div class="hpc"><h4>🗃 Board</h4><p>One column per status. Each task is a card; drag it to another column to change its status.</p></div>
<div class="hpc"><h4>📅 Gantt</h4><p>A timeline with one lane per person (switch to one lane per task). Bars are coloured by status; a red outline means an overlap. Drag a bar to reschedule or hand it to another person. Tasks without dates are not drawn.</p></div></div>
<p>Click <b>☆ Make … my default</b> in the Header to choose which view opens first. The search and filters apply to all three.</p>`},
{id:"hours",ic:"⏱",t:"Hours, overtime and reminders",tags:"hours log overtime ot my hours check reminder 8 working week rev",h:()=>`<h3>Log hours</h3><p>Click <b>Log</b> in the Hours column of a task, choose the date and hours, add a note, save. You can add a <b>Rev</b> if your company tracks revisions.</p>
<h3>Automatic overtime</h3><p>The working week is <b>Monday to Friday, 8 hours a day</b>. Anything on Saturday or Sunday, and any hours beyond 8 on a weekday, is <b>Overtime</b>. You do not choose it; TaskFlow splits it for you.</p>
<h3>My hours check</h3><p>Account menu → <b>⏱ My hours check</b>. Pick a date range (Today, This week, Last week, Last 14 / 30 days), tick the days, and see what you logged, what was <b>assigned and ongoing</b> that day, and whether you are short. <b>+ Add hours</b> fixes a day. Tick <b>Missed days only</b> to see only gaps. Drag the edge of a column heading to resize. Admins can look at anyone.</p>
<h3>Reminders</h3><p>On weekdays at about <b>8am</b> and <b>3pm</b> TaskFlow reminds you to log hours. If you have tasks but none is In Progress, it also asks you to set one. If nobody has assigned you any task, the admins are told.</p>${T("tip","Home shows a reminder card when your last working day or the last 15 days are not fully logged.")}`},
{id:"chat",ic:"💬",t:"Chat, notifications and phone alerts",tags:"chat messages team notifications bell push phone instruction",h:()=>`<h3>Chat</h3><p>Open <b>Chat</b> (💬 on the dock or the edge) for <b>Team chat</b> and one-to-one messages. Press <b>Enter</b> to send, <b>Shift+Enter</b> for a new line. There is an emoji picker.</p>
<h3>Bell and notifications</h3><p>The <b>Bell</b> shows what is new for you. Messages, task assignments, comments and instructions also pop up while you work. <b>Account menu → Notifications</b> controls the pop-ups on this device.</p>
<h3>On your phone</h3><p>Install the Android app (or use <b>Account menu → Install app</b> on a phone browser). After you allow notifications you get a phone alert for messages, tasks given to you, comments, instructions and hours reminders, even when the app is closed.</p>`},
{id:"wiki",ic:"📚",t:"Wiki and Tools",tags:"wiki tools containers converter calculator rules standards search tags",h:()=>`<h3>Wiki</h3><p>Open <b>Tools → Wiki</b>. Pages are grouped into <b>categories</b> (Steel &amp; Materials, Fasteners &amp; Footings, Covers &amp; Fabric…) and carry <b>tags</b>. The search box looks in titles, tags and text, and highlights what it found. Click a picture to zoom. Editors can add pages and upload images with <b>+ New page</b>. Guests can read only.</p>
<h3>Containers</h3><p>Pick a container size, how many, the gap between them and any extra length; TaskFlow gives the total length. Example: 2 × 20 ft + 76 mm = one 40 ft.</p>
<h3>Convert</h3><p>A quick unit converter for lengths and other common units.</p>`},
{id:"admin",ic:"🛠",t:"For admins",admin:1,tags:"admin users access companies manage invoice backup usage log approvals overlap statuses column sync appearance",h:()=>`<div class="hpg3"><div class="hpc"><h4>👥 Users</h4><p>Settings → <b>Users Management</b>: add people, set <b>Member</b>, <b>Guest (view only)</b> or <b>Admin</b>, reset passwords. Give each person access to their companies.</p></div>
<div class="hpc"><h4>🗂 Manage companies</h4><p>Add or rename companies, set logos and details &amp; rates. Companies in the same <b>column group</b> (Allshelter and Allshelter USA) keep the same columns: add or rename in one and it appears in the other.</p></div>
<div class="hpc"><h4>✅ Approvals</h4><p>Review delete requests from members and approve or reject. They are told the result.</p></div>
<div class="hpc"><h4>⚠ Overlap rules</h4><p>Choose whether a hand-over day or same-day tasks count as overlaps.</p></div>
<div class="hpc"><h4>🧾 Worklog &amp; invoice</h4><p>Pick a period, choose a profile and print or save the work logs and invoice. <b>Invoice tracking</b> follows pending and paid invoices.</p></div>
<div class="hpc"><h4>🧾 Usage log</h4><p>Who signed in, what they changed, from where. Filter by person, company and dates.</p></div>
<div class="hpc"><h4>💾 Backups</h4><p>Database copies every 3 hours, each emailed to you. Back up now, download copies, and the cron command for the hours reminders live here.</p></div>
<div class="hpc"><h4>🎨 Appearance</h4><p>Bar colours, <b>Layout</b> (Classic, Refined Navy, Light, Floating dock) and the Glass look. Layout is per person.</p></div></div>`},
{id:"apps",ic:"🖥",t:"Desktop and phone apps",tags:"desktop windows android install app phone pwa sign in remember password",h:()=>`<p>TaskFlow also runs as an app. Download the files from the GitHub <b>Actions → Build apps</b> run.</p>
<h3>Windows desktop app</h3><p>Ribbon and tabs, one tab per company, pinned tabs, tray icon and optional start with Windows. It remembers your sign-in and fills your ID and password for you (kept encrypted by Windows).</p>
<h3>Android app</h3><p>Opens TaskFlow full screen and sends push notifications.</p>
<h3>Install from the browser</h3><p>Account menu → <b>📲 Install app</b> on Chrome or Edge, or Add to Home screen on a phone.</p>`},
{id:"faq",ic:"🆘",t:"Questions and fixes",tags:"faq help problem fix not working sign in notification delete hours",h:()=>`<div class="hpq"><b>I cannot delete a group, status or column.</b><p>Members send a request. An admin approves it in Approvals.</p><b>My hours show Short but I logged time.</b><p>Regular hours count to 8 per weekday. Check the date and that the Log was saved on the day you expect.</p><b>I did not get a reminder or phone alert.</b><p>Allow notifications in the app, open it once after installing, and ask an admin to check Backups → Send test phone notification.</p><b>The app asks me to sign in every time.</b><p>Tick <b>Keep me signed in on this device</b> when you sign in. In the desktop app, update to the latest version.</p><b>A task has a red ⚠.</b><p>The same person has another open task on overlapping dates. Change a date or the person.</p><b>I cannot see a company.</b><p>Ask an admin to give you access to it.</p><b>The screen looks stuck.</b><p>Reload with Ctrl+R (desktop) or pull to refresh (phone).</p></div>`}
];
let cur="start";
const side=document.getElementById("hpside"),main=document.getElementById("hpmain"),q=document.getElementById("hpq");
const vis=()=>S.filter(s=>!s.admin||(typeof me!=="undefined"&&me&&me.role==="admin"));
const text=h=>{const d=document.createElement("div");d.innerHTML=h.replace(/<\/(b|h1|h3|h4|p|div|span|i)>/g,"$& ");return d.textContent.replace(/\s+/g," ")};
function nav(){side.innerHTML=vis().map(s=>`<button type="button" data-s="${s.id}" class="${s.id===cur?"on":""}"><i>${s.ic}</i><span>${s.t}</span></button>`).join("")}
function show(id){cur=id;const s=vis().find(x=>x.id===id)||vis()[0];nav();main.innerHTML=`<article class="hpa"><div class="hpk">${s.ic}</div><h1>${s.t}</h1>${s.h()}</article>`;main.scrollTop=0;hd.classList.remove("nav")}
function find(v){const w=v.toLowerCase().split(/\s+/).filter(Boolean);const r=[];
 for(const s of vis()){const tx=text(s.h()),all=(s.t+" "+s.tags+" "+tx).toLowerCase();if(!w.every(x=>all.includes(x)))continue;const i=tx.toLowerCase().indexOf(w[0]);const sn=i<0?tx.slice(0,140):(i>50?"… ":"")+tx.slice(Math.max(0,i-50),i+110)+" …";r.push([s,sn])}
 main.innerHTML=`<article class="hpa"><h1>${r.length?r.length+" result"+(r.length>1?"s":""):"Nothing found"}</h1>${r.map(([s,sn])=>`<button type="button" class="hpres" data-s="${s.id}"><b>${s.ic} ${s.t}</b><span>${sn.replace(/[<>&]/g,c=>({"<":"&lt;",">":"&gt;","&":"&amp;"}[c])).replace(new RegExp("("+w.map(x=>x.replace(/[.*+?^${}()|[\]\\]/g,"\\$&")).join("|")+")","ig"),"<mark>$1</mark>")}</span></button>`).join("")||"<p>Try a shorter word, like <i>hours</i>, <i>group</i> or <i>chat</i>.</p>"}</article>`}
window.tfHelp=(id)=>{if(!hd.open)hd.showModal();q.value="";show(id||cur);try{q.blur()}catch(e){}};
document.getElementById("hpx").onclick=()=>hd.close();
document.getElementById("hpbr").onclick=()=>hd.classList.toggle("nav");
q.addEventListener("input",()=>{const v=q.value.trim();if(!v)show(cur);else find(v)});
hd.addEventListener("click",e=>{const b=e.target.closest("[data-s]");if(b){q.value="";show(b.dataset.s);return}
 const p=e.target.closest("[data-pin]");if(p){const n=p.dataset.pin;hd.querySelectorAll(".pin.hot,.hpleg .lg.hot").forEach(x=>x.classList.remove("hot"));hd.querySelectorAll('[data-pin="'+n+'"]').forEach(x=>x.classList.add("hot"));const l=hd.querySelector('.hpleg .lg[data-pin="'+n+'"]');if(l&&p.classList.contains("pin"))l.scrollIntoView({block:"nearest",behavior:"smooth"})}});
const m=document.getElementById("hpm");if(m&&window.tfCred)m.hidden=true;if(m)m.addEventListener("click",e=>{e.preventDefault();const u=document.getElementById("umenu");if(u)u.hidden=true;window.tfHelp()});
document.addEventListener("keydown",e=>{if(e.key==="F1"){e.preventDefault();window.tfHelp()}});
if(new URLSearchParams(location.search).get("help"))addEventListener("load",()=>setTimeout(()=>window.tfHelp(),900));
})();

let stR=0;const stQ=()=>{cancelAnimationFrame(stR);stR=requestAnimationFrame(stPos)};
addEventListener("scroll",stQ,{passive:true});addEventListener("resize",stQ);if(window.ResizeObserver)new ResizeObserver(stQ).observe(document.querySelector("header"));setTimeout(stPos,50);setTimeout(stPos,400);addEventListener("load",stQ);document.querySelector("header").addEventListener("transitionend",stQ);if(document.fonts&&document.fonts.ready)document.fonts.ready.then(stQ);setInterval(stPos,1500);
$("#rhide").onclick=e=>{e.stopPropagation();rhSet(!document.body.classList.contains("rh"))};
let reT=0;$("#side").addEventListener("mouseenter",()=>{const b=document.body;if(!b.classList.contains("rh")||b.classList.contains("ro"))return;clearTimeout(reT);reT=setTimeout(()=>b.classList.add("ro"),380)});$("#side").addEventListener("mouseleave",()=>clearTimeout(reT));
document.querySelector("#side .rpill").addEventListener("mouseenter",()=>{const b=document.body;if(!b.classList.contains("rh")||b.classList.contains("ro"))return;clearTimeout(reT);reT=setTimeout(()=>b.classList.add("ro"),90)});
for(const id of ["side","cpanel"])$("#"+id).addEventListener("mouseleave",()=>setTimeout(rhChk,450));
document.addEventListener("click",()=>setTimeout(rhChk,80));let rmT=0;document.addEventListener("mousemove",()=>{if(document.body.classList.contains("ro")){clearTimeout(rmT);rmT=setTimeout(rhChk,700)}});
function cpClose(){$("#cpanel").classList.remove("open");$("#rco").classList.remove("on")}
$("#rco").onclick=e=>{e.stopPropagation();const o=!$("#cpanel").classList.contains("open");$("#smenu").hidden=true;$("#umenu").hidden=true;$("#cpanel").classList.toggle("open",o);$("#rco").classList.toggle("on",o);if(o){$("#cps").value="";fillCp();setTimeout(()=>$("#cps").focus(),60)}};
$("#cpx").onclick=cpClose;$("#rchat").onclick=()=>$("#mgb").click();$("#rtools").onclick=()=>$("#tbtn").click();$("#mfb").onclick=()=>document.body.classList.toggle("mf");
$("#cps").addEventListener("input",fillCp);
$("#amm").onclick=e=>{e.preventDefault();$("#umenu").hidden=true;$("#wm").click()};
function fillCp(){const q=($("#cps").value||"").trim().toLowerCase();$("#cpl").innerHTML=(wss||[]).filter(w=>!q||String(w.name).toLowerCase().includes(q)).map(w=>`<a class="cr${w.id==W&&!HOME?" on":""}" href="index.php?w=${w.id}"><span class="wl">${wlogo(w)}</span><span class="nm">${esc(w.name)}</span></a>`).join("")||'<div class="empty" style="padding:12px">No companies</div>'}
document.addEventListener("keydown",e=>{if(e.key==="Escape"){cpClose();$("#smenu").hidden=true;$("#umenu").hidden=true}});
$("#wf").addEventListener("change",async e=>{const f=e.target.files[0];if(!f||!logoFor)return;
  try{const data=await fileToLogo(f);await api("ws_logo",{id:logoFor,data});await load(true);renderWs()}catch(err){alert(err.message)}logoFor=null});
$("#wc").onclick=()=>$("#wdlg").close();
$("#wa").onclick=async()=>{try{const r=await api("ws_add",{name:$("#wn").value});location="index.php?w="+r.id}catch(e){alert(e.message)}};
$("#wlist").addEventListener("click",async e=>{
  const d=e.target.dataset;try{
    if(d.cod){await coOpen(d.cod);return}
    if(d.wr){const w=wss.find(x=>x.id==d.wr),n=await ask("New name for this company:",w.name);if(n&&n.trim()){await api("ws_rename",{id:d.wr,name:n});await load(true);renderWs()}}
    if(d.wlg){logoFor=d.wlg;$("#wf").value="";$("#wf").click()}
    if(d.wlx){await api("ws_logo",{id:d.wlx,data:""});await load(true);renderWs()}
    if(d.wacc){openWp=openWp==d.wacc?null:d.wacc;renderWs()}
    if(d.wsave){const us=[...e.target.closest(".wp").querySelectorAll("[data-wu]:checked")].map(c=>({id:+c.dataset.wu,level:c.closest("label").querySelector("[data-wl]").value}));await api("ws_access",{id:d.wsave,users:us});await load(true);renderWs();alert("Access saved.")}
    if(d.wd){const w=wss.find(x=>x.id==d.wd);if(confirm("Delete the company \""+w.name+"\" and ALL its tasks and custom columns? This cannot be undone.")){await api("ws_del",{id:d.wd});if(d.wd==W){location="index.php"}else{await load(true);renderWs()}}}
  }catch(err){alert(err.message)}
});
function renderSt(){
  const work=ST.filter(s=>s[0]!=="done"),cnt=k=>tasks.filter(t=>t.status===k).length,full=LV==="full";
  $("#slist").innerHTML=work.map(([k,n],i)=>{const id=(SL.find(x=>x.k===k)||{}).id;
    return `<div class="acts"><span style="flex:1">${esc(n)} <small style="color:var(--mut)">${cnt(k)} task(s)</small></span>${full?`<input type="color" data-sc="${id}" value="${scol(k)||"#9ca3af"}" title="Row colour" style="width:34px;padding:1px"><button data-scx="${id}" title="No colour">∅</button><button data-sm="${id}" data-d="-1"${i?"":" disabled"}>↑</button><button data-sm="${id}" data-d="1"${i<work.length-1?"":" disabled"}>↓</button><button data-sr="${id}">Rename</button><button data-sd="${id}">Remove</button>`:""}</div>`}).join("")+
    `<div class="acts"><span style="flex:1">Completed <small style="color:var(--mut)">${cnt("done")} task(s) · fixed, always last</small></span>${full?`<input type="color" data-sc="${(SL.find(x=>x.k==="done")||{}).id}" value="${scol("done")||"#22c55e"}" style="width:34px;padding:1px"><button data-scx="${(SL.find(x=>x.k==="done")||{}).id}" title="No colour">∅</button>`:""}</div>`;
}
$("#sm").onclick=()=>{renderSt();$("#sdlg").showModal()};
$("#sc").onclick=()=>$("#sdlg").close();
$("#sa").onclick=async()=>{try{await api("st_add",{name:$("#sn").value});$("#sn").value="";await load(true);renderSt()}catch(e){alert(e.message)}};
$("#slist").addEventListener("click",async e=>{
  const d=e.target.dataset;try{
    if(d.scx){await api("st_color",{id:d.scx,color:""});await load(true);renderSt()}
    if(d.sm){await api("st_move",{id:d.sm,dir:d.d});await load(true);renderSt()}
    if(d.sr){const cur=SL.find(x=>x.id==d.sr),n=await ask("New name for this status:",cur.name);if(n&&n.trim()){await api("st_ren",{id:d.sr,name:n});await load(true);renderSt()}}
    if(d.sd){const cur=SL.find(x=>x.id==d.sd);if(confirm("Remove the status \""+cur.name+"\"? Its tasks move to the first remaining status.")){delNote(await api("st_del",{id:d.sd}));await load(true);renderSt()}}
  }catch(err){alert(err.message)}
});
$("#slist").addEventListener("change",async e=>{const id=e.target.dataset.sc;if(!id)return;try{await api("st_color",{id,color:e.target.value});await load(true);renderSt()}catch(err){alert(err.message)}});
$("#stats").addEventListener("click",e=>{if(e.target.closest("[data-more]")){try{localStorage.setItem("tf_smore",localStorage.getItem("tf_smore")==="1"?"":"1")}catch(x){}render();return}if(e.target.closest("[data-ov]")){ovOnly=!ovOnly;render();return}if(e.target.closest("[data-late]")){lateOnly=!lateOnly;if(lateOnly)$("#fs").value="";render();return}const el=e.target.closest("[data-fs]");if(!el)return;lateOnly=false;const f=$("#fs");f.value=f.value===el.dataset.fs?"":el.dataset.fs;try{localStorage.setItem("tf_gc_"+W,"[]")}catch(x){}render()});
$("#gtabs").addEventListener("click",e=>{const a=e.target.closest("[data-g]");if(!a)return;G=a.dataset.g;try{localStorage.setItem("tf_g_"+W,G)}catch(x){}try{const c=getGC();if(G==="")c.clear();else{c.delete(G);if(gParent(G))c.delete(gParent(G))}localStorage.setItem("tf_gc_"+W,JSON.stringify([...c]))}catch(x){}sel.clear();render()});
function renderGr(){
  const full=LV==="full";
  $("#glist").innerHTML=groups.map(g=>`<div class="acts" style="${g.level?"margin-left:26px":""}"><span style="flex:1">${g.level?"↳ ":""}📁 ${esc(g.name)} <small style="color:var(--mut)">${tasks.filter(t=>t.group_id==g.id).length} task(s)</small></span>${LV!=="view"&&!g.level?`<button data-gs="${g.id}">+ Sub-group</button>`:""}${full?`<button data-gr="${g.id}">✎ Edit</button><button data-gd="${g.id}">Delete</button>`:""}</div>`).join("")||'<div class="empty">No groups yet</div>';
}
function renderDt(){const full=LV==="full";
  $("#dlist").innerHTML=dtypes.map(d=>`<div class="acts"><span style="flex:1">${esc(d.name)} <small style="color:var(--mut)">${tasks.filter(t=>t.drawing_type===d.name).length} task(s)</small></span>${full?`<input type="color" data-dcol="${d.id}" value="${dtCol(d.name)}" title="Box colour" style="width:34px;padding:1px"><button data-dr="${d.id}">Rename</button><button data-dd="${d.id}">Delete</button>`:""}</div>`).join("")||'<div class="empty">No drawing types yet</div>'}
$("#dm").onclick=()=>{renderDt();$("#ddlg").showModal()};
$("#dc").onclick=()=>$("#ddlg").close();
$("#dlist").addEventListener("change",async e=>{const i=e.target.dataset&&e.target.dataset.dcol;if(!i)return;try{await api("dt_color",{id:+i,color:e.target.value});await load(true)}catch(er){alert(er.message)}});
$("#da").onclick=async()=>{try{await api("dt_add",{name:$("#dn").value});$("#dn").value="";await load(true);renderDt()}catch(e){alert(e.message)}};
$("#dlist").addEventListener("click",async e=>{try{
  if(e.target.dataset.dr){const d=dtypes.find(x=>x.id==e.target.dataset.dr),n=await ask("Rename drawing type:",d.name);if(n&&n.trim()){await api("dt_ren",{id:d.id,name:n});await load(true);renderDt()}}
  if(e.target.dataset.dd&&confirm("Delete this drawing type? Tasks that already use it keep their value."))await api("dt_del",{id:e.target.dataset.dd}).then(async r=>{delNote(r);await load(true);renderDt()})
}catch(err){alert(err.message)}});
function pPrev(){const bg=$("#pbg").value,fg=$("#pfg").value,p=$("#pprev");p.style.background=bg;p.style.color=fg;applyTheme(bg+"|"+fg)}
$("#pm").onclick=()=>{const t=(me.theme||"#0f2a5c|#ffffff").split("|");$("#pbg").value=t[0];$("#pfg").value=t[1];
  $("#psw").innerHTML=THEMES.map((x,i)=>`<button type="button" class="sw" data-sw="${i}" title="${x[2]}" style="background:${x[0]};color:${x[1]}">Aa</button>`).join("");pPrev();$("#pgl").checked=document.documentElement.classList.contains("glass");{let k="";try{k=localStorage.getItem("tf_look")||""}catch(e){}$("#plk").value=k}$("#pdlg").showModal()};
$("#pgl").addEventListener("change",()=>setGlass($("#pgl").checked));
$("#plk").addEventListener("change",()=>setLook($("#plk").value));
$("#psw").addEventListener("click",e=>{const i=e.target.dataset.sw;if(i==null)return;$("#pbg").value=THEMES[i][0];$("#pfg").value=THEMES[i][1];pPrev()});
$("#pbg").addEventListener("input",pPrev);$("#pfg").addEventListener("input",pPrev);
$("#pcx").onclick=()=>$("#pdlg").close();
$("#pdlg").addEventListener("close",()=>applyTheme(me.theme||""));
$("#psv").onclick=async()=>{const v=$("#pbg").value+"|"+$("#pfg").value;try{await api("pref_theme",{v});me.theme=v;try{localStorage.setItem("tf_theme",v)}catch(x){}$("#pdlg").close()}catch(e){alert(e.message)}};
$("#prs").onclick=async()=>{try{await api("pref_theme",{v:""});me.theme="";try{localStorage.setItem("tf_theme","")}catch(x){}$("#pdlg").close()}catch(e){alert(e.message)}};
$("#om").onclick=()=>{$("#oh").checked=!!+ovset.hand;$("#oo").checked=!!+ovset.one;$("#odlg").showModal()};
$("#oc").onclick=()=>$("#odlg").close();
$("#os").onclick=async()=>{try{await api("ov_set",{hand:$("#oh").checked,one:$("#oo").checked});$("#odlg").close();await load(true)}catch(e){alert(e.message)}};
let geId=0;
let geCol="";function gcsDraw(){$("#gecs").innerHTML='<button class="auto'+(geCol?"":" on")+'" data-gc="">Auto</button>'+GPAL.map(c=>`<button data-gc="${c}" class="${geCol===c?"on":""}" style="background:${c}" title="${c}"></button>`).join("")}
$("#gecs").addEventListener("click",e=>{const b=e.target.closest("button");if(!b)return;e.preventDefault();geCol=b.dataset.gc;gcsDraw()});
$("#gex").onclick=()=>$("#gedlg").close();
$("#gemb").onclick=async()=>{const to=+$("#gemt").value,w=wss.find(x=>x.id==to),g=groups.find(x=>x.id==geId);if(!to||!g)return;
  if(!confirm("Move the group \""+g.name+"\""+(g.level?"":" with its sub-groups")+" and all its tasks to \""+(w?w.name:"")+"\"?"))return;
  try{const r=await api("gr_move",{id:geId,to});$("#gedlg").close();if(G===String(geId))G="";await load(true);renderGr();alert("Moved "+r.moved+" task(s) to "+(w?w.name:"the other company")+".")}catch(e){alert(e.message)}};
$("#gesv").onclick=async()=>{const n=$("#gen").value.trim();if(!n){alert("Enter a name");return}try{await api("gr_ren",{id:geId,name:n,parent_id:+$("#gep").value||0,color:geCol});$("#gedlg").close();await load(true);renderGr()}catch(e){alert(e.message)}};
$("#gm").onclick=()=>{renderGr();$("#gdlg").showModal()};
$("#gc").onclick=()=>$("#gdlg").close();
$("#ga").onclick=async()=>{try{const r=await api("gr_add",{name:$("#gn").value});$("#gn").value="";await load(true);G=String(r.id);try{localStorage.setItem("tf_g_"+W,G)}catch(x){}render();renderGr()}catch(e){alert(e.message)}};
$("#glist").addEventListener("click",async e=>{
  const d=e.target.dataset;try{
    if(d.gs){const n=await ask("New sub-group name:");if(n&&n.trim()){await api("gr_add",{name:n,parent_id:d.gs});await load(true);renderGr()}}
    if(d.gr){const cur=groups.find(g=>g.id==d.gr),hasSub=groups.some(x=>String(x.parent_id)===String(cur.id));geId=cur.id;geCol=cur.color||"";gcsDraw();$("#gen").value=cur.name;
      $("#gep").innerHTML='<option value="">— None (top level) —</option>'+groups.filter(x=>!x.level&&x.id!=cur.id).map(x=>`<option value="${x.id}">${esc(x.name)}</option>`).join("");$("#gep").value=cur.parent_id?String(cur.parent_id):"";
      $("#gep").disabled=hasSub;const oth=wss.filter(w=>w.id!=W&&["edit","full"].includes(w.level));$("#gemw").hidden=!oth.length;$("#gemt").innerHTML=oth.map(w=>`<option value="${w.id}">${esc(w.name)}</option>`).join("");$("#genote").textContent=hasSub?"This group has sub-groups, so it must stay at the top level.":"";$("#gedlg").showModal()}
    if(d.gd){const cur=groups.find(g=>g.id==d.gd);if(confirm("Delete the group \""+cur.name+"\""+(cur.level?"":" and its sub-groups")+"? Their tasks are kept and moved to No group.")){{const r=await api("gr_del",{id:d.gd});delNote(r);if(!(r&&r.pending)&&G===String(d.gd))G=""};await load(true);renderGr()}}
  }catch(err){alert(err.message)}
});
const DAYMS=86400000;
let gz=(()=>{try{return +localStorage.getItem("tf_gz")||32}catch(e){return 32}})(),gShowDone=false,gMode=(()=>{try{return localStorage.getItem("tf_gmode")==="task"?"task":"person"}catch(e){return "person"}})();
const pd=s=>{const m=/^(\d{4})-(\d{2})-(\d{2})/.exec(s||"");return m?Date.UTC(+m[1],m[2]-1,+m[3]):null};
const fd=ms=>new Date(ms).toISOString().slice(0,10);
function renderGantt(list){
  const old=document.querySelector(".gwrap"),keep=old?{l:old.scrollLeft,t:old.scrollTop}:null;
  const open=list.filter(t=>gShowDone||t.status!=="done");
  const items=open.map(t=>{let s=pd(t.start_date),e=pd(t.end_date);if(s==null&&e==null)return null;if(s==null)s=e;if(e==null)e=s;if(e<s)[s,e]=[e,s];return{t,s,e}}).filter(Boolean);
  const nodate=open.length-items.length,now=pd(today());
  let min=now-14*DAYMS,max=now+45*DAYMS;
  for(const x of items){if(x.s<min)min=x.s;if(x.e>max)max=x.e}
  min-=3*DAYMS;max+=7*DAYMS;
  const days=Math.round((max-min)/DAYMS)+1,width=days*gz,LEFT=gMode==="task"?250:170,a=$("#fa").value;
  let lanes=users.map(u=>({id:String(u.id),name:u.name}));
  for(const x of items){const id=x.t.assigned_to?String(x.t.assigned_to):"";if(id&&!lanes.some(l=>l.id===id))lanes.push({id,name:names[id]||"Former member"})}
  lanes.push({id:"",name:"Unassigned"});
  lanes=lanes.filter(l=>a?l.id===a:true);
  // header
  let mh="",dh="";
  for(let i=0;i<days;){const d=new Date(min+i*DAYMS),ml=new Date(Date.UTC(d.getUTCFullYear(),d.getUTCMonth()+1,0)).getUTCDate()-d.getUTCDate()+1,n=Math.min(ml,days-i);
    mh+=`<div class="gm" style="left:${i*gz}px;width:${n*gz}px">${d.toLocaleString("en",{month:"short",year:"numeric",timeZone:"UTC"})}</div>`;i+=n}
  for(let i=0;i<days;i++){const d=new Date(min+i*DAYMS);
    if(gz>=24)dh+=`<div class="gd" style="left:${i*gz}px;width:${gz}px">${d.getUTCDate()}</div>`;
    else if(gz>=10&&d.getUTCDay()===1)dh+=`<div class="gd" style="left:${i*gz}px;width:${gz*7}px;text-align:left;padding-left:3px">${d.getUTCDate()}</div>`}
  const step=gz>=24?gz:gz>=10?gz*7:0,grid=step?`background-image:linear-gradient(to right,var(--line) 1px,transparent 1px);background-size:${step}px 100%;`:"";
  const tip=t=>[t.title,(t.order_no?"Order "+t.order_no:""),(t.customer||""),(t.drawing_type||""),(t.start_date||"?")+" → "+(t.end_date||"?"),(ST.find(x=>x[0]===t.status)||[0,t.status])[1],ov(t).length?ovText(t):""].filter(Boolean).join("\n");
  const laneHTML=l=>{
    const mine=items.filter(x=>(x.t.assigned_to?String(x.t.assigned_to):"")===l.id).sort((p,q)=>p.s-q.s||p.e-q.e);
    const rowsEnd=[],pos=new Map();
    for(const x of mine){let r=rowsEnd.findIndex(e=>e<x.s);if(r<0){r=rowsEnd.length;rowsEnd.push(0)}rowsEnd[r]=x.e;pos.set(x,r)}
    const h=Math.max(1,rowsEnd.length)*30+10;
    const bars=mine.map(x=>{const t=x.t,L=Math.round((x.s-min)/DAYMS*gz),Wd=Math.max(8,Math.round((x.e-x.s)/DAYMS*gz+gz)),c=scol(t.status)||"#6b7385",o=ov(t).length;
      return `<div class="gbar${t.status==="done"?" dn":""}" data-id="${t.id}" title="${ea(tip(t))}" style="left:${L}px;width:${Wd}px;top:${pos.get(x)*30+5}px;background:${c};${o?"outline:2px solid #dc2626;outline-offset:1px;":""}">${o?"⚠ ":""}${t.status==="done"?"✔ ":""}${esc((t.order_no?t.order_no+" · ":"")+t.title)}</div>`}).join("");
    return {n:mine.length,html:`<div class="glane" data-lane="${l.id}" style="height:${h}px"><div class="gname" style="height:${h}px">${esc(l.name)}<small>${mine.length?mine.length+" task"+(mine.length>1?"s":""):"free"}</small></div><div class="gtrack" style="width:${width}px;${grid}">${bars}</div></div>`}};
  const taskRows=()=>{
    const info=new Map(items.map(x=>[x.t.id,x])),key=t=>info.has(t.id)?info.get(t.id).s:Infinity;
    const row=t=>{const x=info.get(t.id),who=t.assigned_to?(names[t.assigned_to]||"?"):"Unassigned",st=(ST.find(z=>z[0]===t.status)||[0,t.status])[1],o=ov(t).length;
      let bar="";
      if(x){const L=Math.round((x.s-min)/DAYMS*gz),Wd=Math.max(8,Math.round((x.e-x.s)/DAYMS*gz+gz)),c=scol(t.status)||"#6b7385";
        bar=`<div class="gbar${t.status==="done"?" dn":""}" data-id="${t.id}" title="${ea(tip(t))}" style="left:${L}px;width:${Wd}px;top:8px;background:${c};${o?"outline:2px solid #dc2626;outline-offset:1px;":""}">${o?"⚠ ":""}${t.status==="done"?"✔ ":""}${esc(who)}</div>`}
      return `<div class="glane" style="height:42px"><div class="gname tk" data-id="${t.id}" style="height:42px;cursor:pointer">${o?"⚠ ":""}${esc((t.order_no?t.order_no+" · ":"")+t.title)}<small>${esc(who)} · ${esc(st)}${x?"":" · no dates"}</small></div><div class="gtrack" style="width:${width}px;${grid}">${bar}</div></div>`};
    const sortT=l=>l.slice().sort((p,q)=>key(p)-key(q)||String(p.title).localeCompare(String(q.title)));
    if(!groups.length)return sortT(open).map(row).join("")||"";
    const secs=groups.map(g=>({name:g.full,l:open.filter(t=>t.group_id==g.id)})).concat([{name:"No group",l:open.filter(t=>!t.group_id)}]).filter(z=>z.l.length);
    return secs.map(z=>`<div class="glane" style="height:26px;background:var(--col)"><div class="gname tk" style="height:26px;background:var(--col)">📁 ${esc(z.name)} <small style="display:inline">${z.l.length}</small></div><div class="gtrack" style="width:${width}px"></div></div>`+sortT(z.l).map(row).join("")).join("");
  };
  const built=gMode==="task"?(taskRows()||'<div class="empty">No tasks to show</div>'):lanes.map(laneHTML).filter((b,i)=>lanes[i].id!==""||b.n>0).map(b=>b.html).join("");
  const tx=LEFT+Math.round((now-min)/DAYMS*gz+gz/2);
  $("#main").innerHTML=`<div class="gtools"><b style="color:var(--ink)">${gMode==="task"?"Tasks timeline":"Who is doing what"}</b>
    <label>Rows <select id="gmode"><option value="person"${gMode==="person"?" selected":""}>By person</option><option value="task"${gMode==="task"?" selected":""}>By task</option></select></label>
    <label>Zoom <select id="gz"><option value="32"${gz===32?" selected":""}>Days</option><option value="14"${gz===14?" selected":""}>Weeks</option><option value="5"${gz===5?" selected":""}>Months</option></select></label>
    <label><input type="checkbox" id="gdone"${gShowDone?" checked":""}> Show completed</label>
    <button id="gtoday">Go to today</button>
    ${nodate&&gMode!=="task"?`<span>${nodate} task(s) have no dates and are not shown</span>`:""}<span>Red outline ⚠ = overlaps another task for the same person</span></div>
    <div class="gwrap"><div class="ginner" style="width:${LEFT+width}px;--gl:${LEFT}px"><div class="ghead"><div class="gname">${gMode==="task"?"Task":"Person"}</div><div class="gtrack" style="width:${width}px">${mh}${dh}</div></div>${built}<div class="gtoday" style="left:${tx}px"></div></div></div>`;
  const w=document.querySelector(".gwrap");
  if(keep){w.scrollLeft=keep.l;w.scrollTop=keep.t}else w.scrollLeft=Math.max(0,tx-LEFT-220);
}
$("#main").addEventListener("change",e=>{
  if(e.target.id==="gz"){gz=+e.target.value;try{localStorage.setItem("tf_gz",gz)}catch(x){}document.querySelector(".gwrap")&&(document.querySelector(".gwrap").remove());render()}
  if(e.target.id==="gmode"){gMode=e.target.value;try{localStorage.setItem("tf_gmode",gMode)}catch(x){}const w=document.querySelector(".gwrap");if(w)w.remove();render()}
  if(e.target.id==="gdone"){gShowDone=e.target.checked;render()}
});
$("#main").addEventListener("click",e=>{if(e.target.id==="gtoday"){const w=document.querySelector(".gwrap"),t=document.querySelector(".gtoday");if(w&&t)w.scrollLeft=Math.max(0,parseInt(t.style.left)-(gMode==="task"?250:170)-220)}});
function renderCols(){
  const hc=hiddenCols(),lb=colLabels();
  const built=BASE_COLS.map(c=>`<div class="acts"><span style="flex:1">${esc(lb[c.id]||c.label)} <small style="color:var(--mut)">built-in${hc.includes(c.id)?" · hidden":""}</small></span><button data-bce="${c.id}">✎ Edit</button></div>`).join("");
  const cust=cols.map(c=>`<div class="acts"><span style="flex:1">${esc(c.name)} <small style="color:var(--mut)">${c.ctype}${c.ctype==="dropdown"?": "+esc((c.options||[]).join(", ")):""}${hc.includes("x_"+c.id)?" · hidden":""}</small></span>${LV!=="view"?`<button data-ce="${c.id}">✎ Edit</button>`:""}${me.role==="admin"?`<button data-cd="${c.id}">Remove</button>`:""}</div>`).join("");
  $("#clist").innerHTML=built+(cust||"")}
let ceId=0,ceBuilt="";
$("#cex").onclick=()=>$("#cedlg").close();
$("#cesv").onclick=async()=>{const key=ceBuilt||"x_"+ceId,hc=hiddenCols().filter(x=>x!==key);if($("#ceh").checked&&key!=="title")hc.push(key);
  try{if(ceBuilt){const lb=colLabels(),n=$("#cen").value.trim();if(n)lb[ceBuilt]=n;else delete lb[ceBuilt];localStorage.setItem(lbKey(),JSON.stringify(lb))}
    else {const cc=cols.find(x=>x.id==ceId),cv=cc&&cc.ctype==="text"&&$("#ceo").value.trim()!=="";await api("col_edit",Object.assign({id:ceId,name:$("#cen").value,options:$("#ceo").value},cv?{ctype:"dropdown"}:{}))}
    localStorage.setItem(hcKey(),JSON.stringify(hc));$("#cedlg").close();await load(true);renderCols();render()}catch(e){alert(e.message)}};
$("#cm").onclick=()=>{renderCols();$("#cdlg").showModal()};
$("#cc").onclick=()=>$("#cdlg").close();
$("#ct").onchange=()=>{$("#co").style.display=$("#ct").value==="dropdown"?"":"none"};
$("#ca").onclick=async()=>{
  try{await api("col_add",{name:$("#cn").value,ctype:$("#ct").value,options:$("#co").value});
    $("#cn").value="";$("#co").value="";await load(true);renderCols()}catch(e){alert(e.message)}
};
$("#clist").addEventListener("click",async e=>{
  const bb=e.target.dataset.bce;if(bb){const c=BASE_COLS.find(x=>x.id===bb);ceBuilt=bb;ceId=0;$("#cen").value=colLabels()[bb]||c.label;$("#ceow").hidden=true;$("#ceh").checked=hiddenCols().includes(bb);$("#ceh").disabled=bb==="title";$("#cet").textContent="Built-in column. The name and visibility apply only on this device.";$("#cedlg").showModal();return}
  const eb=e.target.dataset.ce;if(eb){const c=cols.find(x=>x.id==eb);ceBuilt="";ceId=c.id;$("#ceh").checked=hiddenCols().includes("x_"+c.id);$("#ceh").disabled=false;$("#cen").value=c.name;$("#ceo").value=(c.options||[]).join(", ");$("#ceow").hidden=!(c.ctype==="dropdown"||c.ctype==="text");$("#ceol").textContent=c.ctype==="text"?"Turn into a dropdown: type the options, comma separated (leave empty to keep it as text)":"Options (comma separated)";$("#cet").textContent="Type: "+c.ctype+(c.ctype==="text"?" (can be turned into a dropdown, not back)":" (the type cannot be changed)")+". Hiding applies only on this device.";$("#cedlg").showModal();return}
  const id=e.target.dataset.cd;if(!id||!confirm("Remove this column? The values entered in it will be deleted."))return;
  try{delNote(await api("col_del",{id}));await load(true);renderCols()}catch(err){alert(err.message)}
});
function renderAcc(){
  const th="padding:5px 8px;border-bottom:1px solid var(--line);text-align:left;font-size:12px;white-space:nowrap";
  const mem=allUsers.filter(u=>u.role!=="admin");
  const opt=(u,w)=>{const l=(access[w.id]||{})[u.id]||"";const o=[["","— no access"],["view","View only"]].concat(u.role==="guest"?[]:[["edit","Can edit"],["full","Edit + delete"]]);return`<select data-ac="${w.id}:${u.id}" style="font-size:12px">${o.map(([k,n])=>`<option value="${k}"${l===k||(u.role==="guest"&&l&&k==="view")?" selected":""}>${n}</option>`).join("")}</select>`};
  $("#acbody").innerHTML=mem.length?`<table style="border-collapse:collapse;width:100%"><tr><th style="${th}">User</th>${wss.map(w=>`<th style="${th}">${esc(w.name)}</th>`).join("")}</tr>${mem.map(u=>`<tr><td style="${th}"><b>${esc(u.name)}</b> <small style="color:var(--mut)">${u.role==="guest"?"Guest":"Member"}</small></td>${wss.map(w=>`<td class="acc" data-lv="${(access[w.id]||{})[u.id]||"none"}" style="${th}">${opt(u,w)}</td>`).join("")}</tr>`).join("")}</table>`:'<div class="empty">No members yet</div>'}
$("#tmw").addEventListener("mouseenter",()=>{const r=$("#tmw").getBoundingClientRect(),m=$("#tmw .tms");m.style.left=(r.right+2)+"px";m.style.top=Math.max(8,Math.min(r.top-6,innerHeight-90))+"px"});
$("#tm1").onclick=()=>$("#tm").click();
$("#tm2").onclick=()=>{renderAcc();$("#acdlg").showModal()};
$("#acbody").addEventListener("change",e=>{const sel=e.target.closest("[data-ac]");if(sel)sel.closest("td").dataset.lv=sel.value||"none"});
$("#acx").onclick=()=>$("#acdlg").close();
$("#acsave").onclick=async()=>{try{
  const by={};for(const w of wss)by[w.id]=[];
  document.querySelectorAll("#acbody [data-ac]").forEach(el=>{if(!el.value)return;const [w,u]=el.dataset.ac.split(":");by[w].push({id:+u,level:el.value})});
  for(const w of wss)await api("ws_access",{id:w.id,users:by[w.id]});
  await load(true);alert("Access saved.");renderAcc()}catch(e){alert(e.message)}};
function renderUsers(){
  $("#tlist").innerHTML=allUsers.map(u=>`<div class="urow"><span><b>${esc(u.name)}</b>${u.role==="guest"?' <small style="color:var(--mut)">· Guest</small>':""}${u.email?`<br><small style="color:var(--mut)" title="${ea(u.email)}">${esc(u.email)}</small>`:""}</span><span>${esc(u.username||"")}</span>
   <span class="pw"><input type="password" data-np="${u.id}" placeholder="•••••••• (new password)" autocomplete="new-password"><button type="button" class="eye" title="Show / hide">👁</button></span>
   <select data-role="${u.id}"${u.id==me.id?" disabled":""}><option value="member"${u.role!=="admin"&&u.role!=="guest"?" selected":""}>Member</option><option value="guest"${u.role==="guest"?" selected":""}>Guest (view only)</option><option value="admin"${u.role==="admin"?" selected":""}>Admin</option></select>
   <span style="display:flex;gap:4px"><button data-ed="${u.id}" title="Edit name, username and email">✎</button><button data-pw="${u.id}" title="Type a new password in the box, then press this">Reset password</button>${u.id==me.id?"":`<button data-rm="${u.id}" title="Remove user">✕</button>`}</span></div>`).join("");
}
const LGA={login:"Signed in",auto_login:"Signed in (kept)",logout:"Signed out",login_failed:"Failed sign-in",open_company:"Opened company",save:"Task",field:"Task edit",delete:"Deleted task(s)",dup:"Duplicated task(s)",co_set:"Changed people on task",task_move:"Moved task(s)",task_order:"Reordered tasks",tl_add:"Logged hours",tl_del:"Deleted hours entry",tl_rev:"Changed Rev on hours",cm_add:"Wrote a message",cm_del:"Deleted a message",user_add:"Added user",user_edit:"Edited user",user_pass:"Reset a password",user_role:"Changed user type",user_del:"Removed user",ws_access:"Changed access",ws_add:"Added company",ws_del:"Removed company",ws_rename:"Renamed company",dr_act:"Approval decision"};
const lgUA=u=>{u=u||"";const b=/Edg\//.test(u)?"Edge":/Chrome\//.test(u)?"Chrome":/Firefox\//.test(u)?"Firefox":/Safari\//.test(u)?"Safari":"Browser",o=/Android/.test(u)?"Android":/iPhone|iPad/.test(u)?"iOS":/Windows/.test(u)?"Windows":/Mac OS/.test(u)?"Mac":/Linux/.test(u)?"Linux":"";return b+(o?" · "+o:"")};
const fmtKB=n=>n>1048576?(n/1048576).toFixed(1)+" MB":Math.max(1,Math.round(n/1024))+" KB";
async function bkLoad(){try{const r=await api("bk_list",{});
  $("#remc").value=(r.remcmd||"").replace(/^\*\/30 \* \* \* \* /,"");const l=r.last,when=t=>new Date(t*1000).toLocaleString();
  $("#bks").innerHTML=!r.dir_ok?'<span style="color:#dc2626">The server has no writable folder for backups. Ask your host, or tell me and I will change the location.</span>':l?`Last backup: <b style="color:${l.ok?"#16a34a":"#dc2626"}">${l.ok?"OK":"FAILED"}</b> ${esc(when(l.time))}${l.ok?" · "+fmtKB(l.size)+(l.emailed?" · emailed to "+esc(r.email):""):""}${l.err?`<br><span style="color:#dc2626">${esc(l.err)}</span>`:""}`:`No backup has run yet. Daily emails go to <b>${esc(r.email)}</b>.`;
  const w=r.wlast;$("#wls").innerHTML=w?`Last worklog: <b style="color:${w.ok?"#16a34a":"#dc2626"}">${w.ok?"OK":"FAILED"}</b> ${esc(when(w.time))}${w.ok?" · "+w.rows+" entries, "+(+w.hours).toFixed(2)+" h"+(w.emailed?" · emailed":""):""}${w.err?`<br><span style="color:#dc2626">${esc(w.err)}</span>`:""}`:"No worklog has been created yet.";
  $("#wll").innerHTML=(r.wfiles||[]).length?`<table class="tlt">${r.wfiles.map(f=>`<tr><td>${esc(when(f.time))}</td><td class="n">${fmtKB(f.size)}</td><td><button data-bkd="${ea(f.name)}">Download</button></td></tr>`).join("")}</table>`:"";
  $("#bkl").innerHTML=r.files.length?`<table class="tlt"><tr><th>When</th><th class="n">Size</th><th></th></tr>${r.files.map(f=>`<tr><td>${esc(when(f.time))}</td><td class="n">${fmtKB(f.size)}</td><td><button data-bkd="${ea(f.name)}">Download</button></td></tr>`).join("")}</table>`:'<div class="empty">No copies yet</div>'}catch(e){$("#bks").textContent=e.message}}
$("#remcp").onclick=()=>{const t=$("#remc");t.select();try{navigator.clipboard.writeText(t.value)}catch(e){document.execCommand("copy")}$("#remcp").textContent="Copied ✓";setTimeout(()=>$("#remcp").textContent="Copy command",1500)};
$("#pshtst").onclick=async()=>{try{await api("push_test",{});alert("Sent. It should reach your phone in a few seconds.")}catch(e){alert(e.message)}};
for(const k of["am","pm"])$("#rem"+k).onclick=async()=>{if(!confirm("This sends the real reminder messages to everyone now. Continue?"))return;try{const r=await api("rem_test",{slot:k});alert(r.res)}catch(e){alert(e.message)}};
async function bkRun(mail,wl){const b=$(wl?(mail?"#wlm":"#wln"):(mail?"#bkm":"#bkn"));b.disabled=true;const t=b.textContent;b.textContent="Working…";try{const r=await api(wl?"wl_run":"bk_run",{mail:mail?1:0});if(!r.res.ok)alert("Backup failed: "+r.res.err);else if(r.res.err)alert(r.res.err)}catch(e){alert(e.message)}b.disabled=false;b.textContent=t;bkLoad()}
// ---- Guest home (guests land here; admins can preview it from the account menu) ----
let ghSel=0,ghR=null;try{ghSel=+localStorage.getItem("tf_ghsel")||0}catch(e){}
function ghHtml(r0){
  ghR=r0;const allC=r0.companies||[];if(allC.length&&!allC.some(w=>w.id==ghSel))ghSel=allC[0].id;
  const f=a=>ghSel?a.filter(t=>t.ws_id==ghSel):a;
  const r=Object.assign({},r0,{done:f(r0.done),going:f(r0.going),soon:f(r0.soon),companies:ghSel?allC.filter(w=>w.id==ghSel):allC,people:(r0.people||[]).map(p=>Object.assign({},p,{tasks:f(p.tasks)}))});
  const pick=allC.length>1?`<div class="htabs" style="margin:0 0 4px">${allC.map(w=>`<button type="button" data-ghc="${w.id}" class="${ghSel==w.id?"on":""}">${esc(w.name)}</button>`).join("")}</div>`:"";
  const ini=n=>String(n||"?").trim().split(/\s+/).slice(0,2).map(x=>x[0]).join("").toUpperCase();
  const fd=d=>{if(!d||d==="0000-00-00")return"";const x=new Date(d+"T00:00:00");return x.toLocaleDateString(undefined,{day:"numeric",month:"short"})};
  const meta=t=>[t.order_no,t.customer,t.drawing_type].filter(Boolean).map(esc).join(" · ");
  const dueTag=(t,mode,td)=>{const ed=t.end_date&&t.end_date!=="0000-00-00"?t.end_date:"";
    if(mode==="done")return `<span class="d dn"><small>COMPLETED</small>${esc(fd(t.when))}</span>`;
    if(mode==="soon")return `<span class="d sn"><small>STARTS</small>${esc(fd(t.start_date))}</span>`;
    if(!ed)return `<span class="d" style="font-weight:600">${esc(t.sname||"")}</span>`;
    return ed<td?`<span class="d od"><small>OVERDUE</small>${esc(fd(ed))}</span>`:`<span class="d du"><small>DUE</small>${esc(fd(ed))}</span>`};
  const multi=(r.companies||[]).length>1;
  const row=(t,mode)=>`<a class="hrow" href="index.php?w=${t.ws_id}"><div style="min-width:0"><b>${esc(t.title)}</b><small>${meta(t)}</small></div><div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;justify-content:flex-end">${t.person?`<span class="hco">${esc(t.person)}</span>`:""}${multi&&t.company?`<span class="hst" style="--pc:#64748b">${esc(t.company)}</span>`:""}</div>${dueTag(t,mode,r.today)}</a>`;
  const byType=arr=>{const m={};arr.forEach(t=>{const k=String(t.drawing_type||"").trim().toUpperCase()||"OTHER";(m[k]=m[k]||[]).push(t)});return Object.keys(m).sort((a,b)=>a==="OTHER"?1:b==="OTHER"?-1:(m[b].length-m[a].length)||a.localeCompare(b)).map(k=>[k,m[k]])};
  const sec=(title,c,arr,mode)=>`<div class="hsec" style="--c:${c}"><h3><i></i>${title} <em>${arr.length}</em></h3>${arr.length?byType(arr).map(([k,v])=>`<div class="gh-th">${esc(k)} <small>${v.length} task${v.length===1?"":"s"}</small></div>${v.map(t=>row(t,mode)).join("")}`).join(""):'<div class="hrow" style="color:var(--mut)">Nothing here right now.</div>'}</div>`;
  const dueCell=t=>{const ed=t.end_date&&t.end_date!=="0000-00-00"?t.end_date:"";return ed?(ed<r.today?`<span class="gh-due od">${esc(fd(ed))}<small>Overdue</small></span>`:`<span class="gh-due du">${esc(fd(ed))}</span>`):'<span style="color:var(--mut)">–</span>'};
  const trs=(r.people||[]).map(p=>p.tasks.length?p.tasks.map((t,i)=>`<tr>${i?"":`<td rowspan="${p.tasks.length}" class="gh-pn"><div style="display:flex;align-items:center;gap:8px"><span class="gh-av">${esc(ini(p.name))}</span><b>${esc(p.name)}</b></div><small>${p.tasks.length} task${p.tasks.length===1?"":"s"}</small></td>`}<td><b>${esc(t.title)}</b></td><td>${esc(t.order_no||"")}</td><td>${esc(t.customer||"")}</td><td>${esc(t.drawing_type||"")}</td><td>${dueCell(t)}</td></tr>`).join(""):"").join("");
  const ppl=trs?`<div class="gh-tw"><table class="gh-tb"><thead><tr><th>Person</th><th>Working on</th><th>Order No.</th><th>Customer</th><th>Drawing type</th><th>Due</th></tr></thead><tbody>${trs}</tbody></table></div>`:"";
  const np=(r.people||[]).filter(p=>p.tasks.length).length;
  const late=r.going.filter(t=>t.end_date&&t.end_date!=="0000-00-00"&&t.end_date<r.today).length;
  const lg=w=>{const f=(typeof wss!=="undefined"&&wss.find(x=>x.id==w.id))||w;return wlogo(f)};
  const cards=allC.map(w=>{const g=r0.going.filter(t=>t.ws_id==w.id).length,s=r0.soon.filter(t=>t.ws_id==w.id).length,d=r0.done.filter(t=>t.ws_id==w.id).length;return `<a class="hcc"${ghSel==w.id&&allC.length>1?' style="outline:2px solid #38bdf8"':""} href="index.php?w=${w.id}"><span class="hcl">${lg(w)}</span><b>${esc(w.name)}</b><small>${g} ongoing${s?` · ${s} starting soon`:""}</small><small>${d} completed this week</small></a>`}).join("")||'<div class="empty">No companies shared with you yet.</div>';
  const h=new Date().getHours(),greet=h<5?"Working late":h<12?"Good morning":h<17?"Good afternoon":"Good evening",gem=h<5?"\u{1F319}":h<12?"☀️":h<17?"\u{1F324}️":"\u{1F306}";
  const who=r.asName||(typeof me!=="undefined"&&me.name)||"there";
  const wk=fd(r.from)+" – "+fd(r.to);
  return `<div class="hw"><aside class="hl"><div class="hlt"><div class="hgr">${gem} ${greet}</div><h2>${esc(String(who).split(" ")[0])} <span class="wv">\u{1F44B}</span></h2><p>Week of ${esc(wk)}: <b>${r.going.length}</b> ongoing task${r.going.length===1?"":"s"} across <b>${(r.companies||[]).length}</b> compan${(r.companies||[]).length===1?"y":"ies"}.</p>
  <div class="hch"><span class="gn"><b>${r.done.length}</b>Completed</span><span><b>${r.going.length}</b>Ongoing</span><span><b>${r.soon.length}</b>Starting soon</span><span class="${late?"rd":""}"><b>${late}</b>Overdue</span></div></div>
  <div class="hlc"><div class="hlh">Companies</div><div class="hcg">${cards}</div></div>${(r0.companies||[]).length?`<div class="gh-em"><button type="button" id="ghem">\u2709 Email me Status Report</button><small id="ghemt">${r0.email?(allC.length>1?esc((allC.find(w=>w.id==ghSel)||{}).name||"")+" report will be sent to ":"Will be sent to ")+esc(r0.email):"No email address on your account yet"}</small></div>`:""}<div class="hlf">Updated live from TaskFlow. <i>»</i></div></aside>
  <section class="hrr">${pick}${sec("Completed this week","#16a34a",r.done,"done")}${sec("Ongoing now","#2563eb",r.going,"going")}${sec("Starting in the next 3 days","#f59e0b",r.soon,"soon")}
  <div class="hsec" style="--c:#7c3aed"><h3><i></i>Who is working on what <em>${np}</em></h3>${ppl||'<div class="hrow" style="color:var(--mut)">No one has an ongoing task right now.</div>'}</div></section></div>`}
async function renderGuestHome(){
  const m=$("#main");m.innerHTML='<div class="hm"><div class="empty">Loading overview…</div></div>';
  try{const r=await api("guest_home",{});m.innerHTML=ghHtml(r)}catch(e){m.innerHTML='<div class="gho"><div class="empty">'+esc(e.message)+'</div></div>'}}
document.addEventListener("click",e=>{const b=e.target.closest("[data-ghc]");if(!b||!ghR)return;ghSel=+b.dataset.ghc;try{localStorage.setItem("tf_ghsel",ghSel)}catch(x){}
  const gb=b.closest("#ghb");if(gb){const p=ghPanel(ghR,+$("#ghas").value||0);gb.innerHTML=p+ghHtml(ghR)}else $("#main").innerHTML=ghHtml(ghR)});
document.addEventListener("click",async e=>{const b=e.target.closest("#ghem");if(!b||!ghSel)return;const t=b.parentNode.querySelector("small"),old=b.textContent;b.disabled=true;b.textContent="Sending\u2026";t.className="";
  try{const r=await api("guest_email_report",{ws:ghSel,as:+($("#ghas")&&$("#ghas").value)||0});t.textContent="Sent to "+r.to+". Please check your inbox.";t.className="ok"}catch(x){t.textContent=x.message;t.className="er"}
  b.textContent=old;setTimeout(()=>{b.disabled=false},3000)});
let ghInit=false;
async function ghLoad(){const as=+$("#ghas").value||0;$("#ghb").innerHTML='<div class="empty">Loading…</div>';
  try{const r=await api("guest_home",{as});if(!ghInit){ghInit=true;$("#ghas").innerHTML='<option value="0">Admin (all companies)</option>'+(r.guests||[]).map(g=>`<option value="${g.id}">${esc(g.name)} (guest)</option>`).join("")}$("#ghb").innerHTML=ghPanel(r,as)+ghHtml(r)}catch(e){$("#ghb").innerHTML='<div class="empty">'+esc(e.message)+'</div>'}}
function ghPanel(r,as){
  if(!as||!r.gtree)return"";
  const hide=new Set((r.hide||[]).map(String)),cn={};(r.companies||[]).forEach(w=>cn[w.id]=w.name);
  const by={};r.gtree.forEach(g=>{(by[g.ws_id]=by[g.ws_id]||[]).push(g)});
  const blocks=Object.keys(by).map(k=>`<div class="gh-gb"><b>${esc(+k?cn[k]||"Company":"Tasks without a group")}</b>${by[k].map(g=>`<label><input type="checkbox" data-ghx="${g.gid}"${(hide.has(String(g.gid))||(g.gid<=-1000&&hide.has("-1")))?"":" checked"}> ${esc(g.name)}</label>`).join("")}</div>`).join("");
  return `<details class="gh-gp" open><summary>Groups ${esc(r.asName||"this guest")} can see <small>${hide.size?hide.size+" hidden":"all visible"}</small></summary><div class="gh-gg">${blocks}</div><div class="acts" style="margin:8px 0 0"><button id="ghsv" class="p" style="width:auto;margin:0">Save for ${esc(r.asName||"this guest")}</button><small style="color:var(--mut)">Unticked groups are hidden from this guest's overview. New groups are visible by default.</small></div></details>`}
document.addEventListener("click",async e=>{if(e.target.id!=="ghsv")return;const as=+$("#ghas").value||0;if(!as)return;const hide=[...document.querySelectorAll("#ghb [data-ghx]")].filter(c=>!c.checked).map(c=>+c.dataset.ghx);
  e.target.disabled=true;try{await api("guest_hide_save",{user_id:as,hide});await ghLoad()}catch(x){alert(x.message)}});
$("#ghm").onclick=e=>{e.preventDefault();$("#umenu").hidden=true;$("#smenu").hidden=true;$("#ghdg").showModal();ghLoad()};
$("#ghx").onclick=()=>$("#ghdg").close();$("#ghrf").onclick=ghLoad;$("#ghas").onchange=ghLoad;
// ---- Status report (admin) ----
let srItems=[],srCo=[],srUs=[],srCur=null,srEG=new Set(),srET=new Set(),srTree=[],srOrd=[],srTy=[];
const srAhOpts=[1,2,3,5,7,10,14,21,30,45,60];
const srDays=["","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],srTm=m=>String(m/60|0).padStart(2,"0")+":"+String(m%60).padStart(2,"0");
function srList(){$("#srl").innerHTML=srItems.map(x=>`<div class="src ${srCur===x.id?"on":""}" data-sr="${x.id}"><b>${esc(x.name||"Status report")}</b><small>${esc(srDays[x.dow])} ${srTm(+x.hhmm)} · ${x.emails.split(",").filter(Boolean).length} guest(s)${+x.active?"":" · paused"}</small></div>`).join("")+`<div class="src" data-sr="0" style="display:flex;align-items:center;color:var(--pri)">＋ New weekly email</div>`}
function srForm(id){srCur=id;const x=srItems.find(i=>i.id==id)||{id:0,name:"",ws_ids:"",emails:"",dow:4,hhmm:900,active:1},on=new Set(String(x.ws_ids||"").split(",").filter(Boolean));
 $("#srf").hidden=false;$("#srah").innerHTML=srAhOpts.map(n=>`<option value="${n}">${n===1?"1 day":n+" days"}</option>`).join("");$("#srah").value=String(srAhOpts.includes(+x.ahead)?+x.ahead:3);srOrd=String(x.grp_order||"").split("|").filter(Boolean);$("#srn").value=x.name;$("#sre").value=String(x.emails||"").replace(/,\s*/g,"\n");$("#srd").value=x.dow;$("#srt").value=x.hhmm;$("#sra").checked=!!+x.active;
 srEG=new Set(String(x.excl_g||"").split(",").filter(Boolean));srET=new Set(String(x.excl_t||"").split(",").filter(Boolean));srUsDraw();$("#srw").innerHTML=srCo.map(w=>`<label class="ck"><input type="checkbox" data-w="${w.id}" ${on.has(String(w.id))?"checked":""}> ${esc(w.name)}</label>`).join("");$("#srdel").hidden=!x.id;$("#srsn").hidden=!x.id;srList();srPrev()}
const srEm=()=>String($("#sre").value).split(/[\s,;]+/).map(x=>x.trim().toLowerCase()).filter(Boolean);
function srUsDraw(){const on=new Set(srEm());$("#sru").innerHTML=srUs.map(u=>{const m=String(u.email||"").trim().toLowerCase();return m?`<label class="ck"><input type="checkbox" data-u="${ea(m)}" ${on.has(m)?"checked":""}> ${esc(u.name)}</label>`:`<label class="ck" style="opacity:.45;cursor:not-allowed" title="No email in this user's details. Add one in Manage users."><input type="checkbox" disabled> ${esc(u.name)}</label>`}).join("")||"<small>No users</small>"}
const srWs=()=>[...document.querySelectorAll("#srw [data-w]:checked")].map(c=>+c.dataset.w);
const SRL={done:"Completed",going:"Ongoing",soon:"Starting soon"};
function srTreeDraw(){const m=new Map();for(const t of srTree){const k=t.ws_id+"|"+t.gid;if(!m.has(k))m.set(k,{c:t.company,g:t.grp,gid:t.gid,r:[]});m.get(k).r.push(t)}
 const multi=new Set(srTree.map(t=>t.ws_id)).size>1;let off=0;
 $("#srtree").innerHTML=[...m.values()].map(g=>{const gx=srEG.has(String(g.gid));return `<div><label style="font-weight:700;display:flex;gap:6px;align-items:center"><input type="checkbox" data-xg="${g.gid}" ${gx?"":"checked"}> ${esc(g.g)}${multi?` <small style="color:var(--mut);font-weight:400">${esc(g.c)}</small>`:""} <small style="color:var(--mut);font-weight:400">${g.r.length}</small></label>`+g.r.map(t=>{const x=gx||srET.has(String(t.id));if(x)off++;return `<label style="display:flex;gap:6px;align-items:center;padding-left:22px;${gx?"opacity:.5":""}"><input type="checkbox" data-xt="${t.id}" ${gx?"disabled":""} ${x?"":"checked"}> <span>${esc((t.order_no?t.order_no+" · ":"")+t.title)}</span> <small style="color:var(--mut)">${SRL[t.sec]}</small></label>`}).join("")+`</div>`}).join("")||"<small>No tasks in this report right now.</small>";
 $("#srtc").textContent=off?"("+off+" left out)":""}
function srOrdDraw(){const L=srOrd.length?srOrd.filter(x=>srTy.includes(x)).concat(srTy.filter(x=>!srOrd.includes(x))):srTy;
 $("#srol").innerHTML=L.map((t,i)=>`<div style="display:flex;align-items:center;gap:8px;padding:4px 0;border-top:${i?"1px dashed var(--line)":"0"}"><b style="flex:1;font-size:13px">${esc(t)}</b><button type="button" data-mv="${i}|-1" ${i?"":"disabled"} title="Move up">▲</button><button type="button" data-mv="${i}|1" ${i<L.length-1?"":"disabled"} title="Move down">▼</button></div>`).join("")||"<small>No groups in this report right now.</small>";
 $("#srotc").textContent=srOrd.length?"(custom order)":"(automatic)";srTy=L}
async function srPrev(){$("#srprev").innerHTML="<p>Loading…</p>";try{const r=await api("sr_preview",{ws:srWs(),exg:[...srEG],ext:[...srET],ahead:+$("#srah").value||3,order:srOrd});srTree=r.tree||[];srTy=r.types||[];srTreeDraw();srOrdDraw();$("#srahn").textContent=r.next?"up to "+new Date(r.next+"T00:00:00").toLocaleDateString(undefined,{weekday:"short",day:"numeric",month:"short"}):"";$("#srprev").innerHTML=r.html;$("#srpn").textContent=r.n[0]+" completed · "+r.n[1]+" ongoing · "+r.n[2]+" starting in the next "+($("#srah").value||3)+" day(s)"}catch(e){$("#srprev").innerHTML="";alert(e.message)}}
async function srLoad(keep){try{const r=await api("sr_list",{});srItems=r.items;srCo=r.companies;srUs=r.users||[];$("#srms").textContent=r.ms_from?"✉ Sending through Microsoft 365 as "+r.ms_from:"✉ Sending through the web server's mail (not Microsoft 365)";if(keep===undefined)keep=srItems[0]?srItems[0].id:0;srList();srForm(keep)}catch(e){alert(e.message)}}
$("#srt").innerHTML=Array.from({length:48},(_,i)=>`<option value="${i*30}">${srTm(i*30)}</option>`).join("");
$("#srm").onclick=e=>{e.preventDefault();$("#umenu").hidden=true;$("#srdg").showModal();srLoad()};
$("#srx").onclick=()=>$("#srdg").close();$("#srpr").onclick=srPrev;
$("#srl").addEventListener("click",e=>{const c=e.target.closest("[data-sr]");if(c)srForm(+c.dataset.sr)});
$("#srw").addEventListener("change",srPrev);$("#srah").addEventListener("change",srPrev);
$("#srol").addEventListener("click",e=>{const b=e.target.closest("[data-mv]");if(!b)return;const [i,d]=b.dataset.mv.split("|").map(Number),L=srTy.slice(),j=i+d;if(j<0||j>=L.length)return;[L[i],L[j]]=[L[j],L[i]];srOrd=L;srPrev()});
$("#srorst").onclick=()=>{srOrd=[];srPrev()};
$("#srtree").addEventListener("change",e=>{const c=e.target;if(c.dataset.xg){c.checked?srEG.delete(c.dataset.xg):srEG.add(c.dataset.xg)}else if(c.dataset.xt){c.checked?srET.delete(c.dataset.xt):srET.add(c.dataset.xt)}else return;srPrev()});
$("#srta").onclick=()=>{srEG.clear();srET.clear();srPrev()};
$("#sru").addEventListener("change",e=>{const c=e.target.closest("[data-u]");if(!c)return;const m=c.dataset.u;let l=srEm().filter(x=>x!==m);if(c.checked)l.push(m);$("#sre").value=l.join("\n")});
$("#sre").addEventListener("input",srUsDraw);
$("#srsv").onclick=async()=>{try{await api("sr_save",{id:srCur,ahead:+$("#srah").value||3,order:srOrd,exg:[...srEG],ext:[...srET],name:$("#srn").value,ws:srWs(),emails:$("#sre").value,dow:+$("#srd").value,hhmm:+$("#srt").value,active:$("#sra").checked?1:0});const r=await api("sr_list",{});srItems=r.items;const last=srCur||Math.max(...srItems.map(i=>+i.id));srCur=last;srList();srForm(last)}catch(e){alert(e.message)}};
$("#srdel").onclick=async()=>{if(!confirm("Delete this weekly email?"))return;try{await api("sr_del",{id:srCur});await srLoad(0)}catch(e){alert(e.message)}};
$("#srsn").onclick=async()=>{const b=$("#srsn"),st=$("#srst");b.disabled=true;st.style.color="var(--mut)";st.textContent="Saving and sending…";try{await api("sr_save",{id:srCur,ahead:+$("#srah").value||3,order:srOrd,exg:[...srEG],ext:[...srET],name:$("#srn").value,ws:srWs(),emails:$("#sre").value,dow:+$("#srd").value,hhmm:+$("#srt").value,active:$("#sra").checked?1:0}).then(v=>{if(v.id)srCur=+v.id});const r=await api("sr_send",{id:srCur});srLoad(srCur).then(()=>{const st2=$("#srst");if(r.err){st2.style.color="#dc2626";st2.textContent="Microsoft 365 did not send it. "+r.err+" (the web server's own mail was used instead, "+r.sent+" address(es))."}else{st2.style.color="#16a34a";st2.textContent="Handed over for "+r.sent+" address(es) at "+new Date().toLocaleTimeString()+". Check the inbox and spam folder."}});st.style.color=r.err?"#dc2626":"#16a34a";st.textContent=r.err?"Microsoft 365 did not send it. "+r.err:"Handed over for "+r.sent+" address(es) at "+new Date().toLocaleTimeString()+". Check the inbox and spam folder."}catch(e){st.style.color="#dc2626";st.textContent="Not sent: "+e.message}finally{b.disabled=false}};
// ---- Admin hours report ----
let hrRows=[],hrOpt=null,hrSel={u:new Set(),c:new Set(),g:new Set()},hrGrid=[];
const hrWk=d=>{const x=new Date(d+"T00:00:00Z"),w=(x.getUTCDay()+6)%7;x.setUTCDate(x.getUTCDate()-w);return x.toISOString().slice(0,10)};
const hrKey={person:r=>r.person,company:r=>r.company,grp:r=>r.grp+(r.sub?" › "+r.sub:""),task:r=>(r.order_no?r.order_no+" · ":"")+(r.title||"(deleted task)"),customer:r=>r.customer||"(no customer)",day:r=>r.work_date,week:r=>"Week of "+hrWk(r.work_date),month:r=>r.work_date.slice(0,7)};
function hrSum(){const f=(id,n,all)=>{const k=hrSel[id].size;$("#"+{u:"hru",c:"hrc",g:"hrg"}[id]+" summary").textContent=n+": "+(k?k+" selected":"All")+" ▾"};f("u","People");f("c","Companies");f("g","Groups")}
function hrPanels(){if(!hrOpt)return;
 const mk=(id,items,fn)=>{const el=$("#"+{u:"hru",c:"hrc",g:"hrg"}[id]+" .hrp");el.innerHTML=`<label><input type="checkbox" data-all="${id}"> <b>All</b></label>`+items.map(fn).join("");el.querySelectorAll("[data-v]").forEach(c=>c.checked=hrSel[id].has(c.dataset.v))};
 mk("u",hrOpt.users,u=>`<label><input type="checkbox" data-k="u" data-v="${u.id}"> ${esc(u.name)}</label>`);
 mk("c",hrOpt.companies,w=>`<label><input type="checkbox" data-k="c" data-v="${w.id}"> ${esc(w.name)}</label>`);
 const cs=hrSel.c.size?hrOpt.groups.filter(g=>hrSel.c.has(String(g.ws_id))):hrOpt.groups,wn=Object.fromEntries(hrOpt.companies.map(w=>[w.id,w.name]));
 mk("g",cs,g=>`<label><input type="checkbox" data-k="g" data-v="${g.id}"> ${esc(g.name)} <small style="color:var(--mut)">${esc(wn[g.ws_id]||"")}</small></label>`);hrSum()}
async function hrLoad(){const o=$("#hrout");o.innerHTML="<p>Loading…</p>";
 try{const r=await api("hrs_report",{from:$("#hrf").value,to:$("#hrt").value,users:[...hrSel.u],ws:[...hrSel.c],groups:[...hrSel.g],kind:$("#hrk").value,text:$("#hrq").value});hrRows=r.rows;
  if(!hrOpt){hrOpt=r;hrPanels()}else{hrOpt=r;hrPanels()}
  hrDraw(r.cut)}catch(e){o.innerHTML="";alert(e.message)}}
function hrDraw(cut){const a=$("#hr1").value,b=$("#hr2").value,det=$("#hrd").checked,F=n=>(Math.round(n*100)/100).toFixed(2),o=$("#hrout"),R=hrRows;
 const tot=R.reduce((s,r)=>s+r.hours,0),ot=R.filter(r=>r.kind==="OT").reduce((s,r)=>s+r.hours,0),ppl=new Set(R.map(r=>r.user_id)).size,tk=new Set(R.map(r=>r.task_id)).size;
 $("#hrs").innerHTML=`<div><b>${F(tot)}</b>Total hours</div><div><b>${F(tot-ot)}</b>Regular</div><div><b>${F(ot)}</b>Overtime</div><div><b>${R.length}</b>Entries</div><div><b>${ppl}</b>People</div><div><b>${tk}</b>Tasks</div>`;
 const grp=(rows,fn)=>{const m=new Map();for(const r of rows){const k=fn(r);if(!m.has(k))m.set(k,[]);m.get(k).push(r)}return[...m.entries()].sort((x,y)=>/^(day|week|month)$/.test(a)&&fn===hrKey[a]?(x[0]<y[0]?-1:1):y[1].reduce((s,r)=>s+r.hours,0)-x[1].reduce((s,r)=>s+r.hours,0))};
 const sums=rows=>{let r=0,t=0;for(const x of rows){if(x.kind==="OT")t+=x.hours;else r+=x.hours}return[r,t]};
 const cell=(rows)=>{const[r,t]=sums(rows);return`<td class="n">${F(r)}</td><td class="n">${F(t)}</td><td class="n"><b>${F(r+t)}</b></td><td class="n">${rows.length}</td>`};
 hrGrid=[["Group","Detail","Regular","OT","Total","Entries"]];let H=`<table><tr><th>${esc($("#hr1").selectedOptions[0].text)}${b?" › "+esc($("#hr2").selectedOptions[0].text):""}</th><th class="n">Regular</th><th class="n">OT</th><th class="n">Total</th><th class="n">Entries</th></tr>`;
 const ent=rows=>rows.map(r=>`<tr class="ld"><td>${esc(r.work_date)} · ${esc(r.person)} · ${esc((r.order_no?r.order_no+" ":"")+(r.title||""))}${r.company?" · "+esc(r.company):""}${r.note?" — "+esc(r.note):""}</td><td class="n">${r.kind==="OT"?"":F(r.hours)}</td><td class="n">${r.kind==="OT"?F(r.hours):""}</td><td class="n">${F(r.hours)}</td><td></td></tr>`).join("");
 for(const [k1,r1] of grp(R,hrKey[a])){H+=`<tr class="l1"><td>${esc(k1)}</td>${cell(r1)}</tr>`;const[x,y]=sums(r1);hrGrid.push([k1,"",x,y,x+y,r1.length]);
  if(b)for(const [k2,r2] of grp(r1,hrKey[b])){H+=`<tr class="l2"><td>${esc(k2)}</td>${cell(r2)}</tr>`;const[p,q]=sums(r2);hrGrid.push([k1,k2,p,q,p+q,r2.length]);if(det)H+=ent(r2)}
  else if(det)H+=ent(r1)}
 const[tr,tt]=sums(R);H+=`<tr class="tt"><td>Total</td>${cell(R)}</tr></table>`;hrGrid.push(["Total","",tr,tt,tr+tt,R.length]);
 o.innerHTML=(R.length?H:"<p>No hours match these filters.</p>")+(cut?`<p style="color:#dc2626">Too many entries: showing the first 20,000. Narrow the dates or filters.</p>`:"")}
let hrT=0;const hrSoon=()=>{clearTimeout(hrT);hrT=setTimeout(hrLoad,450)};
function hrRange(k){const t=ltoday(),w=hrWk(t),d=new Date(t+"T00:00:00Z");let a=t,b=t;
 if(k==="week"){a=w;b=isoAdd(w,6)}else if(k==="lweek"){a=isoAdd(w,-7);b=isoAdd(w,-1)}else if(k==="month"){a=t.slice(0,8)+"01";b=t}else if(k==="lmonth"){const e=new Date(Date.UTC(d.getUTCFullYear(),d.getUTCMonth(),0));b=e.toISOString().slice(0,10);a=b.slice(0,8)+"01"}else if(k==="30"){a=isoAdd(t,-29)}
 $("#hrf").value=a;$("#hrt").value=b;hrLoad()}
$("#hrm").onclick=e=>{e.preventDefault();$("#umenu").hidden=true;$("#hrdg").showModal();hrOpt=null;hrSel={u:new Set(),c:new Set(),g:new Set()};if(!$("#hrf").value)hrRange("month");else hrLoad()};
$("#hrx").onclick=()=>$("#hrdg").close();$("#hrgo").onclick=hrLoad;$("#hrq").addEventListener("input",hrSoon);
$("#hrdg").addEventListener("click",e=>{const b=e.target.closest("[data-hrr]");if(b)hrRange(b.dataset.hrr)});
$("#hrdg").addEventListener("change",e=>{const c=e.target;if(c.dataset.all){hrSel[c.dataset.all].clear();hrPanels();hrSoon();return}
 if(c.dataset.k){const s=hrSel[c.dataset.k];c.checked?s.add(c.dataset.v):s.delete(c.dataset.v);if(c.dataset.k==="c"){const ok=new Set(hrOpt.groups.filter(g=>!hrSel.c.size||hrSel.c.has(String(g.ws_id))).map(g=>String(g.id)));for(const x of[...hrSel.g])if(!ok.has(x))hrSel.g.delete(x)}hrPanels();hrSoon();return}
 if(c.id==="hrk"||c.id==="hrf"||c.id==="hrt"){hrSoon();return}
 if(c.id==="hr1"||c.id==="hr2"||c.id==="hrd")hrDraw()});
document.addEventListener("click",e=>{if(!e.target.closest("#hrdg .hrf details"))document.querySelectorAll("#hrdg .hrf details[open]").forEach(d=>d.open=false)});
$("#hrcsv").onclick=()=>{if(!hrRows.length)return alert("Nothing to download");const head=["Date","Person","Company","Group","Sub-group","Order No.","Task","Customer","Drawing type","Rev","Type","Hours","Note"];
 const rows=[head].concat(hrRows.map(r=>[r.work_date,r.person,r.company,r.grp,r.sub,r.order_no,r.title,r.customer,r.drawing_type,r.rev,r.kind,r.hours,r.note]));
 const a=document.createElement("a");a.href=URL.createObjectURL(new Blob(["﻿"+rows.map(r=>r.map(csvCell).join(",")).join("\r\n")],{type:"text/csv;charset=utf-8"}));a.download="hours_report_"+$("#hrf").value+"_to_"+$("#hrt").value+".csv";document.body.appendChild(a);a.click();setTimeout(()=>{URL.revokeObjectURL(a.href);a.remove()},500)};
$("#hrxl").onclick=()=>{if(!hrRows.length)return alert("Nothing to download");const bd="border:1px solid #999;padding:2px 6px;";
 let H=`<table style="border-collapse:collapse;font-family:Calibri,Arial;font-size:11pt"><tr><td colspan="6" style="background:#1e3a8a;color:#fff;font-weight:bold">Hours report ${esc($("#hrf").value)} to ${esc($("#hrt").value)}</td></tr><tr style="font-weight:bold;background:#cfe2f3">${hrGrid[0].map(x=>`<td style="${bd}">${esc(x)}</td>`).join("")}</tr>`;
 H+=hrGrid.slice(1).map(r=>`<tr>${r.map((x,i)=>`<td style="${bd}${i>=2?"text-align:right;":""}${r[0]==="Total"?"font-weight:bold;":""}">${typeof x==="number"?(Math.round(x*100)/100).toFixed(2):esc(x)}</td>`).join("")}</tr>`).join("")+`</table>`;
 H+=`<table style="border-collapse:collapse;font-family:Calibri,Arial;font-size:11pt;margin-top:12px"><tr style="font-weight:bold;background:#cfe2f3">${["Date","Person","Company","Group","Order No.","Task","Customer","Type","Hours","Note"].map(x=>`<td style="${bd}">${x}</td>`).join("")}</tr>${hrRows.map(r=>`<tr>${[r.work_date,r.person,r.company,r.grp+(r.sub?" › "+r.sub:""),r.order_no,r.title,r.customer,r.kind,r.hours.toFixed(2),r.note].map(x=>`<td style="${bd}">${esc(x)}</td>`).join("")}</tr>`).join("")}</table>`;
 const box=document.createElement("div");box.className="pg pgwl";box.style.cssText="position:absolute;left:-12000px;top:0;width:1250px;background:#fff";box.innerHTML=H;document.body.appendChild(box);
 try{XL.save(XL.book([box],["Hours report"]),"hours_report_"+$("#hrf").value+"_to_"+$("#hrt").value+".xlsx")}catch(e){alert("Could not build the Excel file: "+e.message)}finally{box.remove()}};
$("#abk").onclick=e=>{e.preventDefault();$("#umenu").hidden=true;$("#bkdlg").showModal();bkLoad()};
$("#bkx").onclick=()=>$("#bkdlg").close();$("#wln").onclick=()=>bkRun(false,true);$("#wlm").onclick=()=>bkRun(true,true);$("#bkn").onclick=()=>bkRun(false);$("#bkm").onclick=()=>bkRun(true);
$("#bkdlg").addEventListener("click",async e=>{const f=e.target.dataset.bkd;if(!f)return;try{const r=await fetch("api.php?a=bk_get&f="+encodeURIComponent(f),{headers:{"X-Requested-With":"fetch"}});if(!r.ok||(r.headers.get("content-type")||"").includes("json")){const j=await r.json().catch(()=>({}));throw new Error(j.error||"Download failed")}const u=URL.createObjectURL(await r.blob()),a=document.createElement("a");a.href=u;a.download=f;document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(u),2000)}catch(err){alert(err.message)}});
let lgInit=false;
async function lgLoad(){try{const r=await api("log_list",{kind:$("#lgk").value,user:$("#lgu").value,ws:$("#lgw").value,from:$("#lgf").value,to:$("#lgt").value,limit:300});
  if(!lgInit){lgInit=true;$("#lgw").innerHTML='<option value="">All companies</option>'+r.ws.map(w=>`<option value="${w.id}">${esc(w.name)}</option>`).join("");$("#lgu").innerHTML='<option value="">Everyone</option>'+r.seen.map(u=>`<option value="${u.id}">${esc(u.name)}${u.role==="guest"?" (guest)":""}</option>`).join("")}
  $("#lgs").innerHTML=`<div class="acts" style="flex-wrap:wrap;margin:0 0 8px;gap:6px"><span class="stat" style="--pc:#4f46e5"><b>${r.today}</b><span>active today</span></span><span class="stat" style="--pc:#dc2626"><b>${r.fails}</b><span>failed sign-ins (24h)</span></span></div><div style="font-size:12px;color:var(--mut);margin-bottom:6px">Last seen: ${r.seen.map(u=>`<span style="white-space:nowrap;margin-right:10px"><b style="color:var(--ink)">${esc(u.name)}</b>${u.role==="guest"?" (guest)":""} ${u.last_seen?esc(fmtT(u.last_seen)):"never"}</span>`).join("")}</div>`;
  $("#lgl").innerHTML=r.rows.length?`<table class="tlt"><tr><th>When</th><th>Who</th><th>What</th><th>Details</th><th>Company</th><th>Where from</th></tr>${r.rows.map(l=>`<tr${l.kind==="fail"?' style="color:#dc2626"':""}><td style="white-space:nowrap">${esc(fmtT(l.created_at))}</td><td>${esc(l.name||l.username||"?")}</td><td>${esc(LGA[l.action]||l.action)}</td><td>${esc(l.detail||"")}</td><td>${esc(l.ws||"")}</td><td style="white-space:nowrap" title="${ea(l.ua)}">${esc(l.ip)} <small style="color:var(--mut)">${esc(lgUA(l.ua))}</small></td></tr>`).join("")}</table>`:'<div class="empty">Nothing recorded for this filter yet</div>'}catch(e){$("#lgl").innerHTML='<div class="empty">'+esc(e.message)+'</div>'}}
$("#alg").onclick=e=>{e.preventDefault();$("#umenu").hidden=true;$("#lgdlg").showModal();lgLoad()};
$("#lgx").onclick=()=>$("#lgdlg").close();$("#lgr").onclick=lgLoad;for(const i of ["lgk","lgu","lgw","lgf","lgt"])$("#"+i).addEventListener("change",lgLoad);
let edId=0;
$("#eec").onclick=()=>$("#uedlg").close();
$("#ees").onclick=async()=>{try{await api("user_edit",{id:edId,name:$("#een").value,username:$("#eeu").value,email:$("#eee").value});$("#uedlg").close();await load(true);renderUsers()}catch(e){alert(e.message)}};
$("#tm").onclick=()=>{renderUsers();$("#tdlg").showModal()};
$("#tc").onclick=()=>$("#tdlg").close();
$("#tnew").onclick=()=>{for(const i of ["un","uu","ue","up"]){const el=$("#"+i);el.value="";el.readOnly=true;if(i==="up")el.type="password"}$("#ur").value="member";$("#udlg").showModal()};
for(const i of ["un","uu","ue","up"])$("#"+i).addEventListener("focus",e=>{e.target.readOnly=false});
$("#uc").onclick=()=>$("#udlg").close();
$("#udlg").addEventListener("click",e=>{const ey=e.target.closest(".eye");if(ey){const i=ey.parentNode.querySelector("input");i.type=i.type==="password"?"text":"password"}});
$("#ta").onclick=async()=>{
  try{await api("user_add",{name:$("#un").value,username:$("#uu").value,password:$("#up").value,email:$("#ue").value,role:$("#ur").value});
    for(const i of ["un","uu","ue","up"])$("#"+i).value="";$("#udlg").close();await load(true);renderUsers()}catch(e){alert(e.message)}
};
$("#tlist").addEventListener("click",async e=>{
  try{
    if(e.target.dataset.rm&&confirm("Remove this user? Their tasks become unassigned.")){await api("user_del",{id:e.target.dataset.rm});await load(true);renderUsers()}
    if(e.target.dataset.ed){const u=allUsers.find(x=>x.id==e.target.dataset.ed);if(u){edId=u.id;$("#een").value=u.name||"";$("#eeu").value=u.username||"";$("#eee").value=u.email||"";$("#uedlg").showModal()}return}
    if(e.target.dataset.pw){const inp=$('#tlist input[data-np="'+e.target.dataset.pw+'"]'),p=inp.value;if(p.length<8){alert("Type a new password of 8+ characters first.");return}await api("user_pass",{id:e.target.dataset.pw,password:p});inp.value="";alert("Password changed.")}
    const ey=e.target.closest(".eye");if(ey){const i=ey.parentNode.querySelector("input");i.type=i.type==="password"?"text":"password"}
  }catch(err){alert(err.message)}
});
$("#tlist").addEventListener("change",async e=>{if(!e.target.dataset.role)return;try{await api("user_role",{id:e.target.dataset.role,role:e.target.value});await load(true);renderUsers()}catch(err){alert(err.message);await load(true);renderUsers()}});

// ---- Morning welcome card: stays (centred) until the person says "Got it" ----
(()=>{const dlg=$("#mcd");if(!dlg)return;let D=null,busy=false,warned=false;
 const apiw=async(a,d,w)=>{const r=await fetch("api.php?a="+a+"&w="+(w||W),{method:"POST",headers:{"Content-Type":"application/json","X-Requested-With":"fetch"},body:JSON.stringify(d||{})});if(r.status===401){location="login.php";throw new Error("auth")}const j=await r.json();if(j.error)throw new Error(j.error);return j};
 const fdate=d=>new Date(d+"T00:00:00").toLocaleDateString(undefined,{weekday:"short",day:"numeric",month:"short"}),fh=n=>(Math.round(n*100)/100).toString();
 const meta=t=>[t.order_no,t.customer,t.drawing_type].filter(Boolean).map(esc).join(" · ");
 const dueChip=(t,td)=>t.end_date?`<span class="mcd2${t.end_date<td?" late":""}">${t.end_date===td?"Due today":"Due "+esc(fdate(t.end_date))}</span>`:"";
 function render(){
  const td=ltoday(),h=new Date().getHours(),greet=h<12?"Good morning":h<17?"Good afternoon":"Good evening",first=String(D.name||"").split(" ")[0]||"there";
  const nm=(w)=>esc(D.ws[w]||"");
  let hc="";
  if(D.hours){const m=Math.max(0,D.hours.need-D.hours.h),ok=m<.01,dd=fdate(D.hours.date),opts=[].concat(D.today,D.due,D.overdue);
   if(ok)hc=`<div class="mcc ok"><h3><i>✓</i>Hours for ${esc(dd)}</h3><div style="font-size:14px">You logged <b>${fh(D.hours.h)} h</b>. All good, thank you!</div></div>`;
   else hc=`<div class="mcc ${D.hours.h?"warn":"bad"}"><h3><i>⏱</i>Hours for ${esc(dd)}</h3><div style="font-size:14px">${D.hours.h?`You logged <b>${fh(D.hours.h)} h</b>, <b>${fh(m)} h</b> short of ${fh(D.hours.need)} h.`:`Nothing was logged. Please add your hours for this day.`}</div>
    ${opts.length?`<div class="mcf"><select id="mcT">${opts.map(t=>`<option value="${t.id}" data-w="${t.ws_id}">${esc((t.order_no?t.order_no+" · ":"")+t.title)} (${esc(D.ws[t.ws_id]||"")})</option>`).join("")}</select><input id="mcH" type="number" step="0.25" min="0.25" max="24" value="${fh(m)}"><button class="p" id="mcA" type="button">Add hours</button></div>`:`<div style="font-size:12.5px;color:var(--mut);margin-top:6px">You have no open tasks to log against. You can add hours from the task itself.</div>`}</div>`}
  const stBtns=t=>{const L=D.st[t.ws_id]||[];return `<div class="mcst" data-t="${t.id}" data-w="${t.ws_id}">${L.map(x=>`<button type="button" data-k="${esc(x.k)}" class="${x.k===t.status?"on":""}" style="--pc:${esc(x.c||"#64748b")}">${esc(x.n)}</button>`).join("")}${L.some(x=>x.k==="done")?"":`<button type="button" data-k="done" style="--pc:#16a34a">Done</button>`}</div>`};
  const dueHtml=D.due.length?`<div class="mcc"><h3><i>📅</i>Due on ${esc(fdate(D.lw))}${D.lw!==D.due[D.due.length-1].end_date?" and after":""}: where do they stand?</h3>${D.due.map(t=>`<div class="mcr"><div style="min-width:0;flex:1"><b>${esc(t.title)}</b><small>${meta(t)}${meta(t)?" · ":""}${nm(t.ws_id)}</small>${stBtns(t)}</div>${dueChip(t,td)}</div>`).join("")}</div>`:"";
  const todayHtml=`<div class="mcc"><h3><i>🎯</i>On your plate today <em style="font-style:normal;color:var(--mut);font-weight:500">${D.today.length}</em></h3>${D.today.length?D.today.map(t=>`<a class="mcr" style="text-decoration:none;color:inherit" href="index.php?w=${t.ws_id}&t=${t.id}"><div style="min-width:0"><b>${esc(t.title)}</b><small>${meta(t)}${meta(t)?" · ":""}${nm(t.ws_id)}</small></div>${dueChip(t,td)}</a>`).join(""):`<div style="font-size:14px;color:var(--mut)">Nothing is scheduled for you today. A good moment to plan ahead or help a teammate.</div>`}</div>`;
  const odHtml=D.overdue.length?`<div class="mcc bad"><h3><i>⚠</i>Still overdue <em style="font-style:normal;color:var(--mut);font-weight:500">${D.overdue.length}</em></h3>${D.overdue.slice(0,6).map(t=>`<a class="mcr" style="text-decoration:none;color:inherit" href="index.php?w=${t.ws_id}&t=${t.id}"><div style="min-width:0"><b>${esc(t.title)}</b><small>${meta(t)}${meta(t)?" · ":""}${nm(t.ws_id)}</small></div>${dueChip(t,td)}</a>`).join("")}${D.overdue.length>6?`<small style="color:var(--mut)">+ ${D.overdue.length-6} more</small>`:""}</div>`:"";
  dlg.innerHTML=`<div class="mch"><span class="mcs">${h<12?"☀️":h<17?"🌤️":"🌙"}</span><small>${esc(new Date().toLocaleDateString(undefined,{weekday:"long",day:"numeric",month:"long"}))}</small><h2>${greet}, ${esc(first)}!</h2><p>Here is a quick look at where things stand before you start.</p></div><div class="mcb">${hc}${dueHtml}${todayHtml}${odHtml}</div><div class="mcft"><span>This card stays until you confirm. It comes back tomorrow morning.</span><button class="mcgo" id="mcG" type="button">Got it, let's start my day</button></div>`}
 async function fetchData(){D=await api("morning_card",{today:ltoday()});return D}
 async function open(){if(busy)return;busy=true;try{await fetchData();if(!D.show)return;render();if(!dlg.open)dlg.showModal()}catch(e){}finally{busy=false}}
 dlg.addEventListener("cancel",e=>e.preventDefault());
 dlg.addEventListener("click",async e=>{
  const g=e.target.closest("#mcG");if(g){g.disabled=true;try{await api("morning_ack",{today:ltoday()});dlg.close();load(true)}catch(x){g.disabled=false;alert(x.message)}return}
  const a=e.target.closest("#mcA");if(a){const sel=$("#mcT"),o=sel.selectedOptions[0],hv=parseFloat($("#mcH").value);if(!(hv>0&&hv<=24)){alert("Enter hours between 0.25 and 24");return}a.disabled=true;
   try{await apiw("tl_add",{task_id:+sel.value,date:D.hours.date,hours:hv,kind:"R"},o&&o.dataset.w);const sc=dlg.querySelector(".mcb").scrollTop;await fetchData();render();dlg.querySelector(".mcb").scrollTop=sc}catch(x){a.disabled=false;alert(x.message)}return}
  const b=e.target.closest(".mcst button");if(b){const bx=b.parentNode,k=b.dataset.k,tid=+bx.dataset.t,w=+bx.dataset.w;
   try{await apiw("field",{id:tid,f:"status",v:k},w);const t=D.due.find(x=>x.id==tid);if(t)t.status=k;bx.querySelectorAll("button").forEach(x=>x.classList.toggle("on",x===b));b.insertAdjacentHTML("afterend",'<span class="mcd2" style="background:#d7f3df;color:#146c2e">Saved ✓</span>');setTimeout(()=>{const z=bx.querySelector("span.mcd2");z&&z.remove()},1800);load(true)}catch(x){alert(x.message)}}
 });
 let tries=0;const kick=()=>{if(me&&me.role){if(me.role!=="guest")open();return}if(++tries<40)setTimeout(kick,500)};setTimeout(kick,1200);
})();
load(true);
const BUILD=280;
$("#bld").textContent="Build "+BUILD;$("#bld").onclick=()=>location.reload();$("#ubl").textContent="↻ Build "+BUILD;$("#ubl").onclick=e=>{e.preventDefault();location.reload()};
function chkBuild(b){const e=$("#bld"),u=$("#ubl");if(u){if(b&&+b!==BUILD){u.textContent="↻ Build "+b+" ready · tap to update";u.style.background="#fff4d6";u.style.color="#7a5200"}else{u.textContent="↻ Build "+BUILD;u.style.background="";u.style.color=""}}if(b&&+b!==BUILD){e.textContent="↻ Build "+b+" ready · tap to update";e.style.background="#fff4d6";e.style.color="#7a5200";e.style.opacity=1}else{e.textContent="Build "+BUILD;e.style.background="";e.style.color="";e.style.opacity=.8}}
function setLive(ok){const l=$("#live");l.innerHTML=`<span style="color:${ok?"#22c55e":"#9ca3af"};text-shadow:${ok?"0 0 6px #22c55e":"none"}">●</span> ${ok?"Live":"Offline"}`;l.style.color=""}
if(window.tfDesk){document.documentElement.classList.add("indesk");try{tfDesk.onBell(()=>fOpen())}catch(e){}
  const dsk=()=>{try{const b=$("#bld");tfDesk.status({ok:/Live/.test($("#live").textContent),build:b.textContent,warn:b.textContent.indexOf("ready")>-1,n:inboxN})}catch(e){}};dsk();setInterval(dsk,1500)}
// ---- Messages between team members ----
let mgPeer=0,mgLast=0,mgUsers=[],mgTeam={n:0},mgTimer=0;
const mgOpen=()=>!$("#msgw").hidden;
let mgPrev=null,mgRingT=0;
function mgBadge(n){const b=$("#mgn");b.hidden=!n;b.textContent=n;{const c=$("#rchn");c.hidden=!n;c.textContent=n}
  if(mgPrev!==null&&n>mgPrev&&$("#msgw").hidden){for(const t of [$("#mgb"),$("#rchat")]){t.classList.remove("ring");void t.offsetWidth;t.classList.add("ring")}clearTimeout(mgRingT);mgRingT=setTimeout(()=>{for(const t of [$("#mgb"),$("#rchat")])t.classList.remove("ring")},5000)}
  mgPrev=n}
async function mgUsersLoad(){try{const r=await api("msg_users",{});mgUsers=r.users;mgTeam=r.team;mgUsersDraw()}catch(e){}}
function mgUsersDraw(){const l=[...mgUsers].sort((a,b)=>b.last-a.last||a.name.localeCompare(b.name));
  $("#mgu").innerHTML=[`<div class="mgu${mgPeer===0?" on":""}" data-mg="0"><span class="av tm">👥</span><span>Team</span>${mgTeam.n?`<b>${mgTeam.n}</b>`:""}</div>`].concat(l.map(u=>`<div class="mgu${mgPeer===u.id?" on":""}" data-mg="${u.id}">${avHtml(u.name)}<span>${esc(u.name)}</span>${u.n?`<b>${u.n}</b>`:""}</div>`)).join("");
  const u=mgUsers.find(x=>x.id===mgPeer);$("#mgt").textContent=mgPeer===0?"👥 Team — everyone can read this":(u?u.name:"")}
let mgLU=0,mgLD="";
function mgAdd(ms){const box=$("#mgm"),near=box.scrollHeight-box.scrollTop-box.clientHeight<80;
  if(mgLast===0){mgLU=0;mgLD=""}
  for(const m of ms){if(m.id<=mgLast)continue;mgLast=m.id;
    const day=String(m.created_at).slice(0,10);if(day!==mgLD){const sp=document.createElement("div");sp.className="dsep";sp.textContent=dayLbl(day);box.appendChild(sp);mgLD=day;mgLU=0}
    const key=m.mine?-1:(m.name||"?"),gp=mgLU===key;mgLU=key;
    const d=document.createElement("div");d.className="mgmsg"+(m.mine?" me":"")+(gp?" gp":"");d.innerHTML=(mgPeer===0&&!m.mine&&!gp?`<b>${esc(m.name)}</b><br>`:"")+esc(m.body)+`<small>${esc(String(m.created_at).slice(11,16))}</small>`;box.appendChild(d)}
  if(ms.length&&near||box.dataset.first==="1"){box.scrollTop=box.scrollHeight;box.dataset.first=""}}
async function mgLoad(){try{const r=await api("msg_list",{peer:mgPeer,after:mgLast});mgAdd(r.messages)}catch(e){}}
async function mgSel(p){mgPeer=p;mgLast=0;$("#mgm").innerHTML="";$("#mgm").dataset.first="1";mgUsersDraw();await mgLoad();mgUsersLoad()}
function mgStart(){mgSel(mgPeer);clearInterval(mgTimer);mgTimer=setInterval(()=>{if(document.hidden||!mgOpen())return;mgLoad();mgUsersLoad()},3000)}
(()=>{const st=$("#sidetabs"),place=()=>{const hd=document.querySelector("header");if(hd)st.style.top=Math.max(8,Math.round(hd.getBoundingClientRect().bottom+6))+"px"},chk=()=>{place();st.classList.toggle("hid",window.scrollY>10||[...document.querySelectorAll(".listwrap,#main")].some(x=>x.scrollTop>10))};
  document.addEventListener("scroll",chk,true);window.addEventListener("resize",place);place();setTimeout(place,600)})();
$("#mgb").onclick=()=>{const w=$("#msgw");w.hidden=!w.hidden;if(w.hidden){clearInterval(mgTimer);return}
  let g=null;try{g=JSON.parse(localStorage.getItem("tf_mgg")||"null")}catch(e){}if(g&&g.w>=320){w.style.left=Math.min(Math.max(0,g.l),innerWidth-120)+"px";w.style.top=Math.min(Math.max(0,g.t),innerHeight-60)+"px";w.style.width=g.w+"px";w.style.height=g.h+"px"}
  if(!g){const hd=document.querySelector("header");if(hd)w.style.top=Math.max(12,Math.round(hd.getBoundingClientRect().bottom+10))+"px"}
  mgUsersLoad().then(()=>{const first=mgUsers.find(u=>u.n);if(first&&mgPeer===0&&!mgTeam.n)mgPeer=first.id;mgStart()});$("#mgi").focus()};
$("#mgx").onclick=()=>{$("#msgw").hidden=true;clearInterval(mgTimer)};
$("#mgu").addEventListener("click",e=>{const d=e.target.closest("[data-mg]");if(d)mgSel(+d.dataset.mg)});
async function mgSend(){const t=$("#mgi").value.trim();if(!t)return;try{await api("msg_send",{peer:mgPeer,body:t});$("#mgi").value="";$("#mgi").style.height="";$("#mgm").dataset.first="1";await mgLoad();mgUsersLoad()}catch(e){alert(e.message)}}
const MGE="😀 😃 😄 😁 😆 😅 😂 🤣 😊 🙂 😉 😍 😘 😎 🤔 😐 😴 😢 😭 😡 🙏 👍 👎 👌 ✌️ 👏 🙌 💪 👀 🔥 ⭐ ✅ ❌ ⚠️ ❓ ❗ 💡 📌 📎 📅 ⏰ 📐 📏 🏗️ 🏠 📄 📁 💬 🎉 ☕ 🍽️ ❤️ 💯 🤝 👋".split(" ");
$("#mge").innerHTML=MGE.map(e=>`<span>${e}</span>`).join("");
$("#mgem").onclick=e=>{e.stopPropagation();$("#mge").hidden=!$("#mge").hidden};
$("#mge").addEventListener("click",e=>{const s=e.target.closest("span");if(!s)return;const t=$("#mgi"),a=t.selectionStart??t.value.length,b=t.selectionEnd??a;t.value=t.value.slice(0,a)+s.textContent+t.value.slice(b);const p=a+s.textContent.length;t.focus();t.setSelectionRange(p,p)});
document.addEventListener("click",e=>{if(!e.target.closest("#mge,#mgem"))$("#mge").hidden=true});
$("#mgs").onclick=mgSend;$("#mgi").addEventListener("input",e=>{const t=e.target;t.style.height="auto";t.style.height=Math.min(t.scrollHeight,120)+"px"});$("#mgi").addEventListener("keydown",e=>{if(e.key==="Enter"&&!e.shiftKey){e.preventDefault();mgSend()}});
(()=>{const s=$("#msgw");let d=null;const save=()=>{if(s.hidden)return;const r=s.getBoundingClientRect();try{localStorage.setItem("tf_mgg",JSON.stringify({l:Math.round(r.left),t:Math.round(r.top),w:Math.round(r.width),h:Math.round(r.height)}))}catch(e){}};
  s.querySelector(".mgh").addEventListener("pointerdown",e=>{if(e.target.closest("button"))return;const r=s.getBoundingClientRect();d={x:e.clientX-r.left,y:e.clientY-r.top};e.target.setPointerCapture&&e.target.setPointerCapture(e.pointerId)});
  document.addEventListener("pointermove",e=>{if(!d)return;s.style.left=Math.min(Math.max(-s.offsetWidth+120,e.clientX-d.x),innerWidth-120)+"px";s.style.top=Math.min(Math.max(0,e.clientY-d.y),innerHeight-50)+"px"});
  document.addEventListener("pointerup",()=>{if(d){d=null;save()}});new ResizeObserver(()=>{if(!s.hidden)save()}).observe(s)})();
function busy(){return document.querySelector("dialog[open]")||draft||rp||rowDrag||dragId||dragCol||rz||gdrag}
let presK="";function presUpd(l){const k=l.map(u=>u.id).join(",");if(k===presK)return;presK=k;const me0=me&&+me.id,a=l.slice().sort((x,y)=>(y.id===me0)-(x.id===me0)),sh=a.slice(0,5),more=a.length-sh.length;$("#pres").innerHTML=sh.map(u=>`<span class="pb" style="background:${avC(u.name)}" title="${ea(u.name)}${u.id===me0?" (you)":""} \u2022 online">${esc(avI(u.name))}<i></i></span>`).join("")+(more>0?`<span class="pb mo" title="${ea(a.slice(5).map(u=>u.name).join(", "))}">+${more}</span>`:"")}
async function poll(){
  if(document.hidden)return;
  try{
    const r=await api("ping");setLive(true);mgBadge(+r.msgn||0);presUpd(r.on||[]);
    if(($("#dlg").open||$("#mdlg").open)&&thrId&&r.v!==thrVer){thrVer=r.v;thrLoad(false)}
    if(r.v!==lastVer||dirty){if(busy()){dirty=true;return}dirty=false;await load()}
  }catch(e){if(e.message!=="auth")setLive(false)}
}
setInterval(poll,3000);
// ---- Desktop / browser notifications ----
const nfOn=()=>{try{return localStorage.getItem("tf_nf")!=="0"}catch(e){return true}};
const nfCan=()=>"Notification" in window;
let nfCur={cm:null,ic:null},nfBusy=false;
function nfLabel(){const el=$("#anf");if(!el)return;if(!nfCan()){el.textContent="🔔 Notifications: not supported here";return}el.textContent="🔔 Notifications: "+(nfOn()&&Notification.permission!=="denied"?"On":Notification.permission==="denied"?"Blocked":"Off")}
async function nfTick(){
  if(nfBusy||!nfCan()||!me||!me.id)return;nfBusy=true;
  try{const r=await api("notif_poll",nfCur);const first=nfCur.cm===null;nfCur={cm:r.cm,ic:r.ic};
    if(first||!nfOn()||Notification.permission!=="granted")return;
    if(document.hasFocus()&&!document.hidden)return;
    for(const it of r.items){
      const t=it.k==="chat"?(it.group?it.from+" (everyone)":it.from):(it.kind==="instruction"?"📌 "+it.from+" gave you an instruction":it.from+" sent you a message");
      const b=it.k==="chat"?it.body:it.title+": "+it.body;
      const n=new Notification(t,{body:b,tag:it.k+it.id,icon:"icon-192.png"});
      n.onclick=()=>{try{window.focus()}catch(e){}n.close();if(it.k==="chat"){try{$("#mgb").click()}catch(e){}}else location.href="index.php?w="+it.ws+"&t="+it.task};
    }
  }catch(e){}finally{nfBusy=false}
}
setInterval(nfTick,5000);setTimeout(nfTick,1500);
$("#anf").onclick=async e=>{e.preventDefault();if(!nfCan()){alert("This browser does not support notifications.");return}
  const was=nfOn()&&Notification.permission==="granted";
  if(was){try{localStorage.setItem("tf_nf","0")}catch(x){}nfLabel();return}
  try{localStorage.setItem("tf_nf","1")}catch(x){}
  if(Notification.permission==="default"){try{await Notification.requestPermission()}catch(x){}}
  if(Notification.permission==="denied")alert("Notifications are blocked for this site. Allow them in your browser's site settings (the lock icon next to the address), then turn them on here again.");
  else if(Notification.permission==="granted")new Notification("TaskFlow notifications are on",{body:"You will see new messages and task assignments here.",icon:"icon-192.png"});
  nfLabel()};
nfLabel();
document.addEventListener("visibilitychange",()=>{if(!document.hidden)poll()});
window.addEventListener("focus",poll);
document.addEventListener("close",()=>{if(dirty)poll()},true);
document.addEventListener("dragend",()=>{if(dirty)setTimeout(poll,50)});
</script></body></html>
