@extends('layouts.app')
@section('title', 'Laporan Barang Masuk')

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

        .select-kategori {
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
            min-width: 800px;
            border-collapse: collapse;
        }

        /* ── TABLET (≤768px) — tetap sejajar satu baris ── */
        @media (max-width: 768px) {
            .select-kategori,
            .date-input {
                flex: 1;
                max-width: none;
            }
            
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
        }

        /* ── HP (≤480px) ── */
        @media (max-width: 480px) {
            .filter-form {
                flex-wrap: wrap;
                gap: 6px;
            }

            .date-input {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            .select-kategori {
                flex: 1 1 100%;
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
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-file-import" style="color: var(--success); margin-right: 8px;"></i>Laporan Barang Masuk</h3>
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
            <form method="GET" class="filter-form">
                <input type="date" name="dari" class="form-control date-input"
                    value="{{ request('dari', now()->subDays(30)->format('Y-m-d')) }}" title="Dari tanggal">

                <input type="date" name="sampai" class="form-control date-input"
                    value="{{ request('sampai', now()->format('Y-m-d')) }}" title="Sampai tanggal">

                <select name="kategori" class="form-control select-kategori">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>

                <button type="submit" class="btn btn-primary btn-filter">
                    <i class="fa-solid fa-search"></i> <span class="btn-text">Filter</span>
                </button>

                @if (request()->anyFilled(['dari', 'sampai', 'kategori']))
                    <a href="{{ route('laporan.masuk') }}" class="btn btn-secondary btn-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Harga Satuan</th>
                        <th>Total Nominal</th>
                        <th>Stok Sebelum</th>
                        <th>Stok Sesudah</th>
                        <th>Sumber</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $d)
                        <tr>
                            <td><code
                                    style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $d->no_transaksi }}</code>
                            </td>
                            <td>{{ $d->tanggal->format('d/m/Y') }}</td>
                            <td style="font-weight: 600;">{{ $d->barang->nama_barang }}</td>
                            <td>
                                <span class="badge badge-{{ $d->barang->kategori_badge ?? 'secondary' }}">
                                    {{ strtoupper($d->barang->kategori) }}
                                </span>
                            </td>
                            <td><span style="color: var(--success); font-weight: 700;">+{{ $d->jumlah }}</span></td>
                            <td>
                                <span style="font-weight: 600;">
                                    Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <span style="color: var(--primary); font-weight: 700;">
                                    Rp {{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}
                                </span>
                            </td>
                            <td style="color: var(--muted);">{{ $d->stok_sebelum }}</td>
                            <td style="font-weight: 600;">{{ $d->stok_sesudah }}</td>
                            <td style="color: var(--muted);">{{ $d->sumber ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa-solid fa-file-import"></i>
                                    <p>Tidak ada data pada periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($data->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>GRAND TOTAL</strong></td>
                            <td><strong>{{ $totalJumlah }}</strong></td>
                            <td colspan="2"><strong>Rp {{ number_format($totalNominal, 0, ',', '.') }}</strong></td>
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
            </small>
        </div>
    </div>
@endsection