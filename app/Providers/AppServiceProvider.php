<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tampilan pagination memakai Bootstrap 5 (bawaan Laravel = Tailwind).
        Paginator::useBootstrapFive();

        // Maks. 5 percobaan login per menit per kombinasi email + IP (PBI-02).
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by(Str::lower((string) $request->input('email')).'|'.$request->ip())
                ->response(fn () => back()
                    ->withErrors(['email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.'])
                    ->onlyInput('email'));
        });
    }
}
