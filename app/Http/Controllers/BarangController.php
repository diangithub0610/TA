<?php
namespace App\Http\Controllers;
use App\Models\Tipe;
use App\Models\Brand;
use App\Models\Warna;
use App\Models\Barang;
use App\Models\Gambar;
use Illuminate\Support\Str;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::with('tipe','detailBarangs')->get();
        return view('admin.barang.index', compact('barangs'));
    }

    public function show($kode_barang)
    {
        $barang = Barang::with(['tipe', 'detailBarangs.warna'])->findOrFail($kode_barang);
        return view('admin.barang.show', compact('barang'));
    }

    public function create()
    {
        $tipes = Tipe::with('brand')->get();
        $warnas = Warna::all();

        return view('admin.barang.form', compact('tipes', 'warnas'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|max:100',
            'berat' => 'nullable|integer',
            'deskripsi' => 'nullable',
            'kode_tipe' => 'required|exists:tipe,kode_tipe',
            'gambar_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_pendukung.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'detail_barang' => 'required|array|min:1',
            'detail_barang.*.ukuran' => 'required|numeric|min:0',
            'detail_barang.*.kode_warna' => 'required|exists:warna,kode_warna',
            'detail_barang.*.harga_normal' => 'nullable|integer|min:0',
            'detail_barang.*.stok_minimum' => 'nullable|integer|min:0',
            'detail_barang.*.potongan_harga' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Upload gambar utama
            $gambar_utama = null;
            if ($request->hasFile('gambar_utama')) {
                // $file = $request->file('gambar_utama');
                // $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                // $file->storeAs('public/barang', $filename);
                // $gambar_utama = $filename;
                $gambar_utama = $request->file('gambar_utama')->store('barang_images', 'public');
            }

            // Fix: Generate kode_barang dengan parameter yang benar
            $kode_barang = Barang::generateKodeBarang($request->kode_tipe);

            // Simpan barang
            $barang = Barang::create([
                'kode_barang' => $kode_barang,
                'nama_barang' => $request->nama_barang,
                'berat' => $request->berat,
                'deskripsi' => $request->deskripsi,
                'gambar' => $gambar_utama,
                'kode_tipe' => $request->kode_tipe,
                'is_active' => 1,
            ]);

            // Simpan detail barang
            foreach ($request->detail_barang as $detail) {
                // Generate kode detail
                $existingCount = DetailBarang::where('kode_barang', $kode_barang)
                    ->where('kode_warna', $detail['kode_warna'])
                    ->count();

                $kode_detail = DetailBarang::generateKodeDetail(
                    $kode_barang,
                    $detail['kode_warna'],
                    $existingCount + 1
                );

                DetailBarang::create([
                    'kode_detail' => $kode_detail,
                    'kode_barang' => $kode_barang, // Fix: gunakan $kode_barang bukan $request->kode_barang
                    'stok' => 0, // Set default stok = 0
                    'ukuran' => $detail['ukuran'],
                    'kode_warna' => $detail['kode_warna'],
                    // 'harga_beli' => 0, // Set default harga_beli = 0
                    'harga_normal' => $detail['harga_normal'] ?? 0,
                    'stok_minimum' => $detail['stok_minimum'] ?? 0,
                    'potongan_harga' => $detail['potongan_harga'] ?? 0,
                ]);
            }

            // Upload gambar pendukung
            if ($request->hasFile('gambar_pendukung')) {
                foreach ($request->file('gambar_pendukung') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/barang', $filename);

                    Gambar::create([
                        'kode_barang' => $kode_barang, // Fix: gunakan $kode_barang
                        'gambar' => $filename,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('barang.index')
                ->with('success', 'Data barang berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function edit($kode_barang)
    {
        $barang = Barang::with(['detailBarang.warna', 'galeriGambar', 'tipe.brand'])
            ->where('kode_barang', $kode_barang)
            ->firstOrFail();

        $tipes = Tipe::with('brand')->get();
        $warnas = Warna::all();

        return view('admin.barang.form', compact('barang', 'tipes', 'warnas'));
    }


    public function update(Request $request, $kode_barang)
    {
        $request->validate([
            'nama_barang' => 'required|max:100',
            'berat' => 'nullable|integer',
            'deskripsi' => 'nullable',
            'kode_tipe' => 'required|exists:tipe,kode_tipe',
            'gambar_utama' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gambar_pendukung.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'detail_barang' => 'required|array|min:1',
            'detail_barang.*.ukuran' => 'required|numeric|min:0',
            'detail_barang.*.kode_warna' => 'required|exists:warna,kode_warna',
            'detail_barang.*.harga_normal' => 'nullable|integer|min:0',
            'detail_barang.*.stok_minimum' => 'nullable|integer|min:0',
            'detail_barang.*.potongan_harga' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Cari barang yang akan diupdate
            $barang = Barang::findOrFail($kode_barang);

            // Handle upload gambar utama
            $gambar_utama = $barang->gambar; // Gunakan gambar lama jika tidak ada yang baru
            if ($request->hasFile('gambar_utama')) {
                // Hapus gambar lama jika ada
                if ($barang->gambar && Storage::disk('public')->exists($barang->gambar)) {
                    Storage::disk('public')->delete($barang->gambar);
                }

                $gambar_utama = $request->file('gambar_utama')->store('barang_images', 'public');
            }

            // Update data barang
            $barang->update([
                'nama_barang' => $request->nama_barang,
                'berat' => $request->berat,
                'deskripsi' => $request->deskripsi,
                'gambar' => $gambar_utama,
                'kode_tipe' => $request->kode_tipe,
            ]);

            // Update detail barang
            // Ambil detail barang yang sudah ada
            $existingDetails = DetailBarang::where('kode_barang', $kode_barang)->get();
            $existingDetailIds = [];

            foreach ($request->detail_barang as $index => $detail) {
                // Cek apakah detail dengan ukuran dan warna yang sama sudah ada
                $existingDetail = $existingDetails->where('ukuran', $detail['ukuran'])
                    ->where('kode_warna', $detail['kode_warna'])
                    ->first();

                if ($existingDetail) {
                    // Update detail yang sudah ada (TIDAK mengupdate stok dan harga_beli)
                    $existingDetail->update([
                        'harga_normal' => $detail['harga_normal'] ?? 0,
                        'stok_minimum' => $detail['stok_minimum'] ?? 0,
                        'potongan_harga' => $detail['potongan_harga'] ?? 0,
                    ]);

                    $existingDetailIds[] = $existingDetail->kode_detail;
                } else {
                    // Buat detail baru
                    $existingCount = DetailBarang::where('kode_barang', $kode_barang)
                        ->where('kode_warna', $detail['kode_warna'])
                        ->count();

                    $kode_detail = DetailBarang::generateKodeDetail(
                        $kode_barang,
                        $detail['kode_warna'],
                        $existingCount + 1
                    );

                    $newDetail = DetailBarang::create([
                        'kode_detail' => $kode_detail,
                        'kode_barang' => $kode_barang,
                        'stok' => 0, // Default stok = 0 untuk detail baru
                        'ukuran' => $detail['ukuran'],
                        'kode_warna' => $detail['kode_warna'],
                        'harga_normal' => $detail['harga_normal'] ?? 0,
                        'stok_minimum' => $detail['stok_minimum'] ?? 0,
                        'potongan_harga' => $detail['potongan_harga'] ?? 0,
                    ]);

                    $existingDetailIds[] = $newDetail->kode_detail;
                }
            }

            // Hapus detail yang tidak ada dalam form (detail yang dihapus user)
            DetailBarang::where('kode_barang', $kode_barang)
                ->whereNotIn('kode_detail', $existingDetailIds)
                ->delete();

            // Handle upload gambar pendukung
            if ($request->hasFile('gambar_pendukung')) {
                foreach ($request->file('gambar_pendukung') as $file) {
                    $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->storeAs('public/barang', $filename);

                    Gambar::create([
                        'kode_barang' => $kode_barang,
                        'gambar' => $filename,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('barang.index')
                ->with('success', 'Data barang berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal mengupdate data: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Method tambahan untuk menghapus gambar pendukung (sudah ada di JavaScript)
    public function deleteGambar($kode_gambar)
    {
        try {
            $gambar = Gambar::findOrFail($kode_gambar);

            // Hapus file dari storage
            if (Storage::disk('public')->exists('barang/' . $gambar->gambar)) {
                Storage::disk('public')->delete('barang/' . $gambar->gambar);
            }

            // Hapus record dari database
            $gambar->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // public function destroy($kode_barang)
    // {
    //     $barang = Barang::findOrFail($kode_barang);

    //     // Soft delete
    //     // $barang->update(['is_active' => 0]);
    //     $barang->delete();

    //     return redirect()->route('barang.index')
    //         ->with('success', 'Data barang berhasil dihapus');
    // }

    public function destroy($kode_barang)
    {
        try {
            $barang = Barang::findOrFail($kode_barang);
    
            // Cek jika masih ada detail_barang yang berelasi dengan tabel lain (contoh: pemusnahan_barang)
            $relasiAktif = DetailBarang::where('kode_barang', $kode_barang)
                            ->whereHas('pemusnahan_barang') // relasi dari DetailBarang ke PemusnahanBarang
                            ->exists();
    
            if ($relasiAktif) {
                return redirect()->route('barang.index')
                    ->with('error', 'Barang tidak bisa dihapus karena sedang digunakan dalam data pemusnahan.');
            }
    
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
    
        } catch (\Throwable $e) {
            return redirect()->route('barang.index')
                ->with('error', 'Gagal menghapus barang. Data sedang digunakan.');
        }
    }
    

    public function deleteGambarPendukung($kode_gambar)
    {
        $gambar = Gambar::findOrFail($kode_gambar);

        // Hapus file gambar
        Storage::delete('public/barang/' . $gambar->gambar);

        // Hapus record dari database
        $gambar->delete();

        return response()->json(['success' => true]);
    }
    public function byBrand($brandId)
    {
        // Redirect ke route yang benar
        return redirect()->route('pelanggan.barang', ['brand' => $brandId]);
    }
}
