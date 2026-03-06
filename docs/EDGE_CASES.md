# BioTime – Edge Case Handling

## Late entry

- **Rule**: First punch (duty in) compared to shift start + grace minutes (editable per shift or global `grace_minutes` setting; default 10).
- **Behaviour**: If punch_1 &gt; shift_start + grace, `late_minutes` = difference; stored in `attendance_daily`; used in salary deduction (monthly/hourly).
- **Notification**: `LateEntryNotification` sent to branch admins/super admins for that branch.

## Double punch (same slot)

- **Prevention at ingest**: Device push API rejects duplicates: same device + same user + same punch time (rounded to minute). Only one log per minute per user per device is stored.
- **Processing**: If device sends two punches in the same minute, only one is stored; processor uses chronological order of stored punches (1–6). No separate “double punch” flag; duplicate is avoided at API level.

## Missing punch

- **Detection**: Processor sets `remarks` when punch count &lt; 2 (e.g. “Missing punch(s); only 1 punch(es)”) or odd number of punches (e.g. “Odd number of punches; possible missing out punch”).
- **Status**: If no punch-in at all, status can be set to `absent` and remarks “No punch-in recorded”. If only one punch, status remains present/half_day based on work_minutes; remarks explain shortfall.
- **Correction**: Employee can raise a **Forget Punch** request; after approval, `ForgetPunchRequest` is used in `AttendanceProcessor::getPunchesWithForgetCorrections()` to fill the missing slot with `requested_time`. Re-run attendance processing to apply.

## Cross-midnight shift

- **Rule**: Shift has `is_night_shift = true`; `end_time` is next calendar day (e.g. 20:00–08:00).
- **Behaviour**: In `AttendanceProcessor`, shift start for the “date” is computed as previous calendar day + start_time when `is_night_shift`; expected work minutes use start/end with end_time advanced by one day when end &lt; start. Late is computed against this effective shift start.

## Half-day

- **Rule**: Editable `half_day_hours` (default 4) in settings. If work_minutes &gt; 0 but &lt; half_day_hours * 60, status = `half_day`; remarks note “Below half-day hours”.
- **Salary**: Half-day counts as 0.5 day in monthly and daily salary logic.

## Weekly off

- **Rule**: Department has `department_weekly_offs` (day_of_week 0–6). On that day, if no punches, status = `weekly_off`; if they punch, still processed as present with remark “Weekly off”.

## Leave

- **Rule**: Approved leave (approved_paid / approved_unpaid) for that date sets attendance_daily status = `leave` and remark “Leave: &lt;type&gt;”. Paid leave counts in payable days; unpaid in deductions.

## Duplicate log prevention

- **API**: Same device_id + device_user_id (or employee_code) + punch_time within the same minute → skip insert; response still 200 with `inserted` count.
- **Processing**: No second unique constraint on raw table; application logic prevents duplicate lines per minute.

## Summary

| Edge case       | Handling                                                                 |
|-----------------|--------------------------------------------------------------------------|
| Late            | late_minutes; notification; salary deduction                             |
| Double punch    | One log per user per device per minute at API                            |
| Missing punch   | Remarks; forget-punch workflow to fill slot and reprocess                 |
| Cross-midnight  | is_night_shift; shift start from previous day; expected minutes correct   |
| Half-day        | half_day_hours setting; status half_day; 0.5 day in salary               |
| Weekly off      | department_weekly_offs; status weekly_off or present with remark         |
| Leave           | status leave; paid/unpaid in salary                                       |
| Duplicate log   | Skip same device + user + minute in DeviceSyncController                 |
