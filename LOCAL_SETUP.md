# Resumelyzer — Local Setup Guide

## Requirements

| Dependency | Version |
|------------|---------|
| PHP        | ^8.2    |
| Composer   | ^2.x    |
| Node.js    | ^18.x   |
| NPM        | ^9.x    |
| MySQL      | ^8.0    |

---

## 1. Clone the Repository

```bash
git clone git@github.com:ChawSuNway/Resumelyzer.git
cd resumelyzer
```

---

## 2. Install PHP Dependencies

```bash
composer install
```

---

## 3. Install Node Dependencies

```bash
npm install
```

---

## 4. Environment Configuration

Copy the example environment file and edit it:

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure the following sections:

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resumelyzer_db
DB_USERNAME=root
DB_PASSWORD=
```

Create the MySQL database before migrating:

```sql
CREATE DATABASE resumelyzer_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Google Gemini API

Obtain an API key from [Google AI Studio](https://aistudio.google.com/) and set:

```env
GEMINI_API_KEY=your-api-key-here
GEMINI_MODEL=gemini-2.5-flash
GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta
```

### Resume Storage

```env
RESUME_DISK=local
RESUME_RETENTION_DAYS=30
```

---

## 5. Run Migrations

```bash
php artisan migrate
```

To also seed demo data (if seeders are available):

```bash
php artisan migrate --seed
```

---

## 6. Storage Link

```bash
php artisan storage:link
```

---

## 7. Build Frontend Assets

For development (watch mode with HMR):

```bash
npm run dev
```

For production:

```bash
npm run build
```

---

## 8. Start All Services (Recommended)

The project ships with a `composer dev` script that starts the HTTP server,
queue worker, log watcher, and Vite in one terminal using `concurrently`:

```bash
composer dev
```

This runs:

| Process        | Command                              |
|----------------|--------------------------------------|
| HTTP server    | `php artisan serve`                  |
| Queue worker   | `php artisan queue:listen --tries=1` |
| Log watcher    | `php artisan pail --timeout=0`       |
| Vite dev server| `npm run dev`                        |

The application will be available at **http://localhost:8000**.

---

## 9. Manually Running Each Service

If you prefer separate terminals:

```bash
# Terminal 1 — HTTP server
php artisan serve

# Terminal 2 — Queue worker (required for AI analysis jobs)
php artisan queue:listen --tries=1

# Terminal 3 — Vite (frontend hot-reload)
npm run dev
```

---

## 10. Default User Roles

Resumelyzer supports three roles. Create users via the registration page or
with Tinker:

```bash
php artisan tinker
```

```php
// Create an admin
\App\Models\User::create([
    'name'     => 'Admin User',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
    'role'     => 'admin',
]);
```

| Role      | Access                                          |
|-----------|-------------------------------------------------|
| candidate | Upload resumes, view analysis, export reports   |
| recruiter | Browse candidate profiles, manage job postings  |
| admin     | User management, system settings                |

---

## 11. Running Tests

```bash
php artisan test
```

Or with coverage:

```bash
php artisan test --coverage
```

---

## Key Directories

```
app/Http/Controllers/Candidate/   — Resume upload, analysis, export
app/Http/Controllers/Recruiter/   — Candidate browsing, job postings
app/Http/Controllers/Admin/       — User management, settings
app/Models/                        — Eloquent models
app/Services/GeminiClient.php      — Google Gemini API integration
resources/views/exports/           — PDF/Word export blade templates
database/migrations/               — All database schemas
```

---

## Troubleshooting

**Queue jobs not processing**
Make sure the queue worker is running (`php artisan queue:listen`).
Analysis jobs are dispatched asynchronously and require the worker.

**PDF exports blank / error**
DomPDF requires the GD or Imagick PHP extension. Verify with:
```bash
php -m | grep -E "gd|imagick"
```

**Gemini API 403 / quota error**
Double-check `GEMINI_API_KEY` in `.env` and ensure the key has access to
the `gemini-2.5-flash` model in your Google AI Studio project.

**Vite assets not loading**
Run `npm run dev` (development) or `npm run build` (production) and
ensure `APP_URL` in `.env` matches the URL you are accessing.
