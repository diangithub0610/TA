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
    public $timestamps = false;

    protected $fillable = [
        'id_pelanggan', 'nama_pelanggan', 'no_hp', 'email', 'alamat_pengguna', 'username',
        'foto_profil', 'kata_sandi', 'role',
    ];

    protected $hidden = [
        'kata_sandi'
    ];

    // Mutator for password hashing
    // public function setKataSandiAttribute($value)
    // {
    //     $this->attributes['kata_sandi'] = bcrypt($value);
    // }

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
        //return $this->hasMany(Alamat::class);
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

    public function pendaftaran()
    {
        return $this->hasOne(Pendaftaran::class, 'id_pelanggan', 'id_pelanggan');
    }
    // Aksesor untuk harga berdasarkan role
    public function getHargaAttribute()
    {
        // Ambil model barang yang sedang diakses
        $barang = $this;
    
        // Jika user tidak login, tampilkan harga normal
        if (!auth()->check()) {
            return $barang->harga_normal;
        }
    
        $user = auth()->user();
    
        // Jika user adalah pelanggan, tampilkan harga normal
        if ($user->role === 'pelanggan') {
            return $barang->harga_normal;
        }
    
        // Jika user adalah reseller
        if ($user->role === 'reseller') {
            // Cek apakah ada tipe dan potongan harga
            if ($barang->tipe && $barang->tipe->potongan_harga) {
                return $barang->harga_normal - $barang->tipe->potongan_harga;
            }
            
            // Jika reseller tapi tidak ada potongan, tetap harga normal
            return $barang->harga_normal;
        }
    
        // Default fallback untuk role lain
        return $barang->harga_normal;
    }
}
