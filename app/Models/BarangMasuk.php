<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuks';

    protected $fillable = [
        'no_transaksi',
        'barang_id',
        'jumlah',
        'harga_satuan',      // ← TAMBAHKAN INI
        'stok_sebelum',
        'stok_sesudah',
        'tanggal',
        'sumber',
        'keterangan',
        'created_by_name', 
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_satuan' => 'decimal:2',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Generate nomor transaksi barang masuk
     * Format: BM-YYYYMMDD-XXX (XXX = urutan per hari)
     */
    public static function generateNoTransaksi()
    {
        $today = now()->format('Ymd');

        // Ambil transaksi terakhir hari ini
        $last = self::where('no_transaksi', 'like', 'BM-' . $today . '-%')
            ->orderByDesc('no_transaksi')
            ->lockForUpdate()
            ->first();

        if (!$last) {

            $sequence = 1;

        } else {

            // Ambil angka urutan terakhir
            $lastSequence = (int) substr(
                $last->no_transaksi,
                -3
            );

            $sequence = $lastSequence + 1;
        }

        return 'BM-' .
            $today .
            '-' .
            str_pad(
                $sequence,
                3,
                '0',
                STR_PAD_LEFT
            );
    }
}