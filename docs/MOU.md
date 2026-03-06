# Memorandum of Understanding (MoU)

**Date:** 25-02-2026

This Memorandum of Understanding (MoU) is entered into between the Software Developer (hereinafter referred to as **'Developer'**) and the Client (hereinafter referred to as **'Client'**) for the design, development, and deployment of a **Textile Workforce Automation Cloud System** integrated with **Realtime T304F Mini** biometric devices operating in **Push Mode**.

---

## 1. Project Scope

The Developer shall design and implement a cloud-based **Time & Attendance and Workforce Automation System** tailored for the textile industry environment.

- **Multi-Branch Support** (Active from initial phase)
- **Multiple Devices per Branch** (Push Mode Integration via URL Endpoint)
- **Raw Attendance Log Collection** (Device → Cloud Laravel API → MySQL)
- **Employee Management** (CRUD Operations)
- **Department Management** with Department-wise Weekly Off Configuration
- **Role-Based Access Control:** Super Admin, Branch Admin, HR, Manager, Employee
- **Shift Management** (Day/Night Shifts)
- **Shift Rotation Pattern** (15 Days Day + 15 Days Night)
- **Editable Grace Time** (Default 10 Minutes)
- **Editable Half-Day Rule** (Admin Configurable Hours)
- **Break Management** (Lunch + Tea Break Start/End Detection)
- **Forget Punch Correction Workflow**
- **Leave Management** (PL, CL, SL, Half-Day Leave) [paid leave considered]
- **Monthly Salary Calculation** (Daily Option Available but calculation hourly bases)
- **Cross-Midnight Shift Handling** (taking advice: already give notification miss punch)
- **Late Arrival Detection & Warning Notifications**
- **Web-Based Real-Time Notification System**
- **Manager OR HR Approval Workflow** (Admin Override, Super Admin Global Override)

---

## 2. Device Integration & Communication

Each device will transmit attendance logs to a secure API endpoint hosted on the Client's cloud server.

- **T304F Mini → HTTPS POST →** `https://<your-domain>/api/device/push`  
  (Alternative endpoint: `/api/device/sync` — same behaviour; use device API key in header `X-Device-Key` or in body as `device_key`.)

Each branch may contain multiple devices, and all devices will be mapped to their respective branch in the system central database.

---

## 3. Roles & Responsibilities

**Developer Responsibilities:**

- Design secure device API endpoint with token authentication
- Implement raw log storage and processed attendance engine
- Develop role-based access control system
- Implement leave and correction approval workflows
- Configure notification system (Web-based real-time alerts)
- Deploy system on production VPS server
- Provide basic system training and documentation
- Seed CRUD reference data (shifts, departments, shift patterns, department weekly offs) and role-wise users (e.g. 2 HR, 2 Manager) via seeders where applicable

**Client Responsibilities:**

- Provide stable internet connectivity for each device location
- Provide accurate employee, department, and shift data
- Ensure proper device installation and maintenance
- Provide server access credentials for deployment
- Approve configuration rules (Grace Time, Half-Day Hours, Weekly Off, etc.)
- Salary calculation export data as per requirement given example sheet

---

## 4. Approval Workflow

Attendance corrections and leave requests shall follow **Option B Approval Flow:**  
**Employee → Manager OR HR → Final.**  
Branch Admin may override approvals, and Super Admin retains global override authority.

---

## 5. Limitations & Exclusions

The Developer shall **not** be responsible for:

- Hardware malfunctions
- Device firmware limitations
- Fingerprint/face recognition inaccuracies due to environmental factors
- Internet connectivity failures at branch locations
- Data accuracy issues arising from incorrect employee, department, shift, or shift-pattern data entered or maintained by the Client
- CRUD table data (shifts, departments, shift patterns, weekly offs, etc.) beyond what is provided by default seeders; the Client is responsible for maintaining live data. Role-wise demo users (e.g. 2 HR, 2 Manager) are provided via seeders for initial setup only; the Client shall create and manage live HR/Manager users as needed.

---

## 6. Device Push API (Implementation Reference)

The system accepts punches in the following ways:

1. **T304F format (array):** `{ "logs": [ { "employee_code": "1", "timestamp": "2025-02-26 09:05:00", "device_id": 1 } ] }`
2. **Legacy format (array):** `{ "punches": [ { "device_user_id": "1", "punch_time": "2025-02-26 09:05:00" } ] }`
3. **Single punch (flat):** `{ "PIN": "1", "DateTime": "2025-02-26 09:05:00" }` or `UserID` / `timestamp` — device posts one punch per request.

Authentication: `X-Device-Key: <api_key>` or `Authorization: Bearer <api_key>` or body `device_key=<api_key>`.

Configure T304F Mini: **Communication → Server Client Mode → FKWeb → Web Server URL:**  
`https://<your-domain>/api/device/push` (or `/api/device/sync`). Leave Cloud ID empty. Save → Restart device.
