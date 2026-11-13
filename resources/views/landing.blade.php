@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    @php
        $heroSections = $heroSections ?? collect();
        $latestArticles = $latestArticles ?? collect();
    @endphp

    @if ($heroSections->count() > 1)
        <section aria-label="Sorotan Program AbisLulus">
            <div id="heroCarousel" class="carousel slide hero-carousel">
                <div class="carousel-indicators">
                    @foreach ($heroSections as $index => $hero)
                        <button
                            type="button"
                            data-bs-target="#heroCarousel"
                            data-bs-slide-to="{{ $index }}"
                            @if ($loop->first) class="active" aria-current="true" @endif
                            aria-label="Slide {{ $index + 1 }}: {{ $hero->title }}"
                        ></button>
                    @endforeach
                </div>

                <div class="carousel-inner">
                    @foreach ($heroSections as $hero)
                        <div class="carousel-item @if ($loop->first) active @endif">
                            <div class="hero-banner py-5">
                                <img
                                    src="{{ $hero->imageSrc() }}"
                                    alt="{{ $hero->title }}"
                                    width="1200"
                                    height="600"
                                    @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                    class="hero-banner-img"
                                >
                                <div class="hero-banner-overlay"></div>
                                <div class="container py-lg-5">
                                    <div class="hero-banner-content">
                                        @if ($loop->first)
                                            <h1 class="display-5 fw-bold text-white mb-3">{{ $hero->title }}</h1>
                                        @else
                                            <h2 class="display-5 fw-bold text-white mb-3">{{ $hero->title }}</h2>
                                        @endif

                                        @if ($hero->subtitle)
                                            <p class="hero-banner-subtitle mb-4">{{ $hero->subtitle }}</p>
                                        @endif

                                        @if ($hero->target_url)
                                            <a href="{{ $hero->target_url }}" class="btn btn-primary btn-lg btn-panel">
                                                <span>Jelajahi Program</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Slide sebelumnya">
                    <span class="carousel-control-icon-box" aria-hidden="true">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    </span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Slide berikutnya">
                    <span class="carousel-control-icon-box" aria-hidden="true">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    </span>
                </button>
            </div>
        </section>
    @elseif ($heroSections->count() === 1)
        @php
            /** @var \App\Models\HeroSection $hero */
            $hero = $heroSections->first();
        @endphp
        <section class="hero-banner py-5">
            <img
                src="{{ $hero->imageSrc() }}"
                alt="{{ $hero->title }}"
                width="1200"
                height="600"
                fetchpriority="high"
                class="hero-banner-img"
            >
            <div class="hero-banner-overlay"></div>
            <div class="container py-lg-5">
                <div class="hero-banner-content">
                    <h1 class="display-5 fw-bold text-white mb-3">{{ $hero->title }}</h1>
                    @if ($hero->subtitle)
                        <p class="hero-banner-subtitle mb-4">{{ $hero->subtitle }}</p>
                    @endif
                    @if ($hero->target_url)
                        <a href="{{ $hero->target_url }}" class="btn btn-primary btn-lg btn-panel">
                            <span>Jelajahi Program</span>
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @else
        {{-- Tampilan hero default bila belum ada hero aktif --}}
        <section class="hero py-5">
            <div class="container py-lg-5">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6">
                        <h1 class="display-5 fw-bold mb-3">Siap Melangkah Setelah Lulus Bersama <span class="text-primary">AbisLulus</span></h1>
                        <p class="lead panel-muted mb-4">
                            Belajar hard &amp; soft skill lewat E-Learning, uji pemahamanmu dengan kuis,
                            dan ikuti Career Mentoring bersama praktisi secara gratis dalam satu platform.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('materials.index') }}" class="btn btn-primary btn-lg btn-panel">Mulai Belajar</a>
                            <a href="{{ route('events.index') }}" class="btn btn-outline-primary btn-lg btn-panel">Lihat Kegiatan</a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="card p-5 text-center">
                            <i class="bi bi-mortarboard text-primary" style="font-size: 6rem;" aria-hidden="true"></i>
                            <p class="panel-muted mb-0">Untuk lulusan SMA/SMK &amp; fresh graduate</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Highlight layanan --}}
    <section class="section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Layanan Kami</h2>
                <p class="panel-muted mb-0">Dua layanan utama untuk mempersiapkan kariermu.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 p-4">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary mb-3"><i class="bi bi-play-btn" aria-hidden="true"></i></div>
                        <h3 class="h5">E-Learning</h3>
                        <p class="panel-muted">Materi video pilihan dilengkapi kuis untuk mengukur pemahaman, plus rating &amp; ulasan dari pengguna lain.</p>
                        <a href="{{ route('materials.index') }}" class="link-primary fw-medium text-decoration-none mt-auto">Jelajahi materi</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 p-4">
                        <div class="icon-box bg-success bg-opacity-10 text-success mb-3"><i class="bi bi-people" aria-hidden="true"></i></div>
                        <h3 class="h5">Career Mentoring</h3>
                        <p class="panel-muted">Ikuti webinar dan sesi mentoring daring via Zoom/Google Meet bersama praktisi dan komunitas.</p>
                        <a href="{{ route('events.index') }}" class="link-primary fw-medium text-decoration-none mt-auto">Lihat kegiatan</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Placeholder Materi Populer & Kegiatan Mendatang --}}
    @foreach ([
        ['title' => 'Materi Populer', 'icon' => 'play-btn', 'bg' => 'bg-white'],
        ['title' => 'Kegiatan Mendatang', 'icon' => 'calendar-event', 'bg' => ''],
    ] as $section)
        <section class="section {{ $section['bg'] }}">
            <div class="container">
                <h2 class="section-title mb-4">{{ $section['title'] }}</h2>
                <div class="card p-5 text-center panel-muted">
                    <i class="bi bi-{{ $section['icon'] }} fs-1 mb-2" aria-hidden="true"></i>
                    <p class="mb-0">Segera hadir.</p>
                </div>
            </div>
        </section>
    @endforeach

    {{-- Section Artikel Terbaru (PBI-23) --}}
    <section class="section bg-white">
        <div class="container">
            <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="section-title mb-1">Artikel Terbaru</h2>
                    <p class="panel-muted mb-0">Panduan dan wawasan karier terbaru untuk lulusan SMA/SMK dan fresh graduate.</p>
                </div>
                <a href="{{ route('articles.index') }}" class="btn btn-outline-primary btn-panel align-self-start align-self-sm-end">
                    <span>Lihat Semua</span>
                </a>
            </div>

            @if ($latestArticles->isEmpty())
                <div class="card p-5 text-center panel-muted">
                    <i class="bi bi-newspaper fs-1 mb-2" aria-hidden="true"></i>
                    <p class="fw-medium mb-1">Belum ada artikel yang dipublikasikan.</p>
                    <p class="small mb-0">Artikel terbaru seputar persiapan karier akan tampil di bagian ini.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($latestArticles as $article)
                        <div class="col-12 col-md-6 col-lg-4">
                            <article class="card article-card h-100 d-flex flex-column">
                                <a href="{{ route('articles.show', $article) }}" class="text-decoration-none" tabindex="-1" aria-hidden="true">
                                    <img
                                        src="{{ $article->coverImageSrc() ?? asset('images/placeholders/article-cover.svg') }}"
                                        alt=""
                                        width="800"
                                        height="450"
                                        loading="lazy"
                                        class="article-card-cover"
                                    >
                                </a>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="small panel-muted mb-2">
                                        <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                                        <span>{{ ($article->published_at ?? $article->created_at)?->format('d M Y') }}</span>
                                    </div>
                                    <h3 class="h5 fw-semibold mb-2">
                                        <a href="{{ route('articles.show', $article) }}" class="text-decoration-none text-body">
                                            {{ $article->title }}
                                        </a>
                                    </h3>
                                    <p class="panel-muted small mb-3 flex-grow-1">
                                        {{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->content), 120) }}
                                    </p>
                                    <a href="{{ route('articles.show', $article) }}" class="link-primary fw-medium text-decoration-none mt-auto">
                                        Baca Artikel
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Placeholder Testimoni --}}
    <section class="section">
        <div class="container">
            <h2 class="section-title mb-4">Testimoni</h2>
            <div class="card p-5 text-center panel-muted">
                <i class="bi bi-chat-quote fs-1 mb-2" aria-hidden="true"></i>
                <p class="mb-0">Segera hadir.</p>
            </div>
        </div>
    </section>
@endsection
