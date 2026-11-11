@extends('website.layouts.app')

@section('website-content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8">

                <!-- Blog Details Section -->
                <section id="blog-details" class="blog-details section">
                    <div class="container">

                        <article class="article">

                            <div class="post-img">
                                @if ($pembelajaran_detail->link_video)
                                <div class="ratio ratio-16x9 mb-3">
                                    <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}" title="YouTube Video" allowfullscreen></iframe>
                                </div>
                                @endif
                            </div>

                            <h2 class="title">{{ $pembelajaran_detail->judul }}</h2>

                            <div class="meta-top">
                                <ul>
                                    <li class="d-flex align-items-center"><i class="bi bi-person"></i> <a
                                            href="blog-details.html">{{ $pembelajaran_detail->soal_count }}</a></li>
                                    <li class="d-flex align-items-center"><i class="bi bi-clock"></i> <a
                                            href="blog-details.html"><time datetime="2020-01-01">{{ \Carbon\Carbon::parse($pembelajaran_detail->tanggal_kegiatan)->format('d M Y') }}</time></a></li>
                                </ul>
                            </div><!-- End meta top -->

                            <div class="content">
                                <p>{{ $pembelajaran_detail->deskripsi }}</p>
                            </div><!-- End post content -->

                            @guest
                                <button class="btn btn-secondary btn-flat" onclick="showLoginModal()">Login/Daftar untuk memulai Quiz</button>
                            @endguest

                            @auth
                                {{-- <form method="POST" action="{{ route('kegiatan.daftar', $pembelajaran_detail->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-light btn-flat">Mulai Quiz</button>
                                </form> --}}
                                <a href="{{ url('e-learning/' . $pembelajaran_detail->id . '/quiz') }}" class="btn btn-primary btn-flat">Mulai Quiz</a>
                            @endauth
                        </article>

                    </div>
                </section><!-- /Blog Details Section -->

            </div>

            <div class="col-lg-4 sidebar">
                <div class="widgets-container">
                    <!-- Recent Posts Widget -->
                    <div class="recent-posts-widget widget-item">
                        <h3 class="widget-title">Materi Lainnya</h3>
                        @foreach ($pembelajaran as $item)
                        <div class="post-item">
                            @if (!$item->thumbnail)
                            <img src="{{ url('website/assets/img/no-image.webp') }}" class="flex-shrink-0" alt="">    
                            @else
                            <img src="{{ Storage::url($item->thumbnail) }}" class="flex-shrink-0" alt="">    
                            @endif

                            <div>
                                <h4><a href="{{ url('e-learning/' . $item->id ) }}">{{ $item->judul }}</a></h4>
                                <time datetime="2020-01-01">{{ 'Tgl dibuat : ' . \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}</time>
                            </div>
                        </div><!-- End recent post item-->
                        @endforeach
                    </div><!--/Recent Posts Widget -->

                </div>

            </div>

        </div>
    </div>
@endsection