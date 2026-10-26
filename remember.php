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

// ---- Usage log (admin only to read) ----
function aud_ip(){$h=$_SERVER['HTTP_CF_CONNECTING_IP']??($_SERVER['HTTP_X_FORWARDED_FOR']??($_SERVER['REMOTE_ADDR']??''));return mb_substr(trim(explode(',',(string)$h)[0]),0,45);}
function aud_table($db){static $d=false;if($d)return;$d=true;
  $db->exec("CREATE TABLE IF NOT EXISTS audit_log(id INT AUTO_INCREMENT PRIMARY KEY,created_at DATETIME NOT NULL,user_id INT NULL,username VARCHAR(60) NOT NULL DEFAULT '',kind VARCHAR(10) NOT NULL,action VARCHAR(40) NOT NULL DEFAULT '',detail VARCHAR(300) NOT NULL DEFAULT '',ws_id INT NULL,task_id INT NULL,ip VARCHAR(45) NOT NULL DEFAULT '',ua VARCHAR(160) NOT NULL DEFAULT '',INDEX(created_at),INDEX(user_id,created_at),INDEX(kind,created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}
function aud_log($db,$uid,$uname,$kind,$action,$detail='',$ws=null,$task=null){
  try{aud_table($db);
    $db->prepare("INSERT INTO audit_log(created_at,user_id,username,kind,action,detail,ws_id,task_id,ip,ua) VALUES(NOW(),?,?,?,?,?,?,?,?,?)")->execute([$uid?:null,mb_substr((string)$uname,0,60),$kind,mb_substr($action,0,40),mb_substr((string)$detail,0,300),$ws?:null,$task?:null,aud_ip(),mb_substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,160)]);
    if(mt_rand(1,100)===1)$db->exec("DELETE FROM audit_log WHERE created_at<DATE_SUB(NOW(),INTERVAL 90 DAY)");
  }catch(Throwable $e){}
}
if(empty($_SESSION['uid'])&&!empty($_COOKIE['tf_rm'])&&strpos($_COOKIE['tf_rm'],':')!==false){
  try{
    [$u,$t]=explode(':',$_COOKIE['tf_rm'],2);
    $q=$db->prepare("SELECT r.user_id FROM remember_tokens r JOIN users u ON u.id=r.user_id WHERE r.user_id=? AND r.token_hash=? AND r.expires>NOW()");
    $q->execute([(int)$u,hash('sha256',$t)]);
    if($id=$q->fetchColumn()){session_regenerate_id(true);$_SESSION['uid']=(int)$id;$nq=$db->prepare("SELECT username FROM users WHERE id=?");$nq->execute([(int)$id]);aud_log($db,(int)$id,(string)$nq->fetchColumn(),'login','auto_login','Signed in with Keep me signed in');}
  }catch(Throwable $e){}
}
