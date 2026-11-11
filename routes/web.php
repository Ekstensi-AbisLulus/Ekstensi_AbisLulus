<?php

use App\Http\Controllers\Admin\PembelajaranController as AdminPembelajaranController;
use App\Http\Controllers\User\PembelajaranController as UserPembelajaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route untuk Pembelajaran (Admin)
Route::get('/pembelajaran', [AdminPembelajaranController::class, 'index'])->name('pembelajaran.index');
Route::get('/pembelajaran/create', [AdminPembelajaranController::class, 'create'])->name('pembelajaran.create');
Route::post('/pembelajaran', [AdminPembelajaranController::class, 'store'])->name('pembelajaran.store');
Route::get('/pembelajaran/{id}/edit', [AdminPembelajaranController::class, 'edit'])->name('pembelajaran.edit');
Route::put('/pembelajaran/{id}', [AdminPembelajaranController::class, 'update'])->name('pembelajaran.update');

// Route untuk Pembelajaran (User)
Route::get('/user/pembelajaran', [UserPembelajaranController::class, 'index'])->name('user.pembelajaran.index');
Route::get('/user/pembelajaran/{id}', [UserPembelajaranController::class, 'details'])->name('user.pembelajaran.details');
