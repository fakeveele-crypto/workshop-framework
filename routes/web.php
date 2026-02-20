<?php

use Illuminate\Support\Facades\Route;


// Public auth routes
Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.post');
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');

// Dashboard is public (guests can view the page)
Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/home', function () { return redirect()->route('dashboard'); });

// Protected routes: authenticated users with role 'user'
Route::middleware(['auth', \App\Http\Middleware\role::class])->group(function () {
    // Resource routes for kategori and buku
    Route::resource('kategori', App\Http\Controllers\KategoriController::class);
    Route::resource('buku', App\Http\Controllers\BukuController::class);

    // Logout should be accessible to authenticated users
    
});
