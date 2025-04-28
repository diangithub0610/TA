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
        $this->middleware('auth:pelanggan')->except(['notification']);
        $this->midtransService = $midtransService;
    }

    public function show($kodeTransaksi)
    {
        $transaksi = Transaksi::with(['detailTransaksi.detailBarang.barang', 'pelanggan', 'alamat', 'pembayaran'])
            ->where('id_pelanggan', auth()->guard('pelanggan')->id())
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        return view('pelanggan.transaksi.pembayaran', compact('transaksi'));
    }

    public function notificationCallback(Request $request)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');

        try {
            // Buat instance notifikasi
            $notification = new Notification();

            // Ambil data notifikasi
            $orderId = $notification->order_id;
            $statusCode = $notification->status_code;
            $transactionStatus = $notification->transaction_status;
            $fraudStatus = $notification->fraud_status;
            $paymentType = $notification->payment_type;
            $transactionId = $notification->transaction_id;

            Log::info('Midtrans Notification', [
                'order_id' => $orderId,
                'status_code' => $statusCode,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus
            ]);

            // Cari pembayaran dengan midtrans_order_id yang sesuai
            $pembayaran = Pembayaran::where('midtrans_order_id', $orderId)->first();

            if (!$pembayaran) {
                Log::error('Pembayaran tidak ditemukan', ['order_id' => $orderId]);
                return response()->json(['status' => 'error', 'message' => 'Order ID tidak ditemukan'], 404);
            }

            // Cari transaksi
            $transaksi = Transaksi::where('kode_transaksi', $pembayaran->kode_transaksi)->first();

            if (!$transaksi) {
                Log::error('Transaksi tidak ditemukan', ['kode_transaksi' => $pembayaran->kode_transaksi]);
                return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan'], 404);
            }

            DB::beginTransaction();

            // Update pembayaran dengan data dari Midtrans
            $pembayaran->midtrans_transaction_id = $transactionId;

            // Handle status pembayaran berdasarkan transaction_status & fraud_status
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    // Pembayaran challenge, perlu pengecekan manual
                    $pembayaran->status = 'pending';
                    $transaksi->status = 'menunggu_konfirmasi';
                } else if ($fraudStatus == 'accept') {
                    // Pembayaran sukses
                    $pembayaran->status = 'sukses';
                    $pembayaran->tanggal_pembayaran = now();
                    $transaksi->status = 'diproses';

                    // Buat pengiriman jika belum ada
                    // $this->createPengiriman($transaksi);
                }
            } else if ($transactionStatus == 'settlement') {
                // Pembayaran sukses (metode transfer bank, dll)
                $pembayaran->status = 'sukses';
                $pembayaran->tanggal_pembayaran = now();
                $transaksi->status = 'diproses';

                // Buat pengiriman jika belum ada
                // $this->createPengiriman($transaksi);
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
                if (isset($notification->va_numbers) && !empty($notification->va_numbers)) {
                    $vaNumber = $notification->va_numbers[0]->va_number;
                    $pembayaran->virtual_account = $vaNumber;
                }
            } elseif ($paymentType == 'echannel') {
                // Untuk Mandiri Bill Payment
                if (isset($notification->bill_key)) {
                    $pembayaran->virtual_account = $notification->bill_key;
                }
            } elseif ($paymentType == 'gopay' || $paymentType == 'shopeepay') {
                // Untuk e-wallet seperti GoPay atau ShopeePay
                if (isset($notification->actions) && !empty($notification->actions)) {
                    foreach ($notification->actions as $action) {
                        if ($action->name === 'generate-qr-code') {
                            $pembayaran->pdf_url = $action->url;
                            break;
                        }
                    }
                }
            } elseif ($paymentType == 'cstore') {
                // Untuk convenience store seperti Indomaret atau Alfamart
                if (isset($notification->payment_code)) {
                    $pembayaran->virtual_account = $notification->payment_code;
                }
                if (isset($notification->pdf_url)) {
                    $pembayaran->pdf_url = $notification->pdf_url;
                }
            }

            // Simpan perubahan
            $pembayaran->save();
            $transaksi->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Notification processed']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing Midtrans notification: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing notification: ' . $e->getMessage()
            ], 500);
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
