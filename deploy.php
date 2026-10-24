<?php
// Auto-deploy hook. GitHub calls this after each push (Settings > Webhooks).
// Needs deploy_config.php next to this file (NOT in git), example:
//   <?php return ['secret'=>'long-random-text','repo'=>'/home/CPANELUSER/repositories/taskflow','live'=>__DIR__];
header('Content-Type: text/plain');
$cfgf=__DIR__.'/deploy_config.php';
if(!is_file($cfgf)){http_response_code(500);exit("deploy_config.php missing\n");}
$cfg=require $cfgf;
$body=file_get_contents('php://input');
$sig=$_SERVER['HTTP_X_HUB_SIGNATURE_256']??'';
if(!$sig||!hash_equals('sha256='.hash_hmac('sha256',$body,$cfg['secret']),$sig)){http_response_code(403);exit("bad signature\n");}
if(($_SERVER['HTTP_X_GITHUB_EVENT']??'')==='ping')exit("pong\n");
if(!function_exists('shell_exec')||in_array('shell_exec',array_map('trim',explode(',',ini_get('disable_functions'))))){http_response_code(500);exit("shell_exec disabled on this host\n");}
$repo=escapeshellarg($cfg['repo']);
$out=shell_exec("cd $repo && git fetch origin 2>&1 && git reset --hard origin/main 2>&1");
$ok=true;
foreach(['index.php','api.php','login.php','logo.php'] as $f){
  if(!copy($cfg['repo'].'/'.$f,$cfg['live'].'/'.$f)){$ok=false;$out.="copy failed: $f\n";}
}
if(function_exists('opcache_reset'))@opcache_reset();
echo ($ok?"deployed\n":"PARTIAL\n").$out;
