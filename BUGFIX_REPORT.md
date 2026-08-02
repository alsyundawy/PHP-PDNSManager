# PHP-PDNSManager — Laporan Perbaikan Komprehensif
**Tanggal Analisis:** 3 Agustus 2026  
**Sumber:** MegaLinter 9.6.0 + Analisis Manual Kode  
**Repo:** https://github.com/alsyundawy/PHP-PDNSManager

---

## Ringkasan Eksekutif

| Kategori | Jumlah Temuan |
|----------|--------------|
| Bug Kritis | 6 |
| Bug Minor  | 5 |
| Keamanan KRITIS | 5 |
| Keamanan Moderat | 4 |
| CI/CD & Build | 3 |
| Style/Lint | 4 |
| **Total** | **27** |

---

## 🔴 BUG KRITIS

### BUG-01: `Response.php` — PSR-7 Immutability Violation
- **File:** `app/Core/Response.php`
- **Masalah:** `json()`, `html()`, `redirect()` memanggil `getBody()->write()` lalu `withStatus()` pada `$this`, bukan pada instance baru. PSR-7 mensyaratkan immutability — `withHeader()`/`withStatus()` harus dikembalikan sebagai objek baru.
- **Akibat:** Header tidak pernah benar-benar ter-set, respons selalu status default 200.
- **Fix:** Buat `new self($status, [...headers...])` untuk setiap metode.
- **Patch:** `app/Core/Response.php`

### BUG-02: `ZoneService.php` — `FILTER_VALIDATE_DOMAIN` Tidak Valid
- **File:** `app/Services/DNS/ZoneService.php`, baris validasi domain
- **Masalah:** `filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)` — konstanta `FILTER_VALIDATE_DOMAIN` tidak ada di PHP; yang ada adalah `FILTER_VALIDATE_URL`. Hasilnya selalu `false`, semua nama zone ditolak.
- **Akibat:** Tidak bisa membuat zone DNS apapun via API.
- **Fix:** Ganti dengan regex FQDN yang tepat.
- **Patch:** `app/Services/DNS/ZoneService.php`

### BUG-03: `RecordService.php` — TTL Validation Logic Error
- **File:** `app/Services/DNS/RecordService.php`
- **Masalah:** `!isset($data['ttl']) || (int) $data['ttl'] < 0` — TTL = 0 valid dalam DNS (untuk TTL dinamis/proxy), tapi `< 0` akan menolak juga ketika `!isset`, sehingga field TTL wajib ada padahal seharusnya opsional dengan default 3600.
- **Fix:** Ubah ke `isset($data['ttl']) && (int) $data['ttl'] < 0`.
- **Patch:** `app/Services/DNS/RecordService.php`

### BUG-04: `AuditLogRepository.php` — PDO LIMIT Named Parameter Bug
- **File:** `app/Repositories/AuditLogRepository.php`
- **Masalah:** `LIMIT :limit` dengan named placeholder PDO tidak didukung oleh semua driver PDO (terutama MySQL). PDO mengirim LIMIT sebagai string-quoted, menghasilkan `LIMIT '20'` yang menyebabkan error SQL.
- **Fix:** Cast ke int dan embed langsung di SQL string.
- **Patch:** `app/Repositories/AuditLogRepository.php`

### BUG-05: `Logger.php` — 97 PHPStan Errors: Class Not Found
- **File:** `app/Core/Logger.php`
- **Masalah:** Seluruh referensi ke `Monolog\\Logger`, `Monolog\\Handler\\*`, `Monolog\\Formatter\\*` tidak menggunakan `use` statement. PHPStan melaporkan 97 error terkait class tidak ditemukan.
- **Fix:** Tambah semua `use` alias yang diperlukan.
- **Patch:** `app/Core/Logger.php`

### BUG-06: `RbacMiddleware.php` — Namespace/Path Mismatch
- **File:** `app/Middleware/RbacMiddleware.php`
- **Masalah:** Namespace deklarasi menggunakan `App\Core\Middleware` tetapi file berada di `app/Middleware/` — PSR-4 autoloader tidak akan menemukan class ini, menyebabkan `ClassNotFoundException` saat runtime.
- **Fix:** Pindahkan file ke `app/Core/Middleware/` agar sesuai namespace.
- **Patch:** `app/Core/Middleware/RbacMiddleware.php`

---

## 🟡 BUG MINOR

### BUG-07: `ZoneApiController.php` — Unhandled Exceptions
- **File:** `app/Controllers/Api/V1/ZoneApiController.php`
- **Masalah:** Method `show()`, `update()`, `destroy()`, `clone()`, `check()`, `export()` tidak memiliki try-catch. `PowerApiException` akan propagate sebagai 500 Internal Server Error dengan stack trace.
- **Fix:** Tambahkan try-catch pada semua method.
- **Patch:** `app/Controllers/Api/V1/ZoneApiController.php`

