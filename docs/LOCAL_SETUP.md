# BioTime - Local Setup Guide

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+ (or MariaDB)
- Node/npm (optional, for frontend assets)

## Steps

### 1. Install

```bash
cd R:\wamp64\www\biotime
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Environment

Edit `.env`: set `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`. Create MySQL database `biotime`.

### 3. Migrate and seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 4. Default logins

- Super Admin: username `super_admin_biotime`, password `98245@biotime`
- Branch Admin: username `admin_biotime`, password `98245@biotime`

### 5. Queue and scheduler

- Queue: `php artisan queue:work --queue=attendance,default`
- Scheduler: `php artisan schedule:run` (or add to cron). Attendance runs every 15 minutes via `attendance:process --days=3`.

### 6. Device push (local)

Create a Location and Device in admin; copy device API Key. POST to `/api/device/sync` with header `X-Device-Key: <api_key>`. Body T304F: `{"logs":[{"employee_code":"1","timestamp":"2025-02-26 09:05:00","device_id":1}]}`. Legacy: `{"punches":[{"device_user_id":"1","punch_time":"2025-02-26 09:05:00"}]}`.

### 7. Settings

- `grace_minutes` default 10, `half_day_hours` default 4, `working_hours_per_day` default 8. Edit via `Setting::set('key','value')` or admin UI.
