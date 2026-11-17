@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <article class="py-5">
        <div class="container">
            <div class="mx-auto" style="max-width: 48rem;">
                <div class="mb-4">
                    <a href="{{ route('articles.index') }}" class="btn btn-outline-secondary btn-panel">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        <span>Semua Artikel</span>
                    </a>
                </div>

                <div class="small panel-muted mb-2">
                    <span>Oleh <strong>{{ $article->author?->name ?? 'Tim AbisLulus' }}</strong></span>
                    <span class="mx-1">&middot;</span>
                    <span>{{ ($article->published_at ?? $article->created_at)?->format('d M Y') }}</span>
                </div>

                <h1 class="display-6 fw-bold mb-3">{{ $article->title }}</h1>

                @if ($article->excerpt)
                    <p class="lead panel-muted mb-4">{{ $article->excerpt }}</p>
                @endif

                <img
                    src="{{ $article->coverImageSrc() ?? asset('images/placeholders/article-cover.svg') }}"
                    alt="{{ $article->title }}"
                    width="800"
                    height="450"
                    fetchpriority="high"
                    class="article-detail-cover mb-4"
                >

                <div class="card p-4 p-md-5">
                    <div class="article-body">
                        {!! nl2br(e($article->content)) !!}
                    </div>
                </div>
            </div>
        </div>
    </article>

    @if ($relatedArticles->isNotEmpty())
        <section class="section bg-white border-top">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="h4 fw-bold mb-0">Artikel Lainnya</h2>
                    <a href="{{ route('articles.index') }}" class="link-primary fw-medium text-decoration-none">Lihat Semua</a>
                </div>

                <div class="row g-4">
                    @foreach ($relatedArticles as $item)
                        <div class="col-12 col-md-4">
                            <article class="card article-card h-100 d-flex flex-column">
                                <a href="{{ route('articles.show', $item) }}" class="text-decoration-none" tabindex="-1" aria-hidden="true">
                                    <img
                                        src="{{ $item->coverImageSrc() ?? asset('images/placeholders/article-cover.svg') }}"
                                        alt=""
                                        width="800"
                                        height="450"
                                        loading="lazy"
                                        class="article-card-cover"
                                    >
                                </a>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="small panel-muted mb-2">
                                        {{ ($item->published_at ?? $item->created_at)?->format('d M Y') }}
                                    </div>
                                    <h3 class="h6 fw-semibold mb-2">
                                        <a href="{{ route('articles.show', $item) }}" class="text-decoration-none text-body">
                                            {{ $item->title }}
                                        </a>
                                    </h3>
                                    <a href="{{ route('articles.show', $item) }}" class="link-primary small fw-medium text-decoration-none mt-auto">
                                        Baca Artikel
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
