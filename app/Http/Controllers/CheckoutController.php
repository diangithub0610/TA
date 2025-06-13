<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use App\Models\Alamat;
use App\Models\Transaksi;
use App\Models\Pembayaran;
use App\Helpers\GenerateId;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use App\Models\DetailTransaksi;
use App\Services\MidtransService;
use App\Services\KeranjangService;
use Illuminate\Support\Facades\DB;
use App\Services\RajaOngkirService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    protected $keranjangService;
    protected $rajaOngkirService;
    protected $midtransService;
    protected $baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    protected $apiKey;

    public function __construct(
        KeranjangService $keranjangService,
        RajaOngkirService $rajaOngkirService,
        MidtransService $midtransService
    ) {
        $this->middleware('auth:pelanggan');
        $this->keranjangService = $keranjangService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->midtransService = $midtransService;
        $this->apiKey = config('services.rajaongkir.key');
    }

    public function index()
    {
        // Refresh stok keranjang sebelum checkout
        $refreshResult = $this->keranjangService->refreshStok();
        $keranjang = $refreshResult['keranjang'];

        if (empty($keranjang)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang belanja Anda kosong');
        }

        $subtotal = $this->keranjangService->hitungSubtotal();
        $pelanggan = \App\Models\Pelanggan::with('alamat')->find(auth()->guard('pelanggan')->id());

        $alamat = $pelanggan->alamat;
        // dd($alamat);
        $alamatUtama = $pelanggan->alamat()->where('is_utama', 1)->first();

        $provinsi = $this->rajaOngkirService->getProvinces();

        return view('pelanggan.transaksi.checkout', compact('keranjang', 'subtotal', 'pelanggan', 'alamat', 'alamatUtama', 'provinsi'));
    }

    public function beliLangsung(Request $request)
    {
        $request->validate([
            'kode_detail' => 'required|exists:detail_barang,kode_detail',
            'jumlah' => 'required|integer|min:1'
        ]);

        // Kosongkan keranjang
        $this->keranjangService->kosongkanKeranjang();

        // Tambahkan item ke keranjang
        $hasil = $this->keranjangService->tambahItem($request->kode_detail, $request->jumlah);

        if ($hasil['status'] === 'error') {
            return redirect()->back()->with('error', $hasil['message']);
        }

        return redirect()->route('checkout.index');
    }

    public function getKota(Request $request)
    {
        $request->validate([
            'provinsi_id' => 'required'
        ]);

        $kota = $this->rajaOngkirService->getCities($request->provinsi_id);

        return response()->json($kota);
    }

    public function getKecamatan(Request $request)
    {
        $request->validate([
            'kota_id' => 'required'
        ]);

        $kecamatan = $this->rajaOngkirService->getDistricts($request->kota_id);

        return response()->json($kecamatan);
    }

    public function cekOngkir(Request $request)
    {
        $request->validate([
            'kecamatan_id' => 'required|string',
            'kurir' => 'required|string'
        ]);

        // Debug: log input
        Log::info('Cek Ongkir Input', [
            'kecamatan_id' => $request->kecamatan_id,
            'kurir' => $request->kurir
        ]);

        // Ambil data toko untuk origin
        $toko = Toko::first();

        // Debug: log toko
        Log::info('Toko Data', [
            'rajaongkir_id' => $toko->rajaongkir_id
        ]);

        // Hitung berat total (dalam gram)
        $keranjang = $this->keranjangService->getKeranjang();
        $beratTotal = 0;

        foreach ($keranjang as $item) {
            $detailBarang = DetailBarang::with('barang')->find($item['kode_detail']);
            if ($detailBarang && isset($detailBarang->barang->berat)) {
                $beratTotal += $detailBarang->barang->berat * $item['jumlah'];
            }
        }

        // Minimum berat 1000 gram (1 kg)
        $beratTotal = max(1000, $beratTotal);

        // Debug: log berat
        Log::info('Berat Total', [
            'berat' => $beratTotal
        ]);

        try {
            $response = Http::withHeaders([
                'key' => config('services.rajaongkir.key'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin' => $toko->rajaongkir_id,
                'destination' => $request->kecamatan_id,
                'weight' => $beratTotal,
                'courier' => $request->kurir
            ]);

            // Debug: log response
            Log::info('Raja Ongkir Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];

                // Debug: log data
                Log::info('Parsed Data', [
                    'data' => $data
                ]);

                return response()->json($data);
            }

            // Debug: log unsuccessful response
            Log::error('Unsuccessful Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([], 400);
        } catch (\Exception $e) {
            // Log full exception details
            Log::error('Ongkir Calculation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([], 500);
        }
    }

    public function calculateShippingCost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'kecamatan_id' => 'required|string',
                'kurir' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $toko = Toko::first();

            if (!$toko || !$toko->rajaongkir_id) {
                return response()->json(['error' => 'Store origin not configured'], 400);
            }

            $response = Http::withHeaders([
                'key' => config('services.rajaongkir.key'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin' => $toko->rajaongkir_id,
                'destination' => $request->kecamatan_id,
                'weight' => 1000, // Default weight
                'courier' => $request->kurir
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $shippingData = $responseData['data'] ?? [];

                $formattedShippingOptions = [];
                foreach ($shippingData as $service) {
                    $formattedShippingOptions[] = [
                        'name' => $service['name'],
                        'service' => $service['service'],
                        'description' => $service['description'],
                        'cost' => $service['cost'],
                        'etd' => $service['etd']
                    ];
                }

                return response()->json($formattedShippingOptions);
            }

            return response()->json([
                'error' => 'Failed to fetch shipping costs',
                'details' => $response->body()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Shipping Cost Calculation Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Tambahkan method untuk pencarian lokasi
    public function searchDestination(Request $request)
    {
        $request->validate([
            'keyword' => 'string|min:3'
        ]);

        $keyword = $request->input('keyword', '');
        $results = $this->rajaOngkirService->searchDestination($keyword);

        return response()->json($results);
    }

    public function simpanAlamat(Request $request)
    {
        $request->validate([
            'nama_alamat' => 'required|string|max:50',
            'nama_penerima' => 'required|string|max:50',
            'no_hp_penerima' => 'required|string|max:15',
            'provinsi' => 'required|string|max:50',
            'kota' => 'required|string|max:50',
            'kecamatan' => 'required|string|max:50',
            'kelurahan' => 'required|string|max:50',
            'kode_pos' => 'required|string|max:10',
            'alamat_lengkap' => 'required|string',
            'is_utama' => 'boolean'
        ]);

        $pelanggan = auth()->guard('pelanggan')->user();

        $alamat = Alamat::create([
            'id_alamat' => GenerateId::alamat(),
            'id_pelanggan' => $pelanggan->id_pelanggan,
            'nama_alamat' => $request->nama_alamat,
            'nama_penerima' => $request->nama_penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'alamat_lengkap' => $request->alamat_lengkap,
            'provinsi' => $request->provinsi,
            'kota' => $request->kota,
            'kecamatan' => $request->kecamatan,
            'kelurahan' => $request->kelurahan,
            'kode_pos' => $request->kode_pos,
            'is_utama' => $request->is_utama ?? false
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Alamat berhasil disimpan',
                'alamat' => $alamat
            ]);
        }

        return redirect()->back()->with('success', 'Alamat berhasil disimpan');
    }

    public function proses(Request $request)
    {


        $request->validate([
            'id_alamat' => 'required|exists:alamat,id_alamat',
            'ekspedisi' => 'required|string',
            'layanan_ekspedisi' => 'required|string',
            'ongkir' => 'required|numeric',
            'estimasi_waktu' => 'required|string',
            'keterangan' => 'nullable|string',
            'is_dropship' => 'nullable|boolean',
            'nama_pengirim' => 'nullable|required_if:is_dropship,1|string|max:50',
            'no_hp_pengirim' => 'nullable|required_if:is_dropship,1|string|max:15',
        ]);

        // Refresh stok keranjang sebelum proses
        $refreshResult = $this->keranjangService->refreshStok();
        $keranjang = $refreshResult['keranjang'];

        if (empty($keranjang)) {
            return redirect()->route('keranjang.index')->with('error', 'Keranjang belanja Anda kosong');
        }

        $pelanggan = auth()->guard('pelanggan')->user();

        DB::beginTransaction();

        try {
            $pelanggan = auth()->guard('pelanggan')->user();
            $isDropship = $pelanggan->role === 'reseller' ? true : ($request->has('is_dropship') && $request->is_dropship);
            // Buat transaksi
            $kodeTransaksi = GenerateId::transaksi();
            $transaksi = Transaksi::create([
                'kode_transaksi' => $kodeTransaksi,
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'tanggal_transaksi' => now(),
                'id_alamat' => $request->id_alamat,
                'ongkir' => $request->ongkir,
                'keterangan' => $request->keterangan,
                'ekspedisi' => $request->ekspedisi,
                'layanan_ekspedisi' => $request->layanan_ekspedisi,
                'estimasi_waktu' => $request->estimasi_waktu,
                'status' => 'belum_dibayar',
                'jenis' => 'website',
                'is_dropship' => $isDropship,
                'nama_pengirim' => $isDropship ? $request->nama_pengirim : null,
                'no_hp_pengirim' => $isDropship ? $request->no_hp_pengirim : null,
            ]);

            // Buat detail transaksi dan kurangi stok
            foreach ($keranjang as $item) {
                $detailBarang = DetailBarang::find($item['kode_detail']);

                if (!$detailBarang || $detailBarang->stok < $item['jumlah']) {
                    throw new \Exception('Stok tidak mencukupi untuk ' . $item['nama_barang']);
                }

                DetailTransaksi::create([
                    // 'id_detail_transaksi' => GenerateId::detailTransaksi(),
                    'kode_transaksi' => $kodeTransaksi,
                    'kode_detail' => $item['kode_detail'],
                    'kuantitas' => $item['jumlah'],
                    'harga' => $item['harga']
                ]);

                // Kurangi stok
                $detailBarang->decrement('stok', $item['jumlah']);
            }

            $total = 0;
            foreach ($keranjang as $item) {
                $total += $item['jumlah'] * $item['harga'];
            }
            $total += $request->ongkir;

            $transaksi->update(['total' => $total]);


            // Buat pembayaran
            $pembayaran = Pembayaran::create([
                'id_pembayaran' => GenerateId::pembayaran(),
                'kode_transaksi' => $kodeTransaksi,
                'tipe_pembayaran' => 'online',
                'metode_pembayaran' => 'midtrans',
                'jumlah' => $transaksi->total,
                'status' => 'pending',
                'kadaluarsa_pembayaran' => now()->addDay(),
                'midtrans_order_id' => $kodeTransaksi
            ]);

            // Ambil snap token dari Midtrans
            $midtransResult = $this->midtransService->createTransaction($transaksi);

            if ($midtransResult['status'] !== 'success') {
                throw new \Exception('Gagal membuat pembayaran: ' . ($midtransResult['message'] ?? 'Unknown error'));
            }

            // Update snap token
            $pembayaran->update([
                'snap_token' => $midtransResult['snap_token']
            ]);

            // Kosongkan keranjang
            $this->keranjangService->kosongkanKeranjang();

            DB::commit();

            return redirect()->route('pembayaran.show', $kodeTransaksi)
                ->with('success', 'Pesanan berhasil dibuat, silakan lakukan pembayaran');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
