<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="d-flex flex-column min-vh-100">
    @php
        // 'active' = pola nama route (request()->routeIs) untuk menandai menu aktif.
        $menu = [
            ['label' => 'Home', 'route' => 'home', 'active' => 'home'],
            ['label' => 'E-Learning', 'route' => 'materials.index', 'active' => 'materials.*'],
            ['label' => 'Kegiatan', 'route' => 'events.index', 'active' => 'events.index'],
            ['label' => 'Artikel', 'route' => 'articles.index', 'active' => 'articles.*'],
            ['label' => 'Tentang Kami', 'route' => 'about', 'active' => 'about'],
        ];
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light navbar-public sticky-top py-3">
        <div class="container">
            <a class="navbar-brand brand-logo fs-4" href="{{ route('home') }}">Abis<span>Lulus</span></a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar"
                aria-controls="publicNavbar" aria-expanded="false" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="publicNavbar">
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0 gap-lg-2">
                    @foreach ($menu as $item)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                                href="{{ route($item['route']) }}"
                                @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>

                @guest
                    <div class="d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                    </div>
                @endguest

                @auth
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-body dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="fw-medium">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            @if (auth()->user()->isUser())
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.history.learning') }}"><i class="bi bi-clock-history me-2"></i>Riwayat</a></li>
                            @else
                                <li><a class="dropdown-item" href="{{ route(auth()->user()->homeRoute()) }}"><i class="bi bi-speedometer2 me-2"></i>Panel Admin</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-grow-1">
        @if (session('success') || session('error'))
            <div class="container pt-4">
                @include('partials.flash')
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer pt-5 pb-4 mt-auto">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <a class="brand-logo fs-4 text-decoration-none" href="{{ route('home') }}">Abis<span>Lulus</span></a>
                    <p class="mt-3 mb-0 small">
                        Platform E-Learning &amp; Career Mentoring gratis untuk lulusan SMA/SMK dan fresh graduate
                        agar siap melangkah ke dunia kerja.
                    </p>
                </div>
                <div class="col-6 col-lg-3">
                    <h6 class="text-white mb-3">Jelajahi</h6>
                    <ul class="list-unstyled small d-grid gap-2 mb-0">
                        @foreach ($menu as $item)
                            <li><a href="{{ route($item['route']) }}">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-6 col-lg-4">
                    <h6 class="text-white mb-3">Hubungi Kami</h6>
                    <ul class="list-unstyled small d-grid gap-2 mb-3">
                        <li><i class="bi bi-envelope me-2"></i>halo@abislulus.id</li>
                        <li><i class="bi bi-geo-alt me-2"></i>Bandung, Jawa Barat</li>
                    </ul>
                    <div class="d-flex gap-3 fs-5">
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                        <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <p class="small mb-0 text-center">&copy; {{ date('Y') }} AbisLulus · Tim Ekstensi. Semua layanan gratis.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
