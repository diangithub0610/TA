<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengiriman;
use App\Models\DetailPengiriman;
use App\Helpers\GenerateId;

class TransaksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:pelanggan');
    }

    public function index(Request $request)
    {
        $query = Transaksi::with(['detailTransaksi.detailBarang.barang', 'pembayaran'])
            ->where('id_pelanggan', auth()->guard('pelanggan')->id());

        // Filter berdasarkan status jika ada
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transaksi = $query->orderBy('tanggal_transaksi', 'desc')->paginate(10);

        return view('pelanggan.transaksi.daftar-pesanan', compact('transaksi'));
    }

    public function detail($kodeTransaksi)
    {
        $transaksi = Transaksi::with([
            'detailTransaksi.detailBarang.barang',
            'detailTransaksi.detailBarang.warna',
            'pelanggan',
            'alamat',
            'pembayaran',
            'pengiriman.detailPengiriman'
        ])
            ->where('id_pelanggan', auth()->guard('pelanggan')->id())
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        return view('pelanggan.transaksi.detail-transaksi', compact('transaksi'));
    }

    public function terimaBarang(Request $request, $kodeTransaksi)
    {
        $transaksi = Transaksi::where('id_pelanggan', auth()->guard('pelanggan')->id())
            ->where('kode_transaksi', $kodeTransaksi)
            ->where('status', 'dikirim')
            ->firstOrFail();

        $pengiriman = $transaksi->pengiriman;

        if (!$pengiriman) {
            return redirect()->back()->with('error', 'Data pengiriman tidak ditemukan');
        }

        // Update status transaksi
        $transaksi->update([
            'status' => 'selesai'
        ]);

        // Update status pengiriman
        $pengiriman->update([
            'status' => 'terkirim',
            'tanggal_tiba' => now()
        ]);

        // Tambahkan detail pengiriman
        DetailPengiriman::create([
            // 'id_detail_pengiriman' => GenerateId::detailPengiriman(),
            'kode_pengiriman' => $pengiriman->kode_pengiriman,
            'lokasi' => 'Alamat Penerima',
            'waktu_update' => now(),
            'keterangan' => 'Barang telah diterima oleh pelanggan'
        ]);

        return redirect()->back()->with('success', 'Pesanan telah selesai, terima kasih!');
    }

    public function batalkan(Request $request, $kodeTransaksi)
    {
        $transaksi = Transaksi::where('id_pelanggan', auth()->guard('pelanggan')->id())
            ->where('kode_transaksi', $kodeTransaksi)
            ->whereIn('status', ['belum_dibayar', 'menunggu_konfirmasi'])
            ->firstOrFail();

        // Update status transaksi
        $transaksi->update([
            'status' => 'dibatalkan'
        ]);

        // Update status pembayaran jika ada
        if ($transaksi->pembayaran) {
            $transaksi->pembayaran->update([
                'status' => 'gagal'
            ]);
        }

        // Kembalikan stok
        foreach ($transaksi->detailTransaksi as $detail) {
            $detail->detailBarang->increment('stok', $detail->kuantitas);
        }

        return redirect()->back()->with('success', 'Pesanan berhasil dibatalkan');
    }
}
