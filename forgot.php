<?php
require __DIR__.'/ui.php'; secure_headers(); start_session();
$done=false;$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $email=strtolower(trim($_POST['email'] ?? ''));
  try{
    $pdo=db();
    if(!csrf_ok()) $err='انتهت الجلسة، أعد المحاولة.';
    elseif(throttle_hit($pdo,5)) $err='محاولات كتير. جرّب بعد 15 دقيقة.';
    else{
      log_hit($pdo); $done=true;
      $s=$pdo->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$email]); $u=$s->fetch();
      if($u && site_url() && cfg('mail_from')){
        $tok=bin2hex(random_bytes(32));
        $pdo->prepare('DELETE FROM resets WHERE user_id=?')->execute([$u['id']]);
        $pdo->prepare('INSERT INTO resets(user_id,token_hash,expires_at) VALUES(?,?,?)')->execute([$u['id'],hash('sha256',$tok),gmdate('Y-m-d H:i:s',time()+3600)]);
        $subj=mb_encode_mimeheader('استعادة كلمة السر','UTF-8');
        $body="لإعادة تعيين كلمة السر افتح الرابط (صالح لمدة ساعة):\n".site_url().'/account/reset.php?t='.$tok."\n\nلو ماطلبتش ده، تجاهل الرسالة.";
        @mail($email,$subj,$body,'From: '.preg_replace('/[\r\n]/','',cfg('mail_from'))."\r\nContent-Type: text/plain; charset=UTF-8\r\nMIME-Version: 1.0");
      }
    }
  }catch(Throwable $e){ $err='خطأ في الخادم.'; }
}
ui_head('استعادة كلمة السر','login.php'); ?>
<h1>نسيت كلمة السر؟</h1>
<p class="sub">اكتب بريدك وهنبعتلك رابط لإعادة التعيين.</p>
<?php if($err) echo '<div class="msg err">'.h($err).'</div>'; if($done) echo '<div class="msg ok">لو البريد مسجل عندنا، هيوصلك رابط الاستعادة خلال دقائق. افحص Spam كمان.</div>'; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>">
<label for="email">البريد الإلكتروني</label><input id="email" name="email" type="email" dir="ltr" placeholder="ادخل البريد الإلكتروني" autocomplete="email" required>
<button class="go">إرسال الرابط</button></form>
<?php ui_foot();
