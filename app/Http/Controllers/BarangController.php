<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BatchBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BarangController extends Controller
{
    public function index(Request $request)
    {
        $query = Barang::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%')
                    ->orWhere('kode_barang', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            if ($request->status === 'menipis') {
                $query->whereRaw('stok <= stok_minimum');
            } elseif ($request->status === 'habis') {
                $query->where('stok', 0);
            }
        }

        $barang = $query->where('is_active', true)
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('barang.index', compact('barang'));
    }

    public function create()
    {
        return view('barang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|in:cons,material,tools',
            'satuan' => 'required|string|max:50',
            'stok' => 'required|integer|min:0',
            'stok_minimum' => 'required|integer|min:0',
            'foto' => 'nullable|image|max:2048',
            'keterangan' => 'nullable|string',
            'prices' => 'nullable|numeric|min:0',
        ]);

        if ($request->kategori === 'tools') {
            $validated['prices'] = 0;
        }

        $validated['kode_barang'] = Barang::generateKode($request->kategori);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('barang', 'public');
        }

        // ✅ Tambahkan nama penginput
        $validated['created_by_name'] = auth()->user()->name;

        DB::transaction(function () use ($validated, $request) {
            // Simpan barang dengan prices dan created_by_name
            $barang = Barang::create($validated);

            if ($request->stok > 0) {
                $noTransaksi = BarangMasuk::generateNoTransaksi();
                $tanggal = now()->toDateString();

                BarangMasuk::create([
                    'no_transaksi' => $noTransaksi,
                    'barang_id' => $barang->id,
                    'jumlah' => $request->stok,
                    'harga_satuan' => $validated['prices'],
                    'stok_sebelum' => 0,
                    'stok_sesudah' => $request->stok,
                    'tanggal' => $tanggal,
                    'sumber' => 'Stok Awal',
                    'keterangan' => 'Stok awal saat pembuatan barang',
                    'created_by_name' => auth()->user()->name,
                ]);

                BatchBarang::create([
                    'barang_id' => $barang->id,
                    'no_transaksi_masuk' => $noTransaksi,
                    'tanggal_masuk' => $tanggal,
                    'qty_awal' => $request->stok,
                    'qty_sisa' => $request->stok,
                    'harga_satuan' => $validated['prices'],
                    'created_by_name' => auth()->user()->name,
                ]);
            }
        });

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan!');
    }

    public function show(Barang $barang)
    {
        $riwayatMasuk = $barang->barangMasuk()->orderByDesc('tanggal')->take(10)->get();
        $riwayatKeluar = $barang->transaksiPekerjaan()->with('pekerjaan')
            ->orderByDesc('tanggal_keluar')->take(10)->get();
        return view('barang.show', compact('barang', 'riwayatMasuk', 'riwayatKeluar'));
    }

    public function edit(Barang $barang)
    {
        return view('barang.edit', compact('barang'));
    }

    public function update(Request $request, Barang $barang)
    {
        // Perhatikan: stok tidak boleh diupdate langsung melalui form edit.
        // Stok hanya boleh berubah melalui transaksi masuk/keluar.
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|in:cons,material,tools',
            'satuan' => 'required|string|max:50',
            'stok_minimum' => 'required|integer|min:0',
            'foto' => 'nullable|image|max:2048',
            'keterangan' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            if ($barang->foto) {
                Storage::disk('public')->delete($barang->foto);
            }
            $validated['foto'] = $request->file('foto')->store('barang', 'public');
        }

        $barang->update($validated);

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil diupdate!');
    }

    public function destroy(Barang $barang)
    {
        DB::transaction(function () use ($barang) {

            // hapus foto
            if ($barang->foto) {
                Storage::disk('public')->delete($barang->foto);
            }

            // hapus relasi manual jika belum cascade
            $barang->barangMasuk()->delete();
            $barang->batchBarang()->delete();

            // hapus barang
            $barang->delete();
        });

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus!');
    }

    // API untuk autocomplete/search di form
    public function search(Request $request)
    {
        $barang = Barang::where('is_active', true)
            ->where(function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->q . '%')
                    ->orWhere('kode_barang', 'like', '%' . $request->q . '%');
            });

        if ($request->filled('kategori')) {
            $barang->where('kategori', $request->kategori);
        }

        return response()->json($barang->take(10)->get([
            'id',
            'kode_barang',
            'nama_barang',
            'kategori',
            'satuan',
            'stok'
        ]));
    }
}