<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\DetailBarang;
use Illuminate\Support\Facades\Auth;

class KeranjangService
{
    public function getKeranjang()
    {
        if (!session()->has('keranjang')) {
            session(['keranjang' => []]);
        }

        return session('keranjang');
    }

    public function tambahItem($kodeDetail, $jumlah = 1)
    {
        $keranjang = $this->getKeranjang();
    
        // Cek apakah detail barang valid
        $detailBarang = DetailBarang::with('barang', 'barang.tipe', 'warna')
            ->where('kode_detail', $kodeDetail)
            ->first();
    
        if (!$detailBarang || $detailBarang->stok < 1) {
            return [
                'status' => 'error',
                'message' => 'Barang tidak tersedia'
            ];
        }
    
        // Cek apakah jumlah melebihi stok
        if ($jumlah > $detailBarang->stok) {
            return [
                'status' => 'error',
                'message' => 'Jumlah melebihi stok yang tersedia'
            ];
        }
    
        // Cek apakah barang sudah ada di keranjang
        if (isset($keranjang[$kodeDetail])) {
            $newJumlah = $keranjang[$kodeDetail]['jumlah'] + $jumlah;
    
            // Cek apakah penambahan melebihi stok
            if ($newJumlah > $detailBarang->stok) {
                return [
                    'status' => 'error',
                    'message' => 'Jumlah melebihi stok yang tersedia'
                ];
            }
    
            $keranjang[$kodeDetail]['jumlah'] = $newJumlah;
        } else {
            // Hitung harga berdasarkan role pelanggan
            $pelanggan = Auth::guard('pelanggan')->user();
    
            // Gunakan method getHargaByRole untuk mendapatkan harga yang sesuai
            $harga = $detailBarang->getHargaByRole($pelanggan ? $pelanggan->role : 'guest');
    
            $keranjang[$kodeDetail] = [
                'kode_detail' => $kodeDetail,
                'kode_barang' => $detailBarang->kode_barang,
                'nama_barang' => $detailBarang->barang->nama_barang,
                'gambar' => $detailBarang->barang->gambar,
                'warna' => $detailBarang->warna->warna,
                'kode_hex' => $detailBarang->warna->kode_hex,
                'ukuran' => $detailBarang->ukuran,
                'harga' => $harga,
                'jumlah' => $jumlah,
                'stok' => $detailBarang->stok
            ];
        }
    
        session(['keranjang' => $keranjang]);
    
        return [
            'status' => 'success',
            'message' => 'Barang berhasil ditambahkan ke keranjang',
            'jumlah_item' => count($keranjang)
        ];
    }

    public function updateItem($kodeDetail, $jumlah)
    {
        $keranjang = $this->getKeranjang();

        if (!isset($keranjang[$kodeDetail])) {
            return [
                'status' => 'error',
                'message' => 'Barang tidak ditemukan di keranjang'
            ];
        }

        // Cek apakah jumlah valid
        if ($jumlah < 1) {
            return [
                'status' => 'error',
                'message' => 'Jumlah minimal adalah 1'
            ];
        }

        // Cek stok terkini
        $detailBarang = DetailBarang::find($kodeDetail);
        if (!$detailBarang || $jumlah > $detailBarang->stok) {
            return [
                'status' => 'error',
                'message' => 'Jumlah melebihi stok yang tersedia'
            ];
        }

        $keranjang[$kodeDetail]['jumlah'] = $jumlah;
        $keranjang[$kodeDetail]['stok'] = $detailBarang->stok;

        session(['keranjang' => $keranjang]);

        return [
            'status' => 'success',
            'message' => 'Keranjang berhasil diperbarui',
            'subtotal' => $this->hitungSubtotal()
        ];
    }

    public function hapusItem($kodeDetail)
    {
        $keranjang = $this->getKeranjang();

        if (isset($keranjang[$kodeDetail])) {
            unset($keranjang[$kodeDetail]);
            session(['keranjang' => $keranjang]);

            return [
                'status' => 'success',
                'message' => 'Barang berhasil dihapus dari keranjang',
                'jumlah_item' => count($keranjang)
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Barang tidak ditemukan di keranjang'
        ];
    }

    public function kosongkanKeranjang()
    {
        session(['keranjang' => []]);

        return [
            'status' => 'success',
            'message' => 'Keranjang berhasil dikosongkan'
        ];
    }

    public function hitungSubtotal()
    {
        $keranjang = $this->getKeranjang();
        $subtotal = 0;

        foreach ($keranjang as $item) {
            $subtotal += $item['harga'] * $item['jumlah'];
        }

        return $subtotal;
    }

    public function jumlahItem()
    {
        return count($this->getKeranjang());
    }

    public function refreshStok()
    {
        $keranjang = $this->getKeranjang();
        $updated = false;

        foreach ($keranjang as $kodeDetail => $item) {
            $detailBarang = DetailBarang::find($kodeDetail);

            if (!$detailBarang) {
                unset($keranjang[$kodeDetail]);
                $updated = true;
                continue;
            }

            $keranjang[$kodeDetail]['stok'] = $detailBarang->stok;

            if ($item['jumlah'] > $detailBarang->stok) {
                $keranjang[$kodeDetail]['jumlah'] = $detailBarang->stok;
                $updated = true;
            }

            if ($detailBarang->stok < 1) {
                unset($keranjang[$kodeDetail]);
                $updated = true;
            }
        }

        session(['keranjang' => $keranjang]);

        return [
            'updated' => $updated,
            'keranjang' => $keranjang
        ];
    }
    public function getJumlahKeranjang()
{
    $keranjang = $this->getKeranjang();
    return collect($keranjang)->sum('jumlah');
}

}

