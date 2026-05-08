<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BatchBarang;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
class PekerjaanController extends Controller
{
    public function index(Request $request)
    {
        $query = Pekerjaan::withCount(['transaksi', 'toolsDipinjam']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pekerjaan', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_pekerjaan', 'like', '%' . $request->search . '%')
                    ->orWhere('nama_peminjam', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pekerjaan = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('pekerjaan.index', compact('pekerjaan'));
    }

    public function create()
    {
        Gate::authorize('admin-only');
        return view('pekerjaan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_pekerjaan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'nama_peminjam' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $validated['kode_pekerjaan'] = Pekerjaan::generateKode();
        $pekerjaan = Pekerjaan::create($validated);

        return redirect()->route('pekerjaan.show', $pekerjaan)
            ->with('success', 'Pekerjaan berhasil dibuat! Silakan tambahkan barang yang dibutuhkan.');
    }

    public function show(Pekerjaan $pekerjaan)
    {
        $pekerjaan->load(['transaksi.barang']);
        $pekerjaan->total_hpp = $pekerjaan->transaksi->sum('total_hpp');

        $barang = Barang::where('is_active', true)
            ->orderBy('kategori')
            ->orderBy('nama_barang')
            ->get();

        return view('pekerjaan.show', compact('pekerjaan', 'barang'));
    }

    public function edit(Pekerjaan $pekerjaan)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }
        return view('pekerjaan.edit', compact('pekerjaan'));
    }

    public function update(Request $request, Pekerjaan $pekerjaan)
    {
        $validated = $request->validate([
            'nama_pekerjaan' => 'required|string|max:255',
            'lokasi' => 'nullable|string|max:255',
            'nama_peminjam' => 'required|string|max:255',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,selesai',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->status === 'selesai' && $pekerjaan->hasToolsBelumKembali()) {
            return back()->withErrors(['status' => 'Masih ada tools yang belum dikembalikan!']);
        }

        $pekerjaan->update($validated);

        return redirect()->route('pekerjaan.show', $pekerjaan)
            ->with('success', 'Pekerjaan berhasil diupdate!');
    }

    // ════════════════════════════════════════════════════════════
    //  TAMBAH ITEM — FIFO per-batch: 1 harga = 1 baris transaksi
    // ════════════════════════════════════════════════════════════

    public function addItem(Request $request, Pekerjaan $pekerjaan)
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

                // ── 1. Lock barang ─────────────────────────────────────────────
                $barang = Barang::lockForUpdate()->find($item['barang_id']);

                // ── 2. Validasi tools wajib punya tgl_kembali_rencana ──────────
                if ($barang->isTools() && empty($item['tgl_kembali_rencana'])) {
                    $errors[] = "Barang [{$barang->nama_barang}] (tools) wajib memiliki tanggal rencana kembali.";
                    continue;
                }

                // ── 3. Cek kecukupan stok dari batch ──────────────────────────
                $totalBatchSisa = BatchBarang::where('barang_id', $barang->id)
                    ->where('qty_sisa', '>', 0)
                    ->lockForUpdate()
                    ->sum('qty_sisa');

                if ($totalBatchSisa < $item['jumlah']) {
                    if ($barang->stok < $item['jumlah']) {
                        $errors[] = "Stok [{$barang->nama_barang}] tidak cukup! "
                            . "(Batch tersedia: {$totalBatchSisa} {$barang->satuan}, "
                            . "Stok tercatat: {$barang->stok} {$barang->satuan})";
                        continue;
                    }
                    Log::warning("FIFO inkonsistensi [{$barang->nama_barang}]: stok={$barang->stok}, batch={$totalBatchSisa}");
                }

                // ── 4. Pecah kebutuhan ke per-batch FIFO ──────────────────────
                //    ATURAN: 1 harga = 1 baris, tidak boleh dicampur
                $detailFifo = $this->hitungFifoPerBatch($barang, $item['jumlah']);

                // Log fallback jika ada
                foreach ($detailFifo as $d) {
                    if ($d['is_fallback']) {
                        Log::warning("FIFO Fallback [{$barang->nama_barang}]: {$d['qty']} unit @ harga={$d['harga_satuan']}");
                    }
                }

                // ── 5. Kurangi qty_sisa batch ──────────────────────────────────
                $this->kurangiBatch($detailFifo);

                // ── 6. Buat 1 TransaksiPekerjaan PER BATCH ────────────────────
                //    Batch dengan harga beda → baris beda, TIDAK digabung
                foreach ($detailFifo as $batchRow) {
                    $jumlahRow = $batchRow['qty'];
                    $hargaRow = $batchRow['harga_satuan'];
                    $totalHppRow = $batchRow['subtotal'];

                    $keteranganBatch = $this->generateKeteranganBatch(
                        $batchRow,
                        $item['keterangan'] ?? null
                    );

                    if (!$barang->isTools()) {
                        // Cek existing: tanggal sama + barang sama + HARGA SAMA
                        // Harga beda walaupun hari sama → tetap baris baru
                        $existing = TransaksiPekerjaan::where('pekerjaan_id', $pekerjaan->id)
                            ->where('barang_id', $item['barang_id'])
                            ->whereDate('tanggal_keluar', $item['tgl_keluar'])
                            ->where('hpp_satuan', $hargaRow)   // ← kunci: harga harus identik
                            ->whereNull('status_pinjam')
                            ->first();

                        if ($existing) {
                            // ── MERGE: aman karena harga sudah pasti sama ──────
                            $jumlahBaru = $existing->jumlah + $jumlahRow;
                            $totalHppBaru = $existing->total_hpp + $totalHppRow;

                            $existing->update([
                                'jumlah' => $jumlahBaru,
                                'stok_sesudah' => $existing->stok_sesudah - $jumlahRow,
                                'hpp_satuan' => $hargaRow,      // tidak berubah
                                'total_hpp' => $totalHppBaru,
                                'keterangan' => $keteranganBatch ?? $existing->keterangan,
                            ]);

                            $barang->update(['stok' => $barang->stok - $jumlahRow]);

                            continue; // lanjut ke batch row berikutnya
                        }
                    }

                    // ── BUAT baris transaksi baru ──────────────────────────────
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
                        // fifo_detail tetap disimpan untuk keperluan audit
                        'fifo_detail' => [$batchRow],
                    ]);

