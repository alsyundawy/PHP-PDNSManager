# Dokumentasi Konfigurasi PHP-PDNSManager Enterprise Edition

Dokumen ini menjelaskan seluruh variabel konfigurasi yang tersedia pada **PHP-PDNSManager Enterprise Edition**. Konfigurasi aplikasi dikelola melalui berkas `.env` di direktori utama proyek dan dimuat menggunakan pustaka `vlucas/phpdotenv`.

---

## 📄 Berkas Environment (`.env`)

Seluruh variabel konfigurasi dibaca saat bootstrapping aplikasi melalui `App\Core\Config`.

### 1. Konfigurasi Dasar Aplikasi (Application Settings)

| Variabel       | Tipe    | Default            | Deskripsi                                                                      |
| :------------- | :------ | :----------------- | :----------------------------------------------------------------------------- |
| `APP_NAME`     | String  | `PHP-PDNSManager`  | Nama aplikasi yang ditampilkan pada Web GUI dan Header API.                    |
| `APP_ENV`      | String  | `production`       | Lingkungan aplikasi (`production`, `staging`, `local`, `testing`).             |
| `APP_DEBUG`    | Boolean | `false`            | Menampilkan stack trace detail saat terjadi error (Wajib `false` di produksi). |
| `APP_URL`      | String  | `http://localhost` | URL utama akses aplikasi untuk pembentukan tautan & redirect.                  |
| `APP_TIMEZONE` | String  | `UTC`              | Zona waktu standar sistem (misal: `Asia/Jakarta`).                             |

---

### 2. Integrasi PowerDNS Authoritative Server

| Variabel          | Tipe    | Default                 | Deskripsi                                                                |
| :---------------- | :------ | :---------------------- | :----------------------------------------------------------------------- |
| `PDNS_API_URL`    | String  | `http://127.0.0.1:8081` | URL basis REST API PowerDNS Authoritative Server.                        |
| `PDNS_API_KEY`    | String  | _(Wajib diisi)_         | Secret API Key yang dikonfigurasi di `pdns.conf` (`api-key`).            |
| `PDNS_SERVER_ID`  | String  | `localhost`             | Identitas server PowerDNS (default: `localhost`).                        |
| `PDNS_TIMEOUT`    | Integer | `5`                     | Batas waktu (_timeout_) koneksi HTTP Guzzle dalam detik.                 |
| `PDNS_VERIFY_SSL` | Boolean | `true`                  | Memverifikasi sertifikat SSL/TLS saat menggunakan HTTPS ke PowerDNS API. |

---

### 3. Konfigurasi Database (Application Database)

Database aplikasi digunakan untuk menyimpan data pengguna, sesi, peran hak akses (RBAC), API key pengguna, dan log audit (_audit trail_).

| Variabel        | Tipe    | Default        | Deskripsi                                     |
| :-------------- | :------ | :------------- | :-------------------------------------------- |
| `DB_CONNECTION` | String  | `mysql`        | Driver database (`mysql`, `pgsql`, `sqlite`). |
| `DB_HOST`       | String  | `127.0.0.1`    | Alamat IP atau hostname server database.      |
| `DB_PORT`       | Integer | `3306`         | Port komunikasi database.                     |
| `DB_DATABASE`   | String  | `pdns_manager` | Nama skema database.                          |
| `DB_USERNAME`   | String  | `root`         | Username akun database.                       |
| `DB_PASSWORD`   | String  | `""`           | Kata sandi akun database.                     |
| `DB_CHARSET`    | String  | `utf8mb4`      | Karakter set encoding database.               |

---

### 4. Konfigurasi Keamanan & Sesi (Security & Authentication)

| Variabel                | Tipe    | Default         | Deskripsi                                                                |
| :---------------------- | :------ | :-------------- | :----------------------------------------------------------------------- |
| `JWT_SECRET`            | String  | _(Wajib diisi)_ | Kunci rahasia acak (minimum 64 karakter) untuk menandatangani JWT token. |
| `JWT_TTL`               | Integer | `3600`          | Masa berlaku token JWT dalam detik (default: 1 jam).                     |
| `SESSION_LIFETIME`      | Integer | `7200`          | Durasi masa aktif sesi pengguna Web GUI dalam detik (default: 2 jam).    |
| `SESSION_SECURE_COOKIE` | Boolean | `true`          | Mewajibkan flag `Secure` pada cookie sesi (wajib `true` pada HTTPS).     |
| `CSRF_PROTECTION`       | Boolean | `true`          | Mengaktifkan verifikasi token CSRF pada rute POST/PUT/DELETE Web GUI.    |
| `RATE_LIMIT_REQUESTS`   | Integer | `60`            | Jumlah maksimum request per menit per alamat IP.                         |
| `RATE_LIMIT_DECAY`      | Integer | `60`            | Durasi pembatasan laju dalam detik.                                      |

---

### 5. Logging & Audit Trail (Monolog)

| Variabel             | Tipe    | Default  | Deskripsi                                                            |
| :------------------- | :------ | :------- | :------------------------------------------------------------------- |
| `LOG_CHANNEL`        | String  | `single` | Channel pencatatan log (`single`, `daily`, `stderr`, `syslog`).      |
| `LOG_LEVEL`          | String  | `info`   | Tingkat keparahan log minimum (`debug`, `info`, `warning`, `error`). |
| `LOG_RETENTION_DAYS` | Integer | `30`     | Durasi penyimpanan log harian saat menggunakan channel `daily`.      |

---

## 🔒 Praktik Terbaik Manajemen Konfigurasi

1. **Jangan Mengekspos Kunci Rahasia**: Pastikan berkas `.env` terdaftar di `.gitignore` dan tidak pernah di-commit ke repositori Git.
2. **Rotasi Kunci Secara Berkala**: Lakukan rotasi `JWT_SECRET` dan `PDNS_API_KEY` secara berkala untuk menjaga keamanan enterprise.
3. **Environment Hardening**: Pada lingkungan produksi, atur variabel `APP_DEBUG=false` agar tidak membocorkan kredensial atau struktur database saat terjadi kegagalan sistem.
