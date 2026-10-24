<?php
require 'config.php';require 'remember.php';
if(isset($_GET['logout'])){rm_forget($db);session_destroy();header('Location: login.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $s=$db->prepare("SELECT id,pass_hash FROM users WHERE username=?");$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
  if($u&&password_verify($_POST['password']??'',$u['pass_hash'])){session_regenerate_id(true);$_SESSION['uid']=$u['id'];if(!empty($_POST['keep']))rm_issue($db,(int)$u['id']);header('Location: index.php');exit;}
  sleep(1);$err='Wrong username or password.';
}
?><!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow login</title>
<style>body{font:15px system-ui,sans-serif;max-width:340px;margin:60px auto;padding:0 16px}input,button{width:100%;padding:9px;margin:5px 0 12px;box-sizing:border-box}button{background:#4f46e5;color:#fff;border:0;border-radius:8px;font-weight:600}.e{color:#dc2626}</style></head><body>
<h2>TaskFlow</h2><?php if($err)echo "<p class='e'>$err</p>"; ?>
<form method="post"><label>Username</label><input name="username" autocomplete="username" autofocus><label>Password</label><input name="password" type="password" autocomplete="current-password"><label style="display:flex;gap:8px;align-items:center;margin:0 0 12px"><input type="checkbox" name="keep" value="1" checked style="width:auto;margin:0"> Keep me signed in on this device</label><button>Log in</button></form></body></html>
