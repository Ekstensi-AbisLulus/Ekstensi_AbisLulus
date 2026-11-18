# Panduan Laravel Dusk — Tim AbisLulus

Dokumen ini menjelaskan cara menyiapkan dan menjalankan browser test (Laravel Dusk) serta aturan penulisan test yang disepakati tim. Test plan tiap anggota ada di `docs/test-plan/`.

---

## 1. Struktur Folder

```
tests/
├── DuskTestCase.php          ← base class + macro bersama (jangan diubah sendiri-sendiri)
└── Browser/
    ├── ExampleTest.php       ← smoke test landing page
    ├── Concerns/             ← trait bersama (CreatesTestUsers, MakesUploadFiles)
    ├── Aqeel/                ← test milik Aqeel   (E-Learning,          group: aqeel)
    ├── Fakhri/               ← test milik Fakhri  (Profil Admin,        group: fakhri)
    ├── Fulca/                ← test milik Fulca   (Profil Pengguna,     group: fulca)
    ├── Oktavia/              ← test milik Oktavia (Landing Page,        group: oktavia)
    └── screenshots/          ← hasil screenshot (tidak di-commit)
```

Setiap anggota **hanya** menulis file di folder namanya sendiri. File di `Concerns/` dan `DuskTestCase.php` adalah milik bersama — kalau perlu mengubah, diskusikan dulu di grup supaya tidak konflik.

---

## 2. Menyiapkan Database `abislulus_dusk`

Dusk memakai database **terpisah** supaya data development tidak terhapus (setiap test menjalankan `migrate:fresh`).

1. Buat database kosong di MySQL (phpMyAdmin / HeidiSQL / terminal):

   ```sql
   CREATE DATABASE abislulus_dusk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Tidak perlu menjalankan migration/seeder manual — trait `DatabaseMigrations` di setiap test akan membuat tabel sendiri.

---

## 3. Isi `.env.dusk.local`

Salin `.env` menjadi `.env.dusk.local` di root project, lalu pastikan nilai berikut:

```dotenv
APP_ENV=local
APP_KEY=base64:...            # boleh sama dengan .env, WAJIB terisi
APP_DEBUG=true
APP_URL=http://localhost:8001 # harus sama dengan port server Dusk

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=abislulus_dusk    # JANGAN pakai database development
DB_USERNAME=root
DB_PASSWORD=                  # sesuaikan dengan MySQL masing-masing

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
MAIL_MAILER=log
FILESYSTEM_DISK=local
```

Catatan:
- `.env.dusk.local` sudah ada di `.gitignore` — **jangan di-commit** (berisi password DB lokal).
- Selama `php artisan dusk` berjalan, Dusk menukar `.env` dengan `.env.dusk.local` sementara, lalu mengembalikannya. Kalau test dihentikan paksa (Ctrl+C) dan `.env` "berubah", cek file `.env.backup` lalu kembalikan.
- Pastikan `php artisan storage:link` sudah pernah dijalankan (untuk fitur upload gambar).
- Jalankan `npm run build` sebelum Dusk (dan setiap kali ada perubahan di `resources/scss` / `resources/js`). Kalau manifest Vite usang, landing page error 500 "Unable to locate file in Vite manifest" dan semua test gagal.

---

## 4. Menjalankan Dusk

### Windows (3 terminal)

Di Windows, ChromeDriver dijalankan **manual** di port 9515.

**Terminal 1 — ChromeDriver**
```powershell
php artisan dusk:chrome-driver --detect   # sekali saja / saat Chrome update
vendor\laravel\dusk\bin\chromedriver-win.exe --port=9515
```

**Terminal 2 — server aplikasi (mode Dusk)**
```powershell
php artisan serve --env=dusk.local --port=8001
```

**Terminal 3 — jalankan test**
```powershell
php artisan dusk
```

Biarkan Terminal 1 dan 2 tetap terbuka selama menjalankan test.

### Mac (2 terminal)

Di Mac, ChromeDriver dijalankan otomatis oleh `DuskTestCase::prepare()`.

**Terminal 1 — server aplikasi**
```bash
php artisan dusk:chrome-driver --detect   # sekali saja / saat Chrome update
chmod +x vendor/laravel/dusk/bin/chromedriver-mac-*   # bila muncul "permission denied"
php artisan serve --env=dusk.local --port=8001
```

**Terminal 2 — jalankan test**
```bash
php artisan dusk
```

### Opsi tambahan

| Kebutuhan | Perintah |
|---|---|
| Melihat browser (tidak headless) | `php artisan dusk --browse` |
| Satu file saja | `php artisan dusk tests/Browser/Fakhri/AdminProfileTest.php` |
| Satu method saja | `php artisan dusk --filter=test_tc_admin_profile_001` |
| Smoke test saja | `php artisan dusk --group=smoke` |

---

## 5. Menjalankan Test per Anggota

Setiap class test diberi atribut `#[Group('<nama>')]`, sehingga tiap anggota bisa menjalankan test miliknya saja:

