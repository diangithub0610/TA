<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'kode_barang';
    public $incrementing = false;
    // public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'berat',
        'harga_beli',
        'harga_normal',
        'deskripsi',
        'gambar',
        'kode_tipe'
    ];

    // Relasi dengan Tipe
    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'kode_tipe', 'kode_tipe');
    }

    // Relasi dengan Detail Barang
    public function detailBarangs()
    {
        return $this->hasMany(DetailBarang::class, 'kode_barang', 'kode_barang');
    }

    public function detailBarang()
    {
        return $this->hasMany(DetailBarang::class, 'kode_barang', 'kode_barang');
    }

    // Method untuk generate kode barang otomatis
    public static function generateKodeBarang($kode_tipe)
    {
        // Cari nomor terakhir untuk tipe ini
        $last_barang = self::where('kode_tipe', $kode_tipe)
            ->orderBy('kode_barang', 'desc')
            ->first();

        if (!$last_barang) {
            // Jika belum ada barang dengan tipe ini, mulai dari 1
            return "1-{$kode_tipe}";
        }

        // Ambil nomor terakhir dan increment
        $parts = explode('-', $last_barang->kode_barang);
        $next_number = intval($parts[0]) + 1;

        return "{$next_number}-{$kode_tipe}";
    }

    public function getHargaSetelahPotonganAttribute()
    {
        $potongan = $this->tipe->potongan_harga ?? 0;
        $harga_setelah = $this->harga_normal - $potongan;

        return $harga_setelah < 0 ? 0 : $harga_setelah;
    }

    public function getHargaPelangganAttribute()
    {
        // Menaikkan harga normal sebesar 10% untuk pelanggan biasa
        return $this->harga_setelah_potongan * 1.1;
    }

    // Accessor untuk format harga
    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_normal, 0, ',', '.');
    }

    

    public function getHargaPotonganAttribute()
    {
        $potongan = $this->tipe->potongan_harga ?? 0;
        return 'Rp ' . number_format($potongan, 0, ',', '.');
    }

    public function getHargaDiskonAttribute()
    {
        $harga = $this->harga_setelah_potongan;
        return 'Rp ' . number_format($harga, 0, ',', '.');
    }
    public function ulasan()
    {
        return $this->hasMany(Ulasan::class, 'kode_barang', 'kode_barang');
    }

    // Method untuk mendapatkan rata-rata rating
    public function averageRating()
    {
        return $this->ulasan()->avg('rating') ?: 0;
    }

    // Method untuk mendapatkan total ulasan
    public function totalUlasan()
    {
        return $this->ulasan()->count();
    }

    // Method untuk mendapatkan distribusi rating
    public function ratingDistribution()
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->ulasan()->where('rating', $i)->count();
        }
        return $distribution;
    }

    // Scope untuk barang dengan rating tinggi
    public function scopeHighRated($query, $minRating = 4)
    {
        return $query->whereHas('ulasan', function($q) use ($minRating) {
            $q->selectRaw('AVG(rating) as avg_rating')
              ->havingRaw('avg_rating >= ?', [$minRating]);
        });
    }

    // Method untuk mendapatkan ulasan terbaru
    public function latestUlasan($limit = 5)
    {
        return $this->ulasan()
                    ->with('user')
                    ->orderBy('tanggal_review', 'desc')
                    ->limit($limit)
                    ->get();
    }
}
