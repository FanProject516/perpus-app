# Summary: Website Perpustakaan

Dokumen ini adalah ringkasan desain dan spesifikasi untuk pengembangan website perpustakaan.

## Tujuan

- Menyediakan katalog buku online untuk pengguna (siswa, dosen, masyarakat).
- Memfasilitasi pencarian, peminjaman, pengembalian, dan manajemen koleksi oleh petugas perpustakaan.
- Menyediakan sistem autentikasi dan izin peran (user, staff/admin).
- Menyediakan laporan sirkulasi, statistik, dan integrasi notifikasi (email/SMS).

## Target Pengguna

- Pengunjung umum (bisa melihat katalog dan mendaftar)
- Anggota terdaftar (meminjam, melihat riwayat)
- Petugas perpustakaan (manajemen koleksi, peminjaman, laporan)
- Administrator (pengaturan sistem, manajemen pengguna)

## Fitur Utama

1. Autentikasi dan otorisasi
   - Registrasi akun (email/nomor telepon)
   - Login (email/username + password)
   - Role-based access control (Guest, Member, Staff, Admin)
2. Katalog Buku
   - Daftar buku dengan metadata (judul, penulis, penerbit, ISBN, tahun, kategori, sinopsis, cover)
   - Filter & pencarian (judul, penulis, ISBN, kategori, tahun)
   - Detail buku beserta ketersediaan (jumlah total, jumlah tersedia)
3. Sistem Peminjaman
   - Permintaan peminjaman oleh anggota
   - Persetujuan otomatis/manual oleh staff
   - Durasi pinjam (configurable), perpanjangan, denda keterlambatan
   - Riwayat pinjaman pengguna
4. Manajemen Koleksi
   - CRUD buku
   - Import/Export data (CSV/Excel)
5. Notifikasi & Pengingat
   - Email atau SMS untuk pengingat jatuh tempo, persetujuan peminjaman, registrasi
6. Dashboard & Laporan
   - Statistik jumlah buku, peminjaman aktif, buku paling sering dipinjam
   - Laporan sirkulasi dan laporan denda

## Arsitektur & Teknologi yang Disarankan (Laravel)

- Frontend: Blade (server-rendered) atau SPA dengan Inertia.js + Vue/React / atau frontend terpisah (Vite + Vue/React). Gunakan Tailwind CSS atau Bootstrap untuk styling.
- Backend: Laravel (PHP 8.1+) sebagai framework utama.
- Database: MySQL atau PostgreSQL; Redis untuk cache/queue/session.
- Penyimpanan file: Local storage untuk pengembangan, S3 (AWS) untuk produksi.
- Autentikasi: Laravel Sanctum untuk API token / SPA auth; Laravel Breeze atau Jetstream untuk scaffolding auth + session.
- Email: SMTP (SendGrid, Mailgun) dikonfigurasi di `.env` dengan driver `smtp` atau `ses`.
- Queue & Background Jobs: Redis + Laravel Queue (horizon untuk monitoring opsional).
- Deployment: Docker (recommended), Forge, Vapor (serverless), atau VPS (Docker Compose). CI: GitHub Actions.

## Model Data (Skema Ringkas)

- Users
  - id, name, email, password_hash, role, created_at, updated_at
- Books
  - id, title, author, isbn, publisher, year, category_id, summary, cover_url, total_copies, available_copies, created_at, updated_at
- Categories
  - id, name, parent_id
- Loans
  - id, user_id, book_id, copy_id (nullable), status (requested/approved/borrowed/returned/overdue), borrowed_at, due_at, returned_at, fine_amount
- Copies (opsional)
  - id, book_id, barcode, condition, location, is_available
- Notifications
  - id, user_id, type, payload, sent_at, read
- AuditLogs
  - id, user_id, action, target_type, target_id, timestamp, details

## Endpoints API (Contoh)

- Auth
  - POST `/api/auth/register` - registrasi
  - POST `/api/auth/login` - login
  - POST `/api/auth/logout` - logout
  - POST `/api/auth/refresh` - refresh token (jika memakai JWT)
- Users
  - GET `/api/users` (admin)
  - GET `/api/users/:id`
  - PUT `/api/users/:id`
  - DELETE `/api/users/:id` (admin)
- Books
  - GET `/api/books` - list + query params untuk filter/pagination
  - GET `/api/books/:id` - detail
  - POST `/api/books` (staff/admin)
  - PUT `/api/books/:id` (staff/admin)
  - DELETE `/api/books/:id` (staff/admin)
- Loans
  - POST `/api/loans` - request loan (member)
  - GET `/api/loans` - list (staff/admin untuk semua, member hanya miliknya)
  - PUT `/api/loans/:id/approve` (staff)
  - PUT `/api/loans/:id/return` (staff/member)
- Reports
  - GET `/api/reports/loans` - filterable by date range

### Contoh route Laravel (api.php / web.php)

- `Route::post('auth/register', [AuthController::class, 'register']);`
- `Route::post('auth/login', [AuthController::class, 'login']);`
- `Route::middleware('auth:sanctum')->group(function () { Route::apiResource('books', BookController::class); Route::apiResource('loans', LoanController::class); });`

