<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangKeluarController extends Controller
{
    public function index()
    {
        $pekerjaan = Pekerjaan::where('status', 'aktif')
            ->withCount(['transaksi', 'toolsDipinjam'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('transaksi.keluar.index', compact('pekerjaan'));
    }

    public function create(Pekerjaan $pekerjaan)
    {
        if ($pekerjaan->status !== 'aktif') {
            return redirect()->route('barang-keluar.index')
                ->withErrors(['error' => 'Pekerjaan sudah selesai!']);
        }

        $barang = Barang::where('is_active', true)->orderBy('kategori')->orderBy('nama_barang')->get();

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

            // Cek existing — khusus cons & material saja, tools selalu baris baru
            $existing = null;
            if (!$barang->isTools()) {
                $existing = TransaksiPekerjaan::where('pekerjaan_id', $pekerjaan->id)
                    ->where('barang_id', $item['barang_id'])
                    ->whereDate('tanggal_keluar', today())
                    ->whereNull('status_pinjam')
                    ->first();
            }

            if ($existing) {
                // ✅ Tanggal sama → tambah jumlah di baris yang ada
                $existing->update([
                    'jumlah'       => $existing->jumlah + $item['jumlah'],
                    'stok_sesudah' => $existing->stok_sebelum - ($existing->jumlah + $item['jumlah']),
                    'keterangan'   => $item['keterangan'] ?? $existing->keterangan,
                ]);

                $barang->update(['stok' => $barang->stok - $item['jumlah']]);
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

    return redirect()->route('barang-keluar.index')
        ->with('success', 'Barang berhasil dicatat keluar!');
}
}