<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileHistoryController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SuperAdmin;
use App\Http\Controllers\TestimonialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Publik (PRD §8.4)
|--------------------------------------------------------------------------
*/
Route::view('/', 'landing')->name('home');
Route::get('/e-learning', [MaterialController::class, 'index'])->name('materials.index');
Route::get('/e-learning/{material}', [MaterialController::class, 'show'])->name('materials.show');
Route::get('/kegiatan', [EventController::class, 'index'])->name('events.index');
Route::get('/kegiatan/{event}', [EventController::class, 'show'])->name('events.show');
Route::get('/artikel', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/artikel/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/tentang', [PageController::class, 'about'])->name('about');

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Semua pengguna yang login
|--------------------------------------------------------------------------
*/
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Auth + role:user
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil/foto', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::get('/profil/riwayat/e-learning', [ProfileHistoryController::class, 'learning'])->name('profile.history.elearning');
    Route::get('/profil/riwayat/kegiatan', [ProfileHistoryController::class, 'events'])->name('profile.history.events');

    Route::get('/kuis/hasil/{attempt}', [QuizController::class, 'result'])->name('quizzes.result');
    Route::get('/kuis/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::post('/kuis/{quiz}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');

    Route::post('/e-learning/{material}/ulasan', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/e-learning/{material}/ulasan', [ReviewController::class, 'update'])->name('reviews.update');

    Route::post('/kegiatan/{event}/daftar', [EventRegistrationController::class, 'store'])->name('events.register');
    Route::delete('/kegiatan/{event}/daftar', [EventRegistrationController::class, 'destroy'])->name('events.unregister');

    Route::post('/testimoni', [TestimonialController::class, 'store'])->name('testimonials.store');
    Route::put('/testimoni', [TestimonialController::class, 'update'])->name('testimonials.update');
});

/*
|--------------------------------------------------------------------------
| Panel Admin — prefix /admin, role:admin,super_admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profil', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::get('/riwayat', [Admin\HistoryController::class, 'index'])->name('history');

        Route::resource('materials', Admin\MaterialController::class);
        Route::resource('quizzes', Admin\QuizController::class);
        Route::get('/events/{event}/peserta', [Admin\EventController::class, 'participants'])->name('events.participants');
        Route::resource('events', Admin\EventController::class);
    });

/*
|--------------------------------------------------------------------------
| Panel Super Admin — prefix /super-admin, role:super_admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('admins', SuperAdmin\AdminController::class);
        Route::resource('hero-sections', SuperAdmin\HeroSectionController::class);
        Route::resource('articles', SuperAdmin\ArticleController::class);

        Route::get('/testimonials', [SuperAdmin\TestimonialController::class, 'index'])->name('testimonials.index');
        Route::patch('/testimonials/{testimonial}/toggle', [SuperAdmin\TestimonialController::class, 'toggle'])->name('testimonials.toggle');
    });
