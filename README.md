# 🏢 ITAM - Enterprise IT Asset Management System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%20%7C%208.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Security OWASP](https://img.shields.io/badge/Security-OWASP_Top_10-4A90E2?style=for-the-badge&logo=shield&logoColor=white)](https://owasp.org)
[![Tests Passing](https://img.shields.io/badge/Tests-44%20Passed-brightgreen?style=for-the-badge&logo=checkmarx&logoColor=white)](https://phpunit.de)

**ITAM (IT Asset Management)** adalah platform manajemen siklus hidup aset teknologi informasi tingkat enterprise yang dirancang khusus untuk memonitor, mengaudit, dan mengendalikan inventaris perangkat keras, lisensi perangkat lunak, penyusutan depresiasi nilai buku, serta pemeliharaan infrastruktur teknologi secara terpusat, transparan, dan terstandar keamanan OWASP.

---

## 🌟 Fitur Utama

- **📦 Manajemen Siklus Hidup Aset (Lifecycle Control)**
  - Pencatatan aset menyeluruh dari proses pengadaan (*procurement*), penerimaan, penugasan pengguna, pemeliharaan (*maintenance*), hingga pelepasan (*disposal*).
  - Klasifikasi aset multi-kategori (Perangkat Keras, Lisensi Software, Infrastruktur Jaringan, Server).
  
- **🏢 Setup Profil & Identitas Perusahaan Terpusat**
  - Konfigurasi nama korporasi, tagline, profil deskripsi, NPWP (*Tax ID*), alamat domisili, email, website, serta kontak telepon operasional.
  - Unggah logo resmi dan foto gedung/kantor utama dengan validasi format berkas ketat (*OWASP-compliant file uploads*).
  - Integrasi otomatis identitas perusahaan ke layout navigasi, laporan, dan dashboard eksekutif.

- **📈 Analitik Nilai & Depresiasi Aset**
  - Kalkulasi nilai perolehan, estimasi umur manfaat, dan depresiasi nilai buku aset (*book value*).
  - Pelaporan visual statistik dan ringkasan kondisi aset secara real-time.

- **🔒 Keamanan Berstandar OWASP Top 10**
  - Sistem autentikasi ketat berpelindung proteksi *session fixation* dan *session hijacking*.
  - *Rate-limiting* cerdas (IP + Email throttling) untuk mencegah serangan *Brute Force* dan *Credential Stuffing*.
  - Sanitasi file upload dengan acak hash nama berkas (*random hash storage*) dan validasi MIME type ketat.
  - Proteksi penuh terhadap CSRF (*Cross-Site Request Forgery*) dan XSS (*Cross-Site Scripting*).

- **🎨 Desain Modern & Responsif**
  - Dibangun dengan **Tailwind CSS v4** dan **Alpine.js**.
  - Tampilan dashboard gelap elegan (*Dark Aesthetic*) dengan hierarki visual terstruktur.

---

## 🛠️ Stack Teknologi

- **Backend:** Laravel 12.x / PHP 8.3 & 8.4
- **Database:** SQLite / MySQL / PostgreSQL
- **Frontend:** Blade Templating Engine, Tailwind CSS v4, Alpine.js, Vite
- **Testing & Quality Assurance:** PHPUnit 12, Laravel Pint (PSR-12), Laravel Boost

---

## 🚀 Panduan Instalasi Cepat

### Prasyarat
- PHP >= 8.3
- Composer >= 2.5
- Node.js & NPM
- Ekstensi PHP: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`/`imagick`

### Langkah-langkah
1. **Clone Repository**
   ```bash
   git clone https://github.com/satkerit/ITAM.git
   cd ITAM
   ```

2. **Instal Dependensi PHP & Frontend**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Jalankan Migrasi Database & Seeder**
   ```bash
   php artisan migrate --seed
   ```

5. **Buat Symlink Storage Publik**
   ```bash
   php artisan storage:link
   ```

6. **Build Asset Frontend**
   ```bash
   npm run build
   ```

7. **Jalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses melalui peramban di `http://localhost:8000`.

---

## 🧪 Pengujian & Testing

Sistem dilengkapi suite pengujian otomatis menyeluruh mencakup otentikasi, proteksi brute-force, CRUD aset, dan unggah profil:

```bash
# Jalankan test suite
php artisan test --compact

# Jalankan linter dan format kode
vendor/bin/pint --format agent
```

---

## 📄 Lisensi

Platform ini dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
