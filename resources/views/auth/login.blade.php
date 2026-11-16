@extends('layouts.auth')

@section('title', 'Masuk')
@section('greeting', 'Selamat datang kembali!')
@section('tagline', 'Lanjutkan belajar dan ikuti kegiatan karier gratis bersama AbisLulus.')

@section('content')
    <h2 class="auth-title">Masuk ke AbisLulus</h2>
    <p class="auth-subtitle">Silakan masukkan informasi akun kamu</p>

    @include('auth.partials.status')

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        @include('auth.partials.field', [
            'name' => 'email', 'type' => 'email', 'label' => 'Email', 'icon' => 'envelope',
            'placeholder' => 'nama@email.com',
            'attrs' => 'autocomplete="email" autofocus required',
        ])

        @include('auth.partials.field', [
            'name' => 'password', 'type' => 'password', 'label' => 'Password', 'icon' => 'lock',
            'placeholder' => 'Masukkan password',
            'attrs' => 'autocomplete="current-password" required',
        ])

        <div class="d-flex justify-content-between align-items-center mb-4 small">
            <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <a href="{{ route('password.request') }}" class="fw-semibold text-decoration-none">Lupa Password?</a>
        </div>

        <button type="submit" class="btn btn-primary btn-auth w-100 mb-4">
            Masuk
        </button>

        <p class="auth-footnote text-center mb-0">
            Belum mempunyai akun? <a href="{{ route('register') }}">Daftar Sekarang</a>
        </p>
    </form>
@endsection
