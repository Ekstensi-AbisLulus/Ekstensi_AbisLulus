@extends('layouts.panel')

@section('title', 'Pratinjau Hero Section')
@section('page-title', 'Pratinjau Hero Section')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="h5 fw-bold mb-0">{{ $heroSection->title }}</h2>
                @if ($heroSection->is_active)
                    <span class="badge-status badge-status-active">Aktif</span>
                @else
                    <span class="badge-status badge-status-inactive">Nonaktif</span>
                @endif
            </div>
            <p class="panel-muted small mb-0">
                Dibuat oleh {{ $heroSection->author?->name ?? 'Super Admin' }} pada {{ $heroSection->created_at?->format('d M Y H:i') ?? '-' }}.
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('super-admin.hero-sections.edit', $heroSection) }}" class="btn btn-primary btn-panel">
                <i class="bi bi-pencil" aria-hidden="true"></i>
                <span>Ubah Hero</span>
            </a>
            <button
                type="button"
                class="btn btn-outline-danger btn-panel"
                data-bs-toggle="modal"
                data-bs-target="#deleteHeroModal-{{ $heroSection->id }}"
            >
                <i class="bi bi-trash" aria-hidden="true"></i>
                <span>Hapus Hero</span>
            </button>
            <a href="{{ route('super-admin.hero-sections.index') }}" class="btn btn-outline-secondary btn-panel">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>
    </div>

    <div class="panel-card p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 class="panel-section-title mb-0">Pratinjau Tampilan Landing Page</h3>
            <span class="small panel-muted">Tampilan banner di halaman beranda publik</span>
        </div>

        <div class="hero-banner rounded-4 overflow-hidden" dusk="hero-preview">
            <img
                src="{{ $heroSection->imageSrc() }}"
                alt="{{ $heroSection->title }}"
                width="1200"
                height="600"
                fetchpriority="high"
                class="hero-banner-img"
            >
            <div class="hero-banner-overlay"></div>
            <div class="container py-5">
                <div class="hero-banner-content px-2 px-md-4">
                    <h4 class="display-6 fw-bold text-white mb-3">{{ $heroSection->title }}</h4>
                    @if ($heroSection->subtitle)
                        <p class="hero-banner-subtitle mb-4">{{ $heroSection->subtitle }}</p>
                    @endif
                    @if ($heroSection->target_url)
                        <a href="{{ $heroSection->target_url }}" class="btn btn-primary btn-lg btn-panel">
                            <span>Jelajahi Sekarang</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="panel-card p-3 p-md-4">
        <h3 class="panel-section-title mb-3 pb-2 border-bottom">Detail Informasi Banner</h3>
        <dl class="row mb-0 small" dusk="hero-detail">
            <dt class="col-sm-3 panel-muted">Judul</dt>
            <dd class="col-sm-9 fw-medium">{{ $heroSection->title }}</dd>

            <dt class="col-sm-3 panel-muted">Subjudul</dt>
            <dd class="col-sm-9">{{ $heroSection->subtitle ?: '-' }}</dd>

            <dt class="col-sm-3 panel-muted">Target URL Tombol</dt>
            <dd class="col-sm-9">
                @if ($heroSection->target_url)
                    <code>{{ $heroSection->target_url }}</code>
                @else
                    <span>-</span>
                @endif
            </dd>

            <dt class="col-sm-3 panel-muted mb-0">Status Tampil</dt>
            <dd class="col-sm-9 mb-0">{{ $heroSection->is_active ? 'Aktif (ditampilkan di beranda)' : 'Nonaktif (disembunyikan dari beranda)' }}</dd>
        </dl>
    </div>

    <div
        class="modal fade"
        id="deleteHeroModal-{{ $heroSection->id }}"
        tabindex="-1"
        aria-labelledby="deleteHeroModalLabel-{{ $heroSection->id }}"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h3 class="modal-title h6 fw-bold mb-0" id="deleteHeroModalLabel-{{ $heroSection->id }}">
                        Konfirmasi Hapus Hero Section
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        Hapus hero '{{ $heroSection->title }}'? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary btn-panel" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('super-admin.hero-sections.destroy', $heroSection) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-panel">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
