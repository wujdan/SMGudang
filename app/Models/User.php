<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    // Helper cek role
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Setelah method isAdmin() dan isUser()
    public function getRoleLabelAttribute()
    {
        return match ($this->role) {
            'admin' => 'Admin Gudang',
            'user' => 'Pengguna Gudang',
            default => 'Tamu',
        };
    }
}
