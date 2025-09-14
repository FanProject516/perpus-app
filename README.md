# Sistem Perpustakaan Laravel

Aplikasi manajemen perpustakaan berbasis Laravel dengan fitur lengkap untuk pencarian buku, peminjaman, dan administrasi.

## Fitur Utama

- 🔐 **Autentikasi & Otorisasi** - Multi-role (Member, Staff, Admin)
- 📚 **Katalog Buku** - CRUD buku dengan pencarian dan filter
- 📋 **Sistem Peminjaman** - Request, approve, return dengan tracking
- 📊 **Dashboard & Laporan** - Statistik dan laporan sirkulasi
- 📧 **Notifikasi** - Email reminder dan notifikasi status
- 🔍 **Pencarian Advanced** - Filter berdasarkan kategori, penulis, tahun
- 📱 **Responsive Design** - UI yang optimal untuk desktop dan mobile

## Teknologi

- **Backend**: Laravel 10.x (PHP 8.2+)
- **Database**: MySQL 8.0 / PostgreSQL
- **Cache**: Redis
- **Queue**: Redis
- **Frontend**: Blade + Tailwind CSS / Inertia.js + Vue/React
- **Container**: Docker & Docker Compose

## Quick Start

### 1. Clone Repository

```bash
git clone https://github.com/username/perpus-app.git
cd perpus-app
```

### 2. Setup dengan Docker (Recommended)

```bash
# Copy environment file
cp .env.example .env

# Build dan start containers
docker-compose up -d --build

# Install dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations dan seeders
docker-compose exec app php artisan migrate --seed

# Set permissions
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

Aplikasi akan tersedia di `http://localhost:8080`

### 3. Setup Manual (Tanpa Docker)

```bash
# Install dependencies
composer install

# Copy dan edit environment
cp .env.example .env
# Edit .env sesuai konfigurasi database

# Generate key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Start development server
php artisan serve
```

## Development Setup

### Install Packages Tambahan

```bash
# Authentication scaffolding
composer require laravel/breeze --dev
php artisan breeze:install blade

# Permission management
composer require spatie/laravel-permission

# Excel import/export
composer require maatwebsite/excel

# Image processing
composer require intervention/image
```

### Database Seeders

```bash
# Run specific seeder
php artisan db:seed --class=BookSeeder
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=UserSeeder
```

### Testing

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Code style check
./vendor/bin/pint --test

# Static analysis
./vendor/bin/phpstan analyse
```

### Queue Workers

```bash
# Start queue worker
php artisan queue:work

# Or dengan Docker
docker-compose exec app php artisan queue:work
```

## API Documentation

API endpoints tersedia di `/api/documentation` (Swagger UI).

### Authentication

```bash
# Register
POST /api/auth/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}

# Login
POST /api/auth/login
{
  "email": "john@example.com",
  "password": "password"
}
```

### Books

```bash
# List books with search
GET /api/books?search=laravel&category=1&page=1

# Get book detail
GET /api/books/{id}

# Create book (Staff/Admin only)
POST /api/books
{
  "title": "Laravel Best Practices",
  "author": "John Doe",
  "isbn": "9781234567890",
  "category_id": 1,
  "total_copies": 5
}
```

## Deployment

### Production dengan Docker

```bash
# Build untuk production
docker-compose -f docker-compose.prod.yml up -d --build

# Optimize Laravel
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Environment Variables

Key environment variables yang perlu dikonfigurasi:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=your-db-host
DB_DATABASE=perpus
DB_USERNAME=your-username
DB_PASSWORD=your-password

REDIS_HOST=your-redis-host

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

# Custom settings
LOAN_DURATION_DAYS=14
MAX_BOOKS_PER_USER=5
FINE_PER_DAY=1000
AUTO_APPROVE_LOANS=false
```

## Structure

```
perpus-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   ├── BookController.php
│   │   ├── LoanController.php
│   │   └── Admin/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Book.php
│   │   ├── Category.php
│   │   ├── Loan.php
│   │   └── Copy.php
│   └── Policies/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── resources/
│   ├── views/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
└── tests/
    ├── Feature/
    └── Unit/
```

## Contributing

1. Fork repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## License

MIT License. Lihat [LICENSE](LICENSE) file untuk detail.

## Support

Untuk pertanyaan atau dukungan:

- Email: support@perpus-app.com
- Issue: [GitHub Issues](https://github.com/username/perpus-app/issues)
- Documentation: [Wiki](https://github.com/username/perpus-app/wiki)
