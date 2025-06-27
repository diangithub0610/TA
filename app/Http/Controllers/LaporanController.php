<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $status = $request->get('status', 'menunggu_konfirmasi');

        // Mapping status untuk tab
        $statusMap = [
            'pesanan_baru' => 'menunggu_konfirmasi',
            'dalam_proses' => 'diproses',
            'dikirim' => 'dikirim',
            'selesai' => 'selesai',
            'dibatalkan' => 'dibatalkan'
        ];

        $actualStatus = $statusMap[$status] ?? 'menunggu_konfirmasi';

        // Ambil semua transaksi sesuai status
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

        $pdf = Pdf::loadView('admin.shopkeeper.riwayat-pdf', compact('transaksi', 'status'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('riwayat_transaksi_' . $status . '.pdf');
    }

    // Laporan Barang Masuk
    public function barangMasuk(Request $request)
    {
        $query = DB::table('barang_masuk as bm')
            ->join('detail_barang_masuk as dbm', 'bm.kode_pembelian', '=', 'dbm.kode_pembelian')
            ->join('barang as b', 'dbm.kode_barang', '=', 'b.kode_barang')
            ->join('tipe as t', 'b.kode_tipe', '=', 't.kode_tipe')
            ->join('brand as br', 't.kode_brand', '=', 'br.kode_brand')
            ->join('pengguna as p', 'bm.id_admin', '=', 'p.id_admin')
            ->select(
                'bm.kode_pembelian',
                'bm.tanggal_masuk',
                'bm.bukti_pembelian',
                'p.nama_admin as admin',
                'b.kode_barang',
                'b.nama_barang',
                'br.nama_brand',
                't.nama_tipe',
                'dbm.jumlah',
                'dbm.harga_barang_masuk',
                DB::raw('(dbm.jumlah * dbm.harga_barang_masuk) as total_harga')
            );

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->where('bm.tanggal_masuk', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->where('bm.tanggal_masuk', '<=', $request->tanggal_selesai);
        }

        // Filter berdasarkan brand
        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }

        $barangMasuk = $query->orderBy('bm.tanggal_masuk', 'desc')->get();
        
        // Get brands for filter
        $brands = DB::table('brand')->get();
        
        // Calculate totals
        $totalJumlah = $barangMasuk->sum('jumlah');
        $totalNilai = $barangMasuk->sum('total_harga');

        return view('laporan.laporan-barangmasuk', compact('barangMasuk', 'brands', 'totalJumlah', 'totalNilai'));
    }

    // Laporan transaksi Terjual
    public function transaksi(Request $request)
    {
        $query = DB::table('transaksi as t')
            ->join('detail_transaksi as dt', 't.kode_transaksi', '=', 'dt.kode_transaksi')
            ->join('detail_barang as db', 'dt.kode_detail', '=', 'db.kode_detail')
            ->join('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->join('tipe as tp', 'b.kode_tipe', '=', 'tp.kode_tipe')
            ->join('brand as br', 'tp.kode_brand', '=', 'br.kode_brand')
            ->join('warna as w', 'db.kode_warna', '=', 'w.kode_warna')
            ->leftJoin('pelanggan as p', 't.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                't.kode_transaksi',
                't.tanggal_transaksi',
                't.status',
                't.jenis',
                'p.nama_pelanggan',
                'b.nama_barang',
                'br.nama_brand',
                'tp.nama_tipe',
                'w.warna',
                'db.ukuran',
                'dt.kuantitas',
                'dt.harga',
                DB::raw('(dt.kuantitas * dt.harga) as total_harga')
            )
            ->whereIn('t.status', ['selesai', 'dikirim']);
    
        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('t.tanggal_transaksi', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('t.tanggal_transaksi', '<=', $request->tanggal_selesai);
        }
    
        // Filter berdasarkan brand
        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }
    
        // Filter berdasarkan jenis transaksi
        if ($request->filled('jenis')) {
            $query->where('t.jenis', $request->jenis);
        }
    
        $barangTerjual = $query->orderBy('t.tanggal_transaksi', 'desc')->get();
    
        // Grouping berdasarkan kode_transaksi untuk merge row di Blade
        $barangTerjualGrouped = $barangTerjual->groupBy('kode_transaksi');
    
        // Ambil semua brand untuk filter dropdown
        $brands = DB::table('brand')->get();
    
        // Hitung total kuantitas dan total nilai penjualan
        $totalKuantitas = $barangTerjual->sum('kuantitas');
        $totalNilai = $barangTerjual->sum('total_harga');
    
        return view('laporan.laporan-transaksi', [
            'barangTerjual' => $barangTerjual,
            'barangTerjualGrouped' => $barangTerjualGrouped,
            'brands' => $brands,
            'totalKuantitas' => $totalKuantitas,
            'totalNilai' => $totalNilai,
        ]);
    }
    

    // Laporan Pemusnahan Barang (berdasarkan barang dengan stok kadaluarsa/rusak)
    public function pemusnahanBarang(Request $request)
    {
        // Untuk contoh, kita ambil barang dengan stok sangat rendah atau yang sudah lama tidak terjual
        $query = DB::table('detail_barang as db')
            ->join('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->join('tipe as t', 'b.kode_tipe', '=', 't.kode_tipe')
            ->join('brand as br', 't.kode_brand', '=', 'br.kode_brand')
            ->join('warna as w', 'db.kode_warna', '=', 'w.kode_warna')
            ->leftJoin('detail_transaksi as dt', 'db.kode_detail', '=', 'dt.kode_detail')
            ->leftJoin('transaksi as tr', 'dt.kode_transaksi', '=', 'tr.kode_transaksi')
            ->select(
                'db.kode_detail',
                'b.nama_barang',
                'br.nama_brand',
                't.nama_tipe',
                'w.warna',
                'db.ukuran',
                'db.stok',
                'b.harga_beli',
                'b.created_at',
                DB::raw('MAX(tr.tanggal_transaksi) as transaksi_terakhir'),
                DB::raw('DATEDIFF(NOW(), b.created_at) as hari_sejak_dibuat'),
                DB::raw('DATEDIFF(NOW(), MAX(tr.tanggal_transaksi)) as hari_tidak_terjual'),
                DB::raw('(db.stok * b.harga_beli) as nilai_pemusnahan')
            )
            ->groupBy('db.kode_detail', 'b.nama_barang', 'br.nama_brand', 't.nama_tipe', 'w.warna', 'db.ukuran', 'db.stok', 'b.harga_beli', 'b.created_at')
            ->having('hari_sejak_dibuat', '>', 365) // Barang lebih dari 1 tahun
            ->orHaving('hari_tidak_terjual', '>', 180) // Tidak terjual lebih dari 6 bulan
            ->orWhere('db.stok', '>', 0); // Masih ada stok

        // Filter berdasarkan brand
        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }

        // Filter berdasarkan kriteria pemusnahan
        if ($request->filled('kriteria')) {
            switch ($request->kriteria) {
                case 'lama':
                    $query->having('hari_sejak_dibuat', '>', 365);
                    break;
                case 'tidak_laku':
                    $query->having('hari_tidak_terjual', '>', 180);
                    break;
                case 'rusak':
                    // Implementasi kriteria rusak sesuai kebutuhan
                    break;
            }
        }

        $pemusnahanBarang = $query->orderBy('hari_sejak_dibuat', 'desc')->get();
        
        // Get brands for filter
        $brands = DB::table('brand')->get();
        
        // Calculate totals
        $totalStok = $pemusnahanBarang->sum('stok');
        $totalNilai = $pemusnahanBarang->sum('nilai_pemusnahan');

        return view('laporan.pemusnahan-barang', compact('pemusnahanBarang', 'brands', 'totalStok', 'totalNilai'));
    }

    // Export PDF Methods
    public function exportBarangMasukPdf(Request $request)
    {
        $barangMasuk = $this->getBarangMasukData($request);
        $pdf = Pdf::loadView('laporan.pdf.barang-masuk', compact('barangMasuk'));
        return $pdf->download('laporan-barang-masuk-' . date('Y-m-d') . '.pdf');
    }

    public function exportBarangTerjualPdf(Request $request)
    {
        $barangTerjual = $this->getBarangTerjualData($request);
        $pdf = Pdf::loadView('laporan.pdf.barang-terjual', compact('barangTerjual'));
        return $pdf->download('laporan-barang-terjual-' . date('Y-m-d') . '.pdf');
    }

    public function exportPemusnahanBarangPdf(Request $request)
    {
        $pemusnahanBarang = $this->getPemusnahanBarangData($request);
        $pdf = Pdf::loadView('laporan.pdf.pemusnahan-barang', compact('pemusnahanBarang'));
        return $pdf->download('laporan-pemusnahan-barang-' . date('Y-m-d') . '.pdf');
    }

    // Export Excel Methods
 

    // Helper methods untuk mengambil data yang sama dengan method utama
    private function getBarangMasukData($request)
    {
        $query = DB::table('barang_masuk as bm')
            ->join('detail_barang_masuk as dbm', 'bm.kode_pembelian', '=', 'dbm.kode_pembelian')
            ->join('barang as b', 'dbm.kode_barang', '=', 'b.kode_barang')
            ->join('tipe as t', 'b.kode_tipe', '=', 't.kode_tipe')
            ->join('brand as br', 't.kode_brand', '=', 'br.kode_brand')
            ->join('pengguna as p', 'bm.id_admin', '=', 'p.id_admin')
            ->select(
                'bm.kode_pembelian',
                'bm.tanggal_masuk',
                'bm.bukti_pembelian',
                'p.nama_admin as admin',
                'b.kode_barang',
                'b.nama_barang',
                'br.nama_brand',
                't.nama_tipe',
                'dbm.jumlah',
                'dbm.harga_barang_masuk',
                DB::raw('(dbm.jumlah * dbm.harga_barang_masuk) as total_harga')
            );

        if ($request->filled('tanggal_mulai')) {
            $query->where('bm.tanggal_masuk', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->where('bm.tanggal_masuk', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }

        return $query->orderBy('bm.tanggal_masuk', 'desc')->get();
    }

    private function getBarangTerjualData($request)
    {
        $query = DB::table('transaksi as t')
            ->join('detail_transaksi as dt', 't.kode_transaksi', '=', 'dt.kode_transaksi')
            ->join('detail_barang as db', 'dt.kode_detail', '=', 'db.kode_detail')
            ->join('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->join('tipe as tp', 'b.kode_tipe', '=', 'tp.kode_tipe')
            ->join('brand as br', 'tp.kode_brand', '=', 'br.kode_brand')
            ->join('warna as w', 'db.kode_warna', '=', 'w.kode_warna')
            ->leftJoin('pelanggan as p', 't.id_pelanggan', '=', 'p.id_pelanggan')
            ->select(
                't.kode_transaksi',
                't.tanggal_transaksi',
                't.status',
                't.jenis',
                'p.nama_pelanggan',
                'b.nama_barang',
                'br.nama_brand',
                'tp.nama_tipe',
                'w.warna',
                'db.ukuran',
                'dt.kuantitas',
                'dt.harga',
                DB::raw('(dt.kuantitas * dt.harga) as total_harga')
            )
            ->whereIn('t.status', ['selesai', 'dikirim']);

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('t.tanggal_transaksi', '>=', $request->tanggal_mulai);
        }
        
        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('t.tanggal_transaksi', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }

        if ($request->filled('jenis')) {
            $query->where('t.jenis', $request->jenis);
        }

        return $query->orderBy('t.tanggal_transaksi', 'desc')->get();
    }

    private function getPemusnahanBarangData($request)
    {
        $query = DB::table('detail_barang as db')
            ->join('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->join('tipe as t', 'b.kode_tipe', '=', 't.kode_tipe')
            ->join('brand as br', 't.kode_brand', '=', 'br.kode_brand')
            ->join('warna as w', 'db.kode_warna', '=', 'w.kode_warna')
            ->leftJoin('detail_transaksi as dt', 'db.kode_detail', '=', 'dt.kode_detail')
            ->leftJoin('transaksi as tr', 'dt.kode_transaksi', '=', 'tr.kode_transaksi')
            ->select(
                'db.kode_detail',
                'b.nama_barang',
                'br.nama_brand',
                't.nama_tipe',
                'w.warna',
                'db.ukuran',
                'db.stok',
                'b.harga_beli',
                'b.created_at',
                DB::raw('MAX(tr.tanggal_transaksi) as transaksi_terakhir'),
                DB::raw('DATEDIFF(NOW(), b.created_at) as hari_sejak_dibuat'),
                DB::raw('DATEDIFF(NOW(), MAX(tr.tanggal_transaksi)) as hari_tidak_terjual'),
                DB::raw('(db.stok * b.harga_beli) as nilai_pemusnahan')
            )
            ->groupBy('db.kode_detail', 'b.nama_barang', 'br.nama_brand', 't.nama_tipe', 'w.warna', 'db.ukuran', 'db.stok', 'b.harga_beli', 'b.created_at')
            ->having('hari_sejak_dibuat', '>', 365)
            ->orHaving('hari_tidak_terjual', '>', 180)
            ->orWhere('db.stok', '>', 0);

        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }

        if ($request->filled('kriteria')) {
            switch ($request->kriteria) {
                case 'lama':
                    $query->having('hari_sejak_dibuat', '>', 365);
                    break;
                case 'tidak_laku':
                    $query->having('hari_tidak_terjual', '>', 180);
                    break;
            }
        }

        return $query->orderBy('hari_sejak_dibuat', 'desc')->get();
    }
    public function laporanBarangTerjual(Request $request)
    {
        // Query untuk mendapatkan data laporan barang terjual
        $query = DB::table('detail_barang as db')
            ->leftJoin('barang as b', 'db.kode_barang', '=', 'b.kode_barang')
            ->leftJoin('tipe as t', 'b.kode_tipe', '=', 't.kode_tipe')
            ->leftJoin('brand as br', 't.kode_brand', '=', 'br.kode_brand')
            ->leftJoin('warna as w', 'db.kode_warna', '=', 'w.kode_warna')
            ->leftJoin('detail_transaksi as dt', function($join) {
                $join->on('db.kode_detail', '=', 'dt.kode_detail')
                     ->join('transaksi as tr', 'dt.kode_transaksi', '=', 'tr.kode_transaksi')
                     ->whereIn('tr.status', ['diproses', 'dikirim', 'selesai']);
            })
            ->select(
                'db.kode_detail',
                'b.nama_barang',
                'br.nama_brand',
                'w.warna',
                't.nama_tipe',
                'db.ukuran',
                'db.stok',
                'db.harga_normal',
                DB::raw('COALESCE(SUM(dt.kuantitas), 0) as jumlah_terjual'),
                DB::raw('COALESCE(SUM(dt.kuantitas * dt.harga), 0) as total_pendapatan')
            )
            ->where('b.is_active', 1)
            ->groupBy(
                'db.kode_detail',
                'b.nama_barang',
                'br.nama_brand',
                'w.warna',
                't.nama_tipe',
                'db.ukuran',
                'db.stok',
                'db.harga_normal'
            )
            ->orderBy('jumlah_terjual', 'desc');

        // Filter berdasarkan parameter
        if ($request->filled('brand')) {
            $query->where('br.kode_brand', $request->brand);
        }

        if ($request->filled('tipe')) {
            $query->where('t.kode_tipe', $request->tipe);
        }

        if ($request->filled('warna')) {
            $query->where('w.kode_warna', $request->warna);
        }

        // Filter berdasarkan tanggal jika ada
        if ($request->filled('tanggal_mulai')) {
            $query->whereExists(function($query) use ($request) {
                $query->select(DB::raw(1))
                      ->from('detail_transaksi as dt2')
                      ->join('transaksi as tr2', 'dt2.kode_transaksi', '=', 'tr2.kode_transaksi')
                      ->whereColumn('dt2.kode_detail', 'db.kode_detail')
                      ->whereDate('tr2.tanggal_transaksi', '>=', $request->tanggal_mulai);
            });
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereExists(function($query) use ($request) {
                $query->select(DB::raw(1))
                      ->from('detail_transaksi as dt2')
                      ->join('transaksi as tr2', 'dt2.kode_transaksi', '=', 'tr2.kode_transaksi')
                      ->whereColumn('dt2.kode_detail', 'db.kode_detail')
                      ->whereDate('tr2.tanggal_transaksi', '<=', $request->tanggal_selesai);
            });
        }

        $laporanBarang = $query->get();

        // Data untuk filter dropdown
        $brands = DB::table('brand')->select('kode_brand', 'nama_brand')->get();
        $tipes = DB::table('tipe')->join('brand', 'tipe.kode_brand', '=', 'brand.kode_brand')
                    ->select('tipe.kode_tipe', 'tipe.nama_tipe', 'brand.nama_brand')
                    ->get();
        $warnas = DB::table('warna')->select('kode_warna', 'warna')->get();

        return view('laporan.barang-terjual', compact(
            'laporanBarang', 
            'brands', 
            'tipes', 
            'warnas',
            'request'
        ));
    }

    public function exportPdfBarangTerjual(Request $request)
    {
        // Logic untuk export PDF (menggunakan library seperti DomPDF atau TCPDF)
        // Implementasi sesuai kebutuhan
        
        return response()->json(['message' => 'Export PDF berhasil']);
    }
}

