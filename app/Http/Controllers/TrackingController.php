<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Pengiriman;
use App\Services\TrackingService;

class TrackingController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Menampilkan detail tracking pengiriman
     */
    public function show($kodeTransaksi)
    {
        $pelanggan = auth()->guard('pelanggan')->user();

        $transaksi = Transaksi::with(['pengiriman', 'pembayaran', 'alamat', 'detailTransaksi.detailBarang.barang'])
            ->where('kode_transaksi', $kodeTransaksi)
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->firstOrFail();

        $trackingData = null;
        if ($transaksi->pengiriman) {
            $trackingData = $this->trackingService->getPengirimanDenganTracking($transaksi->pengiriman->id_pengiriman);
        }

        return view('customer.tracking.show', compact('transaksi', 'trackingData'));
    }

    /**
     * API endpoint untuk get tracking data (AJAX)
     */
    public function getTrackingData($kodeTransaksi)
    {
        try {
            $pelanggan = auth()->guard('pelanggan')->user();

            $transaksi = Transaksi::with('pengiriman')
                ->where('kode_transaksi', $kodeTransaksi)
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->firstOrFail();

            if (!$transaksi->pengiriman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pengiriman tidak ditemukan'
                ]);
            }

            $trackingData = $this->trackingService->getPengirimanDenganTracking($transaksi->pengiriman->id_pengiriman);

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $trackingData['pengiriman']->status_pengiriman,
                    'status_text' => $trackingData['status_text'],
                    'nomor_resi' => $trackingData['pengiriman']->nomor_resi,
                    'ekspedisi' => $trackingData['pengiriman']->ekspedisi,
                    'layanan' => $trackingData['pengiriman']->layanan_ekspedisi,
                    'estimasi_tiba' => $trackingData['estimasi_tiba']?->format('d M Y'),
                    'sudah_sampai' => $trackingData['sudah_sampai'],
                    'tracking_history' => $trackingData['tracking_history'],
                    'terakhir_update' => $trackingData['pengiriman']->terakhir_update_tracking?->format('d M Y H:i')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data tracking'
            ], 500);
        }
    }

    /**
     * Refresh tracking data untuk satu pengiriman
     */
    public function refreshTracking($kodeTransaksi)
    {
        try {
            $pelanggan = auth()->guard('pelanggan')->user();

            $transaksi = Transaksi::with('pengiriman')
                ->where('kode_transaksi', $kodeTransaksi)
                ->where('id_pelanggan', $pelanggan->id_pelanggan)
                ->firstOrFail();

            if (!$transaksi->pengiriman || !$transaksi->pengiriman->nomor_resi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor resi belum tersedia'
                ]);
            }

            // Cek apakah sudah diupdate dalam 10 menit terakhir untuk menghindari spam
            $terakhirUpdate = $transaksi->pengiriman->terakhir_update_tracking;
            if ($terakhirUpdate && $terakhirUpdate->diffInMinutes(now()) < 10) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking baru saja diupdate. Silakan tunggu beberapa menit.'
                ]);
            }

            $result = $this->trackingService->updateTrackingTunggal($transaksi->pengiriman);

            if ($result['success']) {
                // Ambil data tracking terbaru
                $trackingData = $this->trackingService->getPengirimanDenganTracking($transaksi->pengiriman->id_pengiriman);

                return response()->json([
                    'success' => true,
                    'message' => 'Tracking berhasil diupdate',
                    'data' => [
                        'status' => $trackingData['pengiriman']->status_pengiriman,
                        'status_text' => $trackingData['status_text'],
                        'tracking_history' => $trackingData['tracking_history'],
                        'terakhir_update' => $trackingData['pengiriman']->terakhir_update_tracking?->format('d M Y H:i')
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat refresh tracking'
            ], 500);
        }
    }

    /**
     * Public tracking - bisa diakses tanpa login dengan nomor resi
     */
    public function publicTracking(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'nomor_resi' => 'required|string',
                'ekspedisi' => 'required|string'
            ]);

            $pengiriman = Pengiriman::with(['transaksi', 'transaksi.pelanggan'])
                ->where('nomor_resi', $request->nomor_resi)
                ->where('ekspedisi', strtoupper($request->ekspedisi))
                ->first();

            if (!$pengiriman) {
                return redirect()->back()
                    ->with('error', 'Data pengiriman tidak ditemukan')
                    ->withInput();
            }

            $trackingData = $this->trackingService->getPengirimanDenganTracking($pengiriman->id_pengiriman);

            return view('customer.tracking.public-result', compact('pengiriman', 'trackingData'));
        }

        return view('customer.tracking.public');
    }
}
