<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Validasi kredensial dulu tanpa login, agar akun nonaktif bisa ditolak dengan pesan khusus.
        if (! Auth::validate($request->credentials())) {
            return back()
                ->withErrors(['email' => 'Email atau password salah.'])
                ->onlyInput('email', 'remember');
        }

        /** @var User $user */
        $user = Auth::getLastAttempted();

        if (! $user->isActive()) {
            return back()
                ->withErrors(['email' => 'Akun dinonaktifkan. Silakan hubungi admin.'])
                ->onlyInput('email', 'remember');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route($user->homeRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Kamu berhasil keluar.');
    }
}
