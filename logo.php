<?php
require 'config.php';require 'remember.php';
if(empty($_SESSION['uid'])){http_response_code(401);exit;}
$id=(int)($_GET['id']??0);$uid=(int)$_SESSION['uid'];
$q=$db->prepare("SELECT role FROM users WHERE id=?");$q->execute([$uid]);$role=$q->fetchColumn();
if(!$role){http_response_code(401);exit;}
if($role!=='admin'){$q=$db->prepare("SELECT COUNT(*) FROM ws_users WHERE ws_id=? AND user_id=?");$q->execute([$id,$uid]);if(!$q->fetchColumn()){http_response_code(403);exit;}}
$q=$db->prepare("SELECT logo FROM workspaces WHERE id=?");$q->execute([$id]);$d=(string)$q->fetchColumn();
if(!preg_match('#^data:(image/(?:png|jpeg|webp));base64,(.+)$#',$d,$m)){http_response_code(404);exit;}
header('Content-Type: '.$m[1]);header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=31536000');
echo base64_decode($m[2]);
