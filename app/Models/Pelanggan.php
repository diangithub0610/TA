<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pelanggan extends Authenticatable
{
    use Notifiable;

    protected $table = 'Pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $incrementing = false;

    protected $fillable = [
        'id_pelanggan',
        'nama_pelanggan',
        'no_hp',
        'email',
        'alamat',
        'username',
        'role',
        'foto_profil',
        'kata_sandi'
    ];

    protected $hidden = [
        'kata_sandi'
    ];

    // Mutator for password hashing
    public function setKataSandiAttribute($value)
    {
        $this->attributes['kata_sandi'] = bcrypt($value);
    }

    // Check user role methods
    public function isPelanggan()
    {
        return $this->role === 'pelanggan';
    }

    public function isReseller()
    {
        return $this->role === 'reseller';
    }

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function alamat()
    {
        return $this->hasMany(Alamat::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function alamatUtama()
    {
        return $this->alamat()->where('is_utama', 1)->first();
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_pelanggan', 'id_pelanggan');
    }

    // Aksesor untuk harga berdasarkan role
    public function getHargaAttribute($harga_normal)
    {
        if ($this->role === 'pelanggan') {
            return $harga_normal * 1.1;
        }
        return $harga_normal;
    }
}
