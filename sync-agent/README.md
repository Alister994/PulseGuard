# Sync agent: RealTime T304F Mini / CSV → BioAttent

Run this on a **PC on the same LAN** as your **RealTime T304F Mini** (or any machine that can produce a CSV of punches) to push attendance into the BioAttent website.

## 1. Get your device API key

In BioAttent: **Setup → Devices** → Add device (or open existing) → copy **API key**.

## 2. Configure the agent

```bash
cd sync-agent
copy config.example.json config.json
```

Edit `config.json`:

- **api_url**: Your site URL + `/api/device/sync`, e.g. `https://yourdomain.com/api/device/sync`
- **device_key**: The API key from step 1
- **csv_columns**: If your CSV uses different column names, set e.g. `"device_user_id": "User ID"`, `"punch_time": "Punch Time"`

## 3. Install Python deps

```bash
pip install requests
```

## 4. Run with a CSV file

If your RealTime T304F Mini or its desktop software exports attendance to CSV (or you use USB export and convert to CSV):

```bash
python sync_to_biotime.py path\to\punches.csv
```

CSV should have at least:

- A column for **user ID** (same as **Device user ID** in BioAttent employees)
- A column for **punch date/time** (e.g. `2025-02-10 09:00:00`)

Optional: `punch_type` (e.g. `in`, `out`).

## 5. Automate (optional)

- **Windows**: Task Scheduler — run `python sync_to_biotime.py C:\path\to\export\punches.csv` every 5–10 minutes (after your export runs).
- **Real-time from device**: Use **RealTime T304F Mini SDK** (from RealTime Biometrics) to pull logs from the device over TCP/IP or WiFi, then POST the same JSON to the API (see project `docs/FINGERPRINT_SYNC.md`).

## API format (for custom bridge)

If you write your own bridge (e.g. using RealTime SDK), POST to the same endpoint:

```http
POST /api/device/sync
Content-Type: application/json
X-Device-Key: YOUR_DEVICE_API_KEY

{
  "punches": [
    { "device_user_id": "1", "punch_time": "2025-02-10 09:00:00", "punch_type": "in" },
    { "device_user_id": "1", "punch_time": "2025-02-10 18:00:00", "punch_type": "out" }
  ]
}
```

Duplicate punches (same device + user + minute) are ignored by the server.
