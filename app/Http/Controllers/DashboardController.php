<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Summary cards
        $totalBarang = Barang::where('is_active', true)->count();
        $stokMenipis = Barang::where('is_active', true)
            ->whereRaw('stok <= stok_minimum')
            ->count();
        $toolsDipinjam = TransaksiPekerjaan::where('status_pinjam', 'dipinjam')->sum('jumlah');
        $toolsTerlambat = TransaksiPekerjaan::where('status_pinjam', 'dipinjam')
            ->whereNotNull('tgl_kembali_rencana')
            ->where('tgl_kembali_rencana', '<', today())
            ->count();

        // Stok menipis list
        $barangMenipis = Barang::where('is_active', true)
            ->whereRaw('stok <= stok_minimum')
            ->orderBy('stok')
            ->take(8)
            ->get();

        // Tools aktif dipinjam
        $toolsAktif = TransaksiPekerjaan::with(['barang', 'pekerjaan'])
            ->where('status_pinjam', 'dipinjam')
            ->orderBy('tgl_kembali_rencana')
            ->take(8)
            ->get();

        // Chart: Barang masuk 7 hari terakhir
        $chartMasuk = BarangMasuk::select(
            DB::raw('DATE(tanggal) as tgl'),
            DB::raw('SUM(jumlah) as total')
        )
            ->where('tanggal', '>=', now()->subDays(6))
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl');

        // Chart: Barang keluar 7 hari terakhir
        $chartKeluar = TransaksiPekerjaan::select(
            DB::raw('DATE(tanggal_keluar) as tgl'),
            DB::raw('SUM(jumlah) as total')
        )
            ->where('tanggal_keluar', '>=', now()->subDays(6))
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->pluck('total', 'tgl');

        // Isi hari yang kosong
        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d/m');
            $labels[] = $label;
            $dataMasuk[] = $chartMasuk[$date] ?? 0;
            $dataKeluar[] = $chartKeluar[$date] ?? 0;
        }

        // Stok per kategori
        $stokKategori = Barang::where('is_active', true)
            ->select('kategori', DB::raw('SUM(stok) as total_stok'), DB::raw('COUNT(*) as jumlah_item'))
            ->groupBy('kategori')
            ->get();

        // Pekerjaan aktif terbaru
        $pekerjaanAktif = Pekerjaan::where('status', 'aktif')
            ->withCount(['transaksi'])
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalBarang', 'stokMenipis', 'toolsDipinjam', 'toolsTerlambat',
            'barangMenipis', 'toolsAktif', 'labels', 'dataMasuk', 'dataKeluar',
            'stokKategori', 'pekerjaanAktif'
        ));
    }
}
