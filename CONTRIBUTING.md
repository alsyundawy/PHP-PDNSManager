# Panduan Kontribusi (Contributing Guidelines)

Terima kasih atas minat Anda untuk berkontribusi pada **PHP-PDNSManager Enterprise Edition**! Kami menyambut kontribusi dari komunitas open-source, baik berupa perbaikan bug, penambahan fitur baru, pembaruan dokumentasi, maupun peningkatan performa.

---

## 📋 Kode Etik (Code of Conduct)

Dalam berinteraksi di proyek ini, kami mengharapkan semua kontributor untuk:

- Menggunakan bahasa yang sopan, profesional, dan inklusif.
- Menghormati sudut pandang dan pengalaman anggota komunitas lain.
- Fokus pada penyelesaian masalah secara konstruktif.

---

## 🚀 Memulai (Getting Started)

### 1. Fork & Clone Repositori

Lakukan _fork_ pada repositori ini di GitHub, kemudian _clone_ ke lingkungan lokal Anda:

```bash
git clone https://github.com/USERNAME/PHP-PDNSManager.git
cd PHP-PDNSManager
```

### 2. Setup Lingkungan Pengembangan

Pastikan sistem Anda memenuhi kebutuhan PHP 8.1+ dan Composer 2.2+. Jalankan perintah berikut untuk menginstal dependensi:

```bash
# Instal dependensi PHP (termasuk paket dev)
composer install

# Instal dependensi JavaScript & CSS
npm install
npm run dev

# Salin konfigurasi environment
cp .env.example .env
```

---

## 🌿 Konvensi Git & Branching

Kami menggunakan alur kerja **Feature Branching**:

- `main`: Branch utama yang selalu siap untuk rilis produksi (_production-ready_).
- `feature/nama-fitur`: Untuk pengembangan fitur baru.
- `fix/nama-bug`: Untuk perbaikan bug.
- `docs/nama-dokumentasi`: Untuk perbaikan atau penambahan dokumentasi.

### Format Commit Message

Gunakan format commit message yang jelas dan deklaratif:

```text
<type>(<scope>): <deskripsi singkat>

[Opsional: penjelasan lebih rinci]
```

Contoh:

- `feat(dns): add support for NAPTR records in ZoneService`
- `fix(auth): prevent session fixation on user login`
- `docs(api): update OpenAPI response schema for /api/v1/zones`

---

## 🛠️ Standar Kualitas Kode (Code Quality Standards)

Sebelum mengajukan Pull Request (PR), kode Anda **wajib** lolos seluruh pengujian static analysis dan unit test:

```bash
# 1. Jalankan Unit & Integration Test
make test

# 2. Jalankan Static Analysis (PHPStan)
make stan

# 3. Jalankan Psalm Analysis
make psalm

# 4. Periksa Standar Format Kode (PSR-12)
make cs

# 5. Lakukan perbaikan otomatis (PHPCS Fixer & Rector)
make fix
```

> [!IMPORTANT]
> Proyek ini menerapkan **Strict Typing (`declare(strict_types=1);`)** di setiap berkas PHP. Pastikan seluruh tipe data parameter dan return value terdefinisi dengan jelas.

---

## 📥 Mengirimkan Pull Request (PR)

1. Pastikan branch Anda sudah disinkronkan dengan branch `main` terbaru (`git pull origin main`).
2. Pastikan `make all` berhasil tanpa error.
3. Buat Pull Request melalui GitHub dengan menyertakan:
    - **Judul PR** yang singkat dan representatif.
    - **Deskripsi PR** yang menjelaskan apa yang diubah dan alasan perubahan tersebut.
    - Tautan ke _Issue_ yang relevan (jika ada), misalnya `Closes #42`.
4. Tim penyunting akan meninjau PR Anda dan memberikan umpan balik jika diperlukan.

Terima kasih atas kontribusi Anda dalam membangun platform manajemen DNS yang andal!
