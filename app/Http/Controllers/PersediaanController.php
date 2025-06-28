<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Tipe;
use App\Models\Brand;
use App\Models\Warna;
use App\Models\Barang;
use App\Models\DetailBarang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PersediaanExport;


class PersediaanController extends Controller
{
    public function index(Request $request)
    {
        // Filter
        $warnaFilter = $request->input('warna');
        $brandFilter = $request->input('brand');
        $tipeFilter = $request->input('tipe');

        $detailBarangs = DetailBarang::with(['barang.tipe.brand', 'warna'])
            ->when($warnaFilter, function ($query, $warnaFilter) {
                $query->where('kode_warna', $warnaFilter);
            })
            ->when($brandFilter, function ($query, $brandFilter) {
                $query->whereHas('barang.tipe.brand', function ($q) use ($brandFilter) {
                    $q->where('kode_brand', $brandFilter);
                });
            })
            ->when($tipeFilter, function ($query, $tipeFilter) {
                $query->whereHas('barang.tipe', function ($q) use ($tipeFilter) {
                    $q->where('kode_tipe', $tipeFilter);
                });
            })
            ->get();

        return view('admin.persediaan.index', [
            'detailBarangs' => $detailBarangs,
            'warnas' => Warna::all(),
            'brands' => Brand::all(),
            'tipes' => Tipe::all(),
            'selectedWarna' => $warnaFilter,
            'selectedBrand' => $brandFilter,
            'selectedTipe' => $tipeFilter,
        ]);
    }
    public function exportPdf(Request $request)
    {
        // Filter yang sama dengan index
        $warnaFilter = $request->input('warna');
        $brandFilter = $request->input('brand');
        $tipeFilter = $request->input('tipe');

        $detailBarangs = DetailBarang::with(['barang.tipe.brand', 'warna'])
            ->when($warnaFilter, function ($query, $warnaFilter) {
                $query->where('kode_warna', $warnaFilter);
            })
            ->when($brandFilter, function ($query, $brandFilter) {
                $query->whereHas('barang.tipe.brand', function ($q) use ($brandFilter) {
                    $q->where('kode_brand', $brandFilter);
                });
            })
            ->when($tipeFilter, function ($query, $tipeFilter) {
                $query->whereHas('barang.tipe', function ($q) use ($tipeFilter) {
                    $q->where('kode_tipe', $tipeFilter);
                });
            })
            ->get();

        // Get filter names for display
        $warnaName = $warnaFilter ? Warna::where('kode_warna', $warnaFilter)->first()?->warna : 'Semua';
        $brandName = $brandFilter ? Brand::where('kode_brand', $brandFilter)->first()?->nama_brand : 'Semua';
        $tipeName = $tipeFilter ? Tipe::where('kode_tipe', $tipeFilter)->first()?->nama_tipe : 'Semua';

        Carbon::setLocale('id');
        $tanggal = Carbon::now()->translatedFormat('d F Y');

        $data = [
            'detailBarangs' => $detailBarangs,
            'tanggal' => Carbon::parse($tanggal)->translatedFormat('d F Y'),
            'admin' => optional(auth('admin')->user())->nama_admin ?? 'Administrator',
            'filters' => [
                'warna' => $warnaName,
                'brand' => $brandName,
                'tipe' => $tipeName
            ]
        ];

        $pdf = Pdf::loadView('admin.persediaan.pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('laporan-persediaan-' . now()->format('Y-m-d') . '.pdf');
    }
}

