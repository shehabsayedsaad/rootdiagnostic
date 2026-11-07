<?php
require __DIR__.'/ui.php'; secure_headers(); start_session();
$id=cust_id(); if(!$id){ header('Location: login.php'); exit; }
$pdo=db(); $L=['new'=>'جديد','progress'=>'قيد التنفيذ','done'=>'تم'];
$u=$pdo->prepare('SELECT name,email FROM users WHERE id=?'); $u->execute([$id]); $me=$u->fetch();
$o=$pdo->prepare('SELECT id,type,status,details,created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 100'); $o->execute([$id]); $rows=$o->fetchAll();
ui_head('طلباتي','../index.html'); ?>
<h1>طلباتي</h1><p class="sub"><?=h($me['name'] ?? '')?> · <span dir="ltr"><?=h($me['email'] ?? '')?></span></p>
<?php if(!$rows) echo '<p class="sub">لا توجد طلبات حتى الآن.</p>'; foreach($rows as $r): ?>
<div class="ord"><b>#<?=(int)$r['id']?> — <?=$r['type']==='service'?'صيانة':'منتجات'?> <span class="st <?=h($r['status'])?>"><?=h($L[$r['status']] ?? $r['status'])?></span></b><small><?=h(cairo($r['created_at']))?></small><pre><?=h($r['details'])?></pre></div>
<?php endforeach; ?>
<form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><button class="go" style="background:#1f1b2a;box-shadow:none;border:1px solid #2e2840">تسجيل الخروج</button></form>
<?php ui_foot();
