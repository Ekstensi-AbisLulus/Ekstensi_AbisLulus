@extends('layouts.panel')

@section('title', 'Manajemen E-Learning')
@section('page-title', 'Manajemen E-Learning')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Daftar Materi Pembelajaran Video</h2>
            <p class="text-secondary small mb-0">
                Kelola materi video E-Learning. Hanya materi berstatus <strong>Dipublikasikan</strong> yang tampil untuk pengguna.
            </p>
        </div>
        <a href="{{ route('admin.materials.create') }}" class="btn btn-primary align-self-start align-self-md-center">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Materi Baru
        </a>
    </div>

    <div class="card p-3 p-md-4 mb-4">
        <form method="GET" action="{{ route('admin.materials.index') }}" class="row g-2 align-items-end">
            <div class="col-12 col-md-8 col-lg-9">
                <label for="search" class="form-label fw-medium mb-1">Cari Judul Materi</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-secondary" aria-hidden="true"></i></span>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Ketik judul materi pembelajaran..."
                        class="form-control"
                    >
                </div>
            </div>
            <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Cari</button>
                @if ($search !== '')
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card overflow-hidden">
        @if ($materials->isEmpty())
            <div class="p-5 text-center text-secondary">
                <i class="bi bi-collection-play fs-1 d-block mb-2" aria-hidden="true"></i>
                @if ($search !== '')
                    <h3 class="h6 fw-semibold text-body mb-1">Materi tidak ditemukan</h3>
                    <p class="small mb-3">Tidak ada materi dengan judul yang memuat <strong>"{{ $search }}"</strong>.</p>
                    <a href="{{ route('admin.materials.index') }}" class="btn btn-outline-secondary">Tampilkan Semua Materi</a>
                @else
                    <h3 class="h6 fw-semibold text-body mb-1">Belum ada materi pembelajaran</h3>
                    <p class="small mb-3">Tambahkan materi pembelajaran video pertama untuk mulai ditampilkan di halaman E-Learning.</p>
                    <a href="{{ route('admin.materials.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Tambah Materi Baru
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4" style="width: 44px;">
                                <input class="form-check-input" type="checkbox" aria-label="Pilih semua materi" disabled>
                            </th>
                            <th scope="col" style="width: 112px;">Thumbnail</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Pembuat</th>
                            <th scope="col">Tanggal Unggah</th>
                            <th scope="col" class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($materials as $material)
                            <tr>
                                <td class="ps-4">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="{{ $material->id }}"
                                        aria-label="Pilih materi {{ $material->title }}"
                                    >
                                </td>
                                <td>
                                    @if ($material->thumbnailSrc())
                                        <img
                                            src="{{ $material->thumbnailSrc() }}"
                                            alt="Thumbnail {{ $material->title }}"
                                            width="88"
                                            height="50"
                                            class="rounded object-fit-cover border"
                                        >
                                    @else
                                        <div class="rounded bg-light border d-flex align-items-center justify-content-center text-secondary" style="width: 88px; height: 50px;">
                                            <i class="bi bi-play-btn fs-5" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">
                                        <a href="{{ route('admin.materials.show', $material) }}" class="text-decoration-none text-body">
                                            {{ $material->title }}
                                        </a>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                        @if ($material->is_published)
                                            <span class="badge text-bg-success bg-opacity-75">Dipublikasikan</span>
                                        @else
                                            <span class="badge text-bg-secondary">Draf</span>
                                        @endif
                                        <span class="small text-secondary text-truncate" style="max-width: 320px;">
                                            {{ \Illuminate\Support\Str::limit($material->description, 65) }}
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $material->admin?->name ?? 'Admin' }}</td>
                                <td class="text-secondary small">{{ $material->created_at?->format('d M Y') }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <a
                                            href="{{ route('admin.materials.show', $material) }}"
                                            class="btn-icon btn-icon-view"
                                            title="Lihat Detail"
                                            aria-label="Lihat {{ $material->title }}"
                                        >
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </a>
                                        @can('update', $material)
                                            <a
                                                href="{{ route('admin.materials.edit', $material) }}"
                                                class="btn-icon btn-icon-edit"
                                                title="Ubah Materi"
                                                aria-label="Ubah {{ $material->title }}"
                                            >
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </a>
                                        @endcan
                                        @can('delete', $material)
                                            <button
                                                type="button"
                                                class="btn-icon btn-icon-delete"
                                                title="Hapus Materi"
                                                aria-label="Hapus {{ $material->title }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteMaterialModal-{{ $material->id }}"
                                            >
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @foreach ($materials as $material)
                @can('delete', $material)
                    <div
                        class="modal fade"
                        id="deleteMaterialModal-{{ $material->id }}"
                        tabindex="-1"
                        aria-labelledby="deleteMaterialModalLabel-{{ $material->id }}"
                        aria-hidden="true"
                    >
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3 class="modal-title h6 fw-bold mb-0" id="deleteMaterialModalLabel-{{ $material->id }}">
                                        <i class="bi bi-exclamation-triangle-fill text-danger me-2" aria-hidden="true"></i>Konfirmasi Hapus Materi
                                    </h3>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2">
                                        Apakah Anda yakin ingin menghapus materi pembelajaran <strong>"{{ $material->title }}"</strong>?
                                    </p>
                                    <div class="alert alert-danger py-2 px-3 small mb-0">
                                        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
                                        <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan. Data materi, file thumbnail, serta seluruh ulasan terkait akan dihapus secara permanen dari database.
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        Batal
                                    </button>
                                    <form method="POST" action="{{ route('admin.materials.destroy', $material) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bi bi-trash me-1" aria-hidden="true"></i>Ya, Hapus Materi
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcan
            @endforeach

            @if ($materials->hasPages())
                <div class="px-4 py-3 border-top">
                    {{ $materials->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
