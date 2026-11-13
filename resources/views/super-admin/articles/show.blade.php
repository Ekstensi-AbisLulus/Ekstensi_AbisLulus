@extends('layouts.panel')

@section('title', 'Pratinjau Artikel')
@section('page-title', 'Pratinjau Artikel')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="h5 fw-bold mb-0">{{ $article->title }}</h2>
                @if ($article->status === \App\Enums\ArticleStatus::Published)
                    <span class="badge-status badge-status-active">{{ $article->status->label() }}</span>
                @else
                    <span class="badge-status badge-status-inactive">{{ $article->status->label() }}</span>
                @endif
            </div>
            <p class="panel-muted small mb-0">
                Slug: <code>/artikel/{{ $article->slug }}</code>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if ($article->status === \App\Enums\ArticleStatus::Published)
                <a href="{{ route('articles.show', $article) }}" class="btn btn-outline-secondary btn-panel">
                    <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                    <span>Buka Halaman Publik</span>
                </a>
            @endif
            <a href="{{ route('super-admin.articles.edit', $article) }}" class="btn btn-primary btn-panel">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                <span>Ubah Artikel</span>
            </a>
            <a href="{{ route('super-admin.articles.index') }}" class="btn btn-outline-secondary btn-panel">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <div class="panel-card p-3 p-md-5">
        <div class="mx-auto" style="max-width: 48rem;">
            <div class="small panel-muted mb-2">
                <span>Oleh <strong>{{ $article->author?->name ?? 'Super Admin' }}</strong></span>
                <span class="mx-1">&middot;</span>
                <span>
                    @if ($article->published_at)
                        Dipublikasikan {{ $article->published_at->format('d M Y') }}
                    @else
                        Belum dipublikasikan (Draf)
                    @endif
                </span>
            </div>

            <h3 class="h2 fw-bold mb-3">{{ $article->title }}</h3>

            @if ($article->excerpt)
                <p class="lead panel-muted mb-4">{{ $article->excerpt }}</p>
            @endif

            <img
                src="{{ $article->coverImageSrc() ?? asset('images/placeholders/article-cover.svg') }}"
                alt="Sampul {{ $article->title }}"
                width="800"
                height="450"
                fetchpriority="high"
                class="article-detail-cover mb-4"
            >

            <div class="article-body">
                {!! nl2br(e($article->content)) !!}
            </div>
        </div>
    </div>
@endsection
