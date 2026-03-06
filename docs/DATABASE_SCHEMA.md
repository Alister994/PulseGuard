# BioTime Database Schema

## Core

### users
| Column        | Type         | Notes                    |
|---------------|--------------|--------------------------|
| id            | bigint PK    |                          |
| name          | string       |                          |
| username      | string unique| nullable                 |
| email         | string unique|                          |
| password      | string       |                          |
| role          | string       | super_admin, branch_admin, hr, department_manager, employee |
| is_active     | boolean      |                          |
| location_id    | FK → locations | nullable (branch)     |
| employee_id   | FK → employees | nullable (when role=employee) |
| remember_token, timestamps |  |              |

### locations (branches)
| Column    | Type    |
|-----------|---------|
| id        | bigint PK |
| name      | string  |
| address   | string nullable |
| timezone  | string default Asia/Kolkata |
| is_active | boolean |
| timestamps |  |

### location_user (pivot)
| Column      | Type   |
|-------------|--------|
| id          | bigint PK |
| location_id | FK     |
| user_id     | FK     |
| timestamps  | unique(location_id, user_id) |

### devices
| Column        | Type    |
|---------------|---------|
| id            | bigint PK |
| location_id   | FK      |
| name          | string  |
| device_serial | string nullable |
| api_key       | string unique nullable (token for push) |
| last_sync_at  | timestamp nullable |
| is_active     | boolean |
| timestamps    |  |

### employees
| Column                   | Type    |
|--------------------------|---------|
| id                       | bigint PK |
| location_id              | FK nullable |
| address                  | text nullable |
| department_id            | FK nullable |
| shift_id                 | FK nullable |
| shift_rotation_start_date| date nullable |
| rotation_phase           | tinyint default 0 (0=day, 1=night period) |
| device_user_id           | string nullable (device enrollment ID) |
| employee_no              | string nullable |
| name                     | string  |
| email, phone             | string nullable |
| join_date                | date nullable |
| salary_type              | string default monthly (monthly, hourly, daily) |
| salary_value             | decimal(12,2) |
| currency                 | string default INR |
| is_active                | boolean |
| timestamps               | unique(location_id, device_user_id) |

### departments
| Column      | Type    |
|-------------|---------|
| id          | bigint PK |
| location_id | FK nullable (branch-scoped) |
| name        | string  |
| description | string nullable |
| is_active   | boolean |
| timestamps  |  |

### department_weekly_offs
| Column        | Type   |
|---------------|--------|
| id            | bigint PK |
| department_id | FK     |
| day_of_week   | tinyint (0=Sun..6=Sat) |
| timestamps    | unique(department_id, day_of_week) |

### shifts
| Column        | Type    |
|---------------|---------|
| id            | bigint PK |
| department_id | FK     |
| name          | string  |
| start_time    | time   |
| end_time      | time   |
| is_night_shift| boolean (cross-midnight) |
| grace_minutes | smallint default 0 |
| is_active     | boolean |
| timestamps    |  |

### shift_breaks
| Column            | Type   |
|-------------------|--------|
| id                | bigint PK |
| shift_id          | FK     |
| break_type        | string (lunch, tea, dinner) |
| start_time, end_time | time nullable |
| duration_minutes  | smallint nullable |
| sort_order        | tinyint |
| timestamps        |  |

## Attendance

### attendance_logs (raw from device)
| Column        | Type    |
|---------------|---------|
| id            | bigint PK |
| device_id     | FK     |
| employee_id    | FK nullable |
| device_user_id| string  |
| punch_time    | datetime |
| punch_sequence| tinyint default 1 (1–6) |
| punch_type    | string nullable (in, out, break_in, break_out) |
| synced_at     | timestamp |
| timestamps    | index(device_id, punch_time), index(employee_id, punch_time) |

### attendance_daily (processed)
| Column           | Type    |
|------------------|---------|
| id               | bigint PK |
| employee_id      | FK     |
| date             | date   |
| punch_1_at … punch_6_at | datetime nullable |
| work_minutes     | int default 0 |
| break_minutes    | int default 0 |
| lunch_minutes, tea_minutes | int default 0 |
| late_minutes     | int default 0 |
| overtime_minutes | int default 0 |
| status           | string (present, half_day, absent, leave, holiday, weekly_off) |
| remarks          | text nullable |
| timestamps       | unique(employee_id, date) |

## Leave & Corrections

### leave_requests
| Column               | Type    |
|----------------------|---------|
| id                   | bigint PK |
| employee_id          | FK     |
| from_date, to_date   | date   |
| type                 | string default leave |
| leave_type           | string default CL (PL, CL, SL, half_day) |
| is_half_day          | boolean default false |
| reason               | text nullable |
| status               | string (pending, approved_paid, approved_unpaid, rejected) |
| approval_level       | string (pending_manager, pending_hr, approved, rejected) |
| approved_by          | FK users nullable (final) |
| approved_at          | timestamp nullable |
| approved_by_manager  | FK users nullable |
| approved_at_manager  | timestamp nullable |
| approved_by_hr       | FK users nullable |
| approved_at_hr       | timestamp nullable |
| admin_remarks        | text nullable |
| timestamps           |  |

### forget_punch_requests
| Column        | Type    |
|---------------|---------|
| id            | bigint PK |
| employee_id   | FK     |
| date          | date   |
| punch_slot    | tinyint (1–6) |
| punch_type    | string default in |
| requested_time| datetime |
| reason        | text nullable |
| status        | string (pending, approved, rejected) |
| requested_by  | FK users nullable |
| approved_by   | FK users nullable |
| approved_at   | timestamp nullable |
| admin_remarks | text nullable |
| timestamps    |  |

## Payroll & Config

### salary_slips
| Column      | Type    |
|-------------|---------|
| id          | bigint PK |
| employee_id | FK     |
| month      | tinyint |
| year       | smallint |
| base_amount, additions, deductions, net_amount | decimal(12,2) |
| breakdown   | json nullable |
| pdf_path   | string nullable |
| timestamps  | unique(employee_id, month, year) |

### settings (key-value)
| Column | Type   |
|--------|--------|
| key    | string PK |
| value  | text nullable |
| timestamps |  |

Common keys: `working_hours_per_day`, `break_hours_per_day`, `grace_minutes`, `half_day_hours`, `shift_start_time`, `watermark_text`.

### notifications (Laravel)
Standard Laravel notifications table for database channel.
