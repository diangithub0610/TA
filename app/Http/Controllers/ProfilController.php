<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:pelanggan');
    }

    public function index()
    {
        $pelanggan = auth()->guard('pelanggan')->user();
        return view('web.profil.index', compact('pelanggan'));
    }

    public function update(Request $request)
    {
        $pelanggan = auth()->guard('pelanggan')->user();

        $request->validate([
            'nama_pelanggan' => 'required|string|max:50',
            'no_hp' => 'required|string|max:15',
            'email' => 'required|email|max:100|unique:pelanggan,email,' . $pelanggan->id_pelanggan . ',id_pelanggan',
            'username' => 'required|string|max:25|unique:pelanggan,username,' . $pelanggan->id_pelanggan . ',id_pelanggan',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        // Update data profil
        $dataPelanggan = [
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'username' => $request->username
        ];

        // Upload foto profil jika ada
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($pelanggan->foto_profil) {
                Storage::disk('public')->delete($pelanggan->foto_profil);
            }

            $path = $request->file('foto_profil')->store('pelanggan', 'public');
            $dataPelanggan['foto_profil'] = $path;
        }

        $pelanggan->update($dataPelanggan);

        return redirect()->route('profil.index')->with('success', 'Profil berhasil diperbarui');
    }

    public function password()
    {
        return view('web.profil.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'kata_sandi_lama' => 'required',
            'kata_sandi' => 'required|string|min:8|confirmed'
        ]);

        $pelanggan = auth()->guard('pelanggan')->user();

        // Verifikasi kata sandi lama
        if (!Hash::check($request->kata_sandi_lama, $pelanggan->kata_sandi)) {
            return redirect()->back()->with('error', 'Kata sandi lama tidak sesuai');
        }

        // Update kata sandi
        $pelanggan->update([
            'kata_sandi' => Hash::make($request->kata_sandi)
        ]);

        return redirect()->route('profil.password')->with('success', 'Kata sandi berhasil diperbarui');
    }
}
