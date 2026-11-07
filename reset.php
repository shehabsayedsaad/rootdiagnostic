<?php
require __DIR__.'/ui.php'; secure_headers(); start_session();
$tok=(string)($_GET['t'] ?? $_POST['t'] ?? ''); $err=''; $valid=false; $uid=0;
try{
  $pdo=db();
  if(preg_match('/^[a-f0-9]{64}$/',$tok)){
    $s=$pdo->prepare('SELECT user_id FROM resets WHERE token_hash=? AND expires_at>?'); $s->execute([hash('sha256',$tok),now()]); $r=$s->fetch();
    if($r){ $valid=true; $uid=(int)$r['user_id']; }
  }
  if($valid && $_SERVER['REQUEST_METHOD']==='POST'){
    $p=(string)($_POST['p'] ?? '');
    if(!csrf_ok()) $err='انتهت الجلسة، أعد المحاولة.';
    elseif(strlen($p)<8) $err='كلمة السر 8 حروف على الأقل.';
    else{
      $pdo->prepare('UPDATE users SET pass_hash=? WHERE id=?')->execute([password_hash($p,PASSWORD_DEFAULT),$uid]);
      $pdo->prepare('DELETE FROM resets WHERE user_id=?')->execute([$uid]);
      header('Location: login.php?ok=1'); exit;
    }
  }
}catch(Throwable $e){ $err='خطأ في الخادم.'; }
ui_head('كلمة سر جديدة'); ?>
<h1>كلمة سر جديدة</h1>
<?php if(!$valid): ?><div class="msg err">الرابط غير صالح أو انتهت مدته.</div><p><a class="link" href="forgot.php">اطلب رابطًا جديدًا</a></p>
<?php else: if($err) echo '<div class="msg err">'.h($err).'</div>'; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="t" value="<?=h($tok)?>">
<label for="p">كلمة السر الجديدة</label><?php pw_field('p','8 حروف على الأقل','new-password'); ?>
<button class="go">حفظ</button></form><?php endif; ui_foot();
