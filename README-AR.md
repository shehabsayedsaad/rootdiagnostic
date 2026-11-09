# رفع الموقع (ثابت — من غير قاعدة بيانات)

الموقع HTML فقط، فمش محتاج PHP ولا MySQL. أي استضافة أو **GitHub Pages** تنفع.

## على Hostinger
1. hPanel ← File Manager ← `public_html`.
2. ارفع محتويات الـ ZIP كلها (فك الضغط) مع الحفاظ على مجلد `assets`.
3. فعّل SSL من hPanel ← Security ← SSL. الـ `.htaccess` بيحوّل HTTP إلى HTTPS.
4. افتح الدومين وجرّب طلب تجريبي.

## على GitHub Pages
1. ارفع الملفات على مستودع (Private/Public حسب خطتك).
2. Settings ← Pages ← اختار الفرع `main` والمجلد `/ (root)`.

## الطلبات
- أي طلب بيفتح واتساب برسالة جاهزة على الرقم **01127166238** والعميل بيضغط إرسال.
- مفيش طلبات بتتسجل في الصفحة ولا لوحة أدمن. لو عايز تغيّر الرقم: عدّل `PHONE` (بصيغة 20 + الرقم من غير صفر) ورابط `tel:` والرقم الظاهر في `index.html`.

## النسخة القديمة (بلوحة أدمن وحسابات)
محفوظة في الـ tag `with-admin-backend` وفي ملف `root-diagnostic-orders-with-admin-backup.zip`.
