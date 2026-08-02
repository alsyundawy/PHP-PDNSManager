# Panduan Instalasi PHP-PDNSManager Enterprise Edition

Dokumen ini menyajikan panduan instalasi langkah demi langkah untuk melakukan _deployment_ **PHP-PDNSManager Enterprise Edition** pada lingkungan server produksi atau staging.

---

## 📋 Prasyarat Sistem (System Requirements)

Sebelum memulai proses instalasi, pastikan server Anda telah memenuhi spesifikasi minimum berikut:

### 1. Perangkat Lunak Server & Runtime

- **Sistem Operasi**: Linux (Ubuntu 22.04 LTS / Debian 12 / RHEL 9 disarankan)
- **PHP**: `>= 8.1`
    - Ekstensi PHP Wajib: `ext-pdo`, `ext-pdo_mysql` (atau `pdo_pgsql`), `ext-json`, `ext-sodium`, `ext-curl`, `ext-mbstring`, `ext-xml`, `ext-zip`
- **PowerDNS Authoritative Server**: `>= 4.5` (dengan modul REST API diaktifkan)
- **Database Backend**: MySQL `>= 8.0`, MariaDB `>= 10.5`, atau PostgreSQL `>= 13`
- **Web Server**: Nginx `>= 1.18` atau Apache `>= 2.4`
- **Composer**: `>= 2.2`
- **Node.js & npm**: Node.js `>= 18.x` (untuk melakukan compile asset frontend)

---

## ⚙️ Langkah 1: Konfigurasi PowerDNS REST API

Pastikan PowerDNS Authoritative Server Anda telah mengonfigurasi REST API. Edit berkas `/etc/powerdns/pdns.conf`:

```ini
# Aktifkan Webserver & REST API internal PowerDNS
webserver=yes
webserver-address=127.0.0.1
webserver-port=8081
webserver-allow-from=127.0.0.1,::1

# Setel API Key acak dan kuat
api=yes
api-key=GantiDenganApiKeyPowerDNSYangSangatAman123!
```

Setelah mengubah konfigurasi, muat ulang layanan PowerDNS:

```bash
sudo systemctl restart pdns
```

Uji koneksi REST API PowerDNS:

```bash
curl -H 'X-API-Key: GantiDenganApiKeyPowerDNSYangSangatAman123!' http://127.0.0.1:8081/api/v1/servers/localhost
```

---

## 📥 Langkah 2: Clone Repositori & Instal Dependensi

```bash
# Clone repositori ke direktori web server
cd /var/www
sudo git clone https://github.com/alsyundawy/PHP-PDNSManager.git php-pdnsmanager
cd php-pdnsmanager

# Atur kepemilikan direktori ke pengguna web server (misal: www-data)
sudo chown -R www-data:www-data /var/www/php-pdnsmanager

# Instal dependensi Composer (mode produksi)
composer install --no-dev --optimize-autoloader

# Instal dependensi Node.js dan compile assets
npm install
npm run production
```

---

## 📝 Langkah 3: Konfigurasi Berkas Environment (`.env`)

Salin berkas contoh `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Buka berkas `.env` dan sesuaikan parameter berikut:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://pdns.domainanda.com

# Konfigurasi Database Aplikasi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pdns_manager
DB_USERNAME=pdns_user
DB_PASSWORD=PasswordDatabaseSangatAman!

# Konfigurasi Integrasi PowerDNS REST API
PDNS_API_URL=http://127.0.0.1:8081
PDNS_API_KEY=GantiDenganApiKeyPowerDNSYangSangatAman123!
PDNS_SERVER_ID=localhost

# Konfigurasi Keamanan
JWT_SECRET=GantiDenganSecretJWTKunciSangatPanjangDanAmanMinimum64Karakter!
SESSION_SECURE_COOKIE=true
```

---

## 🗄️ Langkah 4: Migrasi Database & Seeding

Jalankan skrip CLI migrasi untuk menginisialisasi skema tabel database aplikasi (user, roles, audit logs, api keys):

```bash
php bin/console migrate --seed
```

> [!IMPORTANT]
> Skrip migrasi akan membuat pengguna Administrator bawaan. Catat kredensial awal yang muncul di terminal dan segera ubah kata sandi saat login pertama kali.

---

## 🌐 Langkah 5: Konfigurasi Web Server

### Opsi A: Nginx (Direkomendasikan)

Buat berkas konfigurasi virtual host Nginx di `/etc/nginx/sites-available/pdns-manager.conf`:

```nginx
server {
    listen 80;
    server_name pdns.domainanda.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name pdns.domainanda.com;

    root /var/www/php-pdnsmanager/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/pdns.domainanda.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pdns.domainanda.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    # Security Headers
    add_header X-Frame-Options "DENY" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Aktifkan konfigurasi dan reload Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/pdns-manager.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Langkah 6: Hak Akses Direktori & Hardening

Pastikan direktori `storage/` dan `bootstrap/cache/` dapat ditulis oleh proses PHP-FPM (`www-data`):

```bash
sudo chmod -R 775 /var/www/php-pdnsmanager/storage
sudo chown -R www-data:www-data /var/www/php-pdnsmanager/storage
```

---

## ✅ Verifikasi Instalasi

1. Buka peramban web dan akses `https://pdns.domainanda.com`.
2. Masuk menggunakan kredensial Administrator yang telah dibuat.
3. Buka menu **Dashboard** untuk meyakinkan status koneksi ke PowerDNS Authoritative Server berstatus **Connected (Green)**.
