<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipeController extends Controller
{
    public function index()
    {
        $tipes = Tipe::with('brand')->get();
        return view('admin.tipe.index', compact('tipes'));
    }

    public function create()
    {
        $brands = Brand::all();
        return view('admin.tipe.form', compact('brands'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_tipe' => 'required|max:50',
            'kode_brand' => 'required|exists:brand,kode_brand',
            'potongan_harga' => 'nullable|numeric|min:0|max:100000000'
        ]);

        // Generate kode tipe otomatis
        $kode_tipe = Tipe::generateKodeTipe($validatedData['nama_tipe'], $validatedData['kode_brand']);

        // Validasi ulang kode tipe yang di-generate
        $validasi_kode = validator([
            'kode_tipe' => $kode_tipe
        ], [
            'kode_tipe' => [
                'required',
                'max:10',
                'unique:tipe,kode_tipe',
                'regex:/^[A-Z0-9-]+$/'
            ]
        ]);

        // Jika kode sudah ada, tambahkan angka
        $counter = 1;
        while ($validasi_kode->fails()) {
            $kode_tipe = $kode_tipe . $counter;

            $validasi_kode = validator([
                'kode_tipe' => $kode_tipe
            ], [
                'kode_tipe' => [
                    'required',
                    'max:10',
                    'unique:tipe,kode_tipe',
                    'regex:/^[A-Z0-9-]+$/'
                ]
            ]);

            $counter++;
        }

        // Tambahkan kode tipe ke data yang akan disimpan
        $validatedData['kode_tipe'] = $kode_tipe;

        Tipe::create($validatedData);

        return redirect()->route('tipe.index')
            ->with('success', 'Tipe berhasil ditambahkan');
    }

    public function edit($kode_tipe)
    {
        $tipe = Tipe::findOrFail($kode_tipe);
        $brands = Brand::all();
        return view('admin.tipe.form', compact('tipe', 'brands'));
    }

    public function update(Request $request, $kode_tipe)
    {
        $tipe = Tipe::findOrFail($kode_tipe);

        $validatedData = $request->validate([
            'nama_tipe' => 'required|max:50',
            'kode_brand' => 'required|exists:brand,kode_brand',
            'potongan_harga' => 'nullable|numeric|min:0|max:100000000'
        ], [
            'kode_brand.exists' => 'Brand tidak valid.',
            'potongan_harga.max' => 'Potongan harga terlalu besar.'
        ]);

        $tipe->update($validatedData);

        return redirect()->route('tipe.index')
            ->with('success', 'Tipe berhasil diperbarui');
    }

    public function destroy($kode_tipe)
    {
        try {
            $tipe = Tipe::findOrFail($kode_tipe);
            $tipe->delete();

            return redirect()->route('tipe.index')
                ->with('success', 'Tipe berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('tipe.index')
                ->with('error', 'Gagal menghapus tipe');
        }
    }
}
