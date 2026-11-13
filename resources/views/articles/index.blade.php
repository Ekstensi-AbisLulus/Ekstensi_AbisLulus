@extends('layouts.app')

@section('title', 'Artikel')

@section('content')
    <section class="py-5 bg-white border-bottom">
        <div class="container">
            <div class="row align-items-end g-4">
                <div class="col-12 col-lg-7">
                    <h1 class="h2 fw-bold mb-2">Artikel &amp; Panduan Karier</h1>
                    <p class="panel-muted mb-0">
                        Temukan tips melamar kerja, penyusunan CV, persiapan wawancara, dan pengembangan diri untuk lulusan baru.
                    </p>
                </div>
                <div class="col-12 col-lg-5">
                    <form method="GET" action="{{ route('articles.index') }}" class="d-flex gap-2">
                        <div class="flex-grow-1">
                            <label for="search-article" class="visually-hidden">Cari judul artikel</label>
                            <input
                                type="search"
                                id="search-article"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari judul artikel..."
                                class="form-control panel-form-control"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary btn-panel">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <span>Cari</span>
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary btn-panel">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($articles->isEmpty())
                <div class="card p-5 text-center">
                    <i class="bi bi-journal-x fs-1 panel-muted d-block mb-2" aria-hidden="true"></i>
                    @if ($search !== '')
                        <h2 class="h5 fw-semibold mb-1">Artikel tidak ditemukan</h2>
                        <p class="panel-muted small mb-3">
                            Tidak ada artikel dengan judul yang memuat <strong>"{{ $search }}"</strong>.
                        </p>
                        <div>
                            <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary btn-panel">
                                Tampilkan Semua Artikel
                            </a>
                        </div>
                    @else
                        <h2 class="h5 fw-semibold mb-1">Belum ada artikel</h2>
                        <p class="panel-muted small mb-0">
                            Artikel panduan karier akan segera tersedia di halaman ini.
                        </p>
                    @endif
                </div>
            @else
                <div class="row g-4">
                    @foreach ($articles as $article)
                        <div class="col-12 col-md-6 col-lg-4">
                            <article class="card article-card h-100 d-flex flex-column">
                                <a href="{{ route('articles.show', $article) }}" class="text-decoration-none" tabindex="-1" aria-hidden="true">
                                    <img
                                        src="{{ $article->coverImageSrc() ?? asset('images/placeholders/article-cover.svg') }}"
                                        alt=""
                                        width="800"
                                        height="450"
                                        @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif
                                        class="article-card-cover"
                                    >
                                </a>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="small panel-muted mb-2">
                                        <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>
                                        <span>{{ ($article->published_at ?? $article->created_at)?->format('d M Y') }}</span>
                                    </div>
                                    <h2 class="h5 fw-semibold mb-2">
                                        <a href="{{ route('articles.show', $article) }}" class="text-decoration-none text-body">
                                            {{ $article->title }}
                                        </a>
                                    </h2>
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

                @if ($articles->hasPages())
                    <div class="mt-5 d-flex justify-content-center">
                        {{ $articles->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
