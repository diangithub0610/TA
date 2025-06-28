<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Tipe extends Model
{
    protected $table = 'tipe';
    protected $primaryKey = 'kode_tipe';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_tipe',
        'nama_tipe',
        'kode_brand',
        // 'potongan_harga'
    ];

    // Relasi dengan Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'kode_brand', 'kode_brand');
    }

    // Formatting potongan harga
    // public function getFormattedPotonganHargaAttribute()
    // {
    //     return 'Rp ' . number_format($this->potongan_harga, 0, ',', '.');
    // }

    public static function generateKodeTipe($nama_tipe, $kode_brand)
    {
        // Ambil 2-4 huruf pertama dari nama tipe (huruf besar)
        $nama_singkat = Str::upper(Str::substr($nama_tipe, 0, 4));

        // Hilangkan vocal
        $nama_singkat = preg_replace('/[AIUEO]/', '', $nama_singkat);

        // Pastikan panjang 3 huruf
        $nama_singkat = Str::substr($nama_singkat, 0, 3);

        // Gabungkan dengan kode brand
        return $nama_singkat . '-' . $kode_brand;
    }
}
