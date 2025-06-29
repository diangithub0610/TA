<?php

namespace App\Http\Controllers;

use Midtrans\Config;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use Midtrans\Notification;
use App\Helpers\GenerateId;
use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PembayaranController extends Controller
{
    protected $midtransService;
    // protected $midtransService;

    // public function __construct(MidtransService $midtransService)
    // {
    //     $this->midtransService = $midtransService;
    // }

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    public function show($kodeTransaksi)
    {
        // Tambahkan debug ini
        Log::info('Accessing payment page for: ' . $kodeTransaksi);
        
        $pelanggan = auth()->guard('pelanggan')->user();
        if (!$pelanggan) {
            Log::info('User not authenticated, redirecting to login');
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }
        
        $transaksi = Transaksi::with(['detailTransaksi.detailBarang.barang', 'pelanggan', 'alamat', 'pembayaran'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();
    
        // Debug transaksi dan pembayaran
        Log::info('Transaction found', ['transaksi' => $transaksi->kode_transaksi]);
        Log::info('Payment status', ['status' => $transaksi->pembayaran->status ?? 'no payment']);
        Log::info('Snap token', ['token' => $transaksi->pembayaran->snap_token ?? 'no token']);
    
        if (!$transaksi->pembayaran || !$transaksi->pembayaran->snap_token) {
            Log::info('Invalid payment token, redirecting to home');
            return redirect()->route('home')->with('error', 'Token pembayaran tidak valid');
        }
    
        Log::info('Rendering payment view');
        return view('pelanggan.transaksi.pembayaran', compact('transaksi'));
    }

    // public function notificationCallback(Request $request)
    // {
    //     // Log raw request untuk debugging
    //     Log::info('Midtrans Raw Request', [
    //         'method' => $request->method(),
    //         'headers' => $request->headers->all(),
    //         'body' => $request->getContent()
    //     ]);

    //     try {
    //         // Ambil payload dan decode sebagai JSON
    //         $payload = $request->getContent();
    //         $notification = json_decode($payload, true);

    //         // Log untuk debugging
    //         Log::info('Midtrans Notification Received', ['data' => $notification]);

    //         // Validasi parameter yang diperlukan
    //         if (!isset($notification['order_id']) || !isset($notification['transaction_status'])) {
    //             Log::error('Midtrans Notification: Parameter tidak lengkap', ['data' => $notification]);
    //             return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
    //         }

    //         // Ambil data dari notifikasi
    //         $orderId = $notification['order_id'];
    //         $statusCode = $notification['status_code'] ?? null;
    //         $transactionStatus = $notification['transaction_status'];
    //         $fraudStatus = $notification['fraud_status'] ?? null;
    //         $paymentType = $notification['payment_type'] ?? null;
    //         $transactionId = $notification['transaction_id'] ?? null;

    //         Log::info('Midtrans Notification', [
    //             'order_id' => $orderId,
    //             'status_code' => $statusCode,
    //             'transaction_status' => $transactionStatus,
    //             'fraud_status' => $fraudStatus
    //         ]);

    //         // Cari pembayaran dengan midtrans_order_id yang sesuai
    //         $pembayaran = Pembayaran::where('midtrans_order_id', $orderId)->first();

    //         if (!$pembayaran) {
    //             // Coba cari menggunakan substring dari order_id jika mengandung format tertentu
    //             $uuidPattern = '/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i';
    //             if (preg_match($uuidPattern, $orderId, $matches)) {
    //                 $uuid = $matches[1];
    //                 $pembayaran = Pembayaran::where('midtrans_order_id', 'LIKE', "%{$uuid}%")->first();
    //             }

    //             if (!$pembayaran) {
    //                 Log::error('Pembayaran tidak ditemukan', [
    //                     'order_id' => $orderId,
    //                     'attempted_uuid_match' => $matches[1] ?? null
    //                 ]);

    //                 // Cek apakah ini notifikasi testing dari Midtrans
    //                 if (strpos($orderId, 'payment_notif_test') !== false) {
    //                     Log::info('Detected test notification from Midtrans, responding with OK');
    //                     return response('OK', 200);
    //                 }

    //                 return response('OK', 200); // Tetap mengembalikan OK agar Midtrans tidak retry terus-menerus
    //             }
    //         }

    //         // Cari transaksi
    //         $transaksi = Transaksi::where('kode_transaksi', $pembayaran->kode_transaksi)->first();

    //         if (!$transaksi) {
    //             Log::error('Transaksi tidak ditemukan', ['kode_transaksi' => $pembayaran->kode_transaksi]);
    //             return response('OK', 200);
    //         }

    //         DB::beginTransaction();

    //         // Update pembayaran dengan data dari Midtrans
    //         $pembayaran->midtrans_transaction_id = $transactionId;

    //         // Handle status pembayaran berdasarkan transaction_status & fraud_status
    //         if ($transactionStatus == 'capture') {
    //             if ($fraudStatus == 'challenge') {
    //                 // Pembayaran challenge, perlu pengecekan manual
    //                 $pembayaran->status = 'pending';
    //                 $transaksi->status = 'belum_dibayar';
    //             } else if ($fraudStatus == 'accept') {
    //                 // Pembayaran sukses
    //                 $pembayaran->status = 'sukses';
    //                 $pembayaran->tanggal_pembayaran = now();
    //                 $transaksi->status = 'menunggu_konfirmasi';
    //             }
    //         } else if ($transactionStatus == 'settlement') {
    //             // Pembayaran sukses (metode transfer bank, dll)
    //             $pembayaran->status = 'sukses';
    //             $pembayaran->tanggal_pembayaran = now();
    //             $transaksi->status = 'menunggu_konfirmasi';
    //         } else if ($transactionStatus == 'pending') {
    //             // Pembayaran pending
    //             $pembayaran->status = 'pending';
    //             $transaksi->status = 'belum_dibayar';
    //         } else if ($transactionStatus == 'deny') {
    //             // Pembayaran ditolak
    //             $pembayaran->status = 'gagal';
    //             $transaksi->status = 'dibatalkan';
    //         } else if ($transactionStatus == 'expire') {
    //             // Pembayaran kadaluarsa
    //             $pembayaran->status = 'kadaluarsa';
    //             $transaksi->status = 'dibatalkan';
    //         } else if ($transactionStatus == 'cancel') {
    //             // Pembayaran dibatalkan
    //             $pembayaran->status = 'gagal';
    //             $transaksi->status = 'dibatalkan';
    //         }

    //         // Simpan detail VA atau link PDF jika ada
    //         if ($paymentType == 'bank_transfer') {
    //             // Menyimpan nomor VA
    //             if (isset($notification['va_numbers']) && !empty($notification['va_numbers'])) {
    //                 $vaNumber = $notification['va_numbers'][0]['va_number'];
    //                 $pembayaran->virtual_account = $vaNumber;
    //             }
    //         } elseif ($paymentType == 'echannel') {
    //             // Untuk Mandiri Bill Payment
    //             if (isset($notification['bill_key'])) {
    //                 $pembayaran->virtual_account = $notification['bill_key'];
    //             }
    //         } elseif ($paymentType == 'gopay' || $paymentType == 'shopeepay') {
    //             // Untuk e-wallet seperti GoPay atau ShopeePay
    //             if (isset($notification['actions']) && !empty($notification['actions'])) {
    //                 foreach ($notification['actions'] as $action) {
    //                     if ($action['name'] === 'generate-qr-code') {
    //                         $pembayaran->pdf_url = $action['url'];
    //                         break;
    //                     }
    //                 }
    //             }
    //         } elseif ($paymentType == 'cstore') {
    //             // Untuk convenience store seperti Indomaret atau Alfamart
    //             if (isset($notification['payment_code'])) {
    //                 $pembayaran->virtual_account = $notification['payment_code'];
    //             }
    //             if (isset($notification['pdf_url'])) {
    //                 $pembayaran->pdf_url = $notification['pdf_url'];
    //             }
    //         }

    //         // Simpan perubahan
    //         $pembayaran->save();
    //         $transaksi->save();

    //         DB::commit();

    //         Log::info('Midtrans Notification Processed Successfully', [
    //             'order_id' => $orderId,
    //             'transaction_status' => $transactionStatus,
    //             'payment_id' => $pembayaran->id_pembayaran
    //         ]);

    //         return response('OK', 200); // Midtrans mengharapkan "OK" dengan status 200

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error processing Midtrans notification: ' . $e->getMessage(), [
    //             'exception' => $e,
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response('OK', 200); // Tetap mengembalikan OK agar Midtrans tidak retry terus-menerus
    //     }
    // }


    public function notificationCallback(Request $request)
    {
        // Log raw request untuk debugging
        Log::info('Midtrans Raw Request', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent()
        ]);

        try {
            // Ambil payload dan decode sebagai JSON
            $payload = $request->getContent();
            $notification = json_decode($payload, true);

            // Log untuk debugging
            Log::info('Midtrans Notification Received', ['data' => $notification]);

            // Validasi parameter yang diperlukan
            if (!isset($notification['order_id']) || !isset($notification['transaction_status'])) {
                Log::error('Midtrans Notification: Parameter tidak lengkap', ['data' => $notification]);
                return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
            }

            // Ambil data dari notifikasi
            $orderId = $notification['order_id'];
            $statusCode = $notification['status_code'] ?? null;
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'] ?? null;
            $paymentType = $notification['payment_type'] ?? null;
            $transactionId = $notification['transaction_id'] ?? null;

            Log::info('Midtrans Notification', [
                'order_id' => $orderId,
                'status_code' => $statusCode,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus
            ]);

            // Cari pembayaran dengan midtrans_order_id yang sesuai
            $pembayaran = Pembayaran::where('midtrans_order_id', $orderId)->first();

            if (!$pembayaran) {
                // Coba cari menggunakan substring dari order_id jika mengandung format tertentu
                $uuidPattern = '/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i';
                if (preg_match($uuidPattern, $orderId, $matches)) {
                    $uuid = $matches[1];
                    $pembayaran = Pembayaran::where('midtrans_order_id', 'LIKE', "%{$uuid}%")->first();
                }

                if (!$pembayaran) {
                    Log::error('Pembayaran tidak ditemukan', [
                        'order_id' => $orderId,
                        'attempted_uuid_match' => $matches[1] ?? null
                    ]);

                    // Cek apakah ini notifikasi testing dari Midtrans
                    if (strpos($orderId, 'payment_notif_test') !== false) {
                        Log::info('Detected test notification from Midtrans, responding with OK');
                        return response('OK', 200);
                    }

                    return response('OK', 200); // Tetap mengembalikan OK agar Midtrans tidak retry terus-menerus
                }
            }

            // Cari transaksi
            $transaksi = Transaksi::where('kode_transaksi', $pembayaran->kode_transaksi)->first();

            if (!$transaksi) {
                Log::error('Transaksi tidak ditemukan', ['kode_transaksi' => $pembayaran->kode_transaksi]);
                return response('OK', 200);
            }

            DB::beginTransaction();

            // Update pembayaran dengan data dari Midtrans
            $pembayaran->midtrans_transaction_id = $transactionId;

            // Flag untuk menentukan apakah pembayaran sukses
            $pembayaranSukses = false;

            // Handle status pembayaran berdasarkan transaction_status & fraud_status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    // Pembayaran challenge, perlu pengecekan manual
                    $pembayaran->status = 'pending';
                    $transaksi->status = 'belum_dibayar';
                } else if ($fraudStatus == 'accept') {
                    // Pembayaran sukses
                    $pembayaran->status = 'sukses';
                    $pembayaran->tanggal_pembayaran = now();
                    $transaksi->status = 'menunggu_konfirmasi';
                    $pembayaranSukses = true;
                }
            } else if ($transactionStatus == 'settlement') {
                // Pembayaran sukses (metode transfer bank, dll)
                $pembayaran->status = 'sukses';
                $pembayaran->tanggal_pembayaran = now();
                $transaksi->status = 'menunggu_konfirmasi';
                $pembayaranSukses = true;
            } else if ($transactionStatus == 'pending') {
                // Pembayaran pending
                $pembayaran->status = 'pending';
                $transaksi->status = 'belum_dibayar';
            } else if ($transactionStatus == 'deny') {
                // Pembayaran ditolak
                $pembayaran->status = 'gagal';
                $transaksi->status = 'dibatalkan';
            } else if ($transactionStatus == 'expire') {
                // Pembayaran kadaluarsa
                $pembayaran->status = 'kadaluarsa';
                $transaksi->status = 'dibatalkan';
            } else if ($transactionStatus == 'cancel') {
                // Pembayaran dibatalkan
                $pembayaran->status = 'gagal';
                $transaksi->status = 'dibatalkan';
            }

            // Simpan detail VA atau link PDF jika ada
            if ($paymentType == 'bank_transfer') {
                // Menyimpan nomor VA
                if (isset($notification['va_numbers']) && !empty($notification['va_numbers'])) {
                    $vaNumber = $notification['va_numbers'][0]['va_number'];
                    $pembayaran->virtual_account = $vaNumber;
                }
            } elseif ($paymentType == 'echannel') {
                // Untuk Mandiri Bill Payment
                if (isset($notification['bill_key'])) {
                    $pembayaran->virtual_account = $notification['bill_key'];
                }
            } elseif ($paymentType == 'gopay' || $paymentType == 'shopeepay') {
                // Untuk e-wallet seperti GoPay atau ShopeePay
                if (isset($notification['actions']) && !empty($notification['actions'])) {
                    foreach ($notification['actions'] as $action) {
                        if ($action['name'] === 'generate-qr-code') {
                            $pembayaran->pdf_url = $action['url'];
                            break;
                        }
                    }
                }
            } elseif ($paymentType == 'cstore') {
                // Untuk convenience store seperti Indomaret atau Alfamart
                if (isset($notification['payment_code'])) {
                    $pembayaran->virtual_account = $notification['payment_code'];
                }
                if (isset($notification['pdf_url'])) {
                    $pembayaran->pdf_url = $notification['pdf_url'];
                }
            }

            // Simpan perubahan
            $pembayaran->save();
            $transaksi->save();

            // ===== TAMBAHAN UNTUK AUTOMATIC SHIPPING CREATION =====
            // Jika pembayaran sukses, buat record pengiriman otomatis
            if ($pembayaranSukses) {
                try {
                    $trackingService = new \App\Services\TrackingService();
                    $pengiriman = $trackingService->buatPengirimanOtomatis($transaksi->kode_transaksi);

                    if ($pengiriman) {
                        Log::info('Pengiriman otomatis berhasil dibuat', [
                            'kode_transaksi' => $transaksi->kode_transaksi,
                            'id_pengiriman' => $pengiriman->id_pengiriman
                        ]);
                    } else {
                        Log::warning('Gagal membuat pengiriman otomatis', [
                            'kode_transaksi' => $transaksi->kode_transaksi
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error saat membuat pengiriman otomatis: ' . $e->getMessage(), [
                        'kode_transaksi' => $transaksi->kode_transaksi,
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Jangan throw error, biarkan callback tetap sukses
                }
            }
            // ===== END TAMBAHAN =====

            DB::commit();

            Log::info('Midtrans Notification Processed Successfully', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'payment_id' => $pembayaran->id_pembayaran,
                'pengiriman_created' => $pembayaranSukses
            ]);

            return response('OK', 200); // Midtrans mengharapkan "OK" dengan status 200

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing Midtrans notification: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response('OK', 200); // Tetap mengembalikan OK agar Midtrans tidak retry terus-menerus
        }
    }
    /**
     * Membuat data pengiriman setelah pembayaran berhasil
     */
    private function createPengiriman(Transaksi $transaksi)
    {
        // Cek apakah pengiriman sudah ada
        $pengiriman = Pengiriman::where('kode_transaksi', $transaksi->kode_transaksi)->first();

        // Jika belum ada, buat baru
        if (!$pengiriman) {
            Pengiriman::create([
                'kode_pengiriman' => 'KRM001',
                'kode_transaksi' => $transaksi->kode_transaksi,
                'status' => 'disiapkan',
                'tanggal_pengiriman' => null,
                'tanggal_estimasi_tiba' => null,
                'tanggal_tiba' => null
            ]);
        }
    }

    /**
     * Handle redirect setelah pembayaran berhasil
     */
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id');
        return redirect()->route('pembayaran.show', $orderId)
            ->with('success', 'Pembayaran berhasil dilakukan!');
    }

    /**
     * Handle redirect ketika pembayaran belum selesai
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->query('order_id');
        return redirect()->route('pembayaran.show', $orderId)
            ->with('info', 'Pembayaran belum selesai, silakan selesaikan pembayaran Anda.');
    }

    /**
     * Handle redirect ketika terjadi error pada proses pembayaran
     */
    public function error(Request $request)
    {
        $orderId = $request->query('order_id');
        return redirect()->route('pembayaran.show', $orderId)
            ->with('error', 'Terjadi kesalahan dalam proses pembayaran, silakan coba lagi.');
    }



    // public function notification(Request $request)
    // {
    //     // Log raw request untuk debugging
    //     Log::info('Midtrans Notification Received', [
    //         'method' => $request->method(),
    //         'all_data' => $request->all(),
    //         'json_data' => $request->json()->all(),
    //     ]);

    //     try {
    //         // Coba ambil data dari berbagai sumber
    //         $notificationData = $request->json()->all() ?? $request->all();

    //         // Pastikan data yang dibutuhkan ada
    //         if (empty($notificationData)) {
    //             Log::error('No notification data received');
    //             return response()->json(['status' => 'error', 'message' => 'No data received'], 400);
    //         }

    //         $result = $this->midtransService->handleNotification($notificationData);

    //         if ($result['status'] === 'success') {
    //             return response()->json(['status' => 'success']);
    //         } else {
    //             Log::error('Midtrans Notification Handling Failed', [
    //                 'message' => $result['message']
    //             ]);
    //             return response()->json(['status' => 'error', 'message' => $result['message']], 400);
    //         }
    //     } catch (\Exception $e) {
    //         Log::error('Midtrans Notification Exception', [
    //             'message' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    // public function finish(Request $request)
    // {
    //     $kodeTransaksi = $request->order_id;

    //     return redirect()->route('transaksi.detail', $kodeTransaksi)
    //         ->with('success', 'Pembayaran berhasil diproses');
    // }

    // public function unfinish(Request $request)
    // {
    //     $kodeTransaksi = $request->order_id;

    //     return redirect()->route('transaksi.detail', $kodeTransaksi)
    //         ->with('warning', 'Pembayaran belum selesai');
    // }

    // public function error(Request $request)
    // {
    //     $kodeTransaksi = $request->order_id;

    //     return redirect()->route('transaksi.detail', $kodeTransaksi)
    //         ->with('error', 'Pembayaran gagal');
    // }
}
