<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body class="auth-page">
    <div class="row g-0 min-vh-100">
        {{-- Panel kiri: sapaan + ilustrasi (hanya layar besar) --}}
        <aside class="col-lg-6 d-none d-lg-block">
            <div class="auth-aside h-100">
                <span class="auth-blob auth-blob-1" aria-hidden="true"></span>
                <span class="auth-blob auth-blob-2" aria-hidden="true"></span>

                <a href="{{ route('home') }}" class="auth-aside-logo" aria-label="Kembali ke beranda">
                    @include('partials.logo', ['size' => 'sm'])
                </a>

                <div class="auth-aside-body">
                    <h1 class="auth-greeting">@yield('greeting')</h1>
                    <p class="auth-tagline">@yield('tagline')</p>

                    <img src="{{ asset('images/auth-illustration.png') }}" alt="" class="auth-illustration" width="495" height="462">

                    <ul class="auth-features list-unstyled">
                        <li><i class="bi bi-patch-check-fill"></i>100% Gratis</li>
                        <li><i class="bi bi-play-circle-fill"></i>E-Learning &amp; Kuis</li>
                        <li><i class="bi bi-people-fill"></i>Career Mentoring</li>
                    </ul>
                </div>
            </div>
        </aside>

        {{-- Panel kanan: form --}}
        <main class="col-lg-6 d-flex flex-column">
            <div class="flex-grow-1 d-flex align-items-center justify-content-center px-3 py-5">
                <div class="auth-form w-100">
                    <a href="{{ route('home') }}" class="d-inline-block d-lg-none mb-4" aria-label="Kembali ke beranda">
                        @include('partials.logo', ['size' => 'sm'])
                    </a>

                    @yield('content')
                </div>
            </div>

            <p class="auth-copyright text-center mb-3">
                &copy; {{ date('Y') }} AbisLulus &middot; <a href="{{ route('home') }}">Kembali ke Beranda</a>
            </p>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
