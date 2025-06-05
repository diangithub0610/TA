<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\Pendaftaran;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Notification;
use Midtrans\Snap;

class RegistrasiController extends Controller
{
    public function __construct()
    {
        // Set Midtrans configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama_pelanggan' => 'required|string|max:50',
            'no_hp' => 'required|string|max:15',
            'email' => 'required|string|email|max:100|unique:pelanggan',
            'alamat_pengguna' => 'required|string',
            'username' => 'required|string|max:25|unique:pelanggan',
            'password' => 'required|string|min:8|confirmed',
            'tipe_akun' => 'required|in:pelanggan,reseller',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Generate ID Pelanggan
        $id_pelanggan = 'P' . Str::random(9);

        // Ambil biaya pendaftaran dari toko
        $toko = Toko::first();

        // Biaya pendaftaran (gratis untuk pelanggan, sesuai setting untuk reseller)
        $biaya = $request->tipe_akun == 'reseller' ? ($toko ? $toko->biaya_pendaftaran_reseller : 100000) : 0;

        // Simpan data pelanggan
        $pelanggan = Pelanggan::create([
            'id_pelanggan' => $id_pelanggan,
            'nama_pelanggan' => $request->nama_pelanggan,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'alamat_pengguna' => $request->alamat_pengguna,
            'username' => $request->username,
            'role' => 'pelanggan', // Semua dimulai sebagai pelanggan sebelum diverifikasi
            'kata_sandi' => Hash::make($request->password),
        ]);

        // Generate ID Pendaftaran
        $id_pendaftaran = 'REG' . Str::random(7);

        // Simpan data pendaftaran
        $pendaftaran = Pendaftaran::create([
            'id_pendaftaran' => $id_pendaftaran,
            'id_pelanggan' => $id_pelanggan,
            'tipe_akun' => $request->tipe_akun,
            'biaya_pendaftaran' => $biaya,
            'status_pembayaran' => $request->tipe_akun == 'pelanggan' ? 'berhasil' : 'pending',
        ]);

        // Jika tipe_akun adalah reseller, proses pembayaran dengan Midtrans
        if ($request->tipe_akun == 'reseller') {
            // Siapkan data pembayaran untuk Midtrans
            $params = [
                'transaction_details' => [
                    'order_id' => $id_pendaftaran,
                    'gross_amount' => $biaya,
                ],
                'customer_details' => [
                    'first_name' => $pelanggan->nama_pelanggan,
                    'email' => $pelanggan->email,
                    'phone' => $pelanggan->no_hp,
                ],
                'item_details' => [
                    [
                        'id' => $request->tipe_akun,
                        'price' => $biaya,
                        'quantity' => 1,
                        'name' => 'Pendaftaran Reseller',
                    ],
                ],
            ];

            // Dapatkan token snap untuk halaman pembayaran
            $snapToken = Snap::getSnapToken($params);

            // Update pendaftaran dengan snap token
            $pendaftaran->update([
                'snap_token' => $snapToken,
            ]);

            // Redirect ke halaman pembayaran
            return redirect()->route('pembayaran-pendaftaran.show', $id_pendaftaran);
        } else {
            // Untuk pelanggan, langsung login (karena gratis)
            auth()->guard('web')->login($pelanggan);
            return redirect()->route('pelanggan.beranda')->with('success', 'Pendaftaran berhasil!');
        }
    }

    public function showPembayaran($id)
    {
        $pendaftaran = Pendaftaran::with('pelanggan')->where('id_pendaftaran', $id)->firstOrFail();

        return view('auth.pembayaran', compact('pendaftaran'));
    }

    public function notifikasi(Request $request)
    {
        try {
            $notif = new Notification();
    
            $transactionStatus = $notif->transaction_status;
            $orderId = $notif->order_id;
    
            Log::info('Midtrans Notification Received', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);
    
            // Ambil ID pendaftaran dari order_id
            $actualOrderId = $orderId;
    
            // Jika ini adalah notifikasi test, abaikan atau sesuaikan
            if (strpos($actualOrderId, 'payment_notif_test') === 0) {
                Log::info('Test notification received. Skipping update.');
                return response()->json(['status' => 'ok', 'message' => 'Test notification ignored'], 200);
            }
    
            // Cari pendaftaran berdasarkan order_id
            $pendaftaran = Pendaftaran::where('id_pendaftaran', $actualOrderId)->first();
    
            if (!$pendaftaran) {
                Log::error('Pendaftaran not found for order_id', ['order_id' => $actualOrderId]);
                return response()->json(['status' => 'error', 'message' => 'Pendaftaran tidak ditemukan'], 200);
            }
    
            // Update status pembayaran dan tanggal jika transaksi sukses
            if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                $pendaftaran->update([
                    'status_pembayaran' => 'berhasil',
                    'tanggal_pembayaran' => now(),
                ]);
    
                // Update role pelanggan jika akun bertipe reseller
                if ($pendaftaran->tipe_akun === 'reseller') {
                    $pelanggan = Pelanggan::where('id_pelanggan', $pendaftaran->id_pelanggan)->first();
    
                    if ($pelanggan) {
                        $pelanggan->update([
                            'role' => 'reseller',
                        ]);
    
                        Log::info('Role pelanggan berhasil diubah menjadi reseller', [
                            'id_pelanggan' => $pelanggan->id_pelanggan
                        ]);
                    } else {
                        Log::warning('Pelanggan tidak ditemukan untuk pendaftaran ini', [
                            'id_pelanggan' => $pendaftaran->id_pelanggan
                        ]);
                    }
                }
    
                Log::info('Pembayaran berhasil diproses', [
                    'order_id' => $actualOrderId,
                    'transaction_status' => $transactionStatus
                ]);
            }
    
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error processing Midtrans notification', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }
    
}
