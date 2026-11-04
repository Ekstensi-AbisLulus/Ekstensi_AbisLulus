<header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container position-relative d-flex align-items-center justify-content-between">

        <a href="{{ url('/') }}" class="logo d-flex align-items-center me-auto me-xl-0">
            <!-- Uncomment the line below if you also wish to use an image logo -->
            <img src="{{ url('website/assets/img/logo.png') }}" alt="">
            {{-- <h1 class="sitename">Bursa Talenta</h1> --}}
        </a>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="{{ url('/') }}" class="{{ Request::is('/') ? 'active' : '' }}">Home</a></li>
                <li><a href="{{ url('e-learning') }}" class="{{ Request::is('e-learning') ? 'active' : '' }}">E-Learning</a></li>
                <li><a href="{{ url('kegiatan') }}" class="{{ Request::is('kegiatan') ? 'active' : '' }}">Kegiatan</a></li>
                <li><a href="{{ url('tentang-kami') }}" class="{{ Request::is('tentang-kami') ? 'active' : '' }}">Tentang Kami</a></li>
            </ul>
            <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

        {{-- <div class="header-social-links">
            <button class="btn btn-outline-primary btn-flat">Pengguna Baru?</button>
            <button class="btn btn-default btn-flat">Masuk</button>
        </div> --}}

        <div class="header-social-links d-flex gap-2 align-items-center">
            @guest
                <button class="btn btn-light btn-sm" id="btn-register">
                    {{-- <i class="ri-file-edit-line"></i> --}}
                    Pengguna Baru?
                </button>
                <button class="btn btn-primary btn-sm" id="btn-masuk">
                    Masuk
                </button>
            @endguest

            @auth
                <div class="dropdown">
                    <a href="#" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">

                        @if (Auth::user()->role == 'admin')
                        {{-- jika admin, maka akan muncul menu baru --}}
                            <li><a class="dropdown-item" href="{{ url('kelola/pembelajaran') }}">Kelola Pembelajaran</a></li>
                            <li><a class="dropdown-item" href="{{ url('kelola/kegiatan') }}">Kelola Kegiatan</a></li>
                        @elseif (Auth::user()->role == 'superadmin')
                            <li><a class="dropdown-item" href="{{ url('dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ url('kelola/admin') }}">Kelola Admin</a></li>
                        @else 
                        {{-- jika pengguna, maka akan muncul menu aktivitas --}}
                            <li><a class="dropdown-item" href="{{ url('logs/e-learning') }}">Aktivitas E-Learning</a></li>
                            <li><a class="dropdown-item" href="{{ url('logs/kegiatan') }}">Aktivitas Kegiatan</a></li>
                        @endif

                        <hr>
                        <li><a class="dropdown-item" href="{{ url('profile') }}">Profil Saya</a></li>
                        <hr>

                        <li><a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout</a>
                        </li>
                    </ul>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endauth

        </div>


    </div>
</header>