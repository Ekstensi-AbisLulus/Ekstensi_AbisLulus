<?php

use App\Http\Controllers\Admin\PembelajaranController as AdminPembelajaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pembelajaran', [AdminPembelajaranController::class, 'index'])->name('pembelajaran.index');
Route::get('/pembelajaran/create', [AdminPembelajaranController::class, 'create'])->name('pembelajaran.create');
Route::post('/pembelajaran', [AdminPembelajaranController::class, 'store'])->name('pembelajaran.store');


