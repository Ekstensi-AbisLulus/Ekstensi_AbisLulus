# PRD — AbisLulus: Career Preparation & Mentoring Website

> Dokumen ini adalah sumber kebenaran tunggal (single source of truth) untuk membangun AbisLulus dengan bantuan AI coding agent (vibecoding). Disusun dari **Proposal Pengembangan Proyek (Tim Ekstensi, 4 Okt 2026)** dan **Rencana Kerja Sprint #1**. Bagian bertanda **[ASUMSI]** adalah keputusan yang tidak tertulis eksplisit di dokumen sumber — ubah bila tim memutuskan lain.

---

## 0. Ringkasan Cepat

| Item | Nilai |
|---|---|
| Nama produk | **AbisLulus** — E-Learning & Career Mentoring |
| Tim | Ekstensi (SI-48-06) |
| Target pengguna | Lulusan SMA/SMK & fresh graduate (17–24 th) |
| SDG | SDG 8, Target 8.6 (mengurangi pemuda NEET) |
| Prinsip utama | **Semua layanan utama gratis** — tidak ada pembayaran sama sekali |
| Stack | Laravel (PHP) + Blade + **Bootstrap 5** + **MySQL**, arsitektur MVC, server Apache |
| Repo | https://github.com/Ekstensi-AbisLulus/Ekstensi_AbisLulus |
| Jira | https://ekstensi-abislulus.atlassian.net/jira/software/projects/SCRUM/boards/1 |
| Metode | Scrum, 2 sprint; **seluruh PBI Sprint 1 selesai paling lambat 18 Nov 2026** |

---

## 1. Latar Belakang & Masalah

Lulusan baru kesulitan mendapatkan pembelajaran dan kegiatan pengembangan karier karena:

| ID | Masalah | Fitur yang menjawab |
|---|---|---|
| P1 | Layanan belajar & kegiatan karier tersebar di banyak sumber | E-Learning + Career Mentoring dalam satu platform |
| P2 | Biaya jadi hambatan | Semua alur utama gratis (0 transaksi pembayaran) |
| P3 | Sulit mengukur pemahaman setelah belajar daring | Kuis dengan skor + jumlah benar/salah langsung setelah submit |
| P4 | Tidak ada feedback dari pengguna sebelumnya | Rating (1–5) & ulasan, testimoni di landing page |

Data pendukung: BPS 2025 — 19,44% pemuda 15–24 th NEET (nasional), 24,84% (Jawa Barat); kuesioner 33 responden — 80% menganggap hard & soft skill sama penting.

---

## 2. Tujuan & Indikator Keberhasilan

1. Pengguna dapat mengakses E-Learning dan Career Mentoring dari satu aplikasi.
2. 0% transaksi pembayaran pada registrasi, kuis, dan pendaftaran event.
3. 100% kuis yang disubmit langsung menampilkan skor, jumlah benar, dan jumlah salah.
4. Rating (1–5) + ulasan langsung tampil terbarui di halaman detail materi.
5. Semua fitur lulus Black Box Testing.

---

## 3. Ruang Lingkup

### 3.1 Termasuk (In Scope)
- Autentikasi: registrasi (khusus role *user*), login berbasis peran, lupa password.
- Profil & riwayat aktivitas — pengguna dan admin.
- Manajemen E-Learning (materi video via **link**, bukan upload video).
- Manajemen Kuis (admin buat soal + kunci; user mengerjakan & melihat hasil).
- Manajemen Event Career Mentoring (admin CRUD + link Zoom/GMeet; user lihat, daftar, lihat link di profil).
- Rating & ulasan E-Learning.
- Testimoni pengguna yang tampil di landing page.
- Manajemen Landing Page (hero section + artikel) oleh Super Admin.
- Manajemen akun Admin oleh Super Admin.
- Dashboard ringkasan Super Admin.
- Super Admin dapat mengontrol/mengelola konten buatan Admin.

### 3.2 Di Luar Lingkup (JANGAN dibangun)
- Aplikasi mobile native.
- Payment gateway / transaksi / marketplace kelas.
- Hosting video mandiri (video hanya berupa link, mis. YouTube).
- **Sertifikat** kuis / ujian resmi.
- Integrasi API Zoom / Google Meet (link diinput manual oleh Admin).
- Rekomendasi berbasis AI.
- Integrasi rekrutmen / lowongan kerja.
- Registrasi mandiri untuk Admin.
- Verifikasi identitas pemerintah.

> ⚠️ Mockup menampilkan menu **"Lowongan Freelance"**, **"Softskills"**, dan tombol **"Unduh Sertifikat"**. Ketiganya **di luar lingkup** — jangan diimplementasikan. Ganti menu navbar sesuai §8.1.

---

## 4. Aktor & Hak Akses

| Role (`users.role`) | Siapa | Cara mendapat akun | Hak akses |
|---|---|---|---|
| `user` | Lulusan SMA/SMK, fresh graduate (learner) | Registrasi mandiri | Kelola profil, lihat & tonton materi, kerjakan kuis, lihat hasil, rating & ulasan materi, lihat & daftar event, lihat riwayat, kirim testimoni |
| `admin` | Pengelola konten / perwakilan komunitas | Dibuat oleh Super Admin | Kelola profil sendiri + riwayat; CRUD materi E-Learning, kuis, event Career Mentoring |
| `super_admin` | Pengelola platform | Seeder | CRUD akun admin, CRUD hero section & artikel, dashboard ringkasan, **boleh mengakses & mengelola semua konten admin** |

Aturan:
- `users.status = inactive` → tidak bisa login (tampilkan pesan "Akun dinonaktifkan").
- Halaman yang butuh login diproteksi middleware `auth`; halaman per peran diproteksi middleware `role:...`.
- Data akun seorang pengguna tidak boleh bisa diakses/diubah pengguna lain (cek kepemilikan di setiap update/delete — gunakan Policy).
- Admin hanya boleh mengubah/menghapus konten miliknya sendiri **[ASUMSI]**; Super Admin boleh semua.

---

## 5. Stack & Konvensi Teknis

| Komponen | Pilihan |
|---|---|
| Backend | Laravel (versi stabil terbaru yang dipakai tim), PHP |
| View | Blade templates |
| CSS | **Bootstrap 5** (+ Bootstrap Icons). Jangan pakai Tailwind. |
| DB | MySQL |
| Web server | Apache (lokal boleh `php artisan serve` / Laragon / XAMPP) |
| Auth | Session-based Laravel. Rekomendasi: `laravel/ui` (`php artisan ui bootstrap --auth`) atau controller auth manual — **[ASUMSI]**, pilih salah satu dan konsisten |
| Lupa password | Fitur reset bawaan Laravel (`Password` broker, tabel `password_reset_tokens`). Dev: `MAIL_MAILER=log` |
| Upload gambar | `storage/app/public` + `php artisan storage:link` (avatar, thumbnail, hero image, cover artikel) |
| Desain | Figma (mockup di proposal §4.7) |

Konvensi kode:
- Nama tabel/kolom bahasa Inggris snake_case sesuai ERD (§6). Label UI bahasa Indonesia.
- Validasi lewat **FormRequest**; otorisasi lewat **Policy**; akses peran lewat middleware `EnsureUserHasRole` (`role:admin,super_admin`).
- Gunakan Resource Controller & route model binding.
- Setiap aksi simpan/hapus menampilkan flash message sukses/gagal (Bootstrap alert/toast).
- Hapus selalu lewat modal konfirmasi; tombol "Batal" mengembalikan ke daftar tanpa perubahan.
- Daftar data pakai pagination (10/halaman) + pencarian sederhana by judul.
- Enum disimpan sebagai string di kolom `enum` MySQL; buat PHP Enum class di `app/Enums`.

---

## 6. Model Data (dari ERD & Class Diagram)

### 6.1 Tabel

**users**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | hashed |
| role | ENUM(`user`,`admin`,`super_admin`) | default `user` |
| status | ENUM(`active`,`inactive`) | default `active` |
| remember_token, timestamps | | bawaan Laravel |

**user_profiles** (1–0..1 dengan users; PK = FK `user_id`)
| Kolom | Tipe | Catatan |
|---|---|---|
| user_id | BIGINT PK, FK → users.id | cascade delete |
| phone | VARCHAR(25) NULL | "No. HP / WhatsApp" |
| education_level | VARCHAR(50) NULL | Status: Pelajar SMA/SMK / Mahasiswa / Fresh Graduate |
| institution | VARCHAR(150) NULL | |
| graduation_year | YEAR NULL | |
| bio | TEXT NULL | |
| birth_date | DATE NULL | **[ASUMSI — dari mockup profil]** |
| gender | ENUM(`male`,`female`) NULL | **[ASUMSI — dari mockup]** |
| address | VARCHAR(255) NULL | "Alamat Domisili" **[ASUMSI — dari mockup]** |
| linkedin_url | VARCHAR(2048) NULL | "Link CV / Profile LinkedIn" **[ASUMSI — dari mockup]** |
| avatar_path | VARCHAR(2048) NULL | foto profil (sidebar profil) **[ASUMSI]** |
| timestamps | | |

> Profil admin memakai tabel yang sama (`user_profiles`).

**learning_materials**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| admin_id | FK → users.id | pembuat |
| title | VARCHAR(200) | wajib |
| description | TEXT | wajib |
| video_url | VARCHAR(2048) | wajib, URL valid (YouTube/Drive); dikonversi ke embed saat ditampilkan |
| thumbnail_url | VARCHAR(2048) NULL | URL atau path upload |
| is_published | BOOLEAN | default true; hanya yang published tampil ke user |
| timestamps | | |

**quizzes**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| material_id | FK → learning_materials.id **NULL** | kuis opsional terkait materi |
| created_by | FK → users.id | |
| title | VARCHAR(200) | |
| description | TEXT NULL | |
| timestamps | | |

**questions** — `id`, `quiz_id` FK (cascade), `question_text` TEXT, `sort_order` INT, timestamps
**answer_options** — `id`, `question_id` FK (cascade), `option_text` TEXT, `is_correct` BOOLEAN, `sort_order` INT
> Aturan: setiap soal minimal 2 opsi dan **tepat 1** opsi benar (pilihan ganda).

**quiz_attempts**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| user_id | FK → users.id | |
| quiz_id | FK → quizzes.id | |
| score | DECIMAL(5,2) | `correct / total × 100` |
| correct_count | INT | |
| incorrect_count | INT | soal tak dijawab dihitung salah |
| started_at | DATETIME | |
| completed_at | DATETIME NULL | |

**quiz_answers** — `id`, `attempt_id` FK, `question_id` FK, `selected_option_id` FK NULL → answer_options.id, `is_correct` BOOLEAN, `answered_at` DATETIME; UNIQUE(`attempt_id`,`question_id`)

**learning_reviews**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| user_id | FK → users.id | |
| material_id | FK → learning_materials.id | |
| rating | TINYINT | 1–5, wajib |
| review_text | TEXT NULL | |
| timestamps | | |
| | UNIQUE(`user_id`,`material_id`) | 1 ulasan per user per materi (edit, bukan tambah) |

**career_events**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| admin_id | FK → users.id | |
| title | VARCHAR(200) | |
| description | TEXT | |
| start_at, end_at | DATETIME | end_at > start_at |
| meeting_url | VARCHAR(2048) | link Zoom/GMeet |
| platform | ENUM(`zoom`,`google_meet`,`other`) | |
| capacity | INT NULL | NULL = tanpa batas |
| status | ENUM(`draft`,`published`,`cancelled`,`completed`) | hanya `published` yang bisa didaftar |
| category | ENUM(`hardskill`,`softskill`) NULL | **[ASUMSI — mockup riwayat kegiatan punya kolom Kategori]** |
| thumbnail_url | VARCHAR(2048) NULL | **[ASUMSI — mockup ada kolom Sampul]** |
| timestamps | | |

**event_registrations** — `id`, `user_id` FK, `event_id` FK, `status` ENUM(`registered`,`cancelled`,`attended`), `registered_at` DATETIME; UNIQUE(`user_id`,`event_id`)

**articles**
| Kolom | Tipe | Catatan |
|---|---|---|
| id | BIGINT PK | |
| super_admin_id | FK → users.id | |
| title | VARCHAR(200) | |
| slug | VARCHAR(220) UNIQUE | auto dari title |
| excerpt | TEXT NULL | |
| content | LONGTEXT | |
| cover_image_url | VARCHAR(2048) NULL | |
| status | ENUM(`draft`,`published`) | |
| published_at | DATETIME NULL | diisi saat publish |
| timestamps | | |

**hero_sections** — `id`, `super_admin_id` FK, `title` VARCHAR(200), `subtitle` TEXT NULL, `image_url` VARCHAR(2048), `target_url` VARCHAR(2048) NULL, `is_active` BOOLEAN, timestamps

**testimonials** — *tidak ada di ERD, dibutuhkan FR-10 / PBI-32–33* **[ASUMSI]**
`id`, `user_id` FK, `content` TEXT, `rating` TINYINT NULL (1–5), `is_visible` BOOLEAN default true (Super Admin bisa menyembunyikan), timestamps

**password_reset_tokens**, **sessions**, dll. — bawaan Laravel.

### 6.2 Relasi Eloquent
- `User` hasOne `UserProfile`; hasMany `LearningMaterial` (admin_id), `Quiz` (created_by), `CareerEvent` (admin_id), `QuizAttempt`, `LearningReview`, `EventRegistration`, `Article`, `HeroSection`, `Testimonial`.
- `LearningMaterial` hasMany `Quiz`, `LearningReview`; accessor `averageRating`, `reviewsCount`.
- `Quiz` hasMany `Question` (ordered by sort_order), `QuizAttempt`; belongsTo `LearningMaterial` (nullable).
- `Question` hasMany `AnswerOption`.
- `QuizAttempt` hasMany `QuizAnswer`; method `calculateScore()`.
- `CareerEvent` hasMany `EventRegistration`; belongsToMany `User` via registrations; accessor `remainingSeats`.

### 6.3 Seeder
- `RoleUserSeeder`: 1 super admin (`superadmin@abislulus.test` / `password`), 2 admin, 5 user, semua dengan profil.
- `ContentSeeder` (dev only): 6 materi, 2 kuis × 5 soal × 4 opsi, 4 event, 3 artikel, 1 hero aktif, beberapa review & testimoni.

---

## 7. Spesifikasi Fitur (per Product Backlog Item)

Format: **PBI — user story** → kriteria penerimaan (AC). FR = Functional Requirement di proposal.

### F1. Autentikasi (FR-01) — Kolektif
**PBI-01 Registrasi** — Sebagai lulusan baru, saya ingin mendaftar dengan data diri & email agar bisa mengakses program.
- Form: nama lengkap, email, password, konfirmasi password.
- Validasi: semua wajib, email format valid & unik, password min 8 karakter & cocok dengan konfirmasi.
- Akun baru otomatis `role=user`, `status=active`, dan baris `user_profiles` kosong dibuat.
- Sukses → auto login → redirect ke halaman profil dengan pesan "Lengkapi profilmu".
- Tidak ada pilihan role di form (admin tidak bisa daftar sendiri).

**PBI-02 Login** — Sebagai user/admin/super admin, saya ingin login agar dapat mengakses fitur sesuai hak akses.
- Form: email, password, "Ingat saya", link "Lupa password?", link "Daftar sekarang".
- Gagal → pesan "Email atau password salah" (tanpa membedakan mana yang salah).
- `status=inactive` → ditolak dengan pesan khusus.
- Redirect setelah login: `user` → `/` (beranda), `admin` → `/admin/dashboard`, `super_admin` → `/super-admin/dashboard`.
- Logout tersedia di dropdown avatar navbar.
- Rate limit percobaan login (bawaan Laravel throttle).

**PBI-03 Lupa Password**
- Input email → kirim link reset (token, kedaluwarsa 60 menit). Respons sama baik email terdaftar maupun tidak.
- Halaman reset: password baru + konfirmasi → sukses → redirect login dengan pesan.

### F2. Profil & Riwayat Pengguna (FR-03, UC-02) — Fulca
**PBI-04 Melengkapi profil** — Halaman `/profil`, layout sidebar (avatar, menu: Profil · Aktivitas E-Learning · Aktivitas Kegiatan) + form.
- Field: nama lengkap, tanggal lahir, gender, No. HP/WA, alamat domisili, status (Pelajar SMA/SMK / Mahasiswa / Fresh Graduate), institusi, tahun lulus, bio, link CV/LinkedIn, foto profil.
- Wajib: nama, No. HP, status. Lainnya opsional. No. HP hanya angka/+, 10–15 digit; URL valid; foto jpg/png ≤ 2 MB.
- Tombol "Simpan Perubahan" → flash sukses. Error validasi tampil di bawah field.

**PBI-05 Mengedit profil** — Form yang sama terisi data lama; perubahan tersimpan & langsung tampil. Opsional: ubah password (password lama wajib).

**PBI-06 Riwayat aktivitas E-Learning & hasil kuis**
- Tab **Aktivitas E-Learning**: tabel thumbnail, judul materi, judul kuis (link ke kuis), nilai kuis terakhir, tanggal. *(Tanpa kolom "Unduh Sertifikat".)*
- Tab **Aktivitas Kegiatan**: tabel sampul, judul kegiatan, kategori, tanggal, status pendaftaran, **link meeting** (Zoom/GMeet) — hanya untuk event yang didaftar.
- Empty state: "Belum ada riwayat aktivitas".
- Catatan dependensi: data kuis & pendaftaran event baru ada di Sprint 2 → di Sprint 1 bangun halamannya + empty state; query otomatis terisi setelah fitur kuis/event selesai.

### F3. Profil & Riwayat Admin (FR-04, UC-03) — Fakhri
**PBI-07 Melengkapi profil admin**, **PBI-08 Mengedit profil admin** — sama seperti F2 tetapi di panel admin (`/admin/profil`), field: nama, No. HP, institusi/komunitas, bio, foto.
**PBI-09 Riwayat aktivitas admin** — halaman `/admin/riwayat` berisi tiga tab: materi E-Learning, kuis, dan event yang **dibuat oleh admin ini**, dengan tanggal dibuat/diubah dan tombol aksi cepat (edit) ke masing-masing. Empty state bila belum ada.

### F4. Manajemen E-Learning (FR-05, UC-04) — Aqeel
**PBI-10 Tambah materi** — Form: judul, deskripsi, link video, thumbnail (upload/URL), status publish. Validasi: judul ≤ 200, deskripsi wajib, link video URL valid.
**PBI-11 Daftar materi (admin)** — Halaman "Manajemen E-Learning": tabel (checkbox, thumbnail, judul, pembuat, tanggal unggah, aksi lihat/edit/hapus), tombol "+ Tambah Materi Baru", pencarian by judul, pagination.
**PBI-12 Edit materi** — Form terisi data lama (judul, deskripsi, link video, thumbnail, publish).
**PBI-13 Hapus materi** — Modal konfirmasi; batal → tidak terjadi apa-apa. Menghapus materi juga menghapus review terkait; kuis terkait `material_id` di-set NULL **[ASUMSI]**.

Sisi user (dibutuhkan agar fitur bisa didemokan):
- `/e-learning`: grid kartu (thumbnail, judul, potongan deskripsi, rata-rata rating, tombol "Mulai Belajar"), kotak pencarian.
- `/e-learning/{id}`: video embed, judul, tombol **"Mulai Kuis"** (jika ada kuis), deskripsi materi, rating rata-rata + daftar ulasan, form rating/ulasan (jika login).
- Materi bisa dilihat tamu; kuis & ulasan butuh login **[ASUMSI]**.

### F5. Manajemen Kuis (FR-06, UC-05)
**PBI-14 Buat kuis** — admin pilih materi (opsional), isi judul & deskripsi, lalu tambah soal dinamis (tambah/hapus baris soal & opsi via JS), tandai 1 jawaban benar per soal. Validasi: ≥1 soal, ≥2 opsi/soal, tepat 1 benar.
**PBI-15 Lihat kuis** — daftar kuis + detail (soal, opsi, kunci ditandai), jumlah percobaan user.
**PBI-16 Edit kuis** — ubah judul, soal, opsi, kunci. **[ASUMSI]** Mengubah kuis tidak mengubah skor attempt lama.
**PBI-17 Hapus kuis** — modal konfirmasi, cascade soal/opsi/attempt.

Sisi user:
- Halaman kuis: judul, deskripsi, semua soal dalam satu halaman (radio button), tombol "Kembali ke Materi" & "Submit Quiz".
- Submit → simpan `quiz_attempts` + `quiz_answers` dalam satu DB transaction → halaman hasil: **skor, jumlah benar, jumlah salah**, ringkasan per soal (jawaban user vs benar).
- Kuis opsional, boleh diulang; riwayat menampilkan percobaan terakhir **[ASUMSI]**.

### F6. Manajemen Event Career Mentoring (FR-07, UC-06)
**PBI-18 Buat event** — Form: judul, deskripsi, kategori, sampul, tanggal & jam mulai/selesai, platform (Zoom/GMeet/Lainnya), link meeting, kapasitas (opsional), status.
**PBI-19 Lihat event (admin)** — "Manajemen Kegiatan": filter kategori, tabel (sampul, judul, kategori, tanggal, aksi), tombol "+ Tambah Kegiatan", detail + daftar peserta.
**PBI-20 Edit event** — termasuk memperbarui link meeting.
**PBI-21 Hapus event** — modal konfirmasi. **[ASUMSI]** Jika sudah ada pendaftar, sarankan ubah status ke `cancelled` alih-alih hapus.

Sisi user (in scope, belum ada PBI tersendiri — tambahkan saat Sprint 2):
- `/kegiatan`: daftar event `published` mendatang (kartu, filter kategori).
- `/kegiatan/{id}`: judul, penyelenggara, tanggal, gambar, deskripsi, sisa kuota, tombol **"Daftar Sekarang"** (CTA "Segera ikut!").
- Daftar: harus login, event `published`, belum lewat, kuota tersedia, belum pernah daftar. Sukses → status `registered`, muncul di riwayat profil.
- **Link meeting hanya ditampilkan ke user yang sudah terdaftar.**
- User dapat membatalkan pendaftaran sebelum event mulai **[ASUMSI]**.

### F7. Manajemen Landing Page (FR-08, UC-07) — Oktavia
**PBI-22 Tambah hero & artikel** — Hero: judul, subjudul, gambar (upload), target URL, aktif. Artikel: judul, slug otomatis, ringkasan, konten (textarea; editor WYSIWYG opsional), cover, status draft/published.
**PBI-23 Lihat hero & artikel** — daftar di panel Super Admin + pratinjau; di landing publik tampil hero aktif (carousel jika >1) dan 3–6 artikel terbaru berstatus published.
**PBI-24 Ubah hero & artikel** — form terisi data lama; ganti gambar menghapus file lama dari storage.
**PBI-25 Hapus hero & artikel** — modal konfirmasi; file gambar ikut dihapus.

Landing page publik `/` berisi: navbar, hero, highlight layanan (E-Learning & Career Mentoring), materi populer, event mendatang, artikel terbaru, testimoni, footer. Halaman `/artikel` & `/artikel/{slug}`.

### F8. Manajemen Akun Admin (FR-11, UC-08)
**PBI-26 Buat akun admin** — form nama, email, password; role otomatis `admin`.
**PBI-27 Daftar akun admin** — tabel nama, email, status, tanggal dibuat, aksi; pencarian.
**PBI-28 Ubah akun admin** — nama, email, status aktif/nonaktif, reset password (opsional diisi).
**PBI-29 Hapus akun admin** — modal konfirmasi. **[ASUMSI]** Konten milik admin yang dihapus dipindah kepemilikannya ke super admin yang menghapus (atau gunakan soft-delete user) agar materi/event tidak hilang.
- Super Admin tidak bisa menghapus/menonaktifkan dirinya sendiri.

### F9. Rating & Ulasan E-Learning (FR-09, UC-09)
**PBI-30 Beri rating** — di detail materi: pilih bintang 1–5 (wajib) + ulasan teks (opsional, ≤ 1000 karakter). Jika sudah pernah, form berubah jadi mode **Edit**.
**PBI-31 Lihat rating** — rata-rata (1 desimal) + jumlah ulasan tampil di bawah deskripsi materi dan di kartu daftar materi; daftar ulasan (nama, bintang, teks, tanggal), terbaru di atas. Belum ada → "Belum ada ulasan".
- Validasi rating di luar 1–5 → pesan error.

> Proposal juga menyebut **rating & ulasan untuk kegiatan Career Mentoring**, tetapi tidak ada PBI/tabelnya. Opsional (stretch): tabel `event_reviews` dengan pola sama, hanya untuk user berstatus terdaftar setelah event selesai.

### F10. Testimoni Pengguna (FR-10)
**PBI-32 Beri testimoni** — user login dapat mengirim testimoni tentang AbisLulus (teks + rating opsional) dari halaman profil/landing. 1 testimoni per user, bisa diedit **[ASUMSI]**.
**PBI-33 Lihat testimoni** — section testimoni di landing page (kartu/carousel: avatar, nama, status, isi). Super Admin dapat menyembunyikan testimoni yang tidak pantas.

### F11. Dashboard (FR-02)
**PBI-34 Dashboard Super Admin** — kartu ringkasan: jumlah user, jumlah admin, jumlah materi E-Learning, jumlah event; opsional: jumlah attempt kuis, pendaftar event, grafik registrasi user per bulan.
- Dashboard admin **[ASUMSI]**: jumlah materi/kuis/event miliknya + pendaftar terbaru.

---

## 8. Informasi Arsitektur UI

### 8.1 Navbar publik/user
Logo AbisLulus · **Home** · **E-Learning** · **Kegiatan** · **Artikel** · **Tentang Kami** · (tamu: tombol Masuk / Daftar | login: avatar dropdown → Profil, Riwayat, Keluar)

### 8.2 Panel Admin & Super Admin
Layout sidebar kiri (avatar + menu) + konten kanan, tabel bergaya card.
- Admin: Dashboard · Profil · Riwayat · Manajemen E-Learning · Manajemen Kuis · Manajemen Kegiatan
- Super Admin: semua menu Admin + Manajemen Akun Admin · Hero Section · Artikel · Testimoni

### 8.3 Gaya visual (dari mockup)
- Latar putih/abu muda, kartu putih rounded + shadow halus, aksen **biru** (`#2563EB`-ish) untuk tombol utama.
- Font sans-serif modern (mis. Poppins/Inter via Google Fonts).
- Aksi tabel: ikon lingkaran — lihat (biru), edit (hijau), hapus (merah).
- Login: ilustrasi di kiri, form di kanan. Responsif hingga layar smartphone.

### 8.4 Peta Route

```
# Publik
GET  /                          landing
GET  /e-learning                daftar materi
GET  /e-learning/{material}     detail materi
GET  /kegiatan                  daftar event
GET  /kegiatan/{event}          detail event
GET  /artikel, /artikel/{slug}
GET  /tentang

# Guest
GET|POST /register, /login
GET|POST /forgot-password, /reset-password/{token}

# Auth + role:user
GET|PUT  /profil
GET      /profil/riwayat/e-learning
GET      /profil/riwayat/kegiatan
GET      /kuis/{quiz}                     kerjakan
POST     /kuis/{quiz}/submit
GET      /kuis/hasil/{attempt}
POST|PUT /e-learning/{material}/ulasan
POST     /kegiatan/{event}/daftar
DELETE   /kegiatan/{event}/daftar
POST|PUT /testimoni
POST     /logout

# prefix /admin, role:admin,super_admin
dashboard · profil · riwayat
resource materials · resource quizzes · resource events (+ /events/{event}/peserta)

# prefix /super-admin, role:super_admin
dashboard · resource admins · resource hero-sections · resource articles · testimonials (index, toggle visibility)
```

---

## 9. Kebutuhan Non-Fungsional

| ID | Aspek | Kriteria penerimaan |
|---|---|---|
| NFR-01 | Performance | Halaman utama termuat ≤ 3 detik pada koneksi normal (eager loading, hindari N+1, pagination) |
| NFR-02 | Security | Fitur terautentikasi wajib login; data akun tidak bisa diakses user lain; CSRF aktif; password di-hash; validasi semua input; escape output Blade (`{{ }}`) |
| NFR-03 | Reliability | Login, E-Learning, kuis, dan pendaftaran event berjalan tanpa error pada penggunaan normal; operasi multi-tabel pakai DB transaction |
| NFR-04 | Usability | Layanan utama bisa ditemukan & dipakai tanpa panduan; pesan error berbahasa Indonesia yang jelas |
| NFR-05 | Scalability | Tetap berjalan saat data user/materi/kuis/event bertambah (index FK, pagination) |
| NFR-06 | Compliance | Data pribadi hanya dipakai sesuai kebutuhan layanan; tidak ditampilkan publik kecuali nama & avatar di ulasan/testimoni |

---

## 10. Rencana Sprint

### Sprint 1 — fondasi (29 Okt – 16 Nov 2026, batas akhir 18 Nov)
Objektif: autentikasi, e-learning, profil & riwayat (user & admin), landing page.

| Minggu | Kolektif | Aqeel (E-Learning) | Fakhri (Profil Admin) | Fulca (Profil User) | Oktavia (Landing Page) |
|---|---|---|---|---|---|
| 1 · 29 Okt–4 Nov | PBI-01 Registrasi | PBI-10, PBI-11 | PBI-07 | PBI-04 | PBI-22, PBI-23 |
| 2 · 5–11 Nov | PBI-02 Login | PBI-12 | PBI-08 | PBI-05 | PBI-24 |
| 3 · 12–16 Nov | PBI-03 Lupa Password | PBI-13 | PBI-09 | PBI-06 | PBI-25 |

**Tugas fondasi (kerjakan paling awal, prasyarat semua fitur):** inisialisasi project Laravel + Bootstrap, **semua migration dari §6 sekaligus**, model + relasi, enum, middleware role, layout Blade (publik, panel admin), seeder super admin. Dengan begitu PBI-06 dan PBI-09 bisa langsung membaca tabel kuis/event walau fiturnya belum dibuat.

### Sprint 2 — fitur lanjutan (Pekan 11–15)
PBI-14–17 (Kuis), PBI-18–21 (Event admin) + pendaftaran event oleh user, PBI-26–29 (Akun admin), PBI-30–31 (Rating), PBI-32–33 (Testimoni), PBI-34 (Dashboard), dashboard per role, integrasi & pengujian menyeluruh. Pembagian assignee ditentukan saat Sprint Planning 2.