```bash
php artisan dusk --group=aqeel
php artisan dusk --group=fakhri
php artisan dusk --group=fulca
php artisan dusk --group=oktavia
```

---

## 6. Letak Screenshot

- Screenshot dari macro `shot()` tersimpan di `tests/Browser/screenshots/`, contoh:
  `$browser->shot('Fakhri/TC.AdminProfile.001-step4')` → `tests/Browser/screenshots/Fakhri/TC.AdminProfile.001-step4.png`
- Saat test **gagal**, Dusk otomatis menyimpan:
  - screenshot `failure-...png` di `tests/Browser/screenshots/`
  - log console browser di `tests/Browser/console/`
  - HTML halaman di `tests/Browser/source/`
- Ketiga folder tersebut ada di `.gitignore`. Untuk kolom **Evidence** test plan, unggah screenshot ke dokumen/spreadsheet tim, bukan ke repo.

---

## 7. Helper Bersama

### Macro `Browser` (terdaftar di `tests/DuskTestCase.php`)

| Macro | Fungsi |
|---|---|
| `->disableHtmlValidation()` | Menyetel `noValidate = true` pada semua form di halaman saat ini, supaya atribut HTML `required`/`type="email"` tidak memblokir submit. Wajib dipakai pada test validasi **negatif**. Panggil **setelah** `visit()` dan sebelum `press()`. |
| `->shot('Nama/TC.Xxx.001-stepN')` | Menyimpan screenshot dengan nama rapi (karakter aneh diganti `-`, subfolder boleh). |

### Trait `Tests\Browser\Concerns\CreatesTestUsers`

Semua akun memakai password **`password`** (konstanta `self::TEST_PASSWORD`).

```php
$user  = $this->makeUser();                                   // role user + profil terisi acak
$admin = $this->makeAdmin(['name' => 'Admin Uji']);           // override atribut user
$admin = $this->makeAdmin([], ['institution' => 'Komunitas']); // override atribut profil
$admin = $this->makeAdmin([], $this->emptyProfile());         // profil ada tapi masih kosong
$super = $this->makeSuperAdmin([], null);                     // tanpa baris user_profiles
```

### Trait `Tests\Browser\Concerns\MakesUploadFiles`

`Storage::fake()` **tidak bisa** dipakai di Dusk karena browser dan server berjalan di proses terpisah. Trait ini membuat file nyata di `storage/framework/testing/dusk-uploads/` dan menghapusnya otomatis setelah setiap test.

