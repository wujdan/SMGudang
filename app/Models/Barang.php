<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barang extends Model
{
    protected $table = 'barangs';

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'satuan',
        'stok',
        'stok_minimum',
        'foto',
        'keterangan',
        'is_active',
        'prices',
        'created_by_name', 
];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── RELASI ──────────────────────────────────────────────

    public function barangMasuk(): HasMany
    {
        return $this->hasMany(BarangMasuk::class);
    }

    public function transaksiPekerjaan(): HasMany
    {
        return $this->hasMany(TransaksiPekerjaan::class);
    }

    /**
     * Relasi ke batch FIFO.
     * Diurutkan dari yang terlama (terdepan antrian FIFO).
     */
    public function batchBarang(): HasMany
    {
        return $this->hasMany(BatchBarang::class)
            ->orderBy('tanggal_masuk')
            ->orderBy('id');
    }

    /**
     * Hanya batch yang masih ada sisanya (qty_sisa > 0).
     */
    public function batchAktif(): HasMany
    {
        return $this->hasMany(BatchBarang::class)
            ->where('qty_sisa', '>', 0)
            ->orderBy('tanggal_masuk')
            ->orderBy('id');
    }

    // ── KATEGORI HELPERS ─────────────────────────────────────

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

    // ── STOK HELPERS ─────────────────────────────────────────

    public function isStokMenipis(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    public function getStokDipinjamAttribute(): int
    {
        if (!$this->isTools())
            return 0;
        return $this->transaksiPekerjaan()
            ->where('status_pinjam', 'dipinjam')
            ->sum('jumlah');
    }

    public function getStokTersediaAttribute(): int
    {
        return $this->stok;
    }

    // ── HARGA / FIFO HELPERS ──────────────────────────────────

    /**
     * Harga batch tertua yang masih ada stoknya.
     * Inilah harga yang akan dipakai saat barang keluar (FIFO).
     */
    public function getHargaFifoAttribute(): float
    {
        $batch = $this->batchAktif()->first();
        return $batch ? (float) $batch->harga_satuan : (float) $this->prices;
    }

    /**
     * Total nilai stok berdasarkan semua batch aktif (FIFO valuation).
     * Contoh: 10 unit @500k + 8 unit @600k = 9.800.000
     */
    public function getNilaiStokAttribute(): float
    {
        return (float) $this->batchAktif()
            ->get()
            ->sum(fn($b) => $b->qty_sisa * $b->harga_satuan);
    }

    // ── LABEL / BADGE ─────────────────────────────────────────

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'cons' => 'Consumable',
            'material' => 'Material',
            'tools' => 'Tools',
            default => ucfirst($this->kategori),
        };
    }

    public function getKategoriBadgeAttribute(): string
    {
        return match ($this->kategori) {
            'cons' => 'badge-warning',
            'material' => 'badge-info',
            'tools' => 'badge-success',
            default => 'badge-secondary',
        };
    }

    // ── GENERATE KODE ─────────────────────────────────────────

    public static function generateKode(string $kategori): string
    {
        $prefix = match ($kategori) {
            'cons' => 'CNS',
            'material' => 'MTR',
            'tools' => 'TLS',
            default => 'BRG',
        };
        $last = self::where('kategori', $kategori)->count() + 1;
        return $prefix . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}