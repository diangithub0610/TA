<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DetailBarang extends Model
{
    protected $table = 'detail_barang';
    protected $primaryKey = 'kode_detail';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_detail',
        'kode_barang',
        'stok',
        'ukuran',
        'kode_warna',
        'harga_beli',
        'harga_normal',
        'stok_minimum',
        'potongan_harga',
    ];

    // Relasi dengan Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'kode_barang');
    }

    // Relasi dengan Warna
    public function warna()
    {
        return $this->belongsTo(Warna::class, 'kode_warna', 'kode_warna');
    }

    public static function generateKodeDetail($kode_barang, $kode_warna, $nextNumber = 1)
    {
        $prefix = "{$kode_barang}-{$kode_warna}";
        $formattedNumber = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        return "{$prefix}-{$formattedNumber}";
    }

    // Accessor untuk harga berdasarkan role user
    protected function hargaByRole(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Cek apakah user adalah reseller
                if (
                    Auth::guard('pelanggan')->check() &&
                    Auth::guard('pelanggan')->user()->role == 'reseller'
                ) {
                    return $this->harga_normal - $this->potongan_harga;
                }

                // Untuk pelanggan biasa atau tidak login, berikan harga normal
                return $this->harga_normal;
            }
        );
    }

    // Accessor untuk harga terformat berdasarkan role
    protected function formattedHargaByRole(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->harga_by_role, 0, ',', '.')
        );
    }

    // Accessor untuk harga normal terformat
    protected function formattedHargaNormal(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->harga_normal, 0, ',', '.')
        );
    }

    // Accessor untuk harga setelah diskon terformat
    protected function formattedHargaDiskon(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Rp ' . number_format($this->harga_normal - $this->potongan_harga, 0, ',', '.')
        );
    }

    // Method untuk mengecek apakah user adalah reseller
    public function isUserReseller(): bool
    {
        return Auth::guard('pelanggan')->check() &&
            Auth::guard('pelanggan')->user()->role == 'reseller';
    }

    // Method untuk mendapatkan harga berdasarkan role dengan parameter
    public function getHargaByRole($userRole = null): int
    {
        // Jika tidak ada parameter, ambil dari user yang sedang login
        if ($userRole === null) {
            $userRole = $this->getCurrentUserRole();
        }

        if ($userRole === 'reseller') {
            return $this->harga_normal - $this->potongan_harga;
        }

        return $this->harga_normal;
    }

    // Method untuk mendapatkan role user saat ini
    private function getCurrentUserRole(): string
    {
        if (Auth::guard('pelanggan')->check()) {
            return Auth::guard('pelanggan')->user()->role;
        }

        return 'guest';
    }
}
