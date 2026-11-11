@extends('website.layouts.app')

@section('website-content')
    <div class="page-title">
        <div class="container d-lg-flex justify-content-between align-items-center">
            <h1 class="mb-2 mb-lg-0">Edit Pembelajaran</h1>
            <nav class="breadcrumbs">
                <ol>
                    <li><a href="index.html">Profile</a></li>
                    <li class="current">Edit Pembelajaran</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-12">
                <section id="blog-details" class="blog-details section">
                    <div class="container">
                        <article class="article">
                            <form action="{{ route('pembelajaran.update', $pembelajaran->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                {{-- <input type="hidden" name="id"> --}}
                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul</label>
                                    <input type="text" name="judul" class="form-control" id="judul"
                                        value="{{ old('judul', $pembelajaran->judul) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select name="kategori" id="kategori" class="form-control" required>
                                        <option value="">Pilih</option>
                                        <option value="Soft Skill" {{ old('kategori', $pembelajaran->kategori) == 'Soft Skill' ? 'selected' : '' }}>Soft Skill</option>
                                        <option value="Hard Skill" {{ old('kategori', $pembelajaran->kategori) == 'Hard Skill' ? 'selected' : '' }}>Hard Skill</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="5" required>{{ old('deskripsi', $pembelajaran->deskripsi) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="link_video" class="form-label">Link Video</label>
                                    <input type="text" name="link_video" class="form-control" id="link_video"
                                        value="{{ old('link_video', $pembelajaran->link_video) }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="thumbnail" class="form-label">Ganti Thumbnail (Opsional)</label>
                                    <input type="file" name="thumbnail" class="form-control" accept="image/*">
                                    <br>
                                    @if($pembelajaran->thumbnail)
                                        <img src="{{ Storage::url($pembelajaran->thumbnail) }}" class="img-thumbnail" style="max-height: 100px;" alt="{{ $pembelajaran->judul }}">
                                        <p class="text-muted mb-0"><small>Thumbnail saat ini</small></p>
                                    @else
                                        <img src="{{ asset('website/assets/img/no-image.webp') }}" class="img-thumbnail" style="max-height: 100px;" alt="Default">
                                        <p class="text-muted mb-0"><small>Thumbnail saat ini</small></p>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="ri-save-line"></i>
                                    Update
                                </button>
                                <a href="{{ url('kelola/pembelajaran') }}" class="btn btn-sm btn-secondary">
                                    <i class="ri-arrow-left-line"></i>
                                    Kembali
                                </a>
                            </form>
                        </article>
                    </div>
                </section>
            </div><!-- End Team Member -->
        </div>
    </div>
@endsection