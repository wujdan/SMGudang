@extends('layouts.pdf')

@section('title', 'Laporan Barang Masuk')

@section('heading', 'LAPORAN BARANG MASUK')

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

    $totalJumlah = $data->sum('jumlah');

    $totalNominal = $data->sum(function ($item) {
        return $item->jumlah * $item->harga_satuan;
    });

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

        <strong>Total Data:</strong>

        {{ $data->count() }} transaksi

    </div>

@endsection

@section('content')

    <table>

        <thead>

            <tr>

                <th style="width: 35px;">
                    No
                </th>

                <th style="width: 120px;">
                    No Transaksi
                </th>

                <th style="width: 80px;">
                    Tanggal
                </th>

                <th>
                    Barang
                </th>

                <th style="width: 80px;">
                    Kategori
                </th>

                <th style="width: 60px;">
                    Jumlah
                </th>

                <th style="width: 100px;">
                    Harga Satuan
                </th>

                <th style="width: 120px;">
                    Total Nominal
                </th>

                <th style="width: 80px;">
                    Stok Sebelum
                </th>

                <th style="width: 80px;">
                    Stok Sesudah
                </th>

                <th style="width: 140px;">
                    Sumber
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($data as $i => $d)
                <tr>

                    <td>
                        {{ $i + 1 }}
                    </td>

                    <td>

                        {{ $d->no_transaksi ?? '-' }}

                    </td>

                    <td>

                        {{ $d->tanggal->format('d/m/Y') }}

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

                    <td class="text-right">

                        Rp

                        {{ number_format($d->harga_satuan, 0, ',', '.') }}

                    </td>

                    <td class="text-right bold">

                        Rp

                        {{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}

                    </td>

                    <td>

                        {{ number_format($d->stok_sebelum) }}

                    </td>

                    <td class="bold">

                        {{ number_format($d->stok_sesudah) }}

                    </td>

                    <td class="text-left">

                        {{ $d->sumber ?? '-' }}

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="11">

                        <div
                            style="
                        padding: 20px;
                        text-align: center;
                    ">

                            Tidak ada data pada periode ini

                        </div>

                    </td>

                </tr>
            @endforelse

        </tbody>

        @if ($data->count() > 0)
            <tfoot>

                <tr style="background: #f9f9f9;">

                    <td colspan="5" class="text-right bold">

                        GRAND TOTAL

                    </td>

                    <td class="bold">

                        {{ number_format($totalJumlah) }}

                    </td>

                    <td></td>

                    <td class="text-right bold">

                        Rp

                        {{ number_format($totalNominal, 0, ',', '.') }}

                    </td>

                    <td colspan="3"></td>

                </tr>

            </tfoot>
        @endif

    </table>

@endsection
