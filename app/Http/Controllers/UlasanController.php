<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ulasan;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UlasanController extends Controller
{
    public function create($kode_transaksi)
    {
        // Ambil transaksi berdasarkan kode transaksi
        $transaksi = Transaksi::where('kode_transaksi', $kode_transaksi)->first();
        
        if (!$transaksi) {
            return redirect()->back()->with('error', 'Transaksi tidak ditemukan');
        }

        // Ambil barang-barang unik dari detail transaksi
        $barangList = DB::table('detail_transaksi')
            ->join('detail_barang', 'detail_transaksi.kode_detail', '=', 'detail_barang.kode_detail')
            ->join('barang', 'detail_barang.kode_barang', '=', 'barang.kode_barang')
            ->where('detail_transaksi.kode_transaksi', $kode_transaksi)
            ->select('barang.kode_barang', 'barang.nama_barang', 'barang.gambar')
            ->distinct()
            ->get();

            $user = Auth::guard('pelanggan')->user();
            $id_pelanggan = $user->id_pelanggan;
// dd($id_pelanggan);
        // Cek ulasan yang sudah ada untuk pelanggan ini
        $existingReviews = Ulasan::where('id_pelanggan',$id_pelanggan)
            ->whereIn('kode_barang', $barangList->pluck('kode_barang'))
            ->pluck('kode_barang')
            ->toArray();

        return view('pelanggan.transaksi.ulasan', compact('transaksi', 'barangList', 'existingReviews', 'kode_transaksi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_transaksi' => 'required|exists:transaksi,kode_transaksi',
            'reviews' => 'required|array',
            'reviews.*.kode_barang' => 'required|exists:barang,kode_barang',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
            'reviews.*.komentar' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            foreach ($request->reviews as $review) {
                // Cek jika ulasan sudah ada untuk kombinasi pelanggan dan barang ini
                $existingReview = Ulasan::where('id_pelanggan', Auth::id())
                    ->where('kode_barang', $review['kode_barang'])
                    ->first();

                    $user = Auth::guard('pelanggan')->user();
                    $id_pelanggan = $user->id_pelanggan;
                if (!$existingReview) {
                    Ulasan::create([
                        'id_pelanggan' => $id_pelanggan,
                        'kode_barang' => $review['kode_barang'],
                        'kode_transaksi' => $request->kode_transaksi,
                        'rating' => $review['rating'],
                        'komentar' => $review['komentar'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Ulasan berhasil disimpan!');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menyimpan ulasan: ' . $e->getMessage());
        }
    }
}