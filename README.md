# Thaer Media — COLOR & FONT FINAL

## Important: hosting requirement
This is the PHP + MySQL edition of Thaer Media. **GitHub Pages cannot run this project**, because GitHub Pages serves static files and does not execute PHP or MySQL.

Use a hosting account that supports **PHP 8.1+ and MySQL/MariaDB** (for example cPanel hosting), and point `thaerdesigner.com` DNS to that hosting account.

## Installation
1. Upload the contents of this folder to `public_html/` (or the document root for the domain).
2. Create a MySQL/MariaDB database and user in cPanel.
3. Import `database.sql` in phpMyAdmin, or let the application create missing tables after the database connection is configured.
4. Edit `config/config.php` and set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`. Alternatively set the `THAER_DB_HOST`, `THAER_DB_NAME`, `THAER_DB_USER`, and `THAER_DB_PASS` environment variables.
5. Make sure the `uploads/` directory is writable by PHP.
6. Open `https://thaerdesigner.com/`.
7. Admin: `https://thaerdesigner.com/admin/`

Default admin credentials on a fresh database:
- Username: `admin`
- Password: `password`

**Change the password after the first login.**

## What was fixed in this package
- Removed `index.html`, which could take precedence over `index.php` on Apache and prevent the PHP homepage from loading.
- Added `DirectoryIndex index.php`.
- Kept the bilingual PHP site and standalone `contact.php`.
- Kept exact HEX color controls and font/font-color controls in Admin.
- Added `database.sql` for reliable phpMyAdmin installation.
- Made MySQL configuration compatible with normal hosting and environment variables.
- Kept uploads protected against PHP script execution.

## GitHub
You may keep the project in GitHub as source control, but **do not use GitHub Pages as the production server for this PHP version**. Deploy the same repository/files to a PHP/MySQL hosting server instead.
