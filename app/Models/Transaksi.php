<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'kode_transaksi';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_transaksi',
        'id_pelanggan',
        'id_pengguna',
        'tanggal_transaksi',
        'id_alamat',
        'ongkir',
        'keterangan',
        'ekspedisi',
        'layanan_ekspedisi',
        'estimasi_waktu',
        'status',
        'jenis'
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna', 'id_pengguna');
    }

    public function alamat()
    {
        return $this->belongsTo(Alamat::class, 'id_alamat', 'id_alamat');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'kode_transaksi', 'kode_transaksi');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'kode_transaksi', 'kode_transaksi');
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'kode_transaksi', 'kode_transaksi');
    }

    // Aksesor untuk subtotal
    public function getSubtotalAttribute()
    {
        return $this->detailTransaksi->sum(function ($detail) {
            return $detail->kuantitas * $detail->harga;
        });
    }

    // Aksesor untuk total
    public function getTotalAttribute()
    {
        return $this->getSubtotalAttribute() + $this->ongkir;
    }

    // Aksesor untuk format tanggal yang lebih user-friendly
    public function getTanggalFormatAttribute()
    {
        return \Carbon\Carbon::parse($this->tanggal_transaksi)->format('d F Y H:i');
    }

    // Aksesor untuk format status yang lebih user-friendly
    public function getStatusLabelAttribute()
    {
        $labels = [
            'belum_dibayar' => 'Belum Dibayar',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan'
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
