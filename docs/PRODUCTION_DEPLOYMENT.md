# BioTime – Production Deployment (VPS)

## Server

- VPS with PHP 8.2+, MySQL 8+, Nginx or Apache, SSL (e.g. Let’s Encrypt).

## 1. Code and dependencies

```bash
cd /var/www/biotime  # or your path
git pull  # or upload code
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 2. Environment

- Copy `.env.example` to `.env`, set `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`, and production DB credentials.
- Set `APP_URL` to your domain (e.g. `https://biotime.example.com`).
- Queue: `QUEUE_CONNECTION=database` or `redis` (recommended for production).

## 3. Database

```bash
php artisan migrate --force
# Seed only if fresh install:
# php artisan db:seed --force
```

## 4. Permissions

```bash
chown -R www-data:www-data /var/www/biotime
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache  # if web server needs write
```

## 5. Web server (Nginx example)

- Document root: `public/`
- PHP-FPM for `index.php` and `*.php`.
- SSL and redirect HTTP → HTTPS.

## 6. Scheduler (cron)

```bash
* * * * * cd /var/www/biotime && php artisan schedule:run >> /dev/null 2>&1
```

This runs `attendance:process --days=3` every 15 minutes (see `routes/console.php`).

## 7. Queue worker (supervisor or systemd)

Example supervisor config `/etc/supervisor/conf.d/biotime-worker.conf`:

```ini
[program:biotime-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/biotime/artisan queue:work database --queue=attendance,default --sleep=3 --tries=3
directory=/var/www/biotime
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/biotime/storage/logs/worker.log
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start biotime-worker:*
```

## 8. Device push URL

- Configure T304F or sync agent to POST to `https://yourdomain.com/api/device/sync` with device token (X-Device-Key or Bearer).

## 9. Backups

- Daily DB dumps and optional off-site copy (S3, etc.).
- Backup `storage/` (e.g. salary slip PDFs) if needed.

## 10. Security

- HTTPS only; restrict admin routes by IP if needed; keep `api_key` (device token) secret; use strong DB password and `APP_KEY`; see SECURITY.md.
