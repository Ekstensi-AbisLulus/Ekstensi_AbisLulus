@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    {{-- Hero (sementara statis; nanti diisi dari hero section aktif — PBI-23) --}}
    <section class="hero py-5">
        <div class="container py-lg-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill text-bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3">
                        <i class="bi bi-stars me-1"></i>100% Gratis
                    </span>
                    <h1 class="display-5 fw-bold mb-3">Siap Melangkah Setelah Lulus Bersama <span class="text-primary">AbisLulus</span></h1>
                    <p class="lead text-secondary mb-4">
                        Belajar hard &amp; soft skill lewat E-Learning, uji pemahamanmu dengan kuis,
                        dan ikuti Career Mentoring bersama praktisi — semuanya dalam satu platform.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('materials.index') }}" class="btn btn-primary btn-lg">Mulai Belajar</a>
                        <a href="{{ route('events.index') }}" class="btn btn-outline-primary btn-lg">Lihat Kegiatan</a>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="card p-5 text-center">
                        <i class="bi bi-mortarboard text-primary" style="font-size: 6rem;"></i>
                        <p class="text-secondary mb-0">Untuk lulusan SMA/SMK &amp; fresh graduate</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Highlight layanan --}}
    <section class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Layanan Kami</h2>
                <p class="text-secondary mb-0">Dua layanan utama untuk mempersiapkan kariermu.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 p-4">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary mb-3"><i class="bi bi-play-btn"></i></div>
                        <h3 class="h5">E-Learning</h3>
                        <p class="text-secondary">Materi video pilihan dilengkapi kuis untuk mengukur pemahaman, plus rating &amp; ulasan dari pengguna lain.</p>
                        <a href="{{ route('materials.index') }}" class="link-primary fw-medium text-decoration-none mt-auto">Jelajahi materi <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 p-4">
                        <div class="icon-box bg-success bg-opacity-10 text-success mb-3"><i class="bi bi-people"></i></div>
                        <h3 class="h5">Career Mentoring</h3>
                        <p class="text-secondary">Ikuti webinar dan sesi mentoring daring via Zoom/Google Meet bersama praktisi dan komunitas.</p>
                        <a href="{{ route('events.index') }}" class="link-primary fw-medium text-decoration-none mt-auto">Lihat kegiatan <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Placeholder section yang akan diisi PBI berikutnya --}}
    @foreach ([
        ['title' => 'Materi Populer', 'icon' => 'play-btn', 'bg' => 'bg-white'],
        ['title' => 'Kegiatan Mendatang', 'icon' => 'calendar-event', 'bg' => ''],
        ['title' => 'Artikel Terbaru', 'icon' => 'newspaper', 'bg' => 'bg-white'],
        ['title' => 'Testimoni', 'icon' => 'chat-quote', 'bg' => ''],
    ] as $section)
        <section class="section {{ $section['bg'] }}">
            <div class="container">
                <h2 class="section-title mb-4">{{ $section['title'] }}</h2>
                <div class="card p-5 text-center text-secondary">
                    <i class="bi bi-{{ $section['icon'] }} fs-1 mb-2"></i>
                    <p class="mb-0">Segera hadir.</p>
                </div>
            </div>
        </section>
    @endforeach
@endsection
