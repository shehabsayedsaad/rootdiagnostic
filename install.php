<?php
require __DIR__.'/lib.php'; secure_headers();
$lock = __DIR__.'/install.lock'; $msg=''; $done=false;
if(file_exists($lock)){ http_response_code(403); exit('التثبيت تم قبل كده. احذف ملف install.php من السيرفر.'); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $u=trim($_POST['u'] ?? ''); $p=(string)($_POST['p'] ?? '');
  if(!preg_match('/^[A-Za-z0-9_.-]{3,40}$/',$u)) $msg='اسم المستخدم: حروف إنجليزية وأرقام فقط (3 إلى 40).';
  elseif(strlen($p)<10) $msg='كلمة السر لازم تكون 10 حروف على الأقل.';
  else try {
    $pdo=db();
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders(
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      type ENUM('products','service') NOT NULL,
      name VARCHAR(100) NOT NULL, phone VARCHAR(30) NOT NULL, email VARCHAR(150) NOT NULL DEFAULT '',
      lab VARCHAR(150) NOT NULL DEFAULT '', city VARCHAR(150) NOT NULL DEFAULT '',
      details TEXT NOT NULL,
      status ENUM('new','progress','done') NOT NULL DEFAULT 'new',
      ip VARCHAR(45) NOT NULL DEFAULT '', created_at DATETIME NOT NULL,
      KEY(status), KEY(created_at), KEY(ip,created_at)) DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins(
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, username VARCHAR(40) NOT NULL UNIQUE,
      pass_hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL) DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_log(
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, ip VARCHAR(45) NOT NULL, ts DATETIME NOT NULL, KEY(ip,ts)) DEFAULT CHARSET=utf8mb4");
    if((int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn()>0){ $msg='يوجد أدمن بالفعل.'; }
    else {
      $pdo->prepare('INSERT INTO admins(username,pass_hash,created_at) VALUES(?,?,?)')->execute([$u,password_hash($p,PASSWORD_DEFAULT),now()]);
      file_put_contents($lock,'installed '.now()); $done=true;
    }
  } catch(Throwable $e){ $msg='تعذّر الاتصال بقاعدة البيانات. راجع config.php.'; }
}
?><!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>تثبيت</title>
<style>body{font-family:Tahoma,sans-serif;background:#EEF3F2;display:grid;place-items:center;min-height:100vh;margin:0}form,.b{background:#fff;padding:28px;border-radius:12px;max-width:380px;width:92%;border:1px solid #D3DEDC}input{width:100%;padding:10px;margin:6px 0 14px;border:1px solid #D3DEDC;border-radius:8px;font-size:1rem}button{background:#051694;color:#fff;border:0;padding:11px 22px;border-radius:999px;font-size:1rem;cursor:pointer}.e{color:#B3374A}</style></head><body>
<?php if($done): ?><div class="b"><h3>تم التثبيت</h3><p>احذف ملف <b>install.php</b> من السيرفر الآن، ثم ادخل على <a href="admin/login.php">لوحة الأدمن</a>.</p></div>
<?php else: ?><form method="post"><h3>إنشاء حساب الأدمن</h3><?php if($msg) echo '<p class="e">'.h($msg).'</p>'; ?>
<label>اسم المستخدم</label><input name="u" required dir="ltr"><label>كلمة السر (10 حروف على الأقل)</label><input name="p" type="password" required dir="ltr" autocomplete="new-password"><button>تثبيت</button></form><?php endif; ?>
</body></html>
