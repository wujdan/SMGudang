<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BatchBarang;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BarangKeluarController extends Controller
{

    // ── Helper: upload 1 file, simpan sebagai WebP ─────────────────
    private function uploadAsWebp(\Illuminate\Http\UploadedFile $file): string
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($file->getRealPath())
            ->scaleDown(1200)
            ->toWebp(80);

        $filename = 'transaksi-pekerjaan/' . uniqid() . '_' . time() . '.webp';
        Storage::disk('public')->put($filename, (string) $image);

        return $filename;
    }

    // ════════════════════════════════════════════════════════════════

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

        // ── Validasi ───────────────────────────────────────────────
        // heic/heif = format default kamera iPhone
        // max 10 MB karena foto iPhone bisa 4–8 MB
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.tgl_keluar' => 'required|date',
            'items.*.tgl_kembali_rencana' => 'nullable|date|after_or_equal:today',
            'items.*.keterangan' => 'nullable|string',
            'items.*.foto' => 'required|file|mimes:jpeg,jpg,png,webp|max:10240',
        ]);
        
        $fotoPaths = [];

        foreach ($request->file('items', []) as $index => $itemFiles) {
            if (!empty($itemFiles['foto']) && $itemFiles['foto']->isValid()) {
                // Cek apakah benar-benar file gambar dari konten, bukan hanya ekstensi
                $mime = $itemFiles['foto']->getMimeType();
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'])) {
                    return back()->withErrors([
                        'error' => 'File foto item ke-' . ($index + 1) . ' bukan format gambar yang valid (terdeteksi: ' . $mime . ').',
                    ]);
                }
                try {
                    $fotoPaths[$index] = $this->uploadAsWebp($itemFiles['foto']);
                } catch (\Throwable $e) {
                    Log::error("Gagal upload foto item[$index]: " . $e->getMessage());
                    return back()->withErrors([
                        'error' => 'Gagal memproses foto item ke-' . ($index + 1) . ': ' . $e->getMessage(),
                    ]);
                }
            }
        }
        // ── DB Transaction ─────────────────────────────────────────
        $errors = [];
        try {
            DB::transaction(function () use ($request, $pekerjaan, $fotoPaths, &$errors) {

                foreach ($request->input('items', []) as $index => $item) {

                    // 1. Lock barang
                    $barang = Barang::lockForUpdate()->find($item['barang_id']);

                    // 2. Tools wajib ada tgl_kembali_rencana
                    if ($barang->isTools() && empty($item['tgl_kembali_rencana'])) {
                        $errors[] = "Barang [{$barang->nama_barang}] (tools) wajib memiliki tanggal rencana kembali.";
                        continue;
                    }

                    // 3. Cek stok batch
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

                    // 4. Hitung FIFO per-batch
                    $detailFifo = $this->hitungFifoPerBatch($barang, $item['jumlah']);

                    foreach ($detailFifo as $d) {
                        if ($d['is_fallback']) {
                            Log::warning("FIFO Fallback [{$barang->nama_barang}]: {$d['qty']} unit @ {$d['harga_satuan']}");
                        }
                    }

                    // 5. Kurangi batch
                    $this->kurangiBatch($detailFifo);

                    // 6. Simpan transaksi per-batch
                    $fotoPath = $fotoPaths[$index] ?? null;

                    foreach ($detailFifo as $batchIndex => $batchRow) {
                        $jumlahRow = $batchRow['qty'];
                        $hargaRow = $batchRow['harga_satuan'];
                        $totalHppRow = $batchRow['subtotal'];

                        $keteranganBatch = $this->generateKeteranganBatch(
                            $batchRow,
                            $item['keterangan'] ?? null
                        );

                        // if (!$barang->isTools()) {
                        //     $existing = TransaksiPekerjaan::where('pekerjaan_id', $pekerjaan->id)
                        //         ->where('barang_id', $item['barang_id'])
                        //         ->whereDate('tanggal_keluar', $item['tgl_keluar'])
                        //         ->where('hpp_satuan', $hargaRow)
                        //         ->whereNull('status_pinjam')
                        //         ->first();

                        //     if ($existing) {
                        //         $existing->update([
                        //             'jumlah' => $existing->jumlah + $jumlahRow,
                        //             'stok_sesudah' => $existing->stok_sesudah - $jumlahRow,
                        //             'hpp_satuan' => $hargaRow,
                        //             'total_hpp' => $existing->total_hpp + $totalHppRow,
                        //             'keterangan' => $keteranganBatch ?? $existing->keterangan,
                        //             'foto' => $fotoPath ?? $existing->foto,
                        //         ]);
                        //         $barang->update(['stok' => $barang->stok - $jumlahRow]);
                        //         continue;
                        //     }
                        // }

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
                            'foto' => $batchIndex === 0 ? $fotoPath : null,
                            'created_by_name' => auth()->user()->name,
                        ]);

                        $barang->refresh();
                        $barang->update(['stok' => $stokSesudah]);
                    }
                }
            });

        } catch (\Throwable $e) {
            // DB gagal — hapus semua file yang sudah terupload
            foreach ($fotoPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            Log::error('BarangKeluar store error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()]);
        }

        // Error bisnis (stok kurang, dll) — hapus file yang sudah terupload
        if (!empty($errors)) {
            foreach ($fotoPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            return back()->withErrors($errors);
        }

        return redirect()->route('barang-keluar.index')
            ->with('success', 'Barang berhasil dicatat keluar!');
    }

    // ════════════════════════════════════════════════════════════════
    //  FOTO ACTIONS
    // ════════════════════════════════════════════════════════════════

    public function updateFoto(Request $request, TransaksiPekerjaan $transaksi)
    {
        $request->validate([
            'foto' => 'required|file|mimes:jpeg,jpg,png,webp,heic,heif|max:10240',
        ]);

        if ($transaksi->foto) {
            Storage::disk('public')->delete($transaksi->foto);
        }

        $path = $this->uploadAsWebp($request->file('foto'));
        $transaksi->update(['foto' => $path]);

        return back()->with('success', 'Foto berhasil diupdate!');
    }

    public function deleteFoto(TransaksiPekerjaan $transaksi)
    {
        if ($transaksi->foto) {
            Storage::disk('public')->delete($transaksi->foto);
            $transaksi->update(['foto' => null]);
        }

        return back()->with('success', 'Foto berhasil dihapus!');
    }

    public function destroy(TransaksiPekerjaan $transaksi)
    {
        if ($transaksi->foto) {
            Storage::disk('public')->delete($transaksi->foto);
        }
        $transaksi->delete();

        return back()->with('success', 'Transaksi berhasil dihapus!');
    }

    // ════════════════════════════════════════════════════════════════
    //  FIFO HELPERS
    // ════════════════════════════════════════════════════════════════

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
            $sisaKeluar -= $diambil;

            $detailFifo[] = [
                'batch_id' => $batch->id,
                'qty' => $diambil,
                'harga_satuan' => $harga,
                'subtotal' => $diambil * $harga,
                'tanggal_masuk' => $batch->tanggal_masuk,
                'is_fallback' => false,
            ];
        }

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

    private function kurangiBatch(array $detailFifo): void
    {
        foreach ($detailFifo as $d) {
            if ($d['batch_id'] === null)
                continue;
            BatchBarang::where('id', $d['batch_id'])->decrement('qty_sisa', $d['qty']);
        }
    }

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

        return $keteranganUser ? "{$prefix} {$keteranganUser}" : $prefix;
    }
}