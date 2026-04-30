# Resumelyzer — Smart Resume Analyzer

AI-powered resume analysis built with **Laravel 11**, **Blade**, **MySQL**, and **Google Gemini 2.5 Flash**.

---

## Requirements

| Tool | Version |
|------|---------|
| PHP | ≥ 8.2 |
| Composer | 2.x |
| Node.js | ≥ 18 |
| MySQL | 8.0 *(or SQLite for local dev)* |

> A **Google Gemini API key** is required for AI analysis — get one free at https://aistudio.google.com/apikey

---

## Step-by-Step Setup

### 1 — Clone / open the project

```bash
cd d:/AI_Project/Resumelyzer
```

---

### 2 — Install PHP dependencies

```bash
composer install
```

---

### 3 — Install Node dependencies and compile assets

```bash
npm install
npm run build
```

For hot-reload during development:

```bash
npm run dev
```

---

### 4 — Configure the environment file

```bash
cp .env.example .env
```

Open `.env` and set the following:

#### App URL

```env
APP_NAME=Resumelyzer
APP_URL=http://localhost:8000
```

#### Database — MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=resumelyzer
DB_USERNAME=root
DB_PASSWORD=your_password
```

> **SQLite (easier for local dev):** Keep `DB_CONNECTION=sqlite`. The file `database/database.sqlite` is already present — no extra setup needed.

#### Google Gemini API (required)

```env
GEMINI_API_KEY=your_key_here
GEMINI_MODEL=gemini-2.5-flash
```

---

### 5 — Generate the application key

```bash
php artisan key:generate
```

---

### 6 — Create the MySQL database (skip if using SQLite)

```sql
CREATE DATABASE resumelyzer
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

---

### 7 — Run migrations and seed demo accounts

```bash
php artisan migrate --seed
```

Three demo accounts are created:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@resumelyzer.test | password |
| Recruiter | recruiter@resumelyzer.test | password |
| Candidate | candidate@resumelyzer.test | password |

---

### 8 — Create the storage symlink

```bash
php artisan storage:link
```

---

### 9 — Start the development server

```bash
php artisan serve
```

Open http://localhost:8000

---

## What Each Role Can Do

### Candidate
- Upload resumes in PDF, DOCX, or TXT format (up to 10 MB)
- Receive AI scores: Overall, ATS Compatibility, Readability, Skills Match, Professionalism
- Section-by-section breakdown: contact info, education, experience, skills, certifications
- Keyword match visualisation against a selected job posting
- Actionable suggestions: action verbs, quantifiable metrics, section reordering
- Control recruiter visibility and data retention
- Export analysis as PDF or CSV
- Download an improved resume draft PDF

### Recruiter
- Search and filter shared candidate resumes by keyword or minimum score
- Compare a resume against a job posting for AI fit score
- Add private notes and star ratings (1-5) to candidate profiles
- Create and manage job postings with keyword libraries
- Download candidate resume files and export analysis reports

### Admin
- Full user management: create, edit, activate/deactivate, assign roles
- Configure scoring weights, mandatory resume sections, generic-terms blocklist, ATS keyword library
- Set global data retention policy
- View system analytics: upload trends, average scores, activity log

---

## Environment Reference

| Key | Default | Purpose |
|-----|---------|---------|
| GEMINI_API_KEY | (empty) | Required. Google AI Studio key |
| GEMINI_MODEL | gemini-2.5-flash | Gemini model ID |
| GEMINI_TIMEOUT | 60 | API timeout in seconds |
| RESUME_DISK | local | Laravel storage disk |
| RESUME_MAX_SIZE_KB | 10240 | Max upload size (10 MB) |
| RESUME_RETENTION_DAYS | 30 | Days before auto-purge |
| DB_CONNECTION | sqlite | sqlite or mysql |

---

## Project Structure

```
app/
  Http/Controllers/
    Admin/       DashboardController, UserController, SettingsController
    Candidate/   DashboardController, ResumeController, PrivacyController, ExportController
    Recruiter/   DashboardController, CandidateController, JobPostingController
  Http/Middleware/
    EnsureUserHasRole.php
  Models/
    User, Resume, ResumeAnalysis, JobPosting, RecruiterNote, SystemSetting, ActivityLog
  Services/
    GeminiClient.php              HTTP wrapper for Gemini generateContent API
    ResumeAnalysisService.php     orchestrates the AI analysis pipeline
    ResumeTextExtractor.php       PDF / DOCX / TXT to plain text

database/migrations/
resources/views/
  admin/       dashboard, users, settings
  candidate/   dashboard, resumes, privacy
  recruiter/   dashboard, candidates, jobs
  exports/     PDF templates (analysis report, improved draft)
routes/web.php
```

---

## Troubleshooting

**Class "Smalot\PdfParser\Parser" not found**
Run `composer install`

**GEMINI_API_KEY is not configured**
Add key to `.env`, run `php artisan config:clear`

**No application encryption key has been specified**
Run `php artisan key:generate`

**File upload rejected (422)**
In `php.ini` (XAMPP: C:\xampp\php\php.ini), set `upload_max_filesize=10M` and `post_max_size=10M`

**MySQL connection refused**
Start MySQL in XAMPP Control Panel and check DB_* in `.env`

**Analysis returns "failed"**
Check `storage/logs/laravel.log`. Usually the API key is missing or the resume is a scanned image PDF with no text layer.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 |
| Frontend | Blade + Tailwind CSS |
| Auth | Laravel Breeze |
| Database | MySQL 8 / SQLite |
| AI | Google Gemini 2.5 Flash |
| PDF generation | barryvdh/laravel-dompdf |
| PDF parsing | smalot/pdfparser |
| DOCX parsing | phpoffice/phpword |
| File encryption | Laravel Crypt facade |
| Assets | Vite |
