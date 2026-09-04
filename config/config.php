<?php
declare(strict_types=1);

const DB_HOST = ''; // Set your MySQL host here, or use THAER_DB_HOST environment variable
const DB_NAME = ''; // Set your database name here, or use THAER_DB_NAME
const DB_USER = ''; // Set your database username here, or use THAER_DB_USER
const DB_PASS = ''; // Set your database password here, or use THAER_DB_PASS

function db_config(string $key, string $fallback): string {
    $env = getenv('THAER_DB_' . strtoupper($key));
    return ($env !== false && $env !== '') ? $env : $fallback;
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $host = db_config('host', DB_HOST ?: '127.0.0.1');
    $name = db_config('name', DB_NAME ?: 'thaer_media');
    $user = db_config('user', DB_USER ?: 'root');
    $pass = db_config('pass', DB_PASS);
    $dsn = 'mysql:host=' . $host . ';dbname=' . $name . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function ensure_schema(): void {
    try { $pdo = db(); } catch (Throwable $e) { return; }
    $tables = [
        'admins' => "CREATE TABLE IF NOT EXISTS admins (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(100) UNIQUE NOT NULL, password_hash VARCHAR(255) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
        'settings' => "CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(120) UNIQUE NOT NULL, setting_value TEXT NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)",
        'sections' => "CREATE TABLE IF NOT EXISTS sections (id INT AUTO_INCREMENT PRIMARY KEY, section_key VARCHAR(80) UNIQUE NOT NULL, title_ar VARCHAR(255), title_en VARCHAR(255), body_ar TEXT, body_en TEXT, is_visible TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0)",
        'services' => "CREATE TABLE IF NOT EXISTS services (id INT AUTO_INCREMENT PRIMARY KEY, title_ar VARCHAR(255), title_en VARCHAR(255), body_ar TEXT, body_en TEXT, icon VARCHAR(120), image VARCHAR(255), is_visible TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0)",
        'portfolio' => "CREATE TABLE IF NOT EXISTS portfolio (id INT AUTO_INCREMENT PRIMARY KEY, title_ar VARCHAR(255), title_en VARCHAR(255), description_ar TEXT, description_en TEXT, image VARCHAR(255), url VARCHAR(500), is_visible TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0)",
        'pages' => "CREATE TABLE IF NOT EXISTS pages (id INT AUTO_INCREMENT PRIMARY KEY, slug VARCHAR(120) UNIQUE NOT NULL, title_ar VARCHAR(255), title_en VARCHAR(255), body_ar LONGTEXT, body_en LONGTEXT, is_visible TINYINT(1) DEFAULT 1)",
        'menu_items' => "CREATE TABLE IF NOT EXISTS menu_items (id INT AUTO_INCREMENT PRIMARY KEY, label_ar VARCHAR(120), label_en VARCHAR(120), url VARCHAR(255), position VARCHAR(30) DEFAULT 'header', sort_order INT DEFAULT 0, is_visible TINYINT(1) DEFAULT 1)",
        'social_links' => "CREATE TABLE IF NOT EXISTS social_links (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80), url VARCHAR(500), icon VARCHAR(80), is_visible TINYINT(1) DEFAULT 1, sort_order INT DEFAULT 0)",
        'media' => "CREATE TABLE IF NOT EXISTS media (id INT AUTO_INCREMENT PRIMARY KEY, filename VARCHAR(255), original_name VARCHAR(255), mime_type VARCHAR(120), size_bytes BIGINT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)"
    ];
    foreach ($tables as $sql) { try { $pdo->exec($sql); } catch (Throwable $e) {} }
    $count = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO admins(username,password_hash) VALUES(?,?)');
        $stmt->execute(['admin', password_hash('password', PASSWORD_DEFAULT)]);
    }
    $defaults = [
        'green'=>'#0F4A46','green2'=>'#092F32','navy'=>'#09204E','gold'=>'#E2B960','white'=>'#F5F5F2','muted'=>'#B9C7C5',
        'primary'=>'#0F4A46','accent'=>'#E2B960','lightBg'=>'#F5F5F2','darkBg'=>'#061D25','lightText'=>'#092F32','darkText'=>'#F5F5F2',
        'fontFamily'=>'Expo Arabic','fontColor'=>'#F5F5F2','logo'=>'assets/logo-light.png','site_name_ar'=>'ثائر ميديا','site_name_en'=>'Thaer Media',
        'whatsapp'=>'970599351383','email'=>'thaeralqrenawi@gmail.com'
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO settings(setting_key,setting_value) VALUES(?,?)');
    foreach ($defaults as $k=>$v) $stmt->execute([$k,$v]);
    $sections = [
      ['hero','حلول إبداعية تصنع الفرق','Creative solutions that make a difference','نصمم ونبني حضوراً رقمياً قوياً للعلامات التجارية.','We design and build powerful digital presence for brands.'],
      ['about','من نحن','About Us','ثائر ميديا شريكك الإبداعي في التصميم والتسويق وصناعة المحتوى.','Thaer Media is your creative partner in design, marketing and content.']
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO sections(section_key,title_ar,title_en,body_ar,body_en) VALUES(?,?,?,?,?)');
    foreach ($sections as $s) $stmt->execute($s);
}

function setting(string $key, string $fallback=''): string {
    try { $stmt=db()->prepare('SELECT setting_value FROM settings WHERE setting_key=? LIMIT 1'); $stmt->execute([$key]); $v=$stmt->fetchColumn(); return $v===false?$fallback:(string)$v; } catch(Throwable $e){return $fallback;}
}
function set_setting(string $key, string $value): void { $stmt=db()->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)'); $stmt->execute([$key,$value]); }
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
function is_admin(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void { if (!is_admin()) { header('Location: login.php'); exit; } }
ensure_schema();
