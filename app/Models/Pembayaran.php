<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_pembayaran',
        'kode_transaksi',
        'tipe_pembayaran',
        'metode_pembayaran',
        'jumlah',
        'status',
        'tanggal_pembayaran',
        'kadaluarsa_pembayaran',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'payment_token',
        'virtual_account',
        'pdf_url',
        'snap_token'
    ];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
        'kadaluarsa_pembayaran' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'kode_transaksi', 'kode_transaksi');
    }

    // Aksesor untuk format status yang lebih user-friendly
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu Pembayaran',
            'sukses' => 'Pembayaran Sukses',
            'gagal' => 'Pembayaran Gagal',
            'kadaluarsa' => 'Pembayaran Kadaluarsa'
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
