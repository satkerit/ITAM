# Laporan Audit & Progress Perbaikan — ITAM

| Item | Keterangan |
| --- | --- |
| Repositori | `satkerit/ITAM` (branch `main`) |
| Commit terakhir | `c7e5a11` — 30 Oktober 2026 (ada perbaikan lanjutan yang belum di-commit) |
| Tanggal laporan | 30 Oktober 2026 |
| Stack | Laravel 13.38.0, PHP 8.4, Blade + Alpine.js 3.17.5, Tailwind CSS 4, Vite 8, Sanctum 4, PHPUnit |
| Status uji | **70 passed / 373 assertions** |
| Status formatter | Laravel Pint — `passed` |
| Status dependency | `composer audit` — **No security vulnerability advisories found** |

---

## 1. Ringkasan Eksekutif

Audit dilakukan dua lapis: **kualitas kode** (Laravel best practices) dan **keamanan** (OWASP Top 10). Seluruh temuan berprioritas tinggi sudah diperbaiki dan diuji, dan pekerjaan terbagi dalam tiga commit:

| Fase | Fokus | Commit | Dampak |
| --- | --- | --- | --- |
| Kualitas kode (Tahap 1–6) | Integritas data & sentralisasi logika operasi aset | `b037b87` | 21 file, +446 / −335 |
| Keamanan OWASP | Otorisasi, header, validasi, rate limit, dependency | `0cfbc5e` | 27 file, +409 / −92 |
| Lanjutan keamanan | CSP nonce & scoping aset per lokasi | `c7e5a11` | 9 file, +262 / −18 |
| Perbaikan pasca-review (belum di-commit) | Otorisasi operasi tulis, scoping dashboard/daftar operasional, impor CSV dipisah ke service | — | 12 file |

Tidak ada temuan **Critical** yang tersisa. Sisa pekerjaan bersifat pengerasan lanjutan (lihat Bagian 8).

---

## 2. Metodologi

- **Audit kualitas kode** memakai panduan Laravel best practices yang dijalankan bertahap (Tahap 1–6), fokus pada arsitektur, integritas transaksi, konsistensi validasi, dan duplikasi logika.
- **Audit keamanan** memakai panduan OWASP Top 10 untuk aplikasi Laravel + Blade, mencakup otorisasi, injeksi, konfigurasi, komponen pihak ketiga, autentikasi, dan logging.
- Setiap perbaikan diverifikasi dengan test suite (`php artisan test`) dan Pint, bukan dengan skrip manual/tinker.

---

## 3. Hasil Audit Kualitas Kode (Tahap 1–6)

Ringkasan temuan dan status perbaikan pada commit `b037b87`:

| Tahap | Temuan | Perbaikan | Status |
| --- | --- | --- | --- |
| 1 | Endpoint `api/v1` dapat diakses tanpa autentikasi | Seluruh grup `api/v1` dibungkus `auth:sanctum` | Selesai |
| 2 | Logika assign/return/mutasi/maintenance/disposal terduplikasi di controller API dan Web, tanpa penguncian baris sehingga rawan race condition | `App\Services\AssetOperationService` sebagai satu-satunya jalur operasi: `assign`, `checkIn`, `mutate`, `recordMaintenance`, `completeMaintenance`, `dispose`, semuanya di dalam `DB::transaction` + `lockForUpdate` | Selesai |
| 2 | Error operasi tidak konsisten antara API dan Web | `App\Exceptions\AssetOperationException` — render JSON 422 untuk API, flash error untuk Web | Selesai |
| 3 | Tidak ada jaminan satu assignment aktif per aset di level database | Migration `add_active_asset_guard_to_asset_assignments_table` dengan generated column `active_asset_id` + unique index (driver-aware: `virtualAs` untuk SQLite, `storedAs` untuk MySQL/MariaDB) | Selesai |
| 4 | `destroy` di Web mengizinkan hapus aset yang punya riwayat operasional, berbeda dari API | Web kini memblokir aset dengan riwayat operasional, konsisten dengan API | Selesai |
| 5 | Import CSV tidak memvalidasi status enum dan format `purchase_date` | Validasi status terhadap enum dan format tanggal pada import; format `asset_code` dibatasi whitelist karakter | Selesai |
| 5 | Status laporan memakai nilai di luar enum (`archived`, `defective`) | Dipetakan ke `Retired` / `Sold` / `Disposed` | Selesai |
| 6 | Generator QR tersebar di model, Web, dan API | Satu generator `Asset::qrSvg(int $size = 300)` dipakai bersama | Selesai |

