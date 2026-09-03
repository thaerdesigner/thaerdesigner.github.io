<?php
session_start();

const DB_HOST = '127.0.0.1';
const DB_NAME = 'thaer_media';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        if ((int)$e->errorInfo[1] === 1049) {
            $server = new PDO('mysql:host='.DB_HOST.';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            $server->exec('CREATE DATABASE IF NOT EXISTS `'.DB_NAME.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES=>false,
            ]);
        } else {
            throw $e;
        }
    }
    return $pdo;
}

function e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function admin_required(): void { if (empty($_SESSION['admin_id'])) { header('Location: login.php'); exit; } }
function csrf(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) throw new RuntimeException('انتهت صلاحية الطلب، أعد المحاولة.'); }

function ensure_schema(): void {
    $tables = [
        'admins' => "CREATE TABLE IF NOT EXISTS admins(id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'settings' => "CREATE TABLE IF NOT EXISTS settings(`key` VARCHAR(120) PRIMARY KEY, `value` LONGTEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'sections' => "CREATE TABLE IF NOT EXISTS sections(id INT AUTO_INCREMENT PRIMARY KEY, section_key VARCHAR(100) NOT NULL UNIQUE, title_ar VARCHAR(255) NULL, title_en VARCHAR(255) NULL, content_ar LONGTEXT NULL, content_en LONGTEXT NULL, image VARCHAR(500) NULL, enabled TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'services' => "CREATE TABLE IF NOT EXISTS services(id INT AUTO_INCREMENT PRIMARY KEY, title_ar VARCHAR(255) NULL, title_en VARCHAR(255) NULL, description_ar TEXT NULL, description_en TEXT NULL, image VARCHAR(500) NULL, enabled TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'portfolio' => "CREATE TABLE IF NOT EXISTS portfolio(id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NULL, category VARCHAR(120) NULL, description TEXT NULL, image VARCHAR(500) NULL, url VARCHAR(500) NULL, enabled TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'pages' => "CREATE TABLE IF NOT EXISTS pages(id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(150) NOT NULL UNIQUE, title_ar VARCHAR(255) NULL, title_en VARCHAR(255) NULL, content_ar LONGTEXT NULL, content_en LONGTEXT NULL, enabled TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'menu_items' => "CREATE TABLE IF NOT EXISTS menu_items(id INT AUTO_INCREMENT PRIMARY KEY, label_ar VARCHAR(150) NULL, label_en VARCHAR(150) NULL, url VARCHAR(300) NULL, location VARCHAR(30) NOT NULL DEFAULT 'header', enabled TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'social_links' => "CREATE TABLE IF NOT EXISTS social_links(id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NULL, url VARCHAR(500) NULL, icon VARCHAR(100) NULL, enabled TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        'media' => "CREATE TABLE IF NOT EXISTS media(id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255) NOT NULL, path VARCHAR(500) NOT NULL, mime VARCHAR(100) NULL, size INT NOT NULL DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    foreach ($tables as $sql) db()->exec($sql);

    // Repair old installations: add missing columns without deleting existing data.
    $columns = [
        'sections'=>['section_key'=>"VARCHAR(100) NULL",'title_ar'=>"VARCHAR(255) NULL",'title_en'=>"VARCHAR(255) NULL",'content_ar'=>"LONGTEXT NULL",'content_en'=>"LONGTEXT NULL",'image'=>"VARCHAR(500) NULL",'enabled'=>"TINYINT(1) NOT NULL DEFAULT 1",'sort_order'=>"INT NOT NULL DEFAULT 0"],
        'services'=>['title_ar'=>"VARCHAR(255) NULL",'title_en'=>"VARCHAR(255) NULL",'description_ar'=>"TEXT NULL",'description_en'=>"TEXT NULL",'image'=>"VARCHAR(500) NULL",'enabled'=>"TINYINT(1) NOT NULL DEFAULT 1",'sort_order'=>"INT NOT NULL DEFAULT 0"],
        'portfolio'=>['title'=>"VARCHAR(255) NULL",'category'=>"VARCHAR(120) NULL",'description'=>"TEXT NULL",'image'=>"VARCHAR(500) NULL",'url'=>"VARCHAR(500) NULL",'enabled'=>"TINYINT(1) NOT NULL DEFAULT 1",'sort_order'=>"INT NOT NULL DEFAULT 0"],
        'pages'=>['slug'=>"VARCHAR(150) NULL",'title_ar'=>"VARCHAR(255) NULL",'title_en'=>"VARCHAR(255) NULL",'content_ar'=>"LONGTEXT NULL",'content_en'=>"LONGTEXT NULL",'enabled'=>"TINYINT(1) NOT NULL DEFAULT 1",'sort_order'=>"INT NOT NULL DEFAULT 0"],
        'menu_items'=>['label_ar'=>"VARCHAR(150) NULL",'label_en'=>"VARCHAR(150) NULL",'url'=>"VARCHAR(300) NULL",'location'=>"VARCHAR(30) NOT NULL DEFAULT 'header'",'enabled'=>"TINYINT(1) NOT NULL DEFAULT 1",'sort_order'=>"INT NOT NULL DEFAULT 0"],
        'social_links'=>['name'=>"VARCHAR(100) NULL",'url'=>"VARCHAR(500) NULL",'icon'=>"VARCHAR(100) NULL",'enabled'=>"TINYINT(1) NOT NULL DEFAULT 1",'sort_order'=>"INT NOT NULL DEFAULT 0"],
        'media'=>['filename'=>"VARCHAR(255) NULL",'path'=>"VARCHAR(500) NULL",'mime'=>"VARCHAR(100) NULL",'size'=>"INT NOT NULL DEFAULT 0",'created_at'=>"TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP"],
    ];
    foreach ($columns as $table=>$cols) {
        foreach ($cols as $col=>$def) {
            $q=db()->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name=?");
            $q->execute([DB_NAME,$table,$col]);
            if (!(int)$q->fetchColumn()) db()->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
        }
    }

    $hash = '$2y$12$aEjJpYvmwKQ0kTzw1ERqyOSXbQSoBZKKplZogOTEBbfX7WcU14xAq';
    db()->prepare("INSERT INTO admins(username,password_hash) VALUES('admin',?) ON DUPLICATE KEY UPDATE username=username")->execute([$hash]);
    $defaults = [
        'siteName'=>'Thaer Media','layout'=>'classic','menuPosition'=>'top','sidebarEnabled'=>'0','mode'=>'system',
        'primary'=>'#111111','accent'=>'#c7a86b','lightBg'=>'#f6f5f2','darkBg'=>'#0c0c0c','lightText'=>'#151515','darkText'=>'#f5f5f5',
        'logo'=>'','favicon'=>'','phone'=>'+970 599 351 383','whatsapp'=>'970599351383','email'=>'thaeralqrenawi@gmail.com',
        'heroBadge_ar'=>'✦ وكالة إبداعية وتسويقية متكاملة','heroBadge_en'=>'✦ Integrated Creative & Marketing Agency',
        'heroTitle_ar'=>'نوظِّفُ خبرتنا لنجاح علامتك التجارية','heroTitle_en'=>'We turn experience into brand success',
        'heroText_ar'=>'أكثر من 12 عامًا من الخبرة في التصميم والتسويق وصناعة المحتوى، لنمنح علامتك التجارية حضورًا بصريًا واستراتيجيًا يصنع فرقًا حقيقيًا.','heroText_en'=>'More than 12 years of experience in design, marketing and content creation.'
    ];
    $q=db()->prepare("INSERT IGNORE INTO settings(`key`,`value`) VALUES(?,?)"); foreach($defaults as $k=>$v)$q->execute([$k,$v]);
    $count=(int)db()->query("SELECT COUNT(*) FROM sections WHERE section_key='about'")->fetchColumn();
    if(!$count) db()->exec("INSERT INTO sections(section_key,title_ar,title_en,content_ar,content_en,enabled,sort_order) VALUES('about','من نحن','About','Thaer Media هي وكالة إبداعية وتسويقية تقدم حلولًا متكاملة للشركات والمتاجر والمطاعم والعيادات والأعمال حول العالم. نربط التصميم بالتسويق لنصنع أعمالًا جميلة وفعّالة.','Thaer Media is a creative and marketing agency providing integrated solutions for companies, stores, restaurants, clinics and businesses worldwide.',1,2)");
    if(!(int)db()->query("SELECT COUNT(*) FROM services")->fetchColumn()) db()->exec("INSERT INTO services(title_ar,title_en,description_ar,description_en,sort_order) VALUES ('الهوية البصرية','Brand Identity','نبني هوية بصرية متكاملة تعبّر عن شخصية علامتك.','We build complete visual identities.',1),('التصميم الإعلاني','Advertising Design','تصاميم إعلانية جذابة للحملات والمنصات الرقمية.','High-impact advertising visuals.',2),('صناعة المحتوى','Content Creation','محتوى بصري واستراتيجي يساعد علامتك على الظهور.','Visual and strategic content.',3),('التسويق الرقمي','Digital Marketing','حلول تسويقية مخصصة لزيادة الوصول وتحقيق النتائج.','Tailored marketing solutions.',4)");
    if(!(int)db()->query("SELECT COUNT(*) FROM menu_items")->fetchColumn()) db()->exec("INSERT INTO menu_items(label_ar,label_en,url,location,sort_order) VALUES ('الرئيسية','Home','index.php','header',1),('خدماتنا','Services','index.php#services','header',2),('من نحن','About','index.php#about','header',3),('أعمالنا','Work','index.php#portfolio','header',4),('تواصل معنا','Contact','index.php#contact','header',5)");
}

function settings(): array { static $s=null; if($s===null){$s=[];foreach(db()->query("SELECT `key`,`value` FROM settings") as $r)$s[$r['key']]=$r['value'];} return $s; }
function setting(string $k, string $d=''): string { $s=settings(); return (string)($s[$k]??$d); }
function save_setting(string $k,string $v):void{db()->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)")->execute([$k,$v]);}

function upload_media(string $field='file'): string {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) return '';
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('فشل رفع الملف. تأكد من حجمه وإعدادات PHP.');
    $f=$_FILES[$field];
    if((int)$f['size']>12*1024*1024) throw new RuntimeException('الحد الأقصى للملف 12MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif','image/svg+xml'=>'svg','application/pdf'=>'pdf'];
    if(!isset($allowed[$mime])) throw new RuntimeException('نوع الملف غير مسموح. JPG/PNG/WEBP/GIF/SVG/PDF فقط.');
    $dir=dirname(__DIR__).DIRECTORY_SEPARATOR.'uploads'; if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('تعذر إنشاء مجلد uploads.');
    $name=bin2hex(random_bytes(12)).'.'.$allowed[$mime]; $dest=$dir.DIRECTORY_SEPARATOR.$name;
    if(!move_uploaded_file($f['tmp_name'],$dest)) throw new RuntimeException('تعذر حفظ الملف في uploads.');
    $path='uploads/'.$name; db()->prepare("INSERT INTO media(filename,path,mime,size) VALUES(?,?,?,?)")->execute([$f['name'],$path,$mime,(int)$f['size']]); return $path;
}

ensure_schema();
