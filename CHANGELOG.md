# Catatan Perubahan (Changelog)

Semua perubahan penting pada proyek **PHP-PDNSManager Enterprise Edition** akan dicatat dalam berkas ini.

Format berkas ini didasarkan pada [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), dan proyek ini mematuhi [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Planned Features

- Dukungan parsial untuk sinkronisasi DNS multi-server cluster.
- Integrasi webhook notification untuk perubahan record kritis.

---

## [1.0.0] - 2026-08-02

### Fitur Utama Ditambahkan

- **Core Architecture**:
    - Arsitektur berbasis PHP 8.1+ dengan integrasi penuh standar PSR (PSR-7, PSR-11, PSR-14, PSR-15, PSR-18).
    - Middleware Pipeline (`MiddlewarePipeline`) untuk penanganan request HTTP terisolasi.
- **PowerDNS Integration**:
    - `PowerDNSClient` berbasis Guzzle HTTP untuk komunikasi berkecepatan tinggi dengan PowerDNS REST API.
    - Abstraksi resource untuk `ZoneResource`, `RecordResource`, `CryptokeyResource`, dan `ServerResource`.
- **DNS Management Features**:
    - Manajemen DNS Zone lengkap (Native, Master, Slave) dengan kalkulasi otomatis SOA Serial (`YYYYMMDDNN`).
    - Manajemen DNS Records komprehensif (`A`, `AAAA`, `CNAME`, `MX`, `TXT`, `NS`, `SRV`, `CAA`, `PTR`).
    - Otomatisasi DNSSEC Cryptokeys (KSK/ZSK) dan ekstraksi DS Record.
- **Security & Protection Engine**:
    - Hashing kata sandi modern menggunakan ekstensi PHP `Sodium` (Argon2id/Ed25519).
    - Autentikasi berbasis Session dan JSON Web Tokens (JWT).
    - Proteksi Cross-Site Request Forgery (`CsrfProtectionMiddleware`).
    - Manajemen Header Content Security Policy (`ContentSecurityPolicyMiddleware`).
    - Pembatas Laju Akses (`RateLimitMiddleware`) untuk mencegah serangan Brute-Force.
    - Audit Trail Engine (`AuditLogService` & `AuditLogMiddleware`) untuk merekam seluruh jejak aktivitas pengguna.
- **RESTful API V1**:
    - Endpoint REST API V1 untuk Zones (`/api/v1/zones`), Records (`/api/v1/records`), dan DNSSEC (`/api/v1/dnssec`).
    - Format respon JSON konsisten berstandar RFC 7807 (Problem Details).
- **Quality Assurance & Tooling**:
    - Pengujian terintegrasi menggunakan PHPUnit 10 (Unit, Feature, Integration, API).
    - Static Analysis ketat menggunakan PHPStan (level max), Psalm (level 1), PHP CodeSniffer (PSR-12), dan Rector.
    - Dukungan otomatisasi melalui `Makefile` dan Trunk.

### Kebijakan Keamanan Versi 1.0.0

- Penerapan enkripsi Sodium dan CSRF token wajib untuk seluruh rute yang mengubah data (POST, PUT, PATCH, DELETE).
- Isolasi penuh kredensial API PowerDNS melalui konfigurasi `.env`.

---

[Unreleased]: https://github.com/alsyundawy/PHP-PDNSManager/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/alsyundawy/PHP-PDNSManager/releases/tag/v1.0.0
