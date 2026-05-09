@extends('layouts.pdf')

@section('title', 'Laporan Stok Barang')

@section('heading', 'LAPORAN STOK BARANG')

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

    // Hitung total nilai aset stok
    $totalNilaiAset = $barang->sum(function ($item) {
        $harga = $item->prices ?? ($item->harga ?? 0);

        return $item->stok * $harga;
    });
@endphp

@section('info-bar')

    <div>
        <strong>Tanggal Cetak:</strong>
        {{ date('d-m-Y H:i:s') }}
    </div>

    <div>
        <strong>Kategori:</strong>
        {{ $kategoriLabel }}

        |

        <strong>Status:</strong>
        {{ $statusLabel }}
    </div>

    <div>
        <strong>Total Item:</strong>
        {{ $barang->count() }} barang
    </div>

@endsection

@section('content')

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>

                <th style="width: 90px;">
                    Kode Barang
                </th>

                <th>
                    Nama Barang
                </th>

                <th style="width: 85px;">
                    Kategori
                </th>

                <th style="width: 55px;">
                    Satuan
                </th>

                <th style="width: 45px;">
                    Stok
                </th>

                <th style="width: 100px;">
                    Harga
                </th>

                <th style="width: 110px;">
                    Total Nilai
                </th>

                <th style="width: 65px;">
                    Status
                </th>

                @if (request('kategori') == 'tools' || !request('kategori'))
                    <th style="width: 55px;">
                        Dipinjam
                    </th>
                @endif
            </tr>
        </thead>

        <tbody>

            @forelse ($barang as $index => $item)

                @php
                    $harga = $item->prices ?? ($item->harga ?? 0);

                    $totalNilaiItem = $item->stok * $harga;

                    // Status stok
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

                    <td>
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $item->kode_barang }}
                    </td>

                    <td class="text-left">
                        {{ $item->nama_barang }}
                    </td>

                    <td>
                        {{ $formatKategori($item->kategori) }}
                    </td>

                    <td>
                        {{ $item->satuan }}
                    </td>

                    <td class="bold">
                        {{ number_format($item->stok) }}
                    </td>

                    <td class="text-right">

                        @if ($item->kategori === 'tools')
                            <span
                                style="
                            color: #999;
                            font-style: italic;
                        ">
                                -
                            </span>
                        @else
                            Rp
                            {{ number_format($harga, 0, ',', '.') }}
                        @endif

                    </td>

                    <td class="text-right bold">

                        @if ($item->kategori === 'tools')
                            <span style="color: #999;">
                                -
                            </span>
                        @else
                            Rp
                            {{ number_format($totalNilaiItem, 0, ',', '.') }}
                        @endif

                    </td>

                    <td class="{{ $statusClass }} bold">
                        {{ $statusStok }}
                    </td>

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

                    <td
                        colspan="
                    {{ request('kategori') == 'tools' || !request('kategori') ? 10 : 9 }}
                ">

                        <div
                            style="
                        padding: 20px;
                        text-align: center;
                    ">
                            Tidak ada data stok barang
                        </div>

                    </td>

                </tr>

            @endforelse

        </tbody>

        @if ($barang->count() > 0 && $barang->where('kategori', '!=', 'tools')->count() > 0)
            <tfoot>

                <tr style="background: #f9f9f9;">

                    <td colspan="7" class="text-right bold">

                        TOTAL NILAI ASET STOK

                    </td>

                    <td class="text-right bold">

                        Rp
                        {{ number_format($totalNilaiAset, 0, ',', '.') }}

                    </td>

                    <td
                        colspan="
                    {{ request('kategori') == 'tools' || !request('kategori') ? 2 : 1 }}
                ">
                    </td>

                </tr>

            </tfoot>
        @endif

    </table>

@endsection
