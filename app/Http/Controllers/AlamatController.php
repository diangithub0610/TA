<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AlamatRequest;
use Illuminate\Support\Facades\Http;

class AlamatController extends Controller
{
    
    protected $baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
        $this->middleware('auth:pelanggan');
    }

    public function index()
    {
        // dd(auth()->guard('pelanggan')->user()->id);
        $alamat = Alamat::where('id_pelanggan', auth()->user()->id_pelanggan)->get();
        return view('pelanggan.alamat.index', compact('alamat'));
    }

    public function searchLocation(Request $request)
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
                $locations = $response->json()['data'] ?? [];

                // Transform the response to include all necessary details
                $formattedLocations = array_map(function ($location) {
                    return [
                        'id' => $location['id'],
                        'full_address' => sprintf(
                            "%s, %s, %s, %s %s",
                            $location['subdistrict_name'],
                            $location['district_name'],
                            $location['city_name'],
                            $location['province_name'],
                            $location['zip_code']
                        ),
                        'subdistrict' => $location['subdistrict_name'],
                        'district' => $location['district_name'],
                        'city' => $location['city_name'],
                        'province' => $location['province_name'],
                        'zip_code' => $location['zip_code']
                    ];
                }, $locations);

                return response()->json($formattedLocations);
            }

            return response()->json([], 400);
        } catch (\Exception $e) {
            Log::error('Location Search Error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function create()
    {
        return view('pelanggan.alamat.create');
    }

    public function store(AlamatRequest $request)
    {
        $validatedData = $request->validated();

        // Validate that the location details are complete
        if (
            !isset($validatedData['rajaongkir_id']) ||
            !isset($validatedData['provinsi']) ||
            !isset($validatedData['kota']) ||
            !isset($validatedData['kecamatan']) ||
            !isset($validatedData['kode_pos'])
        ) {
            return back()->withErrors(['msg' => 'Lengkapi informasi lokasi terlebih dahulu']);
        }

        try {
            $alamat = new Alamat($validatedData);
            $alamat->id_pelanggan = auth()->user()->id_pelanggan;
            $alamat->kecamatan_id = $validatedData['rajaongkir_id']; // Simpan kecamatan_id
            $alamat->save();

            return redirect()->route('alamat.index')
                ->with('success', 'Alamat berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Gagal menyimpan alamat: ' . $e->getMessage()]);
        }
    }

    public function edit(Alamat $alamat)
    {
        $this->authorize('update', $alamat);
        return view('pelanggan.alamat.edit', compact('alamat'));
    }

    public function update(AlamatRequest $request, Alamat $alamat)
    {
        $this->authorize('update', $alamat);

        $validatedData = $request->validated();

        // Validate that the location details are complete
        if (
            !isset($validatedData['rajaongkir_id']) ||
            !isset($validatedData['provinsi']) ||
            !isset($validatedData['kota']) ||
            !isset($validatedData['kecamatan']) ||
            !isset($validatedData['kode_pos'])
        ) {
            return back()->withErrors(['msg' => 'Lengkapi informasi lokasi terlebih dahulu']);
        }

        try {
            $alamat->fill($validatedData);
            $alamat->kecamatan_id = $validatedData['rajaongkir_id']; // Simpan kecamatan_id
            $alamat->save();

            return redirect()->route('alamat.index')
                ->with('success', 'Alamat berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Gagal memperbarui alamat: ' . $e->getMessage()]);
        }
    }

    public function destroy(Alamat $alamat)
    {
        $this->authorize('delete', $alamat);
        $alamat->delete();

        return redirect()->route('alamat.index')
            ->with('success', 'Alamat berhasil dihapus');
    }

    // Method to get saved addresses for shipping cost calculation
    public function getSavedAddresses()
    {
        return Alamat::where('id_pelanggan', auth()->user()->id_pelanggan)->get();
    }
}
