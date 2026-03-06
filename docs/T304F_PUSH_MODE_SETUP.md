# RealTime T304F Mini – Push Mode: Get Punches to Your Website

This guide explains **how punch data from the T304F Mini reaches your BioTime website** and **which files/SDK you need**.

---

## Two Ways to Get Punch Data to Your Site

| Way | What happens | You need |
|-----|----------------|----------|
| **A. Device built-in push** | T304F sends each punch (or batch) to a URL you set in the device menu. | Device firmware that supports “HTTP push” / “Server URL”. |
| **B. Sync agent (PC bridge)** | A small program on a PC uses the **RealTime SDK** to get logs from the T304F, then POSTs them to your website. | SDK from RealTime + a small app (we provide logic below). |

Most T304F setups use **B** (sync agent), because the device often talks **TCP/IP to a PC or server**, and the PC forwards data to your **HTTPS** website.

---

## Step 1: Get the SDK / Required Files from RealTime

1. Go to **RealTime Biometrics – Software / SDK** page:
   - India: **https://www.supportrealtime.com/Software.aspx**
   - Or: **https://realtimebiometrics.com/software**
2. Find **“Download SDK for Hardware”**.
3. Download the SDK that lists **“T304F mini”** (e.g. “T501 mini, **T304F mini**, RS9w, T304F+, RS910”).
4. Extract the ZIP. You typically get:
   - **DLL(s)** (e.g. for Windows: C#/.NET or C++ SDK)
   - **Header/lib files** if C++
   - **Sample code** or a small demo app that connects to the device and reads attendance logs
   - Sometimes a **PDF** or doc with API functions (e.g. “GetAttendanceLog”, “Connect”, etc.)

If you don’t see a direct “T304F mini” SDK, use the one for **T304F+** or the **Pro/RS** series that supports the same protocol; or contact RealTime support and ask for the **T304F Mini SDK** for “attendance log read” and “push/integration with our server”.

---

## Step 2: How the Machine “Pushes” and How You “Catch” It

### Option A: Device Has “Push to URL” in Menu

Some RealTime devices have a **Network / Server / Cloud** or **HTTP push** setting where you set:

- **Server URL**: your API endpoint  
- **Method**: POST  
- Sometimes **Auth**: token or key in header  

If your T304F has this:

1. In device menu (on screen or web config), set:
   - **URL**: `https://your-website.com/api/device/push` or `/api/device/sync`
   - **Header**: `X-Device-Key: YOUR_DEVICE_API_KEY` (get this from BioTime → Devices → your device → API Key).
2. Set body format to JSON (if the device allows). Your Laravel API accepts:
   - **T304F-style:** `{ "logs": [ { "employee_code": "1", "timestamp": "2025-02-26 09:05:00", "device_id": 1 } ] }`
   - **Or simple:** `{ "punches": [ { "device_user_id": "1", "punch_time": "2025-02-26 09:05:00" } ] }`

Then **each punch (or batch)** is sent by the device to that URL and your website “catches” it in `POST /api/device/sync`. No extra file on PC needed.

If your device **does not** have such a “Server URL / HTTP push” field, use **Option B**.

---

### Option B: Sync Agent on PC (SDK → Your Website) – Most Common

Here the **machine doesn’t talk to the internet directly**. It talks to a **PC on the same LAN** using the SDK. The PC then “pushes” the data to your website.

```
┌─────────────────────┐         LAN (TCP/IP/WiFi)        ┌──────────────────────────┐         HTTPS POST          ┌─────────────────────┐
│  RealTime T304F     │  ◄─────────────────────────────► │  PC – Sync Agent         │  ───────────────────────►  │  Your BioTime       │
│  Mini (punch here)  │    SDK connects, reads new logs  │  (uses RealTime SDK +    │   POST /api/device/sync   │  website (Laravel)   │
│                     │                                  │   sends to your URL)    │   X-Device-Key: <key>    │                     │
└─────────────────────┘                                  └──────────────────────────┘                           └─────────────────────┘
```

**What you do:**

1. **On the PC (same network as T304F):**
   - Install the **T304F mini SDK** (the one you downloaded).
   - Get the **device IP** from the T304F (Network settings on the device).
   - Write a small **sync agent** (or use the SDK sample and modify it) that:
     - Connects to the T304F (IP + port, as per SDK).
     - Calls the SDK function that **reads new attendance logs** (e.g. “GetAttendanceLog” or “ReadLog” – name depends on SDK).
     - For each log you get: **user ID** (employee code / device user id) and **punch time**.
     - Build JSON and **POST** to your website (see below).
   - Run this agent **every 1–5 minutes** (Task Scheduler / cron) or, if the SDK supports it, run it as a service that reacts to “new log” events and POSTs immediately (real-time push).

2. **Your website** already “catches” this: the **same** `POST /api/device/sync` endpoint receives the JSON and saves to `attendance_logs`. So the “push” from your side is: **PC pushes to your website**.

**No extra “file from SDK” is required on the website** – the website only needs to expose the API (which you already have). The SDK and the sync agent run **only on the PC** next to the T304F.

---

## Step 3: Exact Request Your Website Expects (So the Agent Can Send It)

Your Laravel endpoint is:

- **URL:** `POST https://your-domain.com/api/device/sync`
- **Auth:** Device API key in one of:
  - Header: `X-Device-Key: YOUR_DEVICE_API_KEY`
  - Or: `Authorization: Bearer YOUR_DEVICE_API_KEY`
  - Or body: `"device_key": "YOUR_DEVICE_API_KEY"`
- **Body (JSON):**

**Format 1 – T304F style (employee_code = user ID on device):**

```json
{
  "logs": [
    { "employee_code": "1", "timestamp": "2025-02-26 09:05:00", "device_id": 1 },
    { "employee_code": "2", "timestamp": "2025-02-26 09:07:00", "device_id": 1 }
  ]
}
```

**Format 2 – Legacy (device_user_id):**

```json
{
  "punches": [
    { "device_user_id": "1", "punch_time": "2025-02-26 09:05:00" },
    { "device_user_id": "2", "punch_time": "2025-02-26 09:07:00" }
  ]
}
```

- `employee_code` or `device_user_id` must match the **Device user ID** / **Employee code** you set for each employee in BioTime (and same location as the device).
- `device_id` in `logs` should be the BioTime device ID (integer); if omitted, the authenticated device is used.

So in your **sync agent** (the program using the SDK), after you read each log from the T304F, you build an array of such objects and POST to the URL above with the device API key. That’s how the machine’s punch data is “pushed” and “caught” by your site.

---

## Step 4: Summary – What You Need and Who Does What

| Item | Where / What |
|------|----------------|
| **SDK / files** | RealTime site: “Download SDK for Hardware” → **T304F mini** (or T304F+) package. Use it **on the PC**, not on the web server. |
| **Device → your site** | Either (A) device posts directly to your URL if it has “push to URL”, or (B) PC sync agent (using SDK) posts to `POST /api/device/sync`. |
| **Your website** | Already has `POST /api/device/sync`; no SDK file needed on the server. Just keep the device’s **API key** and use it in the agent (or in the device config if A). |
| **Employee mapping** | In BioTime, each employee’s **Device user ID** (or Employee code) must match the **user ID / employee_code** sent by the device or agent. |

If you tell me the **language** you prefer for the sync agent (e.g. C#, Python, PHP), I can outline the exact steps and a minimal code example that uses the SDK to read logs and POST to your Laravel API.
