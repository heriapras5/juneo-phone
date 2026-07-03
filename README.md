<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Juneo Phone Service Management System

**Juneo Phone** adalah aplikasi manajemen service HP berbasis Laravel 12 yang dirancang untuk membantu bengkel service HP dalam mengelola pelanggan, service, keuangan, dan laporan secara efisien.

### 🔧 Fitur Utama
- ✅ Manajemen pelanggan
- ✅ Tracking service dengan multi-item (beberapa HP dalam satu service)
- ✅ Status workflow (Menunggu → Dikerjakan → Selesai → Diambil)
- ✅ Teknisi assignment (Adit/Edi/Nur)
- ✅ Antrian prioritas dengan indikator urgency
- ✅ Garansi management
- ✅ Biaya harian & gaji karyawan
- ✅ Dashboard statistik & KPI
- ✅ Laporan multi-format (Excel, CSV, PDF)
- ✅ Google OAuth login
- ✅ Role-based access control (SuperAdmin, Admin)

### 🚀 Deployment Guide

#### Prasyarat
- PHP 8.2+
- MySQL 8.0+
- Composer 2.0+
- Node.js 18+ (untuk build assets)

#### Langkah Instalasi
```bash
# 1. Clone repository
composer install --optimize-autoloader --no-dev

# 2. Setup environment
cp .env.production.example .env
php artisan key:generate

# 3. Konfigurasi database di .env
#    DB_DATABASE=db_juneo
#    DB_USERNAME=juneo_user
#    DB_PASSWORD=strong_password

# 4. Jalankan migrations & seeders
php artisan migrate --force
php artisan db:seed --class=SuperAdminSeeder --force

# 5. Build assets (jika menggunakan Vite)
npm install
npm run build

# 6. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Konfigurasi web server (Apache/Nginx)
#    Point document root ke /public
#    Set up HTTPS (wajib untuk OAuth)

# 8. Jalankan queue worker (opsional)
php artisan queue:work --daemon --sleep=3 --tries=3

# 9. Set up cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

#### 🔐 Security Best Practices
- Selalu gunakan `APP_DEBUG=false` di production
- Gunakan password database yang kuat
- Aktifkan HTTPS untuk semua traffic
- Batasi akses database ke localhost atau IP tepercaya
- Lakukan backup database secara rutin
- Update Laravel dan dependencies secara berkala

#### 🧪 Testing
Untuk menjalankan testing scenario lengkap:
```bash
php artisan db:seed --class=TestingDataSeeder
```

Lihat file `TESTING_SCENARIO.md` untuk panduan testing manual.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

Juneo Phone Service Management System © 2026 by Juneo Phone.
