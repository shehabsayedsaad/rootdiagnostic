<?php
require __DIR__.'/../lib.php'; secure_headers(); start_session();
$lid=lab_id(); if(!$lid){ header('Location: ../admin/login.php'); exit; }
$pdo=db(); $s=$pdo->prepare('SELECT name FROM labs WHERE id=? AND active=1'); $s->execute([$lid]); $lab=$s->fetch();
if(!$lab){ $_SESSION=[]; session_destroy(); header('Location: ../admin/login.php'); exit; }
$L=['new'=>'جديد','progress'=>'قيد التنفيذ','done'=>'تم'];
$f=$_GET['status'] ?? ''; $w='lab_id=?'; $p=[$lid];
if(isset($L[$f])){ $w.=' AND status=?'; $p[]=$f; }
$st=$pdo->prepare("SELECT id,type,details,status,created_at FROM orders WHERE $w ORDER BY id DESC LIMIT 200"); $st->execute($p); $rows=$st->fetchAll();
$c=$pdo->prepare('SELECT status,COUNT(*) FROM orders WHERE lab_id=? GROUP BY status'); $c->execute([$lid]); $cnt=$c->fetchAll(PDO::FETCH_KEY_PAIR);
?><!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>طلبات <?=h($lab['name'])?></title>
<style>
:root{--ink:#0B2B33;--line:#D3DEDC;--brand:#051694;--glow:#5BC8FF}
body{margin:0;font-family:Tahoma,'Segoe UI',sans-serif;background:#EEF3F2;color:var(--ink)}
header{background:linear-gradient(160deg,#020B3A,#051694);color:#fff;padding:14px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
header img{width:44px;border-radius:50%}header h1{font-size:1.05rem;margin:0;flex:1}
.w{max-width:900px;margin:0 auto;padding:18px}
button,.b{background:var(--brand);color:#fff;border:0;border-radius:999px;padding:8px 18px;cursor:pointer;font:inherit;text-decoration:none;display:inline-block}.b.o,button.o{background:#fff;color:var(--ink);border:1px solid var(--line)}
.chips{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}.chips a{background:#fff;border:1px solid var(--line);border-radius:999px;padding:6px 14px;font-size:.85rem;color:var(--ink);text-decoration:none}.chips a.on{background:var(--brand);color:#fff;border-color:var(--brand)}
.o1{background:#fff;border:1px solid var(--line);border-radius:10px;padding:14px;margin-bottom:12px;border-inline-start:5px solid #B3374A}.o1.progress{border-inline-start-color:var(--glow)}.o1.done{border-inline-start-color:#1E7A67}
.hd{display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px}.hd small{color:#5A6E73}
pre{white-space:pre-wrap;font-family:inherit;background:#F4F8F7;border:1px dashed var(--line);border-radius:8px;padding:10px;margin:0;font-size:.9rem}
</style></head><body>
<header><img src="../assets/logo.jpg" alt=""><h1><?=h($lab['name'])?></h1><a class="b o" href="../index.html">طلب جديد</a>
<form method="post" action="logout.php" style="margin:0"><input type="hidden" name="csrf" value="<?=h(csrf())?>"><button class="o">خروج</button></form></header>
<div class="w">
<div class="chips"><a href="index.php"<?=$f===''?' class="on"':''?>>الكل (<?=array_sum($cnt)?>)</a><?php foreach($L as $k=>$v) echo '<a href="?status='.h($k).'"'.($f===$k?' class="on"':'').'>'.h($v).' ('.(int)($cnt[$k] ?? 0).')</a>'; ?></div>
<?php if(!$rows) echo '<p>لا توجد طلبات بعد. اطلب من الصفحة الرئيسية وأنت مسجّل الدخول وهتظهر هنا.</p>'; foreach($rows as $r): ?>
<div class="o1 <?=h($r['status'])?>"><div class="hd"><b>#<?=(int)$r['id']?> — <?=$r['type']==='service'?'صيانة':'منتجات'?> — <?=h($L[$r['status']] ?? '')?></b><small><?=h(cairo($r['created_at']))?></small></div><pre><?=h($r['details'])?></pre></div>
<?php endforeach; ?>
</div></body></html>
