<?php
require 'config.php';require 'remember.php';
header('Content-Type: application/json');
$WRITEA=['save','field','delete','dup','col_add','col_del','user_add','user_pass','user_del','ws_add','ws_rename','ws_logo','ov_set','ws_del','ws_access','st_add','st_ren','st_del','st_move','st_color','gr_add','gr_ren','gr_del','cm_add','cm_del','gantt_set','task_move','dt_add','dt_ren','dt_del','task_order'];
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
  if(!empty($_SESSION['schema_v13']))return;
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
  $_SESSION['schema_v13']=1;
}
try{ensure_schema($db);}catch(Throwable $e){out(['error'=>'Database upgrade failed: '.$e->getMessage()]);}
$q=$db->prepare("SELECT pref_view FROM users WHERE id=?");$q->execute([$uid]);$pv=$q->fetchColumn();$me['pref_view']=in_array($pv,['board','gantt'],true)?$pv:'list';
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
switch($a){
case 'ping':
  out(['v'=>(int)$db->query("SELECT v FROM meta WHERE k='ver'")->fetchColumn()]);
case 'list':
  $ver=(int)$db->query("SELECT v FROM meta WHERE k='ver'")->fetchColumn();
  $names=[];foreach($db->query("SELECT id,name FROM users")->fetchAll() as $u)$names[$u['id']]=$u['name'];
  $res=['inbox'=>inbox_sql($db,true),'v'=>$ver,'me'=>$me,'ws'=>$WS,'w'=>$W,'level'=>$LV,'names'=>(object)$names,'users'=>[],'cols'=>[],'tasks'=>[]];
  if($W){
    $q=$db->prepare("SELECT id,name,role FROM users WHERE role='admin' OR id IN(SELECT user_id FROM ws_users WHERE ws_id=?) ORDER BY name");$q->execute([$W]);$res['users']=$q->fetchAll();
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
    $res['overlaps']=(object)$ov;$res['ovset']=$ovs;
    $res['tasks']=$tasks;$res['statuses']=get_statuses($db);
    $q=$db->prepare("SELECT id,name FROM task_groups WHERE ws_id=? ORDER BY sort_no,id");$q->execute([$W]);$res['groups']=$q->fetchAll();
    $sk="dt_seed_$W";$q=$db->prepare("SELECT v FROM meta WHERE k=?");$q->execute([$sk]);
    if($q->fetchColumn()===false){
      $db->prepare("INSERT IGNORE INTO drawing_types(ws_id,name,sort_no) SELECT ?,TRIM(drawing_type),0 FROM tasks WHERE ws_id=? AND drawing_type IS NOT NULL AND TRIM(drawing_type)<>'' GROUP BY TRIM(drawing_type)")->execute([$W,$W]);
      $db->prepare("INSERT INTO meta(k,v) VALUES(?,1) ON DUPLICATE KEY UPDATE v=1")->execute([$sk]);
    }
    $q=$db->prepare("SELECT id,name FROM drawing_types WHERE ws_id=? ORDER BY name");$q->execute([$W]);$res['dtypes']=$q->fetchAll();$res['cols']=get_cols($db);
  }
  if($me['role']==='admin'){
    $res['allUsers']=$db->query("SELECT id,name,username,role FROM users ORDER BY name")->fetchAll();
    $acc=[];foreach($db->query("SELECT ws_id,user_id,level FROM ws_users")->fetchAll() as $r)$acc[$r['ws_id']][$r['user_id']]=$r['level'];
    $res['access']=(object)$acc;
  }
  out($res);
case 'save':
  need_level('edit');$v=[];foreach($F as $f)$v[$f]=clean($f,$in[$f]??'');
  if($v['title']==='')out(['error'=>'Title required']);
  if(!empty($in['id'])){
    $set=implode(',',array_map(fn($f)=>"$f=?",$F));
    $db->prepare("UPDATE tasks SET $set,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([...array_values($v),(int)$in['id'],$W]);
  }else{
    $db->prepare("INSERT INTO tasks(".implode(',',$F).",ws_id,updated_at) VALUES(".implode(',',array_fill(0,count($F),'?')).",?,NOW())")->execute([...array_values($v),$W]);
    $in['id']=$db->lastInsertId();
  }
  if(isset($in['x'])&&is_array($in['x'])){
    $vals=[];foreach(get_cols($db) as $c){$k='x_'.$c['id'];if(array_key_exists($k,$in['x']))$vals[$k]=clean_x($c,$in['x'][$k]);}
    if($vals)set_x($db,(int)$in['id'],$vals);
  }
  out(['ok'=>1]);
case 'field':
  need_level('edit');$f=$in['f']??'';
  if(preg_match('/^x_(\d+)$/',$f,$m)){
    foreach(get_cols($db) as $c)if($c['id']==$m[1]){set_x($db,(int)($in['id']??0),[$f=>clean_x($c,$in['v']??'')]);out(['ok'=>1]);}
    out(['error'=>'Unknown column']);
  }
  if(!in_array($f,$F,true))out(['error'=>'bad field']);
  $v=clean($f,$in['v']??'');if($f==='title'&&$v==='')out(['ok'=>1]);
  $db->prepare("UPDATE tasks SET $f=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$v,(int)($in['id']??0),$W]);out(['ok'=>1]);
case 'delete':
  need_level('full');foreach($in['ids']??[] as $i){$db->prepare("DELETE FROM tasks WHERE id=? AND ws_id=?")->execute([(int)$i,$W]);$db->prepare("DELETE FROM task_comments WHERE task_id=? AND ws_id=?")->execute([(int)$i,$W]);}out(['ok'=>1]);
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
  $p[]=$id;$p[]=$W;
  $db->prepare("UPDATE tasks SET $set,updated_at=NOW() WHERE id=? AND ws_id=?")->execute($p);out(['ok'=>1]);
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
    if(trim((string)$t['drawing_type'])!=='')$db->prepare("INSERT IGNORE INTO drawing_types(ws_id,name) VALUES(?,?)")->execute([$to,trim($t['drawing_type'])]);$n++;
  }
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
case 'dt_del':
  need_level('full');$db->prepare("DELETE FROM drawing_types WHERE id=? AND ws_id=?")->execute([(int)($in['id']??0),$W]);out(['ok'=>1]);
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
case 'col_del':
  need_level('full');$id=(int)($in['id']??0);$k='x_'.$id;
  $db->prepare("DELETE FROM custom_cols WHERE id=? AND ws_id=?")->execute([$id,$W]);
  $q=$db->prepare("SELECT id,extra FROM tasks WHERE extra IS NOT NULL AND ws_id=?");$q->execute([$W]);
  foreach($q->fetchAll() as $r){
    $x=json_decode($r['extra'],true);if(is_array($x)&&array_key_exists($k,$x)){unset($x[$k]);
      $db->prepare("UPDATE tasks SET extra=? WHERE id=? AND ws_id=?")->execute([$x?json_encode($x,JSON_UNESCAPED_UNICODE):null,$r['id'],$W]);}
  }
  out(['ok'=>1]);
case 'pref_view':
  $v=in_array($in['v']??'',['board','gantt'],true)?$in['v']:'list';$db->prepare("UPDATE users SET pref_view=? WHERE id=?")->execute([$v,$uid]);out(['ok'=>1]);
case 'gr_add':
  need_level('edit');$n=mb_substr(trim($in['name']??''),0,80);if($n==='')out(['error'=>'Enter a group name']);
  $q=$db->prepare("SELECT id,name FROM task_groups WHERE ws_id=?");$q->execute([$W]);$all=$q->fetchAll();
  if(count($all)>=50)out(['error'=>'Maximum 50 groups']);
  foreach($all as $x)if(mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'A group with that name already exists']);
  $mx=$db->prepare("SELECT COALESCE(MAX(sort_no),0)+1 FROM task_groups WHERE ws_id=?");$mx->execute([$W]);
  $db->prepare("INSERT INTO task_groups(ws_id,name,sort_no) VALUES(?,?,?)")->execute([$W,$n,(int)$mx->fetchColumn()]);out(['ok'=>1,'id'=>(int)$db->lastInsertId()]);
case 'gr_ren':
  need_level('full');$n=mb_substr(trim($in['name']??''),0,80);$id=(int)($in['id']??0);if($n==='')out(['error'=>'Enter a name']);
  $q=$db->prepare("SELECT id,name FROM task_groups WHERE ws_id=?");$q->execute([$W]);
  foreach($q->fetchAll() as $x)if($x['id']!=$id&&mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'A group with that name already exists']);
  $db->prepare("UPDATE task_groups SET name=? WHERE id=? AND ws_id=?")->execute([$n,$id,$W]);out(['ok'=>1]);
