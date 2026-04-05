<!DOCTYPE html>
<html>

<head>
    <title>Laporan Barang Masuk</title>
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

    <h2>LAPORAN BARANG MASUK</h2>

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
        </div>
    </div>
    <!-- TABEL -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No Transaksi</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Kategori</th>
                <th>Jumlah</th>
                <th>Stok Sebelum</th>
                <th>Stok Sesudah</th>
                <th>Sumber</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->no_transaksi ?? '-' }}</td>
                    <td>{{ date('d-m-Y', strtotime($item->tanggal)) }}</td>
                    <td class="text-left">{{ $item->barang->nama_barang ?? '-' }}</td>
                    <td>{{ strtoupper($item->barang->kategori ?? '-') }}</td>
                    <td>{{ $item->jumlah }}</td>
                    <td>{{ $item->stok_sebelum }}</td>
                    <td>{{ $item->stok_sesudah }}</td>
                    <td class="text-left">{{ $item->sumber ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