### BUG-08: `MiddlewarePipeline.php` — Mutating Middleware Stack
- **File:** `app/Core/Middleware/MiddlewarePipeline.php`
- **Masalah:** `array_shift($this->middlewares)` memodifikasi array secara in-place. Jika pipeline digunakan kembali (mis. untuk subrequest), stack sudah kosong.
- **Fix:** Gunakan index counter atau clone array sebelum shift.

### BUG-09: `InitialSeeder.php` — PHPCS PSR-12 Violation
- **File:** `database/seeders/InitialSeeder.php` baris 31–32
- **Masalah:** 3 error PHPCS: opening parenthesis multi-line function call tidak di akhir baris, indentasi tidak benar.
- **Fix:** Jalankan `phpcbf database/seeders/InitialSeeder.php`.

### BUG-10: `RoleRepository.php` — Silent Exception Swallowing
- **File:** `app/Repositories/RoleRepository.php`
- **Masalah:** Semua method membungkus query dalam `try-catch (\Throwable)` yang mengembalikan `[]` atau `null` — error database nyata (koneksi putus, tabel tidak ada) akan disembunyikan sepenuhnya.
- **Fix:** Log exception sebelum return default, atau biarkan throw.

### BUG-11: `database/migrations/001_initial_schema.sql` — Syntax Error
- **File:** `database/migrations/001_initial_schema.sql` baris 71
- **Masalah:** TSQLLint error `invalid-syntax: Incorrect syntax near '.'` — tanda titik di baris 71 tidak valid untuk SQL standar.
- **Fix:** Periksa dan koreksi statement SQL di baris 71.

---

## 🔴 KEAMANAN KRITIS

### SEC-01: `AuthService.php` — Session Fixation Attack
- **File:** `app/Services/Auth/AuthService.php`
- **Masalah:** Session ID tidak di-regenerate setelah login berhasil. Penyerang dapat menanamkan session ID sebelum login, lalu menggunakannya setelah korban login (session fixation).
- **CVSS:** High (7.5)
- **Fix:** Panggil `$this->session->regenerate()` setelah autentikasi berhasil.
- **Patch:** `app/Services/Auth/AuthService.php`

### SEC-02: `AuthService.php` — User Enumeration via Timing
- **File:** `app/Services/Auth/AuthService.php`
- **Masalah:** Jika user tidak ditemukan, fungsi langsung return `null` tanpa menjalankan `password_verify()`. Perbedaan waktu respons memungkinkan enumeration username.
- **Fix:** Selalu jalankan `password_verify()` dummy jika user tidak ada.
- **Patch:** `app/Services/Auth/AuthService.php`

### SEC-03: `AuthService.php` — TOTP Timing Attack
- **File:** `app/Services/Auth/AuthService.php`
- **Masalah:** `verifyTotp()` menggunakan `==` untuk membandingkan kode TOTP — rentan terhadap timing attack.
- **Fix:** Ganti dengan `hash_equals($expected, $code)`.
- **Patch:** `app/Services/Auth/AuthService.php`

### SEC-04: `ContentSecurityPolicyMiddleware.php` — `unsafe-inline` di script-src
- **File:** `app/Core/Middleware/ContentSecurityPolicyMiddleware.php`
- **Masalah:** CSP header mengizinkan `'unsafe-inline'` pada `script-src`, yang membatalkan proteksi XSS dari CSP sepenuhnya.
- **CVSS:** High (8.1)
- **Fix:** Hapus `'unsafe-inline'`, implementasi nonce-based CSP.
- **Patch:** `app/Core/Middleware/ContentSecurityPolicyMiddleware.php`

### SEC-05: `AuditLogService.php` — Sensitive Data Logged, IP Spoofable
- **File:** `app/Services/AuditLogService.php`
- **Masalah:** (1) Payload request di-log mentah — bisa termasuk password/token. (2) IP address dari `$_SERVER['REMOTE_ADDR']` tanpa validasi untuk environment reverse proxy.
- **Fix:** Sanitize payload (redact field sensitif). Tambahkan konfigurasi trust proxy yang eksplisit.
- **Patch:** `app/Services/AuditLogService.php`

---

## 🟠 KEAMANAN MODERAT

### SEC-06: `PowerDNSClient.php` — SSL Verification Tidak Dikonfigurasi
- **File:** `app/Services/PowerDNS/PowerDNSClient.php`
- **Masalah:** Tidak ada konfigurasi `verify` pada Guzzle client — default Guzzle adalah verify=true tapi tidak eksplisit; jika ada env yang mengubahnya, bisa disable TLS verification tanpa disadari.
- **Fix:** Set `'verify' => true` secara eksplisit dengan konfigurasi dari `.env`.
- **Patch:** `app/Services/PowerDNS/PowerDNSClient.php`

