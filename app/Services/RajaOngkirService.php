<?php

namespace App\Services;

use App\Models\Toko;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaOngkirService
{
    protected $baseUrl = 'https://rajaongkir.komerce.id/api/v1';
    protected $apiKey;
    protected $cachePrefix = 'raja_ongkir_';

    public function __construct()
    {
        $this->apiKey = config('services.rajaongkir.key');
    }

    

    /**
     * Search destinations based on keyword
     * 
     * @param string $keyword
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function searchDestination(string $keyword, int $limit = 10, int $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'destination_' . md5($keyword . $limit . $offset);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($keyword, $limit, $offset) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . '/destination/domestic-destination', [
                    'search' => $keyword,
                    'limit' => $limit,
                    'offset' => $offset
                ]);

                return $response->successful() ? $response->json()['data'] : [];
            } catch (\Exception $e) {
                Log::error('Raja Ongkir Search Destination Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Calculate domestic shipping cost
     * 
     * @param string $origin
     * @param string $destination
     * @param int $weight
     * @param string $courier
     * @param string $price
     * @return array
     */
    public function calculateDomesticCost(
        string $origin,
        string $destination,
        int $weight,
        string $courier = 'jne:pos:sicepat',
        string $price = 'lowest'
    ) {
        $cacheKey = $this->cachePrefix . 'domestic_cost_' . md5($origin . $destination . $weight . $courier . $price);

        return Cache::remember($cacheKey, now()->addHours(6), function () use (
            $origin,
            $destination,
            $weight,
            $courier,
            $price
        ) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->asForm()->post($this->baseUrl . '/calculate/domestic-cost', [
                    'origin' => $origin,
                    'destination' => $destination,
                    'weight' => $weight,
                    'courier' => $courier,
                    'price' => $price
                ]);

                $data = $response->json();

                // Log untuk debugging
                Log::info('Raja Ongkir Response', [
                    'status' => $response->status(),
                    'data' => $data
                ]);

                return $response->successful() ? $data['data'] : [];
            } catch (\Exception $e) {
                Log::error('Raja Ongkir Calculate Domestic Cost Error: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get cached destinations
     * 
     * @param string $keyword
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getCachedDestinations(string $keyword, int $limit = 10, int $offset = 0)
    {
        $cacheKey = $this->cachePrefix . 'destinations_' . md5($keyword . $limit . $offset);
        return Cache::get($cacheKey, []);
    }

    /**
     * Get details of a specific destination by ID
     * 
     * @param int $destinationId
     * @return array|null
     */
    public function getDestinationDetails($destinationId)
    {
        try {
            $response = Http::withHeaders([
                'key' => $this->apiKey
            ])->get($this->baseUrl . '/destination/domestic-destination', [
                'search' => $destinationId,
                'limit' => 1
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return !empty($data) ? $data[0] : null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Get Destination Details Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract specific location details
     * 
     * @param array $destination
     * @return array
     */
    public function extractLocationDetails(array $destination)
    {
        return [
            'province' => $destination['province_name'] ?? null,
            'city' => $destination['city_name'] ?? null,
            'district' => $destination['district_name'] ?? null,
            'subdistrict' => $destination['subdistrict_name'] ?? null,
            'zip_code' => $destination['zip_code'] ?? null
        ];
    }

    /**
     * Get unique list of provinces
     * 
     * @return array
     */
    public function getProvinces()
    {
        $cacheKey = $this->cachePrefix . 'provinces';

        return Cache::remember($cacheKey, now()->addDays(7), function () {
            $provinces = collect($this->searchDestination(''))
                ->pluck('province_name')
                ->unique()
                ->values()
                ->toArray();

            return $provinces;
        });
    }

    /**
     * Get cities within a specific province
     * 
     * @param string|null $provinceName
     * @return array
     */
    public function getCities(?string $provinceName = null)
    {
        $cacheKey = $this->cachePrefix . 'cities_' . md5($provinceName ?? 'all');

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($provinceName) {
            $query = $provinceName ? $provinceName : '';
            $destinations = $this->searchDestination($query);

            $cities = collect($destinations)
                ->when($provinceName, fn($collection) => $collection->where('province_name', $provinceName))
                ->pluck('city_name')
                ->unique()
                ->values()
                ->toArray();

            return $cities;
        });
    }

    /**
     * Get districts within a specific city
     * 
     * @param string|null $cityName
     * @return array
     */
    public function getDistricts(?string $cityName = null)
    {
        $cacheKey = $this->cachePrefix . 'districts_' . md5($cityName ?? 'all');

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($cityName) {
            $query = $cityName ? $cityName : '';
            $destinations = $this->searchDestination($query);

            $districts = collect($destinations)
                ->when($cityName, fn($collection) => $collection->where('city_name', $cityName))
                ->pluck('district_name')
                ->unique()
                ->values()
                ->toArray();

            return $districts;
        });
    }

    /**
     * Get available couriers
     * 
     * @return array
     */
    public function getAvailableCouriers()
    {
        return [
            'jne' => 'JNE',
            'sicepat' => 'SiCepat',
            'ide' => 'IDExpress',
            'sap' => 'SAP Express',
            'jnt' => 'J&T Express',
            'ninja' => 'Ninja Express',
            'tiki' => 'TIKI',
            'lion' => 'Lion Parcel',
            'anteraja' => 'AnterAja',
            'pos' => 'POS Indonesia',
            'ncs' => 'Nusantara Card Semesta',
            'rex' => 'Rex Express',
            'rpx' => 'RPX',
            'sentral' => 'Sentral',
            'star' => 'Star Cargo',
            'wahana' => 'Wahana',
            'dse' => 'DSE Express'
        ];
    }

    public function getOriginId()
    {
        // Ambil toko pertama atau toko utama
        $toko = Toko::whereNotNull('rajaongkir_id')->first();

        if (!$toko || !$toko->rajaongkir_id) {
            throw new \Exception('Origin ID untuk RajaOngkir tidak ditemukan');
        }

        return $toko->rajaongkir_id;
    }

    
}