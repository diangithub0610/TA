<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Factory;

class PesananController extends Controller
{
    public function index()
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        // Ambil semua transaksi milik pelanggan yang sedang login, terbaru duluan
        $transaksis = Transaksi::with(['detailTransaksi.barang'])
            ->where('id_pelanggan', $pelanggan->id_pelanggan)
            ->orderBy('tanggal_transaksi', 'desc')
            ->get();

        return view('pelanggan.transaksi.daftar-pesanan', compact('transaksis'));
    }

}
