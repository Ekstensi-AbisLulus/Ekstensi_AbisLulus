{{--
    Avatar bulat: foto profil bila ada, selain itu inisial nama.
    Param: $user (User|null), $size ('' | 'lg' | 'xl'), $class (opsional), $id (opsional, untuk preview foto).
--}}
@php
    $size = $size ?? '';
    $classes = trim('avatar '.($size ? 'avatar-'.$size : '').' '.($class ?? ''));
    $url = $user?->avatarUrl();
@endphp

@if ($url)
    <img src="{{ $url }}" alt="Foto profil {{ $user->name }}" class="{{ $classes }}" @isset($id) id="{{ $id }}" @endisset>
@else
    <span class="{{ $classes }}" aria-hidden="true" @isset($id) id="{{ $id }}" @endisset>{{ $user?->initial() ?? 'A' }}</span>
@endif
