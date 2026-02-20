<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class GoogleController extends Controller
{
    // Redirect the user to Google's OAuth page
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle callback from Google
    public function handleGoogleCallback()
    {
    // 1. TAMBAHKAN stateless() agar tidak mental di localhost
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();
    } catch (\Exception $e) {
        // Jika masih error, kita ingin tahu errornya apa, jangan cuma ditebak
        return redirect()->route('login')->with('error', 'Gagal: ' . $e->getMessage());
    }

    $user = User::where('email', $googleUser->email)->first();

    if (! $user) {
        $user = User::create([
            'name' => $googleUser->name ?? 'Google User',
            'email' => $googleUser->email,
            'password' => Hash::make(Str::random(24)),
            'id_google' => $googleUser->id,
            'role' => 'user',
        ]);
    } else {
        $user->update(['id_google' => $googleUser->id]);
    }

    // Generate OTP and store in session (avoid writing to DB so no migration needed)
    $otpCode = rand(100000, 999999);
    $email = $user->email;
    session([
        "otp_{$email}_code" => (string) $otpCode,
        "otp_{$email}_expires_at" => now()->addMinutes(10),
        'otp_request_email' => $email,
    ]);

    // Kirim email (Opsional, pastikan .env mail sudah benar)
    try {
        Mail::raw("Kode OTP Anda: {$otpCode}", function ($m) use ($user) {
            $m->to($user->email)->subject('Kode OTP Verifikasi');
        });
    } catch (\Exception $e) { }

    return redirect()->route('otp.verify.form')->with('info', 'Kode OTP telah dikirim.');
    }
}
