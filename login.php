<?php
require 'config.php';
if(isset($_GET['logout'])){session_destroy();header('Location: login.php');exit;}
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $s=$db->prepare("SELECT id,pass_hash FROM users WHERE username=?");$s->execute([trim($_POST['username']??'')]);$u=$s->fetch();
  if($u&&password_verify($_POST['password']??'',$u['pass_hash'])){session_regenerate_id(true);$_SESSION['uid']=$u['id'];header('Location: index.php');exit;}
  sleep(1);$err='Wrong username or password.';
}
?><!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>TaskFlow login</title>
<style>body{font:15px system-ui,sans-serif;max-width:340px;margin:60px auto;padding:0 16px}input,button{width:100%;padding:9px;margin:5px 0 12px;box-sizing:border-box}button{background:#4f46e5;color:#fff;border:0;border-radius:8px;font-weight:600}.e{color:#dc2626}</style></head><body>
<h2>TaskFlow</h2><?php if($err)echo "<p class='e'>$err</p>"; ?>
<form method="post"><label>Username</label><input name="username" autofocus><label>Password</label><input name="password" type="password"><button>Log in</button></form></body></html>
