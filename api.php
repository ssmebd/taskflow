<?php
const BUILD=278;
require 'config.php';require 'remember.php';
header('Content-Type: application/json');
if(PHP_SAPI==='cli'){$cmd=$argv[1]??'';if($cmd==='remind'){echo rem_run($db,$argv[2]??'',($argv[3]??'')==='force')."\n".sr_run($db)."\n";exit(0);}$r=$cmd==='backup'?bk_run($db):($cmd==='worklog'?wl_run($db):['ok'=>false,'file'=>'','err'=>'Usage: php api.php backup | worklog | remind [am|pm [force]]']);echo ($r['ok']?'OK ':'FAILED ').$r['file'].' '.$r['err']."\n";exit($r['ok']?0:1);}
$TASKA=['save','field','delete','dup','co_set','task_move','task_order','tl_add','tl_rev','cm_add'];
$AUD_T='';
function aud_detail(){global $a,$in,$AUD_T;$t=$AUD_T;$f=(string)($in['f']??'');
  if($a==='save')return(empty($in['id'])?'Created: ':'Edited: ').($t!==''?$t:(string)($in['title']??''));
  if($a==='field')return $t.' - '.$f.' = '.mb_substr((string)($in['v']??''),0,60);
  if($a==='tl_add')return $t.' - '.(float)($in['hours']??0).'h on '.(string)($in['date']??'');
  if($a==='cm_add')return $t.((($in['kind']??'')==='instruction')?' (instruction)':'');
  foreach(['name','username','level','role'] as $k)if(isset($in[$k])&&is_scalar($in[$k])&&$t==='')return $k.': '.mb_substr((string)$in[$k],0,60);
  return $t;}
$WRITEA=['save','field','delete','dup','col_add','col_del','col_edit','user_add','user_edit','user_pass','user_del','ws_add','ws_rename','ws_logo','ov_set','ws_del','ws_access','dr_act','co_set','st_add','st_ren','st_del','st_move','st_color','gr_add','gr_ren','gr_del','cm_add','cm_del','gantt_set','task_move','gr_move','dt_add','dt_ren','dt_del','gr_order','task_order','tl_add','tl_del','tl_rev','inv_set','dt_color','inv_create','inv_step','inv_pay','inv_del'];

