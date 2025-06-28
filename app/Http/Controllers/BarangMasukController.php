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

        return view('admin.barang-masuk.form', compact('barangMasuk', 'brands', 'barangs', 'warnas', 'selectedBrand'));
    }

    public function getProdukByBrand(Request $request)
    {
        $kodeBrand = $request->kode_brand;
        // $kodeBrand = 'PATR';

        // Ambil semua kode_tipe dari brand ini
        $kodeTipeList = DB::table('tipe')
            ->where('kode_brand', $kodeBrand)
            ->pluck('kode_tipe');

        // Ambil semua barang yang tipe-nya termasuk dalam daftar di atas
        $barangs = DB::table('barang')
            ->whereIn('kode_tipe', $kodeTipeList)
            ->where('is_active', 1)
            ->select(
                'kode_barang',
                'nama_barang',
                'harga_normal',
                'gambar',
                'kode_tipe'
            )
            ->get();

        // dd($barangs);

        return response()->json($barangs);
    }

    public function getDetailByBarang(Request $request)
    {
        try {
            $kodeBarang = $request->get('kode_barang');

            if (!$kodeBarang) {
                return response()->json([]);
            }

            // Query untuk mendapatkan detail barang berdasarkan kode_barang
            $detailBarang = DB::table('detail_barang')
                ->join('warna', 'detail_barang.kode_warna', '=', 'warna.kode_warna')
                ->where('detail_barang.kode_barang', $kodeBarang)
                ->select(
                    'detail_barang.kode_detail',
                    'detail_barang.ukuran',
                    'detail_barang.kode_warna',
                    'warna.warna',
                    'detail_barang.stok'
                )
                ->orderBy('warna.warna')
                ->orderBy('detail_barang.ukuran')
                ->get();

            return response()->json($detailBarang);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan saat mengambil detail barang',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // Method store yang diubah
    // Method store yang diubah
    public function store(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'tanggal_masuk' => 'required|date',
                'bukti_pembelian' => 'nullable|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
                'produk' => 'required|array|min:1',
                'produk.*.kode_barang' => 'required|exists:barang,kode_barang',
                'produk.*.detail' => 'required|array|min:1',
                'produk.*.detail.*.kode_detail' => 'required|exists:detail_barang,kode_detail',
                'produk.*.detail.*.jumlah' => 'required|integer|min:1',
                'produk.*.detail.*.harga_barang_masuk' => 'required|integer|min:1000',
                'produk.*.detail.*.stok_minimum' => 'required|integer|min:0',
                'produk.*.detail.*.potongan_harga' => 'nullable|integer|min:0',
            ]);

            DB::beginTransaction();

            // Generate kode barang masuk
            // $kodeBarangMasuk = $this->generateKodeBarangMasuk();

            // Insert barang masuk

            $buktiPath = null;
            if ($request->hasFile('bukti_pembelian')) {
                $buktiPath = $request->file('bukti_pembelian')->store('bukti_pembelian', 'public');
            }

            DB::table('barang_masuk')->insert([
                'kode_pembelian' => $request->kode_pembelian,
                'tanggal_masuk' => $request->tanggal_masuk,
                'id_admin' => Auth::user()->id_admin,
                'bukti_pembelian' => $buktiPath,
            ]);

            // Insert detail barang masuk dan update stok
            foreach ($request->produk as $produk) {
                foreach ($produk['detail'] as $detail) {
                    // Insert ke tabel detail_barang_masuk (transaksi pembelian)
                    DB::table('detail_barang_masuk')->insert([
                        'kode_pembelian' => $request->kode_pembelian,
                        'kode_barang' => $produk['kode_barang'], // Menggunakan kode_barang dari level produk
                        'jumlah' => $detail['jumlah'],
                        'harga_barang_masuk' => $detail['harga_barang_masuk'],
                    ]);

                    // Update stok di detail_barang (menambah stok)
                    DB::table('detail_barang')
                        ->where('kode_detail', $detail['kode_detail'])
                        ->increment('stok', $detail['jumlah']);

                    // Update harga beli dan stok minimum di detail_barang
                    $updateData = [
                        // 'harga_barang_masuk' => $detail['harga_barang_masuk'],
                        'stok_minimum' => $detail['stok_minimum'],
                    ];

                    // Jika ada potongan harga, update juga
                    if (isset($detail['potongan_harga']) && $detail['potongan_harga'] > 0) {
                        $updateData['potongan_harga'] = $detail['potongan_harga'];
                    }

                    DB::table('detail_barang')
                        ->where('kode_detail', $detail['kode_detail'])
                        ->update($updateData);
                }
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

        //     DB::commit();

        //     return response()->json([
        //         'success' => true,
        //         'message' => 'Barang masuk berhasil disimpan',
        //         'redirect' => route('barang-masuk.index')
        //     ]);

        // } catch (\Exception $e) {
        //     DB::rollback();
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
        //     ], 500);
        // }
    }

    // private function generateKodeBarangMasuk()
    // {
    //     $today = date('Ymd');
    //     $lastBarangMasuk = DB::table('barang_masuk')
    //         ->where('kode_pembelian', 'LIKE', 'BM' . $today . '%')
    //         ->orderBy('kode_pembelian', 'desc')
    //         ->first();

    //     if ($lastBarangMasuk) {
    //         $lastNumber = (int) substr($lastBarangMasuk->kode_barang_masuk, -3);
    //         $newNumber = $lastNumber + 1;
    //     } else {
    //         $newNumber = 1;
    //     }

    //     return 'BM' . $today . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    // }


    // Method untuk menyimpan barang baru dari form barang masuk
    public function storeBarangBaru(Request $request)
    {
        $validatedData = $request->validate([
            'nama_barang' => 'required|max:100',
            'berat' => 'required|integer|min:1',
            'harga_normal' => 'required|integer|min:1000',
            'deskripsi' => 'nullable',
            'kode_tipe' => 'required|exists:tipe,kode_tipe',
            'detail_warnas' => 'required|array|min:1',
            'detail_warnas.*.ukuran' => 'required',
            'detail_warnas.*.kode_warna' => 'required|exists:warna,kode_warna',
            'detail_warnas.*.stok_minimum' => 'nullable|integer|min:0',
            'detail_warnas.*.potongan_harga' => 'nullable|integer|min:0'
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
            $detailBarangs = [];
            foreach ($validatedData['detail_warnas'] as $index => $detail) {
                $kode_detail = DetailBarang::generateKodeDetail($kode_barang, $detail['kode_warna'], $index + 1);

                $detailData = [
                    'kode_detail' => $kode_detail,
                    'kode_barang' => $kode_barang,
                    'kode_warna' => $detail['kode_warna'],
                    'ukuran' => $detail['ukuran'],
                    'stok' => 0, // Stok akan diupdate saat barang masuk
                    'harga_normal' => $validatedData['harga_normal']
                ];

                // Tambahkan stok_minimum jika ada
                if (isset($detail['stok_minimum']) && $detail['stok_minimum'] !== null) {
                    $detailData['stok_minimum'] = $detail['stok_minimum'];
                }

                // Tambahkan potongan_harga jika ada
                if (isset($detail['potongan_harga']) && $detail['potongan_harga'] !== null) {
                    $detailData['potongan_harga'] = $detail['potongan_harga'];
                }

                $detailBarang = DetailBarang::create($detailData);
                $detailBarangs[] = $detailBarang;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'barang' => [
                    'kode_barang' => $barang->kode_barang,
                    'nama_barang' => $barang->nama_barang
                ],
                'detail_barangs' => $detailBarangs->map(function ($detail) {
                    return [
                        'kode_detail' => $detail->kode_detail,
                        'warna' => $detail->warna->warna ?? '',
                        'ukuran' => $detail->ukuran
                    ];
                }),
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

    public function update(Request $request, $kode_pembelian)
    {
        $request->validate([
            'kode_brand' => 'required|string|exists:brand,kode_brand',
            'nama_produk' => 'required|string',
            'tanggal_masuk' => 'required|date',
            'bukti_pembelian' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048',
            'detail_barang' => 'required|array|min:1',
            'detail_barang.*.kode_detail' => 'required|string|exists:detail_barang,kode_detail',
            'detail_barang.*.jumlah' => 'required|integer|min:1',
            'detail_barang.*.harga_barang_masuk' => 'required|integer|min:1000',
            'detail_barang.*.stok_minimum' => 'nullable|integer|min:0',
            'detail_barang.*.potongan_harga' => 'nullable|integer|min:0'
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

            // Get existing detail to calculate stock difference
            $existingDetails = DetailBarangMasuk::where('kode_pembelian', $kode_pembelian)->get();

            // Restore stock from existing details
            foreach ($existingDetails as $existingDetail) {
                $detailBarang = DB::table('detail_barang')
                    ->where('kode_detail', $existingDetail->kode_detail)
                    ->first();

                if ($detailBarang) {
                    DB::table('detail_barang')
                        ->where('kode_detail', $existingDetail->kode_detail)
                        ->update(['stok' => $detailBarang->stok - $existingDetail->jumlah]);
                }
            }

            // Delete existing detail barang masuk
            DetailBarangMasuk::where('kode_pembelian', $kode_pembelian)->delete();

            // Insert new detail barang masuk
            foreach ($request->detail_barang as $detail) {
                // Ambil data detail barang
                $detailBarang = DB::table('detail_barang')
                    ->where('kode_detail', $detail['kode_detail'])
                    ->first();

                DetailBarangMasuk::create([
                    'kode_pembelian' => $kode_pembelian,
                    'kode_barang' => $detailBarang->kode_barang,
                    'jumlah' => $detail['jumlah'],
                    'harga_barang_masuk' => $detail['harga_barang_masuk']
                ]);

                // Update stok, harga, stok minimum, dan potongan harga
                $updateData = [
                    'stok' => $detailBarang->stok + $detail['jumlah'],
                    // 'harga_beli' => $detail['harga_barang_masuk']
                ];

                // Tambahkan stok_minimum jika ada
                if (isset($detail['stok_minimum']) && $detail['stok_minimum'] !== null) {
                    $updateData['stok_minimum'] = $detail['stok_minimum'];
                }

                // Tambahkan potongan_harga jika ada
                if (isset($detail['potongan_harga']) && $detail['potongan_harga'] !== null) {
                    $updateData['potongan_harga'] = $detail['potongan_harga'];
                }

                DB::table('detail_barang')
                    ->where('kode_detail', $detail['kode_detail'])
                    ->update($updateData);

                // Update harga beli di tabel barang
                DB::table('barang')
                    ->where('kode_barang', $detailBarang->kode_barang)
                    ->update(['harga_barang_masuk' => $detail['harga_barang_masuk']]);
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
    public function show($kode_pembelian)
    {
        $barangMasuk = BarangMasuk::with('admin')->findOrFail($kode_pembelian);
        $detailBarangMasuk = DetailBarangMasuk::with('barang')
            ->where('kode_pembelian', $kode_pembelian)
            ->get();

        // Hitung total
        $totalJumlah = $detailBarangMasuk->sum('jumlah');
        $totalHarga = $detailBarangMasuk->sum(function ($item) {
            return $item->jumlah * $item->harga_barang_masuk;
        });

        return view('admin.barang-masuk.show', compact('barangMasuk', 'detailBarangMasuk', 'totalJumlah', 'totalHarga'));
    }
}
