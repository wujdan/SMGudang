@extends('layouts.app')
@section('title', 'Laporan Barang Keluar')

@push('styles')
    <style>
        /* ── CARD HEADER ── */
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            white-space: nowrap;
        }

        /* Container tombol export - selalu di kanan atas */
        .export-buttons {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        /* ── FILTER BAR ── */
        .filter-bar {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
            border-radius: 0;
        }

        /* Filter Form - Default: Desktop sejajar horizontal penuh */
        .filter-form {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            align-items: center;
        }

        .filter-form .form-control {
            height: 34px;
            padding: 0 10px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .search-input {
            flex: 2;
            min-width: 0;
        }

        .select-kategori,
        .select-status {
            flex: 1;
            min-width: 0;
        }

        .date-input {
            flex: 1;
            min-width: 0;
            color: var(--text);
            -webkit-appearance: none;
            appearance: none;
            min-height: 34px;
        }

        .date-input:not(:valid) {
            color: var(--muted);
        }

        .date-input:valid {
            color: var(--text);
        }

        .filter-form .btn {
            flex-shrink: 0;
            white-space: nowrap;
            height: 34px;
            padding: 0 14px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── TABLE WRAP ── */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap table {
            width: 100%;
            min-width: 900px;
            border-collapse: collapse;
        }

        /* ── TABLET (≤768px) — tetap sejajar satu baris ── */
        @media (max-width: 768px) {
            .card-header {
                gap: 8px;
            }

            .card-header h3 {
                font-size: 16px;
            }

            .export-buttons .btn {
                padding: 0 10px;
                font-size: 12px;
                height: 32px;
            }

            .search-input-desktop {
                flex: 2;
            }

            .date-input,
            .select-kategori,
            .select-status {
                flex: 0 0 auto;
                max-width: 130px;
            }
        }

        /* ── HP (≤480px) ── */
        @media (max-width: 480px) {

            /* Export buttons tetap di kanan atas meski di HP */
            .card-header {
                flex-direction: row;
                justify-content: space-between;
            }

            .card-header h3 {
                font-size: 14px;
            }

            .export-buttons .btn {
                padding: 0 8px;
                font-size: 11px;
                height: 28px;
            }

            .filter-form {
                flex-wrap: wrap;
                gap: 6px;
            }

            /* Sembunyikan search desktop */
            .search-input-desktop {
                display: none;
            }

            /* Tampilkan search mobile */
            .search-input-mobile {
                display: block !important;
            }

            .date-input {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            .search-input-mobile {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            .select-kategori,
            .select-status {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            .btn-filter {
                flex: 1 1 auto;
                justify-content: center;
            }

            .filter-form .btn-reset {
                flex: 0 0 auto;
                padding: 0 10px;
                font-size: 11px;
                height: 28px;
                align-self: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-file-export" style="color: var(--warning); margin-right: 8px;"></i>Laporan Barang Keluar
            </h3>
            <div class="export-buttons">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel"></i> <span class="btn-text">Excel</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-file-pdf"></i> <span class="btn-text">PDF</span>
                </a>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="filter-bar">
            <form method="GET" autocomplete="off" class="filter-form">
                {{-- Search Desktop (paling kiri) --}}
                <input type="text" name="nama_barang" class="form-control search-input search-input-desktop"
                    value="{{ request('nama_barang') }}" placeholder="Cari nama barang...">

                <input type="date" name="dari" class="form-control date-input"
                    value="{{ request('dari', now()->subDays(30)->format('Y-m-d')) }}" title="Dari tanggal">

                <input type="date" name="sampai" class="form-control date-input"
                    value="{{ request('sampai', now()->format('Y-m-d')) }}" title="Sampai tanggal">

                {{-- Search Mobile (hidden di desktop) --}}
                <input type="text" name="nama_barang" class="form-control search-input search-input-mobile"
                    value="{{ request('nama_barang') }}" placeholder="Cari nama barang..." style="display: none;">

                <select name="kategori" class="form-control select-kategori">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>

                <select name="status" class="form-control select-status">
                    <option value="">Semua Status</option>
                    <option value="permanen" {{ request('status') == 'permanen' ? 'selected' : '' }}>Permanen (Cons/Mat)
                    </option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan
                    </option>
                </select>

                <button type="submit" class="btn btn-primary btn-filter">
                    <i class="fa-solid fa-search"></i> <span class="btn-text">Filter</span>
                </button>

                @if (request()->anyFilled(['dari', 'sampai', 'kategori', 'status', 'nama_barang']))
                    <a href="{{ route('laporan.keluar') }}" class="btn btn-secondary btn-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tgl Keluar</th>
                        <th>Pekerjaan</th>
                        <th>PIC</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total HPP</th>
                        <th>Tipe</th>
                        <th>Rencana Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $d)
                        <tr>
                            <td>
                                <code
                                    style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $d->no_transaksi }}</code>
                            </td>
                            <td>{{ $d->tanggal_keluar->format('d/m/Y') }}</td>
                            <td style="font-size: 12px; font-weight: 600;">{{ $d->pekerjaan->nama_pekerjaan }}</td>
                            <td style="font-size: 12px;">{{ $d->pekerjaan->nama_peminjam }}</td>
                            <td style="font-weight: 600;">{{ $d->barang->nama_barang }}</td>
                            <td>
                                <span class="badge badge-{{ $d->barang->kategori_badge ?? 'secondary' }}">
                                    {{ strtoupper($d->barang->kategori) }}
                                </span>
                            </td>
                            <td><span style="color: var(--danger); font-weight: 700;">-{{ $d->jumlah }}</span></td>
                            <td>
                                @if ($d->barang->kategori === 'tools')
                                    <span style="color: var(--muted); font-style: italic;">-</span>
                                @else
                                    <span style="font-weight: 600;">
                                        Rp {{ number_format($d->barang->prices ?? ($d->barang->harga ?? 0), 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($d->barang->kategori === 'tools')
                                    <span style="color: var(--muted); font-style: italic;">-</span>
                                @else
                                    <span style="color: var(--primary); font-weight: 700;">
                                        Rp {{ number_format($d->total_hpp, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if ($d->status_pinjam)
                                    <span class="badge badge-warning">PINJAM</span>
                                @else
                                    <span class="badge badge-secondary">PERMANEN</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: {{ $d->isTerlambat() ? 'var(--danger)' : 'inherit' }};">
                                {{ $d->tgl_kembali_rencana?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td>
                                <span class="badge {{ $d->status_badge }}">{{ $d->status_label }}</span>
                                <div style="font-size: 10px; color: var(--muted); margin-top: 4px; white-space: nowrap;">
                                    <i class="fa-solid fa-clock-rotate-left" style="font-size: 9px;"></i>
                                    {{ $d->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="empty-state">
                                    <i class="fa-solid fa-file-export"></i>
                                    <p>Tidak ada data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($data->count() > 0)
                    <tfoot>
                        <tr style="background: #f8f9fa;">
                            <td colspan="6" class="text-right"><strong>GRAND TOTAL</strong></td>
                            <td><strong>{{ $totalJumlah }}</strong></td>
                            <td colspan="2"><strong>Rp {{ number_format($totalHpp, 0, ',', '.') }}</strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="card-body">
            <small style="color: var(--muted);">
                Periode: {{ \Carbon\Carbon::parse(request('dari', now()->subDays(30)))->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse(request('sampai', now()))->format('d/m/Y') }}
                @if (request('kategori'))
                    | Kategori:
                    {{ request('kategori') == 'cons' ? 'Consumable' : (request('kategori') == 'material' ? 'Material' : 'Tools') }}
                @endif
                @if (request('status'))
                    | Status:
                    {{ request('status') == 'permanen' ? 'Permanen' : (request('status') == 'dipinjam' ? 'Dipinjam' : 'Dikembalikan') }}
                @endif
            </small>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Sinkronkan nilai kedua search input
        function syncSearchValues() {
            const searchDesktop = document.querySelector('.search-input-desktop');
            const searchMobile = document.querySelector('.search-input-mobile');

            if (searchDesktop && searchMobile) {
                searchDesktop.addEventListener('input', function() {
                    searchMobile.value = this.value;
                });

                searchMobile.addEventListener('input', function() {
                    searchDesktop.value = this.value;
                });
            }
        }

        window.addEventListener('load', syncSearchValues);
    </script>
@endpush
