<?php require 'config.php';require 'remember.php'; if(empty($_SESSION['uid'])){header('Location: login.php');exit;} ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TaskFlow</title>
<style>
:root{--bg:#f4f5f9;--card:#fff;--ink:#1d2433;--mut:#6b7385;--line:#e2e5ee;--pri:#4f46e5;--priInk:#fff;--col:#eceef5;--hi:#dc2626;--lo:#16a34a;color-scheme:light}
@media (prefers-color-scheme:dark){:root{--bg:#12141c;--card:#1d212d;--ink:#e8eaf2;--mut:#98a0b5;--line:#2c3142;--pri:#818cf8;--priInk:#12141c;--col:#171a24;color-scheme:dark}}
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
.stat{cursor:pointer}.stat.on{background:color-mix(in srgb,var(--pc,#888) 42%,var(--card));border-color:var(--pc,var(--pri));box-shadow:0 0 0 1px var(--pc,var(--pri))}.ro #sm,.ro #tlb{display:none}
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
#bar{display:flex;gap:8px;align-items:center;margin:0 16px 10px;padding:8px 12px;background:var(--card);border:1px solid var(--pri);border-radius:10px}#bar[hidden]{display:none}
dialog{border:1px solid var(--line);border-radius:14px;background:var(--card);color:var(--ink);width:min(460px,92vw);padding:18px}
dialog::backdrop{background:rgba(0,0,0,.45)}
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
#smenu button[hidden]{display:none}
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
.gbar{position:absolute;height:26px;border-radius:6px;color:#fff;font-size:12px;line-height:26px;padding:0 7px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;cursor:pointer;text-shadow:0 1px 1px rgba(0,0,0,.45);box-shadow:0 1px 2px rgba(0,0,0,.25)}
.gbar:hover{filter:brightness(1.1)}.gbar.dn{opacity:.55}
.gtoday{position:absolute;top:0;bottom:0;width:2px;background:#dc2626;z-index:2;pointer-events:none}
.cc{float:right;margin:0 0 4px 8px}.ro .cc{display:none}
.ro #add,.ro #cm,.ro .rc,.ro #all{display:none}.ro .card{cursor:default}
.acts{display:flex;gap:8px;margin-top:10px;align-items:center}.empty{color:var(--mut);text-align:center;padding:24px}
.cmb{background:#e5e7eb;color:#374151;border-radius:6px;font-size:11px;line-height:1;padding:3px 5px;cursor:pointer;flex:none}.cmb.un{background:#f59e0b;color:#fff}.cmb.me{background:#dc2626;color:#fff}
#thr{margin-top:12px;border-top:1px solid var(--line);padding-top:10px}#thl{max-height:220px;overflow:auto;display:flex;flex-direction:column;gap:6px;margin-bottom:8px}
.msg{border:1px solid var(--line);border-radius:10px;padding:6px 9px;font-size:13px;white-space:pre-wrap;word-break:break-word}.msg.ins{border-color:#f59e0b;background:rgba(245,158,11,.1)}.msg.new{box-shadow:0 0 0 2px #3b82f6}.msg small{display:block;color:var(--mut);font-size:11px}.msg .x{float:right;cursor:pointer;color:var(--mut);border:0;background:none;width:auto;padding:0}
#bell{position:relative}#bell b{background:#dc2626;color:#fff;border-radius:9px;font-size:11px;padding:1px 6px;margin-left:3px}
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
#side .wl{background:color-mix(in srgb,var(--sf) 10%,transparent)}#side .wl:hover{border-color:color-mix(in srgb,var(--sf) 50%,transparent)}#side .wl.on{border-color:var(--sf);box-shadow:0 0 0 3px color-mix(in srgb,var(--sf) 28%,transparent)}
body.hdark #side .wl .wh,body.hdark #side .wl img[src^="data:"]{filter:brightness(0)}
.gbar{touch-action:none;user-select:none}.ro .gbar{touch-action:auto}.glane.gdrop{background:rgba(79,70,229,.12)}
#gtip{position:fixed;z-index:60;background:#0f2a5c;color:#fff;font-size:12px;padding:3px 8px;border-radius:6px;pointer-events:none;white-space:nowrap}
.wh{filter:brightness(0) invert(1)}
.uh,.ur2{display:grid;grid-template-columns:1.2fr 1fr 1.5fr .9fr 70px;gap:6px;align-items:center}.uh{font-size:11px;color:var(--mut);font-weight:600;margin:10px 0 4px;text-transform:uppercase}
.ur2{margin:4px 0}.ur2 input,.ur2 select{min-width:0}.pw{display:flex;gap:4px}.pw input{flex:1}.eye{padding:4px 8px;width:auto;flex:none}
.urow{display:grid;grid-template-columns:1.2fr 1fr 1.5fr .9fr 70px;gap:6px;align-items:center;border-bottom:1px solid var(--line);padding:6px 0}.urow>span{overflow:hidden;text-overflow:ellipsis}
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
#mdlg{width:min(580px,94vw)}#mdlg #thr{margin-top:0;border-top:1px solid var(--line)}#mdlg #thl{max-height:46vh;min-height:120px}
.brand{display:flex;flex-direction:column;line-height:1.05;margin-right:6px;padding-right:14px;border-right:1px solid color-mix(in srgb,var(--hfg) 28%,transparent)}.brand b{font-size:23px;letter-spacing:.2px}.brand small{font-size:9px;opacity:.75;letter-spacing:.6px;margin-top:3px;text-transform:uppercase}
header .brand{color:var(--hfg)}
#hrow th{border-right:1px solid color-mix(in srgb,var(--line) 70%,var(--mut))}#hrow th:last-child{border-right:0}
#smenu{position:fixed;right:auto;top:auto;max-height:calc(100vh - 16px);overflow:auto}
#side .sf{display:flex;flex-direction:column;gap:6px}#side .sf #setw{display:block}
#side #smenu button{background:transparent;color:var(--ink);border:0;text-align:left;padding:8px 10px}#side #smenu button:hover{background:var(--col)}
#uw{position:relative}#umenu{position:fixed;min-width:150px;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.2);padding:6px;z-index:60}#umenu[hidden]{display:none}
#umenu a{display:block;padding:8px 10px;border-radius:6px;color:var(--ink);text-decoration:none;font-size:14px}#umenu a:hover{background:var(--col)}
tr.gh th{background:color-mix(in srgb,var(--gc,#888) 22%,var(--card));border-left:8px solid var(--gc,#888);border-top:2px solid var(--gc,#888)}tr.dh.sub th{padding-left:30px;border-left:5px solid var(--gc,#888);background:color-mix(in srgb,var(--gc,#888) 8%,var(--card))}tr.gh.sub th{border-left-width:5px;background:color-mix(in srgb,var(--gc,#888) 12%,var(--card))}tr.gsp td{height:14px;padding:0;border:0;background:transparent}tr.gsp:first-child{display:none}tr.gend td{height:0;padding:0;border:0;border-bottom:3px solid var(--gc,#888)}.gcs{display:flex;gap:6px;flex-wrap:wrap;margin:6px 0}.gcs button{width:26px;height:26px;border-radius:50%;padding:0;border:2px solid transparent}.gcs button.on{border-color:var(--ink);box-shadow:0 0 0 2px var(--card) inset}.gcs button.auto{width:auto;border-radius:14px;padding:0 10px;font-size:12px}.gh.sub th{padding-left:28px}.addrow td{padding:7px 10px;border-bottom:1px solid var(--line);background:var(--card)}.addrow a{color:var(--mut);cursor:pointer;margin-right:18px;font-size:13px;text-decoration:none}.addrow a:hover{color:var(--ink)}
.hbtn{width:100%;background:transparent;border:1px solid transparent;padding:5px 6px;white-space:nowrap;color:var(--ink)}.hbtn:hover{border-color:var(--line);background:var(--card)}
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
#msgw{position:fixed;top:90px;left:calc(50vw - 20px);width:min(640px,96vw);height:min(520px,80vh);min-width:320px;min-height:260px;max-width:98vw;max-height:96vh;resize:both;background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.3);z-index:41;display:flex;flex-direction:column;overflow:hidden}#msgw[hidden]{display:none!important}.mgh{display:flex;align-items:center;padding:8px 10px;border-bottom:1px solid var(--line);cursor:move;user-select:none;touch-action:none}.mgbody{display:flex;flex:1;min-height:0}#mgu{width:170px;border-right:1px solid var(--line);overflow:auto;flex:none}.mgu{display:flex;align-items:center;gap:6px;padding:8px 10px;cursor:pointer;border-bottom:1px solid var(--line)}.mgu:hover{background:var(--col)}.mgu.on{background:var(--col);font-weight:600;box-shadow:inset 3px 0 0 var(--pri)}.mgu span{flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.mgu b{background:#dc2626;color:#fff;border-radius:9px;font-size:11px;padding:1px 6px}.mgr{flex:1;display:flex;flex-direction:column;min-width:0}.mgtt{padding:8px 12px;font-weight:600;border-bottom:1px solid var(--line)}#mgm{flex:1;overflow:auto;padding:10px;display:flex;flex-direction:column;gap:6px}.mgmsg{max-width:80%;padding:6px 10px;border-radius:12px;background:var(--col);align-self:flex-start;white-space:pre-wrap;word-break:break-word}.mgmsg.me{align-self:flex-end;background:color-mix(in srgb,var(--pri) 20%,var(--card))}.mgmsg small{display:block;color:var(--mut);font-size:11px;margin-top:2px}.mgin{display:flex;gap:6px;padding:8px;border-top:1px solid var(--line)}.mgin textarea{flex:1;resize:none;box-sizing:border-box}#mge{position:absolute;bottom:100%;left:8px;width:min(300px,90%);max-height:170px;overflow:auto;background:var(--card);border:1px solid var(--line);border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.25);padding:6px;display:flex;flex-wrap:wrap;gap:2px;z-index:2}#mge[hidden]{display:none}#mge span{font-size:20px;padding:3px 5px;cursor:pointer;border-radius:6px}#mge span:hover{background:var(--col)}@media(max-width:700px){#msgw{left:2vw!important;width:96vw!important}#mgu{width:110px}}
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
</style></head><body>
<header>
 <span class="brand"><b>TaskFlow</b><small>Powered by GURUCADD</small></span><span id="wlogo"></span><h1><span id="wname"></span></h1>
 <span class="tabs"><button id="vl" class="on">List</button><button id="vb">Board</button><button id="vg">Gantt</button></span>
 <span id="gcb" class="tabs" hidden><button data-gc="c" title="Collapse all groups">Collapse all</button><button data-gc="e" title="Expand groups, keep Completed folders collapsed">Expand</button><button data-gc="a" title="Expand groups and Completed folders">Expand all</button></span>
 <button id="dv" title="Choose which view opens first"></button>
 <input id="q" placeholder="Search tasks" style="min-width:140px">
 <select id="fs"></select><select id="fp"></select><select id="fa"></select>
 <span class="sp"></span>
 <span id="live" style="font-size:12px;color:var(--mut)">○ …</span><button id="bld" type="button" title="Version of this page. Tap to reload when a newer one is available." style="font-size:11px;padding:2px 7px;opacity:.8"></button>
 <button id="bell" title="Messages for you">🔔<b id="bn" hidden></b></button>
 <button class="p" id="add">+ New task</button>
 <span id="uw"><button id="ubtn" aria-haspopup="true"><span id="who"></span> ▾</button><div id="umenu" hidden><a href="#" id="mhm">⏱ My hours check</a><a href="login.php?logout=1">⎋ Log out</a></div></span>
</header>
<div id="sidetabs"><button id="tbtn" class="stab" style="--pc:#0891b2" title="Tools: containers, converter, wiki"><i>📐</i><span>Tools</span></button><button id="mgb" class="stab" style="--pc:#7c3aed" title="Messages between team members"><i>💬</i><span>Chat</span><b id="mgn" hidden></b></button></div>
<nav class="gtabs" id="gtabs" hidden></nav>
<div class="stats" id="stats"></div>
<aside id="side"><div class="sh"><button id="stg" title="Collapse or expand the sidebar">◀</button></div><div id="vname"></div><nav id="wlinks"></nav><div class="sf"><span id="setw"><button id="sbtn" aria-haspopup="true" title="Settings"><span class="ic">⚙</span><span class="lbl"> Settings</span></button>
  <div id="smenu" hidden>
   <button id="gm">📁 Groups</button>
   <button id="sm">🏷 Statuses</button>
   <button id="pm">🎨 Colours</button>
   <button id="cm">➕ Column</button>
   <button id="dm">✏ Drawing types</button>
   <button id="rm" hidden>🧾 Worklog &amp; invoice</button>
   <button id="ivm" hidden>📋 Invoice tracking</button>
   <button id="xm">⬇ Export to Excel</button>
   <button id="tm" hidden>👥 Users</button>
   <button id="om" hidden>⚠ Overlap rules</button>
  </div></span><button id="wm" hidden title="Manage companies and logos"><span class="ic">🗂</span><span class="lbl"> Manage</span></button></div></aside>
<div id="bar" hidden></div>
<aside id="msgw" hidden><div class="mgh"><b style="flex:1">💬 Messages</b><button id="mgx" title="Close">✕</button></div>
 <div class="mgbody"><div id="mgu"></div><div class="mgr"><div id="mgt" class="mgtt"></div><div id="mgm"></div><div class="mgin" style="position:relative"><div id="mge" hidden></div><button id="mgem" type="button" title="Emoji">😊</button><textarea id="mgi" rows="2" placeholder="Write a message… (Enter to send, Shift+Enter for a new line)" maxlength="2000"></textarea><button class="p" id="mgs">Send</button></div></div></div></aside>
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
  <div id="wk_list"><div class="two" style="grid-template-columns:1fr auto"><input id="wk_q" placeholder="Search wiki…"><button class="p" id="wk_new">+ Page</button></div><div id="wk_items"></div></div>
  <div id="wk_ed" hidden><input id="wk_t" placeholder="Title (e.g. Roof drawing rules)" maxlength="150"><textarea id="wk_b" rows="14" placeholder="Write rules, notes, sizes…"></textarea>
   <div class="acts"><button id="wk_del" class="danger">Delete</button><span class="sp"></span><button id="wk_back">Back</button><button class="p" id="wk_save">Save</button></div><small id="wk_meta" style="color:var(--mut)"></small></div>
 </div>
</aside>
<div id="pbox"></div>
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
 <div id="ovn" style="display:none;margin-top:8px;padding:8px 10px;border:1px solid #dc2626;border-radius:8px;color:#dc2626;font-size:12px;white-space:pre-line"></div>
 <label>Group</label><select id="gp"></select>
 <label>Notes</label><textarea id="nt" rows="3"></textarea>
 <div id="xf"></div>
 <div id="thr" hidden><b>💬 Discussion</b><div id="thl"></div>
  <div style="display:flex;gap:6px;margin-bottom:6px"><select id="cto"></select><select id="ckind"><option value="comment">Comment</option><option value="instruction">📌 Instruction</option></select></div>
  <textarea id="cbody" rows="2" placeholder="Write a message… (Ctrl+Enter to send)"></textarea>
  <div class="acts"><span class="sp"></span><button type="button" id="csend" class="p">Send</button></div></div>
 <div class="acts"><button id="del" style="display:none">Delete</button><button id="tlb" type="button" title="Log hours for this task">⏱ Hours</button><span class="sp"></span><button id="cx">Cancel</button><button class="p" id="sv">Save</button></div>
</dialog>

<dialog id="tdlg" style="width:min(780px,94vw)"><div class="acts" style="margin:0"><b style="flex:1">Users</b><button class="p" id="tnew">+ New user</button></div>
 <div class="uh"><span>Full name</span><span>Username</span><span>Password</span><span>User type</span><span></span></div>
 <div id="tlist"></div>
 <small style="color:var(--mut)">Saved passwords are stored scrambled, so they cannot be shown. Type a new one and press Set to change it.</small>
 <div class="acts"><span class="sp"></span><button id="tc">Close</button></div>
</dialog>
<dialog id="udlg"><b>New user</b>
 <label>Full name</label><input id="un" name="nf_fullname" autocomplete="off" readonly>
 <label>Username</label><input id="uu" name="nf_user" autocomplete="off" readonly>
 <label>Password (8+ characters)</label><span class="pw"><input id="up" name="nf_pass" type="password" autocomplete="new-password" readonly><button type="button" class="eye" title="Show / hide">👁</button></span>
 <label>User type</label><select id="ur"><option value="member">Member</option><option value="admin">Admin</option></select>
 <div class="acts"><span class="sp"></span><button id="uc">Cancel</button><button class="p" id="ta">Create user</button></div>
</dialog>

<dialog id="pdlg"><b>Top bar colours</b>
 <div id="pprev">TaskFlow – preview text</div>
 <div style="display:flex;gap:16px;align-items:center;margin-bottom:10px"><label style="display:flex;gap:8px;align-items:center;margin:0">Bar <input type="color" id="pbg" style="width:52px;height:34px;padding:2px"></label><label style="display:flex;gap:8px;align-items:center;margin:0">Text <input type="color" id="pfg" style="width:52px;height:34px;padding:2px"></label></div>
 <div id="psw" style="display:flex;gap:8px;flex-wrap:wrap"></div>
 <label style="display:flex;gap:8px;align-items:center;margin:12px 0 0;color:var(--ink)"><input type="checkbox" id="pgl"> <span>✨ Glass look <small style="color:var(--mut)">(this device only; turn off if the app feels slow)</small></span></label>
 <div class="acts"><button id="prs">Reset to default</button><span class="sp"></span><button id="pcx">Cancel</button><button class="p" id="psv">Save</button></div>
</dialog>
<dialog id="tldg"><div class="acts" style="margin:0 0 8px"><div style="flex:1;min-width:0"><b id="tlord" style="color:var(--pri)"></b><div id="tltit" style="font-weight:600;overflow:hidden;text-overflow:ellipsis"></div></div><button id="tlcl">Close</button></div>
 <div id="tll"></div>
 <div class="tlf"><input type="date" id="tld"><input type="number" id="tlh" step="0.25" min="0" max="24" placeholder="Hours"><select id="tlk" title="Regular or overtime"><option value="R">Regular</option><option value="OT">OT</option></select><input id="tln" placeholder="What did you do? (optional)" maxlength="255"><button class="p" id="tla">Add</button></div>
 <small style="color:var(--mut)">Hours are logged under the person the task is assigned to (you will be asked to assign someone if it has no assignee). Use quarter-hours, e.g. 1.5 = 1 h 30 min.</small>
</dialog>
<dialog id="mhdg"><div class="acts" style="margin:0 0 6px"><b style="flex:1">⏱ My hours check</b><button id="mhcl">Close</button></div>
 <div class="rctl"><label style="margin:0">From</label><input type="date" id="mhf"><label style="margin:0">To</label><input type="date" id="mht"><button id="mhtd">Today</button><button id="mhw">This week</button><button id="mhlw">Last week</button><button id="mh14">Last 14 days</button></div>
 <div class="rctl"><label style="margin:0">Target hours / day</label><input type="number" id="mhg" value="8" min="0" step="0.25" style="width:80px"><span id="mhdays"></span><select id="mhu" hidden></select></div>
 <div class="rctl"><label style="margin:0">Download</label><select id="mhdl"><option value="day">Today</option><option value="week">This week (Mon–Sun)</option><option value="lweek">Last week</option><option value="month">This month</option><option value="lmonth">Last month</option><option value="sel">Dates selected above</option></select><button id="mhdb" title="Save your hours as an Excel file laid out like the worklog">⬇ Excel</button></div>
 <div id="mhs"></div><div id="mhout"></div></dialog>
<dialog id="mhad"><b id="mhat">Add hours</b><div class="rctl" style="flex-direction:column;align-items:stretch"><label>Task<select id="mhtk"></select></label><div style="display:flex;gap:8px"><input type="number" id="mhah" step="0.25" min="0" max="24" placeholder="Hours"><select id="mhak" style="width:auto"><option value="R">Regular</option><option value="OT">OT</option></select></div><input id="mhan" placeholder="Note (optional)" maxlength="255"></div><div class="acts"><span class="sp"></span><button id="mhax">Cancel</button><button class="p" id="mhaok">Add</button></div></dialog>
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
<dialog id="mdlg"><div class="acts" style="margin:0 0 8px"><div style="flex:1;min-width:0"><b id="mord" style="color:var(--pri)"></b><div id="mtit" style="font-weight:600;overflow:hidden;text-overflow:ellipsis"></div></div><button id="mopen" title="Open the full task">Open task</button><button id="mcl">Close</button></div><div id="mbody"></div></dialog>
<dialog id="idlg"><b>🔔 Messages for you</b><div id="ilist"></div><div class="acts"><button id="iall">Mark all read</button><span class="sp"></span><button id="icl">Close</button></div></dialog>
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
<dialog id="cedlg"><b>Edit column</b><label>Name</label><input id="cen" maxlength="60"><div id="ceow"><label>Options (comma separated)</label><input id="ceo"></div><label style="display:flex;gap:8px;align-items:center;color:var(--ink)"><input type="checkbox" id="ceh"> <span>Hide this column on this device</span></label><small id="cet" style="color:var(--mut)"></small><div class="acts"><span class="sp"></span><button id="cex">Cancel</button><button class="p" id="cesv">Save</button></div></dialog>

<input type="file" id="wf" accept="image/png,image/jpeg,image/webp,image/svg+xml" hidden>
<dialog id="codlg"><b id="cot">Company details &amp; hourly rates</b>
 <p style="color:var(--mut);font-size:13px;margin:6px 0">Only admins can see this. Rates are used to calculate invoice amounts.</p>
 <div class="pgrid">
  <label>Company name (shown in the app)<input id="con" maxlength="60"></label><label>Name on invoice<input id="coc"></label>
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
let hoursMap={},draft=null,rowDrag=null,dtypes=[],gdrag=null,gSwallow=false,thrVer=-1,cmeta={},inboxN=0,thrId=null,thrKey='',deepT=+(new URLSearchParams(location.search).get('t')||0),deepM=new URLSearchParams(location.search).get('m'),W=+(new URLSearchParams(location.search).get("w")||(()=>{try{return localStorage.getItem("tf_w")}catch(e){return 0}})()||0),viewInit=false,lastVer=-1,dirty=false,ovl={},ovset={hand:1,one:0},ovOnly=false,groups=[],G="",SL=[],LV="",wss=[],names={},allUsers=[],access={},openWp=null;
let tasks=[],users=[],cols=[],me={},view="list",editing=null,sel=new Set(),last="",pend=false;
const $=s=>document.querySelector(s);
(function(){let c;try{const v=localStorage.getItem("tf_sc");c=v===null?innerWidth<700:v==="1"}catch(e){c=false}
  document.body.classList.toggle("sc",c);const b=document.getElementById("stg");if(b){b.textContent=c?"▶":"◀";}})();
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
    tasks=j.tasks;tasks.forEach(t=>{t.id=+t.id});ovl=j.overlaps||{};cmeta=j.cmeta||{};hoursMap=j.hours||{};try{const th=(j.me&&j.me.theme)||"";localStorage.setItem("tf_theme",th);applyTheme(th)}catch(e){}dtypes=j.dtypes||[];inboxN=+j.inbox||0;ovset=j.ovset||ovset;users=j.users;cols=j.cols||[];me=j.me;wss=j.ws||[];LV=j.level||"";SL=j.statuses||[];groups=orderGroups(j.groups||[]);try{G=localStorage.getItem("tf_g_"+W)||""}catch(e){G=""}if(G&&G!=="none"&&!groups.some(g=>g.id==G))G="";if(SL.length)ST=SL.map(x=>[x.k,x.name]);document.body.classList.toggle("ro",LV==="view");names=j.names||{};allUsers=j.allUsers||[];access=j.access||{};
    if(j.w!==W){W=j.w;if(history.replaceState)history.replaceState(null,"","index.php"+(W?"?w="+W:""))}
    
    try{localStorage.setItem("tf_w",W)}catch(e){}
    render();bellUpd();if(deepT){const t=tasks.find(x=>x.id==deepT);deepT=0;if(t){if(deepM){deepM=null;openMsg(t.id)}else openDlg(t)}}}catch(e){}
}
const cmBadge=t=>{const m=cmeta[t.id];if(!m)return"";return `<span class="cmb o${m[2]?" me":m[1]?" un":""}" title="${m[2]?"Message for you":m[1]?"New messages":"Messages"}">💬${m[0]}</span>`};
function bellUpd(){const b=$("#bn");b.hidden=!inboxN;b.textContent=inboxN;const cur=(wss.find(w=>w.id==W)||{}).name;document.title=(inboxN?"("+inboxN+") ":"")+(cur?cur+" – ":"")+"TaskFlow"}
const dtCol=n=>{const i=dtypes.findIndex(d=>d.name===n);if(i<0)return"#94a3b8";return dtypes[i].color||GPAL[i%GPAL.length]};
const dtOpts=cur=>{cur=cur||"";const names=dtypes.map(d=>d.name);if(cur&&!names.includes(cur))names.push(cur);
  return `<option value="">—</option>`+names.map(n=>`<option value="${ea(n)}"${n===cur?" selected":""}>${esc(n)}</option>`).join("")+(LV==="view"?"":`<option value="__new">＋ Add new…</option>`)};
async function addDtype(){const n=prompt("New drawing type:");if(!n||!n.trim())return null;const r=await api("dt_add",{name:n});await load(true);return r.name}
const THEMES=[["#0f2a5c","#ffffff","Navy"],["#312e81","#ffffff","Indigo"],["#0f766e","#ffffff","Teal"],["#166534","#ffffff","Green"],["#7f1d1d","#ffffff","Maroon"],["#1f2937","#ffffff","Charcoal"],["#ffffff","#1d2433","White"],["#dbeafe","#1e3a8a","Sky"]];
function applyTheme(v){const m=/^(#[0-9a-f]{6})\|(#[0-9a-f]{6})$/i.exec(v||""),bg=m?m[1]:"#0f2a5c",fg=m?m[2]:"#ffffff",st=document.documentElement.style;
  st.setProperty("--nv",bg);st.setProperty("--hfg",fg);
  const h=document.querySelector("header");if(h){h.style.setProperty("--nv",bg);h.style.setProperty("--hfg",fg)}
  const lum=c=>{const n=parseInt(c.slice(1),16);return .3*(n>>16)+.59*((n>>8)&255)+.11*(n&255)};document.body.classList.toggle("hdark",lum(fg)<128)}
try{applyTheme(localStorage.getItem("tf_theme")||"")}catch(e){}
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
async function addStatus(){const n=prompt("New status name:");if(!n||!n.trim())return null;const r=await api("st_add",{name:n});await load(true);return r.k}
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

function syncFilters(){
  const f=$("#fs"),fv=f.value;f.innerHTML=`<option value="">All statuses</option>`+ST.map(([k,n])=>`<option value="${k}">${esc(n)}</option>`).join("");f.value=fv;
  const p=$("#fp"),a=$("#fa"),pv=p.value,av=a.value;
  p.innerHTML=`<option value="">All customers</option>`+uniq("customer").map(v=>`<option>${esc(v)}</option>`).join("");p.value=pv;
  a.innerHTML=`<option value="">Everyone</option>`+users.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join("");a.value=av;
  $("#pjl").innerHTML=uniq("customer").map(v=>`<option value="${ea(v)}">`).join("");
  $("#who").textContent=me.name||"";$("#tm").hidden=me.role!=="admin";$("#rm").hidden=me.role!=="admin";$("#ivm").hidden=me.role!=="admin";$("#om").hidden=me.role!=="admin";
}
function filtered(){
  const q=$("#q").value.toLowerCase(),p=$("#fp").value,a=$("#fa").value,st=$("#fs").value;
  return tasks.filter(t=>inGroup(t)&&(!ovOnly||ov(t).length)&&(!st||t.status===st)&&(!p||t.customer===p)&&(!a||String(t.assigned_to)===a)&&(!q||[t.title,t.order_no,t.customer,t.drawing_type,t.notes,...Object.values(t.x||{})].join(" ").toLowerCase().includes(q)));
}
function cardHTML(t){
  return `<div class="card" draggable="true" data-id="${t.id}"${scol(t.status)?` style="border-left:5px solid ${scol(t.status)}"`:""}><input type="checkbox" class="cc" title="Select"${sel.has(t.id)?" checked":""}><b>${esc(t.title)}</b>${cmBadge(t)}${ov(t).length?`<div class="meta"><span class="ovb" title="${ea(ovText(t))}">⚠ Overlap</span></div>`:""}<div class="meta">
  ${t.order_no?`<span class="tag">#${esc(t.order_no)}</span>`:""}
  ${G===""&&t.group_id&&gname(t.group_id)?`<span class="tag">📁 ${esc(gname(t.group_id))}</span>`:""}
  ${t.customer?`<span class="tag">${esc(t.customer)}</span>`:""}
  ${t.drawing_type?`<span class="tag">${esc(t.drawing_type)}</span>`:""}
  ${t.assigned_to?`<span>${esc(uname(t.assigned_to))}</span>`:""}
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
$("#gcb").addEventListener("click",e=>{const b=e.target.closest("[data-gc]");if(b)gcSet(b.dataset.gc)});
function lockView(){$("#mhm").hidden=LV==="view"&&!(me&&me.role==="admin");if(LV!=="view")return;document.querySelectorAll("#main .ed").forEach(el=>el.disabled=true);document.querySelectorAll("#main .card").forEach(c=>c.draggable=false)}
function toggleDone(){dc=!dc;try{localStorage.setItem("tf_dc",dc?"1":"0")}catch(e){}
  if(dc)for(const t of tasks)if(t.status==="done")sel.delete(t.id);render()}
function updBar(){const b=$("#bar"),n=sel.size;b.hidden=!n||view==="gantt";if(n)b.innerHTML=`<b>${n} selected</b>${LV!=="view"?'<button id="bdup">Duplicate</button>':""}${LV!=="view"&&groups.length?`<select id="bmv"><option value="">Move to group…</option><option value="0">No group</option>${gOpts("")}</select>`:""}${LV==="full"&&wss.some(w=>w.id!=W&&["edit","full"].includes(w.level))?`<select id="bws"><option value="">Move to company…</option>${wss.filter(w=>w.id!=W&&["edit","full"].includes(w.level)).map(w=>`<option value="${w.id}">${esc(w.name)}</option>`).join("")}</select>`:""}${LV==="full"?'<button id="bdel">Delete</button>':""}<button id="bclr">Clear</button>`}
const fmtH=n=>{n=+n||0;return n.toFixed(2).replace(/\.00$/,"").replace(/(\.\d)0$/,"$1")};
const BASE_COLS=[
 {id:"title",label:"Title",cell:t=>`<div class="tc">${ovBadge(t)}${cmBadge(t)}<input class="ed" data-f="title" value="${ea(t.title)}"></div>`},
 {id:"order_no",label:"Order No.",cell:t=>`<input class="ed" data-f="order_no" value="${ea(t.order_no)}">`},
 {id:"customer",label:"Customer",cell:t=>`<input class="ed" data-f="customer" list="pjl" value="${ea(t.customer)}">`},
 {id:"drawing_type",label:"Drawing type",cell:t=>`<select class="ed pill" data-f="drawing_type" style="--pc:${t.drawing_type?dtCol(t.drawing_type):"#94a3b8"}">${dtOpts(t.drawing_type)}</select>`},
 {id:"dates",label:"Start / End",cls:"dates",cell:t=>`<button type="button" class="drng ${late(t)?"late":""}${t.start_date||t.end_date?"":" empty"}" data-rng="${t.id}">${(t.start_date||t.end_date)?esc(fmtRange(t)):"Start – End"}</button>`},
 {id:"status",label:"Status",cell:t=>`<select class="ed pill" data-f="status" style="--pc:${scol(t.status||ST[0][0])||"#94a3b8"}">${stOpts(t.status)}</select>`},
 {id:"assigned_to",label:"Assigned to",cell:t=>`<select class="ed" data-f="assigned_to">${asOpts(t.assigned_to)}</select>`},
 {id:"hours",label:"Hours",cell:t=>t.id?`<button type="button" class="hbtn" data-hrs="${t.id}" title="Log or see hours">⏱ ${hoursMap[t.id]?fmtH(hoursMap[t.id]):"Log"}</button>`:""},
 {id:"notes",label:"Notes",cell:t=>`<input class="ed" data-f="notes" value="${ea(t.notes)}">`}];
const coKey=()=>"tf_co_"+W;
const cwKey=()=>"tf_cw_"+W;
const DEFW={title:220,order_no:110,customer:160,drawing_type:140,dates:170,status:140,assigned_to:150,group_id:140,hours:90,notes:220};
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
function render(){const noG=LV==="view"&&!(me&&me.role==="admin");$("#vg").hidden=noG;if(noG&&view==="gantt"){view="list";syncViewBtns()}
  const ae=document.activeElement;if(ae&&ae.dataset&&ae.dataset.f&&$("#main").contains(ae)){pend=true;return}
  pend=false;for(const id of [...sel])if(!tasks.some(t=>t.id==id))sel.delete(id);
  updBar();syncFilters();
  const cur=wss.find(w=>w.id==W);document.title=(cur?cur.name+" – ":"")+"TaskFlow";
  $("#wm").hidden=me.role!=="admin";
  $("#gcb").hidden=view!=="list";const dvb=$("#dv"),isDef=(me.pref_view||"list")===view;dvb.textContent=isDef?"★ Default view":"☆ Make "+view+" my default";dvb.disabled=isDef;
  $("#who").textContent=(me.name||"")+(LV==="view"?" · view only":LV==="full"&&me.role!=="admin"?" · full access":"");
  $("#wname").textContent=cur?cur.name:"";$("#vname").innerHTML=cur?wlogo(cur):"";$("#wlogo").innerHTML=cur?wlogo(cur):"";
  $("#wlinks").innerHTML=wss.map(w=>`<a class="wl${w.id==W?" on":""}" href="index.php?w=${w.id}" title="${ea(w.name)}">${wlogo(w)}</a>`).join("")||'<div class="empty" style="padding:12px">None</div>';
  if(!W){$("#stats").innerHTML="";$("#main").innerHTML='<div class="empty">You have not been given access to any company yet. Please ask an admin.</div>';return}
  const list=filtered();
  const gt=tasks.filter(inGroup);
  const fsv=$("#fs").value;
  const smore=(()=>{try{return localStorage.getItem("tf_smore")==="1"}catch(e){return false}})(),isDefSt=(k,n)=>/ongoing|progress|review|ready/i.test(n+" "+k),shown=ST.filter(([k,n])=>smore||isDefSt(k,n)||fsv===k),hid=ST.length-shown.length;
  const pill=(c,on,att,num,lab,tt)=>`<div class="stat${on?" on":""}" ${att} style="--pc:${c}"${tt?` title="${tt}"`:""}><b>${num}</b><span>${lab}</span></div>`;
  $("#stats").innerHTML=pill("#4f46e5",!fsv,'data-fs=""',gt.length,"All tasks")+
    shown.map(([k,n])=>pill(scol(k)||"#94a3b8",fsv===k,`data-fs="${k}"`,gt.filter(t=>t.status===k).length,esc(n),"Show only this status")).join("")+
    pill("#dc2626",false,"",gt.filter(late).length,"Overdue")+
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
    const rowHTML=t=>`<tr class="row" data-id="${t.id}"${scol(t.status)?` style="--sc:${scol(t.status)}"`:""}><td style="white-space:nowrap">${LV==="view"?"":`<span class="hd" title="Drag to reorder">⋮⋮</span>`}<input type="checkbox" class="rc"${sel.has(t.id)?" checked":""}></td>${defs.map(d=>`<td${d.cls?` class="${d.cls}"`:""}>${d.cell(t)}</td>`).join("")}<td><button class="o" title="Open details">⋯</button></td></tr>`;
    const draftHTML=()=>`<tr class="row draft" data-id="0"><td></td>${defs.map(d=>`<td${d.cls?` class="${d.cls}"`:""}>${d.cell(draft.t)}</td>`).join("")}<td style="white-space:nowrap"><button class="o" data-dsave title="Add task (Enter)">✔</button><button class="o" data-dcancel title="Cancel (Esc)">✕</button></td></tr>`;
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
  $("#tlord").textContent=t.order_no?"Order #"+t.order_no:"";$("#tltit").textContent=t.title||"";$("#tld").value=today();$("#tlh").value="";$("#tln").value="";
  document.querySelectorAll("#tldg .tlf input,#tldg .tlf button").forEach(x=>x.disabled=LV==="view");$("#tll").innerHTML="";$("#tldg").showModal();await tlLoad()}
async function tlLoad(){try{const r=await api("tl_list",{task_id:tlId});let tot=0;r.logs.forEach(l=>tot+=+l.hours);
  $("#tll").innerHTML=r.logs.length?`<table class="tlt"><tr><th>Date</th><th>Person</th><th class="n">Hours</th><th>Note</th><th></th></tr>${r.logs.map(l=>`<tr><td>${esc(l.work_date)}</td><td>${esc(l.name)}${l.logged_by&&l.logged_by!=l.user_id?' <small style="color:var(--mut)">(logged by someone else)</small>':""}</td><td class="n">${fmtH(l.hours)}${l.kind==="OT"?" <b>OT</b>":""}</td><td>${esc(l.note)}</td><td>${l.user_id==r.me||l.logged_by==r.me||LV==="full"?`<button class="x" data-tld="${l.id}" title="Delete" style="border:0;background:none;width:auto;cursor:pointer">✕</button>`:""}</td></tr>`).join("")}<tr><td colspan="2"><b>Total</b></td><td class="n"><b>${fmtH(tot)}</b></td><td colspan="2"></td></tr></table>`:`<div class="empty">No hours logged yet</div>`}catch(e){}}
async function tlSend(assign){const h=parseFloat($("#tlh").value);
  try{await api("tl_add",{task_id:tlId,date:$("#tld").value,hours:h,kind:$("#tlk").value,note:$("#tln").value,assign_to:assign||0});$("#tlh").value="";$("#tln").value="";await tlLoad();await load(true)}catch(e){alert(e.message)}}
$("#tla").onclick=()=>{const h=parseFloat($("#tlh").value);if(!(h>0&&h<=24)){alert("Enter hours between 0.25 and 24");return}
  const t=tasks.find(x=>x.id==tlId);if(t&&!t.assigned_to){$("#asw").innerHTML='<option value="">Choose a person…</option>'+users.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join("");$("#asw").value=String(me.id||"");$("#asdg").showModal();return}
  tlSend(0)};
$("#asx").onclick=()=>$("#asdg").close();
$("#asok").onclick=()=>{const v=+$("#asw").value;if(!v){alert("Choose a person");return}$("#asdg").close();tlSend(v)};
$("#tll").addEventListener("click",async e=>{const id=e.target.dataset.tld;if(!id)return;try{await api("tl_del",{id});await tlLoad();await load(true)}catch(err){alert(err.message)}});
$("#tlcl").onclick=()=>$("#tldg").close();
$("#tlb").onclick=()=>{const id=editing;if(!id)return;$("#dlg").close();openTL(id)};
// ---- Company details & hourly rates (admin) ----
let coId=0;
async function apiW(a,data,wid){const r=await fetch("api.php?a="+a+"&w="+wid,{method:"POST",headers:{"Content-Type":"application/json","X-Requested-With":"fetch"},body:JSON.stringify(data||{})});if(r.status===401){location="login.php";throw new Error("auth")}const j=await r.json();if(j.error)throw new Error(j.error);return j}
async function coOpen(id){coId=id;const w=wss.find(x=>x.id==id);try{const r=await apiW("co_get",{},id),c=r.company||{};
  $("#cot").textContent=(w?w.name:"Company")+" — details & hourly rates";$("#con").value=w?w.name:"";$("#coc").value=c.client_name||"";$("#coa").value=c.client_addr||"";$("#cob").value=c.bank||"";$("#coy").value=c.currency||"";$("#cos").value=c.symbol||"";$("#cod").value=c.rate||"";$("#coo").value=c.ot==null?1:c.ot;$("#colay").value=["items","summary"].includes(c.layout)?c.layout:"worklog";$("#cowt").value=c.wl_title||"";$("#copx").value=c.prefix||"";$("#cosf").value=c.suffix||"";$("#cosq").value=c.seq||"1";$("#copo").value=c.po||"";$("#cosl").value=c.sec_labels||"";
  $("#cor").innerHTML=allUsers.map(u=>`<label>${esc(u.name)}<input type="number" min="0" step="0.01" data-cr="${u.id}" value="${c.rates&&c.rates[u.id]!==undefined?c.rates[u.id]:""}"></label>`).join("")||'<span style="color:var(--mut)">No users yet</span>';
  $("#codlg").showModal()}catch(e){alert(e.message)}}
$("#cox").onclick=()=>$("#codlg").close();
$("#cosv").onclick=async()=>{const rates={};document.querySelectorAll("#cor [data-cr]").forEach(i=>{if(i.value!=="")rates[i.dataset.cr]=+i.value});
  const profile={name:"",client_name:$("#coc").value,client_addr:$("#coa").value,bank:$("#cob").value,currency:$("#coy").value,symbol:$("#cos").value,rate:+$("#cod").value||0,ot:$("#coo").value===""?1:+$("#coo").value,layout:$("#colay").value,wl_title:$("#cowt").value,prefix:$("#copx").value,suffix:$("#cosf").value,seq:$("#cosq").value.replace(/\D/g,"")||"1",po:$("#copo").value,sec_labels:$("#cosl").value,rates};
  try{const w=wss.find(x=>x.id==coId),n=$("#con").value.trim();if(n&&w&&n!==w.name)await apiW("ws_rename",{id:coId,name:n},coId);
    await apiW("inv_set",{slot:0,profile},coId);$("#codlg").close();await load(true);renderWs()}catch(e){alert(e.message)}};
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
  else if(k==="amend"){const n=prompt("What needs to be amended?");if(n&&n.trim())await api("inv_step",{id,op:"amend",note:n}).then(ivLoad)}
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
let mhRows=[],mhUser=0,mhAddDate="",mhSel=new Set(),mhWk="";
const isoAdd=(s,n)=>{const d=new Date(s+"T00:00:00Z");d.setUTCDate(d.getUTCDate()+n);return d.toISOString().slice(0,10)};
const wkStart=s=>{const d=new Date(s+"T00:00:00Z");return isoAdd(s,-((d.getUTCDay()+6)%7))};
const WDN=["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
const mhSorted=()=>[...mhSel].sort();
function mhRange(a,b){mhSel=new Set();for(let d=a,n=0;d<=b&&n<400;d=isoAdd(d,1),n++)mhSel.add(d);mhWk=wkStart(a);mhSync();mhLoad()}
function mhSync(){const s=mhSorted();if(s.length){$("#mhf").value=s[0];$("#mht").value=s[s.length-1]}mhChips()}
function mhChips(){$("#mhdays").innerHTML=`<button data-mhwk="-7" title="Previous week" style="padding:2px 8px">‹</button>`+[0,1,2,3,4,5,6].map(i=>{const d=isoAdd(mhWk,i),dow=(i+1)%7;return `<label style="margin:0 2px;display:inline-flex;gap:2px;align-items:center;white-space:nowrap"><input type="checkbox" data-mhd="${d}" ${mhSel.has(d)?"checked":""}>${WDN[dow]} ${+d.slice(8)}</label>`}).join("")+`<button data-mhwk="7" title="Next week" style="padding:2px 8px">›</button>`}
async function mhLoad(){const s=mhSorted();if(!s.length){mhRows=[];mhDraw();return}
  try{const r=await api("my_hours",{from:s[0],to:s[s.length-1],user:+$("#mhu").value||0});mhRows=r.rows;mhUser=r.user;mhDraw()}catch(e){alert(e.message)}}
function mhDraw(){const tg=+$("#mhg").value||0,td=ltoday(),by={};
  mhRows.forEach(x=>{if(mhSel.has(x.work_date))(by[x.work_date]=by[x.work_date]||[]).push(x)});
  let exp=0,miss=0,rows="",R=0,O=0;
  for(const d of mhSorted()){const dow=new Date(d+"T00:00:00Z").getUTCDay(),l=by[d]||[],h=l.reduce((s,x)=>s+ +x.hours,0),ot=l.filter(x=>x.kind==="OT").reduce((s,x)=>s+ +x.hours,0),rg=h-ot;R+=rg;O+=ot;
    const past=d<=td;if(past){exp+=tg;if(rg<tg)miss++}
    let st="",cl="";if(!past){st="Upcoming";cl="color:var(--mut)"}else if(rg>=tg){st="✓ Complete"+(rg>tg?" · "+fmtH(rg-tg)+" h over target (log as OT?)":"");cl="color:#188038"}else if(rg>0){st="⚠ Short by "+fmtH(tg-rg)+" h";cl="color:#b45f06;font-weight:600"}else{st="✗ Nothing logged";cl="color:#c5221f;font-weight:600"}
    const det=l.map(x=>`<div style="font-size:12px;color:var(--mut)">${esc(x.order_no||"")} ${esc(x.title)} · ${fmtH(x.hours)} h${x.kind==="OT"?" OT":""}${x.ws?" · "+esc(x.ws):""}</div>`).join("");
    rows+=`<tr><td>${esc(d)}</td><td>${WDN[dow]}</td><td class="n"><b>${fmtH(rg)} Regular${ot?" + "+fmtH(ot)+" OT = "+fmtH(h):""}</b>${det}</td><td style="${cl}">${st}</td><td>${LV==="view"?"":`<button data-mha="${d}">+ Add hours</button>`}</td></tr>`}
  $("#mhs").innerHTML=`<div style="margin:6px 0;font-size:15px">Logged <b>${fmtH(R)} h</b> + <b>${fmtH(O)} h OT</b> · expected so far <b>${fmtH(exp)} h</b> · days short: <b style="color:${miss?"#c5221f":"#188038"}">${miss}</b></div>`;
  $("#mhout").innerHTML=`<table class="rt"><tr><th>Date</th><th>Day</th><th class="n">Hours logged</th><th>Status</th><th></th></tr>${rows||'<tr><td colspan="5" class="empty">Tick one or more days above</td></tr>'}</table>`}
function mhOpen(){const sel=$("#mhu");if(me.role==="admin"&&allUsers.length){sel.hidden=false;sel.innerHTML=allUsers.map(x=>`<option value="${x.id}">${esc(x.name)}</option>`).join("");sel.value=String(me.id)}else sel.hidden=true;
  $("#mhdg").showModal();mhRange(ltoday(),ltoday())}
$("#mhm").addEventListener("click",e=>{e.preventDefault();$("#umenu").hidden=true;mhOpen()});
$("#mhcl").onclick=()=>$("#mhdg").close();
$("#mhtd").onclick=()=>mhRange(ltoday(),ltoday());
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
  const mine=tasks.filter(t=>String(t.assigned_to)===String(uid)&&t.status!=="done");
  if(!mine.length){alert("No open tasks assigned to this person in the current company. Switch company or assign a task first.");return}
  $("#mhat").textContent="Add hours · "+mhAddDate;$("#mhtk").innerHTML=mine.map(t=>`<option value="${t.id}">${esc((t.order_no?t.order_no+" · ":"")+t.title)}</option>`).join("");
  const have=mhRows.filter(x=>x.work_date===mhAddDate&&x.kind!=="OT").reduce((s,x)=>s+ +x.hours,0);$("#mhah").value=Math.max(0,(+$("#mhg").value||8)-have)||"";$("#mhan").value="";$("#mhad").showModal()});
$("#mhax").onclick=()=>$("#mhad").close();
$("#mhaok").onclick=async()=>{const h=parseFloat($("#mhah").value);if(!(h>0&&h<=24)){alert("Enter hours between 0.25 and 24");return}
  try{await api("tl_add",{task_id:+$("#mhtk").value,date:mhAddDate,hours:h,kind:$("#mhak").value,note:$("#mhan").value});$("#mhad").close();await mhLoad();await load(true)}catch(e){alert(e.message)}};
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
let wkPages=[],wkId=0;
async function wkLoad(){try{const r=await api("wiki_list",{});wkPages=r.pages||[];wkDraw()}catch(e){alert(e.message)}}
function wkDraw(){const q=$("#wk_q").value.trim().toLowerCase(),l=wkPages.filter(p=>!q||(p.title+" "+p.body).toLowerCase().includes(q));
  $("#wk_items").innerHTML=l.map(p=>`<div class="wki" data-wk="${p.id}"><b>${esc(p.title)}</b><small>${esc((p.body||"").replace(/\s+/g," ").slice(0,80))}</small></div>`).join("")||'<div class="empty">No pages yet</div>'}
$("#wk_q").addEventListener("input",wkDraw);
$("#wk_items").addEventListener("click",e=>{const d=e.target.closest("[data-wk]");if(!d)return;wkEdit(wkPages.find(p=>p.id==d.dataset.wk))});
function wkEdit(p){wkId=p?+p.id:0;$("#wk_t").value=p?p.title:"";$("#wk_b").value=p?p.body:"";$("#wk_meta").textContent=p&&p.by_name?"Last edited by "+p.by_name+" · "+p.updated_at:"";$("#wk_del").style.visibility=p?"visible":"hidden";$("#wk_list").hidden=true;$("#wk_ed").hidden=false}
$("#wk_new").onclick=()=>wkEdit(null);$("#wk_back").onclick=()=>{$("#wk_ed").hidden=true;$("#wk_list").hidden=false};
$("#wk_save").onclick=async()=>{try{await api("wiki_save",{id:wkId,title:$("#wk_t").value,body:$("#wk_b").value});$("#wk_back").onclick();await wkLoad()}catch(e){alert(e.message)}};
$("#wk_del").onclick=async()=>{if(!wkId||!confirm("Delete this page?"))return;try{await api("wiki_del",{id:wkId});$("#wk_back").onclick();await wkLoad()}catch(e){alert(e.message)}};
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
  $("#as").innerHTML=asOpts(t?t.assigned_to:"");$("#nt").value=t?t.notes||"":"";
  $("#gp").innerHTML=`<option value="">No group</option>`+gOpts("");$("#gp").value=t?(t.group_id||""):(/^\d+$/.test(grp!==undefined?grp:G)?(grp!==undefined?grp:G):"");
  $("#xf").innerHTML=cols.map(c=>`<label>${esc(c.name)}</label>`+xCtl(c,t?xv(t,c):"","dlg")).join("");
  const on=$("#ovn");if(t&&ov(t).length){on.style.display="block";on.textContent="⚠ "+ovText(t)}else on.style.display="none";
  $("#del").style.display=t&&LV==="full"?"":"none";
  const ro=LV==="view";$("#sv").style.display=ro?"none":"";
  document.querySelectorAll("#dlg input,#dlg select,#dlg textarea").forEach(el=>el.disabled=ro);
  thrHome();thrInit(t);document.querySelectorAll("#thr select,#thr textarea").forEach(el=>el.disabled=false);
  $("#dlg").showModal();$("#t").focus();if(t)thrLoad(true);
}
const fmtT=s=>(s||"").slice(0,16).replace("T"," ");
function thrHome(){const d=$("#dlg"),ref=$("#del").parentNode;if($("#thr").parentNode!==d)d.insertBefore($("#thr"),ref)}
function openMsg(id){const t=tasks.find(x=>x.id==id);if(!t)return;
  $("#mord").textContent=t.order_no?"Order #"+t.order_no:"";$("#mtit").textContent=t.title||"";
  thrInit(t);$("#mbody").appendChild($("#thr"));document.querySelectorAll("#thr select,#thr textarea").forEach(el=>el.disabled=false);
  $("#mdlg").showModal();thrLoad(true);$("#cbody").focus()}
$("#mcl").onclick=()=>$("#mdlg").close();
$("#mdlg").addEventListener("close",()=>{thrHome();$("#thr").hidden=true;thrId=null;if(dirty)poll()});
$("#mopen").onclick=()=>{const id=thrId,t=tasks.find(x=>x.id==id);$("#mdlg").close();if(t)openDlg(t)};
function thrInit(t){
  thrId=t?t.id:null;$("#thr").hidden=!t;$("#thl").innerHTML="";$("#cbody").value="";if(!t)return;
  $("#cto").innerHTML=`<option value="">To: everyone</option>`+users.filter(u=>u.id!=me.id).map(u=>`<option value="${u.id}">To: ${esc(u.name)}</option>`).join("");
  $("#ckind").value="comment";
}
async function thrLoad(first){
  if(!thrId||!($("#dlg").open||$("#mdlg").open))return;
  try{const r=await api("cm_list",{task_id:thrId}),k=r.comments.map(c=>c.id).join(",");if(!first&&k===thrKey)return;thrKey=k;
    $("#thl").innerHTML=r.comments.length?r.comments.map(c=>`<div class="msg${c.kind==="instruction"?" ins":""}${c.id>r.prev&&c.user_id!=r.me?" new":""}"><small>${c.user_id==r.me||me.role==="admin"?`<button class="x" data-cd="${c.id}" title="Delete">✕</button>`:""}<b>${esc(c.name)}</b>${c.kind==="instruction"?" 📌 instruction":""}${c.to_name?" → "+esc(c.to_name):""} · ${esc(fmtT(c.created_at))}</small>${esc(c.body)}</div>`).join(""):`<small style="color:var(--mut)">No messages yet.</small>`;
    $("#thl").scrollTop=1e9;
    if(first){const t=tasks.find(x=>x.id==thrId);if(cmeta[thrId]){cmeta[thrId]=[cmeta[thrId][0],0,0];render()}api("ping").catch(()=>{});inboxRefresh()}
  }catch(e){}
}
async function inboxRefresh(){try{const r=await api("inbox");inboxN=r.items.length;bellUpd()}catch(e){}}
async function thrSend(){
  const b=$("#cbody").value.trim();if(!b||!thrId)return;
  try{await api("cm_add",{task_id:thrId,body:b,kind:$("#ckind").value,to_user_id:$("#cto").value||0});$("#cbody").value="";thrKey="";await thrLoad(true);dirty=true}catch(e){alert(e.message)}
}
$("#csend").onclick=thrSend;
$("#cbody").addEventListener("keydown",e=>{if(e.key==="Enter"&&(e.ctrlKey||e.metaKey)){e.preventDefault();thrSend()}});
$("#ckind").onchange=()=>{if($("#ckind").value==="instruction"&&!$("#cto").value&&$("#cto").options.length>1)$("#cto").selectedIndex=1};
$("#thl").addEventListener("click",async e=>{const id=e.target.dataset.cd;if(!id)return;try{await api("cm_del",{id});thrKey="";thrLoad(true);dirty=true}catch(x){alert(x.message)}});
$("#bell").onclick=async()=>{try{const r=await api("inbox");$("#ilist").innerHTML=r.items.length?r.items.map(i=>`<div class="ib" data-w="${i.ws_id}" data-t="${i.task_id}"><small style="color:var(--mut)">${esc(i.from_name)}${i.kind==="instruction"?" 📌 instruction":""} · ${esc(i.ws_name)} · ${esc(fmtT(i.created_at))}</small><br><b>${esc(i.title)}</b><br>${esc(i.body.length>140?i.body.slice(0,140)+"…":i.body)}</div>`).join(""):`<p style="color:var(--mut)">Nothing new.</p>`;$("#idlg").showModal()}catch(e){alert(e.message)}};
$("#icl").onclick=()=>$("#idlg").close();
$("#iall").onclick=async()=>{try{await api("inbox_read");$("#idlg").close();await load(true)}catch(e){alert(e.message)}};
$("#ilist").addEventListener("click",e=>{const b=e.target.closest(".ib");if(!b)return;const w=+b.dataset.w,t=+b.dataset.t;$("#idlg").close();
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

$("#main").addEventListener("click",e=>{
  const sdh=e.target.closest(".dh[data-dh]");if(sdh){toggleCC(sdh.dataset.dh);return}
  if(e.target.closest(".dh")){toggleDone();return}
  const gh=e.target.closest(".gh");if(gh){const ad=e.target.closest("[data-gadd]");if(ad)startDraft(ad.dataset.gadd);else toggleGroup(gh.dataset.gh);return}
  if(e.target.classList.contains("cc"))return;
  const ga=e.target.closest(".addrow [data-gadd]");if(ga){startDraft(ga.dataset.gadd);return}
  if(e.target.closest("[data-dsave]")){draftSave();return}
  if(e.target.closest("[data-dcancel]")){draft=null;render();return}
  const gs=e.target.closest(".addrow [data-gsub]");if(gs){(async()=>{const n=prompt("New sub-group name:");if(!n||!n.trim())return;try{await api("gr_add",{name:n,parent_id:gs.dataset.gsub});await load(true)}catch(err){alert(err.message)}})();return}
  if(e.target.closest(".addrow [data-gnew]")){(async()=>{const n=prompt("New group name:");if(!n||!n.trim())return;try{await api("gr_add",{name:n});await load(true)}catch(err){alert(err.message)}})();return}
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

const BUNDLED_LOGOS={"allshelter usa":"data:image/png;base64,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"};
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
    ${members.map(u=>`<label><input type="checkbox" data-wu="${u.id}"${(access[w.id]||{})[u.id]?" checked":""}> <span style="flex:1">${esc(u.name)}</span><select data-wl="${u.id}">${Object.entries(LVN).map(([k,n])=>`<option value="${k}"${((access[w.id]||{})[u.id]||"edit")===k?" selected":""}>${n}</option>`).join("")}</select></label>`).join("")||'<div class="empty">No members yet</div>'}
    <div class="acts"><span class="sp"></span><button class="p" data-wsave="${w.id}">Save access</button></div></div></div>`).join("");
}
$("#wm").onclick=()=>{renderWs();$("#wdlg").showModal()};
$("#sbtn").onclick=e=>{e.stopPropagation();const m=$("#smenu");$("#umenu").hidden=true;if(!m.hidden){m.hidden=true;return}m.hidden=false;const r=$("#sbtn").getBoundingClientRect();m.style.left=(r.right+8)+"px";m.style.bottom=Math.max(8,window.innerHeight-r.bottom)+"px"};
$("#ubtn").onclick=e=>{e.stopPropagation();const m=$("#umenu");$("#smenu").hidden=true;if(!m.hidden){m.hidden=true;return}m.hidden=false;const r=$("#ubtn").getBoundingClientRect();m.style.top=(r.bottom+6)+"px";m.style.right=Math.max(8,window.innerWidth-r.right)+"px"};
$("#smenu").addEventListener("click",()=>{$("#smenu").hidden=true});
document.addEventListener("click",e=>{if(!e.target.closest("#setw"))$("#smenu").hidden=true;if(!e.target.closest("#uw"))$("#umenu").hidden=true});
document.addEventListener("keydown",e=>{if(e.key==="Escape"){$("#smenu").hidden=true;$("#umenu").hidden=true}});
(()=>{let t=0,act=0;const idle=()=>{if(document.body.classList.contains("sc")||$("#side").matches(":hover")||!$("#smenu").hidden)return;document.body.classList.add("sc");$("#stg").textContent="▶"};
  const reset=()=>{clearTimeout(t);t=setTimeout(idle,60000)};["mousemove","keydown","click","scroll","touchstart"].forEach(ev=>document.addEventListener(ev,reset,{passive:true,capture:true}));reset()})();
$("#stg").onclick=()=>{const c=document.body.classList.toggle("sc");$("#stg").textContent=c?"▶":"◀";try{localStorage.setItem("tf_sc",c?"1":"0")}catch(e){}};
$("#wf").addEventListener("change",async e=>{const f=e.target.files[0];if(!f||!logoFor)return;
  try{const data=await fileToLogo(f);await api("ws_logo",{id:logoFor,data});await load(true);renderWs()}catch(err){alert(err.message)}logoFor=null});
$("#wc").onclick=()=>$("#wdlg").close();
$("#wa").onclick=async()=>{try{const r=await api("ws_add",{name:$("#wn").value});location="index.php?w="+r.id}catch(e){alert(e.message)}};
$("#wlist").addEventListener("click",async e=>{
  const d=e.target.dataset;try{
    if(d.cod){await coOpen(d.cod);return}
    if(d.wr){const w=wss.find(x=>x.id==d.wr),n=prompt("New name for this company:",w.name);if(n&&n.trim()){await api("ws_rename",{id:d.wr,name:n});await load(true);renderWs()}}
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
    if(d.sr){const cur=SL.find(x=>x.id==d.sr),n=prompt("New name for this status:",cur.name);if(n&&n.trim()){await api("st_ren",{id:d.sr,name:n});await load(true);renderSt()}}
    if(d.sd){const cur=SL.find(x=>x.id==d.sd);if(confirm("Remove the status \""+cur.name+"\"? Its tasks move to the first remaining status.")){await api("st_del",{id:d.sd});await load(true);renderSt()}}
  }catch(err){alert(err.message)}
});
$("#slist").addEventListener("change",async e=>{const id=e.target.dataset.sc;if(!id)return;try{await api("st_color",{id,color:e.target.value});await load(true);renderSt()}catch(err){alert(err.message)}});
$("#stats").addEventListener("click",e=>{if(e.target.closest("[data-more]")){try{localStorage.setItem("tf_smore",localStorage.getItem("tf_smore")==="1"?"":"1")}catch(x){}render();return}if(e.target.closest("[data-ov]")){ovOnly=!ovOnly;render();return}const el=e.target.closest("[data-fs]");if(!el)return;const f=$("#fs");f.value=f.value===el.dataset.fs?"":el.dataset.fs;try{localStorage.setItem("tf_gc_"+W,"[]")}catch(x){}render()});
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
  if(e.target.dataset.dr){const d=dtypes.find(x=>x.id==e.target.dataset.dr),n=prompt("Rename drawing type:",d.name);if(n&&n.trim()){await api("dt_ren",{id:d.id,name:n});await load(true);renderDt()}}
  if(e.target.dataset.dd&&confirm("Delete this drawing type? Tasks that already use it keep their value."))await api("dt_del",{id:e.target.dataset.dd}).then(async()=>{await load(true);renderDt()})
}catch(err){alert(err.message)}});
function pPrev(){const bg=$("#pbg").value,fg=$("#pfg").value,p=$("#pprev");p.style.background=bg;p.style.color=fg;applyTheme(bg+"|"+fg)}
$("#pm").onclick=()=>{const t=(me.theme||"#0f2a5c|#ffffff").split("|");$("#pbg").value=t[0];$("#pfg").value=t[1];
  $("#psw").innerHTML=THEMES.map((x,i)=>`<button type="button" class="sw" data-sw="${i}" title="${x[2]}" style="background:${x[0]};color:${x[1]}">Aa</button>`).join("");pPrev();$("#pgl").checked=document.documentElement.classList.contains("glass");$("#pdlg").showModal()};
$("#pgl").addEventListener("change",()=>setGlass($("#pgl").checked));
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
    if(d.gs){const n=prompt("New sub-group name:");if(n&&n.trim()){await api("gr_add",{name:n,parent_id:d.gs});await load(true);renderGr()}}
    if(d.gr){const cur=groups.find(g=>g.id==d.gr),hasSub=groups.some(x=>String(x.parent_id)===String(cur.id));geId=cur.id;geCol=cur.color||"";gcsDraw();$("#gen").value=cur.name;
      $("#gep").innerHTML='<option value="">— None (top level) —</option>'+groups.filter(x=>!x.level&&x.id!=cur.id).map(x=>`<option value="${x.id}">${esc(x.name)}</option>`).join("");$("#gep").value=cur.parent_id?String(cur.parent_id):"";
      $("#gep").disabled=hasSub;const oth=wss.filter(w=>w.id!=W&&["edit","full"].includes(w.level));$("#gemw").hidden=!oth.length;$("#gemt").innerHTML=oth.map(w=>`<option value="${w.id}">${esc(w.name)}</option>`).join("");$("#genote").textContent=hasSub?"This group has sub-groups, so it must stay at the top level.":"";$("#gedlg").showModal()}
    if(d.gd){const cur=groups.find(g=>g.id==d.gd);if(confirm("Delete the group \""+cur.name+"\""+(cur.level?"":" and its sub-groups")+"? Their tasks are kept and moved to No group.")){await api("gr_del",{id:d.gd});if(G===String(d.gd))G="";await load(true);renderGr()}}
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
    else await api("col_edit",{id:ceId,name:$("#cen").value,options:$("#ceo").value});
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
  const eb=e.target.dataset.ce;if(eb){const c=cols.find(x=>x.id==eb);ceBuilt="";ceId=c.id;$("#ceh").checked=hiddenCols().includes("x_"+c.id);$("#ceh").disabled=false;$("#cen").value=c.name;$("#ceo").value=(c.options||[]).join(", ");$("#ceow").hidden=c.ctype!=="dropdown";$("#cet").textContent="Type: "+c.ctype+" (the type cannot be changed). Hiding applies only on this device.";$("#cedlg").showModal();return}
  const id=e.target.dataset.cd;if(!id||!confirm("Remove this column? The values entered in it will be deleted."))return;
  try{await api("col_del",{id});await load(true);renderCols()}catch(err){alert(err.message)}
});
function renderUsers(){
  $("#tlist").innerHTML=allUsers.map(u=>`<div class="urow"><span><b>${esc(u.name)}</b></span><span>${esc(u.username||"")}</span>
   <span class="pw"><input type="password" data-np="${u.id}" placeholder="•••••••• (new password)" autocomplete="new-password"><button type="button" class="eye" title="Show / hide">👁</button></span>
   <select data-role="${u.id}"${u.id==me.id?" disabled":""}><option value="member"${u.role!=="admin"?" selected":""}>Member</option><option value="admin"${u.role==="admin"?" selected":""}>Admin</option></select>
   <span style="display:flex;gap:4px"><button data-pw="${u.id}" title="Save the new password">Set</button>${u.id==me.id?"":`<button data-rm="${u.id}" title="Remove user">✕</button>`}</span></div>`).join("");
}
$("#tm").onclick=()=>{renderUsers();$("#tdlg").showModal()};
$("#tc").onclick=()=>$("#tdlg").close();
$("#tnew").onclick=()=>{for(const i of ["un","uu","up"]){const el=$("#"+i);el.value="";el.readOnly=true;if(i==="up")el.type="password"}$("#ur").value="member";$("#udlg").showModal()};
for(const i of ["un","uu","up"])$("#"+i).addEventListener("focus",e=>{e.target.readOnly=false});
$("#uc").onclick=()=>$("#udlg").close();
$("#udlg").addEventListener("click",e=>{const ey=e.target.closest(".eye");if(ey){const i=ey.parentNode.querySelector("input");i.type=i.type==="password"?"text":"password"}});
$("#ta").onclick=async()=>{
  try{await api("user_add",{name:$("#un").value,username:$("#uu").value,password:$("#up").value,role:$("#ur").value});
    for(const i of ["un","uu","up"])$("#"+i).value="";$("#udlg").close();await load(true);renderUsers()}catch(e){alert(e.message)}
};
$("#tlist").addEventListener("click",async e=>{
  try{
    if(e.target.dataset.rm&&confirm("Remove this user? Their tasks become unassigned.")){await api("user_del",{id:e.target.dataset.rm});await load(true);renderUsers()}
    if(e.target.dataset.pw){const inp=$('#tlist input[data-np="'+e.target.dataset.pw+'"]'),p=inp.value;if(p.length<8){alert("Type a new password of 8+ characters first.");return}await api("user_pass",{id:e.target.dataset.pw,password:p});inp.value="";alert("Password changed.")}
    const ey=e.target.closest(".eye");if(ey){const i=ey.parentNode.querySelector("input");i.type=i.type==="password"?"text":"password"}
  }catch(err){alert(err.message)}
});
$("#tlist").addEventListener("change",async e=>{if(!e.target.dataset.role)return;try{await api("user_role",{id:e.target.dataset.role,role:e.target.value});await load(true);renderUsers()}catch(err){alert(err.message);await load(true);renderUsers()}});
load(true);
const BUILD=93;
$("#bld").textContent="Build "+BUILD;$("#bld").onclick=()=>location.reload();
function chkBuild(b){const e=$("#bld");if(b&&+b!==BUILD){e.textContent="↻ Build "+b+" ready · tap to update";e.style.background="#fff4d6";e.style.color="#7a5200";e.style.opacity=1}else{e.textContent="Build "+BUILD;e.style.background="";e.style.color="";e.style.opacity=.8}}
function setLive(ok){const l=$("#live");l.innerHTML=`<span style="color:${ok?"#22c55e":"#9ca3af"};text-shadow:${ok?"0 0 6px #22c55e":"none"}">●</span> ${ok?"Live":"Offline"}`;l.style.color=""}
// ---- Messages between team members ----
let mgPeer=0,mgLast=0,mgUsers=[],mgTeam={n:0},mgTimer=0;
const mgOpen=()=>!$("#msgw").hidden;
function mgBadge(n){const b=$("#mgn");b.hidden=!n;b.textContent=n}
async function mgUsersLoad(){try{const r=await api("msg_users",{});mgUsers=r.users;mgTeam=r.team;mgUsersDraw()}catch(e){}}
function mgUsersDraw(){const l=[...mgUsers].sort((a,b)=>b.last-a.last||a.name.localeCompare(b.name));
  $("#mgu").innerHTML=[`<div class="mgu${mgPeer===0?" on":""}" data-mg="0"><span>👥 Team (everyone)</span>${mgTeam.n?`<b>${mgTeam.n}</b>`:""}</div>`].concat(l.map(u=>`<div class="mgu${mgPeer===u.id?" on":""}" data-mg="${u.id}"><span>${esc(u.name)}</span>${u.n?`<b>${u.n}</b>`:""}</div>`)).join("");
  const u=mgUsers.find(x=>x.id===mgPeer);$("#mgt").textContent=mgPeer===0?"👥 Team — everyone can read this":(u?u.name:"")}
function mgAdd(ms){const box=$("#mgm"),near=box.scrollHeight-box.scrollTop-box.clientHeight<80;
  for(const m of ms){if(m.id<=mgLast)continue;mgLast=m.id;const d=document.createElement("div");d.className="mgmsg"+(m.mine?" me":"");d.innerHTML=(mgPeer===0&&!m.mine?`<b>${esc(m.name)}</b><br>`:"")+esc(m.body)+`<small>${esc(String(m.created_at).slice(5,16))}</small>`;box.appendChild(d)}
  if(ms.length&&near||box.dataset.first==="1"){box.scrollTop=box.scrollHeight;box.dataset.first=""}}
async function mgLoad(){try{const r=await api("msg_list",{peer:mgPeer,after:mgLast});mgAdd(r.messages)}catch(e){}}
async function mgSel(p){mgPeer=p;mgLast=0;$("#mgm").innerHTML="";$("#mgm").dataset.first="1";mgUsersDraw();await mgLoad();mgUsersLoad()}
function mgStart(){mgSel(mgPeer);clearInterval(mgTimer);mgTimer=setInterval(()=>{if(document.hidden||!mgOpen())return;mgLoad();mgUsersLoad()},3000)}
(()=>{const st=$("#sidetabs"),place=()=>{const hd=document.querySelector("header");if(hd)st.style.top=Math.max(8,Math.round(hd.getBoundingClientRect().bottom+6))+"px"},chk=()=>{place();st.classList.toggle("hid",window.scrollY>10||[...document.querySelectorAll(".listwrap,#main")].some(x=>x.scrollTop>10))};
  document.addEventListener("scroll",chk,true);window.addEventListener("resize",place);place();setTimeout(place,600)})();
$("#mgb").onclick=()=>{const w=$("#msgw");w.hidden=!w.hidden;if(w.hidden){clearInterval(mgTimer);return}
  let g=null;try{g=JSON.parse(localStorage.getItem("tf_mgg")||"null")}catch(e){}if(g&&g.w>=320){w.style.left=Math.min(Math.max(0,g.l),innerWidth-120)+"px";w.style.top=Math.min(Math.max(0,g.t),innerHeight-60)+"px";w.style.width=g.w+"px";w.style.height=g.h+"px"}
  mgUsersLoad().then(()=>{const first=mgUsers.find(u=>u.n);if(first&&mgPeer===0&&!mgTeam.n)mgPeer=first.id;mgStart()});$("#mgi").focus()};
$("#mgx").onclick=()=>{$("#msgw").hidden=true;clearInterval(mgTimer)};
$("#mgu").addEventListener("click",e=>{const d=e.target.closest("[data-mg]");if(d)mgSel(+d.dataset.mg)});
async function mgSend(){const t=$("#mgi").value.trim();if(!t)return;try{await api("msg_send",{peer:mgPeer,body:t});$("#mgi").value="";$("#mgm").dataset.first="1";await mgLoad();mgUsersLoad()}catch(e){alert(e.message)}}
const MGE="😀 😃 😄 😁 😆 😅 😂 🤣 😊 🙂 😉 😍 😘 😎 🤔 😐 😴 😢 😭 😡 🙏 👍 👎 👌 ✌️ 👏 🙌 💪 👀 🔥 ⭐ ✅ ❌ ⚠️ ❓ ❗ 💡 📌 📎 📅 ⏰ 📐 📏 🏗️ 🏠 📄 📁 💬 🎉 ☕ 🍽️ ❤️ 💯 🤝 👋".split(" ");
$("#mge").innerHTML=MGE.map(e=>`<span>${e}</span>`).join("");
$("#mgem").onclick=e=>{e.stopPropagation();$("#mge").hidden=!$("#mge").hidden};
$("#mge").addEventListener("click",e=>{const s=e.target.closest("span");if(!s)return;const t=$("#mgi"),a=t.selectionStart??t.value.length,b=t.selectionEnd??a;t.value=t.value.slice(0,a)+s.textContent+t.value.slice(b);const p=a+s.textContent.length;t.focus();t.setSelectionRange(p,p)});
document.addEventListener("click",e=>{if(!e.target.closest("#mge,#mgem"))$("#mge").hidden=true});
$("#mgs").onclick=mgSend;$("#mgi").addEventListener("keydown",e=>{if(e.key==="Enter"&&!e.shiftKey){e.preventDefault();mgSend()}});
(()=>{const s=$("#msgw");let d=null;const save=()=>{if(s.hidden)return;const r=s.getBoundingClientRect();try{localStorage.setItem("tf_mgg",JSON.stringify({l:Math.round(r.left),t:Math.round(r.top),w:Math.round(r.width),h:Math.round(r.height)}))}catch(e){}};
  s.querySelector(".mgh").addEventListener("pointerdown",e=>{if(e.target.closest("button"))return;const r=s.getBoundingClientRect();d={x:e.clientX-r.left,y:e.clientY-r.top};e.target.setPointerCapture&&e.target.setPointerCapture(e.pointerId)});
  document.addEventListener("pointermove",e=>{if(!d)return;s.style.left=Math.min(Math.max(-s.offsetWidth+120,e.clientX-d.x),innerWidth-120)+"px";s.style.top=Math.min(Math.max(0,e.clientY-d.y),innerHeight-50)+"px"});
  document.addEventListener("pointerup",()=>{if(d){d=null;save()}});new ResizeObserver(()=>{if(!s.hidden)save()}).observe(s)})();
function busy(){return document.querySelector("dialog[open]")||draft||rp||rowDrag||dragId||dragCol||rz||gdrag}
async function poll(){
  if(document.hidden)return;
  try{
    const r=await api("ping");setLive(true);mgBadge(+r.msgn||0);
    if(($("#dlg").open||$("#mdlg").open)&&thrId&&r.v!==thrVer){thrVer=r.v;thrLoad(false)}
    if(r.v!==lastVer||dirty){if(busy()){dirty=true;return}dirty=false;await load()}
  }catch(e){if(e.message!=="auth")setLive(false)}
}
setInterval(poll,3000);
document.addEventListener("visibilitychange",()=>{if(!document.hidden)poll()});
window.addEventListener("focus",poll);
document.addEventListener("close",()=>{if(dirty)poll()},true);
document.addEventListener("dragend",()=>{if(dirty)setTimeout(poll,50)});
</script></body></html>
