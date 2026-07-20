# 🌱 BloomLog — Plant Care & Watering Reminder

A PHP + MySQL web application that helps people take care of their houseplants.
Users sign up with their city, pick plants that suit their local climate, track
watering schedules, and can receive email reminders when a plant is due for water.

> **University:** King Saud University — Information Technology
> **Course:** Software Engineering (project 1)

**Live demo:** _add your hosting link here after deployment_

**Demo accounts** (for the live demo / a fresh install):
| Role  | Email               | Password  |
|-------|---------------------|-----------|
| User  | `demo@bloomlog.com` | `demo1234`|
| Admin | `admin@bloomlog.com`| `admin123`|

## Features
- **Accounts:** sign up / log in with hashed passwords (`password_hash`).
- **Climate-aware catalog:** on sign-up the app records the user's city; the
  "Add Plant" page only shows plants whose temperature/humidity range matches
  the user's environment.
- **Garden dashboard:** see all your plants, watering status, and a progress bar
  toward the next watering.
- **Plant details:** water a plant (auto-schedules the next date), edit its
  nickname/notes, or delete it.
- **Admin panel:** manage the shared plant catalog (add / remove plants) and view
  usage stats — restricted to admin accounts only.
- **Email reminders:** a cron script (`reminder-cron.php`) emails users, in their
  own timezone, when a plant is due for watering.

## Tech stack
PHP 8 · MySQL/MariaDB · PHPMailer · vanilla HTML/CSS/JS · OpenWeatherMap API

## Project structure
```
index.php          login + sign-up
homepage.php       user dashboard (their garden)
add-plant.php      add a plant matched to the user's climate
view-plant.php     plant details + water / delete
edit-plant.php     edit a plant
remove_plant.php   AJAX endpoint for deleting a plant
admin.php          admin-only catalog management
reminder-cron.php  scheduled email reminders
backups.php        admin DB backup utility (local only)
config.php         central config: DB credentials + secrets loader
schema.sql         database structure + demo seed data
```

## Run it locally (MAMP / XAMPP)
1. Copy this folder into your web root (e.g. `C:\MAMP\htdocs\bloomlog`).
2. Create the database and import the schema:
   ```sql
   CREATE DATABASE bloomlog CHARACTER SET utf8mb4;
   ```
   Then import `schema.sql` (via phpMyAdmin, or `mysql -u root -p bloomlog < schema.sql`).
3. If your MySQL credentials differ from `root` / `root`, edit `config.php`
   (or set the `DB_HOST` / `DB_USER` / `DB_PASS` / `DB_NAME` environment variables).
4. (Optional) For the weather and email features, copy `secrets.example.php`
   to `secrets.php` and fill in your OpenWeatherMap API key and Gmail App Password.
   `secrets.php` is git-ignored and never committed.
5. Open `http://localhost/bloomlog/index.php` and log in with a demo account above.

## Configuration & secrets
All credentials live in **one place**:
- **Database:** `config.php` (with environment-variable overrides for hosting).
- **API key & email:** `secrets.php` (created from `secrets.example.php`, never committed).

No passwords or API keys are hard-coded in the application source.

## Notes
- `backups.php` uses `mysqldump` via `exec()` and is intended for a local
  environment; most shared/free hosts disable `exec()`.
- Email reminders require SMTP access, which some free hosts block.
