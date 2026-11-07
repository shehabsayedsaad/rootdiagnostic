<?php
require __DIR__.'/ui.php'; secure_headers(); start_session();
if(cust_id()){ header('Location: ../index.html'); exit; }
$err=''; $email=''; $okmsg=isset($_GET['ok'])?'تم تغيير كلمة السر. سجّل الدخول.':'';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=strtolower(trim($_POST['email'] ?? ''));
  try{
    $pdo=db();
    if(!csrf_ok()) $err='انتهت الجلسة، أعد المحاولة.';
    elseif(throttle_hit($pdo)) $err='محاولات كتير. جرّب بعد 15 دقيقة.';
    else{
      $s=$pdo->prepare('SELECT id,pass_hash FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch();
      if($u && password_verify((string)($_POST['p'] ?? ''),$u['pass_hash'])){
        session_regenerate_id(true); $_SESSION['cust']=(int)$u['id']; header('Location: ../index.html'); exit;
      }
      log_hit($pdo); $err='البريد أو كلمة السر غير صحيحة.';
    }
  }catch(Throwable $e){ $err='خطأ في الخادم.'; }
}
ui_head('تسجيل الدخول'); ?>
<h1>تسجيل الدخول</h1>
<p class="sub">ليس لديك حساب؟ <a href="register.php">أنشئ حساب جديد</a></p>
<?php if($err) echo '<div class="msg err">'.h($err).'</div>'; if($okmsg) echo '<div class="msg ok">'.h($okmsg).'</div>'; ?>
<form method="post" autocomplete="on"><input type="hidden" name="csrf" value="<?=h(csrf())?>">
<label for="email">البريد الإلكتروني</label><input id="email" name="email" type="email" dir="ltr" placeholder="ادخل البريد الإلكتروني" value="<?=h($email)?>" autocomplete="email" required>
<label for="p">كلمة السر</label><?php pw_field('p','ادخل كلمة السر','current-password'); ?>
<p style="margin:16px 0 0"><a class="link" href="forgot.php">نسيت كلمة السر؟</a></p>
<button class="go">الدخول</button></form>
<?php ui_foot();
