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
                        <th>Total Jumlah</th>
                        <th>Satuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Group by nama_barang + kategori + status_pinjam, lalu sum jumlah
                        $grouped = $p->transaksi
                            ->groupBy(
                                fn($t) => $t->barang->nama_barang .
                                    '|' .
                                    $t->barang->kategori .
                                    '|' .
                                    ($t->status_pinjam ?? 'permanen'),
                            )
                            ->map(
                                fn($group) => (object) [
                                    'nama_barang' => $group->first()->barang->nama_barang,
                                    'kategori' => $group->first()->barang->kategori,
                                    'satuan' => $group->first()->barang->satuan,
                                    'status_pinjam' => $group->first()->status_pinjam,
                                    'status_label' => $group->first()->status_label ?? null,
                                    'tgl_kembali_aktual' => $group->first()->tgl_kembali_aktual,
                                    'total_jumlah' => $group->sum('jumlah'),
                                ],
                            );
                    @endphp

                    @foreach ($grouped as $i => $row)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            <td class="text-left">{{ $row->nama_barang }}</td>
                            <td>{{ strtoupper($row->kategori) }}</td>
                            <td>{{ $row->total_jumlah }}</td>
                            <td>{{ $row->satuan }}</td>
                            <td>
                                @if ($row->status_pinjam)
                                    {{ $row->status_label }}
                                    @if ($row->tgl_kembali_aktual)
                                        ({{ date('d-m-Y', strtotime($row->tgl_kembali_aktual)) }})
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