## Laravel-specific Implementation Notes

- Scaffolding & Auth

  - Gunakan `composer create-project laravel/laravel perpus-app` untuk memulai proyek.
  - Untuk SPA/Token: `composer require laravel/sanctum` dan ikuti dokumentasi; untuk scaffold UI ringan gunakan `composer require laravel/breeze --dev` lalu `php artisan breeze:install blade` atau `php artisan breeze:install react`.
  - Alternatif lengkap: `laravel/jetstream` (Livewire atau Inertia stacks) bila membutuhkan team features.

- Package Rekomendasi

  - `laravel/sanctum` — auth API/SPA
  - `spatie/laravel-permission` — manajemen role & permission
  - `maatwebsite/excel` — import/export CSV/Excel
  - `laravel/horizon` — monitoring queue (jika menggunakan Redis)
  - `spatie/laravel-activitylog` — audit log

- Migration & Eloquent Models (contoh singkat)
  - Buat migrations: `php artisan make:model Book -m`, `php artisan make:model Loan -m`, `php artisan make:model Copy -m`, `php artisan make:model Category -m`.
  - Contoh migration `books` (ringkas):

```php
Schema::create('books', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('author');
    $table->string('isbn')->nullable()->unique();
    $table->string('publisher')->nullable();
    $table->year('year')->nullable();
    $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
    $table->text('summary')->nullable();
    $table->string('cover_path')->nullable();
    $table->integer('total_copies')->default(1);
    $table->integer('available_copies')->default(1);
    $table->timestamps();
});
```

- Handling concurrency

  - Gunakan DB transactions dan `SELECT ... FOR UPDATE` (Eloquent: `->lockForUpdate()` inside transaction) saat mengurangi `available_copies` untuk menghindari race condition.

- File Uploads

  - Gunakan Laravel Filesystem (config `filesystems.php`) dan simpan ke `public` atau S3.

- Testing

  - Gunakan `phpunit` untuk unit & feature tests, `laravel/dusk` untuk E2E (browser) tests.

- Seeders & Factories

  - Buat factories untuk `User`, `Book`, `Loan` untuk seeding dev dan testing.

- Env & .env.example (minimal)

```
APP_NAME=PerpusApp
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=perpus
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=database
FILESYSTEM_DRIVER=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="PerpusApp"
```

## Project Structure (direkomendasikan)

- `app/Models` - Eloquent models (`User`, `Book`, `Loan`, `Copy`, `Category`)
- `app/Http/Controllers` - Controller untuk `AuthController`, `BookController`, `LoanController`, `Admin` namespace
- `app/Policies` - Authorization policies
- `database/migrations` - migrations
- `database/factories` - model factories
- `routes/web.php` dan `routes/api.php` - route definitions
- `resources/views` - Blade templates (jika memilih server-rendered)
- `resources/js` - jika menggunakan Vite + SPA

## CI / Deployment Snippets

- GitHub Actions basic workflow: run PHP linter, composer install, run tests, build frontend (if any), build docker image and push to registry.
- Docker (minimal `docker-compose.yml` services): `app` (php-fpm), `nginx`, `db` (mysql/postgres), `redis`, `queue-worker`.

## Migration & Seed Example Tasks

- `php artisan migrate` - menjalankan migrasi
- `php artisan db:seed` - seeding data contoh

---

Jika Anda ingin, saya bisa:

- Meng-update `summary.md` lagi untuk memasukkan contoh migration file lengkap dan model Eloquent.
- Membuat skeleton proyek Laravel di `c:\PROJECT\perpus-app` (Docker Compose, `composer.json`, `.env.example`, migrations dasar, dan beberapa controller dasar).
- Menyediakan GitHub Actions workflow contoh.

Pilih salah satu yang Anda inginkan dan saya akan lanjutkan dengan langkah selanjutnya.

## UI/UX Flow (Halaman Utama)

- Landing page dengan pencarian cepat
- Halaman katalog dengan filter dan pagination
- Halaman detail buku (daftar ketersediaan dan tombol pinjam)
- Halaman akun (profil, riwayat peminjaman, denda)
- Halaman admin/staff (manajemen buku, peminjaman, laporan)
- Responsive design: desktop & mobile

## Non-Functional Requirements

- Keamanan: hash password (bcrypt/argon2), validasi input, sanitasi file upload
- Skalabilitas: paging, cache, indexing db untuk pencarian
- Performa: query optimize, lazy-loading cover images
- Ketersediaan: backup DB, monitoring

## Testing

- Unit tests untuk logic bisnis (pinjam, denda, perpanjangan)
- Integration tests untuk API endpoints
- E2E tests untuk alur penting (registrasi, login, peminjaman)
- Contoh tools: Jest, Mocha, Cypress, PyTest

## Deployment & CI/CD

- Pipeline: build frontend -> run unit tests -> build docker image -> deploy ke staging -> run integration tests -> deploy ke production
- Gunakan GitHub Actions / GitLab CI / CircleCI

## Checklist Implementasi (Milestone)

