<?php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    public function createTransaction(Transaksi $transaksi)
    {
        $pelanggan = $transaksi->pelanggan;
        $detailTransaksi = $transaksi->detailTransaksi->load('detailBarang.barang');

        $items = [];
        foreach ($detailTransaksi as $detail) {
            $items[] = [
                'id' => $detail->kode_detail,
                'price' => $detail->harga,
                'quantity' => $detail->kuantitas,
                'name' => substr($detail->detailBarang->barang->nama_barang, 0, 50) . ' (' . $detail->detailBarang->ukuran . ')'
            ];
        }

        // Tambahkan biaya pengiriman sebagai item
        if ($transaksi->ongkir > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => $transaksi->ongkir,
                'quantity' => 1,
                'name' => 'Biaya Pengiriman ' . $transaksi->ekspedisi . ' ' . $transaksi->layanan_ekspedisi
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $transaksi->kode_transaksi,
                'gross_amount' => $transaksi->total
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $pelanggan->nama_pelanggan,
                'email' => $pelanggan->email,
                'phone' => $pelanggan->no_hp,
                'billing_address' => [
                    'first_name' => $transaksi->alamat->nama_penerima,
                    'phone' => $transaksi->alamat->no_hp_penerima,
                    'address' => $transaksi->alamat->alamat_lengkap,
                    'city' => $transaksi->alamat->kota,
                    'postal_code' => $transaksi->alamat->kode_pos,
                    'country_code' => 'IDN'
                ],
                'shipping_address' => [
                    'first_name' => $transaksi->alamat->nama_penerima,
                    'phone' => $transaksi->alamat->no_hp_penerima,
                    'address' => $transaksi->alamat->alamat_lengkap,
                    'city' => $transaksi->alamat->kota,
                    'postal_code' => $transaksi->alamat->kode_pos,
                    'country_code' => 'IDN'
                ]
            ],
            'expiry' => [
                'unit' => 'day',
                'duration' => 1
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return [
                'status' => 'success',
                'snap_token' => $snapToken
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function handleNotification(array $notificationData)
    {
        try {
            // Log incoming notification
            Log::info('Midtrans Notification Received', ['data' => $notificationData]);

            // Validate required parameters
            $requiredKeys = ['transaction_status', 'payment_type', 'order_id'];
            foreach ($requiredKeys as $key) {
                if (!isset($notificationData[$key])) {
                    Log::error('Midtrans Notification: Missing required parameters', [
                        'missing_key' => $key,
                        'data' => $notificationData
                    ]);
                    return [
                        'status' => 'error',
                        'message' => "Parameter $key tidak lengkap"
                    ];
                }
            }

            // Extract transaction details
            $transaction = $notificationData['transaction_status'];
            $type = $notificationData['payment_type'];
            $orderId = $notificationData['order_id'];
            $fraud = $notificationData['fraud_status'] ?? null;
            $transactionId = $notificationData['transaction_id'] ?? null;

            // Variasi pencarian order ID
            $orderIdVariations = [
                $orderId,
                str_replace('payment_notif_test_', '', $orderId),
                substr($orderId, -36),
                substr($orderId, strpos($orderId, '_', strpos($orderId, '_') + 1) + 1)
            ];

            // Cari transaksi
            $transaksi = null;
            foreach ($orderIdVariations as $variation) {
                $transaksi = Transaksi::where('kode_transaksi', $variation)->first();
                if ($transaksi) break;
            }

            if (!$transaksi) {
                Log::error('Midtrans Notification: Transaksi not found', [
                    'order_id_variations' => $orderIdVariations
                ]);
                return [
                    'status' => 'error',
                    'message' => 'Transaksi tidak ditemukan'
                ];
            }

            // Buat atau update pembayaran
            $pembayaran = Pembayaran::firstOrNew([
                'transaksi_id' => $transaksi->id,
                'midtrans_order_id' => $orderId
            ]);

            // Update detail pembayaran
            $pembayaran->midtrans_transaction_id = $transactionId;
            $pembayaran->metode_pembayaran = $type;

            // Handle payment code (sesuaikan dengan struktur tabel Anda)
            $pembayaran->kode_pembayaran = $this->extractPaymentCode($notificationData);

            // Set jumlah dan status pembayaran
            $pembayaran->jumlah = $notificationData['gross_amount'] ?? 0;
            $pembayaran->status = $this->determinePaymentStatus($transaction, $type, $fraud);

            // Tentukan status transaksi
            $statusTransaksi = $this->determineTransactionStatus($transaction, $type, $fraud);

            // Update transaksi
            $transaksi->status = $statusTransaksi;

            // Simpan perubahan
            $pembayaran->save();
            $transaksi->save();

            // Log::info('Midtrans Notification: Successfully processed', [
            //     'order_id' => $orderId,
            //     'transaction_status' => $transaction,
            //     'payment_status' => $pembayaran->status,
            //     'transaction_status' => $statusTransaksi
            // ]);

            return [
                'status' => 'success',
                'pembayaran' => $pembayaran,
                'transaksi' => $transaksi
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans Notification Processing Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'notification_data' => $notificationData
            ]);

            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    // Metode helper untuk ekstrak kode pembayaran
    protected function extractPaymentCode(array $notificationData)
    {
        if (isset($notificationData['payment_code'])) {
            return $notificationData['payment_code'];
        }

        if (isset($notificationData['bill_key'])) {
            return $notificationData['bill_key'];
        }

        if (isset($notificationData['permata_va_number'])) {
            return $notificationData['permata_va_number'];
        }

        if (isset($notificationData['va_numbers']) && !empty($notificationData['va_numbers'])) {
            return $notificationData['va_numbers'][0]['va_number'] ?? null;
        }

        return null;
    }

    // Metode helper untuk menentukan status pembayaran
    protected function determinePaymentStatus($transaction, $type, $fraud)
    {
        switch ($transaction) {
            case 'capture':
                if ($type == 'credit_card') {
                    return $fraud == 'challenge' ? 'pending' : 'sukses';
                }
                return 'sukses';
            case 'settlement':
                return 'sukses';
            case 'pending':
                return 'pending';
            case 'deny':
            case 'expire':
            case 'cancel':
                return 'gagal';
            default:
                return 'pending';
        }
    }

    // Metode helper untuk menentukan status transaksi
    protected function determineTransactionStatus($transaction, $type, $fraud)
    {
        switch ($transaction) {
            case 'capture':
                if ($type == 'credit_card') {
                    return $fraud == 'challenge' ? 'belum_dibayar' : 'menunggu_konfirmasi';
                }
                return 'menunggu_konfirmasi';
            case 'settlement':
                return 'menunggu_konfirmasi';
            case 'pending':
                return 'belum_dibayar';
            case 'deny':
            case 'expire':
            case 'cancel':
                return 'dibatalkan';
            default:
                return 'belum_dibayar';
        }
    }
}