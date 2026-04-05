<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuks';

    protected $fillable = [
        'no_transaksi', 'barang_id', 'jumlah',
        'stok_sebelum', 'stok_sesudah', 'tanggal',
        'sumber', 'keterangan'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    public static function generateNoTransaksi(): string
    {
        $date = date('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'BM-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
