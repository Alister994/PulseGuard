# BioTime – Security Best Practices

## Application

1. **Environment**
   - `APP_DEBUG=false` and `APP_ENV=production` in production.
   - Never commit `.env`; keep `APP_KEY` secret and rotated if compromised.

2. **Authentication**
   - Enforce strong passwords (policy or Laravel validation).
   - Use HTTPS only; consider session secure cookie and same_site.
   - Limit login attempts (throttling) – Laravel default throttle applies to login.

3. **Authorization**
   - All sensitive routes behind `auth` middleware.
   - Use role middleware (`super_admin`, `branch_admin`, `hr`, `dept_manager`) and scope data by branch/location so users cannot access other branches.

4. **Device API**
   - Device push endpoint authenticated by device `api_key` (token). Treat `api_key` as secret; rotate if leaked.
   - Support only `X-Device-Key` or `Authorization: Bearer <token>` (and optional body `device_key`); no other bypass.
   - Validate `device_id` in T304F payload against the device that owns the token (already enforced in DeviceSyncController).
   - Rate limit the device sync endpoint per token to avoid abuse.

5. **Input**
   - Validate and sanitize all input; use Laravel validation and avoid raw SQL.
   - Store punch times in DB as datetime; use application logic for duplicate prevention (same device + user + minute).

6. **Data**
   - DB user with minimal privileges (no DROP, no global grants).
   - Encrypt sensitive data at rest if required (e.g. field-level encryption for PII); backups encrypted.

7. **Dependencies**
   - Run `composer update` with care; audit for known vulnerabilities (`composer audit`).

8. **Logging**
   - Log auth failures and device API failures; avoid logging full tokens or passwords.

9. **Headers**
   - Security headers (CSP, X-Frame-Options, etc.) via middleware or server config.

10. **Super Admin**
    - Single or few Super Admin accounts; strong passwords and 2FA if added later.
