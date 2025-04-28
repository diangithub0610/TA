<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Tipe;
use App\Models\Warna;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::with('tipe')->get();
        return view('admin.barang.index', compact('barangs'));
    }

    public function create()
    {
        $tipes = Tipe::all();
        $warnas = Warna::all();
        $ukurans = [
            '40.0' => '40',
            '41.5' => '41.5',
            '42.0' => '42',
            '43.0' => '43'
        ];
        return view('admin.barang.form', compact('tipes', 'warnas', 'ukurans'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_barang' => 'required|max:100',
            'berat' => 'required|integer|min:1',
            'harga_normal' => 'required|integer|min:1000',
            'deskripsi' => 'nullable',
            'gambar' => 'nullable|image|max:2048',
            'kode_tipe' => 'required|exists:tipe,kode_tipe',
        ]);

        // Generate kode barang
        $kode_barang = Barang::generateKodeBarang($validatedData['kode_tipe']);

        // Upload gambar jika ada
        $gambar_path = null;
        if ($request->hasFile('gambar')) {
            $gambar_path = $request->file('gambar')->store('barang_images', 'public');
        }

        // Simpan barang
        $barang = Barang::create([
            'kode_barang' => $kode_barang,
            'nama_barang' => $validatedData['nama_barang'],
            'berat' => $validatedData['berat'],
            'harga_normal' => $validatedData['harga_normal'],
            'deskripsi' => $validatedData['deskripsi'],
            'gambar' => $gambar_path,
            'kode_tipe' => $validatedData['kode_tipe']
        ]);

        if ($request->has('detail_warnas')) {
            $detailsToCreate = [];
            $kodeWarnaCounter = []; // untuk tracking urutan per warna

            foreach ($request->input('detail_warnas') as $detail) {
                $warna = $detail['kode_warna'];
                $key = "{$kode_barang}-{$warna}";

                // Ambil count dari database + yang sedang dikumpulkan
                if (!isset($kodeWarnaCounter[$key])) {
                    $existingCount = DetailBarang::where('kode_barang', $kode_barang)
                        ->where('kode_warna', $warna)
                        ->count();
                    $kodeWarnaCounter[$key] = $existingCount;
                }

                $kodeWarnaCounter[$key]++; // next urutan

                $kode_detail = DetailBarang::generateKodeDetail($kode_barang, $warna, $kodeWarnaCounter[$key]);

                $detailsToCreate[] = [
                    'kode_detail' => $kode_detail,
                    'kode_barang' => $kode_barang,
                    'kode_warna' => $warna,
                    'ukuran' => $detail['ukuran'],
                    'stok' => $detail['stok']
                ];
            }

            if (!empty($detailsToCreate)) {
                DetailBarang::insert($detailsToCreate);
            }
        }


        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan');
    }

    // Metode update serupa
    public function update(Request $request, $kode_barang)
    {
        // Validasi data utama
        $validatedData = $request->validate([
            'nama_barang' => 'required|max:100',
            'kode_tipe' => 'required|exists:Tipe,kode_tipe',
            'berat' => 'required|integer|min:1',
            'harga_normal' => 'required|integer|min:1000',
            'deskripsi' => 'nullable|string',
            'gambar' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'detail_warnas' => 'array',
            'detail_warnas.*.kode_warna' => 'nullable|exists:Warna,kode_warna',
            'detail_warnas.*.ukuran' => 'nullable|numeric',
            'detail_warnas.*.stok' => 'nullable|integer|min:0'
        ]);

        // Cari barang
        $barang = Barang::findOrFail($kode_barang);

        // Handle gambar
        if ($request->hasFile('gambar')) {
            if ($barang->gambar) {
                Storage::disk('public')->delete($barang->gambar);
            }

            $gambarPath = $request->file('gambar')->store('barang_images', 'public');
            $validatedData['gambar'] = $gambarPath;
        } else {
            $validatedData['gambar'] = $barang->gambar;
        }

        // Update data barang
        $barang->update($validatedData);

        // Hapus semua detail lama
        DetailBarang::where('kode_barang', $kode_barang)->delete();

        // Tambahkan detail baru
        if (isset($validatedData['detail_warnas'])) {
            $detailsToInsert = [];
            $kodeWarnaCounter = [];

            foreach ($validatedData['detail_warnas'] as $detail) {
                if (
                    empty($detail['kode_warna']) &&
                    empty($detail['ukuran']) &&
                    empty($detail['stok'])
                ) {
                    continue;
                }

                $warna = $detail['kode_warna'];
                $key = "{$kode_barang}-{$warna}";

                if (!isset($kodeWarnaCounter[$key])) {
                    $kodeWarnaCounter[$key] = 0;
                }

                $kodeWarnaCounter[$key]++;

                $kode_detail = DetailBarang::generateKodeDetail($kode_barang, $warna, $kodeWarnaCounter[$key]);

                $detailsToInsert[] = [
                    'kode_detail' => $kode_detail,
                    'kode_barang' => $kode_barang,
                    'kode_warna' => $warna,
                    'ukuran' => $detail['ukuran'] ?? null,
                    'stok' => $detail['stok'] ?? 0
                ];
            }

            if (!empty($detailsToInsert)) {
                DetailBarang::insert($detailsToInsert);
            }
        }

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil diupdate');
    }

    public function show($kode_barang)
    {
        $barang = Barang::with(['tipe', 'detailBarangs.warna'])->findOrFail($kode_barang);
        return view('admin.barang.show', compact('barang'));
    }

    public function edit($kode_barang)
    {
        $barang = Barang::with('detailBarangs')->findOrFail($kode_barang);
        $tipes = Tipe::all();
        $warnas = Warna::all();
        $ukurans = [
            '40.0' => '40',
            '41.5' => '41.5',
            '42.0' => '42',
            '43.0' => '43'
        ];

        return view('admin.barang.form', compact('barang', 'tipes', 'warnas', 'ukurans'));
    }


    public function destroy($kode_barang)
    {
        try {
            $barang = Barang::findOrFail($kode_barang);

            // Hapus gambar jika ada
            if ($barang->gambar) {
                Storage::disk('public')->delete($barang->gambar);
            }

            // Hapus detail barang terlebih dahulu
            DetailBarang::where('kode_barang', $kode_barang)->delete();

            // Hapus barang
            $barang->delete();

            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('barang.index')
                ->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    private function storeImage($file, $kode_barang)
    {
        // Simpan dengan nama kode barang
        $extension = $file->getClientOriginalExtension();
        $filename = $kode_barang . '.' . $extension;

        return $file->storeAs('barang', $filename, 'public');
    }
}