### SEC-07: `CsrfService.php` — CSRF Token Tidak Di-Rotate
- **File:** `app/Services/Auth/CsrfService.php`
- **Masalah:** Token CSRF tidak di-rotate setelah digunakan — memungkinkan CSRF token reuse dari request yang bocor (mis. via Referer header atau browser history).
- **Fix:** Tambah method `rotate()` yang dipanggil setelah validasi berhasil.
- **Patch:** `app/Services/Auth/CsrfService.php`

### SEC-08: GitHub Actions — `write-all` Permissions (CKV2_GHA_1)
- **File:** `.github/workflows/ci.yml`, `.github/workflows/codeql.yml`
- **Masalah:** `permissions: write-all` di level top — memberikan akses write penuh ke semua resource repository dari workflow CI. Pelanggaran prinsip least privilege.
- **Fix:** Ganti dengan `permissions: contents: read`.
- **Patch:** `.github/workflows/ci.yml`

### SEC-09: OpenAPI — Global Security Tidak Terdefinisi (CKVOPENAPI4)
- **File:** `docs/openapi.yaml`
- **Masalah:** Tidak ada field `security` global di spec OpenAPI, artinya endpoint dianggap publik/tidak terautentikasi oleh tooling API gateway.
- **Fix:** Tambahkan `security: [{ApiKeyAuth: []}]` global.
- **Patch:** `docs/openapi.yaml`

---

## 🔵 CI/CD & BUILD

### CI-01: `betterleaks` — 8 Generic API Key Detections
- **Sumber:** MegaLinter betterleaks
- **Masalah:** 8 file terdeteksi mengandung pola generic-api-key (termasuk dalam `.php-cs-fixer.cache`). Perlu verifikasi manual apakah ini false positive dari cache file.
- **Fix:** Tambahkan `.php-cs-fixer.cache` ke `.gitignore`. Audit file yang disebutkan.

### CI-02: `grype` — 2 Vulnerable GitHub Actions
- **Sumber:** MegaLinter grype
- **Masalah:** `super-linter/super-linter@v8` (GHSA-r79c-pqj3-577x, High) dan `shivammathur/setup-php@v2` (GHSA-5wxr-w449-57cm, Medium).
- **Fix:** Update ke versi terbaru action dependencies.

### CI-03: `jscpd` — 7 Code Duplications
- **Sumber:** MegaLinter jscpd (1.4% duplikasi)
- **Masalah:** Duplikasi antara `DNSSECApiController` dan `DNSSECController`, duplikasi dalam `UserRepository`, dan antara `RecordService` dan `RecordResource`.
- **Fix:** Ekstrak logic ke base class atau trait.

---

## 🟢 STYLE & LINT

### LINT-01: PHP phpcs — 3 Errors di InitialSeeder.php
- Multi-line function call formatting tidak sesuai PSR-12. Jalankan `phpcbf`.

### LINT-02: JavaScript standard — `no-new` di resources/js/app.js:65
- `new SomeClass()` digunakan untuk side effect saja. Simpan ke variabel.

### LINT-03: CSS stylelint — 4 Errors
- `declaration-block-single-line-max-declarations` — terlalu banyak deklarasi dalam satu baris CSS.

### LINT-04: Broken Links (lychee) — 4 URL 404
- `CHANGELOG.md` dan `README.md` memiliki 4 link yang mengembalikan 404 (tag v1.0.0 dan logo image belum ada di repo).

---

## Prioritas Perbaikan

| Prioritas | Issue | Patch File |
|-----------|-------|-----------|
| P0 | BUG-02: FILTER_VALIDATE_DOMAIN tidak valid | ZoneService.php |
| P0 | SEC-01: Session Fixation | AuthService.php |
| P0 | SEC-04: unsafe-inline CSP | ContentSecurityPolicyMiddleware.php |
| P1 | BUG-01: PSR-7 Response immutability | Response.php |
| P1 | BUG-04: PDO LIMIT bug | AuditLogRepository.php |
| P1 | SEC-03: TOTP timing attack | AuthService.php |
| P1 | SEC-05: Sensitive data in logs | AuditLogService.php |
| P2 | BUG-05/06: PHPStan errors | Logger.php, RbacMiddleware.php |
| P2 | SEC-08: GitHub Actions permissions | ci.yml |
| P3 | CI-01/02/03: betterleaks, grype, jscpd | Various |

---

*Laporan ini dihasilkan oleh analisis otomatis MegaLinter 9.6.0 dikombinasikan dengan review manual kode.*
