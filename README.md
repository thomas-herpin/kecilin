<p align="center">
  Kecilin — URL Shortener
</p>

<p align="center">
  <a href="https://github.com/thomas-herpin/kecilin/actions/workflows/tests.yml">
    <img src="https://github.com/thomas-herpin/kecilin/actions/workflows/tests.yml/badge.svg?branch=main" alt="Build Status" />
  </a>
  <a href="https://github.com/thomas-herpin/kecilin/actions/workflows/tests.yml">
    <img src="https://img.shields.io/badge/tests-passing-brightgreen?logo=github" alt="Tests" />
  </a>
  <a href="https://codecov.io/gh/thomas-herpin/kecilin">
    <img src="https://codecov.io/gh/thomas-herpin/kecilin/branch/main/graph/badge.svg" alt="Coverage" />
  </a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12" />
</p>

---

## Deskripsi

Kecilin adalah layanan manajemen tautan modern berbasis web yang dibangun dengan Laravel. Ubah URL panjang menjadi tautan pendek bermerek dalam hitungan detik, lengkap dengan:

- **Pemendekan URL otomatis** — slug 6 karakter acak atau alias kustom pilihan sendiri
- **QR Code instan** — setiap tautan pendek langsung dilengkapi QR Code SVG yang siap scan
- **Pelacakan klik real-time** — pantau performa tautan dengan dashboard analitik tren harian
- **Filter domain berbahaya** — blacklist otomatis mencegah penyalahgunaan platform
- **Manajemen tautan** — edit URL tujuan atau hapus tautan kapan saja

## Cara Menjalankan Aplikasi

### Prasyarat

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL (untuk produksi) atau SQLite (untuk development lokal)

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/thomas-herpin/kecilin.git
cd kecilin

# 2. Install dependency PHP dan Node
composer install
npm install

# 3. Salin file environment dan generate app key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# Untuk MySQL:
#   DB_CONNECTION=mysql
#   DB_DATABASE=kecilin
#   DB_USERNAME=root
#   DB_PASSWORD=secret
#
# Untuk SQLite lokal:
#   DB_CONNECTION=sqlite
#   DB_DATABASE=/absolute/path/to/database/database.sqlite

# 5. Jalankan migrasi
php artisan migrate

# 6. Build asset frontend
npm run build
```

Atau gunakan shortcut composer:

```bash
composer run setup
```

### Menjalankan Server Development

```bash
# Jalankan semua service sekaligus (server, queue, log, vite)
composer run dev

# Atau manual:
php artisan serve
npm run dev
```

Aplikasi tersedia di `http://localhost:8000`.

## Cara Menjalankan Test

Test menggunakan SQLite in-memory sehingga tidak memerlukan konfigurasi database tambahan.

```bash
# Jalankan semua test suite
php artisan test

# Atau via composer
composer run test

# Jalankan per suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Integration
php artisan test --testsuite=Property
php artisan test --testsuite=Feature

# Jalankan dengan output verbose
php artisan test --verbose
```

## Strategi Pengujian

Kecilin menggunakan pendekatan **dual testing** yang saling melengkapi: unit test untuk memverifikasi contoh spesifik, dan property-based test untuk memverifikasi jaminan universal di seluruh ruang input.

### Unit Tests (`tests/Unit/`) — 5 file, ~30 test case

Memverifikasi perilaku spesifik setiap service secara terisolasi:

| File | Cakupan |
|---|---|
| `SlugGeneratorTest` | Format slug, validasi alias, panjang karakter |
| `BlacklistFilterTest` | Deteksi domain berbahaya, case-insensitivity |
| `QrCodeGeneratorTest` | Output SVG valid, embedding URL |
| `ClickTrackerTest` | Deteksi bot, presisi penghitung klik, timestamp |
| `UrlShortenerServiceTest` | Validasi skema URL, normalisasi, alias duplikat, persistensi |

### Property-Based Tests (`tests/Property/`) — 5 file, 10 properti

Memverifikasi **jaminan universal** yang harus berlaku untuk semua input yang mungkin, dijalankan minimum 100 iterasi per properti:

| Properti | Jaminan |
|---|---|
| P1: Slug format validity | `generate()` selalu menghasilkan tepat 6 karakter `[a-zA-Z0-9]` |
| P2: Alias validation | `validateAlias()` konsisten terhadap karakter dan panjang |
| P3: Slug uniqueness | `generateUnique()` tidak pernah menghasilkan slug yang sudah ada |
| P4: URL scheme validation | URL tanpa `http://`/`https://` selalu ditolak |
| P5: Blacklist case-insensitive | Domain blacklist diblokir tanpa peduli huruf besar/kecil |
| P6: Click counting accuracy | Klik valid menambah tepat +1; klik bot tidak menambah |
| P7: QR Code SVG validity | Output selalu berupa SVG valid yang menyematkan tautan pendek |
| P8: URL normalization | Trailing slash selalu dihapus, URL bersih tidak berubah |
| P9: Cascade delete | Menghapus tautan menghapus semua klik terkait tanpa sisa |
| P10: Daily click aggregation | Agregasi harian akurat sesuai jumlah klik yang dicatat |

### Integration Tests (`tests/Integration/`) — 5 file, 14 test case

Memverifikasi alur end-to-end antar komponen dengan database nyata (SQLite in-memory):

| File | Skenario |
|---|---|
| `RedirectFlowTest` | POST /shorten → GET /{slug} → redirect 301 ke URL asli |
| `AnalyticsPersistenceTest` | Klik tersimpan, terbaca, dan teragregasi per hari dengan benar |
| `CollisionHandlingTest` | Slug bertabrakan di-regenerasi otomatis; exception setelah 10x gagal |
| `LinkCreationWorkflowTest` | Workflow lengkap: input → DB → QR Code + tautan pendek di view |
| `NotFoundAndCascadeTest` | Slug tidak ada → 404 kustom; hapus tautan → cascade delete klik |