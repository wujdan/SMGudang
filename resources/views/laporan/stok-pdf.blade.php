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
            text-transform: uppercase;
            font-size: 16px;
        }

        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-top: -5px;
            margin-bottom: 15px;
            color: #666;
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
            padding: 8px 6px;
            text-align: center;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
        }

        td {
            padding: 6px;
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

        .badge-status {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-habis {
            color: red;
        }

        .status-menipis {
            color: orange;
        }

        .status-aman {
            color: green;
        }

        .footer {
            margin-top: 15px;
            width: 100%;
        }

        .info-bar {
            font-size: 10px;
            margin-bottom: 12px;
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .info-bar div {
            margin-bottom: 3px;
        }

        .signature {
            margin-top: 30px;
            width: 100%;
        }
    </style>
</head>

<body>
    <h2>LAPORAN STOK BARANG</h2>
    <div class="subtitle">GudangKu - Sistem Manajemen Gudang</div>

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

        $statusLabel = request('status') ? strtoupper(request('status')) : 'SEMUA';

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

        // Hitung total nilai aset stok menggunakan prices
        $totalNilaiAset = $barang->sum(function ($item) {
            $harga = $item->prices ?? ($item->harga ?? 0);
            return $item->stok * $harga;
        });
    @endphp

    <div class="info-bar">
        <div><strong>Tanggal Cetak:</strong> {{ date('d-m-Y H:i:s') }}</div>
        <div><strong>Kategori:</strong> {{ $kategoriLabel }} | <strong>Status:</strong> {{ $statusLabel }}</div>
        <div><strong>Total Item:</strong> {{ $barang->count() }} barang</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 90px;">Kode Barang</th>
                <th>Nama Barang</th>
                <th style="width: 85px;">Kategori</th>
                <th style="width: 55px;">Satuan</th>
                <th style="width: 45px;">Stok</th>
                <th style="width: 100px;">Harga</th>
                <th style="width: 110px;">Total Nilai</th>
                <th style="width: 65px;">Status</th>
                @if (request('kategori') == 'tools' || !request('kategori'))
                    <th style="width: 55px;">Dipinjam</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($barang as $index => $item)
                @php
                    // Gunakan prices seperti di view
                    $harga = $item->prices ?? ($item->harga ?? 0);
                    $totalNilaiItem = $item->stok * $harga;

                    // Tentukan status stok
                    if ($item->stok == 0) {
                        $statusStok = 'HABIS';
                        $statusClass = 'status-habis';
                    } elseif ($item->isStokMenipis()) {
                        $statusStok = 'MENIPIS';
                        $statusClass = 'status-menipis';
                    } else {
                        $statusStok = 'AMAN';
                        $statusClass = 'status-aman';
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td class="text-left">{{ $item->nama_barang }}</td>
                    <td>{{ $formatKategori($item->kategori) }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td class="bold">{{ number_format($item->stok) }}</td>
                    <td class="text-right">
                        @if ($item->kategori === 'tools')
                            <span style="color: #999; font-style: italic;">-</span>
                        @else
                            Rp {{ number_format($harga, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="text-right bold">
                        @if ($item->kategori === 'tools')
                            <span style="color: #999;">-</span>
                        @else
                            Rp {{ number_format($totalNilaiItem, 0, ',', '.') }}
                        @endif
                    </td>
                    <td class="{{ $statusClass }} bold">{{ $statusStok }}</td>
                    @if (request('kategori') == 'tools' || !request('kategori'))
                        <td>
                            @if ($item->isTools() && ($item->stok_dipinjam ?? 0) > 0)
                                {{ $item->stok_dipinjam }}
                            @else
                                -
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ request('kategori') == 'tools' || !request('kategori') ? 10 : 9 }}">
                        <div style="padding: 20px; text-align: center;">Tidak ada data stok barang</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($barang->count() > 0 && $barang->where('kategori', '!=', 'tools')->count() > 0)
            <tfoot>
                <tr style="background: #f9f9f9;">
                    <td colspan="7" class="text-right bold">TOTAL NILAI ASET STOK</td>
                    <td class="text-right bold">Rp {{ number_format($totalNilaiAset, 0, ',', '.') }}</td>
                    <td colspan="{{ request('kategori') == 'tools' || !request('kategori') ? 2 : 1 }}"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="signature">
        <table style="border: none; width: 100%;">
            <tr>
                <td style="border: none; width: 50%;">
                    <div style="font-size: 9px; color: #666;">
                        Dicetak pada: {{ date('d/m/Y H:i:s') }}<br>
                        Sistem: GudangKu v1.0
                    </div>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <div style="margin-bottom: 50px;">Mengetahui,</div>
                    <div style="margin-top: 5px;">
                        <div style="border-top: 1px solid black; width: 150px; margin: 0 auto;"></div>
                        <div style="margin-top: 5px;">(Admin Gudang)</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
