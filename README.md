# PulseGuard – Laravel Uptime & SSL Monitor

Production-ready Laravel 11 application for monitoring website uptime and SSL certificate expiration. Includes HTTP ping every minute, downtime logging, response time tracking, SSL expiry alerts (30 days before), and configurable notifications (Slack, Telegram, Email, Webhook). Built with a service layer, queue workers, Laravel Scheduler, and optional WebSocket events for real-time alerts.

---

## Features

- **Website monitoring** – HTTP ping every minute (configurable), status tracking (200, 5xx, timeout), downtime incidents, response time history
- **SSL monitoring** – Certificate expiry check, alert 30 days before expiration
- **Alerts** – Slack, Telegram, Email, and custom Webhook
- **Dashboard** – Real-time uptime %, response time charts, incident history, filtering (Tailwind)
- **Scaling** – Batch processing, queue chunking, retry strategy, configurable workers
- **Architecture** – Service layer, monitoring engine, event-driven failure detection, configurable alert channels
- **API** – REST API with Sanctum auth and rate limiting (60/min)
- **Deployment** – Supervisor example, cron for scheduler, Docker support

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+ (or SQLite for local)
- Node/npm optional (dashboard uses Tailwind CDN)

---

## Installation

### 1. Clone and install

```bash
git clone https://github.com/your-org/pulseguard.git
cd pulseguard
cp .env.example .env
php artisan key:generate
composer install
```

### 2. Database

Using **MySQL** (recommended for production):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pulseguard
DB_USERNAME=root
DB_PASSWORD=your_password
```

Using **SQLite** (default in `.env.example`):

```bash
touch database/database.sqlite
```

Then run migrations:

```bash
php artisan migrate
```

### 3. Create a user (for API and optional dashboard auth)

```bash
php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);
```

### 4. Queue and scheduler

**Local development:**

```bash
# Terminal 1 – web server
php artisan serve

# Terminal 2 – queue worker
php artisan queue:work

# Terminal 3 – scheduler (runs every minute)
php artisan schedule:work
```

**Production:**

- **Cron** – Add one line to crontab (see `cron.example`):

  ```bash
  * * * * * cd /path/to/pulseguard && php artisan schedule:run >> /dev/null 2>&1
  ```

- **Supervisor** – Use `supervisor.conf.example` to run one or more `queue:work` processes.

---

## Configuration

Copy `cron.example` and `supervisor.conf.example` and adjust paths.

### Environment (`.env`)

| Variable | Description |
|----------|-------------|
| `PULSEGUARD_APP_NAME` | App name in UI (default: PulseGuard) |
| `PULSEGUARD_HTTP_TIMEOUT` | HTTP check timeout in seconds (default: 10) |
| `PULSEGUARD_SSL_ALERT_DAYS` | Days before SSL expiry to alert (default: 30) |
| `PULSEGUARD_QUEUE_CONNECTION` | Queue connection (default: database) |
| `PULSEGUARD_CHUNK_SIZE` | Sites per batch (default: 10) |
| `PULSEGUARD_RETRY_ATTEMPTS` | Job retries (default: 3) |
| `PULSEGUARD_SLACK_ENABLED` | Enable Slack alerts |
| `PULSEGUARD_SLACK_WEBHOOK_URL` | Slack incoming webhook URL |
| `PULSEGUARD_TELEGRAM_ENABLED` | Enable Telegram |
| `PULSEGUARD_TELEGRAM_BOT_TOKEN` | Telegram bot token |
| `PULSEGUARD_TELEGRAM_CHAT_ID` | Telegram chat ID |
| `PULSEGUARD_ALERT_EMAILS` | Comma-separated email addresses |
| `PULSEGUARD_WEBHOOK_URL` | Custom webhook URL for alerts |

---

## Usage

### Web dashboard

- Open `/dashboard` for the main overview.
- Click a site for uptime %, response time graph, and incident history.
- Filter by status and search (optional; no auth by default – add middleware if needed).

### API

- **Auth:** `POST /api/auth/token` with `email` and `password` to get a Bearer token.
- **Sites:** `GET/POST /api/sites`, `GET/PUT/PATCH/DELETE /api/sites/{id}`.
- **Stats:** `GET /api/sites/{id}/stats?days=30`, `GET /api/dashboard`.
- **Incidents:** `GET /api/sites/{id}/incidents`.
- **Checks:** `GET /api/sites/{id}/checks`.

See [docs/API.md](docs/API.md) for full API documentation.

### Adding a site via API

```bash
curl -X POST http://localhost:8000/api/sites \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Example","url":"https://example.com","check_interval_minutes":1}'
```

Sites are checked every minute by the scheduler; SSL checks run daily (e.g. 02:00).

---

## Docker

```bash
docker-compose up -d
```

- **app** – Laravel on port 8000  
- **queue** – Queue worker  
- **scheduler** – Runs `schedule:run` every 60 seconds  
- **mysql** – MySQL 8

Set `APP_URL` and other env vars in `docker-compose.yml` or a `.env` file as needed.

---

## Project structure (high level)

- **Migrations** – `monitored_sites`, `http_checks`, `downtime_incidents`, `ssl_checks`, `alert_logs`, `personal_access_tokens`
- **Models** – `MonitoredSite`, `HttpCheck`, `DowntimeIncident`, `SslCheck`, `AlertLog`
- **Services** – `Monitoring\HttpPingService`, `Monitoring\MonitoringEngine`, `Monitoring\UptimeCalculator`, `Ssl\SslChecker`, `Alerts\AlertDispatcher` + channel implementations
- **Events** – `SiteDownDetected`, `SiteRecovered`, `SslExpiringSoon` (broadcast to `pulseguard` and `pulseguard.site.{id}`)
- **Jobs** – `RunHttpCheckJob`, `RunSslCheckJob`, `ProcessSitesCheckBatchJob`
- **Commands** – `pulseguard:dispatch-checks` (every minute), `pulseguard:dispatch-ssl` (daily)

---

## Running locally and in production

- **Locally:** Use `php artisan serve`, `queue:work`, and `schedule:work` as above; SQLite or MySQL; optional `.env` for Slack/Telegram.
- **Production:** Use a real web server (e.g. Nginx + PHP-FPM), cron for scheduler, Supervisor for queue workers, MySQL, and set `APP_ENV=production`, `APP_DEBUG=false`.

---

## License

MIT.
=======
# BioAttent

**Fingerprint Time & Attendance** – Laravel app for multi-location attendance, payroll, and reports. Works with Mantra mBio-G1 (PayTime SDK) via sync agent.

- **Local:** See [docs/BIOTIME_SETUP.md](docs/BIOTIME_SETUP.md) (or project docs) for setup.
- **Production (Hostinger VPS + domain):** See [docs/DEPLOYMENT_HOSTINGER_VPS.md](docs/DEPLOYMENT_HOSTINGER_VPS.md).

---

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
