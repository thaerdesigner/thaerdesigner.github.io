# Thaer Media — PHP/MySQL Final

## تشغيل على XAMPP / Apache 2.4 / PHP 8.2+
1. انسخ مجلد `thaer` إلى `C:\xampp\htdocs\thaer`.
2. شغّل Apache وMySQL.
3. افتح `http://localhost/thaer/`.
4. لوحة التحكم: `http://localhost/thaer/admin/login.php`.
5. الدخول الافتراضي: `admin` / `password`.

قاعدة البيانات تُنشأ وتُصلح تلقائيًا عند أول تشغيل. لا تحذف قاعدة البيانات القديمة؛ النظام يضيف الأعمدة الناقصة ويحافظ على البيانات.

## رفع الصور
من لوحة التحكم > الأعمال والصور أو الخدمات أو مدير الملفات، اختر الملف من الجهاز. لا تحتاج إلى إدخال رابط للصورة.

## إذا كان MySQL يستخدم كلمة مرور
عدّل `DB_PASS` في `config/config.php`.
