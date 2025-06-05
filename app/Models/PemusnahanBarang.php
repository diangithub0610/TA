<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemusnahanBarang extends Model
{
    use HasFactory;

    protected $table = 'pemusnahan_barang';
    protected $primaryKey = 'kode_pemusnahan';
    public $incrementing = false;
    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode_pemusnahan',
        'kode_detail',
        'id_admin',
        'tanggal_pemusnahan',
        'jumlah_diajukan',
        'jumlah_disetujui',
        'alasan',
        'bukti_gambar',
        'status'
    ];

    public function detailBarang()
    {
        return $this->belongsTo(DetailBarang::class, 'kode_detail', 'kode_detail');
    }

    public function admin()
    {
        return $this->belongsTo(Pengguna::class, 'id_admin', 'id_admin');
    }
}
