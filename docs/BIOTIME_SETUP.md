# BioTime Attendance System – Setup & Overview

## What’s Implemented

- **Database:** Locations (branches), devices, employees, departments (with location + weekly off), shifts + shift breaks, attendance logs (raw), attendance_daily (processed, 4–6 punches), leave requests (PL/CL/SL/half-day, two-level approval), forget-punch requests, salary slips, settings, notifications. See [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md).
- **Roles:** Super Admin (global), Branch Admin, HR, Department Manager, Employee. Middleware: `super_admin`, `branch_admin`, `hr`, `dept_manager`, `has_branch`. See [ARCHITECTURE.md](ARCHITECTURE.md).
- **Auth:** Login by username or email. Default Super Admin: `super_admin_biotime` / `98245@biotime`; Branch Admin: `admin_biotime` / `98245@biotime`.
- **Device push API:** `POST /api/device/sync` or `POST /api/device/push` – token-based (X-Device-Key or Bearer). T304F format: `logs[]` with `employee_code`, `timestamp`, `device_id`; legacy `punches[]`; single punch: `PIN`/`UserID`, `DateTime`. See [DEVICE_SYNC_API.md](DEVICE_SYNC_API.md) and [MOU.md](MOU.md).
- **Attendance:** 4–6 punches; grace (editable, default 10 min); half-day rule (editable `half_day_hours`); weekly off per department; leave overlay; forget-punch correction; cross-midnight shift; duplicate prevention at API. Run `php artisan attendance:process` or scheduled every 15 min. Edge cases: [EDGE_CASES.md](EDGE_CASES.md).
- **Leave:** PL, CL, SL, half-day. Approval flow (Option B): Manager/HR → Final; admin override. Use `LeaveApprovalService` and `LeavePendingNotification`.
- **Shift rotation:** 15 days day / 15 days night via `ShiftRotationService` and `shift_rotation_start_date` on employees.
- **Salary:** Monthly (fixed) / hourly / daily. `SalaryService`; PDF slip: `/salary-slip/{employee_id}/{month}/{year}/pdf`.
- **Settings:** `working_hours_per_day`, `break_hours_per_day`, `grace_minutes` (default 10), `half_day_hours` (default 4), `shift_start_time`, `watermark_text`. `Setting::set('key', 'value')`.
- **Local:** [LOCAL_SETUP.md](LOCAL_SETUP.md). **Production:** [PRODUCTION_DEPLOYMENT.md](PRODUCTION_DEPLOYMENT.md). **Security:** [SECURITY.md](SECURITY.md).

## First-Time Setup

1. **Env:** Copy `.env.example` to `.env`, set `APP_KEY`, and configure MySQL:
   ```env
   DB_CONNECTION=mysql
   DB_DATABASE=biotime
   DB_USERNAME=...
   DB_PASSWORD=...
   ```
2. **Migrate & seed:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```
3. **Cron (VPS):** Add to crontab:
   ```bash
   * * * * * cd /path/to/biotime && php artisan schedule:run >> /dev/null 2>&1
   ```
   This runs `attendance:process` every 15 minutes.

## Admin Login

- **URL:** `/login`
- **Username:** `admin_biotime`
- **Password:** `98245@biotime`

## Multi-Location

- Create **locations** (e.g. A, B, C).
- Attach **admin user** to locations via `location_user` (one admin can have many locations).
- Per location: create **devices** (each gets an `api_key` for the sync agent).
- **Employees** are per location; set `device_user_id` to the ID from the fingerprint machine after enrollment.

## RealTime T304F Mini Sync (PC at Each Location)

- Use **RealTime T304F Mini SDK** (from RealTime Biometrics) to read attendance from the device over TCP/IP or WiFi.
- POST punches to `https://yourdomain.com/api/device/sync` with header `X-Device-Key: <device_api_key>`.
- Alternatively use USB/CSV export and the **sync-agent** Python script. See [FINGERPRINT_SYNC.md](FINGERPRINT_SYNC.md) for full details.

## Optional: Notify on New Leave

When creating a leave request (e.g. in a controller), notify admins:

```php
use App\Notifications\LeavePendingNotification;

$leaveRequest = LeaveRequest::create([...]);
foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
    $admin->notify(new LeavePendingNotification($leaveRequest));
}
```

## Backup

- Use your VPS backup (e.g. Hostinger) for MySQL and `storage/`.
- For “auto sync and backup”, add a daily job that dumps the DB and uploads to cloud storage (e.g. S3) or run your host’s backup tool.
