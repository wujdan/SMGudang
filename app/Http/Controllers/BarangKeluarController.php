<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BatchBarang;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BarangKeluarController extends Controller
{
    public function index()
    {
        $pekerjaan = Pekerjaan::where('status', 'aktif')
            ->with([
                'transaksi' => function ($q) {
                    $q->selectRaw('pekerjaan_id, sum(total_hpp) as total_hpp')
                        ->groupBy('pekerjaan_id');
                }
            ])
            ->withCount(['transaksi', 'toolsDipinjam'])
            ->orderByDesc('created_at')
            ->paginate(15);

        foreach ($pekerjaan as $p) {
            $p->total_hpp = $p->transaksi->sum('total_hpp') ?? 0;
        }

        return view('transaksi.keluar.index', compact('pekerjaan'));
    }

    public function create(Pekerjaan $pekerjaan)
    {
        if ($pekerjaan->status !== 'aktif') {
            return redirect()->route('barang-keluar.index')
                ->withErrors(['error' => 'Pekerjaan sudah selesai!']);
        }

        $barang = Barang::where('is_active', true)
            ->orderBy('kategori')
            ->orderBy('nama_barang')
            ->get();

        return view('transaksi.keluar.create', compact('pekerjaan', 'barang'));
    }

    public function store(Request $request, Pekerjaan $pekerjaan)
    {
        if ($pekerjaan->status !== 'aktif') {
            return back()->withErrors(['error' => 'Pekerjaan sudah selesai!']);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.tgl_keluar' => 'required|date',
            'items.*.tgl_kembali_rencana' => 'nullable|date|after_or_equal:today',
            'items.*.keterangan' => 'nullable|string',
        ]);

        $errors = [];

        DB::transaction(function () use ($validated, $pekerjaan, &$errors) {
            foreach ($validated['items'] as $item) {

                // ── 1. Lock barang untuk cegah race condition ──────────────────
                $barang = Barang::lockForUpdate()->find($item['barang_id']);

                // ── 2. Validasi tools wajib punya tgl_kembali_rencana ──────────
                if ($barang->isTools() && empty($item['tgl_kembali_rencana'])) {
                    $errors[] = "Barang [{$barang->nama_barang}] (tools) wajib memiliki tanggal rencana kembali.";
                    continue;
                }

                // ── 3. Hitung total stok nyata dari batch ──────────────────────
                $totalBatchSisa = BatchBarang::where('barang_id', $barang->id)
                    ->where('qty_sisa', '>', 0)
                    ->lockForUpdate()
                    ->sum('qty_sisa');

                // ── 4. Cek kecukupan stok ──────────────────────────────────────
                if ($totalBatchSisa < $item['jumlah']) {
                    if ($barang->stok < $item['jumlah']) {
                        $errors[] = "Stok [{$barang->nama_barang}] tidak cukup! "
                            . "(Batch tersedia: {$totalBatchSisa} {$barang->satuan}, "
                            . "Stok tercatat: {$barang->stok} {$barang->satuan})";
                        continue;
                    }

                    Log::warning("FIFO: Inkonsistensi stok vs batch untuk barang [{$barang->nama_barang}]. "
                        . "Stok: {$barang->stok}, Total batch: {$totalBatchSisa}, Keluar: {$item['jumlah']}");
                }

                // ── 5. Ambil detail FIFO per-batch (TANPA campur harga) ────────
                $detailFifo = $this->hitungFifoPerBatch($barang, $item['jumlah']);

                // Log jika ada fallback
                foreach ($detailFifo as $d) {
                    if ($d['batch_id'] === null) {
                        Log::warning("FIFO Fallback: [{$barang->nama_barang}] {$d['qty']} unit "
                            . "menggunakan harga fallback={$d['harga_satuan']} karena batch tidak cukup.");
                    }
                }

                // ── 6. Kurangi qty_sisa batch (FIFO) ──────────────────────────
                $this->kurangiBatch($detailFifo);

                // ── 7. Buat 1 TransaksiPekerjaan PER BATCH ────────────────────
                //    Prinsip: harga berbeda = baris berbeda, tidak boleh dicampur.
                foreach ($detailFifo as $batchRow) {
                    $jumlahRow = $batchRow['qty'];
                    $hargaRow = $batchRow['harga_satuan'];
                    $totalHppRow = $batchRow['subtotal'];
                    $batchId = $batchRow['batch_id']; // null = fallback

                    // Keterangan otomatis: tandai mana stok lama vs stok baru
                    $keteranganBatch = $this->generateKeteranganBatch(
                        $batchRow,
                        $item['keterangan'] ?? null
                    );

                    if (!$barang->isTools()) {
                        // ── Cek existing transaksi hari yang sama, barang sama,
                        //    DAN harga satuan sama (kunci utama: harga tidak boleh campur)
                        $existing = TransaksiPekerjaan::where('pekerjaan_id', $pekerjaan->id)
                            ->where('barang_id', $item['barang_id'])
                            ->whereDate('tanggal_keluar', $item['tgl_keluar'])
                            ->where('hpp_satuan', $hargaRow)        // ← kunci: harga harus sama
                            ->whereNull('status_pinjam')
                            ->first();

                        if ($existing) {
                            // ── MERGE: hanya jika harga sama ──────────────────
                            $jumlahBaru = $existing->jumlah + $jumlahRow;
                            $totalHppBaru = $existing->total_hpp + $totalHppRow;

                            $existing->update([
                                'jumlah' => $jumlahBaru,
                                'stok_sesudah' => $existing->stok_sesudah - $jumlahRow,
                                'hpp_satuan' => $hargaRow, // tetap sama, tidak berubah
                                'total_hpp' => $totalHppBaru,
                                'keterangan' => $keteranganBatch ?? $existing->keterangan,
                            ]);

                            $barang->update(['stok' => $barang->stok - $jumlahRow]);

                            continue; // lanjut ke batch berikutnya
                        }
                    }

                    // ── BUAT baris transaksi baru untuk batch ini ──────────────
                    $stokSebelum = $barang->stok;
                    $stokSesudah = $stokSebelum - $jumlahRow;

                    TransaksiPekerjaan::create([
                        'no_transaksi' => TransaksiPekerjaan::generateNoTransaksi($pekerjaan->id),
                        'pekerjaan_id' => $pekerjaan->id,
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $jumlahRow,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'tanggal_keluar' => $item['tgl_keluar'],
                        'tgl_kembali_rencana' => $barang->isTools() ? $item['tgl_kembali_rencana'] : null,
                        'status_pinjam' => $barang->isTools() ? 'dipinjam' : null,
                        'hpp_satuan' => $hargaRow,
                        'total_hpp' => $totalHppRow,
                        'keterangan' => $keteranganBatch,
                    ]);

                    // Refresh stok barang setelah setiap baris agar stok_sebelum
                    // pada baris berikutnya (batch berbeda) selalu akurat
                    $barang->refresh();
                    $barang->update(['stok' => $stokSesudah]);
                }
            }
        });

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        return redirect()->route('barang-keluar.index')
            ->with('success', 'Barang berhasil dicatat keluar!');
    }

    // ════════════════════════════════════════════════════════════
    //  FIFO HELPERS
    // ════════════════════════════════════════════════════════════

    /**
     * Pisahkan kebutuhan keluar ke dalam kelompok per-batch FIFO.
     *
     * ATURAN UTAMA: 1 harga = 1 entri.
     * Tidak ada rata-rata tertimbang — harga tidak boleh dicampur.
     *
     * Contoh (8 unit paku):
     *   Batch A: sisa 5 unit @10.000  → ambil 5  = 50.000  (stok lama)
     *   Batch B: sisa 5 unit @15.000  → ambil 3  = 45.000  (stok baru)
     *   ──────────────────────────────────────────────────────────────
     *   Hasil: 2 entri → 2 baris transaksi terpisah di pekerjaan.
     *
     * @return array<int, array{
     *   batch_id: int|null,
     *   qty: int,
     *   harga_satuan: float,
     *   subtotal: float,
     *   tanggal_masuk: string|null,
     *   is_fallback: bool
     * }>
     */
    private function hitungFifoPerBatch(Barang $barang, int $jumlahKeluar): array
    {
        $batches = BatchBarang::where('barang_id', $barang->id)
            ->where('qty_sisa', '>', 0)
            ->orderBy('tanggal_masuk', 'asc')
            ->orderBy('id', 'asc')
            ->lockForUpdate()
            ->get();

        $sisaKeluar = $jumlahKeluar;
        $detailFifo = [];

        foreach ($batches as $batch) {
            if ($sisaKeluar <= 0)
                break;

            $diambil = min($batch->qty_sisa, $sisaKeluar);
            $harga = (float) $batch->harga_satuan;
            $subtotal = $diambil * $harga;
            $sisaKeluar -= $diambil;

            $detailFifo[] = [
                'batch_id' => $batch->id,
                'qty' => $diambil,
                'harga_satuan' => $harga,
                'subtotal' => $subtotal,
                'tanggal_masuk' => $batch->tanggal_masuk,
                'is_fallback' => false,
            ];
        }

        // ── Fallback jika batch tidak mencukupi (data tidak sinkron) ──────
        if ($sisaKeluar > 0) {
            $hargaFallback = (float) ($barang->prices ?? 0);

            $detailFifo[] = [
                'batch_id' => null,
                'qty' => $sisaKeluar,
                'harga_satuan' => $hargaFallback,
                'subtotal' => $sisaKeluar * $hargaFallback,
                'tanggal_masuk' => null,
                'is_fallback' => true,
            ];
        }

        return $detailFifo;
    }

    /**
     * Kurangi qty_sisa batch sesuai hasil kalkulasi FIFO.
     * Entry dengan batch_id = null (fallback) dilewati.
     */
    private function kurangiBatch(array $detailFifo): void
    {
        foreach ($detailFifo as $d) {
            if ($d['batch_id'] === null)
                continue;

            BatchBarang::where('id', $d['batch_id'])
                ->decrement('qty_sisa', $d['qty']);
        }
    }

    /**
     * Buat keterangan otomatis per-baris berdasarkan info batch.
     *
     * Contoh output:
     *   "[Stok lama • masuk 08/05/2025 • @Rp10.000] Catatan user"
     *   "[Stok baru • masuk 10/05/2025 • @Rp15.000] Catatan user"
     *   "[Fallback • tanpa batch • @Rp15.000] Catatan user"
     */
    private function generateKeteranganBatch(array $batchRow, ?string $keteranganUser): string
    {
        $harga = number_format($batchRow['harga_satuan'], 0, ',', '.');

        if ($batchRow['is_fallback']) {
            $prefix = "[Fallback • tanpa batch • @Rp{$harga}]";
        } else {
            $tgl = $batchRow['tanggal_masuk']
                ? \Carbon\Carbon::parse($batchRow['tanggal_masuk'])->format('d/m/Y')
                : '-';
            $label = 'Stok batch'; // bisa dikembangkan: bandingkan tgl untuk label "lama/baru"
            $prefix = "[{$label} • masuk {$tgl} • @Rp{$harga}]";
        }

        return $keteranganUser
            ? "{$prefix} {$keteranganUser}"
            : $prefix;
    }
}