// ---- Backups: database dump to a private folder, daily copy by email ----
if(!defined('BACKUP_EMAIL'))define('BACKUP_EMAIL','gurucadd.bd@gmail.com');
// Daily reminders (cron): php api.php remind  -> run every 30 min; sends at ~8am (am) and ~3pm (pm) Dhaka time, once per day each, Mon-Fri.
// Sends in-app chat messages: log-hours reminder (+ "set a task In Progress" when nothing is ongoing); admins get an alert for people with no assigned task.
// ---- Chat rich text (Outlook / Word paste): strict whitelist, rebuilt from the DOM ----
function msg_clean_html($h){$h=mb_substr((string)$h,0,150000);if(trim($h)==='')return '';
  $d=new DOMDocument();libxml_use_internal_errors(true);$d->loadHTML('<?xml encoding="utf-8"?><div id="r">'.$h.'</div>',LIBXML_HTML_NODEFDTD);libxml_clear_errors();
  $xp=new DOMXPath($d);$r=$xp->query("//div[@id='r']")->item(0);if(!$r)return '';
  $ok=['p'=>1,'div'=>1,'b'=>1,'strong'=>1,'i'=>1,'em'=>1,'u'=>1,'s'=>1,'strike'=>1,'del'=>1,'ul'=>1,'ol'=>1,'li'=>1,'blockquote'=>1,'pre'=>1,'code'=>1,'span'=>1,'table'=>1,'thead'=>1,'tbody'=>1,'tfoot'=>1,'tr'=>1,'td'=>1,'th'=>1,'hr'=>1,'sub'=>1,'sup'=>1];
  $w=function($n,$dep)use(&$w,$ok){if($dep>20)return '';$o='';
    foreach($n->childNodes as $c){
      if($c->nodeType===XML_TEXT_NODE){$o.=htmlspecialchars($c->nodeValue,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');continue;}
      if($c->nodeType!==XML_ELEMENT_NODE)continue;$t=strtolower($c->nodeName);
      if(in_array($t,['script','style','head','title','meta','link','object','embed','iframe','form','input','button','svg','xml'],true))continue;
      if($t==='br'){$o.='<br>';continue;}
      if($t==='hr'){$o.='<hr>';continue;}
      if($t==='img'){$i=$c->getAttribute('data-i');if($i!==''&&ctype_digit($i))$o.='<img data-i="'.(int)$i.'">';continue;}
      if($t==='a'){$hr=trim($c->getAttribute('href'));$in=$w($c,$dep+1);if(preg_match('~^(https?://|mailto:)~i',$hr))$o.='<a href="'.htmlspecialchars($hr,ENT_QUOTES,'UTF-8').'" target="_blank" rel="noopener noreferrer">'.$in.'</a>';else $o.=$in;continue;}
      if(preg_match('/^h[1-6]$/',$t)){$o.='<p><b>'.$w($c,$dep+1).'</b></p>';continue;}
      if(isset($ok[$t])){$at='';if($t==='td'||$t==='th'){foreach(['colspan','rowspan'] as $k){$v=$c->getAttribute($k);if($v!==''&&ctype_digit($v)&&(int)$v>0&&(int)$v<20)$at.=' '.$k.'="'.(int)$v.'"';}}
        $o.='<'.$t.$at.'>'.$w($c,$dep+1).'</'.$t.'>';continue;}
      $o.=$w($c,$dep+1);}
    return $o;};
  $out=$w($r,0);$out=preg_replace('~(<br>\s*){4,}~i','<br><br>',$out);$out=preg_replace('~<(p|div|span)>\s*</\1>~i','',$out);
  return mb_strlen($out)>80000?'':trim($out);}
// ---- Flow: the assistant's notification centre (bell). Automatic reminders/alerts land here, not in chat. ----
function notices_ddl($db){static $d=0;if($d)return;$d=1;$db->exec("CREATE TABLE IF NOT EXISTS notices(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,kind VARCHAR(16) NOT NULL DEFAULT 'info',title VARCHAR(160) NOT NULL DEFAULT '',body VARCHAR(1000) NOT NULL DEFAULT '',ws_id INT NOT NULL DEFAULT 0,task_id INT NOT NULL DEFAULT 0,act VARCHAR(12) NOT NULL DEFAULT '',dkey VARCHAR(60) NOT NULL DEFAULT '',created_at DATETIME NOT NULL,read_at DATETIME NULL,KEY u(user_id,read_at,id),KEY d(user_id,dkey)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}
function notices_unread($db){global $me;try{notices_ddl($db);$q=$db->prepare("SELECT COUNT(*) FROM notices WHERE user_id=? AND read_at IS NULL");$q->execute([(int)$me['id']]);return (int)$q->fetchColumn();}catch(Throwable $e){return 0;}}
function notify($db,$to,$kind,$title,$body='',$o=[]){$to=(int)$to;if(!$to)return;try{notices_ddl($db);$dk=(string)($o['dkey']??'');
  if($dk!==''){$q=$db->prepare("SELECT COUNT(*) FROM notices WHERE user_id=? AND dkey=?");$q->execute([$to,$dk]);if((int)$q->fetchColumn())return;}
  $db->prepare("INSERT INTO notices(user_id,kind,title,body,ws_id,task_id,act,dkey,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())")->execute([$to,$kind,mb_substr($title,0,160),mb_substr($body,0,1000),(int)($o['ws']??0),(int)($o['task']??0),(string)($o['act']??''),$dk]);
  push_q($to,'Flow · '.$title,$body!==''?$body:$title);}catch(Throwable $e){}}
function rem_key(){return substr(hash_hmac('sha256','taskflow-remind',DB_PASS.DB_NAME),0,24);}
function rem_url(){$h=$_SERVER['HTTP_HOST']??'taskflow.gurucadd.com';return 'https://'.$h.rtrim(dirname($_SERVER['SCRIPT_NAME']??'/api.php'),'/').'/api.php?a=remind&key='.rem_key();}
function rem_run($db,$slot='',$force=false){
  $tz=new DateTimeZone('Asia/Dhaka');$now=new DateTime('now',$tz);$h=(int)$now->format('G');$today=$now->format('Y-m-d');$dow=(int)$now->format('N');
  if($slot!=='am'&&$slot!=='pm'){$slot=($h>=8&&$h<10)?'am':(($h>=15&&$h<17)?'pm':'');}
  if($slot==='')return 'Not a reminder time (Dhaka '.$now->format('H:i').')';
  if(!$force&&$dow>=6)return 'Weekend - no reminders';
  $db->exec("CREATE TABLE IF NOT EXISTS meta(k VARCHAR(20) PRIMARY KEY,v BIGINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $key='rem_'.$slot;$dn=(int)str_replace('-','',$today);
  $q=$db->prepare("SELECT v FROM meta WHERE k=?");$q->execute([$key]);
  if(!$force&&(int)$q->fetchColumn()===$dn)return 'Already sent today ('.$slot.')';
  $db->prepare("INSERT INTO meta(k,v) VALUES(?,?) ON DUPLICATE KEY UPDATE v=VALUES(v)")->execute([$key,$dn]);
  $users=$db->query("SELECT id,name,role FROM users WHERE role<>'guest' ORDER BY id")->fetchAll();
  if(!$users)return 'No users';
  $admins=array_values(array_filter($users,fn($u)=>$u['role']==='admin'));
  $sender=function($to)use($users,$admins){foreach($admins as $a)if((int)$a['id']!==(int)$to)return (int)$a['id'];foreach($users as $u)if((int)$u['id']!==(int)$to)return (int)$u['id'];return 0;};
  $prog=[];foreach($db->query("SELECT ws_id,skey,name FROM statuses")->fetchAll() as $r)if($r['skey']==='progress'||preg_match('/progress|ongoing|working/i',(string)$r['name']))$prog[$r['ws_id'].'|'.$r['skey']]=1;
  $open=$db->query("SELECT t.id,t.ws_id,t.status,t.assigned_to,t.title,t.end_date FROM tasks t JOIN workspaces w ON w.id=t.ws_id WHERE t.status<>'done'")->fetchAll();
  $asg=[];foreach($db->query("SELECT task_id,user_id FROM task_assignees")->fetchAll() as $r)$asg[(int)$r['task_id']][]=(int)$r['user_id'];
  $mine=[];$going=[];$first=[];$due=[];$late=[];
  foreach($open as $t){$us=$asg[(int)$t['id']]??[];if($t['assigned_to'])$us[]=(int)$t['assigned_to'];
    foreach(array_unique($us) as $u){if(!isset($first[$u]))$first[$u]=[(int)$t['ws_id'],(int)$t['id']];$mine[$u]=($mine[$u]??0)+1;$ed=(string)($t['end_date']??'');if($ed!==''&&$ed!=='0000-00-00'){if($ed===$today)$due[$u][]=$t;elseif($ed<$today)$late[$u][]=$t;}if(isset($prog[$t['ws_id'].'|'.$t['status']]))$going[$u]=($going[$u]??0)+1;}}
  $prev=$now->modify('-1 day')->format('Y-m-d');$now=new DateTime('now',$tz);
  $lb=$dow===1?date('Y-m-d',strtotime($today.' -3 day')):$prev;
  $hq=$db->prepare("SELECT COALESCE(SUM(hours),0) FROM time_logs WHERE user_id=? AND work_date=?");
  $nUsers=0;$unas=[];
  foreach($users as $u){$id=(int)$u['id'];$fn=explode(' ',trim((string)$u['name']))[0];
    $fh=function($x){return rtrim(rtrim(number_format($x,2),'0'),'.');};
    if($slot==='am'){$hq->execute([$id,$lb]);$hh=(float)$hq->fetchColumn();
      notify($db,$id,'hours',"Good morning, ".$fn." ☀️",$hh<7.99?"Last working day you logged ".$fh($hh)."h of 8h. Let's complete it so your hours are accurate.":"Your hours are up to date. Keep logging as you go today.",['act'=>'hours','dkey'=>'am'.$today]);}
    else{$hq->execute([$id,$today]);$hh=(float)$hq->fetchColumn();
      notify($db,$id,'hours',"Time to log today's hours 🕒",$fh($hh)."h of 8h logged so far. A minute now saves guessing later.",['act'=>'hours','dkey'=>'pm'.$today]);}
    if(($mine[$id]??0)>0&&empty($going[$id])){$f=$first[$id]??[0,0];
      notify($db,$id,'nudge',"Nothing is In Progress",$mine[$id]." task".($mine[$id]>1?"s are":" is")." assigned to you but none is In Progress. Open one and set it going.",['act'=>'task','ws'=>$f[0],'task'=>$f[1],'dkey'=>'np'.$slot.$today]);}
    if($slot==='am'){
      if(!empty($due[$id])){$d=$due[$id];$f=$d[0];notify($db,$id,'nudge',count($d)>1?count($d)." tasks are due today":"Due today: ".$f['title'],count($d)>1?implode("\n",array_map(fn($x)=>"• ".$x['title'],array_slice($d,0,4))):"This one finishes today. Need more time? Adjust the date early so the plan stays honest.",['act'=>'task','ws'=>(int)$f['ws_id'],'task'=>(int)$f['id'],'dkey'=>'due'.$today]);}
      if(!empty($late[$id])){$d=$late[$id];$f=$d[0];notify($db,$id,'alert',count($d)>1?count($d)." tasks are overdue":"Overdue: ".$f['title'],count($d)>1?implode("\n",array_map(fn($x)=>"• ".$x['title']." (was due ".$x['end_date'].")",array_slice($d,0,4))):"This was due ".$f['end_date'].". Finish it, or move the end date if the plan changed.",['act'=>'task','ws'=>(int)$f['ws_id'],'task'=>(int)$f['id'],'dkey'=>'late'.$today]);}}
    $nUsers++;
    if(!($mine[$id]??0)&&$u['role']!=='admin')$unas[]=$u;}
  foreach($unas as $u)foreach($admins as $a){$aid=(int)$a['id'];if($aid===(int)$u['id'])continue;
    notify($db,$aid,'alert',$u['name']." has no open task","Nothing is assigned to ".$u['name'].", so nothing is ongoing for them. Please assign work.",['dkey'=>'un'.$u['id'].$today]);}
  return 'Sent '.$slot.' reminders to '.$nUsers.' users; '.count($unas).' unassigned alert(s)';
}
// ---- Phone push (Firebase Cloud Messaging, HTTP v1). Needs the service-account key file on the server:
// define('FCM_KEY_FILE','/home/USER/taskflow-fcm.json') in config.php, or just place taskflow-fcm.json in your home folder.
$PUSHQ=[];
function push_q($to,$title,$body,$app=''){global $PUSHQ;$PUSHQ[]=[(int)$to,(string)$title,(string)$body,(string)$app];}
function push_key(){
  $c=[];if(defined('FCM_KEY_FILE'))$c[]=FCM_KEY_FILE;$h=getenv('HOME');if($h)$c[]=rtrim($h,'/').'/taskflow-fcm.json';$c[]=dirname(__DIR__,2).'/taskflow-fcm.json';$c[]=__DIR__.'/../taskflow-fcm.json';
  foreach($c as $f)if($f&&is_file($f)){$j=json_decode((string)file_get_contents($f),true);if(is_array($j)&&!empty($j['private_key'])&&!empty($j['client_email'])&&!empty($j['project_id']))return $j;}
  return null;}
function push_b64($s){return rtrim(strtr(base64_encode($s),'+/','-_'),'=');}
function push_token($k){
  $cf=sys_get_temp_dir().'/tf_fcm_'.md5($k['client_email']).'.json';
  if(is_file($cf)){$c=json_decode((string)file_get_contents($cf),true);if(is_array($c)&&($c['exp']??0)>time()+120)return $c['tok'];}
  $uri=$k['token_uri']??'https://oauth2.googleapis.com/token';$now=time();
  $in=push_b64(json_encode(['alg'=>'RS256','typ'=>'JWT'])).'.'.push_b64(json_encode(['iss'=>$k['client_email'],'scope'=>'https://www.googleapis.com/auth/firebase.messaging','aud'=>$uri,'iat'=>$now,'exp'=>$now+3600]));
  if(!openssl_sign($in,$sig,$k['private_key'],OPENSSL_ALGO_SHA256))return '';
  $ch=curl_init($uri);curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>http_build_query(['grant_type'=>'urn:ietf:params:oauth:grant-type:jwt-bearer','assertion'=>$in.'.'.push_b64($sig)]),CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>6,CURLOPT_CONNECTTIMEOUT=>4]);
  $r=json_decode((string)curl_exec($ch),true);curl_close($ch);
  if(empty($r['access_token']))return '';
  @file_put_contents($cf,json_encode(['tok'=>$r['access_token'],'exp'=>$now+(int)($r['expires_in']??3500)]));@chmod($cf,0600);return $r['access_token'];}
function push_send_one($k,$tok,$token,$title,$body){
  $ch=curl_init('https://fcm.googleapis.com/v1/projects/'.$k['project_id'].'/messages:send');
  curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$tok,'Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode(['message'=>['token'=>$token,'notification'=>['title'=>$title,'body'=>$body],'android'=>['priority'=>'HIGH','notification'=>['sound'=>'default']]]]),CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>6,CURLOPT_CONNECTTIMEOUT=>4]);
  $o=(string)curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);return [$code,$o];}
function push_flush(){global $PUSHQ,$db,$uid;if(!$PUSHQ||!isset($db)||!function_exists('curl_init'))return;$q=$PUSHQ;$PUSHQ=[];
  if(function_exists('fastcgi_finish_request'))@fastcgi_finish_request();
  try{$k=push_key();if(!$k)return;
    try{$db->exec("CREATE TABLE IF NOT EXISTS push_tokens(token VARCHAR(400) NOT NULL,user_id INT NOT NULL,platform VARCHAR(12) NOT NULL DEFAULT 'android',updated_at DATETIME NOT NULL,PRIMARY KEY(token(190)),KEY(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");if(!$db->query("SHOW COLUMNS FROM push_tokens LIKE 'app'")->fetch())$db->exec("ALTER TABLE push_tokens ADD app VARCHAR(12) NOT NULL DEFAULT ''");}catch(Throwable $e){}
    $tok='';$sel=$db->prepare("SELECT token,app FROM push_tokens WHERE user_id=?");$del=$db->prepare("DELETE FROM push_tokens WHERE token=?");
    foreach($q as [$to,$title,$body,$app]){
      if($to>0)$ids=[$to];else{$ids=$db->query("SELECT DISTINCT user_id FROM push_tokens")->fetchAll(PDO::FETCH_COLUMN);}
      foreach($ids as $u){if($to===0&&(int)$u===(int)($uid??0))continue;$sel->execute([$u]);$rows=$sel->fetchAll();$tk=[];foreach($rows as $r0)if($app==='chat'?($r0['app']==='chat'):($r0['app']!=='chat'))$tk[]=$r0['token'];if(!$tk&&$app==='chat')$tk=array_column($rows,'token');
        foreach($tk as $t){if($tok==='')$tok=push_token($k);if($tok==='')return;
          [$code,$o]=push_send_one($k,$tok,$t,$title,mb_substr($body,0,180));
          if($code===404||($code===400&&strpos($o,'INVALID_ARGUMENT')!==false)||strpos($o,'UNREGISTERED')!==false)$del->execute([$t]);}}}
  }catch(Throwable $e){}}
register_shutdown_function('push_flush');
function bk_dir(){
  $c=[];$h=getenv('HOME');if($h)$c[]=rtrim($h,'/').'/taskflow-backups';$c[]=dirname(__DIR__,2).'/taskflow-backups';$c[]=__DIR__.'/backups';
  foreach($c as $d){
    if(!is_dir($d))@mkdir($d,0700,true);
    if(is_dir($d)&&is_writable($d)){
      if(strpos(realpath($d)?:$d,realpath(__DIR__)?:__DIR__)===0){@file_put_contents($d.'/.htaccess',"Require all denied\nDeny from all\n");@file_put_contents($d.'/index.html','');}
      return $d;}
  }
  return '';
}
function bk_dump($db,$path){
  $gz=gzopen($path,'wb6');if(!$gz)throw new Exception('Cannot write the backup file');
  gzwrite($gz,"-- TaskFlow backup ".date('Y-m-d H:i:s')."\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
  $tabs=$db->query("SHOW FULL TABLES WHERE Table_type='BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
  foreach($tabs as $tr){
    $t=$tr[0];$q='`'.str_replace('`','``',$t).'`';
    $cr=$db->query("SHOW CREATE TABLE $q")->fetch(PDO::FETCH_NUM);
    gzwrite($gz,"DROP TABLE IF EXISTS $q;\n".$cr[1].";\n\n");
    $st=$db->query("SELECT * FROM $q");$buf=[];$cols=null;
    while($r=$st->fetch(PDO::FETCH_ASSOC)){
      if($cols===null)$cols='(`'.implode('`,`',array_keys($r)).'`)';
      $buf[]='('.implode(',',array_map(fn($v)=>$v===null?'NULL':$db->quote((string)$v),array_values($r))).')';
      if(count($buf)>=100){gzwrite($gz,"INSERT INTO $q $cols VALUES\n".implode(",\n",$buf).";\n");$buf=[];}
    }
    if($buf)gzwrite($gz,"INSERT INTO $q $cols VALUES\n".implode(",\n",$buf).";\n");
    gzwrite($gz,"\n");
  }
  gzwrite($gz,"SET FOREIGN_KEY_CHECKS=1;\n");gzclose($gz);
}
function bk_files($dir){$o=[];foreach(glob($dir.'/taskflow-????-??-??_??-??.sql.gz')?:[] as $f)$o[]=['name'=>basename($f),'size'=>filesize($f),'time'=>filemtime($f)];usort($o,fn($x,$y)=>$y['time']<=>$x['time']);return $o;}
function bk_prune($dir){
  $keepDay=[];$now=time();
  foreach(bk_files($dir) as $f){
    if($now-$f['time']<=86400)continue;
    $d=substr($f['name'],9,10);
    if($now-$f['time']>14*86400||isset($keepDay[$d]))@unlink($dir.'/'.$f['name']);else $keepDay[$d]=1;
  }
}
function bk_host(){return preg_replace('/^www\./','',$_SERVER['SERVER_NAME']??'gurucadd.com');}
function bk_mail($file,$name,$why){
  $to=BACKUP_EMAIL;if(!$to)return 'No email address set';
  $size=is_file($file)?filesize($file):0;
  $b='tf'.bin2hex(random_bytes(8));$att=$size>0&&$size<=18*1024*1024;
  $txt="TaskFlow database backup: $name (".round($size/1024)." KB)\n$why\n".($att?"The backup is attached. To restore, unzip the .gz and import the .sql file in phpMyAdmin.":"The backup is too large to attach. It is saved on the server in the taskflow-backups folder; download it from TaskFlow > user menu > Backups.")."\n";
  $h="From: TaskFlow <backup@".bk_host().">\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$b\"\r\n";
  $m="--$b\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n$txt\r\n";
  if($att)$m.="--$b\r\nContent-Type: application/gzip; name=\"$name\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"$name\"\r\n\r\n".chunk_split(base64_encode(file_get_contents($file)))."\r\n";
  $m.="--$b--";
  return @mail($to,'TaskFlow backup '.substr($name,9,16),$m,$h)?'':'The server could not send the email';
}
function bk_run($db,$force=false){
  $dir=bk_dir();$res=['ok'=>false,'time'=>time(),'file'=>'','size'=>0,'emailed'=>false,'err'=>''];
  try{
    if($dir==='')throw new Exception('No writable folder for backups');
    $name='taskflow-'.date('Y-m-d_H-i').'.sql.gz';$path=$dir.'/'.$name;
    bk_dump($db,$path);@chmod($path,0600);
    if(is_file(__DIR__.'/config.php')&&!is_file($dir.'/config.php.bak')){@copy(__DIR__.'/config.php',$dir.'/config.php.bak');@chmod($dir.'/config.php.bak',0600);}
    $res['ok']=true;$res['file']=$name;$res['size']=filesize($path);
    $day=substr($name,9,10);$n=0;foreach(bk_files($dir) as $f)if(substr($f['name'],9,10)===$day)$n++;
    {$e=bk_mail($path,$name,$force?'Sent on request.':'Scheduled copy.');$res['emailed']=($e==='');if($e!=='')$res['err']='Saved, but email failed: '.$e;}
    bk_prune($dir);
  }catch(Throwable $e){
    $res['err']=$e->getMessage();
    @mail(BACKUP_EMAIL,'TaskFlow backup FAILED','The TaskFlow backup failed: '.$res['err']."\n",'From: TaskFlow <backup@'.bk_host().">\r\n");
  }
  if($dir!=='')@file_put_contents($dir.'/last.json',json_encode($res));
  aud_log($db,0,'system','activity','backup',$res['ok']?('Backup '.$res['file'].' ('.round($res['size']/1024).' KB)'.($res['emailed']?', emailed':'').($res['err']?' - '.$res['err']:'')):'Backup FAILED: '.$res['err']);
  return $res;
}

// ---- Worklog report: last 15 days, all companies, Excel file ----
function wl_x($s){return htmlspecialchars((string)$s,ENT_XML1|ENT_QUOTES,'UTF-8');}
function wl_col($i){$s='';$i++;while($i>0){$m=($i-1)%26;$s=chr(65+$m).$s;$i=intdiv($i-$m-1,26);}return $s;}
function wl_sheet($rows,$widths,$numCols=[]){
  $x='<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>';
  foreach($widths as $i=>$w)$x.='<col min="'.($i+1).'" max="'.($i+1).'" width="'.$w.'" customWidth="1"/>';
  $x.='</cols><sheetData>';
  foreach($rows as $ri=>$r){
    $x.='<row r="'.($ri+1).'">';
    foreach($r as $ci=>$v){
      $ref=wl_col($ci).($ri+1);$st=$ri===0?' s="1"':'';
      if($ri>0&&in_array($ci,$numCols,true)&&is_numeric($v))$x.='<c r="'.$ref.'" s="2"><v>'.$v.'</v></c>';
      else $x.='<c r="'.$ref.'" t="inlineStr"'.$st.'><is><t xml:space="preserve">'.wl_x($v).'</t></is></c>';
    }
    $x.='</row>';
  }
  return $x.'</sheetData></worksheet>';
}
function wl_data($db,$days=15){
  $q=$db->prepare("SELECT l.work_date,COALESCE(u.name,'Former member') AS person,COALESCE(w.name,'') AS company,t.order_no,t.title,t.customer,t.drawing_type,l.rev,l.kind,l.hours,l.note FROM time_logs l LEFT JOIN users u ON u.id=l.user_id LEFT JOIN workspaces w ON w.id=l.ws_id LEFT JOIN tasks t ON t.id=l.task_id WHERE l.work_date>=DATE_SUB(CURDATE(),INTERVAL ? DAY) ORDER BY l.work_date DESC,w.name,u.name,l.id");
  $q->execute([$days]);return $q->fetchAll();
}
function wl_build($db,$path,$days=15){
  $d=wl_data($db,$days);
  $rows=[['Date','Person','Company','Order No.','Task','Customer','Drawing type','Rev','Type','Hours','Note']];
  $tot=[];$grand=0;
  foreach($d as $r){
    $h=(float)$r['hours'];$ot=$r['kind']==='OT';
    $rows[]=[$r['work_date'],$r['person'],$r['company'],$r['order_no'],$r['title'],$r['customer'],$r['drawing_type'],$r['rev'],$ot?'OT':'Regular',$h,$r['note']];
    $k=$r['person']."\0".$r['company'];if(!isset($tot[$k]))$tot[$k]=[$r['person'],$r['company'],0,0];
    $tot[$k][$ot?3:2]+=$h;$grand+=$h;
  }
  ksort($tot);
  $t2=[['Person','Company','Regular hours','OT hours','Total hours']];
  foreach($tot as $v)$t2[]=[$v[0],$v[1],$v[2],$v[3],$v[2]+$v[3]];
  $t2[]=['Total','','','',$grand];
  $s1=wl_sheet($rows,[12,22,20,12,36,22,16,8,9,8,40],[9]);
  $s2=wl_sheet($t2,[24,22,15,12,13],[2,3,4]);
  $z=new ZipArchive();if($z->open($path,ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true)throw new Exception('Cannot write the worklog file');
  $z->addFromString('[Content_Types].xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
  $z->addFromString('_rels/.rels','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
  $z->addFromString('xl/workbook.xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Worklog" sheetId="1" r:id="rId1"/><sheet name="Totals" sheetId="2" r:id="rId2"/></sheets></workbook>');
  $z->addFromString('xl/_rels/workbook.xml.rels','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
  $z->addFromString('xl/styles.xml','<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><numFmts count="1"><numFmt numFmtId="164" formatCode="0.00"/></numFmts><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCF3EA"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
  $z->addFromString('xl/worksheets/sheet1.xml',$s1);$z->addFromString('xl/worksheets/sheet2.xml',$s2);
  $z->close();
  return ['rows'=>count($d),'hours'=>$grand];
}
function wl_files($dir){$o=[];foreach(glob($dir.'/worklog-????-??-??_??-??.xlsx')?:[] as $f)$o[]=['name'=>basename($f),'size'=>filesize($f),'time'=>filemtime($f)];usort($o,fn($x,$y)=>$y['time']<=>$x['time']);return $o;}
// ---- Task status report: completed this week, ongoing, starting in the next 3 days; emailed to guests on a weekly schedule ----
function sr_ddl($db){$db->exec("CREATE TABLE IF NOT EXISTS status_reports(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(80) NOT NULL DEFAULT '',ws_ids VARCHAR(200) NOT NULL DEFAULT '',emails TEXT NOT NULL,dow TINYINT NOT NULL DEFAULT 4,hhmm INT NOT NULL DEFAULT 900,active TINYINT NOT NULL DEFAULT 1,last_sent VARCHAR(10) NOT NULL DEFAULT '',created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");if(!$db->query("SHOW COLUMNS FROM status_reports LIKE 'excl_g'")->fetch())$db->exec("ALTER TABLE status_reports ADD excl_g TEXT NULL, ADD excl_t TEXT NULL");}
function gh_ddl($db){$db->exec("CREATE TABLE IF NOT EXISTS guest_hide(user_id INT NOT NULL,gid INT NOT NULL,PRIMARY KEY(user_id,gid)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}
function sr_now(){$tz=new DateTimeZone('Asia/Dhaka');return new DateTime('now',$tz);}
function sr_ids($v){return array_values(array_unique(array_filter(array_map('intval',is_array($v)?$v:explode(',',(string)$v)))));}
function sr_gid($r){return (int)$r['top_id']?:-1;}
function sr_data($db,$ws=[],$exg=[],$ext=[]){
  $now=sr_now();$today=$now->format('Y-m-d');$mon=date('Y-m-d',strtotime($today.' -'.((int)$now->format('N')-1).' day'));$sun=date('Y-m-d',strtotime($mon.' +6 day'));$nx=date('Y-m-d',strtotime($today.' +3 day'));
  $ws=array_values(array_filter(array_map('intval',(array)$ws)));
  $wh=$ws?" AND t.ws_id IN(".implode(',',$ws).")":"";
  $rows=$db->query("SELECT t.id,t.ws_id,COALESCE(w.name,'') AS company,t.title,t.order_no,t.customer,t.drawing_type,t.start_date,t.end_date,DATE(t.updated_at) AS upd,t.status,COALESCE(s.name,t.status) AS sname,COALESCE(u.name,'') AS person,COALESCE(pg.id,g.id) AS top_id,COALESCE(pg.name,g.name,'(No group)') AS grp FROM tasks t LEFT JOIN workspaces w ON w.id=t.ws_id LEFT JOIN statuses s ON s.ws_id=t.ws_id AND s.skey=t.status LEFT JOIN users u ON u.id=t.assigned_to LEFT JOIN task_groups g ON g.id=t.group_id LEFT JOIN task_groups pg ON pg.id=g.parent_id WHERE 1=1".$wh." ORDER BY w.sort_no,w.name,t.order_no,t.title")->fetchAll();
  $exg=sr_ids($exg);$ext=sr_ids($ext);$d=['done'=>[],'going'=>[],'soon'=>[],'tree'=>[],'from'=>$mon,'to'=>$sun,'today'=>$today,'next'=>$nx];
  foreach($rows as $r){
    $nm=(string)$r['sname'];$sec='';
    if($r['status']==='done'){
      if(preg_match('/^not[\s_-]*needed$/i',trim($nm)))continue;
      $r['when']=($r['end_date']&&$r['end_date']!=='0000-00-00')?$r['end_date']:$r['upd'];
      if($r['when']>=$mon&&$r['when']<=$sun)$sec='done';
    }elseif($r['status']==='progress'||$r['status']==='review'||preg_match('/progress|ongoing|working|review/i',$nm)){$sec='going';}
    elseif(!preg_match('/hold|cancel/i',$nm)&&$r['start_date']&&$r['start_date']>=$today&&$r['start_date']<=$nx){$sec='soon';}
    if($sec==='')continue;
    $d['tree'][]=['id'=>(int)$r['id'],'ws_id'=>(int)$r['ws_id'],'company'=>$r['company'],'gid'=>sr_gid($r),'grp'=>$r['grp'],'title'=>$r['title'],'order_no'=>$r['order_no'],'sec'=>$sec];
    if(in_array(sr_gid($r),$exg,true)||(!(int)$r['top_id']&&in_array(-(1000+(int)$r['ws_id']),$exg,true))||in_array((int)$r['id'],$ext,true))continue;
    $d[$sec][]=$r;
  }
  usort($d['done'],fn($a,$b)=>strcmp($b['when'],$a['when']));
  usort($d['going'],fn($a,$b)=>strcmp((string)($a['end_date']?:'9999'),(string)($b['end_date']?:'9999')));
  usort($d['soon'],fn($a,$b)=>strcmp($a['start_date'],$b['start_date']));
  return $d;
}
function sr_site(){if(PHP_SAPI==='cli')return 'https://taskflow.gurucadd.com';$h=$_SERVER['HTTP_HOST']??'taskflow.gurucadd.com';$d=rtrim(str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']??'/api.php')),'/');return 'https://'.$h.$d;}
function sr_types($rows){$m=[];foreach($rows as $r){$k=strtoupper(trim((string)$r['drawing_type']));if($k==='')$k='OTHER';$m[$k][]=$r;}
  uksort($m,function($a,$b)use($m){if($a==='OTHER')return 1;if($b==='OTHER')return -1;$c=count($m[$b])<=>count($m[$a]);return $c?:strcmp($a,$b);});return $m;}
function sr_whiten($b64){
  // Turns a transparent logo into a white silhouette so it reads on the dark header in light and dark mode. Returns '' if it can't (no GD, or the logo has no transparency).
  if(!function_exists('imagecreatefromstring'))return '';
  $im=@imagecreatefromstring((string)base64_decode($b64));if(!$im)return '';
  $w=imagesx($im);$h=imagesy($im);if($w<2||$h<2)return '';
  $sc=min(1,160/$h);$nw=max(1,(int)round($w*$sc));$nh=max(1,(int)round($h*$sc));
  $o=imagecreatetruecolor($nw,$nh);imagealphablending($o,false);imagesavealpha($o,true);imagefill($o,0,0,imagecolorallocatealpha($o,255,255,255,127));
  imagecopyresampled($o,$im,0,0,0,0,$nw,$nh,$w,$h);
  $t=0;$n=$nw*$nh;
  for($y=0;$y<$nh;$y++)for($x=0;$x<$nw;$x++){$a=(imagecolorat($o,$x,$y)>>24)&127;if($a>100)$t++;}
  if($t<$n*0.05)return '';
  $wc=[];for($a=0;$a<=127;$a++)$wc[$a]=imagecolorallocatealpha($o,255,255,255,$a);
  for($y=0;$y<$nh;$y++)for($x=0;$x<$nw;$x++){$a=(imagecolorat($o,$x,$y)>>24)&127;imagesetpixel($o,$x,$y,$wc[$a]);}
  ob_start();imagepng($o);$png=ob_get_clean();return $png?base64_encode($png):'';}
function sr_assets($db,$ws,$mode){
  // mode 'cid': images are attached to the email and referenced by cid. mode 'data': inline data URIs (for the on-screen preview).
  $o=['tf'=>'','co'=>[],'names'=>[],'att'=>[],'white'=>false];
  $f=__DIR__.'/icon-192.png';
  if(is_file($f)&&filesize($f)<400000){$b=file_get_contents($f);if($mode==='cid'){$o['tf']='cid:tflogo';$o['att'][]=['tflogo.png','image/png',base64_encode($b)];}else $o['tf']='data:image/png;base64,'.base64_encode($b);}
  $ws=array_values(array_filter(array_map('intval',(array)$ws)));
  $q=$db->query("SELECT id,name,COALESCE(logo,'') AS logo FROM workspaces ".($ws?"WHERE id IN(".implode(',',$ws).") ":"")."ORDER BY sort_no,name");$L=[];
  foreach($q->fetchAll() as $w){$o['names'][]=$w['name'];
    if(preg_match('#^data:(image/(?:png|jpeg));base64,(.+)$#',$w['logo'],$m)&&strlen($m[2])<500000&&count($L)<3)$L[]=[$w['name'],$m[1],$m[2],sr_whiten($m[2])];}
  if(count($o['names'])>3)$L=[];
  $o['white']=$L&&!array_filter($L,fn($l)=>$l[3]==='');
  foreach($L as $i=>$l){$n=$i+1;$mime=$o['white']?'image/png':$l[1];$b64=$o['white']?$l[3]:$l[2];
    if($mode==='cid'){$id='cologo'.$n;$o['co'][]=['cid:'.$id,$l[0]];$o['att'][]=[$id.'.'.($mime==='image/png'?'png':'jpg'),$mime,$b64,$id];}
    else $o['co'][]=['data:'.$mime.';base64,'.$b64,$l[0]];}
  return $o;}
function sr_html($d,$title='Task status report',$as=null){
  $e=fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');$fd=fn($s)=>($s&&$s!=='0000-00-00')?date('D j M',strtotime($s)):'';
  $as=$as?:['tf'=>'','co'=>[],'names'=>[]];$F="font-family:'Segoe UI',Arial,Helvetica,sans-serif;";
  $nd=count($d['done']);$ng=count($d['going']);$ns=count($d['soon']);$tot=$nd+$ng+$ns;
  $pal=['#2563eb','#0891b2','#16a34a','#d97706','#9333ea','#dc2626','#0d9488','#db2777'];
  $av=function($n)use($e,$pal){$n=trim((string)$n);if($n==='')return '<span style="color:#94a3b8">Unassigned</span>';$w=preg_split('/\s+/',$n);$in=strtoupper(mb_substr($w[0],0,1).(isset($w[1])?mb_substr($w[count($w)-1],0,1):''));$c=$pal[crc32($n)%count($pal)];
    return '<table role="presentation" cellspacing="0" cellpadding="0"><tr><td width="22" height="22" bgcolor="'.$c.'" align="center" valign="middle" style="font-family:Arial,sans-serif;width:22px;height:22px;background:'.$c.';color:#ffffff;font-size:10px;font-weight:700;border-radius:11px;text-align:center;white-space:nowrap">'.$e($in).'</td><td width="6">&nbsp;</td><td valign="middle" style="font-family:Arial,sans-serif;font-size:12px;color:#334155;white-space:nowrap">'.$e($n).'</td></tr></table>';};
  $multi=count(array_unique(array_map(fn($r)=>$r['company'],array_merge($d['done'],$d['going'],$d['soon']))))>1;
  $logos='';foreach($as['co'] as $c)$logos.='<img src="'.$e($c[0]).'" alt="'.$e($c[1]).'" height="58" style="height:58px;max-width:230px;display:inline-block;vertical-align:middle;margin-right:14px;border:0">';
  if($logos==='')$logos='<span style="'.$F.'font-size:14px;font-weight:700;color:#0f2a5c">'.$e(implode('  ·  ',array_slice($as['names'],0,3))).(count($as['names'])>3?' +'.(count($as['names'])-3):'').'</span>';
  $tfimg=$as['tf']?'<img src="'.$e($as['tf']).'" alt="TaskFlow" width="34" height="34" style="width:34px;height:34px;border-radius:9px;display:block;border:0">':'';
  $o='<div style="margin:0;padding:0;background:#eef2f7"><div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#eef2f7">'.$nd.' completed, '.$ng.' ongoing, '.$ns.' starting in the next 3 days.</div>';
  $o.='<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef2f7"><tr><td align="center" style="padding:22px 10px">';
  $o.='<table role="presentation" width="680" cellspacing="0" cellpadding="0" style="width:100%;max-width:680px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(15,42,92,.12)">';
  // header
  $o.='<tr><td bgcolor="#0f2a5c" style="background:#0f2a5c;padding:20px 26px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
    .'<td align="left" valign="middle">'.(!empty($as['white'])?'<span style="display:inline-block;line-height:0">'.$logos.'</span>':'<span style="display:inline-block;background:#ffffff;border-radius:10px;padding:8px 12px;line-height:0">'.$logos.'</span>').'</td>'
    .'<td align="right" valign="middle"><table role="presentation" cellspacing="0" cellpadding="0" align="right"><tr><td valign="middle" align="right" style="'.$F.'padding-right:10px"><div style="font-size:16px;font-weight:800;color:#ffffff;letter-spacing:.2px;white-space:nowrap">TaskFlow</div><div style="font-size:8px;letter-spacing:1.4px;color:#93c5fd;font-weight:700;white-space:nowrap">POWERED BY GURUCADD</div></td><td width="34" valign="middle">'.$tfimg.'</td></tr></table></td></tr></table></td></tr>';
  $o.='<tr><td style="font-size:0;line-height:0;height:4px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td width="34%" height="4" bgcolor="#e2e8f0" style="height:4px;line-height:4px;font-size:0">&nbsp;</td><td width="33%" height="4" bgcolor="#38bdf8" style="height:4px;line-height:4px;font-size:0">&nbsp;</td><td width="33%" height="4" bgcolor="#22c55e" style="height:4px;line-height:4px;font-size:0">&nbsp;</td></tr></table></td></tr>';
  // title
  $o.='<tr><td style="'.$F.'padding:24px 26px 6px"><div style="font-size:11px;letter-spacing:1.4px;color:#64748b;font-weight:700">WEEKLY PROGRESS REPORT</div><div style="font-size:25px;font-weight:800;color:#0f172a;margin-top:4px">'.$e($title).'</div><div style="font-size:13px;color:#475569;margin-top:6px">Week of <b>'.$e($fd($d['from'])).'</b> to <b>'.$e($fd($d['to'])).'</b> &nbsp;·&nbsp; prepared '.$e($fd($d['today'])).'</div></td></tr>';
  // KPI
  $bk=function($rows)use($e){$t=[];foreach(sr_types($rows) as $k=>$v)$t[]=$e($k).' <b>'.count($v).'</b>';return $t?implode(' &nbsp;·&nbsp; ',$t):'&nbsp;';};
  $k=function($n,$l,$c,$bg,$rows)use($F,$bk){return '<td width="33%" valign="top" style="padding:0 5px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:'.$bg.';border-radius:10px;border-top:3px solid '.$c.'"><tr><td align="center" style="'.$F.'padding:12px 4px"><div style="font-size:34px;font-weight:800;color:'.$c.';line-height:1">'.$n.'</div><div style="font-size:11px;color:#475569;margin-top:5px;font-weight:600">'.$l.'</div><div style="font-size:10px;color:#64748b;margin-top:4px;line-height:1.4">'.$bk($rows).'</div></td></tr></table></td>';};
  $o.='<tr><td style="padding:12px 21px 4px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'.$k($nd,'Completed this week','#16a34a','#f0fdf4',$d['done']).$k($ng,'Ongoing now','#2563eb','#eff6ff',$d['going']).$k($ns,'Starting in 3 days','#d97706','#fffbeb',$d['soon']).'</tr></table></td></tr>';
  if($tot>0){$p=fn($n)=>max(0,(int)round($n*100/$tot));
    $o.='<tr><td style="padding:10px 26px 2px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius:6px;overflow:hidden"><tr>'.($nd?'<td width="'.$p($nd).'%" height="8" bgcolor="#22c55e" style="height:8px;line-height:8px;font-size:0">&nbsp;</td>':'').($ng?'<td width="'.$p($ng).'%" height="8" bgcolor="#3b82f6" style="height:8px;line-height:8px;font-size:0">&nbsp;</td>':'').($ns?'<td width="'.$p($ns).'%" height="8" bgcolor="#f59e0b" style="height:8px;line-height:8px;font-size:0">&nbsp;</td>':'').'</tr></table></td></tr>';}
  // sections
  $sec=function($name,$sub,$col,$rows,$mode)use($e,$F,$fd,$av,$multi){
    $h='<tr><td style="'.$F.'padding:22px 26px 6px"><table role="presentation" cellspacing="0" cellpadding="0"><tr><td style="'.$F.'font-size:17px;color:'.$col.';padding-right:8px" valign="middle">&#9679;</td><td style="'.$F.'font-size:17px;font-weight:800;color:#0f172a;white-space:nowrap" valign="middle">'.$e($name).'</td><td width="8">&nbsp;</td><td bgcolor="'.$col.'" align="center" valign="middle" style="'.$F.'background:'.$col.';color:#ffffff;font-size:11px;font-weight:700;padding:2px 9px;border-radius:10px;white-space:nowrap">'.count($rows).'</td></tr></table><div style="font-size:12px;color:#64748b;padding-top:2px">'.$e($sub).'</div>'.($rows?'<table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:8px"><tr>'.implode('<td width="6">&nbsp;</td>',array_map(fn($t,$v)=>'<td bgcolor="#f1f5f9" valign="middle" style="'.$F.'background:#f1f5f9;border:1px solid #e2e8f0;color:#0f172a;font-size:11px;font-weight:700;padding:3px 9px;white-space:nowrap">'.$e($t).' <span style="color:'.$col.'">'.count($v).'</span></td>',array_keys(sr_types($rows)),sr_types($rows))).'</tr></table>':'').'</td></tr>';
    if(!$rows)return $h.'<tr><td style="'.$F.'padding:6px 26px 4px;font-size:13px;color:#94a3b8">Nothing to show here right now.</td></tr>';
    $h.='<tr><td style="padding:4px 20px 0"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden">';
    $i=0;foreach(sr_types($rows) as $tn=>$trs){
     $h.='<tr><td colspan="3" bgcolor="#eaf1fb" style="'.$F.'background:#eaf1fb;padding:7px 12px;border-bottom:1px solid #dbe5f3;font-size:11px;font-weight:800;letter-spacing:1px;color:#0f2a5c">'.$e($tn).' <span style="font-weight:600;color:#64748b;letter-spacing:0">&nbsp;'.count($trs).' task'.(count($trs)>1?'s':'').'</span></td></tr>';
     foreach($trs as $r){$bg=$i++%2?'#f8fafc':'#ffffff';
      $td=$d['today']??date('Y-m-d');$pill=function($lab,$dtx,$col,$bgc,$bdc)use($e){return '<div style="font-size:9px;font-weight:800;letter-spacing:1px;color:'.$col.'">'.$lab.'</div><div style="display:inline-block;margin-top:2px;background:'.$bgc.';border:1px solid '.$bdc.';border-radius:8px;padding:3px 8px;font-size:14px;font-weight:900;color:'.$col.';white-space:nowrap">'.$e($dtx).'</div>';};
      if($mode==='done')$dt=$pill('COMPLETED',$fd($r['when']),'#15803d','#f0fdf4','#bbf7d0');
      elseif($mode==='soon')$dt=$pill('STARTS',$fd($r['start_date']),'#b45309','#fffbeb','#fde68a').($r['end_date']&&$r['end_date']!=='0000-00-00'?'<div style="font-size:11px;color:#64748b;margin-top:4px">Due <b style="color:#0f172a">'.$e($fd($r['end_date'])).'</b></div>':'');
      else{$ed=($r['end_date']&&$r['end_date']!=='0000-00-00')?$r['end_date']:'';$dt=$ed?($ed<$td?$pill('OVERDUE',$fd($ed),'#b91c1c','#fef2f2','#fecaca'):$pill('DUE',$fd($ed),'#1d4ed8','#eff6ff','#bfdbfe')):'<span style="color:#94a3b8">No due date</span>';}
      $chip=$mode==='going'?'<table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:4px"><tr><td bgcolor="#dbeafe" style="font-family:Arial,sans-serif;background:#dbeafe;font-size:10px;font-weight:700;color:#1d4ed8;padding:1px 8px;border-radius:8px;white-space:nowrap">'.$e(strtoupper($r['sname'])).'</td></tr></table>':'';
      $sub=array_filter([$r['customer'],$multi?$r['company']:'']);
      $h.='<tr><td bgcolor="'.$bg.'" style="'.$F.'background:'.$bg.';padding:10px 12px;border-bottom:1px solid #eef2f7;font-size:13px;color:#0f172a" valign="top"><div><b style="color:#0f2a5c">'.$e($r['order_no']).'</b>'.($r['order_no']?' &nbsp;':'').$e($r['title']).'</div>'.($sub?'<div style="font-size:11px;color:#64748b;margin-top:2px">'.$e(implode('  ·  ',$sub)).'</div>':'').$chip.'</td>'
        .'<td bgcolor="'.$bg.'" width="150" style="'.$F.'background:'.$bg.';padding:10px 8px;border-bottom:1px solid #eef2f7;font-size:12px;color:#334155" valign="top">'.$av($r['person']).'</td>'
        .'<td bgcolor="'.$bg.'" width="120" align="right" style="'.$F.'background:'.$bg.';padding:10px 12px;border-bottom:1px solid #eef2f7;font-size:12px;color:#334155" valign="top">'.$dt.'</td></tr>';}}
    return $h.'</table></td></tr>';};
  $o.=$sec('Completed this week','Delivered since Monday','#16a34a',$d['done'],'done');
  $o.=$sec('Ongoing','Work in progress and in review right now','#2563eb',$d['going'],'going');
  $o.=$sec('Starting in the next 3 days','Scheduled to kick off by '.$fd($d['next']),'#d97706',$d['soon'],'soon');
  // footer
  $o.='<tr><td style="height:26px;font-size:0;line-height:0">&nbsp;</td></tr><tr><td bgcolor="#0f2a5c" style="background:#0f2a5c;'.$F.'padding:18px 26px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
    .($tfimg?'<td width="40" valign="middle"><img src="'.$e($as['tf']).'" alt="" width="30" height="30" style="width:30px;height:30px;border-radius:8px;display:block;border:0"></td>':'')
    .'<td valign="middle" style="font-size:12px;color:#cbd5e1;line-height:1.5"><b style="color:#ffffff">Sent automatically by TaskFlow</b><br>Live task tracking, hours and reporting for engineering and drafting teams.</td>'
    .'<td align="right" valign="middle"><a href="'.$e(sr_site()).'/login.php?discover=1" style="display:inline-block;background:#38bdf8;color:#0f2a5c;font-weight:800;font-size:12px;text-decoration:none;border-radius:8px;padding:9px 14px;white-space:nowrap">Discover TaskFlow &rarr;</a></td></tr></table></td></tr>';
  $o.='</table><div style="'.$F.'font-size:11px;color:#94a3b8;margin-top:12px">TaskFlow &middot; powered by GURUCADD</div></td></tr></table></div>';
  return $o;
}
function sr_valid_emails($s){$o=[];foreach(preg_split('/[\s,;]+/',(string)$s,-1,PREG_SPLIT_NO_EMPTY) as $m){$m=strtolower(trim($m));if(filter_var($m,FILTER_VALIDATE_EMAIL)&&!in_array($m,$o,true))$o[]=$m;}return array_slice($o,0,30);}
// ---- Microsoft 365 mail (Graph API, app-only). Put taskflow-mail.json in your home folder (or define('MS_MAIL_FILE','/path') in config.php):
// {"tenant_id":"...","client_id":"...","client_secret":"...","from":"taskflow@gurucadd.com"}
function ms_cfg(){
  $c=[];if(defined('MS_MAIL_FILE'))$c[]=MS_MAIL_FILE;$h=getenv('HOME');if($h)$c[]=rtrim($h,'/').'/taskflow-mail.json';$c[]=dirname(__DIR__,2).'/taskflow-mail.json';$c[]=__DIR__.'/../taskflow-mail.json';
  foreach($c as $f)if($f&&is_file($f)){$j=json_decode((string)file_get_contents($f),true);if(is_array($j)&&!empty($j['tenant_id'])&&!empty($j['client_id'])&&!empty($j['client_secret'])&&!empty($j['from']))return $j;}
  return null;}
function ms_http($url,$post,$hdr,&$code){
  $ch=curl_init($url);curl_setopt_array($ch,[CURLOPT_POST=>1,CURLOPT_POSTFIELDS=>$post,CURLOPT_HTTPHEADER=>$hdr,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT=>25,CURLOPT_CONNECTTIMEOUT=>8]);
  $o=(string)curl_exec($ch);$code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE);$er=curl_error($ch);curl_close($ch);return $er!==''?'curl: '.$er:$o;}
function ms_token($c,&$err){
  $guid='/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
  if(!preg_match($guid,trim((string)$c['tenant_id']))){$err='The tenant_id in taskflow-mail.json is not a full ID (it has '.strlen(trim((string)$c['tenant_id'])).' characters, it should be 36 like ab45c236-6119-4912-a6a2-xxxxxxxxxxxx). Copy it again from the app Overview page.';return '';}
  if(!preg_match($guid,trim((string)$c['client_id']))){$err='The client_id in taskflow-mail.json is not a full ID (it has '.strlen(trim((string)$c['client_id'])).' characters, it should be 36).';return '';}
  $cf=sys_get_temp_dir().'/tf_ms_'.md5($c['tenant_id'].$c['client_id']).'.json';
  if(is_file($cf)){$t=json_decode((string)file_get_contents($cf),true);if(is_array($t)&&($t['exp']??0)>time()+120)return $t['tok'];}
  $o=ms_http('https://login.microsoftonline.com/'.rawurlencode(trim($c['tenant_id'])).'/oauth2/v2.0/token',http_build_query(['client_id'=>trim($c['client_id']),'client_secret'=>trim($c['client_secret']),'scope'=>'https://graph.microsoft.com/.default','grant_type'=>'client_credentials']),['Content-Type: application/x-www-form-urlencoded'],$code);
  $r=json_decode($o,true);if(empty($r['access_token'])){$err='Microsoft sign-in failed: '.(is_array($r)?mb_substr((string)($r['error_description']??$o),0,220):'Microsoft did not recognise these details. Check tenant_id, client_id and client_secret.');return '';}
  @file_put_contents($cf,json_encode(['tok'=>$r['access_token'],'exp'=>time()+(int)($r['expires_in']??3000)]));@chmod($cf,0600);return $r['access_token'];}
function ms_send($to,$subject,$html,$att=[]){
  if(!function_exists('curl_init'))return 'PHP curl is not available';
  $c=ms_cfg();if(!$c)return 'Microsoft 365 mail is not set up';
  $tok=ms_token($c,$err);if($tok==='')return $err;
  $o=ms_http('https://graph.microsoft.com/v1.0/users/'.rawurlencode($c['from']).'/sendMail',json_encode(['message'=>['subject'=>$subject,'body'=>['contentType'=>'HTML','content'=>$html],'toRecipients'=>[['emailAddress'=>['address'=>$to]]],'attachments'=>array_map(fn($a)=>['@odata.type'=>'#microsoft.graph.fileAttachment','name'=>$a[0],'contentType'=>$a[1],'contentBytes'=>$a[2],'isInline'=>true,'contentId'=>$a[3]??pathinfo($a[0],PATHINFO_FILENAME)],$att)],'saveToSentItems'=>false]),['Authorization: Bearer '.$tok,'Content-Type: application/json'],$code);
  if($code===202||$code===200)return '';
  $r=json_decode($o,true);return 'Microsoft refused it ('.$code.'): '.mb_substr((string)($r['error']['message']??$o),0,220);}
function sr_send($db,$s){
  $to=sr_valid_emails($s['emails']);if(!$to)return ['ok'=>false,'sent'=>0,'err'=>'No valid email addresses'];
  $ws=array_filter(array_map('intval',explode(',',(string)$s['ws_ids'])));$d=sr_data($db,$ws,$s['excl_g']??'',$s['excl_t']??'');
  $title=($s['name']!==''?$s['name']:'Task status report');$as=sr_assets($db,$ws,'cid');$html='<!doctype html><html><body style="margin:0;padding:0;background:#eef2f7">'.sr_html($d,$title,$as).'</body></html>';$htmlPlain='<!doctype html><html><body style="margin:0;padding:0;background:#eef2f7">'.sr_html($d,$title,['tf'=>'','co'=>[],'names'=>$as['names']]).'</body></html>';
  $txt=$title."\nWeek ".$d['from'].' to '.$d['to']."\n\n";
  foreach([['COMPLETED THIS WEEK','done'],['ONGOING','going'],['STARTING IN THE NEXT 3 DAYS','soon']] as $g){$txt.=$g[0].' ('.count($d[$g[1]]).")\n";foreach($d[$g[1]] as $r)$txt.='- '.trim(($r['order_no']?$r['order_no'].' ':'').$r['title']).($r['customer']?' | '.$r['customer']:'').($r['person']?' | '.$r['person']:'').($g[1]==='soon'?' | starts '.$r['start_date']:'')."\n";$txt.="\n";}
  $host=bk_host();$sent=0;$err='';
  $viaMs=ms_cfg()!==null;
  foreach($to as $m){
    $subj='TaskFlow status report '.date('j M',strtotime($d['from'])).' - '.date('j M',strtotime($d['to']));
    if($viaMs){$e=ms_send($m,$subj,$html,$as['att']);if($e===''){$sent++;continue;}$err=$e;}
    $b='tf'.bin2hex(random_bytes(8));
    $h="From: TaskFlow <backup@$host>\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$b\"\r\n";
    $body="--$b\r\nContent-Type: text/plain; charset=utf-8\r\n\r\n".$txt."\r\nThe full formatted report is attached (open the .html file in a browser).\r\n"
      ."--$b\r\nContent-Type: text/html; name=\"status-report.html\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"status-report.html\"\r\n\r\n".chunk_split(base64_encode($htmlPlain))."\r\n--$b--";
    if(@mail($m,$subj,$body,$h))$sent++;else{$le=error_get_last();$err='The server could not send to '.$m.($le?' ('.$le['message'].')':'');}}
  return ['ok'=>$sent>0,'sent'=>$sent,'err'=>$err];
}
function sr_run($db,$force=false){
  try{sr_ddl($db);$list=$db->query("SELECT * FROM status_reports WHERE active=1")->fetchAll();}catch(Throwable $e){return 'Status reports: not set up';}
  $now=sr_now();$today=$now->format('Y-m-d');$dow=(int)$now->format('N');$min=(int)$now->format('G')*60+(int)$now->format('i');$n=0;$msg=[];
  foreach($list as $s){
    if((int)$s['dow']!==$dow||$min<(int)$s['hhmm']||$min>=(int)$s['hhmm']+180||$s['last_sent']===$today)continue;
    $db->prepare("UPDATE status_reports SET last_sent=? WHERE id=?")->execute([$today,$s['id']]);
    $r=sr_send($db,$s);$n+=$r['sent'];$msg[]=$s['name'].': '.($r['ok']?'sent to '.$r['sent']:'FAILED '.$r['err']);}
  return $msg?'Status reports: '.implode('; ',$msg):'Status reports: none due';
}
function wl_mail($file,$name,$why){
  $to=BACKUP_EMAIL;if(!$to)return 'No email address set';
  $b='tf'.bin2hex(random_bytes(8));
  $h="From: TaskFlow <backup@".bk_host().">\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"$b\"\r\n";
  $m="--$b\r\nContent-Type: text/plain; charset=utf-8\r\n\r\nTaskFlow worklog for the last 15 days, all companies.\n$why\nThe Excel file is attached: sheet 'Worklog' has every entry, sheet 'Totals' has hours per person per company.\r\n";
  $m.="--$b\r\nContent-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; name=\"$name\"\r\nContent-Transfer-Encoding: base64\r\nContent-Disposition: attachment; filename=\"$name\"\r\n\r\n".chunk_split(base64_encode(file_get_contents($file)))."\r\n--$b--";
  return @mail($to,'TaskFlow worklog '.substr($name,8,16),$m,$h)?'':'The server could not send the email';
}
function wl_run($db,$mail=true){
  $dir=bk_dir();$res=['ok'=>false,'time'=>time(),'file'=>'','size'=>0,'rows'=>0,'hours'=>0,'emailed'=>false,'err'=>''];
  try{
    if($dir==='')throw new Exception('No writable folder for backups');
    if(!class_exists('ZipArchive'))throw new Exception('This server has no zip support (ZipArchive)');
    $name='worklog-'.date('Y-m-d_H-i').'.xlsx';$path=$dir.'/'.$name;
    $i=wl_build($db,$path);@chmod($path,0600);
    $res['ok']=true;$res['file']=$name;$res['size']=filesize($path);$res['rows']=$i['rows'];$res['hours']=$i['hours'];
    if($mail){$e=wl_mail($path,$name,$i['rows'].' entries, '.round($i['hours'],2).' hours.');$res['emailed']=($e==='');if($e!=='')$res['err']='Saved, but email failed: '.$e;}
    foreach(array_slice(wl_files($dir),30) as $f)@unlink($dir.'/'.$f['name']);
  }catch(Throwable $e){$res['err']=$e->getMessage();}
  if($dir!=='')@file_put_contents($dir.'/worklog_last.json',json_encode($res));
  aud_log($db,0,'system','activity','worklog',$res['ok']?('Worklog '.$res['file'].': '.$res['rows'].' entries'.($res['emailed']?', emailed':'').($res['err']?' - '.$res['err']:'')):'Worklog FAILED: '.$res['err']);
  return $res;
}
const MSG_FILE_HOURS=48;
// Chat attachments are removed after 48 hours to save disk space (the message text stays; the file becomes "expired").
function msg_purge($db,$force=false){
  try{
    $lk=sys_get_temp_dir().'/tf_msgpurge';if(!$force&&is_file($lk)&&time()-filemtime($lk)<600)return 0;@touch($lk);
    if(!$db->query("SHOW TABLES LIKE 'msg_files'")->fetch())return 0;
    if(!$db->query("SHOW COLUMNS FROM msg_files LIKE 'gone'")->fetch())$db->exec("ALTER TABLE msg_files ADD gone TINYINT NOT NULL DEFAULT 0");
    $n=$db->exec("UPDATE msg_files f JOIN messages m ON m.id=f.msg_id SET f.data='',f.gone=1 WHERE f.gone=0 AND m.created_at<DATE_SUB(NOW(),INTERVAL ".MSG_FILE_HOURS." HOUR)");
    if($n>0){$ol=sys_get_temp_dir().'/tf_msgopt';if(!is_file($ol)||time()-filemtime($ol)>86400){@touch($ol);try{$db->exec("OPTIMIZE TABLE msg_files");}catch(Throwable $e){}}}
    return (int)$n;
  }catch(Throwable $e){return 0;}
}
function msg_dlv_mark($db,$u){try{$db->prepare("INSERT INTO msg_dlv(user_id,last_id) SELECT ?,COALESCE(MAX(id),0) FROM messages ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$u]);}catch(Throwable $e){}}
function msg_reacts($db,$ids,$uid){
  $ids=array_values(array_filter(array_map('intval',$ids)));if(!$ids)return [];
  $by=[];$q=$db->query("SELECT r.msg_id,r.emoji,r.user_id,u.name FROM msg_reacts r LEFT JOIN users u ON u.id=r.user_id WHERE r.msg_id IN(".implode(',',$ids).") ORDER BY r.created_at");
  foreach($q->fetchAll() as $r){$m=(int)$r['msg_id'];$e=$r['emoji'];if(!isset($by[$m][$e]))$by[$m][$e]=['e'=>$e,'n'=>0,'mine'=>false,'names'=>[]];
    $by[$m][$e]['n']++;if((int)$r['user_id']===$uid)$by[$m][$e]['mine']=true;if(count($by[$m][$e]['names'])<8)$by[$m][$e]['names'][]=(string)$r['name'];}
  $o=[];foreach($by as $m=>$l)$o[$m]=array_values($l);return $o;}
function out($d){
  global $a,$db,$WRITEA,$uid,$me,$W,$in,$AUD_T;
  if(isset($a,$db)&&in_array($a,$WRITEA,true)&&empty($d['error'])){try{$db->exec("INSERT INTO meta(k,v) VALUES('ver',1) ON DUPLICATE KEY UPDATE v=v+1");}catch(Throwable $e){}}
  if(isset($a,$db,$me)&&in_array($a,$WRITEA,true)&&empty($d['error'])){aud_log($db,$uid,(string)($me['username']??$me['name']),'activity',$a,aud_detail(),$W?:null,(int)($in['id']??$in['task_id']??0)?:null);}
  echo json_encode($d);exit;
}
if(($_GET['a']??'')==='remind'){header('Content-Type: text/plain');
  if(!hash_equals(rem_key(),(string)($_GET['key']??''))){http_response_code(403);exit("bad key\n");}
  echo rem_run($db,'',false)."\n".sr_run($db)."\nChat files expired: ".msg_purge($db,true)."\n";exit;}
if(empty($_SESSION['uid'])){http_response_code(401);out(['error'=>'auth']);}
if(($_GET['a']??'')==='wiki_img'){
  try{$q=$db->prepare("SELECT mime,data FROM wiki_media WHERE id=?");$q->execute([(int)($_GET['id']??0)]);$r=$q->fetch();}catch(Throwable $e){$r=false;}
  if(!$r){http_response_code(404);exit;}
  header('Content-Type: '.$r['mime']);header('Cache-Control: private, max-age=604800');header('X-Content-Type-Options: nosniff');echo $r['data'];exit;
}
if(($_GET['a']??'')==='photo'){
  try{$q=$db->prepare("SELECT mime,data FROM user_photos WHERE user_id=?");$q->execute([(int)($_GET['id']??0)]);$r=$q->fetch();}catch(Throwable $e){$r=false;}
  if(!$r){http_response_code(404);exit;}
  header('Content-Type: '.$r['mime']);header('X-Content-Type-Options: nosniff');header('Cache-Control: private, max-age=2592000');echo $r['data'];exit;
}
if(($_GET['a']??'')==='msg_file'){
  $u0=(int)$_SESSION['uid'];
  try{$gq=$db->prepare("SELECT role FROM users WHERE id=?");$gq->execute([$u0]);if($gq->fetchColumn()==='guest'){http_response_code(403);exit;}}catch(Throwable $e){}
  try{$q=$db->prepare("SELECT f.name,f.mime,f.data,f.gone FROM msg_files f JOIN messages m ON m.id=f.msg_id WHERE f.id=? AND (m.to_id=0 OR m.from_id=? OR m.to_id=?)");$q->execute([(int)($_GET['id']??0),$u0,$u0]);$r=$q->fetch();}catch(Throwable $e){$r=false;}
  if(!$r||!empty($r['gone'])){http_response_code($r?410:404);exit;}
  $img=in_array($r['mime'],['image/png','image/jpeg','image/gif','image/webp'],true);
  header('Content-Type: '.($img?$r['mime']:'application/octet-stream'));header('X-Content-Type-Options: nosniff');header('Cache-Control: private, max-age=604800');
  header('Content-Disposition: '.($img&&empty($_GET['dl'])?'inline':'attachment').'; filename="'.preg_replace('/[^A-Za-z0-9._ -]/','_',$r['name']).'"');echo $r['data'];exit;
}
if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='fetch'){http_response_code(400);out(['error'=>'bad request']);}
$in=json_decode(file_get_contents('php://input'),true)?:[];
$a=$_GET['a']??'';$uid=(int)$_SESSION['uid'];
$q=$db->prepare("SELECT id,name,username,role FROM users WHERE id=?");$q->execute([$uid]);$me=$q->fetch();
if(!$me){session_destroy();http_response_code(401);out(['error'=>'auth']);}
if($me['role']==='guest'&&preg_match('/^(msg_|chat_)/',$a))out(['error'=>'Chat is not available for guest accounts. You can add messages on tasks.']);
const WORK_H=8.0;
// Overtime is automatic: all of Saturday/Sunday, and anything beyond 8 hours on a weekday.
function ot_rebalance($db,$who,$d){
  try{
    $wk=(int)date('N',strtotime($d))>=6;$cum=0.0;
    $q=$db->prepare("SELECT id,task_id,ws_id,user_id,logged_by,hours,kind,note,rev,work_date FROM time_logs WHERE user_id=? AND work_date=? ORDER BY id");$q->execute([$who,$d]);
    foreach($q->fetchAll() as $r){$h=(float)$r['hours'];$k='R';$sp=0.0;
      if($wk)$k='OT';
      elseif($cum+$h<=WORK_H+0.001)$k='R';
      elseif($cum>=WORK_H-0.001)$k='OT';
      else{$k='R';$sp=round($cum+$h-WORK_H,2);$h=round(WORK_H-$cum,2);}
      if($sp>0){
        $db->prepare("UPDATE time_logs SET hours=?,kind='R' WHERE id=?")->execute([$h,$r['id']]);
        $db->prepare("INSERT INTO time_logs(task_id,ws_id,user_id,logged_by,work_date,hours,kind,note,rev,created_at) VALUES(?,?,?,?,?,?,'OT',?,?,NOW())")->execute([$r['task_id'],$r['ws_id'],$r['user_id'],$r['logged_by'],$r['work_date'],$sp,$r['note'],$r['rev']]);
        $cum=WORK_H;
      }else{
        if($k!==$r['kind'])$db->prepare("UPDATE time_logs SET kind=? WHERE id=?")->execute([$k,$r['id']]);
        $cum+=$h;
      }
    }
  }catch(Throwable $e){}
}
function col_peers($db,$w){$q=$db->prepare("SELECT col_grp FROM workspaces WHERE id=?");$q->execute([$w]);$g=(int)$q->fetchColumn();if($g<=0)return [];
  $q=$db->prepare("SELECT id FROM workspaces WHERE col_grp=? AND id<>?");$q->execute([$g,$w]);return array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));}
function col_put($db,$w,$name,$type,$opts){$q=$db->prepare("SELECT id FROM custom_cols WHERE ws_id=? AND LOWER(name)=LOWER(?)");$q->execute([$w,$name]);if($q->fetchColumn())return;
  $q=$db->prepare("SELECT COUNT(*) FROM custom_cols WHERE ws_id=?");$q->execute([$w]);if((int)$q->fetchColumn()>=30)return;
  $db->prepare("INSERT INTO custom_cols(name,ctype,options,sort_no,ws_id) VALUES(?,?,?,?,?)")->execute([$name,$type,$opts,(int)$db->query("SELECT COALESCE(MAX(sort_no),0)+1 FROM custom_cols")->fetchColumn(),$w]);}
function wiki_seed_dir(){
  $repo='';$f=__DIR__.'/deploy_config.php';
  if(is_file($f)){try{$c=include $f;if(is_array($c)&&!empty($c['repo']))$repo=$c['repo'];}catch(Throwable $e){}}
  if($repo==='')$repo=dirname(__DIR__,2).'/repositories/taskflow';
  foreach([$repo.'/wiki_seed',__DIR__.'/wiki_seed'] as $d)if(is_file($d.'/pages.json'))return $d;
  return '';
}
function wiki_tags($t){$o=[];$seen=[];foreach(preg_split('/[,;\n]+/',(string)$t) as $x){$x=mb_substr(trim(ltrim(trim($x),'#')),0,30);if($x===''||isset($seen[mb_strtolower($x)]))continue;$seen[mb_strtolower($x)]=1;$o[]=$x;if(count($o)>=14)break;}return implode(', ',$o);}
function wiki_seed_run($db,$uid){
  $d=wiki_seed_dir();if($d==='')return ['n'=>0,'err'=>'No starter pages found'];
  $j=json_decode((string)file_get_contents($d.'/pages.json'),true);if(!is_array($j)||empty($j['pages']))return ['n'=>0,'err'=>'Starter file is not valid'];
  $src=mb_substr((string)($j['src']??'seed'),0,40);$map=[];$n=0;
  foreach($j['pages'] as $p){
    $t=mb_substr(trim((string)($p['title']??'')),0,150);if($t==='')continue;
    $q=$db->prepare("SELECT id FROM wiki_pages WHERE title=? AND src=?");$q->execute([$t,$src]);if($q->fetchColumn())continue;
    $body=(string)($p['body']??'');
    $body=preg_replace_callback('/\{\{img:([^}]+)\}\}/',function($m)use($db,$d,&$map){
      $nm=trim($m[1]);if(isset($map[$nm]))return 'wimg:'.$map[$nm];
      $fp=$d.'/img/'.basename($nm);if(!is_file($fp))return '';
      $raw=file_get_contents($fp);$inf=@getimagesizefromstring($raw);if(!$inf)return '';
      $db->prepare("INSERT INTO wiki_media(name,mime,data,created_at) VALUES(?,?,?,NOW())")->execute([mb_substr($nm,0,120),$inf['mime'],$raw]);
      $map[$nm]=(int)$db->lastInsertId();return 'wimg:'.$map[$nm];},$body);
    $db->prepare("INSERT INTO wiki_pages(title,body,cat,tags,src,updated_by,updated_at) VALUES(?,?,?,?,?,?,NOW())")->execute([$t,mb_substr($body,0,60000),mb_substr((string)($p['cat']??''),0,80),wiki_tags(is_array($p['tags']??null)?implode(',',$p['tags']):(string)($p['tags']??'')),$src,$uid]);$n++;
  }
  return ['n'=>$n,'err'=>''];
}
function ensure_schema($db){
  if(!empty($_SESSION['schema_v41']))return;
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
  if(!$db->query("SHOW COLUMNS FROM users LIKE 'email'")->fetch())$db->exec("ALTER TABLE users ADD email VARCHAR(190) NOT NULL DEFAULT ''");
  try{$ix=[];foreach($db->query("SHOW INDEX FROM users")->fetchAll() as $r)if($r['Column_name']==='email'&&(int)$r['Non_unique']===0&&$r['Key_name']!=='PRIMARY')$ix[$r['Key_name']]=1;
    foreach(array_keys($ix) as $k)$db->exec("ALTER TABLE users DROP INDEX `".str_replace('`','``',$k)."`");}catch(Throwable $e){}
  if(!$db->query("SHOW COLUMNS FROM time_logs LIKE 'rev'")->fetch())$db->exec("ALTER TABLE time_logs ADD rev VARCHAR(60) NOT NULL DEFAULT ''");
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
  sr_ddl($db);
  $db->exec("CREATE TABLE IF NOT EXISTS invoices(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,slot INT NOT NULL DEFAULT 0,inv_no VARCHAR(80) NOT NULL,inv_date DATE NOT NULL,from_date DATE NOT NULL,to_date DATE NOT NULL,client VARCHAR(200) NOT NULL DEFAULT '',currency VARCHAR(8) NOT NULL DEFAULT '',amount DECIMAL(14,2) NOT NULL DEFAULT 0,hours DECIMAL(10,2) NOT NULL DEFAULT 0,status VARCHAR(10) NOT NULL DEFAULT 'pending',paid_date DATE NULL,html MEDIUMTEXT NULL,approved_by INT NULL,created_at DATETIME NOT NULL,UNIQUE KEY(ws_id,inv_no),KEY(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM task_groups LIKE 'color'")->fetch())$db->exec("ALTER TABLE task_groups ADD color VARCHAR(7) NULL");
  if(!$db->query("SHOW COLUMNS FROM invoices LIKE 'seq'")->fetch())$db->exec("ALTER TABLE invoices ADD seq VARCHAR(12) NOT NULL DEFAULT '', ADD amend_note VARCHAR(500) NULL");
  if($db->query("SHOW INDEX FROM invoices WHERE Key_name='ws_id'")->fetch())$db->exec("ALTER TABLE invoices DROP INDEX ws_id");
  if(!$db->query("SHOW COLUMNS FROM drawing_types LIKE 'color'")->fetch())$db->exec("ALTER TABLE drawing_types ADD color VARCHAR(7) NULL");
  $db->exec("CREATE TABLE IF NOT EXISTS messages(id INT AUTO_INCREMENT PRIMARY KEY,from_id INT NOT NULL,to_id INT NOT NULL DEFAULT 0,body TEXT NOT NULL,created_at DATETIME NOT NULL,KEY(to_id,id),KEY(from_id,to_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS msg_files(id INT AUTO_INCREMENT PRIMARY KEY,msg_id INT NOT NULL,name VARCHAR(160) NOT NULL DEFAULT '',mime VARCHAR(80) NOT NULL DEFAULT '',size INT NOT NULL DEFAULT 0,data MEDIUMBLOB NOT NULL,KEY(msg_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM msg_files LIKE 'gone'")->fetch())$db->exec("ALTER TABLE msg_files ADD gone TINYINT NOT NULL DEFAULT 0");
  $db->exec("CREATE TABLE IF NOT EXISTS msg_reacts(msg_id INT NOT NULL,user_id INT NOT NULL,emoji VARCHAR(16) NOT NULL,created_at DATETIME NOT NULL,PRIMARY KEY(msg_id,user_id,emoji),KEY(msg_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS msg_reads(user_id INT NOT NULL,peer_id INT NOT NULL,last_id INT NOT NULL DEFAULT 0,PRIMARY KEY(user_id,peer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  try{$c=$db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();if($c&&stripos($c['Type'],'enum')!==false)$db->exec("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'member'");}catch(Throwable $e){}
  $db->exec("CREATE TABLE IF NOT EXISTS delete_requests(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,kind VARCHAR(12) NOT NULL,target_id INT NOT NULL,target_name VARCHAR(255) NOT NULL DEFAULT '',requested_by INT NOT NULL,status VARCHAR(10) NOT NULL DEFAULT 'pending',decided_by INT NULL,created_at DATETIME NOT NULL,decided_at DATETIME NULL,KEY st(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS task_assignees(task_id INT NOT NULL,user_id INT NOT NULL,PRIMARY KEY(task_id,user_id),KEY u(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS presence(user_id INT NOT NULL PRIMARY KEY,seen DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM workspaces LIKE 'col_grp'")->fetch())$db->exec("ALTER TABLE workspaces ADD col_grp INT NOT NULL DEFAULT 0");
  foreach(["cat VARCHAR(80) NOT NULL DEFAULT ''","tags VARCHAR(400) NOT NULL DEFAULT ''","src VARCHAR(40) NOT NULL DEFAULT ''"] as $cd){$cn=strtok($cd,' ');if(!$db->query("SHOW COLUMNS FROM wiki_pages LIKE '$cn'")->fetch())$db->exec("ALTER TABLE wiki_pages ADD $cd");}
  $db->exec("CREATE TABLE IF NOT EXISTS wiki_media(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL DEFAULT '',mime VARCHAR(40) NOT NULL,data MEDIUMBLOB NOT NULL,created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  foreach(["edited_at DATETIME NULL","deleted TINYINT NOT NULL DEFAULT 0"] as $cd){$cn=strtok($cd,' ');if(!$db->query("SHOW COLUMNS FROM messages LIKE '$cn'")->fetch())$db->exec("ALTER TABLE messages ADD $cd");}
  if(!$db->query("SHOW COLUMNS FROM messages LIKE 'html'")->fetch())$db->exec("ALTER TABLE messages ADD html MEDIUMTEXT NULL");
  $db->exec("CREATE TABLE IF NOT EXISTS msg_dlv(user_id INT NOT NULL PRIMARY KEY,last_id INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS user_photos(user_id INT NOT NULL PRIMARY KEY,mime VARCHAR(30) NOT NULL,data MEDIUMBLOB NOT NULL,updated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  notices_ddl($db);
  photo_seed($db);photo_seed2($db);photo_seed3($db);
  $_SESSION['schema_v41']=1;
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
  if($f==='status'){global $db;$k=status_keys($db);$v=in_array($v,$k,true)?$v:$k[0];
    foreach(get_statuses($db) as $s)if($s['k']===$v&&preg_match('/^not[\s_-]*needed$/i',trim($s['name'])))return 'done';
    return $v;}
  $lim=['title'=>255,'customer'=>255,'order_no'=>100,'drawing_type'=>100,'notes'=>5000][$f];
  return mb_substr((string)$v,0,$lim);
}
function notify_assign($db,$tid,$new,$old,$pre="📌 New task assigned to you: "){global $uid,$W,$WS;$new=(int)$new;if(!$new||$new===(int)$old||$new===$uid)return;
  try{$q=$db->prepare("SELECT title,order_no FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);$t=$q->fetch();if(!$t)return;
  $cn='';foreach($WS as $w)if((int)$w['id']===(int)$W)$cn=$w['name'];
  $b=$pre.$t['title'].($t['order_no']!==''?" (Order ".$t['order_no'].")":"").($cn!==''?" — ".$cn:"");
  notify($db,$new,'assign',$pre==="📌 New task assigned to you: "?"New task for you":trim($pre," :📌"),$t['title'].($t['order_no']!==''?" (Order ".$t['order_no'].")":"").($cn!==''?" — ".$cn:""),['act'=>'task','ws'=>$W,'task'=>(int)$tid]);}catch(Throwable $e){}}
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
      notify($db,(int)$ad,'approval',"Delete request to review",$b,['dkey'=>'dr'.$kind.$id.$W.date('YmdHi')]);}
  }
  out(['ok'=>1,'pending'=>1]);}
if(in_array($a,$TASKA,true)&&$W){$ids=[];foreach(['id','task_id'] as $k)if(!empty($in[$k]))$ids[]=(int)$in[$k];if(!empty($in['ids'])&&is_array($in['ids']))foreach(array_slice($in['ids'],0,5) as $x)$ids[]=(int)$x;
  if($ids){$ids=array_slice(array_unique($ids),0,5);$q=$db->prepare("SELECT title FROM tasks WHERE ws_id=? AND id IN(".implode(',',array_fill(0,count($ids),'?')).")");$q->execute(array_merge([$W],$ids));$AUD_T=mb_substr(implode(' | ',$q->fetchAll(PDO::FETCH_COLUMN)),0,150);}}
if($a==='list'&&$W&&empty($_SESSION['aud_ws'][$W])){$_SESSION['aud_ws'][$W]=1;$wn='';foreach($WS as $w)if((int)$w['id']===$W)$wn=$w['name'];aud_log($db,$uid,(string)$me['username'],'activity','open_company',$wn,$W);}
switch($a){
case 'notif_poll':
  $u=(int)$me['id'];$cm=isset($in['cm'])?(int)$in['cm']:null;$ic=isset($in['ic'])?(int)$in['ic']:null;$items=[];
  $mx=(int)$db->query("SELECT COALESCE(MAX(id),0) FROM messages WHERE from_id<>$u AND (to_id=$u OR to_id=0)")->fetchColumn();
  if($cm!==null&&$mx>$cm){$q=$db->prepare("SELECT m.id,m.to_id,m.body,COALESCE(u.name,'Someone') AS name FROM messages m LEFT JOIN users u ON u.id=m.from_id WHERE m.id>? AND m.from_id<>? AND (m.to_id=? OR m.to_id=0) ORDER BY m.id LIMIT 5");$q->execute([$cm,$u,$u]);
    foreach($q->fetchAll() as $r)$items[]=['k'=>'chat','id'=>(int)$r['id'],'from'=>$r['name'],'group'=>(int)$r['to_id']===0,'body'=>mb_substr((string)$r['body'],0,140)];}
  $ids=ws_ids();$ix=0;
  if($ids){$in_=implode(',',$ids);$ix=(int)$db->query("SELECT COALESCE(MAX(c.id),0) FROM task_comments c WHERE c.to_user_id=$u AND c.user_id<>$u AND c.ws_id IN($in_)")->fetchColumn();
    if($ic!==null&&$ix>$ic){$q=$db->prepare("SELECT c.id,c.task_id,c.ws_id,c.kind,c.body,t.title,COALESCE(us.name,'Someone') AS name FROM task_comments c JOIN tasks t ON t.id=c.task_id LEFT JOIN users us ON us.id=c.user_id WHERE c.id>? AND c.to_user_id=? AND c.user_id<>? AND c.ws_id IN($in_) ORDER BY c.id LIMIT 5");$q->execute([$ic,$u,$u]);
      foreach($q->fetchAll() as $r)$items[]=['k'=>'comment','id'=>(int)$r['id'],'from'=>$r['name'],'task'=>(int)$r['task_id'],'ws'=>(int)$r['ws_id'],'title'=>$r['title'],'kind'=>$r['kind'],'body'=>mb_substr((string)$r['body'],0,140)];}}
  out(['cm'=>max($mx,(int)$cm),'ic'=>max($ix,(int)$ic),'items'=>$items]);
case 'bk_list':
  admin();$d=bk_dir();$last=($d&&is_file($d.'/last.json'))?json_decode((string)file_get_contents($d.'/last.json'),true):null;
  $wl=($d&&is_file($d.'/worklog_last.json'))?json_decode((string)file_get_contents($d.'/worklog_last.json'),true):null;
  out(['files'=>$d?bk_files($d):[],'last'=>$last,'wfiles'=>$d?wl_files($d):[],'wlast'=>$wl,'email'=>BACKUP_EMAIL,'dir_ok'=>$d!=='','remcmd'=>'*/30 * * * * curl -s "'.rem_url().'" >/dev/null 2>&1']);
case 'rem_test':
  admin();$sl=($in['slot']??'am')==='pm'?'pm':'am';out(['res'=>rem_run($db,$sl,true)]);
case 'push_reg':
  $t=trim((string)($in['token']??''));if($t===''||strlen($t)>400)out(['error'=>'bad token']);
  $db->exec("CREATE TABLE IF NOT EXISTS push_tokens(token VARCHAR(400) NOT NULL,user_id INT NOT NULL,platform VARCHAR(12) NOT NULL DEFAULT 'android',updated_at DATETIME NOT NULL,PRIMARY KEY(token(190)),KEY(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM push_tokens LIKE 'app'")->fetch())$db->exec("ALTER TABLE push_tokens ADD app VARCHAR(12) NOT NULL DEFAULT ''");
  $db->prepare("INSERT INTO push_tokens(token,user_id,platform,updated_at,app) VALUES(?,?,?,NOW(),?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),updated_at=NOW(),app=VALUES(app)")->execute([$t,$uid,'android',(($in['app']??'')==='chat')?'chat':'']);out(['ok'=>1]);
case 'push_test':
  $db->exec("CREATE TABLE IF NOT EXISTS push_tokens(token VARCHAR(400) NOT NULL,user_id INT NOT NULL,platform VARCHAR(12) NOT NULL DEFAULT 'android',updated_at DATETIME NOT NULL,PRIMARY KEY(token(190)),KEY(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $n=(int)$db->query("SELECT COUNT(*) FROM push_tokens WHERE user_id=".(int)$uid)->fetchColumn();
  if(!push_key())out(['error'=>'Server has no Firebase key file yet (taskflow-fcm.json)']);
  if(!$n)out(['error'=>'No phone registered for you yet. Open the Android app once and allow notifications.']);
  push_q($uid,'TaskFlow test','Phone notifications are working \u2705');out(['ok'=>1,'phones'=>$n]);
case 'wl_run':
  admin();$r=wl_run($db,!empty($in['mail']));out(['res'=>$r]);
case 'bk_run':
  admin();$r=bk_run($db,!empty($in['mail']));out(['res'=>$r]);
case 'bk_get':
  admin();$f=(string)($_GET['f']??'');$d=bk_dir();
  if(!preg_match('/^(taskflow-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.sql\.gz|worklog-\d{4}-\d{2}-\d{2}_\d{2}-\d{2}\.xlsx)$/',$f)||!$d||!is_file($d.'/'.$f))out(['error'=>'Backup not found']);
  header('Content-Type: '.(substr($f,-5)==='.xlsx'?'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':'application/gzip'));header('Content-Disposition: attachment; filename="'.$f.'"');header('Content-Length: '.filesize($d.'/'.$f));readfile($d.'/'.$f);exit;
case 'log_list':
  admin();aud_table($db);$w=[];$p=[];
  $k=(string)($in['kind']??'');if($k==='login'){$w[]="l.kind IN('login','fail')";}elseif($k==='activity'){$w[]="l.kind='activity'";}
  if(!empty($in['user']))$w[]="l.user_id=".(int)$in['user'];
  if(!empty($in['ws']))$w[]="l.ws_id=".(int)$in['ws'];
  if(preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)($in['from']??''))){$w[]="l.created_at>=?";$p[]=$in['from'].' 00:00:00';}
  if(preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)($in['to']??''))){$w[]="l.created_at<=?";$p[]=$in['to'].' 23:59:59';}
  $lim=min(500,max(20,(int)($in['limit']??200)));
  $q=$db->prepare("SELECT l.id,l.created_at,l.user_id,COALESCE(u.name,l.username) AS name,l.username,l.kind,l.action,l.detail,l.ws_id,w.name AS ws,l.ip,l.ua FROM audit_log l LEFT JOIN users u ON u.id=l.user_id LEFT JOIN workspaces w ON w.id=l.ws_id".($w?" WHERE ".implode(' AND ',$w):"")." ORDER BY l.id DESC LIMIT $lim");$q->execute($p);
  $rows=$q->fetchAll();
  $seen=$db->query("SELECT u.id,u.name,u.role,(SELECT MAX(created_at) FROM audit_log a WHERE a.user_id=u.id AND a.kind='login') AS last_login,(SELECT MAX(created_at) FROM audit_log a WHERE a.user_id=u.id) AS last_seen FROM users u ORDER BY u.name")->fetchAll();
  $st=$db->query("SELECT COUNT(DISTINCT user_id) FROM audit_log WHERE created_at>=CURDATE() AND user_id IS NOT NULL")->fetchColumn();
  $fl=$db->query("SELECT COUNT(*) FROM audit_log WHERE kind='fail' AND created_at>=DATE_SUB(NOW(),INTERVAL 1 DAY)")->fetchColumn();
  $wl=$db->query("SELECT id,name FROM workspaces ORDER BY name")->fetchAll();
  out(['rows'=>$rows,'seen'=>$seen,'today'=>(int)$st,'fails'=>(int)$fl,'ws'=>$wl]);
case 'co_set':
  need_level('edit');$tid=(int)($in['id']??0);$us=array_values(array_unique(array_map('intval',is_array($in['users']??null)?$in['users']:[])));
  $q=$db->prepare("SELECT assigned_to FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);$tr=$q->fetch();if(!$tr)out(['error'=>'Task not found']);
  $ok=[];$acc=$db->prepare("SELECT COUNT(*) FROM users u WHERE u.id=? AND u.role<>'guest' AND (u.role='admin' OR u.id IN(SELECT user_id FROM ws_users WHERE ws_id=?))");
  foreach($us as $x){if($x<=0||$x===(int)$tr['assigned_to'])continue;$acc->execute([$x,$W]);if((int)$acc->fetchColumn())$ok[]=$x;}
  $q=$db->prepare("SELECT user_id FROM task_assignees WHERE task_id=?");$q->execute([$tid]);$old=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
  $db->prepare("DELETE FROM task_assignees WHERE task_id=?")->execute([$tid]);
  foreach($ok as $x)$db->prepare("INSERT INTO task_assignees(task_id,user_id) VALUES(?,?)")->execute([$tid,$x]);
  $db->prepare("UPDATE tasks SET updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$tid,$W]);
  foreach($ok as $x)if(!in_array($x,$old,true))notify_assign($db,$tid,$x,0,"📌 You were added to a task: ");
  out(['ok'=>1,'co'=>$ok]);
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
  notify($db,(int)$r['requested_by'],'approval',$ap?"Delete request approved":"Delete request not approved",ucfirst($kn).' "'.$r['target_name'].'"');
  out(['ok'=>1]);
case 'ping':
  $q=$db->prepare("SELECT COUNT(*) FROM messages m LEFT JOIN msg_reads r ON r.user_id=? AND r.peer_id=IF(m.to_id=0,0,m.from_id) WHERE m.from_id<>? AND (m.to_id=? OR m.to_id=0) AND m.id>COALESCE(r.last_id,0)");$q->execute([$uid,$uid,$uid]);
  $msgn=(int)$q->fetchColumn();$on=[];
  try{
    $db->prepare("INSERT INTO presence(user_id,seen) VALUES(?,NOW()) ON DUPLICATE KEY UPDATE seen=NOW()")->execute([$uid]);
    if($W){$q=$db->prepare("SELECT u.id,u.name FROM presence p JOIN users u ON u.id=p.user_id WHERE p.seen>=DATE_SUB(NOW(),INTERVAL 25 SECOND) AND (u.role='admin' OR u.id IN(SELECT user_id FROM ws_users WHERE ws_id=?)) ORDER BY u.name");$q->execute([$W]);}
    else $q=$db->query("SELECT u.id,u.name FROM presence p JOIN users u ON u.id=p.user_id WHERE p.seen>=DATE_SUB(NOW(),INTERVAL 25 SECOND) ORDER BY u.name");
    foreach($q->fetchAll() as $r)$on[]=['id'=>(int)$r['id'],'name'=>$r['name']];
  }catch(Throwable $e){}
  out(['v'=>(int)$db->query("SELECT v FROM meta WHERE k='ver'")->fetchColumn(),'build'=>BUILD,'msgn'=>$msgn,'on'=>$on]);
case 'msg_users':
  msg_purge($db);msg_dlv_mark($db,$uid);
  $onl=[];try{$db->prepare("INSERT INTO presence(user_id,seen) VALUES(?,NOW()) ON DUPLICATE KEY UPDATE seen=NOW()")->execute([$uid]);foreach($db->query("SELECT user_id FROM presence WHERE seen>=DATE_SUB(NOW(),INTERVAL 40 SECOND)")->fetchAll(PDO::FETCH_COLUMN) as $x)$onl[(int)$x]=1;}catch(Throwable $e){}
  $q=$db->prepare("SELECT IF(m.to_id=0,0,m.from_id) AS peer,COUNT(*) AS n FROM messages m LEFT JOIN msg_reads r ON r.user_id=? AND r.peer_id=IF(m.to_id=0,0,m.from_id) WHERE m.from_id<>? AND (m.to_id=? OR m.to_id=0) AND m.id>COALESCE(r.last_id,0) GROUP BY peer");$q->execute([$uid,$uid,$uid]);$un=[];foreach($q->fetchAll() as $r)$un[(int)$r['peer']]=(int)$r['n'];
  $q=$db->prepare("SELECT IF(from_id=?,to_id,from_id) AS peer,MAX(id) AS last FROM messages WHERE to_id<>0 AND (from_id=? OR to_id=?) GROUP BY peer");$q->execute([$uid,$uid,$uid]);$la=[];foreach($q->fetchAll() as $r)$la[(int)$r['peer']]=(int)$r['last'];
  $q=$db->prepare("SELECT MAX(id) FROM messages WHERE to_id=0");$q->execute();$la[0]=(int)$q->fetchColumn();
  $pv=[];$q=$db->prepare("SELECT m.id,m.from_id,m.to_id,m.body,m.created_at,m.deleted,(SELECT COUNT(*) FROM msg_files f WHERE f.msg_id=m.id) AS nf FROM messages m WHERE m.id IN(SELECT MAX(id) FROM messages WHERE to_id=0 UNION SELECT MAX(id) FROM messages WHERE to_id<>0 AND (from_id=? OR to_id=?) GROUP BY IF(from_id=?,to_id,from_id))");$q->execute([$uid,$uid,$uid]);
  foreach($q->fetchAll() as $m){$pr=(int)$m['to_id']===0?0:((int)$m['from_id']===$uid?(int)$m['to_id']:(int)$m['from_id']);$bb=preg_replace('/\[\[task:\d+:\d+:([^\]]*)\]\]/u','Task: $1',(string)$m['body']);if(trim($bb)===''&&(int)$m['nf'])$bb="\u{1F4CE} Attachment";if((int)$m['deleted'])$bb='Message deleted';$pv[$pr]=['lb'=>mb_substr($bb,0,90),'at'=>$m['created_at'],'lm'=>(int)$m['from_id']===$uid];}
  $phs=[];try{foreach($db->query("SELECT user_id,UNIX_TIMESTAMP(updated_at) AS t FROM user_photos")->fetchAll() as $r)$phs[(int)$r['user_id']]=(int)$r['t'];}catch(Throwable $e){}
  $us=[];foreach($db->query("SELECT id,name,role FROM users WHERE role<>'guest' ORDER BY name")->fetchAll() as $u)if((int)$u['id']!==$uid)$us[]=['id'=>(int)$u['id'],'name'=>$u['name'],'role'=>$u['role'],'online'=>!empty($onl[(int)$u['id']]),'ph'=>$phs[(int)$u['id']]??0,'n'=>$un[(int)$u['id']]??0,'last'=>$la[(int)$u['id']]??0]+($pv[(int)$u['id']]??[]);
  out(['build'=>BUILD,'users'=>$us,'team'=>['n'=>$un[0]??0,'last'=>$la[0]]+($pv[0]??[])]);
case 'msg_list':
  $peer=(int)($in['peer']??0);$after=(int)($in['after']??0);
  if($peer){$q=$db->prepare("SELECT m.id,m.from_id,IF(m.deleted=1,'',m.body) AS body,IF(m.deleted=1,NULL,m.html) AS html,m.created_at,m.deleted,(m.edited_at IS NOT NULL) AS edited,COALESCE(u.name,'Former member') AS name FROM messages m LEFT JOIN users u ON u.id=m.from_id WHERE m.id>? AND ((m.from_id=? AND m.to_id=?) OR (m.from_id=? AND m.to_id=?)) ORDER BY m.id DESC LIMIT 150");$q->execute([$after,$uid,$peer,$peer,$uid]);}
  else{$q=$db->prepare("SELECT m.id,m.from_id,IF(m.deleted=1,'',m.body) AS body,IF(m.deleted=1,NULL,m.html) AS html,m.created_at,m.deleted,(m.edited_at IS NOT NULL) AS edited,COALESCE(u.name,'Former member') AS name FROM messages m LEFT JOIN users u ON u.id=m.from_id WHERE m.id>? AND m.to_id=0 ORDER BY m.id DESC LIMIT 150");$q->execute([$after]);}
  $ms=array_reverse($q->fetchAll());foreach($ms as &$m){$m['id']=(int)$m['id'];$m['mine']=(int)$m['from_id']===$uid;$m['deleted']=(int)$m['deleted'];$m['edited']=(int)$m['edited'];$m['files']=[];}unset($m);
  if($ms){$fi=implode(',',array_map(fn($m)=>$m['id'],$ms));$by=[];foreach($db->query("SELECT id,msg_id,name,mime,size,gone FROM msg_files WHERE msg_id IN($fi) ORDER BY id")->fetchAll() as $f)$by[(int)$f['msg_id']][]=['id'=>(int)$f['id'],'name'=>$f['name'],'mime'=>$f['mime'],'size'=>(int)$f['size'],'gone'=>(int)$f['gone']];foreach($ms as &$m)$m['files']=$by[$m['id']]??[];unset($m);}
  msg_dlv_mark($db,$uid);
  $dlv=0;if($peer){$qd=$db->prepare("SELECT last_id FROM msg_dlv WHERE user_id=?");$qd->execute([$peer]);$dlv=(int)$qd->fetchColumn();}
  $seen=0;if($peer){$qs=$db->prepare("SELECT last_id FROM msg_reads WHERE user_id=? AND peer_id=?");$qs->execute([$peer,$uid]);$seen=(int)$qs->fetchColumn();}
  if($ms&&empty($in['noread'])){$mx=end($ms)['id'];$db->prepare("INSERT INTO msg_reads(user_id,peer_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$uid,$peer,$mx]);}
  if($peer){$qi=$db->prepare("SELECT id FROM messages WHERE (from_id=? AND to_id=?) OR (from_id=? AND to_id=?) ORDER BY id DESC LIMIT 150");$qi->execute([$uid,$peer,$peer,$uid]);}
  else{$qi=$db->query("SELECT id FROM messages WHERE to_id=0 ORDER BY id DESC LIMIT 150");}
  $ids=$qi->fetchAll(PDO::FETCH_COLUMN);$rc=msg_reacts($db,$ids,$uid);
  $ch=[];if($ids){foreach($db->query("SELECT id,IF(deleted=1,'',body) AS body,deleted,(edited_at IS NOT NULL) AS e FROM messages WHERE id IN(".implode(',',array_map('intval',$ids)).") AND (deleted=1 OR edited_at IS NOT NULL)")->fetchAll() as $r)$ch[(int)$r['id']]=['b'=>$r['body'],'d'=>(int)$r['deleted'],'e'=>(int)$r['e']];}
  out(['messages'=>$ms,'peer_seen'=>$seen,'peer_dlv'=>$dlv,'reacts'=>(object)$rc,'changes'=>(object)$ch]);
case 'msg_edit':
  $id=(int)($in['id']??0);$b=mb_substr(trim((string)($in['body']??'')),0,2000);
  $q=$db->prepare("SELECT id,from_id,deleted,html FROM messages WHERE id=?");$q->execute([$id]);$m=$q->fetch();
  if(!$m||(int)$m['from_id']!==$uid||(int)$m['deleted'])out(['error'=>'You can only edit your own messages']);
  if($m['html']!==null&&$m['html']!=='')out(['error'=>'Formatted messages cannot be edited. Delete and paste again.']);
  $nf=$db->prepare("SELECT COUNT(*) FROM msg_files WHERE msg_id=? AND gone=0");$nf->execute([$id]);
  if($b===''&&!(int)$nf->fetchColumn())out(['error'=>'A message cannot be empty. Use delete instead.']);
  $db->prepare("UPDATE messages SET body=?,edited_at=NOW() WHERE id=?")->execute([$b,$id]);out(['ok'=>1,'body'=>$b]);
case 'msg_del':
  $id=(int)($in['id']??0);
  $q=$db->prepare("SELECT id,from_id FROM messages WHERE id=?");$q->execute([$id]);$m=$q->fetch();
  if(!$m||(int)$m['from_id']!==$uid)out(['error'=>'You can only delete your own messages']);
  $db->prepare("UPDATE messages SET deleted=1,body='',html=NULL,edited_at=NULL WHERE id=?")->execute([$id]);
  try{$db->prepare("DELETE FROM msg_files WHERE msg_id=?")->execute([$id]);$db->prepare("DELETE FROM msg_reacts WHERE msg_id=?")->execute([$id]);}catch(Throwable $e){}
  out(['ok'=>1]);
case 'msg_react':
  $mid=(int)($in['id']??0);$em=trim((string)($in['e']??''));
  if($mid<=0||$em===''||mb_strlen($em)>8||preg_match('/[<>&"\'\s]/u',$em))out(['error'=>'Pick an emoji']);
  $q=$db->prepare("SELECT COUNT(*) FROM messages WHERE id=? AND (to_id=0 OR from_id=? OR to_id=?)");$q->execute([$mid,$uid,$uid]);if(!(int)$q->fetchColumn())out(['error'=>'Message not found']);
  $q=$db->prepare("SELECT COUNT(*) FROM msg_reacts WHERE msg_id=? AND user_id=? AND emoji=?");$q->execute([$mid,$uid,$em]);
  if((int)$q->fetchColumn())$db->prepare("DELETE FROM msg_reacts WHERE msg_id=? AND user_id=? AND emoji=?")->execute([$mid,$uid,$em]);
  else $db->prepare("INSERT IGNORE INTO msg_reacts(msg_id,user_id,emoji,created_at) VALUES(?,?,?,NOW())")->execute([$mid,$uid,$em]);
  $r=msg_reacts($db,[$mid],$uid);out(['ok'=>1,'reacts'=>$r[$mid]??[]]);
case 'msg_send':
  $peer=(int)($in['peer']??0);$b=mb_substr(trim((string)($in['body']??'')),0,2000);$fl=is_array($in['files']??null)?array_slice($in['files'],0,6):[];
  if($b===''&&!$fl)out(['error'=>'Write a message first']);
  if($peer){$q=$db->prepare("SELECT COUNT(*) FROM users WHERE id=?");$q->execute([$peer]);if(!(int)$q->fetchColumn()||$peer===$uid)out(['error'=>'Choose someone to message']);}
  $raws=[];$tot=0;foreach($fl as $fx=>$f){$raw=base64_decode((string)($f['data']??''),true);if($raw===false||$raw==='')continue;$tot+=strlen($raw);if(strlen($raw)>6*1048576||$tot>10*1048576)out(['error'=>'Files are too large (max 6 MB each, 10 MB in total)']);
    $mime=(string)($f['mime']??'');$inf=@getimagesizefromstring($raw);if($inf&&in_array($inf['mime'],['image/png','image/jpeg','image/gif','image/webp'],true))$mime=$inf['mime'];elseif(preg_match('/\.(exe|bat|cmd|com|scr|msi|js|vbs|ps1|php|html?|svg)$/i',(string)($f['name']??'')))out(['error'=>'That file type cannot be sent']);
    $raws[]=[mb_substr(basename((string)($f['name']??'file')),0,150),mb_substr($mime,0,80),$raw,$fx];}
  $html=isset($in['html'])?msg_clean_html($in['html']):'';
  if($b===''&&!$raws&&$html==='')out(['error'=>'Write a message first']);
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,$peer,$b]);$id=(int)$db->lastInsertId();
  $fmap=[];foreach($raws as $r){$db->prepare("INSERT INTO msg_files(msg_id,name,mime,size,data) VALUES(?,?,?,?,?)")->execute([$id,$r[0],$r[1],strlen($r[2]),$r[2]]);$fmap[$r[3]]=(int)$db->lastInsertId();}
  if($html!==''){$html=preg_replace_callback('~<img data-i="(\d+)">~',function($m)use($fmap){return isset($fmap[(int)$m[1]])?'<img data-fid="'.$fmap[(int)$m[1]].'">':'';},$html);
    $db->prepare("UPDATE messages SET html=? WHERE id=?")->execute([$html,$id]);}
  $pb=preg_replace('/\[\[task:\d+:\d+:([^\]]*)\]\]/u','Task: $1',$b);if(trim($pb)==='')$pb="\u{1F4CE} ".($raws[0][0]??'Attachment');
  push_q($peer,$peer?($me['name']??'Message'):'Team chat: '.($me['name']??''),$pb,'chat');
  $db->prepare("INSERT INTO msg_reads(user_id,peer_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$uid,$peer,$id]);out(['ok'=>1,'id'=>$id]);
case 'chat_me':
  $pq=$db->prepare("SELECT UNIX_TIMESTAMP(updated_at) FROM user_photos WHERE user_id=?");$pq->execute([$uid]);$pv=(int)$pq->fetchColumn();
  out(['me'=>['id'=>$uid,'name'=>$me['name'],'role'=>$me['role'],'ph'=>$pv]]);
case 'photo_set':
  $tu=$uid;if(!empty($in['user'])&&$me['role']==='admin')$tu=(int)$in['user'];
  if(!empty($in['remove'])){$db->prepare("DELETE FROM user_photos WHERE user_id=?")->execute([$tu]);out(['ok'=>1,'ph'=>0]);}
  $raw=base64_decode((string)($in['data']??''),true);if($raw===false||$raw===''||strlen($raw)>400000)out(['error'=>'Choose a smaller picture']);
  $inf=@getimagesizefromstring($raw);if(!$inf||!in_array($inf['mime'],['image/jpeg','image/png','image/webp'],true))out(['error'=>'Use a JPG, PNG or WebP picture']);
  $db->prepare("INSERT INTO user_photos(user_id,mime,data,updated_at) VALUES(?,?,?,NOW()) ON DUPLICATE KEY UPDATE mime=VALUES(mime),data=VALUES(data),updated_at=NOW()")->execute([$tu,$inf['mime'],$raw]);
  out(['ok'=>1,'ph'=>time()]);
case 'chat_tasks':
  $qq=trim((string)($in['q']??''));$ids=array_map(fn($w)=>(int)$w['id'],$WS);if(!$ids)out(['tasks'=>[]]);
  $st=$db->prepare("SELECT t.id,t.ws_id,t.title,t.order_no,w.name AS company FROM tasks t JOIN workspaces w ON w.id=t.ws_id WHERE t.ws_id IN(".implode(',',$ids).") AND (?='' OR t.title LIKE ? OR t.order_no LIKE ?) ORDER BY t.updated_at DESC LIMIT 25");$like='%'.str_replace(['%','_'],['\\%','\\_'],$qq).'%';$st->execute([$qq,$like,$like]);
  out(['tasks'=>$st->fetchAll()]);
case 'morning_card':
case 'morning_ack':
  try{$db->exec("CREATE TABLE IF NOT EXISTS morning_ack(user_id INT NOT NULL,day DATE NOT NULL,at DATETIME NOT NULL,PRIMARY KEY(user_id,day)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");}catch(Throwable $e){}
  $td=(string)($in['today']??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$td))$td=date('Y-m-d');
  if($me['role']==='guest')out(['show'=>false]);
  if($a==='morning_ack'){$db->prepare("INSERT INTO morning_ack(user_id,day,at) VALUES(?,?,NOW()) ON DUPLICATE KEY UPDATE at=at")->execute([$uid,$td]);out(['ok'=>1]);}
  $q=$db->prepare("SELECT COUNT(*) FROM morning_ack WHERE user_id=? AND day=?");$q->execute([$uid,$td]);if((int)$q->fetchColumn())out(['show'=>false]);
  $ids=array_map(fn($w)=>(int)$w['id'],$WS);if(!$ids)out(['show'=>false]);$inl=implode(',',$ids);
  $yday=date('Y-m-d',strtotime($td.' -1 day'));
  // last working day (Mon-Fri) before today
  $lw=null;for($t=strtotime($yday);$t>strtotime($td.' -8 day');$t-=86400){if((int)date('N',$t)>=6)continue;$lw=date('Y-m-d',$t);break;}
  $q=$db->prepare("SELECT MIN(work_date) FROM time_logs WHERE user_id=?");$q->execute([$uid]);$first=$q->fetchColumn();
  $hrs=null;if($lw&&$first&&$first<=$lw){$q=$db->prepare("SELECT COALESCE(SUM(hours),0) FROM time_logs WHERE user_id=? AND work_date=?");$q->execute([$uid,$lw]);$hrs=['date'=>$lw,'h'=>(float)$q->fetchColumn(),'need'=>WORK_H];}
  $mine="(t.assigned_to=? OR t.id IN(SELECT task_id FROM task_assignees WHERE user_id=?))";
  $cols="t.id,t.ws_id,t.title,t.order_no,t.customer,t.drawing_type,t.start_date,t.end_date,t.status";
  $due=[];if($lw){$q=$db->prepare("SELECT $cols FROM tasks t WHERE $mine AND t.status<>'done' AND t.ws_id IN($inl) AND t.end_date BETWEEN ? AND ? ORDER BY t.end_date,t.id LIMIT 40");$q->execute([$uid,$uid,$lw,$yday]);$due=$q->fetchAll();}
  $dueIds=array_map(fn($r)=>(int)$r['id'],$due);
  $q=$db->prepare("SELECT $cols FROM tasks t WHERE $mine AND t.status<>'done' AND t.ws_id IN($inl) AND COALESCE(t.start_date,t.end_date)<=? AND COALESCE(t.end_date,t.start_date)>=? ORDER BY (t.end_date IS NULL),t.end_date,t.id LIMIT 60");$q->execute([$uid,$uid,$td,$td]);
  $today=array_values(array_filter($q->fetchAll(),fn($r)=>!in_array((int)$r['id'],$dueIds,true)));
  $od=[];$q=$db->prepare("SELECT $cols FROM tasks t WHERE $mine AND t.status<>'done' AND t.ws_id IN($inl) AND t.end_date<? ORDER BY t.end_date,t.id LIMIT 60");$q->execute([$uid,$uid,$lw?$lw:$td]);$od=array_values(array_filter($q->fetchAll(),fn($r)=>!in_array((int)$r['id'],$dueIds,true)));
  $st=[];foreach($db->query("SELECT ws_id,skey,name,color FROM statuses WHERE ws_id IN($inl) ORDER BY ws_id,sort_no,id")->fetchAll() as $r)$st[$r['ws_id']][]=['k'=>$r['skey'],'n'=>$r['name'],'c'=>$r['color']];
  $wn=[];foreach($WS as $w)$wn[$w['id']]=$w['name'];
  out(['show'=>true,'name'=>$me['name'],'hours'=>$hrs,'due'=>$due,'today'=>$today,'overdue'=>$od,'st'=>(object)$st,'ws'=>(object)$wn,'lw'=>$lw]);
case 'hours_gap':
  $td=(string)($in['today']??'');if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$td))$td=date('Y-m-d');
  $q=$db->prepare("SELECT MIN(work_date) FROM time_logs WHERE user_id=?");$q->execute([$uid]);$first=$q->fetchColumn();
  if(!$first||$me['role']==='guest')out(['days'=>[],'last'=>null,'need'=>WORK_H]);
  $to=date('Y-m-d',strtotime($td.' -1 day'));$from=date('Y-m-d',strtotime($td.' -15 day'));if($first>$from)$from=$first;
  $q=$db->prepare("SELECT work_date,SUM(hours) h FROM time_logs WHERE user_id=? AND work_date BETWEEN ? AND ? GROUP BY work_date");$q->execute([$uid,date('Y-m-d',strtotime($td.' -20 day')),$to]);
  $sm=[];foreach($q->fetchAll() as $r)$sm[$r['work_date']]=(float)$r['h'];
  $days=[];for($t=strtotime($from);$t<=strtotime($to);$t+=86400){$d=date('Y-m-d',$t);if((int)date('N',$t)>=6)continue;$h=$sm[$d]??0.0;if($h<WORK_H-0.001)$days[]=['date'=>$d,'h'=>$h];}
  $last=null;for($t=strtotime($to);$t>strtotime($to.' -7 day');$t-=86400){if((int)date('N',$t)>=6)continue;$d=date('Y-m-d',$t);if($d>=$first)$last=['date'=>$d,'h'=>$sm[$d]??0.0];break;}
  out(['days'=>$days,'last'=>$last,'need'=>WORK_H]);
case 'home_msgs':
  $q=$db->prepare("SELECT m.id,m.from_id,m.to_id,m.body,m.created_at,COALESCE(u.name,'Former member') AS name FROM messages m LEFT JOIN users u ON u.id=m.from_id WHERE m.created_at>=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND m.from_id<>? AND (m.to_id=? OR m.to_id=0) ORDER BY m.id DESC LIMIT 200");$q->execute([$uid,$uid]);
  $rd=[];$q2=$db->prepare("SELECT peer_id,last_id FROM msg_reads WHERE user_id=?");$q2->execute([$uid]);foreach($q2->fetchAll() as $r)$rd[(int)$r['peer_id']]=(int)$r['last_id'];
  $cv=[];foreach($q->fetchAll() as $m){$pr=(int)$m['to_id']===0?0:(int)$m['from_id'];
    if(!isset($cv[$pr]))$cv[$pr]=['peer'=>$pr,'name'=>$pr?$m['name']:'Team chat','from'=>$m['name'],'body'=>mb_substr((string)$m['body'],0,140),'at'=>$m['created_at'],'n'=>0,'unread'=>0];
    $cv[$pr]['n']++;if((int)$m['id']>($rd[$pr]??0))$cv[$pr]['unread']++;}
  out(['convs'=>array_values($cv)]);
case 'my_tasks':
  $ids=array_map(fn($w)=>(int)$w['id'],$WS);
  if(!$ids)out(['tasks'=>[],'st'=>(object)[],'ws'=>(object)[]]);
  $in=implode(',',$ids);
  $q=$db->prepare("SELECT id,ws_id,title,order_no,customer,drawing_type,start_date,end_date,status FROM tasks WHERE (assigned_to=? OR id IN(SELECT task_id FROM task_assignees WHERE user_id=?)) AND status<>'done' AND ws_id IN($in) ORDER BY (end_date IS NULL),end_date,id LIMIT 500");$q->execute([$uid,$uid]);
  $tk=$q->fetchAll();$st=[];
  foreach($db->query("SELECT ws_id,skey,name,color FROM statuses WHERE ws_id IN($in)")->fetchAll() as $r)$st[$r['ws_id']][$r['skey']]=['n'=>$r['name'],'c'=>$r['color']];
  $wn=[];foreach($WS as $w)$wn[$w['id']]=$w['name'];
  $cn=[];foreach($db->query("SELECT ws_id,COUNT(*) o,SUM(end_date IS NOT NULL AND end_date<CURDATE()) l FROM tasks WHERE status<>'done' AND ws_id IN($in) GROUP BY ws_id")->fetchAll() as $r)$cn[$r['ws_id']]=['open'=>(int)$r['o'],'late'=>(int)$r['l']];
  out(['tasks'=>$tk,'st'=>(object)$st,'ws'=>(object)$wn,'cnt'=>(object)$cn]);
case 'list':
  $ver=(int)$db->query("SELECT v FROM meta WHERE k='ver'")->fetchColumn();
  $names=[];foreach($db->query("SELECT id,name FROM users")->fetchAll() as $u)$names[$u['id']]=$u['name'];
  $res=['build'=>BUILD,'inbox'=>inbox_sql($db,true)+notices_unread($db),'v'=>$ver,'me'=>$me,'ws'=>$WS,'w'=>$W,'level'=>$LV,'names'=>(object)$names,'users'=>[],'cols'=>[],'tasks'=>[]];
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
    $cm=[];$cq=$db->prepare("SELECT a.task_id,a.user_id FROM task_assignees a JOIN tasks t ON t.id=a.task_id WHERE t.ws_id=?");$cq->execute([$W]);foreach($cq->fetchAll() as $r)$cm[(int)$r['task_id']][]=(int)$r['user_id'];
    foreach($tasks as &$tk){$tk['co']=$cm[(int)$tk['id']]??[];}unset($tk);
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
    $res['allUsers']=$db->query("SELECT id,name,username,email,role FROM users ORDER BY name")->fetchAll();
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
    $q=$db->prepare("SELECT COALESCE(MAX(sort_no),0)+1 FROM tasks WHERE ws_id=?");$q->execute([$W]);$sn=(int)$q->fetchColumn();
    $db->prepare("INSERT INTO tasks(".implode(',',$F).",ws_id,sort_no,updated_at) VALUES(".implode(',',array_fill(0,count($F),'?')).",?,?,NOW())")->execute([...array_values($v),$W,$sn]);
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
  need_level('full');foreach($in['ids']??[] as $i){$db->prepare("DELETE FROM task_assignees WHERE task_id=?")->execute([(int)$i]);$db->prepare("DELETE FROM tasks WHERE id=? AND ws_id=?")->execute([(int)$i,$W]);$db->prepare("DELETE FROM task_comments WHERE task_id=? AND ws_id=?")->execute([(int)$i,$W]);$db->prepare("DELETE FROM time_logs WHERE task_id=? AND ws_id=?")->execute([(int)$i,$W]);}out(['ok'=>1]);
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
    $db->prepare("DELETE FROM task_assignees WHERE task_id=?")->execute([$i]);
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
      $db->prepare("UPDATE tasks SET ws_id=?,group_id=?,status=?,assigned_to=?,extra=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$to,$gid,$st,$as,$nx?json_encode($nx):null,$t['id'],$W]);$db->prepare("DELETE FROM task_assignees WHERE task_id=?")->execute([$t['id']]);
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
  $q=$db->prepare("SELECT l.id,l.user_id,COALESCE(u.name,'Former member') AS name,l.work_date,l.hours,l.kind,l.logged_by,l.note,l.rev FROM time_logs l LEFT JOIN users u ON u.id=l.user_id WHERE l.task_id=? AND l.ws_id=? ORDER BY l.work_date DESC,l.id DESC");$q->execute([$tid,$W]);
  out(['logs'=>$q->fetchAll(),'me'=>(int)$uid]);
case 'tl_add':
  need_level('edit');$tid=(int)($in['task_id']??0);$d=(string)($in['date']??'');$h=round((float)($in['hours']??0),2);$n=mb_substr(trim((string)($in['note']??'')),0,255);$kd=(($in['kind']??'')==='OT')?'OT':'R';$rv=mb_substr(trim((string)($in['rev']??'')),0,60);
  if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$d))out(['error'=>'Choose a date']);
  if($h<=0||$h>24)out(['error'=>'Hours must be between 0.25 and 24']);
  $q=$db->prepare("SELECT assigned_to FROM tasks WHERE id=? AND ws_id=?");$q->execute([$tid,$W]);$tr=$q->fetch();if(!$tr)out(['error'=>'Task not found']);
  // Rule: hours always belong to the person logging them. Only an admin can add hours for someone else.
  if($me['role']!=='admin'){$who=(int)$uid;}
  else{
    $who=(int)($in['for_user']??0);
    if($who){$q=$db->prepare("SELECT COUNT(*) FROM users WHERE id=?");$q->execute([$who]);if(!(int)$q->fetchColumn())out(['error'=>'Unknown person']);}
    else{
      $who=(int)$tr['assigned_to'];
      if($who!==$uid){$q=$db->prepare("SELECT COUNT(*) FROM task_assignees WHERE task_id=? AND user_id=?");$q->execute([$tid,$uid]);if((int)$q->fetchColumn())$who=$uid;}
      if(!$who){$as=(int)($in['assign_to']??0);if(!$as)out(['error'=>'This task is unassigned. Choose who it is assigned to.','need_assign'=>1]);
        $q=$db->prepare("SELECT COUNT(*) FROM users WHERE id=?");$q->execute([$as]);if(!(int)$q->fetchColumn())out(['error'=>'Unknown person']);
        $db->prepare("UPDATE tasks SET assigned_to=?,updated_at=NOW() WHERE id=? AND ws_id=?")->execute([$as,$tid,$W]);$who=$as;}
    }
  }
  $db->prepare("INSERT INTO time_logs(task_id,ws_id,user_id,logged_by,work_date,hours,kind,note,rev,created_at) VALUES(?,?,?,?,?,?,?,?,?,NOW())")->execute([$tid,$W,$who,$uid,$d,$h,$kd,$n,$rv]);ot_rebalance($db,$who,$d);out(['ok'=>1]);
case 'tl_rev':
  need_level('edit');$id=(int)($in['id']??0);$q=$db->prepare("SELECT user_id,logged_by,work_date FROM time_logs WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$o=$q->fetch();
  if($o===false)out(['error'=>'Entry not found']);
  if((int)$o['user_id']!==(int)$uid&&$me['role']!=='admin')out(['error'=>'You can only edit your own hours. Only an admin can change someone else\'s.']);
  $db->prepare("UPDATE time_logs SET rev=? WHERE id=?")->execute([mb_substr(trim((string)($in['rev']??'')),0,60),$id]);out(['ok'=>1]);
case 'tl_del':
  need_level('edit');$id=(int)($in['id']??0);$q=$db->prepare("SELECT user_id,logged_by FROM time_logs WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$o=$q->fetch();
  if($o===false)out(['error'=>'Entry not found']);
  if((int)$o['user_id']!==(int)$uid&&$me['role']!=='admin')out(['error'=>'You can only delete your own hours. Only an admin can delete someone else\'s.']);
  $db->prepare("DELETE FROM time_logs WHERE id=?")->execute([$id]);ot_rebalance($db,(int)$o['user_id'],$o['work_date']);out(['ok'=>1]);
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
case 'hrs_report':
  admin();$f=(string)($in['from']??'');$t=(string)($in['to']??'');
  if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$f)||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$t)||$f>$t)out(['error'=>'Choose a valid date range']);
  $wh=["l.work_date BETWEEN ? AND ?"];$pr=[$f,$t];
  foreach(['users'=>'l.user_id','ws'=>'l.ws_id','groups'=>'COALESCE(pg.id,g.id)'] as $k2=>$col){
    $ids=array_values(array_unique(array_filter(array_map('intval',(array)($in[$k2]??[])))));
    if($ids){$wh[]="$col IN (".implode(',',array_fill(0,count($ids),'?')).")";foreach($ids as $x)$pr[]=$x;}
  }
  $kd=(string)($in['kind']??'');if($kd==='R'||$kd==='OT'){$wh[]="l.kind=?";$pr[]=$kd;}
  $tx=trim((string)($in['text']??''));if($tx!==''){$lk='%'.str_replace(['%','_'],['\\%','\\_'],mb_substr($tx,0,80)).'%';$wh[]="(t.title LIKE ? OR t.order_no LIKE ? OR t.customer LIKE ? OR l.note LIKE ?)";array_push($pr,$lk,$lk,$lk,$lk);}
  $q=$db->prepare("SELECT l.id,l.user_id,COALESCE(u.name,'Former member') AS person,l.ws_id,COALESCE(w.name,'(deleted company)') AS company,l.work_date,l.hours,l.kind,l.note,l.rev,t.id AS task_id,t.order_no,t.title,t.customer,t.drawing_type,COALESCE(pg.name,g.name,'(No group)') AS grp,IF(pg.id IS NULL,'',COALESCE(g.name,'')) AS sub FROM time_logs l LEFT JOIN tasks t ON t.id=l.task_id LEFT JOIN users u ON u.id=l.user_id LEFT JOIN workspaces w ON w.id=l.ws_id LEFT JOIN task_groups g ON g.id=t.group_id LEFT JOIN task_groups pg ON pg.id=g.parent_id WHERE ".implode(' AND ',$wh)." ORDER BY l.work_date,w.name,u.name,l.id LIMIT 20001");$q->execute($pr);
  $rows=$q->fetchAll();$cut=count($rows)>20000;if($cut)array_pop($rows);
  foreach($rows as &$r){$r['hours']=(float)$r['hours'];}unset($r);
  out(['rows'=>$rows,'cut'=>$cut,
    'users'=>$db->query("SELECT id,name FROM users ORDER BY name")->fetchAll(),
    'companies'=>$db->query("SELECT id,name FROM workspaces ORDER BY sort_no,name")->fetchAll(),
    'groups'=>$db->query("SELECT id,ws_id,name FROM task_groups WHERE parent_id IS NULL OR parent_id=0 ORDER BY ws_id,sort_no,name")->fetchAll()]);
case 'guest_home':
  // Overview for guests (and an admin test window): status report numbers plus who is working on what, limited to the companies the viewer can see.
  $as=(int)($in['as']??0);$ids=[];$asName='';
  if($me['role']==='admin'&&$as>0){$q=$db->prepare("SELECT name FROM users WHERE id=?");$q->execute([$as]);$asName=(string)$q->fetchColumn();$q=$db->prepare("SELECT ws_id FROM ws_users WHERE user_id=?");$q->execute([$as]);$ids=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));}
  else $ids=array_map(fn($w)=>(int)$w['id'],$WS);
  $wn=[];if($ids){$q=$db->query("SELECT id,name,CRC32(COALESCE(logo,'')) AS lv,(logo IS NOT NULL AND logo<>'') AS hl FROM workspaces WHERE id IN(".implode(',',$ids).") ORDER BY sort_no,id");$wn=$q->fetchAll();}
  $qe=$db->prepare("SELECT email FROM users WHERE id=?");$qe->execute([(int)$me['id']]);$res=['email'=>trim((string)$qe->fetchColumn()),'companies'=>$wn,'as'=>$as,'asName'=>$asName,'from'=>'','to'=>'','done'=>[],'going'=>[],'soon'=>[],'people'=>[]];
  if($me['role']==='admin'){$res['guests']=$db->query("SELECT id,name FROM users WHERE role='guest' ORDER BY name")->fetchAll();}
  if(!$ids)out($res);
  gh_ddl($db);$hu=$me['role']==='guest'?(int)$me['id']:($as>0?$as:0);$hide=[];
  if($hu){$q=$db->prepare("SELECT gid FROM guest_hide WHERE user_id=?");$q->execute([$hu]);$hide=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));}
  if($me['role']==='admin'&&$as>0){$gt=[];$q=$db->query("SELECT g.id,g.ws_id,g.name FROM task_groups g WHERE g.ws_id IN(".implode(',',$ids).") AND (g.parent_id IS NULL OR g.parent_id=0) ORDER BY g.ws_id,g.sort_no,g.id");foreach($q->fetchAll() as $g)$gt[]=['gid'=>(int)$g['id'],'ws_id'=>(int)$g['ws_id'],'name'=>$g['name']];foreach($ids as $wi)$gt[]=['gid'=>-(1000+$wi),'ws_id'=>$wi,'name'=>'(No group)'];$res['gtree']=$gt;$res['hide']=$hide;}
  $d=sr_data($db,$ids,$hide);
  $keep=['id','ws_id','company','title','order_no','customer','drawing_type','start_date','end_date','sname','person','when'];
  $strip=function($r)use($keep){$o=[];foreach($keep as $k)$o[$k]=$r[$k]??'';return $o;};
  foreach(['done','going','soon'] as $k)$res[$k]=array_map($strip,$d[$k]);
  $res['from']=$d['from'];$res['to']=$d['to'];$res['today']=$d['today'];$res['next']=$d['next'];
  // who is working on what: every person on an ongoing task (main assignee plus extra assignees)
  $tid=array_map(fn($r)=>(int)$r['id'],$d['going']);$ppl=[];
  if($tid){
    $q=$db->query("SELECT t.id,t.assigned_to AS u FROM tasks t WHERE t.id IN(".implode(',',$tid).") AND t.assigned_to IS NOT NULL AND t.assigned_to>0 UNION SELECT task_id,user_id FROM task_assignees WHERE task_id IN(".implode(',',$tid).")");
    $by=[];foreach($q->fetchAll() as $r)$by[(int)$r['id']][(int)$r['u']]=1;
    $un=[];foreach($db->query("SELECT id,name FROM users WHERE role<>'guest'")->fetchAll() as $u)$un[(int)$u['id']]=$u['name'];
    foreach($d['going'] as $r){$us=array_keys($by[(int)$r['id']]??[]);if(!$us)$us=[0];
      foreach($us as $u){$nm=$u?($un[$u]??''):'Unassigned';if($nm==='')continue;$ppl[$u]['name']=$nm;$ppl[$u]['tasks'][]=$strip($r);}}
  }
  uasort($ppl,fn($a,$b)=>($a['name']==='Unassigned')<=>($b['name']==='Unassigned')?:strcmp($a['name'],$b['name']));
  $res['people']=array_values($ppl);
  out($res);
case 'guest_email_report':
  // Emails the viewer a status report for one of their companies, to the address on their own account only.
  $as=(int)($in['as']??0);$wid=(int)($in['ws']??0);
  $qe=$db->prepare("SELECT email FROM users WHERE id=?");$qe->execute([(int)$me['id']]);$to=trim((string)$qe->fetchColumn());
  if(!filter_var($to,FILTER_VALIDATE_EMAIL))out(['error'=>'There is no email address on your account yet. Please ask the admin to add it.']);
  $hu=$me['role']==='guest'?(int)$me['id']:0;
  if($me['role']==='admin'&&$as>0){$q=$db->prepare("SELECT ws_id FROM ws_users WHERE user_id=?");$q->execute([$as]);$ids=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));$hu=$as;}
  elseif($me['role']==='admin'){$ids=array_map('intval',$db->query("SELECT id FROM workspaces")->fetchAll(PDO::FETCH_COLUMN));}
  else $ids=array_map(fn($w)=>(int)$w['id'],$WS);
  if(!in_array($wid,$ids,true))out(['error'=>'That company is not available to you']);
  $lk=sys_get_temp_dir().'/tf_ghm_'.(int)$me['id'];if(is_file($lk)&&time()-filemtime($lk)<60)out(['error'=>'A report was just sent. Please wait a minute before sending another.']);
  gh_ddl($db);$hide=[];if($hu){$q=$db->prepare("SELECT gid FROM guest_hide WHERE user_id=?");$q->execute([$hu]);$hide=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));}
  $wq=$db->prepare("SELECT name FROM workspaces WHERE id=?");$wq->execute([$wid]);$cn=(string)$wq->fetchColumn();
  $r=sr_send($db,['emails'=>$to,'ws_ids'=>(string)$wid,'excl_g'=>implode(',',$hide),'excl_t'=>'','name'=>'Task status report'.($cn!==''?' - '.$cn:'')]);
  if(empty($r['ok']))out(['error'=>'The email could not be sent'.(!empty($r['err'])?': '.$r['err']:'')]);
  @touch($lk);out(['ok'=>1,'to'=>$to]);
