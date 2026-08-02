# Pre-Deployment & Production Hardening Checklist

Gunakan daftar periksa ini sebelum melakukan rilis **PHP-PDNSManager Enterprise Edition** ke lingkungan produksi (_production_).

---

## 1. ⚙️ Konfigurasi Environment & Aplikasi

- [ ] **APP_ENV**: Dipastikan bernilai `production`.
- [ ] **APP_DEBUG**: Dipastikan bernilai `false` (Wajib untuk mencegah kebocoran informasi kredensial).
- [ ] **JWT_SECRET**: Dikonfigurasi dengan string acak kriptografis acak yang panjang (minimum 64 karakter).
- [ ] **PDNS_API_KEY**: Menggunakan kunci rahasia unik yang tidak digunakan pada aplikasi lain.
- [ ] **APP_URL**: Dipastikan menggunakan skema `https://`.

---

## 2. 🛡️ Keamanan Web Server & Jaringan

- [ ] **Sertifikat SSL/TLS**: Sertifikat HTTPS aktif (Let's Encrypt / Commercial SSL) dengan protokol minimal TLS 1.2 / TLS 1.3.
- [ ] **Nginx / Apache Security Headers**: Header `X-Frame-Options`, `X-Content-Type-Options`, `HSTS`, dan `Content-Security-Policy` aktif.
- [ ] **Isolasi PowerDNS REST API**: Port REST API PowerDNS (`8081`) diproteksi firewall (`ufw` / `iptables`) sehingga hanya dapat diakses oleh alamat IP internal server PHP-PDNSManager.
- [ ] **Blokir Berkas Sensitif**: Memastikan akses publik ke berkas `.env`, `.git`, `composer.json`, `storage/logs` diblokir oleh web server.

---

## 3. 🐘 Optimasi PHP Runtime

- [ ] **OPcache Aktif**:
    ```ini
    opcache.enable=1
    opcache.enable_cli=1
    opcache.memory_consumption=128
    opcache.interned_strings_buffer=16
    opcache.max_accelerated_files=10000
    opcache.validate_timestamps=0
    ```
- [ ] **Composer Autoloader**: Dibuat dengan optimasi produksi:
    ```bash
    composer install --no-dev --optimize-autoloader
    ```
- [ ] **Ekstensi PHP Wajib**: Dipastikan ekstensi `sodium`, `pdo_mysql`/`pdo_pgsql`, `json`, `curl`, `mbstring` aktif.

---

## 4. 🗄️ Database & Keamanan Sesi

- [ ] **Database Migration**: Berkas migrasi database telah selesai dijalankan (`php bin/console migrate`).
- [ ] **Kata Sandi Default**: Kata sandi Administrator bawaan telah diubah ke kata sandi baru yang kuat.
- [ ] **Session Secure Flag**: Variabel `SESSION_SECURE_COOKIE=true` di `.env` telah aktif.

---

## 5. 🔍 Verifikasi Pasca Deployment (Post-Deployment Check)

- [ ] **Uji Login GUI**: Berhasil masuk ke Web GUI menggunakan HTTPS.
- [ ] **Koneksi PowerDNS**: Dashboard menampilkan status koneksi PowerDNS **Connected**.
- [ ] **Uji Perubahan Zone**: Mencoba membuat record DNS pengujian dan memverifikasi serial SOA bertambah.
- [ ] **Uji Audit Log**: Memastikan aksi pembuatan record tercatat di menu Audit Trail.
- [ ] **Uji REST API**: Menguji akses endpoint `/api/v1/zones` menggunakan token JWT.
