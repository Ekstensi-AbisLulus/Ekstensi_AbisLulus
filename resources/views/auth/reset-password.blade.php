@extends('layouts.auth')

@section('title', 'Atur Ulang Password')
@section('greeting', 'Buat password baru untuk akunmu')
@section('tagline', 'Gunakan password yang kuat dan mudah kamu ingat.')

@section('content')
    <div class="auth-icon-badge"><i class="bi bi-shield-lock"></i></div>
    <h2 class="auth-title">Atur Ulang Password</h2>
    <p class="auth-subtitle">Silakan masukkan password baru kamu</p>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        @include('auth.partials.field', [
            'name' => 'email', 'type' => 'email', 'label' => 'Email', 'icon' => 'envelope',
            'value' => old('email', $email),
            'placeholder' => 'nama@email.com',
            'attrs' => 'autocomplete="email" required'.($email ? ' readonly' : ' autofocus'),
        ])

        @include('auth.partials.field', [
            'name' => 'password', 'type' => 'password', 'label' => 'Password Baru', 'icon' => 'lock',
            'placeholder' => 'Buat password baru',
            'hint' => 'Minimal 8 karakter.',
            'attrs' => 'autocomplete="new-password" minlength="8" required'.($email ? ' autofocus' : ''),
        ])

        @include('auth.partials.field', [
            'name' => 'password_confirmation', 'type' => 'password', 'label' => 'Konfirmasi Password Baru', 'icon' => 'lock',
            'placeholder' => 'Ulangi password baru',
            'attrs' => 'autocomplete="new-password" required',
        ])

        <button type="submit" class="btn btn-primary btn-auth w-100 mt-2 mb-4">
            <i class="bi bi-check2-circle me-1"></i> Simpan Password
        </button>

        <p class="auth-footnote text-center mb-0">
            <a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Kembali ke Masuk</a>
        </p>
    </form>
@endsection
