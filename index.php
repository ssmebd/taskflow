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
.stat{background:var(--card);border:1px solid var(--line);border-radius:10px;padding:8px 14px;min-width:92px}
th.ch{cursor:grab;user-select:none;white-space:nowrap}th.ch.over{box-shadow:inset 3px 0 0 var(--pri)}
tr.row[style] td{background:color-mix(in srgb,var(--sc) 14%,var(--card))}
tr.row[style] td:first-child{box-shadow:inset 5px 0 0 var(--sc)}
.dot{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px;vertical-align:middle}
.gtabs{display:flex;gap:6px;padding:8px 16px 0;overflow-x:auto}
.gt{padding:5px 14px;border-radius:8px 8px 0 0;border:1px solid var(--line);border-bottom:0;background:var(--col);cursor:pointer;white-space:nowrap;color:var(--ink)}
.gt.on{background:var(--card);font-weight:600;box-shadow:inset 0 3px 0 var(--pri)}.gt small{color:var(--mut)}.ro #gm{display:none}
.gh th,.dh th{background:var(--col);color:var(--ink);font-size:13px;cursor:pointer}
.stat{cursor:pointer}.stat.on{border-color:var(--pri);outline:1px solid var(--pri)}.ro #sm{display:none}
.stat b{display:block;font-size:20px}.stat span{color:var(--mut);font-size:12px}
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
body.sc #wlinks,body.sc .sf{display:none}body.sc #vname{display:block}
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
.ro #setw{display:none}
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
header{--nv:#0f2a5c;background:var(--nv);border-bottom:1px solid #0a1d42;color:#fff}
header h1,header .app,header #who,header a.out,header #live{color:#fff!important}header .app{opacity:.75}
header input,header select,header button{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.3)}
header input::placeholder{color:rgba(255,255,255,.7)}header option{color:#1d2433;background:#fff}
header button:hover:not(:disabled){background:rgba(255,255,255,.22)}header button:disabled{opacity:.7}
header .tabs button.on{background:#fff;color:var(--nv);border-color:#fff;font-weight:600}
header button.p{background:#fff;color:var(--nv);border-color:#fff}header button.p:hover{background:#e6ecfb}
header #bell b{background:#ef4444;color:#fff}
header #smenu button{background:var(--card);color:var(--ink);border-color:transparent;text-align:left}header #smenu button:hover{background:var(--col)}
#side{background:#0b2150;border-right-color:#081a40}#side .sh{border-bottom-color:rgba(255,255,255,.18)}
#side button{background:rgba(255,255,255,.12);color:#fff;border-color:rgba(255,255,255,.3)}#side button:hover{background:rgba(255,255,255,.24)}
#side .wl{background:rgba(255,255,255,.1)}#side .wl:hover{border-color:rgba(255,255,255,.5)}#side .wl.on{border-color:#fff;box-shadow:0 0 0 3px rgba(255,255,255,.28)}
.gbar{touch-action:none;user-select:none}.ro .gbar{touch-action:auto}.glane.gdrop{background:rgba(79,70,229,.12)}
#gtip{position:fixed;z-index:60;background:#0f2a5c;color:#fff;font-size:12px;padding:3px 8px;border-radius:6px;pointer-events:none;white-space:nowrap}
.wh{filter:brightness(0) invert(1)}
</style></head><body>
<header>
 <span id="wlogo"></span><h1><span class="app">TaskFlow</span><span id="wname"></span></h1>
 <span class="tabs"><button id="vl" class="on">List</button><button id="vb">Board</button><button id="vg">Gantt</button></span>
 <button id="dv" title="Choose which view opens first"></button>
 <input id="q" placeholder="Search tasks" style="min-width:140px">
 <select id="fs"></select><select id="fp"></select><select id="fa"></select>
 <span class="sp"></span>
 <span id="live" style="font-size:12px;color:var(--mut)">○ …</span>
 <span id="who" style="color:var(--mut)"></span>
 <button id="bell" title="Messages for you">🔔<b id="bn" hidden></b></button>
 <button id="cr" hidden title="Restore the original column order and widths">Reset columns</button>
 <button class="p" id="add">+ New task</button>
 <span id="setw"><button id="sbtn" aria-haspopup="true">⚙ Settings ▾</button>
  <div id="smenu" hidden>
   <button id="gm">📁 Groups</button>
   <button id="sm">🏷 Statuses</button>
   <button id="cm">➕ Column</button>
   <button id="xm">⬇ Export to Excel</button>
   <button id="tm" hidden>👥 Users</button>
   <button id="om" hidden>⚠ Overlap rules</button>
  </div></span>
 <a class="out" href="login.php?logout=1">Log out</a>
</header>
<nav class="gtabs" id="gtabs" hidden></nav>
<div class="stats" id="stats"></div>
<aside id="side"><div class="sh"><button id="stg" title="Collapse or expand the sidebar">◀</button></div><div id="vname"></div><nav id="wlinks"></nav><div class="sf"><button id="wm" hidden title="Manage workspaces and logos">⚙ Manage</button></div></aside>
<div id="bar" hidden></div>
<div id="main"></div>

<dialog id="dlg">
 <label>Title</label><input id="t">
 <label>Order number</label><input id="on">
 <label>Customer name</label><input id="pj" list="pjl"><datalist id="pjl"></datalist>
 <label>Drawing type</label><input id="dt" list="dtl"><datalist id="dtl"></datalist>
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
 <div class="acts"><button id="del" style="display:none">Delete</button><span class="sp"></span><button id="cx">Cancel</button><button class="p" id="sv">Save</button></div>
</dialog>

<dialog id="tdlg"><b>Users</b><div id="tlist"></div>
 <label>Add user</label>
 <input id="un" placeholder="Full name"><input id="uu" placeholder="Username" style="margin-top:6px"><input id="up" type="password" placeholder="Password (8+ chars)" style="margin-top:6px">
 <select id="ur" style="margin-top:6px"><option value="member">Member</option><option value="admin">Admin</option></select>
 <div class="acts"><span class="sp"></span><button id="tc">Close</button><button class="p" id="ta">Add</button></div>
</dialog>

<dialog id="idlg"><b>🔔 Messages for you</b><div id="ilist"></div><div class="acts"><button id="iall">Mark all read</button><span class="sp"></span><button id="icl">Close</button></div></dialog>
<dialog id="odlg"><b>Overlap rules</b>
 <p style="color:var(--mut);font-size:13px;margin:8px 0">A task is flagged ⚠ when the same person has another open task on overlapping dates, in any workspace. These rules apply to everyone.</p>
 <label style="display:flex;gap:8px;align-items:flex-start;color:var(--ink)"><input type="checkbox" id="oh"> <span>Allow a hand-over day: a task may start on the same day another one ends</span></label>
 <label style="display:flex;gap:8px;align-items:flex-start;color:var(--ink)"><input type="checkbox" id="oo"> <span>Accept same-day tasks: tasks that start and finish on the same day (small jobs) are never flagged</span></label>
 <div class="acts"><span class="sp"></span><button id="oc">Cancel</button><button class="p" id="os">Save</button></div>
</dialog>

<dialog id="gdlg"><b>Groups (folders)</b><div id="glist"></div>
 <label>New group</label><input id="gn" placeholder="e.g. Production drawing, Sales drawing">
 <div class="acts"><span class="sp"></span><button id="gc">Close</button><button class="p" id="ga">Add group</button></div>
</dialog>

<dialog id="sdlg"><b>Statuses</b><div id="slist"></div>
 <label>New status</label><input id="sn" placeholder="e.g. Waiting for client, Drafting, Checking">
 <div class="acts"><span class="sp"></span><button id="sc">Close</button><button class="p" id="sa">Add status</button></div>
</dialog>

<dialog id="cdlg"><b>Custom columns</b><div id="clist"></div>
 <label>New column name</label><input id="cn" placeholder="e.g. Revision, Sheet size, Priority">
 <label>Type</label><select id="ct"><option value="text">Text</option><option value="number">Number</option><option value="date">Date</option><option value="dropdown">Dropdown</option></select>
 <input id="co" placeholder="Options, separated by commas" style="display:none;margin-top:6px">
 <div class="acts"><span class="sp"></span><button id="cc">Close</button><button class="p" id="ca">Add column</button></div>
</dialog>

<input type="file" id="wf" accept="image/png,image/jpeg,image/webp,image/svg+xml" hidden>
<dialog id="wdlg"><b>Workspaces</b><div id="wlist"></div>
 <label>New workspace (e.g. a client name)</label><input id="wn" placeholder="e.g. Allshelter">
 <div class="acts"><span class="sp"></span><button id="wc">Close</button><button class="p" id="wa">Add</button></div>
</dialog>

<script>
let ST=[["todo","To Do"],["progress","In Progress"],["review","In Review"],["done","Completed"]];
let dc=(()=>{try{return localStorage.getItem("tf_dc")==="1"}catch(e){return false}})();
let gdrag=null,gSwallow=false,thrVer=-1,cmeta={},inboxN=0,thrId=null,thrKey='',deepT=+(new URLSearchParams(location.search).get('t')||0),W=+(new URLSearchParams(location.search).get("w")||(()=>{try{return localStorage.getItem("tf_w")}catch(e){return 0}})()||0),viewInit=false,lastVer=-1,dirty=false,ovl={},ovset={hand:1,one:0},ovOnly=false,groups=[],G="",SL=[],LV="",wss=[],names={},allUsers=[],access={},openWp=null;
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
  try{const j=await api("list"),{v:jv,...rest}=j,s=JSON.stringify(rest);lastVer=jv;setLive(true);if(s===last&&!force)return;
    last=s;if(!viewInit){viewInit=true;view=["board","gantt"].includes(j.me&&j.me.pref_view)?j.me.pref_view:"list";syncViewBtns()}
    tasks=j.tasks;tasks.forEach(t=>{t.id=+t.id});ovl=j.overlaps||{};cmeta=j.cmeta||{};inboxN=+j.inbox||0;ovset=j.ovset||ovset;users=j.users;cols=j.cols||[];me=j.me;wss=j.ws||[];LV=j.level||"";SL=j.statuses||[];groups=j.groups||[];try{G=localStorage.getItem("tf_g_"+W)||""}catch(e){G=""}if(G&&G!=="none"&&!groups.some(g=>g.id==G))G="";if(SL.length)ST=SL.map(x=>[x.k,x.name]);document.body.classList.toggle("ro",LV==="view");names=j.names||{};allUsers=j.allUsers||[];access=j.access||{};
    if(j.w!==W){W=j.w;if(history.replaceState)history.replaceState(null,"","index.php"+(W?"?w="+W:""))}
    
    try{localStorage.setItem("tf_w",W)}catch(e){}
    render();bellUpd();if(deepT){const t=tasks.find(x=>x.id==deepT);deepT=0;if(t)openDlg(t)}}catch(e){}
}
const cmBadge=t=>{const m=cmeta[t.id];if(!m)return"";return `<span class="cmb o${m[2]?" me":m[1]?" un":""}" title="${m[2]?"Message for you":m[1]?"New messages":"Messages"}">💬${m[0]}</span>`};
function bellUpd(){const b=$("#bn");b.hidden=!inboxN;b.textContent=inboxN;const cur=(wss.find(w=>w.id==W)||{}).name;document.title=(inboxN?"("+inboxN+") ":"")+(cur?cur+" – ":"")+"TaskFlow"}
const ov=t=>ovl[t.id]||[];
const ovText=t=>"Overlaps with:\n"+ov(t).map(o=>"• "+o.t+" ("+(o.s||"?")+" → "+(o.e||"?")+")"+(o.w?" – "+o.w:"")).join("\n");
const ovBadge=t=>ov(t).length?`<span class="ovb" title="${ea(ovText(t))}">⚠</span>`:"";
const inGroup=t=>G===""||(G==="none"?!t.group_id:String(t.group_id)===G);
const gname=id=>(groups.find(g=>g.id==id)||{}).name||"";
const scol=k=>{const c=(SL.find(x=>x.k===k)||{}).color||"";return /^#[0-9a-f]{6}$/i.test(c)?c:""};
const stOpts=s=>ST.map(([k,n])=>`<option value="${k}"${(s||ST[0][0])===k?" selected":""}>${n}</option>`).join("");
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
const late=t=>t.end_date&&t.end_date<today()&&t.status!=="done";

function syncFilters(){
  const f=$("#fs"),fv=f.value;f.innerHTML=`<option value="">All statuses</option>`+ST.map(([k,n])=>`<option value="${k}">${esc(n)}</option>`).join("");f.value=fv;
  const p=$("#fp"),a=$("#fa"),pv=p.value,av=a.value;
  p.innerHTML=`<option value="">All customers</option>`+uniq("customer").map(v=>`<option>${esc(v)}</option>`).join("");p.value=pv;
  a.innerHTML=`<option value="">Everyone</option>`+users.map(u=>`<option value="${u.id}">${esc(u.name)}</option>`).join("");a.value=av;
  $("#pjl").innerHTML=uniq("customer").map(v=>`<option value="${ea(v)}">`).join("");
  $("#dtl").innerHTML=uniq("drawing_type").map(v=>`<option value="${ea(v)}">`).join("");
  $("#who").textContent=me.name||"";$("#tm").hidden=me.role!=="admin";$("#om").hidden=me.role!=="admin";
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
  ${(t.start_date||t.end_date)?`<span class="${late(t)?"late":""}">${esc(t.start_date||"?")} → ${esc(t.end_date||"?")}</span>`:""}</div></div>`;
}
function completedHTML(list){
  const c=list.filter(t=>t.status==="done");
  return `<div class="done-wrap"><div class="col done" data-s="done"><h3 class="dh" style="cursor:pointer" title="Click to show or hide">${dc?"▸":"▾"} <i class="dot" style="background:${scol("done")||"#22c55e"}"></i>Completed<em>${c.length}</em></h3>${dc?"":`<div class="donegrid">${c.map(cardHTML).join("")||'<div class="empty">Completed tasks appear here</div>'}</div>`}</div></div>`;
}
function sorted(l){const a=l.filter(t=>t.status!=="done"),d=l.filter(t=>t.status==="done");return a.concat(d)}
function getGC(){try{return new Set(JSON.parse(localStorage.getItem("tf_gc_"+W)||"[]"))}catch(e){return new Set()}}
function hiddenRow(t){if(t.status==="done")return dc;return groups.length&&getGC().has(t.group_id?String(t.group_id):"none")}
function vis(){return filtered().filter(t=>!hiddenRow(t))}
function toggleGroup(id){const g=getGC();g.has(id)?g.delete(id):g.add(id);try{localStorage.setItem("tf_gc_"+W,JSON.stringify([...g]))}catch(e){}
  for(const t of tasks)if(hiddenRow(t))sel.delete(t.id);render()}
function lockView(){if(LV!=="view")return;document.querySelectorAll("#main .ed").forEach(el=>el.disabled=true);document.querySelectorAll("#main .card").forEach(c=>c.draggable=false)}
function toggleDone(){dc=!dc;try{localStorage.setItem("tf_dc",dc?"1":"0")}catch(e){}
  if(dc)for(const t of tasks)if(t.status==="done")sel.delete(t.id);render()}
function updBar(){const b=$("#bar"),n=sel.size;b.hidden=!n||view==="gantt";if(n)b.innerHTML=`<b>${n} selected</b>${LV!=="view"?'<button id="bdup">Duplicate</button>':""}${LV!=="view"&&groups.length?`<select id="bmv"><option value="">Move to group…</option><option value="0">No group</option>${groups.map(g=>`<option value="${g.id}">${esc(g.name)}</option>`).join("")}</select>`:""}${LV==="full"&&wss.some(w=>w.id!=W&&["edit","full"].includes(w.level))?`<select id="bws"><option value="">Move to workspace…</option>${wss.filter(w=>w.id!=W&&["edit","full"].includes(w.level)).map(w=>`<option value="${w.id}">${esc(w.name)}</option>`).join("")}</select>`:""}${LV==="full"?'<button id="bdel">Delete</button>':""}<button id="bclr">Clear</button>`}
const BASE_COLS=[
 {id:"title",label:"Title",cell:t=>`<div class="tc">${ovBadge(t)}${cmBadge(t)}<input class="ed" data-f="title" value="${ea(t.title)}"></div>`},
 {id:"order_no",label:"Order No.",cell:t=>`<input class="ed" data-f="order_no" value="${ea(t.order_no)}">`},
 {id:"customer",label:"Customer",cell:t=>`<input class="ed" data-f="customer" list="pjl" value="${ea(t.customer)}">`},
 {id:"drawing_type",label:"Drawing type",cell:t=>`<input class="ed" data-f="drawing_type" list="dtl" value="${ea(t.drawing_type)}">`},
 {id:"dates",label:"Start / End",cls:"dates",cell:t=>`<input class="ed" type="date" data-f="start_date" value="${ea(t.start_date)}"> → <input class="ed ${late(t)?"late":""}" type="date" data-f="end_date" value="${ea(t.end_date)}">`},
 {id:"status",label:"Status",cell:t=>`<select class="ed" data-f="status">${stOpts(t.status)}</select>`},
 {id:"assigned_to",label:"Assigned to",cell:t=>`<select class="ed" data-f="assigned_to">${asOpts(t.assigned_to)}</select>`},
 {id:"group_id",label:"Group",cell:t=>`<select class="ed" data-f="group_id"><option value="">No group</option>${groups.map(g=>`<option value="${g.id}"${g.id==t.group_id?" selected":""}>${esc(g.name)}</option>`).join("")}</select>`},
 {id:"notes",label:"Notes",cell:t=>`<input class="ed" data-f="notes" value="${ea(t.notes)}">`}];
const coKey=()=>"tf_co_"+W;
const cwKey=()=>"tf_cw_"+W;
const DEFW={title:220,order_no:110,customer:160,drawing_type:140,dates:290,status:140,assigned_to:150,group_id:140,notes:220};
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
  if(r.cur){const m=cwSaved();m[r.id]=r.cur;try{localStorage.setItem(cwKey(),JSON.stringify(m))}catch(x){}$("#cr").hidden=false}
}
document.addEventListener("pointerup",endRz);document.addEventListener("pointercancel",endRz);
function colOrder(){try{return JSON.parse(localStorage.getItem(coKey())||"[]")}catch(e){return []}}
function colDefs(){
  const all=BASE_COLS.concat(cols.map(c=>({id:"x_"+c.id,label:c.name,cell:t=>xCtl(c,xv(t,c),"row")})));
  const saved=colOrder(),out=[];
  for(const id of saved){const d=all.find(x=>x.id===id);if(d&&!out.includes(d))out.push(d)}
  for(const d of all)if(!out.includes(d))out.push(d);
  return out;
}
function moveCol(from,to){
  if(from===to)return;
  const ids=colDefs().map(d=>d.id),a=ids.indexOf(from),b=ids.indexOf(to);if(a<0||b<0)return;
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
function render(){
  const ae=document.activeElement;if(ae&&ae.dataset&&ae.dataset.f&&$("#main").contains(ae)){pend=true;return}
  pend=false;for(const id of [...sel])if(!tasks.some(t=>t.id==id))sel.delete(id);
  updBar();syncFilters();
  const cur=wss.find(w=>w.id==W);document.title=(cur?cur.name+" – ":"")+"TaskFlow";
  $("#wm").hidden=me.role!=="admin";
  const dvb=$("#dv"),isDef=(me.pref_view||"list")===view;dvb.textContent=isDef?"★ Default view":"☆ Make "+view+" my default";dvb.disabled=isDef;
  $("#who").textContent=(me.name||"")+(LV==="view"?" · view only":LV==="full"&&me.role!=="admin"?" · full access":"");
  $("#wname").textContent=cur?cur.name:"";$("#vname").innerHTML=cur?wlogo(cur):"";$("#wlogo").innerHTML=cur?wlogo(cur):"";
  $("#wlinks").innerHTML=wss.map(w=>`<a class="wl${w.id==W?" on":""}" href="index.php?w=${w.id}" title="${ea(w.name)}">${wlogo(w)}</a>`).join("")||'<div class="empty" style="padding:12px">None</div>';
  if(!W){$("#stats").innerHTML="";$("#main").innerHTML='<div class="empty">You have not been given access to any workspace yet. Please ask an admin.</div>';return}
  const list=filtered();
  const gt=tasks.filter(inGroup);
  const fsv=$("#fs").value;
  $("#stats").innerHTML=`<div class="stat${fsv?"":" on"}" data-fs=""><b>${gt.length}</b><span>All tasks</span></div>`+
    ST.map(([k,n])=>`<div class="stat${fsv===k?" on":""}" data-fs="${k}" title="Show only this status"${scol(k)?` style="border-top:4px solid ${scol(k)}"`:""}><b>${gt.filter(t=>t.status===k).length}</b><span>${esc(n)}</span></div>`).join("")+
    `<div class="stat"><b>${gt.filter(late).length}</b><span>Overdue</span></div>`+
    (gt.some(t=>ov(t).length)||ovOnly?`<div class="stat${ovOnly?" on":""}" data-ov="1" title="Show only tasks that overlap for the same person" style="border-top:4px solid #dc2626"><b>${gt.filter(t=>ov(t).length).length}</b><span>⚠ Overlaps</span></div>`:"");
  const gtb=$("#gtabs"),nn=tasks.filter(t=>!t.group_id).length;
  gtb.hidden=!groups.length;
  gtb.innerHTML=`<a class="gt${G===""?" on":""}" data-g="">All <small>${tasks.length}</small></a>`+groups.map(g=>`<a class="gt${String(g.id)===G?" on":""}" data-g="${g.id}">${esc(g.name)} <small>${tasks.filter(t=>t.group_id==g.id).length}</small></a>`).join("")+(nn?`<a class="gt${G==="none"?" on":""}" data-g="none">No group <small>${nn}</small></a>`:"");
  if(view==="board"){
    $("#cr").hidden=true;
    $("#main").innerHTML=`<div class="board" style="grid-template-columns:repeat(${ST.length-1},minmax(220px,1fr))">`+ST.filter(s=>s[0]!=="done").map(([k,n])=>{
      const c=list.filter(t=>t.status===k);
      return `<div class="col" data-s="${k}"><h3><span>${scol(k)?`<i class="dot" style="background:${scol(k)}"></i>`:""}${n}</span><em>${c.length}</em></h3>${c.map(cardHTML).join("")||'<div class="empty">No tasks</div>'}</div>`}).join("")+`</div>`+completedHTML(list);
  }else if(view==="gantt"){
    $("#cr").hidden=true;renderGantt(list);
  }else{
    const defs=colDefs(),span=defs.length+2,gc=getGC();
    const act_=list.filter(t=>t.status!=="done"),done_=list.filter(t=>t.status==="done"),nd=done_.length;
    const rowHTML=t=>`<tr class="row" data-id="${t.id}"${scol(t.status)?` style="--sc:${scol(t.status)}"`:""}><td><input type="checkbox" class="rc"${sel.has(t.id)?" checked":""}></td>${defs.map(d=>`<td${d.cls?` class="${d.cls}"`:""}>${d.cell(t)}</td>`).join("")}<td><button class="o" title="Open details">⋯</button></td></tr>`;
    let rows="";
    if(groups.length){
      const filt=$("#q").value||$("#fs").value||$("#fp").value||$("#fa").value;
      let secs=groups.map(g=>({id:String(g.id),name:g.name})).concat([{id:"none",name:"No group"}]);
      if(G!=="")secs=secs.filter(x=>x.id===G);
      for(const sc of secs){
        const items=act_.filter(t=>sc.id==="none"?!t.group_id:String(t.group_id)===sc.id),dn=done_.filter(t=>sc.id==="none"?!t.group_id:String(t.group_id)===sc.id).length;
        if(sc.id==="none"&&!items.length&&!dn)continue;
        if(filt&&!items.length&&!dn)continue;
        const col=gc.has(sc.id);
        rows+=`<tr class="gh" data-gh="${sc.id}" title="Click to show or hide"><th colspan="${span}">${col?"▸":"▾"} 📁 ${esc(sc.name)} <small style="font-weight:400">${items.length} open${dn?` · ${dn} completed`:""}</small>${LV!=="view"&&sc.id!=="none"?` <button class="o" data-gadd="${sc.id}" style="margin-left:8px">+ Task</button>`:""}</th></tr>`;
        if(!col)rows+=items.map(rowHTML).join("");
      }
    }else rows=act_.map(rowHTML).join("");
    if(nd)rows+=`<tr class="dh" style="cursor:pointer" title="Click to show or hide"><th colspan="${span}">${dc?"▸":"▾"} ✔ Completed (${nd})</th></tr>`+(dc?"":done_.map(rowHTML).join(""));
    $("#main").innerHTML=`<div class="listwrap"><table style="width:${36+defs.reduce((a,d)=>a+colW(d.id),0)+48}px"><colgroup><col style="width:36px">${defs.map(d=>`<col data-c="${d.id}" style="width:${colW(d.id)}px">`).join("")}<col style="width:48px"></colgroup><thead><tr id="hrow"><th><input type="checkbox" id="all"${vis().length&&vis().every(t=>sel.has(t.id))?" checked":""}></th>${defs.map(d=>`<th class="ch" draggable="true" data-col="${d.id}" title="Drag to rearrange">${esc(d.label)}<span class="rz" data-rz="${d.id}" draggable="false"></span></th>`).join("")}<th></th></tr></thead><tbody>`+
      (rows||`<tr><td colspan="${span}" class="empty">No tasks yet. Click “New task”.</td></tr>`)+`</tbody></table></div>`;
    $("#cr").hidden=!colOrderSaved();
  }
  lockView();
}
function openDlg(t,grp){
  editing=t?t.id:null;
  $("#t").value=t?t.title:"";$("#on").value=t?t.order_no||"":"";$("#pj").value=t?t.customer||"":"";$("#dt").value=t?t.drawing_type||"":"";
  $("#sd").value=t?t.start_date||"":"";$("#du").value=t?t.end_date||"":"";$("#st").innerHTML=stOpts(t?t.status:ST[0][0]);
  $("#as").innerHTML=asOpts(t?t.assigned_to:"");$("#nt").value=t?t.notes||"":"";
  $("#gp").innerHTML=`<option value="">No group</option>`+groups.map(g=>`<option value="${g.id}">${esc(g.name)}</option>`).join("");$("#gp").value=t?(t.group_id||""):(/^\d+$/.test(grp!==undefined?grp:G)?(grp!==undefined?grp:G):"");
  $("#xf").innerHTML=cols.map(c=>`<label>${esc(c.name)}</label>`+xCtl(c,t?xv(t,c):"","dlg")).join("");
  const on=$("#ovn");if(t&&ov(t).length){on.style.display="block";on.textContent="⚠ "+ovText(t)}else on.style.display="none";
  $("#del").style.display=t&&LV==="full"?"":"none";
  const ro=LV==="view";$("#sv").style.display=ro?"none":"";
  document.querySelectorAll("#dlg input,#dlg select,#dlg textarea").forEach(el=>el.disabled=ro);
  thrInit(t);document.querySelectorAll("#thr select,#thr textarea").forEach(el=>el.disabled=false);
  $("#dlg").showModal();$("#t").focus();if(t)thrLoad(true);
}
const fmtT=s=>(s||"").slice(0,16).replace("T"," ");
function thrInit(t){
  thrId=t?t.id:null;$("#thr").hidden=!t;$("#thl").innerHTML="";$("#cbody").value="";if(!t)return;
  $("#cto").innerHTML=`<option value="">To: everyone</option>`+users.filter(u=>u.id!=me.id).map(u=>`<option value="${u.id}">To: ${esc(u.name)}</option>`).join("");
  $("#ckind").value="comment";
}
async function thrLoad(first){
  if(!thrId||!$("#dlg").open)return;
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
  if(w===W){const x=tasks.find(z=>z.id==t);if(x)openDlg(x)}else location.href="index.php?w="+w+"&t="+t});
$("#add").onclick=()=>openDlg(null);
$("#cx").onclick=()=>$("#dlg").close();
$("#sv").onclick=async()=>{
  const title=$("#t").value.trim();if(!title)return;
  const x={};document.querySelectorAll("#xf [data-x]").forEach(el=>x[el.dataset.x]=el.value);
  try{await api("save",{id:editing,title,order_no:$("#on").value,customer:$("#pj").value,drawing_type:$("#dt").value,start_date:$("#sd").value,end_date:$("#du").value,status:$("#st").value,assigned_to:$("#as").value,notes:$("#nt").value,group_id:$("#gp").value,x});$("#dlg").close();load(true)}catch(e){alert(e.message)}
};
$("#del").onclick=()=>{if(editing&&confirm("Delete this task?")){$("#dlg").close();act("delete",{ids:[editing]})}};
$("#dv").onclick=async()=>{try{await api("pref_view",{v:view});me.pref_view=view;render()}catch(e){alert(e.message)}};
function syncViewBtns(){for(const [id,v] of [["vl","list"],["vb","board"],["vg","gantt"]])$("#"+id).classList.toggle("on",view===v)}
$("#vb").onclick=()=>{view="board";syncViewBtns();render()};
$("#vl").onclick=()=>{view="list";syncViewBtns();render()};
$("#vg").onclick=()=>{view="gantt";syncViewBtns();render()};
for(const id of ["q","fs","fp","fa"])$("#"+id).addEventListener("input",render);

$("#main").addEventListener("click",e=>{
  if(e.target.closest(".dh")){toggleDone();return}
  const gh=e.target.closest(".gh");if(gh){const ad=e.target.closest("[data-gadd]");if(ad)openDlg(null,ad.dataset.gadd);else toggleGroup(gh.dataset.gh);return}
  if(e.target.classList.contains("cc"))return;
  const el=e.target.closest("[data-id]");if(!el)return;
  if(el.tagName==="TR"&&!e.target.closest(".o"))return;
  const t=tasks.find(x=>x.id==el.dataset.id);if(t)openDlg(t);
});
$("#main").addEventListener("change",e=>{
  const f=e.target.dataset.f,tr=e.target.closest("tr[data-id]");
  if(f&&tr){const v=e.target.value;e.target.blur();act("field",{id:tr.dataset.id,f,v});return}
  if(e.target.id==="all"){vis().forEach(t=>e.target.checked?sel.add(t.id):sel.delete(t.id));render()}
  else if(e.target.classList.contains("cc")){const id=+e.target.closest(".card").dataset.id;e.target.checked?sel.add(id):sel.delete(id);updBar()}
  else if(e.target.classList.contains("rc")){const id=+e.target.closest("tr").dataset.id;e.target.checked?sel.add(id):sel.delete(id);render()}
});
$("#main").addEventListener("keydown",e=>{if(e.key==="Enter"&&e.target.dataset.f)e.target.blur()});
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
// Logos with a transparent background are shown as white silhouettes on the navy bars
document.addEventListener("load",e=>{const im=e.target;if(!im||im.tagName!=="IMG"||!im.closest(".wl,#wlogo,#vname"))return;
  try{const c=document.createElement("canvas");c.width=c.height=32;const x=c.getContext("2d");x.drawImage(im,0,0,32,32);const d=x.getImageData(0,0,32,32).data;
    const px=(i,j)=>d[(j*32+i)*4+3];if(px(0,0)<40&&px(31,0)<40&&px(0,31)<40&&px(31,31)<40)im.classList.add("wh")}catch(err){}
},true);
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

// ---- Move selected tasks to another workspace ----
$("#bar").addEventListener("change",async e=>{
  if(e.target.id!=="bws"||!e.target.value)return;const to=+e.target.value,ids=[...sel],w=wss.find(x=>x.id==to);
  if(!confirm("Move "+ids.length+" task(s) to \""+(w?w.name:"")+"\"?\n\nThey leave this workspace. Group is cleared; status, people and matching custom columns are carried over where possible."))return load(true);
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

function wlogo(w){
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
  $("#wlist").innerHTML=wss.map(w=>`<div class="wrow"><div class="acts"><span class="wl" style="width:44px;height:44px;flex:none;padding:2px;pointer-events:none">${wlogo(w)}</span><span style="flex:1"><b>${esc(w.name)}</b>${w.id==W?' <small style="color:var(--mut)">(current)</small>':""}</span><button data-wr="${w.id}">Rename</button><button data-wlg="${w.id}">Logo</button>${+w.hl?`<button data-wlx="${w.id}" title="Remove logo">✕</button>`:""}<button data-wacc="${w.id}">Access</button><button data-wd="${w.id}">Delete</button></div>
    <div class="wp" ${openWp==w.id?"":"hidden"}><small style="color:var(--mut)">Admins always see every workspace. Tick the members who may open this one and choose what they can do:</small>
    ${members.map(u=>`<label><input type="checkbox" data-wu="${u.id}"${(access[w.id]||{})[u.id]?" checked":""}> <span style="flex:1">${esc(u.name)}</span><select data-wl="${u.id}">${Object.entries(LVN).map(([k,n])=>`<option value="${k}"${((access[w.id]||{})[u.id]||"edit")===k?" selected":""}>${n}</option>`).join("")}</select></label>`).join("")||'<div class="empty">No members yet</div>'}
    <div class="acts"><span class="sp"></span><button class="p" data-wsave="${w.id}">Save access</button></div></div></div>`).join("");
}
$("#wm").onclick=()=>{renderWs();$("#wdlg").showModal()};
$("#sbtn").onclick=e=>{e.stopPropagation();$("#smenu").hidden=!$("#smenu").hidden};
$("#smenu").addEventListener("click",()=>{$("#smenu").hidden=true});
document.addEventListener("click",e=>{if(!e.target.closest("#setw"))$("#smenu").hidden=true});
document.addEventListener("keydown",e=>{if(e.key==="Escape")$("#smenu").hidden=true});
$("#stg").onclick=()=>{const c=document.body.classList.toggle("sc");$("#stg").textContent=c?"▶":"◀";try{localStorage.setItem("tf_sc",c?"1":"0")}catch(e){}};
$("#wf").addEventListener("change",async e=>{const f=e.target.files[0];if(!f||!logoFor)return;
  try{const data=await fileToLogo(f);await api("ws_logo",{id:logoFor,data});await load(true);renderWs()}catch(err){alert(err.message)}logoFor=null});
$("#wc").onclick=()=>$("#wdlg").close();
$("#wa").onclick=async()=>{try{const r=await api("ws_add",{name:$("#wn").value});location="index.php?w="+r.id}catch(e){alert(e.message)}};
$("#wlist").addEventListener("click",async e=>{
  const d=e.target.dataset;try{
    if(d.wr){const w=wss.find(x=>x.id==d.wr),n=prompt("New name for this workspace:",w.name);if(n&&n.trim()){await api("ws_rename",{id:d.wr,name:n});await load(true);renderWs()}}
    if(d.wlg){logoFor=d.wlg;$("#wf").value="";$("#wf").click()}
    if(d.wlx){await api("ws_logo",{id:d.wlx,data:""});await load(true);renderWs()}
    if(d.wacc){openWp=openWp==d.wacc?null:d.wacc;renderWs()}
    if(d.wsave){const us=[...e.target.closest(".wp").querySelectorAll("[data-wu]:checked")].map(c=>({id:+c.dataset.wu,level:c.closest("label").querySelector("[data-wl]").value}));await api("ws_access",{id:d.wsave,users:us});await load(true);renderWs();alert("Access saved.")}
    if(d.wd){const w=wss.find(x=>x.id==d.wd);if(confirm("Delete the workspace \""+w.name+"\" and ALL its tasks and custom columns? This cannot be undone.")){await api("ws_del",{id:d.wd});if(d.wd==W){location="index.php"}else{await load(true);renderWs()}}}
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
$("#stats").addEventListener("click",e=>{if(e.target.closest("[data-ov]")){ovOnly=!ovOnly;render();return}const el=e.target.closest("[data-fs]");if(!el)return;const f=$("#fs");f.value=f.value===el.dataset.fs?"":el.dataset.fs;render()});
$("#gtabs").addEventListener("click",e=>{const a=e.target.closest("[data-g]");if(!a)return;G=a.dataset.g;try{localStorage.setItem("tf_g_"+W,G)}catch(x){}sel.clear();render()});
function renderGr(){
  const full=LV==="full";
  $("#glist").innerHTML=groups.map(g=>`<div class="acts"><span style="flex:1">📁 ${esc(g.name)} <small style="color:var(--mut)">${tasks.filter(t=>t.group_id==g.id).length} task(s)</small></span>${full?`<button data-gr="${g.id}">Rename</button><button data-gd="${g.id}">Delete</button>`:""}</div>`).join("")||'<div class="empty">No groups yet</div>';
}
$("#om").onclick=()=>{$("#oh").checked=!!+ovset.hand;$("#oo").checked=!!+ovset.one;$("#odlg").showModal()};
$("#oc").onclick=()=>$("#odlg").close();
$("#os").onclick=async()=>{try{await api("ov_set",{hand:$("#oh").checked,one:$("#oo").checked});$("#odlg").close();await load(true)}catch(e){alert(e.message)}};
$("#gm").onclick=()=>{renderGr();$("#gdlg").showModal()};
$("#gc").onclick=()=>$("#gdlg").close();
$("#ga").onclick=async()=>{try{const r=await api("gr_add",{name:$("#gn").value});$("#gn").value="";await load(true);G=String(r.id);try{localStorage.setItem("tf_g_"+W,G)}catch(x){}render();renderGr()}catch(e){alert(e.message)}};
$("#glist").addEventListener("click",async e=>{
  const d=e.target.dataset;try{
    if(d.gr){const cur=groups.find(g=>g.id==d.gr),n=prompt("New name for this group:",cur.name);if(n&&n.trim()){await api("gr_ren",{id:d.gr,name:n});await load(true);renderGr()}}
    if(d.gd){const cur=groups.find(g=>g.id==d.gd);if(confirm("Delete the group \""+cur.name+"\"? Its tasks are kept and moved to No group.")){await api("gr_del",{id:d.gd});if(G===String(d.gd))G="";await load(true);renderGr()}}
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
    const secs=groups.map(g=>({name:g.name,l:open.filter(t=>t.group_id==g.id)})).concat([{name:"No group",l:open.filter(t=>!t.group_id)}]).filter(z=>z.l.length);
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
  $("#clist").innerHTML=cols.map(c=>`<div class="acts"><span style="flex:1">${esc(c.name)} <small style="color:var(--mut)">${c.ctype}${c.ctype==="dropdown"?": "+esc((c.options||[]).join(", ")):""}</small></span>${me.role==="admin"?`<button data-cd="${c.id}">Remove</button>`:""}</div>`).join("")||'<div class="empty">No custom columns yet</div>';
}
$("#cm").onclick=()=>{renderCols();$("#cdlg").showModal()};
$("#cc").onclick=()=>$("#cdlg").close();
$("#ct").onchange=()=>{$("#co").style.display=$("#ct").value==="dropdown"?"":"none"};
$("#ca").onclick=async()=>{
  try{await api("col_add",{name:$("#cn").value,ctype:$("#ct").value,options:$("#co").value});
    $("#cn").value="";$("#co").value="";await load(true);renderCols()}catch(e){alert(e.message)}
};
$("#clist").addEventListener("click",async e=>{
  const id=e.target.dataset.cd;if(!id||!confirm("Remove this column? The values entered in it will be deleted."))return;
  try{await api("col_del",{id});await load(true);renderCols()}catch(err){alert(err.message)}
});
function renderUsers(){
  $("#tlist").innerHTML=allUsers.map(u=>`<div class="acts"><span style="flex:1">${esc(u.name)} <small style="color:var(--mut)">${u.role}</small></span><button data-pw="${u.id}">Reset password</button>${u.id==me.id?"":`<button data-rm="${u.id}">Remove</button>`}</div>`).join("");
}
$("#tm").onclick=()=>{renderUsers();$("#tdlg").showModal()};
$("#tc").onclick=()=>$("#tdlg").close();
$("#ta").onclick=async()=>{
  try{await api("user_add",{name:$("#un").value,username:$("#uu").value,password:$("#up").value,role:$("#ur").value});
    for(const i of ["un","uu","up"])$("#"+i).value="";await load(true);renderUsers()}catch(e){alert(e.message)}
};
$("#tlist").addEventListener("click",async e=>{
  try{
    if(e.target.dataset.rm&&confirm("Remove this user? Their tasks become unassigned.")){await api("user_del",{id:e.target.dataset.rm});await load(true);renderUsers()}
    if(e.target.dataset.pw){const p=prompt("New password (8+ characters):");if(p){await api("user_pass",{id:e.target.dataset.pw,password:p});alert("Password changed.")}}
  }catch(err){alert(err.message)}
});
load(true);
function setLive(ok){const l=$("#live");l.textContent=ok?"● Live":"○ Offline";l.style.color=ok?"var(--lo)":"var(--mut)"}
function busy(){return document.querySelector("dialog[open]")||dragId||dragCol||rz||gdrag}
async function poll(){
  if(document.hidden)return;
  try{
    const r=await api("ping");setLive(true);
    if($("#dlg").open&&thrId&&r.v!==thrVer){thrVer=r.v;thrLoad(false)}
    if(r.v!==lastVer||dirty){if(busy()){dirty=true;return}dirty=false;await load()}
  }catch(e){if(e.message!=="auth")setLive(false)}
}
setInterval(poll,3000);
document.addEventListener("visibilitychange",()=>{if(!document.hidden)poll()});
window.addEventListener("focus",poll);
document.addEventListener("close",()=>{if(dirty)poll()},true);
document.addEventListener("dragend",()=>{if(dirty)setTimeout(poll,50)});
</script></body></html>
