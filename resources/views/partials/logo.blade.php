{{--
    Logo AbisLulus. Pakai file asli di public/images/logo-abislulus.png bila ada,
    selain itu tampil wordmark + ikon sebagai pengganti.
    @param string $size  'sm' (header) | 'lg' (ilustrasi halaman auth)
--}}
@php
    $size = $size ?? 'sm';
    $logoPath = 'images/logo-abislulus.png';
@endphp

@if (file_exists(public_path($logoPath)))
    <img src="{{ asset($logoPath) }}" alt="AbisLulus — E-learning & Career Mentoring"
        class="auth-logo auth-logo-{{ $size }}">
@else
    <span class="auth-logo-fallback auth-logo-fallback-{{ $size }} brand-logo" role="img" aria-label="AbisLulus">
        <i class="bi bi-mortarboard-fill"></i>
        <span class="d-block">Abis<span>Lulus</span></span>
        @if ($size === 'lg')
            <small class="d-block text-secondary fw-medium">E-learning &bull; Career Mentoring</small>
        @endif
    </span>
@endif
