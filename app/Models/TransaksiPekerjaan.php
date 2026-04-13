<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiPekerjaan extends Model
{
    protected $table = 'transaksi_pekerjaans';

    protected $fillable = [
        'no_transaksi', 'pekerjaan_id', 'barang_id',
        'jumlah', 'stok_sebelum', 'stok_sesudah',
        'tanggal_keluar', 'tgl_kembali_rencana', 'tgl_kembali_aktual',
        'stok_sebelum_kembali', 'status_pinjam', 'keterangan'
    ];

    protected $casts = [
        'tanggal_keluar' => 'date',
        'tgl_kembali_rencana' => 'date',
        'tgl_kembali_aktual' => 'date',
    ];

    public function pekerjaan(): BelongsTo
    {
        return $this->belongsTo(Pekerjaan::class);
    }

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public function isTerlambat(): bool
    {
        if ($this->status_pinjam !== 'dipinjam') return false;
        if (!$this->tgl_kembali_rencana) return false;
        return now()->gt($this->tgl_kembali_rencana);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status_pinjam === null) return 'Keluar Permanen';
        return match($this->status_pinjam) {
            'dipinjam' => $this->isTerlambat() ? 'Terlambat' : 'Dipinjam',
            'dikembalikan' => 'Dikembalikan',
            default => '-',
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->status_pinjam === null) return 'badge-secondary';
        return match($this->status_pinjam) {
            'dipinjam' => $this->isTerlambat() ? 'badge-danger' : 'badge-warning',
            'dikembalikan' => 'badge-success',
            default => 'badge-secondary',
        };
    }

   public static function generateNoTransaksi(int $pekerjaanId): string
{
    $date = date('Ymd');
    $prefix = 'TRX-' . $date . '-P' . str_pad($pekerjaanId, 3, '0', STR_PAD_LEFT) . '-';

    $last = self::where('pekerjaan_id', $pekerjaanId)
        ->where('no_transaksi', 'like', $prefix . '%')
        ->max('no_transaksi');

    $next = $last ? (intval(substr($last, strrpos($last, '-') + 1)) + 1) : 1;

    return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
}
}
