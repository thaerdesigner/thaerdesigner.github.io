CREATE DATABASE IF NOT EXISTS thaer_media CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thaer_media;
CREATE TABLE IF NOT EXISTS admins(id INT AUTO_INCREMENT PRIMARY KEY,username VARCHAR(100) UNIQUE NOT NULL,password_hash VARCHAR(255) NOT NULL);
CREATE TABLE IF NOT EXISTS settings(`key` VARCHAR(120) PRIMARY KEY,`value` LONGTEXT NOT NULL);
CREATE TABLE IF NOT EXISTS sections(id INT AUTO_INCREMENT PRIMARY KEY,section_key VARCHAR(100) UNIQUE NOT NULL,title_ar VARCHAR(255),title_en VARCHAR(255),content_ar LONGTEXT,content_en LONGTEXT,image VARCHAR(500),enabled TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS services(id INT AUTO_INCREMENT PRIMARY KEY,title_ar VARCHAR(255),title_en VARCHAR(255),description_ar TEXT,description_en TEXT,image VARCHAR(500),enabled TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS portfolio(id INT AUTO_INCREMENT PRIMARY KEY,title VARCHAR(255),category VARCHAR(120),description TEXT,image VARCHAR(500),url VARCHAR(500),enabled TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS pages(id INT AUTO_INCREMENT PRIMARY KEY,slug VARCHAR(150) UNIQUE,title_ar VARCHAR(255),title_en VARCHAR(255),content_ar LONGTEXT,content_en LONGTEXT,enabled TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS menu_items(id INT AUTO_INCREMENT PRIMARY KEY,label_ar VARCHAR(150),label_en VARCHAR(150),url VARCHAR(300),location VARCHAR(30) DEFAULT 'header',enabled TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS social_links(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100),url VARCHAR(500),icon VARCHAR(100),enabled TINYINT(1) DEFAULT 1,sort_order INT DEFAULT 0);
CREATE TABLE IF NOT EXISTS media(id INT AUTO_INCREMENT PRIMARY KEY,filename VARCHAR(255) NOT NULL,path VARCHAR(500) NOT NULL,mime VARCHAR(100),size INT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
INSERT IGNORE INTO admins(username,password_hash) VALUES('admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC6y1KQK9R6KqK8Q0j8u');
INSERT IGNORE INTO settings(`key`,`value`) VALUES
('siteName','Thaer Media'),('layout','classic'),('menuPosition','top'),('sidebarEnabled','0'),('mode','system'),
('primary','#111111'),('accent','#c7a86b'),('lightBg','#f6f5f2'),('darkBg','#0c0c0c'),('lightText','#151515'),('darkText','#f5f5f5'),
('logo',''),('favicon',''),('phone','+970 599 351 383'),('whatsapp','970599351383'),('email',''),
('heroBadge_ar','✦ وكالة إبداعية وتسويقية متكاملة'),('heroBadge_en','✦ Integrated Creative & Marketing Agency'),
('heroTitle_ar','نوظِّفُ خبرتنا لنجاح علامتك التجارية'),('heroTitle_en','We turn experience into brand success'),
('heroText_ar','أكثر من 12 عامًا من الخبرة في التصميم والتسويق وصناعة المحتوى، لنمنح علامتك التجارية حضورًا بصريًا واستراتيجيًا يصنع فرقًا حقيقيًا.'),
('heroText_en','More than 12 years of experience in design, marketing and content creation.');
-- Default password: password
INSERT IGNORE INTO sections(section_key,title_ar,title_en,content_ar,content_en,sort_order) VALUES
('about','من نحن','About','Thaer Media هي وكالة إبداعية وتسويقية تقدم حلولًا متكاملة للعلامات التجارية.','Thaer Media is a creative and marketing agency providing integrated solutions for brands.',2);
INSERT IGNORE INTO services(title_ar,title_en,description_ar,description_en,sort_order) VALUES
('الهوية البصرية','Brand Identity','نبني هوية بصرية متكاملة تعبّر عن شخصية علامتك.','We build complete visual identities.',1),
('التصميم الإعلاني','Advertising Design','تصاميم إعلانية جذابة للحملات والمنصات الرقمية.','High-impact advertising visuals.',2),
('صناعة المحتوى','Content Creation','محتوى بصري واستراتيجي يساعد علامتك على الظهور.','Visual and strategic content.',3),
('التسويق الرقمي','Digital Marketing','حلول تسويقية مخصصة لزيادة الوصول وتحقيق النتائج.','Tailored marketing solutions.',4);
INSERT IGNORE INTO menu_items(label_ar,label_en,url,location,sort_order) VALUES
('الرئيسية','Home','index.php','header',1),('خدماتنا','Services','index.php#services','header',2),('من نحن','About','index.php#about','header',3),('أعمالنا','Work','index.php#portfolio','header',4),('تواصل معنا','Contact','index.php#contact','header',5);
