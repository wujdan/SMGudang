<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchBarang extends Model
{
    protected $table = 'batch_barang';

    protected $fillable = [
        'barang_id',
        'no_transaksi_masuk',
        'tanggal_masuk',
        'qty_awal',
        'qty_sisa',
        'harga_satuan',
        'created_by_name',
    ];

    protected $casts = [
        'tanggal_masuk' => 'date',
        'harga_satuan' => 'float',
        'qty_awal' => 'integer',
        'qty_sisa' => 'integer',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(Barang::class);
    }

    /**
     * Nilai total batch (qty_awal × harga_satuan).
     */
    public function getTotalNilaiAttribute(): float
    {
        return $this->qty_awal * $this->harga_satuan;
    }

    /**
     * Nilai sisa batch (qty_sisa × harga_satuan).
     */
    public function getNilaiSisaAttribute(): float
    {
        return $this->qty_sisa * $this->harga_satuan;
    }

    /**
     * Apakah batch ini sudah habis?
     */
    public function isHabis(): bool
    {
        return $this->qty_sisa <= 0;
    }
}