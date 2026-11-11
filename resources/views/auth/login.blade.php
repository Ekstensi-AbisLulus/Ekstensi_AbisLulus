@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <section class="section">
        <div class="container">
            <div class="card overflow-hidden mx-auto" style="max-width: 960px;">
                <div class="row g-0">
                    <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center text-center p-5 hero">
                        <i class="bi bi-mortarboard text-primary" style="font-size: 7rem;"></i>
                        <h2 class="h4 mt-3">Selamat datang kembali!</h2>
                        <p class="text-secondary mb-0">Lanjutkan belajar dan ikuti kegiatan karier gratis bersama AbisLulus.</p>
                    </div>

                    <div class="col-lg-6 p-4 p-md-5">
                        <h1 class="h3 mb-1">Masuk</h1>
                        <p class="text-secondary mb-4">Masuk ke akun AbisLulus kamu.</p>

                        <form method="POST" action="{{ route('login') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    autocomplete="email" autofocus required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    autocomplete="current-password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                                    <label class="form-check-label" for="remember">Ingat saya</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="small">Lupa password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">Masuk</button>

                            <p class="text-center small mb-0">
                                Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
