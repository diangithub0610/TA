<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailBarangMasuk extends Model
{
    protected $table = 'detail_barang_masuk';
    public $timestamps = false;

    protected $fillable = ['kode_pembelian', 'kode_barang', 'jumlah', 'harga_barang_masuk'];

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class, 'kode_pembelian', 'kode_pembelian');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }
}
