<?php
const BUILD=115;
require 'config.php';require 'remember.php';
header('Content-Type: application/json');
$WRITEA=['save','field','delete','dup','col_add','col_del','col_edit','user_add','user_pass','user_del','ws_add','ws_rename','ws_logo','ov_set','ws_del','ws_access','dr_act','st_add','st_ren','st_del','st_move','st_color','gr_add','gr_ren','gr_del','cm_add','cm_del','gantt_set','task_move','gr_move','dt_add','dt_ren','dt_del','gr_order','task_order','tl_add','tl_del','inv_set','dt_color','inv_create','inv_step','inv_pay','inv_del'];
function out($d){
  global $a,$db,$WRITEA;
  if(isset($a,$db)&&in_array($a,$WRITEA,true)&&empty($d['error'])){try{$db->exec("INSERT INTO meta(k,v) VALUES('ver',1) ON DUPLICATE KEY UPDATE v=v+1");}catch(Throwable $e){}}
  echo json_encode($d);exit;
}
if(empty($_SESSION['uid'])){http_response_code(401);out(['error'=>'auth']);}
if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='fetch'){http_response_code(400);out(['error'=>'bad request']);}
$in=json_decode(file_get_contents('php://input'),true)?:[];
$a=$_GET['a']??'';$uid=(int)$_SESSION['uid'];
$q=$db->prepare("SELECT id,name,role FROM users WHERE id=?");$q->execute([$uid]);$me=$q->fetch();
if(!$me){session_destroy();http_response_code(401);out(['error'=>'auth']);}
function ensure_schema($db){
  if(!empty($_SESSION['schema_v26']))return;
  $db->exec("CREATE TABLE IF NOT EXISTS custom_cols(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(60) NOT NULL,ctype VARCHAR(10) NOT NULL DEFAULT 'text',options TEXT NULL,sort_no INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM tasks LIKE 'extra'")->fetch())$db->exec("ALTER TABLE tasks ADD extra MEDIUMTEXT NULL");
  $db->exec("CREATE TABLE IF NOT EXISTS workspaces(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL,sort_no INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS ws_users(ws_id INT NOT NULL,user_id INT NOT NULL,PRIMARY KEY(ws_id,user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM tasks LIKE 'ws_id'")->fetch())$db->exec("ALTER TABLE tasks ADD ws_id INT NOT NULL DEFAULT 1, ADD INDEX(ws_id)");
  if(!$db->query("SHOW COLUMNS FROM custom_cols LIKE 'ws_id'")->fetch())$db->exec("ALTER TABLE custom_cols ADD ws_id INT NOT NULL DEFAULT 1, ADD INDEX(ws_id)");
  $db->exec("CREATE TABLE IF NOT EXISTS statuses(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,skey VARCHAR(20) NOT NULL,name VARCHAR(40) NOT NULL,sort_no INT NOT NULL DEFAULT 0,UNIQUE KEY ws_key(ws_id,skey)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS task_groups(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,name VARCHAR(80) NOT NULL,sort_no INT NOT NULL DEFAULT 0,INDEX(ws_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM workspaces LIKE 'logo'")->fetch())$db->exec("ALTER TABLE workspaces ADD logo MEDIUMTEXT NULL");
  $db->exec("CREATE TABLE IF NOT EXISTS task_comments(id INT AUTO_INCREMENT PRIMARY KEY,task_id INT NOT NULL,ws_id INT NOT NULL,user_id INT NOT NULL,to_user_id INT NULL,kind VARCHAR(12) NOT NULL DEFAULT 'comment',body TEXT NOT NULL,created_at DATETIME NOT NULL,INDEX(task_id),INDEX(ws_id),INDEX(to_user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS comment_reads(user_id INT NOT NULL,task_id INT NOT NULL,last_id INT NOT NULL DEFAULT 0,PRIMARY KEY(user_id,task_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS time_logs(id INT AUTO_INCREMENT PRIMARY KEY,task_id INT NOT NULL,ws_id INT NOT NULL,user_id INT NOT NULL,work_date DATE NOT NULL,hours DECIMAL(5,2) NOT NULL,note VARCHAR(255) NOT NULL DEFAULT '',created_at DATETIME NOT NULL,INDEX(task_id),INDEX(ws_id,work_date),INDEX(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM time_logs LIKE 'kind'")->fetch())$db->exec("ALTER TABLE time_logs ADD kind VARCHAR(4) NOT NULL DEFAULT 'R'");
  $db->exec("CREATE TABLE IF NOT EXISTS invoice_profiles(ws_id INT NOT NULL,slot TINYINT NOT NULL,data MEDIUMTEXT NOT NULL,PRIMARY KEY(ws_id,slot)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM task_groups LIKE 'parent_id'")->fetch())$db->exec("ALTER TABLE task_groups ADD parent_id INT NULL");
  if(!$db->query("SHOW COLUMNS FROM users LIKE 'pref_theme'")->fetch())$db->exec("ALTER TABLE users ADD pref_theme VARCHAR(20) NOT NULL DEFAULT ''");
  if(!$db->query("SHOW COLUMNS FROM tasks LIKE 'sort_no'")->fetch())$db->exec("ALTER TABLE tasks ADD sort_no INT NOT NULL DEFAULT 0");
  $db->exec("CREATE TABLE IF NOT EXISTS drawing_types(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,name VARCHAR(100) NOT NULL,sort_no INT NOT NULL DEFAULT 0,UNIQUE KEY u(ws_id,name)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS meta(k VARCHAR(20) PRIMARY KEY,v BIGINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("INSERT IGNORE INTO meta(k,v) VALUES('ver',1)");
  if(!$db->query("SHOW COLUMNS FROM users LIKE 'pref_view'")->fetch())$db->exec("ALTER TABLE users ADD pref_view VARCHAR(10) NOT NULL DEFAULT 'list'");
  if(!$db->query("SHOW COLUMNS FROM tasks LIKE 'group_id'")->fetch())$db->exec("ALTER TABLE tasks ADD group_id INT NULL, ADD INDEX(group_id)");
  if(!$db->query("SHOW COLUMNS FROM statuses LIKE 'color'")->fetch()){
    $db->exec("ALTER TABLE statuses ADD color VARCHAR(9) NOT NULL DEFAULT ''");
    foreach(['todo'=>'#9ca3af','progress'=>'#3b82f6','review'=>'#f59e0b','done'=>'#22c55e'] as $k=>$c)$db->prepare("UPDATE statuses SET color=? WHERE skey=?")->execute([$c,$k]);
  }
  if(!$db->query("SHOW COLUMNS FROM ws_users LIKE 'level'")->fetch())$db->exec("ALTER TABLE ws_users ADD level VARCHAR(10) NOT NULL DEFAULT 'edit'");
  if(!(int)$db->query("SELECT COUNT(*) FROM workspaces")->fetchColumn()){
    $db->exec("INSERT INTO workspaces(id,name,sort_no) VALUES(1,'General',1)");
    $db->exec("INSERT IGNORE INTO ws_users(ws_id,user_id) SELECT 1,id FROM users");
  }
  $db->exec("CREATE TABLE IF NOT EXISTS wiki_pages(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(150) NOT NULL,body MEDIUMTEXT NOT NULL,updated_by INT NULL,updated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM time_logs LIKE 'logged_by'")->fetch())$db->exec("ALTER TABLE time_logs ADD logged_by INT NULL");
  $db->exec("CREATE TABLE IF NOT EXISTS invoices(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,slot INT NOT NULL DEFAULT 0,inv_no VARCHAR(80) NOT NULL,inv_date DATE NOT NULL,from_date DATE NOT NULL,to_date DATE NOT NULL,client VARCHAR(200) NOT NULL DEFAULT '',currency VARCHAR(8) NOT NULL DEFAULT '',amount DECIMAL(14,2) NOT NULL DEFAULT 0,hours DECIMAL(10,2) NOT NULL DEFAULT 0,status VARCHAR(10) NOT NULL DEFAULT 'pending',paid_date DATE NULL,html MEDIUMTEXT NULL,approved_by INT NULL,created_at DATETIME NOT NULL,UNIQUE KEY(ws_id,inv_no),KEY(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM task_groups LIKE 'color'")->fetch())$db->exec("ALTER TABLE task_groups ADD color VARCHAR(7) NULL");
  if(!$db->query("SHOW COLUMNS FROM invoices LIKE 'seq'")->fetch())$db->exec("ALTER TABLE invoices ADD seq VARCHAR(12) NOT NULL DEFAULT '', ADD amend_note VARCHAR(500) NULL");
  if($db->query("SHOW INDEX FROM invoices WHERE Key_name='ws_id'")->fetch())$db->exec("ALTER TABLE invoices DROP INDEX ws_id");
  if(!$db->query("SHOW COLUMNS FROM drawing_types LIKE 'color'")->fetch())$db->exec("ALTER TABLE drawing_types ADD color VARCHAR(7) NULL");
  $db->exec("CREATE TABLE IF NOT EXISTS messages(id INT AUTO_INCREMENT PRIMARY KEY,from_id INT NOT NULL,to_id INT NOT NULL DEFAULT 0,body TEXT NOT NULL,created_at DATETIME NOT NULL,KEY(to_id,id),KEY(from_id,to_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS msg_reads(user_id INT NOT NULL,peer_id INT NOT NULL,last_id INT NOT NULL DEFAULT 0,PRIMARY KEY(user_id,peer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  try{$c=$db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();if($c&&stripos($c['Type'],'enum')!==false)$db->exec("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'member'");}catch(Throwable $e){}
  $db->exec("CREATE TABLE IF NOT EXISTS delete_requests(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,kind VARCHAR(12) NOT NULL,target_id INT NOT NULL,target_name VARCHAR(255) NOT NULL DEFAULT '',requested_by INT NOT NULL,status VARCHAR(10) NOT NULL DEFAULT 'pending',decided_by INT NULL,created_at DATETIME NOT NULL,decided_at DATETIME NULL,KEY st(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $_SESSION['schema_v26']=1;
}
try{ensure_schema($db);}catch(Throwable $e){out(['error'=>'Database upgrade failed: '.$e->getMessage()]);}
$q=$db->prepare("SELECT pref_view FROM users WHERE id=?");$q->execute([$uid]);$pv=$q->fetchColumn();$me['pref_view']=in_array($pv,['board','gantt'],true)?$pv:'list';
$q=$db->prepare("SELECT pref_theme FROM users WHERE id=?");$q->execute([$uid]);$pt=(string)$q->fetchColumn();$me['theme']=preg_match('/^#[0-9a-f]{6}\|#[0-9a-f]{6}$/i',$pt)?$pt:'';
function my_ws($db){
  global $me;
  if($me['role']==='admin')return $db->query("SELECT id,name,'full' AS level,CRC32(COALESCE(logo,'')) AS lv,(logo IS NOT NULL AND logo<>'') AS hl FROM workspaces ORDER BY sort_no,id")->fetchAll();
  $q=$db->prepare("SELECT w.id,w.name,u.level,CRC32(COALESCE(w.logo,'')) AS lv,(w.logo IS NOT NULL AND w.logo<>'') AS hl FROM workspaces w JOIN ws_users u ON u.ws_id=w.id WHERE u.user_id=? ORDER BY w.sort_no,w.id");$q->execute([$me['id']]);return $q->fetchAll();
}
$WS=my_ws($db);$W=(int)($_GET['w']??0);
$okW=false;foreach($WS as $w)if((int)$w['id']===$W)$okW=true;
if(!$okW)$W=$WS?(int)$WS[0]['id']:0;
$LV='';foreach($WS as $w)if((int)$w['id']===$W)$LV=$w['level'];
function need_level($l){global $LV;need_ws();$r=['view'=>1,'edit'=>2,'full'=>3];if(($r[$LV]??0)<$r[$l])out(['error'=>'You do not have permission to do that in this workspace']);}
function need_ws(){global $W;if(!$W)out(['error'=>'You have not been given access to any workspace']);}
function get_cols($db){
  global $W;
  $q=$db->prepare("SELECT id,name,ctype,options FROM custom_cols WHERE ws_id=? ORDER BY sort_no,id");$q->execute([$W]);$r=$q->fetchAll();
  foreach($r as &$c){$c['options']=json_decode($c['options']?:'[]',true)?:[];}
  return $r;
}
function clean_x($c,$v){
  $v=is_string($v)?trim($v):'';
  if($v==='')return '';
  if($c['ctype']==='number')return is_numeric($v)?$v:'';
  if($c['ctype']==='date')return preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)?$v:'';
  if($c['ctype']==='dropdown')return in_array($v,$c['options'],true)?$v:'';
  return mb_substr($v,0,500);
}
function set_x($db,$tid,$vals){
  global $W;
  $q=$db->prepare("SELECT extra FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);
  $x=json_decode((string)$q->fetchColumn(),true);if(!is_array($x))$x=[];
  foreach($vals as $k=>$v){if($v==='')unset($x[$k]);else $x[$k]=$v;}
  $db->prepare("UPDATE tasks SET extra=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$x?json_encode($x,JSON_UNESCAPED_UNICODE):null,$tid,$W]);
}
function get_statuses($db){
  global $W;
  $q=$db->prepare("SELECT COUNT(*) FROM statuses WHERE ws_id=?");$q->execute([$W]);
  if(!(int)$q->fetchColumn()){
    $i=$db->prepare("INSERT IGNORE INTO statuses(ws_id,skey,name,sort_no,color) VALUES(?,?,?,?,?)");
    foreach([['todo','To Do',1,'#9ca3af'],['progress','In Progress',2,'#3b82f6'],['review','In Review',3,'#f59e0b'],['done','Completed',99,'#22c55e']] as $r)$i->execute([$W,$r[0],$r[1],$r[2],$r[3]]);
  }
  $q=$db->prepare("SELECT id,skey AS k,name,color FROM statuses WHERE ws_id=? ORDER BY (skey='done'),sort_no,id");$q->execute([$W]);return $q->fetchAll();
}
function status_keys($db){return array_column(get_statuses($db),'k');}
function ov_settings($db){
  $r=['hand'=>1,'one'=>0];
  foreach($db->query("SELECT k,v FROM meta WHERE k IN('ov_hand','ov_one')")->fetchAll() as $x){if($x['k']==='ov_hand')$r['hand']=(int)$x['v']?1:0;if($x['k']==='ov_one')$r['one']=(int)$x['v']?1:0;}
  return $r;
}
function ws_ids(){global $WS;return array_map('intval',array_column($WS,'id'));}
function inbox_sql($db,$count){
  global $me;$ids=ws_ids();if(!$ids)return $count?0:[];
  $in=implode(',',$ids);$u=(int)$me['id'];
  $from="FROM task_comments c JOIN tasks t ON t.id=c.task_id LEFT JOIN users us ON us.id=c.user_id LEFT JOIN comment_reads r ON r.task_id=c.task_id AND r.user_id=$u WHERE c.to_user_id=$u AND c.user_id<>$u AND c.id>COALESCE(r.last_id,0) AND c.ws_id IN($in)";
  if($count)return (int)$db->query("SELECT COUNT(*) $from")->fetchColumn();
  return $db->query("SELECT c.id,c.task_id,c.ws_id,t.title,c.kind,c.body,c.created_at,COALESCE(us.name,'Former member') AS from_name $from ORDER BY c.id DESC LIMIT 50")->fetchAll();
}
function inv_defaults(){return ['name'=>'Invoice','match_cust'=>'','match_order'=>'','client_name'=>'','client_addr'=>'','wl_title'=>'','po'=>'','sec_labels'=>'','currency'=>'AUD','symbol'=>'$','layout'=>'worklog','rate'=>0,'rates'=>new stdClass(),'ot'=>1,'bank'=>'','prefix'=>'INVOICE-','suffix'=>'','seq'=>1];}
function inv_profiles($db){global $W;$q=$db->prepare("SELECT slot,data FROM invoice_profiles WHERE ws_id=? AND slot>0 ORDER BY slot");$q->execute([$W]);$o=[];foreach($q->fetchAll() as $r){$j=json_decode((string)$r['data'],true);$d=inv_defaults();if(is_array($j))$d=array_merge($d,$j);if(empty($d['rates']))$d['rates']=new stdClass();$d['slot']=(int)$r['slot'];$o[]=$d;}return $o;}
function inv_company($db){global $W;$q=$db->prepare("SELECT data FROM invoice_profiles WHERE ws_id=? AND slot=0");$q->execute([$W]);$j=json_decode((string)$q->fetchColumn(),true);$d=inv_defaults();$d['prefix']='';$d['name']='';if(is_array($j))$d=array_merge($d,$j);if(empty($d['rates']))$d['rates']=new stdClass();return $d;}
function admin(){global $me;if($me['role']!=='admin')out(['error'=>'Admins only']);}
$F=['title','order_no','customer','drawing_type','start_date','end_date','status','assigned_to','notes','group_id'];
function clean($f,$v){
  $v=is_string($v)?trim($v):$v;
  if($f==='start_date'||$f==='end_date')return preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$v)?$v:null;
  if($f==='group_id'){global $db,$W;$g=(int)$v;if(!$g)return null;$q=$db->prepare("SELECT COUNT(*) FROM task_groups WHERE id=? AND ws_id=?");$q->execute([$g,$W]);return $q->fetchColumn()?$g:null;}
  if($f==='assigned_to')return($v===''||$v===null)?null:(int)$v;
  if($f==='status'){global $db;$k=status_keys($db);return in_array($v,$k,true)?$v:$k[0];}
  $lim=['title'=>255,'customer'=>255,'order_no'=>100,'drawing_type'=>100,'notes'=>5000][$f];
  return mb_substr((string)$v,0,$lim);
}
function notify_assign($db,$tid,$new,$old){global $uid,$W,$WS;$new=(int)$new;if(!$new||$new===(int)$old||$new===$uid)return;
  try{$q=$db->prepare("SELECT title,order_no FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);$t=$q->fetch();if(!$t)return;
  $cn='';foreach($WS as $w)if((int)$w['id']===(int)$W)$cn=$w['name'];
  $b="📌 New task assigned to you: ".$t['title'].($t['order_no']!==''?" (Order ".$t['order_no'].")":"").($cn!==''?" — ".$cn:"");
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,$new,mb_substr($b,0,1900)]);}catch(Throwable $e){}}
function del_name($db,$kind,$id){global $W;
  $t=['group'=>['task_groups','name'],'status'=>['statuses','name'],'column'=>['custom_cols','name'],'dtype'=>['drawing_types','name']][$kind]??null;if(!$t)return null;
  $q=$db->prepare("SELECT {$t[1]} FROM {$t[0]} WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$n=$q->fetchColumn();return $n===false?null:(string)$n;}
function del_do($db,$kind,$id){global $W;
  if($kind==='dtype'){$db->prepare("DELETE FROM drawing_types WHERE id=? AND ws_id=?")->execute([$id,$W]);return;}
  if($kind==='column'){$k='x_'.$id;
    $db->prepare("DELETE FROM custom_cols WHERE id=? AND ws_id=?")->execute([$id,$W]);
    $q=$db->prepare("SELECT id,extra FROM tasks WHERE extra IS NOT NULL AND ws_id=?");$q->execute([$W]);
    foreach($q->fetchAll() as $r){$x=json_decode($r['extra'],true);if(is_array($x)&&array_key_exists($k,$x)){unset($x[$k]);
      $db->prepare("UPDATE tasks SET extra=? WHERE id=? AND ws_id=?")->execute([$x?json_encode($x,JSON_UNESCAPED_UNICODE):null,$r['id'],$W]);}}
    return;}
  if($kind==='group'){
    $q=$db->prepare("SELECT id FROM task_groups WHERE parent_id=? AND ws_id=?");$q->execute([$id,$W]);$all=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));$all[]=$id;
    foreach($all as $g){$db->prepare("UPDATE tasks SET group_id=NULL WHERE group_id=? AND ws_id=?")->execute([$g,$W]);$db->prepare("DELETE FROM task_groups WHERE id=? AND ws_id=?")->execute([$g,$W]);}
    return;}
  if($kind==='status'){$work=array_values(array_filter(get_statuses($db),fn($x)=>$x['k']!=='done'));
    $tgt=null;foreach($work as $x)if($x['id']==$id)$tgt=$x;
    if(!$tgt)out(['error'=>'Status not found']);
    $others=array_values(array_filter($work,fn($x)=>$x['id']!=$id));if(!$others)out(['error'=>'You need at least one working status']);
    $db->prepare("UPDATE tasks SET status=? WHERE ws_id=? AND status=?")->execute([$others[0]['k'],$W,$tgt['k']]);
    $db->prepare("DELETE FROM statuses WHERE id=? AND ws_id=?")->execute([$id,$W]);}}
