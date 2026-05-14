@extends('layouts.app')
@section('title', 'Laporan Stok Terkini')

@push('styles')
    <style>
        /* ── FILTER BAR ── */
        .filter-bar {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
            border-radius: var(--radius) var(--radius) 0 0;
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

        .select-kategori,
        .select-status {
            flex: 1;
            min-width: 0;
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

        .btn-reset {
            flex-shrink: 0;
        }

        /* ── TABLE WRAP ── */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap table {
            width: 100%;
            min-width: 700px;
            border-collapse: collapse;
        }

        /* ── TABLET (≤768px) — tetap sejajar satu baris ── */
        @media (max-width: 768px) {

            .select-kategori,
            .select-status {
                flex: 1;
                max-width: none;
            }
        }

        /* ── HP (≤480px) ── */
        @media (max-width: 480px) {
            .filter-form {
                gap: 6px;
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
            <h3><i class="fa-solid fa-clipboard-list" style="color: var(--primary); margin-right: 8px;"></i>Laporan Stok
                Terkini</h3>
            <div style="display: flex; gap: 8px;">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        {{-- FILTER --}}
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <select name="kategori" class="form-control select-kategori">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>

                <select name="status" class="form-control select-status">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>

                <button type="submit" class="btn btn-primary btn-filter">
                    <i class="fa-solid fa-search"></i> <span class="btn-text">Filter</span>
                </button>

                @if (request()->anyFilled(['kategori', 'status']))
                    <a href="{{ route('laporan.stok') }}" class="btn btn-secondary btn-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Harga</th>
                        @if (request('kategori') == 'tools' || !request('kategori'))
                            <th>Dipinjam</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($barang as $i => $b)
                        <tr>
                            <td style="color: var(--muted);">{{ $i + 1 }}</td>
                            <td><code
                                    style="font-size: 12px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $b->kode_barang }}</code>
                            </td>
                            <td style="font-weight: 600;">{{ $b->nama_barang }}</td>
                            <td><span
                                    class="badge badge-{{ $b->kategori_badge ?? 'secondary' }}">{{ strtoupper($b->kategori) }}</span>
                            </td>
                            <td>{{ $b->satuan }}</td>
                            <td>
                                <span
                                    style="font-weight: 700; font-size: 15px; color: {{ $b->stok == 0 ? 'var(--danger)' : ($b->isStokMenipis() ? 'var(--warning)' : 'var(--success)') }}">
                                    {{ $b->stok }}
                                </span>
                            </td>
                            <td>
                                @if ($b->stok == 0)
                                    <span class="badge badge-danger">HABIS</span>
                                @elseif($b->isStokMenipis())
                                    <span class="badge badge-warning">MENIPIS</span>
                                @else
                                    <span class="badge badge-success">AMAN</span>
                                @endif
                            </td>
                            <td>
                                @if ($b->kategori === 'tools')
                                    <span style="color: var(--muted); font-size: 12px; font-style: italic;">
                                        Tidak digunakan
                                    </span>
                                @else
                                    <span class="harga-item">
                                        Rp {{ number_format($b->prices, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            @if (request('kategori') == 'tools' || !request('kategori'))
                                <td>
                                    @if ($b->isTools() && ($b->stok_dipinjam ?? 0) > 0)
                                        <span class="badge badge-warning">{{ $b->stok_dipinjam }} dipinjam</span>
                                    @else
                                        <span style="color: var(--muted);">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa-solid fa-clipboard-list"></i>
                                    <p>Tidak ada data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            <small style="color: var(--muted);">Total: {{ $barang->count() }} item | Diperbarui:
                {{ now()->format('d/m/Y H:i') }}</small>
        </div>
    </div>
@endsection
