@extends('layouts.app')

@section('title', 'E-Learning')

@section('content')
    <section class="hero py-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-7">
                    <span class="badge text-bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2 rounded-pill mb-3">
                        E-Learning Gratis
                    </span>
                    <h1 class="display-6 fw-bold mb-2">Materi Pembelajaran Video</h1>
                    <p class="text-secondary mb-0">
                        Pelajari berbagai materi persiapan karier melalui video pembelajaran praktis yang dapat diakses kapan saja tanpa biaya.
                    </p>
                </div>
                <div class="col-12 col-lg-5">
                    <form method="GET" action="{{ route('materials.index') }}" class="card p-3">
                        <label for="search-material" class="visually-hidden">Cari judul materi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search text-secondary" aria-hidden="true"></i>
                            </span>
                            <input
                                type="search"
                                id="search-material"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Cari judul materi..."
                                class="form-control border-start-0"
                            >
                            <button type="submit" class="btn btn-primary">Cari</button>
                            @if ($search !== '')
                                <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary">Reset</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($materials->isEmpty())
                <div class="card p-5 text-center text-secondary">
                    <i class="bi bi-collection-play fs-1 d-block mb-2" aria-hidden="true"></i>
                    @if ($search !== '')
                        <h2 class="h5 fw-semibold text-body mb-1">Materi tidak ditemukan</h2>
                        <p class="small mb-3">
                            Tidak ada materi pembelajaran dengan judul yang memuat <strong>"{{ $search }}"</strong>.
                        </p>
                        <div>
                            <a href="{{ route('materials.index') }}" class="btn btn-outline-secondary">
                                Tampilkan Semua Materi
                            </a>
                        </div>
                    @else
                        <h2 class="h5 fw-semibold text-body mb-1">Belum ada materi pembelajaran</h2>
                        <p class="small mb-0">Materi pembelajaran video akan segera tersedia di halaman ini.</p>
                    @endif
                </div>
            @else
                <div class="row g-4">
                    @foreach ($materials as $material)
                        <div class="col-12 col-md-6 col-lg-4">
                            <article class="card h-100 d-flex flex-column overflow-hidden">
                                <a href="{{ route('materials.show', $material) }}" class="text-decoration-none">
                                    @if ($material->thumbnailSrc())
                                        <img
                                            src="{{ $material->thumbnailSrc() }}"
                                            alt="Thumbnail {{ $material->title }}"
                                            class="card-img-top object-fit-cover"
                                            style="height: 200px;"
                                        >
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center text-primary" style="height: 200px;">
                                            <i class="bi bi-play-circle-fill display-4" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </a>

                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <div class="d-flex align-items-center justify-content-between small text-secondary mb-2">
                                        <span>
                                            <i class="bi bi-person-circle me-1" aria-hidden="true"></i>{{ $material->admin?->name ?? 'Tim AbisLulus' }}
                                        </span>
                                        <span>
                                            @if ($material->average_rating !== null)
                                                <i class="bi bi-star-fill text-warning me-1" aria-hidden="true"></i>
                                                <strong class="text-body">{{ number_format($material->average_rating, 1) }}</strong>
                                                <span>({{ $material->reviews_count }})</span>
                                            @else
                                                <i class="bi bi-star text-secondary me-1" aria-hidden="true"></i>Belum ada ulasan
                                            @endif
                                        </span>
                                    </div>

                                    <h2 class="h5 fw-semibold mb-2">
                                        <a href="{{ route('materials.show', $material) }}" class="text-decoration-none text-body">
                                            {{ $material->title }}
                                        </a>
                                    </h2>

                                    <p class="text-secondary small mb-4 flex-grow-1">
                                        {{ \Illuminate\Support\Str::limit($material->description, 110) }}
                                    </p>

                                    <a href="{{ route('materials.show', $material) }}" class="btn btn-primary w-100 mt-auto">
                                        <i class="bi bi-play-fill me-1" aria-hidden="true"></i>Mulai Belajar
                                    </a>
                                </div>
                            </article>
                        </div>
                    @endforeach
                </div>

                @if ($materials->hasPages())
                    <div class="mt-5 d-flex justify-content-center">
                        {{ $materials->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
