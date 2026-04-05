<!DOCTYPE html>
<html>

<head>
    <title>Laporan Barang KELUAR</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th {
            background: #f2f2f2;
        }

        th,
        td {
            padding: 5px;
            text-align: center;
        }

        td.text-left {
            text-align: left;
        }
    </style>
</head>

<body>

    <h2>LAPORAN BARANG KELUAR</h2>

    <!-- INFO FILTER -->
    @php
        $dari = request('dari') ?? \Carbon\Carbon::now()->subDays(30)->format('Y-m-d');
        $sampai = request('sampai') ?? \Carbon\Carbon::now()->format('Y-m-d');
        $kategori = request('kategori');
        if ($kategori === 'cons') {
            $kategoriLabel = 'Consumable';
        } elseif ($kategori === 'material') {
            $kategoriLabel = 'Material';
        } elseif ($kategori === 'tools') {
            $kategoriLabel = 'Tools';
        } elseif ($kategori) {
            $kategoriLabel = strtoupper($kategori);
        } else {
            $kategoriLabel = 'SEMUA';
        }
    @endphp

    <div style="font-size: 12px; margin-bottom: 10px;">
        <div>
            <strong>Periode:</strong>
            {{ date('d-m-Y', strtotime($dari)) }}
            s/d
            {{ date('d-m-Y', strtotime($sampai)) }}
        </div>

        <div>
            <strong>Kategori:</strong>
            {{ $kategoriLabel }}
            <strong>Status:</strong>
            {{ request('status') ? strtoupper(request('status')) : 'Semua' }}
        </div>

    </div>
    <!-- TABEL -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Transaksi</th>
                <th>Tgl Keluar</th>
                <th>Pekerjaan</th>
                <th>PIC</th>
                <th>Barang</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Tipe</th>
                <th>Rencana Kembali</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->no_transaksi ?? '-' }}</td>
                    <td>{{ date('d-m-Y', strtotime($item->tanggal_keluar)) }}</td>
                    <td class="text-left">{{ $item->pekerjaan->nama_pekerjaan ?? '-' }}</td>
                    <td>{{ $item->pekerjaan->nama_peminjam ?? '-' }}</td>
                    <td class="text-left">{{ $item->barang->nama_barang ?? '-' }}</td>
                    <td>{{ strtoupper($item->barang->kategori ?? '-') }}</td>
                    <td>{{ $item->jumlah }}</td>
                    <td>
                        {{ $item->status_pinjam ? 'PINJAM' : 'PERMANEN' }}
                    </td>

                    <td>
                        <span style="color: {{ $item->isTerlambat() ? 'red' : 'black' }};">
                            {{ $item->tgl_kembali_rencana ? $item->tgl_kembali_rencana->format('d-m-Y') : '-' }}
                        </span>
                    </td>

                    <td>
                        {{ strtoupper($item->status_label ?? '-') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
