<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CustomerOrderController extends Controller
{
    public function show($kode_transaksi)
    {
        $user = Auth::guard('pelanggan')->user();
        $id_pelanggan = $user->id_pelanggan;
        // Ambil transaksi dengan eager loading
        $transaksi = Transaksi::with([
            'pelanggan',
            'pengiriman',
            'detailTransaksi.detailBarang.barang',
            'detailTransaksi.detailBarang.warna'
        ])
        ->where('kode_transaksi', $kode_transaksi)
        ->where('id_pelanggan', $id_pelanggan) // Pastikan hanya pelanggan yang bersangkutan yang bisa akses
        ->firstOrFail();

        // Hitung subtotal
        $subtotal = $transaksi->detailTransaksi->sum(function($detail) {
            return $detail->kuantitas * $detail->harga;
        });

        // Hitung total
        $total = $subtotal + $transaksi->ongkir - ($transaksi->diskon ?? 0);

        return view('pelanggan.transaksi.show', compact('transaksi', 'subtotal', 'total'));
    }

    public function index()
    {
        $transaksiList = Transaksi::with(['detailTransaksi.detailBarang.barang'])
            ->where('id_pelanggan', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.orders.index', compact('transaksiList'));
    }

    public function downloadInvoice($kode_transaksi)
    {
        $pelanggan = auth()->guard('pelanggan')->user();
        $id_pelanggan = $pelanggan ? $pelanggan->id_pelanggan : null;

        // Ambil data transaksi
        $transaksi = Transaksi::with([
            'pelanggan',
            'pengiriman',
            'detailTransaksi.detailBarang.barang',
            'detailTransaksi.detailBarang.warna'
        ])
        ->where('kode_transaksi', $kode_transaksi)
        ->where('id_pelanggan', $id_pelanggan)
        ->firstOrFail();
    
        // Hitung subtotal
        $subtotal = $transaksi->detailTransaksi->sum(function($detail) {
            return $detail->kuantitas * $detail->harga;
        });
    
        // Hitung total
        $total = $subtotal + $transaksi->ongkir - ($transaksi->diskon ?? 0);
    
        // Data untuk PDF
        $data = [
            'transaksi' => $transaksi,
            'subtotal' => $subtotal,
            'total' => $total,
            'title' => 'Invoice - ' . $kode_transaksi
        ];
      
      
        // Generate PDF menggunakan DomPDF
        $pdf = Pdf::loadView('pelanggan.transaksi.invoice', $data);
        
        // Set paper size dan orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Set options untuk PDF
        $pdf->setOptions([
            'defaultFont' => 'sans-serif', 
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultPaperSize' => 'A4',
            'dpi' => 150,
            'fontHeightRatio' => 1.1,
            'isPhpEnabled' => true
        ]);

    
        // Nama file PDF
        $filename = 'invoice-' . $kode_transaksi . '-' . date('Y-m-d') . '.pdf';
    
        // Download PDF
        return $pdf->download($filename);
    }

    public function viewInvoice($kode_transaksi)
    {
        // Method tambahan untuk melihat invoice di browser tanpa download
        $transaksi = Transaksi::with([
            'pelanggan',
            'pengiriman',
            'detailTransaksi.detailBarang.barang',
            'detailTransaksi.detailBarang.warna'
        ])
        ->where('kode_transaksi', $kode_transaksi)
        ->where('id_pelanggan', Auth::id())
        ->firstOrFail();

        $subtotal = $transaksi->detailTransaksi->sum(function($detail) {
            return $detail->kuantitas * $detail->harga;
        });

        $total = $subtotal + $transaksi->ongkir - ($transaksi->diskon ?? 0);

        $data = [
            'transaksi' => $transaksi,
            'subtotal' => $subtotal,
            'total' => $total,
            'title' => 'Invoice - ' . $kode_transaksi
        ];

        $pdf = Pdf::loadView('customer.orders.invoice', $data);
        $pdf->setPaper('A4', 'portrait');

        // Stream PDF ke browser
        return $pdf->stream('invoice-' . $kode_transaksi . '.pdf');
    }

    public function saveInvoice($kode_transaksi)
    {
        // Method tambahan untuk menyimpan invoice ke storage
        $transaksi = Transaksi::with([
            'pelanggan',
            'pengiriman',
            'detailTransaksi.detailBarang.barang',
            'detailTransaksi.detailBarang.warna'
        ])
        ->where('kode_transaksi', $kode_transaksi)
        ->where('id_pelanggan', Auth::id())
        ->firstOrFail();

        $subtotal = $transaksi->detailTransaksi->sum(function($detail) {
            return $detail->kuantitas * $detail->harga;
        });

        $total = $subtotal + $transaksi->ongkir - ($transaksi->diskon ?? 0);

        $data = [
            'transaksi' => $transaksi,
            'subtotal' => $subtotal,
            'total' => $total,
            'title' => 'Invoice - ' . $kode_transaksi
        ];

        $pdf = Pdf::loadView('customer.orders.invoice', $data);
        $pdf->setPaper('A4', 'portrait');

        // Simpan ke storage
        $filename = 'invoices/invoice-' . $kode_transaksi . '-' . date('Y-m-d') . '.pdf';
        Storage::put($filename, $pdf->output());

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil disimpan',
            'file_path' => $filename
        ]);
    }
}