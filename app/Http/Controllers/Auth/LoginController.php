<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // credentials OK — do not fully log the user in yet; send OTP
            $user = Auth::user();
            // generate 6-character alphanumeric OTP
            $otp = strtoupper(Str::random(6));
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            // send OTP by email (best-effort)
            try {
                Mail::raw("Kode OTP Anda: {$otp}", function ($m) use ($user) {
                    $m->to($user->email)->subject('Kode OTP untuk login');
                });
            } catch (\Exception $e) {
                // ignore mail failures for now
            }

            // logout the temporary authenticated session and redirect to OTP form
            Auth::logout();
            $request->session()->put('otp_request_email', $user->email);
            return redirect()->route('otp.verify.form')->with('info', 'Kode OTP telah dikirim ke email Anda.');
        }

        return back()->withErrors(['email' => 'Credensial tidak cocok'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