case 'gr_del':
  need_level('full');$id=(int)($in['id']??0);
  $db->prepare("UPDATE tasks SET group_id=NULL WHERE group_id=? AND ws_id=?")->execute([$id,$W]);
  $db->prepare("DELETE FROM task_groups WHERE id=? AND ws_id=?")->execute([$id,$W]);out(['ok'=>1]);
case 'st_add':
  need_level('edit');$n=mb_substr(trim($in['name']??''),0,40);if($n==='')out(['error'=>'Enter a status name']);
  $all=get_statuses($db);if(count($all)>=13)out(['error'=>'Maximum 12 statuses plus Completed']);
  foreach($all as $x)if(mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'A status with that name already exists']);
  $mx=$db->prepare("SELECT COALESCE(MAX(sort_no),0) FROM statuses WHERE ws_id=? AND skey<>'done'");$mx->execute([$W]);
  $db->prepare("INSERT INTO statuses(ws_id,skey,name,sort_no) VALUES(?,?,?,?)")->execute([$W,'t'.bin2hex(random_bytes(6)),$n,(int)$mx->fetchColumn()+1]);
  $id=(int)$db->lastInsertId();$db->prepare("UPDATE statuses SET skey=? WHERE id=?")->execute(['s'.$id,$id]);out(['ok'=>1]);