                    // Refresh stok agar stok_sebelum batch berikutnya akurat
                    $barang->refresh();
                    $barang->update(['stok' => $stokSesudah]);
                }
            }
        });

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        return redirect()->route('pekerjaan.show', $pekerjaan)
            ->with('success', 'Barang berhasil dicatat keluar!');
    }

    // ════════════════════════════════════════════════════════════
    //  FIFO HELPERS
    // ════════════════════════════════════════════════════════════

    /**
     * Pisahkan kebutuhan keluar ke dalam entri per-batch FIFO.
     *
     * ATURAN UTAMA: 1 harga = 1 entri. Tidak ada rata-rata tertimbang.
     *
     * Contoh (10 unit batu gerinda):
     *   Batch A: sisa 5 unit @10.000 → ambil 5 = 50.000  (stok lama)
     *   Batch B: sisa 5 unit @15.000 → ambil 5 = 75.000  (stok baru)
     *   ──────────────────────────────────────────────────────────────
     *   Hasil: 2 entri → 2 baris transaksi terpisah di halaman pekerjaan.
     *
     * @return array<int, array{
     *   batch_id: int|null,
     *   no_masuk: string|null,
     *   tanggal_masuk: string|null,
     *   qty_sisa_sebelum: int|null,
     *   qty: int,
     *   harga_satuan: float,
     *   subtotal: float,
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
                'no_masuk' => $batch->no_transaksi_masuk ?? null,
                'tanggal_masuk' => $batch->tanggal_masuk,
                'qty_sisa_sebelum' => $batch->qty_sisa,
                'qty' => $diambil,
                'harga_satuan' => $harga,
                'subtotal' => $subtotal,
                'is_fallback' => false,
            ];
        }

        // Fallback jika batch tidak mencukupi (inkonsistensi data)
        if ($sisaKeluar > 0) {
            $hargaFallback = (float) ($barang->prices ?? 0);

            $detailFifo[] = [
                'batch_id' => null,
                'no_masuk' => null,
                'tanggal_masuk' => null,
                'qty_sisa_sebelum' => null,
                'qty' => $sisaKeluar,
                'harga_satuan' => $hargaFallback,
                'subtotal' => $sisaKeluar * $hargaFallback,
                'is_fallback' => true,
            ];
        }

        return $detailFifo;
    }

    /**
     * Kurangi qty_sisa batch sesuai hasil kalkulasi FIFO.
     * Entry fallback (batch_id = null) dilewati.
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
     *   "[Stok batch • masuk 08/05/2026 • @Rp10.000] catatan user"
     *   "[Stok batch • masuk 10/05/2026 • @Rp15.000] catatan user"
     *   "[Fallback • tanpa batch • @Rp15.000] catatan user"
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
            $prefix = "[Stok batch • masuk {$tgl} • @Rp{$harga}]";
        }

        return $keteranganUser
            ? "{$prefix} {$keteranganUser}"
            : $prefix;
    }

    // ════════════════════════════════════════════════════════════
    //  ITEM ACTIONS
    // ════════════════════════════════════════════════════════════

    public function editItem(TransaksiPekerjaan $transaksi)
    {
        return view('transaksi.edit', compact('transaksi'));
    }

    /**
     * Update jumlah item.
     * Karena setiap baris sudah terikat 1 batch & 1 harga,
     * hpp_satuan TIDAK berubah saat edit jumlah — hanya total_hpp yang disesuaikan.
     */
    public function updateItem(Request $request, TransaksiPekerjaan $transaksi)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $transaksi) {
                $barang = Barang::lockForUpdate()->find($transaksi->barang_id);
                $selisih = $request->jumlah - $transaksi->jumlah;

                if ($selisih > $barang->stok) {
                    throw new \Exception(
                        "Stok [{$barang->nama_barang}] tidak cukup! (Tersedia: {$barang->stok} {$barang->satuan})"
                    );
                }

                if ($selisih > 0) {
                    // Tambah jumlah: kurangi batch FIFO untuk selisihnya
                    $detailTambahan = $this->hitungFifoPerBatch($barang, $selisih);

                    // Cek apakah semua tambahan dari batch yang sama & harga sama
                    // Jika tidak, tolak edit dan minta user pakai tambah item baru
                    foreach ($detailTambahan as $d) {
                        if ($d['harga_satuan'] != $transaksi->hpp_satuan) {
                            throw new \Exception(
                                "Tidak bisa menambah jumlah karena stok tersisa berasal dari batch dengan harga berbeda "
                                . "(Rp" . number_format($d['harga_satuan'], 0, ',', '.') . " vs "
                                . "Rp" . number_format($transaksi->hpp_satuan, 0, ',', '.') . "). "
                                . "Silakan gunakan tombol Tambah Barang untuk mencatat pengeluaran tambahan."
                            );
                        }
                    }

                    $this->kurangiBatch($detailTambahan);
                }

                // hpp_satuan tetap — hanya total_hpp yang dihitung ulang dari harga yang sudah ada
                $totalHppBaru = round($transaksi->hpp_satuan * $request->jumlah, 2);

                $barang->update(['stok' => $barang->stok - $selisih]);

                $transaksi->update([
                    'jumlah' => $request->jumlah,
                    'keterangan' => $request->keterangan,
                    'stok_sesudah' => $transaksi->stok_sebelum - $request->jumlah,
                    'hpp_satuan' => $transaksi->hpp_satuan, // tidak berubah
                    'total_hpp' => $totalHppBaru,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Item berhasil diupdate!');
    }

    public function returnItem(Request $request, TransaksiPekerjaan $transaksi)
    {
        if ($transaksi->status_pinjam !== 'dipinjam') {
            return back()->withErrors(['error' => 'Item ini sudah dikembalikan!']);
        }

        $request->validate([
            'tgl_kembali_aktual' => 'required|date',
            'keterangan_kembali' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $transaksi) {
            $barang = Barang::lockForUpdate()->find($transaksi->barang_id);

            $transaksi->update([
                'tgl_kembali_aktual' => $request->tgl_kembali_aktual,
                'stok_sebelum_kembali' => $barang->stok,
                'status_pinjam' => 'dikembalikan',
                'keterangan' => $transaksi->keterangan
                    . ($request->keterangan_kembali ? ' | Kembali: ' . $request->keterangan_kembali : ''),
            ]);

            $barang->update(['stok' => $barang->stok + $transaksi->jumlah]);
        });

        return back()->with('success', 'Tools berhasil dikembalikan! Stok sudah ditambahkan kembali.');
    }

    public function destroyItem(TransaksiPekerjaan $transaksi)
    {
        if ($transaksi->status_pinjam === 'dipinjam') {
            return back()->withErrors(['error' => 'Tidak bisa hapus tools yang masih dipinjam!']);
        }

        DB::transaction(function () use ($transaksi) {
            if ($transaksi->status_pinjam === null) {
                $barang = Barang::lockForUpdate()->find($transaksi->barang_id);

                // Rollback batch: kembalikan qty LIFO (batch terbaru dulu)
                $batches = BatchBarang::where('barang_id', $barang->id)
                    ->orderBy('tanggal_masuk', 'desc')
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->get();

                $sisaRollback = $transaksi->jumlah;
                foreach ($batches as $batch) {
                    if ($sisaRollback <= 0)
                        break;
                    $bisaDikembalikan = $batch->qty_awal - $batch->qty_sisa;
                    $dikembalikan = min($bisaDikembalikan, $sisaRollback);
                    if ($dikembalikan > 0) {
                        BatchBarang::where('id', $batch->id)->increment('qty_sisa', $dikembalikan);
                        $sisaRollback -= $dikembalikan;
                    }
                }

                $barang->update(['stok' => $barang->stok + $transaksi->jumlah]);
            }
            $transaksi->delete();
        });

        return back()->with('success', 'Item berhasil dihapus dan stok dikembalikan!');
    }

    public function destroy(Pekerjaan $pekerjaan)
    {
        if ($pekerjaan->hasToolsBelumKembali()) {
            return back()->withErrors(['error' => 'Tidak bisa hapus! Masih ada tools yang dipinjam.']);
        }

        DB::transaction(function () use ($pekerjaan) {
            foreach ($pekerjaan->transaksi as $trx) {
                if ($trx->status_pinjam === null) {
                    $barang = $trx->barang;
                    $batches = BatchBarang::where('barang_id', $barang->id)
                        ->orderBy('tanggal_masuk', 'desc')
                        ->orderBy('id', 'desc')
                        ->get();

                    $sisaRollback = $trx->jumlah;
                    foreach ($batches as $batch) {
                        if ($sisaRollback <= 0)
                            break;
                        $bisaDikembalikan = $batch->qty_awal - $batch->qty_sisa;
                        $dikembalikan = min($bisaDikembalikan, $sisaRollback);
                        if ($dikembalikan > 0) {
                            BatchBarang::where('id', $batch->id)->increment('qty_sisa', $dikembalikan);
                            $sisaRollback -= $dikembalikan;
                        }
                    }
                    $barang->update(['stok' => $barang->stok + $trx->jumlah]);
                }
            }
            $pekerjaan->delete();
        });

        return redirect()->route('pekerjaan.index')
            ->with('success', 'Pekerjaan berhasil dihapus!');
    }
}