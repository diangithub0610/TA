<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gambar extends Model
{
    protected $table = 'gambar';
    protected $primaryKey = 'kode_gambar';
    public $timestamps = false;

    protected $fillable = [
        'kode_barang', 'gambar'
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang');
    }
}
