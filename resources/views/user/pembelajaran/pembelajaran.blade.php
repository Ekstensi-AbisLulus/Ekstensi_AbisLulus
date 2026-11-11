@extends('website.layouts.app')

@section('website-content')
    <section id="team" class="team section">
        <div class="container">
            <form action="{{ url('e-learning') }}" method="GET">
                <div class="row">
                    <div class="col-lg-2">
                        <label for="" class="visually-hidden">Kategori</label>
                        <select name="kategori" id="kategori" class="form-control form-control-sm">
                            <option value="">Semua Kategori</option>
                            <option value="Soft Skill" {{ request('kategori') == 'Soft Skill' ? 'selected' : '' }}>Soft
                                Skill</option>
                            <option value="Hard Skill" {{ request('kategori') == 'Hard Skill' ? 'selected' : '' }}>Hard
                                Skill</option>
                        </select>
                    </div>

                    <div class="col-lg-3">
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" class="form-control form-control-sm" name="judul"
                                value="{{ request('judul') }}" placeholder="Cari pembelajaran"
                                aria-describedby="button-addon2">
                            <button class="btn btn-secondary" type="submit" id="button-addon2">
                                <i class="ri-search-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <br>

            <div class="row gy-4">
                @foreach ($pembelajaran as $item)
                    <div class="col-lg-6 aos-init aos-animate" data-aos="fade-up" data-aos-delay="400">
                        <div class="team-member d-flex align-items-start">
                            <div class="pic">
                                @if (!$item->thumbnail)
                                    <img src="{{ url('website/assets/img/no-image.webp') }}" class="img-fluid"
                                        alt="">
                                @else
                                    <img src="{{ Storage::url($item->thumbnail) }}" class="img-fluid" alt="">
                                @endif
                            </div>
                            <div class="member-info">
                                <h4>
                                    <a href="{{ url('e-learning/' . $item->id) }}">{{ $item->judul }}</a>
                                </h4>
                                <p>{{ \Illuminate\Support\Str::limit($item->deskripsi, 150, '...') }}</p>
                                <br>
                                <a href="{{ url('e-learning/' . $item->id) }}" class="btn btn-sm btn-primary">Mulai
                                    Belajar</a>
                            </div>
                        </div>
                    </div><!-- End Team Member -->
                @endforeach
            </div>
        </div>
    </section>
@endsection