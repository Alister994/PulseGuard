# BioTime Enterprise Architecture

## Overview

Textile Workforce Automation Cloud with RealTime T304F Mini (Push Mode), multi-branch, role-based access, and approval workflows.

## Folder Structure

- **app/Console/** – ProcessAttendanceCommand (attendance:process)
- **app/Http/Controllers/Api/** – DeviceSyncController (device push, token auth)
- **app/Http/Middleware/** – EnsureSuperAdmin, EnsureBranchAdminOrSuperAdmin, EnsureHrOrAbove, EnsureDepartmentManagerOrAbove, EnsureUserHasBranch
- **app/Jobs/** – ProcessAttendanceJob (queued attendance)
- **app/Models/** – User, Location, Device, Employee, Department, DepartmentWeeklyOff, Shift, ShiftBreak, AttendanceLog, AttendanceDaily, LeaveRequest, ForgetPunchRequest, SalarySlip, Setting
- **app/Notifications/** – LateEntryNotification, DailyHoursShortfallNotification, LeavePendingNotification (database + optional broadcast)
- **app/Services/** – AttendanceProcessor, LeaveApprovalService, SalaryService, ShiftRotationService
- **database/migrations/** – Full schema
- **routes/api.php** – POST /api/device/sync
- **routes/web.php** – Web UI + auth

## Layers

- Controllers: HTTP only; delegate to Services.
- Services: Business logic (attendance, leave, salary, shift rotation).
- Models: Eloquent + relationships.
- Jobs: Async attendance processing.
- Notifications: Database + optional broadcast.

## Roles and Middleware

- **Super Admin** – `super_admin` – Global.
- **Branch Admin** – `branch_admin` – Assigned branch(es).
- **HR** – `hr` – Branch.
- **Department Manager** – `dept_manager` – Department.
- **Employee** – No middleware; self only.
- **has_branch** – Ensures user has at least one location (except Super Admin).

## Device Push API

- Endpoint: `POST /api/device/sync`
- Auth: Device `api_key` via `X-Device-Key` or `Authorization: Bearer <token>` or body `device_key`.
- T304F: `logs[]` with `employee_code`, `timestamp`, `device_id`.
- Legacy: `punches[]` with `device_user_id`, `punch_time`, `punch_sequence`, `punch_type`.
- Duplicate: same device + user + minute → skipped.

## Attendance Flow

1. Device pushes logs → `attendance_logs`.
2. AttendanceProcessor (scheduled or job): groups by employee, applies forget-punch corrections, computes work/break/late/overtime; respects shift (and rotation), grace, half-day hours, weekly off, leave; writes `attendance_daily`; sends notifications.

## Leave Approval (Option B)

- Employee → Manager or HR → Final (approved_paid / approved_unpaid / rejected).
- LeaveApprovalService: approveByManagerOrHr, approveFinal, reject, adminOverride.

## Salary Engine

- SalaryService::calculateForMonth – Monthly (present + half-day + paid leave, late/overtime), hourly (work_minutes), daily (present + half-day).

## Multi-Branch

- Location = branch; Device and Employee belong to Location; Department can have location_id; users linked via location_id or location_user.
