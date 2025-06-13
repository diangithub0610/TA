<?php

namespace App\Http\Controllers;

use index;
use Carbon\Carbon;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Periode filter (default 30 hari terakhir)
        $period = $request->get('period', 30);
        $startDate = Carbon::now()->subDays($period)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Statistik Umum
        $totalPelanggan = DB::table('pelanggan')->where('role', 'pelanggan')->count();
        $totalReseller = DB::table('pelanggan')->where('role', 'reseller')->count();
        $totalPelangganBulanIni = DB::table('pelanggan')
            ->where('role', 'pelanggan')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $totalResellerBulanIni = DB::table('pelanggan')
            ->where('role', 'reseller')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Total Penjualan dalam periode
        $totalPenjualan = DB::table('transaksi')
            ->join('detail_transaksi', 'transaksi.kode_transaksi', '=', 'detail_transaksi.kode_transaksi')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate, $endDate])
            ->sum(DB::raw('detail_transaksi.kuantitas * detail_transaksi.harga'));

       

        // Total Order dalam periode
        $totalOrder = DB::table('transaksi')
            ->where('status', 'selesai')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->count();

        // Statistik Penjualan Harian (7 hari terakhir)
        $dailySales = DB::table('transaksi')
            ->join('detail_transaksi', 'transaksi.kode_transaksi', '=', 'detail_transaksi.kode_transaksi')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.tanggal_transaksi', [Carbon::now()->subDays(7), Carbon::now()])
            ->select(
                DB::raw('DATE(transaksi.tanggal_transaksi) as tanggal'),
                DB::raw('SUM(detail_transaksi.kuantitas * detail_transaksi.harga) as total'),
                DB::raw('COUNT(DISTINCT transaksi.kode_transaksi) as jumlah_order')
            )
            ->groupBy(DB::raw('DATE(transaksi.tanggal_transaksi)'))
            ->orderBy('tanggal')
            ->get();

        // Top 10 Barang Terlaris
        $topBarang = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.kode_transaksi', '=', 'transaksi.kode_transaksi')
            ->join('detail_barang', 'detail_transaksi.kode_detail', '=', 'detail_barang.kode_detail')
            ->join('barang', 'detail_barang.kode_barang', '=', 'barang.kode_barang')
            ->join('tipe', 'barang.kode_tipe', '=', 'tipe.kode_tipe')
            ->join('brand', 'tipe.kode_brand', '=', 'brand.kode_brand')
            ->join('warna', 'detail_barang.kode_warna', '=', 'warna.kode_warna')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate, $endDate])
            ->select(
                'barang.nama_barang',
                'brand.nama_brand',
                'tipe.nama_tipe',
                'warna.warna',
                'detail_barang.ukuran',
                DB::raw('SUM(detail_transaksi.kuantitas) as total_terjual'),
                DB::raw('SUM(detail_transaksi.kuantitas * detail_transaksi.harga) as total_pendapatan')
            )
            ->groupBy('detail_barang.kode_detail', 'barang.nama_barang', 'brand.nama_brand', 'tipe.nama_tipe', 'warna.warna', 'detail_barang.ukuran')
            ->orderBy('total_terjual', 'desc')
            ->limit(10)
            ->get();

        // Top 10 Reseller Paling Loyal (berdasarkan jumlah transaksi)
        $loyalResellerByTransaction = DB::table('transaksi')
            ->join('pelanggan', 'transaksi.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->join('detail_transaksi', 'transaksi.kode_transaksi', '=', 'detail_transaksi.kode_transaksi')
            ->select(
                'pelanggan.nama_pelanggan',
                'pelanggan.email',
                'pelanggan.no_hp',
                DB::raw('COUNT(DISTINCT transaksi.kode_transaksi) as total_transaksi'),
                DB::raw('SUM(detail_transaksi.harga * detail_transaksi.kuantitas) as total_spend')
            )
            ->where('pelanggan.role', 'reseller')
            ->where('transaksi.status', 'selesai')
            ->whereNotIn('transaksi.status', ['dibatalkan'])
            ->groupBy('transaksi.id_pelanggan', 'pelanggan.nama_pelanggan', 'pelanggan.email', 'pelanggan.no_hp')
            ->orderByDesc('total_transaksi')
            ->limit(10)
            ->get();

        // Top 10 Reseller Paling Loyal (berdasarkan total spend)
        $loyalResellerBySpend = DB::table('pelanggan')
            ->join('transaksi', 'pelanggan.id_pelanggan', '=', 'transaksi.id_pelanggan')
            ->join('detail_transaksi', 'transaksi.kode_transaksi', '=', 'detail_transaksi.kode_transaksi')
            ->where('pelanggan.role', 'reseller')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.tanggal_transaksi', [$startDate, $endDate])
            ->select(
                'pelanggan.nama_pelanggan',
                'pelanggan.email',
                'pelanggan.no_hp',
                DB::raw('COUNT(DISTINCT transaksi.kode_transaksi) as total_transaksi'),
                DB::raw('SUM(detail_transaksi.kuantitas * detail_transaksi.harga) as total_spend')
            )
            ->groupBy('pelanggan.id_pelanggan', 'pelanggan.nama_pelanggan', 'pelanggan.email', 'pelanggan.no_hp')
            ->orderBy('total_spend', 'desc')
            ->limit(10)
            ->get();

        // Status Transaksi
        $statusTransaksi = DB::table('transaksi')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status')
            ->get();


        //
        $topRatedBarang = $this->getTopRatedProducts();

        // Stok Barang Menipis (kurang dari 10)
        $stokMenupis = DB::table('detail_barang')
            ->join('barang', 'detail_barang.kode_barang', '=', 'barang.kode_barang')
            ->join('warna', 'detail_barang.kode_warna', '=', 'warna.kode_warna')
            ->where('detail_barang.stok', '<', 10)
            ->where('barang.is_active', 1)
            ->select(
                'barang.nama_barang',
                'warna.warna',
                'detail_barang.ukuran',
                'detail_barang.stok'
            )
            ->orderBy('detail_barang.stok', 'asc')
            ->limit(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalPelanggan',
            'totalReseller',
            'totalPenjualan',
            'totalOrder',
            'dailySales',
            'topBarang',
            'loyalResellerByTransaction',
            'loyalResellerBySpend',
            'statusTransaksi',
            // 'metodePembayaran',
            'stokMenupis',
            'period',
            'topRatedBarang',
            'totalPelangganBulanIni', 
            'totalResellerBulanIni'
        ));
        
    }
    // Controller method untuk mendapatkan top 10 barang dengan ulasan terbaik
    public function getTopRatedProducts()
    {
        $topRatedBarang = DB::table('barang')
            ->join('ulasan', 'barang.kode_barang', '=', 'ulasan.kode_barang')
            ->select(
                'barang.kode_barang',
                'barang.nama_barang',
                'barang.deskripsi',
                DB::raw('AVG(ulasan.rating) as avg_rating'),
                DB::raw('COUNT(ulasan.id) as total_ulasan'),
                DB::raw('MAX(ulasan.rating) as max_rating'),
                DB::raw('MIN(ulasan.rating) as min_rating')
            )
            ->where('barang.is_active', 1)
            ->groupBy('barang.kode_barang', 'barang.nama_barang', 'barang.deskripsi')
            ->having('total_ulasan', '>=', 1) // Minimal 1 ulasan
            ->orderBy('avg_rating', 'DESC')
            ->orderBy('total_ulasan', 'DESC') // Sebagai tie-breaker
            ->limit(10)
            ->get();

        return $topRatedBarang;
    }

    public function getCustomerGrowth(Request $request)
    {
        $period = $request->get('period', '7'); // default 7 hari
        
        $startDate = Carbon::now()->subDays($period - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        $customerGrowth = DB::table('pelanggan')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(CASE WHEN role = "pelanggan" THEN 1 END) as pelanggan'),
                DB::raw('COUNT(CASE WHEN role = "reseller" THEN 1 END) as reseller')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing dates with 0 values
        $dates = [];
        $pelangganData = [];
        $resellerData = [];
        
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('d M');
            
            $existingData = $customerGrowth->firstWhere('date', $dateStr);
            $pelangganData[] = $existingData ? $existingData->pelanggan : 0;
            $resellerData[] = $existingData ? $existingData->reseller : 0;
        }

        return response()->json([
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Pelanggan Baru',
                    'data' => $pelangganData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#3b82f6',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 3,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8
                ],
                [
                    'label' => 'Reseller Baru',
                    'data' => $resellerData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 3,
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => '#10b981',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 3,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8
                ]
            ]
        ]);
    }

    public function getResellerGrowth(Request $request)
    {
        $months = $request->get('months', 6); // default 6 bulan
        
        $monthlyGrowth = DB::table('pelanggan')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(CASE WHEN role = "pelanggan" THEN 1 END) as pelanggan'),
                DB::raw('COUNT(CASE WHEN role = "reseller" THEN 1 END) as reseller')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths($months - 1)->startOfMonth())
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $pelangganData = [];
        $resellerData = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $monthData = $monthlyGrowth->first(function ($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            
            $pelangganData[] = $monthData ? $monthData->pelanggan : 0;
            $resellerData[] = $monthData ? $monthData->reseller : 0;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pelanggan',
                    'data' => $pelangganData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3b82f6',
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                ],
                [
                    'label' => 'Reseller',
                    'data' => $resellerData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => '#10b981',
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                    'borderSkipped' => false,
                ]
            ]
        ]);
    }

    public function getRoleDistribution()
    {
        
        $distribution = DB::table('pelanggan')
            ->select('role', DB::raw('COUNT(*) as count'))
            ->groupBy('role')
            ->get();

        $labels = [];
        $data = [];
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'];
        
        foreach ($distribution as $index => $item) {
            $labels[] = ucfirst($item->role);
            $data[] = $item->count;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                'data' => $data,
                'backgroundColor' => array_slice($colors, 0, count($labels)),
                'borderColor' => '#ffffff',
                'borderWidth' => 3,
                'hoverBorderWidth' => 4,
                'hoverOffset' => 10
            ]
        ]);
    }
}

