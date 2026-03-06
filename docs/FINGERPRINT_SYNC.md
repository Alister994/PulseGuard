# Fingerprint Device Sync (RealTime T304F Mini)

This guide explains how to sync your **RealTime T304F Mini** (face & fingerprint biometric terminal) with the BioAttent website database over LAN.

## Supported device: RealTime T304F Mini

- **Connectivity:** TCP/IP, WiFi, optional 4G/5G, cloud
- **Storage:** Up to 100,000 attendance records; ~1,000 fingerprints, ~500 face templates
- **Features:** Face + fingerprint + RFID card + PIN; 3-inch display; USB export; offline/online modes
- **SDK:** RealTime Biometrics provides SDK and documentation for hardware integration (contact them or see [realtimebiometrics.com](https://realtimebiometrics.com))

## Architecture

```
┌─────────────────────────┐      TCP/IP / WiFi (LAN)    ┌──────────────────────┐      HTTPS       ┌─────────────────────┐
│  RealTime T304F Mini    │ ◄─────────────────────────► │  Sync agent          │ ───────────────► │  BioAttent website   │
│  (face + fingerprint    │   pull attendance logs      │  (PC on same network  │   POST /api/     │  (Laravel API)        │
│   terminal)             │   or USB export → CSV)      │   or server)          │   device/sync   │                       │
└─────────────────────────┘                             └──────────────────────┘                  └─────────────────────┘
```

- **T304F Mini:** On your LAN (WiFi or Ethernet). Stores up to 100,000 transaction logs.
- **Sync agent:** A program that (1) connects to the device over TCP/IP and pulls new logs (via RealTime SDK), or (2) reads exported CSV/USB data and sends it to your website API.
- **Website:** Laravel app exposes `POST /api/device/sync` and stores punches in `attendance_logs`.

## Step 1: Register the device in BioAttent

1. Log in → **Setup** → **Devices**.
2. Click **Add device**.
3. Choose **Location**, enter **Name** (e.g. "Reception T304F Mini") and optional **Device serial**.
4. Save. Copy the **API key** and keep it secret (used by the sync agent).

## Step 2: Map employees to device user IDs

On the T304F Mini, each person has a **user ID** (number or code). In BioAttent, each employee has a **Device user ID** field.

- In **Employees** → Edit an employee → set **Device user ID** to the **exact** ID used on the T304F Mini (e.g. `1`, `101`, `EMP001`).
- Only employees with a matching `device_user_id` (and same location as the device) will be linked to attendance logs.

## Step 3: Get logs from the RealTime T304F Mini

### Option A: RealTime SDK (recommended for real-time sync)

1. Contact **RealTime Biometrics** for the **T304F Mini SDK** (or check [realtimebiometrics.com](https://realtimebiometrics.com) for downloads/support).
2. On a **PC on the same LAN** as the device (WiFi or Ethernet), use the SDK to:
   - Connect to the T304F Mini (device IP from its network settings).
   - Pull new attendance/transaction logs (user ID + date/time per punch).
3. Build a small **bridge** (e.g. C#, Java, or PHP if the SDK supports it) that:
   - Calls the SDK to get new logs.
   - Converts each log to the JSON format below.
   - POSTs to `https://your-website.com/api/device/sync` with header `X-Device-Key: YOUR_DEVICE_API_KEY`.

Run this bridge on a schedule (e.g. every 5–10 minutes) via Task Scheduler or cron.

### Option B: USB export or CSV + Python script

The T304F Mini supports **USB disk** export. If the device or RealTime desktop software can export attendance to CSV:

1. Export CSV with columns for **user ID** and **punch date/time** (e.g. `userId`, `dateTime`).
2. Use the provided **Python sync agent** in the `sync-agent` folder to POST to the API (see `sync-agent/README.md`).

You can automate: run the export (if the vendor tool supports it), then run the Python script after each export.

### Option C: Manual test via API

To verify the website stores punches and links employees:

```bash
curl -X POST "https://your-website.com/api/device/sync" \
  -H "Content-Type: application/json" \
  -H "X-Device-Key: YOUR_DEVICE_API_KEY" \
  -d '{
    "punches": [
      { "device_user_id": "1", "punch_time": "2025-02-10 09:00:00", "punch_type": "in" }
    ]
  }'
```

Replace `YOUR_DEVICE_API_KEY` and the URL with your real values.

## API contract: `POST /api/device/sync`

- **Auth:** Header `X-Device-Key: <api_key>` or body `device_key: <api_key>`.
- **Body:** JSON with an array of punches.

| Field            | Required | Description |
|------------------|----------|-------------|
| `device_user_id` | Yes      | User ID from the machine (string, e.g. `"1"`, `"101"`). |
| `punch_time`     | Yes      | Date/time (e.g. `2025-02-10 09:00:00` or ISO 8601). |
| `punch_sequence` | No       | 1=in, 2=break start, 3=break end, 4=out. |
| `punch_type`     | No       | e.g. `in`, `out`, `break_in`, `break_out`. |

Duplicates (same device + same `device_user_id` + same minute) are ignored. The API links punches to employees by `device_user_id` and device location.

## Summary checklist

1. **Website:** Create the device in **Setup → Devices**, copy **API key**.
2. **Employees:** Set **Device user ID** to match the T304F Mini user ID.
3. **LAN:** Give the T304F Mini a static IP (WiFi or Ethernet); ensure the sync PC can reach it and your website.
4. **Sync:** Use RealTime SDK to pull logs and POST to `POST /api/device/sync`, or use USB/CSV export + Python agent.
5. **Schedule:** Run sync every 5–10 minutes for up-to-date attendance.

After logs are in `attendance_logs`, your existing daily attendance processing (e.g. into `attendance_daily`) uses them for reports and payroll.
