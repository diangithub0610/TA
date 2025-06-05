<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan';

    protected $fillable = [
        'user_id',
        'kode_barang',
        'transaksi_id',
        'nama_reviewer',
        'rating',
        'komentar',
        'tanggal_review'
    ];

    protected $dates = [
        'tanggal_review',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'tanggal_review' => 'datetime'
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    // Relasi dengan Transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class);
    }

    // Scope untuk mendapatkan ulasan berdasarkan barang
    public function scopeByBarang($query, $kodeBarang)
    {
        return $query->where('kode_barang', $kodeBarang);
    }

    // Scope untuk mendapatkan ulasan berdasarkan user
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Method untuk mendapatkan rata-rata rating suatu barang
    public static function averageRatingByBarang($kodeBarang)
    {
        return self::where('kode_barang', $kodeBarang)->avg('rating');
    }

    // Method untuk mendapatkan total ulasan suatu barang
    public static function totalUlasanByBarang($kodeBarang)
    {
        return self::where('kode_barang', $kodeBarang)->count();
    }

    // Method untuk mengecek apakah user sudah memberikan ulasan untuk barang tertentu
    public static function hasUserReviewedBarang($userId, $kodeBarang)
    {
        return self::where('user_id', $userId)
                   ->where('kode_barang', $kodeBarang)
                   ->exists();
    }

    // Method untuk mengecek apakah user sudah membeli barang tersebut
    public static function hasUserPurchasedBarang($userId, $kodeBarang)
    {
        // Asumsi: tabel transaksi memiliki detail_transaksi atau field yang menunjukkan barang yang dibeli
        // Sesuaikan dengan struktur tabel transaksi Anda
        return DB::table('transaksi')
                  ->join('detail_transaksi', 'kode_transaksi', '=', 'detail_transaksi.kode_transaksi')
                  ->where('transaksi.id_pelanggan', $userId)
                  ->where('detail_transaksi.kode_barang', $kodeBarang)
                  ->where('transaksi.status', 'selesai') // Asumsi ada status transaksi
                  ->exists();
    }
}