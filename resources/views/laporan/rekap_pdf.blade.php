@extends('layouts.pdf')

@section('title', 'Rekap Barang Per Pekerjaan')

@section('heading', 'REKAP BARANG PER PEKERJAAN')

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

@section('info-bar')

    <div>
        <strong>Periode:</strong>

        {{ date('d-m-Y', strtotime($dari)) }}

        s/d

        {{ date('d-m-Y', strtotime($sampai)) }}
    </div>

    <div>
        <strong>Status:</strong>
        {{ $statusLabel }}
    </div>

    @if ($search)
        <div>
            <strong>Pencarian:</strong>
            {{ $search }}
        </div>
    @endif

@endsection

@section('content')

    @forelse ($pekerjaan as $p)

        <div style="
        margin-top: 14px;
        margin-bottom: 4px;
        font-size: 11px;
    ">

            <strong>
                {{ $p->kode_pekerjaan }}
                —
                {{ $p->nama_pekerjaan }}
            </strong>

            &nbsp;|&nbsp;

            Status:
            <strong>
                {{ strtoupper($p->status) }}
            </strong>

            &nbsp;|&nbsp;

            Peminjam:
            {{ $p->nama_peminjam }}

            @if ($p->lokasi)
                &nbsp;|&nbsp;

                Lokasi:
                {{ $p->lokasi }}
            @endif

            &nbsp;|&nbsp;

            Tgl Mulai:
            {{ date('d-m-Y', strtotime($p->tanggal_mulai)) }}

        </div>

        @if ($p->transaksi->isNotEmpty())
            <table>

                <thead>

                    <tr>

                        <th style="width: 30px;">
                            No
                        </th>

                        <th>
                            Barang
                        </th>

                        <th>
                            Kategori
                        </th>

                        <th>
                            Total Jumlah
                        </th>

                        <th>
                            Satuan
                        </th>

                        <th>
                            HPP / Unit
                        </th>

                        <th>
                            Total HPP
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @php

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

                                    'hpp_satuan' => $group->first()->hpp_satuan,

                                    'status_pinjam' => $group->first()->status_pinjam,

                                    'status_label' => $group->first()->status_label ?? null,

                                    'tgl_kembali_aktual' => $group->first()->tgl_kembali_aktual,

                                    'total_jumlah' => $group->sum('jumlah'),

                                    'total_hpp' => $group->sum('total_hpp'),
                                ],
                            );

                    @endphp

                    @foreach ($grouped as $row)
                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td class="text-left">
                                {{ $row->nama_barang }}
                            </td>

                            <td>
                                {{ strtoupper($row->kategori) }}
                            </td>

                            <td class="bold">
                                {{ number_format($row->total_jumlah) }}
                            </td>

                            <td>
                                {{ $row->satuan }}
                            </td>

                            <td class="text-right">

                                Rp
                                {{ number_format($row->hpp_satuan, 0, ',', '.') }}

                            </td>

                            <td class="text-right bold">

                                Rp
                                {{ number_format($row->total_hpp, 0, ',', '.') }}

                            </td>

                            <td>

                                @if ($row->status_pinjam)
                                    {{ $row->status_label }}

                                    @if ($row->tgl_kembali_aktual)
                                        <br>

                                        ({{ date('d-m-Y', strtotime($row->tgl_kembali_aktual)) }})
                                    @endif
                                @else
                                    Keluar Permanen
                                @endif

                            </td>

                        </tr>
                    @endforeach

                </tbody>

                <tfoot>

                    <tr class="bold" style="background: #f9f9f9;">

                        <td colspan="3" class="text-right">

                            TOTAL PER PEKERJAAN

                        </td>

                        <td>
                            {{ number_format($grouped->sum('total_jumlah')) }}
                        </td>

                        <td></td>

                        <td></td>

                        <td class="text-right">

                            Rp
                            {{ number_format($grouped->sum('total_hpp'), 0, ',', '.') }}

                        </td>

                        <td></td>

                    </tr>

                </tfoot>

            </table>
        @else
            <table>

                <tbody>

                    <tr>

                        <td colspan="8">
                            Belum ada barang dicatat
                        </td>

                    </tr>

                </tbody>

            </table>
        @endif

    @empty

        <table>

            <tbody>

                <tr>

                    <td colspan="8">
                        Tidak ada data pekerjaan
                    </td>

                </tr>

            </tbody>

        </table>

    @endforelse

@endsection
