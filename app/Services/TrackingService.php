<?php

namespace App\Services;

use App\Models\Pengiriman;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TrackingService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
        $this->baseUrl = config('services.rajaongkir.url');
    }

    /**
     * Membuat record pengiriman otomatis setelah pembayaran sukses
     */
    public function buatPengirimanOtomatis($kodeTransaksi)
    {
        try {
            $transaksi = Transaksi::where('kode_transaksi', $kodeTransaksi)->first();

            if (!$transaksi) {
                Log::error('Transaksi tidak ditemukan untuk kode: ' . $kodeTransaksi);
                return false;
            }

            // Cek apakah sudah ada record pengiriman
            $pengirimanExist = Pengiriman::where('kode_transaksi', $kodeTransaksi)->first();
            if ($pengirimanExist) {
                Log::info('Pengiriman sudah ada untuk transaksi: ' . $kodeTransaksi);
                return $pengirimanExist;
            }

            // Hitung estimasi tiba berdasarkan estimasi waktu dari checkout
            $estimasiTiba = $this->hitungEstimasiTiba($transaksi->estimasi_waktu);

            $pengiriman = Pengiriman::create([
                'kode_transaksi' => $kodeTransaksi,
                'ekspedisi' => $transaksi->ekspedisi,
                'layanan_ekspedisi' => $transaksi->layanan_ekspedisi,
                'status_pengiriman' => 'menunggu_pengiriman',
                'estimasi_tiba' => $estimasiTiba,
                'catatan_pengiriman' => 'Pengiriman dibuat otomatis setelah pembayaran berhasil'
            ]);

            Log::info('Pengiriman berhasil dibuat otomatis', [
                'id_pengiriman' => $pengiriman->id_pengiriman,
                'kode_transaksi' => $kodeTransaksi
            ]);

            return $pengiriman;
        } catch (\Exception $e) {
            Log::error('Error membuat pengiriman otomatis: ' . $e->getMessage(), [
                'kode_transaksi' => $kodeTransaksi,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Update nomor resi dan mulai tracking
     */
    public function updateNomorResi($idPengiriman, $nomorResi)
    {
        try {
            $pengiriman = Pengiriman::find($idPengiriman);

            if (!$pengiriman) {
                return ['success' => false, 'message' => 'Pengiriman tidak ditemukan'];
            }

            $pengiriman->update([
                'nomor_resi' => $nomorResi,
                'status_pengiriman' => 'dikemas',
                'tanggal_pengiriman' => now()
            ]);

            // Langsung coba tracking pertama kali
            $this->updateTrackingTunggal($pengiriman);

            return ['success' => true, 'message' => 'Nomor resi berhasil diupdate'];
        } catch (\Exception $e) {
            Log::error('Error update nomor resi: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal update nomor resi'];
        }
    }

    /**
     * Update tracking untuk semua pengiriman yang aktif
     */
    public function updateSemuaTracking()
    {
        $pengirimanAktif = Pengiriman::whereNotNull('nomor_resi')
            ->whereNotIn('status_pengiriman', ['terkirim', 'gagal_kirim'])
            ->get();

        $berhasil = 0;
        $gagal = 0;

        foreach ($pengirimanAktif as $pengiriman) {
            $result = $this->updateTrackingTunggal($pengiriman);

            if ($result['success']) {
                $berhasil++;
            } else {
                $gagal++;
            }

            // Delay untuk menghindari rate limiting
            sleep(1);
        }

        Log::info('Update tracking selesai', [
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'total' => $pengirimanAktif->count()
        ]);

        return [
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'total' => $pengirimanAktif->count()
        ];
    }

    /**
     * Update tracking untuk satu pengiriman
     */
    public function updateTrackingTunggal($pengiriman)
    {
        try {
            if (empty($pengiriman->nomor_resi)) {
                return ['success' => false, 'message' => 'Nomor resi kosong'];
            }

            $trackingData = $this->getTrackingFromAPI($pengiriman->nomor_resi, $pengiriman->ekspedisi);

            if (!$trackingData['success']) {
                return $trackingData;
            }

            // Update data tracking
            $pengiriman->updateTracking($trackingData['data']);

            // Update status transaksi jika sudah terkirim
            if ($pengiriman->isSelesai()) {
                $pengiriman->transaksi->update(['status' => 'selesai']);
            }

            return ['success' => true, 'message' => 'Tracking berhasil diupdate'];
        } catch (\Exception $e) {
            Log::error('Error update tracking tunggal: ' . $e->getMessage(), [
                'id_pengiriman' => $pengiriman->id_pengiriman
            ]);

            return ['success' => false, 'message' => 'Gagal update tracking'];
        }
    }

    /**
     * Ambil data tracking dari API Raja Ongkir
     */
    private function getTrackingFromAPI($nomorResi, $ekspedisi)
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->post($this->baseUrl . '/waybill', [
                'waybill' => $nomorResi,
                'courier' => strtolower($ekspedisi)
            ]);

            if (!$response->successful()) {
                Log::error('API Raja Ongkir error', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return ['success' => false, 'message' => 'API Raja Ongkir error'];
            }

            $data = $response->json();

            if (isset($data['rajaongkir']['status']['code']) && $data['rajaongkir']['status']['code'] != 200) {
                return ['success' => false, 'message' => $data['rajaongkir']['status']['description']];
            }

            $result = $data['rajaongkir']['result'];

            if (empty($result['manifest'])) {
                return ['success' => false, 'message' => 'Data tracking tidak ditemukan'];
            }

            // Ambil tracking terbaru
            $latestManifest = end($result['manifest']);

            return [
                'success' => true,
                'data' => [
                    'tanggal' => now(),
                    'status' => $this->normalizeStatus($latestManifest['manifest_description']),
                    'keterangan' => $latestManifest['manifest_description'],
                    'kota' => $latestManifest['city_name'] ?? '',
                    'ekspedisi_response' => $result
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error get tracking from API: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Gagal mengambil data tracking'];
        }
    }

    /**
     * Normalisasi status dari berbagai ekspedisi
     */
    private function normalizeStatus($description)
    {
        $description = strtolower($description);

        if (strpos($description, 'terkirim') !== false || strpos($description, 'delivered') !== false) {
            return 'DELIVERED';
        } elseif (strpos($description, 'diantar') !== false || strpos($description, 'out for delivery') !== false) {
            return 'OUT_FOR_DELIVERY';
        } elseif (strpos($description, 'transit') !== false || strpos($description, 'perjalanan') !== false) {
            return 'IN_TRANSIT';
        } elseif (strpos($description, 'diterima') !== false || strpos($description, 'received') !== false) {
            return 'SHIPMENT_RECEIVED';
        } elseif (strpos($description, 'pickup') !== false || strpos($description, 'diambil') !== false) {
            return 'PICKED_UP';
        }

        return 'IN_TRANSIT'; // default
    }

    /**
     * Hitung estimasi tiba berdasarkan string estimasi
     */
    private function hitungEstimasiTiba($estimasiWaktu)
    {
        try {
            // Parse estimasi waktu seperti "1-2 hari", "3-4 HARI", dll
            preg_match('/(\d+)(?:-(\d+))?\s*hari/i', $estimasiWaktu, $matches);

            if (!empty($matches)) {
                $hariMin = (int) $matches[1];
                $hariMax = isset($matches[2]) ? (int) $matches[2] : $hariMin;

                // Ambil rata-rata atau maksimum hari
                $estimasiHari = $hariMax;

                return now()->addDays($estimasiHari);
            }

            // Default 3 hari jika tidak bisa parse
            return now()->addDays(3);
        } catch (\Exception $e) {
            return now()->addDays(3);
        }
    }

    /**
     * Mendapatkan pengiriman berdasarkan kode transaksi
     */
    public function getPengirimanByTransaksi($kodeTransaksi)
    {
        return Pengiriman::where('kode_transaksi', $kodeTransaksi)->first();
    }

    /**
     * Mendapatkan pengiriman dengan tracking lengkap
     */
    public function getPengirimanDenganTracking($idPengiriman)
    {
        $pengiriman = Pengiriman::with('transaksi')->find($idPengiriman);

        if (!$pengiriman) {
            return null;
        }

        return [
            'pengiriman' => $pengiriman,
            'tracking_history' => $pengiriman->riwayat_tracking ?? [],
            'status_text' => $pengiriman->status_pengiriman_text,
            'estimasi_tiba' => $pengiriman->estimasi_tiba,
            'sudah_sampai' => $pengiriman->sudah_sampai
        ];
    }
}
