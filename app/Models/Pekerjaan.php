<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pekerjaan extends Model
{
    protected $fillable = [
        'kode_pekerjaan',
        'nama_pekerjaan',
        'lokasi',
        'nama_peminjam',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'keterangan',
        'created_by_name',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiPekerjaan::class);
    }

    public function toolsDipinjam(): HasMany
    {
        return $this->hasMany(TransaksiPekerjaan::class)
            ->whereHas('barang', fn($q) => $q->where('kategori', 'tools'))
            ->where('status_pinjam', 'dipinjam');
    }

    public function hasToolsBelumKembali(): bool
    {
        return $this->toolsDipinjam()->exists();
    }

    /**
     * Generate kode pekerjaan unik: PKJ-YYYYMM-XXX
     * Mencari nomor urut terakhir di bulan berjalan, bukan dari count().
     */
    public static function generateKode(): string
    {
        $prefix = 'PKJ-' . date('Ym') . '-';

        $last = self::where('kode_pekerjaan', 'like', $prefix . '%')
            ->orderBy('kode_pekerjaan', 'desc')
            ->lockForUpdate()
            ->first();

        if ($last) {
            $lastNumber = (int) substr($last->kode_pekerjaan, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function getTotalHppAttribute()
    {
        return $this->transaksi()->sum('total_hpp');
    }
}