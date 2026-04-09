@extends('layouts.app')
@section('title', $barang->nama_barang)

@section('content')

    <div style="margin-bottom: 20px;">
        <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Barang
        </a>
    </div>

    <div class="grid-2" style="align-items:start;">

        {{-- INFO BARANG --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-box" style="color:var(--accent);"></i> Info Barang</h3>
                <div style="display:flex; gap:6px;">
                    <a href="{{ route('barang.edit', $barang) }}" class="btn btn-xs btn-warning">
                        <i class="fa-solid fa-pen"></i> Edit
                    </a>
                    <form method="POST" action="{{ route('barang.destroy', $barang) }}"
                        onsubmit="return confirm('Hapus barang ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">

                @if ($barang->foto)
                    <img src="{{ asset('storage/' . $barang->foto) }}"
                        style="width:100%; max-height:200px; object-fit:cover; border-radius:8px; margin-bottom:16px; border:1px solid var(--border);">
                @endif

                <table style="width:100%; font-size:13.5px;">
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); width:130px; font-weight:600;">Kode</td>
                        <td><code class="kode">{{ $barang->kode_barang }}</code></td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); font-weight:600;">Nama</td>
                        <td style="font-weight:700;">{{ $barang->nama_barang }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); font-weight:600;">Kategori</td>
                        <td><span class="badge badge-{{ $barang->kategori }}">{{ strtoupper($barang->kategori) }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); font-weight:600;">Satuan</td>
                        <td>{{ $barang->satuan }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); font-weight:600;">Stok</td>
                        <td>
                            <span
                                style="font-size:22px; font-weight:800; font-family:'Barlow Condensed',sans-serif;
                              color:{{ $barang->stok == 0 ? 'var(--danger)' : ($barang->isStokMenipis() ? 'var(--warning)' : 'var(--success)') }};">
                                {{ $barang->stok }}
                            </span>
                            <span style="color:var(--text-muted);"> {{ $barang->satuan }}</span>

                            @if ($barang->isTools() && $barang->stok_dipinjam > 0)
                                <span class="badge badge-warning" style="margin-left:8px;">
                                    {{ $barang->stok_dipinjam }} dipinjam
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); font-weight:600;">Stok Min</td>
                        <td>{{ $barang->stok_minimum }} {{ $barang->satuan }}</td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--text-muted); font-weight:600;">Status</td>
                        <td>
                            @if ($barang->stok == 0)
                                <span class="badge badge-danger">HABIS</span>
                            @elseif($barang->isStokMenipis())
                                <span class="badge badge-warning">MENIPIS</span>
                            @else
                                <span class="badge badge-success">AMAN</span>
                            @endif
                        </td>
                    </tr>
                    @if ($barang->keterangan)
                        <tr>
                            <td style="padding:8px 0; color:var(--text-muted); font-weight:600; vertical-align:top;">
                                Keterangan</td>
                            <td style="padding-top:8px;">{{ $barang->keterangan }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- RIWAYAT --}}
        <div style="display:flex; flex-direction:column; gap:14px;">

            {{-- Riwayat Masuk --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-arrow-trend-up" style="color:var(--success);"></i> Riwayat Masuk</h3>
                    <span style="font-size:12px; color:var(--text-muted);">10 terakhir</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Stok Sesudah</th>
                                <th>Sumber</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatMasuk as $rm)
                                <tr>
                                    <td style="font-size:12px;">{{ $rm->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <span style="color:var(--success); font-weight:700;">+{{ $rm->jumlah }}</span>
                                    </td>
                                    <td style="font-weight:600;">{{ $rm->stok_sesudah }}</td>
                                    <td style="font-size:12px; color:var(--text-muted);">{{ $rm->sumber ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:20px;">
                                        Belum ada riwayat masuk
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Riwayat Keluar --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="fa-solid fa-arrow-trend-down" style="color:var(--danger);"></i> Riwayat Keluar</h3>
                    <span style="font-size:12px; color:var(--text-muted);">10 terakhir</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Pekerjaan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riwayatKeluar as $rk)
                                <tr>
                                    <td style="font-size:12px;">{{ $rk->tanggal_keluar->format('d/m/Y') }}</td>
                                    <td>
                                        <span style="color:var(--danger); font-weight:700;">-{{ $rk->jumlah }}</span>
                                    </td>
                                    <td style="font-size:12px;">
                                        <a href="{{ route('pekerjaan.show', $rk->pekerjaan) }}"
                                            style="color:var(--text); font-weight:600; text-decoration:none;">
                                            {{ $rk->pekerjaan->nama_pekerjaan }}
                                        </a>
                                    </td>
                                    <td>
                                        @if ($rk->status_pinjam)
                                            <span class="badge {{ $rk->status_badge }}">{{ $rk->status_label }}</span>
                                        @else
                                            <span style="font-size:11px; color:var(--text-muted);">Permanen</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:20px;">
                                        Belum ada riwayat keluar
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection
