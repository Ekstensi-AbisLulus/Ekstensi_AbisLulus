@extends('layouts.panel')

@section('title', 'Ubah Materi E-Learning')
@section('page-title', 'Manajemen E-Learning · Ubah Materi')

@section('content')
    @php
        $existingThumbnailUrl = is_string($material->thumbnail_url)
            && (str_starts_with($material->thumbnail_url, 'http://') || str_starts_with($material->thumbnail_url, 'https://'))
            ? $material->thumbnail_url
            : '';
    @endphp

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Ubah Materi Pembelajaran Video</h2>
            <p class="text-secondary small mb-0">
                Perbarui informasi materi video pembelajaran <strong>{{ $material->title }}</strong>.
            </p>
        </div>
        <button
            type="button"
            class="btn btn-outline-secondary align-self-start align-self-md-center"
            data-bs-toggle="modal"
            data-bs-target="#confirmCancelModal"
        >
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Kembali ke Daftar
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-start gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
            <div>
                <div class="fw-semibold">Perubahan belum dapat disimpan. Periksa kembali input berikut:</div>
                <ul class="small mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div id="clientValidationAlert" class="alert alert-danger d-none align-items-start gap-2 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
        <div>
            <div class="fw-semibold">Validasi gagal sebelum menyimpan perubahan:</div>
            <ul id="clientValidationList" class="small mb-0 mt-1 ps-3"></ul>
        </div>
    </div>

    <div class="card p-3 p-md-4">
        <form
            id="editMaterialForm"
            method="POST"
            action="{{ route('admin.materials.update', $material) }}"
            enctype="multipart/form-data"
            novalidate
        >
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="title" class="form-label fw-medium">
                    Judul Materi <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title', $material->title) }}"
                    maxlength="200"
                    required
                    placeholder="Contoh: Cara Membuat CV yang Dilirik HRD"
                    class="form-control @error('title') is-invalid @enderror"
                    @error('title') aria-invalid="true" aria-describedby="title-error" @else aria-describedby="title-hint" @enderror
                >
                <div class="invalid-feedback @error('title') d-block @enderror" id="title-error">
                    {{ $errors->first('title', 'Judul materi wajib diisi (maksimal 200 karakter).') }}
                </div>
                @unless ($errors->has('title'))
                    <div class="form-text" id="title-hint">Maksimal 200 karakter.</div>
                @endunless
            </div>

            <div class="mb-3">
                <label for="video_url" class="form-label fw-medium">
                    Link Video <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-play-btn" aria-hidden="true"></i></span>
                    <input
                        type="url"
                        id="video_url"
                        name="video_url"
                        value="{{ old('video_url', $material->video_url) }}"
                        maxlength="2048"
                        required
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="form-control @error('video_url') is-invalid @enderror"
                        @error('video_url') aria-invalid="true" aria-describedby="video-url-error" @else aria-describedby="video-url-hint" @enderror
                    >
                </div>
                <div class="invalid-feedback @error('video_url') d-block @enderror" id="video-url-error">
                    {{ $errors->first('video_url', 'Link video wajib berupa URL yang valid (http/https).') }}
                </div>
                @unless ($errors->has('video_url'))
                    <div class="form-text" id="video-url-hint">Masukkan URL video yang valid (misalnya tautan YouTube atau Google Drive).</div>
                @endunless
            </div>

            <div class="mb-3">
                <label for="description" class="form-label fw-medium">
                    Deskripsi Materi <span class="text-danger" aria-hidden="true">*</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    required
                    placeholder="Tuliskan ringkasan dan poin pembelajaran dari materi video ini..."
                    class="form-control @error('description') is-invalid @enderror"
                    @error('description') aria-invalid="true" aria-describedby="description-error" @enderror
                >{{ old('description', $material->description) }}</textarea>
                <div class="invalid-feedback @error('description') d-block @enderror" id="description-error">
                    {{ $errors->first('description', 'Deskripsi materi wajib diisi.') }}
                </div>
            </div>

            @if ($material->thumbnailSrc())
                <div class="mb-3">
                    <span class="form-label fw-medium d-block">Thumbnail Saat Ini</span>
                    <img
                        src="{{ $material->thumbnailSrc() }}"
                        alt="Thumbnail {{ $material->title }}"
                        width="200"
                        height="112"
                        class="rounded border object-fit-cover"
                    >
                </div>
            @endif

            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <label for="thumbnail" class="form-label fw-medium">Ganti dengan Unggah Thumbnail (Opsional)</label>
                    <input
                        type="file"
                        id="thumbnail"
                        name="thumbnail"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="form-control @error('thumbnail') is-invalid @enderror"
                        @error('thumbnail') aria-invalid="true" aria-describedby="thumbnail-error" @else aria-describedby="thumbnail-hint" @enderror
                    >
                    @error('thumbnail')
                        <div class="invalid-feedback d-block" id="thumbnail-error">{{ $message }}</div>
                    @else
                        <div class="form-text" id="thumbnail-hint">Format JPG, JPEG, PNG, atau WebP (maks. 2 MB). Kosongkan jika tidak ingin mengganti.</div>
                    @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="thumbnail_url" class="form-label fw-medium">Atau URL Thumbnail (Opsional)</label>
                    <input
                        type="url"
                        id="thumbnail_url"
                        name="thumbnail_url"
                        value="{{ old('thumbnail_url', $existingThumbnailUrl) }}"
                        maxlength="2048"
                        placeholder="https://example.com/thumbnail.jpg"
                        class="form-control @error('thumbnail_url') is-invalid @enderror"
                        @error('thumbnail_url') aria-invalid="true" aria-describedby="thumbnail-url-error" @else aria-describedby="thumbnail-url-hint" @enderror
                    >
                    @error('thumbnail_url')
                        <div class="invalid-feedback d-block" id="thumbnail-url-error">{{ $message }}</div>
                    @else
                        <div class="form-text" id="thumbnail-url-hint">Jika mengunggah file baru, file unggahan yang akan dipakai.</div>
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
                        id="is_published"
                        name="is_published"
                        value="1"
                        @checked((bool) old('is_published', $material->is_published))
                    >
                    <label class="form-check-label fw-medium" for="is_published">
                        Publikasikan materi
                    </label>
                </div>
                <div class="form-text">Hanya materi berstatus dipublikasikan yang tampil di halaman E-Learning pengguna.</div>
                @error('is_published')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                <button
                    type="button"
                    id="openSaveModalBtn"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmSaveModal"
                >
                    <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Simpan Perubahan
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmCancelModal"
                >
                    Batal
                </button>
            </div>
        </form>
    </div>

    {{-- Pop-up Modal Konfirmasi Simpan Perubahan --}}
    <div class="modal fade" id="confirmSaveModal" tabindex="-1" aria-labelledby="confirmSaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h6 fw-bold mb-0" id="confirmSaveModalLabel">
                        <i class="bi bi-question-circle text-primary me-2" aria-hidden="true"></i>Konfirmasi Simpan Perubahan
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Apakah Anda yakin ingin menyimpan perubahan pada materi pembelajaran ini?</p>
                    <div class="p-3 rounded bg-light border small text-secondary">
                        Pastikan <strong>Judul Materi</strong>, <strong>Link Video</strong>, dan <strong>Deskripsi Materi</strong> sudah benar sebelum disimpan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Periksa Kembali
                    </button>
                    <button type="submit" form="editMaterialForm" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1" aria-hidden="true"></i>Ya, Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Pop-up Modal Konfirmasi & Validasi Batal Perubahan --}}
    <div class="modal fade" id="confirmCancelModal" tabindex="-1" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title h6 fw-bold mb-0" id="confirmCancelModalLabel">
                        <i class="bi bi-exclamation-triangle text-warning me-2" aria-hidden="true"></i>Batalkan Perubahan?
                    </h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Apakah Anda yakin ingin membatalkan perubahan pada materi <strong>{{ $material->title }}</strong>?</p>
                    <div class="alert alert-warning py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>Seluruh perubahan yang belum disimpan akan hilang dan data materi tidak akan berubah.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Tetap Mengedit
                    </button>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-danger">
                        Ya, Batalkan Perubahan
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const saveModalEl = document.getElementById('confirmSaveModal');
            const titleInput = document.getElementById('title');
            const videoUrlInput = document.getElementById('video_url');
            const descriptionInput = document.getElementById('description');
            const thumbnailUrlInput = document.getElementById('thumbnail_url');
            const alertBox = document.getElementById('clientValidationAlert');
            const alertList = document.getElementById('clientValidationList');

            if (!saveModalEl || !titleInput || !videoUrlInput || !descriptionInput) {
                return;
            }

            const isValidHttpUrl = function (value) {
                try {
                    const parsed = new URL(value);
                    return parsed.protocol === 'http:' || parsed.protocol === 'https:';
                } catch (_) {
                    return false;
                }
            };

            saveModalEl.addEventListener('show.bs.modal', function (event) {
                const messages = [];

                [titleInput, videoUrlInput, descriptionInput, thumbnailUrlInput].forEach(function (el) {
                    if (el) {
                        el.classList.remove('is-invalid');
                    }
                });

                const titleVal = titleInput.value.trim();
                const videoVal = videoUrlInput.value.trim();
                const descVal = descriptionInput.value.trim();
                const thumbUrlVal = thumbnailUrlInput ? thumbnailUrlInput.value.trim() : '';

                if (titleVal === '') {
                    titleInput.classList.add('is-invalid');
                    messages.push('Judul materi wajib diisi.');
                } else if (titleVal.length > 200) {
                    titleInput.classList.add('is-invalid');
                    messages.push('Judul materi maksimal 200 karakter.');
                }

                if (videoVal === '') {
                    videoUrlInput.classList.add('is-invalid');
                    messages.push('Link video wajib diisi.');
                } else if (!isValidHttpUrl(videoVal)) {
                    videoUrlInput.classList.add('is-invalid');
                    messages.push('Link video harus berupa URL yang valid (http/https).');
                }

                if (descVal === '') {
                    descriptionInput.classList.add('is-invalid');
                    messages.push('Deskripsi materi wajib diisi.');
                }

                if (thumbUrlVal !== '' && !isValidHttpUrl(thumbUrlVal)) {
                    thumbnailUrlInput.classList.add('is-invalid');
                    messages.push('URL thumbnail harus berupa URL yang valid (http/https).');
                }

                if (messages.length > 0) {
                    event.preventDefault();
                    alertList.innerHTML = messages.map(function (msg) {
                        return '<li>' + msg + '</li>';
                    }).join('');
                    alertBox.classList.remove('d-none');
                    alertBox.classList.add('d-flex');
                    alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    alertBox.classList.add('d-none');
                    alertBox.classList.remove('d-flex');
                }
            });
        });
    </script>
@endpush