Aturan Scrum dari dosen: tiap anggota minimal 1 fitur ≥ 3 PBI, tidak ada pekan kosong per anggota, semua PBI dipindah ke Jira (assignee, tanggal, user story, mockup), daily report di Slack.

---

## 11. Definition of Done (per PBI)
- [ ] Semua AC di §7 terpenuhi dan dicoba manual di Chrome (desktop + mobile view).
- [ ] Validasi & pesan error berjalan; akses peran yang salah ditolak (403/redirect).
- [ ] Tidak ada error di log Laravel; tidak ada query N+1 yang jelas.
- [ ] Ada Feature Test minimal untuk alur sukses + satu alur gagal (`php artisan test`) **[ASUMSI — disarankan]**.
- [ ] Kode di-push ke branch fitur, PR ke `main`/`develop`, issue Jira dipindah ke Done.
- [ ] Black Box Test case dicatat (input, langkah, hasil harapan, hasil aktual).

---

## 12. Isu & Inkonsistensi di Dokumen Sumber (perlu diputuskan tim)
1. **Penomoran PBI di Tabel 28 & 29 proposal tidak cocok** dengan Product Backlog (menyebut PBI-35–41 yang tidak ada). PRD ini memakai **Sprint Planning #1** untuk Sprint 1 dan Product Backlog (PBI-01–34) untuk sisanya.
2. **FR di skenario use case salah rujuk** (mis. UC-04 → FR-03, UC-07 → FR-09). Rujukan yang benar ada di §7.
3. **Testimoni (FR-10)** tidak punya tabel di ERD → ditambahkan tabel `testimonials`.
4. **Pendaftaran event oleh user** ada di ruang lingkup tapi tidak punya PBI → tambahkan PBI baru di Sprint 2.
5. **Rating kegiatan** ada di ruang lingkup tapi tidak di backlog/ERD → stretch goal.
6. Mockup memuat **sertifikat, Lowongan Freelance, Softskills** yang di luar lingkup → tidak dibangun.
7. Field profil di mockup (tgl lahir, gender, alamat, LinkedIn) tidak ada di ERD → ditambahkan ke `user_profiles`.
8. Proposal §4.3 menyebut "Bursa Talenta" — salah nama, yang benar AbisLulus.

---

## 13. Panduan untuk AI Coding Agent (Vibecoding)

Tempel bagian ini (atau seluruh file) ke agent di awal sesi.

**Aturan kerja**
1. Baca PRD ini dulu. Jangan bangun apa pun yang ada di §3.2 (di luar lingkup).
2. Kerjakan **satu PBI per sesi/prompt**. Setelah selesai, sebutkan file yang diubah dan cara mengetesnya.
3. Ikuti skema §6 persis (nama tabel/kolom). Kalau butuh kolom baru, tanyakan dulu.
4. Gunakan Blade + Bootstrap 5, FormRequest, Policy, middleware `role`. Jangan tambah framework frontend (React/Vue/Tailwind/Livewire) kecuali diminta.
5. UI berbahasa Indonesia; kode & nama variabel bahasa Inggris.
6. Jika ada kode dari versi project lama, porting logikanya ke struktur ini, jangan menyalin mentah-mentah.
7. Jangan ubah migration yang sudah di-push; buat migration baru untuk perubahan.

**Urutan prompt yang disarankan**
1. "Setup project Laravel + Bootstrap 5 + MySQL sesuai §5 PRD. Buat layout `layouts/app` (navbar §8.1) dan `layouts/panel` (sidebar §8.2)."
2. "Buat semua migration, model, relasi, dan enum sesuai §6, plus seeder §6.3."
3. "Buat middleware `role` dan grup route sesuai §8.4 (controller boleh kosong dulu)."
4. Lalu per PBI: "Implementasikan **PBI-XX** sesuai §7 PRD, termasuk validasi, flash message, dan Feature Test."

**Format commit** — `feat(PBI-10): tambah materi e-learning` / `fix(PBI-02): ...`
**Branch** — `feature/pbi-10-tambah-materi` (satu branch per anggota/fitur sesuai evidence GitHub tim).

---

## 14. Tim

| Nama | NIM | Fitur Sprint 1 |
|---|---|---|
| Fulca Liya Rufaidah | 102022580003 | Profil & Riwayat (Pengguna) |
| Oktavia Rosa Laili | 102022580013 | Landing Page |
| Muhammad Fakhri Abdullah | 102022580033 | Profil & Riwayat (Admin) |
| Muhammad Aqeel Jabbar | 102022580052 | E-Learning |
| Kolektif | — | Autentikasi |
