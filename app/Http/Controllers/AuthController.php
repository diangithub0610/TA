<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Pelanggan;
use App\Helpers\GenerateId;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // can be email or username
            'password' => 'required|string',
            'remember' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        // Check login in both Pengguna and Pelanggan tables
        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->input('remember', false);

        // Try login for Admin/Pengguna
        $admin = Pengguna::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login);
        })->first();

        // Try login for Pelanggan
        $pelanggan = Pelanggan::where(function ($query) use ($login) {
            $query->where('email', $login)
                ->orWhere('username', $login);
        })->first();

        // Verify credentials
        if ($admin && Hash::check($password, $admin->kata_sandi)) {
            // Login admin
            Auth::guard('admin')->login($admin, $remember);

            // Redirect based on role
            // switch ($admin->role) {
            //     case 'gudang':
            //         return redirect()->route('gudang.dashboard');
            //     case 'pemesanan':
            //         return redirect()->route('pemesanan.dashboard');
            //     case 'owner':
            //         return redirect()->route('dashboard');
            //     default:
            //         return redirect()->route('dashboard');

            return redirect()->route('dashboard');
            
        } elseif ($pelanggan && Hash::check($password, $pelanggan->kata_sandi)) {
            // Login pelanggan
            Auth::guard('pelanggan')->login($pelanggan, $remember);

            // Redirect based on role
            switch ($pelanggan->role) {
                case 'reseller':
                    return redirect()->route('pelanggan.beranda');
                default:
                    return redirect()->route('pelanggan.beranda');
            }
        }

        // Login failed
        return redirect()->back()
            ->withErrors(['login' => 'Email/Username atau kata sandi salah'])
            ->withInput($request->except('password'));
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // Logout from both guards
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
        }

        if (Auth::guard('pelanggan')->check()) {
            Auth::guard('pelanggan')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Forgot Password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // Check in both Pengguna and Pelanggan
        $admin = Pengguna::where('email', $request->email)->first();
        $pelanggan = Pelanggan::where('email', $request->email)->first();

        if ($admin || $pelanggan) {
            // Generate reset token
            $token = Str::random(60);

            // Store reset token (you'd typically use a password_resets table)
            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now()
            ]);

            // Send reset password email
            // You would implement email sending logic here
            // Use a service like Laravel's Mail facade

            return redirect()->back()->with('status', 'Link reset kata sandi telah dikirim');
        }

        return redirect()->back()->withErrors(['email' => 'Email tidak ditemukan']);
    }

    public function register()
    {
        if (Auth::guard('pelanggan')->check()) {
            return redirect()->route('beranda');
        }

        return view('web.auth.register');
    }

    public function doRegister(Request $request)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:50',
            'username' => 'required|string|max:25|unique:pelanggan,username',
            'email' => 'required|string|email|max:100|unique:pelanggan,email',
            'no_hp' => 'required|string|max:15',
            'kata_sandi' => 'required|string|min:8|confirmed'
        ]);

        $pelanggan = Pelanggan::create([
            'id_pelanggan' => GenerateId::pelanggan(),
            'nama_pelanggan' => $request->nama_pelanggan,
            'username' => $request->username,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'role' => 'pelanggan'
        ]);

        Auth::guard('pelanggan')->login($pelanggan);

        return redirect()->route('beranda')
            ->with('success', 'Registrasi berhasil! Selamat datang di toko kami.');
    }
}
