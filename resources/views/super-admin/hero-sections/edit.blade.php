@extends('layouts.panel')

@section('title', 'Ubah Hero Section')
@section('page-title', 'Ubah Hero Section')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Ubah Banner Hero</h2>
            <p class="panel-muted small mb-0">
                Perbarui teks, tautan, gambar, atau status tampil banner hero pada halaman beranda publik.
            </p>
        </div>
        <a href="{{ route('super-admin.hero-sections.index') }}" class="btn btn-outline-secondary btn-panel align-self-start align-self-md-center">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('super-admin.hero-sections.update', $heroSection) }}" enctype="multipart/form-data" data-panel-form novalidate>
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="panel-card p-3 p-md-4 h-100">
                    <h3 class="panel-section-title mb-3 pb-2 border-bottom">Konten Banner Hero</h3>

                    <div class="mb-3">
                        <label for="title" class="panel-form-label">
                            Judul Hero <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $heroSection->title) }}"
                            maxlength="200"
                            required
                            placeholder="Contoh: Siap Melangkah Setelah Lulus"
                            class="form-control panel-form-control @error('title') is-invalid @enderror"
                            @error('title') aria-invalid="true" aria-describedby="title-error" @else aria-describedby="title-hint" @enderror
                        >
                        @error('title')
                            <div class="invalid-feedback d-block" id="title-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="title-hint">Maksimal 200 karakter. Gunakan kalimat utama yang jelas dan ringkas.</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subtitle" class="panel-form-label">Subjudul (Opsional)</label>
                        <textarea
                            id="subtitle"
                            name="subtitle"
                            rows="3"
                            placeholder="Contoh: Belajar gratis lewat E-Learning dan ikuti Career Mentoring bersama praktisi."
                            class="form-control panel-form-control @error('subtitle') is-invalid @enderror"
                            @error('subtitle') aria-invalid="true" aria-describedby="subtitle-error" @else aria-describedby="subtitle-hint" @enderror
                        >{{ old('subtitle', $heroSection->subtitle) }}</textarea>
                        @error('subtitle')
                            <div class="invalid-feedback d-block" id="subtitle-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="subtitle-hint">Kalimat pendamping di bawah judul utama hero.</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="target_url" class="panel-form-label">Target URL Tombol (Opsional)</label>
                        <input
                            type="text"
                            id="target_url"
                            name="target_url"
                            value="{{ old('target_url', $heroSection->target_url) }}"
                            maxlength="2048"
                            placeholder="Contoh: /e-learning atau https://abislulus.com/kegiatan"
                            class="form-control panel-form-control @error('target_url') is-invalid @enderror"
                            @error('target_url') aria-invalid="true" aria-describedby="target_url-error" @else aria-describedby="target_url-hint" @enderror
                        >
                        @error('target_url')
                            <div class="invalid-feedback d-block" id="target_url-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="target_url-hint">
                                Isi dengan URL valid (<code>https://...</code>) atau path halaman internal (<code>/e-learning</code>).
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="panel-card p-3 p-md-4">
                    <h3 class="panel-section-title mb-3 pb-2 border-bottom">Gambar &amp; Status Aktif</h3>

                    <div class="mb-3" data-image-uploader>
                        <label for="image" class="panel-form-label">
                            Ganti Gambar Hero (Opsional)
                        </label>
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            data-image-input
                            class="form-control panel-form-control @error('image') is-invalid @enderror"
                            @error('image') aria-invalid="true" aria-describedby="image-error" @else aria-describedby="image-hint" @enderror
                        >
                        @error('image')
                            <div class="invalid-feedback d-block" id="image-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="image-hint">
                                Kosongkan jika ingin tetap menggunakan gambar saat ini. Format JPG, JPEG, PNG, atau WebP (maksimal 3 MB).
                            </div>
                        @enderror

                        <div class="alert alert-danger py-2 px-3 small mt-2 mb-0 d-none" role="alert" data-image-error></div>

                        <div class="image-preview-frame mt-3">
                            <div class="d-none" data-image-empty>
                                <i class="bi bi-image fs-2 panel-muted d-block mb-1" aria-hidden="true"></i>
                                <p class="small fw-semibold mb-1">Belum ada gambar dipilih</p>
                                <p class="small panel-muted mb-0">Pilih file gambar untuk melihat pratinjau banner sebelum disimpan.</p>
                            </div>

                            <div data-image-preview-wrap>
                                <img
                                    src="{{ $heroSection->imageSrc() }}"
                                    data-initial-src="{{ $heroSection->imageSrc() }}"
                                    alt="Pratinjau gambar hero {{ $heroSection->title }}"
                                    width="600"
                                    height="300"
                                    class="image-preview-media mb-2"
                                    data-image-preview
                                >
                                <div class="small panel-muted text-truncate" data-image-meta data-initial-meta="Gambar saat ini">
                                    Gambar saat ini
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 pt-2 border-top">
                        <input type="hidden" name="is_active" value="0">
                        <div class="form-check form-switch panel-switch">
                            <input
                                class="form-check-input @error('is_active') is-invalid @enderror"
                                type="checkbox"
                                role="switch"
                                id="is_active"
                                name="is_active"
                                value="1"
                                @checked((string) old('is_active', $heroSection->is_active ? '1' : '0') === '1')
                                @error('is_active') aria-invalid="true" aria-describedby="is_active-error" @enderror
                            >
                            <label class="form-check-label fw-medium" for="is_active">
                                Aktif
                            </label>
                        </div>
                        @error('is_active')
                            <div class="invalid-feedback d-block" id="is_active-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-panel flex-grow-1" data-submit-btn>
                            <span data-submit-default>
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Simpan Perubahan
                            </span>
                            <span class="d-none" data-submit-loading>
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Menyimpan...
                            </span>
                        </button>
                        <a href="{{ route('super-admin.hero-sections.index') }}" class="btn btn-outline-secondary btn-panel">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
