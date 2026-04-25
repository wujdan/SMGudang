@extends('layouts.app')
@section('title', 'Rekap Per Pekerjaan')

@section('content')
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3><i class="fa-solid fa-folder-open" style="color: var(--primary); margin-right: 8px;"></i>Rekap Barang Per
                Pekerjaan
            </h3>
            <div style="display: flex; gap: 8px;">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm"><i
                        class="fa-solid fa-file-excel"></i> Excel</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm"><i
                        class="fa-solid fa-file-pdf"></i> PDF</a>
            </div>
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET" style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: end;">

                <input type="text" name="search" class="form-control" placeholder="Cari nama pekerjaan..."
                    value="{{ request('search') }}" style="flex: 1; min-width: 200px;">

                <select name="status" class="form-control" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control" value="{{ $dari }}"
                        style="width: 160px;">
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control" value="{{ $sampai }}"
                        style="width: 160px;">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-search"></i> Filter
                </button>

                @if (request()->anyFilled(['search', 'status', 'dari', 'sampai']))
                    <a href="{{ route('laporan.rekap') }}" class="btn btn-secondary">Reset</a>
                @endif

            </form>
        </div>
    </div>

    @forelse($pekerjaan as $p)
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span class="badge badge-primary">{{ $p->kode_pekerjaan }}</span>
                        <span style="font-size: 15px; font-weight: 700;">{{ $p->nama_pekerjaan }}</span>
                        <span
                            class="badge {{ $p->status == 'aktif' ? 'badge-warning' : 'badge-success' }}">{{ strtoupper($p->status) }}</span>
                    </div>
                    <div
                        style="font-size: 12px; color: var(--muted); margin-top: 4px; display: flex; gap: 12px; flex-wrap: wrap;">
                        <span><i class="fa-solid fa-user"></i> {{ $p->nama_peminjam }}</span>
                        @if ($p->lokasi)
                            <span><i class="fa-solid fa-location-dot"></i> {{ $p->lokasi }}</span>
                        @endif
                        <span><i class="fa-regular fa-calendar"></i> {{ $p->tanggal_mulai->format('d/m/Y') }}</span>
                    </div>
                </div>
                <div style="display: flex; gap: 6px;">
                    <a href="{{ route('pekerjaan.show', $p) }}" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-eye"></i> Detail
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'excel', 'pekerjaan_id' => $p->id]) }}"
                        class="btn btn-sm btn-success" title="Export Excel">
                        <i class="fa-solid fa-file-excel"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf', 'pekerjaan_id' => $p->id]) }}"
                        class="btn btn-sm btn-danger" title="Export PDF">
                        <i class="fa-solid fa-file-pdf"></i>
                    </a>
                </div>
            </div>
            @if ($p->transaksi->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Tgl Keluar</th>
                                <th>Status / Kembali</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($p->transaksi as $t)
                                <tr>
                                    <td style="font-weight: 600;">{{ $t->barang->nama_barang }}</td>
                                    <td><span
                                            class="badge badge-{{ $t->barang->kategori_badge }}">{{ strtoupper($t->barang->kategori) }}</span>
                                    </td>
                                    <td>{{ $t->jumlah }}</td>
                                    <td>{{ $t->barang->satuan }}</td>
                                    <td>
                                        <div>{{ $t->tanggal_keluar->format('d/m/Y') }}</div>
                                        <div style="font-size: 10px; color: var(--muted); margin-top: 2px;">
                                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 9px;"></i>
                                            {{ $t->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($t->status_pinjam)
                                            <span class="badge {{ $t->status_badge }}">{{ $t->status_label }}</span>
                                            @if ($t->tgl_kembali_aktual)
                                                <span
                                                    style="font-size: 11px; color: var(--muted); margin-left: 4px;">{{ $t->tgl_kembali_aktual->format('d/m/Y') }}</span>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">Keluar Permanen</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card-body" style="color: var(--muted); font-size: 13px; text-align: center; padding: 20px;">
                    Belum ada barang dicatat</div>
            @endif
        </div>
    @empty
        <div class="card">
            <div class="empty-state"><i class="fa-solid fa-folder-open"></i>
                <p>Tidak ada pekerjaan</p>
            </div>
        </div>
    @endforelse

    <div>{{ $pekerjaan->links('vendor.pagination.custom') }}</div>
@endsection
