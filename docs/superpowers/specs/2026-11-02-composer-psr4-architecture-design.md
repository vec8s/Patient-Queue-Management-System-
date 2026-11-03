 # PQMS Composer PSR-4 Architecture Specification

**Project:** Patient Queue Management System (PQMS)  
**Date:** 2026-11-02  
**Status:** Approved  
**Author:** Senior Software & Web Application Security Engineer  

---

## 1. Objective & Motivation

Transform the native, unstructured PHP codebase of PQMS into a modern, highly secure, industry-standard **Composer-driven PSR-4 application architecture**.

### Core Goals:
1. **Security Isolation (`public/` DocumentRoot):** Prevent direct browser access to sensitive backend files, configuration files (`.env`), SQL dumps, and internal logic scripts.
2. **Standard PSR-4 Autoloading:** Eliminate manual `require_once` statements across files by defining `"PQMS\\": "src/"` in `composer.json`.
3. **Clean Separation of Concerns:**
   - **Controllers:** Handle HTTP requests and responses.
   - **Services:** Execute core business logic (Queue assignment, JWT processing).
   - **Database:** Encapsulate MySQL connection and parameterized query execution.
   - **Middleware:** Enforce server-side authentication and role-based access control (RBAC).
   - **Views/Templates:** Pure presentation components (HTML5/Bootstrap RTL).
4. **Environment Isolation:** Move all hardcoded secrets (`JWT_SECRET`, database credentials) into `.env`.
5. **Clean Data & Git Hygiene:** Remove vendor directory from Git, purge sensitive logs and system artifacts (`.DS_Store`).

---

## 2. Directory Structure Mapping

```text
pqms/
├── .env.example                # Safe environment template
├── .gitignore                  # Git ignore rules for vendor, .env, OS artifacts
├── composer.json               # Composer configuration and PSR-4 mapping
├── README.md                   # Consolidated technical & deployment documentation
│
├── config/                     # Application configurations
│   ├── app.php                 # App metadata, timezones, debug settings
│   └── database.php            # Database connection configuration
│
├── database/                   # Clean Database files
│   ├── schema.sql              # Clean DDL schema (CREATE TABLE statements only)
│   ├── seed.sql                # Anonymized synthetic seed data (no real patients/passwords)
│   └── migration.php           # Automated CLI/web migration script
│
├── public/                     # The ONLY directory accessible to the web server
│   ├── index.php               # Front controller / central entry point & router
│   ├── .htaccess               # Apache rewrite rules & security headers
│   ├── css/                    # Static CSS (style.css)
│   ├── js/                     # Static JS (script.js)
│   └── images/                 # Assets (profile.png, compressed queue image)
│
├── src/                        # Core Application Code (PSR-4 namespace: PQMS\)
│   ├── Config/
│   │   └── Config.php          # Environment loader & configuration wrapper
│   ├── Controllers/
│   │   ├── AuthController.php        # Staff login & logout
│   │   ├── PatientController.php     # Patient registration & lookup
│   │   ├── AppointmentController.php # Appointment booking & retrieval
│   │   ├── QueueController.php       # Check-in, ticket generation & queue calling
│   │   └── AdminController.php       # Clinic metrics & staff listing
│   ├── Database/
│   │   └── Database.php        # Safe PDO / MySQLi singleton with prepared queries
│   ├── Middleware/
│   │   └── AuthMiddleware.php  # Server-side JWT and RBAC guard
│   ├── Services/
│   │   ├── JWTService.php      # Token encoding, decoding, and validation
│   │   └── QueueService.php    # Atomic ticket numbering & queue transitions
│   └── Utils/
│       └── Response.php        # Standard JSON envelope helper (JSend format)
│
├── templates/                  # UI Templates & Views (Separated from business logic)
│   ├── layouts/
│   │   ├── header.php          # HTML head, RTL CSS, alert container
│   │   ├── navbar.php          # Unified navigation bar
│   │   └── footer.php          # Scripts and footer markup
│   └── pages/
│       ├── home.php            # Landing page (formerly index.php)
│       ├── visit.php           # Welcome / path selection
│       ├── registration.php    # Patient registration
│       ├── patient-search.php  # Safe patient lookup
│       ├── checkin.php         # Ticket check-in (appointments & walk-ins)
│       ├── appointments.php    # Pre-scheduled appointments
│       ├── queue.php           # Live waiting room TV display
│       ├── dashboard.php       # Doctor/staff workspace
│       ├── admin.php           # Administration panel
│       └── login.php           # Staff authentication portal
│
└── vendor/                     # Managed exclusively by Composer (ignored in Git)
```

---

## 3. Key Components Specification

### 3.1 `composer.json`
```json
{
    "name": "clinic/pqms",
    "description": "Patient Queue Management System - Modernized PSR-4 Architecture",
    "type": "project",
    "license": "MIT",
    "require": {
        "php": ">=7.4",
        "firebase/php-jwt": "^6.10",
        "vlucas/phpdotenv": "^5.6"
    },
    "autoload": {
        "psr-4": {
            "PQMS\\": "src/"
        }
    }
}
```

### 3.2 Autoloading & Front Controller (`public/index.php`)
- Loads `vendor/autoload.php`.
- Loads environment variables using `Dotenv\Dotenv`.
- Initializes database connection.
- Routes incoming web page requests to templates or API calls to Controllers.
- Enforces HTTP 404 for undefined routes.

### 3.3 Security Middleware (`src/Middleware/AuthMiddleware.php`)
- Verifies JWT token from `Authorization` header or HTTP-only cookie.
- Rejects unauthenticated requests with HTTP 401.
- Validates user roles (`admin`, `doctor`, `nurse`, `receptionist`).
- Protects administrative views (`dashboard.php`, `admin.php`) directly on the server before rendering HTML.

### 3.4 Concurrency-Safe Queue Service (`src/Services/QueueService.php`)
- Uses database transactions with row-locking (`FOR UPDATE`) on daily sequence records to prevent race conditions during simultaneous patient check-ins.
- Generates unique ticket numbers (`WALK-001`, `APPT-001`).

---

## 4. Verification & Testing Strategy
1. **Autoloading Verification:** Ensure `new PQMS\Services\JWTService()` and `new PQMS\Database\Database()` load cleanly without manual `require`.
2. **Access Control Testing:** Direct HTTP GET to `/dashboard.php` and `/admin.php` without valid credentials must return HTTP 401/403 or redirect to `/login.php`.
3. **API Integrity:** All API responses adhere to `{ status, message, data, timestamp }`.
4. **Data Sanitization:** Verify that `database/schema.sql` contains no `INSERT` statements with real patient data.
