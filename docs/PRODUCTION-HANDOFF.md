# QuickMart IOMS Production Handoff

Status: handoff-ready with deployment-owner caveats
Prepared: 2026-08-25
Application: QuickMart IOMS
Runtime baseline verified locally: PHP 8.0.30, MariaDB 10.4.32, Apache 2.4.58 on XAMPP

This document prepares the repository for a controlled staging and production handoff. No external deployment, cloud resource, email delivery, or credential change was performed.

## 1. Release boundary

The application is a PHP/MariaDB server-rendered system. It has no frontend build step, worker process, queue, external payment integration, or public password-reset delivery provider.

The deployment owner supplies all production secrets and infrastructure values. Do not copy local XAMPP credentials, demo data, browser sessions, or `.htaccess` values into production.

## 2. Prerequisites

- Apache 2.4 or an equivalent PHP-capable web server, with `mod_headers` available for deployment security headers. `mod_env` is needed only by the local `.htaccess` fallback and should not be the production secret-injection mechanism.
- PHP 8.0 or newer with `PDO`, `pdo_mysql`, `session`, `json`, `mbstring`, and `openssl` enabled.
- MariaDB 10.4 or newer with InnoDB and `utf8mb4` support.
- TLS/HTTPS with a valid certificate for every production origin.
- A secret-management mechanism for database credentials and approved operational accounts.
- A separate staging database and staging hostname before production cutover.
- A current backup, restore destination, and an owner for recovery decisions.

The current views load Bootstrap and, on the Create Order page, Google Fonts from CDNs. A restricted network must either permit those exact origins with an appropriate CSP or replace them with reviewed, version-pinned local assets before deployment.

## 3. Environment handoff

The application reads process/server environment variables; it does not load `.env` files.

| Variable | Required | Production guidance |
| --- | --- | --- |
| `QUICKMART_DB_HOST` | Yes | Use the private database host or `127.0.0.1`; do not use an unrestricted public address. |
| `QUICKMART_DB_NAME` | Yes | Use the production database name supplied by the owner. |
| `QUICKMART_DB_USER` | Yes | Use a dedicated least-privilege runtime account; never `root`. |
| `QUICKMART_DB_PASSWORD` | Yes | Non-empty secret supplied through the deployment secret store; never commit it. |
| `QUICKMART_ALLOWED_ORIGIN` | No | Leave empty for same-origin deployment. If cross-origin access is explicitly required, set exactly one trusted origin; never use `*`. |
| `QUICKMART_RATE_LIMIT_SECRET` | Recommended | High-entropy secret used to HMAC login limiter keys. It is optional for local development but should be supplied through the production secret store. |

The application fails closed with a generic configuration error when a required value is missing. Verify the production process actually exposes the variables to PHP, not only to an unrelated shell.

The tracked `.htaccess` contains a root/blank-password fallback for local XAMPP review only. Do not use it as production configuration. Production must inject the values through the Apache/PHP service environment and must use a non-root account with a non-empty password.

## 4. Apache and PHP configuration

Point the production virtual host or document root at the project directory, or provide an equivalent path mapping that preserves the existing `/assets`, `/backend/api`, and `/backend/views` URLs. Disable directory listing and keep writable operational files outside the web root.

At minimum, the production server configuration must:

1. Enforce HTTPS and preserve the HTTPS signal to PHP so the application sets secure session cookies.
2. Set `display_errors=Off`, `log_errors=On`, and send PHP error logs to a protected, rotated location outside the document root.
3. Deny direct web access to `.git`, `.env*`, `database`, `docs`, backup files, `README.md`, and `STATUS.md`.
4. Disable directory indexes.
5. Add HSTS only after HTTPS is confirmed for every intended subdomain.
6. Add a reviewed CSP that matches the current Bootstrap/Google Fonts CDN dependencies, or self-host those assets first.
7. Keep application source and configuration read-only to the web user; grant write access only to a protected log directory if required.

Example Apache hardening shape for an owner-managed virtual host; replace paths and hostnames before use:

```apache
DocumentRoot "C:/srv/quickmart-ioms"

<Directory "C:/srv/quickmart-ioms">
    Options -Indexes
    AllowOverride None
    Require all granted
</Directory>

<Directory "C:/srv/quickmart-ioms/database">
    Require all denied
</Directory>

<Directory "C:/srv/quickmart-ioms/docs">
    Require all denied
</Directory>

<Directory "C:/srv/quickmart-ioms/.git">
    Require all denied
</Directory>

<FilesMatch "^(?:\.env.*|README\.md|STATUS\.md|.*\.(?:sql|bak|backup|log))$">
    Require all denied
</FilesMatch>
```

