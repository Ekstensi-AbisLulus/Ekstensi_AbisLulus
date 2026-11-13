@extends('layouts.panel')

@section('title', 'Manajemen Artikel')
@section('page-title', 'Manajemen Artikel')

@section('content')
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h5 fw-bold mb-1">Daftar Artikel</h2>
            <p class="panel-muted small mb-0">
                Kelola artikel panduan karier. Artikel berstatus <strong>Dipublikasikan</strong> akan tampil di halaman beranda dan daftar artikel publik.
            </p>
        </div>
        <a href="{{ route('super-admin.articles.create') }}" class="btn btn-primary btn-panel align-self-start align-self-md-center">
            <i class="bi bi-plus-lg" aria-hidden="true"></i>
            <span>+ Tambah Artikel</span>
        </a>
    </div>

    <div class="panel-card p-3 p-md-4 mb-4">
        <form method="GET" action="{{ route('super-admin.articles.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
                <label for="search" class="panel-form-label">Cari Judul Artikel</label>
                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Ketik kata kunci judul artikel..."
                    class="form-control panel-form-control"
                >
            </div>

            <div class="col-12 col-sm-6 col-md-3">
                <label for="status" class="panel-form-label">Filter Status</label>
                <select id="status" name="status" class="form-select panel-form-select">
                    <option value="">Semua Status</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-panel flex-grow-1">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>Cari</span>
                </button>
                @if ($search !== '' || $selectedStatus !== '')
                    <a href="{{ route('super-admin.articles.index') }}" class="btn btn-outline-secondary btn-panel">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="panel-card overflow-hidden">
        @if ($articles->isEmpty())
            <div class="p-5 text-center">
                <i class="bi bi-journal-text fs-1 panel-muted d-block mb-2" aria-hidden="true"></i>
                @if ($search !== '' || $selectedStatus !== '')
                    <h3 class="h6 fw-semibold mb-1">Tidak ada artikel yang cocok</h3>
                    <p class="panel-muted small mb-3">
                        Coba ubah kata kunci pencarian atau tampilkan semua status artikel.
                    </p>
                    <a href="{{ route('super-admin.articles.index') }}" class="btn btn-outline-secondary btn-panel">
                        Reset Pencarian
                    </a>
                @else
                    <h3 class="h6 fw-semibold mb-1">Belum ada artikel</h3>
                    <p class="panel-muted small mb-3">
                        Mulai tulis artikel pertama untuk dibaca oleh lulusan baru di halaman publik.
                    </p>
                    <a href="{{ route('super-admin.articles.create') }}" class="btn btn-primary btn-panel">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i>
                        <span>+ Tambah Artikel</span>
                    </a>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table panel-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Sampul</th>
                            <th scope="col">Judul</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal Publish</th>
                            <th scope="col" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($articles as $article)
                            <tr>
                                <td>
                                    <img
                                        src="{{ $article->coverImageSrc() ?? asset('images/placeholders/article-cover.svg') }}"
                                        alt="Sampul {{ $article->title }}"
                                        width="96"
                                        height="54"
                                        loading="lazy"
                                        class="table-thumb"
                                    >
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $article->title }}</div>
                                    @if ($article->excerpt)
                                        <div class="small panel-muted" style="max-width: 380px;">
                                            {{ \Illuminate\Support\Str::limit($article->excerpt, 90) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($article->status === \App\Enums\ArticleStatus::Published)
                                        <span class="badge-status badge-status-active">{{ $article->status->label() }}</span>
                                    @else
                                        <span class="badge-status badge-status-inactive">{{ $article->status->label() }}</span>
                                    @endif
                                </td>
                                <td class="small panel-muted text-nowrap">
                                    {{ $article->published_at ? $article->published_at->format('d M Y') : '-' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a
                                            href="{{ route('super-admin.articles.show', $article) }}"
                                            class="btn-icon btn-icon-view"
                                            aria-label="Lihat pratinjau {{ $article->title }}"
                                            title="Lihat pratinjau"
                                        >
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </a>
                                        <a
                                            href="{{ route('super-admin.articles.edit', $article) }}"
                                            class="btn-icon btn-icon-edit"
                                            aria-label="Ubah {{ $article->title }}"
                                            title="Ubah"
                                        >
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                        <form method="POST" action="{{ route('super-admin.articles.destroy', $article) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn-icon btn-icon-delete"
                                                aria-label="Hapus {{ $article->title }}"
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

            @if ($articles->hasPages())
                <div class="p-3 border-top">
                    {{ $articles->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
