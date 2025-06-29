<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengiriman;
use App\Models\Transaksi;
use App\Services\TrackingService;

class PengirimanController extends Controller
{
    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    /**
     * Menampilkan daftar pengiriman
     */
    public function index(Request $request)
    {
        $query = Pengiriman::with(['transaksi', 'transaksi.pelanggan'])
            ->orderBy('created_at', 'desc');

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status_pengiriman', $request->status);
        }

        // Filter berdasarkan ekspedisi
        if ($request->filled('ekspedisi')) {
            $query->where('ekspedisi', $request->ekspedisi);
        }

        // Search berdasarkan nomor resi atau kode transaksi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_resi', 'like', "%{$search}%")
                    ->orWhere('kode_transaksi', 'like', "%{$search}%");
            });
        }

        $pengiriman = $query->paginate(20);

        // Data untuk filter
        $statusList = [
            'menunggu_pengiriman' => 'Menunggu Pengiriman',
            'dikemas' => 'Sedang Dikemas',
            'diserahkan_ke_kurir' => 'Diserahkan ke Kurir',
            'dalam_perjalanan' => 'Dalam Perjalanan',
            'tiba_di_kota_tujuan' => 'Tiba di Kota Tujuan',
            'sedang_diantar' => 'Sedang Diantar',
            'terkirim' => 'Terkirim',
            'gagal_kirim' => 'Gagal Kirim'
        ];

        $ekspedisiList = Pengiriman::distinct()->pluck('ekspedisi')->filter();

        return view('admin.pengiriman.index', compact('pengiriman', 'statusList', 'ekspedisiList'));
    }

    /**
     * Menampilkan detail pengiriman
     */
    public function show($id)
    {
        $pengiriman = Pengiriman::with(['transaksi', 'transaksi.pelanggan', 'transaksi.alamat'])
            ->findOrFail($id);

        $trackingData = $this->trackingService->getPengirimanDenganTracking($id);

        return view('admin.pengiriman.show', compact('pengiriman', 'trackingData'));
    }

    /**
     * Form untuk update nomor resi
     */
    public function editResi($id)
    {
        $pengiriman = Pengiriman::with('transaksi')->findOrFail($id);
        return view('admin.pengiriman.edit-resi', compact('pengiriman'));
    }

    /**
     * Update nomor resi
     */
    public function updateResi(Request $request, $id)
    {
        $request->validate([
            'nomor_resi' => 'required|string|max:50',
            'catatan_pengiriman' => 'nullable|string'
        ]);

        try {
            $result = $this->trackingService->updateNomorResi($id, $request->nomor_resi);

            if ($result['success']) {
                // Update catatan jika ada
                if ($request->filled('catatan_pengiriman')) {
                    $pengiriman = Pengiriman::find($id);
                    $pengiriman->update(['catatan_pengiriman' => $request->catatan_pengiriman]);
                }

                return redirect()->route('admin.pengiriman.show', $id)
                    ->with('success', 'Nomor resi berhasil diupdate dan tracking dimulai');
            } else {
                return redirect()->back()
                    ->with('error', $result['message'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update tracking manual untuk satu pengiriman
     */
    public function updateTracking($id)
    {
        try {
            $pengiriman = Pengiriman::findOrFail($id);
            $result = $this->trackingService->updateTrackingTunggal($pengiriman);

            if ($result['success']) {
                return redirect()->back()->with('success', 'Tracking berhasil diupdate');
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Update tracking untuk semua pengiriman aktif
     */
    public function updateSemuaTracking()
    {
        try {
            $result = $this->trackingService->updateSemuaTracking();

            $message = "Update tracking selesai. Berhasil: {$result['berhasil']}, Gagal: {$result['gagal']}, Total: {$result['total']}";

            if ($result['gagal'] > 0) {
                return redirect()->back()->with('warning', $message);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Export data pengiriman
     */
    public function export(Request $request)
    {
        // Implement export logic here
        // Bisa menggunakan Laravel Excel atau export manual

        return redirect()->back()->with('info', 'Fitur export sedang dalam pengembangan');
    }

    /**
     * Dashboard pengiriman - statistik
     */
    public function dashboard()
    {
        $stats = [
            'total_pengiriman' => Pengiriman::count(),
            'menunggu_pengiriman' => Pengiriman::where('status_pengiriman', 'menunggu_pengiriman')->count(),
            'dalam_perjalanan' => Pengiriman::whereIn('status_pengiriman', ['dikemas', 'diserahkan_ke_kurir', 'dalam_perjalanan', 'tiba_di_kota_tujuan', 'sedang_diantar'])->count(),
            'terkirim' => Pengiriman::where('status_pengiriman', 'terkirim')->count(),
            'gagal_kirim' => Pengiriman::where('status_pengiriman', 'gagal_kirim')->count(),
        ];

        // Pengiriman terbaru yang perlu nomor resi
        $perluResi = Pengiriman::with(['transaksi', 'transaksi.pelanggan'])
            ->whereNull('nomor_resi')
            ->where('status_pengiriman', 'menunggu_pengiriman')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Pengiriman yang sudah lama tidak diupdate
        $perluUpdate = Pengiriman::with(['transaksi', 'transaksi.pelanggan'])
            ->whereNotNull('nomor_resi')
            ->whereNotIn('status_pengiriman', ['terkirim', 'gagal_kirim'])
            ->where(function ($query) {
                $query->whereNull('terakhir_update_tracking')
                    ->orWhere('terakhir_update_tracking', '<', now()->subHours(6));
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.pengiriman.dashboard', compact('stats', 'perluResi', 'perluUpdate'));
    }
}
