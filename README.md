# AbisLulus

**AbisLulus** adalah aplikasi yang dikembangkan untuk mendukung pembelajaran online (*e-learning*) dan pendampingan karier (*career mentoring*).

Repository ini digunakan sebagai tempat penyimpanan kode sumber dan kolaborasi pengembangan aplikasi AbisLulus.

## Teknologi yang Digunakan

- **Backend:** Laravel (PHP)
- **Dependency Manager:** Composer
- **Frontend Build Tool:** Vite
- **Database:** MySQL
- **Version Control:** Git dan GitHub

> Versi teknologi yang digunakan perlu disesuaikan dengan konfigurasi proyek yang sebenarnya.

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