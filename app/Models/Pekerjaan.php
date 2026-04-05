<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pekerjaan extends Model
{
    protected $fillable = [
        'kode_pekerjaan', 'nama_pekerjaan', 'lokasi',
        'nama_peminjam', 'tanggal_mulai', 'tanggal_selesai',
        'status', 'keterangan'
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

    public static function generateKode(): string
    {
        $year = date('Y');
        $month = date('m');
        $count = self::whereYear('created_at', $year)->whereMonth('created_at', $month)->count() + 1;
        return 'PKJ-' . $year . $month . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