The application currently emits `X-Content-Type-Options`, `X-Frame-Options`, and `Referrer-Policy`. The deployment layer remains responsible for HTTPS redirect, HSTS, CSP, log policy, and any WAF/rate limiting required for public exposure.

## 5. Database creation and migrations

### New database

Use a migration/admin account or approved DBA process to create the database and import the current baseline:

```sql
CREATE DATABASE quickmart_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
```

Then import `database/schema.sql`. The current baseline already contains the latest Staff, Login_Rate_Limits, Password_Reset_Tokens, Products, Orders, and Order_Details constraints. Import `database/data.sql` only into disposable local or staging environments; never use its demo records as production data.

### Existing pre-baseline database

1. Take and verify a backup.
2. Confirm the target is an older compatible schema, not the current `schema.sql` baseline.
3. Validate existing roles, nonblank Staff names/emails, order types, positive detail quantities/prices, and foreign-key names/data before DDL.
4. Apply the migrations exactly once and in this order:

   1. `database/migrations/001_products_inventory_foundation.sql`
   2. `database/migrations/002_order_management_database_foundation.sql`
   3. `database/migrations/003_staff_roles_database_foundation.sql`
   4. `database/migrations/004_password_reset_tokens_foundation.sql`
   5. `database/migrations/005_authentication_revision_foundation.sql`
   6. `database/migrations/006_login_rate_limits_foundation.sql`

5. Verify constraints, indexes, foreign keys, row counts, and application smoke checks.

These files are manual DDL migrations, not an automated migration runner. Do not rerun them against the current baseline because their constraints/tables already exist. Migrations are forward-only for production. The commented DOWN plans are recovery guidance, not a safe general rollback; migration 002 intentionally restores weaker cascading behavior if its DOWN plan is used. Migration 006 stores only bounded one-way limiter keys and has a documented `DROP TABLE` DOWN plan for a controlled rollback after traffic is stopped.

## 6. Least-privilege runtime account

Create the runtime account separately from the migration/DBA account. Use a secret manager or an interactive prompt for the password; never place a real password in this document or a command history:

```sql
CREATE USER 'quickmart_app'@'127.0.0.1'
    IDENTIFIED BY '<secret-from-deployment-secret-store>';

GRANT SELECT, INSERT, UPDATE, DELETE
    ON quickmart_db.*
    TO 'quickmart_app'@'127.0.0.1';
```

Do not grant `ALL PRIVILEGES`, `CREATE`, `ALTER`, `DROP`, `FILE`, `PROCESS`, or `GRANT OPTION` to the runtime account. Use a separate short-lived migration account for schema changes. If the database is remote, restrict the account host and require database TLS according to the MariaDB deployment policy.

Set `QUICKMART_DB_USER` and `QUICKMART_DB_PASSWORD` to this runtime account only after verifying the connection from PHP.

## 7. Backup, restore, and recovery

### Backup

Run from a protected operations host, with the password requested interactively or provided by the secret manager:

```powershell
mysqldump --single-transaction --routines --triggers --hex-blob `
  --default-character-set=utf8mb4 `
  -u <backup_user> -p quickmart_db > quickmart_db_YYYYMMDD_HHMMSS.sql
```

Store the dump outside the web root, encrypt it at rest, restrict access, record its checksum, and retain it according to the owner-approved recovery policy. Never commit dumps to Git.

### Restore dry run

Restore each scheduled backup into a disposable database, not `quickmart_db`:

```powershell
mysql -u <restore_user> -p -e "CREATE DATABASE quickmart_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
mysql -u <restore_user> -p quickmart_restore_test < quickmart_db_YYYYMMDD_HHMMSS.sql
```

Verify table engines, expected row counts, foreign-key integrity, login, a read-only product/order/report request, and the application smoke checklist. Drop only the disposable restore database after evidence is recorded.

### Production recovery

1. Declare the incident and pause writes or place the application in maintenance mode.
2. Preserve the failing database and logs; do not overwrite evidence.
3. Take a final emergency backup if the database is readable.
4. Restore to a new database name and validate it before cutover.
5. Point `QUICKMART_DB_NAME` to the validated database through the deployment secret/configuration system.
6. Restart PHP/Apache workers, run the smoke checklist, and compare critical row counts and recent order totals.
7. Keep the previous database available until the owner accepts the recovery.