function del_gate($db,$kind,$id){global $me,$uid,$W,$WS;
  $nm=del_name($db,$kind,$id);if($nm===null)out(['error'=>'Not found']);
  if($me['role']==='admin'){del_do($db,$kind,$id);out(['ok'=>1]);}
  if($kind==='status'){$work=array_values(array_filter(get_statuses($db),fn($x)=>$x['k']!=='done'));$tg=false;foreach($work as $x)if($x['id']==$id)$tg=true;if(!$tg)out(['error'=>'Status not found']);if(count($work)<2)out(['error'=>'You need at least one working status']);}
  $q=$db->prepare("SELECT COUNT(*) FROM delete_requests WHERE ws_id=? AND kind=? AND target_id=? AND status='pending'");$q->execute([$W,$kind,$id]);
  if(!(int)$q->fetchColumn()){
    $db->prepare("INSERT INTO delete_requests(ws_id,kind,target_id,target_name,requested_by,created_at) VALUES(?,?,?,?,?,NOW())")->execute([$W,$kind,$id,mb_substr($nm,0,255),$uid]);
    $cn='';foreach($WS as $w)if((int)$w['id']===(int)$W)$cn=$w['name'];
    $kn=['group'=>'group','status'=>'status','column'=>'column','dtype'=>'drawing type'][$kind];
    $b="🗑 Delete request: ".$kn." \"".$nm."\"".($cn!==''?" in ".$cn:"")." — please review under Settings ▸ Approvals.";
    foreach($db->query("SELECT id FROM users WHERE role='admin'")->fetchAll(PDO::FETCH_COLUMN) as $ad){if((int)$ad===$uid)continue;
      $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,(int)$ad,mb_substr($b,0,1900)]);}
  }
  out(['ok'=>1,'pending'=>1]);}
