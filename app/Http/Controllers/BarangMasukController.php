<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
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
            'items.*.tanggal' => 'required|date',
            'items.*.sumber' => 'nullable|string|max:255',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $barang = Barang::lockForUpdate()->find($item['barang_id']);
                $stokSebelum = $barang->stok;
                $stokSesudah = $stokSebelum + $item['jumlah'];

                BarangMasuk::create([
                    'no_transaksi' => BarangMasuk::generateNoTransaksi(),
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $stokSesudah,
                    'tanggal' => $item['tanggal'],
                    'sumber' => $item['sumber'] ?? null,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                $barang->update(['stok' => $stokSesudah]);
            }
        });

        return redirect()->route('barang-masuk.index')
            ->with('success', 'Barang masuk berhasil dicatat!');
    }

    public function destroy(BarangMasuk $barangMasuk)
    {
        DB::transaction(function () use ($barangMasuk) {
            // Rollback stok
            $barang = $barangMasuk->barang;
            $barang->update(['stok' => $barang->stok - $barangMasuk->jumlah]);
            $barangMasuk->delete();
        });

        return back()->with('success', 'Transaksi masuk berhasil dihapus!');
    }
}
