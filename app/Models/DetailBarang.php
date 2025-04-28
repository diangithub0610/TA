<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model
{
    protected $table = 'detail_barang';
    protected $primaryKey = 'kode_detail';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_detail',
        'kode_barang',
        'stok',
        'ukuran',
        'kode_warna'
    ];

    // Relasi dengan Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    // Relasi dengan Warna
    public function warna()
    {
        return $this->belongsTo(Warna::class, 'kode_warna', 'kode_warna');
    }

    // Method untuk generate kode detail barang
    public static function generateKodeDetail($kode_barang, $kode_warna, $nextNumber = 1)
    {
        $prefix = "{$kode_barang}-{$kode_warna}";
        $formattedNumber = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        return "{$prefix}-{$formattedNumber}";
    }

    public function getHargaPelangganAttribute()
    {
        // Menaikkan harga normal sebesar 10% untuk pelanggan biasa
        return $this->harga_setelah_potongan * 1.1;
    }
}