case 'guest_hide_save':
  admin();gh_ddl($db);$u=(int)($in['user_id']??0);$g=sr_ids($in['hide']??[]);
  if(!$u)out(['error'=>'Pick a guest first']);
  $db->prepare("DELETE FROM guest_hide WHERE user_id=?")->execute([$u]);
  foreach(array_unique(array_merge($g,array_map('intval',(array)($in['hide_none']??[])))) as $x){if($x===0)continue;$db->prepare("INSERT IGNORE INTO guest_hide(user_id,gid) VALUES(?,?)")->execute([$u,$x]);}
  out(['ok'=>1]);
case 'sr_list':
  admin();sr_ddl($db);$q=$db->query("SELECT id,name,ws_ids,emails,dow,hhmm,active,last_sent,COALESCE(excl_g,'') AS excl_g,COALESCE(excl_t,'') AS excl_t FROM status_reports ORDER BY id");
  out(['items'=>$q->fetchAll(),'companies'=>$db->query("SELECT id,name FROM workspaces ORDER BY sort_no,name")->fetchAll(),'users'=>$db->query("SELECT id,name,email,role FROM users ORDER BY name")->fetchAll(),'ms_from'=>($mc=ms_cfg())?$mc['from']:'']);
case 'sr_preview':
  admin();sr_ddl($db);$d=sr_data($db,(array)($in['ws']??[]),(array)($in['exg']??[]),(array)($in['ext']??[]));out(['html'=>sr_html($d,'Task status report',sr_assets($db,(array)($in['ws']??[]),'data')),'n'=>[count($d['done']),count($d['going']),count($d['soon'])],'tree'=>$d['tree']]);
