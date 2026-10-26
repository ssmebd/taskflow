<?php
// Auto-deploy hook. GitHub calls this after each push (Settings > Webhooks).
// Needs deploy_config.php next to this file (NOT in git). Only the secret is required:
//   <?php return ['secret'=>'long-random-text'];
// Optional keys, full example:
//   <?php return ['secret'=>'long-random-text','repo'=>'/home/CPANELUSER/repositories/taskflow','live'=>__DIR__,'git'=>'/usr/local/cpanel/3rdparty/bin/git'];
header('Content-Type: text/plain');
$cfgf=__DIR__.'/deploy_config.php';
if(!is_file($cfgf)){http_response_code(500);exit("deploy_config.php missing\n");}
$cfg=require $cfgf;
if(empty($cfg['repo']))$cfg['repo']=dirname(__DIR__,2).'/repositories/taskflow';
if(empty($cfg['live']))$cfg['live']=__DIR__;
if(empty($cfg['git'])&&is_file('/usr/local/cpanel/3rdparty/bin/git'))$cfg['git']='/usr/local/cpanel/3rdparty/bin/git';
$body=file_get_contents('php://input');
$sig=$_SERVER['HTTP_X_HUB_SIGNATURE_256']??'';
if(!$sig||!hash_equals('sha256='.hash_hmac('sha256',$body,$cfg['secret']),$sig)){http_response_code(403);exit("bad signature\n");}
if(($_SERVER['HTTP_X_GITHUB_EVENT']??'')==='ping')exit("pong\n");
if(!function_exists('shell_exec')||in_array('shell_exec',array_map('trim',explode(',',ini_get('disable_functions'))))){http_response_code(500);exit("shell_exec disabled on this host\n");}
$repo=escapeshellarg($cfg['repo']);$git=escapeshellarg($cfg['git']??'git');
$home=preg_match('#^(/home\d*/[^/]+)/#',$cfg['repo'],$m)?$m[1]:(getenv('HOME')?:'');
$out=shell_exec(($home?'export HOME='.escapeshellarg($home).'; ':'')."cd $repo && $git fetch origin 2>&1 && $git reset --hard origin/main 2>&1");
$ok=true;
foreach(['index.php','api.php','login.php','logo.php','remember.php'] as $f){
  if(!copy($cfg['repo'].'/'.$f,$cfg['live'].'/'.$f)){$ok=false;$out.="copy failed: $f\n";}
}
if(function_exists('opcache_reset'))@opcache_reset();
echo ($ok?"deployed\n":"PARTIAL\n").$out;
