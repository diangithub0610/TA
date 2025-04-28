<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $table = 'toko';
    protected $primaryKey = 'id_toko';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_toko',
        'nama_toko',
        'alamat',
        'provinsi',
        'provinsi_id',
        'kota',
        'kota_id',
        'kecamatan',
        'kecamatan_id',
        'kelurahan',
        'kode_pos',
        'rajaongkir_id',
        'no_telp',
        'email'
    ];
}
