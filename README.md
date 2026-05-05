<p align="center">
  <h1 align="center">Kecilin</h1>
</p>

<p align="center">
  <a href="https://github.com/thomas-herpin/kecilin/actions/workflows/tests.yml"><img src="https://github.com/thomas-herpin/kecilin/actions/workflows/tests.yml/badge.svg?branch=main" alt="Tests"></a>
  <a href="https://codecov.io/gh/thomas-herpin/kecilin"><img src="https://codecov.io/gh/thomas-herpin/kecilin/branch/main/graph/badge.svg" alt="Coverage"></a>
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
</p>

---

## Deskripsi

Kecilin adalah aplikasi berbasis web yang memudahkan kamu untuk mengelola tautan. Dengan Kecilin, kamu bisa mengubah URL yang panjang dan rumit menjadi tautan pendek yang rapi hanya dalam hitungan detik. Aplikasi ini dibangun menggunakan framework Laravel dan dilengkapi dengan berbagai fitur:

- **Pemendek URL Otomatis** — Gunakan kode acak 6 karakter atau buat nama unik (alias) kamu sendiri.
- **QR Code Instan** — Setiap tautan yang dibuat otomatis mendapatkan QR Code (format SVG) yang siap scan dan diunduh.
- **Pantau Klik (Analitik)** — Lihat berapa banyak orang yang mengeklik tautanmu melalui grafik tren harian.
- **Keamanan Link** — Sistem secara otomatis memblokir domain berbahaya (blacklist) untuk menjaga keamanan pengguna.
- **Manajemen Mandiri** — Kamu bebas mengubah alamat tujuan tautan atau menghapusnya kapan saja.

## Cara Menjalankan Aplikasi

### Prasyarat

Sebelum memulai, pastikan perangkat kamu sudah terpasang:
- PHP 8.2+
- Composer (manajer paket PHP)
- Node.js & npm (untuk tampilan antarmuka)
- MySQL (digunakan untuk menyimpan data dan analitik secara stabil)

### Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/thomas-herpin/kecilin.git
cd kecilin

# 2. Pasang library pendukung (PHP & Node)
composer install
npm install

# 3. Salin file environment dan generate app key
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# Buka file .env dan sesuaikan bagian ini dengan data MySQL kamu:
#   DB_CONNECTION=mysql
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_DATABASE=kecilin
#   DB_USERNAME=root
#   DB_PASSWORD=secret

# 5. Jalankan migrasi database
php artisan migrate

# 6. Build asset frontend
npm run build
```

### Menjalankan Server Development

Jalankan di terminal yang berbeda:

```bash
# Terminal 1
php artisan serve

# Terminal 1
npm run dev
```

Aplikasi tersedia di `http://localhost:8000`.

## Cara Menjalankan Test

Pengujian dilakukan untuk memastikan semua fitur berjalan normal tanpa ada kerusakan kode.

```bash
# Jalankan semua pengujian sekaligus
php artisan test

# Atau via composer
composer run test

# Jalankan per bagian tertentu
php artisan test --testsuite=Unit
php artisan test --testsuite=Property
php artisan test --testsuite=Integration
```

## Strategi Pengujian

Kecilin telah melalui tiga tahapan pengujian, yaitu **unit test** untuk memverifikasi fungsi dasar, **property-based test** untuk menguji ketahanan sistem terhadap berbagai variasi data acak, serta **integration test** untuk memastikan seluruh alur sistem mulai dari input link hingga penyimpanan ke database berjalan dengan baik tanpa kendala.

### Unit Tests (`tests/Unit/`) — 7 file, ~40 test case

Memastikan setiap layanan aplikasi bekerja dengan benar secara terpisah:

| File | Cakupan |
|---|---|
| `SlugGeneratorTest` | Format slug, validasi alias, panjang karakter |
| `BlacklistFilterTest` | Deteksi domain berbahaya, case-insensitivity |
| `QrCodeGeneratorTest` | Output SVG valid, embedding URL |
| `ClickTrackerTest` | Deteksi bot, presisi penghitung klik, timestamp |
| `UrlShortenerServiceTest` | Validasi skema URL, normalisasi, alias duplikat, persistensi |
| `LinkModelTest` | Accessor `full_short_url`, relasi ke klik, dan pengujian `scopeLatest` |
| `ClickModelTest` | Verifikasi relasi `belongsTo` ke model Link |

### Property-Based Tests (`tests/Property/`) — 5 file, 10 properti

Menguji aturan utama aplikasi dengan semua input yang mungkin:

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

### Integration Tests (`tests/Integration/`) — 7 file, ~20 test case

Menguji bagaimana seluruh komponen bekerja sama dalam alur yang utuh untuk memastikan tidak ada hambatan saat digunakan oleh pengguna:

| File | Skenario |
|---|---|
| `RedirectFlowTest` | POST /shorten → GET /{slug} → redirect 302 ke URL asli, serta update URL tujuan |
| `AnalyticsPersistenceTest` | Klik tersimpan, terbaca, dan teragregasi per hari dengan benar |
| `CollisionHandlingTest` | Slug bertabrakan di-regenerasi otomatis; exception setelah 10x gagal |
| `LinkCreationWorkflowTest` | Workflow lengkap: input → DB → QR Code + tautan pendek di view |
| `NotFoundAndCascadeTest` | Slug tidak ada → 404 kustom; hapus tautan → cascade delete klik |
| `HistoryViewTest` | Memastikan daftar riwayat tautan dapat dimuat dan diurutkan dengan benar |
| `AnalyticsViewTest` | Verifikasi dashboard analitik dan integritas data grafik tren klik |