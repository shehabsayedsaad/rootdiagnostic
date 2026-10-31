<?php
require __DIR__.'/../lib.php'; secure_headers(); start_session();
if(empty($_SESSION['admin'])){ header('Location: login.php'); exit; }
$pdo=db(); $flash='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(!csrf_ok()){ http_response_code(400); exit('CSRF'); }
  $id=(int)($_POST['id'] ?? 0);
  if(($_POST['a'] ?? '')==='status' && in_array($_POST['s'] ?? '',['new','progress','done'],true)){
    $pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$_POST['s'],$id]); $flash='تم تحديث الحالة.';
  } elseif(($_POST['a'] ?? '')==='delete'){
    $pdo->prepare('DELETE FROM orders WHERE id=?')->execute([$id]); $flash='تم حذف الطلب.';
  }
}
$f_status=$_GET['status'] ?? ''; $f_type=$_GET['type'] ?? ''; $q=trim($_GET['q'] ?? '');
$w=[];$p=[];
if(in_array($f_status,['new','progress','done'],true)){ $w[]='status=?'; $p[]=$f_status; }
if(in_array($f_type,['products','service'],true)){ $w[]='type=?'; $p[]=$f_type; }
if($q!==''){ $like='%'.addcslashes($q,'%_\\').'%'; $w[]='(name LIKE ? OR phone LIKE ? OR email LIKE ? OR lab LIKE ? OR details LIKE ?)'; array_push($p,$like,$like,$like,$like,$like); }
$sql='SELECT * FROM orders'.($w?' WHERE '.implode(' AND ',$w):'').' ORDER BY id DESC LIMIT 200';
$st=$pdo->prepare($sql); $st->execute($p); $rows=$st->fetchAll();
$cnt=$pdo->query("SELECT status,COUNT(*) c FROM orders GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$L=['new'=>'جديد','progress'=>'قيد التنفيذ','done'=>'تم'];
?><!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>لوحة الطلبات</title>
<style>
:root{--ink:#0B2B33;--line:#D3DEDC;--brand:#051694;--glow:#5BC8FF}
body{margin:0;font-family:Tahoma,'Segoe UI',sans-serif;background:#EEF3F2;color:var(--ink)}
header{background:linear-gradient(160deg,#020B3A,#051694);color:#fff;padding:14px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
header img{width:44px;border-radius:50%}header h1{font-size:1.05rem;margin:0;flex:1}
.w{max-width:960px;margin:0 auto;padding:18px}
.stats{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}.stats span{background:#fff;border:1px solid var(--line);border-radius:999px;padding:6px 14px;font-size:.85rem}
form.f{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}input,select{padding:9px 11px;border:1px solid var(--line);border-radius:8px;font:inherit;background:#fff}
button,.b{background:var(--brand);color:#fff;border:0;border-radius:999px;padding:8px 18px;cursor:pointer;font:inherit;text-decoration:none;display:inline-block}.b.o,button.o{background:#fff;color:var(--ink);border:1px solid var(--line)}
.o1{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:12px;border-inline-start:5px solid #B3374A}
.o1.progress{border-inline-start-color:var(--glow)}.o1.done{border-inline-start-color:#1E7A67}
.hd{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px}.hd small{color:#5A6E73}
pre{white-space:pre-wrap;font-family:inherit;background:#F4F8F7;border:1px dashed var(--line);border-radius:8px;padding:10px;margin:0 0 10px;font-size:.9rem}
.ac{display:flex;gap:8px;flex-wrap:wrap;align-items:center}.ac form{margin:0}.fl{background:#E4F1EC;color:#1E7A67;padding:8px 14px;border-radius:8px;margin-bottom:12px}
</style></head><body>
<header><img src="../assets/logo.jpg" alt=""><h1>لوحة طلبات روت دياجنوستيك</h1>
<form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><button class="o">خروج</button></form></header>
<div class="w">
<?php if($flash) echo '<div class="fl">'.h($flash).'</div>'; ?>
<div class="stats"><?php foreach($L as $k=>$v) echo '<span>'.h($v).': <b>'.(int)($cnt[$k] ?? 0).'</b></span>'; ?></div>
<form class="f" method="get"><input name="q" value="<?=h($q)?>" placeholder="بحث بالاسم أو الموبايل أو الجهة">
<select name="status"><option value="">كل الحالات</option><?php foreach($L as $k=>$v) echo '<option value="'.h($k).'"'.($f_status===$k?' selected':'').'>'.h($v).'</option>'; ?></select>
<select name="type"><option value="">منتجات وصيانة</option><option value="products"<?=$f_type==='products'?' selected':''?>>منتجات</option><option value="service"<?=$f_type==='service'?' selected':''?>>صيانة</option></select>
<button>تصفية</button></form>
<?php if(!$rows) echo '<p>لا توجد طلبات.</p>'; foreach($rows as $r): ?>
<div class="o1 <?=h($r['status'])?>"><div class="hd"><b>#<?=(int)$r['id']?> — <?=$r['type']==='service'?'صيانة':'منتجات'?> — <?=h($r['name'])?></b><small><?=h(cairo($r['created_at']))?></small></div>
<?php if($r['email']!==''): ?><div style="margin-bottom:8px">✉ <a href="mailto:<?=h($r['email'])?>"><?=h($r['email'])?></a></div><?php endif; ?>
<pre><?=h($r['details'])?></pre>
<div class="ac">
<form method="post"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="a" value="status"><input type="hidden" name="id" value="<?=(int)$r['id']?>">
<select name="s" onchange="this.form.submit()"><?php foreach($L as $k=>$v) echo '<option value="'.h($k).'"'.($r['status']===$k?' selected':'').'>'.h($v).'</option>'; ?></select></form>
<a class="b o" target="_blank" rel="noopener" href="https://wa.me/<?=h(wa_num($r['phone']))?>">رد على واتساب</a>
<a class="b o" href="tel:<?=h(preg_replace('/[^\d+]/','',$r['phone']))?>">اتصال</a>
<form method="post" onsubmit="return confirm('حذف الطلب نهائيًا؟')"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><input type="hidden" name="a" value="delete"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button class="o">حذف</button></form>
</div></div>
<?php endforeach; ?>
</div></body></html>