---

## 4. Hasil Audit Keamanan (OWASP Top 10)

Status per kategori setelah commit `0cfbc5e` dan `c7e5a11`:

| Kategori | Temuan | Perbaikan | Status |
| --- | --- | --- | --- |
| **A01** Broken Access Control | Route hanya dilindungi `auth`, tanpa pembedaan role; user bisa mengubah/menghapus akunnya sendiri; admin terakhir bisa dihapus; staff dapat membaca aset cabang lain | Middleware `EnsureUserHasRole` (alias `role`, variatif `role:admin,technician`); route dikelompokkan per role di `routes/web.php` dan `routes/api.php`; proteksi ubah/hapus akun sendiri + `isLastAdmin()` di `UserWebController`; `AssetPolicy` + `scopeVisibleTo()` untuk scoping lokasi | Selesai |
| **A02** Cryptographic Failures | Tidak ada HSTS; password sudah di-hash | `Strict-Transport-Security` saat request HTTPS; `password` cast `hashed`; cookie `httpOnly` & `SameSite=lax` | Selesai (lihat 8.3) |
| **A03** Injection | `asset_code` menerima karakter bebas (risiko injeksi ke konteks QR/CSV); upload logo menerima `svg` (XSS tersimpan) | Regex `^[A-Za-z0-9._-]+$` di `StoreAssetRequest`, `UpdateAssetRequest`, `ScanAssetRequest`, validasi Web, dan guard `preg_match` pada import CSV; `svg` dihapus dari `mimes` logo & foto kantor; query tetap memakai Eloquent binding | Selesai |
| **A04** Insecure Design | Operasi aset tanpa transaksi/lock; tidak ada constraint satu assignment aktif | Service terpusat + `DB::transaction` + `lockForUpdate`; unique index `active_asset_id` | Selesai |
| **A05** Security Misconfiguration | Tidak ada security header maupun CSP; API tanpa rate limit; session & token berumur panjang | Middleware global `SecurityHeaders`: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Cross-Origin-Opener-Policy`, `Strict-Transport-Security`, dan `Content-Security-Policy` berbasis nonce; `throttleApi('60,1')`; session 30 menit; expiry Sanctum 7 hari | Selesai |
| **A06** Vulnerable Components | `symfony/mime` memiliki CVE-2026-45070 | `composer update symfony/mime --with-all-dependencies`; `composer audit` bersih | Selesai |
| **A07** Authentication Failures | Login tanpa pembatasan percobaan; tidak ada session regeneration | `RateLimiter` 5 percobaan / 60 detik; `session()->regenerate()` saat login, `invalidate()` + `regenerateToken()` saat logout | Selesai |
| **A08** Data Integrity | — | Validasi enum status dan tanggal pada import; guard DB untuk assignment aktif; `composer audit` bersih | Sebagian (lihat 8.4) |
| **A09** Logging & Monitoring | Percobaan login gagal tidak tercatat | `Log::warning('Percobaan login gagal.', [...])` tanpa menyertakan password | Selesai |
| **A10** SSRF | Tidak ada permintaan HTTP keluar dari input pengguna | QR dibuat lokal (`simplesoftwareio/simple-qrcode`), tidak ada fetch URL eksternal | Tidak berlaku |

### 4.1 Detail kebijakan CSP (`c7e5a11`)

| Direktif | Nilai |
| --- | --- |
| `default-src` | `'self'` |
| `base-uri`, `form-action`, `frame-ancestors` | `'self'` |
| `object-src` | `'none'` |
| `script-src` | `'self'`, `'nonce-<per-request>'`, `'unsafe-eval'`, `blob:` |
| `style-src` | `'self'`, `'unsafe-inline'`, `https://fonts.googleapis.com` |
| `font-src` | `'self'`, `data:`, `https://fonts.gstatic.com` |
| `img-src` | `'self'`, `data:`, `blob:` |
| `media-src`, `worker-src` | `'self'`, `blob:` |
| `connect-src` | `'self'`, `blob:`, `data:` |

Nonce dibuat per request (`base64_encode(random_bytes(16))`), dipasang ke Vite lewat `Vite::useCspNonce()`, sehingga tag `@vite` otomatis ber-nonce. Inline script scanner di `resources/views/assets/scan.blade.php` diberi `nonce="{{ Vite::cspNonce() }}"`. Dev server `localhost:5173`/`127.0.0.1:5173` beserta `ws://` hanya diizinkan ketika `app()->environment('local')`.

