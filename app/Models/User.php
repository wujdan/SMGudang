<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',        // ← tambahkan agar bisa diisi saat create/user
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',   // ← casting ke boolean
    ];

    // ─── Role Checkers ────────────────────────────
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    // ─── Label Role ──────────────────────────────
    public function getRoleLabelAttribute()
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin Gudang',
            'user' => 'Pengguna',
            default => 'User',
        };
    }

    // ─── Status Aktif ────────────────────────────
    public function isActive()
    {
        return $this->is_active;
    }
}