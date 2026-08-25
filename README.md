# QuickMart IOMS

QuickMart IOMS is a server-rendered inventory and order management system for retail operations. It combines product and stock control, sell and purchase orders, staff roles, and Admin-only reporting in a PHP and MariaDB application.

The interface is built around the QuickMart Operations Ledger direction: a calm, RTL-first operations console with Arabic-friendly typography, clear stock states, and responsive layouts.

## Current capabilities

- Product catalog and inventory tracking with normal, low-stock, and out-of-stock states.
- Sell orders with transactional stock deduction and insufficient-stock protection.
- Purchase orders with transactional stock replenishment.
- Historical order details using the price stored at the time of the order.
- Role-based access for `Admin`, `Manager`, and `Staff`.
- Admin-only staff management and reporting.
- Authenticated user profile and password management.
- Dashboard inventory health, recent orders, order details, and report summaries.
- CSRF protection, password hashing, prepared SQL statements, foreign-key integrity, and session revalidation.

Admin-only staff creation is available through the Staff Create API. The staff-management contract also supports listing, viewing, updating, deleting, profile updates, and password changes.

## Technology stack

- PHP 8.0+
- MariaDB 10.4+ with InnoDB transactions
- PDO for database access
- Vanilla JavaScript and CSS
- Apache through XAMPP or an equivalent PHP web-server setup
- Bootstrap-compatible markup and existing CDN assets on legacy views; no frontend build step is required

## Project structure

```text
QuickMart-IOMS-main/
├── assets/
│   ├── common.css              # Shared design tokens and application shell
│   ├── common.js               # Shared frontend utilities and apiFetch
│   ├── dashboard/              # Dashboard styles and behavior
│   ├── products/               # Product page styles and behavior
│   ├── orders/                 # Order list and creation behavior
│   ├── create-order/           # Order creation assets
│   ├── order-details/          # Order detail assets
│   ├── reports/                # Reporting assets
│   ├── staff/                  # Staff management assets
│   ├── profile/                # Profile assets
│   └── login-signup/           # Login assets
├── backend/
│   ├── api/                    # JSON API endpoints
│   ├── application/            # Application services
│   ├── core/                   # Configuration, database, auth, and helpers
│   ├── infrastructure/        # Repository implementations
│   └── views/                  # Protected PHP views and shared shell partials
├── database/
│   ├── schema.sql              # Current baseline schema
│   ├── data.sql                # Optional disposable demo data
│   └── migrations/             # Incremental migrations 001 through 006
├── .env.example               # Environment variable names only
├── index.php                  # Application entry point
└── README.md
```

## Local setup with XAMPP

### Prerequisites

- XAMPP or an equivalent Apache/PHP/MariaDB environment.
- PHP 8.0 or newer.
- MariaDB 10.4 or newer.
- A modern browser.

### 1. Clone the repository

```bash
git clone https://github.com/mohammad-emad-dev/QuickMart-IOMS.git
```

For the default Windows XAMPP layout, place the project at:

```text
C:\xampp\htdocs\QuickMart-IOMS-main
```

### 2. Configure the database connection

The application does not read a `.env` file. Configure these variables through Apache `SetEnv`, Windows environment variables, or the PHP runtime. Use `.env.example` as the variable-name reference:

```text
QUICKMART_DB_HOST
QUICKMART_DB_NAME
QUICKMART_DB_USER
QUICKMART_DB_PASSWORD
QUICKMART_ALLOWED_ORIGIN
QUICKMART_RATE_LIMIT_SECRET
```

`QUICKMART_RATE_LIMIT_SECRET` is optional for local development and should be a high-entropy deployment secret in production. It protects the one-way login limiter keys from offline correlation. Never commit a real password or other secret. `QUICKMART_ALLOWED_ORIGIN` may remain empty for a same-origin local deployment.

### 3. Create the database

Import the current baseline schema into a disposable local database:

```sql
SOURCE /path/to/QuickMart-IOMS-main/database/schema.sql;
```

Optional demo data can be imported afterward:

```sql
SOURCE /path/to/QuickMart-IOMS-main/database/data.sql;
```

The demo data is for local testing only. Rotate or replace seeded credentials before using the application in any shared environment.

For an existing database that predates the current baseline, review and apply migrations `001` through `006` in order using the project’s migration process. Migration `006` creates the persistent login-attempt limiter table. Do not apply migrations blindly to a production database.

### 4. Backup and restore

Create a logical backup before applying migrations or deploying. Use a least-privilege database account for shared or production environments and keep the dump outside the repository:

```powershell
mysqldump -u <database_user> -p quickmart_db > quickmart_db_backup.sql
```

Restore only to a verified target, preferably a disposable database first:

```powershell
mysql -u <database_user> -p quickmart_db < quickmart_db_backup.sql
```

Pause writes during a production restore, verify foreign keys and row counts afterward, and never commit backup files or database credentials. The tracked `.htaccess` database fallback is for local XAMPP review only; production deployments must provide the `QUICKMART_*` values through server environment configuration.

### 5. Start the application

Start Apache and MariaDB from the XAMPP Control Panel, then open:

```text
http://localhost/QuickMart-IOMS-main/
```

Useful direct review pages include:

```text
http://localhost/QuickMart-IOMS-main/assets/login-signup/login.html
http://localhost/QuickMart-IOMS-main/backend/views/dashboard.php
```

