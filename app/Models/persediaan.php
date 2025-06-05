<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'kode_barang';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_barang',
        'nama_barang', 
        'berat',
        'harga_beli',
        'harga_normal',
        'deskripsi',
        'gambar',
        'kode_tipe',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'harga_beli' => 'integer',
        'harga_normal' => 'integer',
        'berat' => 'integer'
    ];

    // Relasi ke tipe
    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'kode_tipe', 'kode_tipe');
    }

    // Relasi ke detail barang
    public function detailBarang()
    {
        return $this->hasMany(DetailBarang::class, 'kode_barang', 'kode_barang');
    }

    // Accessor untuk format harga
    public function getFormattedHargaNormalAttribute()
    {
        return 'Rp ' . number_format($this->harga_normal, 0, ',', '.');
    }

    // Accessor untuk total stok
    public function getTotalStokAttribute()
    {
        return $this->detailBarang->sum('stok');
    }

    // Accessor untuk status stok
    public function getStatusStokAttribute()
    {
        $totalStok = $this->total_stok;
        
        if ($totalStok == 0) {
            return 'Habis';
        } elseif ($totalStok <= 10) {
            return 'Hampir Habis';
        } else {
            return 'Aktif';
        }
    }

    // Accessor untuk CSS class status
    public function getStatusClassAttribute()
    {
        $status = $this->status_stok;
        
        switch ($status) {
            case 'Habis':
                return 'status-tidak-aktif';
            case 'Hampir Habis':
                return 'status-hampir-habis';
            default:
                return 'status-aktif';
        }
    }

    // Scope untuk filter brand
    public function scopeFilterByBrand($query, $brand)
    {
        if ($brand) {
            return $query->whereHas('tipe.brand', function($q) use ($brand) {
                $q->where('nama_brand', $brand);
            });
        }
        return $query;
    }

    // Scope untuk barang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}

// Model DetailBarang
class DetailBarang extends Model
{
    protected $table = 'detail_barang';
    protected $primaryKey = 'kode_detail';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_detail',
        'kode_barang',
        'stok',
        'ukuran',
        'kode_warna'
    ];

    protected $casts = [
        'stok' => 'integer',
        'ukuran' => 'decimal:1'
    ];

    // Relasi ke barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    // Relasi ke warna
    public function warna()
    {
        return $this->belongsTo(Warna::class, 'kode_warna', 'kode_warna');
    }
}

// Model Tipe (jika belum ada)
class Tipe extends Model
{
    protected $table = 'tipe';
    protected $primaryKey = 'kode_tipe';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_tipe',
        'nama_tipe',
        'kode_brand'
    ];

    // Relasi ke brand
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'kode_brand', 'kode_brand');
    }

    // Relasi ke barang
    public function barang()
    {
        return $this->hasMany(Barang::class, 'kode_tipe', 'kode_tipe');
    }
}

// Model Warna (jika belum ada)
class Warna extends Model
{
    protected $table = 'warna';
    protected $primaryKey = 'kode_warna';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_warna',
        'warna',
        'kode_hex'
    ];

    // Relasi ke detail barang
    public function detailBarang()
    {
        return $this->hasMany(DetailBarang::class, 'kode_warna', 'kode_warna');
    }
}

// Model Brand (jika belum ada)
class Brand extends Model
{
    protected $table = 'brand';
    protected $primaryKey = 'kode_brand';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_brand',
        'nama_brand'
    ];

    // Relasi ke tipe
    public function tipe()
    {
        return $this->hasMany(Tipe::class, 'kode_brand', 'kode_brand');
    }
}