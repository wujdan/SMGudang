<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BatchBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = BarangMasuk::with('barang');

        if ($request->filled('search')) {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%');
            })->orWhere('no_transaksi', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('kategori')) {
            $query->whereHas('barang', fn($q) => $q->where('kategori', $request->kategori));
        }

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        $barangMasuk = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('transaksi.masuk.index', compact('barangMasuk'));
    }

    public function create()
    {
        $barang = Barang::where('is_active', true)->orderBy('nama_barang')->get();
        return view('transaksi.masuk.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',

            // nullable karena tools bisa disable input
            'items.*.harga_satuan' => 'nullable',

            'items.*.tanggal' => 'required|date',
            'items.*.sumber' => 'nullable|string|max:255',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {

            foreach ($validated['items'] as $item) {

                $barang = Barang::lockForUpdate()
                    ->find($item['barang_id']);

                // NORMALISASI HARGA
                if ($barang->kategori === 'tools') {
                    $hargaSatuan = 0;
                } else {
                    $hargaSatuan = str_replace('.', '', $item['harga_satuan'] ?? 0);
                    $hargaSatuan = (float) $hargaSatuan;
                }

                $stokSebelum = $barang->stok;
                $stokSesudah = $stokSebelum + $item['jumlah'];
                $noTransaksi = BarangMasuk::generateNoTransaksi();

                // 1. SIMPAN BARANG MASUK
                $existingMasuk = BarangMasuk::where('barang_id', $item['barang_id'])
                    ->whereDate('tanggal', $item['tanggal'])
                    ->where('harga_satuan', $hargaSatuan)
                    ->first();

                if ($existingMasuk) {
                    // UPDATE JUMLAH (tanpa ubah created_by_name)
                    $existingMasuk->update([
                        'jumlah' => $existingMasuk->jumlah + $item['jumlah'],
                        'stok_sesudah' => $existingMasuk->stok_sesudah + $item['jumlah'],
                    ]);
                } else {
                    // BUAT BARU -> catat penginput
                    BarangMasuk::create([
                        'no_transaksi' => $noTransaksi,
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $hargaSatuan,
                        'stok_sebelum' => $stokSebelum,
                        'stok_sesudah' => $stokSesudah,
                        'tanggal' => $item['tanggal'],
                        'sumber' => $item['sumber'] ?? null,
                        'keterangan' => $item['keterangan'] ?? null,
                        'created_by_name' => auth()->user()->name, // ✅
                    ]);
                }

                // 2. UPDATE MASTER BARANG
                $updateData = ['stok' => $stokSesudah];
                if ($barang->kategori !== 'tools') {
                    $updateData['prices'] = $hargaSatuan;
                }
                $barang->update($updateData);

                // 3. SIMPAN BATCH FIFO
                $existingBatch = BatchBarang::where('barang_id', $item['barang_id'])
                    ->whereDate('tanggal_masuk', $item['tanggal'])
                    ->where('harga_satuan', $hargaSatuan)
                    ->first();

                if ($existingBatch) {
                    // TAMBAH QTY (tanpa ubah created_by_name)
                    $existingBatch->update([
                        'qty_awal' => $existingBatch->qty_awal + $item['jumlah'],
                        'qty_sisa' => $existingBatch->qty_sisa + $item['jumlah'],
                    ]);
                } else {
                    // BUAT BARU -> catat penginput
                    BatchBarang::create([
                        'barang_id' => $item['barang_id'],
                        'no_transaksi_masuk' => $noTransaksi,
                        'tanggal_masuk' => $item['tanggal'],
                        'qty_awal' => $item['jumlah'],
                        'qty_sisa' => $item['jumlah'],
                        'harga_satuan' => $hargaSatuan,
                        'created_by_name' => auth()->user()->name, // ✅
                    ]);
                }
            }
        });

        return redirect()
            ->route('barang-masuk.index')
            ->with('success', 'Barang masuk berhasil dicatat!');
    }

    public function destroy(BarangMasuk $barangMasuk)
    {
        DB::transaction(function () use ($barangMasuk) {
            $barang = $barangMasuk->barang;

            // Rollback stok
            $barang->update(['stok' => $barang->stok - $barangMasuk->jumlah]);

            // Hapus batch yang terkait transaksi ini
            BatchBarang::where('no_transaksi_masuk', $barangMasuk->no_transaksi)->delete();

            // Update harga master ke batch terbaru yang masih ada
            $batchTerbaru = BatchBarang::where('barang_id', $barang->id)
                ->orderByDesc('tanggal_masuk')
                ->orderByDesc('id')
                ->first();

            $barang->update([
                'prices' => $batchTerbaru ? $batchTerbaru->harga_satuan : 0,
            ]);

            $barangMasuk->delete();
        });

        return back()->with('success', 'Transaksi masuk dihapus, stok & harga dikembalikan.');
    }
}