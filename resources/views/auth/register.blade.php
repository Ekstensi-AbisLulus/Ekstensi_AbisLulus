@extends('layouts.auth')

@section('title', 'Daftar')
@section('greeting', 'Mari bergabung dengan AbisLulus!')
@section('tagline', 'Akses materi E-Learning, kuis, dan Career Mentoring — semuanya gratis.')

@section('content')
    <h2 class="auth-title">Daftar dulu yuk!</h2>
    <p class="auth-subtitle">Silakan isi form berikut</p>

    <form method="POST" action="{{ route('register') }}" novalidate>
        @csrf

        @include('auth.partials.field', [
            'name' => 'name', 'label' => 'Nama Lengkap', 'icon' => 'person',
            'placeholder' => 'Nama lengkap sesuai identitas',
            'attrs' => 'autocomplete="name" maxlength="100" autofocus required',
        ])

        @include('auth.partials.field', [
            'name' => 'email', 'type' => 'email', 'label' => 'Email', 'icon' => 'envelope',
            'placeholder' => 'nama@email.com',
            'attrs' => 'autocomplete="email" maxlength="150" required',
        ])

        @include('auth.partials.field', [
            'name' => 'password', 'type' => 'password', 'label' => 'Password', 'icon' => 'lock',
            'placeholder' => 'Buat password',
            'hint' => 'Minimal 8 karakter.',
            'attrs' => 'autocomplete="new-password" minlength="8" required',
        ])

        @include('auth.partials.field', [
            'name' => 'password_confirmation', 'type' => 'password', 'label' => 'Konfirmasi Password', 'icon' => 'lock',
            'placeholder' => 'Ulangi password',
            'attrs' => 'autocomplete="new-password" required',
        ])

        <button type="submit" class="btn btn-primary btn-auth w-100 mt-2 mb-4">
            Daftar
        </button>

        <p class="auth-footnote text-center mb-3">
            Sudah mempunyai akun? <a href="{{ route('login') }}">Masuk</a>
        </p>
        <p class="auth-terms text-center mb-0">
            Dengan membuat akun, saya setuju dengan <strong>Syarat dan Ketentuan</strong>,
            dan <strong>Kebijakan Privasi</strong>
        </p>
    </form>
@endsection
