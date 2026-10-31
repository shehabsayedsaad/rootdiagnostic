<?php
require __DIR__.'/../lib.php'; secure_headers(); start_session();
if(!empty($_SESSION['admin'])){ header('Location: index.php'); exit; }
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    $pdo=db();
    $c=$pdo->prepare('SELECT COUNT(*) FROM login_log WHERE ip=? AND ts>?'); $c->execute([ip(),ago(900)]);
    if((int)$c->fetchColumn()>=8){ $err='محاولات كتير. جرّب بعد 15 دقيقة.'; }
    elseif(!csrf_ok()){ $err='انتهت الجلسة، أعد المحاولة.'; }
    else{
      $s=$pdo->prepare('SELECT id,pass_hash FROM admins WHERE username=?'); $s->execute([trim($_POST['u'] ?? '')]); $a=$s->fetch();
      if($a && password_verify((string)($_POST['p'] ?? ''),$a['pass_hash'])){
        session_regenerate_id(true); $_SESSION['admin']=(int)$a['id']; header('Location: index.php'); exit;
      }
      $pdo->prepare('INSERT INTO login_log(ip,ts) VALUES(?,?)')->execute([ip(),now()]);
      $err='بيانات الدخول غير صحيحة.';
    }
  }catch(Throwable $e){ $err='خطأ في الخادم.'; }
}
?><!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>دخول الأدمن</title>
<style>body{font-family:Tahoma,sans-serif;background:linear-gradient(160deg,#020B3A,#051694);display:grid;place-items:center;min-height:100vh;margin:0}form{background:#fff;padding:28px;border-radius:14px;max-width:360px;width:92%;text-align:center}img{width:72px;border-radius:50%}input{width:100%;padding:10px;margin:6px 0 12px;border:1px solid #D3DEDC;border-radius:8px;font-size:1rem}button{background:#051694;color:#fff;border:0;padding:11px 26px;border-radius:999px;font-size:1rem;cursor:pointer}.e{color:#B3374A}</style></head><body>
<form method="post"><img src="../assets/logo.jpg" alt=""><h3>لوحة الطلبات</h3><?php if($err) echo '<p class="e">'.h($err).'</p>'; ?>
<input type="hidden" name="csrf" value="<?=h(csrf())?>"><input name="u" placeholder="اسم المستخدم" dir="ltr" required autocomplete="username"><input name="p" type="password" placeholder="كلمة السر" dir="ltr" required autocomplete="current-password"><button>دخول</button></form></body></html>
