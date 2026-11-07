<?php
require __DIR__.'/ui.php'; secure_headers(); start_session();
if(cust_id()){ header('Location: ../index.html'); exit; }
$err=''; $name=''; $email='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $name=trim($_POST['name'] ?? ''); $email=strtolower(trim($_POST['email'] ?? '')); $p=(string)($_POST['p'] ?? '');
  try{
    $pdo=db();
    if(!csrf_ok()) $err='انتهت الجلسة، أعد المحاولة.';
    elseif(throttle_hit($pdo)) $err='محاولات كتير. جرّب بعد 15 دقيقة.';
    elseif(mb_strlen($name)<2 || mb_strlen($name)>100) $err='اكتب اسمك الكامل.';
    elseif(!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>150) $err='اكتب بريدًا إلكترونيًا صحيحًا.';
    elseif(strlen($p)<8) $err='كلمة السر 8 حروف على الأقل.';
    elseif(empty($_POST['terms'])) $err='لازم توافق على الشروط والأحكام.';
    else{
      log_hit($pdo);
      $x=$pdo->prepare('SELECT id FROM users WHERE email=?'); $x->execute([$email]);
      if($x->fetch()) $err='هذا البريد مسجل بالفعل. سجّل الدخول.';
      else{
        $pdo->prepare('INSERT INTO users(name,email,pass_hash,created_at) VALUES(?,?,?,?)')->execute([$name,$email,password_hash($p,PASSWORD_DEFAULT),now()]);
        session_regenerate_id(true); $_SESSION['cust']=(int)$pdo->lastInsertId(); header('Location: ../index.html'); exit;
      }
    }
  }catch(Throwable $e){ $err='خطأ في الخادم.'; }
}
ui_head('التسجيل','login.php'); ?>
<h1>التسجيل</h1>
<p class="sub">لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
<?php if($err) echo '<div class="msg err">'.h($err).'</div>'; ?>
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>">
<label for="name">الاسم الكامل</label><input id="name" name="name" type="text" placeholder="ادخل الاسم" value="<?=h($name)?>" autocomplete="name" required>
<label for="email">البريد الإلكتروني</label><input id="email" name="email" type="email" dir="ltr" placeholder="ادخل البريد الإلكتروني" value="<?=h($email)?>" autocomplete="email" required>
<label for="p">كلمة السر</label><?php pw_field('p','ادخل كلمة السر (8 حروف على الأقل)','new-password'); ?>
<label class="row" for="terms"><input id="terms" name="terms" type="checkbox" value="1"><span>أوافق على <a class="link" href="../terms.html" target="_blank" rel="noopener">الشروط والأحكام</a></span></label>
<button class="go">إنشاء حساب</button></form>
<?php ui_foot();
