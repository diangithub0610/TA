<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\ResetPasswordMail;

class PasswordResetController extends Controller
{
    // Menampilkan form untuk meminta reset password
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    // Memproses permintaan reset password
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:pelanggan,email',
        ], [
            'email.exists' => 'Email tidak terdaftar dalam sistem kami.'
        ]);

        $Pelanggan = Pelanggan::where('email', $request->email)->first();
        
        // Generate token unik
        $token = Str::random(60);
        
        // Simpan token dan waktu kadaluarsa (24 jam dari sekarang)
        $Pelanggan->reset_password_token = $token;
        $Pelanggan->reset_password_expires_at = Carbon::now()->addHours(24);
        $Pelanggan->save();

        // Kirim email
        Mail::to($Pelanggan->email)->send(new ResetPasswordMail($Pelanggan, $token));

        return back()->with('status', 'Link reset password telah dikirim ke email Anda!');
    }

    // Menampilkan form untuk reset password
    public function showResetPasswordForm($token)
    {
        $Pelanggan = Pelanggan::where('reset_password_token', $token)
                    ->where('reset_password_expires_at', '>', Carbon::now())
                    ->first();

        if (!$Pelanggan) {
            return redirect()->route('password.request')
                    ->with('error', 'Token reset password tidak valid atau sudah kadaluarsa.');
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    // Memproses reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:pelanggan,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Cek apakah token valid dan belum kadaluarsa
        $Pelanggan = Pelanggan::where('email', $request->email)
                    ->where('reset_password_token', $request->token)
                    ->where('reset_password_expires_at', '>', Carbon::now())
                    ->first();

        if (!$Pelanggan) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau sudah kadaluarsa.']);
        }

        // Update password
        $Pelanggan->kata_sandi = Hash::make($request->password);
        $Pelanggan->reset_password_token = null;
        $Pelanggan->reset_password_expires_at = null;
        $Pelanggan->save();

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login dengan password baru.');
    }
}