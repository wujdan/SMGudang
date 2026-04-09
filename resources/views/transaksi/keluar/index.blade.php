@extends('layouts.app')
@section('title', 'Barang Keluar ')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-truck-ramp-box" style="color: var(--primary); margin-right: 8px;"></i>Barang Keluar </h3>
            <p style="font-size: 13px; color: var(--muted); margin: 4px 0 0 0;">Pilih pekerjaan aktif untuk mencatat barang keluar</p>
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET" style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama pekerjaan / PIC..."
                    value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Cari</button>
                @if (request()->anyFilled(['search']))
                    <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Pekerjaan</th>
                        <th>PIC / Peminjam</th>
                        <th>Lokasi</th>
                        <th>Tgl Mulai</th>
                        <th>Tools Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pekerjaan as $p)
                        <tr>
                            <td><span class="badge badge-primary">{{ $p->kode_pekerjaan }}</span></td>
                            <td style="font-weight: 600;">{{ $p->nama_pekerjaan }}</td>
                            <td>{{ $p->nama_peminjam }}</td>
                            <td style="color: var(--muted);">{{ $p->lokasi ?? '-' }}</td>
                            <td style="color: var(--muted);">{{ $p->tanggal_mulai->format('d/m/Y') }}</td>
                            <td>
                                @if ($p->tools_dipinjam_count > 0)
                                    <span class="badge badge-warning"><i class="fa-solid fa-screwdriver-wrench"></i>
                                        {{ $p->tools_dipinjam_count }} aktif</span>
                                @else
                                    <span class="badge badge-success">Semua kembali</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('barang-keluar.create', $p) }}" class="btn btn-sm btn-primary">
                                    <i class="fa-solid fa-plus"></i> Tambah Barang Keluar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state"><i class="fa-solid fa-hard-hat"></i>
                                    <p>Tidak ada pekerjaan aktif</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body" style="padding-top: 12px;">
            {{ $pekerjaan->links('vendor.pagination.custom') }}
        </div>
    </div>
@endsection