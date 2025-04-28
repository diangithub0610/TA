<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KeranjangService;
use App\Models\DetailBarang;

class KeranjangController extends Controller
{
    protected $keranjangService;

    public function __construct(KeranjangService $keranjangService)
    {
        $this->keranjangService = $keranjangService;
    }

    public function index()
    {
        // Refresh stok keranjang sebelum ditampilkan
        $this->keranjangService->refreshStok();
        $keranjang = $this->keranjangService->getKeranjang();
        $subtotal = $this->keranjangService->hitungSubtotal();

        return view('pelanggan.transaksi.keranjang', compact('keranjang', 'subtotal'));
    }

    public function tambah(Request $request)
    {
        $request->validate([
            'kode_detail' => 'required|exists:detail_barang,kode_detail',
            'jumlah' => 'required|integer|min:1'
        ]);

        $hasil = $this->keranjangService->tambahItem($request->kode_detail, $request->jumlah);

        if ($request->ajax()) {
            return response()->json($hasil);
        }

        if ($hasil['status'] === 'success') {
            return redirect()->back()->with('success', $hasil['message']);
        } else {
            return redirect()->back()->with('error', $hasil['message']);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'kode_detail' => 'required|exists:detail_barang,kode_detail',
            'jumlah' => 'required|integer|min:1'
        ]);

        $hasil = $this->keranjangService->updateItem($request->kode_detail, $request->jumlah);

        if ($request->ajax()) {
            return response()->json($hasil);
        }

        if ($hasil['status'] === 'success') {
            return redirect()->back()->with('success', $hasil['message']);
        } else {
            return redirect()->back()->with('error', $hasil['message']);
        }
    }

    public function hapus(Request $request, $kodeDetail)
    {
        $hasil = $this->keranjangService->hapusItem($kodeDetail);

        if ($request->ajax()) {
            return response()->json($hasil);
        }

        if ($hasil['status'] === 'success') {
            return redirect()->back()->with('success', $hasil['message']);
        } else {
            return redirect()->back()->with('error', $hasil['message']);
        }
    }

    public function kosongkan(Request $request)
    {
        $hasil = $this->keranjangService->kosongkanKeranjang();

        if ($request->ajax()) {
            return response()->json($hasil);
        }

        return redirect()->back()->with('success', $hasil['message']);
    }

    public function getJumlahKeranjang()
    {
        return response()->json([
            'jumlah' => $this->keranjangService->jumlahItem()
        ]);
    }

    public function checkStok(Request $request)
    {
        $request->validate([
            'kode_detail' => 'required|exists:detail_barang,kode_detail',
            'jumlah' => 'required|integer|min:1'
        ]);

        $detailBarang = DetailBarang::find($request->kode_detail);

        if (!$detailBarang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ]);
        }

        if ($detailBarang->stok < $request->jumlah) {
            return response()->json([
                'status' => 'error',
                'message' => 'Stok tidak mencukupi',
                'stok_tersedia' => $detailBarang->stok
            ]);
        }

        return response()->json([
            'status' => 'success',
            'stok_tersedia' => $detailBarang->stok
        ]);
    }
}
