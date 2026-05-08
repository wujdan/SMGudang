<!DOCTYPE html>
<html>

<head>
    <title>Laporan Barang Keluar</title>
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
            text-transform: uppercase;
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
            padding: 5px;
            text-align: center;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }

        td.text-left {
            text-align: left;
        }

        td.text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            width: 100%;
        }
    </style>
</head>

<body>

    <h2>LAPORAN BARANG KELUAR</h2>

    @php
        $dari = request('dari') ?? \Carbon\Carbon::now()->subDays(30)->format('Y-m-d');
        $sampai = request('sampai') ?? \Carbon\Carbon::now()->format('Y-m-d');
        $kategori = request('kategori');
        $statusReq = request('status');

        $totalQty = $data->sum('jumlah');
        $totalHpp = $data->sum('total_hpp');
    @endphp

    <div style="font-size: 12px; margin-bottom: 10px;">
        <div>
            <strong>Periode:</strong>
            {{ date('d-m-Y', strtotime($dari)) }} s/d {{ date('d-m-Y', strtotime($sampai)) }}
        </div>
        <div>
            <strong>Kategori:</strong> {{ $kategori ? strtoupper($kategori) : 'SEMUA' }}
        </div>
        <div>
            <strong>Status:</strong> {{ $statusReq ? strtoupper($statusReq) : 'SEMUA' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Tgl Keluar</th>
                <th>Pekerjaan / PIC</th>
                <th>Barang</th>
                <th style="width: 60px;">Kategori</th>
                <th style="width: 40px;">Qty</th>
                <th style="width: 80px;">HPP / Unit</th>
                <th style="width: 90px;">Total HPP</th>
                <th style="width: 70px;">Tipe</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->tanggal_keluar ? $item->tanggal_keluar->format('d-m-Y') : '-' }}</td>
                    <td class="text-left">
                        <strong>{{ $item->pekerjaan->nama_pekerjaan ?? '-' }}</strong><br>
                        <small>PIC: {{ $item->pekerjaan->nama_peminjam ?? '-' }}</small>
                    </td>
                    <td class="text-left">{{ $item->barang->nama_barang ?? '-' }}</td>
                    <td>{{ strtoupper($item->barang->kategori ?? '-') }}</td>
                    <td class="bold">{{ number_format($item->jumlah) }}</td>
                    <td class="text-right">Rp {{ number_format($item->hpp_satuan ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right bold">Rp {{ number_format($item->total_hpp ?? 0, 0, ',', '.') }}</td>
                    <td>{{ $item->status_pinjam ? 'PINJAM' : 'PERMANEN' }}</td>
                    <td>{{ strtoupper($item->status_label ?? '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Tidak ada data ditemukan</td>
                </tr>
            @endforelse
        </tbody>
        @if ($data->count() > 0)
            <tfoot>
                <tr class="bold" style="background: #f9f9f9;">
                    <td colspan="5" class="text-right">TOTAL KESELURUHAN</td>
                    <td>{{ number_format($totalQty) }}</td>
                    <td></td>
                    <td class="text-right">Rp {{ number_format($totalHpp, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>

</html>
