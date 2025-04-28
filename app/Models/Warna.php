<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warna extends Model
{
    use HasFactory;
    protected $table = 'warna';
    protected $primaryKey = 'kode_warna';
    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode_warna',
        'warna',
        'kode_hex'
    ];

    public static function generateKodeWarna($nama_warna)
    {
        // Pecah nama warna menjadi kata-kata
        $kata = explode(' ', Str::lower($nama_warna));

        // Jika hanya satu kata
        if (count($kata) == 1) {
            // Ambil 2-3 huruf pertama dan konversi ke huruf besar
            return Str::upper(Str::substr($nama_warna, 0, 2));
        }

        // Jika lebih dari satu kata, ambil huruf pertama dari setiap kata
        $kode = '';
        foreach ($kata as $k) {
            $kode .= Str::upper(Str::substr($k, 0, 1));
        }

        return $kode;
    }

    // Accessor untuk menampilkan preview warna
    public function getColorPreviewAttribute()
    {
        return $this->kode_hex ? "#" . $this->kode_hex : null;
    }
}
