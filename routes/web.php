<?php

use Illuminate\Support\Facades\Route;

// Dashboard route handled by controller
Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

// Keep `/home` compatible by redirecting to dashboard
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Authentication (simple)
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Registration
Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');

// Resource routes for kategori and buku
Route::resource('kategori', App\Http\Controllers\KategoriController::class);
Route::resource('buku', App\Http\Controllers\BukuController::class);
