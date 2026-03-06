# Device Sync API (PayTime / Mantra mBio-G1 / RealTime T304F)

The Laravel app accepts attendance punch data from a sync agent running on each location PC, or from devices that support HTTP push (e.g. RealTime T304F).  
**For RealTime T304F Mini push mode (which SDK, how to send punches to this API):** see [T304F_PUSH_MODE_SETUP.md](T304F_PUSH_MODE_SETUP.md).

## Endpoint

- **URL:** `POST /api/device/sync` or `POST /api/device/push` (both accepted; MoU references `/api/device/push` for T304F Mini).
- **Auth:** Device API key (see below).

## Authentication

Send the device API key in one of these ways:

- Header: `X-Device-Key: <api_key>`
- Or body: `device_key=<api_key>`

Each device has a unique `api_key` generated when the device is created in the admin panel. Use that key in the sync agent on the PC connected to that machine.

## Request body

Send a JSON body with an array of punches:

```json
{
  "punches": [
    {
      "device_user_id": "1",
      "punch_time": "2025-02-10 09:05:00",
      "punch_sequence": 1,
      "punch_type": "in"
    },
    {
      "device_user_id": "1",
      "punch_time": "2025-02-10 13:00:00",
      "punch_sequence": 2,
      "punch_type": "break_in"
    }
  ]
}
```

### Field mapping

| PayTime / agent field | Laravel field   | Notes |
|-----------------------|-----------------|--------|
| `device_user_id` / `userId` / `user_id` | `device_user_id` | Required. ID of user on the machine. |
| `punch_time` / `timestamp` / `dateTime` / `DateTime` | `punch_time`     | Required. Date/time of punch. |
| `PIN` / `UserID` (single-punch format) | same as `device_user_id` | When device sends one punch per request (flat body, no `logs`/`punches` array). |
| `punch_sequence`      | `punch_sequence` | Optional. 1=in, 2=break start, 3=break end, 4=out. |
| `punch_type`          | `punch_type`    | Optional. in, out, break_in, break_out. |

If you send only `device_user_id` and `punch_time`, the server will still accept the request. Punch order per day is inferred from time when building daily attendance (4-punch logic).

## Response

- **200:** `{ "success": true, "message": "Synced", "inserted": 4, "device_id": 1 }`
- **401:** Missing or invalid device key.
- **422:** Invalid payload (e.g. not an array).

## Sync agent (PC at each location)

1. Use PayTime SDK 3.8 on the PC to read attendance logs from the Mantra mBio-G1 (LAN/USB).
2. Map device user ID from the machine to the same `device_user_id` used when enrolling employees in the Laravel app.
3. Periodically (e.g. every 1–5 minutes) POST the new punches to `https://yourdomain.com/api/device/sync` with the device’s `api_key`.
4. Create the device and get its `api_key` from the admin panel (Locations → Device → API Key).

## After sync

- Raw punches are stored in `attendance_logs`.
- Run `php artisan attendance:process` (e.g. via cron every 15 minutes) to build `attendance_daily` (4-punch: in, break start, break end, out) and compute work/break/late/overtime. Late entries trigger in-app notifications to admins.
