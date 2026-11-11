{{-- Kartu kiri halaman profil & riwayat admin: foto profil + menu (PBI-07/08/09). --}}
@php
    $profileMenu = [
        ['label' => 'Profil', 'icon' => 'person', 'route' => 'admin.profile.edit', 'active' => 'admin.profile.*'],
        ['label' => 'Riwayat Aktivitas', 'icon' => 'clock-history', 'route' => 'admin.history', 'active' => 'admin.history'],
    ];
@endphp

<div class="card profile-nav">
    <div class="card-body text-center pt-4">
        @include('partials.avatar', ['user' => $user, 'size' => 'xl', 'class' => 'mb-3'])
        <h2 class="h6 fw-semibold mb-1 text-break">{{ $user->name }}</h2>
        <span class="badge text-bg-primary bg-opacity-75">{{ $user->role->label() }}</span>
    </div>

    <nav class="nav flex-column gap-1 p-3 pt-0" aria-label="Menu profil">
        @foreach ($profileMenu as $item)
            <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}"
                @if (request()->routeIs($item['active'])) aria-current="page" @endif>
                <i class="bi bi-{{ $item['icon'] }}"></i>{{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>
