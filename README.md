# AbisLulus

**AbisLulus** adalah aplikasi yang dikembangkan untuk mendukung pembelajaran online (*e-learning*) dan pendampingan karier (*career mentoring*).

Repository ini digunakan sebagai tempat penyimpanan kode sumber dan kolaborasi pengembangan aplikasi AbisLulus.

## Teknologi yang Digunakan

- **Backend:** Laravel (PHP)
- **Dependency Manager:** Composer
- **Frontend Build Tool:** Bootstrap
- **Database:** MySQL
- **Version Control:** Git dan GitHub


---

## Persyaratan Instalasi

Sebelum menjalankan proyek, pastikan perangkat sudah memiliki:

1. PHP dengan versi yang sesuai dengan proyek.
2. Composer.
3. Git.
4. MySQL atau MariaDB.
5. Node.js dan npm.
6. Code editor, seperti Visual Studio Code.

Pastikan instalasi berhasil dengan menjalankan perintah berikut di terminal:

```bash
php -v
composer -V
git --version
node -v
npm -v
```

## Cara Menggunakan Repository

### 1. Clone Repository

Buka terminal, lalu masuk ke folder tempat kamu ingin menyimpan proyek.

Jalankan:

```bash
git clone https://github.com/Ekstensi-AbisLulus/Ekstensi_AbisLulus.git
```

Masuk ke folder proyek:

```bash
cd Ekstensi_AbisLulus
```

### 2. Install Dependensi PHP

Jalankan perintah:

```bash
composer install
```

Perintah ini akan memasang dependensi PHP yang dibutuhkan aplikasi berdasarkan `composer.lock`.

### 3. Install Dependensi Frontend

Jalankan:

```bash
npm install
```

Perintah ini akan memasang dependensi frontend yang tercantum dalam `package.json`.

### 4. Konfigurasi File Environment

Buat file `.env` berdasarkan konfigurasi contoh:

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**Linux atau macOS:**

```bash
cp .env.example .env
```

File `.env` digunakan untuk menyimpan konfigurasi lokal, seperti koneksi database dan application key.

### 5. Generate Application Key

Jalankan:

```bash
php artisan key:generate
```

Perintah ini akan membuat application key untuk instalasi lokal.

### 6. Konfigurasi Database

Buat database baru melalui MySQL atau aplikasi pengelola database yang digunakan.

Contoh nama database:

```text
abis_lulus
```

Kemudian buka file `.env` dan sesuaikan konfigurasi berikut dengan pengaturan database lokal:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abis_lulus
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan kredensial MySQL di perangkat masing-masing.

Jangan mengunggah file `.env` ke GitHub karena file tersebut dapat berisi informasi rahasia.

### 7. Jalankan Migrasi Database

Pastikan database sudah dibuat dan konfigurasi `.env` benar.

Jalankan:

```bash
php artisan migrate
```

Perintah ini akan membuat tabel berdasarkan file migrasi yang tersedia di proyek.

Jika proyek menyediakan seeder dan membutuhkan data awal, jalankan seeder sesuai petunjuk tim pengembang. Contohnya:

```bash
php artisan db:seed
```

> Jalankan seeder hanya jika sudah dipastikan sesuai dengan kebutuhan proyek. Hindari menjalankan `migrate:fresh` pada database yang berisi data penting karena perintah tersebut dapat menghapus tabel beserta datanya.

### 8. Jalankan Aplikasi

Buka terminal pertama, lalu jalankan server Laravel:

```bash
php artisan serve
```

Buka terminal kedua dan jalankan Vite:

```bash
npm run dev
```

Setelah kedua proses berjalan, buka alamat lokal yang ditampilkan oleh Laravel di terminal. Secara default, alamatnya adalah:

http://127.0.0.1:8000

Biarkan kedua terminal tetap berjalan selama aplikasi digunakan.

---

## Menjalankan Test

Proyek ini memiliki dua jenis test:

| Jenis | Lokasi | Perintah |
|---|---|---|
| Feature & Unit Test | `tests/Feature`, `tests/Unit` | `php artisan test` |
| Browser Test (Laravel Dusk) | `tests/Browser` | `php artisan dusk` |

### Feature & Unit Test

Tidak memerlukan persiapan tambahan (memakai database SQLite di memori):

```bash
php artisan test
```

### Browser Test (Laravel Dusk)

Dusk menjalankan skenario test plan langsung di browser Chrome. Panduan lengkap, termasuk aturan penulisan test tim, ada di [docs/dusk.md](docs/dusk.md). Ringkasannya:

#### 1. Persiapan (sekali saja)

1. Buat database khusus test (terpisah dari database development):

   ```sql
   CREATE DATABASE abislulus_dusk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Salin `.env` menjadi `.env.dusk.local`, lalu ubah nilai berikut:

   ```env
   APP_URL=http://localhost:8001
   DB_DATABASE=abislulus_dusk
   ```

   `.env.dusk.local` tidak boleh di-commit (sudah ada di `.gitignore`).

3. Siapkan aset, storage, dan ChromeDriver yang sesuai dengan versi Chrome:

   ```bash
   npm run build
   php artisan storage:link
   php artisan dusk:chrome-driver --detect
   ```

   Ulangi `npm run build` setiap ada perubahan di `resources/scss` atau `resources/js`, dan `dusk:chrome-driver --detect` setiap Chrome ter-update.

#### 2. Menjalankan Dusk di Windows (3 terminal)

**Terminal 1: ChromeDriver**

```powershell
vendor\laravel\dusk\bin\chromedriver-win.exe --port=9515
```

**Terminal 2: server aplikasi mode Dusk** (dari root project)

```powershell
cd public
$env:APP_ENV='dusk.local'
php -d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8001 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

