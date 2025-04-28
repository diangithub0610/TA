<?php

namespace App\Http\Controllers;

use App\Models\Warna;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WarnaController extends Controller
{
    public function index()
    {
        $warnas = Warna::all();
        return view('admin.warna.index', compact('warnas'));
    }

    public function create()
    {
        return view('admin.warna.form');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'warna' => 'required|max:30|unique:warna,warna',
            'kode_hex' => [
                'required',
                'size:6',
                'regex:/^[0-9A-Fa-f]+$/',
                'unique:warna,kode_hex'
            ]
        ]);

        // Generate kode warna otomatis
        $kode_warna = Warna::generateKodeWarna($validatedData['warna']);

        // Validasi ulang kode warna yang di-generate
        $validasi_kode = validator([
            'kode_warna' => $kode_warna
        ], [
            'kode_warna' => [
                'required',
                'max:3',
                'unique:warna,kode_warna',
                'regex:/^[A-Z]+$/'
            ]
        ]);

        // Jika kode sudah ada, tambahkan angka
        $counter = 1;
        while ($validasi_kode->fails()) {
            $kode_warna = $kode_warna . $counter;

            $validasi_kode = validator([
                'kode_warna' => $kode_warna
            ], [
                'kode_warna' => [
                    'required',
                    'max:3',
                    'unique:warna,kode_warna',
                    'regex:/^[A-Z]+$/'
                ]
            ]);

            $counter++;
        }

        // Hapus # jika ada dari kode hex
        $validatedData['kode_hex'] = ltrim($validatedData['kode_hex'], '#');

        // Tambahkan kode warna ke data yang akan disimpan
        $validatedData['kode_warna'] = $kode_warna;

        Warna::create($validatedData);

        return redirect()->route('warna.index')
            ->with('success', 'Warna berhasil ditambahkan');
    }

    public function edit($kode_warna)
    {
        $warna = Warna::findOrFail($kode_warna);
        return view('admin.warna.form', compact('warna'));
    }

    public function update(Request $request, $kode_warna)
    {
        $warna = Warna::findOrFail($kode_warna);

        $validatedData = $request->validate([
            'warna' => [
                'required',
                'max:30',
                Rule::unique('warna')->ignore($warna->kode_warna, 'kode_warna')
            ],
            'kode_hex' => [
                'required',
                'size:6',
                'regex:/^[0-9A-Fa-f]+$/',
                Rule::unique('warna')->ignore($warna->kode_warna, 'kode_warna')
            ]
        ], [
            'warna.unique' => 'Nama warna sudah digunakan.',
            'kode_hex.unique' => 'Kode hex warna sudah digunakan.',
            'kode_hex.size' => 'Kode hex harus terdiri dari 6 karakter.',
            'kode_hex.regex' => 'Kode hex hanya boleh berisi huruf A-F dan angka 0-9.'
        ]);

        // Hapus # jika ada
        $validatedData['kode_hex'] = ltrim($validatedData['kode_hex'], '#');

        $warna->update($validatedData);

        return redirect()->route('warna.index')
            ->with('success', 'Warna berhasil diperbarui');
    }

    public function destroy($kode_warna)
    {
        try {
            $warna = Warna::findOrFail($kode_warna);
            $warna->delete();

            return redirect()->route('warna.index')
                ->with('success', 'Warna berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('warna.index')
                ->with('error', 'Gagal menghapus warna');
        }
    }
}
