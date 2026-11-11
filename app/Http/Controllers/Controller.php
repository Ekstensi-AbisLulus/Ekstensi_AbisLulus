<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Halaman sementara (layout publik) untuk fitur yang belum diimplementasikan.
     */
    protected function placeholder(string $title, ?string $pbi = null): View
    {
        return view('placeholders.public', compact('title', 'pbi'));
    }

    /**
     * Halaman sementara (layout panel admin) untuk fitur yang belum diimplementasikan.
     */
    protected function panelPlaceholder(string $title, ?string $pbi = null): View
    {
        return view('placeholders.panel', compact('title', 'pbi'));
    }

    /**
     * Aksi (POST/PUT/DELETE) yang belum diimplementasikan: kembali dengan pesan.
     */
    protected function notAvailable(?string $pbi = null): RedirectResponse
    {
        return back()->with('error', 'Fitur ini belum tersedia'.($pbi ? " ({$pbi})" : '').'.');
    }
}
