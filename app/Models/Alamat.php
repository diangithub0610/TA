<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alamat extends Model
{
    use HasFactory;

    protected $table = 'alamat';
    protected $primaryKey = 'id_alamat';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_alamat',
        'id_pelanggan',
        // 'nama_alamat',
        'nama_penerima',
        'no_hp_penerima',
        'alamat_lengkap',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'kode_pos',
        'is_utama',
        'rajaongkir_id',
        'latitude',
        'longitude',
        'catatan',
        'jenis',
        'kecamatan_id',
    ];

    protected $casts = [
        'is_utama' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    public static function boot()
    {
        parent::boot();

        // Generate unique ID before creating
        static::creating(function ($model) {
            if (empty($model->id_alamat)) {
                $model->id_alamat = 'ALM-' . Str::upper(Str::random(6));
            }

            // Ensure only one main address per customer
            if ($model->is_utama) {
                self::where('id_pelanggan', $model->id_pelanggan)
                    ->update(['is_utama' => false]);
            }
        });
    }

    public function getFullAddressAttribute()
    {
        return "{$this->alamat_lengkap}, {$this->kelurahan}, {$this->kecamatan}, {$this->kota}, {$this->provinsi} {$this->kode_pos}";
    }

    public function getNamaAlamatAttribute()
    {
        return "{$this->alamat_lengkap}, {$this->kelurahan}, {$this->kecamatan}, {$this->kota}, {$this->provinsi} {$this->kode_pos}";
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }
}
