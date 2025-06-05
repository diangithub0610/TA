<?php

namespace App\Http\Controllers;

use App\Helpers\GenerateId;
use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class TokoController extends Controller
{
    protected $rajaOngkirService;

    public function __construct(RajaOngkirService $rajaOngkirService)
    {
        $this->rajaOngkirService = $rajaOngkirService;
    }

    public function index()
    {
        $toko = Toko::first();
        return view('admin.toko.index', compact('toko'));
    }

    public function searchDestination(Request $request)
    {
        $keyword = $request->input('keyword');

        // Gunakan RajaOngkirService yang sudah kita buat sebelumnya
        $rajaOngkir = new RajaOngkirService();
        $destinations = $rajaOngkir->searchDestination($keyword, 10);

        return response()->json($destinations);
    }

    public function save(Request $request)
    {
        $request->validate([
            'nama_toko' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:255',
            'provinsi' => 'nullable|string|max:50',
            'kota' => 'nullable|string|max:50',
            'kecamatan' => 'nullable|string|max:50',
            'kelurahan' => 'nullable|string|max:50',
            'kode_pos' => 'nullable|string|max:10',
            'rajaongkir_id' => 'nullable|string|max:20',
            'no_telp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:100',
        ]);

        $toko = Toko::first();

        if (!$toko) {
            $toko = new Toko();
            $toko->id_toko = 'TKO0000001';
        }

        $toko->nama_toko = $request->nama_toko;
        $toko->alamat = $request->alamat;
        $toko->provinsi = $request->provinsi;
        $toko->kota = $request->kota;
        $toko->kecamatan = $request->kecamatan;
        $toko->kelurahan = $request->kelurahan;
        $toko->kode_pos = $request->kode_pos;
        $toko->rajaongkir_id = $request->rajaongkir_id;
        $toko->no_telp = $request->no_telp;
        $toko->email = $request->email;

        $toko->save();

        // Bersihkan cache toko
        Cache::forget('toko_data');

        return redirect()->route('admin.toko.index')->with('success', 'Pengaturan toko berhasil disimpan');
    }

    public function updateBiayaPendaftaran(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'biaya_pendaftaran_reseller' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $toko = Toko::first();
        if ($toko) {
            $toko->update([
                'biaya_pendaftaran_reseller' => $request->biaya_pendaftaran_reseller
            ]);
        }

        return redirect()->back()->with('success', 'Biaya pendaftaran reseller berhasil diperbarui.');
    }
}