```php
$browser->attach('@avatar-input', $this->imageFile());           // PNG valid ±50 KB
$browser->attach('@avatar-input', $this->imageFile('jpg', 200));  // JPG valid ±200 KB
$browser->attach('@avatar-input', $this->largeImageFile(2));      // gambar valid ±2,1 MB (> 2 MB)
$browser->attach('@hero-image',   $this->largeImageFile(3, 'jpg'));// gambar valid > 3 MB
$browser->attach('@avatar-input', $this->nonImageFile());         // file PDF
$browser->attach('@avatar-input', $this->nonImageFile('jpg'));    // teks berekstensi .jpg (palsu)
```

Format gambar yang didukung: `png`, `jpg`/`jpeg`, `gif` (`webp` hanya jika GD di mesin mendukung). Ekstensi PHP **GD** wajib aktif.

---

## 8. Aturan Penulisan Test Dusk Tim

1. **Satu class per kelompok PBI, satu method per Case ID.**
   Nama method: `test_tc_<prefix>_<nomor>_<ringkasan>` dengan huruf kecil & underscore.
   Contoh: Case ID `TC.AdminProfile.001` → `test_tc_admin_profile_001_lengkapi_profil`.

2. **Docblock setiap method** berisi Case ID dan Test Case dari CSV test plan.

3. **Setiap class** memakai trait `DatabaseMigrations` (**BUKAN** `RefreshDatabase` — transaksi tidak terlihat oleh server yang melayani browser) dan atribut `#[Group('<nama anggota>')]` dengan huruf kecil (`aqeel`, `fakhri`, `fulca`, `oktavia`).

4. **Data uji dibuat di dalam test** lewat factory/model (atau helper `makeUser()` dkk.). Jangan bergantung pada seeder.

5. **Login memakai `$browser->loginAs($user)`**, kecuali test yang memang menguji form login.

6. **Selector memakai atribut `dusk="..."`** (diakses dengan `@nama`, mis. `->type('@phone', '0812...')`). Anggota hanya boleh menambahkan atribut `dusk` pada view milik fiturnya sendiri, **tanpa mengubah tampilan atau logika**. Butuh selector di view milik anggota lain → minta ke pemiliknya.

7. **Ambil screenshot (`shot()`) di langkah akhir setiap Case ID** untuk kolom Evidence, dengan pola `<Anggota>/<Case ID>-step<N>`.

8. **Test harus independen** dan tidak bergantung pada urutan eksekusi. Jangan memakai data/hasil dari test lain.

### Contoh kerangka (hanya ilustrasi format)

```php
<?php

namespace Tests\Browser\Fakhri;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Group;
use Tests\Browser\Concerns\CreatesTestUsers;
use Tests\Browser\Concerns\MakesUploadFiles;
use Tests\DuskTestCase;

#[Group('fakhri')]
class AdminProfileTest extends DuskTestCase
{
    use CreatesTestUsers, DatabaseMigrations, MakesUploadFiles;

    /**
     * Case ID  : TC.Contoh.001
     * Test Case: <salin kolom "Test Case" dari CSV>
     */
    public function test_tc_contoh_001_ringkasan(): void
    {
        $admin = $this->makeAdmin([], $this->emptyProfile());

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/halaman')
                // ... langkah sesuai test plan ...
                ->shot('Fakhri/TC.Contoh.001-step4');
        });
    }
}
```

---

## 9. Masalah Umum

| Gejala | Solusi |
|---|---|
| `session not created: This version of ChromeDriver only supports Chrome version ...` | Jalankan `php artisan dusk:chrome-driver --detect`, lalu restart ChromeDriver. |
| `Failed to connect to localhost port 9515` | ChromeDriver belum berjalan (Windows: Terminal 1). |
| Halaman kosong / `Connection refused` di screenshot | Server `php artisan serve --env=dusk.local --port=8001` belum berjalan, atau `APP_URL` tidak sama dengan port. |
| Data development hilang | `DB_DATABASE` di `.env.dusk.local` salah — harus `abislulus_dusk`. |
| Submit form validasi negatif tidak terjadi apa-apa | Lupa memanggil `->disableHtmlValidation()` setelah `visit()`. |
