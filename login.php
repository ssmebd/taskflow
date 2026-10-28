<?php
if(isset($_GET['logo'])){header('Content-Type: image/svg+xml');header('Cache-Control: public, max-age=86400');echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><defs><linearGradient id="tfbg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2557b5"/><stop offset=".55" stop-color="#12337a"/><stop offset="1" stop-color="#081a40"/></linearGradient><radialGradient id="tfhl" cx=".2" cy=".08" r=".75"><stop offset="0" stop-color="#fff" stop-opacity=".22"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient><linearGradient id="tfgr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#6ee7a0"/><stop offset="1" stop-color="#16a34a"/></linearGradient><linearGradient id="tfsk" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#bae6fd"/><stop offset="1" stop-color="#38bdf8"/></linearGradient><filter id="tfsh" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="9" stdDeviation="8" flood-color="#020b22" flood-opacity=".35"/></filter></defs><rect width="512" height="512" rx="116" fill="url(#tfbg)"/><rect width="512" height="512" rx="116" fill="url(#tfhl)"/><g transform="translate(0.00,0.00) scale(1.0)"><g filter="url(#tfsh)"><rect x="84" y="118" width="226" height="68" rx="34" fill="#fff"/><rect x="150" y="222" width="226" height="68" rx="34" fill="url(#tfsk)"/><path d="M216 326 H372 L426 360 L372 394 H216 a34 34 0 0 1 0-68z" fill="url(#tfgr)"/></g><path d="M120 152 l22 22 l40 -44" fill="none" stroke="#12337a" stroke-width="16" stroke-linecap="round" stroke-linejoin="round"/></g></svg>';exit;}
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
