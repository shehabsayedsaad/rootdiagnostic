<?php
require __DIR__.'/../lib.php';
function ui_head($title,$back=''){ ?><!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($title)?> — روت دياجنوستيك</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;font-family:'IBM Plex Sans Arabic',Tahoma,sans-serif;color:#fff;background:radial-gradient(900px 520px at 20% -10%,#3b1360 0%,transparent 62%),#0f0b17;line-height:1.6}
.top{text-align:center;padding:44px 20px 10px;position:relative}.top img{width:84px;height:84px;border-radius:50%}.top h2{margin:12px 0 0;font-size:1.35rem}
.back{position:absolute;right:20px;top:24px;width:52px;height:52px;border-radius:16px;background:#1b1626;border:1px solid #2a2338;color:#fff;display:grid;place-items:center;text-decoration:none;font-size:1.4rem}
.card{max-width:440px;margin:22px auto 40px;background:#15111d;border:1px solid #2a2338;border-radius:32px;padding:30px 24px}
h1{font-size:2rem;margin:0 0 8px;line-height:1.3}.sub{color:#cfc8dc;margin:0 0 20px}.sub a,.link{color:#b857f0;text-decoration:none}
label{display:block;margin:16px 0 8px;font-weight:500}
input[type=text],input[type=email],input[type=password]{width:100%;background:#1f1b2a;border:1.5px solid #2e2840;border-radius:20px;padding:16px 18px;color:#fff;font-family:inherit;font-size:1rem}
input::placeholder{color:#7d7790}input:focus{outline:none;border-color:#a23be4;box-shadow:0 0 0 5px rgba(162,59,228,.18)}
.pw{position:relative}.pw input{padding-left:54px}.eye{position:absolute;left:14px;top:50%;transform:translateY(-50%);background:none;border:0;color:#9d97ad;cursor:pointer;padding:6px;display:grid}
.go{width:100%;margin-top:22px;border:0;border-radius:999px;padding:17px;color:#fff;font-family:inherit;font-weight:600;font-size:1.1rem;background:linear-gradient(90deg,#8e2fc9,#5a60e6);box-shadow:0 10px 30px rgba(120,60,220,.35);cursor:pointer;display:block;text-align:center;text-decoration:none}
.go:focus-visible,.eye:focus-visible,a:focus-visible{outline:3px solid #b857f0;outline-offset:2px}
.msg{padding:11px 14px;border-radius:14px;margin:0 0 6px;font-size:.95rem}.err{background:#3a1420;border:1px solid #7a2a40;color:#ffb3c4}.ok{background:#112a22;border:1px solid #1f6b57;color:#9ee6cf}
.row{display:flex;align-items:center;gap:10px;margin-top:18px}.row input{width:22px;height:22px;accent-color:#a23be4;flex-shrink:0}
.ord{background:#1b1626;border:1px solid #2a2338;border-radius:18px;padding:14px;margin-bottom:12px}.ord b{display:block}.ord small{color:#9d97ad}.ord pre{white-space:pre-wrap;font-family:inherit;margin:8px 0 0;color:#d8d2e6;font-size:.9rem}
.st{display:inline-block;border-radius:999px;padding:2px 12px;font-size:.8rem;background:#3a1420;color:#ffb3c4}.st.progress{background:#10283a;color:#9fd6ff}.st.done{background:#112a22;color:#9ee6cf}
</style></head><body>
<div class="top"><?php if($back): ?><a class="back" href="<?=h($back)?>" aria-label="رجوع">›</a><?php endif; ?><img src="../assets/logo.jpg" alt=""><h2>روت دياجنوستيك</h2></div>
<main class="card"><?php }
function ui_foot(){ echo '</main></body></html>'; }
function pw_field($id,$ph,$auto){ ?><div class="pw"><input id="<?=h($id)?>" name="<?=h($id)?>" type="password" placeholder="<?=h($ph)?>" autocomplete="<?=h($auto)?>" required>
<button type="button" class="eye" aria-label="إظهار أو إخفاء كلمة السر" onclick="var i=this.previousElementSibling;i.type=i.type==='password'?'text':'password'"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg></button></div><?php }
function throttle_hit($pdo,$limit=8){ $c=$pdo->prepare('SELECT COUNT(*) FROM login_log WHERE ip=? AND ts>?'); $c->execute([ip(),ago(900)]); return (int)$c->fetchColumn()>=$limit; }
function log_hit($pdo){ $pdo->prepare('INSERT INTO login_log(ip,ts) VALUES(?,?)')->execute([ip(),now()]); }
