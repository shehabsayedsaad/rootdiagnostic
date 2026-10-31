<?php
ini_set('display_errors','0');
date_default_timezone_set('UTC');
$GLOBALS['cfg'] = require __DIR__.'/config.php';
function cfg($k){ return $GLOBALS['cfg'][$k] ?? null; }
function db(): PDO {
  static $p;
  if(!$p){
    $p = new PDO('mysql:host='.cfg('db_host').';dbname='.cfg('db_name').';charset=utf8mb4', cfg('db_user'), cfg('db_pass'), [
      PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
  }
  return $p;
}
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function ip(){ return substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45); }
function now(){ return gmdate('Y-m-d H:i:s'); }
function ago($sec){ return gmdate('Y-m-d H:i:s', time()-$sec); }
function cairo($dt){ $d=new DateTime($dt,new DateTimeZone('UTC')); $d->setTimezone(new DateTimeZone('Africa/Cairo')); return $d->format('Y-m-d H:i'); }
function wa_num($p){ $p=preg_replace('/\D/','',$p); return ($p!=='' && $p[0]==='0') ? '20'.substr($p,1) : $p; }
function start_session(){
  if(session_status()===PHP_SESSION_NONE){
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
    session_name('rdadm'); session_start();
  }
}
function csrf(){ start_session(); if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16)); return $_SESSION['csrf']; }
function csrf_ok(){ start_session(); return isset($_POST['csrf']) && hash_equals($_SESSION['csrf'] ?? '', (string)$_POST['csrf']); }
function secure_headers(){
  header('X-Frame-Options: DENY'); header('X-Content-Type-Options: nosniff');
  header('Referrer-Policy: same-origin'); header('Cache-Control: no-store');
}