1. Setup project (repo, CI, basic README)
2. Authentication & user model
3. CRUD buku & kategori
4. Sistem peminjaman dasar (request, approve, return)
5. Notifikasi & email
6. Dashboard & laporan
7. Testing & dokumentasi
8. Deploy

## Estimasi Waktu Kasar

- MVP (CRUD buku + pinjam + auth): 2-4 minggu (1-2 pengembang)
- Fitur lengkap (rekomendasi, integrasi, laporan lengkap): +4-8 minggu

## Risiko & Edge Cases

- Buku dipinjam bersamaan (race condition) -> gunakan transaksi DB dan penguncian record
- Data duplikat ISBN -> validasi unik dan mekanisme impor yang robust
- Kehilangan buku atau perubahan jumlah salinan -> mekanisme inventory

## Integrasi MCP Server GitHub

### Setup Repository & Project Management

1. **Inisialisasi Repository GitHub**

   ```bash
   git init
   git remote add origin https://github.com/username/perpus-app.git
   git branch -M main
   git push -u origin main
   ```

2. **GitHub Project Setup**

   - Buat GitHub Project untuk tracking milestone dan task
   - Setup issue templates untuk bug report dan feature request
   - Buat labels: `bug`, `enhancement`, `documentation`, `help wanted`, `good first issue`

3. **Branch Strategy**
   - `main` - production ready code
   - `develop` - integration branch
   - `feature/*` - feature development
   - `hotfix/*` - critical fixes

### GitHub Actions CI/CD Pipeline

Buat file `.github/workflows/ci.yml`:

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: perpus_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.1"
          extensions: mbstring, dom, fileinfo, mysql
          coverage: xdebug

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Copy Environment File
        run: cp .env.example .env

      - name: Generate Application Key
        run: php artisan key:generate

      - name: Run Migrations
        run: php artisan migrate --env=testing

      - name: Run PHP CS Fixer
        run: vendor/bin/pint --test

      - name: Run PHPStan
        run: vendor/bin/phpstan analyse

      - name: Execute Tests
        run: php artisan test --coverage

  deploy:
    if: github.ref == 'refs/heads/main'
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to Production
        run: echo "Deploy to production server"
```

### GitHub Integration Features

1. **Issue Tracking & Project Management**

   - Link commits dengan issues menggunakan keywords: `fixes #123`, `closes #456`
   - Gunakan GitHub Projects untuk kanban board dan milestone tracking
   - Setup automated project cards movement berdasarkan PR status

2. **Pull Request Automation**

   - Template PR dengan checklist testing dan documentation
   - Required status checks sebelum merge
   - Automatic deployment ke staging environment untuk review

3. **Release Management**

   - Semantic versioning dengan GitHub releases
   - Automated changelog generation
   - Tag-based deployment ke production

4. **Repository Security**
   - Dependabot untuk dependency updates
   - CodeQL analysis untuk security scanning
   - Secret scanning untuk credentials

### Environment Configuration

Setup GitHub Secrets untuk deployment:

- `DB_PASSWORD` - Database password
- `MAIL_USERNAME` / `MAIL_PASSWORD` - Email credentials
- `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` - S3 storage
- `DEPLOY_SSH_KEY` - SSH key untuk deployment

### Development Workflow dengan GitHub

1. **Feature Development**

   ```bash
   git checkout -b feature/add-book-search
   # Development work
   git commit -m "feat: implement book search functionality"
   git push origin feature/add-book-search
   # Create Pull Request via GitHub UI
   ```

2. **Code Review Process**

   - Mandatory code review sebelum merge
   - Automated testing must pass
   - Documentation updates required

3. **Issue Management**
   - Gunakan issue templates untuk consistent reporting
   - Label issues berdasarkan priority dan type
   - Link issues ke milestones dan projects

### Monitoring & Maintenance

1. **GitHub Insights**

   - Monitor code frequency dan contributor activity
   - Track pulse metrics dan dependency graphs
   - Security advisories dan vulnerability alerts

2. **Automated Maintenance**
   - Scheduled workflows untuk cleanup dan maintenance
   - Automated dependency updates dengan Dependabot
   - Regular security scans dan reports

### Team Collaboration

1. **GitHub Teams & Permissions**

   - Setup teams untuk different roles (developers, testers, admins)
   - Branch protection rules dengan required reviewers
   - Different access levels untuk repository features

2. **Documentation**
   - Wiki untuk project documentation
   - README dengan setup instructions dan contributing guidelines
   - API documentation dengan GitHub Pages

## Catatan Tambahan

- Mulailah dengan API-first design: dokumentasikan endpoint (OpenAPI / Swagger)
- Siapkan seed data untuk pengujian dan demo
- Pertimbangkan privasi data pengguna dan kebijakan retensi
- Gunakan GitHub Projects untuk tracking progress dan milestone
- Setup automated testing dan deployment pipeline sejak awal development

---

Dokumen ini dibuat sebagai referensi awal. Dengan integrasi MCP Server GitHub, proyek ini akan memiliki workflow development yang terstruktur, automated testing/deployment, dan project management yang efisien.
