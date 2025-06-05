<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use App\Models\Brand;
use App\Models\Warna;
use App\Models\Barang;
use Barryvdh\DomPDF\PDF;
use App\Models\Pelanggan;
use App\Models\Transaksi;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasirController extends Controller
{
    public function index()
    {
        $resellers = Pelanggan::where('role', 'reseller')->get();
        $brands = Brand::all();
        $warnas = Warna::all();
        
        return view('admin.shopkeeper.kasir', compact('resellers', 'brands', 'warnas'));
    }

    public function getBarang(Request $request)
    {
        $query = Barang::with(['tipe.brand', 'detailBarang.warna'])
                      ->where('is_active', 1);

        if ($request->has('brand') && $request->brand != '') {
            $query->whereHas('tipe', function($q) use ($request) {
                $q->where('kode_brand', $request->brand);
            });
        }

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_barang', 'like', '%' . $request->search . '%');
        }

        $barang = $query->get();

        return response()->json($barang);
    }

    public function getDetailBarang($kode_barang)
    {
        $details = DetailBarang::with('warna')
                              ->where('kode_barang', $kode_barang)
                              ->where('stok', '>', 0)
                              ->get();

        return response()->json($details);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.kode_detail' => 'required|exists:detail_barang,kode_detail',
            'items.*.qty' => 'required|integer|min:1',
            'jenis_transaksi' => 'required|in:offline,marketplace',
            'id_pelanggan' => 'nullable|exists:pelanggan,id_pelanggan',
            'keterangan_marketplace' => 'nullable|string',
            'nama_pengirim' => 'nullable|string|max:50',
            'no_hp_pengirim' => 'nullable|string|max:15',
        ]);

        DB::beginTransaction();
        
        try {
            // Generate kode transaksi
            $kode_transaksi = 'TR' . date('YmdHis') . rand(100, 999);
            
            // Hitung total
            $total = 0;
            foreach ($request->items as $item) {
                $detail = DetailBarang::find($item['kode_detail']);
                if ($detail->stok < $item['qty']) {
                    throw new \Exception("Stok tidak mencukupi untuk " . $detail->barang->nama_barang);
                }
                $total += $detail->harga_normal * $item['qty'];
            }

            // Buat transaksi
            $transaksi = new Transaksi();
            $transaksi->kode_transaksi = $kode_transaksi;
            $transaksi->id_pelanggan = $request->id_pelanggan ?: 'GUEST001'; // Default guest
            $transaksi->id_pengguna = Auth::user()->id_admin ?? null;
            $transaksi->id_alamat = 'DEFAULT001'; // Default alamat toko
            $transaksi->ongkir = 0;
            $transaksi->ekspedisi = $request->jenis_transaksi == 'marketplace' ? 'Marketplace' : 'Toko';
            $transaksi->layanan_ekspedisi = $request->jenis_transaksi == 'marketplace' ? 'Pickup' : 'Langsung';
            $transaksi->status = 'selesai';
            $transaksi->jenis = 'offline';
            $transaksi->is_dropship = $request->has('is_dropship') ? 1 : 0;
            $transaksi->nama_pengirim = $request->nama_pengirim;
            $transaksi->no_hp_pengirim = $request->no_hp_pengirim;
            
            if ($request->jenis_transaksi == 'marketplace' && $request->keterangan_marketplace) {
                $transaksi->keterangan = $request->keterangan_marketplace;
            }
            
            $transaksi->save();

            // Buat detail transaksi dan kurangi stok
            foreach ($request->items as $item) {
                $detail = DetailBarang::find($item['kode_detail']);
                
                // Kurangi stok
                $detail->stok -= $item['qty'];
                $detail->save();

                // Simpan detail transaksi (asumsi ada tabel detail_transaksi)
                DB::table('detail_transaksi')->insert([
                    'kode_transaksi' => $kode_transaksi,
                    'kode_detail' => $item['kode_detail'],
                    'qty' => $item['qty'],
                    'harga' => $detail->harga_normal,
                    'subtotal' => $detail->harga_normal * $item['qty']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'kode_transaksi' => $kode_transaksi,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
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