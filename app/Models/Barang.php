<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'kode_barang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'berat',
        'deskripsi',
        'gambar',
        'kode_tipe',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'kode_tipe', 'kode_tipe');
    }

    public function detailBarang()
    {
        return $this->hasMany(DetailBarang::class, 'kode_barang', 'kode_barang');
    }
    public function detailBarangs()
    {
        return $this->hasMany(DetailBarang::class, 'kode_barang', 'kode_barang');
    }

    public function galeriGambar()
    {
        return $this->hasMany(Gambar::class, 'kode_barang', 'kode_barang');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Accessors
    public function getGambarUrlAttribute()
    {
        return $this->gambar ? asset('storage/barang/' . $this->gambar) : null;
    }

    public function getTotalStokAttribute()
    {
        return $this->detailBarang->sum('stok');
    }

    public function getJumlahVarianAttribute()
    {
        return $this->detailBarang->count();
    }

        public static function generateKodeBarang($kode_tipe)
    {
        // Cari nomor terakhir untuk tipe inif
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
}