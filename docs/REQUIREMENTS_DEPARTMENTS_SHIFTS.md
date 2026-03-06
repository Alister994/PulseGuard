# Optimized client requirements: Departments, shifts & 6-punch attendance

## Architecture (confirmed)

```
Fingerprint Device (LAN)
        ↓
Windows Middleware Service (SDK runs here)
        ↓ HTTP API
Laravel Web App (VPS)
        ↓
MySQL Database
        ↓
Reports / Payroll / Export
```

---

## 1. Departments

- **CRUD:** Admin can add, edit, delete departments.
- **Scope:** Each employee belongs to exactly one department (required).
- Departments are independent of location (or per location – to confirm).

---

## 2. Department shifts & break timings (dynamic)

- Each **department** can have multiple **shifts** (e.g. Day, Night).
- Each **shift** has:
  - **Duty window:** start time, end time (e.g. 8:00–19:00 day, 19:00–8:00 night).
  - **Breaks:** lunch, dinner, tea – each with either:
    - **Fixed time window** (e.g. lunch 12:00–13:30 = 90 min), or
    - **Duration only** (e.g. lunch 60 min, tea 20 min); system infers from punch order.
- **Examples:**
  - **Waterjet:** Day 8am–7pm, Night 7pm–8am; Day: 60 min lunch + 20 min tea; Night: 60 min lunch + 20 min tea.
  - **Cleaning:** 9am–5pm; lunch 12:00–1:30 (90 min).
  - **HEAP:** Day 8:30am–6pm lunch 60 min; Night 8:30pm–5:30am lunch 60 min.
- Admin can create/edit shifts and break rules per department.
- **Employee assignment:** Admin assigns each employee to one department and (optionally) one shift; if not set, use department default or first shift.

---

## 3. Punch sequence (6 punches per day)

- **Standard sequence (machine order):**
  1. **On duty start** (check-in)
  2. **Lunch break start**
  3. **Lunch break end**
  4. **Tea break start**
  5. **Tea break end**
  6. **Off duty** (check-out)
- **Out of order:** If the employee punches in a different order (e.g. forgets tea), the system should:
  - **Option A:** Auto-detect from timestamps (assign punch types by time order and shift rules).
  - **Option B:** Allow admin to manually edit/correct punch type or order for that day.
  - **Option C:** Both – auto-detect first, with admin override.
- **Per day:** Calculate **total working duty time** = (duty end − duty start) − (lunch duration + tea duration + any other breaks). Store per-day totals.

---

## 4. Timing tolerance (late / early)

- Finger scans may not be exactly on shift start/end (e.g. 2 min late, 5 min early).
- System should:
  - **Record** actual punch times (already done).
  - **Allow configurable grace** (e.g. up to 15 min late = not marked late; or mark but no deduction).
  - **Support per-shift or per-department** grace rules if needed.
- “Maintain record” = keep real punch times; use grace only for reporting/deductions.

---

## 5. Open points (for client)

- **Departments:** Global or per location? (e.g. “Waterjet” at Location A vs B.)
- **Dinner:** Only some departments have “dinner” (e.g. night shift). Treat as second meal break (start/end) in shift config?
- **Punch sequence mismatch:** Prefer auto-detect only, admin edit only, or both?
- **Grace:** One global grace (e.g. 15 min) or per shift/department?
- **Existing 4-punch data:** Migrate to 6-punch (lunch start/end, tea start/end) or support both 4 and 6 per department?

---

## 6. Implementation summary

- **Departments:** Global. Admin CRUD at `/departments`.
- **Shifts:** Per department; start/end time, is_night_shift, grace_minutes. CRUD at `/shifts`; breaks (lunch/dinner/tea) with fixed window or duration.
- **Employee:** `department_id`, `shift_id` (optional). Assign in employee create/edit.
- **Punch order:** 1=duty_in, 2=lunch_start, 3=lunch_end, 4=tea_start, 5=tea_end, 6=duty_out. Auto-assigned by time order. Fewer punches supported.
- **Working time:** work_minutes = (duty_out − duty_in) − (lunch_minutes + tea_minutes). Late/overtime from shift or global settings.
- **Setting:** `admin_editable_punch` for future admin override of punch times.

---

## Summary

| Area              | Requirement                                              |
|-------------------|----------------------------------------------------------|
| Departments       | CRUD; every employee has one department                  |
| Shifts            | Per department; multiple shifts; start/end times        |
| Breaks            | Lunch/dinner/tea; fixed window or duration; per shift    |
| Employee          | Assign department + (optional) shift                    |
| Punches           | 6 per day: duty in, lunch in/out, tea in/out, duty out |
| Sequence mismatch | Auto-detect and/or admin-editable punch types/order     |
| Working time      | Total duty minutes per day (excluding breaks)           |
| Late/early        | Record actual times; configurable grace                 |
