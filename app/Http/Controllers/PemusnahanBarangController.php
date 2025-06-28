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
            'jumlah_diajukan' => $request->jumlah,
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
    
        // Pastikan status masih dalam tahap pengajuan
        if ($pemusnahan->status !== 'diajukan') {
            return redirect()->route('pemusnahan-barang.index')
                ->with('error', 'Pemusnahan ini sudah diproses sebelumnya.');
        }
    
        if ($request->aksi === 'disetujui') {
            $request->validate([
                'jumlah' => 'required|integer|min:1|max:' . $pemusnahan->jumlah_diajukan,
            ], [
                'jumlah.max' => 'Jumlah yang disetujui tidak boleh melebihi jumlah pengajuan (' . $pemusnahan->jumlah_diajukan . ').'
            ]);
    
            // Set jumlah yang disetujui
            $pemusnahan->jumlah_disetujui = $request->jumlah;
    
            // Kurangi stok di detail_barang berdasarkan jumlah yang disetujui
            $detailBarang = $pemusnahan->detailBarang; // Pastikan relasi detailBarang() ada di model PemusnahanBarang
            
            if ($detailBarang) {
                // Cek apakah stok mencukupi
                if ($detailBarang->stok < $request->jumlah) {
                    return redirect()->back()
                        ->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $detailBarang->stok . ', diminta: ' . $request->jumlah);
                }
    
                // Kurangi stok sesuai jumlah yang disetujui
                $detailBarang->stok -= $request->jumlah;
                $detailBarang->save();
            } else {
                return redirect()->back()
                    ->with('error', 'Detail barang tidak ditemukan.');
            }
    
            $pemusnahan->status = 'disetujui';
            $statusMessage = 'Pemusnahan disetujui dan stok telah dikurangi sebanyak ' . $request->jumlah . ' unit.';
            
        } elseif ($request->aksi === 'ditolak') {
            // Jika ditolak, tidak ada pengurangan stok
            $pemusnahan->status = 'ditolak';
            $pemusnahan->jumlah_disetujui = 0; // Set ke 0 karena ditolak
            $statusMessage = 'Pemusnahan ditolak. Stok tidak dikurangi.';
        }
    
        // Set tanggal pemusnahan jika disetujui
        if ($request->aksi === 'disetujui') {
            $pemusnahan->tanggal_pemusnahan = now()->toDateString();
        }
    
        $pemusnahan->save();
    
        return redirect()->route('pemusnahan-barang.index')
            ->with('success', $statusMessage);
    }  
}
