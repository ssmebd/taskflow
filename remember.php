<?php
// "Keep me signed in": long-lived login cookie stored as a hash in the database.
function rm_secure(){return !empty($_SERVER['HTTPS']);}
function rm_cookie($v,$exp){setcookie('tf_rm',$v,['expires'=>$exp,'path'=>'/','secure'=>rm_secure(),'httponly'=>true,'samesite'=>'Lax']);}
function rm_table($db){$db->exec("CREATE TABLE IF NOT EXISTS remember_tokens(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,token_hash CHAR(64) NOT NULL,expires DATETIME NOT NULL,INDEX(user_id),INDEX(token_hash)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}
function rm_issue($db,$uid){
  try{rm_table($db);$t=bin2hex(random_bytes(32));
    $db->prepare("DELETE FROM remember_tokens WHERE expires<NOW()")->execute();
    $db->prepare("INSERT INTO remember_tokens(user_id,token_hash,expires) VALUES(?,?,DATE_ADD(NOW(),INTERVAL 60 DAY))")->execute([$uid,hash('sha256',$t)]);
    rm_cookie($uid.':'.$t,time()+60*86400);}catch(Throwable $e){}
}
function rm_forget($db){
  if(!empty($_COOKIE['tf_rm'])&&strpos($_COOKIE['tf_rm'],':')!==false){
    try{$p=explode(':',$_COOKIE['tf_rm'],2);$db->prepare("DELETE FROM remember_tokens WHERE user_id=? AND token_hash=?")->execute([(int)$p[0],hash('sha256',$p[1])]);}catch(Throwable $e){}
  }
  rm_cookie('',time()-3600);
}
if(empty($_SESSION['uid'])&&!empty($_COOKIE['tf_rm'])&&strpos($_COOKIE['tf_rm'],':')!==false){
  try{
    [$u,$t]=explode(':',$_COOKIE['tf_rm'],2);
    $q=$db->prepare("SELECT r.user_id FROM remember_tokens r JOIN users u ON u.id=r.user_id WHERE r.user_id=? AND r.token_hash=? AND r.expires>NOW()");
    $q->execute([(int)$u,hash('sha256',$t)]);
    if($id=$q->fetchColumn()){session_regenerate_id(true);$_SESSION['uid']=(int)$id;}
  }catch(Throwable $e){}
}
