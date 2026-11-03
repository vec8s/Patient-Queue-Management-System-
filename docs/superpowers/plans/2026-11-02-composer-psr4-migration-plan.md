# Composer PSR-4 Architecture Migration Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor PQMS from an unstructured native PHP project into a secure, standard Composer-based PSR-4 architecture with a `public/` web root, sanitized data, and server-side RBAC protection.

**Architecture:** 
1. Establish root `composer.json` with PSR-4 autoloading (`"PQMS\\": "src/"`) and standard dependencies.
2. Isolate web-accessible assets and entry points in `public/` protected by `.htaccess`.
3. Separate business logic, database queries, and token management into dedicated classes in `src/`.
4. Provide sanitized `database/schema.sql` and synthetic `database/seed.sql` while cleaning Git artifacts.

**Tech Stack:** PHP (>=7.4 / 8.x), Composer, MySQL/MariaDB (InnoDB, utf8mb4), Firebase PHP-JWT (`firebase/php-jwt`), vlucas/phpdotenv (`vlucas/phpdotenv`), Bootstrap 5.3 RTL.

**Spec:** `docs/superpowers/specs/2026-11-02-composer-psr4-architecture-design.md`

## Global Constraints
- Target workspace: `C:\Users\ComputerWorld\Downloads\Telegram Desktop\pqms\pqms`
- All PHP classes must use strict typing and reside within the `PQMS` namespace root.
- Do not lose or break existing Arabic UI functionality, templates, or styles.
- Zero sensitive patient/doctor data in database dumps or source code.
- Prevent any direct web access to `.env`, `.git`, or PHP source files outside `public/`.

---

### Task 1: Environment, Git & Composer Initialization

**Files:**
- Create: `composer.json`
- Create: `.env.example`
- Create: `.gitignore`
- Remove: `.DS_Store`, `vendor/.DS_Store`

- [ ] **Step 1: Create `composer.json` at project root**
  Write valid composer configuration with `firebase/php-jwt`, `vlucas/phpdotenv`, and PSR-4 autoloading map pointing `PQMS\` to `src/`.

- [ ] **Step 2: Create `.env.example` and default `.env`**
  Define database configuration keys (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`) and `JWT_SECRET` with strong fallback defaults.

- [ ] **Step 3: Create `.gitignore` and purge OS debris**
  Add `/vendor/`, `.env`, `*.log`, `.DS_Store`, and `Thumbs.db` to `.gitignore`. Delete all `.DS_Store` files.

---

### Task 2: Data Sanitization & Database Modernization

**Files:**
- Create: `database/schema.sql`
- Create: `database/seed.sql`
- Modify: `php/migration.php` -> `database/migration.php`

- [ ] **Step 1: Extract pure DDL schema into `database/schema.sql`**
  Extract all `CREATE TABLE` and `ALTER TABLE` statements from `db/db_pqms.sql` without any `INSERT INTO` statements. Fix `address` column length to `VARCHAR(255)` and add missing composite indexes.

- [ ] **Step 2: Create clean synthetic dataset in `database/seed.sql`**
  Create realistic dummy patients (non-existent names/numbers) and default staff accounts (`admin@clinic.local`, `doctor@clinic.local`) with securely hashed passwords.

- [ ] **Step 3: Update `database/migration.php`**
  Remove hardcoded personal accounts (`mvumapatrick@gmail.com`). Integrate with the new Database connection class.

---

### Task 3: Core Database & Configuration Classes (`src/`)

**Files:**
- Create: `src/Config/Config.php`
- Create: `src/Database/Database.php`
- Create: `src/Utils/Response.php`

- [ ] **Step 1: Implement `src/Config/Config.php`**
  Wrap `.env` loading and provide safe accessors `Config::get($key, $default)`.

- [ ] **Step 2: Implement `src/Database/Database.php`**
  Singleton MySQLi/PDO connection helper with transactional support (`beginTransaction()`, `commit()`, `rollback()`) and prepared statement execution.

- [ ] **Step 3: Implement `src/Utils/Response.php`**
  Provide standardized JSend helper methods: `Response::json($status, $data, $message, $code)`.

---

### Task 4: Security & Business Services (`src/Services/` & `src/Middleware/`)

**Files:**
- Create: `src/Services/JWTService.php`
- Create: `src/Services/QueueService.php`
- Create: `src/Middleware/AuthMiddleware.php`

- [ ] **Step 1: Implement `src/Services/JWTService.php`**
  OOP wrapper around `Firebase\JWT\JWT` supporting token encoding, decoding, validation, and payload extraction using dynamic secrets from `Config`.

- [ ] **Step 2: Implement `src/Services/QueueService.php`**
  Encapsulate ticket issuance (`WALK-xxx`, `APPT-xxx`) using concurrency-safe queries and queue transition handling (waiting -> in_progress -> completed).

- [ ] **Step 3: Implement `src/Middleware/AuthMiddleware.php`**
  Server-side authentication and role verification for protected pages and API endpoints.

---

### Task 5: Controllers & Modern API Layer (`src/Controllers/`)

**Files:**
- Create: `src/Controllers/AuthController.php`
- Create: `src/Controllers/PatientController.php`
- Create: `src/Controllers/AppointmentController.php`
- Create: `src/Controllers/QueueController.php`

- [ ] **Step 1: Implement `AuthController.php`**
  Staff login with password verification and token generation. Block unauthenticated registration of admin users.

- [ ] **Step 2: Implement `PatientController.php`**
  Safe patient registration and query-parameterized lookup (preventing data leaks to unauthenticated callers).

- [ ] **Step 3: Implement `AppointmentController.php`**
  Advance appointment scheduling and lookup.

- [ ] **Step 4: Implement `QueueController.php`**
  Live queue status for display and doctor workspace actions.

---

### Task 6: Public Web Root & Template Organization

**Files:**
- Create: `public/index.php`
- Create: `public/.htaccess`
- Move & Organize: `css/`, `js/`, `images/` into `public/`
- Move & Clean: View files into `templates/pages/` and `templates/layouts/`

- [ ] **Step 1: Scaffold `public/index.php` Front Controller & Router**
  Direct requests to corresponding controllers and template views while maintaining backward compatibility for URLs.

- [ ] **Step 2: Configure `public/.htaccess`**
  Protect sensitive files and rewrite clean routes through `index.php`.

- [ ] **Step 3: Move static assets into `public/`**
  Relocate CSS, JS, and images into `public/` and delete empty/redundant files like `js/main.js`.

- [ ] **Step 4: Move UI views into `templates/` and update includes**
  Organize headers, footers, and view templates to use PSR-4 classes and clean layout rendering.

---

### Task 7: Verification & Cleanup

- [ ] **Step 1: Run Composer validation and dump autoload**
  Verify `composer.json` syntax and generate standard autoloader.

- [ ] **Step 2: Test API and page rendering**
  Ensure public pages (visit, checkin, queue) and protected pages (dashboard, admin) respond with correct status codes and headers.

- [ ] **Step 3: Consolidate documentation into `README.md`**
  Merge `PROJECT_README.md` and `README.md` with clear installation and architecture guides.

