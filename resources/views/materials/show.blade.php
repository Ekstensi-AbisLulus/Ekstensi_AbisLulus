@extends('layouts.app')

@section('title', $material->title)

@section('content')
    <section class="py-4 py-lg-5">
        <div class="container">
            <div class="mb-4">
                <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar Materi
                </a>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card p-3 p-md-4 mb-4">
                        @if ($material->videoEmbedUrl())
                            <div class="ratio ratio-16x9 rounded overflow-hidden bg-dark mb-3">
                                <iframe
                                    src="{{ $material->videoEmbedUrl() }}"
                                    title="Video {{ $material->title }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                        @endif

                        <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-between gap-3 mb-3">
                            <div>
                                <h1 class="h3 fw-bold mb-2">{{ $material->title }}</h1>
                                <div class="d-flex flex-wrap align-items-center gap-3 small text-secondary">
                                    <span>
                                        <i class="bi bi-person-circle me-1" aria-hidden="true"></i>{{ $material->admin?->name ?? 'Tim AbisLulus' }}
                                    </span>
                                    <span>
                                        <i class="bi bi-calendar3 me-1" aria-hidden="true"></i>{{ $material->created_at?->format('d M Y') }}
                                    </span>
                                    <span>
                                        @if ($material->average_rating !== null)
                                            <i class="bi bi-star-fill text-warning me-1" aria-hidden="true"></i>
                                            <strong class="text-body">{{ number_format($material->average_rating, 1) }}</strong>
                                            <span>({{ $material->reviews_count }} ulasan)</span>
                                        @else
                                            <i class="bi bi-star text-secondary me-1" aria-hidden="true"></i>Belum ada ulasan
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if ($material->quizzes->isNotEmpty())
                                <a href="{{ route('quizzes.show', $material->quizzes->first()) }}" class="btn btn-primary flex-shrink-0">
                                    <i class="bi bi-patch-question me-1" aria-hidden="true"></i>Mulai Kuis
                                </a>
                            @endif
                        </div>

                        <hr>

                        <h2 class="h5 fw-bold mb-3">Deskripsi Materi</h2>
                        <div class="text-body" style="white-space: pre-line;">{{ $material->description }}</div>

                        <div class="mt-4 pt-3 border-top small text-secondary">
                            <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Jika video tidak tampil di atas, buka langsung melalui tautan:
                            <a href="{{ $material->video_url }}" target="_blank" rel="noopener" class="text-break">{{ $material->video_url }}</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h5 fw-bold mb-0">Rating &amp; Ulasan</h2>
                            @if ($material->average_rating !== null)
                                <span class="badge text-bg-warning">
                                    <i class="bi bi-star-fill me-1" aria-hidden="true"></i>{{ number_format($material->average_rating, 1) }} / 5.0
                                </span>
                            @endif
                        </div>

                        @if ($material->reviews->isEmpty())
                            <div class="text-center text-secondary py-4">
                                <i class="bi bi-chat-square-text fs-2 d-block mb-2" aria-hidden="true"></i>
                                <p class="small mb-0">Belum ada ulasan</p>
                            </div>
                        @else
                            <div class="d-flex flex-column gap-3">
                                @foreach ($material->reviews as $review)
                                    <div class="border-bottom pb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-semibold small">{{ $review->user?->name ?? 'Pengguna' }}</span>
                                            <span class="text-warning small">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}" aria-hidden="true"></i>
                                                @endfor
                                            </span>
                                        </div>
                                        @if ($review->review_text)
                                            <p class="small text-secondary mb-1">{{ $review->review_text }}</p>
                                        @endif
                                        <span class="text-secondary" style="font-size: 0.75rem;">
                                            {{ $review->created_at?->format('d M Y') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
