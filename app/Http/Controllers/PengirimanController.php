<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Pengiriman;
use Illuminate\Http\Request;
use App\Services\TrackingService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class PengirimanController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }


    /**
     * Menampilkan detail pengiriman
     */
    public function show($id)
    {
        $pengiriman = Pengiriman::with(['transaksi', 'transaksi.pelanggan', 'transaksi.alamat'])
            ->findOrFail($id);

        $trackingData = $this->trackingService->getPengirimanDenganTracking($id);

        return view('admin.shopkeeper.pengiriman', compact('pengiriman', 'trackingData'));
    }

    /**
     * Form untuk update nomor resi
     */
    public function editResi($id)
    {
        $pengiriman = Pengiriman::with('transaksi')->findOrFail($id);
        return view('admin.shopkeeper.edit-resi', compact('pengiriman'));
    }

    /**
     * Update nomor resi
     */
    public function updateResi(Request $request, $id)
    {
        $request->validate([
            'nomor_resi' => 'required|string|max:50',
            'catatan_pengiriman' => 'nullable|string'
        ]);

        try {
            $result = $this->trackingService->updateNomorResi($id, $request->nomor_resi);

            if ($result['success']) {
                // Update catatan jika ada
                if ($request->filled('catatan_pengiriman')) {
                    $pengiriman = Pengiriman::find($id);
                    $pengiriman->update(['catatan_pengiriman' => $request->catatan_pengiriman]);
                }

                return redirect()->route('admin.pengiriman.show', $id)
                    ->with('success', 'Nomor resi berhasil diupdate dan tracking dimulai');
            } else {
                return redirect()->back()
                    ->with('error', $result['message'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update tracking manual untuk satu pengiriman
     */
    // public function updateTracking($id)
    // {
    //     try {
    //         $pengiriman = Pengiriman::findOrFail($id);
    //         $result = $this->trackingService->updateTrackingTunggal($pengiriman);

    //         if ($result['success']) {
    //             return redirect()->back()->with('success', 'Tracking berhasil diupdate');
    //         } else {
    //             return redirect()->back()->with('error', $result['message']);
    //         }
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    public function updateTracking($id)
    {
        try {
            $pengiriman = Pengiriman::findOrFail($id);

            // Validasi data pengiriman
            if (empty($pengiriman->nomor_resi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor resi tidak ditemukan pada pengiriman ini'
                ], 400);
            }

            if (empty($pengiriman->ekspedisi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data ekspedisi tidak ditemukan pada pengiriman ini'
                ], 400);
            }

            $result = $this->trackingService->updateTrackingTunggal($pengiriman);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tracking berhasil diupdate',
                    'data' => [
                        'pengiriman_id' => $pengiriman->id_pengiriman,
                        'nomor_resi' => $pengiriman->nomor_resi,
                        'ekspedisi' => $pengiriman->ekspedisi,
                        'tracking_info' => $result['data'] ?? null
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'debug_info' => [
                        'nomor_resi' => $pengiriman->nomor_resi,
                        'ekspedisi' => $pengiriman->ekspedisi
                    ]
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error in updateTracking controller: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    // Method tambahan untuk mendapatkan detail tracking
    public function getTrackingDetail($id)
    {
        try {
            $pengiriman = Pengiriman::findOrFail($id);

            if (empty($pengiriman->nomor_resi)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nomor resi tidak ditemukan'
                ], 400);
            }

            // Ambil data tracking langsung dari API tanpa update database
            $trackingData = $this->trackingService->getTrackingFromAPIAlternative($pengiriman->nomor_resi, $pengiriman->ekspedisi);

            return response()->json([
                'success' => $trackingData['success'],
                'message' => $trackingData['success'] ? 'Data tracking berhasil diambil' : $trackingData['message'],
                'data' => $trackingData['data'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getTrackingDetail: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Update tracking untuk semua pengiriman aktif
     */
    public function updateSemuaTracking()
    {
        try {
            $result = $this->trackingService->updateSemuaTracking();

            $message = "Update tracking selesai. Berhasil: {$result['berhasil']}, Gagal: {$result['gagal']}, Total: {$result['total']}";

            if ($result['gagal'] > 0) {
                return redirect()->back()->with('warning', $message);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function debugTrackingAPI(Request $request)
    {
        try {
            $awb = $request->input('awb', 'JP9323358880');
            $courier = $request->input('courier', 'jnt');

            $apiKey = config('services.rajaongkir.key');
            $baseUrl = config('services.rajaongkir.url');

            Log::info('Debug Tracking API', [
                'awb' => $awb,
                'courier' => $courier,
                'apiKey' => substr($apiKey, 0, 10) . '...',
                'baseUrl' => $baseUrl
            ]);

            // Test 1: Query Parameters
            $url1 = $baseUrl . '/track/waybill?' . http_build_query([
                'awb' => $awb,
                'courier' => $courier
            ]);

            $response1 = Http::withHeaders([
                'key' => $apiKey
            ])->post($url1);

            // Test 2: Form Data
            $response2 = Http::withHeaders([
                'key' => $apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($baseUrl . '/track/waybill', [
                'awb' => $awb,
                'courier' => $courier
            ]);

            // Test 3: JSON Body
            $response3 = Http::withHeaders([
                'key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($baseUrl . '/track/waybill', [
                'awb' => $awb,
                'courier' => $courier
            ]);

            return response()->json([
                'debug_info' => [
                    'awb' => $awb,
                    'courier' => $courier,
                    'api_key_prefix' => substr($apiKey, 0, 10) . '...',
                    'base_url' => $baseUrl
                ],
                'test_1_query_params' => [
                    'url' => $url1,
                    'status' => $response1->status(),
                    'response' => $response1->json(),
                    'headers_sent' => ['key' => substr($apiKey, 0, 10) . '...']
                ],
                'test_2_form_data' => [
                    'status' => $response2->status(),
                    'response' => $response2->json(),
                    'method' => 'POST with form data'
                ],
                'test_3_json_body' => [
                    'status' => $response3->status(),
                    'response' => $response3->json(),
                    'method' => 'POST with JSON body'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // Method untuk test dengan berbagai courier codes
    public function testCourierCodes(Request $request)
    {
        $awb = $request->input('awb', 'JP9323358880');
        $testCouriers = ['jnt', 'j&t', 'jne', 'tiki', 'pos', 'sicepat'];

        $apiKey = config('services.rajaongkir.key');
        $baseUrl = config('services.rajaongkir.url');

        $results = [];

        foreach ($testCouriers as $courier) {
            try {
                $response = Http::withHeaders([
                    'key' => $apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->asForm()->post($baseUrl . '/track/waybill', [
                    'awb' => $awb,
                    'courier' => $courier
                ]);

                $results[$courier] = [
                    'status' => $response->status(),
                    'success' => $response->successful(),
                    'response' => $response->json()
                ];
            } catch (\Exception $e) {
                $results[$courier] = [
                    'error' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'awb' => $awb,
            'test_results' => $results
        ]);
    }
}
