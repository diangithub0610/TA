<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengiriman';
    protected $primaryKey = 'kode_pengiriman';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_pengiriman',
        'kode_transaksi',
        'status',
        'resi',
        'tanggal_pengiriman',
        'tanggal_estimasi_tiba',
        'tanggal_tiba'
    ];

    protected $casts = [
        'tanggal_pengiriman' => 'datetime',
        'tanggal_estimasi_tiba' => 'datetime',
        'tanggal_tiba' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'kode_transaksi', 'kode_transaksi');
    }

    public function detailPengiriman()
    {
        return $this->hasMany(DetailPengiriman::class, 'kode_pengiriman', 'kode_pengiriman');
    }

    // Aksesor untuk format status yang lebih user-friendly
    public function getStatusLabelAttribute()
    {
        $labels = [
            'disiapkan' => 'Sedang Disiapkan',
            'dikirim' => 'Sudah Dikirim',
            'dalam_perjalanan' => 'Dalam Perjalanan',
            'terkirim' => 'Terkirim'
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
