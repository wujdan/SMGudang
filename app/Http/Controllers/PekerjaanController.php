<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $barang = Barang::where('is_active', true)->orderBy('kategori')->orderBy('nama_barang')->get();
        return view('pekerjaan.show', compact('pekerjaan', 'barang'));
    }

    public function edit(Pekerjaan $pekerjaan)
    {
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

        // Kalau mau selesaikan, cek dulu tools belum kembali
        if ($request->status === 'selesai' && $pekerjaan->hasToolsBelumKembali()) {
            return back()->withErrors(['status' => 'Masih ada tools yang belum dikembalikan!']);
        }

        $pekerjaan->update($validated);

        return redirect()->route('pekerjaan.show', $pekerjaan)
            ->with('success', 'Pekerjaan berhasil diupdate!');
    }

    // Tambah item ke pekerjaan (cart submit)
    public function addItem(Request $request, Pekerjaan $pekerjaan)
    {
        if ($pekerjaan->status !== 'aktif') {
            return back()->withErrors(['error' => 'Pekerjaan sudah selesai!']);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'items.*.tgl_kembali_rencana' => 'nullable|date|after_or_equal:today',
            'items.*.keterangan' => 'nullable|string',
        ]);

        $errors = [];

        DB::transaction(function () use ($validated, $pekerjaan, &$errors) {
            foreach ($validated['items'] as $item) {
                $barang = Barang::lockForUpdate()->find($item['barang_id']);

                if ($barang->stok < $item['jumlah']) {
                    $errors[] = "Stok {$barang->nama_barang} tidak cukup! (Tersedia: {$barang->stok} {$barang->satuan})";
                    return;
                }

                // Cek apakah sudah ada transaksi barang yang sama di tanggal hari ini
                // Khusus cons & material (status_pinjam === null), tools selalu baris baru
                $existing = null;
                if (!$barang->isTools()) {
                    $existing = TransaksiPekerjaan::where('pekerjaan_id', $pekerjaan->id)
                        ->where('barang_id', $item['barang_id'])
                        ->whereDate('tanggal_keluar', today())
                        ->whereNull('status_pinjam')
                        ->first();
                }

                if ($existing) {
                    // ✅ Sudah ada di tanggal yang sama → tambah jumlah saja
                    $selisih = $item['jumlah'];

                    $existing->update([
                        'jumlah'      => $existing->jumlah + $selisih,
                        'stok_sesudah' => $existing->stok_sebelum - ($existing->jumlah + $selisih),
                        'keterangan'  => $item['keterangan'] ?? $existing->keterangan,
                    ]);

                    $barang->update(['stok' => $barang->stok - $selisih]);
                } else {
                    // 📋 Belum ada → buat baris baru
                    $stokSebelum = $barang->stok;
                    $stokSesudah = $stokSebelum - $item['jumlah'];

                    TransaksiPekerjaan::create([
                        'no_transaksi'        => TransaksiPekerjaan::generateNoTransaksi($pekerjaan->id),
                        'pekerjaan_id'        => $pekerjaan->id,
                        'barang_id'           => $item['barang_id'],
                        'jumlah'              => $item['jumlah'],
                        'stok_sebelum'        => $stokSebelum,
                        'stok_sesudah'        => $stokSesudah,
                        'tanggal_keluar'      => today(),
                        'tgl_kembali_rencana' => $barang->isTools() ? ($item['tgl_kembali_rencana'] ?? null) : null,
                        'status_pinjam'       => $barang->isTools() ? 'dipinjam' : null,
                        'keterangan'          => $item['keterangan'] ?? null,
                    ]);

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

    // Edit item transaksi (ubah jumlah/keterangan)
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

                if ($selisih > 0 && $barang->stok < $selisih) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak cukup! (Tersedia: {$barang->stok} {$barang->satuan})");
                }

                $barang->update(['stok' => $barang->stok - $selisih]);

                $transaksi->update([
                    'jumlah'       => $request->jumlah,
                    'keterangan'   => $request->keterangan,
                    'stok_sesudah' => $transaksi->stok_sebelum - $request->jumlah,
                ]);
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Item berhasil diupdate!');
    }

    public function editItem(TransaksiPekerjaan $transaksi)
    {
        return view('transaksi.edit', compact('transaksi'));
    }

    // Konfirmasi pengembalian tools
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
                'keterangan' => $transaksi->keterangan . ($request->keterangan_kembali ? ' | Kembali: ' . $request->keterangan_kembali : ''),
            ]);

            // Tambah kembali stok
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
        // Hanya rollback stok untuk cons/material (status_pinjam === null)
        // Tools dikembalikan → stok sudah dikembalikan saat returnItem, tidak perlu rollback lagi
        if ($transaksi->status_pinjam === null) {
            $barang = Barang::lockForUpdate()->find($transaksi->barang_id);
            $barang->update(['stok' => $barang->stok + $transaksi->jumlah]);
        }

        $transaksi->delete();
    });

    return back()->with('success', 'Item berhasil dihapus!');
}

    public function destroy(Pekerjaan $pekerjaan)
    {
        if ($pekerjaan->hasToolsBelumKembali()) {
            return back()->withErrors(['error' => 'Tidak bisa hapus! Masih ada tools yang dipinjam.']);
        }

        // Rollback semua stok
        DB::transaction(function () use ($pekerjaan) {
            foreach ($pekerjaan->transaksi as $trx) {
                $barang = $trx->barang;
                // Hanya rollback yang belum dikembalikan (cons/material)
                if ($trx->status_pinjam === null) {
                    $barang->update(['stok' => $barang->stok + $trx->jumlah]);
                }
            }
            $pekerjaan->delete();
        });

        return redirect()->route('pekerjaan.index')
            ->with('success', 'Pekerjaan berhasil dihapus!');
    }
}
