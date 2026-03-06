# Production – Full Command Reference

Use this as a single checklist for **first-time deployment** and **post-deploy updates** on your VPS (e.g. Hostinger). All commands assume app root: `/var/www/bioattendtime` and web user: `www-data`.

---

## First-time deployment (in order)

### 1. Server (one-time)

```bash
apt update && apt upgrade -y
apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath mysql-server nginx git unzip
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

### 2. Database (one-time)

```bash
mysql -u root -p
```

```sql
CREATE DATABASE bioattendtime CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bioattendtime'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL ON bioattendtime.* TO 'bioattendtime'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Deploy app and env

```bash
cd /var/www
git clone YOUR_REPO_URL bioattendtime
cd bioattendtime
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env`: set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`.

### 4. Migrate, seed, cache

```bash
cd /var/www/bioattendtime
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Frontend build (if you use Vite/npm)

```bash
npm ci
npm run build
```

### 6. Permissions

```bash
chown -R www-data:www-data /var/www/bioattendtime
chmod -R 755 /var/www/bioattendtime
chmod -R 775 /var/www/bioattendtime/storage /var/www/bioattendtime/bootstrap/cache
```

### 7. Nginx

Create site config, enable, test, reload:

```bash
ln -s /etc/nginx/sites-available/bioattendtime /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

### 8. SSL (recommended)

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d bioattendtime.yourdomain.com
```

Set `APP_URL=https://...` in `.env`, then:

```bash
php artisan config:cache
```

### 9. Cron (required for attendance to show on website)

```bash
crontab -u www-data -e
```

Add this line (exactly one per minute):

```
* * * * * cd /var/www/bioattendtime && php artisan schedule:run >> /dev/null 2>&1
```

This runs the Laravel scheduler, which runs `attendance:process --days=3` every 15 minutes. **Without this, raw punches stay in the database but daily attendance and reports will not update.**

### 10. Queue worker (optional, for notifications)

```bash
apt install -y supervisor
```

Create `/etc/supervisor/conf.d/bioattendtime-worker.conf` (see [DEPLOYMENT_HOSTINGER_VPS.md](DEPLOYMENT_HOSTINGER_VPS.md#6-cron-scheduler-and-queue-optional)), then:

```bash
supervisorctl reread && supervisorctl update && supervisorctl start bioattendtime-worker:*
```

---

## After each code deploy (git pull / SFTP update)

Run from app root:

```bash
cd /var/www/bioattendtime
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data /var/www/bioattendtime
```

**Important:** Run `php artisan route:clear` before `route:cache` so the route cache is rebuilt from current code. Otherwise you may get "Route [name] not defined" if new routes were added.

If you use npm build:

```bash
npm ci
npm run build
```

---

## One-off commands (when needed)

| Command | When to use |
|--------|-------------|
| `php artisan attendance:process --days=3` | Process raw punches into daily attendance (e.g. after fixing employee mapping or if cron missed runs). |
| `php artisan db:seed --force` | Re-run seeders (e.g. after adding new seeders). Use with care on production. |
| `php artisan cache:clear` | Clear app cache (after changing config without `config:cache`). |
| `php artisan config:clear` | Clear config cache before editing `.env` and then run `config:cache` again. |
| `php artisan queue:work --once` | Process one queue job manually (e.g. for testing). |

---

## Quick reference – minimal post-deploy

```bash
cd /var/www/bioattendtime
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data .
```

**Critical for punch data to appear on website:** cron must be set for `schedule:run` (see §9 above), and employees must be mapped (see [TROUBLESHOOTING_PUNCH_NOT_SHOWING.md](TROUBLESHOOTING_PUNCH_NOT_SHOWING.md)).