### 4.2 Detail scoping lokasi (`c7e5a11`)

| Lapisan | Implementasi |
| --- | --- |
| Model user | `User::isAdmin()`, `User::visibleLocationId()`, `User::canAccessLocation()` |
| Query | `Asset::scopeVisibleTo()` — staff otomatis difilter `location_id`, admin tidak dibatasi |
| Otorisasi objek | `App\Policies\AssetPolicy` (`view`, `update`, `delete`) dipanggil lewat `authorize()` |
| Web | `AssetWebController`: `index`, `printLabels`, `scan` ter-scope; `show`, `edit`, `update`, `destroy` memakai policy |
| API | `AssetController`: `index`, `scan` ter-scope; `show`, `update`, `destroy`, `qrImage` memakai policy (aset luar lokasi → 403, scan kode aset luar lokasi → 404) |
| Laporan Web | `ReportWebController` memakai closure `$visible` pada `withCount`, `withSum`, `whereHas`, total aset, total valuasi, dan detail aset |
| Laporan API | `Api\ReportController`: `assetHistory` memakai `authorize('view', $asset)`; `stockByLocation` dan `disposals` memakai closure `$visible` |
| Dashboard Web & API | `Web\DashboardController` dan `Api\DashboardController` memakai `visibleTo()` / `whereHas('asset', $visible)` untuk semua agregat, hitungan per lokasi/kategori, dan aset terbaru |
| Daftar operasional Web | `OperationWebController`: assignments, mutations, maintenances, disposals di-scope lewat `whereHas('asset', visibleTo)`, termasuk daftar aset pada form |
| Daftar operasional API | `AssetAssignmentController`, `AssetMutationController`, `MaintenanceLogController`, `AssetDisposalController` di-scope dengan pola sama |
| Operasi tulis | Setiap operasi tulis (assign, return/check-in, mutasi, maintenance, complete, disposal) memanggil `authorize('update', $asset)` pada aset terkait — Web dan API |
| Master data lokasi | `AssetWebController`: dropdown lokasi difilter `availableLocations()`, validasi `location_id` dibatasi `Rule::exists` sesuai lokasi user, impor CSV hanya memakai lokasi yang terlihat |

---

## 5. Progress per Item (Checklist)

- [x] Semua endpoint `api/v1` memakai `auth:sanctum`
- [x] Service operasi aset terpusat + transaksi + `lockForUpdate`
- [x] Exception operasi yang seragam untuk API & Web
- [x] Constraint database satu assignment aktif per aset
- [x] Aturan hapus aset beriwayat disamakan antara Web dan API
- [x] Validasi enum status & tanggal pada import CSV
- [x] Satu generator QR (`Asset::qrSvg`)
- [x] Middleware role & pengelompokan route per role
- [x] Security header global + CSP nonce
- [x] Rate limit API `60,1` dan rate limit login
- [x] Sanctum expiry 7 hari, session 30 menit
- [x] Log percobaan login gagal
- [x] Proteksi ubah/hapus akun sendiri dan admin terakhir
- [x] `svg` dihapus dari whitelist upload logo
- [x] Whitelist karakter `asset_code` (request, web, CSV)
- [x] Upgrade `symfony/mime` (CVE-2026-45070)
- [x] Scoping aset per lokasi (query + policy + laporan Web)
- [x] Otorisasi per objek pada seluruh operasi tulis (Web & API)
- [x] Scoping lokasi untuk dashboard, daftar operasional, dan laporan API
- [x] Dropdown & validasi `location_id` dibatasi lokasi user
- [x] Impor CSV dipisah ke `App\Services\AssetCsvImporter`
- [ ] Pindahkan 4 blok `<style>` inline ke `resources/css/app.css`
- [ ] Verifikasi scan kamera di iOS (butuh HTTPS)
- [ ] Analisis statis (PHPStan/Larastan) — butuh persetujuan penambahan dependency

---

## 6. Riwayat Commit

