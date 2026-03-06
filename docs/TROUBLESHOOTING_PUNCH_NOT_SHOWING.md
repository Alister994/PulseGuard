# Troubleshooting: Punch Data Not Showing on Website

After the device pushes to `/api/device/push` (or `/api/device/sync`), data can be stored but still **not appear in reports, dashboard, or attendance views**. This document explains the data flow and the main reasons it fails.

---

## How data flows from device to website

1. **Device → API**  
   T304F (or sync agent) sends a POST to `https://your-domain.com/api/device/push` with:
   - Header `X-Device-Key: <device_api_key>` (or Bearer / body `device_key`)
   - Body: e.g. `{"PIN":"1","DateTime":"2025-02-26 09:05:00"}` or `{"logs":[...]}` or `{"punches":[...]}`

2. **API → `attendance_logs` table**  
   The API checks the device token, then inserts **one row per punch** into `attendance_logs` with:
   - `device_id`, `device_user_id` (from PIN/UserID/employee_code), `punch_time`
   - **`employee_id`**: set only if an **employee exists** for that device’s **location** with matching **device_user_id** (or employee_no). Otherwise `employee_id` is **NULL**.

3. **Scheduler → `attendance:process`**  
   Every 15 minutes (if cron is set up), Laravel runs:
   - `php artisan attendance:process --days=3`  
   This reads **`attendance_logs`**, groups by employee, and writes/updates **`attendance_daily`** (one row per employee per day: status, work minutes, late, etc.).

4. **Website**  
   Reports, payroll, and attendance views read from **`attendance_daily`** (and related tables), **not** directly from `attendance_logs`. So:
   - If nothing is written into `attendance_daily` for an employee, **nothing will show** for that person on the website, even if raw punches exist in `attendance_logs`.

---

## Why punch data is “not mapped” or not showing

### 1. Employee not mapped (most common)

**Symptom:** API returns `200` and `inserted: 1`, and you see rows in `attendance_logs`, but no (or wrong) row in `attendance_daily` and nothing in reports.

**Reason:** The system can only attach a punch to a **known employee** in the **same location as the device**. Matching is done by:

- **Device’s location** (each device is linked to one location/branch).
- **Employee’s `device_user_id` or `employee_no`** must match the value sent by the device (e.g. `PIN` or `UserID` or `employee_code`).

If no employee in **that device’s location** has the same `device_user_id` / `employee_no` as the punch:

- The punch is still saved in `attendance_logs` with **`employee_id = NULL`**.
- The attendance processor **skips** logs with no matching employee, so **no row is created in `attendance_daily`** and nothing shows on the website.

**What to do:**

1. In admin: **Locations** → select the branch → **Employees** (or **Employees** list).
2. For each person who punches on the device, ensure:
   - **Location** = same as the **device’s location**.
   - **Device User ID** (and/or **Employee No**) = **exactly** the same as the ID sent by the device (e.g. PIN `1` → set Device User ID to `1`).
3. Save. Then run once:
   - `php artisan attendance:process --days=3`
4. Check **Reports / Attendance** again.

**Check in database (optional):**

```sql
-- Raw logs for a device (replace DEVICE_ID)
SELECT id, device_id, employee_id, device_user_id, punch_time
FROM attendance_logs WHERE device_id = DEVICE_ID ORDER BY punch_time DESC LIMIT 20;
```

If `employee_id` is NULL, that punch is not linked to any employee; fix the employee’s `device_user_id`/location and re-run `attendance:process`.

---

### 2. Device not set up or wrong API key

**Symptom:** Device or sync agent gets `401 Unauthorized` or punches never reach the server.

**What to do:**

1. In admin: **Locations** → select branch → **Devices** → **Add device** (or edit existing). Copy the **API Key**.
2. In device config (or sync agent): set **Web Server URL** to `https://your-domain.com/api/device/push` (or `/api/device/sync`) and send the API key as:
   - Header: `X-Device-Key: <api_key>`, or
   - Body: `device_key=<api_key>`
3. Ensure the device is **Active** in the admin.
4. Test with curl:

```bash
curl -X POST "https://your-domain.com/api/device/push" \
  -H "X-Device-Key: YOUR_DEVICE_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"PIN":"1","DateTime":"2025-02-26 10:00:00"}'
```

Expected: `{"success":true,"message":"Synced","inserted":1,...}`.

---

### 3. Attendance process not running (cron missing)

**Symptom:** `attendance_logs` has rows with correct `employee_id`, but `attendance_daily` is empty or not updating.

**Reason:** Daily attendance is built only when `php artisan attendance:process` runs. That is triggered by the **Laravel scheduler**, which must be run every minute via **cron**. If cron is not set, no one runs the scheduler and punches are never converted to daily attendance.

**What to do:**

1. Add cron for the web user (e.g. `www-data`):

```bash
crontab -u www-data -e
```

Add:

```
* * * * * cd /var/www/bioattendtime && php artisan schedule:run >> /dev/null 2>&1
```

2. Run once manually to backfill:

```bash
cd /var/www/bioattendtime
php artisan attendance:process --days=3
```

3. Wait up to 15 minutes for the next scheduled run, then check reports again.

---

### 4. Location mismatch

**Symptom:** Employee exists and `device_user_id` matches the punch, but still no attendance on the website.

**Reason:** The device is tied to a **location**. The API only links a punch to an employee that belongs to **that same location**. If the employee is in a different branch/location, they will not be matched.

**What to do:** In admin, set the employee’s **Location** to the same branch as the device they punch on.

---

## Summary checklist

| Check | Action |
|-------|--------|
| API returns 200 and `inserted >= 1` | Device and API key are OK. |
| Rows in `attendance_logs` with `employee_id = NULL` | Fix employee: same **location** as device, and **device_user_id** (or employee_no) = value sent by device (e.g. PIN). |
| No rows in `attendance_daily` for that employee/date | Run `php artisan attendance:process --days=3`; ensure cron runs `schedule:run` every minute. |
| Device returns 401 | Create/activate device in admin and use correct API key in header or body. |
| Employee has correct device_user_id but different location | Set employee’s location to the device’s branch. |

After fixing mapping and/or cron, run once:

```bash
cd /var/www/bioattendtime
php artisan attendance:process --days=3
```

Then refresh reports/dashboard; punch data should appear where attendance is shown (reports, payroll, etc.).
