<!DOCTYPE html>
<html lang="id">
<head>
    @include('partials.head')
</head>
<body>
    @php
        $user = auth()->user();
        $isSuperAdmin = (bool) $user?->isSuperAdmin();

        // 'active' = pola nama route (request()->routeIs) untuk menandai menu aktif.
        $dashboardRoute = $isSuperAdmin ? 'super-admin.dashboard' : 'admin.dashboard';

        $adminMenu = [
            ['label' => 'Dashboard', 'icon' => 'speedometer2', 'route' => $dashboardRoute, 'active' => $dashboardRoute],
            ['label' => 'Profil', 'icon' => 'person', 'route' => 'admin.profile.edit', 'active' => 'admin.profile.*'],
            ['label' => 'Riwayat Aktivitas', 'icon' => 'clock-history', 'route' => 'admin.history', 'active' => 'admin.history'],
            ['label' => 'Manajemen E-Learning', 'icon' => 'play-btn', 'route' => 'admin.materials.index', 'active' => 'admin.materials.*'],
            ['label' => 'Manajemen Kuis', 'icon' => 'patch-question', 'route' => 'admin.quizzes.index', 'active' => 'admin.quizzes.*'],
            ['label' => 'Manajemen Kegiatan', 'icon' => 'calendar-event', 'route' => 'admin.events.index', 'active' => 'admin.events.*'],
        ];

        $superAdminMenu = [
            ['label' => 'Manajemen Akun Admin', 'icon' => 'people', 'route' => 'super-admin.admins.index', 'active' => 'super-admin.admins.*'],
            ['label' => 'Hero Section', 'icon' => 'image', 'route' => 'super-admin.hero-sections.index', 'active' => 'super-admin.hero-sections.*'],
            ['label' => 'Artikel', 'icon' => 'newspaper', 'route' => 'super-admin.articles.index', 'active' => 'super-admin.articles.*'],
            ['label' => 'Testimoni', 'icon' => 'chat-quote', 'route' => 'super-admin.testimonials.index', 'active' => 'super-admin.testimonials.*'],
        ];

        $roleLabel = $user?->role?->label() ?? 'Admin';
    @endphp

    <div class="d-lg-flex">
        <aside class="panel-sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="panelSidebar" aria-labelledby="panelSidebarLabel">
            <div class="offcanvas-header border-bottom">
                <a href="{{ route('home') }}" id="panelSidebarLabel" aria-label="AbisLulus — ke beranda">
                    <img src="{{ asset('images/logo-abislulus.png') }}" alt="AbisLulus" class="navbar-logo" width="797" height="531">
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#panelSidebar" aria-label="Tutup"></button>
            </div>

            <div class="offcanvas-body d-flex flex-column p-3 h-100">
                <a href="{{ route('home') }}" class="d-none d-lg-block px-2 mb-3" aria-label="AbisLulus — ke beranda">
                    <img src="{{ asset('images/logo-abislulus.png') }}" alt="AbisLulus" class="navbar-logo" width="797" height="531">
                </a>

                <div class="d-flex align-items-center gap-3 px-2 pb-3 mb-2 border-bottom">
                    @include('partials.avatar', ['user' => $user, 'size' => 'lg', 'class' => 'flex-shrink-0'])
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-truncate">{{ $user?->name ?? 'Admin' }}</div>
                        <span class="badge text-bg-primary bg-opacity-75">{{ $roleLabel }}</span>
                    </div>
                </div>

                <nav class="nav flex-column gap-1">
                    @foreach ($adminMenu as $item)
                        <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                            <i class="bi bi-{{ $item['icon'] }}"></i>{{ $item['label'] }}
                        </a>
                    @endforeach

                    @if ($isSuperAdmin)
                        <div class="nav-heading">Super Admin</div>
                        @foreach ($superAdminMenu as $item)
                            <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                                <i class="bi bi-{{ $item['icon'] }}"></i>{{ $item['label'] }}
                            </a>
                        @endforeach
                    @endif
                </nav>

                <form method="POST" action="{{ route('logout') }}" class="mt-auto pt-4">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                </form>
            </div>
        </aside>

        <div class="flex-grow-1 d-flex flex-column min-vh-100">
            <header class="panel-topbar px-3 px-lg-4 py-3 d-flex align-items-center gap-3">
                <button class="btn btn-light d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#panelSidebar"
                    aria-controls="panelSidebar" aria-label="Buka menu">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <h1 class="h5 mb-0">@yield('page-title', 'Dashboard')</h1>
            </header>

            <main class="p-3 p-lg-4 flex-grow-1">
                @include('partials.flash')

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