For an application-only rollback, redeploy the previous reviewed artifact/commit first. Do not edit deployed migration files or run a destructive DOWN plan as a first response. Use a backup restore or a new compensating forward migration after DBA review.

## 8. Staging and smoke-test checklist

Run this checklist on staging before production and again after cutover:

- [ ] Login page loads over HTTPS and static assets resolve.
- [ ] Missing configuration fails closed with no stack trace or secret output.
- [ ] Anonymous protected API access returns 401 and protected views redirect safely.
- [ ] Admin login, CSRF issuance, product read/write, order sell/purchase, staff management, and reports work.
- [ ] Manager and Staff can read only their permitted data and receive 403 for Admin-only endpoints.
- [ ] Forgot-password endpoint remains intentionally disabled with HTTP 503.
- [ ] Passwords, hashes, reset tokens, and session internals are absent from responses and logs.
- [ ] Product/order/staff mutation rollback is confirmed on validation, stock, and foreign-key failures.
- [ ] Order details show stored prices and backend totals; invoice print/PDF is readable.
- [ ] CSV export safely handles commas, quotes, and newlines.
- [ ] Security headers, exact CORS behavior, `HttpOnly`, `SameSite=Strict`, and HTTPS-secure cookies are present.
- [ ] PHP/Apache logs are writable only where intended, rotated, monitored, and free of credentials.
- [ ] Backup restore dry run and row-count comparison are recorded.
- [ ] No demo data, temporary accounts, test databases, or test sessions remain.

There is no dedicated health endpoint in the current architecture. Use the login page for liveness and the authenticated API smoke checks above for readiness until an approved health endpoint is added in a separate phase.

## 9. Production security checklist

- [ ] Production credentials are supplied by the deployment owner through a secret manager.
- [ ] The runtime database account is not root and has no DDL/admin privileges.
- [ ] HTTPS is enforced and the correct HTTPS signal reaches PHP.
- [ ] The local `.htaccess` fallback is not used.
- [ ] `.git`, `.env*`, SQL dumps, logs, docs, and backups are inaccessible from HTTP.
- [ ] `display_errors` is off; generic errors are returned to clients.
- [ ] CORS is same-origin or one exact trusted origin; wildcard CORS is not used.
- [ ] CSRF and server-side role/ownership checks are enabled.
- [ ] Application login rate limiting is verified, `QUICKMART_RATE_LIMIT_SECRET` is supplied in production, and deployment-edge monitoring/alerting is configured after validating current CDN requirements.
- [ ] Demo credentials/data are removed or replaced before production.
- [ ] Backups are encrypted, access-controlled, tested, and retained.
- [ ] The fixed application timezone (`Asia/Riyadh`) is confirmed with the business owner.
- [ ] Public password reset remains disabled until a secure delivery provider and complete recovery/session-invalidation design are approved.

## 10. Known limitations

- No external HTTPS/domain deployment was available for this handoff.
- Public password-reset delivery is intentionally unavailable and remains HTTP 503.
- The repository contains a local XAMPP `.htaccess` database fallback; it is documented as local-only and must not be used in production.
- There is no automated migration runner or dedicated health endpoint.
- Current pages depend on reviewed external Bootstrap/Google Fonts CDN assets unless the deployment owner self-hosts them.
- Login rate limiting is implemented in the database-backed application path; edge-wide abuse monitoring and alerting still belong at the deployment boundary.
- Composer is intentionally not part of the release because the current application has no Composer dependencies or autoloading.

## 11. Password-reset activation gates

The public forgot-password endpoint is intentionally disabled by default and returns HTTP 503 without reading or enumerating the submitted identifier. Do not enable it by changing a flag alone. An approved implementation must provide all of the following before activation:

1. A reviewed email/SMS delivery adapter and provider account.
2. Secret-manager-backed provider credentials and an HTTPS-only reset URL.
3. Request throttling and generic known/unknown-account responses.
4. Audit-safe delivery/error telemetry with no raw token logging.
5. Hashed, expiring, single-use, revocable token storage and cleanup.
6. Auth_Revision/session invalidation behavior after successful reset.
7. Staging tests for replay, expiry, revocation, delivery failure, and account deletion.

## 12. Handoff decision

The repository is ready for staging handoff with the caveats above. Production deployment remains the deployment owner’s responsibility and must not begin until environment injection, least-privilege database access, HTTPS, protected document-root rules, backup verification, and the staging smoke checklist are complete.
