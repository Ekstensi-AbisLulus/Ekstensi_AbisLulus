@extends('layouts.panel')

@section('title', $material->title)
@section('page-title', 'Manajemen E-Learning · Detail Materi')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                @if ($material->is_published)
                    <span class="badge text-bg-success">Dipublikasikan</span>
                @else
                    <span class="badge text-bg-secondary">Draf</span>
                @endif
                <span class="text-secondary small">
                    Diunggah {{ $material->created_at?->format('d M Y, H:i') }} oleh <strong>{{ $material->admin?->name ?? 'Admin' }}</strong>
                </span>
            </div>
            <h2 class="h4 fw-bold mb-0">{{ $material->title }}</h2>
        </div>

        <div class="d-flex flex-wrap gap-2 align-self-start align-self-md-center">
            @if ($material->is_published)
                <a href="{{ route('materials.show', $material) }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>Lihat di Halaman Publik
                </a>
            @endif
            <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card p-3 p-md-4 mb-4">
                <h3 class="h6 fw-bold mb-3">Pratinjau Video Pembelajaran</h3>

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

                <div class="small text-secondary d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span>
                        <i class="bi bi-link-45deg me-1" aria-hidden="true"></i>Tautan asli:
                        <a href="{{ $material->video_url }}" target="_blank" rel="noopener" class="text-break">{{ $material->video_url }}</a>
                    </span>
                </div>
            </div>

            <div class="card p-3 p-md-4">
                <h3 class="h6 fw-bold mb-3">Deskripsi Materi</h3>
                <div class="text-body" style="white-space: pre-line;">{{ $material->description }}</div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card p-3 p-md-4 mb-4">
                <h3 class="h6 fw-bold mb-3">Thumbnail Materi</h3>
                @if ($material->thumbnailSrc())
                    <img
                        src="{{ $material->thumbnailSrc() }}"
                        alt="Thumbnail {{ $material->title }}"
                        class="img-fluid rounded border mb-2"
                    >
                @else
                    <div class="rounded bg-light border p-4 text-center text-secondary">
                        <i class="bi bi-image fs-2 d-block mb-1" aria-hidden="true"></i>
                        <span class="small">Tidak ada thumbnail khusus</span>
                    </div>
                @endif
            </div>

            <div class="card p-3 p-md-4">
                <h3 class="h6 fw-bold mb-3">Ringkasan Informasi</h3>
                <dl class="row small mb-0">
                    <dt class="col-5 text-secondary">Pembuat</dt>
                    <dd class="col-7 fw-medium">{{ $material->admin?->name ?? 'Admin' }}</dd>

                    <dt class="col-5 text-secondary">Status</dt>
                    <dd class="col-7">{{ $material->is_published ? 'Dipublikasikan' : 'Draf' }}</dd>

                    <dt class="col-5 text-secondary">Rating Rata-rata</dt>
                    <dd class="col-7">
                        @if ($material->average_rating !== null)
                            <i class="bi bi-star-fill text-warning me-1" aria-hidden="true"></i>{{ number_format($material->average_rating, 1) }}
                            <span class="text-secondary">({{ $material->reviews_count }} ulasan)</span>
                        @else
                            <span class="text-secondary">Belum ada ulasan</span>
                        @endif
                    </dd>

                    <dt class="col-5 text-secondary mb-0">Kuis Terkait</dt>
                    <dd class="col-7 mb-0">{{ $material->quizzes->count() }} kuis</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
