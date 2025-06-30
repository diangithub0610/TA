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

            // Ambil riwayat tracking yang sudah ada
            $existingTracking = [];
            if (!empty($pengiriman->riwayat_tracking)) {
                $existingTracking = is_string($pengiriman->riwayat_tracking)
                    ? json_decode($pengiriman->riwayat_tracking, true)
                    : $pengiriman->riwayat_tracking;
            }

            // Ambil manifest baru dari API
            $newManifest = $trackingData['data']['full_manifest'] ?? [];

            // Cari data terbaru yang belum ada
            $latestEntries = $this->getNewTrackingEntries($existingTracking, $newManifest);

            if (empty($latestEntries)) {
                return ['success' => true, 'message' => 'Tidak ada update tracking baru', 'data' => $trackingData['data']];
            }

            // Gabungkan dengan data yang sudah ada
            $updatedTracking = array_merge($existingTracking, $latestEntries);

            // Update data tracking
            $pengiriman->updateTracking($updatedTracking);

            // Update status transaksi jika sudah terkirim
            if ($this->isPackageDelivered($latestEntries)) {
                $pengiriman->transaksi->update(['status' => 'selesai']);
            }

            return [
                'success' => true,
                'message' => count($latestEntries) . ' update tracking baru ditambahkan',
                'data' => array_merge($trackingData['data'], ['new_entries' => $latestEntries])
            ];
        } catch (\Exception $e) {
            Log::error('Error update tracking tunggal: ' . $e->getMessage(), [
                'id_pengiriman' => $pengiriman->id_pengiriman
            ]);

            return ['success' => false, 'message' => 'Gagal update tracking: ' . $e->getMessage()];
        }
    }

    private function getNewTrackingEntries($existingTracking, $newManifest)
    {
        $newEntries = [];

        foreach ($newManifest as $manifest) {
            // Create unique identifier untuk setiap entry
            $manifestId = $manifest['manifest_date'] . '_' . $manifest['manifest_time'] . '_' . $manifest['manifest_code'];

            // Cek apakah entry ini sudah ada
            $exists = false;
            foreach ($existingTracking as $existing) {
                $existingId = ($existing['manifest_date'] ?? '') . '_' .
                    ($existing['manifest_time'] ?? '') . '_' .
                    ($existing['manifest_code'] ?? '');

                if ($manifestId === $existingId) {
                    $exists = true;
                    break;
                }
            }

            // Jika belum ada dan ada informasi lokasi, tambahkan
            if (!$exists && !empty($manifestId)) {
                // Format lokasi
                $cityName = $manifest['city_name'] ?? '';
                $cityName = trim(str_replace([' - ', ' -', '- '], '', $cityName));

                $newEntries[] = [
                    'manifest_code' => $manifest['manifest_code'] ?? '',
                    'manifest_description' => $manifest['manifest_description'] ?? '',
                    'manifest_date' => $manifest['manifest_date'] ?? '',
                    'manifest_time' => $manifest['manifest_time'] ?? '',
                    'city_name' => $cityName,
                    'datetime' => $manifest['manifest_date'] . ' ' . $manifest['manifest_time'],
                    'created_at' => now()->toDateTimeString()
                ];
            }
        }

        // Urutkan berdasarkan datetime
        usort($newEntries, function ($a, $b) {
            return strtotime($a['datetime']) - strtotime($b['datetime']);
        });

        return $newEntries;
    }

    private function isPackageDelivered($trackingEntries)
    {
        foreach ($trackingEntries as $entry) {
            $description = strtolower($entry['manifest_description'] ?? '');
            if (
                strpos($description, 'terkirim') !== false ||
                strpos($description, 'delivered') !== false ||
                strpos($description, 'diterima') !== false
            ) {
                return true;
            }
        }
        return false;
    }

    public function getPengirimanDenganTracking($idPengiriman)
    {
        $pengiriman = Pengiriman::with('transaksi')->find($idPengiriman);

        if (!$pengiriman) {
            return null;
        }

        // Ambil tracking history dari database
        $trackingHistory = [];

        if (!empty($pengiriman->riwayat_tracking)) {
            $riwayatData = is_string($pengiriman->riwayat_tracking)
                ? json_decode($pengiriman->riwayat_tracking, true)
                : $pengiriman->riwayat_tracking;

            if (is_array($riwayatData)) {
                $trackingHistory = $riwayatData;
            }
        }

        // Urutkan berdasarkan datetime (terbaru di atas)
        usort($trackingHistory, function ($a, $b) {
            $dateA = strtotime(($a['manifest_date'] ?? '') . ' ' . ($a['manifest_time'] ?? ''));
            $dateB = strtotime(($b['manifest_date'] ?? '') . ' ' . ($b['manifest_time'] ?? ''));
            return $dateB - $dateA; // Descending order
        });

        return [
            'pengiriman' => $pengiriman,
            'tracking_history' => $trackingHistory,
            'status_text' => $pengiriman->status_pengiriman_text,
            'estimasi_tiba' => $pengiriman->estimasi_tiba,
            'sudah_sampai' => $pengiriman->sudah_sampai
        ];
    }

    private function getTrackingFromAPI($nomorResi, $ekspedisi)
    {
        try {
            // Normalisasi nama ekspedisi ke kode yang benar
            $courierCode = $this->normalizeCourierCode($ekspedisi);

            // Method 1: POST dengan query parameters di URL (sesuai dokumentasi)
            $url = $this->baseUrl . '/track/waybill?' . http_build_query([
                'awb' => $nomorResi,
                'courier' => $courierCode
            ]);

            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->post($url);

            // Log untuk debugging
            Log::info('Raja Ongkir Tracking Request', [
                'url' => $url,
                'awb' => $nomorResi,
                'courier' => $courierCode,
                'original_ekspedisi' => $ekspedisi,
                'status' => $response->status(),
                'headers' => $response->headers()
            ]);

            if (!$response->successful()) {
                // Jika method 1 gagal, coba method 2: POST dengan form data
                Log::info('Method 1 failed, trying method 2 with form data');

                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->asForm()->post($this->baseUrl . '/track/waybill', [
                    'awb' => $nomorResi,
                    'courier' => $courierCode
                ]);

                Log::info('Raja Ongkir Tracking Request Method 2', [
                    'url' => $this->baseUrl . '/track/waybill',
                    'awb' => $nomorResi,
                    'courier' => $courierCode,
                    'status' => $response->status()
                ]);
            }

            if (!$response->successful()) {
                // Jika method 2 juga gagal, coba method 3: POST dengan JSON body
                Log::info('Method 2 failed, trying method 3 with JSON');

                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])->post($this->baseUrl . '/track/waybill', [
                    'awb' => $nomorResi,
                    'courier' => $courierCode
                ]);

                Log::info('Raja Ongkir Tracking Request Method 3', [
                    'url' => $this->baseUrl . '/track/waybill',
                    'awb' => $nomorResi,
                    'courier' => $courierCode,
                    'status' => $response->status()
                ]);
            }

            if (!$response->successful()) {
                Log::error('API Raja Ongkir tracking error - All methods failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'awb' => $nomorResi,
                    'courier' => $courierCode,
                    'original_ekspedisi' => $ekspedisi
                ]);
                return ['success' => false, 'message' => 'API Raja Ongkir error: ' . $response->status() . ' - ' . $response->body()];
            }

            $data = $response->json();

            // Log response untuk debugging
            Log::info('Raja Ongkir Tracking Response', ['data' => $data]);

            // Cek struktur response sesuai dokumentasi
            if (isset($data['meta']['code']) && $data['meta']['code'] != 200) {
                return ['success' => false, 'message' => $data['meta']['message'] ?? 'Unknown error'];
            }

            $result = $data['data'] ?? null;

            if (!$result) {
                return ['success' => false, 'message' => 'Data tracking tidak ditemukan'];
            }

            // Cek apakah ada manifest data
            if (empty($result['manifest'])) {
                return ['success' => false, 'message' => 'Data tracking tidak tersedia'];
            }

            // Ambil tracking terbaru (manifest biasanya array dengan urutan terbaru di akhir)
            $latestManifest = end($result['manifest']);

            return [
                'success' => true,
                'data' => [
                    'awb' => $result['awb'] ?? $nomorResi,
                    'courier' => $result['courier'] ?? $ekspedisi,
                    'status' => $this->normalizeStatus($latestManifest['description'] ?? ''),
                    'keterangan' => $latestManifest['description'] ?? '',
                    'tanggal' => $latestManifest['date'] ?? now()->format('Y-m-d H:i:s'),
                    'kota' => $latestManifest['city'] ?? '',
                    'full_manifest' => $result['manifest'], // Simpan semua history tracking
                    'ekspedisi_response' => $result
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error get tracking from API: ' . $e->getMessage(), [
                'awb' => $nomorResi,
                'courier' => $ekspedisi,
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Gagal mengambil data tracking: ' . $e->getMessage()];
        }
    }

    // Method untuk normalisasi kode kurir
    private function normalizeCourierCode($ekspedisi)
    {
        $courierMap = [
            'J&T Express' => 'jnt',
            'JNT' => 'jnt',
            'J&T' => 'jnt',
            'JNE' => 'jne',
            'JNE Express' => 'jne',
            'TIKI' => 'tiki',
            'TIKI Express' => 'tiki',
            'POS Indonesia' => 'pos',
            'POS' => 'pos',
            'SiCepat' => 'sicepat',
            'SiCepat Express' => 'sicepat',
            'AnterAja' => 'anteraja',
            'SAP Express' => 'sap',
            'SAP' => 'sap',
            'Wahana' => 'wahana',
            'Lion Parcel' => 'lion',
            'ID Express' => 'ide',
            'First Logistics' => 'first',
            'Ninja Express' => 'ninja',
            'RPX' => 'rpx',
            'Shopee Express' => 'spx'
        ];

        // Cari mapping yang tepat
        foreach ($courierMap as $key => $value) {
            if (stripos($ekspedisi, $key) !== false || strcasecmp($ekspedisi, $key) === 0) {
                return $value;
            }
        }

        // Jika tidak ditemukan mapping, gunakan lowercase dari input
        return strtolower(str_replace([' ', '&'], ['', ''], $ekspedisi));
    }

    // Alternative method menggunakan query parameters di URL
    public function getTrackingFromAPIAlternative($nomorResi, $ekspedisi)
    {
        try {
            // Sesuai contoh curl di dokumentasi
            $url = $this->baseUrl . '/track/waybill?' . http_build_query([
                'awb' => $nomorResi,
                'courier' => strtolower($ekspedisi)
            ]);

            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->post($url);

            // Log untuk debugging
            Log::info('Raja Ongkir Tracking Request (Alternative)', [
                'url' => $url,
                'status' => $response->status()
            ]);

            if (!$response->successful()) {
                Log::error('API Raja Ongkir tracking error (Alternative)', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'url' => $url
                ]);
                return ['success' => false, 'message' => 'API Raja Ongkir error: ' . $response->status()];
            }

            $data = $response->json();

            // Log response untuk debugging
            Log::info('Raja Ongkir Tracking Response (Alternative)', ['data' => $data]);

            // Cek struktur response
            if (isset($data['meta']['code']) && $data['meta']['code'] != 200) {
                return ['success' => false, 'message' => $data['meta']['message'] ?? 'Unknown error'];
            }

            $result = $data['data'] ?? null;

            if (!$result || empty($result['manifest'])) {
                return ['success' => false, 'message' => 'Data tracking tidak ditemukan'];
            }

            $latestManifest = end($result['manifest']);

            return [
                'success' => true,
                'data' => [
                    'awb' => $result['awb'] ?? $nomorResi,
                    'courier' => $result['courier'] ?? $ekspedisi,
                    'status' => $this->normalizeStatus($latestManifest['description'] ?? ''),
                    'keterangan' => $latestManifest['description'] ?? '',
                    'tanggal' => $latestManifest['date'] ?? now()->format('Y-m-d H:i:s'),
                    'kota' => $latestManifest['city'] ?? '',
                    'full_manifest' => $result['manifest'],
                    'ekspedisi_response' => $result
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error get tracking from API (Alternative): ' . $e->getMessage(), [
                'awb' => $nomorResi,
                'courier' => $ekspedisi
            ]);
            return ['success' => false, 'message' => 'Gagal mengambil data tracking: ' . $e->getMessage()];
        }
    }

    private function normalizeStatus($description)
    {
        $description = strtolower($description);

        if (strpos($description, 'terkirim') !== false || strpos($description, 'delivered') !== false) {
            return 'selesai';
        } elseif (strpos($description, 'transit') !== false || strpos($description, 'perjalanan') !== false) {
            return 'dalam_perjalanan';
        } elseif (strpos($description, 'pickup') !== false || strpos($description, 'diambil') !== false) {
            return 'diambil';
        } else {
            return 'diproses';
        }
    }

    /**
     * Normalisasi status dari berbagai ekspedisi
     */
    // private function normalizeStatus($description)
    // {
    //     $description = strtolower($description);

    //     if (strpos($description, 'terkirim') !== false || strpos($description, 'delivered') !== false) {
    //         return 'DELIVERED';
    //     } elseif (strpos($description, 'diantar') !== false || strpos($description, 'out for delivery') !== false) {
    //         return 'OUT_FOR_DELIVERY';
    //     } elseif (strpos($description, 'transit') !== false || strpos($description, 'perjalanan') !== false) {
    //         return 'IN_TRANSIT';
    //     } elseif (strpos($description, 'diterima') !== false || strpos($description, 'received') !== false) {
    //         return 'SHIPMENT_RECEIVED';
    //     } elseif (strpos($description, 'pickup') !== false || strpos($description, 'diambil') !== false) {
    //         return 'PICKED_UP';
    //     }

    //     return 'IN_TRANSIT'; // default
    // }

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
  
}
