@extends('layouts.app')
@section('title', 'Rekap Per Pekerjaan')

@section('content')

    {{-- SUMMARY CARDS --}}
    <div class="stats-grid" style="margin-bottom: 20px;">

        <div class="stat-card blue">
            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-hard-hat"></i>
                </div>
                <span class="stat-status neutral">
                    <i class="fa-solid fa-circle"></i> Total
                </span>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalPekerjaan) }}</div>
                <div class="stat-lbl">Total Pekerjaan</div>
            </div>
        </div>

        <div class="stat-card amber">
            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <span class="stat-status warn">
                    <i class="fa-solid fa-circle"></i> Transaksi
                </span>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalTransaksi) }}</div>
                <div class="stat-lbl">Total Transaksi</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <span class="stat-status danger">
                    <i class="fa-solid fa-circle"></i> Keluar
                </span>
            </div>
            <div>
                <div class="stat-val">{{ number_format($totalQty) }}</div>
                <div class="stat-lbl">Total Qty Keluar</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-top">
                <div class="stat-icon">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <span class="stat-status ok">
                    <i class="fa-solid fa-circle"></i> HPP
                </span>
            </div>
            <div>
                <div class="stat-val" style="font-size: 1.4rem;">
                    Rp {{ number_format($grandTotalHpp, 0, ',', '.') }}
                </div>
                <div class="stat-lbl">Grand Total HPP</div>
            </div>
        </div>

    </div>

    {{-- FILTER CARD --}}
    <div class="card" style="margin-bottom: 20px;">

        <div class="card-header">
            <h3>
                <i class="fa-solid fa-folder-open" style="color: var(--primary);"></i>
                Rekap Barang Per Pekerjaan
            </h3>

            <div style="display: flex; gap: 8px;">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET"
                style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: flex-end;">

                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama pekerjaan..."
                        value="{{ request('search') }}" autocomplete="off">
                </div>

                <div>
                    <select name="status" class="form-control" style="width: 150px;">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label class="form-label" style="margin-bottom: 4px;">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control" value="{{ $dari }}"
                        style="width: 160px;">
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label class="form-label" style="margin-bottom: 4px;">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control" value="{{ $sampai }}"
                        style="width: 160px;">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-search"></i> Filter
                </button>

                @if (request()->anyFilled(['search', 'status', 'dari', 'sampai']))
                    <a href="{{ route('laporan.rekap') }}" class="btn btn-secondary">
                        Reset
                    </a>
                @endif

            </form>
        </div>

    </div>

    {{-- LIST PEKERJAAN --}}
    @forelse($pekerjaan as $p)

        @php
            $totalQtyPekerjaan = $p->transaksi->sum('jumlah');
            $totalHppPekerjaan = $p->transaksi->sum('total_hpp');
            $totalItemPekerjaan = $p->transaksi->count();
        @endphp

        <div class="card" style="margin-bottom: 16px;">

            {{-- HEADER PEKERJAAN --}}
            <div class="card-header">

                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">

                        <span class="badge badge-primary">
                            {{ $p->kode_pekerjaan }}
                        </span>

                        <span style="font-size: 15px; font-weight: 700;">
                            {{ $p->nama_pekerjaan }}
                        </span>

                        <span class="badge {{ $p->status == 'aktif' ? 'badge-warning' : 'badge-success' }}">
                            {{ strtoupper($p->status) }}
                        </span>

                    </div>

                    <div
                        style="
                        font-size: 12px;
                        color: var(--muted);
                        margin-top: 6px;
                        display: flex;
                        gap: 14px;
                        flex-wrap: wrap;
                    ">
                        <span>
                            <i class="fa-solid fa-user" style="margin-right: 4px;"></i>
                            {{ $p->nama_peminjam }}
                        </span>

                        @if ($p->lokasi)
                            <span>
                                <i class="fa-solid fa-location-dot" style="margin-right: 4px;"></i>
                                {{ $p->lokasi }}
                            </span>
                        @endif

                        <span>
                            <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>
                            {{ $p->tanggal_mulai->format('d/m/Y') }}
                        </span>
                    </div>
                </div>

                <div style="display: flex; gap: 6px; flex-shrink: 0;">
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

            {{-- SUMMARY PEKERJAAN --}}
            <div
                style="
                padding: 12px 18px;
                background: var(--border-soft);
                border-bottom: 1px solid var(--border);
                display: flex;
                gap: 24px;
                flex-wrap: wrap;
                font-size: 13px;
            ">
                <div>
                    <span style="color: var(--muted);">Total Transaksi: </span>
                    <strong>{{ number_format($totalItemPekerjaan) }}</strong>
                </div>
                <div>
                    <span style="color: var(--muted);">Total Qty: </span>
                    <strong>{{ number_format($totalQtyPekerjaan) }}</strong>
                </div>
                <div>
                    <span style="color: var(--muted);">Total HPP: </span>
                    <strong style="color: var(--success);">
                        Rp {{ number_format($totalHppPekerjaan, 0, ',', '.') }}
                    </strong>
                </div>
            </div>

            {{-- TABLE TRANSAKSI --}}
            @if ($p->transaksi->isNotEmpty())
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Kategori</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>HPP / Unit</th>
                                <th>Total HPP</th>
                                <th>Tgl Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($p->transaksi as $t)
                                <tr>
                                    <td style="font-weight: 600;">
                                        {{ $t->barang->nama_barang }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $t->barang->kategori_badge }}">
                                            {{ strtoupper($t->barang->kategori) }}
                                        </span>
                                    </td>
                                    <td>{{ number_format($t->jumlah) }}</td>
                                    <td>{{ $t->barang->satuan }}</td>
                                    <td>Rp {{ number_format($t->hpp_satuan, 0, ',', '.') }}</td>
                                    <td style="font-weight: 700; color: var(--success);">
                                        Rp {{ number_format($t->total_hpp, 0, ',', '.') }}
                                    </td>
                                    <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                    <td>
                                        @if ($t->status_pinjam)
                                            <span class="badge {{ $t->status_badge }}">
                                                {{ $t->status_label }}
                                            </span>
                                            @if ($t->tgl_kembali_aktual)
                                                <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">
                                                    {{ $t->tgl_kembali_aktual->format('d/m/Y') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">Keluar Permanen</span>
                                        @endif

                                        <div
                                            style="font-size: 10px; color: var(--muted); margin-top: 4px; white-space: nowrap;">
                                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 9px;"></i>
                                            {{ $t->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot style="background: var(--border-soft);">
                            <tr>
                                <th colspan="2" style="text-align: right; color: var(--muted);">TOTAL</th>
                                <th>{{ number_format($totalQtyPekerjaan) }}</th>
                                <th></th>
                                <th></th>
                                <th style="color: var(--success);">
                                    Rp {{ number_format($totalHppPekerjaan, 0, ',', '.') }}
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>

                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <p>Belum ada barang dicatat</p>
                </div>
            @endif

        </div>

    @empty

        <div class="card">
            <div class="empty-state">
                <i class="fa-solid fa-folder-open"></i>
                <p>Tidak ada pekerjaan ditemukan</p>
            </div>
        </div>

    @endforelse

    {{-- PAGINATION --}}
    <div>
        {{ $pekerjaan->links('vendor.pagination.custom') }}
    </div>

@endsection
