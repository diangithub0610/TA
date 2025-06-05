<?php

namespace App\Http\Controllers;

use App\Models\PemusnahanBarang;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon; // pastikan di atas import Carbon

class PemusnahanBarangController extends Controller
{
    public function index()
    {
        // dd(Auth::user());
        // Ambil semua data pemusnahan
        $pemusnahanBarangs = PemusnahanBarang::all();

        return view('admin.pemusnahan.index', compact('pemusnahanBarangs'));
    }

    public function detail()
    {
        $barangs = Barang::with('tipe')->get();
        return view('admin.barang.index', compact('barangs'));
    }

    public function show($kode_barang)
    {
        $barang = Barang::with(['tipe', 'detailBarangs.warna'])->findOrFail($kode_barang);
        return view('admin.barang.show', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_detail' => 'required|exists:detail_barang,kode_detail',
            'jumlah' => 'required|integer|min:1',
            'bukti_gambar' => 'required|image|mimes:jpg,jpeg,png',
            'alasan' => 'required|string'
        ]);

        // Generate kode pemusnahan
        $tanggalHariIni = Carbon::now()->format('dmy'); // 120425
        $lastPemusnahan = \App\Models\PemusnahanBarang::whereDate('tanggal_pemusnahan', Carbon::today())
            ->orderBy('kode_pemusnahan', 'desc')
            ->first();

        if ($lastPemusnahan) {
            $lastNumber = intval(substr($lastPemusnahan->kode_pemusnahan, 9)); // ambil 001 nya
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $kodePemusnahan = 'DEL' . $tanggalHariIni . $newNumber;

        // Upload gambar
        $path = $request->file('bukti_gambar')->storeAs('pemusnahan', $kodePemusnahan . '.' . $request->file('bukti_gambar')->extension(), 'public');

        // Simpan ke database
        \App\Models\PemusnahanBarang::create([
            'kode_pemusnahan' => $kodePemusnahan,
            'kode_detail' => $request->kode_detail,
            'id_admin' => Auth::user()->id_admin,
            'tanggal_pemusnahan' => now(),
            'jumlah' => $request->jumlah,
            'alasan' => $request->alasan,
            'bukti_gambar' => $path,
            'status' => 'diajukan',
        ]);

        return redirect()->back()->with('success', 'Pemusnahan barang berhasil diajukan!');
    }

    public function persetujuan(Request $request, $kode_pemusnahan)
    {
        $request->validate([
            'aksi' => 'required|in:disetujui,ditolak',
        ]);
    
        $pemusnahan = PemusnahanBarang::where('kode_pemusnahan', $kode_pemusnahan)->firstOrFail();
    
        if ($request->aksi === 'disetujui') {
            $request->validate([
                'jumlah' => 'required|integer|min:1|max:' . $pemusnahan->jumlah,
            ], [
                'jumlah.max' => 'Jumlah yang disetujui tidak boleh melebihi jumlah pengajuan (' . $pemusnahan->jumlah . ').'
            ]);
    
            $pemusnahan->jumlah = $request->jumlah;
        }
    
        $pemusnahan->status = $request->aksi;
        $pemusnahan->save();
    
        return redirect()->route('pemusnahan-barang.index')->with('success', 'Status pemusnahan berhasil diperbarui.');
    }
    
    
}
