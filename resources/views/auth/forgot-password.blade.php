@extends('layouts.auth')

@section('title', 'Lupa Password')
@section('greeting', 'Lupa password? Tenang saja!')
@section('tagline', 'Kami bantu atur ulang password kamu hanya dalam beberapa langkah.')

@section('content')
    <div class="auth-icon-badge"><i class="bi bi-key"></i></div>
    <h2 class="auth-title">Lupa Password</h2>
    <p class="auth-subtitle">Masukkan email akunmu. Kami akan mengirim link untuk mengatur ulang password (berlaku 60 menit).</p>

    @include('auth.partials.status')

    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        @include('auth.partials.field', [
            'name' => 'email', 'type' => 'email', 'label' => 'Email', 'icon' => 'envelope',
            'placeholder' => 'nama@email.com',
            'attrs' => 'autocomplete="email" autofocus required',
        ])

        <button type="submit" class="btn btn-primary btn-auth w-100 mt-2 mb-4">
            <i class="bi bi-send me-1"></i> Kirim Link Reset
        </button>

        <p class="auth-footnote text-center mb-0">
            <a href="{{ route('login') }}"><i class="bi bi-arrow-left me-1"></i>Kembali ke Masuk</a>
        </p>
    </form>
@endsection
