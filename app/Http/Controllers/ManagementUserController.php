<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ManagementUserController extends Controller
{
    public function index()
    {
        $users = DB::table('pengguna')
            ->where('role', '!=', 'owner')
            ->orderBy('nama_admin')
            ->get();
            
        return view('management-user.index', compact('users'));
    }

    public function create()
    {
        return view('management-user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_admin' => 'required|string|max:100',
            'no_hp' => 'required|string|max:15',
            'email' => 'required|email|max:100|unique:pengguna,email',
            'username' => 'required|string|max:50|unique:pengguna,username',
            'role' => 'required|in:gudang,shopkeeper',
            'kata_sandi' => 'required|string|min:6',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Generate ID Admin
        $lastId = DB::table('pengguna')
            ->where('id_admin', 'like', 'ADM%')
            ->orderBy('id_admin', 'desc')
            ->first();
            
        if ($lastId) {
            $lastNumber = intval(substr($lastId->id_admin, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $id_admin = 'ADM' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);

        $foto_profil = null;
        if ($request->hasFile('foto_profil')) {
            $file = $request->file('foto_profil');
            $foto_profil = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile_photos', $foto_profil);
        }

        DB::table('pengguna')->insert([
            'id_admin' => $id_admin,
            'nama_admin' => $request->nama_admin,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'username' => $request->username,
            'role' => $request->role,
            'foto_profil' => $foto_profil,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'status' => 'aktif'
        ]);

        return redirect()->route('management-user.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function show($id)
    {
        $user = DB::table('pengguna')->where('id_admin', $id)->first();
        
        if (!$user) {
            return redirect()->route('management-user.index')
                ->with('error', 'User tidak ditemukan');
        }
        
        return view('management-user.show', compact('user'));
    }

    public function edit($id)
    {
        $user = DB::table('pengguna')->where('id_admin', $id)->first();
        
        if (!$user) {
            return redirect()->route('management-user.index')
                ->with('error', 'User tidak ditemukan');
        }
        
        return view('management-user.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = DB::table('pengguna')->where('id_admin', $id)->first();
        
        if (!$user) {
            return redirect()->route('management-user.index')
                ->with('error', 'User tidak ditemukan');
        }

        $request->validate([
            'nama_admin' => 'required|string|max:100',
            'no_hp' => 'required|string|max:15',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('pengguna', 'email')->ignore($id, 'id_admin')
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pengguna', 'username')->ignore($id, 'id_admin')
            ],
            'role' => 'required|in:gudang,shopkeeper',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $updateData = [
            'nama_admin' => $request->nama_admin,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'username' => $request->username,
            'role' => $request->role,
        ];

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil && Storage::exists('public/profile_photos/' . $user->foto_profil)) {
                Storage::delete('public/profile_photos/' . $user->foto_profil);
            }
            
            $file = $request->file('foto_profil');
            $foto_profil = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/profile_photos', $foto_profil);
            $updateData['foto_profil'] = $foto_profil;
        }

        DB::table('pengguna')
            ->where('id_admin', $id)
            ->update($updateData);

        return redirect()->route('management-user.index')
            ->with('success', 'User berhasil diupdate');
    }

    public function destroy($id)
    {
        $user = DB::table('pengguna')->where('id_admin', $id)->first();
        
        if (!$user) {
            return redirect()->route('management-user.index')
                ->with('error', 'User tidak ditemukan');
        }

        // Hapus foto profil jika ada
        if ($user->foto_profil && Storage::exists('public/profile_photos/' . $user->foto_profil)) {
            Storage::delete('public/profile_photos/' . $user->foto_profil);
        }

        DB::table('pengguna')->where('id_admin', $id)->delete();

        return redirect()->route('management-user.index')
            ->with('success', 'User berhasil dihapus');
    }

    public function toggleStatus($id)
    {
        $user = DB::table('pengguna')->where('id_admin', $id)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        
        DB::table('pengguna')
            ->where('id_admin', $id)
            ->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'Status user berhasil diubah',
            'status' => $newStatus
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        $user = DB::table('pengguna')->where('id_admin', $id)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        DB::table('pengguna')
            ->where('id_admin', $id)
            ->update(['kata_sandi' => Hash::make($request->new_password)]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset'
        ]);
    }
}