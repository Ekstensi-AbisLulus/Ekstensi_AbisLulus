@extends('layouts.panel')

@section('title', 'Tambah Materi E-Learning')
@section('page-title', 'Manajemen E-Learning · Tambah Materi')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Tambah Materi Pembelajaran Baru</h2>
            <p class="text-secondary small mb-0">
                Tambahkan materi video pembelajaran baru menggunakan tautan video (YouTube/Google Drive).
            </p>
        </div>
        <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary align-self-start align-self-md-center">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar
        </a>
    </div>

    <div class="card p-3 p-md-4">
        <form method="POST" action="{{ route('admin.materials.store') }}" dusk="material-form" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label fw-medium">
                    Judul Materi <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="title" dusk="title-input"
                    name="title"
                    value="{{ old('title') }}"
                    maxlength="200"
                    required
                    placeholder="Contoh: Cara Membuat CV yang Dilirik HRD"
                    class="form-control @error('title') is-invalid @enderror"
                    @error('title') aria-invalid="true" aria-describedby="title-error" @else aria-describedby="title-hint" @enderror
                >
                @error('title')
                    <div class="invalid-feedback d-block" id="title-error">{{ $message }}</div>
                @else
                    <div class="form-text" id="title-hint">Maksimal 200 karakter.</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="video_url" class="form-label fw-medium">
                    Link Video <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-play-btn" aria-hidden="true"></i></span>
                    <input
                        type="url"
                        id="video_url" dusk="video-url-input"
                        name="video_url"
                        value="{{ old('video_url') }}"
                        maxlength="2048"
                        required
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="form-control @error('video_url') is-invalid @enderror"
                        @error('video_url') aria-invalid="true" aria-describedby="video-url-error" @else aria-describedby="video-url-hint" @enderror
                    >
                </div>
                @error('video_url')
                    <div class="invalid-feedback d-block" id="video-url-error">{{ $message }}</div>
                @else
                    <div class="form-text" id="video-url-hint">Masukkan URL video yang valid (misalnya tautan YouTube atau Google Drive).</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-medium">
                    Deskripsi Materi <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <textarea
                    id="description" dusk="description-input"
                    name="description"
                    rows="5"
                    required
                    placeholder="Tuliskan ringkasan dan poin pembelajaran dari materi video ini..."
                    class="form-control @error('description') is-invalid @enderror"
                    @error('description') aria-invalid="true" aria-describedby="description-error" @enderror
                >{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback d-block" id="description-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="thumbnail" class="form-label fw-medium">Unggah Thumbnail (Opsional)</label>
                    <input
                        type="file"
                        id="thumbnail" dusk="thumbnail-input"
                        name="thumbnail"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="form-control @error('thumbnail') is-invalid @enderror"
                        @error('thumbnail') aria-invalid="true" aria-describedby="thumbnail-error" @else aria-describedby="thumbnail-hint" @enderror
                    >
                    @error('thumbnail')
                        <div class="invalid-feedback d-block" id="thumbnail-error">{{ $message }}</div>
                    @else
                        <div class="form-text" id="thumbnail-hint">Format JPG, JPEG, PNG, atau WebP (maks. 2 MB).</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="thumbnail_url" class="form-label fw-medium">Atau URL Thumbnail (Opsional)</label>
                    <input
                        type="url"
                        id="thumbnail_url" dusk="thumbnail-url-input"
                        name="thumbnail_url"
                        value="{{ old('thumbnail_url') }}"
                        maxlength="2048"
                        placeholder="https://example.com/thumbnail.jpg"
                        class="form-control @error('thumbnail_url') is-invalid @enderror"
                        @error('thumbnail_url') aria-invalid="true" aria-describedby="thumbnail-url-error" @else aria-describedby="thumbnail-url-hint" @enderror
                    >
                    @error('thumbnail_url')
                        <div class="invalid-feedback d-block" id="thumbnail-url-error">{{ $message }}</div>
                    @else
                        <div class="form-text" id="thumbnail-url-hint">Jika mengunggah file sekaligus mengisi URL, file unggahan yang akan dipakai.</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <input type="hidden" name="is_published" value="0">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input @error('is_published') is-invalid @enderror"
                        type="checkbox"
                        role="switch"
                        id="is_published" dusk="publish-switch"
                        name="is_published"
                        value="1"
                        @checked((bool) old('is_published', true))
                    >
                    <label class="form-check-label fw-medium" for="is_published">
                        Publikasikan materi sekarang
                    </label>
                </div>
                <div class="form-text">Hanya materi berstatus dipublikasikan yang tampil di halaman E-Learning pengguna.</div>
                @error('is_published')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary" dusk="submit-button">
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Simpan Materi
                </button>
                <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
