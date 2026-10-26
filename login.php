<?php
require 'config.php';require 'remember.php';
if(isset($_GET['logout'])){if(!empty($_SESSION['uid'])){$lq=$db->prepare("SELECT username FROM users WHERE id=?");$lq->execute([(int)$_SESSION['uid']]);aud_log($db,(int)$_SESSION['uid'],(string)$lq->fetchColumn(),'login','logout','Signed out');}rm_forget($db);session_destroy();header('Location: login.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $s=$db->prepare("SELECT id,pass_hash FROM users WHERE username=?");$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
  if($u&&password_verify($_POST['password']??'',$u['pass_hash'])){session_regenerate_id(true);$_SESSION['uid']=$u['id'];if(!empty($_POST['keep']))rm_issue($db,(int)$u['id']);aud_log($db,(int)$u['id'],trim($_POST['username']??''),'login','login','Signed in');header('Location: index.php');exit;}
  aud_log($db,$u?(int)$u['id']:0,trim($_POST['username']??''),'fail','login_failed',$u?'Wrong password':'Unknown username');
  sleep(1);$err='Wrong username or password.';
}
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><link rel="manifest" href="manifest.webmanifest"><meta name="theme-color" content="#0f2a5c"><link rel="icon" type="image/png" href="icon-192.png"><link rel="apple-touch-icon" href="icon-192.png"><meta name="mobile-web-app-capable" content="yes"><title>TaskFlow login</title>
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
.logo i{font-style:normal;color:var(--green);margin-left:1px}
.logo small{font:600 11px/1 "DM Sans",sans-serif;letter-spacing:.14em;text-transform:uppercase;opacity:.65;margin-left:14px;align-self:center}
.hero h1{margin:0 0 14px;font:800 clamp(34px,4.6vw,64px)/1.02 "Bricolage Grotesque",system-ui,sans-serif;letter-spacing:-.03em;text-wrap:balance;max-width:14ch}
.hero h1 em{font-style:normal;color:var(--green)}
.hero p{margin:0;max-width:44ch;font-size:clamp(15px,1.3vw,18px);color:rgba(255,255,255,.78)}
.tl{margin:clamp(22px,4vh,44px) 0 0;position:relative;max-width:560px}
.lane{position:relative;height:34px;margin:0 0 10px;border-radius:8px;background:rgba(255,255,255,.06)}
.bar{position:absolute;top:4px;height:26px;border-radius:7px;display:flex;align-items:center;padding:0 10px;font-size:12px;font-weight:600;color:#06121f;white-space:nowrap;overflow:hidden;transform-origin:left center;animation:grow .9s cubic-bezier(.2,.8,.2,1) both;box-shadow:0 4px 14px rgba(0,0,0,.25)}
.lane:nth-child(1) .bar{left:4%;width:46%;background:#60a5fa;animation-delay:.15s}
.lane:nth-child(2) .bar{left:22%;width:40%;background:#4ade80;animation-delay:.35s}
.lane:nth-child(3) .bar{left:10%;width:34%;background:#fbbf24;animation-delay:.55s}
.lane:nth-child(4) .bar{left:42%;width:50%;background:#c4b5fd;animation-delay:.75s}
.lane:nth-child(5) .bar{left:30%;width:36%;background:#67e8f9;animation-delay:.95s}
.today{position:absolute;top:-8px;bottom:-4px;left:52%;width:2px;background:var(--green);box-shadow:0 0 0 0 rgba(34,197,94,.6);animation:pulse 2.4s ease-out infinite}
.today::before{content:"Today";position:absolute;top:-4px;left:50%;transform:translate(-50%,-100%);font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--green)}
.chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
.chip{display:inline-flex;align-items:center;gap:7px;padding:6px 12px;border-radius:999px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);font-size:13px;font-weight:500;backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
.chip b{width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 0 0 rgba(34,197,94,.7);animation:pulse 2s infinite}
.chip.r b{background:#f87171;animation:none}.chip.v b{background:#a78bfa;animation:none}
.foot{font-size:11px;letter-spacing:.14em;text-transform:uppercase;opacity:.6}.foot i{font-style:normal;color:var(--green);font-weight:800;opacity:1;font-size:15px;vertical-align:-1px}
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
</style></head><body>
<main class="wrap">
<section class="art" aria-hidden="false">
 <div class="logo">TaskFlow<small>Powered by GURUCADD</small></div>
 <div class="hero">
  <h1>Drawings in. <em>Deadlines</em> met.</h1>
  <p>Plan, assign and track every job across all your companies, from the first sketch to the final revision.</p>
  <div class="tl" aria-hidden="true">
   <div class="today"></div>
   <div class="lane"><div class="bar">SHOP-1042 · Ongoing</div></div>
   <div class="lane"><div class="bar">FDS-2208 · Completed</div></div>
   <div class="lane"><div class="bar">LP-0917 · In review</div></div>
   <div class="lane"><div class="bar">SHOP-1051 · Ready to start</div></div>
   <div class="lane"><div class="bar">FDS-2214 · Ongoing</div></div>
  </div>
  <div class="chips" aria-hidden="true"><span class="chip"><b></b>Live for the whole team</span><span class="chip r"><b></b>Overdue at a glance</span><span class="chip v"><b></b>Chat on every task</span></div>
 </div>
 <div class="foot">Powered by GURUCADD<i>&raquo;</i></div>
</section>
<section class="side">
 <div class="card">
  <h2>Welcome back</h2><p class="sub">Log in to continue to your tasks.</p>
  <?php if($err)echo "<div class='err' role='alert'>".htmlspecialchars($err)."</div>"; ?>
  <form method="post">
   <label class="f" for="un">Username</label>
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
<script>document.getElementById("pwt").onclick=function(){var p=document.getElementById("pw"),h=p.type==="password";p.type=h?"text":"password";this.textContent=h?"Hide":"Show"}</script>
</body></html>
