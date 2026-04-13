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
    Route::prefix('modul5')->name('modul5.')->group(function () {
        Route::get('/', [App\Http\Controllers\Modul5Controller::class, 'index'])->name('index');
        Route::get('pilihan-wilayah', [App\Http\Controllers\Modul5Controller::class, 'pilihanWilayah'])->name('pilihanwilayah');
        Route::get('pilihan-wilayah/ajax', [App\Http\Controllers\Modul5Controller::class, 'pilihanWilayahAjax'])->name('pilihanwilayah.ajax');
        Route::get('pilihan-wilayah/axios', [App\Http\Controllers\Modul5Controller::class, 'pilihanWilayahAxios'])->name('pilihanwilayah.axios');
        Route::get('pos', [App\Http\Controllers\Modul5Controller::class, 'pos'])->name('pos');
        Route::get('pos/ajax', [App\Http\Controllers\Modul5Controller::class, 'posAjax'])->name('pos.ajax');
        Route::get('pos/axios', [App\Http\Controllers\Modul5Controller::class, 'posAxios'])->name('pos.axios');
        Route::get('pos/barang', [App\Http\Controllers\Modul5Controller::class, 'findBarangByKode'])->name('pos.barang');
        Route::post('pos/checkout', [App\Http\Controllers\Modul5Controller::class, 'checkoutPos'])->name('pos.checkout');
        Route::get('provinces', [App\Http\Controllers\Modul5Controller::class, 'provinces'])->name('provinces');
        Route::get('regencies', [App\Http\Controllers\Modul5Controller::class, 'regencies'])->name('regencies');
        Route::get('districts', [App\Http\Controllers\Modul5Controller::class, 'districts'])->name('districts');
        Route::get('villages', [App\Http\Controllers\Modul5Controller::class, 'villages'])->name('villages');
    });
    Route::post('barang/print-labels', [App\Http\Controllers\BarangController::class, 'printLabels'])->name('barang.print-labels');
    Route::get('/cetak-sertifikat', [App\Http\Controllers\PdfController::class, 'cetakSertifikat'])->name('cetak.sertifikat');
    Route::get('/cetak-laporan', [App\Http\Controllers\PdfController::class, 'cetakLaporan'])->name('cetak.laporan');

    Route::prefix('customer')->name('customer_data.')->group(function () {
        Route::get('/', [App\Http\Controllers\CustomerController::class, 'dataCustomer'])->name('index');
        Route::get('tambah-1', [App\Http\Controllers\CustomerController::class, 'createCustomerBlob'])->name('create_blob');
        Route::post('tambah-1', [App\Http\Controllers\CustomerController::class, 'storeCustomerBlob'])->name('store_blob');
        Route::get('tambah-2', [App\Http\Controllers\CustomerController::class, 'createCustomerPath'])->name('create_path');
        Route::post('tambah-2', [App\Http\Controllers\CustomerController::class, 'storeCustomerPath'])->name('store_path');
    });
});

Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback']);
Route::post('xendit/callback', [App\Http\Controllers\CustomerController::class, 'xenditCallback'])->name('xendit.callback.global');

Route::prefix('kantin/customer')->name('customer.')->group(function () {
    Route::get('/', [App\Http\Controllers\CustomerController::class, 'index'])->name('index');
    Route::get('vendors/{idvendor}/menus', [App\Http\Controllers\CustomerController::class, 'menusByVendor'])->name('menus');
    Route::post('checkout', [App\Http\Controllers\CustomerController::class, 'checkout'])->name('checkout');
    Route::post('xendit/callback', [App\Http\Controllers\CustomerController::class, 'xenditCallback'])->name('xendit.callback');
    Route::get('pembayaran-selesai/{idpesanan}', [App\Http\Controllers\CustomerController::class, 'finish'])->name('pembayaran_selesai');
});

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

    Route::prefix('kantin/vendor')->name('vendor.')->group(function () {
        Route::get('/', [App\Http\Controllers\VendorController::class, 'index'])->name('index');
        Route::post('menu', [App\Http\Controllers\VendorController::class, 'store'])->name('store');
        Route::get('menu/{idmenu}/edit', [App\Http\Controllers\VendorController::class, 'edit'])->name('edit');
        Route::put('menu/{idmenu}', [App\Http\Controllers\VendorController::class, 'update'])->name('update');
        Route::delete('menu/{idmenu}', [App\Http\Controllers\VendorController::class, 'destroy'])->name('destroy');
        Route::get('orders', [App\Http\Controllers\VendorController::class, 'orders'])->name('orders');
    });
});