| Commit | Tanggal | Pesan | File | Perubahan |
| --- | --- | --- | --- | --- |
| `cffec25` | 2026-10-29 | fix: validasi input scan dan detail hasil scan aset | — | — |
| `5db9ba8` | 2026-10-29 | build: rebuild frontend assets | — | — |
| `b037b87` | 2026-10-29 | refactor: pusatkan operasi aset di service dengan lock dan perketat integritas data | 21 | +446 / −335 |
| `0cfbc5e` | 2026-10-30 | fix: perketat otorisasi role, header keamanan, dan validasi input | 27 | +409 / −92 |
| `c7e5a11` | 2026-10-30 | feat: CSP nonce untuk Alpine/Vite dan scoping aset per lokasi | 9 | +262 / −18 |

### 6.1 File kunci per commit

**`b037b87`** — `app/Services/AssetOperationService.php` (baru), `app/Exceptions/AssetOperationException.php` (baru), seluruh controller operasi API & Web, `app/Models/Asset.php`, `routes/api.php`, migration `2026_10_29_101208_add_active_asset_guard_to_asset_assignments_table.php`, dan test pendukung.

**`0cfbc5e`** — `app/Http/Middleware/EnsureUserHasRole.php` (baru), `app/Http/Middleware/SecurityHeaders.php` (baru), `bootstrap/app.php`, `app/Http/Controllers/Controller.php`, `app/Http/Controllers/Web/UserWebController.php`, `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, `app/Http/Requests/Asset/*`, `routes/web.php`, `routes/api.php`, `config/session.php`, `config/sanctum.php`, `database/factories/UserFactory.php`, `composer.lock`, `tests/Feature/AuthorizationTest.php` (baru).

**`c7e5a11`** — `app/Http/Middleware/SecurityHeaders.php`, `app/Policies/AssetPolicy.php` (baru), `app/Models/User.php`, `app/Models/Asset.php`, `app/Http/Controllers/Web/AssetWebController.php`, `app/Http/Controllers/Web/ReportWebController.php`, `app/Http/Controllers/Api/AssetController.php`, `resources/views/assets/scan.blade.php`, `tests/Feature/AuthorizationTest.php`.

---

## 7. Bukti Verifikasi

Perintah dan hasil terakhir (setelah perbaikan pasca-review, belum di-commit):

```text
php artisan test --compact
  Tests:    70 passed (373 assertions)

vendor/bin/pint --dirty --format agent
  {"tool":"pint","result":"passed"}

composer audit
  No security vulnerability advisories found.
```

Cakupan `tests/Feature/AuthorizationTest.php` — **17 test**:
- header keamanan terpasang, termasuk pola `nonce-...` pada `script-src`
- staff ditolak pada route tulis aset, master data, dan manajemen user
- teknisi dapat mengakses route tulis aset namun tidak master data
- staff tetap dapat membaca katalog aset, dashboard, dan laporan
- akses API read-only untuk staff dan pembatasan tulis untuk teknisi
- admin tidak dapat menghapus akun sendiri atau mengubah role sendiri
- `asset_code` menolak karakter tidak aman
- staff hanya melihat aset pada lokasinya, aset cabang lain → 403
- admin melihat aset dari semua lokasi meski memiliki `location_id`
- API list/read/scan ter-scope sesuai lokasi
- daftar operasional (Web & API) serta `reports/stock-by-location` ter-scope per lokasi
- teknisi ditolak (403) saat assign/mutasi/maintenance/disposal aset cabang lain, baik lewat API maupun Web, dan tetap diizinkan untuk aset di lokasinya
- `reports/assets/{id}/history` mengembalikan 403 untuk aset luar lokasi

Cakupan `tests/Feature/AuthenticationSecurityTest.php` — **7 test**: redirect guest, render halaman login, login valid/invalid, rate limit percobaan login, logout + invalidasi session, redirect user terautentikasi dari halaman login.

Test lain yang relevan: `AssetQrPrintTest`, `AssetOperationsTest`, `AssetApiTest`, `AssetFullLifecycleTest`, `DashboardAndReportTest`, `WebFrontendFullOperationsTest`.

---

## 8. Sisa Pekerjaan & Risiko yang Diterima

### 8.1 `'unsafe-inline'` pada `style-src` — risiko rendah
Masih ada empat blok `<style>` inline yang mencegah penghapusan `'unsafe-inline'`:

| File | Baris |
| --- | --- |
| `resources/views/layouts/app.blade.php` | 12 |
| `resources/views/auth/login.blade.php` | 14 |
| `resources/views/assets/print.blade.php` | 7 |
| `resources/views/welcome.blade.php` | 15 |

**Rencana:** pindahkan ke `resources/css/app.css` (atau partial terpisah) lalu hapus `'unsafe-inline'` dari `style-src`. Catatan: saat ini hanya mengizinkan CSS inline, bukan script, sehingga dampak eksekusi kode tidak ada.

### 8.2 `'unsafe-eval'` pada `script-src` — kebutuhan fungsional
Alpine.js mengevaluasi ekspresi `x-data`/`@click` menggunakan `new Function`, sehingga `'unsafe-eval'` diperlukan. Alternatif jangka panjang: migrasi ke Alpine CSP build (`@alpinejs/csp`) yang tidak membutuhkan `'unsafe-eval'`.

### 8.3 Konfigurasi cookie produksi
`SESSION_ENCRYPT=false` dan `SESSION_SECURE_COOKIE` masih mengikuti environment. Untuk produksi, pastikan:

```dotenv
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
```

### 8.4 Scoping lokasi — sudah menyeluruh
Scoping berlaku pada aset (Web & API), laporan Web & API, dashboard Web & API, daftar operasional Web & API, dan seluruh operasi tulis. Semua memakai pola yang sama: `Asset::scopeVisibleTo()`, closure `$visible`, dan `authorize()` pada aset terkait.

Satu area belum di-scope dan sengaja dibiarkan:

| Area | File | Alasan |
| --- | --- | --- |
| Daftar lokasi master data | `app/Http/Controllers/Api/LocationController.php` | Endpoint hanya dapat diakses `role:admin` (sudah dibatasi di `routes/api.php`), sehingga daftar lokasi global memang diperlukan admin |

### 8.5 Verifikasi lingkungan nyata
- Scan kamera perlu diuji di iOS Safari melalui HTTPS (akses kamera ditolak pada HTTP non-localhost).
- Label QR perlu diverifikasi pada printer produksi (ukuran & margin).

### 8.6 Cakupan test yang masih kurang
- Proteksi "admin terakhir" (`UserWebController::isLastAdmin()`) belum memiliki test khusus; saat ini hanya diverifikasi manual.
- Constraint database `active_asset_id` belum diuji dengan skenario dua assignment aktif bersamaan (diuji tidak langsung lewat operasi service).
- Operasi tulis per role sudah diuji untuk kasus lintas lokasi; kombinasi role lain (staff pada setiap endpoint tulis) baru diuji sebagian.

### 8.7 Analisis statis belum ada
`composer.json` belum memuat PHPStan/Larastan, sehingga kesalahan tipe hanya tertangkap lewat test. Penambahan `larastan/larastan` perlu persetujuan karena mengubah dependency pengembangan.

---

## 9. Catatan Deployment & Migrasi

- Migration guard assignment bersifat **driver-aware**: `virtualAs` untuk SQLite (hanya mendukung generated column VIRTUAL via `ALTER`) dan `storedAs` untuk MySQL/MariaDB (index unik tidak dapat dipasang pada kolom VIRTUAL).
- Untuk database produksi yang sudah berisi data, jalankan versi `STORED` + rebuild penuh (bukan `ALTER` kolom `VIRTUAL`) agar unique index dapat dibuat. Pastikan tidak ada lebih dari satu assignment `active` per aset sebelum migration dijalankan, karena unique index akan gagal dibuat.
- CSP mengizinkan `localhost:5173` hanya pada environment `local`; tidak perlu konfigurasi tambahan di produksi selama aset dibangun dengan `npm run build`.

---

## 10. Referensi Berkas Kunci

| Kebutuhan | Berkas |
| --- | --- |
| Keamanan header & CSP | `app/Http/Middleware/SecurityHeaders.php` |
| Otorisasi role | `app/Http/Middleware/EnsureUserHasRole.php`, `bootstrap/app.php`, `routes/web.php`, `routes/api.php` |
| Otorisasi per objek | `app/Policies/AssetPolicy.php`, `app/Http/Controllers/Controller.php` |
| Scoping lokasi | `app/Models/User.php`, `app/Models/Asset.php` (`scopeVisibleTo`) |
| Operasi aset | `app/Services/AssetOperationService.php`, `app/Exceptions/AssetOperationException.php` |
| Integritas data | `database/migrations/2026_10_29_101208_add_active_asset_guard_to_asset_assignments_table.php` |
| Generator QR | `app/Models/Asset.php` (`qrSvg`) |
| Impor CSV | `app/Services/AssetCsvImporter.php` |
| Uji keamanan | `tests/Feature/AuthorizationTest.php`, `tests/Feature/AuthenticationSecurityTest.php` |