**Terminal 3: jalankan test**

```powershell
php artisan dusk
```

#### 3. Menjalankan Dusk di macOS (2 terminal)

ChromeDriver dijalankan otomatis oleh Dusk.

**Terminal 1: server aplikasi mode Dusk** (dari root project)

```bash
cd public && APP_ENV=dusk.local php -d upload_max_filesize=10M -d post_max_size=12M -S 127.0.0.1:8001 ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
```

**Terminal 2: jalankan test**

```bash
php artisan dusk
```

> Server Dusk dijalankan dengan `php -d …`, bukan `php artisan serve`, agar batas upload PHP lebih besar dari batas aplikasi (2 MB untuk foto/thumbnail, 3 MB untuk gambar hero/sampul). Tanpa ini, test validasi upload akan gagal.

#### 4. Menjalankan test per anggota atau per file

```bash
php artisan dusk --group=aqeel
php artisan dusk --group=fakhri
php artisan dusk --group=fulca
php artisan dusk --group=oktavia
php artisan dusk --group=smoke                                   # smoke test landing page
php artisan dusk tests/Browser/Fakhri/AdminProfileTest.php       # satu file
php artisan dusk --filter=test_tc_admin_profile_001              # satu method
php artisan dusk --browse                                        # tampilkan browser (tidak headless)
```

#### 5. Hasil & screenshot

- Screenshot setiap Case ID tersimpan di `tests/Browser/screenshots/<Anggota>/` (mis. `Fakhri/TC.AdminProfile.001-step4.png`) untuk kolom Evidence test plan.
- Jika test gagal, Dusk menyimpan `failure-*.png` di `tests/Browser/screenshots/`, log console di `tests/Browser/console/`, dan HTML halaman di `tests/Browser/source/`. Folder-folder ini dikosongkan setiap kali Dusk dijalankan, jadi salin dulu file yang dibutuhkan.

#### Penting

- **Server Dusk hanya untuk test.** Database `abislulus_dusk` dikosongkan setelah setiap test. Jangan membuka server Dusk untuk mencoba aplikasi secara manual.
- **Jangan jalankan server development dan server Dusk bersamaan di port yang sama.** Matikan `php artisan serve` sebelum menjalankan Dusk, lalu matikan server Dusk (Ctrl+C) setelah selesai.

---

## Alur Kerja Git untuk Tim

### Mengambil Perubahan Terbaru

Sebelum mulai mengerjakan tugas:

```bash
git pull origin main
```

### Memeriksa Perubahan

```bash
git status
```

### Menambahkan Perubahan

```bash
git add .
```

### Membuat Commit

Gunakan pesan commit yang menjelaskan perubahan:

```bash
git commit -m "Menambahkan fitur login"
```

Ganti pesan tersebut sesuai perubahan yang dilakukan.

### Mengunggah Perubahan

```bash
git push origin main
```

**Catatan:** Untuk kerja tim, sebaiknya gunakan branch masing-masing dan ajukan Pull Request sebelum menggabungkan perubahan ke branch `main`, terutama jika beberapa anggota mengerjakan fitur berbeda.

---

## Troubleshooting

### Composer Error

Jalankan:

```bash
composer install
```

Pastikan versi PHP sesuai dengan persyaratan proyek.

### Application Key Belum Tersedia

Jalankan:

```bash
php artisan key:generate
```

### Database Connection Error

Periksa konfigurasi database dalam `.env`, pastikan layanan MySQL berjalan, dan pastikan database sudah dibuat.

### Perubahan Tidak Muncul di Browser

Pastikan server Laravel dan Vite berjalan. Jika diperlukan, bersihkan cache konfigurasi:

```bash
php artisan optimize:clear
```

### Error `Table 'abislulus_dusk.sessions' doesn't exist`

Browser sedang membuka server Dusk, bukan server development. Matikan server Dusk, lalu jalankan `php artisan serve` (tambahkan `--port=8001` bila port 8000 sudah dipakai Apache/XAMPP).

### Test Dusk Gagal

Lihat tabel "Masalah Umum" di [docs/dusk.md](docs/dusk.md#9-masalah-umum), misalnya ChromeDriver tidak cocok dengan versi Chrome, manifest Vite usang, atau pesan "failed to upload".

### Konflik Git

Jika terjadi konflik ketika melakukan `git pull`, selesaikan konflik pada file yang ditandai sebelum melanjutkan commit.

---

## Catatan untuk Developer

- Jangan mengunggah `.env`, password, API key, atau kredensial pribadi ke repository.
- Jangan mengunggah folder `vendor/` dan `node_modules/`; dependensi dipasang melalui Composer dan npm.
- Pastikan perubahan sudah diuji sebelum diunggah.
- Komunikasikan perubahan struktur database kepada anggota tim.
- Periksa konfigurasi dan dependensi proyek sebelum mengikuti instruksi instalasi tambahan.

## Repository

GitHub: https://github.com/Ekstensi-AbisLulus/Ekstensi_AbisLulus