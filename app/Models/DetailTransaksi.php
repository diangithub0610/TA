<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    use HasFactory;

    protected $table = 'detail_transaksi';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        // 'id_detail_transaksi',
        'kode_transaksi',
        'kode_detail',
        'kuantitas',
        'harga'
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'kode_transaksi', 'kode_transaksi');
    }

    public function detailBarang()
    {
        return $this->belongsTo(DetailBarang::class, 'kode_detail', 'kode_detail');
    }

    // Aksesor untuk subtotal
    public function getSubtotalAttribute()
    {
        return $this->kuantitas * $this->harga;
    }
}
