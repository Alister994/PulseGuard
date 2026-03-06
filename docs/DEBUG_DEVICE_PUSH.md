# Debug: Device push not reaching attendance_logs

When **last_sync_at: never** and **No punches in database**, the API is not receiving valid requests (or not at all). Follow this flow to find the cause.

---

## 0. Production: run test commands on the server

On the **production server** run:

```bash
cd /var/www/html/bioattendtime
php artisan device:push-test
```

This prints **exact curl commands** using your real API key and APP_URL. Then:

1. **Test ping** (no key): run the first curl. Expected: `{"ok":true,"message":"Push URL is live"}`. If this fails, the URL or Nginx/firewall is wrong.
2. **Test push**: run the second or third curl (with key in header or in URL). Expected: `{"success":true,"message":"Synced","inserted":1,...}`.
3. In another terminal: `tail -f storage/logs/laravel.log`. You must see **"Device push request received"** when curl runs. If you see **"Device push: success"**, run `php artisan device:push-status` and you should see last_sync_at and recent punches.

**If device cannot send custom headers**, set the device URL to include the key:  
`https://YOUR-DOMAIN.com/api/device/push?api_key=YOUR_DEVICE_API_KEY`  
(Paste the key from Admin → Devices → Edit device.)

---

## 1. Confirm requests reach the server

On production, tail the log while you trigger a punch (or send a test POST):

```bash
cd /var/www/html/bioattendtime
tail -f storage/logs/laravel.log
```

Then from **another terminal or your PC** send a test request (replace URL and API key):

```bash
curl -X POST 'https://YOUR-DOMAIN.com/api/device/push' \
  -H 'X-Device-Key: YOUR_DEVICE_API_KEY' \
  -H 'Content-Type: application/json' \
  -d '{"PIN":"00000001","DateTime":"2026-02-26 10:00:00"}'
```

**What you see in laravel.log:**

| Log message | Meaning |
|-------------|--------|
| **Device push request received** (with body_keys, content_type) | Request reached Laravel. Check next lines for auth/payload. |
| **Device push: missing token** | No API key in header or body. Device/sync agent must send `X-Device-Key` or body `device_key` (or `api_key` / `token`). |
| **Device push: invalid or inactive device** | Key not found in DB or device is inactive. Copy key from Admin → Devices → Edit device. |
| **Device push: invalid or empty payload** (with body_keys, sample) | Body format not recognised. Use body_keys/sample to see what the device sends and adjust device config or our parser. |
| **Device push: success** (inserted: N) | Punch accepted; `last_sync_at` will update and row appears in attendance_logs. |

If you see **no new lines** in laravel.log when you punch or run curl:

- Request is not reaching the app: **firewall, Nginx, or URL wrong** (e.g. device pointing to wrong domain/path).
- Or **HTTPS/SSL** problem from device/PC (e.g. self-signed cert, wrong port).

---

## 2. Integration flow (what must be correct)

```
T304F Mini (or sync agent on PC)
  → POST https://YOUR-DOMAIN.com/api/device/push
  → Header: X-Device-Key: <exact API key from Admin → Devices → Edit>
  → Body: JSON e.g. {"PIN":"00000001","DateTime":"2026-02-26 10:00:00"}
       or form: PIN=00000001&DateTime=2026-02-26 10:00:00
  → Laravel receives → checks token → finds device → parses payload → inserts attendance_logs → updates device.last_sync_at
  → Cron runs schedule:run → attendance:process → builds attendance_daily (reports)
```

Checklist:

1. **Device / sync agent URL** = `https://YOUR-DOMAIN.com/api/device/push` (same as in curl).
2. **API key** = exact value from Admin → Setup → Devices → Edit that device → copy “API key”.
3. **Key sent** = in header `X-Device-Key` (preferred) or in body as `device_key`, `api_key`, or `token`.
4. **Body** = at least one user id (e.g. `PIN` or `UserID`) and one time (e.g. `DateTime` or `timestamp`). JSON or form-urlencoded both supported.

---

## 3. Test from the server (same machine)

If the device cannot reach the internet, test that the app accepts a valid request from the server itself:

```bash
cd /var/www/html/bioattendtime
php artisan tinker
```

In tinker:

```php
$key = \App\Models\Device::where('is_active', true)->value('api_key');
echo $key;
// Copy the key, then exit and run curl to localhost or your domain
```

Then:

```bash
curl -X POST 'https://YOUR-DOMAIN.com/api/device/push' \
  -H 'X-Device-Key: PASTE_KEY_HERE' \
  -H 'Content-Type: application/json' \
  -d '{"PIN":"00000001","DateTime":"2026-02-26 10:00:00"}'
```

Expected: `{"success":true,"message":"Synced","inserted":1,"device_id":1}` and a new line in `storage/logs/laravel.log` with **Device push: success**.

---

## 4. Typical causes of “never” and no punches

| Cause | Fix |
|-------|-----|
| T304F not configured to push to your URL | Set Server URL = `https://YOUR-DOMAIN.com/api/device/push` or with key = `https://YOUR-DOMAIN.com/api/device/push?api_key=YOUR_KEY`. |
| Device does not support custom header | Use **key in URL**: `.../api/device/push?api_key=YOUR_KEY`. Or body: `device_key=YOUR_KEY`. App accepts key in header, body, or query. |
| Sync agent on PC not running or wrong URL/key | Point agent to same URL; set key as in Admin; ensure agent runs (e.g. as service). |
| Firewall blocks outbound from device/PC | Allow HTTPS to your server IP/domain from device/sync agent network. |
| Device sends different field names | Check laravel.log “body_keys” and “sample”; we accept PIN/UserID/employee_code and DateTime/datetime/timestamp/punch_time. |
| Wrong API key (typo, old key) | Run `php artisan device:push-test` on server for curl with correct key; or copy from Admin → Devices → Edit. |
| APP_URL wrong in .env | Set `APP_URL=https://your-actual-domain.com`. Run `php artisan config:cache` after change. |

After fixing, run again:

```bash
php artisan device:push-status --limit=25
```

You should see **last_sync_at** with a time and “Recent punches” with rows.
