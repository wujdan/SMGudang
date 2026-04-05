<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $fillable = [
        'kode_barang', 'nama_barang', 'kategori', 'satuan',
        'stok', 'stok_minimum', 'foto', 'keterangan', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class);
    }

    public function transaksiPekerjaan(): HasMany
    {
        return $this->hasMany(TransaksiPekerjaan::class);
    }

    public function isTools(): bool
    {
        return $this->kategori === 'tools';
    }

    public function isCons(): bool
    {
        return $this->kategori === 'cons';
    }

    public function isMaterial(): bool
    {
        return $this->kategori === 'material';
    }

    public function isStokMenipis(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    public function getKategoriLabelAttribute(): string
    {
        return match($this->kategori) {
            'cons' => 'Consumable',
            'material' => 'Material',
            'tools' => 'Tools',
            default => ucfirst($this->kategori),
        };
    }

    public function getKategoriBadgeAttribute(): string
    {
        return match($this->kategori) {
            'cons' => 'badge-warning',
            'material' => 'badge-info',
            'tools' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    // Hitung tools yang sedang dipinjam
    public function getStokDipinjamAttribute(): int
    {
        if (!$this->isTools()) return 0;
        return $this->transaksiPekerjaan()
            ->where('status_pinjam', 'dipinjam')
            ->sum('jumlah');
    }

    public function getStokTersediaAttribute(): int
    {
        return $this->stok;
    }

    public static function generateKode(string $kategori): string
    {
        $prefix = match($kategori) {
            'cons' => 'CNS',
            'material' => 'MTR',
            'tools' => 'TLS',
            default => 'BRG',
        };
        $last = self::where('kategori', $kategori)->count() + 1;
        return $prefix . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
