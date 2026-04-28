<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Pekerjaan;
use App\Models\TransaksiPekerjaan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StokExport;
use App\Exports\BarangMasukExport;
use App\Exports\BarangKeluarExport;

class LaporanController extends Controller
{
    public function stok(Request $request)
    {
        $query = Barang::where('is_active', true);

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('status')) {
            if ($request->status === 'menipis') {
                $query->whereRaw('stok <= stok_minimum');
            } elseif ($request->status === 'habis') {
                $query->where('stok', 0);
            } elseif ($request->status === 'aman') {
                $query->whereRaw('stok > stok_minimum');
            }
        }

        $barang = $query->orderBy('kategori')->orderBy('nama_barang')->get();

        if ($request->export === 'excel') {
            return Excel::download(
                new StokExport($barang, $request->kategori, $request->status),
                'laporan-stok-' . date('Ymd') . '.xlsx'
            );
        }

        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('laporan.stok-pdf', compact('barang'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-stok-' . date('Ymd') . '.pdf');
        }

        return view('laporan.stok', compact('barang'));
    }

    public function masuk(Request $request)
    {
        $query = BarangMasuk::with('barang');

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        } else {
            $query->where('tanggal', '>=', now()->subDays(30));
        }

        if ($request->filled('kategori')) {
            $query->whereHas('barang', fn($q) => $q->where('kategori', $request->kategori));
        }

        $data = $query->orderByDesc('tanggal')->get();
        $totalItems = $data->count();
        $totalJumlah = $data->sum('jumlah');

        if ($request->export === 'excel') {
            return Excel::download(
                new BarangMasukExport($data, $request->dari, $request->sampai, $request->kategori),
                'laporan-masuk-' . date('Ymd') . '.xlsx'
            );
        }

        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('laporan.masuk-pdf', compact('data', 'totalItems', 'totalJumlah'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-masuk-' . date('Ymd') . '.pdf');
        }

        return view('laporan.masuk', compact('data', 'totalItems', 'totalJumlah'));
    }

    public function keluar(Request $request)
    {
        $query = TransaksiPekerjaan::with(['barang', 'pekerjaan']);

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('tanggal_keluar', [$request->dari, $request->sampai]);
        } else {
            $query->where('tanggal_keluar', '>=', now()->subDays(30));
        }

        if ($request->filled('kategori')) {
            $query->whereHas('barang', fn($q) => $q->where('kategori', $request->kategori));
        }

        if ($request->filled('nama_barang')) {
        $query->whereHas('barang', fn($q) => $q->where('nama_barang', 'LIKE', '%' . $request->nama_barang . '%'));
    }

        if ($request->filled('status')) {
            if ($request->status === 'dipinjam') {
                $query->where('status_pinjam', 'dipinjam');
            } elseif ($request->status === 'dikembalikan') {
                $query->where('status_pinjam', 'dikembalikan');
            } elseif ($request->status === 'permanen') {
                $query->whereNull('status_pinjam');
            }
        }

        $data = $query->orderByDesc('updated_at')->get();
        $dari = $request->filled('dari') ? $request->dari : now()->subDays(30)->format('Y-m-d');
        $sampai = $request->filled('sampai') ? $request->sampai : now()->format('Y-m-d');

        if ($request->export === 'excel') {
            return Excel::download(
                new BarangKeluarExport($data, $dari, $sampai, $request->kategori, $request->status),
                'laporan-keluar-' . date('Ymd') . '.xlsx'
            );
        }

