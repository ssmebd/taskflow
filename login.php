<?php
if(isset($_GET['logo'])){header('Content-Type: image/svg+xml');header('Cache-Control: public, max-age=86400');echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><defs><linearGradient id="tfbg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2557b5"/><stop offset=".55" stop-color="#12337a"/><stop offset="1" stop-color="#081a40"/></linearGradient><radialGradient id="tfhl" cx=".2" cy=".08" r=".75"><stop offset="0" stop-color="#fff" stop-opacity=".22"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient><linearGradient id="tfgr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6ee7a0"/><stop offset="1" stop-color="#16a34a"/></linearGradient><linearGradient id="tfsk" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#bae6fd"/><stop offset="1" stop-color="#38bdf8"/></linearGradient><filter id="tfsh" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="9" stdDeviation="8" flood-color="#020b22" flood-opacity=".35"/></filter></defs><rect width="512" height="512" rx="116" fill="url(#tfbg)"/><rect width="512" height="512" rx="116" fill="url(#tfhl)"/><g transform="translate(0.00,0.00) scale(1.0)"><g filter="url(#tfsh)"><rect x="84" y="118" width="226" height="68" rx="34" fill="#fff"/><rect x="150" y="222" width="226" height="68" rx="34" fill="url(#tfsk)"/><path d="M216 326 H372 L426 360 L372 394 H216 a34 34 0 0 1 0-68z" fill="url(#tfgr)"/></g><path d="M120 152 l22 22 l40 -44" fill="none" stroke="#12337a" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/></g></svg>';exit;}
if(isset($_GET['discover'])){header('Content-Type: text/html; charset=utf-8');header('Cache-Control: public, max-age=300');echo <<<'TFDISCOVER'
<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow · Discover</title>
<meta name="description" content="TaskFlow: task, drawing and hours tracking for engineering and drafting teams.">
<link rel="icon" type="image/svg+xml" href="login.php?logo=1">
<style>
:root{--navy:#0f2a5c;--navy2:#081a3d;--sky:#38bdf8;--green:#22c55e;--ink:#0f172a;--mut:#64748b;--line:#e2e8f0;--bg:#f6f8fc}
*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;font-family:"Segoe UI",system-ui,-apple-system,Roboto,Arial,sans-serif;color:var(--ink);background:var(--bg);line-height:1.55}
a{color:inherit}.w{max-width:1120px;margin:0 auto;padding:0 22px}
nav.top{position:sticky;top:0;z-index:20;background:rgba(8,26,61,.92);backdrop-filter:blur(10px);border-bottom:1px solid rgba(255,255,255,.08)}
nav.top .w{display:flex;align-items:center;gap:14px;height:60px}.brand{display:flex;align-items:center;gap:10px;color:#fff;text-decoration:none;font-weight:800;font-size:19px}.brand img{width:34px;height:34px;border-radius:9px}.brand small{display:block;font-size:9px;letter-spacing:1.5px;color:#93c5fd;font-weight:700;margin-top:-3px}
nav.top .sp{flex:1}nav.top a.l{color:#cbd5e1;text-decoration:none;font-size:14px;padding:6px 10px;border-radius:8px}nav.top a.l:hover{background:rgba(255,255,255,.1);color:#fff}
.btn{display:inline-flex;align-items:center;gap:8px;border-radius:12px;padding:12px 20px;font-weight:700;font-size:15px;text-decoration:none;border:0;cursor:pointer}.b1{background:var(--sky);color:var(--navy)}.b1:hover{background:#7dd3fc}.b2{background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.3)}.b2:hover{background:rgba(255,255,255,.2)}
.hero{background:radial-gradient(1100px 500px at 85% -10%,rgba(56,189,248,.28),transparent 60%),radial-gradient(800px 500px at -10% 110%,rgba(34,197,94,.2),transparent 55%),linear-gradient(160deg,#0f2a5c,#081a3d);color:#fff;padding:72px 0 96px;overflow:hidden}
.hero .w{display:grid;grid-template-columns:1.05fr .95fr;gap:42px;align-items:center}
.eyebrow{display:inline-block;font-size:12px;letter-spacing:1.6px;font-weight:800;color:#7dd3fc;background:rgba(56,189,248,.12);border:1px solid rgba(56,189,248,.3);border-radius:999px;padding:5px 12px}
h1{font-size:clamp(34px,5vw,54px);line-height:1.08;margin:16px 0 14px;font-weight:900;letter-spacing:-.5px}h1 em{font-style:normal;background:linear-gradient(90deg,#38bdf8,#22c55e);-webkit-background-clip:text;background-clip:text;color:transparent}
.hero p.lead{font-size:18px;color:#cbd5e1;max-width:520px;margin:0 0 26px}.cta{display:flex;flex-wrap:wrap;gap:12px}
.ticks{display:flex;flex-wrap:wrap;gap:8px 18px;margin-top:26px;color:#94a3b8;font-size:13px}.ticks span:before{content:"✓";color:var(--green);font-weight:900;margin-right:6px}
/* gantt mock */
.mock{background:#fff;border-radius:18px;box-shadow:0 30px 70px rgba(0,0,0,.45);color:var(--ink);overflow:hidden;transform:perspective(1400px) rotateY(-6deg) rotateX(3deg)}
.mock .bar{display:flex;gap:6px;padding:11px 14px;background:#0f2a5c}.mock .bar i{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.35)}
.mock .bd{padding:16px 18px}.mock h4{margin:0 0 10px;font-size:13px;color:var(--mut);letter-spacing:.8px;text-transform:uppercase}
.gr{display:grid;grid-template-columns:96px 1fr;gap:10px;align-items:center;margin:9px 0;font-size:12px;font-weight:600}.gt{position:relative;height:20px;background:#f1f5f9;border-radius:6px}.gt b{position:absolute;top:0;height:20px;border-radius:6px}
.pills{display:flex;gap:7px;flex-wrap:wrap;margin-top:14px}.pill{font-size:11px;font-weight:800;border-radius:8px;padding:4px 10px}
.sec{padding:84px 0}.sec.alt{background:#fff}.sec.dark{background:linear-gradient(160deg,#0f2a5c,#081a3d);color:#fff}
.k{font-size:12px;font-weight:800;letter-spacing:1.5px;color:#0ea5e9;text-transform:uppercase}h2{font-size:clamp(26px,3.6vw,38px);line-height:1.15;margin:8px 0 12px;font-weight:900;letter-spacing:-.3px}.sub{color:var(--mut);max-width:640px;font-size:17px;margin:0 0 34px}.dark .sub{color:#cbd5e1}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}.card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px;transition:.25s}.card:hover{transform:translateY(-4px);box-shadow:0 14px 34px rgba(15,42,92,.13);border-color:#bfdbfe}
.ic{width:46px;height:46px;border-radius:13px;display:grid;place-items:center;font-size:23px;margin-bottom:14px}.card h3{margin:0 0 6px;font-size:18px}.card p{margin:0;color:var(--mut);font-size:14.5px}
.two{display:grid;grid-template-columns:1fr 1fr;gap:42px;align-items:center}
.list{margin:0;padding:0;list-style:none;display:grid;gap:12px}.list li{display:flex;gap:12px;font-size:15.5px}.list li b{display:block}.list .d{flex:none;width:26px;height:26px;border-radius:8px;background:#dcfce7;color:#15803d;display:grid;place-items:center;font-weight:900;font-size:14px;margin-top:2px}
.tbl{background:#fff;border:1px solid var(--line);border-radius:16px;overflow:hidden;box-shadow:0 18px 44px rgba(15,42,92,.12);font-size:13px}.tbl .r{display:grid;grid-template-columns:1.6fr 1fr .9fr;gap:10px;padding:11px 16px;border-bottom:1px solid #eef2f7;align-items:center}.tbl .r:last-child{border:0}.tbl .h{background:#f8fafc;font-size:11px;color:var(--mut);font-weight:800;letter-spacing:.8px;text-transform:uppercase}.st{display:inline-block;font-size:11px;font-weight:800;border-radius:7px;padding:3px 9px}.av{display:inline-grid;place-items:center;width:22px;height:22px;border-radius:50%;color:#fff;font-size:10px;font-weight:800;margin-right:6px;vertical-align:middle}
.kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px}.kpi{border-radius:12px;padding:12px;text-align:center;border-top:3px solid}.kpi b{display:block;font-size:30px;line-height:1.1}.kpi span{font-size:11px;color:#475569;font-weight:600}
.num{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;text-align:center}.num div{padding:22px 10px;border-radius:16px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12)}.num b{display:block;font-size:38px;background:linear-gradient(90deg,#38bdf8,#22c55e);-webkit-background-clip:text;background-clip:text;color:transparent}.num span{font-size:14px;color:#cbd5e1}
.final{background:linear-gradient(135deg,#0ea5e9,#22c55e);color:#06223f;border-radius:24px;padding:50px 34px;text-align:center}.final h2{margin-top:0}.final p{max-width:560px;margin:0 auto 24px;font-size:17px}.final .btn{background:#0f2a5c;color:#fff}.final .btn.o{background:rgba(255,255,255,.35);color:#06223f}
footer{background:var(--navy2);color:#94a3b8;padding:30px 0;font-size:13px;text-align:center}
.rv{opacity:0;transform:translateY(22px);transition:opacity .7s,transform .7s}.rv.in{opacity:1;transform:none}@media(prefers-reduced-motion:reduce){.rv{opacity:1;transform:none;transition:none}html{scroll-behavior:auto}.mock{transform:none}}
@media(max-width:900px){.hero .w,.two{grid-template-columns:1fr}.grid{grid-template-columns:1fr 1fr}.num{grid-template-columns:1fr 1fr}.mock{transform:none}nav.top a.l{display:none}}@media(max-width:560px){.grid{grid-template-columns:1fr}.hero{padding:48px 0 64px}.sec{padding:56px 0}}
</style></head><body>
<nav class="top"><div class="w"><a class="brand" href="#top"><img src="login.php?logo=1" alt=""><span>TaskFlow<small>POWERED BY GURUCADD</small></span></a><span class="sp"></span><a class="l" href="#features">Features</a><a class="l" href="#hours">Hours</a><a class="l" href="#reports">Reports</a><a class="l" href="#safe">Control</a><a class="btn b1" style="padding:8px 16px;font-size:14px" href="login.php">Sign in</a></div></nav>
<header class="hero" id="top"><div class="w"><div>
<span class="eyebrow">TASK · DRAWING · HOURS</span>
<h1>Every drawing, task and hour, <em>flowing in one place.</em></h1>
<p class="lead">TaskFlow is the work tracker built for engineering and drafting teams. Plan the week, see who is on what, log hours with automatic overtime, and send clients a clear progress report without lifting a finger.</p>
<div class="cta"><a class="btn b1" href="mailto:shihab.shahriar@gurucadd.com?subject=TaskFlow%20enquiry">Talk to GURUCADD →</a><a class="btn b2" href="#features">See what it does</a></div>
<div class="ticks"><span>List, Board and Gantt</span><span>Windows and Android apps</span><span>Several companies, one login</span></div></div>
<div class="mock rv"><div class="bar"><i></i><i></i><i></i></div><div class="bd"><h4>This week · Gantt</h4>
<div class="gr"><span>PNL-2041 Wall panels</span><div class="gt"><b style="left:2%;width:46%;background:#22c55e"></b></div></div>
<div class="gr"><span>FRM-1180 Floor frame</span><div class="gt"><b style="left:20%;width:55%;background:#38bdf8"></b></div></div>
<div class="gr"><span>TRS-0907 Roof truss</span><div class="gt"><b style="left:38%;width:34%;background:#0f2a5c"></b></div></div>
<div class="gr"><span>STR-3302 Stair set</span><div class="gt"><b style="left:55%;width:42%;background:#22c55e"></b></div></div>
<div class="pills"><span class="pill" style="background:#dcfce7;color:#15803d">7 Ongoing</span><span class="pill" style="background:#fef3c7;color:#b45309">1 In review</span><span class="pill" style="background:#cffafe;color:#0e7490">1 Ready to start</span><span class="pill" style="background:#e0e7ff;color:#4338ca">16 All tasks</span></div></div></div></div></header>

<section class="sec" id="features"><div class="w"><div class="k rv">What you get</div><h2 class="rv">Built around how drafting teams really work</h2><p class="sub rv">Order numbers, customers, drawing types and revisions are first-class, not bolted on.</p>
<div class="grid">
<div class="card rv"><div class="ic" style="background:#dbeafe">📋</div><h3>Tasks, your way</h3><p>Switch between a sortable List, a Kanban Board and a Gantt timeline. Group by company, project or drawing type. Everyone sees the same live picture.</p></div>
<div class="card rv"><div class="ic" style="background:#dcfce7">⏱</div><h3>Hours with automatic overtime</h3><p>Log time against the task in two taps. Monday to Friday the first 8 hours are regular; the rest, and weekends, become overtime on their own.</p></div>
<div class="card rv"><div class="ic" style="background:#fef3c7">💬</div><h3>Always in the loop</h3><p>Comments, instructions and chat on every task, plus phone and desktop notifications when work is assigned or a deadline is near.</p></div>
<div class="card rv"><div class="ic" style="background:#ede9fe">📊</div><h3>Reports that write themselves</h3><p>A weekly status email for clients, an hours report by person, company or group, and a worklog and invoice view for billing.</p></div>
<div class="card rv"><div class="ic" style="background:#cffafe">🏢</div><h3>Many companies, one login</h3><p>Each company has its own tab, logo, statuses and access list. Admins see the whole picture; members see what they are given.</p></div>
<div class="card rv"><div class="ic" style="background:#fee2e2">📱</div><h3>Works where you work</h3><p>In the browser, as a Windows desktop app with company tabs, and as an Android app with push alerts. It remembers your sign-in.</p></div>
</div></div></section>

<section class="sec alt" id="hours"><div class="w two"><div><div class="k rv">Know where the time goes</div><h2 class="rv">Hours your team will actually log</h2><p class="sub rv" style="margin-bottom:22px">A gentle nudge at 8am and 3pm, a personal “My hours check” that shows missed days, and totals that always add up.</p>
<ul class="list rv"><li><span class="d">✓</span><div><b>Only you and an admin change your hours</b>Nobody can add or delete another person’s time.</div></li><li><span class="d">✓</span><div><b>Regular and overtime, calculated for you</b>No spreadsheets, no arguments at month end.</div></li><li><span class="d">✓</span><div><b>Excel in one click</b>Download a worklog laid out the way clients expect.</div></li></ul></div>
<div class="tbl rv"><div class="r h"><span>Person</span><span>Regular</span><span>Overtime</span></div>
<div class="r"><span><i class="av" style="background:#2563eb">AR</i>Alex R.</span><span>40.00</span><span>3.50</span></div><div class="r"><span><i class="av" style="background:#d97706">SK</i>Sam K.</span><span>40.00</span><span>0.00</span></div><div class="r"><span><i class="av" style="background:#9333ea">PN</i>Priya N.</span><span>36.50</span><span>8.00</span></div><div class="r"><span><i class="av" style="background:#0d9488">JM</i>Jon M.</span><span>32.00</span><span>0.00</span></div></div></div></section>

<section class="sec" id="reports"><div class="w two"><div class="rv" style="background:#fff;border:1px solid var(--line);border-radius:18px;padding:20px;box-shadow:0 18px 44px rgba(15,42,92,.12)"><div class="kpis"><div class="kpi" style="background:#f0fdf4;border-color:#16a34a"><b style="color:#16a34a">3</b><span>Completed this week</span></div><div class="kpi" style="background:#eff6ff;border-color:#2563eb"><b style="color:#2563eb">7</b><span>Ongoing now</span></div><div class="kpi" style="background:#fffbeb;border-color:#d97706"><b style="color:#d97706">2</b><span>Starting in 3 days</span></div></div>
<div class="tbl" style="box-shadow:none"><div class="r"><span><b style="color:#0f2a5c">PNL-2041</b> Wall panels</span><span><i class="av" style="background:#d97706">SK</i>Sam K.</span><span style="color:#16a34a;font-weight:800">✓ Wed 28 Oct</span></div><div class="r"><span><b style="color:#0f2a5c">FRM-1180</b> Floor frame</span><span><i class="av" style="background:#2563eb">AR</i>Alex R.</span><span>Ends Fri 30 Oct</span></div></div></div>
<div><div class="k rv">Clients stay informed</div><h2 class="rv">A weekly report they will actually read</h2><p class="sub rv" style="margin-bottom:22px">Pick the companies, groups and tasks to include, choose the guests and the day and time. TaskFlow sends a branded summary with your logo and theirs.</p>
<ul class="list rv"><li><span class="d">✓</span><div><b>Completed, ongoing and starting soon</b>With a breakdown by drawing type, such as FDS and Jobsheets.</div></li><li><span class="d">✓</span><div><b>Different views for different guests</b>Leave out anything a client should not see.</div></li><li><span class="d">✓</span><div><b>Sent through Microsoft 365</b>It arrives from your own domain.</div></li></ul></div></div></section>

<section class="sec dark" id="safe"><div class="w"><div class="k rv" style="color:#7dd3fc">In control</div><h2 class="rv">Simple for the team, safe for the business</h2><p class="sub rv">Admins keep the keys. Members get a clean workspace without the clutter.</p>
<div class="grid" style="margin-bottom:30px">
<div class="card rv" style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.14)"><div class="ic" style="background:rgba(56,189,248,.18)">🔐</div><h3 style="color:#fff">Roles and access</h3><p style="color:#cbd5e1">Admin, member and guest roles, with view, edit or full access per company. Sign in with a username or an email address.</p></div>
<div class="card rv" style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.14)"><div class="ic" style="background:rgba(34,197,94,.18)">✅</div><h3 style="color:#fff">Approvals for deletions</h3><p style="color:#cbd5e1">Members request, admins approve. A usage log records who did what and when.</p></div>
<div class="card rv" style="background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.14)"><div class="ic" style="background:rgba(245,158,11,.18)">💾</div><h3 style="color:#fff">Backed up all day</h3><p style="color:#cbd5e1">A database copy every 3 hours and a worklog twice a day, emailed to the admin.</p></div></div>
<div class="num rv"><div><b>3</b><span>views of every task</span></div><div><b>8am · 3pm</b><span>hours reminders</span></div><div><b>1 click</b><span>Excel worklogs</span></div><div><b>24/7</b><span>live on web and phone</span></div></div></div></section>

<section class="sec"><div class="w"><div class="final rv"><h2>See TaskFlow with your own work</h2><p>We can show you a live walkthrough, set it up for your team and bring your companies, groups and people across. No long contracts to read first.</p><div class="cta" style="justify-content:center"><a class="btn" href="mailto:shihab.shahriar@gurucadd.com?subject=TaskFlow%20demo%20request">Book a walkthrough →</a><a class="btn o" href="login.php">Team sign in</a></div></div></div></section>
<footer><div class="w">TaskFlow · powered by GURUCADD<br><small>Names, tasks and numbers shown on this page are samples.</small></div></footer>
<script>try{const o=new IntersectionObserver(e=>e.forEach(x=>{if(x.isIntersecting){x.target.classList.add("in");o.unobserve(x.target)}}),{threshold:.12});document.querySelectorAll(".rv").forEach((n,i)=>{n.style.transitionDelay=(i%3)*90+"ms";o.observe(n)})}catch(e){document.querySelectorAll(".rv").forEach(n=>n.classList.add("in"))}</script>
</body></html>

TFDISCOVER;
exit;}
require 'config.php';require 'remember.php';
if(isset($_GET['logout'])){if(!empty($_SESSION['uid'])){$lq=$db->prepare("SELECT username FROM users WHERE id=?");$lq->execute([(int)$_SESSION['uid']]);aud_log($db,(int)$_SESSION['uid'],(string)$lq->fetchColumn(),'login','logout','Signed out');}rm_forget($db);session_destroy();header('Location: login.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $lid=trim($_POST['username']??'');
  // Sign in with the username or the email address saved in the user's details.
  $s=$db->prepare("SELECT id,pass_hash,(username=?) AS exact FROM users WHERE username=? OR (email<>'' AND LOWER(email)=LOWER(?)) ORDER BY (username=?) DESC LIMIT 2");$s->execute([$lid,$lid,$lid,$lid]);$rows=$s->fetchAll();
  $u=$rows?$rows[0]:false;if($u&&count($rows)>1&&!(int)$u['exact'])$u=false;
  if($u&&password_verify($_POST['password']??'',$u['pass_hash'])){session_regenerate_id(true);$_SESSION['uid']=$u['id'];if(!empty($_POST['keep']))rm_issue($db,(int)$u['id']);aud_log($db,(int)$u['id'],trim($_POST['username']??''),'login','login','Signed in');header('Location: index.php');exit;}
  aud_log($db,$u?(int)$u['id']:0,trim($_POST['username']??''),'fail','login_failed',$u?'Wrong password':'Unknown username');
  sleep(1);$err='Wrong username or password.';
}
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><link rel="icon" type="image/svg+xml" href="login.php?logo=1"><link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#0f2a5c"><link rel="icon" type="image/png" href="icon-192.png"><link rel="apple-touch-icon" href="icon-192.png"><meta name="mobile-web-app-capable" content="yes"><title>TaskFlow login</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--navy:#0f2a5c;--navy2:#0a1d44;--green:#22c55e;--bg:#f3f6f5;--card:#ffffff;--ink:#0d1b2e;--mut:#5d6b7c;--line:#d8e0e4;--field:#f8fafb;--acc:#0f2a5c;--accfg:#ffffff;--err:#b91c1c;--errbg:#fdecec}
@media (prefers-color-scheme:dark){:root:not([data-theme="light"]){--bg:#0b1220;--card:#121c30;--ink:#eaf0f6;--mut:#9aa9bb;--line:#243249;--field:#0e1729;--acc:#22c55e;--accfg:#06240f;--err:#fca5a5;--errbg:#3a1820;color-scheme:dark}}
*{box-sizing:border-box}
html,body{height:100%}
body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.5 "DM Sans",system-ui,-apple-system,"Segoe UI",sans-serif}
.wrap{min-height:100%;display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr)}
/* art side */
.art{position:relative;overflow:hidden;color:#fff;background:radial-gradient(1200px 700px at 15% 10%,#1d4694 0%,transparent 60%),radial-gradient(900px 600px at 90% 95%,#0e6b4f 0%,transparent 55%),linear-gradient(160deg,var(--navy2),var(--navy));display:flex;flex-direction:column;justify-content:space-between;padding:clamp(28px,5vw,64px)}
.art::before{content:"";position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse at 40% 45%,#000 20%,transparent 75%);-webkit-mask-image:radial-gradient(ellipse at 40% 45%,#000 20%,transparent 75%)}
.art>*{position:relative}
.logo{display:flex;align-items:baseline;gap:2px;font:800 clamp(34px,4vw,48px)/1 "Bricolage Grotesque",system-ui,sans-serif;letter-spacing:-.02em}
.logo i{font-style:normal;color:var(--green);margin-left:1px}.logo svg.tfm{width:1.25em;height:1.25em;align-self:center;margin-right:12px;flex:none;filter:drop-shadow(0 6px 14px rgba(8,26,64,.35))}
.logo small{font:600 11px/1 "DM Sans",sans-serif;letter-spacing:.14em;text-transform:uppercase;opacity:.65;margin-left:14px;align-self:center}
.hero h1{margin:0 0 14px;font:800 clamp(34px,4.6vw,64px)/1.02 "Bricolage Grotesque",system-ui,sans-serif;letter-spacing:-.03em;text-wrap:balance;max-width:14ch}
.hero h1 em{font-style:normal;color:var(--green)}
.hero p{margin:0;max-width:44ch;font-size:clamp(15px,1.3vw,18px);color:rgba(255,255,255,.78)}
.tl{margin:clamp(22px,4vh,44px) 0 0;position:relative;max-width:560px;container-type:inline-size}
.lane{position:relative;height:34px;margin:0 0 10px;border-radius:8px;background:rgba(255,255,255,.06)}
.bar{position:absolute;top:4px;height:26px;border-radius:7px;display:flex;align-items:center;padding:0 10px;font-size:clamp(9px,2.2cqi,12px);font-weight:600;color:#06121f;white-space:nowrap;overflow:hidden;transform-origin:left center;animation:grow .9s cubic-bezier(.2,.8,.2,1) both;box-shadow:0 4px 14px rgba(0,0,0,.25)}
.l1 .bar{left:3%;width:30%;background-color:#60a5fa;--p:70%;animation-delay:.15s}
.l2 .bar{left:20%;width:40%;background-color:#4ade80;--p:100%;animation-delay:.4s}
.l3 .bar{left:8%;width:28%;background-color:#fbbf24;--p:55%;animation-delay:.65s}
.l4 .bar{left:3%;width:62%;background-color:#c4b5fd;--p:25%;animation-delay:.9s}
.bar{background-image:linear-gradient(90deg,rgba(0,0,0,.16) var(--p,0%),transparent var(--p,0%))}
.today{z-index:2;position:absolute;top:-8px;bottom:-4px;left:51%;width:2px;background:var(--green);box-shadow:0 0 0 0 rgba(34,197,94,.6);animation:pulse 2.4s ease-out infinite}
.today::before{content:"Today";position:absolute;top:-4px;left:50%;transform:translate(-50%,-100%);font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--green)}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
.chip{display:inline-flex;align-items:center;gap:7px;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);font-size:13px;font-weight:500;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
.chip b{width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 0 0 rgba(34,197,94,.7);animation:pulse 2s infinite}
.chip.r b{background:#f87171;animation:none}.chip.v b{background:#a78bfa;animation:none}
.foot{font-size:11px;letter-spacing:.14em;text-transform:uppercase;opacity:.6}.quip{display:inline-block;margin-bottom:8px;text-transform:none;letter-spacing:.01em;font:italic 500 14px "DM Sans",sans-serif;opacity:.9}.foot i{font-style:normal;color:var(--green);font-weight:800;opacity:1;font-size:15px;vertical-align:-1px}
@keyframes grow{from{transform:scaleX(0);opacity:0}to{transform:none;opacity:1}}
@keyframes pulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.55)}70%,100%{box-shadow:0 0 0 9px rgba(34,197,94,0)}}
/* form side */
.side{display:flex;align-items:center;justify-content:center;padding:clamp(24px,4vw,56px);padding-left:max(16px,env(safe-area-inset-left,0px));padding-right:max(16px,env(safe-area-inset-right,0px))}
.card{width:100%;max-width:400px;background:var(--card);border:1px solid var(--line);border-radius:20px;padding:clamp(24px,3.2vw,40px);box-shadow:0 24px 60px -24px rgba(15,42,92,.28)}
.card h2{margin:0 0 4px;font:800 28px/1.1 "Bricolage Grotesque",system-ui,sans-serif;letter-spacing:-.02em}
.card .sub{margin:0 0 22px;color:var(--mut);font-size:15px}
label.f{display:block;font-size:13px;font-weight:600;margin:0 0 6px}
.in{position:relative;margin-bottom:16px}
.in input{width:100%;height:48px;padding:0 14px;border:1.5px solid var(--line);border-radius:12px;background:var(--field);color:var(--ink);font:inherit;transition:border-color .15s,box-shadow .15s}
.in input:focus{outline:none;border-color:var(--green);box-shadow:0 0 0 4px rgba(34,197,94,.18)}
.in input.pw{padding-right:68px}
.show{position:absolute;right:6px;top:6px;height:36px;padding:0 12px;border:0;border-radius:9px;background:transparent;color:var(--mut);font:600 13px "DM Sans",sans-serif;cursor:pointer}.show:hover{color:var(--ink);background:var(--line)}
.keep{display:flex;gap:10px;align-items:center;margin:2px 0 20px;font-size:14px;color:var(--mut);cursor:pointer}
.keep input{width:18px;height:18px;accent-color:var(--green)}
.go{width:100%;height:50px;border:0;border-radius:12px;background:var(--acc);color:var(--accfg);font:700 16px "DM Sans",sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:transform .12s,box-shadow .15s}
.go i{font-style:normal;font-weight:800;color:var(--green);transition:transform .2s}
@media (prefers-color-scheme:dark){.go i{color:#06240f}}
.go:hover{box-shadow:0 10px 24px -8px rgba(15,42,92,.55)}.go:hover i{transform:translateX(5px)}.go:active{transform:translateY(1px)}
.go:focus-visible,.show:focus-visible{outline:3px solid var(--green);outline-offset:2px}
.err{margin:0 0 16px;padding:10px 14px;border-radius:10px;background:var(--errbg);color:var(--err);font-size:14px;font-weight:500}
.note{margin:18px 0 0;text-align:center;font-size:12px;color:var(--mut)}
@media(max-width:900px){.wrap{grid-template-columns:1fr}.art{padding:22px 20px 20px}.hero h1{font-size:30px;max-width:none}.hero p,.tl,.chips{display:none}.foot{display:none}.art{gap:14px}.side{padding-top:22px}}
@media(prefers-reduced-motion:reduce){.bar,.today,.chip b{animation:none}}
@view-transition{navigation:auto}::view-transition-old(root){animation:lo .3s ease both}::view-transition-new(root){animation:li .55s cubic-bezier(.22,.8,.25,1) both}@keyframes lo{to{opacity:0;transform:scale(.99)}}@keyframes li{from{opacity:0;transform:translateY(14px)}}@media(prefers-reduced-motion:reduce){@view-transition{navigation:none}}
.curt{position:fixed;inset:0;z-index:99;display:flex;align-items:center;justify-content:center;color:#fff;background:radial-gradient(1000px 600px at 20% 10%,#1d4694 0%,transparent 60%),radial-gradient(800px 500px at 90% 95%,#0e6b4f 0%,transparent 55%),linear-gradient(160deg,var(--navy2),var(--navy));clip-path:circle(0 at var(--cx,50%) var(--cy,50%));transition:clip-path .85s cubic-bezier(.65,0,.2,1)}
.curt.on{clip-path:circle(150vmax at var(--cx,50%) var(--cy,50%))}
.cl{font:800 clamp(42px,7vw,86px)/1 "Bricolage Grotesque",system-ui,sans-serif;letter-spacing:-.02em;opacity:0;transform:scale(.9);transition:opacity .45s .4s,transform .7s .4s cubic-bezier(.22,.8,.25,1)}
.cl i{font-style:normal;color:var(--green)}.curt.on .cl{opacity:1;transform:none}
body.leaving .card{opacity:0;transform:translateY(-16px) scale(.98);filter:blur(5px);transition:opacity .4s ease,transform .5s cubic-bezier(.22,.8,.25,1),filter .4s ease}
body.leaving .bar{filter:brightness(1.12);transition:filter .4s}
.go[disabled]{opacity:.85;cursor:progress}
@media(prefers-reduced-motion:reduce){.curt,.cl{transition:none}}
</style></head><body><svg width="0" height="0" style="position:absolute" aria-hidden="true"><defs><linearGradient id="tfbg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2557b5"/><stop offset=".55" stop-color="#12337a"/><stop offset="1" stop-color="#081a40"/></linearGradient><radialGradient id="tfhl" cx=".2" cy=".08" r=".75"><stop offset="0" stop-color="#fff" stop-opacity=".22"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient><linearGradient id="tfgr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6ee7a0"/><stop offset="1" stop-color="#16a34a"/></linearGradient><linearGradient id="tfsk" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#bae6fd"/><stop offset="1" stop-color="#38bdf8"/></linearGradient><filter id="tfsh" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="9" stdDeviation="8" flood-color="#020b22" flood-opacity=".35"/></filter></defs></svg>
<main class="wrap">
<section class="art" aria-hidden="false">
 <div class="logo"><svg class="tfm" viewBox="0 0 512 512" aria-hidden="true"><rect width="512" height="512" rx="116" fill="url(#tfbg)"/><rect width="512" height="512" rx="116" fill="url(#tfhl)"/><g transform="translate(0.00,0.00) scale(1.0)"><g filter="url(#tfsh)"><rect x="84" y="118" width="226" height="68" rx="34" fill="#fff"/><rect x="150" y="222" width="226" height="68" rx="34" fill="url(#tfsk)"/><path d="M216 326 H372 L426 360 L372 394 H216 a34 34 0 0 1 0-68z" fill="url(#tfgr)"/></g><path d="M120 152 l22 22 l40 -44" fill="none" stroke="#12337a" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/></g></svg>TaskFlow<small>Powered by GURUCADD</small></div>
 <div class="hero">
  <h1>Drawings in. <em>Deadlines</em> met.</h1>
  <p>Plan, assign and track every job across all your companies, from the first sketch to the final revision.</p>
  <div class="tl" aria-hidden="true">
   <div class="lane l1"><div class="bar">ACA1212A</div></div>
   <div class="lane l2"><div class="bar">SCAL3024AOM-PE</div></div>
   <div class="lane l3"><div class="bar">UCA4040F</div></div>
   <div class="lane l4"><div class="bar">SCAL3024A-PRESENTATION DRAWING</div></div>
   <div class="today"></div>
  </div>
  <div class="chips" aria-hidden="true"><span class="chip"><b></b>Live for the whole team</span><span class="chip r"><b></b>Overdue at a glance</span><span class="chip v"><b></b>Chat on every task</span></div>
 </div>
 <div class="foot"><span class="quip">A little glamour goes a long way.</span><br>Powered by GURUCADD<i>&raquo;</i></div>
</section>
<section class="side">
 <div class="card">
  <h2>Welcome back</h2><p class="sub">Log in to continue to your tasks.</p>
  <?php if($err)echo "<div class='err' role='alert'>".htmlspecialchars($err)."</div>"; ?>
  <form method="post">
   <label class="f" for="un">Username or email</label>
   <div class="in"><input id="un" name="username" autocomplete="username" autofocus autocapitalize="none" spellcheck="false"></div>
   <label class="f" for="pw">Password</label>
   <div class="in"><input id="pw" class="pw" name="password" type="password" autocomplete="current-password"><button type="button" class="show" id="pwt">Show</button></div>
   <label class="keep"><input type="checkbox" name="keep" value="1" checked> Keep me signed in on this device</label>
   <button class="go">Log in <i>&raquo;</i></button>
  </form>
  <p class="note">Need access? Ask your TaskFlow admin.</p>
 </div>
</section>
</main>
<script>(function(){var f=document.querySelector("form"),u=document.getElementById("un"),p=document.getElementById("pw");if(!f||!window.tfCred)return;
 window.tfCred.get().then(function(c){if(c&&c.u&&!u.value){u.value=c.u;p.value=c.p;var k=f.querySelector("[name=keep]");if(k)k.checked=true}}).catch(function(){});
 f.addEventListener("submit",function(){var k=f.querySelector("[name=keep]");window.__tfp=[u.value,p.value,!!(k&&k.checked)]},true)})();</script>
<script>document.getElementById("pwt").onclick=function(){var p=document.getElementById("pw"),h=p.type==="password";p.type=h?"text":"password";this.textContent=h?"Hide":"Show"}</script>
<script>
(function(){var f=document.querySelector("form"),btn=f&&f.querySelector(".go");if(!f||!btn||!window.fetch||!window.URLSearchParams)return;
function enter(u){try{sessionStorage.setItem("tf_enter","1")}catch(e){}try{var q=window.__tfp;if(window.tfCred&&q){if(q[2]&&q[0]&&q[1])window.tfCred.save(q[0],q[1]);else window.tfCred.clear()}}catch(e){}
 var b=btn.getBoundingClientRect(),o=document.createElement("div");o.className="curt";o.style.setProperty("--cx",(b.left+b.width/2)+"px");o.style.setProperty("--cy",(b.top+b.height/2)+"px");
 o.innerHTML='<div class="cl">TaskFlow<i>&raquo;</i></div>';document.body.classList.add("leaving");document.body.appendChild(o);
 requestAnimationFrame(function(){requestAnimationFrame(function(){o.classList.add("on")})});setTimeout(function(){location.href=u},950)}
f.addEventListener("submit",function(e){
 if(matchMedia("(prefers-reduced-motion:reduce)").matches||btn.disabled)return;
 e.preventDefault();btn.disabled=true;btn.innerHTML="Signing in\u2026";
 fetch(location.href,{method:"POST",body:new URLSearchParams(new FormData(f)),credentials:"same-origin"}).then(function(r){
  if(r.redirected&&!/login\.php/.test(r.url)){enter(r.url);return}
  return r.text().then(function(h){document.open();document.write(h);document.close()})
 }).catch(function(){btn.disabled=false;f.submit()})})})();
</script>
</body></html>
