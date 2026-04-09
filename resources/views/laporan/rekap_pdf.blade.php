<!DOCTYPE html>
<html>

<head>
    <title>Rekap Barang Per Pekerjaan</title>
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

    <h2>REKAP BARANG PER PEKERJAAN</h2>

    <!-- INFO FILTER -->
    @php
        $dari = request('dari') ?? \Carbon\Carbon::now()->subDays(30)->format('Y-m-d');
        $sampai = request('sampai') ?? \Carbon\Carbon::now()->format('Y-m-d');
        $search = request('search');
        $status = request('status');

        if ($status === 'aktif') {
            $statusLabel = 'Aktif';
        } elseif ($status === 'selesai') {
            $statusLabel = 'Selesai';
        } else {
            $statusLabel = 'SEMUA';
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
            <strong>Status:</strong> {{ $statusLabel }}
        </div>
        @if ($search)
            <div>
                <strong>Pencarian:</strong> {{ $search }}
            </div>
        @endif
    </div>

    <!-- TABEL -->
    @forelse ($pekerjaan as $p)
        <!-- Header Pekerjaan -->
        <div style="margin-top: 14px; margin-bottom: 4px; font-size: 11px;">
            <strong>{{ $p->kode_pekerjaan }} — {{ $p->nama_pekerjaan }}</strong>
            &nbsp;|&nbsp; Status: <strong>{{ strtoupper($p->status) }}</strong>
            &nbsp;|&nbsp; Peminjam: {{ $p->nama_peminjam }}
            @if ($p->lokasi)
                &nbsp;|&nbsp; Lokasi: {{ $p->lokasi }}
            @endif
            &nbsp;|&nbsp; Tgl Mulai: {{ date('d-m-Y', strtotime($p->tanggal_mulai)) }}
        </div>

        @if ($p->transaksi->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Satuan</th>
                        <th>Tgl Keluar</th>
                        <th>Status / Kembali</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($p->transaksi as $i => $t)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-left">{{ $t->barang->nama_barang ?? '-' }}</td>
                            <td>{{ strtoupper($t->barang->kategori ?? '-') }}</td>
                            <td>{{ $t->jumlah }}</td>
                            <td>{{ $t->barang->satuan ?? '-' }}</td>
                            <td>{{ date('d-m-Y', strtotime($t->tanggal_keluar)) }}</td>
                            <td>
                                @if ($t->status_pinjam)
                                    {{ $t->status_label }}
                                    @if ($t->tgl_kembali_aktual)
                                        ({{ date('d-m-Y', strtotime($t->tgl_kembali_aktual)) }})
                                    @endif
                                @else
                                    Keluar Permanen
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table>
                <tbody>
                    <tr>
                        <td>Belum ada barang dicatat</td>
                    </tr>
                </tbody>
            </table>
        @endif

    @empty
        <table>
            <tbody>
                <tr>
                    <td>Tidak ada data pekerjaan</td>
                </tr>
            </tbody>
        </table>
    @endforelse

</body>

</html>
