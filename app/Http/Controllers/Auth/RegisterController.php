<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        // generate OTP, store on user, send email, then redirect to verify form
        $otp = strtoupper(Str::random(6));
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(10);
        $user->save();

        try {
            Mail::raw("Kode OTP Anda: {$otp}", function ($m) use ($user) {
                $m->to($user->email)->subject('Kode OTP untuk verifikasi');
            });
        } catch (\Exception $e) {
            // ignore
        }

        // ensure not logged in yet; set session to track OTP request
        Auth::logout();
        $request->session()->put('otp_request_email', $user->email);
        return redirect()->route('otp.verify.form')->with('info', 'Akun dibuat. Kode OTP telah dikirim ke email Anda.');
    }
}

