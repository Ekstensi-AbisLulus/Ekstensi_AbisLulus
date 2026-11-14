<aside class="col-lg-4 col-xl-3">
    <div class="card profile-card border-0 shadow-sm rounded-4 text-center p-4">
        @if ($user->profile?->avatar_path)
            <img src="{{ asset('storage/'.$user->profile->avatar_path) }}" class="profile-avatar mx-auto mb-3" alt="Foto profil {{ $user->name }}">
        @else
            <span class="profile-avatar profile-avatar-default mx-auto mb-3" aria-label="Avatar {{ $user->name }}">
                {{ filled($user->name) ? mb_strtoupper(mb_substr($user->name, 0, 1)) : '' }}
                @if (blank($user->name)) <i class="bi bi-person-fill"></i> @endif
            </span>
        @endif
        <h2 class="h5 mb-1">{{ $user->name }}</h2>
        <p class="text-secondary small mb-4">{{ $user->email }}</p>
        <nav class="nav nav-pills flex-column text-start gap-1">
            <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : 'text-body' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil</a>
            <a class="nav-link {{ request()->routeIs('profile.history.elearning') ? 'active' : 'text-body' }}" href="{{ route('profile.history.elearning') }}"><i class="bi bi-play-circle me-2"></i>Aktivitas E-Learning</a>
            <a class="nav-link {{ request()->routeIs('profile.history.events') ? 'active' : 'text-body' }}" href="{{ route('profile.history.events') }}"><i class="bi bi-calendar-event me-2"></i>Aktivitas Kegiatan</a>
        </nav>
    </div>
</aside>