case 'st_color':
  need_level('full');$c=(string)($in['color']??'');if($c!==''&&!preg_match('/^#[0-9a-fA-F]{6}$/',$c))out(['error'=>'Invalid color']);
  $db->prepare("UPDATE statuses SET color=? WHERE id=? AND ws_id=?")->execute([$c,(int)($in['id']??0),$W]);out(['ok'=>1]);
case 'st_ren':
  need_level('full');$n=mb_substr(trim($in['name']??''),0,40);$id=(int)($in['id']??0);if($n==='')out(['error'=>'Enter a name']);
  foreach(get_statuses($db) as $x)if($x['id']!=$id&&mb_strtolower($x['name'])===mb_strtolower($n))out(['error'=>'A status with that name already exists']);
  $db->prepare("UPDATE statuses SET name=? WHERE id=? AND ws_id=? AND skey<>'done'")->execute([$n,$id,$W]);out(['ok'=>1]);
case 'st_del':
  need_level('full');$id=(int)($in['id']??0);$work=array_values(array_filter(get_statuses($db),fn($x)=>$x['k']!=='done'));
  $tgt=null;foreach($work as $x)if($x['id']==$id)$tgt=$x;
  if(!$tgt)out(['error'=>'Status not found']);
  $others=array_values(array_filter($work,fn($x)=>$x['id']!=$id));if(!$others)out(['error'=>'You need at least one working status']);
  $db->prepare("UPDATE tasks SET status=? WHERE ws_id=? AND status=?")->execute([$others[0]['k'],$W,$tgt['k']]);
  $db->prepare("DELETE FROM statuses WHERE id=? AND ws_id=?")->execute([$id,$W]);out(['ok'=>1]);
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
  $db->prepare("DELETE FROM drawing_types WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM task_groups WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM statuses WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM ws_users WHERE ws_id=?")->execute([$id]);
  $db->prepare("DELETE FROM workspaces WHERE id=?")->execute([$id]);out(['ok'=>1]);
case 'ws_access':
  admin();$id=(int)($in['id']??0);$us=is_array($in['users']??null)?$in['users']:[];
  $db->prepare("DELETE FROM ws_users WHERE ws_id=?")->execute([$id]);
  foreach($us as $u){$lv=in_array($u['level']??'',['view','edit','full'],true)?$u['level']:'edit';
    $db->prepare("INSERT IGNORE INTO ws_users(ws_id,user_id,level) SELECT ?,id,? FROM users WHERE id=?")->execute([$id,$lv,(int)($u['id']??0)]);}
  out(['ok'=>1]);
case 'user_add':
  admin();$n=trim($in['name']??'');$u=trim($in['username']??'');$p=$in['password']??'';$r=($in['role']??'')==='admin'?'admin':'member';
  if($n===''||!preg_match('/^[A-Za-z0-9_.-]{3,40}$/',$u)||strlen($p)<8)out(['error'=>'Need a name, a username (3-40 chars) and an 8+ character password']);
  try{$db->prepare("INSERT INTO users(name,username,pass_hash,role) VALUES(?,?,?,?)")->execute([mb_substr($n,0,100),$u,password_hash($p,PASSWORD_DEFAULT),$r]);}
  catch(PDOException $e){out(['error'=>'That username is already taken']);}
  if($W)$db->prepare("INSERT IGNORE INTO ws_users(ws_id,user_id) VALUES(?,?)")->execute([$W,(int)$db->lastInsertId()]);
  out(['ok'=>1]);
case 'user_pass':
  admin();if(strlen($in['password']??'')<8)out(['error'=>'Password must be 8+ characters']);
  $db->prepare("UPDATE users SET pass_hash=? WHERE id=?")->execute([password_hash($in['password'],PASSWORD_DEFAULT),(int)$in['id']]);try{$db->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([(int)$in['id']]);}catch(Throwable $e){}out(['ok'=>1]);
case 'user_role':
  admin();$id=(int)($in['id']??0);$r=($in['role']??'')==='admin'?'admin':'member';
  if($id===$uid)out(['error'=>'You cannot change your own type']);
  $db->prepare("UPDATE users SET role=? WHERE id=?")->execute([$r,$id]);out(['ok'=>1]);
case 'user_del':
  admin();$id=(int)($in['id']??0);if($id===$uid)out(['error'=>'You cannot remove yourself']);
  $db->prepare("UPDATE tasks SET assigned_to=NULL WHERE assigned_to=?")->execute([$id]);
  $db->prepare("DELETE FROM ws_users WHERE user_id=?")->execute([$id]);
  try{$db->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([$id]);}catch(Throwable $e){}
  $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);out(['ok'=>1]);
}
out(['error'=>'unknown action']);
