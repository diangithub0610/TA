<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use Laravel\Sanctum\Guard;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminPesananController extends Controller
{
      public function index(Request $request)
    {
        $query = Transaksi::with(['pelanggan', 'detailTransaksi.detailBarang.barang'])
                          ->orderBy('tanggal_transaksi', 'desc');

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // Default tampilkan pesanan baru (belum dibayar dan menunggu konfirmasi)
            $query->whereIn('status', ['belum_dibayar', 'menunggu_konfirmasi']);
        }

        // Filter berdasarkan tanggal
        if ($request->has('tanggal_dari') && $request->tanggal_dari != '') {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_dari);
        }

        if ($request->has('tanggal_sampai') && $request->tanggal_sampai != '') {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_sampai);
        }

        $transaksi = $query->paginate(10);

        return view('admin.shopkeeper.index', compact('transaksi'));
    }
    public function show($kode_transaksi)
    {
        $transaksi = Transaksi::with([
            'pelanggan','pengiriman','detailTransaksi.detailBarang.barang'
        ])->where('kode_transaksi', $kode_transaksi)->firstOrFail();

        return view('admin.shopkeeper.show', compact('transaksi'));
    }

    public function terima(Request $request, $kode_transaksi)
    {
        try {
            $transaksi = Transaksi::findOrFail($kode_transaksi);
            
            if ($transaksi->status == 'menunggu_konfirmasi') {
                $transaksi->update([
                    'status' => 'diproses',
                    'id_pengguna' => auth()->user()->id ?? null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil diterima dan sedang diproses'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak valid untuk diterima'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function tolak(Request $request, $kode_transaksi)
    {
        $request->validate([
            'alasan' => 'required|string|max:255'
        ]);

        try {
            $transaksi = Transaksi::findOrFail($kode_transaksi);
            
            if (in_array($transaksi->status, ['belum_dibayar', 'menunggu_konfirmasi'])) {
                $transaksi->update([
                    'status' => 'dibatalkan',
                    'keterangan' => $request->alasan,
                    'id_pengguna' => auth()->user()->id ?? null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil ditolak'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Status pesanan tidak valid untuk ditolak'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStatus(Request $request, $kode_transaksi)
    {
        $request->validate([
            'status' => 'required|in:diproses,dikirim,selesai'
        ]);

        try {
            $transaksi = Transaksi::findOrFail($kode_transaksi);
            
            $transaksi->update([
                'status' => $request->status,
                'id_pengguna' => auth()->user()->id ?? null
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status pesanan berhasil diupdate'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
    public function invoice($kode_transaksi)
    {
        $transaksi = Transaksi::with([
            'pelanggan',
            'detailTransaksi.detailBarang.barang'
        ])->where('kode_transaksi', $kode_transaksi)->firstOrFail();

        return view('admin.transaksi.invoice', compact('transaksi'));
    }
    public function statistics()
    {
        $stats = [
            'total_pesanan' => Transaksi::count(),
            'pesanan_baru' => Transaksi::whereIn('status', ['belum_dibayar', 'menunggu_konfirmasi'])->count(),
            'dalam_proses' => Transaksi::where('status', 'diproses')->count(),
            'dikirim' => Transaksi::where('status', 'dikirim')->count(),
            'selesai' => Transaksi::where('status', 'selesai')->count(),
            'dibatalkan' => Transaksi::where('status', 'dibatalkan')->count(),
            'total_penjualan' => Transaksi::where('status', 'selesai')->sum('total_harga'),
            'penjualan_bulan_ini' => Transaksi::where('status', 'selesai')
                ->whereMonth('tanggal_transaksi', now()->month)
                ->whereYear('tanggal_transaksi', now()->year)
                ->sum('total_harga')
        ];

        return response()->json($stats);
    }
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'kode_transaksi' => 'required|array',
            'kode_transaksi.*' => 'exists:transaksi,kode_transaksi',
            'status' => 'required|in:diproses,dikirim,selesai,dibatalkan'
        ]);

        try {
            DB::beginTransaction();

            $affected = Transaksi::whereIn('kode_transaksi', $request->kode_transaksi)
                ->update(['status' => $request->status]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memperbarui {$affected} pesanan"
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
    

