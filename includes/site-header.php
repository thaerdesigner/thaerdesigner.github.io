<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__.'/../config/config.php';
$lang = ($_GET['lang'] ?? $_SESSION['lang'] ?? 'ar') === 'en' ? 'en' : 'ar'; $_SESSION['lang']=$lang;
$dir = $lang==='ar'?'rtl':'ltr';
$font = setting('fontFamily','Expo Arabic');
$colors = ['green','green2','navy','gold','white','muted','primary','accent','lightBg','darkBg','lightText','darkText','fontColor'];
$css=[]; foreach($colors as $c) $css[]='--'.$c.':'.e(setting($c,'')).';';
$css[]='--site-font:"'.e($font).'", Arial, sans-serif;';
?><!doctype html><html lang="<?=e($lang)?>" dir="<?=$dir?>"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(setting($lang==='ar'?'site_name_ar':'site_name_en','Thaer Media'))?></title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/site.css"><style>:root{<?=implode('', $css)?>}body{font-family:var(--site-font);color:var(--fontColor)}</style></head><body>
<header class="site-header"><div class="container nav-wrap"><a class="brand" href="index.php"><img src="<?=e(setting('logo','assets/logo-light.png'))?>" alt="Thaer Media"></a><nav><a href="index.php">⌂ <?= $lang==='ar'?'الرئيسية':'Home' ?></a><a href="index.php#services"><?= $lang==='ar'?'الخدمات':'Services' ?></a><a href="index.php#about"><?= $lang==='ar'?'من نحن':'About' ?></a><a href="index.php#portfolio"><?= $lang==='ar'?'أعمالنا':'Work' ?></a><a href="contact.php"><?= $lang==='ar'?'تواصل معنا':'Contact' ?></a></nav><div class="nav-actions"><a href="?lang=<?=$lang==='ar'?'en':'ar'?>" class="lang-btn"><?=$lang==='ar'?'EN':'ع'?></a><a class="admin-link" href="admin/">Admin</a></div></div></header>
