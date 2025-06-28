<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use App\Models\Brand;
use App\Models\Barang;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PelangganController extends Controller
{
    public function beranda()
    {
        $userRole = Auth::guard('pelanggan')->check()
            ? Auth::guard('pelanggan')->user()->role
            : 'guest';

        $brands = Brand::all();

        $barang = Barang::with('detailbarang') // ambil relasi
            ->where('is_active', 1)
            ->get()
            ->map(function ($item) use ($userRole) {
                // Ambil harga termurah berdasarkan role
                $termurah = $item->detailbarang->min(fn($d) => $d->getHargaByRole($userRole));

                $normal = $item->detailbarang->min(fn($d) => $d->harga_normal);

                // Tambahkan properti baru ke koleksi barang
                $item->harga_termurah = 'Rp ' . number_format($termurah, 0, ',', '.');

                $item->harganormal = 'Rp ' . number_format($normal, 0, ',', '.');

                return $item;
            });

        return view('pelanggan.beranda.index', compact('barang', 'brands'));
    }
    public function barang(Request $request)
    {
        $query = Barang::with(['tipe.brand'])
            ->where('is_active', 1);

        if ($request->has('brand') && $request->brand) {
            $query->whereHas('tipe', function ($q) use ($request) {
                $q->where('kode_brand', $request->brand);
            });
        }

        if ($request->has('tipe') && $request->tipe) {
            $query->where('kode_tipe', $request->tipe);
        }

        if ($request->has('cari') && $request->cari) {
            $query->where('nama_barang', 'like', '%' . $request->cari . '%');
        }

        if ($request->has('urutkan')) {
            switch ($request->urutkan) {
                case 'terbaru':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'harga-terendah':
                    $query->orderBy('harga_normal', 'asc');
                    break;
                case 'harga-tertinggi':
                    $query->orderBy('harga_normal', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $barang = $query->paginate(12);
        $brands = Brand::all();
        $tipes = Tipe::all();

        return view('pelanggan.barang.index', compact('barang', 'brands', 'tipes'));
    }

    public function detailBarang($kode_barang)
    {
        $barang = Barang::with([
            'tipe.brand',
            'detailBarangs.warna',
            // 'gambarBarang'
        ])->where('kode_barang', $kode_barang)
            ->where('is_active', 1)
            ->firstOrFail();

        $detailByUkuranWarna = [];
        foreach ($barang->detailBarangs as $detail) {
            $detailByUkuranWarna[$detail->ukuran][$detail->kode_warna] = [
                'kode_detail' => $detail->kode_detail,
                'stok' => (int)$detail->stok,
                'warna' => $detail->warna->warna,
                'kode_hex' => $detail->warna->kode_hex,
                'harga_normal' => (int)$detail->harga_normal,
                'potongan_harga' => (int)$detail->potongan_harga,
                'harga_by_role' => (int)$detail->harga_by_role,
                'formatted_harga_by_role' => $detail->formatted_harga_by_role,
            ];
        }

        // Tambahkan informasi role user untuk JavaScript
        $userRole = 'guest';
        if (Auth::guard('pelanggan')->check()) {
            $userRole = Auth::guard('pelanggan')->user()->role;
        }

        $barangTerkait = Barang::with(['tipe.brand'])
            ->where('kode_tipe', $barang->kode_tipe)
            ->where('kode_barang', '!=', $kode_barang)
            ->where('is_active', 1)
            ->take(4)
            ->get();

        // Group detail barang by ukuran dan warna
        $ukuranTersedia = $barang->detailBarangs
            ->where('stok', '>', 0)
            ->pluck('ukuran')
            ->unique()
            ->sort()
            ->values();

        $warnaTersedia = $barang->detailBarangs
            ->where('stok', '>', 0)
            ->pluck('warna')
            ->unique()
            ->values();

        // Mengelompokkan detail barang
        $detailByUkuranWarna = [];
        foreach ($barang->detailBarangs as $detail) {
            $detailByUkuranWarna[$detail->ukuran][$detail->kode_warna] = [
                'kode_detail' => $detail->kode_detail,
                'stok' => (int)$detail->stok,  // Pastikan integer
                'warna' => $detail->warna->warna,
                'kode_hex' => $detail->warna->kode_hex
            ];
        }
        // dd($detailByUkuranWarna);

        // Ambil ulasan terkait barang
        $ulasan = Ulasan::where('kode_barang', $barang->kode_barang)
            ->latest()
            ->paginate(5); // paginasi

        // Hitung rata-rata rating
        $averageRating = Ulasan::where('kode_barang', $barang->kode_barang)->avg('rating') ?? 0;

        // Hitung total ulasan
        $totalUlasan = Ulasan::where('kode_barang', $barang->kode_barang)->count();

        // Cek apakah user bisa memberikan ulasan
        $canReview = false;
        $hasPurchased = false;

        if (Auth::check()) {
            $user = Auth::user();

            // Cek apakah user pernah beli barang ini dan belum pernah mengulas
            $hasPurchased = $user->transaksis()
                ->whereHas('detailTransaksis', function ($query) use ($barang) {
                    $query->where('kode_barang', $barang->kode_barang);
                })
                ->where('status', 'selesai')
                ->exists();

            $hasReviewed = Ulasan::where('kode_barang', $barang->kode_barang)
                ->where('id_pelanggan', $user->id_pelanggan)
                ->exists();

            $canReview = $hasPurchased && !$hasReviewed;
        }


        return view('pelanggan.barang.show', compact(
            'barang',
            'barangTerkait',
            'ukuranTersedia',
            'warnaTersedia',
            'detailByUkuranWarna',
            'ulasan',
            'averageRating',
            'totalUlasan',
            'canReview',
            'hasPurchased',
            'userRole' // Tambahkan ini
        ));
    }
    public function resellerLoyal()
    {
        // Berdasarkan jumlah transaksi
        $byTransaksi = DB::table('transaksi')
            ->join('pelanggan', 'transaksi.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->select('pelanggan.nama_pelanggan', DB::raw('COUNT(*) as total_transaksi'))
            ->where('pelanggan.role', 'reseller')
            ->where('transaksi.status', '!=', 'dibatalkan')
            ->groupBy('transaksi.id_pelanggan')
            ->orderByDesc('total_transaksi')
            ->limit(10)
            ->get();

        // Berdasarkan total spend
        $bySpend = DB::table('transaksi')
            ->join('detail_transaksi', 'transaksi.kode_transaksi', '=', 'detail_transaksi.kode_transaksi')
            ->join('pelanggan', 'transaksi.id_pelanggan', '=', 'pelanggan.id_pelanggan')
            ->select(
                'pelanggan.nama_pelanggan',
                DB::raw('SUM(detail_transaksi.harga * detail_transaksi.kuantitas + transaksi.ongkir) as total_spend')
            )
            ->where('pelanggan.role', 'reseller')
            ->where('transaksi.status', '!=', 'dibatalkan')
            ->groupBy('transaksi.id_pelanggan')
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get();

        return view('admin.reseller-ranking', compact('byTransaksi', 'bySpend'));
    }
}