case 'sr_save':
  admin();sr_ddl($db);$id=(int)($in['id']??0);$nm=mb_substr(trim((string)($in['name']??'')),0,80);
  $em=implode(', ',sr_valid_emails($in['emails']??''));if($em==='')out(['error'=>'Add at least one valid email address']);
  $ws=implode(',',array_values(array_unique(array_filter(array_map('intval',(array)($in['ws']??[]))))));
  $dw=max(1,min(7,(int)($in['dow']??4)));$hm=max(0,min(1439,(int)($in['hhmm']??900)));$ac=empty($in['active'])?0:1;
  $xg=implode(',',sr_ids($in['exg']??[]));$xt=implode(',',sr_ids($in['ext']??[]));
  if($id){$db->prepare("UPDATE status_reports SET name=?,ws_ids=?,emails=?,dow=?,hhmm=?,active=?,excl_g=?,excl_t=? WHERE id=?")->execute([$nm,$ws,$em,$dw,$hm,$ac,$xg,$xt,$id]);}
  else{$db->prepare("INSERT INTO status_reports(name,ws_ids,emails,dow,hhmm,active,excl_g,excl_t,created_at) VALUES(?,?,?,?,?,?,?,?,NOW())")->execute([$nm,$ws,$em,$dw,$hm,$ac,$xg,$xt]);}
  out(['ok'=>1,'id'=>$id?:(int)$db->lastInsertId()]);
