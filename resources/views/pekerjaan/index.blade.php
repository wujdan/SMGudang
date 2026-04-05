@extends('layouts.app')
@section('title', 'Manajemen Pekerjaan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-hard-hat" style="color: var(--primary); margin-right: 8px;"></i>Daftar Pekerjaan</h3>
            <a href="{{ route('pekerjaan.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Buat Pekerjaan Baru
            </a>
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET" style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama pekerjaan / PIC..."
                    value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
                <select name="status" class="form-control" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Cari</button>
                @if (request()->anyFilled(['search', 'status']))
                    <a href="{{ route('pekerjaan.index') }}" class="btn btn-secondary">Reset</a>
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
                        <th>Status</th>
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
                                <span class="badge {{ $p->status == 'aktif' ? 'badge-warning' : 'badge-success' }}">
                                    {{ strtoupper($p->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <a href="{{ route('pekerjaan.show', $p) }}" class="btn btn-sm btn-primary"
                                        title="Detail & Tambah Barang">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('pekerjaan.edit', $p) }}" class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state"><i class="fa-solid fa-hard-hat"></i>
                                    <p>Belum ada pekerjaan</p>
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
