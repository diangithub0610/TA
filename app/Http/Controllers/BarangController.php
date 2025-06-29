<?php
namespace App\Http\Controllers;
use App\Models\Barang;
use App\Models\DetailBarang;
use App\Models\Gambar;
use App\Models\Tipe;
use App\Models\Warna;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
        $barang = Barang::findOrFail($kode_barang);
    
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
            // Update gambar utama jika ada
            if ($request->hasFile('gambar_utama')) {
                // Hapus gambar lama
                if ($barang->gambar) {
                    Storage::delete('public/barang/' . $barang->gambar);
                }
                $barang->gambar = $request->file('gambar_utama')->store('barang_images', 'public');
            }
    
            // Update barang
            $barang->update([
                'nama_barang' => $request->nama_barang,
                'berat' => $request->berat,
                'deskripsi' => $request->deskripsi,
                'kode_tipe' => $request->kode_tipe,
                'gambar' => $barang->gambar,
            ]);
    
            // PERBAIKAN: Cek detail yang bisa dihapus vs yang tidak bisa
            $existingDetails = DetailBarang::where('kode_barang', $kode_barang)->get();
            $cannotDeleteDetails = [];
            $deletableDetails = [];
    
            foreach ($existingDetails as $detail) {
                // Cek apakah detail ini digunakan di tabel lain (pemusnahan_barang, dll)
                $isUsedInPemusnahan = DB::table('pemusnahan_barang')
                    ->where('kode_detail', $detail->kode_detail)
                    ->exists();
                
                // Tambahkan pengecekan tabel lain yang mungkin menggunakan kode_detail
                $isUsedInTransaksi = DB::table('detail_transaksi')
                    ->where('kode_detail', $detail->kode_detail)
                    ->exists();
    
                if ($isUsedInPemusnahan || $isUsedInTransaksi) {
                    $cannotDeleteDetails[] = $detail;
                } else {
                    $deletableDetails[] = $detail;
                }
            }
    
            // Hapus hanya detail yang bisa dihapus
            foreach ($deletableDetails as $detail) {
                $detail->delete();
            }
    
            // Hitung counter berdasarkan detail yang tidak bisa dihapus
            $warnaCounters = [];
            foreach ($cannotDeleteDetails as $detail) {
                $warna = $detail->kode_warna;
                if (!isset($warnaCounters[$warna])) {
                    $warnaCounters[$warna] = 0;
                }
                $warnaCounters[$warna]++;
            }
    
            // Simpan detail barang baru
            foreach ($request->detail_barang as $detail) {
                $kode_warna = $detail['kode_warna'];
                
                // Increment counter untuk warna ini
                if (!isset($warnaCounters[$kode_warna])) {
                    $warnaCounters[$kode_warna] = 0;
                }
                $warnaCounters[$kode_warna]++;
    
                // Generate kode detail dengan counter yang sudah di-increment
                $kode_detail = DetailBarang::generateKodeDetail(
                    $kode_barang,
                    $kode_warna,
                    $warnaCounters[$kode_warna]
                );
    
                // Safety check: pastikan kode_detail unique
                $attempt = 0;
                $originalKode = $kode_detail;
                while (DetailBarang::where('kode_detail', $kode_detail)->exists() && $attempt < 50) {
                    $attempt++;
                    $kode_detail = DetailBarang::generateKodeDetail(
                        $kode_barang,
                        $kode_warna,
                        $warnaCounters[$kode_warna] + $attempt
                    );
                }
    
                if ($attempt >= 50) {
                    throw new \Exception("Tidak dapat generate kode_detail yang unique untuk {$kode_barang}-{$kode_warna}");
                }
    
                DetailBarang::create([
                    'kode_detail' => $kode_detail,
                    'kode_barang' => $kode_barang,
                    'stok' => 0,
                    'ukuran' => $detail['ukuran'],
                    'kode_warna' => $kode_warna,
                    // 'harga_beli' => 0,
                    'harga_normal' => $detail['harga_normal'] ?? 0,
                    'stok_minimum' => $detail['stok_minimum'] ?? 0,
                    'potongan_harga' => $detail['potongan_harga'] ?? 0,
                ]);
            }
    
            // Upload gambar pendukung baru jika ada
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
    
            $message = 'Data barang berhasil diperbarui';
            if (!empty($cannotDeleteDetails)) {
                $message .= '. Beberapa detail lama tidak dapat dihapus karena sedang digunakan dalam transaksi lain.';
            }
    
            return redirect()->route('barang.index')->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollback();
            
            // Debug logging
            \Log::error('Error updating barang: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->back()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage())
                ->withInput();
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
