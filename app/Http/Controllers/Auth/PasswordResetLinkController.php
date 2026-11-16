<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * PBI-03: respons selalu sama (terdaftar, tidak terdaftar, atau terkena throttle)
     * agar keberadaan sebuah email tidak bisa ditebak.
     */
    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Jika email terdaftar, link reset password sudah dikirim. Cek kotak masuk email kamu.');
    }
}
