<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    /**
     * Menampilkan form permintaan OTP (input email manual).
     */
    public function showRequestForm()
    {
        return view('auth.otp_request');
    }

    /**
     * Mengirim OTP ke email (untuk jalur manual).
     */
    public function send(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->email;

        $code = random_int(100000, 999999);

        // Simpan data OTP di session berdasarkan email
        session([
            "otp_{$email}_code" => (string) $code,
            "otp_{$email}_expires_at" => now()->addMinutes(5),
            'otp_request_email' => $email,
        ]);

        try {
            Mail::raw("Kode OTP Anda: {$code}", function ($m) use ($email) {
                $m->to($email)->subject('Kode OTP Anda');
            });
        } catch (\Exception $e) {
            // Error pengiriman email diabaikan untuk testing localhost
        }

        return redirect()->route('otp.verify.form')->with('info', 'Kode OTP telah dikirim ke email.');
    }

    /**
     * Menampilkan halaman input kode OTP.
     */
    public function showVerifyForm(Request $request)
    {
        // 1. Cek Jalur Google (Berdasarkan otp_user_id di session)
        $userId = session('otp_user_id');
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                session()->forget('otp_user_id');
                return redirect()->route('login')->withErrors(['otp' => 'Pengguna tidak ditemukan.']);
            }
            // Tampilkan form (otp_code diambil dari database HeidiSQL)
            return view('auth.otp', ['email' => $user->email]);
        }

        // 2. Jalur Cadangan (Berdasarkan request email manual)
        $email = session('otp_request_email');
        if (!$email) {
            return redirect()->route('otp.request');
        }
        return view('auth.otp', ['email' => $email]);
    }

    /**
     * Verifikasi kode OTP dan proses Login.
     */
    public function verify(Request $request)
    {
        $request->validate(['otp' => 'required|string']);

        // --- PROSES JALUR GOOGLE (DATABASE) ---
        $userId = session('otp_user_id');
        if ($userId) {
            $user = User::find($userId);
            
            if (!$user || !$user->otp_code || now()->gt($user->otp_expires_at)) {
                $user->update(['otp_code' => null, 'otp_expires_at' => null]);
                session()->forget('otp_user_id');
                return redirect()->route('login')->withErrors(['otp' => 'OTP kadaluarsa. Silakan login ulang.']);
            }

            // Bandingkan input dengan otp_code di database HeidiSQL
            if (hash_equals((string) $user->otp_code, (string) $request->otp)) {
                $user->update(['otp_code' => null, 'otp_expires_at' => null]);
                session()->forget('otp_user_id');
                Auth::login($user); // Login resmi ke Koleksi Buku
                return redirect()->route('dashboard')->with('success', 'Berhasil login melalui Google.');
            }
        }

        // --- PROSES JALUR MANUAL (SESSION) ---
        $email = $request->email ?? session('otp_request_email');
        if ($email) {
            $stored = session("otp_{$email}_code");
            $expires = session("otp_{$email}_expires_at");

            if (!$stored || now()->gt($expires)) {
                session()->forget(["otp_{$email}_code", "otp_{$email}_expires_at", 'otp_request_email']);
                return redirect()->route('otp.request')->withErrors(['otp' => 'OTP kadaluarsa.']);
            }

            if (hash_equals((string) $stored, (string) $request->otp)) {
                $user = User::where('email', $email)->first();
                
                // Jika user belum ada (register otomatis)
                if (!$user) {
                    $user = User::create([
                        'name' => explode('@', $email)[0],
                        'email' => $email,
                        'password' => Hash::make(Str::random(24)),
                        'role' => 'user',
                    ]);
                }

                Auth::login($user);
                session()->forget(["otp_{$email}_code", "otp_{$email}_expires_at", 'otp_request_email']);
                return redirect()->route('dashboard')->with('success', 'Berhasil login.');
            }
        }

        return back()->withErrors(['otp' => 'Kode OTP tidak cocok.'])->withInput();
    }
}