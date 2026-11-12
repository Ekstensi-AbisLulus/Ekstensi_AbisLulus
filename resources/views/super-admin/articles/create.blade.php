@extends('layouts.panel')

@section('title', 'Tambah Artikel')
@section('page-title', 'Tambah Artikel')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Tulis Artikel Baru</h2>
            <p class="panel-muted small mb-0">
                Artikel berstatus <strong>Dipublikasikan</strong> akan tampil di halaman beranda dan daftar artikel publik.
            </p>
        </div>
        <a href="{{ route('super-admin.articles.index') }}" class="btn btn-outline-secondary btn-panel align-self-start align-self-md-center">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>

    <form method="POST" action="{{ route('super-admin.articles.store') }}" enctype="multipart/form-data" data-panel-form novalidate>
        @csrf

        <div class="row g-4">
            <div class="col-12 col-xl-8">
                <div class="panel-card p-3 p-md-4 h-100">
                    <h3 class="panel-section-title mb-3 pb-2 border-bottom">Isi Artikel</h3>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label for="title" class="panel-form-label">
                                Judul Artikel <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                name="title"
                                value="{{ old('title') }}"
                                maxlength="200"
                                required
                                data-slug-source="slug-preview"
                                placeholder="Contoh: 5 Skill yang Paling Dicari Perusahaan"
                                class="form-control panel-form-control @error('title') is-invalid @enderror"
                                @error('title') aria-invalid="true" aria-describedby="title-error" @else aria-describedby="title-hint" @enderror
                            >
                            @error('title')
                                <div class="invalid-feedback d-block" id="title-error">{{ $message }}</div>
                            @else
                                <div class="form-text panel-muted" id="title-hint">Maksimal 200 karakter.</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="slug-preview" class="panel-form-label">Slug URL (Otomatis)</label>
                            <div class="input-group">
                                <span class="input-group-text slug-prefix" id="slug-addon">/artikel/</span>
                                <input
                                    type="text"
                                    id="slug-preview"
                                    value="{{ old('title') ? \Illuminate\Support\Str::limit(\Illuminate\Support\Str::slug((string) old('title')), 200, '') : '' }}"
                                    placeholder="dibuat-otomatis"
                                    readonly
                                    class="form-control panel-form-control"
                                    aria-describedby="slug-addon slug-hint"
                                >
                            </div>
                            <div class="form-text panel-muted" id="slug-hint">Dibuat otomatis dari judul saat disimpan.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="excerpt" class="panel-form-label">Ringkasan / Excerpt (Opsional)</label>
                        <textarea
                            id="excerpt"
                            name="excerpt"
                            rows="3"
                            maxlength="300"
                            placeholder="Tulis ringkasan singkat (1 sampai 2 kalimat) untuk ditampilkan pada kartu daftar artikel..."
                            class="form-control panel-form-control @error('excerpt') is-invalid @enderror"
                            @error('excerpt') aria-invalid="true" aria-describedby="excerpt-error" @else aria-describedby="excerpt-hint" @enderror
                        >{{ old('excerpt') }}</textarea>
                        @error('excerpt')
                            <div class="invalid-feedback d-block" id="excerpt-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="excerpt-hint">Maksimal 300 karakter.</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="content" class="panel-form-label">
                            Konten Artikel <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <textarea
                            id="content"
                            name="content"
                            rows="12"
                            required
                            placeholder="Tulis pembahasan lengkap artikel di sini. Pisahkan antarparagraf dengan baris kosong agar nyaman dibaca..."
                            class="form-control panel-form-control @error('content') is-invalid @enderror"
                            @error('content') aria-invalid="true" aria-describedby="content-error" @else aria-describedby="content-hint" @enderror
                        >{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback d-block" id="content-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="content-hint">Wajib diisi. Mendukung teks multi-paragraf.</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="panel-card p-3 p-md-4">
                    <h3 class="panel-section-title mb-3 pb-2 border-bottom">Publikasi &amp; Sampul</h3>

                    <div class="mb-3">
                        <label for="status" class="panel-form-label">
                            Status Publikasi <span class="text-danger" aria-hidden="true">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="form-select panel-form-select @error('status') is-invalid @enderror"
                            @error('status') aria-invalid="true" aria-describedby="status-error" @else aria-describedby="status-hint" @enderror
                        >
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'draft') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="invalid-feedback d-block" id="status-error">{{ $message }}</div>
                        @else
                            <div class="form-text panel-muted" id="status-hint">
                                Pilih <strong>Draf</strong> untuk menyimpan sementara atau <strong>Dipublikasikan</strong> untuk langsung menayangkan.
                            </div>
                        @enderror
                    </div>

                    <div class="mb-4 pt-2 border-top" data-image-uploader>
                        <label for="cover_image" class="panel-form-label">Gambar Sampul (Opsional)</label>
                        <input
                            type="file"
                            id="cover_image"
                            name="cover_image"
                            accept="image/jpeg,image/png,image/webp"
                            data-image-input
                            class="form-control panel-form-control @error('cover_image') is-invalid @enderror @error('cover') is-invalid @enderror"
                            @if ($errors->has('cover_image') || $errors->has('cover')) aria-invalid="true" aria-describedby="cover_image-error" @else aria-describedby="cover_image-hint" @endif
                        >
                        @if ($errors->has('cover_image') || $errors->has('cover'))
                            <div class="invalid-feedback d-block" id="cover_image-error">
                                {{ $errors->first('cover_image') ?: $errors->first('cover') }}
                            </div>
                        @else
                            <div class="form-text panel-muted" id="cover_image-hint">Format JPG, JPEG, PNG, atau WebP. Ukuran maksimal 3 MB.</div>
                        @endif

                        <div class="alert alert-danger py-2 px-3 small mt-2 mb-0 d-none" role="alert" data-image-error></div>

                        <div class="image-preview-frame mt-3">
                            <div data-image-empty>
                                <i class="bi bi-image fs-2 panel-muted d-block mb-1" aria-hidden="true"></i>
                                <p class="small fw-semibold mb-1">Belum ada sampul dipilih</p>
                                <p class="small panel-muted mb-0">Opsional. Unggah gambar rasio 16:9 agar kartu artikel tampak informatif.</p>
                            </div>

                            <div class="d-none" data-image-preview-wrap>
                                <img
                                    src=""
                                    alt="Pratinjau sampul artikel yang dipilih"
                                    width="640"
                                    height="360"
                                    class="image-preview-media mb-2"
                                    data-image-preview
                                >
                                <div class="small panel-muted text-truncate" data-image-meta></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary btn-panel flex-grow-1" data-submit-btn>
                            <span data-submit-default>
                                <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Simpan Artikel
                            </span>
                            <span class="d-none" data-submit-loading>
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Menyimpan...
                            </span>
                        </button>
                        <a href="{{ route('super-admin.articles.index') }}" class="btn btn-outline-secondary btn-panel">
                            Batal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
