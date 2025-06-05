<?php

namespace App\Http\Controllers;

use App\Models\Ulasan;
use App\Models\Barang;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UlasanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Menampilkan ulasan untuk barang tertentu
    public function index($kodeBarang)
    {
        $barang = Barang::where('kode_barang', $kodeBarang)->firstOrFail();
        
        $ulasan = Ulasan::with(['user'])
                        ->byBarang($kodeBarang)
                        ->orderBy('tanggal_review', 'desc')
                        ->paginate(10);

        $averageRating = Ulasan::averageRatingByBarang($kodeBarang);
        $totalUlasan = Ulasan::totalUlasanByBarang($kodeBarang);
        
        // Cek apakah user sudah pernah membeli barang ini
        $hasPurchased = false;
        $canReview = false;
        
        if (Auth::check()) {
            $hasPurchased = Ulasan::hasUserPurchasedBarang(Auth::id(), $kodeBarang);
            $hasReviewed = Ulasan::hasUserReviewedBarang(Auth::id(), $kodeBarang);
            $canReview = $hasPurchased && !$hasReviewed;
        }

        return view('pelanggan.ulasan.index', compact(
            'barang', 
            'ulasan', 
            'averageRating', 
            'totalUlasan', 
            'canReview',
            'hasPurchased'
        ));
    }

    // Form untuk membuat ulasan baru
    public function create($kodeBarang)
    {
        $barang = Barang::where('kode_barang', $kodeBarang)->firstOrFail();
        
        // Cek apakah user sudah membeli barang ini
        if (!Ulasan::hasUserPurchasedBarang(Auth::id(), $kodeBarang)) {
            return redirect()->back()->with('error', 'Anda harus membeli barang ini terlebih dahulu untuk dapat memberikan ulasan.');
        }

        // Cek apakah user sudah memberikan ulasan
        if (Ulasan::hasUserReviewedBarang(Auth::id(), $kodeBarang)) {
            return redirect()->back()->with('error', 'Anda sudah memberikan ulasan untuk barang ini.');
        }

        // Ambil transaksi user untuk barang ini
        $transaksi = DB::table('transaksi')
                      ->join('detail_transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
                      ->where('transaksi.user_id', Auth::id())
                      ->where('detail_transaksi.kode_barang', $kodeBarang)
                      ->where('transaksi.status', 'selesai')
                      ->select('transaksi.id')
                      ->first();

        return view('ulasan.create', compact('barang', 'transaksi'));
    }

    // Menyimpan ulasan baru
    public function store(Request $request)
    {
        $request->validate([
            'kode_barang' => 'required|exists:barang,kode_barang',
            'transaksi_id' => 'required|exists:transaksi,id',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        // Cek lagi apakah user sudah membeli barang ini
        if (!Ulasan::hasUserPurchasedBarang(Auth::id(), $request->kode_barang)) {
            return redirect()->back()->with('error', 'Anda harus membeli barang ini terlebih dahulu.');
        }

        // Cek apakah user sudah memberikan ulasan
        if (Ulasan::hasUserReviewedBarang(Auth::id(), $request->kode_barang)) {
            return redirect()->back()->with('error', 'Anda sudah memberikan ulasan untuk barang ini.');
        }

        Ulasan::create([
            'user_id' => Auth::id(),
            'kode_barang' => $request->kode_barang,
            'transaksi_id' => $request->transaksi_id,
            'nama_reviewer' => Auth::user()->name,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
            'tanggal_review' => now(),
        ]);

        return redirect()->route('ulasan.index', $request->kode_barang)
                        ->with('success', 'Ulasan berhasil ditambahkan!');
    }

    // Menampilkan form edit ulasan
    public function edit($id)
    {
        $ulasan = Ulasan::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        return view('ulasan.edit', compact('ulasan'));
    }

    // Update ulasan
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:1000',
        ]);

        $ulasan = Ulasan::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        $ulasan->update([
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return redirect()->route('ulasan.index', $ulasan->kode_barang)
                        ->with('success', 'Ulasan berhasil diperbarui!');
    }

    // Hapus ulasan
    public function destroy($id)
    {
        $ulasan = Ulasan::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        $kodeBarang = $ulasan->kode_barang;
        $ulasan->delete();

        return redirect()->route('ulasan.index', $kodeBarang)
                        ->with('success', 'Ulasan berhasil dihapus!');
    }

    // AJAX untuk mendapatkan ulasan (untuk tab)
    public function getUlasanByBarang($kodeBarang)
    {
        $ulasan = Ulasan::with(['user'])
                        ->byBarang($kodeBarang)
                        ->orderBy('tanggal_review', 'desc')
                        ->get();

        $averageRating = Ulasan::averageRatingByBarang($kodeBarang);
        $totalUlasan = Ulasan::totalUlasanByBarang($kodeBarang);

        return response()->json([
            'ulasan' => $ulasan,
            'average_rating' => round($averageRating, 1),
            'total_ulasan' => $totalUlasan
        ]);
    }
}