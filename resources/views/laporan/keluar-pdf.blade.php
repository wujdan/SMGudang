@extends('layouts.pdf')

@section('title', 'Laporan Barang Keluar')

@section('heading', 'LAPORAN BARANG KELUAR')

@php
    $dari = request('dari') ?? \Carbon\Carbon::now()->subDays(30)->format('Y-m-d');

    $sampai = request('sampai') ?? \Carbon\Carbon::now()->format('Y-m-d');

    $kategori = request('kategori');

    $statusReq = request('status');

    $namaBarang = request('nama_barang');

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

    if ($statusReq === 'permanen') {
        $statusLabel = 'PERMANEN';
    } elseif ($statusReq === 'dipinjam') {
        $statusLabel = 'DIPINJAM';
    } elseif ($statusReq === 'dikembalikan') {
        $statusLabel = 'DIKEMBALIKAN';
    } else {
        $statusLabel = 'SEMUA';
    }

    $totalJumlah = $data->sum('jumlah');

    $totalHpp = $data->sum('total_hpp');
@endphp

@section('info-bar')

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

    <div>
        <strong>Status:</strong>
        {{ $statusLabel }}
    </div>

    @if ($namaBarang)
        <div>
            <strong>Nama Barang:</strong>
            {{ $namaBarang }}
        </div>
    @endif

    <div>
        <strong>Total Data:</strong>
        {{ $data->count() }} transaksi
    </div>

@endsection

@section('content')

    <style>
        .table-keluar {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 9px;
        }

        .table-keluar th,
        .table-keluar td {
            padding: 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            vertical-align: middle;
        }

        .table-keluar th {
            font-size: 8px;
        }

        .small-text {
            font-size: 8px;
            color: #666;
        }
    </style>

    <table class="table-keluar">

        <thead>

            <tr>

                <th style="width: 20px;">
                    No
                </th>

                <th style="width: 75px;">
                    No Transaksi
                </th>

                <th style="width: 50px;">
                    Tgl
                </th>

                <th style="width: 90px;">
                    Pekerjaan
                </th>

                <th style="width: 70px;">
                    PIC
                </th>

                <th style="width: 100px;">
                    Barang
                </th>

                <th style="width: 45px;">
                    Kat
                </th>

                <th style="width: 35px;">
                    Qty
                </th>

                <th style="width: 70px;">
                    Harga
                </th>

                <th style="width: 75px;">
                    Total
                </th>

                <th style="width: 45px;">
                    Tipe
                </th>

                <th style="width: 60px;">
                    Kembali
                </th>

                <th style="width: 70px;">
                    Status
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($data as $d)
                <tr>

                    <td>
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        {{ $d->no_transaksi }}
                    </td>

                    <td>
                        {{ $d->tanggal_keluar->format('d/m/Y') }}
                    </td>

                    <td class="text-left">
                        {{ $d->pekerjaan->nama_pekerjaan }}
                    </td>

                    <td class="text-left">
                        {{ $d->pekerjaan->nama_peminjam }}
                    </td>

                    <td class="text-left">
                        {{ $d->barang->nama_barang }}
                    </td>

                    <td>
                        {{ strtoupper($d->barang->kategori) }}
                    </td>

                    <td class="bold">
                        {{ number_format($d->jumlah) }}
                    </td>

                    {{-- Harga Satuan --}}
                    <td class="text-right">

                        @if ($d->barang->kategori === 'tools')
                            <span style="color: #999; font-style: italic;">
                                -
                            </span>
                        @else
                            Rp {{ number_format($d->barang->prices ?? ($d->barang->harga ?? 0), 0, ',', '.') }}
                        @endif

                    </td>

                    {{-- Total HPP --}}
                    <td class="text-right bold">

                        @if ($d->barang->kategori === 'tools')
                            <span style="color: #999; font-style: italic;">
                                -
                            </span>
                        @else
                            Rp {{ number_format($d->total_hpp, 0, ',', '.') }}
                        @endif

                    </td>

                    <td>
                        {{ $d->status_pinjam ? 'PJM' : 'PERM' }}
                    </td>

                    <td>
                        {{ $d->tgl_kembali_rencana?->format('d/m/Y') ?? '-' }}
                    </td>

                    <td>

                        {{ strtoupper($d->status_label) }}

                        <br>

                        <span class="small-text">
                            {{ $d->updated_at->format('d/m/y H:i') }}
                        </span>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="13">

                        <div style="padding: 20px; text-align: center;">

                            Tidak ada data

                        </div>

                    </td>

                </tr>
            @endforelse

        </tbody>

        @if ($data->count() > 0)
            <tfoot>

                <tr style="background: #f9f9f9;">

                    <td colspan="7" class="text-right bold">
                        GRAND TOTAL
                    </td>

                    <td class="bold">
                        {{ number_format($totalJumlah) }}
                    </td>

                    <td></td>

                    <td class="text-right bold">
                        Rp {{ number_format($totalHpp, 0, ',', '.') }}
                    </td>

                    <td colspan="3"></td>

                </tr>

            </tfoot>
        @endif

    </table>

@endsection
