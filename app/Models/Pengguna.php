<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'Pengguna';
    protected $primaryKey = 'id_admin';
    public $incrementing = false;

    protected $fillable = [
        'id_admin',
        'nama_admin',
        'no_hp',
        'email',
        'username',
        'role',
        'foto_profil',
        'kata_sandi',
        'status'
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
    public function isGudang()
    {
        return $this->role === 'gudang';
    }

    public function isPemesanan()
    {
        return $this->role === 'pemesanan';
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }
}