case 'sr_del':
  admin();sr_ddl($db);$db->prepare("DELETE FROM status_reports WHERE id=?")->execute([(int)($in['id']??0)]);out(['ok'=>1]);
case 'sr_send':
  admin();sr_ddl($db);$q=$db->prepare("SELECT * FROM status_reports WHERE id=?");$q->execute([(int)($in['id']??0)]);$s=$q->fetch();if(!$s)out(['error'=>'Not found']);
  $r=sr_send($db,$s);if(!$r['ok'])out(['error'=>$r['err']?:'Could not send']);out(['ok'=>1,'sent'=>$r['sent'],'err'=>$r['err']]);
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
  admin();$pe=col_peers($db,$W);out(['company'=>inv_company($db),'colsync'=>$pe?$pe[0]:0]);
case 'ws_colsync':
  admin();$id=(int)($in['id']??0);$with=(int)($in['with']??0);
  if(!$with){$db->prepare("UPDATE workspaces SET col_grp=0 WHERE id=?")->execute([$id]);out(['ok'=>1]);}
  if($with===$id)out(['error'=>'Pick a different company']);
  $q=$db->prepare("SELECT col_grp FROM workspaces WHERE id=?");$q->execute([$with]);$g=(int)$q->fetchColumn();if($g<=0)$g=$with;
  $db->prepare("UPDATE workspaces SET col_grp=? WHERE id IN(?,?)")->execute([$g,$id,$with]);
  $q=$db->prepare("SELECT id FROM workspaces WHERE col_grp=?");$q->execute([$g]);$m=array_map('intval',$q->fetchAll(PDO::FETCH_COLUMN));
  $all=[];foreach($m as $w){$q=$db->prepare("SELECT name,ctype,options FROM custom_cols WHERE ws_id=? ORDER BY sort_no,id");$q->execute([$w]);foreach($q->fetchAll() as $c)$all[]=$c;}
  foreach($m as $w)foreach($all as $c)col_put($db,$w,$c['name'],$c['ctype'],$c['options']);
  out(['ok'=>1]);
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
  $q=$db->prepare("SELECT l.id,l.task_id,l.work_date,l.hours,l.kind,l.note,w.name AS ws,t.title,t.order_no,COALESCE(pg.name,g.name) AS top_name FROM time_logs l JOIN tasks t ON t.id=l.task_id LEFT JOIN workspaces w ON w.id=l.ws_id LEFT JOIN task_groups g ON g.id=t.group_id LEFT JOIN task_groups pg ON pg.id=g.parent_id WHERE l.user_id=? AND l.work_date BETWEEN ? AND ? ORDER BY l.work_date,l.id");$q->execute([$who,$f,$t]);$rows=$q->fetchAll();
  $plan=[];try{$q=$db->prepare("SELECT t.id,t.title,t.order_no,t.customer,t.start_date,t.end_date,t.status,w.name AS ws FROM tasks t LEFT JOIN workspaces w ON w.id=t.ws_id WHERE (t.assigned_to=? OR t.id IN(SELECT task_id FROM task_assignees WHERE user_id=?)) AND COALESCE(t.start_date,t.end_date) IS NOT NULL AND COALESCE(t.start_date,t.end_date)<=? AND COALESCE(t.end_date,t.start_date)>=? ORDER BY t.start_date,t.id LIMIT 600");$q->execute([$who,$who,$t,$f]);$plan=$q->fetchAll();}catch(Throwable $e){}
  out(['rows'=>$rows,'user'=>$who,'plan'=>$plan]);
case 'wiki_list':
  $seeded=0;$serr='';
  if($me['role']==='admin'){try{$sd=wiki_seed_dir();if($sd!==''){$j=json_decode((string)file_get_contents($sd.'/pages.json'),true);$sr=mb_substr((string)($j['src']??'seed'),0,40);$q=$db->prepare("SELECT COUNT(*) FROM wiki_pages WHERE src=?");$q->execute([$sr]);if(!(int)$q->fetchColumn()){$r=wiki_seed_run($db,$uid);$seeded=$r['n'];$serr=$r['err'];}}}catch(Throwable $e){$serr=$e->getMessage();}}
  out(['pages'=>$db->query("SELECT p.id,p.title,p.body,p.cat,p.tags,p.updated_at,COALESCE(u.name,'') AS by_name FROM wiki_pages p LEFT JOIN users u ON u.id=p.updated_by ORDER BY p.cat,p.title")->fetchAll(),'seeded'=>$seeded,'seed_err'=>$serr]);
case 'wiki_seed':
  admin();$r=wiki_seed_run($db,$uid);out(['ok'=>1,'n'=>$r['n'],'err'=>$r['err']]);
case 'wiki_save':
  if($me['role']==='guest')out(['error'=>'Guests cannot edit the wiki']);
  $id=(int)($in['id']??0);$t=mb_substr(trim((string)($in['title']??'')),0,150);$b=mb_substr((string)($in['body']??''),0,60000);if($t==='')out(['error'=>'Give the page a title']);
  $ct=mb_substr(trim((string)($in['cat']??'')),0,80);$tg=wiki_tags($in['tags']??'');
  if($id){$db->prepare("UPDATE wiki_pages SET title=?,body=?,cat=?,tags=?,updated_by=?,updated_at=NOW() WHERE id=?")->execute([$t,$b,$ct,$tg,$uid,$id]);}
  else{$db->prepare("INSERT INTO wiki_pages(title,body,cat,tags,updated_by,updated_at) VALUES(?,?,?,?,?,NOW())")->execute([$t,$b,$ct,$tg,$uid]);$id=(int)$db->lastInsertId();}
  out(['ok'=>1,'id'=>$id]);
case 'wiki_up':
  if($me['role']==='guest')out(['error'=>'Guests cannot upload']);
  $d=(string)($in['data']??'');if(strpos($d,',')!==false&&strpos($d,'base64')!==false)$d=substr($d,strpos($d,',')+1);
  $raw=base64_decode($d,true);if($raw===false||strlen($raw)<50)out(['error'=>'Could not read the image']);
  if(strlen($raw)>4500000)out(['error'=>'Image is too large (max 4 MB)']);
  $inf=@getimagesizefromstring($raw);if(!$inf||!in_array($inf['mime'],['image/png','image/jpeg','image/gif','image/webp'],true))out(['error'=>'Only PNG, JPEG, GIF or WebP images']);
  $db->prepare("INSERT INTO wiki_media(name,mime,data,created_at) VALUES(?,?,?,NOW())")->execute([mb_substr((string)($in['name']??'image'),0,120),$inf['mime'],$raw]);
  out(['ok'=>1,'id'=>(int)$db->lastInsertId()]);
case 'wiki_del':
  if($me['role']==='guest')out(['error'=>'Guests cannot delete pages']);
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
  foreach(col_peers($db,$W) as $pw)col_put($db,$pw,$n,$t,$o?json_encode($o,JSON_UNESCAPED_UNICODE):null);
  out(['ok'=>1]);
case 'col_edit':
  need_level('edit');$id=(int)($in['id']??0);$n=mb_substr(trim($in['name']??''),0,60);if($n==='')out(['error'=>'Enter a column name']);
  $cur=null;foreach(get_cols($db) as $e){if((int)$e['id']===$id)$cur=$e;}if(!$cur)out(['error'=>'Column not found']);
  foreach(get_cols($db) as $e)if((int)$e['id']!==$id&&mb_strtolower($e['name'])===mb_strtolower($n))out(['error'=>'A column with that name already exists']);
  $conv=$cur['ctype']==='text'&&trim((string)($in['options']??''))!==''&&($in['ctype']??'')==='dropdown';$o=null;if($cur['ctype']==='dropdown'||$conv){$l=[];foreach(explode(',',(string)($in['options']??'')) as $p){$p=mb_substr(trim($p),0,60);if($p!==''&&!in_array($p,$l,true))$l[]=$p;}if(!$l)out(['error'=>'Enter at least one option, separated by commas']);$o=json_encode($l,JSON_UNESCAPED_UNICODE);}
  $db->prepare("UPDATE custom_cols SET name=?,options=?".($conv?",ctype='dropdown'":"")." WHERE id=? AND ws_id=?")->execute([$n,$o,$id,$W]);
  foreach(col_peers($db,$W) as $pw){$u=$db->prepare("UPDATE custom_cols SET name=?,options=?".($conv?",ctype='dropdown'":"")." WHERE ws_id=? AND LOWER(name)=LOWER(?)");$u->execute([$n,$o,$pw,$cur['name']]);if(!$u->rowCount())col_put($db,$pw,$n,($conv||$cur['ctype']==='dropdown')?'dropdown':$cur['ctype'],$o);}
  out(['ok'=>1]);
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
  $db->prepare("INSERT INTO task_comments(task_id,ws_id,user_id,to_user_id,kind,body,created_at) VALUES(?,?,?,?,?,?,NOW())")->execute([$tid,$W,$me['id'],$to?:null,$kind,$body]);if($to)push_q($to,($kind==='instruction'?'Instruction from ':'Message from ').($me['name']??''),$body);
  $nid=(int)$db->lastInsertId();
  $db->prepare("INSERT INTO comment_reads(user_id,task_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$me['id'],$tid,$nid]);
  out(['ok'=>1]);
case 'cm_del':
  need_ws();$id=(int)($in['id']??0);$q=$db->prepare("SELECT user_id FROM task_comments WHERE id=? AND ws_id=?");$q->execute([$id,$W]);$o=$q->fetchColumn();
  if($o===false)out(['error'=>'Message not found']);
  if((int)$o!==(int)$me['id']&&$me['role']!=='admin')out(['error'=>'You can only delete your own messages']);
  $db->prepare("DELETE FROM task_comments WHERE id=?")->execute([$id]);out(['ok'=>1]);
case 'notices':
  notices_ddl($db);$u=(int)$me['id'];
  $q=$db->prepare("SELECT id,kind,title,body,ws_id,task_id,act,created_at,read_at IS NULL AS unread FROM notices WHERE user_id=? ORDER BY id DESC LIMIT 40");$q->execute([$u]);
  $c=$db->prepare("SELECT COUNT(*) FROM notices WHERE user_id=? AND read_at IS NULL");$c->execute([$u]);
  out(['items'=>$q->fetchAll(),'unread'=>(int)$c->fetchColumn(),'comments'=>inbox_sql($db,true)]);
case 'notice_read':
  notices_ddl($db);$id=(int)($in['id']??0);
  if($id)$db->prepare("UPDATE notices SET read_at=NOW() WHERE id=? AND user_id=? AND read_at IS NULL")->execute([$id,$me['id']]);
  else $db->prepare("UPDATE notices SET read_at=NOW() WHERE user_id=? AND read_at IS NULL")->execute([$me['id']]);
  out(['ok'=>1]);
case 'notice_del':
  notices_ddl($db);$id=(int)($in['id']??0);
  if($id)$db->prepare("DELETE FROM notices WHERE id=? AND user_id=?")->execute([$id,$me['id']]);
  else $db->prepare("DELETE FROM notices WHERE user_id=?")->execute([$me['id']]);
  out(['ok'=>1]);
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
  $em=trim((string)($in['email']??''));if($em!==''&&!filter_var($em,FILTER_VALIDATE_EMAIL))out(['error'=>'That email address does not look right']);
  try{$db->prepare("INSERT INTO users(name,username,email,pass_hash,role) VALUES(?,?,?,?,?)")->execute([mb_substr($n,0,100),$u,mb_substr($em,0,190),password_hash($p,PASSWORD_DEFAULT),$r]);}
  catch(PDOException $e){out(['error'=>'That username is already taken']);}
  $nid=(int)$db->lastInsertId();
  out(['ok'=>1]);
case 'user_edit':
  admin();$id=(int)($in['id']??0);$n=mb_substr(trim((string)($in['name']??'')),0,100);$u=trim((string)($in['username']??''));$em=trim((string)($in['email']??''));
  if($n==='')out(['error'=>'Enter a name']);
  if(!preg_match('/^[A-Za-z0-9_.-]{3,40}$/',$u))out(['error'=>'Username must be 3-40 letters, numbers, dots, dashes or underscores']);
  if($em!==''&&!filter_var($em,FILTER_VALIDATE_EMAIL))out(['error'=>'That email address does not look right']);
  $q=$db->prepare("SELECT COUNT(*) FROM users WHERE id=?");$q->execute([$id]);if(!(int)$q->fetchColumn())out(['error'=>'User not found']);
  $q=$db->prepare("SELECT COUNT(*) FROM users WHERE username=? AND id<>?");$q->execute([$u,$id]);if((int)$q->fetchColumn())out(['error'=>'That username is already used by another person']);
  try{$db->prepare("UPDATE users SET name=?,username=?,email=? WHERE id=?")->execute([$n,$u,mb_substr($em,0,190),$id]);}
  catch(PDOException $e){$m=$e->getMessage();out(['error'=>stripos($m,'duplicate')!==false?('Could not save, a value is already in use ('.preg_replace('/^.*Duplicate entry (.*?) for key.*$/s','$1',$m).')'):('Could not save: '.preg_replace('/^SQLSTATE\[[^\]]*\]:?\s*/','',$m))]);}
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
  $db->prepare("UPDATE tasks SET assigned_to=NULL WHERE assigned_to=?")->execute([$id]);$db->prepare("DELETE FROM task_assignees WHERE user_id=?")->execute([$id]);
  $db->prepare("DELETE FROM ws_users WHERE user_id=?")->execute([$id]);
  try{$db->prepare("DELETE FROM remember_tokens WHERE user_id=?")->execute([$id]);}catch(Throwable $e){}
  $db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);out(['ok'=>1]);
}
out(['error'=>'unknown action']);

