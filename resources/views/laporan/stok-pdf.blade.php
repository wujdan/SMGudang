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
            padding: 6px;
            text-align: center;
            text-transform: uppercase;
            font-size: 10px;
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

        .footer {
            margin-top: 15px;
            width: 100%;
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

        // Hitung total nilai aset stok
        $totalNilaiAset = $barang->sum(fn($item) => $item->stok * ($item->hpp_satuan ?? 0));
    @endphp

    <div style="font-size: 12px; margin-bottom: 10px;">
        <div style="float: left;">
            <strong>Tanggal Cetak:</strong> {{ date('d-m-Y') }}
        </div>
        <div style="float: right; text-align: right;">
            <strong>Kategori:</strong> {{ $kategoriLabel }} |
            <strong>Status:</strong> {{ request('status') ? strtoupper(request('status')) : 'SEMUA' }}
        </div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 80px;">Kode</th>
                <th>Nama Barang</th>
                <th style="width: 80px;">Kategori</th>
                <th style="width: 50px;">Satuan</th>
                <th style="width: 40px;">Stok</th>
                <th style="width: 80px;">HPP Satuan</th>
                <th style="width: 100px;">Total Nilai</th>
                <th style="width: 60px;">Status</th>
                <th style="width: 50px;">Pinjam</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($barang as $index => $item)
                @php
                    $totalNilaiItem = $item->stok * ($item->hpp_satuan ?? 0);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->kode_barang }}</td>
                    <td class="text-left">{{ $item->nama_barang }}</td>
                    <td>{{ $formatKategori($item->kategori) }}</td>
                    <td>{{ $item->satuan }}</td>
                    <td class="bold">{{ number_format($item->stok) }}</td>
                    <td class="text-right">Rp {{ number_format($item->hpp_satuan ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right bold">Rp {{ number_format($totalNilaiItem, 0, ',', '.') }}</td>
                    <td>
                        @if ($item->stok == 0)
                            <span style="color: red;">Habis</span>
                        @elseif ($item->stok <= $item->min_stok)
                            <span style="color: orange;">Menipis</span>
                        @else
                            Aman
                        @endif
                    </td>
                    <td>{{ $item->dipinjam ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Data tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
        @if ($barang->count() > 0)
            <tfoot>
                <tr class="bold" style="background: #f9f9f9;">
                    <td colspan="7" class="text-right">TOTAL NILAI ASET STOK</td>
                    <td class="text-right">Rp {{ number_format($totalNilaiAset, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table class="footer" style="border: none;">
        <tr>
            <td style="border: none; text-align: left; color: #666; font-size: 9px;">
                GudangKu — Sistem Manajemen Gudang<br>
                Dicetak oleh sistem secara otomatis.
            </td>
            <td style="border: none; width: 200px;">
                <div style="margin-bottom: 50px;">Penanggung Jawab,</div>
                <div style="border-top: 1px solid black; width: 150px; margin: 0 auto;"></div>
            </td>
        </tr>
    </table>

</body>

</html>