switch($a){
case 'dr_list':
  admin();
  $o=[];foreach($db->query("SELECT r.id,r.ws_id,r.kind,r.target_name,r.created_at,COALESCE(w.name,'') AS ws_name,COALESCE(u.name,'Former member') AS by_name FROM delete_requests r LEFT JOIN workspaces w ON w.id=r.ws_id LEFT JOIN users u ON u.id=r.requested_by WHERE r.status='pending' ORDER BY r.id")->fetchAll() as $r)$o[]=$r;
  out(['items'=>$o]);
case 'dr_act':
  admin();$id=(int)($in['id']??0);$ap=($in['act']??'')==='approve';
  $q=$db->prepare("SELECT * FROM delete_requests WHERE id=? AND status='pending'");$q->execute([$id]);$r=$q->fetch();if(!$r)out(['error'=>'That request was already handled']);
  $W=(int)$r['ws_id'];
  if($ap){if(del_name($db,$r['kind'],(int)$r['target_id'])!==null)del_do($db,$r['kind'],(int)$r['target_id']);}
  $db->prepare("UPDATE delete_requests SET status=?,decided_by=?,decided_at=NOW() WHERE id=?")->execute([$ap?'approved':'rejected',$uid,$id]);
  $kn=['group'=>'group','status'=>'status','column'=>'column','dtype'=>'drawing type'][$r['kind']]??$r['kind'];
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,(int)$r['requested_by'],mb_substr(($ap?"✅ Approved: ":"❌ Not approved: ").$kn." \"".$r['target_name']."\" delete request.",1900)]);
  out(['ok'=>1]);
case 'ping':
  $q=$db->prepare("SELECT COUNT(*) FROM messages m LEFT JOIN msg_reads r ON r.user_id=? AND r.peer_id=IF(m.to_id=0,0,m.from_id) WHERE m.from_id<>? AND (m.to_id=? OR m.to_id=0) AND m.id>COALESCE(r.last_id,0)");$q->execute([$uid,$uid,$uid]);
  out(['v'=>(int)$db->query("SELECT v FROM meta WHERE k='ver'")->fetchColumn(),'build'=>BUILD,'msgn'=>(int)$q->fetchColumn()]);
case 'msg_users':
  $q=$db->prepare("SELECT IF(m.to_id=0,0,m.from_id) AS peer,COUNT(*) AS n FROM messages m LEFT JOIN msg_reads r ON r.user_id=? AND r.peer_id=IF(m.to_id=0,0,m.from_id) WHERE m.from_id<>? AND (m.to_id=? OR m.to_id=0) AND m.id>COALESCE(r.last_id,0) GROUP BY peer");$q->execute([$uid,$uid,$uid]);$un=[];foreach($q->fetchAll() as $r)$un[(int)$r['peer']]=(int)$r['n'];
  $q=$db->prepare("SELECT IF(from_id=?,to_id,from_id) AS peer,MAX(id) AS last FROM messages WHERE to_id<>0 AND (from_id=? OR to_id=?) GROUP BY peer");$q->execute([$uid,$uid,$uid]);$la=[];foreach($q->fetchAll() as $r)$la[(int)$r['peer']]=(int)$r['last'];
  $q=$db->prepare("SELECT MAX(id) FROM messages WHERE to_id=0");$q->execute();$la[0]=(int)$q->fetchColumn();
  $us=[];foreach($db->query("SELECT id,name FROM users ORDER BY name")->fetchAll() as $u)if((int)$u['id']!==$uid)$us[]=['id'=>(int)$u['id'],'name'=>$u['name'],'n'=>$un[(int)$u['id']]??0,'last'=>$la[(int)$u['id']]??0];
  out(['users'=>$us,'team'=>['n'=>$un[0]??0,'last'=>$la[0]]]);
