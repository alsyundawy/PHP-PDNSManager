# Kebijakan Keamanan (Security Policy) PHP-PDNSManager Enterprise Edition

Keamanan adalah prioritas tertinggi dalam perancangan **PHP-PDNSManager Enterprise Edition**. Dokumen ini menjelaskan mekanisme proteksi keamanan yang diterapkan dalam aplikasi, serta tata cara pelaporan kerentanan keamanan (_vulnerability disclosure_).

---

## 🛡️ Kebijakan Pelaporan Kerentanan (Vulnerability Disclosure)

Jika Anda menemukan celah atau kerentanan keamanan pada proyek ini, **mohon untuk tidak melaporkannya melalui GitHub Issue publik**.

### Prosedur Pelaporan

1. Kirimkan laporan detail melalui email ke: `security@alsyundawy.dev`.
2. Sertakan informasi berikut dalam laporan Anda:
    - Deskripsi celah keamanan dan potensi dampaknya.
    - Langkah-langkah untuk mereproduksi celah (_Proof of Concept_ / PoC).
    - Versi PHP-PDNSManager dan PowerDNS yang terpengaruh.
3. Tim Keamanan kami akan memberikan konfirmasi tanda terima dalam kurun waktu **24 jam** dan berupaya memberikan perbaikan (_patch_) dalam kurun waktu **7 hari kerja**.

---

## 🔐 Arsitektur Proteksi Keamanan

PHP-PDNSManager menerapkan pendekatan **Defense-in-Depth** melalui beberapa lapisan proteksi otomatis:

```text
[ Incoming Request ]
        │
        ▼
┌────────────────────────────────────────────────────────┐
│  ContentSecurityPolicyMiddleware                       │  ---> CSP Headers & Frame Protections
└───────────────────┬────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────────┐
│  RateLimitMiddleware                                   │  ---> Brute-Force & Denial-of-Service Defense
└───────────────────┬────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────────┐
│  CsrfProtectionMiddleware                              │  ---> Cross-Site Request Forgery Prevention
└───────────────────┬────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────────┐
│  Authentication & RbacMiddleware                       │  ---> Sodium Hashing, JWT / Session & RBAC
└───────────────────┬────────────────────────────────────┘
                    │
                    ▼
┌────────────────────────────────────────────────────────┐
│  AuditLogMiddleware                                    │  ---> Immutable Audit Trail Logging
└────────────────────────────────────────────────────────┘
```

### 1. Enkripsi & Hashing Kata Sandi Modern

- Menggunakan pustaka native PHP `ext-sodium` (Argon2id) untuk pembentukan hash kata sandi pengguna.
- Kunci rahasia API Key dan Sesi diacak menggunakan generator nomor acak kriptografis aman (`random_bytes()`).

### 2. Proteksi Cross-Site Request Forgery (CSRF)

- Diterapkan melalui `CsrfProtectionMiddleware`.
- Setiap rute Web GUI bertipe mutasi (`POST`, `PUT`, `PATCH`, `DELETE`) wajib menyertakan token CSRF unik per sesi.
- Token diverifikasi menggunakan pembandingan waktu konstan (`hash_equals()`) untuk mencegah serangan _Timing Attack_.

### 3. Content Security Policy (CSP) & Header Keamanan

`ContentSecurityPolicyMiddleware` secara otomatis menyuntikkan header proteksi browser pada setiap respon HTTP:

```http
Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; frame-ancestors 'none';
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
```

### 4. Rate Limiting Middleware

- Mencegah serangan _Brute-Force Login_ dan _Denial of Service_ (DoS).
- Melacak jumlah permintaan per IP dalam jendela waktu tertentu.
- Mengembalikan HTTP Status `429 Too Many Requests` saat batas ambang terlampaui.

### 5. Audit Logging & Non-Repudiation

- Setiap aksi sensitif (pembuatan zone, penghapusan record, regenerasi DNSSEC key, login pengguna) dicatat secara otomatis oleh `AuditLogService`.
- Catatan audit merekam: `user_id`, `ip_address`, `action`, `resource_type`, `resource_id`, `payload_diff`, dan `timestamp`.

### 6. Role-Based Access Control (RBAC)

Aplikasi menerapkan pembatasan hak akses ketat berdasarkan peran pengguna:

| Peran (Role)      | Hak Akses                                                                      |
| :---------------- | :----------------------------------------------------------------------------- |
| **Administrator** | Akses penuh seluruh fitur, manajemen pengguna, konfigurasi sistem, dan DNS.    |
| **Operator**      | CRUD Zone dan DNS Record, kelola DNSSEC. Tidak dapat mengubah pengguna/sistem. |
| **Viewer**        | Akses _read-only_ untuk melihat Zone, Record, dan log audit.                   |
