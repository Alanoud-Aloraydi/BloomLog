# Deploying BloomLog to free hosting (InfinityFree)

BloomLog needs **PHP + MySQL**, so it can't run on GitHub Pages. These steps put a
live, shareable demo online for free on [InfinityFree](https://infinityfree.com)
(no credit card required). Any host that offers PHP + MySQL + phpMyAdmin works the
same way.

## 1. Create a free hosting account
1. Sign up at <https://infinityfree.com> and create a new site.
2. You'll get a free subdomain (e.g. `bloomlog.rf.gd`) or you can attach your own.

## 2. Create the database
1. In the control panel open **MySQL Databases** and create one.
2. Note the four values it gives you:
   - **Host** (e.g. `sqlXXX.infinityfree.com`)
   - **Database name** (e.g. `epiz_123_bloomlog`)
   - **Username** (e.g. `epiz_123`)
   - **Password** (the one you set)

## 3. Import the schema
1. Open **phpMyAdmin** from the control panel and select your new database.
2. Go to the **Import** tab and upload `schema.sql` from this project.
3. This creates the tables and the demo accounts.

## 4. Point the app at the database
Edit `config.php` and replace the fallback values with your hosting values:
```php
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_USER', 'epiz_123');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'epiz_123_bloomlog');
```
(Leave the `getenv(...)` wrappers or replace them with the literals above — either works.)

## 5. Upload the files
1. In the control panel open the **File Manager** (or use FTP).
2. Upload everything in this project into the `htdocs` folder — **except**:
   - `schema.sql` (already imported)
   - `README.md` / `DEPLOYMENT.md` (optional)
3. If you want weather + email, also create `secrets.php` from
   `secrets.example.php` and upload it.

## 6. Visit your site
Open `https://your-subdomain/index.php` and log in with:
- **User:** `demo@bloomlog.com` / `demo1234`
- **Admin:** `admin@bloomlog.com` / `admin123`

Put the final link in `README.md` under **Live demo** and in your portfolio/CV.

---
### Good to know on free hosting
- **`backups.php`** relies on `exec()` + `mysqldump`, which free hosts usually
  disable. The rest of the app is unaffected.
- **Email reminders** need SMTP, which some free hosts block. Login, plants, and the
  admin panel all work regardless.
- Free sites can show a brief interstitial and may sleep when idle — fine for a
  portfolio demo, but expect that when sharing the link.
