<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPengiriman extends Model
{
    use HasFactory;

    protected $table = 'detail_pengiriman';
    // protected $primaryKey = 'id_detail_pengiriman';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        // 'id_detail_pengiriman',
        'kode_pengiriman',
        'lokasi',
        'waktu_update',
        'keterangan'
    ];

    protected $casts = [
        'waktu_update' => 'datetime',
    ];

    public function pengiriman()
    {
        return $this->belongsTo(Pengiriman::class, 'kode_pengiriman', 'kode_pengiriman');
    }
}