        if ($request->export === 'pdf') {
            $pdf = Pdf::loadView('laporan.keluar-pdf', compact('data'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('laporan-keluar-' . date('Ymd') . '.pdf');
        }

        return view('laporan.keluar', compact('data'));
    }

    public function statistik(Request $request)
    {
        $periode = $request->periode ?? 30;

        // Trend barang masuk & keluar per hari
        $trendMasuk = BarangMasuk::select(
            DB::raw('DATE(tanggal) as tgl'),
            DB::raw('SUM(jumlah) as total')
        )
            ->where('tanggal', '>=', now()->subDays($periode))
            ->groupBy('tgl')->orderBy('tgl')
            ->pluck('total', 'tgl');

        $trendKeluar = TransaksiPekerjaan::select(
            DB::raw('DATE(tanggal_keluar) as tgl'),
            DB::raw('SUM(jumlah) as total')
        )
            ->where('tanggal_keluar', '>=', now()->subDays($periode))
            ->groupBy('tgl')->orderBy('tgl')
            ->pluck('total', 'tgl');

        // Fill dates
        $labels = [];
        $dataMasuk = [];
        $dataKeluar = [];
        for ($i = $periode - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            $dataMasuk[] = $trendMasuk[$date] ?? 0;
            $dataKeluar[] = $trendKeluar[$date] ?? 0;
        }

        // Top 10 barang paling sering keluar
        $topKeluar = TransaksiPekerjaan::with('barang')
            ->select('barang_id', DB::raw('SUM(jumlah) as total_keluar'))
            ->where('tanggal_keluar', '>=', now()->subDays($periode))
            ->groupBy('barang_id')
            ->orderByDesc('total_keluar')
            ->take(10)
            ->get();

        // Stok menipis
        $stokMenipis = Barang::where('is_active', true)
            ->whereRaw('stok <= stok_minimum')
            ->orderBy('stok')
            ->get();

        // Tools masih dipinjam
        $toolsDipinjam = TransaksiPekerjaan::with(['barang', 'pekerjaan'])
            ->where('status_pinjam', 'dipinjam')
            ->orderBy('tgl_kembali_rencana')
            ->get();

        // Distribusi per kategori
        $distribusiKategori = Barang::where('is_active', true)
            ->select('kategori', DB::raw('COUNT(*) as count'), DB::raw('SUM(stok) as total_stok'))
            ->groupBy('kategori')
            ->get();

        return view('laporan.statistik', compact(
            'labels',
            'dataMasuk',
            'dataKeluar',
            'topKeluar',
            'stokMenipis',
            'toolsDipinjam',
            'distribusiKategori',
            'periode'
        ));
    }

    public function rekap(Request $request)
    {
        // Default tanggal (hanya untuk tampilan, bukan memaksa query)
        $dari = $request->input('dari', now()->subDays(30)->format('Y-m-d'));
        $sampai = $request->input('sampai', now()->format('Y-m-d'));

        $query = Pekerjaan::with(['transaksi' => function($q) {
          $q->orderBy('updated_at', 'desc')->with('barang');
        }]);

        // 🔍 Filter search
        if ($request->filled('search')) {
            $query->where('nama_pekerjaan', 'like', '%' . $request->search . '%');
        }

        // 🔍 Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 🔍 Filter per pekerjaan (untuk export tombol per baris)
        if ($request->filled('pekerjaan_id')) {
            $query->where('id', $request->pekerjaan_id);
        } else {
            // 📅 Filter tanggal — hanya aktif jika bukan export per pekerjaan
            $query->whereBetween('tanggal_mulai', [$dari, $sampai]);
        }

        $export = $request->get('export');

        // 📊 Ambil data
        if ($export === 'pdf' || $export === 'excel') {
            $pekerjaan = $query->orderByDesc('tanggal_mulai')->get();
        } else {
            $pekerjaan = $query->orderByDesc('tanggal_mulai')->paginate(10)->withQueryString();
        }

       // 📄 Export PDF
        if ($export === 'pdf') {
            $namaFile = $request->filled('pekerjaan_id')
                ? 'rekap-pekerjaan-' . $pekerjaan->first()?->kode_pekerjaan . '.pdf'
                : 'rekap-pekerjaan.pdf';

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.rekap_pdf', [
                'pekerjaan' => $pekerjaan,
                'dari'      => $dari,
                'sampai'    => $sampai,
                'search'    => $request->search,
                'status'    => $request->status,
            ])->setPaper('a4', 'landscape');

            return $pdf->download($namaFile);
            }

        // 📊 Export Excel
        if ($export === 'excel') {
            $namaFile = $request->filled('pekerjaan_id')
                ? 'rekap-pekerjaan-' . $pekerjaan->first()?->kode_pekerjaan . '.xlsx'
                : 'rekap-pekerjaan.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\PekerjaanExport(
                    $pekerjaan,
                    $dari,
                    $sampai,
                    $request->search,
                    $request->status
                ),
                $namaFile
            );
        }
        return view('laporan.rekap', compact('pekerjaan', 'dari', 'sampai'));
    }
}
