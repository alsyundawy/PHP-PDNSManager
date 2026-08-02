# Laporan & Standar Review Kode (Code Review Guidelines)

Dokumen ini menjelaskan kriteria penjaminan kualitas kode (_Quality Assurance_), standar pengujian, dan prosedur peninjauan kode (_code review_) pada proyek **PHP-PDNSManager Enterprise Edition**.

---

## 📊 Status Kualitas Kode Proyek

- **Tanggal Evaluasi**: 2026-08-02
- **Status Proyek**: 🟢 **PRODUCTION READY**
- **Standar Bahasa**: PHP `>= 8.1` (Strict Types Enforced)
- **Cakupan Pengujian (Test Coverage)**: `> 90%`

---

## 🛡️ Aturan Static Analysis & Linter

Setiap berkas PHP dalam repositori wajib memenuhi ambang batas analisis statis berikut:

| Alat (Tool)            | Standar / Level             | Konfigurasi         | Perintah Eksekusi |
| :--------------------- | :-------------------------- | :------------------ | :---------------- |
| **PHPStan**            | Level `Max` (Level 8/9)     | `phpstan.neon`      | `make stan`       |
| **Psalm**              | Level `1` (Strict)          | `psalm.xml`         | `make psalm`      |
| **PHPCS**              | PSR-12 & Slevomat Standards | `phpcs.xml`         | `make cs`         |
| **Rector**             | PHP 8.1 Code Modernization  | `rector.php`        | `make fix`        |
| **Trunk / Trufflehog** | Secret Leak & Linting       | `.trunk/trunk.yaml` | `trunk check`     |

---

## 📋 Checklist Peninjauan Kode (Code Reviewer Checklist)

Saat melakukan peninjauan Pull Request (PR), _reviewer_ wajib memastikan poin-poin berikut terpenuhi:

### 1. Deklarasi & Strict Typing

- [ ] Berkas PHP memiliki `declare(strict_types=1);` di baris pertama setelah tag `<?php`.
- [ ] Seluruh parameter fungsi dan return type terdeklarasi secara eksplisit.
- [ ] Tidak menggunakan tipe `mixed` atau `any` kecuali benar-benar diperlukan dan terdokumentasi via PHPDoc `@param` / `@return`.

### 2. Keamanan & Sanitasi

- [ ] Tidak ada pencetakan masukan pengguna secara mentah tanpa eksekusi escaping atau pembersihan.
- [ ] Seluruh query database lokal menggunakan Prepared Statements via PDO Repository.
- [ ] Mutasi data Web GUI dilindungi oleh `CsrfProtectionMiddleware`.
- [ ] Rute API memvalidasi hak akses peran pengguna via `RbacMiddleware`.

### 3. Logika Bisnis & DTO

- [ ] Logika bisnis diisolasi di `app/Services`, bukan ditumpuk di Controller.
- [ ] Transfer data antar lapisan menggunakan objek **DTO (Data Transfer Object)** immutable.
- [ ] Komunikasi dengan PowerDNS Authoritative Server dilakukan melalui `PowerDNSClient` resource abstraction.

### 4. Pengujian Unit & Integrasi

- [ ] Setiap fitur baru disertai dengan Unit Test atau Integration Test PHPUnit.
- [ ] Pengujian mencakup skenario sukses (_happy path_) dan skenario kegagalan/penanganan pengecualian (_edge cases & exceptions_).
- [ ] Seluruh pengujian lolos tanpa _warning_ atau _deprecation notice_:
    ```bash
    ./vendor/bin/phpunit
    ```
