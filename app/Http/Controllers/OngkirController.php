<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Toko; // Pastikan model Toko sudah ada

class OngkirController extends Controller
{
    protected $baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
    }

    public function index()
    {
        // dd(Pelanggan::with('alamat')->find(auth()->guard('pelanggan')->id()));
        // Ambil data toko untuk origin
        $toko = Toko::first(); // Atau sesuaikan dengan logic pengambilan toko

        $id_pelanggan = Pelanggan::with('alamat')->find(auth()->guard('pelanggan')->id());

        // dd(Alamat::all());

        // Cek apakah pengguna sudah login
        $savedAddresses = Alamat::all();
        // $savedAddresses = auth('pelanggan')->check()
        //     ? Alamat::where('id_pelanggan', $id_pelanggan)->get()
        //     : collect();

            // dd($savedAddresses);

        return view('ongkir', [
            'toko' => $toko,
            'savedAddresses' => $savedAddresses
        ]);
    }

    public function searchDestination(Request $request)
    {
        $keyword = $request->input('keyword', '');

        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . '/destination/domestic-destination', [
                'search' => $keyword,
                'limit' => 10
            ]);

            if ($response->successful()) {
                return response()->json($response->json()['data'] ?? []);
            }

            return response()->json([], 400);
        } catch (\Exception $e) {
            Log::error('Destination Search Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function calculateCost(Request $request)
    {
        $request->validate([
            'destination' => 'required|string',
            'weight' => 'required|integer|min:100'
        ]);

        // Ambil data toko untuk origin
        $toko = Toko::first(); // Atau sesuaikan dengan logic pengambilan toko

        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($this->baseUrl . '/calculate/domestic-cost', [
                'origin' => $toko->rajaongkir_id, // Pastikan toko memiliki rajaongkir_id
                'destination' => $request->destination,
                'weight' => $request->weight,
                'courier' => 'jne:pos:tiki:jnt' // Bisa disesuaikan
            ]);

            if ($response->successful()) {
                return response()->json($response->json()['data'] ?? []);
            }

            return response()->json([], 400);
        } catch (\Exception $e) {
            Log::error('Ongkir Calculation Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
}
