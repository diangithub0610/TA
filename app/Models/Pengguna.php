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
    public $timestamps = false;

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
    // public function setKataSandiAttribute($value)
    // {
    //     $this->attributes['kata_sandi'] = bcrypt($value);
    // }

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

    //management user

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    // Accessor untuk foto profil
    public function getFotoProfilUrlAttribute()
    {
        if ($this->foto_profil) {
            return asset('storage/profile_photos/' . $this->foto_profil);
        }
        return asset('assets/img/default-avatar.png');
    }

    // Scope untuk filter berdasarkan role
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk mengecualikan role owner
    public function scopeExcludeOwner($query)
    {
        return $query->where('role', '!=', 'owner');
    }

    // Method untuk generate ID Admin
    public static function generateIdAdmin()
    {
        $lastId = self::where('id_admin', 'like', 'ADM%')
            ->orderBy('id_admin', 'desc')
            ->first();
            
        if ($lastId) {
            $lastNumber = intval(substr($lastId->id_admin, 3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'ADM' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    // Method untuk check apakah user aktif
    public function isActive()
    {
        return $this->status === 'aktif';
    }

    // Method untuk toggle status
    public function toggleStatus()
    {
        $this->status = $this->status === 'aktif' ? 'nonaktif' : 'aktif';
        return $this->save();
    }

    // Method untuk get role display name
    public function getRoleDisplayAttribute()
    {
        $roles = [
            'gudang' => 'Staff Gudang',
            'shopkeeper' => 'Shopkeeper',
            'owner' => 'Owner'
        ];

        return $roles[$this->role] ?? $this->role;
    }

    // Method untuk get status badge class
    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'aktif' ? 'badge-success' : 'badge-danger';
    }
}

