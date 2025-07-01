<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use App\Models\Toko;
use Midtrans\Config;
use App\Models\Pelanggan;
use Midtrans\Notification;
use App\Models\Pendaftaran;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            // 'alamat_pengguna' => 'required|string',
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
            // 'alamat_pengguna' => $request->alamat_pengguna,
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
        // Log permintaan awal untuk debugging
        Log::info('Midtrans Registration Raw Request', [
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'body' => $request->getContent()
        ]);
    
        try {
            // Ambil payload dan decode sebagai JSON
            $payload = $request->getContent();
            $notification = json_decode($payload, true);
    
            Log::info('Midtrans Registration Notification Received', ['data' => $notification]);
    
            // Validasi data penting
            if (!isset($notification['order_id']) || !isset($notification['transaction_status'])) {
                Log::error('Midtrans Registration Notification: Parameter tidak lengkap', ['data' => $notification]);
                return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap'], 400);
            }
    
            $orderId = $notification['order_id'];
            $transactionStatus = $notification['transaction_status'];
            $fraudStatus = $notification['fraud_status'] ?? null;
            $transactionId = $notification['transaction_id'] ?? null;
    
            // Abaikan jika notifikasi test
            if (strpos($orderId, 'payment_notif_test') !== false) {
                Log::info('Detected test notification from Midtrans, responding with OK');
                return response('OK', 200);
            }
    
            // Cari pendaftaran berdasarkan order_id
            $pendaftaran = Pendaftaran::where('id_pendaftaran', $orderId)->first();
    
            if (!$pendaftaran) {
                Log::error('Pendaftaran tidak ditemukan', ['order_id' => $orderId]);
                return response('OK', 200);
            }
    
            DB::beginTransaction();
    
            // Update status pendaftaran berdasarkan status transaksi
            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'challenge') {
                    $pendaftaran->status_pembayaran = 'pending';
                } elseif ($fraudStatus === 'accept') {
                    $pendaftaran->status_pembayaran = 'berhasil';
                    $pendaftaran->tanggal_pembayaran = now();
                }
            } elseif ($transactionStatus === 'settlement') {
                $pendaftaran->status_pembayaran = 'berhasil';
                $pendaftaran->tanggal_pembayaran = now();
            } elseif ($transactionStatus === 'pending') {
                $pendaftaran->status_pembayaran = 'pending';
            } elseif (in_array($transactionStatus, ['deny', 'cancel', 'expire'])) {
                $pendaftaran->status_pembayaran = 'gagal';
            }
    
            // Simpan perubahan pada pendaftaran
            $pendaftaran->save();
    
            // Jika berhasil bayar dan akun tipe reseller, ubah role pelanggan
            if ($pendaftaran->status_pembayaran === 'berhasil' && $pendaftaran->tipe_akun === 'reseller') {
                $pelanggan = Pelanggan::where('id_pelanggan', $pendaftaran->id_pelanggan)->first();
                if ($pelanggan) {
                    $pelanggan->role = 'reseller';
                    $pelanggan->save();
    
                    Log::info('Pelanggan di-upgrade menjadi reseller', [
                        'id_pelanggan' => $pelanggan->id_pelanggan
                    ]);
                } else {
                    Log::warning('Pelanggan tidak ditemukan saat mengubah role', [
                        'id_pelanggan' => $pendaftaran->id_pelanggan
                    ]);
                }
            }
    
            DB::commit();
    
            Log::info('Midtrans Registration Notification Processed Successfully', [
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
                'id_pendaftaran' => $pendaftaran->id_pendaftaran
            ]);
    
            return response('OK', 200);
    
        } catch (\Exception $e) {
            DB::rollBack();
    
            Log::error('Error processing Midtrans registration notification: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
    
            return response('OK', 200); // Tetap OK agar Midtrans tidak retry
        }
    }
    
}
