# bioattendtime – Deploy to Hostinger VPS (KVM 1) with domain

Follow these steps **after the app works locally** to put bioattendtime live on your Hostinger VPS with your domain.

---

## 1. VPS and domain (Hostinger)

- **VPS:** KVM 1 (or any plan with root/SSH).
- **Domain:** Point your domain’s **A record** to the VPS public IP (e.g. `bioattendtime.yourdomain.com` → `123.45.67.89`).
- **SSH:** Use the SSH details from Hostinger (user often `root`, or the one they give you).

---

## 2. Server setup (one-time)

SSH into the VPS and run:

```bash
# Update system
apt update && apt upgrade -y

# PHP 8.2+, MySQL, Nginx, Git, Composer, Node (optional, for npm build)
apt install -y php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath mysql-server nginx git unzip

# Composer (global)
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

Create a MySQL database and user for bioattendtime:

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

---

## 3. Deploy the Laravel app

```bash
# Example: deploy to /var/www/bioattendtime
cd /var/www
git clone YOUR_REPO_URL bioattendtime
# Or upload via SFTP and extract to /var/www/bioattendtime

cd bioattendtime
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env` for **production**:

```env
APP_NAME=bioattendtime
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bioattendtime.neoarcade.fun

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bioattendtime
DB_USERNAME=bio_user
DB_PASSWORD="s?f4mYPI74QhV7RP:1#A"

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Then:

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
chown -R www-data:www-data /var/www/bioattendtime
chmod -R 755 /var/www/bioattendtime
chmod -R 775 /var/www/bioattendtime/storage /var/www/bioattendtime/bootstrap/cache
```

If you use frontend build (Vite/npm):

```bash
npm ci
npm run build
```

---

## 4. Nginx site (with SSL later)

Create `/etc/nginx/sites-available/bioattendtime`:

```nginx
server {
    listen 80;
    server_name bioattendtime.yourdomain.com;
    root /var/www/bioattendtime/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

Enable and test:

```bash
ln -s /etc/nginx/sites-available/bioattendtime /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 5. SSL (HTTPS) with Let’s Encrypt

```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d bioattendtime.yourdomain.com
```

Follow prompts. Certbot will adjust Nginx for HTTPS. Ensure `APP_URL` in `.env` uses `https://`.

---

## 6. Cron (scheduler) and queue (optional)

Laravel scheduler runs `attendance:process` and other scheduled tasks.

```bash
crontab -u www-data -e
```

Add:

```
* * * * * cd /var/www/bioattendtime && php artisan schedule:run >> /dev/null 2>&1
```

For queued notifications (recommended in production):

```bash
# Run queue worker (e.g. via supervisor)
apt install -y supervisor
```

Create `/etc/supervisor/conf.d/bioattendtime-worker.conf`:

```ini
[program:bioattendtime-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/bioattendtime/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/bioattendtime/storage/logs/worker.log
```

Then:

```bash
supervisorctl reread && supervisorctl update && supervisorctl start bioattendtime-worker:*
```

---

## 7. After going live

1. **Test:** Open `https://bioattendtime.yourdomain.com`, log in with `admin_biotime` / `98245@biotime`, then change the password.
2. **Device push / sync:** Configure T304F to POST to the URL above. On each location PC, point the PayTime sync agent to `https://bioattendtime.yourdomain.com/api/device/push` (or `/api/device/sync`) and use each device’s API key from the admin.
3. **Backup:** Use Hostinger backup or a cron job to backup MySQL and `storage/` (e.g. daily dump + optional upload to object storage).

---

## 8. Root files and folder security

- **Production (Nginx):** The site root is set to `public` (`root /var/www/bioattendtime/public`), so only files under `public/` are web-accessible. No `.htaccess` is used; nothing else is exposed.
- **Local (Apache/WAMP):** The project root contains a `.htaccess` that blocks direct access to `.env`, `.git`, `app/`, `config/`, `storage/`, `vendor/`, `database/`, `routes/`, etc. If your WAMP document root is the project folder (not `public/`), the same file also routes requests to `public/index.php`. This does not affect the live Nginx server.

---

## 9. Full production command list and troubleshooting

- **All commands in order:** [PRODUCTION_COMMANDS.md](PRODUCTION_COMMANDS.md) – first-time deployment and post-deploy updates.
- **Punch data not showing on website:** [TROUBLESHOOTING_PUNCH_NOT_SHOWING.md](TROUBLESHOOTING_PUNCH_NOT_SHOWING.md) – employee mapping (`device_user_id` / location), cron, and `attendance:process`.

---

## 10. Checklist

- [ ] Domain A record → VPS IP  
- [ ] PHP 8.2+, MySQL, Nginx installed  
- [ ] Database and user created  
- [ ] App in `/var/www/bioattendtime`, `.env` set, `migrate` and `seed` run  
- [ ] Nginx site enabled, `nginx -t` OK  
- [ ] SSL with certbot  
- [ ] Cron for `schedule:run`  
- [ ] Queue worker (supervisor) if using queues  
- [ ] Admin password changed after first login  
- [ ] Cron for `schedule:run` (so `attendance:process` runs and punch data appears in reports)  
- [ ] Employees have correct **Device User ID** and **Location** so punches map to attendance (see [TROUBLESHOOTING_PUNCH_NOT_SHOWING.md](TROUBLESHOOTING_PUNCH_NOT_SHOWING.md))

Your bioattendtime fingerprint time and attendance system will then be live on Hostinger VPS with your domain.