Default account credentials are intentionally not documented. Use disposable local seed data or provision accounts through an approved administrative process.

For the complete staging and production handoff runbook, see [docs/PRODUCTION-HANDOFF.md](docs/PRODUCTION-HANDOFF.md).

## Roles and access

The database accepts exactly these roles:

| Role | Access summary |
| --- | --- |
| `Admin` | Full administrative access, staff management, all orders, and reports. |
| `Manager` | Authenticated operational access permitted by each endpoint; no Admin-only staff or report access. |
| `Staff` | Authenticated operational access permitted by each endpoint, including own profile/password and owned orders; no Admin-only staff or report access. |

The backend is authoritative for authorization. Client-side role hints are used only to shape the interface and never grant access.

## API surface

All API responses use the project’s JSON response helpers and the existing `{ "success": true, "data": ... }` success shape where applicable.

### Authentication

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `POST` | `backend/api/auth/login.php` | Authenticate a staff member. |
| `POST` | `backend/api/auth/logout.php` | End the current session. |
| `GET` | `backend/api/auth/csrf.php` | Return the authenticated session CSRF token. |
| `GET` | `backend/api/auth/session.php` | Revalidate the server-side session and return safe UI identity data. |
| `POST` | `backend/api/auth/forgot_password.php` | Intentionally disabled with HTTP 503 until secure delivery is available. |

### Products

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `backend/api/products/list.php` | List products. |
| `GET` | `backend/api/products/get.php?id=...` | Read one product. |
| `POST` | `backend/api/products/create.php` | Create a product where authorized. |
| `PUT` | `backend/api/products/update.php` | Update a product where authorized. |
| `DELETE` | `backend/api/products/delete.php` | Delete a product where authorized. |

### Orders

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `backend/api/orders/list.php` | List authorized orders with existing filters. |
| `GET` | `backend/api/orders/get.php?id=...` | Read an authorized order and its stored detail prices. |
| `POST` | `backend/api/orders/create.php` | Create a sell or purchase order transactionally. |

### Staff

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `backend/api/staff/list.php` | Admin-only staff list. |
| `GET` | `backend/api/staff/get.php?id=...` | Read a permitted staff record. |
| `POST` | `backend/api/staff/create.php` | Admin-only creation of a staff account with an initial password. |
| `POST` | `backend/api/staff/update.php` | Admin-only staff update. |
| `POST` | `backend/api/staff/delete.php` | Admin-only staff deletion with order-history protection. |
| `POST` | `backend/api/staff/update-profile.php` | Update the authenticated user’s profile. |
| `POST` | `backend/api/staff/change-password.php` | Change the authenticated user’s password. |

### Reports

| Method | Endpoint | Purpose |
| --- | --- | --- |
| `GET` | `backend/api/reports/stats.php` | Admin-only dashboard and reporting metrics. |

## Database and migration notes

The current schema includes:

- `Staff` with canonical roles and `Auth_Revision` for future session invalidation.
- `Products` with stock, price, threshold, and status constraints.
- `Orders` and `Order_Details` with restricted history-preserving foreign keys.
- `Login_Rate_Limits` with bounded, one-way identifier/client buckets for concurrent login throttling.
- `Password_Reset_Tokens` storing only case-sensitive SHA-256 token hashes, with expiry, use, revocation, and cleanup indexes.

The public password-reset request remains disabled because no secure email or token-delivery provider is configured. The internal token lifecycle must not be treated as a user-facing recovery flow until delivery and global session invalidation requirements are completed. Login attempts are limited to five valid-shaped attempts per identifier/client bucket in a 15-minute window; the sixth receives HTTP 429 with `Retry-After`, and a successful login clears the bucket.

## Security notes

- Passwords are stored as password hashes and are never returned by the APIs.
- Reset-token records contain hashes only; raw tokens, reset URLs, and credentials are not persisted or logged.
- PDO prepared statements and repository boundaries protect database operations from SQL injection.
- CSRF validation protects authenticated mutations.
- Session cookies use the project’s secure session settings, and protected requests revalidate the current staff record and role.
- `Auth_Revision` invalidates stale sessions after password-sensitive changes without deleting arbitrary PHP session files.
- The authenticated session probe is server-authoritative; sessionStorage is only a UI cache and never grants access.
- HTML authorization failures use a styled shared page, while API authorization failures remain JSON.
- Login rate-limit keys use normalized identifiers plus `REMOTE_ADDR`; forwarded proxy headers are not trusted by default.
- Orders use transactions and row locking for stock changes.
- Foreign keys preserve order history and protect referenced products and staff records.

## Dependency management decision

Composer is intentionally not used in the current architecture. The application uses native PHP, PDO, and the existing server-side files with no Composer package or autoload requirement. A misleading empty `composer.json` would not improve deployment; add one only when an approved runtime dependency and matching autoload convention are introduced.

## Development checks

Run PHP lint with the project PHP executable:

```powershell
C:\xampp\php\php.exe -l backend\views\dashboard.php
```

Run JavaScript syntax checks with Node.js:

```powershell
node --check assets\dashboard\dashboard.js
```

For broader changes, lint all affected PHP and JavaScript files and perform browser smoke testing against an isolated database. Do not use the live runtime database for destructive verification.

## License

No license file is currently included in the repository. Confirm licensing before redistributing or deploying the project outside its intended environment.
