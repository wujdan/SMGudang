@extends('layouts.app')
@section('title', 'Manajemen Pekerjaan')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fa-solid fa-hard-hat" style="color: var(--primary); margin-right: 8px;"></i>
                Daftar Pekerjaan
            </h3>

            {{-- Tombol hanya untuk admin --}}
            @if (auth()->user()->isAdmin())
                <a href="{{ route('pekerjaan.create') }}" class="btn btn-dark">
                    <i class="fa-solid fa-plus"></i> Buat Pekerjaan
                </a>
            @endif
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET" class="filter-form">
                <input type="text" name="search" class="form-control search-input"
                    placeholder="Cari nama pekerjaan / PIC..." value="{{ request('search') }}" autocomplete="off">

                <select name="status" class="form-control status-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-search"></i> Cari
                </button>
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
                        <th>Total HPP</th>
                        <th>DiBuat Oleh</th>
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
                                <div style="font-weight: 700; font-size: 13px;">
                                    Rp {{ number_format($p->transaksi->sum('total_hpp'), 0, ',', '.') }}
                                </div>
                            </td>
                            <td style="font-size:13px;">{{ $p->created_by_name ?? '—' }}</td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    {{-- Detail: muncul untuk semua role --}}
                                    <a href="{{ route('pekerjaan.show', $p) }}" class="btn btn-xs btn-secondary"
                                        title="Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>

                                    {{-- Edit & Hapus: hanya admin --}}
                                    @if (auth()->user()->isAdmin())
                                        <a href="{{ route('pekerjaan.edit', $p) }}" class="btn btn-sm btn-warning"
                                            title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form action="{{ route('pekerjaan.destroy', $p) }}" method="POST"
                                            style="display: inline;"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pekerjaan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
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

@push('styles')
    <style>
        /* Filter Form - Default: Desktop sejajar horizontal */
        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            align-items: center;
        }

        .search-input {
            flex: 2;
            min-width: 0;
        }

        .status-select {
            flex: 1;
            min-width: 0;
            max-width: 180px;
        }

        .filter-form .btn {
            flex-shrink: 0;
            white-space: nowrap;
        }

        /* Mobile/HP: Search di atas, select & tombol di bawah */
        @media (max-width: 640px) {
            .filter-form {
                flex-wrap: wrap;
                gap: 8px;
            }

            .search-input {
                flex: 1 1 100%;
            }

            .status-select {
                flex: 1;
                max-width: none;
            }

            .filter-form .btn {
                flex-shrink: 0;
            }
        }
    </style>
@endpush
