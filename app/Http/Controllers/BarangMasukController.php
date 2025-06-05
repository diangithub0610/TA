<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Barang;
use App\Models\Pengguna;
use App\Models\BarangMasuk;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use App\Models\DetailBarangMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BarangMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $barangMasuk = BarangMasuk::with(['admin', 'detailBarangMasuk'])
                ->orderBy('tanggal_masuk', 'desc')
                ->get();

            return view('admin.barang-masuk.index', compact('barangMasuk'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data: ' . $e->getMessage());
        }
    }


    // Tambahkan method untuk mendapatkan barang berdasarkan brand
    public function getBarangByBrand(Request $request)
    {
        $kodeBrand = $request->kode_brand;

        // Ambil barang berdasarkan brand melalui relasi tipe
        $barangs = DB::table('barang')
            ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
            ->where('tipe.kode_brand', $kodeBrand)
            ->where('barang.is_active', 1)
            ->select('barang.kode_barang', 'barang.nama_barang')
            ->get();

        return response()->json($barangs);
    }

    /**
     * Store a newly created resource in storage.
     */
    // Update method create untuk include brand
    public function create()
    {
        $brands = DB::table('brand')->get();
        $warnas = DB::table('warna')->get();
        $barangs = collect(); // Kosong dulu, akan diisi via AJAX

        return view('admin.barang-masuk.form', compact('brands', 'barangs', 'warnas'));
    }

    // Update method edit untuk include brand
    public function edit($kode_pembelian)
    {
        $barangMasuk = BarangMasuk::with('detailBarangMasuk')->findOrFail($kode_pembelian);
        $brands = DB::table('brand')->get();
        $warnas = DB::table('warna')->get();

        // Ambil barang berdasarkan brand yang sudah dipilih (dari barang pertama di detail)
        $barangs = collect();
        $selectedBrand = null;

        if ($barangMasuk->detailBarangMasuk->count() > 0) {
            $firstBarang = $barangMasuk->detailBarangMasuk->first();
            $selectedBrand = DB::table('barang')
                ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
                ->where('barang.kode_barang', $firstBarang->kode_barang)
                ->value('tipe.kode_brand');

            if ($selectedBrand) {
                $barangs = DB::table('barang')
                    ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
                    ->where('tipe.kode_brand', $selectedBrand)
                    ->where('barang.is_active', 1)
                    ->select('barang.kode_barang', 'barang.nama_barang')
                    ->get();
            }
        }

        return view('admin.barang-masuk.edit', compact('barangMasuk', 'brands', 'barangs', 'warnas', 'selectedBrand'));
    }
    public function update(Request $request, $kode_pembelian)
    {
        $request->validate([
            'kode_brand' => 'required|string|exists:brand,kode_brand',
            'nama_produk' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'bukti_pembelian' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'detail_barang' => 'required|array|min:1',
            'detail_barang.*.kode_barang' => 'required|string|exists:barang,kode_barang',
            'detail_barang.*.jumlah' => 'required|integer|min:1',
            'detail_barang.*.harga_barang_masuk' => 'required|integer|min:1000'
        ]);

        try {
            DB::beginTransaction();

            $barangMasuk = BarangMasuk::where('kode_pembelian', $kode_pembelian)->firstOrFail();

            // Update data barang masuk
            $dataUpdate = [
                'tanggal_masuk' => $request->tanggal_masuk
            ];

            // Handle file upload
            if ($request->hasFile('bukti_pembelian')) {
                // Delete old file if exists
                if ($barangMasuk->bukti_pembelian && Storage::exists($barangMasuk->bukti_pembelian)) {
                    Storage::delete($barangMasuk->bukti_pembelian);
                }

                $buktiPath = $request->file('bukti_pembelian')->store('bukti_pembelian', 'public');
                $dataUpdate['bukti_pembelian'] = $buktiPath;
            }

            $barangMasuk->update($dataUpdate);

            // Delete existing detail barang masuk
            DetailBarangMasuk::where('kode_pembelian', $kode_pembelian)->delete();

            // Insert new detail barang masuk
            foreach ($request->detail_barang as $detail) {
                DetailBarangMasuk::create([
                    'kode_pembelian' => $kode_pembelian,
                    'kode_barang' => $detail['kode_barang'],
                    'jumlah' => $detail['jumlah'],
                    'harga_barang_masuk' => $detail['harga_barang_masuk']
                ]);

                // Update harga beli di tabel barang
                DB::table('barang')
                    ->where('kode_barang', $detail['kode_barang'])
                    ->update(['harga_beli' => $detail['harga_barang_masuk']]);
            }

            DB::commit();

            return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk berhasil diupdate');
        } catch (Exception $e) {
            DB::rollback();

            if (isset($buktiPath) && Storage::exists($buktiPath)) {
                Storage::delete($buktiPath);
            }

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mengupdate data: ' . $e->getMessage());
        }
    }

    public function getProdukByBrand(Request $request)
    {
        $kodeBrand = $request->kode_brand;

        // Ambil barang berdasarkan brand melalui relasi tipe
        $barangs = DB::table('barang')
            ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
            ->where('tipe.kode_brand', $kodeBrand)
            ->where('barang.is_active', 1)
            ->select('barang.kode_barang', 'barang.nama_barang')
            ->get();

        return response()->json($barangs);
    }

    // Method baru untuk mengambil detail barang berdasarkan nama produk
    public function getDetailByProduk(Request $request)
    {
        $namaBarang = $request->nama_barang;
        $kodeBrand = $request->kode_brand;

        $detailBarangs = DB::table('detail_barang')
            ->join('barang', 'detail_barang.kode_barang', '=', 'barang.kode_barang')
            ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
            ->join('warna', 'detail_barang.kode_warna', '=', 'warna.kode_warna')
            ->where('barang.nama_barang', $namaBarang)
            ->where('tipe.kode_brand', $kodeBrand)
            ->select(
                'detail_barang.kode_detail',
                'detail_barang.kode_barang',
                'detail_barang.ukuran',
                'warna.warna',
                'barang.nama_barang'
            )
            ->get();

        return response()->json($detailBarangs);
    }

    // Method store yang diubah
    public function store(Request $request)
    {
        $request->validate([
            'kode_pembelian' => 'required|string|max:10|unique:barang_masuk,kode_pembelian',
            'kode_brand' => 'required|string|exists:brand,kode_brand',
            'tanggal_masuk' => 'required|date',
            'bukti_pembelian' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'detail_barang' => 'required|array|min:1',
            'detail_barang.*.kode_detail' => 'required|string|exists:detail_barang,kode_detail',
            'detail_barang.*.jumlah' => 'required|integer|min:1',
            'detail_barang.*.harga_barang_masuk' => 'required|integer|min:1000'
        ]);

        try {
            DB::beginTransaction();

            $dataBarangMasuk = [
                'kode_pembelian' => $request->kode_pembelian,
                'id_admin' => Auth::guard('admin')->user()->id_admin,
                'tanggal_masuk' => $request->tanggal_masuk
            ];

            if ($request->hasFile('bukti_pembelian')) {
                $buktiPath = $request->file('bukti_pembelian')->store('bukti_pembelian', 'public');
                $dataBarangMasuk['bukti_pembelian'] = $buktiPath;
            }

            $barangMasuk = BarangMasuk::create($dataBarangMasuk);

            foreach ($request->detail_barang as $detail) {
                // Ambil data detail barang
                $detailBarang = DB::table('detail_barang')
                    ->where('kode_detail', $detail['kode_detail'])
                    ->first();

                // Simpan detail barang masuk
                DetailBarangMasuk::create([
                    'kode_pembelian' => $request->kode_pembelian,
                    'kode_barang' => $detailBarang->kode_barang,
                    'jumlah' => $detail['jumlah'],
                    'harga_barang_masuk' => $detail['harga_barang_masuk']
                ]);

                // Update stok dan harga di detail_barang yang spesifik
                DB::table('detail_barang')
                    ->where('kode_detail', $detail['kode_detail'])
                    ->update([
                        'stok' => $detailBarang->stok + $detail['jumlah'],
                        'harga_beli' => $detail['harga_barang_masuk'],
                        'harga_normal' => $detail['harga_barang_masuk'] // atau sesuai logic bisnis
                    ]);

                // Update harga beli di tabel barang utama
                DB::table('barang')
                    ->where('kode_barang', $detailBarang->kode_barang)
                    ->update(['harga_beli' => $detail['harga_barang_masuk']]);
            }

            DB::commit();

            return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk berhasil disimpan');
        } catch (Exception $e) {
            DB::rollback();

            if (isset($buktiPath) && Storage::exists($buktiPath)) {
                Storage::delete($buktiPath);
            }

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($kode_pembelian)
    {
        try {
            DB::beginTransaction();

            $barangMasuk = BarangMasuk::where('kode_pembelian', $kode_pembelian)->firstOrFail();

            DetailBarangMasuk::where('kode_pembelian', $kode_pembelian)->delete();

            if ($barangMasuk->bukti_pembelian && Storage::exists($barangMasuk->bukti_pembelian)) {
                Storage::delete($barangMasuk->bukti_pembelian);
            }

            $barangMasuk->delete();

            DB::commit();

            return redirect()->route('barang-masuk.index')->with('success', 'Barang masuk berhasil dihapus');
        } catch (Exception $e) {
            DB::rollback();

            return redirect()->route('barang-masuk.index')->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }
    // Method untuk mendapatkan tipe berdasarkan brand
    public function getTipeByBrand(Request $request)
    {
        $kodeBrand = $request->kode_brand;

        $tipes = DB::table('tipe')
            ->where('kode_brand', $kodeBrand)
            ->select('kode_tipe', 'nama_tipe')
            ->get();

        return response()->json($tipes);
    }
    // Method untuk menyimpan barang baru dari form barang masuk
    public function storeBarangBaru(Request $request)
    {
        $validatedData = $request->validate([
            'nama_barang' => 'required|max:100',
            'berat' => 'required|integer|min:1',
            'harga_normal' => 'required|integer|min:1000',
            'deskripsi' => 'nullable',
            'kode_tipe' => 'required|exists:tipe,kode_tipe',
            'ukuran' => 'required',
            'kode_warna' => 'required|exists:warna,kode_warna'
        ]);

        try {
            DB::beginTransaction();

            // Generate kode barang
            $kode_barang = Barang::generateKodeBarang($validatedData['kode_tipe']);

            // Simpan barang
            $barang = Barang::create([
                'kode_barang' => $kode_barang,
                'nama_barang' => $validatedData['nama_barang'],
                'berat' => $validatedData['berat'],
                'harga_normal' => $validatedData['harga_normal'],
                'deskripsi' => $validatedData['deskripsi'],
                'kode_tipe' => $validatedData['kode_tipe']
            ]);

            // Simpan detail barang
            $kode_detail = DetailBarang::generateKodeDetail($kode_barang, $validatedData['kode_warna'], 1);

            DetailBarang::create([
                'kode_detail' => $kode_detail,
                'kode_barang' => $kode_barang,
                'kode_warna' => $validatedData['kode_warna'],
                'ukuran' => $validatedData['ukuran'],
                'stok' => 0, // Stok akan diupdate saat barang masuk
                'harga_normal' => $validatedData['harga_normal']
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'barang' => [
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang
                ],
                'message' => 'Barang berhasil ditambahkan'
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
