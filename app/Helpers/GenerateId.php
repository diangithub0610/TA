<?php

namespace App\Helpers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Pembayaran;
use App\Models\Pengiriman;
use App\Models\DetailPengiriman;
use App\Models\Pelanggan;
use App\Models\Alamat;

class GenerateId
{
    public static function transaksi()
    {
        $today = now()->format('ymd');
        $lastOrder = Transaksi::where('kode_transaksi', 'like', "WF{$today}%")
            ->orderBy('kode_transaksi', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->kode_transaksi, 9);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "WF{$today}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function pembayaran()
    {
        $today = now()->format('ymd');
        $lastPayment = Pembayaran::where('id_pembayaran', 'like', "PAY{$today}%")
            ->orderBy('id_pembayaran', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->id_pembayaran, 9);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "PAY{$today}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function pengiriman()
    {
        $today = now()->format('ymd');
        $lastShipment = Pengiriman::where('kode_pengiriman', 'like', "SHP{$today}%")
            ->orderBy('kode_pengiriman', 'desc')
            ->first();

        if ($lastShipment) {
            $lastNumber = (int) substr($lastShipment->kode_pengiriman, 9);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "SHP{$today}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function pelanggan()
    {
        $lastCustomer = Pelanggan::orderBy('id_pelanggan', 'desc')->first();

        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->id_pelanggan, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "CUS" . str_pad($newNumber, 7, '0', STR_PAD_LEFT);
    }

    public static function alamat()
    {
        $lastAddress = Alamat::orderBy('id_alamat', 'desc')->first();

        if ($lastAddress) {
            $lastNumber = (int) substr($lastAddress->id_alamat, 3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "ADR" . str_pad($newNumber, 7, '0', STR_PAD_LEFT);
    }

    // public static function detailPengiriman()
    // {
    //     $today = now()->format('ymd');
    //     $lastDetail = DetailPengiriman::where('id_detail_pengiriman', 'like', "DSP{$today}%")
    //         ->orderBy('id_detail_pengiriman', 'desc')
    //         ->first();

    //     if ($lastDetail) {
    //         $lastNumber = (int) substr($lastDetail->id_detail_pengiriman, 9);
    //         $newNumber = $lastNumber + 1;
    //     } else {
    //         $newNumber = 1;
    //     }

    //     return "DSP{$today}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    // }

    // public static function detailTransaksi()
    // {
    //     $today = now()->format('ymd');
    //     $lastDetail = DetailTransaksi::where('id_detail_transaksi', 'like', "DTX{$today}%")
    //         ->orderBy('id_detail_transaksi', 'desc')
    //         ->first();

    //     if ($lastDetail) {
    //         $lastNumber = (int) substr($lastDetail->id_detail_transaksi, 9);
    //         $newNumber = $lastNumber + 1;
    //     } else {
    //         $newNumber = 1;
    //     }

    //     return "DTX{$today}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    // }
}
