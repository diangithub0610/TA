<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';
    protected $primaryKey = 'kode_pembelian';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['kode_pembelian', 'id_admin', 'tanggal_masuk', 'bukti_pembelian'];

    /**
     * Relationship with detail_barang_masuk
     */
    public function detailBarangMasuk()
    {
        return $this->hasMany(DetailBarangMasuk::class, 'kode_pembelian', 'kode_pembelian');
    }

    /**
     * Alias for detail relationship
     */
    public function detail()
    {
        return $this->detailBarangMasuk();
    }

    /**
     * Relationship with admin (Pengguna)
     */
    public function admin()
    {
        return $this->belongsTo(Pengguna::class, 'id_admin', 'id_admin');
    }
}
