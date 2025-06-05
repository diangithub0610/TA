<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:pelanggan');
    }

    public function index()
    {
        $pelanggan = auth()->guard('pelanggan')->user();
        return view('profil.index', compact('pelanggan'));
    }

    public function alamat()
    {
        $pelanggan = auth()->guard('pelanggan')->user();
        $alamat = $pelanggan->alamat;
        $alamatUtama = $pelanggan->alamat->where('is_utama', 1)->first();
        return view('profil.alamat', compact('pelanggan', 'alamat', 'alamatUtama'));
    }

    public function update(Request $request)
    {
        $user = auth()->guard('pelanggan')->user();

        $request->validate([
            'nama_pelanggan' => 'required|string|max:50',
            'email' => 'required|email|max:100',
            'no_hp' => 'nullable|string|max:15',
        ]);

        $user->update([
            'nama_pelanggan' => $request->nama_pelanggan,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('profil.index')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateFoto(Request $request)
    {
        $user = auth()->guard('pelanggan')->user();

        $request->validate([
            'foto_profil' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Hapus foto lama jika ada
        if ($user->foto_profil && Storage::exists('public/profil/' . $user->foto_profil)) {
            Storage::delete('public/profil/' . $user->foto_profil);
        }

        // Simpan foto baru
        $foto = $request->file('foto_profil');
        $namaFile = $user->username . '.' . $foto->getClientOriginalExtension();
        $foto->storeAs('public/profil', $namaFile);

        $user->update(['foto_profil' => $namaFile]);

        return redirect()->route('profil.index')->with('success', 'Foto profil berhasil diperbarui.');
    }

    /**
     * Tampilkan form ubah password
     */
    public function showChangePasswordForm()
    {
        $pelanggan = auth()->guard('pelanggan')->user();
        return view('profil.ubah-password',compact('pelanggan'));
    }

   
public function changePassword(Request $request)
{
    // Validasi input
    $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|confirmed|min:6',
    ], [
        'current_password.required' => 'Password lama harus diisi.',
        'new_password.required' => 'Password baru harus diisi.',
        'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        'new_password.min' => 'Password baru minimal 6 karakter.',
    ]);

    // Ambil data user
    $user = auth()->guard('pelanggan')->user();

    // Cek apakah password lama cocok dengan hash Bcrypt
    if (!Hash::check($request->current_password, $user->password)) {
        return redirect()->back()
            ->with('error', 'Password lama tidak sesuai.')
            ->withInput();
    }

    // Update password dengan hash Bcrypt
    $user->password = Hash::make($request->new_password);
    $user->save();

    return redirect()->back()->with('success', 'Password berhasil diubah!');
}

}