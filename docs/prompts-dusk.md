# Prompt Claude Code — Laravel Dusk Sprint 1 AbisLulus

Prompt untuk mengubah test plan tiap anggota menjadi test otomatis **Laravel Dusk**.
Bisa dipakai di Claude Code maupun AI agent lain.

| Anggota | File test plan | Case ID | Folder test | Group |
|---|---|---|---|---|
| Aqeel | `docs/test-plan/test-plan-aqeel.csv` | TC.Material.001–020 | `tests/Browser/Aqeel/` | `aqeel` |
| Fakhri | `docs/test-plan/test-plan-fakhri.csv` | TC.AdminProfile.001–028 | `tests/Browser/Fakhri/` | `fakhri` |
| Fulca | `docs/test-plan/test-plan-fulca.csv` | TC.UserProfile.001–021 | `tests/Browser/Fulca/` | `fulca` |
| Oktavia | `docs/test-plan/test-plan-oktavia.csv` | TC.LandingPage.001–029 | `tests/Browser/Oktavia/` | `oktavia` |

---

## Langkah 0 — Persiapan (dikerjakan sekali oleh satu orang)

1. Salin keempat file CSV test plan ke folder `docs/test-plan/` di project.
2. Pastikan Dusk sudah terpasang dan `ExampleTest` sudah **Pass**.
3. Jalankan **Prompt A** (fondasi Dusk) di bawah.
4. Commit hasilnya, push, lalu merge ke `Develop_Sprint1`.
5. Setiap anggota menarik `Develop_Sprint1` ke branch-nya, lalu menjalankan prompt miliknya (Prompt B–E).

**Menjalankan Dusk di Windows** (tiga terminal, biarkan dua yang pertama tetap terbuka):
```powershell
php artisan serve
.\vendor\laravel\dusk\bin\chromedriver-win.exe --port=9515
php artisan dusk --group=namakamu
```
Di Mac, ChromeDriver biasanya bisa dijalankan otomatis oleh Dusk. Kalau muncul error `localhost:9515`, jalankan manual: `./vendor/laravel/dusk/bin/chromedriver-mac-arm --port=9515` (Mac Intel: `chromedriver-mac-intel`).

---

## Prompt A — Fondasi Dusk (sekali saja)

```text
Baca CLAUDE.md, prd.md, dan file di docs/test-plan/. Laravel Dusk sudah terpasang
(tests/DuskTestCase.php, tests/Browser/ExampleTest.php sudah Pass). Tugasmu menyiapkan
fondasi bersama supaya 4 anggota tim bisa menulis test Dusk di folder masing-masing
tanpa konflik. JANGAN menulis test untuk PBI mana pun di tugas ini.

Lingkungan:
- Database Dusk terpisah: abislulus_dusk (dari .env.dusk.local, APP_ENV=local).
- Tim memakai Windows dan Mac. Di Windows, ChromeDriver dijalankan manual di port 9515.

Yang harus dibuat:
1. Struktur folder: tests/Browser/Aqeel, tests/Browser/Fakhri, tests/Browser/Fulca,
   tests/Browser/Oktavia (masing-masing dengan .gitkeep), dan tests/Browser/Concerns.
2. Trait tests/Browser/Concerns/CreatesTestUsers.php:
   makeUser(), makeAdmin(), makeSuperAdmin() — memakai UserFactory yang sudah ada
   (state admin(), superAdmin(), withProfile()). Parameter opsional untuk atribut user
   dan profil. Password semua akun "password".
3. Trait tests/Browser/Concerns/MakesUploadFiles.php — membuat file uji nyata di disk
   (Dusk butuh path file asli untuk ->attach(), Storage::fake TIDAK bisa dipakai karena
   browser berjalan di proses terpisah):
   - imageFile(string $ext = 'png', int $kb = 50): string  → gambar valid (pakai GD)
   - largeImageFile(int $mb): string                        → gambar > batas ukuran
   - nonImageFile(string $ext = 'pdf'): string             → file bukan gambar
   Simpan di storage/framework/testing/dusk-uploads, hapus otomatis di tearDown.
4. Di tests/DuskTestCase.php daftarkan macro Browser:
   - disableHtmlValidation(): menjalankan script yang menyetel noValidate = true pada semua
     form, karena form di aplikasi memakai atribut HTML "required" yang akan memblokir
     submit pada test validasi negatif.
   - shot(string $name): menyimpan screenshot dengan nama rapi (mis. "Fakhri/TC.AdminProfile.001-step4").
   Jangan mengubah logika startChromeDriver bawaan.
5. Ubah tests/Browser/ExampleTest.php menjadi smoke test landing page (assertSee 'AbisLulus').
6. Pastikan .gitignore berisi: .env.dusk.local, /tests/Browser/screenshots,
   /tests/Browser/console, /tests/Browser/source.
7. Buat docs/dusk.md (bahasa Indonesia): cara menyiapkan database abislulus_dusk,
   isi .env.dusk.local, cara menjalankan di Windows (3 terminal) dan Mac,
   cara menjalankan per anggota (php artisan dusk --group=fakhri),
   letak screenshot, dan aturan penulisan test (lihat poin 8).
8. Tulis aturan penulisan test Dusk tim di docs/dusk.md:
   - Satu class test per kelompok PBI, satu method per Case ID test plan,
     nama method: test_tc_<prefix>_<nomor>_<ringkasan> (mis. test_tc_admin_profile_001_lengkapi_profil).
   - Docblock setiap method berisi Case ID dan Test Case dari CSV.
   - Setiap class memakai trait DatabaseMigrations (BUKAN RefreshDatabase) dan atribut
     #[Group('<nama anggota>')].
   - Data uji dibuat lewat factory/model di dalam test, tidak bergantung pada seeder.
   - Login memakai $browser->loginAs($user).
   - Selector memakai atribut dusk="..." ; anggota boleh menambahkan atribut dusk pada
     view milik fiturnya sendiri saja, tanpa mengubah tampilan atau logika.
   - Ambil screenshot (macro shot) di langkah akhir setiap Case ID untuk kolom Evidence.
   - Test harus independen dan tidak bergantung urutan.

Setelah selesai: jalankan php artisan dusk (hanya ExampleTest yang ada), laporkan
hasilnya, dan sebutkan file yang dibuat/diubah. Jangan commit.
```

---

## Aturan bersama untuk Prompt B–E

Sudah disertakan di setiap prompt, jadi tidak perlu dikirim terpisah:

- Test **hanya memverifikasi** perilaku aplikasi. Kalau ada langkah yang gagal karena bug, **jangan ubah kode aplikasi dan jangan melemahkan assertion** supaya lolos. Laporkan sebagai **Fail** beserta penyebabnya.
- Boleh menambahkan atribut `dusk="..."` di view milik sendiri.
- Untuk test validasi negatif, panggil `->disableHtmlValidation()` sebelum submit.
- Modal Bootstrap: tunggu dengan `waitFor()` / `whenAvailable()` sebelum klik.
- Pengecekan file di storage: pakai `Storage::disk('public')->exists()` setelah aksi selesai.
- Link yang dibuka di tab baru cukup dicek atribut `href` dan `target="_blank"`.

---

## Prompt B — Aqeel (Manajemen E-Learning, PBI-10 sampai 13)

```text
Baca CLAUDE.md, prd.md, docs/dusk.md, dan docs/test-plan/test-plan-aqeel.csv.
Saya Aqeel. Ubah SEMUA test case di CSV tersebut (TC.Material.001 sampai TC.Material.020)
menjadi test Laravel Dusk, mengikuti aturan di docs/dusk.md.

Sebelum menulis test, baca kode fitur terkait agar selector, teks tombol, URL, dan pesan
sesuai implementasi: routes/web.php (admin.materials.*, materials.index/show),
app/Http/Controllers/Admin/MaterialController.php, app/Http/Controllers/MaterialController.php,
app/Http/Requests/Admin/StoreMaterialRequest.php & UpdateMaterialRequest.php,
LearningMaterialPolicy, dan view resources/views/admin/materials/* serta resources/views/materials/*.

Struktur file (folder tests/Browser/Aqeel, #[Group('aqeel')]):
- MaterialCreateTest.php  → TC.Material.001–007 (PBI-10)
- MaterialReadTest.php    → TC.Material.008–012 (PBI-11)
- MaterialUpdateTest.php  → TC.Material.013–017 (PBI-12)
- MaterialDeleteTest.php  → TC.Material.018–020 (PBI-13)

Ketentuan:
- Setiap Steps di CSV menjadi langkah di test; setiap Expected Result menjadi assertion
  (assertPathIs, assertSee, assertVisible, assertInputValue, assertDatabaseHas, dst).
- Kasus otorisasi (Guest → redirect /login, role lain → 403) ikut diuji di browser.
- TC.Material.014 & 019: verifikasi file thumbnail lama/baru di storage public.
- TC.Material.019: siapkan ulasan dan kuis terkait, lalu pastikan ulasan terhapus dan
  material_id kuis menjadi NULL.
- Untuk validasi negatif panggil ->disableHtmlValidation() sebelum klik simpan.
- Jangan mengubah kode aplikasi selain menambahkan atribut dusk pada view materi.
  Kalau ada langkah yang gagal karena bug aplikasi, JANGAN melemahkan assertion; laporkan.

Setelah selesai, jalankan php artisan dusk --group=aqeel dan berikan tabel:
Case ID | method test | hasil (Pass/Fail) | catatan (penyebab jika Fail) | nama file screenshot.
Tabel ini akan saya salin ke kolom Status dan Evidence di test plan. Jangan commit.
```

---

## Prompt C — Fakhri (Profil & Riwayat Admin, PBI-07 sampai 09)

```text
Baca CLAUDE.md, prd.md, docs/dusk.md, dan docs/test-plan/test-plan-fakhri.csv.
Saya Fakhri. Ubah SEMUA test case di CSV tersebut (TC.AdminProfile.001 sampai
TC.AdminProfile.028) menjadi test Laravel Dusk, mengikuti aturan di docs/dusk.md.

Sebelum menulis test, baca kode fitur terkait agar selector, teks tombol, URL, dan pesan
sesuai implementasi: routes/web.php (admin.profile.*, admin.history),
app/Http/Controllers/Admin/ProfileController.php, app/Http/Controllers/Admin/ActivityHistoryController.php,
app/Http/Requests/Admin/UpdateProfileRequest.php & UpdatePasswordRequest.php,
app/Http/Requests/Concerns/ProfileRules.php, app/Rules/PhoneNumber.php,
resources/views/admin/profile/edit.blade.php, resources/views/admin/history/*,
dan tests/Feature/Admin/ProfileTest.php, EditProfileTest.php, ActivityHistoryTest.php sebagai acuan.

Struktur file (folder tests/Browser/Fakhri, #[Group('fakhri')]):
- AdminProfileTest.php          → TC.AdminProfile.001–009 (PBI-07)
- AdminEditProfileTest.php      → TC.AdminProfile.010–018 (PBI-08)
- AdminActivityHistoryTest.php  → TC.AdminProfile.019–028 (PBI-09)

Ketentuan khusus:
- TC.AdminProfile.007 (email read-only) dan TC.AdminProfile.018 (sisipkan user_id admin lain):
  manipulasi DOM lewat $browser->script(), lalu pastikan data di database tidak berubah.
- TC.AdminProfile.012 & 017: verifikasi file foto lama/baru di storage public.
- TC.AdminProfile.013 langkah 3: logout lalu login ulang lewat form /login dengan password baru.
- Pesan error password tampil di dalam card "Ubah Password" (error bag updatePassword).
- PBI-09: buat data materi, kuis (beserta soal dan percobaan), dan kegiatan (beserta
  pendaftaran, termasuk yang berstatus cancelled) milik admin yang login dan milik admin lain,
  memakai factory/model yang ada. Untuk TC.AdminProfile.024 buat > 10 data.
- TC.AdminProfile.020: pastikan tombol Edit mengarah ke route edit modul terkait.
- Untuk validasi negatif panggil ->disableHtmlValidation() sebelum klik simpan.
- Jangan mengubah kode aplikasi selain menambahkan atribut dusk pada view profil dan riwayat admin.
  Kalau ada langkah yang gagal karena bug aplikasi, JANGAN melemahkan assertion; laporkan.

Setelah selesai, jalankan php artisan dusk --group=fakhri dan berikan tabel:
Case ID | method test | hasil (Pass/Fail) | catatan (penyebab jika Fail) | nama file screenshot.
Tabel ini akan saya salin ke kolom Status dan Evidence di test plan. Jangan commit.
```

---

## Prompt D — Fulca (Profil & Riwayat Pengguna, PBI-04 sampai 06)

```text
Baca CLAUDE.md, prd.md, docs/dusk.md, dan docs/test-plan/test-plan-fulca.csv.
Saya Fulca. Ubah SEMUA test case di CSV tersebut (TC.UserProfile.001 sampai
TC.UserProfile.021) menjadi test Laravel Dusk, mengikuti aturan di docs/dusk.md.

Sebelum menulis test, baca kode fitur terkait agar selector, teks tombol, URL, dan pesan
sesuai implementasi: routes/web.php (profile.*, profile.history.*),
app/Http/Controllers/ProfileController.php, app/Http/Controllers/ProfileHistoryController.php,
app/Http/Requests/UpdateProfileRequest.php, resources/views/profile/**,
dan tests/Feature/ProfileTest.php & ProfileHistoryTest.php sebagai acuan.

Struktur file (folder tests/Browser/Fulca, #[Group('fulca')]):
- UserProfileTest.php          → TC.UserProfile.001–007 (PBI-04)
- UserEditProfileTest.php      → TC.UserProfile.008–014 (PBI-05)
- UserActivityHistoryTest.php  → TC.UserProfile.015–021 (PBI-06)

Ketentuan khusus:
- Ubah password di profil pengguna menjadi satu form dengan data profil (bagian
  "Ubah Password (opsional)", tombol "Simpan Perubahan"), bukan form terpisah.
- Beberapa pesan validasi masih bahasa Inggris bawaan Laravel (lihat Keterangan di CSV).
  Untuk kasus itu, assert bahwa field mendapat kelas is-invalid dan pesan .invalid-feedback
  muncul di bawahnya, tanpa mencocokkan teks persisnya. Untuk pesan yang sudah bahasa
  Indonesia (No. HP, tahun lulus, password lama) cocokkan teksnya persis.
- Field tanggal lahir bertipe date: isi nilainya lewat $browser->script() (format YYYY-MM-DD)
  karena format ketik berbeda tiap locale browser.
- PBI-06: tidak ada factory untuk QuizAttempt dan EventRegistration, buat datanya lewat
  Model::create (beserta Quiz, LearningMaterial, CareerEvent). Siapkan kuis yang dikerjakan
  lebih dari sekali, nilai di tiap rentang badge (>=70, 50–69, <50), pendaftaran berstatus
  registered pada event mendatang, serta pendaftaran cancelled dan event completed.
  Untuk TC.UserProfile.020 buat > 10 data.
- TC.UserProfile.016: cek tombol "Buka Link" memiliki href = meeting_url dan target="_blank".
- TC.UserProfile.009 & 014: verifikasi file foto lama/baru di storage public.
- Untuk validasi negatif panggil ->disableHtmlValidation() sebelum klik simpan.
- Jangan mengubah kode aplikasi selain menambahkan atribut dusk pada view profil pengguna.
  Kalau ada langkah yang gagal karena bug aplikasi, JANGAN melemahkan assertion; laporkan.

Setelah selesai, jalankan php artisan dusk --group=fulca dan berikan tabel:
Case ID | method test | hasil (Pass/Fail) | catatan (penyebab jika Fail) | nama file screenshot.
Tabel ini akan saya salin ke kolom Status dan Evidence di test plan. Jangan commit.
```

---

## Prompt E — Oktavia (Landing Page: Hero Section & Artikel, PBI-22 sampai 25)

```text
Baca CLAUDE.md, prd.md, docs/dusk.md, dan docs/test-plan/test-plan-oktavia.csv.
Saya Oktavia. Ubah SEMUA test case di CSV tersebut (TC.LandingPage.001 sampai
TC.LandingPage.029) menjadi test Laravel Dusk, mengikuti aturan di docs/dusk.md.

Sebelum menulis test, baca kode fitur terkait agar selector, teks tombol, URL, dan pesan
sesuai implementasi: routes/web.php (super-admin.hero-sections.*, super-admin.articles.*,
home, articles.index/show), app/Http/Controllers/SuperAdmin/HeroSectionController.php &
ArticleController.php, app/Http/Controllers/PageController.php & ArticleController.php,
app/Http/Requests/SuperAdmin/*, resources/views/super-admin/**, resources/views/landing.blade.php,
resources/views/articles/*, dan tests/Feature/SuperAdmin/* & LandingAndArticleViewTest.php sebagai acuan.

Struktur file (folder tests/Browser/Oktavia, #[Group('oktavia')]):
- HeroSectionArticleCreateTest.php  → TC.LandingPage.001–010 (PBI-22)
- LandingPageViewTest.php           → TC.LandingPage.011–017 (PBI-23)
- HeroSectionArticleUpdateTest.php  → TC.LandingPage.018–024 (PBI-24)
- HeroSectionArticleDeleteTest.php  → TC.LandingPage.025–029 (PBI-25)

Ketentuan khusus:
- Batas ukuran gambar hero & sampul artikel adalah 3 MB; buat file uji > 3 MB untuk kasus negatif.
- TC.LandingPage.005: uji target URL 'bukan-url' dan 'javascript:alert(1)'.
- TC.LandingPage.008 & 021: verifikasi slug (unik dengan akhiran -2, dan perilaku centang
  "Perbarui slug sesuai judul baru").
- TC.LandingPage.014: buat beberapa hero aktif dan satu nonaktif; pastikan carousel tampil
  dan hero nonaktif tidak tampil. TC.LandingPage.015 & 027: hero default tampil tanpa error.
- TC.LandingPage.016: buat > 9 artikel published untuk menguji pagination /artikel.
- TC.LandingPage.019, 022, 026, 028: verifikasi file gambar lama/baru di storage public.
- TC.LandingPage.029: login sebagai user/admin, buka halaman apa saja yang memuat
  <meta name="csrf-token">, lalu kirim request DELETE lewat $browser->script() (form yang
  dibuat via JavaScript dengan _token dan _method=DELETE); pastikan halaman 403 dan data tetap ada.
- Untuk validasi negatif panggil ->disableHtmlValidation() sebelum klik simpan.
- Jangan mengubah kode aplikasi selain menambahkan atribut dusk pada view hero section,
  artikel, dan landing page. Kalau ada langkah yang gagal karena bug aplikasi,
  JANGAN melemahkan assertion; laporkan.

Setelah selesai, jalankan php artisan dusk --group=oktavia dan berikan tabel:
Case ID | method test | hasil (Pass/Fail) | catatan (penyebab jika Fail) | nama file screenshot.
Tabel ini akan saya salin ke kolom Status dan Evidence di test plan. Jangan commit.
```

---

## Prompt pendukung

**Kalau test gagal atau error**
```text
Test Dusk berikut gagal:
[tempel output php artisan dusk]
Screenshot kegagalan ada di tests/Browser/screenshots/[nama file].
Analisis apakah penyebabnya (a) test yang salah (selector, waktu tunggu, data uji), atau
(b) bug di aplikasi. Kalau (a), perbaiki test-nya. Kalau (b), jangan ubah test agar lolos;
jelaskan bug-nya dan usulkan perbaikan kode aplikasinya, tunggu persetujuan saya.
```

**Kalau test kadang lolos, kadang gagal (flaky)**
```text
Test [nama method] kadang lolos kadang gagal. Periksa apakah ada animasi modal/carousel,
redirect, atau render yang belum selesai saat assertion dijalankan. Ganti pause() tetap
dengan waitFor/waitForText/waitForLocation yang sesuai, lalu jalankan test itu 3 kali berturut-turut.
```

---

## Setelah semua test selesai

1. Salin tabel hasil dari Claude Code ke kolom **Status (Pass/Fail)** dan **Keterangan** di sheet masing-masing.
2. Ambil screenshot dari `tests/Browser/screenshots/<Nama>/`, unggah ke Drive atau tempel di kolom **Evidence**.
3. Commit test Dusk (tanpa screenshot), contoh: `git commit -m "SCRUM-xx Dusk test PBI 07-09"`, lalu push dan buat PR ke `Develop_Sprint1`.
4. Sebelum dikumpulkan, satu orang menjalankan semuanya sekaligus dari `Develop_Sprint1`: `php artisan dusk`.
