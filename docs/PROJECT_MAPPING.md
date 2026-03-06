# Project mapping (reference)

Single reference so the whole project stays aligned.

## Multiple devices – API keys in central database

- **All devices and API keys are stored in the central database** (table `devices`).
- **One device = one row = one API key.** Each device has a unique `api_key` (generated on create, 48 chars).
- **Multiple devices:** Create one device per machine/sync agent (e.g. Branch A – Gate 1, Branch A – Gate 2, Branch B – Reception). Each gets its own API key. The same push URL is used for all; the **request header `X-Device-Key`** identifies which device (and thus which location) the punch belongs to.
- **Flow:** Request with `X-Device-Key: <key>` → lookup `devices.api_key` → get `device_id` and `location_id` → store punch for that device; employee matching uses that location. No per-device config file on server; everything is in the DB.

## Location → Device → Push URL

- **Location** (branch): CRUD under Setup → Locations. Create first.
- **Device**: Belongs to one Location. Created under Setup → Devices. Each device has an **API key** (view/copy from Edit device).
- **Push URL**: Same for all devices: `POST {{ APP_URL }}/api/device/push` (or `/api/device/sync`). Send header **`X-Device-Key: <that device's api_key>`**.

## Employee ↔ Device punch (device_user_id)

- **Device user ID** on employee must match the value the device sends (e.g. PIN). Can be zero-padded, e.g. **00000001**.
- Matching: same **Location** as the device, and **device_user_id** or **employee_no** equal (exact or numeric zero-padded to 8 digits). So PIN `1` or `00000001` both match employee with device_user_id `00000001`.
- Register employees with **Device User ID** = value from machine (e.g. 00000001). Employee must be in the **same Location** as the device they punch on.

## Flow

1. Create **Location** (e.g. Main Branch).
2. Create **Device** for that location → copy **API key**.
3. Configure T304F or sync agent: URL = `https://your-domain.com/api/device/push`, header **X-Device-Key** = that API key.
4. Create **Employees** for that location; set **Device User ID** (e.g. 00000001) to match device PIN.
5. Punches → `attendance_logs` → cron runs `attendance:process` → `attendance_daily` → reports.

## Code references

- **Resolve employee from punch:** `Employee::findByDeviceUserIdForLocation($locationId, $deviceUserId)` (exact + zero-pad 8).
- **API:** `App\Http\Controllers\Api\DeviceSyncController` (sync/push); token from `X-Device-Key`, `Authorization: Bearer`, or body `device_key`.
- **Process raw → daily:** `attendance:process` (scheduler every 15 min); `AttendanceProcessor` groups by employee via `findByDeviceUserIdForLocation`.
