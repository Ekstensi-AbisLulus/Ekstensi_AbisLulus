@extends('layouts.panel')

@section('title', 'Hero Section')
@section('page-title', 'Hero Section')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Daftar Hero Section</h2>
            <p class="panel-muted small mb-0">
                Kelola banner utama di halaman beranda. Jika lebih dari satu hero berstatus aktif, beranda akan menampilkannya sebagai carousel.
            </p>
        </div>
        <a href="{{ route('super-admin.hero-sections.create') }}" class="btn btn-primary btn-panel align-self-start align-self-md-center">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <span>+ Tambah Hero</span>
        </a>
    </div>

    <div class="panel-card overflow-hidden">
        @if ($heroSections->isEmpty())
            <div class="p-5 text-center">
                <i class="bi bi-images fs-1 panel-muted d-block mb-2" aria-hidden="true"></i>
                <h3 class="h6 fw-semibold mb-1">Belum ada hero section</h3>
                <p class="panel-muted small mb-3">
                    Tambahkan banner hero pertama agar halaman beranda menampilkan visual utama program AbisLulus.
                </p>
                <a href="{{ route('super-admin.hero-sections.create') }}" class="btn btn-primary btn-panel">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    <span>+ Tambah Hero</span>
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table panel-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Pratinjau</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Subjudul</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($heroSections as $hero)
                            <tr>
                                <td>
                                    <img
                                        src="{{ $hero->imageSrc() }}"
                                        alt="Pratinjau {{ $hero->title }}"
                                        width="96"
                                        height="54"
                                        loading="lazy"
                                        class="table-thumb"
                                    >
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $hero->title }}</div>
                                    @if ($hero->target_url)
                                        <div class="small panel-muted text-truncate" style="max-width: 240px;">
                                            <i class="bi bi-link-45deg" aria-hidden="true"></i>{{ $hero->target_url }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small panel-muted" style="max-width: 300px;">
                                        {{ $hero->subtitle ? \Illuminate\Support\Str::limit($hero->subtitle, 90) : '-' }}
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('super-admin.hero-sections.toggle', $hero) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="badge-status {{ $hero->is_active ? 'badge-status-active' : 'badge-status-inactive' }}"
                                            aria-label="{{ $hero->is_active ? 'Nonaktifkan hero '.$hero->title : 'Aktifkan hero '.$hero->title }}"
                                            title="Klik untuk mengubah status tampil"
                                        >
                                            <i class="bi {{ $hero->is_active ? 'bi-check-circle-fill' : 'bi-pause-circle' }}" aria-hidden="true"></i>
                                            <span>{{ $hero->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="small panel-muted text-nowrap">
                                    {{ $hero->created_at?->format('d M Y') ?? '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a
                                            href="{{ route('super-admin.hero-sections.show', $hero) }}"
                                            class="btn-icon btn-icon-view"
                                            aria-label="Lihat pratinjau {{ $hero->title }}"
                                            title="Lihat pratinjau"
                                        >
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </a>
                                        <a
                                            href="{{ route('super-admin.hero-sections.edit', $hero) }}"
                                            class="btn-icon btn-icon-edit"
                                            aria-label="Ubah {{ $hero->title }}"
                                            title="Ubah"
                                        >
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="{{ route('super-admin.hero-sections.destroy', $hero) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn-icon btn-icon-delete"
                                                aria-label="Hapus {{ $hero->title }}"
                                                title="Hapus"
                                            >
                                                <i class="bi bi-trash" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($heroSections->hasPages())
                <div class="p-3 border-top">
                    {{ $heroSections->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
