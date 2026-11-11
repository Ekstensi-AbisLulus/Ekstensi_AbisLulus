<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Enums\EventCategory;
use App\Enums\EventPlatform;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Article;
use App\Models\CareerEvent;
use App\Models\HeroSection;
use App\Models\LearningMaterial;
use App\Models\LearningReview;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * PRD §6.3 — data contoh untuk development (jangan dijalankan di production).
 * Butuh RoleUserSeeder dijalankan lebih dulu.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $superAdmin = User::where('role', UserRole::SuperAdmin)->firstOrFail();
            $admins = User::where('role', UserRole::Admin)->orderBy('id')->get();
            $users = User::where('role', UserRole::User)->orderBy('id')->get();

            $materials = $this->seedMaterials($admins->all());
            $quizzes = $this->seedQuizzes($materials, $admins->all());
            $events = $this->seedEvents($admins->all());

            $this->seedArticlesAndHero($superAdmin);
            $this->seedReviews($materials, $users->all());
            $this->seedAttempt($quizzes[0], $users[0]);
            $this->seedRegistrations($events, $users->all());
            $this->seedTestimonials($users->all());

            $this->seedAdminHistoryDemo($admins->first(), $materials);
        });
    }

    /**
     * PBI-09 — data tambahan milik admin pertama agar halaman Riwayat Aktivitas bisa didemokan:
     * >10 materi (pagination), materi draf, kuis tanpa materi, event draf & dibatalkan,
     * dengan tanggal dibuat/diubah yang bervariasi.
     *
     * @param  list<LearningMaterial>  $materials
     */
    private function seedAdminHistoryDemo(User $admin, array $materials): void
    {
        $extraMaterials = [
            ['Dasar Desain Grafis dengan Canva', true],
            ['Menulis Surat Lamaran Kerja', true],
            ['Etika Profesional di Kantor', true],
            ['Pengenalan Google Workspace', true],
            ['Teknik Negosiasi Gaji Pertama', true],
            ['Membangun Portofolio Digital', false],
            ['Persiapan Psikotes Kerja', false],
            ['Dasar Public Speaking', true],
        ];

        foreach ($extraMaterials as $i => [$title, $published]) {
            $created = now()->subDays(30 - $i * 3)->setTime(9, 0);

            LearningMaterial::forceCreate([
                'admin_id' => $admin->id,
                'title' => $title,
                'description' => 'Materi contoh untuk demo riwayat aktivitas admin: '.strtolower($title).'.',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'thumbnail_url' => $i === 6 ? null : 'https://picsum.photos/seed/abislulus-demo-'.($i + 1).'/640/360',
                'is_published' => $published,
                'created_at' => $created,
                'updated_at' => $created->copy()->addDays($i % 3),
            ]);
        }

        $extraQuizzes = [
            ['Kuis: Etika Profesional', $materials[0]->id, 12],
            ['Kuis Umum: Kesiapan Kerja', null, 5],
        ];

        foreach ($extraQuizzes as [$title, $materialId, $daysAgo]) {
            $quiz = Quiz::forceCreate([
                'material_id' => $materialId,
                'created_by' => $admin->id,
                'title' => $title,
                'description' => 'Kuis contoh untuk demo riwayat aktivitas admin.',
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays($daysAgo),
            ]);

            $questions = [
                ['Sikap yang tepat saat terlambat masuk kerja adalah...', ['Diam saja', 'Memberi kabar ke atasan', 'Pulang lagi', 'Menyalahkan macet'], 1],
                ['Dokumen yang umumnya dilampirkan saat melamar kerja...', ['CV', 'Rapor SD', 'Akta tanah', 'Struk belanja'], 0],
            ];

            foreach ($questions as $q => [$text, $options, $correctIndex]) {
                $question = $quiz->questions()->create(['question_text' => $text, 'sort_order' => $q + 1]);

                foreach ($options as $o => $optionText) {
                    $question->options()->create([
                        'option_text' => $optionText,
                        'is_correct' => $o === $correctIndex,
                        'sort_order' => $o + 1,
                    ]);
                }
            }
        }

        $extraEvents = [
            ['Webinar: Mengenal Dunia Startup', EventStatus::Draft, EventCategory::Hardskill, 21, 8],
            ['Mentoring: Mengelola Stres Saat Mencari Kerja', EventStatus::Cancelled, null, 3, 15],
        ];

        foreach ($extraEvents as $i => [$title, $status, $category, $startInDays, $daysAgo]) {
            $start = now()->addDays($startInDays)->setTime(19, 0);

            CareerEvent::forceCreate([
                'admin_id' => $admin->id,
                'title' => $title,
                'description' => 'Kegiatan contoh untuk demo riwayat aktivitas admin.',
                'start_at' => $start,
                'end_at' => $start->copy()->addHours(2),
                'meeting_url' => 'https://meet.google.com/demo-abis-lulus'.$i,
                'platform' => EventPlatform::GoogleMeet,
                'capacity' => 40,
                'status' => $status,
                'category' => $category,
                'thumbnail_url' => null,
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays($daysAgo),
            ]);
        }
    }

    /**
     * @param  list<User>  $admins
     * @return list<LearningMaterial>
     */
    private function seedMaterials(array $admins): array
    {
        $data = [
            ['Cara Membuat CV yang Dilirik HRD', 'Pelajari struktur CV yang ringkas, cara menonjolkan pengalaman organisasi dan proyek sekolah, serta kesalahan umum yang membuat CV langsung ditolak.'],
            ['Persiapan Wawancara Kerja Pertama', 'Simulasi pertanyaan wawancara yang paling sering muncul, teknik menjawab dengan metode STAR, dan tips menghadapi rasa gugup.'],
            ['Dasar Microsoft Excel untuk Dunia Kerja', 'Rumus dasar (SUM, AVERAGE, IF, VLOOKUP), format tabel, dan membuat grafik sederhana untuk laporan kantor.'],
            ['Komunikasi Efektif di Tempat Kerja', 'Cara menyampaikan ide dengan jelas, menulis email profesional, dan berkomunikasi dengan atasan maupun rekan kerja.'],
            ['Mengenal Personal Branding di LinkedIn', 'Langkah membuat profil LinkedIn yang menarik, menulis headline dan ringkasan, serta membangun jaringan profesional sejak dini.'],
            ['Manajemen Waktu untuk Fresh Graduate', 'Teknik Pomodoro, prioritas dengan matriks Eisenhower, dan menyusun rencana belajar sambil mencari kerja.'],
        ];

        $materials = [];

        foreach ($data as $i => [$title, $description]) {
            $materials[] = LearningMaterial::create([
                'admin_id' => $admins[$i % count($admins)]->id,
                'title' => $title,
                'description' => $description,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'thumbnail_url' => 'https://picsum.photos/seed/abislulus-materi-'.($i + 1).'/640/360',
                'is_published' => true,
            ]);
        }

        return $materials;
    }

    /**
     * 2 kuis × 5 soal × 4 opsi, tepat 1 opsi benar per soal.
     *
     * @param  list<LearningMaterial>  $materials
     * @param  list<User>  $admins
     * @return list<Quiz>
     */
    private function seedQuizzes(array $materials, array $admins): array
    {
        $data = [
            [
                'material' => $materials[0],
                'title' => 'Kuis: Membuat CV yang Menarik',
                'description' => 'Uji pemahamanmu tentang penulisan CV.',
                'questions' => [
                    ['Berapa panjang ideal CV untuk fresh graduate?', ['1 halaman', '3 halaman', '5 halaman', 'Tidak ada batas'], 0],
                    ['Bagian apa yang sebaiknya diletakkan paling atas di CV?', ['Hobi', 'Data diri & kontak', 'Referensi', 'Riwayat SD'], 1],
                    ['Format file CV yang paling aman dikirim ke HRD adalah...', ['.docx', '.jpg', '.pdf', '.txt'], 2],
                    ['Pengalaman organisasi sekolah sebaiknya...', ['Tidak ditulis', 'Ditulis dengan pencapaian konkret', 'Ditulis semua tanpa urutan', 'Hanya ditulis nama organisasinya'], 1],
                    ['Alamat email yang tepat untuk CV adalah...', ['cowokkeren99@mail.com', 'nama.lengkap@mail.com', 'email orang tua', 'email sekolah lama'], 1],
                ],
            ],
            [
                'material' => $materials[1],
                'title' => 'Kuis: Wawancara Kerja',
                'description' => 'Seberapa siap kamu menghadapi wawancara?',
                'questions' => [
                    ['Metode STAR terdiri dari...', ['Situation, Task, Action, Result', 'Start, Talk, Ask, Reply', 'Skill, Talent, Attitude, Role', 'Study, Train, Apply, Repeat'], 0],
                    ['Kapan waktu terbaik datang ke lokasi wawancara?', ['Tepat waktu', '10–15 menit lebih awal', '1 jam lebih awal', 'Sedikit terlambat'], 1],
                    ['Saat ditanya kelemahan, sebaiknya kamu...', ['Mengaku tidak punya kelemahan', 'Menyebut kelemahan & upaya memperbaikinya', 'Mengalihkan pembicaraan', 'Menyebut banyak kelemahan'], 1],
                    ['Pertanyaan yang baik diajukan ke pewawancara adalah...', ['Kapan saya boleh cuti?', 'Seperti apa budaya kerja di tim ini?', 'Berapa gaji direktur?', 'Tidak perlu bertanya'], 1],
                    ['Setelah wawancara, langkah yang disarankan adalah...', ['Menelepon HRD setiap hari', 'Mengirim email terima kasih', 'Langsung melamar ulang', 'Tidak melakukan apa-apa'], 1],
                ],
            ],
        ];

        $quizzes = [];

        foreach ($data as $i => $quizData) {
            $quiz = Quiz::create([
                'material_id' => $quizData['material']->id,
                'created_by' => $admins[$i % count($admins)]->id,
                'title' => $quizData['title'],
                'description' => $quizData['description'],
            ]);

            foreach ($quizData['questions'] as $q => [$text, $options, $correctIndex]) {
                $question = $quiz->questions()->create([
                    'question_text' => $text,
                    'sort_order' => $q + 1,
                ]);

                foreach ($options as $o => $optionText) {
                    $question->options()->create([
                        'option_text' => $optionText,
                        'is_correct' => $o === $correctIndex,
                        'sort_order' => $o + 1,
                    ]);
                }
            }

            $quizzes[] = $quiz;
        }

        return $quizzes;
    }

    /**
     * @param  list<User>  $admins
     * @return list<CareerEvent>
     */
    private function seedEvents(array $admins): array
    {
        $data = [
            ['Webinar: Strategi Lolos Seleksi Kerja BUMN', EventCategory::Hardskill, EventPlatform::Zoom, 5, 100, EventStatus::Published],
            ['Mentoring: Public Speaking untuk Pemula', EventCategory::Softskill, EventPlatform::GoogleMeet, 10, 30, EventStatus::Published],
            ['Workshop: Membangun Portofolio Desain', EventCategory::Hardskill, EventPlatform::Zoom, 14, null, EventStatus::Published],
            ['Sharing Session: Kerja Sambil Kuliah', EventCategory::Softskill, EventPlatform::GoogleMeet, -7, 50, EventStatus::Completed],
        ];

        $events = [];

        foreach ($data as $i => [$title, $category, $platform, $daysFromNow, $capacity, $status]) {
            $start = now()->addDays($daysFromNow)->setTime(19, 0);

            $events[] = CareerEvent::create([
                'admin_id' => $admins[$i % count($admins)]->id,
                'title' => $title,
                'description' => 'Sesi daring gratis bersama praktisi untuk membantu lulusan baru mempersiapkan karier. Terbuka untuk lulusan SMA/SMK dan fresh graduate.',
                'start_at' => $start,
                'end_at' => $start->copy()->addHours(2),
                'meeting_url' => $platform === EventPlatform::Zoom
                    ? 'https://zoom.us/j/12345678'.$i
                    : 'https://meet.google.com/abc-defg-hi'.$i,
                'platform' => $platform,
                'capacity' => $capacity,
                'status' => $status,
                'category' => $category,
                'thumbnail_url' => 'https://picsum.photos/seed/abislulus-event-'.($i + 1).'/640/360',
            ]);
        }

        return $events;
    }

    private function seedArticlesAndHero(User $superAdmin): void
    {
        $articles = [
            ['5 Skill yang Paling Dicari Perusahaan dari Lulusan Baru', 'Komunikasi, kerja sama tim, dan kemampuan digital menempati urutan teratas.'],
            ['Mengapa Soft Skill Sama Pentingnya dengan Hard Skill', '80% responden survei kami menilai keduanya sama penting. Ini alasannya.'],
            ['Panduan Memilih Jalur Setelah Lulus SMA/SMK', 'Kuliah, kerja, atau keduanya? Pertimbangkan hal-hal berikut sebelum memutuskan.'],
        ];

        foreach ($articles as $i => [$title, $excerpt]) {
            Article::create([
                'super_admin_id' => $superAdmin->id,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $excerpt."\n\n".'Artikel contoh untuk keperluan pengembangan. Konten lengkap akan dikelola oleh Super Admin melalui panel Manajemen Artikel.',
                'cover_image_url' => 'https://picsum.photos/seed/abislulus-artikel-'.($i + 1).'/800/450',
                'status' => ArticleStatus::Published,
                'published_at' => now()->subDays(3 - $i),
            ]);
        }

        HeroSection::create([
            'super_admin_id' => $superAdmin->id,
            'title' => 'Siap Melangkah Setelah Lulus',
            'subtitle' => 'Belajar gratis lewat E-Learning dan ikuti Career Mentoring bersama praktisi.',
            'image_url' => 'https://picsum.photos/seed/abislulus-hero/1200/600',
            'target_url' => '/e-learning',
            'is_active' => true,
        ]);
    }

    /**
     * @param  list<LearningMaterial>  $materials
     * @param  list<User>  $users
     */
    private function seedReviews(array $materials, array $users): void
    {
        $samples = [
            [5, 'Materinya jelas dan langsung bisa dipraktikkan.'],
            [4, 'Bermanfaat, semoga ada materi lanjutan.'],
            [5, 'Sangat membantu saya yang baru lulus.'],
            [3, null],
        ];

        // Materi 1–4 mendapat ulasan dari beberapa user (unik per user-materi).
        foreach (array_slice($materials, 0, 4) as $m => $material) {
            foreach (array_slice($users, 0, 3) as $u => $user) {
                [$rating, $text] = $samples[($m + $u) % count($samples)];

                LearningReview::create([
                    'user_id' => $user->id,
                    'material_id' => $material->id,
                    'rating' => $rating,
                    'review_text' => $text,
                ]);
            }
        }
    }

    private function seedAttempt(Quiz $quiz, User $user): void
    {
        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'started_at' => now()->subMinutes(10),
        ]);

        // Jawab 4 dari 5 soal: 3 benar, 1 salah, 1 tidak dijawab → skor 60.
        foreach ($quiz->questions()->with('options')->get()->take(4) as $i => $question) {
            $option = $i < 3
                ? $question->options->firstWhere('is_correct', true)
                : $question->options->firstWhere('is_correct', false);

            QuizAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'selected_option_id' => $option->id,
                'is_correct' => $option->is_correct,
                'answered_at' => now()->subMinutes(5),
            ]);
        }

        $attempt->calculateScore();
    }

    /**
     * @param  list<CareerEvent>  $events
     * @param  list<User>  $users
     */
    private function seedRegistrations(array $events, array $users): void
    {
        $pairs = [
            [0, 0, RegistrationStatus::Registered],
            [0, 1, RegistrationStatus::Registered],
            [1, 0, RegistrationStatus::Registered],
            [1, 2, RegistrationStatus::Cancelled],
            [3, 0, RegistrationStatus::Attended],
            [3, 3, RegistrationStatus::Attended],
        ];

        foreach ($pairs as [$e, $u, $status]) {
            $events[$e]->registrations()->create([
                'user_id' => $users[$u]->id,
                'status' => $status,
                'registered_at' => now()->subDays(2),
            ]);
        }
    }

    /**
     * @param  list<User>  $users
     */
    private function seedTestimonials(array $users): void
    {
        $data = [
            ['AbisLulus membantu saya menyiapkan CV dan akhirnya lolos wawancara pertama!', 5, true],
            ['Webinarnya gratis tapi kualitasnya seperti kelas berbayar. Terima kasih!', 5, true],
            ['Kuisnya bikin saya tahu bagian mana yang belum saya pahami.', 4, true],
        ];

        foreach ($data as $i => [$content, $rating, $visible]) {
            Testimonial::create([
                'user_id' => $users[$i]->id,
                'content' => $content,
                'rating' => $rating,
                'is_visible' => $visible,
            ]);
        }
    }
}