case 'msg_list':
  $peer=(int)($in['peer']??0);$after=(int)($in['after']??0);
  if($peer){$q=$db->prepare("SELECT m.id,m.from_id,m.body,m.created_at,COALESCE(u.name,'Former member') AS name FROM messages m LEFT JOIN users u ON u.id=m.from_id WHERE m.id>? AND ((m.from_id=? AND m.to_id=?) OR (m.from_id=? AND m.to_id=?)) ORDER BY m.id DESC LIMIT 150");$q->execute([$after,$uid,$peer,$peer,$uid]);}
  else{$q=$db->prepare("SELECT m.id,m.from_id,m.body,m.created_at,COALESCE(u.name,'Former member') AS name FROM messages m LEFT JOIN users u ON u.id=m.from_id WHERE m.id>? AND m.to_id=0 ORDER BY m.id DESC LIMIT 150");$q->execute([$after]);}
  $ms=array_reverse($q->fetchAll());foreach($ms as &$m){$m['id']=(int)$m['id'];$m['mine']=(int)$m['from_id']===$uid;}unset($m);
  if($ms){$mx=end($ms)['id'];$db->prepare("INSERT INTO msg_reads(user_id,peer_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$uid,$peer,$mx]);}
  out(['messages'=>$ms]);
case 'msg_send':
  $peer=(int)($in['peer']??0);$b=mb_substr(trim((string)($in['body']??'')),0,2000);if($b==='')out(['error'=>'Write a message first']);
  if($peer){$q=$db->prepare("SELECT COUNT(*) FROM users WHERE id=?");$q->execute([$peer]);if(!(int)$q->fetchColumn()||$peer===$uid)out(['error'=>'Choose someone to message']);}
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,$peer,$b]);$id=(int)$db->lastInsertId();
  $db->prepare("INSERT INTO msg_reads(user_id,peer_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$uid,$peer,$id]);out(['ok'=>1,'id'=>$id]);
case 'my_tasks':
  $ids=array_map(fn($w)=>(int)$w['id'],$WS);
  if(!$ids)out(['tasks'=>[],'st'=>(object)[],'ws'=>(object)[]]);
  $in=implode(',',$ids);
  $q=$db->prepare("SELECT id,ws_id,title,order_no,customer,drawing_type,start_date,end_date,status FROM tasks WHERE assigned_to=? AND status<>'done' AND ws_id IN($in) ORDER BY (end_date IS NULL),end_date,id LIMIT 500");$q->execute([$uid]);
  $tk=$q->fetchAll();$st=[];
  foreach($db->query("SELECT ws_id,skey,name,color FROM statuses WHERE ws_id IN($in)")->fetchAll() as $r)$st[$r['ws_id']][$r['skey']]=['n'=>$r['name'],'c'=>$r['color']];
  $wn=[];foreach($WS as $w)$wn[$w['id']]=$w['name'];
  out(['tasks'=>$tk,'st'=>(object)$st,'ws'=>(object)$wn]);
case 'list':
  $ver=(int)$db->query("SELECT v FROM meta WHERE k='ver'")->fetchColumn();
  $names=[];foreach($db->query("SELECT id,name FROM users")->fetchAll() as $u)$names[$u['id']]=$u['name'];
  $res=['build'=>BUILD,'inbox'=>inbox_sql($db,true),'v'=>$ver,'me'=>$me,'ws'=>$WS,'w'=>$W,'level'=>$LV,'names'=>(object)$names,'users'=>[],'cols'=>[],'tasks'=>[]];
  if($W){
    $q=$db->prepare("SELECT id,name,role FROM users WHERE role='admin' OR (role<>'guest' AND id IN(SELECT user_id FROM ws_users WHERE ws_id=? AND level<>'view')) ORDER BY name");$q->execute([$W]);$res['users']=$q->fetchAll();
    $q=$db->prepare("SELECT * FROM tasks WHERE ws_id=? ORDER BY sort_no ASC,id DESC");$q->execute([$W]);$tasks=$q->fetchAll();
    foreach($tasks as &$t){$t['x']=(object)(json_decode((string)($t['extra']??''),true)?:[]);unset($t['extra']);}
    $ov=[];$wn=array_column($WS,'name','id');
    $ovs=ov_settings($db);
    $as='COALESCE(a.start_date,a.end_date)';$ae='COALESCE(a.end_date,a.start_date)';$bs='COALESCE(b.start_date,b.end_date)';$be='COALESCE(b.end_date,b.start_date)';
    $cond=$ovs['hand']?"(($as<$be AND $bs<$ae) OR ($as=$ae AND $bs=$be AND $as=$bs))":"($as<=$be AND $bs<=$ae)";
    if($ovs['one'])$cond.=" AND $as<>$ae AND $bs<>$be";
    $q=$db->prepare("SELECT a.id AS aid,b.title,b.ws_id,b.start_date,b.end_date FROM tasks a JOIN tasks b ON a.assigned_to=b.assigned_to AND a.id<>b.id WHERE a.ws_id=? AND a.assigned_to IS NOT NULL AND a.status<>'done' AND b.status<>'done' AND $as IS NOT NULL AND $bs IS NOT NULL AND $cond ORDER BY b.start_date LIMIT 5000");
    $q->execute([$W]);
    foreach($q->fetchAll() as $r){$ok=isset($wn[$r['ws_id']]);if(count($ov[$r['aid']]??[])>=8)continue;
      $ov[$r['aid']][]=['t'=>$ok?$r['title']:'A task in another workspace','w'=>($ok&&$r['ws_id']!=$W)?$wn[$r['ws_id']]:'','s'=>$r['start_date'],'e'=>$r['end_date']];}
    $cm=[];$q=$db->prepare("SELECT c.task_id,COUNT(*) n,SUM(c.id>COALESCE(r.last_id,0) AND c.user_id<>?) un,SUM(c.id>COALESCE(r.last_id,0) AND c.to_user_id=? AND c.user_id<>?) tm FROM task_comments c LEFT JOIN comment_reads r ON r.task_id=c.task_id AND r.user_id=? WHERE c.ws_id=? GROUP BY c.task_id");
    $q->execute([$me['id'],$me['id'],$me['id'],$me['id'],$W]);foreach($q->fetchAll() as $r)$cm[$r['task_id']]=[(int)$r['n'],(int)$r['un'],(int)$r['tm']];
    $res['cmeta']=(object)$cm;
    $hq=$db->prepare("SELECT task_id,SUM(hours) h FROM time_logs WHERE ws_id=? GROUP BY task_id");$hq->execute([$W]);$hm=[];if($LV!=='view'||$me['role']==='admin')foreach($hq->fetchAll() as $r)$hm[$r['task_id']]=(float)$r['h'];$res['hours']=(object)$hm;
    $res['overlaps']=(object)$ov;$res['ovset']=$ovs;
    $res['tasks']=$tasks;$res['statuses']=get_statuses($db);
    $q=$db->prepare("SELECT id,name,parent_id,color FROM task_groups WHERE ws_id=? ORDER BY sort_no,id");$q->execute([$W]);$res['groups']=$q->fetchAll();
    $sk="dt_seed_$W";$q=$db->prepare("SELECT v FROM meta WHERE k=?");$q->execute([$sk]);
    if($q->fetchColumn()===false){
      $db->prepare("INSERT IGNORE INTO drawing_types(ws_id,name,sort_no) SELECT ?,TRIM(drawing_type),0 FROM tasks WHERE ws_id=? AND drawing_type IS NOT NULL AND TRIM(drawing_type)<>'' GROUP BY TRIM(drawing_type)")->execute([$W,$W]);
      $db->prepare("INSERT INTO meta(k,v) VALUES(?,1) ON DUPLICATE KEY UPDATE v=1")->execute([$sk]);
    }
    $q=$db->prepare("SELECT id,name,color FROM drawing_types WHERE ws_id=? ORDER BY name");$q->execute([$W]);$res['dtypes']=$q->fetchAll();$res['cols']=get_cols($db);
  }
  if($me['role']==='admin'){
    $res['drq']=(int)$db->query("SELECT COUNT(*) FROM delete_requests WHERE status='pending'")->fetchColumn();
    $res['allUsers']=$db->query("SELECT id,name,username,role FROM users ORDER BY name")->fetchAll();
    $acc=[];foreach($db->query("SELECT ws_id,user_id,level FROM ws_users")->fetchAll() as $r)$acc[$r['ws_id']][$r['user_id']]=$r['level'];
    $res['access']=(object)$acc;
  }
  out($res);
case 'save':
  need_level('edit');$v=[];foreach($F as $f)$v[$f]=clean($f,$in[$f]??'');
  if($v['title']==='')out(['error'=>'Title required']);
  $oldA=null;if(!empty($in['id'])){$q=$db->prepare("SELECT assigned_to FROM tasks WHERE id=? AND ws_id=?");$q->execute([(int)$in['id'],$W]);$oldA=$q->fetchColumn();}
  if(!empty($in['id'])){
    $set=implode(',',array_map(fn($f)=>"$f=?",$F));
    $db->prepare("UPDATE tasks SET $set,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([...array_values($v),(int)$in['id'],$W]);
  }else{
    $db->prepare("INSERT INTO tasks(".implode(',',$F).",ws_id,updated_at) VALUES(".implode(',',array_fill(0,count($F),'?')).",?,NOW())")->execute([...array_values($v),$W]);
    $in['id']=$db->lastInsertId();
  }
  notify_assign($db,(int)$in['id'],$v['assigned_to'],$oldA);
  if(isset($in['x'])&&is_array($in['x'])){
    $vals=[];foreach(get_cols($db) as $c){$k='x_'.$c['id'];if(array_key_exists($k,$in['x']))$vals[$k]=clean_x($c,$in['x'][$k]);}
    if($vals)set_x($db,(int)$in['id'],$vals);
  }
  out(['ok'=>1,'id'=>(int)$in['id']]);
case 'field':
  need_level('edit');$f=$in['f']??'';
  if(preg_match('/^x_(\d+)$/',$f,$m)){
    foreach(get_cols($db) as $c)if($c['id']==$m[1]){set_x($db,(int)($in['id']??0),[$f=>clean_x($c,$in['v']??'')]);out(['ok'=>1]);}
    out(['error'=>'Unknown column']);
  }
  if(!in_array($f,$F,true))out(['error'=>'bad field']);
  $v=clean($f,$in['v']??'');if($f==='title'&&$v==='')out(['ok'=>1]);
  $oldA=null;if($f==='assigned_to'){$q=$db->prepare("SELECT assigned_to FROM tasks WHERE id=? AND ws_id=?");$q->execute([(int)($in['id']??0),$W]);$oldA=$q->fetchColumn();}
  $db->prepare("UPDATE tasks SET $f=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$v,(int)($in['id']??0),$W]);
  if($f==='assigned_to')notify_assign($db,(int)($in['id']??0),$v,$oldA);out(['ok'=>1]);
case 'delete':
  need_level('full');foreach($in['ids']??[] as $i){$db->prepare("DELETE FROM tasks WHERE id=? AND ws_id=?")->execute([(int)$i,$W]);$db->prepare("DELETE FROM task_comments WHERE task_id=? AND ws_id=?")->execute([(int)$i,$W]);$db->prepare("DELETE FROM time_logs WHERE task_id=? AND ws_id=?")->execute([(int)$i,$W]);}out(['ok'=>1]);
case 'dup':
  need_level('edit');foreach($in['ids']??[] as $i)$db->prepare("INSERT INTO tasks(title,order_no,customer,drawing_type,start_date,end_date,status,assigned_to,notes,group_id,extra,ws_id,updated_at) SELECT CONCAT(title,' (copy)'),order_no,customer,drawing_type,start_date,end_date,IF(status='done',?,status),assigned_to,notes,group_id,extra,ws_id,NOW() FROM tasks WHERE id=? AND ws_id=?")->execute([status_keys($db)[0],(int)$i,$W]);
  out(['ok'=>1]);
case 'gantt_set':
  need_level('edit');$id=(int)($in['id']??0);
  $sd=clean('start_date',$in['s']??'');$ed=clean('end_date',$in['e']??'');
  if($sd&&$ed&&$ed<$sd)[$sd,$ed]=[$ed,$sd];
  $set="start_date=?,end_date=?";$p=[$sd,$ed];
  if(array_key_exists('a',$in)){
    $a=(int)$in['a'];
    if($a){$q=$db->prepare("SELECT COUNT(*) FROM users u WHERE u.id=? AND (u.role='admin' OR u.id IN(SELECT user_id FROM ws_users WHERE ws_id=?))");$q->execute([$a,$W]);if(!$q->fetchColumn())out(['error'=>'That person cannot open this workspace']);}
    $set.=",assigned_to=?";$p[]=$a?:null;
  }
  $oldA=null;if(array_key_exists('a',$in)){$q=$db->prepare("SELECT assigned_to FROM tasks WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$oldA=$q->fetchColumn();}
  $p[]=$id;$p[]=$W;
  $db->prepare("UPDATE tasks SET $set,updated_at=NOW() WHERE id=? AND ws_id=?")->execute($p);
  if(array_key_exists('a',$in))notify_assign($db,$id,(int)$in['a'],$oldA);out(['ok'=>1]);
case 'task_move':
  need_level('full');$to=(int)($in['to']??0);$ids=array_map('intval',$in['ids']??[]);
  if(!$to||$to===$W)out(['error'=>'Choose another workspace']);
  $okTo=false;foreach($WS as $w)if((int)$w['id']===$to&&in_array($w['level'],['edit','full'],true))$okTo=true;
  if(!$okTo)out(['error'=>'You cannot add tasks to that workspace']);
  $q=$db->prepare("SELECT skey FROM statuses WHERE ws_id=? ORDER BY (skey='done'),sort_no,id");$q->execute([$to]);$dk=$q->fetchAll(PDO::FETCH_COLUMN);
  if(!$dk)$dk=['todo','progress','review','done'];
  $q=$db->prepare("SELECT id,name,ctype FROM custom_cols WHERE ws_id=?");$q->execute([$W]);$sc=$q->fetchAll();
  $q=$db->prepare("SELECT id,name,ctype FROM custom_cols WHERE ws_id=?");$q->execute([$to]);$dc=$q->fetchAll();
  $map=[];foreach($sc as $c)foreach($dc as $d)if(mb_strtolower($c['name'])===mb_strtolower($d['name'])&&$c['ctype']===$d['ctype'])$map['x_'.$c['id']]='x_'.$d['id'];
  $acc=$db->prepare("SELECT COUNT(*) FROM users u WHERE u.id=? AND (u.role='admin' OR u.id IN(SELECT user_id FROM ws_users WHERE ws_id=?))");
  $n=0;
  foreach($ids as $i){
    $q=$db->prepare("SELECT status,assigned_to,extra,drawing_type FROM tasks WHERE id=? AND ws_id=?");$q->execute([$i,$W]);$t=$q->fetch();if(!$t)continue;
    $st=in_array($t['status'],$dk,true)?$t['status']:$dk[0];
    $as=$t['assigned_to'];if($as){$acc->execute([$as,$to]);if(!$acc->fetchColumn())$as=null;}
    $ex=json_decode((string)$t['extra'],true);$nx=[];if(is_array($ex))foreach($ex as $k=>$v)if(isset($map[$k]))$nx[$map[$k]]=$v;
    $db->prepare("UPDATE tasks SET ws_id=?,group_id=NULL,status=?,assigned_to=?,extra=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$to,$st,$as,$nx?json_encode($nx):null,$i,$W]);
    $db->prepare("UPDATE task_comments SET ws_id=? WHERE task_id=?")->execute([$to,$i]);
    $db->prepare("UPDATE time_logs SET ws_id=? WHERE task_id=?")->execute([$to,$i]);
    if(trim((string)$t['drawing_type'])!=='')$db->prepare("INSERT IGNORE INTO drawing_types(ws_id,name) VALUES(?,?)")->execute([$to,trim($t['drawing_type'])]);$n++;
  }
  out(['ok'=>1,'moved'=>$n]);
case 'gr_move':
  need_level('full');$id=(int)($in['id']??0);$to=(int)($in['to']??0);
  if(!$to||$to===$W)out(['error'=>'Choose another company']);
  $okTo=false;foreach($WS as $w)if((int)$w['id']===$to&&in_array($w['level'],['edit','full'],true))$okTo=true;
  if(!$okTo)out(['error'=>'You cannot add to that company']);
  $q=$db->prepare("SELECT id,name,parent_id FROM task_groups WHERE ws_id=?");$q->execute([$W]);$all=$q->fetchAll();$g=null;foreach($all as $x)if((int)$x['id']===$id)$g=$x;if(!$g)out(['error'=>'Group not found']);
  $subs=[];foreach($all as $x)if((int)($x['parent_id']??0)===$id)$subs[]=$x;
  $q=$db->prepare("SELECT id,name,parent_id FROM task_groups WHERE ws_id=?");$q->execute([$to]);$dst=$q->fetchAll();
  if(count($dst)+1+count($subs)>80)out(['error'=>'That company would have more than 80 groups']);
  $find=function($name,$par)use(&$dst){foreach($dst as $x)if((int)($x['parent_id']??0)===(int)$par&&mb_strtolower($x['name'])===mb_strtolower($name))return (int)$x['id'];return 0;};
  $mk=function($name,$par)use($db,$to,&$dst,$find){$e=$find($name,$par);if($e)return $e;$mx=$db->prepare("SELECT COALESCE(MAX(sort_no),0)+1 FROM task_groups WHERE ws_id=?");$mx->execute([$to]);
    $db->prepare("INSERT INTO task_groups(ws_id,name,sort_no,parent_id) VALUES(?,?,?,?)")->execute([$to,$name,(int)$mx->fetchColumn(),$par?:null]);$nid=(int)$db->lastInsertId();$dst[]=['id'=>$nid,'name'=>$name,'parent_id'=>$par?:null];return $nid;};
  $top=$mk($g['name'],0);$map=[$id=>$top];foreach($subs as $s)$map[(int)$s['id']]=$mk($s['name'],$top);
  $q=$db->prepare("SELECT id,name,ctype FROM custom_cols WHERE ws_id=?");$q->execute([$W]);$sc=$q->fetchAll();
  $q=$db->prepare("SELECT id,name,ctype FROM custom_cols WHERE ws_id=?");$q->execute([$to]);$dc=$q->fetchAll();
  $cm=[];foreach($sc as $c)foreach($dc as $d)if(mb_strtolower($c['name'])===mb_strtolower($d['name'])&&$c['ctype']===$d['ctype'])$cm['x_'.$c['id']]='x_'.$d['id'];
  $q=$db->prepare("SELECT skey FROM statuses WHERE ws_id=? ORDER BY (skey='done'),sort_no,id");$q->execute([$to]);$dk=$q->fetchAll(PDO::FETCH_COLUMN);if(!$dk)$dk=['todo','progress','review','done'];
  $acc=$db->prepare("SELECT COUNT(*) FROM users u WHERE u.id=? AND (u.role='admin' OR u.id IN(SELECT user_id FROM ws_users WHERE ws_id=?))");
  $n=0;
  foreach($map as $from=>$gid){
    $q=$db->prepare("SELECT id,status,assigned_to,extra,drawing_type FROM tasks WHERE group_id=? AND ws_id=?");$q->execute([$from,$W]);
    foreach($q->fetchAll() as $t){
      $st=in_array($t['status'],$dk,true)?$t['status']:$dk[0];
      $as=$t['assigned_to'];if($as){$acc->execute([$as,$to]);if(!$acc->fetchColumn())$as=null;}
      $ex=json_decode((string)$t['extra'],true);$nx=[];if(is_array($ex))foreach($ex as $k=>$v)if(isset($cm[$k]))$nx[$cm[$k]]=$v;
      $db->prepare("UPDATE tasks SET ws_id=?,group_id=?,status=?,assigned_to=?,extra=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$to,$gid,$st,$as,$nx?json_encode($nx):null,$t['id'],$W]);
      $db->prepare("UPDATE task_comments SET ws_id=? WHERE task_id=?")->execute([$to,$t['id']]);
      $db->prepare("UPDATE time_logs SET ws_id=? WHERE task_id=?")->execute([$to,$t['id']]);
      if(trim((string)$t['drawing_type'])!=='')$db->prepare("INSERT IGNORE INTO drawing_types(ws_id,name) VALUES(?,?)")->execute([$to,trim($t['drawing_type'])]);$n++;}}
  foreach(array_keys($map) as $gi)$db->prepare("DELETE FROM task_groups WHERE id=? AND ws_id=?")->execute([$gi,$W]);
  out(['ok'=>1,'moved'=>$n]);
case 'task_order':
  need_level('edit');$id=(int)($in['id']??0);$before=(int)($in['before']??0);
  $q=$db->prepare("SELECT id FROM tasks WHERE ws_id=? ORDER BY sort_no ASC,id DESC");$q->execute([$W]);$ids=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
  if(!in_array($id,$ids,true))out(['error'=>'Task not found']);
  $ids=array_values(array_filter($ids,fn($x)=>$x!==$id));
  $pos=$before?array_search($before,$ids,true):false;
  if($pos===false)$ids[]=$id;else array_splice($ids,$pos,0,[$id]);
  foreach(array_chunk($ids,400,true) as $ch){
    $case='';$in2=[];$par=[];foreach($ch as $i=>$tid){$case.=' WHEN ? THEN ?';$par[]=$tid;$par[]=$i+1;$in2[]='?';}
    $db->prepare("UPDATE tasks SET sort_no=CASE id$case END WHERE ws_id=? AND id IN(".implode(',',$in2).")")->execute(array_merge($par,[$W],array_values($ch)));
  }
  if(array_key_exists('g',$in)){$g=clean('group_id',$in['g']);$db->prepare("UPDATE tasks SET group_id=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$g,$id,$W]);}
  out(['ok'=>1]);
case 'tl_list':
  need_ws();if($LV==='view'&&$me['role']!=='admin')out(['error'=>'Hours are not available with view-only access']);$tid=(int)($in['task_id']??0);
  $q=$db->prepare("SELECT id FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);if(!$q->fetch())out(['error'=>'Task not found']);
  $q=$db->prepare("SELECT l.id,l.user_id,COALESCE(u.name,'Former member') AS name,l.work_date,l.hours,l.kind,l.logged_by,l.note FROM time_logs l LEFT JOIN users u ON u.id=l.user_id WHERE l.task_id=? AND l.ws_id=? ORDER BY l.work_date DESC,l.id DESC");$q->execute([$tid,$W]);
  out(['logs'=>$q->fetchAll(),'me'=>(int)$uid]);
case 'tl_add':
  need_level('edit');$tid=(int)($in['task_id']??0);$d=(string)($in['date']??'');$h=round((float)($in['hours']??0),2);$n=mb_substr(trim((string)($in['note']??'')),0,255);$kd=(($in['kind']??'')==='OT')?'OT':'R';
  if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$d))out(['error'=>'Choose a date']);
  if($h<=0||$h>24)out(['error'=>'Hours must be between 0.25 and 24']);
  $q=$db->prepare("SELECT assigned_to FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);$tr=$q->fetch();if(!$tr)out(['error'=>'Task not found']);
  $who=(int)$tr['assigned_to'];
  if(!$who){$as=(int)($in['assign_to']??0);if(!$as)out(['error'=>'This task is unassigned. Choose who it is assigned to.','need_assign'=>1]);
    $q=$db->prepare("SELECT COUNT(*) FROM users WHERE id=?");$q->execute([$as]);if(!(int)$q->fetchColumn())out(['error'=>'Unknown person']);
    $db->prepare("UPDATE tasks SET assigned_to=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$as,$tid,$W]);$who=$as;}
  $db->prepare("INSERT INTO time_logs(task_id,ws_id,user_id,logged_by,work_date,hours,kind,note,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())")->execute([$tid,$W,$who,$uid,$d,$h,$kd,$n]);out(['ok'=>1]);
case 'tl_del':
  need_level('edit');$id=(int)($in['id']??0);$q=$db->prepare("SELECT user_id,logged_by FROM time_logs WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$o=$q->fetch();
  if($o===false)out(['error'=>'Entry not found']);
  if((int)$o['user_id']!==(int)$uid&&(int)$o['logged_by']!==(int)$uid&&$LV!=='full')out(['error'=>'You can only delete your own entries']);
  $db->prepare("DELETE FROM time_logs WHERE id=?")->execute([$id]);out(['ok'=>1]);
case 'tl_report':
  admin();$f=(string)($in['from']??'');$t=(string)($in['to']??'');
  if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$f)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$t))out(['error'=>'Choose both dates']);
  $q=$db->prepare("SELECT l.user_id,COALESCE(u.name,'Former member') AS person,l.work_date,l.hours,l.kind,l.note,t.id AS task_id,t.order_no,t.title,t.customer,t.drawing_type,COALESCE(pg.name,g.name) AS top_name,COALESCE(pg.id,g.id) AS top_id,IF(pg.id IS NULL,NULL,g.name) AS sub_name FROM time_logs l JOIN tasks t ON t.id=l.task_id LEFT JOIN users u ON u.id=l.user_id LEFT JOIN task_groups g ON g.id=t.group_id LEFT JOIN task_groups pg ON pg.id=g.parent_id WHERE l.ws_id=? AND l.work_date BETWEEN ? AND ? ORDER BY l.work_date,l.id");$q->execute([$W,$f,$t]);
  out(['rows'=>$q->fetchAll(),'profiles'=>inv_profiles($db),'company'=>inv_company($db)]);
case 'inv_create':
  admin();$slot=(int)($in['slot']??0);if($slot<0||$slot>9)out(['error'=>'Bad profile']);
  $no=mb_substr(trim((string)($in['no']??'')),0,80);$f=(string)($in['from']??'');$t=(string)($in['to']??'');$eid=(int)($in['id']??0);
  if($no===''||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$f)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$t))out(['error'=>'Missing invoice number or dates']);
  $html=(string)($in['html']??'');if(strlen($html)>3000000)out(['error'=>'Report too large to store']);
  $cl=mb_substr((string)($in['client']??''),0,200);$cu=mb_substr((string)($in['currency']??''),0,8);$am=round((float)($in['amount']??0),2);$hr=round((float)($in['hours']??0),2);$sq=substr(preg_replace('/\D/','',(string)($in['seq']??'')),0,12);
  if($eid){$q=$db->prepare("SELECT status FROM invoices WHERE id=?");$q->execute([$eid]);$st=$q->fetchColumn();if($st!=='draft'&&$st!=='amend')out(['error'=>'Only a draft or an invoice waiting for amendment can be edited']);
    $db->prepare("UPDATE invoices SET slot=?,inv_no=?,inv_date=?,from_date=?,to_date=?,client=?,currency=?,amount=?,hours=?,html=?,seq=?,status='draft',amend_note=NULL WHERE id=?")->execute([$slot,$no,$t,$f,$t,$cl,$cu,$am,$hr,$html,$sq,$eid]);out(['ok'=>1,'id'=>$eid]);}
  $db->prepare("INSERT INTO invoices(ws_id,slot,inv_no,inv_date,from_date,to_date,client,currency,amount,hours,status,html,seq,approved_by,created_at) VALUES(?,?,?,?,?,?,?,?,?,?, 'draft',?,?,?,NOW())")->execute([$W,$slot,$no,$t,$f,$t,$cl,$cu,$am,$hr,$html,$sq,$uid]);
  out(['ok'=>1,'id'=>(int)$db->lastInsertId()]);
case 'inv_step':
  admin();$id=(int)($in['id']??0);$op=(string)($in['op']??'');$q=$db->prepare("SELECT * FROM invoices WHERE id=?");$q->execute([$id]);$iv=$q->fetch();if(!$iv)out(['error'=>'Invoice not found']);
  if($op==='amend'){if($iv['status']!=='draft')out(['error'=>'Only a draft can be sent back for amendment']);$n=mb_substr(trim((string)($in['note']??'')),0,500);if($n==='')out(['error'=>'Write what needs to change']);
    $db->prepare("UPDATE invoices SET status='amend',amend_note=? WHERE id=?")->execute([$n,$id]);out(['ok'=>1]);}
  if($op==='approve'){if($iv['status']!=='draft'&&$iv['status']!=='amend')out(['error'=>'Already approved']);
    $q=$db->prepare("SELECT id FROM invoices WHERE ws_id=? AND inv_no=? AND status IN('pending','paid') AND id<>?");$q->execute([(int)$iv['ws_id'],$iv['inv_no'],$id]);if($q->fetch())out(['error'=>'Invoice number '.$iv['inv_no'].' is already used by an approved invoice. Edit this draft and change the number.']);
    $db->prepare("UPDATE invoices SET status='pending',amend_note=NULL,approved_by=? WHERE id=?")->execute([$uid,$id]);
    // move the next number on, keeping leading zeros; never move it backwards
    $slot=(int)$iv['slot'];$Wx=(int)$iv['ws_id'];$q=$db->prepare("SELECT data FROM invoice_profiles WHERE ws_id=? AND slot=?");$q->execute([$Wx,$slot]);$raw=$q->fetchColumn();$d=json_decode((string)$raw,true);
    if(is_array($d)||$slot===0){if(!is_array($d))$d=inv_defaults();$cur=preg_replace('/\D/','',(string)($d['seq']??'1'));if($cur==='')$cur='1';$used=(string)$iv['seq'];if($used===''||!preg_match('/^\d+$/',$used))$used=$cur;
      $next=(int)$used+1;if($next>(int)$cur){$d['seq']=str_pad((string)$next,max(strlen($used),strlen($cur)),'0',STR_PAD_LEFT);
        $db->prepare("INSERT INTO invoice_profiles(ws_id,slot,data) VALUES(?,?,?) ON DUPLICATE KEY UPDATE data=VALUES(data)")->execute([$Wx,$slot,json_encode($d,JSON_UNESCAPED_UNICODE)]);}}
    out(['ok'=>1]);}
  out(['error'=>'Unknown step']);
case 'inv_list':
  admin();$q=$db->query("SELECT i.id,i.ws_id,COALESCE(w.name,'(deleted company)') AS company,i.inv_no,i.inv_date,i.from_date,i.to_date,i.client,i.currency,i.amount,i.hours,i.status,i.paid_date,i.created_at,i.amend_note,i.slot,i.seq,COALESCE(u.name,'') AS by_name FROM invoices i LEFT JOIN workspaces w ON w.id=i.ws_id LEFT JOIN users u ON u.id=i.approved_by ORDER BY i.inv_date DESC,i.id DESC");out(['invoices'=>$q->fetchAll()]);
case 'inv_get':
  admin();$q=$db->prepare("SELECT html,slot,inv_no,seq,from_date,to_date,status,ws_id FROM invoices WHERE id=?");$q->execute([(int)($in['id']??0)]);out(['inv'=>$q->fetch()]);
case 'inv_pay':
  admin();$paid=($in['status']??'')==='paid';$pd=(string)($in['paid_date']??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$pd))$pd=date('Y-m-d');
  $db->prepare("UPDATE invoices SET status=?,paid_date=? WHERE id=? AND status IN('pending','paid')")->execute([$paid?'paid':'pending',$paid?$pd:null,(int)($in['id']??0)]);out(['ok'=>1]);
case 'inv_del':
  admin();$db->prepare("DELETE FROM invoices WHERE id=?")->execute([(int)($in['id']??0)]);out(['ok'=>1]);
case 'co_get':
  admin();out(['company'=>inv_company($db)]);
case 'inv_set':
  admin();$slot=(int)($in['slot']??0);if($slot<0||$slot>9)out(['error'=>'Bad profile']);
  if(!empty($in['remove'])){$db->prepare("DELETE FROM invoice_profiles WHERE ws_id=? AND slot=?")->execute([$W,$slot]);out(['ok'=>1,'profiles'=>inv_profiles($db)]);}
  $p=is_array($in['profile']??null)?$in['profile']:[];$d=inv_defaults();
  $s=fn($k,$n)=>mb_substr(trim((string)($p[$k]??$d[$k])),0,$n);
  $rates=[];if(is_array($p['rates']??null))foreach($p['rates'] as $k=>$v)if(is_numeric($v)&&(float)$v>=0&&(float)$v<100000)$rates[(string)(int)$k]=round((float)$v,2);
  $clean=['name'=>$s('name',60),'match_cust'=>$s('match_cust',300),'match_order'=>$s('match_order',60),'client_name'=>$s('client_name',150),'client_addr'=>$s('client_addr',300),'wl_title'=>$s('wl_title',80),'po'=>$s('po',60),'sec_labels'=>$s('sec_labels',500),'currency'=>$s('currency',8),'symbol'=>$s('symbol',4),
    'layout'=>in_array($p['layout']??'',['items','summary'],true)?$p['layout']:'worklog','rate'=>round(max(0,min(100000,(float)($p['rate']??0))),2),'rates'=>$rates,
    'ot'=>round(max(0,min(10,(float)($p['ot']??1))),2),'bank'=>$s('bank',600),'prefix'=>$s('prefix',30),'suffix'=>$s('suffix',30),'seq'=>(function($v){$v=substr(preg_replace('/\D/','',(string)$v),0,8);return $v===''?'1':$v;})($p['seq']??'1')];
  $db->prepare("INSERT INTO invoice_profiles(ws_id,slot,data) VALUES(?,?,?) ON DUPLICATE KEY UPDATE data=VALUES(data)")->execute([$W,$slot,json_encode($clean,JSON_UNESCAPED_UNICODE)]);out(['ok'=>1,'profiles'=>inv_profiles($db)]);
case 'my_hours':
  if($LV==='view'&&$me['role']!=='admin')out(['error'=>'Hours are not available with view-only access']);
  $f=(string)($in['from']??'');$t=(string)($in['to']??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$f)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$t))out(['error'=>'Choose both dates']);
  $who=$uid;if(!empty($in['user'])&&$me['role']==='admin')$who=(int)$in['user'];
  $q=$db->prepare("SELECT l.id,l.work_date,l.hours,l.kind,l.note,w.name AS ws,t.title,t.order_no,COALESCE(pg.name,g.name) AS top_name FROM time_logs l JOIN tasks t ON t.id=l.task_id LEFT JOIN workspaces w ON w.id=l.ws_id LEFT JOIN task_groups g ON g.id=t.group_id LEFT JOIN task_groups pg ON pg.id=g.parent_id WHERE l.user_id=? AND l.work_date BETWEEN ? AND ? ORDER BY l.work_date,l.id");$q->execute([$who,$f,$t]);
  out(['rows'=>$q->fetchAll(),'user'=>$who]);
case 'wiki_list':
  out(['pages'=>$db->query("SELECT p.id,p.title,p.body,p.updated_at,COALESCE(u.name,'') AS by_name FROM wiki_pages p LEFT JOIN users u ON u.id=p.updated_by ORDER BY p.title")->fetchAll()]);
case 'wiki_save':
  $id=(int)($in['id']??0);$t=mb_substr(trim((string)($in['title']??'')),0,150);$b=mb_substr((string)($in['body']??''),0,60000);if($t==='')out(['error'=>'Give the page a title']);
  if($id){$db->prepare("UPDATE wiki_pages SET title=?,body=?,updated_by=?,updated_at=NOW() WHERE id=?")->execute([$t,$b,$uid,$id]);}
  else{$db->prepare("INSERT INTO wiki_pages(title,body,updated_by,updated_at) VALUES(?,?,?,NOW())")->execute([$t,$b,$uid]);$id=(int)$db->lastInsertId();}
  out(['ok'=>1,'id'=>$id]);
case 'wiki_del':
  $db->prepare("DELETE FROM wiki_pages WHERE id=?")->execute([(int)($in['id']??0)]);out(['ok'=>1]);
case 'dt_add':
  need_level('edit');$n=mb_substr(trim($in['name']??''),0,100);if($n==='')out(['error'=>'Enter a drawing type']);
  $q=$db->prepare("SELECT COUNT(*) FROM drawing_types WHERE ws_id=?");$q->execute([$W]);if((int)$q->fetchColumn()>=200)out(['error'=>'Maximum 200 drawing types']);
  $db->prepare("INSERT IGNORE INTO drawing_types(ws_id,name) VALUES(?,?)")->execute([$W,$n]);
  $q=$db->prepare("SELECT name FROM drawing_types WHERE ws_id=? AND name=?");$q->execute([$W,$n]);out(['ok'=>1,'name'=>$q->fetchColumn()?:$n]);
case 'dt_ren':
  need_level('full');$n=mb_substr(trim($in['name']??''),0,100);$id=(int)($in['id']??0);if($n==='')out(['error'=>'Enter a name']);
  $q=$db->prepare("SELECT name FROM drawing_types WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$old=$q->fetchColumn();if($old===false)out(['error'=>'Not found']);
  $q=$db->prepare("SELECT COUNT(*) FROM drawing_types WHERE ws_id=? AND name=? AND id<>?");$q->execute([$W,$n,$id]);if($q->fetchColumn())out(['error'=>'That drawing type already exists']);
  $db->prepare("UPDATE drawing_types SET name=? WHERE id=?")->execute([$n,$id]);
  $db->prepare("UPDATE tasks SET drawing_type=?,updated_at=NOW() WHERE ws_id=? AND drawing_type=?")->execute([$n,$W,$old]);out(['ok'=>1]);
case 'dt_color':
  need_level('full');$c=(string)($in['color']??'');$db->prepare("UPDATE drawing_types SET color=? WHERE id=? AND ws_id=?")->execute([preg_match('/^#[0-9a-fA-F]{6}$/',$c)?strtolower($c):null,(int)($in['id']??0),$W]);out(['ok'=>1]);
case 'dt_del':
  need_level('full');del_gate($db,'dtype',(int)($in['id']??0));
case 'col_add':
  need_level('edit');$n=mb_substr(trim($in['name']??''),0,60);$t=$in['ctype']??'text';
  if($n==='')out(['error'=>'Enter a column name']);
  if(!in_array($t,['text','number','date','dropdown'],true))$t='text';
  if((int)count(get_cols($db))>=30)out(['error'=>'Maximum 30 custom columns']);
  $o=[];if($t==='dropdown'){foreach(explode(',',(string)($in['options']??'')) as $p){$p=mb_substr(trim($p),0,60);if($p!==''&&!in_array($p,$o,true))$o[]=$p;}
    if(!$o)out(['error'=>'Enter at least one option, separated by commas']);}
  foreach(get_cols($db) as $e)if(mb_strtolower($e['name'])===mb_strtolower($n))out(['error'=>'A column with that name already exists']);
  $db->prepare("INSERT INTO custom_cols(name,ctype,options,sort_no,ws_id) VALUES(?,?,?,?,?)")->execute([$n,$t,$o?json_encode($o,JSON_UNESCAPED_UNICODE):null,(int)$db->query("SELECT COALESCE(MAX(sort_no),0)+1 FROM custom_cols")->fetchColumn(),$W]);
  out(['ok'=>1]);
case 'col_edit':
  need_level('edit');$id=(int)($in['id']??0);$n=mb_substr(trim($in['name']??''),0,60);if($n==='')out(['error'=>'Enter a column name']);
  $cur=null;foreach(get_cols($db) as $e){if((int)$e['id']===$id)$cur=$e;}if(!$cur)out(['error'=>'Column not found']);
  foreach(get_cols($db) as $e)if((int)$e['id']!==$id&&mb_strtolower($e['name'])===mb_strtolower($n))out(['error'=>'A column with that name already exists']);
  $o=null;if($cur['ctype']==='dropdown'){$l=[];foreach(explode(',',(string)($in['options']??'')) as $p){$p=mb_substr(trim($p),0,60);if($p!==''&&!in_array($p,$l,true))$l[]=$p;}if(!$l)out(['error'=>'Enter at least one option, separated by commas']);$o=json_encode($l,JSON_UNESCAPED_UNICODE);}
  $db->prepare("UPDATE custom_cols SET name=?,options=? WHERE id=? AND ws_id=?")->execute([$n,$o,$id,$W]);out(['ok'=>1]);
case 'col_del':
  need_level('full');del_gate($db,'column',(int)($in['id']??0));
case 'pref_theme':
  $v=(string)($in['v']??'');if($v!==''&&!preg_match('/^#[0-9a-f]{6}\|#[0-9a-f]{6}$/i',$v))out(['error'=>'Invalid colours']);
  $db->prepare("UPDATE users SET pref_theme=? WHERE id=?")->execute([$v,$uid]);out(['ok'=>1]);
case 'pref_view':
  $v=in_array($in['v']??'',['board','gantt'],true)?$in['v']:'list';$db->prepare("UPDATE users SET pref_view=? WHERE id=?")->execute([$v,$uid]);out(['ok'=>1]);
case 'gr_add':
  need_level('edit');$n=mb_substr(trim($in['name']??''),0,80);if($n==='')out(['error'=>'Enter a group name']);
  $par=(int)($in['parent_id']??0);
  $q=$db->prepare("SELECT id,name,parent_id FROM task_groups WHERE ws_id=?");$q->execute([$W]);$all=$q->fetchAll();
  if(count($all)>=80)out(['error'=>'Maximum 80 groups and sub-groups']);
  if($par){$ok=false;foreach($all as $x)if((int)$x['id']===$par&&empty($x['parent_id']))$ok=true;if(!$ok)out(['error'=>'Sub-groups can only be added under a main group']);}
  foreach($all as $x)if((int)($x['parent_id']??0)===$par&&mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'That name already exists here']);
  $mx=$db->prepare("SELECT COALESCE(MAX(sort_no),0)+1 FROM task_groups WHERE ws_id=?");$mx->execute([$W]);
  $db->prepare("INSERT INTO task_groups(ws_id,name,sort_no,parent_id) VALUES(?,?,?,?)")->execute([$W,$n,(int)$mx->fetchColumn(),$par?:null]);out(['ok'=>1,'id'=>(int)$db->lastInsertId()]);
case 'gr_order':
  need_level('full');$id=(int)($in['id']??0);$before=(int)($in['before']??0);
  $q=$db->prepare("SELECT id,parent_id,sort_no FROM task_groups WHERE ws_id=? ORDER BY sort_no,id");$q->execute([$W]);$all=$q->fetchAll();$me_=null;foreach($all as $x)if((int)$x['id']===$id)$me_=$x;if(!$me_)out(['error'=>'Group not found']);
  $par=(int)($me_['parent_id']??0);$sib=[];foreach($all as $x)if((int)($x['parent_id']??0)===$par)$sib[]=$x;
  $slots=array_map(fn($x)=>(int)$x['sort_no'],$sib);sort($slots);$ids=array_map(fn($x)=>(int)$x['id'],$sib);
  $ids=array_values(array_filter($ids,fn($x)=>$x!==$id));$pos=$before?array_search($before,$ids,true):false;if($pos===false)$ids[]=$id;else array_splice($ids,$pos,0,[$id]);
  // give every sibling a distinct, ascending number so order is stable even if old numbers repeated
  $base=0;foreach($slots as $s)$base=max($base,$s);$n=count($ids);$up=$db->prepare("UPDATE task_groups SET sort_no=? WHERE id=? AND ws_id=?");
  $vals=$slots;for($i=count($vals);$i<$n;$i++)$vals[]=++$base;for($i=1;$i<$n;$i++)if($vals[$i]<=$vals[$i-1])$vals[$i]=$vals[$i-1]+1;
  foreach($ids as $i=>$gid)$up->execute([$vals[$i],$gid,$W]);
  out(['ok'=>1]);
case 'gr_ren':
  need_level('full');$n=mb_substr(trim($in['name']??''),0,80);$id=(int)($in['id']??0);if($n==='')out(['error'=>'Enter a name']);
  $q=$db->prepare("SELECT id,name,parent_id FROM task_groups WHERE ws_id=?");$q->execute([$W]);$all=$q->fetchAll();$cur=null;foreach($all as $x)if((int)$x['id']===$id)$cur=$x;if(!$cur)out(['error'=>'Group not found']);
  $np=(int)($cur['parent_id']??0);
  if(array_key_exists('parent_id',$in)){$np=(int)$in['parent_id'];
    if($np){if($np===$id)out(['error'=>'A group cannot be inside itself']);$par=null;foreach($all as $x)if((int)$x['id']===$np)$par=$x;if(!$par||$par['parent_id'])out(['error'=>'Choose a top-level group']);
      foreach($all as $x)if((int)($x['parent_id']??0)===$id)out(['error'=>'This group has sub-groups, so it cannot go inside another group']);}}
  foreach($all as $x)if((int)$x['id']!==$id&&(int)($x['parent_id']??0)===$np&&mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'That name already exists here']);
  $db->prepare("UPDATE task_groups SET name=?,parent_id=? WHERE id=? AND ws_id=?")->execute([$n,$np?:null,$id,$W]);
  if(array_key_exists('color',$in)){$c=(string)$in['color'];$db->prepare("UPDATE task_groups SET color=? WHERE id=? AND ws_id=?")->execute([preg_match('/^#[0-9a-fA-F]{6}$/',$c)?strtolower($c):null,$id,$W]);}
  out(['ok'=>1]);
case 'gr_del':
  need_level('full');del_gate($db,'group',(int)($in['id']??0));
case 'st_add':
  need_level('edit');$n=mb_substr(trim($in['name']??''),0,40);if($n==='')out(['error'=>'Enter a status name']);
  $all=get_statuses($db);if(count($all)>=13)out(['error'=>'Maximum 12 statuses plus Completed']);
  foreach($all as $x)if(mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'A status with that name already exists']);
  $mx=$db->prepare("SELECT COALESCE(MAX(sort_no),0) FROM statuses WHERE ws_id=? AND skey<>'done'");$mx->execute([$W]);
  $db->prepare("INSERT INTO statuses(ws_id,skey,name,sort_no) VALUES(?,?,?,?)")->execute([$W,'t'.bin2hex(random_bytes(6)),$n,(int)$mx->fetchColumn()+1]);
  $id=(int)$db->lastInsertId();$db->prepare("UPDATE statuses SET skey=? WHERE id=?")->execute(['s'.$id,$id]);out(['ok'=>1,'k'=>'s'.$id]);
case 'st_color':
  need_level('full');$c=(string)($in['color']??'');if($c!==''&&!preg_match('/^#[0-9a-fA-F]{6}$/',$c))out(['error'=>'Invalid color']);
  $db->prepare("UPDATE statuses SET color=? WHERE id=? AND ws_id=?")->execute([$c,(int)($in['id']??0),$W]);out(['ok'=>1]);
case 'st_ren':
  need_level('full');$n=mb_substr(trim($in['name']??''),0,40);$id=(int)($in['id']??0);if($n==='')out(['error'=>'Enter a name']);
  foreach(get_statuses($db) as $x)if($x['id']!=$id&&mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'A status with that name already exists']);
  $db->prepare("UPDATE statuses SET name=? WHERE id=? AND ws_id=? AND skey<>'done'")->execute([$n,$id,$W]);out(['ok'=>1]);
case 'st_del':
  need_level('full');del_gate($db,'status',(int)($in['id']??0));
case 'st_move':
  need_level('full');$id=(int)($in['id']??0);$d=((int)($in['dir']??0))<0?-1:1;
  $work=array_values(array_filter(get_statuses($db),fn($x)=>$x['k']!=='done'));$ix=null;
  foreach($work as $n=>$x)if($x['id']==$id)$ix=$n;
  if($ix===null||!isset($work[$ix+$d]))out(['ok'=>1]);
  [$work[$ix],$work[$ix+$d]]=[$work[$ix+$d],$work[$ix]];
  foreach($work as $n=>$x)$db->prepare("UPDATE statuses SET sort_no=? WHERE id=?")->execute([$n+1,$x['id']]);out(['ok'=>1]);
case 'cm_list':
  need_ws();$tid=(int)($in['task_id']??0);
  $q=$db->prepare("SELECT id FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);if(!$q->fetch())out(['error'=>'Task not found']);
  $q=$db->prepare("SELECT last_id FROM comment_reads WHERE user_id=? AND task_id=?");$q->execute([$me['id'],$tid]);$prev=(int)$q->fetchColumn();
  $q=$db->prepare("SELECT c.id,c.user_id,COALESCE(u.name,'Former member') AS name,c.to_user_id,tu.name AS to_name,c.kind,c.body,c.created_at FROM task_comments c LEFT JOIN users u ON u.id=c.user_id LEFT JOIN users tu ON tu.id=c.to_user_id WHERE c.task_id=? AND c.ws_id=? ORDER BY c.id");$q->execute([$tid,$W]);$rows=$q->fetchAll();
  if($rows){$mx=max(array_column($rows,'id'));$db->prepare("INSERT INTO comment_reads(user_id,task_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$me['id'],$tid,$mx]);}
  out(['comments'=>$rows,'prev'=>$prev,'me'=>(int)$me['id']]);
case 'cm_add':
  need_ws();$tid=(int)($in['task_id']??0);$body=mb_substr(trim((string)($in['body']??'')),0,2000);$kind=($in['kind']??'')==='instruction'?'instruction':'comment';$to=(int)($in['to_user_id']??0);
  if($body==='')out(['error'=>'Write a message first']);
  $q=$db->prepare("SELECT id FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);if(!$q->fetch())out(['error'=>'Task not found']);
  if($kind==='instruction'&&!$to)out(['error'=>'Choose who the instruction is for']);
  if($to){$q=$db->prepare("SELECT COUNT(*) FROM users u WHERE u.id=? AND (u.role='admin' OR u.id IN(SELECT user_id FROM ws_users WHERE ws_id=?))");$q->execute([$to,$W]);if(!$q->fetchColumn())out(['error'=>'That person cannot open this workspace']);}
  $db->prepare("INSERT INTO task_comments(task_id,ws_id,user_id,to_user_id,kind,body,created_at) VALUES(?,?,?,?,?,?,NOW())")->execute([$tid,$W,$me['id'],$to?:null,$kind,$body]);
  $nid=(int)$db->lastInsertId();
  $db->prepare("INSERT INTO comment_reads(user_id,task_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$me['id'],$tid,$nid]);
  out(['ok'=>1]);
case 'cm_del':
  need_ws();$id=(int)($in['id']??0);$q=$db->prepare("SELECT user_id FROM task_comments WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$o=$q->fetchColumn();
  if($o===false)out(['error'=>'Message not found']);
  if((int)$o!==(int)$me['id']&&$me['role']!=='admin')out(['error'=>'You can only delete your own messages']);
  $db->prepare("DELETE FROM task_comments WHERE id=?")->execute([$id]);out(['ok'=>1]);
case 'inbox':
  $rows=inbox_sql($db,false);$wn=array_column($WS,'name','id');foreach($rows as &$r)$r['ws_name']=$wn[$r['ws_id']]??'';out(['items'=>$rows]);
case 'inbox_read':
  $ids=ws_ids();if($ids){$u=(int)$me['id'];$in_=implode(',',$ids);
    $db->exec("INSERT INTO comment_reads(user_id,task_id,last_id) SELECT $u,task_id,MAX(id) FROM task_comments WHERE ws_id IN($in_) GROUP BY task_id HAVING SUM(to_user_id=$u)>0 ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))");}
  out(['ok'=>1]);
case 'ov_set':
  admin();$h=!empty($in['hand'])?1:0;$o=!empty($in['one'])?1:0;
  $st=$db->prepare("INSERT INTO meta(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)");$st->execute(['ov_hand',$h]);$st->execute(['ov_one',$o]);out(['ok'=>1]);
case 'ws_add':
  admin();$n=mb_substr(trim($in['name']??''),0,80);if($n==='')out(['error'=>'Enter a workspace name']);
  foreach($WS as $w)if(mb_strtolower($w['name'])===mb_strtolower($n))out(['error'=>'A workspace with that name already exists']);
  $db->prepare("INSERT INTO workspaces(name,sort_no) VALUES(?,?)")->execute([$n,(int)$db->query("SELECT COALESCE(MAX(sort_no),0)+1 FROM workspaces")->fetchColumn()]);
  out(['ok'=>1,'id'=>(int)$db->lastInsertId()]);
case 'ws_rename':
  admin();$n=mb_substr(trim($in['name']??''),0,80);if($n==='')out(['error'=>'Enter a name']);
  $db->prepare("UPDATE workspaces SET name=? WHERE id=?")->execute([$n,(int)($in['id']??0)]);out(['ok'=>1]);
case 'ws_logo':
  admin();$id=(int)($in['id']??0);$d=(string)($in['data']??'');
  if($d===''){$db->prepare("UPDATE workspaces SET logo=NULL WHERE id=?")->execute([$id]);out(['ok'=>1]);}
  if(!preg_match('#^data:image/(png|jpeg|webp);base64,([A-Za-z0-9+/=]+)$#',$d,$m))out(['error'=>'Unsupported image. Use PNG, JPG or WebP.']);
  $bin=base64_decode($m[2],true);if($bin===false||strlen($bin)>300000)out(['error'=>'Logo is too large. Use a smaller image.']);
  $i=@getimagesizefromstring($bin);if(!$i||strpos($i['mime'],'image/')!==0)out(['error'=>'That file is not a valid image']);
  $db->prepare("UPDATE workspaces SET logo=? WHERE id=?")->execute([$d,$id]);out(['ok'=>1]);
case 'ws_del':
  admin();$id=(int)($in['id']??0);
  if((int)$db->query("SELECT COUNT(*) FROM workspaces")->fetchColumn()<=1)out(['error'=>'You cannot delete the last workspace']);
  $db->prepare("DELETE FROM tasks WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM custom_cols WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM task_comments WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM time_logs WHERE ws_id=?")->execute([$id]);$db->prepare("DELETE FROM invoice_profiles WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM drawing_types WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM task_groups WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM statuses WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM ws_users WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM workspaces WHERE id=?")->execute([$id]);out(['ok'=>1]);
case 'ws_access':
  admin();$id=(int)($in['id']??0);$us=is_array($in['users']??null)?$in['users']:[];
  $db->prepare("DELETE FROM ws_users WHERE ws_id=?")->execute([$id]);
  foreach($us as $u){$lv=in_array($u['level']??'',['view','edit','full'],true)?$u['level']:'edit';
    $db->prepare("INSERT IGNORE INTO ws_users(ws_id,user_id,level) SELECT ?,id,IF(role='guest','view',?) FROM users WHERE id=?")->execute([$id,$lv,(int)($u['id']??0)]);}
  out(['ok'=>1]);
case 'user_add':
  admin();$n=trim($in['name']??'');$u=trim($in['username']??'');$p=$in['password']??'';$r=in_array($in['role']??'',['admin','guest'],true)?$in['role']:'member';
  if($n===''||!preg_match('/^[A-Za-z0-9_.-]{3,40}$/',$u)||strlen($p)<8)out(['error'=>'Need a name, a username (3-40 chars) and an 8+ character password']);
  try{$db->prepare("INSERT INTO users(name,username,pass_hash,role) VALUES(?,?,?,?)")->execute([mb_substr($n,0,100),$u,password_hash($p,PASSWORD_DEFAULT),$r]);}
  catch(PDOException $e){out(['error'=>'That username is already taken']);}
  $nid=(int)$db->lastInsertId();
  out(['ok'=>1]);
case 'user_pass':
  admin();if(strlen($in['password']??'')<8)out(['error'=>'Password must be 8+ characters']);
  $db->prepare("UPDATE users SET pass_hash=? WHERE id=?")->execute([password_hash($in['password'],PASSWORD_DEFAULT),(int)$in['id']]);try{$db->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([(int)$in['id']]);}catch(Throwable $e){}out(['ok'=>1]);
case 'user_role':
  admin();$id=(int)($in['id']??0);$r=in_array($in['role']??'',['admin','guest'],true)?$in['role']:'member';
  if($id===$uid)out(['error'=>'You cannot change your own type']);
  $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$r,$id]);
  if($r==='guest'){$db->prepare("UPDATE ws_users SET level='view' WHERE user_id=?")->execute([$id]);}
  out(['ok'=>1]);
case 'user_del':
  admin();$id=(int)($in['id']??0);if($id===$uid)out(['error'=>'You cannot remove yourself']);
  $db->prepare("UPDATE tasks SET assigned_to=NULL WHERE assigned_to=?")->execute([$id]);
  $db->prepare("DELETE FROM ws_users WHERE user_id=?")->execute([$id]);
  try{$db->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([$id]);}catch(Throwable $e){}
  $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);out(['ok'=>1]);
}
out(['error'=>'unknown action']);
