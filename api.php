<?php
const BUILD=217;
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
  $ins=$db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())");
  $send=function($from,$to,$b)use($ins){if(!$from||!$to)return;try{$ins->execute([$from,$to,mb_substr($b,0,1900)]);push_q($to,'TaskFlow',$b);}catch(Throwable $e){}};
  $prog=[];foreach($db->query("SELECT ws_id,skey,name FROM statuses")->fetchAll() as $r)if($r['skey']==='progress'||preg_match('/progress|ongoing|working/i',(string)$r['name']))$prog[$r['ws_id'].'|'.$r['skey']]=1;
  $open=$db->query("SELECT t.id,t.ws_id,t.status,t.assigned_to FROM tasks t JOIN workspaces w ON w.id=t.ws_id WHERE t.status<>'done'")->fetchAll();
  $asg=[];foreach($db->query("SELECT task_id,user_id FROM task_assignees")->fetchAll() as $r)$asg[(int)$r['task_id']][]=(int)$r['user_id'];
  $mine=[];$going=[];
  foreach($open as $t){$us=$asg[(int)$t['id']]??[];if($t['assigned_to'])$us[]=(int)$t['assigned_to'];
    foreach(array_unique($us) as $u){$mine[$u]=($mine[$u]??0)+1;if(isset($prog[$t['ws_id'].'|'.$t['status']]))$going[$u]=($going[$u]??0)+1;}}
  $prev=$now->modify('-1 day')->format('Y-m-d');$now=new DateTime('now',$tz);
  $lb=$dow===1?date('Y-m-d',strtotime($today.' -3 day')):$prev;
  $hq=$db->prepare("SELECT COALESCE(SUM(hours),0) FROM time_logs WHERE user_id=? AND work_date=?");
  $nUsers=0;$unas=[];
  foreach($users as $u){$id=(int)$u['id'];$fn=explode(' ',trim((string)$u['name']))[0];
    if($slot==='am'){$hq->execute([$id,$lb]);$hh=(float)$hq->fetchColumn();
      $b="☀️ Good morning ".$fn."! Reminder to log your hours. ".($hh<7.99?"Last working day you logged ".rtrim(rtrim(number_format($hh,2),'0'),'.')."h of 8h - please complete it in Home → My hours check.":"Keep your hours up to date today.");}
    else{$hq->execute([$id,$today]);$hh=(float)$hq->fetchColumn();
      $b="🕒 Hi ".$fn.", time to log today's hours (".rtrim(rtrim(number_format($hh,2),'0'),'.')."h of 8h so far).";}
    if(($mine[$id]??0)>0&&empty($going[$id]))$b.="\n⚠️ You have ".$mine[$id]." assigned task".($mine[$id]>1?"s":"")." but none is In Progress. Please open your assigned task and set it to In Progress.";
    $send($sender($id),$id,$b);$nUsers++;
    if(!($mine[$id]??0)&&$u['role']!=='admin')$unas[]=$u;}
  foreach($unas as $u)foreach($admins as $a){$aid=(int)$a['id'];if($aid===(int)$u['id'])continue;
    $send((int)$u['id'],$aid,"🔔 TaskFlow alert: ".$u['name']." is not assigned to any open task, so nothing is ongoing for them. Please assign work.");}
  return 'Sent '.$slot.' reminders to '.$nUsers.' users; '.count($unas).' unassigned alert(s)';
}
// ---- Phone push (Firebase Cloud Messaging, HTTP v1). Needs the service-account key file on the server:
// define('FCM_KEY_FILE','/home/USER/taskflow-fcm.json') in config.php, or just place taskflow-fcm.json in your home folder.
$PUSHQ=[];
function push_q($to,$title,$body){global $PUSHQ;$PUSHQ[]=[(int)$to,(string)$title,(string)$body];}
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
    $tok='';$sel=$db->prepare("SELECT token FROM push_tokens WHERE user_id=?");$del=$db->prepare("DELETE FROM push_tokens WHERE token=?");
    foreach($q as [$to,$title,$body]){
      if($to>0)$ids=[$to];else{$ids=$db->query("SELECT DISTINCT user_id FROM push_tokens")->fetchAll(PDO::FETCH_COLUMN);}
      foreach($ids as $u){if($to===0&&(int)$u===(int)($uid??0))continue;$sel->execute([$u]);
        foreach($sel->fetchAll(PDO::FETCH_COLUMN) as $t){if($tok==='')$tok=push_token($k);if($tok==='')return;
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
    if(in_array(sr_gid($r),$exg,true)||in_array((int)$r['id'],$ext,true))continue;
    $d[$sec][]=$r;
  }
  usort($d['done'],fn($a,$b)=>strcmp($b['when'],$a['when']));
  usort($d['going'],fn($a,$b)=>strcmp((string)($a['end_date']?:'9999'),(string)($b['end_date']?:'9999')));
  usort($d['soon'],fn($a,$b)=>strcmp($a['start_date'],$b['start_date']));
  return $d;
}
function sr_assets($db,$ws,$mode){
  // mode 'cid': images are attached to the email and referenced by cid. mode 'data': inline data URIs (for the on-screen preview).
  $o=['tf'=>'','co'=>[],'names'=>[],'att'=>[]];
  $f=__DIR__.'/icon-192.png';
  if(is_file($f)&&filesize($f)<400000){$b=file_get_contents($f);if($mode==='cid'){$o['tf']='cid:tflogo';$o['att'][]=['tflogo.png','image/png',base64_encode($b)];}else $o['tf']='data:image/png;base64,'.base64_encode($b);}
  $ws=array_values(array_filter(array_map('intval',(array)$ws)));
  $q=$db->query("SELECT id,name,COALESCE(logo,'') AS logo FROM workspaces ".($ws?"WHERE id IN(".implode(',',$ws).") ":"")."ORDER BY sort_no,name");$i=0;
  foreach($q->fetchAll() as $w){$o['names'][]=$w['name'];
    if(preg_match('#^data:(image/(?:png|jpeg));base64,(.+)$#',$w['logo'],$m)&&strlen($m[2])<500000&&count($o['co'])<3){$i++;
      if($mode==='cid'){$id='cologo'.$i;$o['co'][]=['cid:'.$id,$w['name']];$o['att'][]=[$id.'.'.($m[1]==='image/png'?'png':'jpg'),$m[1],$m[2],$id];}
      else $o['co'][]=[$w['logo'],$w['name']];}}
  if(count($o['names'])>3)$o['co']=[];
  return $o;}
function sr_html($d,$title='Task status report',$as=null){
  $e=fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');$fd=fn($s)=>($s&&$s!=='0000-00-00')?date('D j M',strtotime($s)):'';
  $as=$as?:['tf'=>'','co'=>[],'names'=>[]];$F="font-family:'Segoe UI',Arial,Helvetica,sans-serif;";
  $nd=count($d['done']);$ng=count($d['going']);$ns=count($d['soon']);$tot=$nd+$ng+$ns;
  $pal=['#2563eb','#0891b2','#16a34a','#d97706','#9333ea','#dc2626','#0d9488','#db2777'];
  $av=function($n)use($e,$pal){$n=trim((string)$n);if($n==='')return '<span style="color:#94a3b8">Unassigned</span>';$w=preg_split('/\s+/',$n);$in=strtoupper(mb_substr($w[0],0,1).(isset($w[1])?mb_substr($w[count($w)-1],0,1):''));$c=$pal[crc32($n)%count($pal)];
    return '<span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:'.$c.';color:#fff;font-size:10px;font-weight:700;text-align:center;vertical-align:middle;margin-right:6px">'.$e($in).'</span><span style="vertical-align:middle">'.$e($n).'</span>';};
  $multi=count(array_unique(array_map(fn($r)=>$r['company'],array_merge($d['done'],$d['going'],$d['soon']))))>1;
  $logos='';foreach($as['co'] as $c)$logos.='<img src="'.$e($c[0]).'" alt="'.$e($c[1]).'" height="38" style="height:38px;max-width:150px;display:inline-block;vertical-align:middle;margin-left:10px;border:0">';
  if($logos==='')$logos='<span style="'.$F.'font-size:14px;font-weight:700;color:#0f2a5c">'.$e(implode('  ·  ',array_slice($as['names'],0,3))).(count($as['names'])>3?' +'.(count($as['names'])-3):'').'</span>';
  $tfimg=$as['tf']?'<img src="'.$e($as['tf']).'" alt="TaskFlow" width="46" height="46" style="width:46px;height:46px;border-radius:11px;display:block;border:0">':'';
  $o='<div style="margin:0;padding:0;background:#eef2f7"><div style="display:none;max-height:0;overflow:hidden;opacity:0;color:#eef2f7">'.$nd.' completed, '.$ng.' ongoing, '.$ns.' starting in the next 3 days.</div>';
  $o.='<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef2f7"><tr><td align="center" style="padding:22px 10px">';
  $o.='<table role="presentation" width="680" cellspacing="0" cellpadding="0" style="width:100%;max-width:680px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 18px rgba(15,42,92,.12)">';
  // header
  $o.='<tr><td bgcolor="#0f2a5c" style="background:#0f2a5c;padding:20px 26px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
    .'<td width="56" valign="middle">'.$tfimg.'</td><td valign="middle" style="'.$F.'padding-left:'.($tfimg?'12':'0').'px"><div style="font-size:23px;font-weight:800;color:#ffffff;letter-spacing:.2px">TaskFlow</div><div style="font-size:10px;letter-spacing:1.6px;color:#93c5fd;font-weight:700">POWERED BY GURUCADD</div></td>'
    .'<td align="right" valign="middle"><span style="display:inline-block;background:#ffffff;border-radius:10px;padding:8px 12px;line-height:0">'.$logos.'</span></td></tr></table></td></tr>';
  $o.='<tr><td style="font-size:0;line-height:0;height:4px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td width="34%" height="4" bgcolor="#e2e8f0" style="height:4px;line-height:4px;font-size:0">&nbsp;</td><td width="33%" height="4" bgcolor="#38bdf8" style="height:4px;line-height:4px;font-size:0">&nbsp;</td><td width="33%" height="4" bgcolor="#22c55e" style="height:4px;line-height:4px;font-size:0">&nbsp;</td></tr></table></td></tr>';
  // title
  $o.='<tr><td style="'.$F.'padding:24px 26px 6px"><div style="font-size:11px;letter-spacing:1.4px;color:#64748b;font-weight:700">WEEKLY PROGRESS REPORT</div><div style="font-size:25px;font-weight:800;color:#0f172a;margin-top:4px">'.$e($title).'</div><div style="font-size:13px;color:#475569;margin-top:6px">Week of <b>'.$e($fd($d['from'])).'</b> to <b>'.$e($fd($d['to'])).'</b> &nbsp;·&nbsp; prepared '.$e($fd($d['today'])).'</div></td></tr>';
  // KPI
  $k=function($n,$l,$c,$bg)use($F){return '<td width="33%" valign="top" style="padding:0 5px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:'.$bg.';border-radius:10px;border-top:3px solid '.$c.'"><tr><td align="center" style="'.$F.'padding:12px 4px"><div style="font-size:34px;font-weight:800;color:'.$c.';line-height:1">'.$n.'</div><div style="font-size:11px;color:#475569;margin-top:5px;font-weight:600">'.$l.'</div></td></tr></table></td>';};
  $o.='<tr><td style="padding:12px 21px 4px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'.$k($nd,'Completed this week','#16a34a','#f0fdf4').$k($ng,'Ongoing now','#2563eb','#eff6ff').$k($ns,'Starting in 3 days','#d97706','#fffbeb').'</tr></table></td></tr>';
  if($tot>0){$p=fn($n)=>max(0,(int)round($n*100/$tot));
    $o.='<tr><td style="padding:10px 26px 2px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-radius:6px;overflow:hidden"><tr>'.($nd?'<td width="'.$p($nd).'%" height="8" bgcolor="#22c55e" style="height:8px;line-height:8px;font-size:0">&nbsp;</td>':'').($ng?'<td width="'.$p($ng).'%" height="8" bgcolor="#3b82f6" style="height:8px;line-height:8px;font-size:0">&nbsp;</td>':'').($ns?'<td width="'.$p($ns).'%" height="8" bgcolor="#f59e0b" style="height:8px;line-height:8px;font-size:0">&nbsp;</td>':'').'</tr></table></td></tr>';}
  // sections
  $sec=function($name,$sub,$col,$rows,$mode)use($e,$F,$fd,$av,$multi){
    $h='<tr><td style="'.$F.'padding:22px 26px 6px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="font-size:17px;font-weight:800;color:#0f172a"><span style="display:inline-block;width:10px;height:10px;border-radius:5px;background:'.$col.';margin-right:8px"></span>'.$e($name).' <span style="display:inline-block;background:'.$col.';color:#fff;font-size:11px;font-weight:700;border-radius:10px;padding:1px 9px;vertical-align:middle">'.count($rows).'</span></td></tr><tr><td style="font-size:12px;color:#64748b;padding-top:2px">'.$e($sub).'</td></tr></table></td></tr>';
    if(!$rows)return $h.'<tr><td style="'.$F.'padding:6px 26px 4px;font-size:13px;color:#94a3b8">Nothing to show here right now.</td></tr>';
    $h.='<tr><td style="padding:4px 20px 0"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden">';
    $i=0;foreach($rows as $r){$bg=$i++%2?'#f8fafc':'#ffffff';
      $dt=$mode==='done'?'<span style="color:#16a34a;font-weight:700">&#10003; '.$e($fd($r['when'])).'</span>':($mode==='soon'?'Starts <b>'.$e($fd($r['start_date'])).'</b>'.($r['end_date']?'<br>Ends '.$e($fd($r['end_date'])):''):($r['end_date']?'Ends <b>'.$e($fd($r['end_date'])).'</b>':'<span style="color:#94a3b8">No end date</span>'));
      $chip=$mode==='going'?'<span style="display:inline-block;margin-top:4px;font-size:10px;font-weight:700;color:#1d4ed8;background:#dbeafe;border-radius:8px;padding:1px 8px">'.$e(strtoupper($r['sname'])).'</span>':'';
      $sub=array_filter([$r['customer'],$r['drawing_type'],$multi?$r['company']:'']);
      $h.='<tr><td bgcolor="'.$bg.'" style="'.$F.'background:'.$bg.';padding:10px 12px;border-bottom:1px solid #eef2f7;font-size:13px;color:#0f172a" valign="top"><div><b style="color:#0f2a5c">'.$e($r['order_no']).'</b>'.($r['order_no']?' &nbsp;':'').$e($r['title']).'</div>'.($sub?'<div style="font-size:11px;color:#64748b;margin-top:2px">'.$e(implode('  ·  ',$sub)).'</div>':'').$chip.'</td>'
        .'<td bgcolor="'.$bg.'" width="150" style="'.$F.'background:'.$bg.';padding:10px 8px;border-bottom:1px solid #eef2f7;font-size:12px;color:#334155" valign="top">'.$av($r['person']).'</td>'
        .'<td bgcolor="'.$bg.'" width="105" align="right" style="'.$F.'background:'.$bg.';padding:10px 12px;border-bottom:1px solid #eef2f7;font-size:12px;color:#334155" valign="top">'.$dt.'</td></tr>';}
    return $h.'</table></td></tr>';};
  $o.=$sec('Completed this week','Delivered since Monday','#16a34a',$d['done'],'done');
  $o.=$sec('Ongoing','Work in progress and in review right now','#2563eb',$d['going'],'going');
  $o.=$sec('Starting in the next 3 days','Scheduled to kick off by '.$fd($d['next']),'#d97706',$d['soon'],'soon');
  // footer
  $o.='<tr><td style="height:26px;font-size:0;line-height:0">&nbsp;</td></tr><tr><td bgcolor="#0f2a5c" style="background:#0f2a5c;'.$F.'padding:18px 26px"><table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr>'
    .($tfimg?'<td width="40" valign="middle"><img src="'.$e($as['tf']).'" alt="" width="30" height="30" style="width:30px;height:30px;border-radius:8px;display:block;border:0"></td>':'')
    .'<td valign="middle" style="font-size:12px;color:#cbd5e1;line-height:1.5"><b style="color:#ffffff">Sent automatically by TaskFlow</b><br>Live task tracking, hours and reporting for engineering and drafting teams.</td>'
    .'<td align="right" valign="middle"><a href="https://gurucadd.com" style="display:inline-block;background:#38bdf8;color:#0f2a5c;font-weight:800;font-size:12px;text-decoration:none;border-radius:8px;padding:9px 14px;white-space:nowrap">Discover TaskFlow &rarr;</a></td></tr></table></td></tr>';
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
function out($d){
  global $a,$db,$WRITEA,$uid,$me,$W,$in,$AUD_T;
  if(isset($a,$db)&&in_array($a,$WRITEA,true)&&empty($d['error'])){try{$db->exec("INSERT INTO meta(k,v) VALUES('ver',1) ON DUPLICATE KEY UPDATE v=v+1");}catch(Throwable $e){}}
  if(isset($a,$db,$me)&&in_array($a,$WRITEA,true)&&empty($d['error'])){aud_log($db,$uid,(string)($me['username']??$me['name']),'activity',$a,aud_detail(),$W?:null,(int)($in['id']??$in['task_id']??0)?:null);}
  echo json_encode($d);exit;
}
if(($_GET['a']??'')==='remind'){header('Content-Type: text/plain');
  if(!hash_equals(rem_key(),(string)($_GET['key']??''))){http_response_code(403);exit("bad key\n");}
  echo rem_run($db,'',false)."\n".sr_run($db)."\n";exit;}
if(empty($_SESSION['uid'])){http_response_code(401);out(['error'=>'auth']);}
if(($_GET['a']??'')==='wiki_img'){
  try{$q=$db->prepare("SELECT mime,data FROM wiki_media WHERE id=?");$q->execute([(int)($_GET['id']??0)]);$r=$q->fetch();}catch(Throwable $e){$r=false;}
  if(!$r){http_response_code(404);exit;}
  header('Content-Type: '.$r['mime']);header('Cache-Control: private, max-age=604800');header('X-Content-Type-Options: nosniff');echo $r['data'];exit;
}
if(($_SERVER['HTTP_X_REQUESTED_WITH']??'')!=='fetch'){http_response_code(400);out(['error'=>'bad request']);}
$in=json_decode(file_get_contents('php://input'),true)?:[];
$a=$_GET['a']??'';$uid=(int)$_SESSION['uid'];
$q=$db->prepare("SELECT id,name,username,role FROM users WHERE id=?");$q->execute([$uid]);$me=$q->fetch();
if(!$me){session_destroy();http_response_code(401);out(['error'=>'auth']);}
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
  if(!empty($_SESSION['schema_v33']))return;
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
  $db->exec("CREATE TABLE IF NOT EXISTS msg_reads(user_id INT NOT NULL,peer_id INT NOT NULL,last_id INT NOT NULL DEFAULT 0,PRIMARY KEY(user_id,peer_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  try{$c=$db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();if($c&&stripos($c['Type'],'enum')!==false)$db->exec("ALTER TABLE users MODIFY role VARCHAR(20) NOT NULL DEFAULT 'member'");}catch(Throwable $e){}
  $db->exec("CREATE TABLE IF NOT EXISTS delete_requests(id INT AUTO_INCREMENT PRIMARY KEY,ws_id INT NOT NULL,kind VARCHAR(12) NOT NULL,target_id INT NOT NULL,target_name VARCHAR(255) NOT NULL DEFAULT '',requested_by INT NOT NULL,status VARCHAR(10) NOT NULL DEFAULT 'pending',decided_by INT NULL,created_at DATETIME NOT NULL,decided_at DATETIME NULL,KEY st(status)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS task_assignees(task_id INT NOT NULL,user_id INT NOT NULL,PRIMARY KEY(task_id,user_id),KEY u(user_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $db->exec("CREATE TABLE IF NOT EXISTS presence(user_id INT NOT NULL PRIMARY KEY,seen DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  if(!$db->query("SHOW COLUMNS FROM workspaces LIKE 'col_grp'")->fetch())$db->exec("ALTER TABLE workspaces ADD col_grp INT NOT NULL DEFAULT 0");
  foreach(["cat VARCHAR(80) NOT NULL DEFAULT ''","tags VARCHAR(400) NOT NULL DEFAULT ''","src VARCHAR(40) NOT NULL DEFAULT ''"] as $cd){$cn=strtok($cd,' ');if(!$db->query("SHOW COLUMNS FROM wiki_pages LIKE '$cn'")->fetch())$db->exec("ALTER TABLE wiki_pages ADD $cd");}
  $db->exec("CREATE TABLE IF NOT EXISTS wiki_media(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL DEFAULT '',mime VARCHAR(40) NOT NULL,data MEDIUMBLOB NOT NULL,created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
  $_SESSION['schema_v33']=1;
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
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,$new,mb_substr($b,0,1900)]);push_q($new,'TaskFlow',$b);}catch(Throwable $e){}}
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
      $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,(int)$ad,mb_substr($b,0,1900)]);push_q((int)$ad,'TaskFlow',$b);}
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
  $db->prepare("INSERT INTO push_tokens(token,user_id,platform,updated_at) VALUES(?,?,?,NOW()) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id),updated_at=NOW()")->execute([$t,$uid,'android']);out(['ok'=>1]);
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
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,(int)$r['requested_by'],mb_substr(($ap?"✅ Approved: ":"❌ Not approved: ").$kn." \"".$r['target_name']."\" delete request.",1900)]);push_q((int)$r['requested_by'],'TaskFlow',($ap?'Approved: ':'Not approved: ').$kn.' delete request');
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
  $db->prepare("INSERT INTO messages(from_id,to_id,body,created_at) VALUES(?,?,?,NOW())")->execute([$uid,$peer,$b]);$id=(int)$db->lastInsertId();push_q($peer,$peer?($me['name']??'Message'):'Team chat: '.($me['name']??''),$b);
  $db->prepare("INSERT INTO msg_reads(user_id,peer_id,last_id) VALUES(?,?,?) ON DUPLICATE KEY UPDATE last_id=GREATEST(last_id,VALUES(last_id))")->execute([$uid,$peer,$id]);out(['ok'=>1,'id'=>$id]);
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
