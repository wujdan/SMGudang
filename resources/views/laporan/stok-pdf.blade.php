<!DOCTYPE html>
<html>

<head>
    <title>Laporan Stok Barang</title>
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
            background-color: #f2f2f2;
        }

        th,
        td {
            padding: 6px;
            text-align: center;
        }

        td.text-left {
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>LAPORAN STOK BARANG</h2>

    @php
        $kategori = request('kategori');
        $kategoriLabel = 'SEMUA';
        if ($kategori === 'cons') {
            $kategoriLabel = 'Consumable';
        } elseif ($kategori === 'material') {
            $kategoriLabel = 'Material';
        } elseif ($kategori === 'tools') {
            $kategoriLabel = 'Tools';
        } elseif ($kategori) {
            $kategoriLabel = strtoupper($kategori);
        }

        $formatKategori = function ($value) {
            if ($value === 'cons') {
                return 'Consumable';
            }
            if ($value === 'material') {
                return 'Material';
            }
            if ($value === 'tools') {
                return 'Tools';
            }
            return $value ? strtoupper($value) : '-';
        };
    @endphp

    <div
        style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 10px;">

        <div>
            Tanggal: {{ date('d-m-Y') }}
        </div>

        <div>
            <strong>Kategori:</strong>
            {{ $kategoriLabel }}
      
            <strong>Status:</strong>
            {{ request('status') ? strtoupper(request('status')) : 'SEMUA' }}
        </div>

    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Dipinjam</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barang as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>   
                    <td class="text-left">{{ $item->nama_barang }}</td>
                    <td>{{ $formatKategori($item->kategori) }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td>{{ $item->stok }}</td>
                    <td>
                        @if ($item->stok == 0)
                            Habis
                        @elseif ($item->stok <= $item->min_stok)
                            Menipis
                        @else
                            Aman
                        @endif
                    </td>
                    <td>{{ $item->dipinjam ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
