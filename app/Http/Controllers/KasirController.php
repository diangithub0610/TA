<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use App\Models\Brand;
use App\Models\Warna;
use App\Models\Barang;
use Barryvdh\DomPDF\PDF;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Helpers\GenerateId;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function index()
    {
        // Ambil semua produk dengan detail dan brand
        $products = Barang::with(['detailBarang.warna', 'tipe'])
            ->where('is_active', 1)
            ->get();

        // Ambil semua brand/tipe untuk filter
        $brands = Tipe::all();

        return view('admin.shopkeeper.kasir', compact('products', 'brands'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('search', '');
        $brand = $request->get('brand', '');

        $products = Barang::with(['detailBarang.warna', 'tipe'])
            ->where('is_active', 1)
            ->when($query, function ($q) use ($query) {
                $q->where('nama_barang', 'LIKE', '%' . $query . '%');
            })
            ->when($brand, function ($q) use ($brand) {
                $q->where('kode_tipe', $brand);
            })
            ->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'kode_barang' => $product->kode_barang,
                    'nama_barang' => $product->nama_barang,
                    'harga_normal' => $product->harga_normal,
                    'gambar' => $product->gambar,
                    'brand' => $product->tipe->nama_tipe ?? '',
                    'stok' => $product->detailBarang->sum('stok'),
                    'variants' => $product->detailBarang->map(function ($detail) {
                        return [
                            'kode_detail' => $detail->kode_detail,
                            'ukuran' => $detail->ukuran,
                            'warna' => $detail->warna->nama_warna ?? '',
                            'stok' => $detail->stok,
                            'harga_normal' => $detail->harga_normal ?? $detail->barang->harga_normal
                        ];
                    })
                ];
            })
        ]);
    }

    public function getProductVariants($kode_barang)
    {
        $variants = DetailBarang::with('warna')
            ->where('kode_barang', $kode_barang)
            ->where('stok', '>', 0)
            ->get();

        return response()->json([
            'variants' => $variants->map(function ($variant) {
                return [
                    'kode_detail' => $variant->kode_detail,
                    'ukuran' => $variant->ukuran,
                    'warna' => $variant->warna->nama_warna ?? '',
                    'stok' => $variant->stok,
                    'harga_normal' => $variant->harga_normal
                ];
            })
        ]);
    }

    public function checkReseller(Request $request)
    {

        $id_reseller = $request->get('id_reseller');

        $reseller = Pelanggan::where('id_pelanggan', $id_reseller)
            ->where('role', 'reseller')
            ->first();

        if ($reseller) {
            return response()->json([
                'valid' => true,
                'nama' => $reseller->nama_pelanggan,
                'discount' => 10 // Anda bisa adjust persentase diskon sesuai kebutuhan
            ]);
        }

        return response()->json(['valid' => false]);
    }

    public function processTransaction(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'items' => 'required|array',
                'jenis_transaksi' => 'required|in:offline,marketplace',
                'marketplace' => 'nullable|in:shopee,tokopedia',
                'id_reseller' => 'nullable|string',
                'keterangan' => 'nullable|string'
            ]);

            // Generate kode transaksi
            // $kode_transaksi = 'TRX' . date('YmdHis') . rand(100, 999);

            $kodeTransaksi = GenerateId::transaksi();

            // Determine customer
            // $id_pelanggan = 'GUEST001'; // Default guest customer
            if ($request->id_reseller) {
                $reseller = Pelanggan::where('id_pelanggan', $request->id_reseller)
                    ->where('role', 'reseller')
                    ->first();
                if ($reseller) {
                    $id_pelanggan = $reseller->id_pelanggan;
                }
            }

            // Prepare keterangan
            $keterangan = $request->keterangan ?? '';
            if ($request->jenis_transaksi == 'marketplace' && $request->marketplace) {
                $keterangan = ucfirst($request->marketplace) . ($keterangan ? ' - ' . $keterangan : '');
            }

            $id_pelanggan = $request->input('id_pelanggan');
            // Create transaction
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'id_pelanggan' => $id_pelanggan,
                'id_pengguna' => auth()->user()->id ?? null,
                'tanggal_transaksi' => now(),
                // 'id_alamat' => 'DEFAULT01', // You might need to adjust this
                'ongkir' => 0,
                'keterangan' => $keterangan,
                'ekspedisi' => 'PICKUP',
                'layanan_ekspedisi' => 'PICKUP',
                'status' => 'selesai',
                'jenis' => 'offline'
            ]);

            // Add transaction details
            foreach ($request->items as $item) {
                DetailTransaksi::create([
                    'kode_transaksi' => $kodeTransaksi,
                    'kode_detail' => $item['kode_detail'],
                    'kuantitas' => $item['quantity'],
                    'harga' => $item['price']
                ]);

                // Update stock
                $detailBarang = DetailBarang::where('kode_detail', $item['kode_detail'])->first();
                if ($detailBarang) {
                    $detailBarang->stok -= $item['quantity'];
                    $detailBarang->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses',
                'kode_transaksi' => $kodeTransaksi
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getResellerPrice(Request $request)
    {
        $kode_detail = $request->get('kode_detail');
        $id_reseller = $request->get('id_reseller');
        
        if (!$id_reseller) {
            return response()->json(['error' => 'ID reseller required']);
        }
        
        // Validasi reseller
        $reseller = Pelanggan::where('id_pelanggan', $id_reseller)
            ->where('role', 'reseller')
            ->first();
            
        if (!$reseller) {
            return response()->json(['error' => 'Invalid reseller']);
        }
        
        // Get detail barang dengan join ke barang dan tipe
        $detail = DB::table('detail_barang')
            ->join('barang', 'detail_barang.kode_barang', '=', 'barang.kode_barang')
            ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
            ->where('detail_barang.kode_detail', $kode_detail)
            ->select('detail_barang.harga_normal', 'tipe.potongan_harga')
            ->first();
        
        if (!$detail) {
            return response()->json(['error' => 'Product not found']);
        }
        
        $harga_reseller = $detail->harga_normal - $detail->potongan_harga;
        
        return response()->json([
            'harga_normal' => $detail->harga_normal,
            'potongan_harga' => $detail->potongan_harga,
            'harga_reseller' => $harga_reseller
        ]);
    }

    public function print($kode_transaksi)
    {
        $transaksi = Transaksi::with(['pelanggan', 'detailTransaksi.detailBarang.barang'])
            ->find($kode_transaksi);

        if (!$transaksi) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }

        return view('admin.shopkeeper.struk-kasir', compact('transaksi'));
    }
    public function riwayat(Request $request)
    {
        $status = $request->get('status', 'menunggu_konfirmasi');
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        // Mapping status untuk tab
        $statusMap = [
            'pesanan_baru' => 'menunggu_konfirmasi',
            'dalam_proses' => 'diproses',
            'dikirim' => 'dikirim',
            'selesai' => 'selesai',
            'dibatalkan' => 'dibatalkan'
        ];

        $actualStatus = $statusMap[$status] ?? 'menunggu_konfirmasi';

        // Query untuk mendapatkan data transaksi dengan join
        $query = DB::table('transaksi as t')
            ->leftJoin('pelanggan as p', 't.id_pelanggan', '=', 'p.id_pelanggan')
            ->leftJoin('detail_transaksi as dt', 't.kode_transaksi', '=', 'dt.kode_transaksi')
            ->leftJoin('detail_barang as db', 'dt.kode_detail', '=', 'db.kode_detail')
            ->leftJoin('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->select(
                't.kode_transaksi',
                't.tanggal_transaksi',
                't.keterangan',
                't.status',
                'p.nama_pelanggan',
                DB::raw('GROUP_CONCAT(DISTINCT b.nama_barang SEPARATOR ", ") as produk'),
                DB::raw('SUM(dt.kuantitas * dt.harga) + t.ongkir as total')
            )
            ->where('t.status', $actualStatus)
            ->groupBy('t.kode_transaksi', 't.tanggal_transaksi', 't.keterangan', 't.status', 'p.nama_pelanggan', 't.ongkir')
            ->orderBy('t.tanggal_transaksi', 'desc');

        // Hitung total data untuk pagination
        $total = $query->get()->count();
        $totalPages = ceil($total / $perPage);

        // Ambil data dengan limit dan offset
        $transaksi = $query->limit($perPage)->offset($offset)->get();

        return view('admin.shopkeeper.riwayat', compact('transaksi', 'status', 'page', 'totalPages', 'total'));
    }

    public function show($kode_transaksi)
    {
        $transaksi = DB::table('transaksi as t')
            ->leftJoin('pelanggan as p', 't.id_pelanggan', '=', 'p.id_pelanggan')
            ->leftJoin('alamat as a', 't.id_alamat', '=', 'a.id_alamat')
            ->select('t.*', 'p.nama_pelanggan', 'p.email', 'p.no_hp', 'a.alamat_lengkap', 'a.kota', 'a.provinsi')
            ->where('t.kode_transaksi', $kode_transaksi)
            ->first();

        $detail_transaksi = DB::table('detail_transaksi as dt')
            ->leftJoin('detail_barang as db', 'dt.kode_detail', '=', 'db.kode_detail')
            ->leftJoin('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->leftJoin('warna as w', 'db.kode_warna', '=', 'w.kode_warna')
            ->select('dt.*', 'b.nama_barang', 'b.gambar', 'db.ukuran', 'w.nama_warna')
            ->where('dt.kode_transaksi', $kode_transaksi)
            ->get();

        return view('admin.transaksi.detail', compact('transaksi', 'detail_transaksi'));
    }

    public function exportPdf(Request $request)
    {
        $status = $request->get('status', 'menunggu_konfirmasi');

        $statusMap = [
            'pesanan_baru' => 'menunggu_konfirmasi',
            'dalam_proses' => 'diproses',
            'dikirim' => 'dikirim',
            'selesai' => 'selesai',
            'dibatalkan' => 'dibatalkan'
        ];

        $actualStatus = $statusMap[$status] ?? 'menunggu_konfirmasi';

        $transaksi = DB::table('transaksi as t')
            ->leftJoin('pelanggan as p', 't.id_pelanggan', '=', 'p.id_pelanggan')
            ->leftJoin('detail_transaksi as dt', 't.kode_transaksi', '=', 'dt.kode_transaksi')
            ->leftJoin('detail_barang as db', 'dt.kode_detail', '=', 'db.kode_detail')
            ->leftJoin('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->select(
                't.kode_transaksi',
                't.tanggal_transaksi',
                't.keterangan',
                't.status',
                'p.nama_pelanggan',
                DB::raw('GROUP_CONCAT(DISTINCT b.nama_barang SEPARATOR ", ") as produk'),
                DB::raw('SUM(dt.kuantitas * dt.harga) + t.ongkir as total')
            )
            ->where('t.status', $actualStatus)
            ->groupBy('t.kode_transaksi', 't.tanggal_transaksi', 't.keterangan', 't.status', 'p.nama_pelanggan', 't.ongkir')
            ->orderBy('t.tanggal_transaksi', 'desc')
            ->get();

        $pdf = PDF::loadView('admin.transaksi.pdf', compact('transaksi', 'status'));

        return $pdf->download('riwayat-transaksi-' . $status . '-' . date('Y-m-d') . '.pdf');
    }
}