// ---- One-time import of the team photos used on the public website (matched by first name; only users without a photo) ----
function photo_seed_data(){return [
'aslam'=>'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',
'hemayat'=>'/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAQDAwMDAgQDAwMEBAQFBgoGBgUFBgwICQcKDgwPDg4MDQ0PERYTDxAVEQ0NExoTFRcYGRkZDxIbHRsYHRYYGRj/2wBDAQQEBAYFBgsGBgsYEA0QGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBj/wAARCADwAPADASIAAhEBAxEB/8QAHQAAAgMBAQEBAQAAAAAAAAAABQYDBAcIAgEACf/EAEQQAAIBAwIEBAMFBgMHAwUBAAECAwAEEQUhBhIxQRMiUWEHcYEUMlKRoRUjQrHB8GLR8QgWM0NyouEXJIIlRWOSs8L/xAAcAQABBQEBAQAAAAAAAAAAAAAEAQIDBQYHAAj/xAAyEQACAgIBAwIEBQMEAwAAAAAAAQIDBBESBSExQVEGExQiMkJhcbEHgaFSkdHhssHw/9oADAMBAAIRAxEAPwDHom8tSc9U438tSc5r7BjA4g4dyyHr0JKqc9fvEqTgN+WXfHYdDXoXTDvVDxK+eLSfLQ5RkvDCaXrK2d6ZNE1XMgUtST4lW9OujDeI2dicGh8jFjODQVj3zrkt+DZLdEurcbA5FCdS0bctyVd4euVlt037VZ4k1GHT7FUAYyynkBTGU9Tv1O/SsHndSh0xStteor/7S/U1uLivKahFbbEoaM91q8FgnLCZgSJpfLGO27HbrWj8D6d9lu7WCZ7QXmwNnOVVsjsrAHuMnPbvis7s9d0DRZZ2YpfJM2Sl7OY3Rh0ZeVTjuNjv3qS2+IdlfarH+xtGvI+TJkxMiqyjvzHBKj5Vxrr/AMSZfWLN2vUF4j6L/l/r/wCjcYHTacKGoL7vV+r/AOjoSfW7uC8MMd4NIRYz4tzyrIoJGwUZAOD6dsVVj4guLWOS6utROochKyPbNGMEfxcuc9D0IpCHFGg2nCpYaBqUkvIMiRUeNj1AXBIPz2rDOLOP7q71VpLG6FiUbmECr4RU+oIwfrms/wAOXYM5cTpbV/ixDbSiOG5hmijXnNzZgNJDgEnxom35dtyuRS1qPHvEl3GkuijTtYVkLmK3w7gH+JY36jG/l7VzNPxtPdWnJqmRNEQyT5bxGPr4gOAfoKL6Lx9bx2bjxStwG51ZCvK+4/hxjnxn065G/X3y2hfmRfg0m+4q0t9WF5qttqHD1y7FftloWSCRsY8yuML7iqOq6u8+jfap7xNXB8ssUaZdFByebJ+6cdVz9O4u/wCIoOJ7J7ZdWksJgFQ292uY5OYgZcYIIHrgHG1Ztf3eoaNqj2ssAtJoGMZVDlD229O23cUqgNczTdO14RW9tfaXcmVrUKqxgYkCZOVZT94fI9x0xRv/ANTdetJpZtO1a6hkGGEcoyHUDqc/L556771ilrrBaMx27/ZZlJY8jeUn032wc9PXpUo1ETBbhHMsvL++hVsHPqpPr2P0qRQ2N5m03PxA0viaUT6zYRePyANIjiGTOdz6MM5/vei9jpVk9ut9p+pW13AW5SoyskZ/xKe3uCaxi1sb23sHuZ5xHE5ANu4Mc0IO4fDgcy5xupPQ+oNF9Avb6wvBcQuj5A5o0yVcHbI9e249a1HRPijM6a1U5uVXs++v29v4K3N6bRlJy46n7/8APub3p9v4aDar8rhY6AaVr9vdWEUyeGARlog3mQAb7Hrjr7irt7eosPMrAgjII7iulY2RHNStre0zLXUyobhMGavdqkbb1mWtXX2i8Kg5Apg4g1Q+ZVbelBgWYs25O9bXpmN8uPJmS6tlcn8tEHKBVW4kCKd6tTuEWl/UbzGQDVhbYorbK/HqdkinqF3nIBpdu5ObNXJ5S7E5odcHJrGdXyecWanEpUNI0dITybV8aNhVyHBUVKYwwreRs0ZJ26fcEkkGvPPV+W1B6CqMtu6HpRMJxkEQnGR55/evnOPWoiSDivPNU3Em4FgPUkbkNtVUNRO2tIxYDUZLtBGjEMiglwRg9PQ+vzoLqGZRg0SvyJcYr1J8fDsybFVUttjXp3F66JYp4lmZnCkkqCAPmc9j/XrSRqfHVnr99ctc6W7QhsKYLl06dSc82fr8ulUtR1Sym1oy+NdQXRbnR45SoOcYwQT/AHmg0kmoahfSAysgkzzJGPvd98dfcmvlnr/Wbeq5MrLH9u3peiR13p+HDDqUILv6v1ZoHCtrw1rt9yXbX8kT/dhaEEL7YQA+u+/0rSbH4V6AySTWtvNFEpBEkExKAjuARt7g1m3CWnyadHFexCO0bcAFcux9QB6+4q9JqmrW19LcNd6jcSONnmHgJgn7uRv+dUG0g1pyDXEdjpWjxm2s76aWZXbmC5jcDt08vf0rG9fnmuZmEzxyICcBt2GfcDGa1CefTruwY3b6sZW3Z5IAfDbuPv7j360Dm0W1uLjntlM8fdvsbDc/Nt6RTUXs865SMnbT5xA08FxI0aHDgRM6IfQkf1odJbSeIXEb8w6vF3+hrV34O52MkGkyrJvnkblGPQqVO1UJOBngwf2PfRBtwwbbH02p/wBRD3GfTT9hIh1q9M8TTzhpY0CAyAqZAOgPqcbZz2FE5tXgvrZEnjmllClQ7OBt+HGCTj/Taj83BV/OCsFpKqMR/wASMDcfL61Uk+HOtwJ4phZA2c/5/OmO+v3F+ns9hTmliZgrxPCQOUknoPY968Q3WoWVwZLa63QFj4bAMPcEfOjOoaDq9okcd7A58IcqSAZ8uehHpnP5mgsUEMbSu4CjkKg/hLeX8tyanhNSXYhnFx7MOWvEd3qAjW9nklUER87zcxAA9+2M0z6dqpe1nNsyc8cvi9QrGMgjy7ZwNtu2azaCO4s2xNDjmTK5A5Tvsfnnv86YNLvVuYI5EIiukbZIiBt/TodqWSEjL0Nj4U16ZJlJuLaRYPKY5HUb+g5u2D7e1PAgv5dMmCrEsMKl428ZSCmx5c5+8Mnb0rE9E1OYI1oRCGk8qlVU539x9MVpGmKkNvBJ4NzG9xD4aMG5AknQMAcjfJGM/wBK1Xwx1W3Es4J9tgPUcWGRB8vJX1G1mE7pcoUdTgqe1Ap3VCQOgotqtwYEWPlZcjbIxt0zSnqF6EU719BY8vs2zj+TBWXcYlbUb0KpANLF1cGRzvXu9vDI5GaHNJk1VZ2VyfFF3h4irjs+u1Upzv8AWrXUVWmXBrL9QbdbLWntJGhwXZAGavxXat1NAIn8tWFkI6V1Z0pmTtx0xkg5JWCk9aunSvFTINLVveFWG9NOk6oj4jkIz60BkQsr+6JBVTBS42AS+0iSIklDj1FB5Ld0PStVFvDdRYIBzQDVOHSCXhXHtTcbqS3xmGyx7K1uPdCKFbmx0rzqYur5ltLcRwKcAYflXJ9sZ/Lail9D+zYmmeBJHyFWNwTzE9MAdfSpNI1WWe5dbqz0x5UAEly1mimFOhw+cdlAJPf3rjv9TuuzuylgVv7IJN/q33/wtf5Og/CeHGOM8iS+6T/wu387ANnodraKJVP2mVhylGBIjz0+ZO/0o5pVncS3P7qBmSM8oEShYyfTO2T7U8cKcOapxLaiVre1ikdiyLbKOY52BJ7epH171tPC/wAL7KwhghjhjeSIY+0SebDHc8o9PfvXFcnOUJcfLOgY2C5rk+yMo4a+HOr6jOlzqkjRIcMkQPMyjt7CtY0v4VWSRR3NzYoxOGHN5sjtkf0rVNI4ds7GFUiTBO7MRux/pRwWqZyQpxQysnZ3bJnGuHZIxe8+F9rM2RbIsZbblXl5fp0olafDfTbeDkW1TA2GVG1as0cHIQrRkg4xncVXeFTzeXvkECmyi/cdGz9DO4+BNPSUsbRC/Tp/e9frrgu0ZWUW69B/D6VobxBAWIBPXfYVWnAbcHPpioJwJ4WtszNeCrKHPNAmUI6DpQ284YtHnlUwqwxsMZFaJqLRBiec4HXFL1xMgnflY4xnpQu2n5C13Xgy/WuBbG9tXRoVyM42AxWAcdfDptMuZWtwMOobmHfrsRXV+qXSxE4AAOTn51knHckd3lXTJ656Zqww7pxl2ZX5tEJx7o5e1G1vFijibmxGoUKR1Azj+ZodDO8F0snNhlPrjIxWh61FbtcSrkbHAYjBpF1OHw7kqcgD23HzrRVz5GashxYe0PX5LK6gMwRlSTmA6sT6fL3rftFubHizQ1S1QGdY/wDglt+cY3X+lc02ChYZpH86KwjA7jOTgn6Vpfw71kaDrkMl3I8drnmWRfvJ67nI6+tTV2yqlyiR65LTHLW9IvY5jNcXFlaxP/wzcXCptnoc9DWcapPI07QqQ7BuXyHmyfbHWtY4zu11iyk13Tk8WJUBntoWGJWJ7gqTnG57bbGsdk1C4vbtLbTbSGN5XCLFAoViSdhzda7j0v4itzcZT7a/21+5g8vo1OLbqO9/77BEzMJWVgQwOCD2rwu53ovrujNpE1tFM5NxNF40ik55cscfpjrQoDFGQsVsVNeGJKLg+L8noDaq8+Mj51MzgCqckmZAM1W9SsjCpjqYOUkhxiby1Lz1Uiby1JzV2BIoJQ7lgSY71ctrxo5BvQwNXoOc9aSUFJaI51KS0zSNB1gSFY3bfsfWnKNI7mEZGaxnTb1oplw2CDWo6DqSz2yEn51luqYrrfOI/DtcJfKn/YU/iBb/AGS6tHWKJ0CuzJIuQ24/LHXIpDmuPEEItvEbxSoAOG5h/CGPfHy6VonxdhMumadcRScuBIjAdSDy/wB/Wlv4V6GuscWxtJEWitW5j3HcAfzr59+NLddQtsl7L/xR1H4fhyxoQj7v+WdW/DLh6HR+CrC28NVlaMO5GxJO/wBOtanYxJGg6DHUYpQ0LkjsokORyINvWmOG7R0yrDI2PtXJue5uTOguGoqKGJGQIrE8vQnbY+tSEhvMOU426daDJdjw8Rkk56dqsLO5XlY4Hqo6UdC3YFOlruWVIjc/dH03qtLPFEGlkkUHp12FeGYjPNI7YHTOKHzSQpzCeLnXOQDvSys0hY17ZcnuFKH94pGNsjtQy5vCGHmTpsfSqd3cvAoKENGd8L1P9+lCb68ATYtzfgYYP50JZY2F11aP2pX8SK3OQxwcUqzX45mYOR171+1S7cHHMScbjIwKXbi5IhOT13IFJCOyST0VNY1N3lKuxHUbH8utJ93C95qPI+MY3oteMZZPEOTjOcnNQ20BN004AU9tulHV/agC37mYx8SuGbi1txfQRIV25wCQf7NZPPMzxtEXfJAzk9cGupeL7WO60mWHl5meMjOxOeoxXN+t6fFFO0ka4BY/z/Q1c4dvKOmUWdUoy2inp03hc4klBUjc4zjB2NG2u7ibkmV8KigsynBHyG1LSTKrDcjf0/mKNWojMM64ZA6FcAZOfbfpRbAUaTwxxE6yvFJh0dSjy8xPMOoKjbbbcelXuIeK7rQLlbLR9G0ix8RFd7qO2BklHcZ/hGQQQMHbekXh53sjC3KxKYGD13wBkntnainGVyXv7VWlDsIiWCgAAljuMbb1rPhCMLMr5Vi3HTevQr+rTlGjlF6YDvbye+vZbu6k8SaVuZmxj9O1VHkCivEku3Wqck2T1rpuRkwqjpGVjBye2SSzdd6rq2XzmoWkya+o1Y7qudyi1ss8Oj70x1jO1Sc1QRnavea+hkzKNdyQNXoNUINegadsY4lmOQo4Ipx0DUzEVy3l70jhqKaZdchwTQmXUrIaYJk1vXKPlDrx/OtzwML1AGNu4ZiTgcp2x+ZFM3+z1oDNwxc6nIMvPNtnso2wKzDiG6afhSS1ZyY2kQsvrg5xXVHwe4eOm/DzTo/DwXhWUjHdhn+tfMf9VKo42YoR8uK/l/8AR1H4BslfQ5z9G/4Q52cDBQCDkgY9qLWsDJLvjPUbdKu2enlTgrj6Vdii5JTsN+1chjW29s6NK1LsiutnKzBy2D6etWGjZUGGzt0zvVoOQ3IMHI6E4xUN0WjjAAjLHoVPf3o2MEl2BXNt6ZCVBi5iMepqnLal913HXaiMTxmDkckk9q+SMrMEQBEUb+5p7gmhqm0xalgljuC0aHyg/MUA1WKVkdxzEnuTTzLBEpZzzbJjA3BPvS9ewI0JckkHPlFDWV6C6rdmaX8UkYJGT6c3+VAbwuivyEqSMk96dtUtbqZmKRnHTbuaXbzT5IccyHJPQivQQ6b2LLxfuhzAL6DqajUKtuW5iCDsCetGJbcREsxAXG53/Sq91aKYT4ZO4yRREWCzQsamgkjOR97pnoKwnjnSja6tJIEPIw5sL05vX+/Sug7u3/dujc2RuKxzj20lDPLGTyEYZG2AqwxJaloqs2O47Mf2jkBK82PfvRqzDxwJKVZWI6AbD51QlASboCB6b5oi5lMcSggFwfID1/varYpgtos0818YxLnnILHHXcDH0rzr+oxXeuTNAHESfuk5+pC7ZqbRk+yaPdT+JEpgTmQyqSrDOQMDr1paeYsxY9Sc7bVrvhhKnne/PgqeqyclGv8AuTSTe9VnlqN5M96iLVc5Wc2V1dJLzb1IjZaoY0ZjVyOHcVQZKssWyxocYPQ2xnaveahQ+WvpbevqBTMO49yUNXoGoA1ew1LzEcSXNTQOVfNVg29Sp1GKbKXYjlHsHLCCbVb+10yEc0lzPHCg92YD+td4aSdP4f06KOUoqoojVR1OBjauY/gR8O73V9bg42vDEmmabc4jVjlp5lHQD0UlSTWuavFeaxqFzdHVJYIVflijRsDy+v8ArXzJ/VfLx8nq0aqZbcI6l++29ful5OmfBWLZRhSnNdpPt+2jY4tb094w3iog9WIGKr3eoWMa8/2yJR1xzCufNaPFEeUsp4DnZpE8vMvv2zSd/vFxXp90kMlxcEL/AAuObb2/qK5pwUl2ZrU3F+Dqg6xazKDHOjAnt1r4LyN5Tl+o271z/acVX81t4hlMT9u2+3QdulaDoHE7XmmRyS4WYbMG7UNP7Q2uKkh/kvRHHzcpwBsR3qKPVIo7gySS8u3r0pffVQYPNKGDb1n+tcQzxaswMwe37IRg57j3/wAxSKTb7CutJPZrU+rWzJ+8ukjQdy1V57/SgxLXUTHl6g52rANW4q1FFYWyFygCBQfvAZI/n+lLsWrcS3140FteleYjmc5Crt023I/vvRUKlLyBzt49kdIXGraQsZeG4hkCjJ5Tkmk+91/SLm8ZHlQ4OAObp8wdxWa22j68IDJPqBmbYKM4AHXPzqHVdCvGtB40stxIBkMWyR7DfanuiHuMV0/Oh/uJLadRyqWUtgMO+KpGI+E3iDlx2pC03XNe0Ypa3cLvblskO3MB779KcjfpeWYljJGd9/zqGVTiyaNymgdqVo88DuhJwPXesp40sZ5LWXKcp6En+dbPkSsU25QOtJ/GGktc6ZJlQJBucD0qWifGSBsmvlF6OXbq2MVyUYKcelW0gzElyfvKQo/PbH0zmruuxLDrUiFOUg4btmqEzkiCGEeZieUHY7irpPsZ9rT0EbiRTw/cp4ioF5Ry5PnP970sua2jhv4Wf728NG1F6NPu8c0Zli5o3cjbmIOQO3Q9fass1/hvV+G+IrnRdatHtru3blZTuGHZlPRlI3BFaHpGVGcHTB/d7AHUMacGrJrsBcknAqeG3Zzkip4bTzZIorb2mANq1WJ0qdz3PwUd+WoLUSrDagLuKl8LlcYootrhc4qvLCefbtVzk4dVNT7FfVe7LEkXFkHLXwyVTEr4+6a9h5CNkNb1/EeMl+JEH0ti/KywJK9CWqv738Br4DL+A1A/ijGT/EhfpZv8rLwlGamjmAYEjON8etDOaX8BqxGJmXpj60j+KcX/AFIY8Kx+Is7R+D1pa2Xw3/8ApzMIHjW4kQOxVZZMsQoP4VCDPc5pG+JnEl/oUDR2HPGzbhkwcZ67etaP8Eo4J/g7pt0iMouWaTztk+XEYH/YaqfEjgCPWb9buQhEUHygffOO9fKnV8x39Uvsse9yf8nZcCnjh1xh27IwtdL4xvvh7ecW/blxBAZ1hkeSRmG2/UDHfYdqzybiviC5gtpZLZ+chxMQDEAQcggjoOUgb53BrpjRVm0fT2sGtueAKVKncYJ6Y6Y60tX3A3ClxqH2mbTLxYckiATBYs9R5d9vamrJqS1o9LBvb2mZfa6txTY2dtPczTLFPGJFjuDzYU9PN/Q1rPwpu9S1zX3tLhJAEQSggZyM46UOnsA16qW6XEhb92qAjlPYjpitr+D3Aq6MkuqTW3gzTgRRwhshF67Z9TQtrhNdl5C4RnV3bLmrcPTRWHixeKoIzhlxmsU4x1I6WWa7HMy/dHWuteIo0W1Fvy4AQAbVzp8ReDG1aBxaeaRST/pUTgq56fgkhbKyrfqYZd8Wajcl1hIiHcv5VUe/qfap7eTiC34bu+I4YtQuLO1RpJJ0Igj26gEjJpt0fgq3triS7ltVkuGTleO4jEiIfVQeh6HNOlvf2lpw/NpOqWiPYyxMkqLExBBGO2R37UZXOCen4A7KbJR3Hyc93nxP1aVLb7O8gEycwUXcisjAkFCTsSMA5/xCjlhxpxLZafaanf8ANLY3CB45JzzDrjHOo2O3cUbl+GmjSXSiHUILnT1bmWNoGSbGRsTjGe2fareu6Ik2lRafaokdhAoWOMbYA6dd6JlOprSA405EXuTCdhxBp+u2u6hXGzRsN1P9/nV2xPhSMqFuQdFApHsNDns2V7OXGBylfUDtT/oltK0SNJGQTg4IoO1qPgMrTfkvWZdizsGX0AOc1+1W2NxalZQCCvbvREQOrc4Ug9hnNQ3o8SFSCQcH60OpbZPKPY5d+IdotlrrDlOSx2PpnY1U4O0mfiHjnT4Y150VhsB0A3yfamr4pWDScTQlFzny+uST/Om/gnhy20rT4pI+QTv5mkXZyu23sM5+dW8reNSfqyiqx3ZkNeiZqGnQw6TaC2tgA2MGUbZPt7Ui/GbQ7XiH4crxEQG1PSJAjMoyzQM2GB9gxDD5tTW9w6W7AqSAMg1+kNte8I8QNen/ANsLCYyZ2/5bf+KD6bmSw82q5ejW/wBV6/4NFm4MMrBshL2/z6HJ6KoI2q7G6qBvVRoJVQEnBxvX5AQRzEmu5S61RSuzONvAts9AuhZ49th614ZQNq+QTjkxUhHP0FZrqfXJXrin2LPC6YqXt+QLHKSdzV6Jxjel9LrB61Zjvsd6ysb7Pc0M+LD4KkV+8tBhqA/FXr9oD8VSfPl7g/FBU8tTxMBjJoH+0Ads1Ziu8rkmmu6XuKoo7/8AgvaCH4NcLxYwDYLIQfVizf1p71/TEnsVKIHKjpnrSb8L5gnwj4ZdT/8Aa7cbdPuCnlbsSx+GPqf6VjMhr5k2/LbNjjp/Lg17Iy+/0q2jcu7TQ5OWGcYNA/slteX32HS7SS+uQM7NlR7seg+ta7d6Db6i/Nc24ZCdg29WLfTLDTLXw7a3iiQdQq4/1qOPLyHKyKWhM4d4IjsZUu9R8Ke6P4R5IvZc/qa1TQ9OxyIo5VGDQbS4Zr655/DIhB++RjmPt607Wax29iZTtkYFE4lfOXJlfnXOK4ryK/EwkSV3QFtjgE5wKzm5kSRiWHIozkcvatK12ZG8SUtzbZ69ayq71J1kuibKQxKxOy9qbd+Jk2LvgiGfRYrxWkhjMM/3o2/z9qW7q1SBmjvAbW4B3YDKsfU+tO2hXEd9hkbKPsd84opqOgw30WJ4VkUD+Ib/AOlRreuwT2T0zHZ7O5MnNbfZpcDIdDyneh8/DOoXmHnHJnqAc/rWqWvCNnbXBlXxQScYboD6Ci76dbhcSFcLvg9qcrGvBHNJ9mZHacHvburOhB6Fe31oilitpzEkDk6nG1OV1c2ccwXnBbGcLvjeg96S/OwjBTG4Pak5OXkbwUQFdShFHlIA26UJkuzIpcds7A9MVbvZl8Bl5mA5iAR6UGncW1uyuylQOYtT4IiskZXxri54g5wqsIyGOP5VZ0vVrq11R7k4VJDnkHRR2A+QqvqkyXOunkYI0snQHON9v5dK86pyWty8CFQUblHL3o+xbikB4OvmSZp+lX8eq6dI2VOFOcGl7jTUjpnwdnhRgJNVuVtxvv4Y8zY9fuqPrU3CSG04WvbhUK5UjPqzbD9TST8XNUSLXNM4ejO2n2geXf8A5kuG/RQv50PjV8r4v27hufkfKxZxX5uxn0iBiajW2DHpXw3AJ61btWDEVo3dIx3BElvp+TnFFItOAA2qeziBA2oskI5elRSm2KkvQxERuegNevs8voaMR26gDarKQpy9BUbu0EKjYv8Agze9fvCmB70w+BH6V9FvH6Cm/UDvpv1F8RTZ6GiNvDJtkE0SW3jz0FW4Yo1xgCk+oF+mO6vhMDJ8EeEw7En9mQ5P/wAcVpdpbxxQjbzdSazH4LzpL8GOG3XOFslQj/pJB/lWjNcFYkKbN6VnrWvmSb9zSUpuuKXsi9JdLGuMAD2PShc2qWa3ojupFWNVLOCeoH+tVLu9KKzs4TlXfff86TbjT7riCS5I8WGLkIE5OMHqMetQzsfoF11LTbH+PjjTDdeDDKmM8oC9hRGbi21ktAGflHXr94VxOZuK9F4nnvrW9nTllPiQXEvPHKB7dtuhGK0Ww+JVrdWhiuJWtplXzQu2D9D3FEr5sV2e0DONDemtaNp1Xi+xbKq4xnfFfbHU9MbR3ICkON1JBzXJPEnxP4qnvZrbQbfTYkViFebMrNv1OCAPlV7hb4kcVW6LbcWQWJiY4E1qChHzGSPyqR0Wa5M8r6m+KOg9IubbT+N5LG25Vim/eouc4PcVoxHNErswyx2x0/8ANcxaLxONQ+JGlPp5Z7dXIllI2ORjA9a6Ksp08BHZpCy58gPX6VElwemS2akuSLNwihAyjp0oRexQY53HmwV9zV+4ustvkDpjOTQy5mLSHZT339aSWmNimK15YxLeMVQg8hwR0qjMSkRjZAWbGaM6kySRFVyWJ9f72oLNKTGXDdOv8q9FaFlL3FnWo2hgPgKcZ6Cs/wCKtWEeneHC3NK/lPmxjfBp41q7hSwcu+ADnc96xDW75LzWwEkJ5sknP6f360ZRXt7ZV5VulpEGnEXOvorvIqiZSWON29j2zTzpOhaeLA3WoRxl1Zid9+tLHDMMQuEluR4isSAXGOhO5PsPTvTVYw3dq6207lkdedT+Jf8AOiLWQ4iYyafdw3sqQeCLbSrIGebHVgoySfkAcCuaeItXuNf4p1HW5s895cNNj8Kk+VfouB9K6S1wTw/CPiO405FS5FngyEf8osFk+vKTg/OubzagbYp+DXrlIb1a7k41rwkCAZOcdaNacrFhmoo7PmfpRzT7PBG1HMp2F9PiPKNqMLH5OlRWNvhRtRMQ4XpTWeRh8l0IxvUX7TRdiapX7EA0Gd2ydzSKtSQRO1xYzftWP1r6NVjHelTnb1NfQ756mvfIQ36ljcurR/iq1HqkeAeakxWf1NWo2fHU15Y6F+pZ3p8A9bjufgrpivIR9mLod9gDIxFa5JdF4RyFW5umDXInwH4pk07gKbSOZvFeZpIQ3TckHH866H0TXYpbOKNWOWPKWPVj6/oTVBmVOFkjR4NynXHYyzoCnNcbeitg/Wg3FHF9joGimztWaW+dfLCoy2COp9qXuM+LW06NLOwZTdSuUBb7oAGSfoKTODUttev7niHU44vCjm8NQqBOcAY5vf1z71HTStc5+Ce69uSrh5Mi16TWJL2W5ufElM/73DKBgEkjf1wMY+dXdPugNPure9skLWwDvK+5yceXJ+ddCRtwzFGjQ2lr5CoB5AxyowM569ah1PhTgbiCd9ZuomgaSNUmt7bCxy4IIPt0o1ZEfDQO+n2/iT2cr6zdyM3i2MRtpY252QHysmNmBHbO1X+Fda+23kdrdRK7DpzYxjvze/Wtl1f4e8NXs1vHpFuLOGJeTliJYcvNnG/XcmlK++G0mn6lNdWPJIgYlVC4KnGxwPfFERvrktAlmHfVLl5HbhK3tbmaGWNFUcylSN8jqD+lbtHKnIrg/eUEY2I+veuOtG4n1XhfXIoi04tJOYsmclDkZxn0I6f51uWg8eQajaW85LGLHISM5yM9u39+lB30SX3IKx8qMlxfZmmzXAMiglzjcemfSqNzOqoxDDfbYUKttbFxAZI2CHOAuem1D7zVitu8hblKsVOO/vQqTYa5JIkvrvkDkPgLv6mgV/eotk8hKkOcjPfal2/4guXhzBiXAPMy+uT26nale/4pEcUYyfDwBjOQMbfrtj60VCpsBtyEirxZfSpfXERblideYMdxkD/Tas40+P7VrypGow0RLZ2we/8AOmDX71r2cvE55U8nTtjr8+lANFcrfvLlvKw5SD97bp8qsILUSoslykNemW88v2a2j5W5vLyqBkk7kfXNavZcPPqSQssbnkXlGB0rLODLoJrSSSjLI22egO+Bj2z1ot8ToOKrW1h4h0zU7uHSgRZTra3DJySbsCwB6NuAfUY9KidbnNR2EVZCpg5a2H/irrem8OcEXHCVvcJNq2ohVniQ5NvCGDEv6MxAAXrjJ9KwcQ8wzivqh5JC7szMxyzMckn1J71eigJHSjqqlXHSK3Ivd0+bK8FqOYbUasrbBG1eba0JIwKP2OnO2PLUjICW2hAUVbKDkq5DpkvKPKakfTpuTPKaazxzBqHegj/eNGtQPWgr/eNSQ8D7fxHmvq9a+V9HWnkRYiGTRGCIHFDYWw1FrZgQKnqjsim9Dxwbqc2nBPBlxJFJlFBxkHB/nmujuCddtrkh0kyB5VTuuxwB89965Y0mRY7xHMnJy7g49N61HgfiHwdbktkndImK8re/Lvj3OarOp43faLXpuTrsxv4uu5puMmKXEhikhCGQDYhvMzD0zy/pVc6xc6Xbx2VjY3Vykjc0aQIWAJPT2r3q95b20jXN2oLpCirscEA5H6N+Qp24KudOl05JUgiV1AVlHmGOo8361USlxguxcUfdb5BVvwr8QdXkWS3jsbCPAJV5fEb6gCiGqcIfEzQtKZjaWeoJIQALeQxtnHYb7+taTps9sp5oLoZ/iBI9f500ftu1SKPHhsyDB3+760yElL8RoNcIpwWzniLTfirb6f40HDUMeeniXGDv16CvK6xxTaRsNU4du45OvNGyyDI9MHNbXxBxNLJp5toDkei9/rWd3qXMtwWmuU26ouWPT8qfuLfYjnXuO5dmZZxBrHDb2DpdiWzuJHyDcRkDm79Rt+dUNFkvbFTNbTsIJCAr5PkbsT+lPWqcNaLdyFryP7UxwVjfoh+XrjNJd6tjpS3CRqF3OEXouD1A70VCaa4mcyIcZcjROHtXmuOHkK5WSNmWRWfGACD29M/lX7XNYubU/boeYqfI6ZyT7/T9elLPB94klncvDInhuRIU6DJGDt6HA/OqWuapPaePa3aGaDmblYdVPcHH0NR/K+49877D9Brkb6rNb82I5YedcDoR2B+f9KXtduQ00vhOqqOVXAbPPhtz+YzVRJ15kukbzRA4wOqnBPz7/wA6ISWAuQZYpAvh+d+f+LJz+W/8qIUVHuCym5diveQlNFjnBImU4kBGM9wSPX/Ogtp/7SZxJsQ2QD2HcbflTTxHd2sdv9mVgpKAkE9cjb5n3pBubotNyBiu/Lt3qWtckQWtRY36FcG1uHvSh5AwbAIwQO31rob4ccJf75fBzXNP1TCjXYW8FnH/AAyMGJ/o4DfKsM+HvDc/GGupZFHXToWD3MinHQ7ID6n+VdfaMsem2lvDAiqqgKFUYAUUTXR+YFtuWuJw0+lXVlqE1jewNDc28jRSxMMFHU4YfQg1dgtugxW3/H/g5LPi2241sIsWesri4KjZblBvn/rUBvmGrJIYfN0p7ItlvTbAO48tPWk6MGVTy0A0SEeKuRWmaPAnIuwqKT0PitkNvoSkDyVLLoKiM+Sm60tkKjarctmhjOBUXIk4n82r/oaDP940Yv8AvQd/vUXDwR2eTzX6v1fqeRntTg0QtZcEUOFWIWIINS1y0yOa2MdrMQwIPTemSwvJIOWUArkZBBwTuP8AIj86T7WToc0Xju5VtgI2PlYNipciHzIjaLOEjS59c+1W3gXSh3fk8q9TnPU9sZ/SmnSdTj06GO38QvDJB27sCQfnWN/byZElhyiR7Zbq2Pb17Ypp4d11Z7xIpeZ28XmYHYIvc/LAxVJZjaRcVZPcbdZ4u1uCeKewu5IlbchdyBtkfTIoL/6p8awXvK92eXYeZQfbr9RT9ZcM295p7XEv3p3PhKRgogO2fcnc/Kgt98M5m0y3VSrPJN5ynYAE9T60PF1rtJBrd3mEn/uBofirxyZjAY7SYqOY/u9wCfajltxbr2qWgW+UxvnJ5Ry7A96s3vAaQaorBWWVGMMnKebytjkOPb19qsRaXDCAfH51x4TEkEjAzv8AQZpWoflQ5WXeJyb/ALkEWp4gQzEmbHKS56HHl/Ppn1pS1nXba/Z0LBbhc8rIMEHGd/6+or5rlw2nX/g3A8SMDkkCHB5NirqfkRt6ikq4aSLUZJ2nL8zA86jY+n1xU1VK8gN178DxwzxAsc0RaIItywQr6Y2/oPr86+6zqU8OoXHK3NGwWRdu42JHsRSPFdStIYIWwysZMjucbEenyq42sS3Fn4jr5ozzMebrnYn9P7zUjq77Io3dtBnSrdLniD93gR8okRCcA4PT9SKJtdiK9e2hCl2bYMd9vX8hSfYX6faftEeceCVb0PpXq91doAssJKuqHzJ3JxmvOtt6PfNSP2sao93q92Q+8ZCqPxDp+lD9KtbvV9UitLSMyzO4ijRAT170OkleXUCqKWmk8qrnJJwBXSfwW+G/7GsV1vWLYfbbheVISM8in+RNGVU77IDtt13Zpvw94RteGuGba2SARzNGHuX7s1PSuWUEbc2w9hVeGLlgCOdgOaQnv7VIS3fId/8AtWjWkuyBNv1CH7J0fi/hi74U13nFncsvK8ZAeJlOQ6Z2BB/TI71jfGv+z9xTwoJdQ0V/94NLTLF7eMrcRL6vF3HuufkK1a2uGhu1mjOP4Yx61qWkaml1pq+KG/d4BkTqvv8AKhLY8XtE9b2jiHSI8ONqf9Kk5FXJrcuNvhJonE5fVNPWHT9WbzfaYVxHcH/8qjv/AIhv65rEr7SdT4e1V9N1a1e2uI/4W3DDsynoyn1FCWE9f6jVYzgqN6JGVTH9KTrW9KgDmogdTATdu1QomP55X3eg7daN3cLNnAoc1pKT900dFrRDZF7KdfcVbFlL+E17Wxk/DTuSGKLKiqamVTVtLF/w1Mtk/wCGlUkedbIoHK1dSciovsbjtUi28nTBqaNvbRG6WX4Ha4DSeLylcZJ/vvRfTblbO7BbmbB5V5SANtwT6nm9aE6YJbfUE5YmlWQiN4lGS4JxgD19Penbjj4fa9wHrh03XrGezleMTxBwW51YDYEbZGcH0IIqCzTJIbRpHBvEZbThHKrtOqpIzuc5yeg+mN60Gz1qBorG6j5eV5RzZwAufL/SuaOHNclt9ThSdiISPCbO23v8tqbm4n5LFYY2kCiYyqRvg5J5cem1VluP93YtaMrUdM2HVNSjGrz3UDArJDyHJzysvX3xkn8qQrrXDJctclgFm8snhjr0Kk5+oz8qU7vii7uHhRXYMCcddhktg+2c4pf1LVZ1M0UCDMhB5hsex29BSwoa7MS3JT7o/azqMl9K9sZS3KzCCUjOR15SfnS1C1zJFyjzAAgp7f8AirjDxIo5AyoSrO2/yx9arY8isrMshGAoO9GxWlorpPb2ySO6eO5ilRQ0h3O3f+/617mmCPLHEOUuO+2w70NEuGD4bYsM9K/S3JWLnmOxweUnel0IXo7j7FDykFsgls9gdtqG3+pZxKSWJIKrneh91qTuc9SxyK034MfDW64p19eINUtibC2b9zGw2kcHrjuB/OnwjtjJy0h5+D3wxBSHX9dt+a9lUNHEw/4KH/8A0a6W0mx8JA/JgAYQV40HhxLK2RGADsPN7CmJI1QmQL5V8qAdzRvJRWkCqLb2yLk5RyMchfM5/pURDM2dw0n6Crhj/wCWTknzuarSc2OZchpDhfYUzkO0e7ZEMhfAwnlQeppp4c1EWmrS2Q3VYVZ2/wATE/0FKkRWNmkY4iiHU/rTBoElulmsh3ubn96SB1HZfoKis7ofDyOqTeGcxc3IdynYfKotU0nQuI7IWmt6fFdRjdGbZlJ7qw3U1WtpSIxzbDt7Vdj3GOXbuB/MUNomMu4j+C19EHu+D9QW8Tr9iu2CSD2V+jfXFZFrD6rod89hrFhc2Fyv/KuIyhPyz1HuK64RmUjDZ2//AGH+dfNR0vSOI9LbS9e0221G0bok6ZK/I9VPuMU3gheTP5CGBSdwKkSzjYbqKjM6jqRUiXkYGOaoJbD1omWxj/CKkFhH+EV4W9j/ABCpFvU/FUf3Eq4ki2Ef4RUq2EX4RUa3iHvU6XSnuKVchdxPw06M/wAIqQaXH+EVNFOrHqKNaPp17res2mk6XbPdXt3KsEECdZHY4AH97DJpU5CPga3/ALK3wptuJvidJxdqluJdN4eKSxRsuVmu2yYwfZAC/wA+WukfiXwfo3H1lcWGrQIUjJ5LlAPEWQggcpPT1PrtR74e8EWvws+Edtw9CVmvDme7uF6TXDAcxH+EYCr7KPWvOojwbEIfNIx5QD1Z26k/y+lT7ekANpybRwlx18Itc4VurhmtpL3T1PML21XYHP8AEvUYIPtt1rPUzDlhK5d2wR79f6da/oRqFtbvpktqV5o1TDkgZOOx/wDNcw6tw3oGs6res9qqXEUxX935SvcHApkr1D8RJChz/CYvMbt7V5w7FYzy5HlyemMVQIlcM0h5nJxudiK069+HCtGWtNXlQYwVKDB+VLNxwHfwS4GpwkZyAV+7SLJrfqK8S1egrpaSyIJHkUDGQueo/wAqhkMMMGGfL9OYb537UxtwkqGYNdFuyjtXheFYPDwJOb/40v1EPcRYtnsJtzdmQkLGAAMgAe9RJZXt4OREJJxknpinyHhmzhjXEJJHQH1q19hzIkUMJLsQqKi5LE7AAep9KT6lPwPWI1+Jg7gH4b3nFHFlpo9unNJJ5ppAMiGMdT/QepNd0cK8G6Zw3o8FhZW6Rw2yBRgdSP7/ADNB/g/8NIuC+EI5LmAfti8Ae6cjJQ9ox7L/ADzWoR2qqemEj3PuaKq2lt+QO1pvUfAMFo3hhMYkfdj+EV7Eagc2PImygdzRIwNy4xiSTr/hFRNEqt6Rx9Pc1Nsi0DmgbHIfvP5m9h6VWdMlpANz5Eoo6E+To0m59hUBjXmMgXyr5UHqacmNaAGtF49PisYGxJcSrAD6Z3Y/RQxpj0x1ikwVwTun+H/Kg0sQuOKYoD5ltYDI/wD1ucD9FP50Vt1LHy/xHr7V6XjR6K77Ge2nZmC8vNJ/GB3FFoui4bbs3p7Gl+yaSNC/ZcKD3JPai8M3PGBDuCf3gPaoGiVBJM4O22dwO3vVqMb9sn/u/wDNUIpl8TkR+Yjo/Y+1XYXBXocd19PlTdCn8aLmRlBwaHm5lz941euxsaHMu9OjFDrJPZIt1KP4qlW7l9TVdVqZI8mn8ER82i1Hdy+pq7BcTEjeq0FvnqKKWdlNcXEdvbwyTTSMESONSzOx2AAG5J9Keq0NdrLEFy4HXp1Nd3f7Kvwafh7hiL4j8TWpXWdTizpsEq72dsw/4hB6PIPyX/qNKnwI/wBkSVp7biz4r23JGhWW30Dm3JG4NyR//Mf/ACPauvrplKkRsoGMAKMAAdqis4rwOhKT8+AFqZ+03qqz7L5uX2HSlm4ZZb95mBIhUqgboWPf8s0Y1CbwoJJXwrSZOfRRQESn7AZHXJc+X1yf7x9KgaJUxc1zH2OfMvLGqFnfP6VyVBq5tvjXrNvIx+zTqgAz0OWIP9PrXVXFUK/sK5tyTyFCZGHrXItzYy3HxC1VHGLiOJBt2YGgr9JPZYYy29oepTsWX6j1FBdRSGT7yYPopq/YXP23TFZvLKnlcH1FVr2FWTzZNV6fctNdgBLFZRk8yyOfTIqsTGz4itgg6As29W3tR4m5/OvogSMFQN/SpExmgXIZmjKhVA/wjpWyfAH4bPqmsjjPVIC0Fs5SwVxkPL/FJ8l6D3+VI/B3C8nFvG1nowZo4ZGL3EiDJjiUZc/lsPciupTqb6HpMOm6FBDY2lvGIYo0UE8o+dH4lXP7iuzbeK4Lyx3hs1jjBVd8cqD+tSGBQeUfcTdj6mstl404ht+ScaoxjU8p50U59gMVDb/FjVYr5bK4sbW7PNzMBmNgvqSMj9Ks+L8lUalIhUcwHnk2H+EVVdU5cfwR9fnS9Z/ErQrxgLtZrKV9l8QcyfmOlH0mt7iJXt50lgxzc6HIb615CFdlLAgbPJ/2iozyLEWbASPYfOpvMXJGzv09hQviWcWmhS+HseXwl92bb9Bmnpb7DW/UG6GWuxe6gc815OeU+iKMD9P50zWsIUcwHTygUL0K2EWjwlR/CFUUxQRhGA/hjH5mksfc9BdiVl8ONYgfu7n51NIfsltEo2c7nfpmvsMfNKvP0Hnaqd9P414qj1yR7VGh4Ss7qNf3PKAW3OOi0bhIVlQnmlPRh0NLOnwNKzzMep5Qfai8M0kXlQ8ybAD0pGKj/9k=',
'masum'=>'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',
'saddam'=>'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',
'salim'=>'/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAQDAwMDAgQDAwMEBAQFBgoGBgUFBgwICQcKDgwPDg4MDQ0PERYTDxAVEQ0NExoTFRcYGRkZDxIbHRsYHRYYGRj/2wBDAQQEBAYFBgsGBgsYEA0QGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBgYGBj/wAARCADwAPADASIAAhEBAxEB/8QAHQAAAQQDAQEAAAAAAAAAAAAAAQACBAYDBQcICf/EAD0QAAEDAwMCAwYEBAUDBQAAAAEAAgMEBREGEiExQQcTUSJhcYGRoRQVI7EIMkLBQ2KS0eEWJFIzcnOC8P/EABoBAAEFAQAAAAAAAAAAAAAAAAABAgMEBQb/xAAoEQACAgICAgICAgIDAAAAAAAAAQIRAwQSIQUxIkETFCNRFUIycYH/2gAMAwEAAhEDEQA/APSfvRSRCjHC7opIgJaAWBhFJJKAkkkkAFDKSBOBknAQAUlBuN1o7ZQuqaqUAD+Vo5Lj6ALmGsvGul03paa7R0T3jd5cQIwHPwfX0+HcJLA62+SONpMj2tA9ThQLlfbZarZLcKuriZBC0ve4u4AAXgq8+O+vbxLNPPdZIY3OAMcXs7fQDutTWeJWornZn0lZdZ5WSNLCx7sg8YSAezG+PGhpKwQNuLMbg0vOQB81vIfFDSlVMIqS4xSkjJLXZwF85Zq+picZGyHYVZdIagqaCpbOZZHHIcR6c5/sEC0e9J9eW2l17FYJahonmpxNtJ4aOc/sVb6eqhqY98Lw5ucZC+d941ndazxCbXOqpWyyxiMPHZuMABd/8IPFaeCmhsdykknk82Rxmee3GM/MlF0FHpfOEc57qoP1Oycx1dPUxiBzA/rwVYKC6UtdSiWKVjjnBDTnlKmJRsB1R74TGvDujgU7PKUaOQKSSAAkkkgAEcoJyGEABJJFArMaSCcmiiR7YQCcnAJJJJACSQJQJHdACc4AEnsuK+Mvi7Tadtc1js0+66Y8yQt/w2AHqfUnA+qs2v8AxEoNJafu1RLLmoi/9GMdSPLHP1zyvFutr3LctU3CurHnM8vnytznDcZa39zhNbFSN5Z/G/VlVr+K43qtE8LDtDDwxjCducf3UbxR1/DqF1LZ6HIo6WPIBPLnuGS4/VUClom1myWEhrvK34A6kvOPthaOp3DV9ZA+XzGxSuA94Df+Eg4m7WVNC6SORuwO7c5IOPqoVQ+SOJpYMs67j91q7XdPKt7aQn2RMXvHTIHOFtKSV1ZpZnALmnkn0Lh/bH1TvQlpmSGd0jfw73Dkhuf3K2ti2yfi9knGS1rj2wAc/YrWVdH5cW8FuXENcM+oxn7o2Ov/AAVdPTyNz7QY4E8cscD8uiQVInXupfFJS10biJI3NIJPB54/ur5bLq+3XejuNKcF0Yd39Dkrm9wnZVzQUEYD3eYCMc8AK2W2oE9tkaRh1HEWOyPThILVHbrRqWWTTdMJpnvqKWY1GxriA4BuGNPuyXHHqp3h94mT2HVH5bd6lz2VBD3yPP8AK5/J+XC5Zp+8fhLvSNqD+kYd7W9Nzj0B+yjzTRuuc8u4l1PMxufUgD/980gNKj35bq2muNBFWUsofG8ZaQcqeCvPPhpq6vobLb6bcXw73whh6jA3n5crulnukF0oPOicCWna8A9CnJkbjRtAj2TAU4JwgkkkvigKAkl3SQIJBOQwgUxIoduUU0UQTkB6IpUwEkkgeiUAdFVddarpdK6edWSvb5p4ZGTjJ6fuQrSVyHxWtss9b+eTlj6W2wPfsdy3JIGcevf5BNkxUrPNXiZqT84v1XXTVM3tNDJWvJ2gNaOcduc/ZcjvtzZVeXNHKSK0k8npgDP2HHxVp8RLhX19JK+aNsUzychgxkei5dPVeXPiUECPOxvp7IDf7pEh90Tm6iFPUMgp8NYzJc7129B8OPutUy6H8wdUOBLnR8n345Wr3/q7seqaM54UlEVh3Oa44PVbW1XR1Mx1O7lji0e8YcD/AGWq2OAztOE0deEVYejdyXuXNQzJIc4Y59HAj9lFqa981W+pa7Bf1HvxhQAx7jw0qRHQ1UgIETs8cEJHS9iq2So7nJ+dw1Z9rYQGj3K7Wu9R0r7s1zwWzNLQOuSGZ/dc+fR1McgBid9Fu2W+uFGZWxuDwzGCO5GOfqmyaHwT/ot1Reni2wVgfgxANDgerjgKTpa7OuVyZudlr6t1TM7P+G3AaD8cFUbdUOsL6d24Na5nGMHvk/QFT7Rc4bfSuhp3EA4bvd1cf9hlJ0D9nsHwdlF1pJZXtHmCWSOL0DpdmT8m5/1BdC8Gby6t1Xq+FkpfSvusxp+eMNw3I+OF588OtaR6Y0VNVNe01HlyvaSeGyPw1h9+0EnHqG+i6h4N1clusUVPSZNXW+ZI7I5aCQ0Ekeg3u95ITUqY6StHo+jlM0Lps5a57i0+7OB+ylBQaB7TTMijj2MY0NaPdhTQpERDu2UeyQ6JJQBylhJLKBBJdku6SAMSSSITRRBFJJKgEgeqKB6pQGn0XNvFhr3+H1a4+zBnG0dZHBzcD4Z5+S6JOJyxxhLQ7tu6LkXjZda6k0SyjFK6SVxdKNn9W0dPnnH1TWOj7PJvifHSN8i1B0hka0zTyE45JJzn0OQuPut9HUVhYyV5c0AdOD2Vr1dqKe8TzySBz6mfEj3HoOSMAegwAAhonStzvdaJHtc2L+o4xuTJzWONsmjB5JUkaSn0TUVczDSPdID/AE7e6vds8IWzQNklYRIQCQ4cA+5dw0xoq32+jjD4g5/qQrrDZqbGBE34rD2fKNOom5reIjVzPN8vhPT/AId8AgI2+0MdCf3VeqvCiWGU7Y8D17dOn1XrV1ip+oYAVEn0xHN1jBHwVaHk5p+y3LxONro832zwqjMQcYzl4GB2GFZqbwzjiGJKfoB0GV2+DToiaMMAHuC2UFpj2+23n4JmTyEmx0PF44+zg7PC6klnEjqYAjoNuFNl8M2vY5oZta4YJx1XchbIWnIaCsgoIh1YFH/kMhIvH4f6PM1/8NI2W98cURYwZO1gxuPqVzeo0U6nhOyM+Yw59c/AL2nX2enmYfZC5zqbRUWfPp4wCOoA6rS1fI31Iztvxf8AtjOD2+lq5YBRbjtaR7PbOeSfou56A1g2zRNjjZsm3BnmvyAfh29eFQI7QYLnLE+MtcTguRfUSW983O5sZDHM7DPf+62YyUlaMOcWume2tEahh1Hp+KuhI9lzonDvkHlWwdcLz5/Dnd2OtsltYcsfNJIHE8k4b/sV6CAUiIGOHVFDoilEEkkklASCKHUIAwjKcmj3opoB7oodCl2RYBQPVLhBLYAK85/xR6rZbbDSWuic0Vkkm18neNpaeAvRh56LxR/Fa6qk8UW22PpHTMqs59waPrtckkOj7OH0No/O9Q01DC3EEcTDK8c546LvulrVTWynZFFE0bQM49VQdAWYUVtbVTsxNM3c7IXSqGQMe3b0PosbdyOXSOg8dhUaky40j2k574W9pHDYB1VVopSXDk8Ky0QcWg4XPZlTOjiujbsiDwD2Wby2gYwsMD8DBWUO9pQoa7DsaT0RLWtZ2R6YPdNcMkFOsZ7GnG0kLG52OE45DcBYHA90rdjkjFO8c8qBIyOZu1wBBUqYknB+KicjOU/H0DXRStS6YjdFNUws9sN3Nx65yuP18n4i6CAk7nRuMo9cEnP0XpKUMmp3McM5C4pqq1U1v1k2oYMRzNMbgO3HZdB47O38JHP+S10vmjffw5XKeLxNbRNcfLfyG+5ezV5S/hs0zG7xLqbjI8FtDRec0Du+Q7Rn4AuP0Xq0jC2kc9L2FFDHCdhOG2BJHACRQKBJJJAGAIhBLKaA5EpueEe6AF3QR7lBKgEF4i8fr5Bf/wCIO6MgLTFbBHb9w/qfGPbP+pzh8l7eDxGDK4eywbiPcOV8yK27VFx1bcK+aQOlrK2Srmf6l7i4Af6k2fokx+zolukdHRwtAxkYVstRDg3PqqrQxl1PEGg5DQrhZaV+wYBJWDsy9nUacaotNqi8ybGPdyrhSwgMAH7Ku2mmfE0PcOFaaQtwMrDzO2bMV0ZxB7HRANw7lSwQWDBTfLBOFEkNswE56I4PClCBoGUBGAUUxLIxiyOVhliI6dFsg2MDLiAsUphx/O36peLG8qZpZWEjooEzXA+4lbmQQlhcHtUF8bX5LcH4J8LRJaNW44GVy3WckctYGuGJI3bx7wutTQY6Bcg1zG+K7cgg/utjx7uZk+Sj/G2X/wDhvukDNfVtMSA+tojsb7435P2IXp7qvF3gjXG3+ONozI0NlkfT7f8A5Gn+4C9pLpInJzVMI6IjomjonBOIw90O6KXdAAQTiO6CBURvgj2Q7opoouyKanIASWeUMpd0AQb7K+DSN2njGXx0NQ9oz1IicQvlxYmS1Oq4aJw3YIcSF9SrrTGu07cKJpw6oppYAfQuYWj9181dBW2QayqXVLMvgjAdkdHZwR9QmZXUWyfXjymkdToGiGmD5MNDRlxPZSbXr2ljmMVPA1zGnaHHv71pr1Tz1sMFvgeY2yOzIW9x/st/pzS1thZskYJCPVY03BK5HQ41klJRgbk+JEtNC54pWytHTYMI0fi9G6QskoJGnP3VgZo20VNNgQtacdAtJcdCQQSb4Ym5HKo88EuqL6wbEe0y2WzXFLWkBpLDxkOVlprxBLj2hn4rjscX5VJh8ZHxVgtFybMSGu5JVbJiiu4lvC3Lqfs6ZUXKOOnyHcqsVeqy2YxRP5HUqVG189EC4HGOqpV5gFPWOcDjJUeOm6ZNLHSJ121JW+Zspp3HIB4PdaGW+X18ZaKh/wABnJWWmjbMcuxlW6y2234Ek+wkdMlWOcYL0V3gcu7KxHWX+S3BkMFQ57+Xyu4+nuTaO4altVSHvhkmhJ9sOHT4LpjTRNZtjMeB8EyT8K5u1zWnKas69OIx67Xdmtttzhu1F5rGOjeP5mOHIVO8QbI6ptLq6Fn6kQycDqFeo6SCCYywMa0HqAOqFVSx1lHNTyNy17SMfJOxZFCalEjz4ucHFnm/RVykoPFbTb48h7blT5+cgbj7r6DuGCR6FeB9J2SoqP4gbPYw0h4u8TQ70a2QPz9Gle+Hu3OLvU5XXQdpNHFZVUqC3onBNb0TgnkIUkkkAJJJLugCL3RS6oJo4JSHVJBAB7oJd0CUAODsEHsOV4OqbIbN44a9pC0BkF0kjjwOA0yPeMfIhe4rtdaCyWae6XOpZT0sIy+R54HYLxzd6623vxk1fdrW5z6SruDJGlwxn9MZPzOSq+zKsbL2jByyoTKcgicN5AxlYDfZKGr8inLd39UjzhrVa5rYRZS6JuXkcLlV90/dqy5NY6R0cW7JY3jPxPyWLjnHI6kdJlhPFTidCi8QbPbAz811FHDnqG4A+SM/iZbKytFNZ7uKl7mlzWPZkuHqqbc/DO0X2307JXvgqIs/qgZyCOhCtOg9CWfSNxnutPOau4vh8prqhodjJ5x6dAkljwJWvZJGW3JpV0Y5tSOuRdDPAwux/MxN0tVSt1c6hfuw4AtB9Mq01Om7XDWOu24R1Tn+aWQtABO0DHwyM/NNsdCyo1m65yRhrtoa0AKtOUVFpFqEJuSbZ0qGAMtLSG4XNdTMqJri5kLC7bzwupRO/wCyx2VauNCWVf4qFgOeHBZ+OXGVsvcW1Rx6tulZTTNhjlMIPUkc/ILXXbXE2lKuAXCir6gzN3tLuGkLot5tFA6sNWaZ284cDjoQtRc7Hb9VNigvsL5mwj9Mg4Wpinjfcl0Z+fDmd/jfZCofFa1Xq4NoKS2TtmDednfHUqzWrUkU1SGQ1Bfg4fE/+Zqwaf0Bp2xTTTWql8qWZmxzickN7gErdUmjKCKq/EwQBkhOS4d03LLB/qhMOPYiv5GWWnk8xjTnqpTYy1hJ7oWygdEwNdzj1UqobtyAs9S+RNJFI8MbFDU/xYV9U9oxQUr6tg/zuaIwflvJXpk9V5jtlZcbT413qtt0gicbfE1zsZyC7OPqB9F3jRF7qb9pt1VVMIfHKYdxGN+ACSB811upsRko4/ujj97UnHlm+rLMOAnBNHROHRXzKD3SSSQAkkkkAReySBRTEPoSSWeECeEgUIppKJPKaixSleLdlnv3g7eaOlYX1MUbaqJoPJMbtxx/9dy8kaQhd+cXRzx/NJE4f6F7rcxsjTG9oc13slp7g8ELx+620dt1ReG0Tt0AqnxMd6tY9zW/ZUtzqJr+LVv/AKLtQgTUTIy3PCi1em2VNSJMYTrRUARtOeMKz072SYyuXnNwZ2EIKS7KwzTe0AF5OFNp7NFAMgZVjLG9gmPZtjcQAolmkWFFJFRvAbFhvRO0+zdV+aOQoGpZJfxvls7hbnS0DnwFoHQKw74WyJP5UWwy7aIdVFbJwWyDg+q2BptsDQR0HOVrKwOYMjqqvEli0wSUMMwIcwOBUUaephLvY0gqdb6jzJNjv5ltvLGB6o5Sj0hspUa6ntLGAZwp7aVoHTCzxxkY4WV2Gtz3THJsil2YWt2NUWp5fhSnv4JUN53Oyn412QTRpqC1NOs7pWvGRJTwsGfUbl0/QbBDaKmmH8rJdw+Y/wCFSacgVj8dwAuiaRiYzT3mge1JI4u+XAW341uWxf8ASMny1Q1Kf20b8dER6IDoiF0pyA5JDuigUSSSSAIqGUs8JKMeJAj1SJSzwgAEjCGUHJpOQgekOBO4Y69l5CfE+GWpE2fMMjt4PZwcc/deuScd15e1zQzWnxKvdDJGWxvnNXC7s5kntfuSPkqW5FtJmr4zIoSaf2C2TFsLRnorXQzey0hUWgmGAMq222b2AM8Lm9iB2Gu1JIs0Zy1ZnR5jzjsoUMwLeVMLyYTg9lSRZlE53qKoj/MZT3YccKdpDUELZZKd20Ec8nsq1qaB7bhUxVPmNjkPsvaufWa133T1/kqaK5zVlI8ucYJece4FauPAp46szcuw8U+1aPT4vlPJSlh2l3UFayWvp5ZiJHNb7iVzahud027zG5jn8AO/pK19x8Pam46ibeKzU11bg7xAyTazPwUX69f8mS/sXXBHWAY454poHg5KsNLO2RoLwM45XPtNCsPl0by57IzjzHdSFfGtEbG4+yqZFxLEkmuzYtAIyPko8riD1TGzEM6rFLLkqFjVGhkjiThYcHcnA8nlDPJJVjCuytmdIMIzWromj379OE/0iZ7R9v7rnLN75PKizvecZHX4Lq1lt4tlipqLA3Mbl59XHk/crY8Rjl+WU/oxfN5o/rxh9mwCcEEQuiOVQUkEUogkkkkAQ+6ROAUkioyQBPGUD0S7oHqgckApp6IlMcUWSDHEhcm8a7MyW2UN+jYBJGTTSu9Wu5b9CD9V1guVN8UKcVPhdcjjJhMc49214z9iVFljcWS4JOM0zznRT7XhW62TgxAgqjgFk5wOhwrFa5ztxlc7tY7Ow0Mv0y5wVbWtw4qb+OaISN2BhU38Y/zQ3PxUl1U8xFu7sqKwWaOTOl6MN6mbVy4YzcM45WG02yNsmXRDLu+OiZPcLdb2CSrlaZCMhmVDj1o0PxDCwM7K7GEuNRRRk4ylc2bq5sbFU07ImZ9ocAdVvZ4/OoWgN5xhaeg1NZKq1/iZ+J4zgtx0Kwf9dRCXDadpj9ExxnLqib8cYq7LJbcUjsOCsAqmPh4IzhVKi1NZrkPKc4Qy9ge6lurIon4Dx9VVyY39gps3BrcPx2TvxAd3WnkmDo94II9QmsqXAZ5+CgcGWk1JG5EuT1WXI25WqjqN3C2LD+gCVawx7M3ZZa9H2h1RXtr5Y/0ouQ493dgPh1V/HK11ijEOm6FgH+C0/M8/3WxAXWauFYoJI4vd2HmyNv66HIod0cqyUxJdQkglAckgeyR6YQBE4SKCWc5UZIAnlNQPVIlBIlQCVjceOqLisTj700cNJ5Wr1FRG56SudvAy6elkjb8S04++FsXO5TN3OUCp07PIe7c4PIwT19xWztrtsoGU/V1rdYtfXS2Yd5bJ3SRE92P9pv2OPktfTz7ZAQVjbOJ9nS6WW6ZZ5IQD5gHBWqr6ms8t8dJsEgHBetnFWNdRgOwSAsfkRzDcACSqEHXs1Jx5emUy32WWqkkqL5cH+aRwyNvA+ZVjoNN6dniBe+Vp3DALz0WWel55YsTYYY3DMjmEc8K3+Xl6IseNY32iwQ6M03HDiGeZrXn2mB5wfTKfJpbStPEd4e7jn2uq1LLtSsDY3zuOFKbVW+oeMTOdnplRu12XecJKkQ6u1WYc01O9o/yuOVqDa7+KnzKWvldAMARycnHxV/paamkp2hkYHvIUwUTY4+AoZbKXVETwcjQ2Q134Ty60e0PRbstAAwOqjyAsyU6KQHBzlU5fJ2TR+KomQNy/3BbVoMpigjGXPcGge8nC1kJAA+Ks2kKP8w1dBxujp/1n/Lp98K7qY3KSRk72VQi5HVYYmwU8cLekbQ0fIYWQcpvdOHVdUcU+xySSSBA5SQSShQUEkkARB0QPRAFAlMJUgH1TCcIucsbj6pLJAOKxOcnEj1WNxRQGNxwUwnhJ5BCxOOEoHJfGnTj54KbVNK0l0DRTVQH/AIE5a/5EkfMLi/mvZL14XrS4w01Xa6qlq42ywSxOZIx3QtIOQvKN2oZLbc5KKTPscsceNzT0Kr58d9l3V2OPxNnSVZMYB6e9biGXgFueeFTqacsPX45Vit1ZHIz4cLFz4nE6LV2FJ+ywRMEjRluU2a0ifnYQQpFvljH83TsttFLA44BCo83FmvxjNdlYOnXbuIAfgthR6YMThI5mCt4+tjp8B/OStnSV9PLDtBbwnSzSaIlijB2Qqel8lgAGOFmcSG4CkSTRk+yokkg5yqvbZK5KiNUMGxa6NxaSM8KXUVDcYWsMu55AHzVrFib7KGXNXRtoZiGErrOgLU6h066vmbiasIcM9QwdPryfouS2aldc71TUDScSvAd8O/2XoGm2tpI2MADWtDQB2A4W54/Al8zm/KbDdQMw6J7RymhELUMQekhlLKACkkhnhABQKOcIZ4SgQcoEppPvTXOGEwnoTisbk10hc7A6eqBPvTRRpKxuKc491icefcnANcQOVheeOqc4qPJIGjlwCVIRuiFdphBZql5P9BaPieP7rjertMuu1sE9G3/u4Muj7bx3aVfLtfhX3iptNOP06ZrHSH1c4nA+gymxU7ZIU/japkXOnaPN7HOa98bgWSMO17HcEEKfQ1TmPIJ+S6Trbw+F2a65WsCGvYMkdBL7j7/euQyyVlvq3UtwppI5WHDhjBHyVLLhXpmnr537iXWmuOyADceqnxXhuQN4z3VIgr2yjDJWuPpnlZXTSDnkLNlqJm1i8jKKpl2rK4zU7JGv6FSaO6+W0ZfzjsqTHWzGPY55AHuWdlS/HsvUX6n0T/vt9l9Zdi48E4WV1f7Gd3ZUqlqZsgufj4rYitaRguLz6DomfqpPsP23JdG4fVbyRnj1Ud04bw08qGZZHjJ4C2um9PVmp7sKWAujpozmeoH9A/8AEf5j9lPjhyajEr5Zfji5zfRf/DC1uldLepm+wAYocjqf6iP2+q6tSP4LStPbqKC222GipYxHFE0Ma0dgFs4HeW5p963cWLhBROWz5nlm5M2ITh0WNj2uGWkFZAUpEFJBFACR+CCSACUOySSUDWbhjlYJpdkZIPPZOJGOFjeW45blRUWANJEbeeccpOdgcoF2AoVVVtiadpy7ufRKo2I2l7JEk0bOXvAKiyVjAcNBcfctTFJJO50ziSSdrQpr2iClJHLyOvvUygQvI36GSVkr2Ej2eegUF5f5MsjnFxHTlESYo3yHktb904xYpC3vtOfiUtUNuyq0Nrijq66eLk1DxI8nrnC2VIMP2nj3KdboRsk46kfsEyemMdQXsbweUt2IOfThwJx19FV9TaJtWo6ciqh8ucD2J2DDmq4U7xIwA9VnEAcMYTZRTVMdGbi7R5n1B4f3OwSmSWIzU3aojHA+I7LSR2+bgMkfj/3ZXq99DFK0sljDmuGCCFznVnhi/a+4aaDY5f5nUzuGSfD0P2Wfn1pJXBmvq70G1HKv/TkkVsqnOwXuU6Gz1WQdxUqGsnp6h9PW0j4JoztfHIMFpW1hrYnty3A9xWNky5IumdJi18M1aZChtEnV3KmR28RjPAx6LMa6FrC5zgAOysmm9G3HUM7Kq5MkpbdnIiPD5R7/AECTDHLnlUQ2MmDVjyka2w6ar9R1zYaUGOlaf1anH2b6n9l3CyWSisdqjoaKEMjaOfVx9Se5Wa226mt1IyCmhZGxowGtGMLYNaui1tWOFV9nH7m7PZl30v6E1qzsamNbz0WXjgK4UiK6V7Kp7GcPbh3xBUqKveSBIwccHC1lRJ5epImHpLAR8wf+VmmOwk568qNoWzcMqInf1gH0Kygg9CtJHiaLd37ppq5qJ+clzB1am8Qs3wKQKhx3Gmka0h+Nw7qU1zXDIII9yQUekhlLugD/2Q==',
'shihab'=>'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'
];}
function photo_seed($db){
  try{$db->exec("CREATE TABLE IF NOT EXISTS meta(k VARCHAR(20) PRIMARY KEY,v BIGINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $q=$db->prepare("SELECT v FROM meta WHERE k='photo_seed'");$q->execute();if((int)$q->fetchColumn())return;
    $db->prepare("INSERT INTO meta(k,v) VALUES('photo_seed',1) ON DUPLICATE KEY UPDATE v=1")->execute();
    $have=array_flip($db->query("SELECT user_id FROM user_photos")->fetchAll(PDO::FETCH_COLUMN));
    $ins=$db->prepare("INSERT INTO user_photos(user_id,mime,data,updated_at) VALUES(?,?,?,NOW())");
    foreach($db->query("SELECT id,name FROM users WHERE role<>'guest'")->fetchAll() as $u){if(isset($have[$u['id']]))continue;
      foreach(photo_seed_data() as $k=>$b){if(preg_match('/\b'.preg_quote($k,'/').'\b/i',(string)$u['name'])){$ins->execute([(int)$u['id'],'image/jpeg',base64_decode($b)]);break;}}}
  }catch(Throwable $e){}
}

// ---- One-time import of Raiyan's photo (only if he has none yet) ----
function photo_seed2($db){
  try{$db->exec("CREATE TABLE IF NOT EXISTS meta(k VARCHAR(20) PRIMARY KEY,v BIGINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $q=$db->prepare("SELECT v FROM meta WHERE k='photo_seed2'");$q->execute();if((int)$q->fetchColumn())return;
    $db->prepare("INSERT INTO meta(k,v) VALUES('photo_seed2',1) ON DUPLICATE KEY UPDATE v=1")->execute();
    $have=array_flip($db->query("SELECT user_id FROM user_photos")->fetchAll(PDO::FETCH_COLUMN));
    $ins=$db->prepare("INSERT INTO user_photos(user_id,mime,data,updated_at) VALUES(?,?,?,NOW())");
    foreach($db->query("SELECT id,name FROM users WHERE role<>'guest'")->fetchAll() as $u){if(isset($have[$u['id']]))continue;
      if(preg_match('/\braiyan\b/i',(string)$u['name']))$ins->execute([(int)$u['id'],'image/jpeg',base64_decode("/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAUDBAQEAwUEBAQFBQUGBwwIBwcHBw8LCwkMEQ8SEhEPERETFhwXExQaFRERGCEYGh0dHx8fExciJCIeJBweHx7/2wBDAQUFBQcGBw4ICA4eFBEUHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh7/wAARCADwAPADASIAAhEBAxEB/8QAHQAAAQQDAQEAAAAAAAAAAAAAAQACAwUEBwgGCf/EAD4QAAEDAgQDBgUCBQQCAQUAAAEAAgMEEQUSITEGQVEHEyJhcYEUMpGhwUKxCBUj0eFSYnLwgvEXJDNDkrL/xAAbAQACAwEBAQAAAAAAAAAAAAAAAQIDBAUGB//EAC0RAAICAgEEAQMDAwUAAAAAAAABAgMEETEFEiFBEyJRYRRx8CORoTIzgcHR/9oADAMBAAIRAxEAPwDpGycAkAnAKJIQCKQ80bdEAABOslZEIAACNkQEbIACSKHNACKISSKADojdMCNroAdcJXF03QNzXFuqDXsJs2RhJ2AcgCT3QJTTdLUboAfdEFRgpwKAHJWQRugAJJJIABCabp5TSLpiIyExwUrlGQkAwpjmqQhNIQMfySSS3QAWpyQCdZAARARATrIAFkgNEbInZMBpCFk6yQCQDToLk6BeU4g7QeFcFm7ipxHvZr2EcEZkJPQEaH2K8n2zcfS0lUOEsCbUvrpnNbVzxRh3dNd+lpP6rbnWw81zhxR/MjjlTJPV1NR8Q4RwNvZ2ouS4g2v6KSjsZu3He2vGIqqWbD6OiZRF4EccrSZCPPUan6Kqr+2bGMVp/wCWzNOENmGXv2tsCSbfNy9lpabiQOpRRzN7o3vFrmNxy8xr91RS4rW00bw+J8VM6Mgd4/MJCBewupuA00btquLcbp6CsaMZFM2If1IYDkEjWjcuJsCdhzKp8D7U8Qifkp6ypJPzus15PmdtR1Gq1fQcWyVRqYJGse18YLI5BcPc3dt+umicZuGZrB9IKGTSzY3EC5FxfW/PmhLfI29cG+cP7Y+IY2ZG4t3rzbSqpmut02tb3XteD+18zVJpcfiY8WuJaeOzmgc8tyHDrbUdFybWUfw7RLFPL3V7tJb3jD/5A3+ynwfiKWmcyGacRNzgxky+C/JzHbtP/SFGUfsEWn4aO9cN4jwLEbCixSnmcRcNa7X6K2BuLtII6hcP1uKCvZHUGpkp6lrS6OWN2W7h5jY+9vULdPYJ2k1dXCMFxqYSSw5mB73aktt9iCCPdV+tjcPsb3BTkxr2yRtkYbtcLgpwKaKwgI2QCPJMQAEkUigBjkwhSFNIQBEQmkKUhNIQMFk4BAbJ7UgFZORCSAEE6yARCAFZLZHkgUABed7ScdPDnB1fiMMjG1YjLKVpIu6U6CwO9t/Zei81yb/EPxlJifGtbRumk/l+HPdAyPVoc5oAPPrm9fomltgeHruLKymkhiqK6tnrZM0rZS0Pe+R5JeHG+o2Gn2VVLViWdhc5zhI43DXWLHkbdd/wqd2M08sszZ6YOvH/AE2X8V76HqvOPxaoZV5opZprHRj22B6a/lXrwRb2WNczJM615Irk+E2yXFioK7Gm1EcDXwumMIyRlzrNbp0G5Kr8YqpJXFzHluY3cGu0vzVdRvMUp79jjE/e3LoR5hDYcEzxNJM17BlO7A0AZV6mhqnT0bZJYIH10Le7kY+1pmfndUbJA1osInk7m9r+YvopM5nAzPaC0WAbq4/TZAJlnT4hgkE5dLT1FI7nCZntb7K0GK4YYwyHEGUoLr3dG2RlvMG11V4bhs0rTM6Kasfu0Ob4R1F+apcXw6ugkDnYeIGFxJuL36kn8clX3Rb1ss7ZJb0evmqcPmjINbTM7wgNloyQHEbZ4j8vqDY6hZvAXENXhnEFPUudm+HmaGOG1r2Id5EXHsFrZ0kHe2a1rQAdWNLS705fUK6wKsdE57w7K2QtJDRpcbfsouOkxxntn0A7OMaZUyVuAvkdK6jJmppSb99TPccjvbVvsvZBc6/wv8RyYnxDUSVQy00dDHhtC7a+VzpCLe+noV0YRZQQpcgCcBomhPTIgSSSKYgEJhTygQgBm6BCchZIYAnBAJwCACCnIBEIAVkbJbpIARQIRSIugBriGRue7ZouV86ePKw4nxVic5cDC+qklMsrzaxcSAAvohijjHhVY8AEtp5Da+9mnRfNzFJI21MkrmsysfZgtpmvcm3lsFOPIeiKBxbKBC5gnPyvyWt0VtU8J48/DBUtpobPOmVhBJ5nmqfBJfiMRjnm0iBsCdyus+yjApjhMNdiUBja9g7qN/zZepHJVZN/xLaNOJjfNLTOb+D+yniXGwJ5qd9PTnUOkaW5vQWXpf8A4S4nqJiykFGxg0zyTEA+1l1YKK+XKzKOQU8eGHPny689FzHm3d20diPT6FHTOTD2B8UMkaZamhDXblktx9LL0HD/AGLvo3D4p3eEHUAan3XSdTh92hzQLhYr6MtBcWix5qqzLul4bLq8GiHlI1phHAFLT07WsiAeObhqpMY7PKSuo3QvgZdwtmy3WyO5e0+Btj5pzC5oyuaD1usjnJedmrsi1rRx12n9mFTgkklTTRnKNWkbALXeESyQ1Is3Uuylp6/hd28YYDT4zQvhfGDmaRfoSFxlxxg8/DnFdRTticcklieouuxg5TuThLk4PUMRUtThwzafYZW1MvFGH0FFK0Runhc+SRhHdlrr6nbkPuu1HBcD8F4x/K8LlloaOSOSR7RGDIAHOzNLrAG50HpqV3Vw3USVnDmG1ct+8mpY5H33uWgn7rYjnyMwJyQCNkyA1JOshZMAJEIoFAhtk22qkTTugBoF04BAaFOCQxI8kE7kgBI2QRCAEUkkrXQBj4rGZcKq4gwSOfA9rWnYktNgvmpxKyeox2Sk7t12vLDECCS4E3X0pxqpNFg1XVNaHuiiLmtOxPJccdsXC/DZw+h4ypcE/lVRUmQVdOXOdHI4a5xrz+99VD54xsVb5ZphiWTplcuEYvYZwdSivOJSyYXi00RDTD3t4aZ29i4fO7/jcDqttY3iHHEnENPQYZV0FNA6l7yWoZEHsaQbO+YXvq3K0fgrK7F+GqbD+AMNJpI46ypj+IlIaB82ov7WVhjGAyS1FSxlVUsDomuIiA2LiDbTyWRZEP1G7EnE6MMWXwJRbTPO1tFjcwPf8c2nfp/9trSB7G6p6nEeM8BqmfB4pHiEDbfI/wCYX1zAn8puLYfNRwTNwrGpGS6Bsco7tzOt8upv1XnWHiXuC6pqYqqrDwMrW6lltTmNr68j9V6F2dNnDzr/AIWmcd1ZsZpLa/O9o2zwb2gOxVrqetYIaqNoEjHaFp9F7STE6dsQc4tcStDUuD43UY3g8dNVMpK8TOc6YRhxFOG3c0tPzeIgAdT6q77S6ninA6elgfXUtfFUvsCylME8dhf9JLXC9uQXmL6q3Zqp+Hxvn/w9FTbZGvdi49r+bNrVPEOEQTmKWsgjIAOrlLS1lDijCaGqglI5MfcrnGTBqbE6gx1+LPnqnayCE+Fp6Ak6/ZXuHcJVGFvbJgmMyQzgXEFRcZvQg/st8ug2OtScl/cwrq/1uMYvwblrDNASxzCPMrlXt7qaeo4qkljY3O2URTed1vbDONpKagGH8X001F3VoxibAZKck7B7rXYTyJFvNcvdptZJVcT4s037oVmaMgXEjRfxA8xsbhYMLFnTe1JcFvUMqFtC7TP7O8Mjmxpomh7yD4uFkEQk3DiCBb3v9F9C4ImQU8cEbAxkbA1rRyAFgFyB/CXhJxfjihrA1rWYfC+pmDmZg64AYPI3N7+S7BO66nLOK+EEIhBFAgHdJFBAAQsiShdMAIJyaUCGhOTRoiCkMcEeSAKKAEjZBK6AHBLmgCigDzfaPNUt4f8AhqUgPqniG58yB+VpbtYloMc/l3AtBTttHM2MynV2hALffUn2W9OL25sPjcGBxjf3gBNrltja/LZcxy4kH9rzWtqIrfFve3Luxt/CL9f8LBKDeQ5fZHcqthHBjX93/P8AB0Jw/QU9LSRUsTbRRMDGA8mgWH7LOr6aON7KhsZeGsLJWNAu5p1BHmCNvMqmpsVa1veBhyHax+ilqMYAhL5C0NIvqsPfFcm+FbfBiYtgvDWLAvqIKWdw0JcAHN/IXn48J4XpnyfA0EMkkWhMbS9/oBuT6Kl4gxUY5iPwdFMyngDrTVTyAG9Q2+5t9FiUfHtBwZE2hwagGKulk/qSd81rwPNzt/rbVOCcuE9Fk5Rh4bWz2fBfCj48QmxqrDu8lP8AQhccxp2a+G/usLtfw2aWHD5YogQyoYx7wAdHPaDvor7BeN6CroO9dE6llv44ZPC9hOtj/jQoV2KYRxBA/DJauOK+pcCDlO4NvUXSlJJp+xKDcWteDQ3EPC3wTjT1NLLSwiQujqYDoSRa9yCfYqbBsIrG0kdPh+MMkiaSXRSvJc4nYtI+Rw+/NblwXEMNraqqw2qfBJNTymOW+gd5gHyIV/RcOYOw54KKEG97hgC0rqGQo9uzNLp+P3d+vJ4DAaaV2D1sWO0wfG+lfHM1wu14Lfvdcq4tjFK6nxLBpaYS1MVRGaGpGY93kzCVtjYWcCPPQaLtutpIO9kkGsFK10rnfpcWgkNB5gHU+gHVaH7Buy7BuKeNxjuMsqaiGEfEPpsg7lzgfCXG99TrYbqWFf5l3e9GPPo7knD0n/Y3N/DPwLLwX2fRyVzSzEcSDJp2WFo2geBo5/KbnzW00TYaILpHGEikigBtiikjyQAwjmhZOKbdAAKCJQTERgp4KYiEhjxZOTAnXQAUkkkAJFCyIQBRcfV7cM4aqKx5YBEMwzHflYediVxVgGJum7S5cQdIPG+R0bQ2x309N7ey6p/iGxGSh4HlbFq+QkDXQi2o+hv7LiylrmU2J99E5oe51u8Ata6OxPb+5dCxpJPhM6p4TxIVFNTRCoLswIJNgCb2AHsq/tqxipwbA6UwsJE8mV2U20AuVrTgLil38wpYRIY2iQNzcjc/+/Zbf4jghx7DKGnnibO0yHxEaXI187Li21/HYnJeD0FFrtqag9M0dw9NVcR4y92M4hT4dQsJDY5JBfLawDWDdehxDgXhmujqpqfiSenke68Icy7DbbMd7L3R7LsOpOJ6fiHDqSkdNlbFLFPFnjIG5GosbXtyvutnU9HwvWsZLj+BUVNMC9/gbfMXE9ACdADfa62Kxy8xekULHUI7nFy/PJy/UcP8eUGEOp6GQ4iGyNMMtDVNcAzUEHMQ6+3Jecghx/DMegq8cpalzmEOcx4LRbmbg30XUGL9nXCD4BVUOOyUTpZvCWzBzWMcTpY6jQblag7RDNwiYPisShxzDq1tw+Oz3Bp+UixN9LG401Ue9700nsjKFbW4ya19z0vDtTh78YgxSkcZIK5obKHG+V46+a2phj6SEtcY2uBsbO1H02XLHZ5W4lTcX0+DwtzRVc7XMaQb5dTm8tN10TAZG2Z3mrBdwtyWHJh8UvpfJvxrvmrfcuC/49xOKk4SxCsA+WndE1o0F3CwUHYBSCDhWScRNYZO7Ybb3a1a17YeJoWcOU+GRzXknmLpBe1gwX1+o+i212I00tJwDSRzvLpXkyvvuM2oB9lpwam9Tf8APRz+oXKMZQj70v8AO/8Ao9u5BIohdU4YQkgiEAEpqdfRNKAAd0CiUEABInVIppTAjCeEwJwSAcE5AJIASISSCADdIIFJvVAGp/4nYJH8FiWOPvQCWFh1Fj+q3O1vpfouM8TmoxWytiMbGh5zg6AgbD0/uu3e3+GCbhF3xWURNdmcS25DQDqBYm48tdSuH+InGCPK+xjDic1rAm/3VkBPgdg2K93iMXcu7tsbhZvUrrDssrTi3D7JJi0yuOYdbaLiyF73vZKLtdm8LR00stvdkHaBLgGJRNr5XmnlGTKCCW87hZM3Hdsdx9G/AylVLUvZ1NWYnFQRSMneMtjuteVfa1RUVb8M+CcNa75XbfQ7FY/E3FtLiFLH3FYyka9oa8hoMuXmL30v1814BlFg+IV8lW9w+GhJLnON3P8ATz5A+pWWmrS+o6dmVJP+mzdGDcZ4dj7S6GOnL9jmdlP2KNVgmEyvdUzUNGJDrdsYsOi8r2a8OUHw8Na1zfELv6M6g9dea9RxbiFLg9G+aWcMijbqLanpYLJfJ9/bE3V3ylXuZ53DMP4ewvF6riGsez4qFrmRDbICPF6lVeN8bwxwzAPDHvFzZ+o/0j6LUnH/ABtLXYm+SnlMdO1/hAF7kc/XReSq8UrJmGcyylucizQTdy1wwnPUpnJt6go7jBHq8SxyTHOJWTTPe2lzZRcZgOR/Zdq9mUZi4Wp4u9DmNALGgEBreQ139ea4g7OqQVfE0FPUSd605czBobaC2vPW9/Rd68MwPpsBo4X/ADNiDdraDbRbowUfCOXZNyW2WBRQKSmVDkkEvdABSSQQAkEbppQALoWR5pJgRhEbJqcCkA4FFCyKAEigEkAFAbopAIA8p2rUkdRwbX96PAIXOc/KXGOwuHADpv7LhLjqmArnNa9s4dqHC1hfkANPWwHRd1dpWP4XRcM1tMZ4qiqmjMTIGPBJJ/1W5dRz2XIs+FHEePaemraKZmd0s744I25Mwa53htsL/QBX1Vyk0iEpJI1d8PGyakksS2SHUMFzm2LfXQosZUGtBaW5XC4zG1/+/hZeIUr6WvfTB7RK0lml9Tra4/7uq1k0r5oo3lrHw5iS5tgBuVIRbuxSvpgYw4vdcRnUnZZFDj0zo8lNM5oBJc3lovOzVE+V7899A4lug3/tZClfHHG4FxMpdqb5TruouKJqbNoYL2i4phjHPhzZG+GwNxzOvrZUHGnG2PY6WSVs7mxxtL2xA2BF7e/NeagroaeQiNxD3Fwe0kWNwBp6dVFWTBz6YyghvdkZf0kb/wDtVKitS7teS2WRY49u/BhMke6Tvg55ic75f3VjQSOfaeMPe14ylrz1uLiybTUkDBCH5xTPdq48w7mbdLFXeF4S4xRvN3x96WkZbNubm11OXgqj5Nz/AMM/CJxDHI55y0UwylzcveFwHjF/9INuuq670tstJfw71lNgmEQ4ZXtp4DIzNG+PYOOpaTz9VuqCaGobnglZK3qxwKq0SkOIQRKSBCKQStqkEALZJLdAoASR2QSQIBSukUEwIgnNKaEbJDH8kVFNNFBH3k8rImDdz3AD7qgxPjPB6S4hkdVPH+jRv1KlGDlwhNpHpVHVVVLRxGWqnihYNbvdZavx3tGqS1zICynGwEervdx/C11jfElZVPc+ad73Hq4lXxxm+SDsXo3DxH2lYPhzHNo2Oqnj9TjlZ/crXWK9oOO49OYWTdxSjV7YtAB+fda9nq5Z5bXOpsFZ07hBH3MYyuv4z1PT2WiNMYlbm2ZldXyT1BLvCxvyhYnZy+A9s+DMnAc2WKoZlOtyY3Efsm1GUNIB9VQ4DP8AD9qHDNS0lobiEY318Ry/lXVL60VWv6GU3bdwhW4BxpU0sMTZG1d6inkb4cw5tFhqRpotZ1XDWJUtE+qq6d8ebKGh4Nz5Lsz+Izhd+M8HR4pQxZq/CpPiYsu7gPmb7i61LRw0uPYHHLKQ8PaCA4XF91zOp2yxrE0vD/jOj02qOVW039SOc6qB4kHduJjJ0B2PmmSStDtY73ILidrdAFtzG+BoHNmZFoXEuzaeEnovHYrwnLHG2DIY3DZ7v1KmvMrmTswrIHlqQMmZ3TdgCWE7tVhTytjiuXHRlg4m/lb6LOp+FKw5WNhfZx1ty87qyw/g+V5GeUd2PmcNS31VjvgvZXHHsfoqMObO5rW5AG+EEnbS+3TdbfxnhRuC8DcOVNW2QVOLVT5sjtLMawAWtp+oarznBPDg4h47w7h2kjvTRPElQ8c2g6A+p/Yrcn8Sbo28acNYLCAIcPw+4jGwL3/2Yr6YfJDvZVdL45qC5JMMlihwmmfGGxujGmX7rOxHiLFaGBuJ0FQWd2AJxytfRwPI8ivO4ZO5tI2Nx15jkFKalou1zQ9h0c0j5h/6TjHyDZ7Hh/thrmPbFiMLZW88+/1C2RgXHWA4rG3+sad55P1H1C5dxinFLVuYzNYjMx9tHDkp8ExiopHA9463ruFOVMXwQU9HYMT45WCSJ7ZGnYtNwlZc9YHxhVwEOZUPjPQOsCvY4b2jVrcvelkwG7ZBr9QqXRJcE1JG1NUl5XCuOsMq2gVET6d/PKc4/uvQUWJYfWj/AOlq4ZCf0h1j9CqnFrkZkoFFwTSojEhdBAlAinxfiLCcMb/VqGyyWuI4iHH35BeKxztEqSSyiaymb1Hiefc6D6LWVZi8j6mEOefG1zfpqP3KwZ6txkN3DyXRhjxjz5KHY2eqxPiSeqe4zzyPfvmc6/7qhqsSc5187r+aqnzm+judzYqGSYEG5uRurkkiGySqrZJHGziet1hSkvJGtknE29SkNOXPRAw0s9JS1TPiZWsc7SIP0DnevVWlO10kjnA233OiqK6kp66nNNPE2VpHykXKiwuCsoIX0z8QdPA63dsdq+PyL+Y+/mkIz62c5jHEQTe17XA/uVTUrcvFOEyjNcV0Ti4/8xqsx5LjuWs5ALGqBkraSblHOw/RwUqnqaf5IWrcGvwdePp46qikglALXt59CFzxjOAHhHi2aiDclBWPMlPf5Qb+Jv3uPIrb3adxtS8F8PwTDJJidb/SoYXbF3N7v9o+5sF4vh7hrGuIOHqus4jxaSsqqxwnp+8BPcPbmy5eQa4XBaOoU8/AeViy+64/co6f1BYuVFenz+x53EsFiliD8h1F9FSv4fjeD3jSb7Zmr22GAPoGB+jrWIO91M6mJFshuN189cnF6PovYpI17Jw9C3YOJ/2ixVRxDHHSUroomBpIsbDUrZdTTuDSGt8R0AtdDDODqdlX/NsccRBSt790Nr3A2v5k2sOq6HT8a7Ms7YL9/wAHPz8inEr7pvX2/JY/w18BfyeB+MV0Nqyf+o+41bf5W+w+5K8L26Sy1Ha7iBdYiOKJjddhlvb7lbX7Hu0elx7Fqrhqqw5mH1scZmp8shcJmA6gg7OFwVqDtScajtPx95zEsqBH7NaAvZ3U/AnXrhJHiqb/ANRL5E+dkWF1oI7p4uRq1p6LOqX3s7nyXnNc7TY3abg32WS/EpIqOTNTvqX2JZG2wc49NdP+7LGkb9lhiBhmws97M1j4ySzPYA9W+/7qlLQ1wLRb0KwqKkrKusZX4vJeSM3ip2EhkV/3PmVaPPlrtZTRFsdS1DmOALiB0VnBXGws+x5KkIa11gL9VPFI61tALpi2ejpsTexwLZbEeSu6PHXsylzr9LFeCbI//V6rJiqnN8JOqi0SUjb2DccV9KGtFS6Rg3ZJ4gvZ4VxxhtU1kdS10MxFzl1bba653pq2zvmPTeyy8MxmYVM8oeQ0yd2Nb2yi373VUqkySmdSNc18bZGOD2uF2kHQhDmvNdl+IOxDhGFznZnRSOZvsNwPuvS81jfgtOQMUqDF8PMDpHM2/ofCf3Uxlubk6Ktxd3eYdOAfEG3HqNQpIZu+ije1wsWhy62zLoy3OAcdLhMIBN7qHPqRyCcHWNhqfJAE2W+pLb+aY95jBfe9tbBOJBboSCmyC4dexFrFLYE8z7QtdF4Q4A5jqSsZgFyXOLjuPNNpZc1DFcAmO7NfI/2spW69PUJCFa776edlh4o7uou8FrNIP0KziCDe233WDirS+leND4Sjeg5NvR8OV3ElW7FOIGx1gmaMkf6YmW0a3oAvZ0fd4VgkjZDI6SmYe5a4k5i7S9uZAJS4Gd33D9G4nV0DHD3aFd1FAydtnuIvzG67llqb7XwcGurS2uTytDg0dZBd/gedQ8Df1TKjDqplS2lbFd7vlyah/nf+6tazAD3oqI8bxmLYOibVHIR5D9PspIaPFGxzPgxfuMpIaJmtkdltcOJdq650sNlyM/pGNlvv4f4OxgdZycNdnK/Jj02BsoAJX5X1J0B/S30/uoKiGGXDK9s1w2/ivsQ3/KRqeKm1ETJ6bD6qMtL3ANfG64tpcEjn0WQ2LE6qMRSULKaMuDngSZs1tRyXRx8avGrVdaSRzsjKsyrHZY22awpeGsUw3FmY/g8BpK9j+8E0mrv+Nv8ATbQheKx6slxHinFa+aFsUk1XI5zGuzAOvqAei6KraZ4gLcu25XNsjhJWVUmo7yokd9XFV9Qn3QTLOnw7ZvQc1joSPVMe25vf0Tm3vYtuk6w+bS4+nkuUjqsfDKBaOcZmnS/MehQqTGybu2uLjlDrEagIMsXC6x2ES11VIOTgxptyaLfvdMCZg5n62Ts1naDfZLS3p1TC8Ag2F9tOSYhxLtLO33uhe+z9PVNc8A7HTyTWm52sOqQyUy93E6QkHKLqLDZntw6DPu4Z3Hzcb/lYmNPPwLmR6OkIjH/kbLJLg2QMu4hosAOg2SfA1yb+7Aq0uoayicTZwbMwH6H9wtoc1zz2QYs+hx6lcSRHI4MdfmDoV0ORYlYJrTLzjKVwLC0i4dosfBHl2HxMLvEwFjv/ABJH4UjzmOmg2JWHgbstRXU52bNmHo4A/wB10tmcs/p7hPZvsAmc7Ha6lboL2ugQ5puLgm10XEjXWyIuWgkDTkkdb6X80AQUuk9RENNRIL+Ysf2U7HWG2qxh/SxCJ1vnBjJ+4/ZZLy1tgb5r/VACcTcXGv7LGqhmiIJ5bKYmztSopGktIP2SA6H7LXibgrCJt81IwH1At+F646a20XhuxKXvuz6gF7mJ8sZt5PJ/K96RyXSctpM5TWm0YdY3xxgfK97R9wstsLAQLAluoNlE9geYm82ysP3CzwNUpS0khRj5MOojzSM1sMp/cJCIWudlPUNGdnQA/hQPJkflHyjdKLbQ2iq4gkyYdUFgsGxOdf0F1yzDmDQb3BC6f46kFLwji1RtkpJLe7bD91zKANPzyVOVLaijTiLzJjhrrzRsx9z9UQ3wkahCw06rIjaxOLYmulfYta0u9gFi4Y0to2l2rneJ3qdUcTuKdkQOszww+m5+wUwNm38tECHG2Ujbmo3chqk5+Y36Jjn8gfLZAxznNOgKYXan9k0OsPwmSEBuZ2tvugDAqZDLi1HTC1g4yOB6NH9yFmRzG9997BVVI9suM1MpvlgiDB6uN/2AWW3M9+Voy331UXwNHq+Eq57MRjLdmG48iuqcGrWYjhNLXRm4miDj5HmPrdciYO5tPVxuv4vJdE9i2KfFYFPhz33fTPEjP+Dv8j7rJai6PBzPIS1+XUEfdYeHvy4/O3YTQNd7tJH5CsKyPMwu3I2sqYSd3jdG8k3dniPu24//AJW1lBftJBPT1UrXX0JO+iiYLnQ/RSN62sUxEwcSLXISPqhmAOu/RFzhbK0anndAGNX5hAZALFhDx7G6yPCTdoBDvECon+Jha7mLFMoXONExnOImNx9Dp9rIAyCASCSLhRykhpsdFJdu2gUMh58imBuf+HmpJ4dqqZzrsZVut5XAK2sG6b7LS/8ADtMHRYvSn5myRyD3aR+FuZrjkF/dbl5hFo5s1qySEGgTxm/6rrILxcaLBBJqYwbm7iPsVM8vBtbXkiUeCKYquQeHU/KdPohC3LHtuhLG4zRB3JpJ+oUpGmiN6SQezxnbHOYOAMQANjLkj+rgudQPF0W9u32fu+FIKcHWWpZ9gStDj5iAeaz5HKNeKvpZO0gi3LokBzBsg0ga7o5sxOwCoNJhTPEmKBg1bAy/o53+B91K52trhYeGkTd7Ukn+q8uA8th9gsr5TflZIYnEG9jYKKQhpIA0T3OGUgaE+SiNhq6yQCzXGl7qGqktEb805r47nUZRuQNFg47UNp8PmlJHgjc7QeSG/AIwsEOenqZg7WeodY+TfCP2VpHMyEZW2zKkwoup8MpYQLvEYvvudT+Vb0cGQiWXWw5pDLGjeWzMeSb3C212NYn8HxPTNLssVReCQH/d8p//AGAWnInh8wIGgPVe24Xq+5rWsicBI0BzXC97g3CosRbBmvqutnpZSZGgt8tlWV1dGaqkqhYZKhhI97flXEsjXAx1AF+tuS8txHRuhb31MbtBzZdxpqtDKke5a+23iKla8k/Q7LEpnmaKOYfraHDXqLrIYfFawUiJLmzE2JTg8kmwNk1oG37I87m9+QsgAnXT7qCkOSrnid8sgDwPPY/hTEu9li1UgjqYJjoA/K70On9kAZpPiuN/wmPGmuqkkDmvy3tfdMdtogR77sAqxDxZW0xJtNSg+7Xf5W+tLEaWXNPZLUmm48p7XGeGRv2B/C6Qa/PA1wO4W+pbrRz7/FjFCb1kQP8AuI18is5rP6b5g85hmsCLjw768j/hVkEYkq2EuLTZ1vos+OnjDw++Z41uSbX5XHNSs0VwY2bvPi2gj/8AGefmn69EyUyNqwXEWLPynXOpKh6QzUf8Q1TaHDacG95HOPsLflaeGm3utk9vdQJcZoogbhsTifd3+FrXIQMzTcFUZH+vRtxv9vZKwaEarHxaR0OGyloGZ4ytN+ZNv++imF9GquxOdrq+CkeA5rQZT67D8lZ2Xj6e0cTA0XAbYEDRGSazSCR5EHX7JjzmIzOJHIEqKUtYNbDTmUDA+XMDd8hJ3tYffVQSSOzjwtA6uGYn6pFxf8rCSRrlH5Tcp3zNFup/sojJJHF1OQ85gqriV7W4c4POjntB89RorBgGuZziTyGyoeLXkimibu6cAjXYAlJgiywiPOO/lIAHLoPNTz1jppu6p2lzeZ5LFpYKqrjbHYRRADTqrOKKDDw35S7oApaET08ZiaC4HVXuBVBjro3N0sVQMkknlB0AVnROdFIHflVyWycXo//Z")]);}
  }catch(Throwable $e){}
}
// ---- One-time import of Hasibur's photo (only if he has none yet) ----
function photo_seed3($db){
  try{$db->exec("CREATE TABLE IF NOT EXISTS meta(k VARCHAR(20) PRIMARY KEY,v BIGINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $q=$db->prepare("SELECT v FROM meta WHERE k='photo_seed3'");$q->execute();if((int)$q->fetchColumn())return;
    $db->prepare("INSERT INTO meta(k,v) VALUES('photo_seed3',1) ON DUPLICATE KEY UPDATE v=1")->execute();
    $have=array_flip($db->query("SELECT user_id FROM user_photos")->fetchAll(PDO::FETCH_COLUMN));
    $ins=$db->prepare("INSERT INTO user_photos(user_id,mime,data,updated_at) VALUES(?,?,?,NOW())");
    foreach($db->query("SELECT id,name FROM users WHERE role<>'guest'")->fetchAll() as $u){if(isset($have[$u['id']]))continue;
      if(preg_match('/\bhasib(ur)?\b/i',(string)$u['name']))$ins->execute([(int)$u['id'],'image/jpeg',base64_decode("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")]);}
  }catch(Throwable $e){}
}
