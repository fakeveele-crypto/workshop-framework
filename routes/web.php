<?php

use Illuminate\Support\Facades\Route;

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
    Route::resource('kategori', App\Http\Controllers\KategoriController::class);
    Route::resource('buku', App\Http\Controllers\BukuController::class);
    Route::resource('barang', App\Http\Controllers\BarangController::class);
    Route::get('inputbarang', [App\Http\Controllers\InputBarangController::class, 'index'])->name('inputbarang.index');
    Route::get('inputbarang/selectkota', [App\Http\Controllers\InputBarangController::class, 'selectKota'])->name('inputbarang.selectkota');
    Route::get('inputbarang/html', [App\Http\Controllers\InputBarangController::class, 'html'])->name('inputbarang.html');
    Route::get('inputbarang/datatables', [App\Http\Controllers\InputBarangController::class, 'datatables'])->name('inputbarang.datatables');
    Route::get('inputbarang/html/crud', [App\Http\Controllers\InputBarangController::class, 'htmlCrud'])->name('inputbarang.html.crud');
    Route::get('inputbarang/datatables/crud', [App\Http\Controllers\InputBarangController::class, 'datatablesCrud'])->name('inputbarang.datatables.crud');
    Route::post('inputbarang', [App\Http\Controllers\InputBarangController::class, 'store'])->name('inputbarang.store');
    Route::put('inputbarang', [App\Http\Controllers\InputBarangController::class, 'update'])->name('inputbarang.update');
    Route::delete('inputbarang', [App\Http\Controllers\InputBarangController::class, 'destroy'])->name('inputbarang.destroy');
    Route::post('barang/print-labels', [App\Http\Controllers\BarangController::class, 'printLabels'])->name('barang.print-labels');
    Route::get('/cetak-sertifikat', [App\Http\Controllers\PdfController::class, 'cetakSertifikat'])->name('cetak.sertifikat');
    Route::get('/cetak-laporan', [App\Http\Controllers\PdfController::class, 'cetakLaporan'])->name('cetak.laporan');
});

Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback']);

// OTP request / send / verify
Route::get('otp', [App\Http\Controllers\Auth\OtpController::class, 'showRequestForm'])->name('otp.request');
Route::post('otp/send', [App\Http\Controllers\Auth\OtpController::class, 'send'])->name('otp.send');
Route::get('otp/verify', [App\Http\Controllers\Auth\OtpController::class, 'showVerifyForm'])->name('otp.verify.form');
Route::post('otp/verify', [App\Http\Controllers\Auth\OtpController::class, 'verify'])->name('otp.verify');

// PDF routes: index page (view) and actions handled by existing PdfController
Route::middleware(['auth'])->group(function () {
    Route::get('pdf', function () { return view('pdf.index'); })->name('pdf.index');
    Route::get('pdf/sertifikat', [App\Http\Controllers\PdfController::class, 'cetakSertifikat'])->name('pdf.sertifikat');
    Route::get('pdf/laporan', [App\Http\Controllers\PdfController::class, 'cetakLaporan'])->name('pdf.laporan');
});
